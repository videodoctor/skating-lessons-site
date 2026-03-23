<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $monthParam   = $request->query('month');
        $currentMonth = $monthParam ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : now()->startOfMonth();
        $prevMonth    = $currentMonth->copy()->subMonth();
        $nextMonth    = $currentMonth->copy()->addMonth();

        // Fetch all bookings for this month + overflow weeks
        $start = $currentMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $bookings = Booking::with(['client', 'student', 'service', 'timeSlot.rink'])
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereNotIn('status', ['rejected'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Group by date
        $bookingsByDate = $bookings->groupBy(fn($b) => Carbon::parse($b->date)->format('Y-m-d'));

        // Build calendar grid (6 weeks max)
        $calendarWeeks = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = [
                    'date'         => $day->copy(),
                    'currentMonth' => $day->month === $currentMonth->month,
                ];
                $day->addDay();
            }
            $calendarWeeks[] = $week;
        }

        // JSON for JS detail panel
        $bookingsJson = $bookings->keyBy('id')->map(fn($b) => [
            'id'                => $b->id,
            'client_name'       => $b->client_name,
            'client_email'      => $b->client_email,
            'client_phone'      => $b->client_phone,
            'date'              => Carbon::parse($b->date)->format('D, M j Y'),
            'start_time'        => Carbon::parse($b->start_time)->format('g:i A'),
            'status'            => $b->status,
            'service'           => $b->service?->name ?? 'Lesson',
            'rink'              => $b->timeSlot?->rink?->name,
            'student'           => $b->student ? $b->student->first_name . ' ' . $b->student->last_name : null,
            'price'             => $b->price_paid ? number_format($b->price_paid, 0) : null,
            'notes'             => $b->notes,
            'confirmation_code' => $b->confirmation_code,
        ]);

        return view('admin.calendar', compact(
            'bookings',
            'bookingsByDate',
            'bookingsJson',
            'calendarWeeks',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }
}
