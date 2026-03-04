<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\NewBookingNotification;
use App\Notifications\BookingRequestedNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Step 1: Select Service
    public function index()
    {
        $services = Service::where('is_active', true)->orderBy('price')->get();
        
        return view('booking.index', compact('services'));
    }
    
    // Step 2: Select Date
    public function selectDate(Service $service)
    {
        // Get available dates for the next 60 days
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(60);
        $now = Carbon::now();
    
        // Get all time slots in date range that are available
        $availableDates = TimeSlot::where('is_available', true)
            ->whereNull('booking_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('rink', function($q) {
                $q->where('is_active', true);
            })
            // For today, only include future times
            ->where(function($query) use ($now) {
                $query->where('date', '>', $now->toDateString())
                      ->orWhere(function($q) use ($now) {
                          $q->where('date', '=', $now->toDateString())
                            ->where('start_time', '>', $now->format('H:i:s'));
                      });
            })
            ->select('date')
            ->distinct()
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date);
            });
     
        return view('booking.select-date', compact('service', 'availableDates'));
    }
    
    // Step 3: Select Time & Rink
    public function selectTime(Service $service, $date)
    {
        $date = Carbon::parse($date);
        $now = Carbon::now();
        
        // Get available time slots for this date, grouped by rink
        $timeSlots = TimeSlot::with(['rink'])
            ->where('date', $date)
            ->where('is_available', true)
            ->whereNull('booking_id')
            ->whereHas('rink', function($q) {
                $q->where('is_active', true);
            })
            // Only show future times if date is today
            ->when($date->isToday(), function($query) use ($now) {
                $query->where('start_time', '>', $now->format('H:i:s'));
            })
            ->orderBy('start_time')
            ->get()
            ->groupBy('rink_id');
    
            $client = Auth::guard('client')->user();
            return view('booking.select-time', compact('service', 'date', 'timeSlots', 'client'));    
    }
    
    // Step 4: Submit Booking
public function submit(Request $request)
{
    $validated = $request->validate([
        'service_id' => 'required|exists:services,id',
        'time_slot_id' => 'required|exists:time_slots,id',
        'client_name' => 'required|string|max:255',
        'client_email' => 'required|email',
        'client_phone' => 'required|string',
        'notes' => 'nullable|string',
        'email_consent' => 'required|accepted',
    ]);
    
    // Check slot is still available
    $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
    
    if (!$timeSlot->is_available || $timeSlot->booking_id) {
        return back()->with('error', 'Sorry, that time slot is no longer available.');
    }
    
    // Create booking
    $booking = Booking::create([
        'client_id' => Auth::guard('client')->id(),
        'service_id' => $validated['service_id'],
        'time_slot_id' => $validated['time_slot_id'],
        'client_name' => $validated['client_name'],
        'client_email' => $validated['client_email'],
        'client_phone' => $validated['client_phone'],
        'notes' => $validated['notes'],
        'status' => 'pending',
        'price_paid' => Service::find($validated['service_id'])->price,
        'date' => $timeSlot->date,
        'email_consent_at' => now(),
        'start_time' => $timeSlot->start_time,
        'end_time' => $timeSlot->end_time,
    ]);
    
        // Mark slot as unavailable
        $timeSlot->update([
            'booking_id' => $booking->id,
            'is_available' => false,
        ]);

        // Send confirmation to client
        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingRequestedNotification($booking));
        } elseif ($booking->client_email) {
             \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingRequestedNotification($booking));
        }
    
        // Send email to all admins
        User::all()->each(function($admin) use ($booking) {
            $admin->notify(new NewBookingNotification($booking));
        });

       return redirect()->route('booking.confirmation', $booking);
    }    
    // Step 5: Confirmation
    public function confirmation(Booking $booking)
    {
        return view('booking.confirmation', compact('booking'));
    }
}
