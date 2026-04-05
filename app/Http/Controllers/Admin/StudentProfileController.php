<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentMedia;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function show(Student $student)
    {
        $student->load(['client', 'aliases', 'bookings.service', 'media.versions', 'profilePhoto']);
        $media = $student->media()->paginate(24);
        $photoCount = $student->photos()->count();
        $videoCount = $student->videos()->count();
        $lessonCount = $student->bookings()->count();
        $allStudents = Student::where('id', '!=', $student->id)->orderBy('first_name')->get();

        return view('admin.students.profile', compact('student', 'media', 'photoCount', 'videoCount', 'lessonCount', 'allStudents'));
    }

    public function upload(Request $request, Student $student)
    {
        $request->validate([
            'files'   => 'required|array|max:20',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,heic,mp4,mov,avi,webm,zip|max:512000',
            'caption' => 'nullable|string|max:500',
        ]);

        $uploaded = MediaUploadService::processUpload(
            $request->file('files'), $student, $request->caption, 'admin', auth()->id()
        );

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

    public function reassignMedia(Request $request, StudentMedia $media)
    {
        $request->validate(['student_id' => 'required|exists:students,id']);
        $oldStudent = $media->student;
        $newStudent = \App\Models\Student::findOrFail($request->student_id);

        // Clear profile photo if this was it
        if ($oldStudent->profile_photo_id === $media->id) {
            $oldStudent->update(['profile_photo_id' => null]);
        }

        $media->update(['student_id' => $newStudent->id]);

        // Set as profile photo on new student if they don't have one
        if ($media->type === 'photo' && !$newStudent->profile_photo_id) {
            $newStudent->update(['profile_photo_id' => $media->id]);
        }

        return back()->with('success', "Moved \"{$media->original_filename}\" to {$newStudent->full_name}.");
    }

    public function revertMedia(Request $request, StudentMedia $media)
    {
        $versionId = $request->input('version_id');

        if ($versionId) {
            // Revert to a specific version
            $version = $media->versions()->findOrFail($versionId);
            $media->update([
                'path'      => $version->path,
                'file_size' => $version->file_size,
                'width'     => $version->width,
                'height'    => $version->height,
                'duration'  => $version->duration,
            ]);
            // If reverting to version 1 (original), clear original_path
            if ($version->version === 1) {
                $media->update(['original_path' => null]);
            }
            return back()->with('success', "Reverted to version {$version->version}.");
        }

        // Revert to original (legacy behavior)
        if (!$media->original_path) {
            return back()->with('error', 'No original to revert to.');
        }

        $media->update([
            'path'          => $media->original_path,
            'original_path' => null,
        ]);

        return back()->with('success', 'Reverted to original.');
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

        // Remove from S3 (both edited and original if exists)
        Storage::disk('s3')->delete($media->path);
        if ($media->original_path) {
            Storage::disk('s3')->delete($media->original_path);
        }
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
