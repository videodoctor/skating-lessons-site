<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MediaUploadService
{
    const MEDIA_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'gif', 'mp4', 'mov', 'avi', 'webm'];
    const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'webm'];

    /**
     * Process uploaded files (including zips) and store to S3.
     * Returns count of successfully uploaded files.
     */
    public static function processUpload(
        array $files,
        Student $student,
        ?string $caption,
        string $uploadedByType,
        ?int $uploadedById
    ): int {
        $uploaded = 0;
        $prefix = config('services.media_path_prefix');

        foreach ($files as $file) {
            if ($file->getClientOriginalExtension() === 'zip' || $file->getMimeType() === 'application/zip') {
                $uploaded += self::processZip($file, $student, $caption, $uploadedByType, $uploadedById, $prefix);
            } else {
                $result = self::processFile($file->getPathname(), $file->getClientOriginalName(), $file->getMimeType(), $file->getSize(), $student, $caption, $uploadedByType, $uploadedById, $prefix);
                if ($result) $uploaded++;
            }
        }

        return $uploaded;
    }

    /**
     * Extract a zip and process each media file inside.
     */
    private static function processZip(
        UploadedFile $zipFile,
        Student $student,
        ?string $caption,
        string $uploadedByType,
        ?int $uploadedById,
        string $prefix
    ): int {
        $zip = new ZipArchive();
        if ($zip->open($zipFile->getPathname()) !== true) {
            return 0;
        }

        $tempDir = sys_get_temp_dir() . '/media_upload_' . Str::random(8);
        mkdir($tempDir, 0755, true);

        $uploaded = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            // Skip directories, hidden files, macOS resource forks
            if (str_ends_with($name, '/') || str_contains($name, '__MACOSX') || str_starts_with(basename($name), '.')) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, self::MEDIA_EXTENSIONS)) {
                continue;
            }

            // Extract to temp
            $zip->extractTo($tempDir, $name);
            $extractedPath = $tempDir . '/' . $name;

            if (!file_exists($extractedPath)) continue;

            $mime = mime_content_type($extractedPath) ?: 'application/octet-stream';
            $size = filesize($extractedPath);

            $result = self::processFile($extractedPath, basename($name), $mime, $size, $student, $caption, $uploadedByType, $uploadedById, $prefix);
            if ($result) $uploaded++;

            @unlink($extractedPath);
        }

        $zip->close();

        // Clean up temp dir
        self::rmdir($tempDir);

        return $uploaded;
    }

    /**
     * Process a single media file: upload to S3, extract metadata, create DB record.
     */
    private static function processFile(
        string $localPath,
        string $originalName,
        string $mimeType,
        int $fileSize,
        Student $student,
        ?string $caption,
        string $uploadedByType,
        ?int $uploadedById,
        string $prefix
    ): ?StudentMedia {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::MEDIA_EXTENSIONS)) return null;

        $isVideo = in_array($ext, self::VIDEO_EXTENSIONS);
        $type = $isVideo ? 'video' : 'photo';
        $s3Path = ($prefix ? $prefix . '/' : '') . 'students/' . $student->id . '/' . $type . 's/' . Str::uuid() . '.' . $ext;

        Storage::disk('s3')->put($s3Path, file_get_contents($localPath));

        // Extract metadata
        $width = null;
        $height = null;
        $duration = null;
        $metadata = null;

        if (!$isVideo) {
            $dims = @getimagesize($localPath);
            if ($dims) { $width = $dims[0]; $height = $dims[1]; }
            $exif = @exif_read_data($localPath);
            if ($exif) { $metadata = $exif; }
        } else {
            $path = escapeshellarg($localPath);
            $probeJson = @shell_exec("ffprobe -v error -show_format -show_streams -of json $path 2>/dev/null");
            if ($probeJson) {
                $metadata = json_decode($probeJson, true);
                $videoStream = collect($metadata['streams'] ?? [])->firstWhere('codec_type', 'video');
                if ($videoStream) {
                    $width = (int)($videoStream['width'] ?? 0);
                    $height = (int)($videoStream['height'] ?? 0);
                }
                $duration = (float)($metadata['format']['duration'] ?? 0);
            }
        }

        $media = StudentMedia::create([
            'student_id'        => $student->id,
            'type'              => $type,
            'path'              => $s3Path,
            'original_filename' => $originalName,
            'mime_type'         => $mimeType,
            'file_size'         => $fileSize,
            'width'             => $width,
            'height'            => $height,
            'duration'          => $duration,
            'media_metadata'    => $metadata,
            'caption'           => $caption,
            'uploaded_by_type'  => $uploadedByType,
            'uploaded_by_id'    => $uploadedById,
        ]);

        // Set as profile photo if first photo
        if ($type === 'photo' && !$student->profile_photo_id) {
            $student->update(['profile_photo_id' => $media->id]);
        }

        return $media;
    }

    private static function rmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::rmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
