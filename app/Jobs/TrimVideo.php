<?php

namespace App\Jobs;

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

    public function __construct(
        public int $mediaId,
        public float $startTime,
        public float $endTime,
    ) {}

    public function handle(): void
    {
        $media = StudentMedia::find($this->mediaId);
        if (!$media || $media->type !== 'video') {
            Log::warning("TrimVideo: Media {$this->mediaId} not found or not video");
            return;
        }

        $prefix = config('services.media_path_prefix');

        // Download from S3 to temp
        $ext = pathinfo($media->path, PATHINFO_EXTENSION) ?: 'mp4';
        $tempInput = tempnam(sys_get_temp_dir(), 'trim_in_') . '.' . $ext;
        $tempOutput = tempnam(sys_get_temp_dir(), 'trim_out_') . '.' . $ext;

        file_put_contents($tempInput, Storage::disk('s3')->get($media->path));

        // FFmpeg trim — use -c copy for instant lossless cut
        $ss = number_format($this->startTime, 3, '.', '');
        $to = number_format($this->endTime, 3, '.', '');
        $cmd = sprintf(
            'ffmpeg -y -ss %s -to %s -i %s -c copy -avoid_negative_ts make_zero %s 2>&1',
            escapeshellarg($ss),
            escapeshellarg($to),
            escapeshellarg($tempInput),
            escapeshellarg($tempOutput)
        );

        $output = shell_exec($cmd);

        if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
            // Fallback: re-encode if copy fails (happens with some codecs at exact cut points)
            $cmd = sprintf(
                'ffmpeg -y -ss %s -to %s -i %s -preset fast -crf 23 %s 2>&1',
                escapeshellarg($ss),
                escapeshellarg($to),
                escapeshellarg($tempInput),
                escapeshellarg($tempOutput)
            );
            $output = shell_exec($cmd);
        }

        if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
            Log::error("TrimVideo: FFmpeg failed for media {$this->mediaId}", ['output' => $output]);
            @unlink($tempInput);
            @unlink($tempOutput);
            return;
        }

        // Upload trimmed version to S3
        $newPath = ($prefix ? $prefix . '/' : '') . 'students/' . $media->student_id . '/videos/' . Str::uuid() . '.' . $ext;
        Storage::disk('s3')->put($newPath, file_get_contents($tempOutput));

        // Get new metadata
        $width = null;
        $height = null;
        $duration = null;
        $metadata = null;
        $path = escapeshellarg($tempOutput);
        $probeJson = @shell_exec("ffprobe -v error -show_format -show_streams -of json $path 2>/dev/null");
        if ($probeJson) {
            $metadata = json_decode($probeJson, true);
            $vs = collect($metadata['streams'] ?? [])->firstWhere('codec_type', 'video');
            if ($vs) {
                $width = (int)($vs['width'] ?? 0);
                $height = (int)($vs['height'] ?? 0);
            }
            $duration = (float)($metadata['format']['duration'] ?? 0);
        }

        // Preserve original (first trim saves it, subsequent trims keep the first original)
        if (!$media->original_path) {
            $media->original_path = $media->path;
        } else {
            // Delete previous trimmed version
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

        // Cleanup
        @unlink($tempInput);
        @unlink($tempOutput);

        Log::info("TrimVideo: Trimmed media {$this->mediaId} ({$ss}s - {$to}s), new duration: {$duration}s");
    }
}
