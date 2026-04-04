<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessZipUpload;
use App\Jobs\TrimVideo;
use App\Models\Student;
use App\Models\StudentMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadApiController extends Controller
{
    /**
     * Generate a presigned S3 upload URL for direct browser → S3 upload.
     * Bypasses Cloudflare and Apache upload limits entirely.
     */
    public function presignedUrl(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'filename'   => 'required|string|max:255',
            'mime_type'  => 'required|string|max:100',
            'file_size'  => 'required|integer|max:524288000', // 500MB
        ]);

        $ext = strtolower(pathinfo($request->filename, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'gif', 'mp4', 'mov', 'avi', 'webm', 'zip'];
        if (!in_array($ext, $allowedExts)) {
            return response()->json(['error' => 'File type not allowed'], 422);
        }

        $isVideo = in_array($ext, ['mp4', 'mov', 'avi', 'webm']);
        $isZip = $ext === 'zip';
        $type = $isVideo ? 'video' : ($isZip ? 'zip' : 'photo');
        $prefix = config('services.media_path_prefix');
        $s3Path = ($prefix ? $prefix . '/' : '') . 'students/' . $request->student_id . '/' . ($isZip ? 'uploads' : $type . 's') . '/' . Str::uuid() . '.' . $ext;

        // Generate presigned PUT URL (15 min expiry)
        $client = Storage::disk('s3')->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $s3Path,
            'ContentType' => $request->mime_type,
        ]);
        $presigned = $client->createPresignedRequest($command, '+15 minutes');
        $uploadUrl = (string) $presigned->getUri();

        return response()->json([
            'upload_url' => $uploadUrl,
            's3_path'    => $s3Path,
            'type'       => $type,
        ]);
    }

    /**
     * After browser uploads directly to S3, register the file in the DB.
     */
    public function register(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            's3_path'    => 'required|string',
            'type'       => 'required|in:photo,video,zip',
            'filename'   => 'required|string|max:255',
            'mime_type'  => 'required|string|max:100',
            'file_size'  => 'required|integer',
            'width'            => 'nullable|integer',
            'height'           => 'nullable|integer',
            'duration'         => 'nullable|numeric',
            'caption'          => 'nullable|string|max:500',
            'replace_media_id' => 'nullable|integer|exists:student_media,id',
        ]);

        $student = Student::findOrFail($request->student_id);

        // Replace existing media (from image editor) — keep original
        if ($request->replace_media_id) {
            $existing = StudentMedia::findOrFail($request->replace_media_id);
            // Preserve the original (first edit saves it, subsequent edits keep the first original)
            if (!$existing->original_path) {
                $existing->original_path = $existing->path;
            } else {
                // Delete the previous edited version (not the original)
                Storage::disk('s3')->delete($existing->path);
            }
            $existing->update([
                'original_path' => $existing->original_path,
                'path'          => $request->s3_path,
                'mime_type'     => $request->mime_type,
                'file_size'     => $request->file_size,
                'width'         => $request->width,
                'height'        => $request->height,
            ]);
            return response()->json(['id' => $existing->id, 'url' => $existing->url]);
        }

        if ($request->type === 'zip') {
            ProcessZipUpload::dispatch(
                $student->id,
                $request->s3_path,
                $request->caption,
                'admin',
                auth()->id()
            );
            return response()->json(['message' => "Zip uploaded! Extracting {$request->filename} in the background — files will appear shortly."]);
        }

        $media = StudentMedia::create([
            'student_id'        => $student->id,
            'type'              => $request->type,
            'path'              => $request->s3_path,
            'original_filename' => $request->filename,
            'mime_type'         => $request->mime_type,
            'file_size'         => $request->file_size,
            'width'             => $request->width,
            'height'            => $request->height,
            'duration'          => $request->duration,
            'caption'           => $request->caption,
            'uploaded_by_type'  => 'admin',
            'uploaded_by_id'    => auth()->id(),
        ]);

        if ($request->type === 'photo' && !$student->profile_photo_id) {
            $student->update(['profile_photo_id' => $media->id]);
        }

        return response()->json(['id' => $media->id, 'url' => $media->url]);
    }

    /**
     * Trim a video — runs FFmpeg server-side.
     */
    public function trimVideo(Request $request)
    {
        $request->validate([
            'media_id'   => 'required|exists:student_media,id',
            'start_time' => 'required|numeric|min:0',
            'end_time'   => 'required|numeric|gt:start_time',
            'brightness' => 'nullable|integer|min:50|max:150',
            'contrast'   => 'nullable|integer|min:50|max:150',
            'saturation' => 'nullable|integer|min:0|max:200',
        ]);

        $media = StudentMedia::findOrFail($request->media_id);
        if ($media->type !== 'video') {
            return response()->json(['error' => 'Not a video'], 422);
        }

        TrimVideo::dispatchSync(
            $media->id,
            (float) $request->start_time,
            (float) $request->end_time,
            (int) ($request->brightness ?? 100),
            (int) ($request->contrast ?? 100),
            (int) ($request->saturation ?? 100),
        );

        $media->refresh();

        return response()->json([
            'success'  => true,
            'url'      => $media->url,
            'duration' => $media->duration,
            'size'     => $media->file_size,
        ]);
    }
}
