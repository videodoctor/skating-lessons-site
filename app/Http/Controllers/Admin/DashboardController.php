<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RinkSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'upcoming' => Booking::where('status', 'confirmed')
                ->whereHas('timeSlot', function($q) {
                    $q->where('date', '>=', today());
                })
                ->count(),
        ];
        
        $recentBookings = Booking::with(['service', 'timeSlot.rink'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
