<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentProfileController extends Controller
{
    public function show(Student $student)
    {
        $student->load(['client', 'aliases', 'bookings.service', 'media', 'profilePhoto']);
        $media = $student->media()->paginate(24);
        $photoCount = $student->photos()->count();
        $videoCount = $student->videos()->count();
        $lessonCount = $student->bookings()->count();

        return view('admin.students.profile', compact('student', 'media', 'photoCount', 'videoCount', 'lessonCount'));
    }

    public function upload(Request $request, Student $student)
    {
        $request->validate([
            'files'   => 'required|array|max:20',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,heic,mp4,mov,avi,webm|max:102400', // 100MB max per file
            'caption' => 'nullable|string|max:500',
        ]);

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            $isVideo = str_starts_with($file->getMimeType(), 'video/');
            $type = $isVideo ? 'video' : 'photo';
            $ext = $file->getClientOriginalExtension() ?: ($isVideo ? 'mp4' : 'jpg');
            $prefix = config('services.media_path_prefix');
            $filename = ($prefix ? $prefix . '/' : '') . 'students/' . $student->id . '/' . $type . 's/' . Str::uuid() . '.' . $ext;

            Storage::disk('s3')->put($filename, file_get_contents($file));

            // Extract dimensions and metadata
            $width = null;
            $height = null;
            $duration = null;
            $metadata = null;
            if (!$isVideo) {
                $dims = @getimagesize($file->getPathname());
                if ($dims) { $width = $dims[0]; $height = $dims[1]; }
                $exif = @exif_read_data($file->getPathname());
                if ($exif) { $metadata = $exif; }
            } else {
                $path = escapeshellarg($file->getPathname());
                $probeJson = @shell_exec("ffprobe -v error -show_format -show_streams -of json $path 2>/dev/null");
                if ($probeJson) {
                    $metadata = json_decode($probeJson, true);
                    // Extract key fields from the full probe
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
                'path'              => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'width'             => $width,
                'height'            => $height,
                'duration'          => $duration,
                'media_metadata'    => $metadata,
                'caption'           => $request->caption,
                'uploaded_by_type'  => 'admin',
                'uploaded_by_id'    => auth()->id(),
            ]);

            // Set as profile photo if first photo
            if ($type === 'photo' && !$student->profile_photo_id) {
                $student->update(['profile_photo_id' => $media->id]);
            }

            $uploaded++;
        }

        return back()->with('success', "{$uploaded} file(s) uploaded for {$student->first_name}.");
    }

    public function setProfilePhoto(Student $student, StudentMedia $media)
    {
        if ($media->student_id !== $student->id || $media->type !== 'photo') {
            return back()->with('error', 'Invalid photo.');
        }

        $student->update(['profile_photo_id' => $media->id]);
        return back()->with('success', 'Profile photo updated.');
    }

    public function updateCaption(Request $request, StudentMedia $media)
    {
        $request->validate(['caption' => 'nullable|string|max:500']);
        $media->update(['caption' => $request->caption]);
        return back()->with('success', 'Caption updated.');
    }

    public function destroyMedia(StudentMedia $media)
    {
        $student = $media->student;

        // Remove from S3
        Storage::disk('s3')->delete($media->path);
        if ($media->thumbnail_path) {
            Storage::disk('s3')->delete($media->thumbnail_path);
        }

        // Clear profile photo reference if needed
        if ($student->profile_photo_id === $media->id) {
            $student->update(['profile_photo_id' => null]);
        }

        $media->delete();
        return back()->with('success', 'File deleted.');
    }
}
