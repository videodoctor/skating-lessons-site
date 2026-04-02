<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingInterest;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function index()
    {
        $entries = BookingInterest::with('service')->orderByDesc('created_at')->paginate(30);
        $isPaused = SiteSetting::isBookingPaused();
        $pausedMessage = SiteSetting::get('booking_paused_message', '');
        $opensAt = SiteSetting::get('booking_opens_at');

        return view('admin.booking-waitlist', compact('entries', 'isPaused', 'pausedMessage', 'opensAt'));
    }

    public function togglePause(Request $request)
    {
        $request->validate([
            'booking_paused'         => 'required|in:0,1',
            'booking_paused_message' => 'nullable|string|max:500',
            'booking_opens_at'       => 'nullable|date',
        ]);

        SiteSetting::set('booking_paused', $request->booking_paused);
        SiteSetting::set('booking_paused_message', $request->booking_paused_message);
        SiteSetting::set('booking_opens_at', $request->booking_opens_at);

        $status = $request->booking_paused === '1' ? 'paused' : 'resumed';
        return back()->with('success', "Booking {$status}.");
    }

    public function destroy(BookingInterest $interest)
    {
        $interest->delete();
        return back()->with('success', 'Entry removed.');
    }
}
