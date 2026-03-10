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

PLANNER LAYOUT INSTRUCTIONS:
- The planner is a two-page weekly spread photographed as separate images
- LEFT PAGE (binding on the RIGHT side of the image): has 4 columns with printed headers: "Notes", "Sunday", "Monday", "Tuesday" — in that order left to right
- RIGHT PAGE (binding on the LEFT side of the image): has 4 columns with printed headers: "Wednesday", "Thursday", "Friday", "Saturday" — in that order left to right
- The "Notes" column on the left page is NOT a day — ignore it for lesson entries or mark as type "note"
- Each day column has the DATE NUMBER printed in the TOP LEFT corner of that column's cell
- ALL handwritten entries below a date number belong to THAT date — not the adjacent column
- Do NOT assign entries to the previous or next day based on spatial proximity — always use the printed column header and the date number in the top left of the cell
- Entries near the right edge of a left-page column belong to that column, NOT to the next day's column on the same page
- When in doubt about which column an entry belongs to, identify the nearest column header above it

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
      "confidence": 95,
      "image_index": 0,
      "bbox": {"x": 45.2, "y": 23.1, "w": 18.5, "h": 4.2}
    }
  ]
}

Types: private_lesson, lts, ltp, cancelled_public, cancelled_class, personal_block, note
Rinks: creve-coeur, kirkwood, maryville, brentwood, webster-groves, unknown
Normalize times to "HH:MM" 24-hour format. Separate entry per student. Return ONLY the JSON object.

BOUNDING BOX INSTRUCTIONS:
- image_index: 0 for first image, 1 for second image (if two images uploaded)
- bbox: the bounding box of the handwritten entry in the image, as percentages of image dimensions (0-100)
  - x: left edge percentage
  - y: top edge percentage  
  - w: width percentage
  - h: height percentage
- Be generous with bbox — include some padding around the text so context is visible
- For entries spanning multiple lines, include all lines in the bbox
- If you cannot determine bbox with reasonable confidence, return null for bbox
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
            // Store permanently in public disk so images persist for rescan
            $path = $image->store('planner-scans', 'public');
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

        $refs     = $this->loadHandwritingRefs();
        $content  = array_merge($refs, $imageData, [['type' => 'text', 'text' => 'Extract all planner entries as JSON.']]);
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => self::ANTHROPIC_MODEL,
            'max_tokens' => 4000,
            'system'     => $this->buildSystemPrompt(null, null, $request->input('page_side', 'both')),
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

        $scan = PlannerScan::create([
            'month'             => $extracted['month'] ?? date('F'),
            'year'              => $extracted['year']  ?? date('Y'),
            'image_paths'       => $imagePaths,
            'entries_extracted' => count($extracted['entries']),
            'scanned_by'        => auth()->id(),
            'page_side'         => $request->input('page_side', 'both'),
        ]);

        $students = Student::with('aliases')->where('is_active', true)->get();

        $this->processEntries($scan, $extracted['entries'], $students);

        return redirect()->route('admin.planner.scan', $scan->id);
    }

    // ── Show scan results ──────────────────────────────────────────────────────

    public function show(PlannerScan $scan)
    {
        $scan->load(['entries.student.client', 'entries.booking.service']);
        $students = Student::with(['aliases', 'client'])->where('is_active', true)->orderBy('first_name')->get();

        // Find bookings in this scan's month that are NOT referenced in any entry
        // These are potential cancellations or lessons not written in the planner
        $startDate = Carbon::createFromFormat('F Y', "{$scan->month} {$scan->year}")->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Get all booking IDs already linked to entries
        $linkedBookingIds = $scan->entries->pluck('booking_id')->filter()->values()->toArray();

        // Find confirmed/pending bookings in this month not in the planner
        $missingBookings = Booking::with(['student', 'service'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotIn('id', $linkedBookingIds)
            ->get();

        return view('admin.planner-scan', compact('scan', 'students', 'missingBookings'));
    }

    // ── Rescan existing scan with updated rink context ─────────────────────────

    public function rescan(PlannerScan $scan)
    {
        $imagePaths = $scan->image_paths ?? [];
        $imageData  = [];

        foreach ($imagePaths as $path) {
            if (!Storage::disk('public')->exists($path)) {
                return back()->withErrors(['rescan' => 'Original images are no longer available. Please upload a new scan instead.']);
            }
            $fullPath    = Storage::disk('public')->path($path);
            $mimeType    = mime_content_type($fullPath);
            $imageData[] = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $mimeType,
                    'data'       => base64_encode(file_get_contents($fullPath)),
                ],
            ];
        }

        $refs     = $this->loadHandwritingRefs();
        $content  = array_merge($refs, $imageData, [['type' => 'text', 'text' => 'Extract all planner entries as JSON.']]);
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => self::ANTHROPIC_MODEL,
            'max_tokens' => 4000,
            'system'     => $this->buildSystemPrompt($scan->month, (string)$scan->year, $scan->page_side ?? 'both'),
            'messages'   => [['role' => 'user', 'content' => $content]],
        ]);

        if (!$response->successful()) {
            Log::error('Claude rescan error', ['status' => $response->status(), 'body' => $response->body()]);
            return back()->withErrors(['rescan' => 'Claude API call failed: ' . $response->status()]);
        }

        $text = $response->json('content.0.text', '');
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/', '', $text);
        $text = preg_replace('/,(\s*[}\]])/', '$1', trim($text));

        $extracted = json_decode($text, true);
        if (!$extracted || !isset($extracted['entries'])) {
            return back()->withErrors(['rescan' => 'Could not parse Claude response.']);
        }

        // Wipe old entries and re-process
        $scan->entries()->delete();
        $students = Student::with('aliases')->where('is_active', true)->get();
        $this->processEntries($scan, $extracted['entries'], $students);

        $scan->update([
            'entries_extracted' => count($extracted['entries']),
            'entries_confirmed' => 0,
            'is_finalized'      => false,
        ]);

        return redirect()->route('admin.planner.scan', $scan->id)
            ->with('success', '🔄 Rescan complete — ' . count($extracted['entries']) . ' entries extracted with updated rink session context.');
    }

    // ── Delete a scan ──────────────────────────────────────────────────────────

    public function destroy(PlannerScan $scan)
    {
        // Delete stored images
        foreach ($scan->image_paths ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        // Unlink any bookings created from this scan's entries
        PlannerScanEntry::where('planner_scan_id', $scan->id)
            ->whereNotNull('booking_id')
            ->each(function ($entry) {
                // Just unlink, don't delete the booking
                $entry->update(['booking_id' => null]);
            });

        $scan->entries()->delete();
        $scan->delete();

        return redirect()->route('admin.planner')->with('success', 'Scan deleted.');
    }

    // ── Update entry ───────────────────────────────────────────────────────────

    public function updateEntry(Request $request, PlannerScanEntry $entry)
    {
        $validated = $request->validate([
            'type'           => 'required|string',
            'date'           => 'required|date',
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
        $service = \App\Models\Service::findOrFail($validated['service_id']);
        $start   = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $end     = $start->copy()->addMinutes($service->duration_minutes);

        $rinkId = null;
        if (!empty($validated['rink']) && $validated['rink'] !== 'unknown') {
            $rink   = \App\Models\Rink::where('slug', $validated['rink'])->first();
            $rinkId = $rink?->id;
        }

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
                $entry->update(['student_id' => $student->id, 'match_status' => 'no_booking_found']);
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

        StudentAlias::firstOrCreate(['student_id' => $validated['student_id'], 'alias' => $validated['alias']]);

        if (!empty($validated['entry_id'])) {
            $entry = PlannerScanEntry::find($validated['entry_id']);
            if ($entry) {
                $entry->update(['student_id' => $validated['student_id'], 'match_status' => 'no_booking_found']);
            }
        }

        $student = Student::find($validated['student_id']);
        return back()->with('success', "Alias '{$validated['alias']}' linked to {$student->first_name}.");
    }

    // ── Dismiss a "missing" booking (it was just missed by OCR, not cancelled) ─

    public function dismissMissing(Request $request, PlannerScan $scan, Booking $booking)
    {
        // Create a placeholder confirmed entry so it won't show as missing again
        PlannerScanEntry::create([
            'planner_scan_id' => $scan->id,
            'date'            => $booking->date,
            'time'            => $booking->start_time,
            'type'            => 'private_lesson',
            'extracted_name'  => $booking->student?->first_name ?? $booking->client_name,
            'student_id'      => $booking->student_id,
            'booking_id'      => $booking->id,
            'match_status'    => 'matched',
            'confidence'      => 100,
            'notes'           => 'Manually linked — missed by OCR',
            'confirmed_at'    => now(),
        ]);
        $scan->increment('entries_confirmed');
        return back()->with('success', 'Booking dismissed and linked to this scan.');
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

    // ── Load handwriting reference images ─────────────────────────────────────

    private function loadHandwritingRefs(): array
    {
        $refDir  = storage_path('app/handwriting-ref');
        $refData = [];

        if (!is_dir($refDir)) return [];

        $files = glob($refDir . '/*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE);
        if (empty($files)) return [];

        // Sort so digits come first (0-9, then colon, then combos)
        sort($files);

        // Build label from filename e.g. "2a.png" → "2", "colon.png" → ":"
        $labels = [];
        foreach ($files as $file) {
            $base = pathinfo($file, PATHINFO_FILENAME);
            // Strip trailing letters for variants: "2a" → "2", "30" → "30"
            $label = preg_replace('/[a-z]+$/i', '', $base);
            $label = $label === 'colon' ? ':' : $label;
            $labels[$file] = $label;
        }

        // Group by label for intro text
        $uniqueLabels = array_unique(array_values($labels));
        sort($uniqueLabels);

        // Intro text block
        $refData[] = [
            'type' => 'text',
            'text' => "HANDWRITING REFERENCE: The following " . count($files) . " image(s) show examples of this coach's actual handwriting for digits and characters (" . implode(', ', $uniqueLabels) . "). Use these to correctly identify ambiguous digits in the planner scan — especially when distinguishing between similar-looking numbers like 2 vs 9, or 3 vs 8.",
        ];

        foreach ($files as $file) {
            $mimeType  = mime_content_type($file);
            $refData[] = [
                'type' => 'text',
                'text' => "Reference character: \"" . $labels[$file] . "\"",
            ];
            $refData[] = [
                'type'   => 'image',
                'source' => [
                    'type'       => 'base64',
                    'media_type' => $mimeType,
                    'data'       => base64_encode(file_get_contents($file)),
                ],
            ];
        }

        return $refData;
    }

    // ── Build dynamic system prompt with rink session context ──────────────────

    private function buildSystemPrompt(?string $month, ?string $year, string $pageSide = 'both'): string
    {
        $month = $month ?? date('F');
        $year  = (int)($year ?? date('Y'));

        // Page-specific layout context
        $pageContext = match($pageSide) {
            'left'  => "\nPAGE IDENTIFICATION: This is the LEFT page of the weekly spread (binding is on the RIGHT side of the image).\nThis page contains ONLY these columns in order from left to right: Notes, Sunday, Monday, Tuesday.\nDo NOT extract any Wednesday, Thursday, Friday, or Saturday entries from this image — they are on the other page.\nThe leftmost column is 'Notes' — it is not a day. Any entries there should be type 'note'.\n",
            'right' => "\nPAGE IDENTIFICATION: This is the RIGHT page of the weekly spread (binding is on the LEFT side of the image).\nThis page contains ONLY these columns in order from left to right: Wednesday, Thursday, Friday, Saturday.\nDo NOT extract any Sunday, Monday, or Tuesday entries from this image — they are on the other page.\n",
            default => "\nPAGE IDENTIFICATION: Both pages of the weekly spread are provided (2 images).\nimage_index 0 = LEFT page: Notes, Sunday, Monday, Tuesday (binding on right).\nimage_index 1 = RIGHT page: Wednesday, Thursday, Friday, Saturday (binding on left).\n",
        };

        $startDate = Carbon::createFromFormat('F Y', "{$month} {$year}")->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $sessions = \App\Models\RinkSession::with('rink')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('is_cancelled', false)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $sessionContext = '';
        if ($sessions->isNotEmpty()) {
            $byDow = $sessions->groupBy(fn($s) => Carbon::parse($s->date)->format('l'));
            $sessionContext  = "\n\nKNOWN PUBLIC SKATE WINDOWS FOR {$month} {$year} (from rink schedules):\n";
            $sessionContext .= "Private lessons can ONLY happen during public skate sessions. Use these to validate and correct extracted times.\n";
            foreach ($byDow as $dow => $dowSessions) {
                $sessionContext .= "{$dow}s:\n";
                foreach ($dowSessions->unique(fn($s) => $s->rink_id . $s->start_time) as $s) {
                    $start = Carbon::parse($s->start_time)->format('g:i A');
                    $end   = Carbon::parse($s->end_time)->format('g:i A');
                    $sessionContext .= "  - {$s->rink->name}: {$start} – {$end}\n";
                }
            }
            $sessionContext .= "\nIMPORTANT: If a handwritten time is ambiguous (e.g. '2:30' could be AM or PM), ";
            $sessionContext .= "always choose the interpretation that falls within a known public skate window. ";
            $sessionContext .= "For example, if you see '2:30' and there is a public skate at 2:30 PM but not 2:30 AM, use 14:30. ";
            $sessionContext .= "Morning times like 9:30 AM or 10:00 AM are almost certainly wrong if no public skate exists then.";
        }

        return self::SYSTEM_PROMPT . $pageContext . $sessionContext;
    }

    // ── Shared entry processing ────────────────────────────────────────────────

    private function processEntries(PlannerScan $scan, array $entries, $students): void
    {
        foreach ($entries as $entry) {
            $studentId   = null;
            $matchStatus = 'no_booking_expected';
            $confidence  = $entry['confidence'] ?? 100;

            if ($entry['type'] === 'private_lesson' && !empty($entry['student_name'])) {
                [$studentId, $matchStatus, $confidence] = $this->matchStudent($entry['student_name'], $students, $confidence);
                if ($studentId && $matchStatus === 'matched') {
                    $booking = $this->findMatchingBooking($studentId, $entry['date'], $entry['time'] ?? null);
                    if (!$booking) $matchStatus = 'no_booking_found';
                }
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
                'bbox'            => isset($entry['bbox']) ? $entry['bbox'] : null,
                'image_index'     => $entry['image_index'] ?? 0,
            ]);
        }
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
