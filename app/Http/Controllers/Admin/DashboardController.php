<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingInterest;
use App\Models\Client;
use App\Models\PageVisit;
use App\Models\Rink;
use App\Models\RinkScrapeRun;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\TimeSlot;
use App\Models\VenmoPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $prefs = $user->dashboard_prefs ?? [];

        $data = ['prefs' => $prefs];

        // ── Bookings section ──
        if ($user->dashboardPref('bookings')) {
            $data['bookings'] = [
                'pending'   => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'upcoming'  => Booking::whereIn('status', ['pending', 'confirmed'])
                    ->where('date', '>=', today())->count(),
                'today'     => Booking::whereIn('status', ['pending', 'confirmed'])
                    ->where('date', today())->count(),
                'unpaid'    => Booking::whereIn('status', ['pending', 'confirmed'])
                    ->where('payment_status', '!=', 'paid')->count(),
                'this_week' => Booking::where('created_at', '>=', now()->startOfWeek())->count(),
            ];
            $data['recentBookings'] = Booking::with(['service', 'timeSlot.rink'])
                ->latest()->take(5)->get();
        }

        // ── Schedule section ──
        if ($user->dashboardPref('schedule')) {
            $data['schedule'] = [
                'open_slots'   => TimeSlot::where('is_available', true)->whereNull('booking_id')
                    ->where('date', '>=', today())->count(),
                'blocked_slots' => TimeSlot::where('is_available', false)->whereNull('booking_id')
                    ->where('date', '>=', today())->count(),
                'next_7_days'  => TimeSlot::where('is_available', true)->whereNull('booking_id')
                    ->whereBetween('date', [today(), today()->addDays(7)])->count(),
            ];
        }

        // ── Clients section ──
        if ($user->dashboardPref('clients')) {
            $data['clients'] = [
                'total'       => Client::count(),
                'new_7d'      => Client::where('created_at', '>=', now()->subDays(7))->count(),
                'with_bookings' => Client::has('bookings')->count(),
            ];
            $data['students'] = [
                'total'    => Student::count(),
                'orphaned' => Student::whereNull('client_id')->count(),
                'active'   => Student::where('is_active', true)->count(),
            ];
        }

        // ── Analytics section ──
        if ($user->dashboardPref('analytics')) {
            $data['analytics'] = [
                'today'     => PageVisit::public()->homepage()->where('created_at', '>=', today())->count(),
                'week'      => PageVisit::public()->homepage()->where('created_at', '>=', now()->subDays(7))->count(),
                'unique_7d' => PageVisit::public()->homepage()->where('created_at', '>=', now()->subDays(7))
                    ->distinct('ip_address')->count('ip_address'),
            ];
        }

        // ── Payments section ──
        if ($user->dashboardPref('payments')) {
            $data['payments'] = [
                'unpaid_bookings' => Booking::whereIn('status', ['pending', 'confirmed'])
                    ->where('payment_status', '!=', 'paid')->count(),
                'unlinked_venmo'  => VenmoPayment::where('match_status', 'unmatched')->count(),
                'revenue_30d'     => Booking::where('payment_status', 'paid')
                    ->where('created_at', '>=', now()->subDays(30))->sum('price_paid'),
            ];
        }

        // ── Scraper section ──
        if ($user->dashboardPref('scraper')) {
            $data['scraperRuns'] = RinkScrapeRun::with('rink')
                ->whereIn('id', function ($q) {
                    $q->selectRaw('MAX(id)')->from('rink_scrape_runs')->groupBy('rink_id');
                })
                ->orderByDesc('created_at')->get();
        }

        // ── Waitlist section ──
        if ($user->dashboardPref('waitlist')) {
            $data['waitlist'] = [
                'paused'  => SiteSetting::isBookingPaused(),
                'entries' => BookingInterest::count(),
                'recent'  => BookingInterest::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        }

        return view('admin.dashboard', $data);
    }

    public function updatePrefs(Request $request)
    {
        $user = auth()->user();
        $section = $request->input('section');
        $visible = $request->boolean('visible');

        $prefs = $user->dashboard_prefs ?? [];
        $prefs[$section] = $visible;
        $user->update(['dashboard_prefs' => $prefs]);

        return response()->json(['ok' => true]);
    }
}
