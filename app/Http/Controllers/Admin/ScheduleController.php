<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rink;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = $request->get('month');
        $currentMonth = $monthParam ? Carbon::parse($monthParam . '-01') : Carbon::now()->startOfMonth();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth   = $currentMonth->copy()->endOfMonth();

        $startDow   = $startOfMonth->dayOfWeek;
        $daysInMonth = $startOfMonth->daysInMonth;

        // Bookings in this month, keyed by date
        $bookings = Booking::with(['service', 'timeSlot'])
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $bookingsByDate = $bookings->groupBy(fn($b) => Carbon::parse($b->date)->format('Y-m-d'));

        // Open time slots (not booked)
        // Also exclude slots where a booking exists at the same date+time even without slot link
        $bookedTimes = $bookings->map(fn($b) => $b->date->format('Y-m-d') . '_' . $b->start_time)->values()->toArray();
        $slots = TimeSlot::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_available', true)
            ->whereNull('booking_id')
            ->get()
            ->filter(function($slot) use ($bookedTimes) {
                $key = Carbon::parse($slot->date)->format('Y-m-d') . '_' . $slot->start_time;
                return !in_array($key, $bookedTimes);
            });

        $slotsByDate = $slots->groupBy(fn($s) => Carbon::parse($s->date)->format('Y-m-d'));

        $rinks = Rink::where('is_active', true)->get();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $bookingsJson = $bookingsByDate->map(fn($b) => $b->map(fn($x) => [
            'id' => $x->id, 'client_name' => $x->client_name,
            'status' => $x->status, 'start_time' => $x->start_time,
            'service_name' => $x->service->name ?? ''
        ]));
        $slotsJson = $slotsByDate->map(fn($s) => $s->map(fn($x) => [
            'id' => $x->id, 'start_time' => $x->start_time, 'is_available' => $x->is_available
        ]));
        return view('admin.schedule', compact(
            'currentMonth', 'startDow', 'daysInMonth',
            'bookingsByDate', 'slotsByDate', 'rinks',
            'prevMonth', 'nextMonth', 'bookingsJson', 'slotsJson'
        ));
    }

    public function storeSlot(Request $request)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|in:30,60',
            'rink_id'    => 'required|exists:rinks,id',
        ]);

        $start = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $end   = $start->copy()->addMinutes($validated['duration_minutes']);

        TimeSlot::create([
            'date'             => $validated['date'],
            'start_time'       => $start->format('H:i:s'),
            'end_time'         => $end->format('H:i:s'),
            'duration_minutes' => $validated['duration_minutes'],
            'rink_id'          => $validated['rink_id'],
            'is_available'     => true,
        ]);

        return redirect()->back()->with('success', 'Slot added successfully.');
    }

    public function destroySlot(TimeSlot $timeSlot)
    {
        if ($timeSlot->booking_id) {
            return redirect()->back()->with('error', 'Cannot remove a slot with an active booking.');
        }
        $timeSlot->delete();
        return redirect()->back()->with('success', 'Slot removed.');
    }

    public function blockDay(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $count = TimeSlot::where('date', $request->date)
            ->whereNull('booking_id')
            ->where('is_available', true)
            ->update(['is_available' => false]);

        return redirect()->back()->with('success', "Blocked {$count} open slot(s) on {$request->date}.");
    }

    public function plannerOcr()
    {
        $recentBookings = Booking::with('service')
            ->where('date', '>=', now()->subDays(14))
            ->where('date', '<=', now()->addDays(60))
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['id', 'client_name', 'date', 'start_time', 'status']);

        return view('admin.planner-ocr', compact('recentBookings'));
    }
}
