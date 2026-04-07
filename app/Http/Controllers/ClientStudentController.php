<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class ClientStudentController extends Controller
{
    public function store(Request $request)
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'age'         => 'nullable|integer|min:2|max:99',
            'skill_level' => 'nullable|in:beginner,intermediate,advanced',
        ]);

        Student::create([
            'client_id'   => $client->id,
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'] ?? null,
            'age'         => $validated['age'] ?? null,
            'skill_level' => $validated['skill_level'] ?? null,
            'is_active'   => true,
        ]);

        return back()->with('success', "{$validated['first_name']} added!");
    }

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
