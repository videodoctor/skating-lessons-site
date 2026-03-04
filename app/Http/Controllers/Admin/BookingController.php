<?php

namespace App\Http\Controllers\Admin;

use App\Notifications\BookingApprovedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        
        $query = Booking::with(['service', 'timeSlot.rink'])
            ->latest();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $bookings = $query->paginate(20);
        
        return view('admin.bookings.index', compact('bookings', 'status'));
    }
    
    public function approve(Booking $booking)
    {
        $booking->update(['status' => 'confirmed']);
        
        // Send email to client
        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingApprovedNotification($booking));
        } elseif ($booking->client_email) {
            // Guest booking - send to their email
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingApprovedNotification($booking));
        }
        
        return back()->with('success', 'Booking approved successfully!');
    }
    
    public function reject(Booking $booking)
    {
        // Release the time slot
        $booking->timeSlot->update([
            'booking_id' => null,
            'is_available' => true,
        ]);
        
        $booking->update(['status' => 'cancelled']);
        
        // Send email to client
        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingRejectedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingRejectedNotification($booking));
        }
        
        return back()->with('success', 'Booking rejected and time slot released.');        
    }
}
