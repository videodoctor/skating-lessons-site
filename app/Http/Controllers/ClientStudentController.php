<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

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
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,heic,mp4,mov,zip|max:512000',
            'caption' => 'nullable|string|max:500',
        ]);

        $uploaded = MediaUploadService::processUpload(
            $request->file('files'), $student, $request->caption, 'client', $client->id
        );

        return back()->with('success', "{$uploaded} file(s) uploaded!");
    }
}
