<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Student;
use App\Models\StudentAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    // ── Phone helpers ──────────────────────────────────────────────────────────

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) return '+1' . $digits;
        if (strlen($digits) === 11 && $digits[0] === '1') return '+' . $digits;
        return '+' . $digits;
    }

    public static function displayPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 11 && $digits[0] === '1') $digits = substr($digits, 1);
        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        return $phone;
    }

    // ── Index ──────────────────────────────────────────────────────────────────

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

        $guestCount       = Booking::whereNull('client_id')->distinct('client_email')->count('client_email');
        $orphanedStudents = Student::whereNull('client_id')->where('is_active', true)->with('aliases')->get();

        return view('admin.clients.index', compact('clients', 'search', 'guestCount', 'orphanedStudents'));
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(Client $client)
    {
        $bookings = $client->bookings()->with(['service', 'timeSlot.rink', 'student'])
            ->orderByDesc('date')->orderByDesc('start_time')->get();
        $students = $client->students()->with('aliases')->orderBy('first_name')->get();

        return view('admin.clients.show', compact('client', 'bookings', 'students'));
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'nullable|string|max:100',
            'email'           => 'required|email|unique:clients,email',
            'phone'           => 'nullable|string|max:30',
            'notes'           => 'nullable|string',
            'link_student_id' => 'nullable|exists:students,id',
        ]);

        $phone = $this->normalizePhone($validated['phone'] ?? null);

        $client = Client::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'name'       => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'      => $validated['email'],
            'phone'      => $phone,
            'sms_phone'  => $phone,
            'notes'      => $validated['notes'] ?? null,
            'password'   => Hash::make(str()->random(16)),
        ]);

        if (!empty($validated['link_student_id'])) {
            Student::where('id', $validated['link_student_id'])->update(['client_id' => $client->id]);
        }

        return back()->with('success', "Client {$client->full_name} created successfully.");
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:clients,email,' . $client->id,
            'phone'      => 'nullable|string|max:30',
            'notes'      => 'nullable|string',
        ]);

        $phone = $this->normalizePhone($validated['phone'] ?? null);

        $client->update([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'name'       => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'      => $validated['email'],
            'phone'      => $phone,
            'sms_phone'  => $client->sms_consent ? $phone : $client->sms_phone,
            'notes'      => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Client {$client->full_name} updated.");
    }

    // ── Delete ─────────────────────────────────────────────────────────────────

    public function destroy(Client $client)
    {
        if ($client->bookings()->count() > 0) {
            return back()->withErrors(['delete' => "Cannot delete {$client->full_name} — they have existing bookings. Cancel bookings first."]);
        }

        $name = $client->full_name;
        $client->students()->update(['client_id' => null]);
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', "Client {$name} deleted.");
    }

    // ── Link orphaned student to client ───────────────────────────────────────

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

    // ── Add student to client ──────────────────────────────────────────────────

    public function addStudent(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'age'         => 'nullable|integer|min:3|max:80',
            'skill_level' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $student = Student::create([
            'client_id'   => $client->id,
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'] ?? null,
            'age'         => $validated['age'] ?? null,
            'skill_level' => $validated['skill_level'] ?? null,
            'notes'       => $validated['notes'] ?? null,
            'is_active'   => true,
        ]);

        return back()->with('success', "{$student->first_name} added as a student for {$client->full_name}.");
    }

    // ── Update student ─────────────────────────────────────────────────────────

    public function updateStudent(Request $request, Client $client, Student $student)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'nullable|string|max:100',
            'age'         => 'nullable|integer|min:3|max:80',
            'skill_level' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $student->update($validated);
        return back()->with('success', "{$student->first_name} updated.");
    }

    // ── Unlink student from client ─────────────────────────────────────────────

    public function unlinkStudent(Client $client, Student $student)
    {
        $student->update(['client_id' => null]);
        return back()->with('success', "{$student->first_name} unlinked from {$client->full_name}.");
    }
}
