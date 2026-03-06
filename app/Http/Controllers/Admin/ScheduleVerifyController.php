<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rink;
use App\Models\RinkScrapeRun;
use App\Models\RinkSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ScheduleVerifyController extends Controller
{
    public function index(Request $request)
    {
        $rinks      = Rink::orderByRaw("FIELD(slug,'creve-coeur','kirkwood','webster-groves','brentwood','maryville')")->get();
        $rinkId     = $request->get('rink_id', $rinks->first()?->id);
        $month      = (int)$request->get('month', date('n'));
        $year       = (int)$request->get('year', date('Y'));

        $selectedRink = Rink::find($rinkId);
        $scrapeRun    = null;
        $sessions     = collect();
        $calendarDays = [];

        if ($selectedRink) {
            $scrapeRun = RinkScrapeRun::where('rink_id', $rinkId)
                ->where('month', $month)
                ->where('year', $year)
                ->latest('scraped_at')
                ->first();

            $sessions = RinkSession::where('rink_id', $rinkId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            // Build calendar grid
            $firstDay    = Carbon::create($year, $month, 1);
            $daysInMonth = $firstDay->daysInMonth;
            $startDow    = $firstDay->dayOfWeek; // 0=Sun

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date                = Carbon::create($year, $month, $d)->toDateString();
                $calendarDays[$date] = $sessions->filter(fn($s) => \Carbon\Carbon::parse($s->date)->toDateString() === $date)->values();
            }
        }

        // Build month/year options
        $monthOptions = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthOptions[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }
        $yearOptions = [date('Y') - 1, date('Y'), date('Y') + 1];

        return view('admin.schedule-verify', compact(
            'rinks', 'selectedRink', 'rinkId', 'month', 'year',
            'scrapeRun', 'sessions', 'calendarDays',
            'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Serve the stored raw source file (PDF/image/HTML) for the left panel.
     */
    public function serveSource(Request $request, int $runId)
    {
        $run = RinkScrapeRun::findOrFail($runId);

        if (!$run->hasSourceFile()) {
            abort(404, 'Source file not found');
        }

        $content  = Storage::get($run->source_file_path);
        $mimeType = match ($run->source_type) {
            'pdf'   => 'application/pdf',
            'image' => 'image/jpeg',
            'html'  => 'text/html',
            default => 'application/octet-stream',
        };

        return response($content, 200)->header('Content-Type', $mimeType);
    }

    /**
     * Add a session manually.
     */
    public function storeSession(Request $request)
    {
        $validated = $request->validate([
            'rink_id'    => 'required|exists:rinks,id',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        RinkSession::updateOrCreate(
            ['rink_id' => $validated['rink_id'], 'date' => $validated['date'], 'start_time' => $validated['start_time']],
            ['end_time' => $validated['end_time'], 'session_type' => 'public_skate', 'scraped_at' => null]
        );

        return back()->with('success', 'Session added successfully.');
    }

    /**
     * Update an existing session.
     */
    public function updateSession(Request $request, int $sessionId)
    {
        $session   = RinkSession::findOrFail($sessionId);
        $validated = $request->validate([
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        $session->update($validated);
        return back()->with('success', 'Session updated.');
    }

    /**
     * Delete a session (and its unbooked slots).
     */
    public function destroySession(int $sessionId)
    {
        $session = RinkSession::findOrFail($sessionId);

        // Delete unbooked slots tied to this session
        \App\Models\TimeSlot::where('rink_session_id', $sessionId)
            ->whereNull('booking_id')
            ->delete();

        $session->delete();
        return back()->with('success', 'Session deleted.');
    }

    /**
     * Trigger a re-scrape for a specific rink.
     */
    public function rescrape(Request $request)
    {
        $rink = Rink::findOrFail($request->rink_id);
        \Artisan::call('scrape:rink-schedules', ['rink' => $rink->slug]);
        $output = \Artisan::output();
        return back()->with('success', "Re-scrape complete for {$rink->name}.")->with('scrape_output', $output);
    }
}
