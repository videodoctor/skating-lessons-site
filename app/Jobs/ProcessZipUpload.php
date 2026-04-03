<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\StudentMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ProcessZipUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes

    public function __construct(
        public int $studentId,
        public string $s3ZipPath,
        public ?string $caption,
        public string $uploadedByType,
        public ?int $uploadedById,
    ) {}

    public function handle(): void
    {
        $student = Student::find($this->studentId);
        if (!$student) {
            Log::warning("ProcessZipUpload: Student {$this->studentId} not found");
            return;
        }

        $prefix = config('services.media_path_prefix');
        $mediaExts = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'gif', 'mp4', 'mov', 'avi', 'webm'];
        $videoExts = ['mp4', 'mov', 'avi', 'webm'];

        // Download zip from S3 to temp
        $tempZip = tempnam(sys_get_temp_dir(), 'zip_');
        file_put_contents($tempZip, Storage::disk('s3')->get($this->s3ZipPath));

        $zip = new ZipArchive();
        if ($zip->open($tempZip) !== true) {
            Log::warning("ProcessZipUpload: Could not open zip {$this->s3ZipPath}");
            @unlink($tempZip);
            return;
        }

        $uploaded = 0;
        $tempDir = sys_get_temp_dir() . '/zip_extract_' . Str::random(8);
        mkdir($tempDir, 0755, true);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '/') || str_contains($name, '__MACOSX') || str_starts_with(basename($name), '.')) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $mediaExts)) continue;

            $zip->extractTo($tempDir, $name);
            $extractedPath = $tempDir . '/' . $name;
            if (!file_exists($extractedPath)) continue;

            $isVideo = in_array($ext, $videoExts);
            $type = $isVideo ? 'video' : 'photo';
            $s3Path = ($prefix ? $prefix . '/' : '') . 'students/' . $student->id . '/' . $type . 's/' . Str::uuid() . '.' . $ext;

            Storage::disk('s3')->put($s3Path, file_get_contents($extractedPath));

            $width = null; $height = null; $duration = null; $metadata = null;
            if (!$isVideo) {
                $dims = @getimagesize($extractedPath);
                if ($dims) { $width = $dims[0]; $height = $dims[1]; }
            } else {
                $path = escapeshellarg($extractedPath);
                $probeJson = @shell_exec("ffprobe -v error -show_format -show_streams -of json $path 2>/dev/null");
                if ($probeJson) {
                    $metadata = json_decode($probeJson, true);
                    $vs = collect($metadata['streams'] ?? [])->firstWhere('codec_type', 'video');
                    if ($vs) { $width = (int)($vs['width'] ?? 0); $height = (int)($vs['height'] ?? 0); }
                    $duration = (float)($metadata['format']['duration'] ?? 0);
                }
            }

            $media = StudentMedia::create([
                'student_id'        => $student->id,
                'type'              => $type,
                'path'              => $s3Path,
                'original_filename' => basename($name),
                'mime_type'         => mime_content_type($extractedPath),
                'file_size'         => filesize($extractedPath),
                'width'             => $width,
                'height'            => $height,
                'duration'          => $duration,
                'media_metadata'    => $metadata,
                'caption'           => $this->caption,
                'uploaded_by_type'  => $this->uploadedByType,
                'uploaded_by_id'    => $this->uploadedById,
            ]);

            if ($type === 'photo' && !$student->profile_photo_id) {
                $student->update(['profile_photo_id' => $media->id]);
            }

            @unlink($extractedPath);
            $uploaded++;
        }

        $zip->close();
        @unlink($tempZip);
        $this->rmdir($tempDir);

        // Delete the original zip from S3
        Storage::disk('s3')->delete($this->s3ZipPath);

        Log::info("ProcessZipUpload: Extracted {$uploaded} files from {$this->s3ZipPath} for student {$student->full_name}");
    }

    private function rmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
