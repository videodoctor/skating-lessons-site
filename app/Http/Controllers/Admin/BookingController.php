<?php

namespace App\Http\Controllers\Admin;

use App\Notifications\BookingApprovedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Student;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Booking::with(['service', 'timeSlot.rink', 'client', 'student.client'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(20);
        $clients  = Client::orderBy('name')->get(['id', 'first_name', 'last_name', 'name', 'email']);

        return view('admin.bookings.index', compact('bookings', 'status', 'clients'));
    }

    public function approve(Booking $booking)
    {
        $booking->update(['status' => 'confirmed']);

        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingApprovedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingApprovedNotification($booking));
        }

        return back()->with('success', 'Booking approved successfully!');
    }

    public function reject(Booking $booking)
    {
        if ($booking->timeSlot) {
            $booking->timeSlot->update(['booking_id' => null, 'is_available' => true]);
        }

        $booking->update(['status' => 'cancelled']);

        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingRejectedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingRejectedNotification($booking));
        }

        return back()->with('success', 'Booking rejected and time slot released.');
    }

    public function linkClient(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Update booking with client info
        $booking->update([
            'client_id'    => $client->id,
            'client_name'  => $client->full_name,
            'client_email' => $client->email,
            'client_phone' => $client->phone ?? $booking->client_phone,
        ]);

        // If booking has a student, link student to client too if not already linked
        if ($booking->student && !$booking->student->client_id) {
            $booking->student->update(['client_id' => $client->id]);
        }

        return back()->with('success', "Booking linked to {$client->full_name}.");
    }

    public function markCashPaid(Request $request, Booking $booking)
    {
        $booking->update([
            'payment_type'   => 'cash',
            'payment_status' => 'paid',
            'cash_paid_at'   => now(),
            'cash_marked_by' => auth()->user()->name ?? 'admin',
        ]);

        return back()->with('success', 'Booking marked as cash paid.');
    }

    public function markVenmoPaid(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'venmo_username' => 'nullable|string',
        ]);

        $booking->update([
            'payment_type'       => 'venmo',
            'payment_status'     => 'paid',
            'venmo_confirmed_at' => now(),
            'venmo_username'     => $validated['venmo_username'] ?? null,
        ]);

        return back()->with('success', 'Booking marked as Venmo paid.');
    }
}
