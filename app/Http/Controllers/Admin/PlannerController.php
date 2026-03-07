<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\PlannerScan;
use App\Models\PlannerScanEntry;
use App\Models\Student;
use App\Models\StudentAlias;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PlannerController extends Controller
{
    const ANTHROPIC_MODEL = 'claude-sonnet-4-20250514';

    const SYSTEM_PROMPT = <<<'PROMPT'
You are analyzing a photo of a skating coach's paper planner. Extract all entries and return ONLY valid JSON with no markdown fences, no trailing commas, no explanation.

Context:
- Coach Kristine teaches skating in St. Louis
- LTS = Learn to Skate (group class, her employer schedule)
- LTP = Learn to Play (group class, her employer schedule)
- CC = Creve Coeur Ice Arena
- S&P = Stick & Puck session
- Kirkwood = Kirkwood Ice Arena (Mondays)
- Maryville = Maryville University Hockey Center (Fridays)
- Named entries like "12:00 Mick" or "1230 Ezra" are PRIVATE LESSONS with student names
- Arrows (→) mean traveling to rink or continued from previous day - note as type "note"
- "no CC P/S X" or similar = cancelled public session
- "no LTS X" = cancelled group class
- Personal events (birthday parties, concerts, family events) = type "personal_block"
- Confidence: 100=certain, 80=likely, 60=unsure, 40=guessing

Return this exact JSON structure:
{
  "month": "March",
  "year": 2026,
  "entries": [
    {
      "date": "2026-03-07",
      "time": "12:00",
      "student_name": "Mick",
      "type": "private_lesson",
      "rink": "creve-coeur",
      "notes": null,
      "confidence": 95
    }
  ]
}

Types: private_lesson, lts, ltp, cancelled_public, cancelled_class, personal_block, note
Rinks: creve-coeur, kirkwood, maryville, brentwood, webster-groves, unknown
Normalize times to "HH:MM" 24-hour format. Separate entry per student. Return ONLY the JSON object.
PROMPT;

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index()
    {
        $recentScans = PlannerScan::with('entries')->orderByDesc('created_at')->take(10)->get();
        $students    = Student::with('aliases')->where('is_active', true)->orderBy('first_name')->get();
        return view('admin.planner', compact('recentScans', 'students'));
    }

    // ── Analyze uploaded images ────────────────────────────────────────────────

    public function analyze(Request $request)
    {
        $request->validate([
            'images'   => 'required|array|min:1|max:2',
            'images.*' => 'required|image|max:10240',
        ]);

        $imagePaths = [];
        $imageData  = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('planner-scans', 'local');
            $imagePaths[] = $path;
            $imageData[]  = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $image->getMimeType(),
                    'data'       => base64_encode(file_get_contents($image->getRealPath())),
                ],
            ];
        }

        $content  = array_merge($imageData, [['type' => 'text', 'text' => 'Extract all planner entries as JSON.']]);
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => self::ANTHROPIC_MODEL,
            'max_tokens' => 2000,
            'system'     => self::SYSTEM_PROMPT,
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if (!$response->successful()) {
            Log::error('Claude API error', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->withErrors(['api' => 'Claude API call failed: ' . $response->status() . ' — ' . $response->json('error.message', '')]);
        }

        $text = $response->json('content.0.text', '');
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/', '', $text);
        $text = preg_replace('/,(\s*[}\]])/', '$1', trim($text));

        $extracted = json_decode($text, true);
        if (!$extracted || !isset($extracted['entries'])) {
            Log::error('Claude planner parse failed', ['raw' => $text]);
            return back()->withErrors(['parse' => 'Could not parse Claude response. Raw: ' . substr($text, 0, 300)]);
        }

        $scan     = PlannerScan::create([
            'month'             => $extracted['month'] ?? date('F'),
            'year'              => $extracted['year']  ?? date('Y'),
            'image_paths'       => $imagePaths,
            'entries_extracted' => count($extracted['entries']),
            'scanned_by'        => auth()->id(),
        ]);

        $students = Student::with('aliases')->where('is_active', true)->get();

        foreach ($extracted['entries'] as $entry) {
            $studentId   = null;
            $matchStatus = 'no_booking_expected';
            $confidence  = $entry['confidence'] ?? 100;

            if ($entry['type'] === 'private_lesson' && !empty($entry['student_name'])) {
                [$studentId, $matchStatus, $confidence] = $this->matchStudent($entry['student_name'], $students, $confidence);
                // If matched student, check for existing booking
                if ($studentId && $matchStatus === 'matched') {
                    $booking = $this->findMatchingBooking($studentId, $entry['date'], $entry['time'] ?? null);
                    if (!$booking) $matchStatus = 'no_booking_found';
                }
            } elseif (in_array($entry['type'], ['personal_block', 'note'])) {
                $matchStatus = 'no_booking_expected';
            }

            $bookingId = null;
            if ($studentId && $matchStatus === 'matched') {
                $booking   = $this->findMatchingBooking($studentId, $entry['date'], $entry['time'] ?? null);
                $bookingId = $booking?->id;
                if (!$booking) $matchStatus = 'no_booking_found';
            }

            PlannerScanEntry::create([
                'planner_scan_id' => $scan->id,
                'date'            => $entry['date'],
                'time'            => $entry['time'] ?? null,
                'raw_text'        => $entry['student_name'] ?? $entry['notes'] ?? null,
                'type'            => $entry['type'],
                'rink'            => $entry['rink'] ?? null,
                'extracted_name'  => $entry['student_name'] ?? null,
                'student_id'      => $studentId,
                'booking_id'      => $bookingId,
                'confidence'      => $confidence,
                'match_status'    => $matchStatus,
                'notes'           => $entry['notes'] ?? null,
            ]);
        }

        return redirect()->route('admin.planner.scan', $scan->id);
    }

    // ── Show scan results ──────────────────────────────────────────────────────

    public function show(PlannerScan $scan)
    {
        $scan->load(['entries.student.client', 'entries.booking.service']);
        $students = Student::with(['aliases', 'client'])->where('is_active', true)->orderBy('first_name')->get();
        return view('admin.planner-scan', compact('scan', 'students'));
    }

    // ── Update entry ───────────────────────────────────────────────────────────

    public function updateEntry(Request $request, PlannerScanEntry $entry)
    {
        $validated = $request->validate([
            'type'           => 'required|string',
            'time'           => 'nullable|string',
            'extracted_name' => 'nullable|string',
            'student_id'     => 'nullable|exists:students,id',
            'rink'           => 'nullable|string',
            'notes'          => 'nullable|string',
            'match_status'   => 'nullable|string',
        ]);
        $entry->update($validated);
        return back()->with('success', 'Entry updated.');
    }

    // ── Confirm entry ──────────────────────────────────────────────────────────

    public function confirmEntry(PlannerScanEntry $entry)
    {
        $entry->update(['confirmed_at' => now()]);
        $entry->scan->increment('entries_confirmed');
        return back()->with('success', 'Entry confirmed.');
    }

    // ── Ignore entry ───────────────────────────────────────────────────────────

    public function ignoreEntry(PlannerScanEntry $entry)
    {
        $entry->update(['match_status' => 'ignored']);
        return back()->with('success', 'Entry ignored.');
    }

    // ── Unignore entry ─────────────────────────────────────────────────────────

    public function unignoreEntry(PlannerScanEntry $entry)
    {
        // Restore to appropriate status based on type
        $status = 'no_booking_expected';
        if ($entry->type === 'private_lesson') {
            $status = $entry->student_id ? 'no_booking_found' : 'unmatched';
        }
        $entry->update(['match_status' => $status]);
        return back()->with('success', 'Entry restored.');
    }

    // ── Create booking from planner entry ─────────────────────────────────────

    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'entry_id'     => 'required|exists:planner_scan_entries,id',
            'student_id'   => 'required|exists:students,id',
            'service_id'   => 'required|exists:services,id',
            'date'         => 'required|date',
            'time'         => 'required',
            'rink'         => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,venmo',
            'status'       => 'required|in:confirmed,pending',
        ]);

        $entry   = PlannerScanEntry::findOrFail($validated['entry_id']);
        $student = Student::findOrFail($validated['student_id']);
        $client  = $student->client;

        $start = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $service = \App\Models\Service::findOrFail($validated['service_id']);
        $end   = $start->copy()->addMinutes($service->duration_minutes);

        // Find matching rink
        $rinkId = null;
        if ($validated['rink'] && $validated['rink'] !== 'unknown') {
            $rink = \App\Models\Rink::where('slug', $validated['rink'])->first();
            $rinkId = $rink?->id;
        }

        // Find matching time slot if available
        $slot = null;
        if ($rinkId) {
            $slot = TimeSlot::where('rink_id', $rinkId)
                ->where('date', $validated['date'])
                ->where('start_time', $start->toTimeString())
                ->whereNull('booking_id')
                ->first();
        }

        $booking = Booking::create([
            'student_id'        => $student->id,
            'client_id'         => $client?->id,
            'client_name'       => $client?->full_name ?? '',
            'client_email'      => $client?->email ?? '',
            'client_phone'      => $client?->phone ?? '',
            'service_id'        => $validated['service_id'],
            'time_slot_id'      => $slot?->id,
            'date'              => $validated['date'],
            'start_time'        => $start->toTimeString(),
            'end_time'          => $end->toTimeString(),
            'status'            => $validated['status'],
            'payment_type'      => $validated['payment_type'],
            'price_paid'        => $validated['price'],
            'notes'             => 'Created from planner scan #' . $entry->planner_scan_id,
            'confirmation_code' => strtoupper(substr(md5(uniqid()), 0, 8)),
        ]);

        // Mark slot as booked
        if ($slot) {
            $slot->update(['booking_id' => $booking->id, 'is_available' => false]);
        }

        $entry->update([
            'booking_id'   => $booking->id,
            'match_status' => 'matched',
            'confirmed_at' => now(),
        ]);

        $entry->scan->increment('entries_confirmed');

        return back()->with('success', "Booking created for {$student->first_name} on {$start->format('M j g:i A')}.");
    }

    // ── Create student ─────────────────────────────────────────────────────────

    public function createStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'client_id'  => 'nullable|exists:clients,id',
            'age'        => 'nullable|integer',
            'alias'      => 'nullable|string',
            'entry_id'   => 'nullable|exists:planner_scan_entries,id',
        ]);

        $student = Student::create([
            'client_id'  => $validated['client_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'] ?? null,
            'age'        => $validated['age'] ?? null,
            'is_active'  => true,
        ]);

        if (!empty($validated['alias'])) {
            StudentAlias::create(['student_id' => $student->id, 'alias' => $validated['alias']]);
        }

        if (!empty($validated['entry_id'])) {
            $entry = PlannerScanEntry::find($validated['entry_id']);
            if ($entry) {
                $entry->update([
                    'student_id'   => $student->id,
                    'match_status' => 'no_booking_found',
                ]);
            }
        }

        return back()->with('success', "Student {$student->first_name} created.");
    }

    // ── Add alias ──────────────────────────────────────────────────────────────

    public function addAlias(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'alias'      => 'required|string',
            'entry_id'   => 'nullable|exists:planner_scan_entries,id',
        ]);

        StudentAlias::firstOrCreate([
            'student_id' => $validated['student_id'],
            'alias'      => $validated['alias'],
        ]);

        if (!empty($validated['entry_id'])) {
            $entry = PlannerScanEntry::find($validated['entry_id']);
            if ($entry) {
                $entry->update([
                    'student_id'   => $validated['student_id'],
                    'match_status' => 'no_booking_found',
                ]);
            }
        }

        $student = Student::find($validated['student_id']);
        return back()->with('success', "Alias '{$validated['alias']}' linked to {$student->first_name}.");
    }

    // ── Finalize scan ──────────────────────────────────────────────────────────

    public function finalize(PlannerScan $scan)
    {
        $scan->update([
            'is_finalized'      => true,
            'entries_confirmed' => $scan->entries()->whereNotNull('confirmed_at')->count(),
        ]);
        return redirect()->route('admin.planner.scan', $scan->id)->with('success', 'Scan marked as reviewed.');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function matchStudent(string $name, $students, int $baseConfidence): array
    {
        $bestScore   = 0;
        $bestStudent = null;

        foreach ($students as $student) {
            $score = $student->similarityScore($name);
            if ($score > $bestScore) {
                $bestScore   = $score;
                $bestStudent = $student;
            }
        }

        if ($bestScore >= 90) return [$bestStudent->id, 'matched', min($baseConfidence, 95)];
        if ($bestScore >= 70) return [$bestStudent->id, 'matched', min($baseConfidence, 75)];
        return [null, 'unmatched', min($baseConfidence, 60)];
    }

    private function findMatchingBooking(int $studentId, string $date, ?string $time): ?Booking
    {
        $query = Booking::where('student_id', $studentId)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'confirmed']);

        if ($time) {
            $timeParsed = Carbon::parse($date . ' ' . $time);
            $query->where('start_time', '>=', $timeParsed->copy()->subMinutes(15)->toTimeString())
                  ->where('start_time', '<=', $timeParsed->copy()->addMinutes(15)->toTimeString());
        }

        return $query->first();
    }
}
