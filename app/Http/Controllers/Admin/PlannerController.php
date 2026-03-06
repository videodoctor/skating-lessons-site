<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\PlannerScan;
use App\Models\PlannerScanEntry;
use App\Models\Student;
use App\Models\StudentAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PlannerController extends Controller
{
    const ANTHROPIC_MODEL  = 'claude-sonnet-4-20250514';
    const WAIVER_VERSION   = '1.0';

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

    // ── Index: show upload form + recent scans ─────────────────────────────────

    public function index()
    {
        $recentScans = PlannerScan::with('entries')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $students = Student::with('aliases')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('admin.planner', compact('recentScans', 'students'));
    }

    // ── Upload & analyze ───────────────────────────────────────────────────────

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
            Storage::disk('local')->setVisibility($path, 'private');
            $imagePaths[] = $path;
            $imageData[]  = [
                'type'       => 'image',
                'source'     => [
                    'type'       => 'base64',
                    'media_type' => $image->getMimeType(),
                    'data'       => base64_encode(file_get_contents($image->getRealPath())),
                ],
            ];
        }

        // Call Claude Vision API
        $content   = array_merge($imageData, [['type' => 'text', 'text' => 'Extract all planner entries as JSON.']]);
        $response  = Http::withHeaders([
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
            return back()->withErrors(['api' => 'Claude API call failed: ' . $response->status()]);
        }

        $text = $response->json('content.0.text', '');

        // Strip markdown fences if present
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);
        $text = trim($text);

        // Remove trailing commas
        $text = preg_replace('/,(\s*[}\]])/', '$1', $text);

        $extracted = json_decode($text, true);
        if (!$extracted || !isset($extracted['entries'])) {
            Log::error('Claude planner parse failed', ['raw' => $text]);
            return back()->withErrors(['parse' => 'Could not parse Claude response. Raw: ' . substr($text, 0, 500)]);
        }

        // Create scan record
        $scan = PlannerScan::create([
            'month'              => $extracted['month'] ?? date('F'),
            'year'               => $extracted['year']  ?? date('Y'),
            'image_paths'        => $imagePaths,
            'entries_extracted'  => count($extracted['entries']),
            'scanned_by'         => auth()->id(),
        ]);

        // Process each entry — attempt student matching
        $students = Student::with('aliases')->where('is_active', true)->get();

        foreach ($extracted['entries'] as $entry) {
            $studentId   = null;
            $matchStatus = 'no_booking_expected';
            $confidence  = $entry['confidence'] ?? 100;

            if ($entry['type'] === 'private_lesson' && !empty($entry['student_name'])) {
                [$studentId, $matchStatus, $confidence] = $this->matchStudent($entry['student_name'], $students, $confidence);
            } elseif (in_array($entry['type'], ['personal_block', 'note'])) {
                $matchStatus = 'personal';
            }

            // Try to find matching booking
            $bookingId = null;
            if ($studentId && $entry['type'] === 'private_lesson' && !empty($entry['date'])) {
                $booking = $this->findMatchingBooking($studentId, $entry['date'], $entry['time'] ?? null);
                if ($booking) {
                    $bookingId   = $booking->id;
                    $matchStatus = 'matched';
                } elseif ($matchStatus !== 'unmatched') {
                    $matchStatus = 'no_booking_found';
                }
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

    // ── View a scan's results ──────────────────────────────────────────────────

    public function show(PlannerScan $scan)
    {
        $scan->load(['entries.student.client', 'entries.booking.service']);

        $students = Student::with(['aliases', 'client'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $entriesByDate = $scan->entries
            ->sortBy('date')
            ->groupBy(fn($e) => Carbon::parse($e->date)->toDateString());

        $needsReview   = $scan->entries->filter(fn($e) => $e->needsReview());
        $matched       = $scan->entries->where('match_status', 'matched');
        $unmatched     = $scan->entries->where('match_status', 'unmatched');
        $noBooking     = $scan->entries->where('match_status', 'no_booking_found');

        return view('admin.planner-scan', compact(
            'scan', 'entriesByDate', 'students',
            'needsReview', 'matched', 'unmatched', 'noBooking'
        ));
    }

    // ── Update a single entry ──────────────────────────────────────────────────

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

        if ($request->confirm) {
            $entry->update(['confirmed_at' => now()]);
        }

        return back()->with('success', 'Entry updated.');
    }

    // ── Confirm an entry ───────────────────────────────────────────────────────

    public function confirmEntry(PlannerScanEntry $entry)
    {
        $entry->update(['confirmed_at' => now()]);

        // Update scan confirmed count
        $entry->scan->increment('entries_confirmed');

        return back()->with('success', 'Entry confirmed.');
    }

    // ── Create student + alias from unmatched name ─────────────────────────────

    public function createStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string',
            'last_name'   => 'nullable|string',
            'client_id'   => 'nullable|exists:clients,id',
            'age'         => 'nullable|integer',
            'alias'       => 'nullable|string',
            'entry_id'    => 'nullable|exists:planner_scan_entries,id',
        ]);

        $student = Student::create([
            'client_id'   => $validated['client_id'] ?? null,
            'first_name'  => $validated['first_name'],
            'last_name'   => $validated['last_name'] ?? null,
            'age'         => $validated['age'] ?? null,
            'is_active'   => true,
        ]);

        if (!empty($validated['alias'])) {
            StudentAlias::create(['student_id' => $student->id, 'alias' => $validated['alias']]);
        }

        // Link to entry if provided
        if (!empty($validated['entry_id'])) {
            $entry = PlannerScanEntry::find($validated['entry_id']);
            if ($entry) {
                $entry->update([
                    'student_id'   => $student->id,
                    'match_status' => 'matched',
                    'confirmed_at' => now(),
                ]);
                $entry->scan->increment('entries_confirmed');
            }
        }

        return back()->with('success', "Student {$student->first_name} created and linked.");
    }

    // ── Add alias to existing student ─────────────────────────────────────────

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
                    'match_status' => 'matched',
                    'confirmed_at' => now(),
                ]);
                $entry->scan->increment('entries_confirmed');
            }
        }

        $student = Student::find($validated['student_id']);
        return back()->with('success', "Alias '{$validated['alias']}' added to {$student->first_name}.");
    }

    // ── Finalize scan ─────────────────────────────────────────────────────────────

    public function finalize(PlannerScan $scan)
    {
        $scan->update([
            'is_finalized'       => true,
            'entries_confirmed'  => $scan->entries()->whereNotNull('confirmed_at')->count(),
        ]);
        return redirect()->route('admin.planner.scan', $scan->id)
            ->with('success', 'Scan finalized successfully.');
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

        if ($bestScore >= 90) {
            return [$bestStudent->id, 'matched', min($baseConfidence, 95)];
        } elseif ($bestScore >= 70) {
            return [$bestStudent->id, 'matched', min($baseConfidence, 75)];
        } else {
            return [null, 'unmatched', min($baseConfidence, 60)];
        }
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
