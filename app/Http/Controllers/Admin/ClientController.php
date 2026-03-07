<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');

        $clients = Client::withCount('bookings')
            ->withCount('students')
            ->withSum('bookings', 'price_paid')
            ->with('students')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(30);

        $guestCount      = Booking::whereNull('client_id')->distinct('client_email')->count('client_email');
        $orphanedStudents = Student::whereNull('client_id')->where('is_active', true)->get();

        return view('admin.clients.index', compact('clients', 'search', 'guestCount', 'orphanedStudents'));
    }

    public function show(Client $client)
    {
        $bookings = $client->bookings()->with(['service', 'timeSlot.rink'])
            ->orderByDesc('created_at')->get();
        $students = $client->students()->orderBy('first_name')->get();

        return view('admin.clients.show', compact('client', 'bookings', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:clients,email',
            'phone'      => 'nullable|string|max:30',
            'notes'      => 'nullable|string',
        ]);

        $client = Client::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'name'       => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'notes'      => $validated['notes'] ?? null,
            'password'   => Hash::make(str()->random(16)), // random password, they can reset
        ]);

        return back()->with('success', "Client {$client->full_name} created successfully.");
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:clients,email,' . $client->id,
            'phone'      => 'nullable|string|max:30',
            'notes'      => 'nullable|string',
        ]);

        $client->update([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'name'       => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'notes'      => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Client {$client->full_name} updated.");
    }

    public function linkStudent(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'client_id'  => 'required|exists:clients,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $student->update(['client_id' => $validated['client_id']]);

        $client = Client::find($validated['client_id']);
        return back()->with('success', "{$student->full_name} linked to {$client->full_name}.");
    }
}
