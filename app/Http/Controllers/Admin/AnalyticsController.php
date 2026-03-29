<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientActivityLog;
use App\Models\PageVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $since = now()->subDays($days)->startOfDay();

        // Headline stats
        $totalVisits   = PageVisit::homepage()->where('created_at', '>=', $since)->count();
        $uniqueVisitors = PageVisit::homepage()->where('created_at', '>=', $since)->distinct('ip_address')->count('ip_address');
        $todayVisits   = PageVisit::homepage()->where('created_at', '>=', today())->count();
        $newClients    = Client::where('created_at', '>=', $since)->count();

        // Visits per day
        $dailyVisits = PageVisit::homepage()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Referrer breakdown
        $referrers = PageVisit::homepage()
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer_source')
            ->where('referrer_source', '!=', 'internal')
            ->selectRaw('referrer_source, COUNT(*) as visits')
            ->groupBy('referrer_source')
            ->orderByDesc('visits')
            ->get();

        // Top cities
        $cities = PageVisit::homepage()
            ->where('created_at', '>=', $since)
            ->whereNotNull('city')
            ->selectRaw("CONCAT(city, ', ', region) as location, COUNT(*) as visits")
            ->groupByRaw("city, region")
            ->orderByDesc('visits')
            ->limit(15)
            ->get();

        // UTM campaigns
        $campaigns = PageVisit::where('created_at', '>=', $since)
            ->whereNotNull('utm_source')
            ->selectRaw('utm_source, utm_medium, utm_campaign, COUNT(*) as visits')
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
            ->orderByDesc('visits')
            ->get();

        // Recent visits (last 50 — all pages, to catch vetting/reviewer traffic)
        $recentVisits = PageVisit::where('created_at', '>=', $since)
            ->latest('created_at')
            ->limit(50)
            ->get();

        // Top pages
        $topPages = PageVisit::where('created_at', '>=', $since)
            ->selectRaw('path, COUNT(*) as visits')
            ->groupBy('path')
            ->orderByDesc('visits')
            ->limit(15)
            ->get();

        return view('admin.analytics.index', compact(
            'days', 'totalVisits', 'uniqueVisitors', 'todayVisits', 'newClients',
            'dailyVisits', 'referrers', 'cities', 'campaigns', 'recentVisits', 'topPages'
        ));
    }

    public function activity(Request $request)
    {
        $query = ClientActivityLog::with('client')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $logs = $query->paginate(50)->withQueryString();

        $actions = ClientActivityLog::distinct('action')->pluck('action');
        $clients = Client::orderBy('first_name')->get();

        return view('admin.analytics.activity', compact('logs', 'actions', 'clients'));
    }

    public function funnel(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $since = now()->subDays($days)->startOfDay();

        $homepageVisits = PageVisit::homepage()->where('created_at', '>=', $since)->distinct('ip_address')->count('ip_address');
        $bookingPageVisits = PageVisit::where('path', 'LIKE', '/book%')->where('created_at', '>=', $since)->distinct('ip_address')->count('ip_address');
        $bookingsSubmitted = Booking::where('created_at', '>=', $since)->count();
        $bookingsConfirmed = Booking::where('created_at', '>=', $since)->where('status', 'approved')->count();
        $clientRegistrations = Client::where('created_at', '>=', $since)->count();

        return view('admin.analytics.funnel', compact(
            'days', 'homepageVisits', 'bookingPageVisits', 'bookingsSubmitted',
            'bookingsConfirmed', 'clientRegistrations'
        ));
    }
}
