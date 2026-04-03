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
        $student->load(['client', 'aliases', 'bookings.service', 'media', 'profilePhoto']);
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
