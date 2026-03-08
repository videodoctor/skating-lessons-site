<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAlias;
use App\Models\Client;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');

        $students = Student::with(['client', 'aliases'])
            ->withCount('bookings')
            ->when($search, fn($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"))
            ->orderBy('first_name')
            ->paginate(30);

        $clients = Client::orderBy('name')->get(['id', 'first_name', 'last_name', 'name']);

        return view('admin.students.index', compact('students', 'search', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'client_id'   => 'nullable|exists:clients,id',
            'age'         => 'nullable|integer|min:3|max:80',
            'skill_level' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $student = Student::create(array_merge($validated, ['is_active' => true]));
        return back()->with('success', "{$student->first_name} created successfully.");
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'client_id'   => 'nullable|exists:clients,id',
            'age'         => 'nullable|integer|min:3|max:80',
            'skill_level' => 'nullable|string',
            'notes'       => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $student->update($validated);
        return back()->with('success', "{$student->first_name} updated.");
    }

    public function destroy(Student $student)
    {
        if ($student->bookings()->count() > 0) {
            return back()->withErrors(['delete' => "Cannot delete {$student->full_name} — they have existing bookings."]);
        }

        $name = $student->full_name;
        $student->aliases()->delete();
        $student->delete();

        return back()->with('success', "Student {$name} deleted.");
    }

    public function addAlias(Request $request, Student $student)
    {
        $validated = $request->validate(['alias' => 'required|string|max:100']);
        StudentAlias::firstOrCreate(['student_id' => $student->id, 'alias' => $validated['alias']]);
        return back()->with('success', "Alias '{$validated['alias']}' added.");
    }

    public function removeAlias(Student $student, StudentAlias $alias)
    {
        $alias->delete();
        return back()->with('success', 'Alias removed.');
    }
}
