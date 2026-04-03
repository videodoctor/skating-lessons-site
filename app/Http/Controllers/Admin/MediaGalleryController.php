<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Student;
use App\Models\StudentMedia;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class MediaGalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentMedia::with('student.client')->latest();

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('client_id')) {
            $query->whereHas('student', fn($q) => $q->where('client_id', $request->client_id));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('caption', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $media = $query->paginate(36)->withQueryString();

        $students = Student::orderBy('first_name')->get();
        $clients = Client::has('students')->orderBy('first_name')->get();

        $stats = [
            'total'  => StudentMedia::count(),
            'photos' => StudentMedia::where('type', 'photo')->count(),
            'videos' => StudentMedia::where('type', 'video')->count(),
            'size'   => StudentMedia::sum('file_size'),
        ];

        return view('admin.media-gallery', compact('media', 'students', 'clients', 'stats'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'files'      => 'required|array|max:20',
            'files.*'    => 'required|file|mimes:jpg,jpeg,png,webp,heic,mp4,mov,avi,webm,zip|max:512000',
            'caption'    => 'nullable|string|max:500',
        ]);

        $student = Student::findOrFail($request->student_id);
        $uploaded = MediaUploadService::processUpload(
            $request->file('files'), $student, $request->caption, 'admin', auth()->id()
        );

        return back()->with('success', "{$uploaded} file(s) uploaded for {$student->first_name}.");
    }
}
