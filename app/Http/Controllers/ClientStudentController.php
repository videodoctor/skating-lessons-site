<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientStudentController extends Controller
{
    public function show(Student $student)
    {
        $client = auth('client')->user();

        // Client can only view their own students
        if ($student->client_id !== $client->id) {
            abort(403);
        }

        $student->load(['media', 'profilePhoto', 'bookings.service']);
        $media = $student->media()->paginate(24);

        return view('client.student-profile', compact('student', 'media'));
    }

    public function upload(Request $request, Student $student)
    {
        $client = auth('client')->user();
        if ($student->client_id !== $client->id) {
            abort(403);
        }

        $request->validate([
            'files'   => 'required|array|max:10',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,heic,mp4,mov|max:102400',
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
                'uploaded_by_type'  => 'client',
                'uploaded_by_id'    => $client->id,
            ]);

            if ($type === 'photo' && !$student->profile_photo_id) {
                $student->update(['profile_photo_id' => $media->id]);
            }

            $uploaded++;
        }

        return back()->with('success', "{$uploaded} file(s) uploaded!");
    }
}
