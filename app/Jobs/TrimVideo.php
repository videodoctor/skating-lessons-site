<?php

namespace App\Jobs;

use App\Models\MediaJob;
use App\Models\StudentMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrimVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public ?int $jobTrackerId = null;

    public function __construct(
        public int $mediaId,
        public float $startTime,
        public float $endTime,
        public int $brightness = 100,
        public int $contrast = 100,
        public int $saturation = 100,
    ) {}

    public function handle(): void
    {
        $media = StudentMedia::find($this->mediaId);
        if (!$media || $media->type !== 'video') {
            $this->updateTracker('failed', 0, 'Media not found');
            return;
        }

        $prefix = config('services.media_path_prefix');
        $ext = pathinfo($media->path, PATHINFO_EXTENSION) ?: 'mp4';
        $tempInput = tempnam(sys_get_temp_dir(), 'trim_in_') . '.' . $ext;
        $tempOutput = tempnam(sys_get_temp_dir(), 'trim_out_') . '.' . $ext;
        $tempProgress = tempnam(sys_get_temp_dir(), 'ffprog_');

        // Step 1: Download from S3
        $this->updateTracker('downloading', 10, 'Downloading from storage...');
        file_put_contents($tempInput, Storage::disk('s3')->get($media->path));
        $inputSize = filesize($tempInput);
        $this->updateTracker('processing', 20, 'Downloaded ' . round($inputSize / 1048576, 1) . 'MB. Processing...');

        // Get total duration for progress calculation
        $totalDuration = $this->endTime - $this->startTime;

        $ss = number_format($this->startTime, 3, '.', '');
        $to = number_format($this->endTime, 3, '.', '');
        $hasAdjustments = $this->brightness !== 100 || $this->contrast !== 100 || $this->saturation !== 100;

        if (!$hasAdjustments) {
            $this->updateTracker('processing', 30, 'Lossless trim (no re-encoding)...');
            $cmd = sprintf(
                'ffmpeg -y -ss %s -to %s -i %s -c copy -avoid_negative_ts make_zero %s 2>&1',
                escapeshellarg($ss), escapeshellarg($to),
                escapeshellarg($tempInput), escapeshellarg($tempOutput)
            );
            shell_exec($cmd);

            if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
                $this->updateTracker('processing', 35, 'Re-encoding (lossless failed)...');
                $this->runFfmpegWithProgress($tempInput, $tempOutput, $ss, $to, null, $totalDuration, $tempProgress);
            }
        } else {
            $eqBrightness = number_format(($this->brightness - 100) / 100, 2, '.', '');
            $eqContrast = number_format($this->contrast / 100, 2, '.', '');
            $eqSaturation = number_format($this->saturation / 100, 2, '.', '');
            $vf = "eq=brightness={$eqBrightness}:contrast={$eqContrast}:saturation={$eqSaturation}";

            $this->updateTracker('processing', 25, 'Re-encoding with adjustments...');
            $this->runFfmpegWithProgress($tempInput, $tempOutput, $ss, $to, $vf, $totalDuration, $tempProgress);
        }

        if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
            $this->updateTracker('failed', 0, 'FFmpeg processing failed');
            Log::error("TrimVideo: FFmpeg failed for media {$this->mediaId}");
            @unlink($tempInput);
            @unlink($tempOutput);
            @unlink($tempProgress);
            return;
        }

        // Step 3: Upload to S3
        $this->updateTracker('uploading', 85, 'Uploading trimmed video...');
        $newPath = ($prefix ? $prefix . '/' : '') . 'students/' . $media->student_id . '/videos/' . Str::uuid() . '.' . $ext;
        Storage::disk('s3')->put($newPath, file_get_contents($tempOutput));

        // Get metadata
        $this->updateTracker('uploading', 95, 'Finalizing...');
        $width = null; $height = null; $duration = null; $metadata = null;
        $path = escapeshellarg($tempOutput);
        $probeJson = @shell_exec("ffprobe -v error -show_format -show_streams -of json $path 2>/dev/null");
        if ($probeJson) {
            $metadata = json_decode($probeJson, true);
            $vs = collect($metadata['streams'] ?? [])->firstWhere('codec_type', 'video');
            if ($vs) { $width = (int)($vs['width'] ?? 0); $height = (int)($vs['height'] ?? 0); }
            $duration = (float)($metadata['format']['duration'] ?? 0);
        }

        // Preserve original
        if (!$media->original_path) {
            $media->original_path = $media->path;
        } else {
            Storage::disk('s3')->delete($media->path);
        }

        $media->update([
            'original_path'  => $media->original_path,
            'path'           => $newPath,
            'file_size'      => filesize($tempOutput),
            'width'          => $width,
            'height'         => $height,
            'duration'       => $duration,
            'media_metadata' => $metadata,
        ]);

        @unlink($tempInput);
        @unlink($tempOutput);
        @unlink($tempProgress);

        $this->updateTracker('complete', 100, 'Done! Duration: ' . number_format($duration, 1) . 's');
        Log::info("TrimVideo: Trimmed media {$this->mediaId} ({$ss}s - {$to}s), new duration: {$duration}s");
    }

    private function runFfmpegWithProgress(string $input, string $output, string $ss, string $to, ?string $vf, float $totalDuration, string $progressFile): void
    {
        $vfArg = $vf ? '-vf ' . escapeshellarg($vf) : '';
        $cmd = sprintf(
            'ffmpeg -y -ss %s -to %s -i %s %s -preset fast -crf 23 -progress %s %s 2>/dev/null &',
            escapeshellarg($ss), escapeshellarg($to),
            escapeshellarg($input), $vfArg,
            escapeshellarg($progressFile), escapeshellarg($output)
        );

        // Run in background
        $pid = shell_exec($cmd . ' echo $!');
        $pid = trim($pid);

        // Poll progress file
        $maxWait = 300; // 5 min max
        $waited = 0;
        while ($waited < $maxWait) {
            sleep(1);
            $waited++;

            // Check if ffmpeg is still running
            if ($pid && !file_exists("/proc/$pid")) break;

            // Parse progress
            if (file_exists($progressFile)) {
                $content = @file_get_contents($progressFile);
                if (preg_match('/out_time_us=(\d+)/', $content, $m)) {
                    $currentSec = (int)$m[1] / 1000000;
                    $pct = $totalDuration > 0 ? min(80, 25 + round(($currentSec / $totalDuration) * 60)) : 50;
                    $this->updateTracker('processing', $pct, 'Encoding: ' . number_format($currentSec, 1) . 's / ' . number_format($totalDuration, 1) . 's');
                }
                if (str_contains($content, 'progress=end')) break;
            }
        }
    }

    private function updateTracker(string $status, int $progress, string $message): void
    {
        if ($this->jobTrackerId) {
            MediaJob::where('id', $this->jobTrackerId)->update([
                'status'   => $status,
                'progress' => $progress,
                'message'  => $message,
            ]);
        }
    }
}
