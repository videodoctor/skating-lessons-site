<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rink;
use App\Models\RinkScrapeRun;
use App\Models\RinkSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ScraperController extends Controller
{
    public function index()
    {
        $rinks = Rink::where('is_active', true)->orderBy('name')->get();

        $rinkRuns = RinkScrapeRun::whereIn('rink_id', $rinks->pluck('id'))
            ->orderByDesc('scraped_at')
            ->get()
            ->groupBy('rink_id');

        $rinkSessions = RinkSession::whereIn('rink_id', $rinks->pluck('id'))
            ->where('date', '>=', now()->toDateString())
            ->where('date', '<=', now()->addDays(14)->toDateString())
            ->where('is_cancelled', false)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('rink_id');

        return view('admin.scraper', compact('rinks', 'rinkRuns', 'rinkSessions'));
    }

    public function runAll()
    {
        try {
            Artisan::call('scrape:rink-schedules');
            $output = Artisan::output();
            return redirect()->route('admin.scraper.index')
                ->with('success', 'All scrapers ran successfully. ' . substr(strip_tags($output), 0, 100));
        } catch (\Exception $e) {
            return redirect()->route('admin.scraper.index')
                ->with('error', 'Scraper failed: ' . $e->getMessage());
        }
    }

    public function runOne(string $rinkSlug)
    {
        try {
            Artisan::call('scrape:rink-schedules', ['rink' => $rinkSlug]);
            $output = Artisan::output();
            $rink   = Rink::where('slug', $rinkSlug)->first();
            return redirect()->route('admin.scraper.index')
                ->with('success', ($rink?->name ?? $rinkSlug) . ' scraped successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.scraper.index')
                ->with('error', 'Scraper failed: ' . $e->getMessage());
        }
    }
}
