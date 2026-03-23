<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $monthParam   = $request->query('month');
        $currentMonth = $monthParam ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : now()->startOfMonth();
        $prevMonth    = $currentMonth->copy()->subMonth();
        $nextMonth    = $currentMonth->copy()->addMonth();

        $start = $currentMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $end   = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $bookings = Booking::with(['client', 'student', 'service', 'timeSlot.rink'])
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->whereNotIn('status', ['rejected'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $bookingsByDate = $bookings->groupBy(fn($b) => Carbon::parse($b->date)->format('Y-m-d'));

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

    // ── iCal feed for Kristine's bookings ─────────────────────────────────────

    public function icalFeed(Request $request)
    {
        // Validate token to prevent public access
        $token = $request->query('token');
        if ($token !== config('services.calendar.admin_token')) {
            abort(403);
        }

        $bookings = Booking::with(['client', 'student', 'service', 'timeSlot.rink'])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where('date', '>=', today()->subDays(30)) // 30 days back
            ->where('date', '<=', today()->addDays(180)) // 6 months ahead
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $calendar = Calendar::create("Kristine Skates — Bookings")
            ->refreshInterval(30);

        foreach ($bookings as $booking) {
            $date      = Carbon::parse($booking->date);
            $startTime = Carbon::parse($booking->start_time);
            $endTime   = $booking->end_time
                ? Carbon::parse($booking->end_time)
                : $startTime->copy()->addMinutes($booking->service?->duration_minutes ?? 30);

            $studentPart  = $booking->student ? " ({$booking->student->first_name})" : '';
            $servicePart  = $booking->service?->name ?? 'Lesson';
            $rinkPart     = $booking->timeSlot?->rink?->name ?? '';
            $statusEmoji  = match($booking->status) {
                'confirmed' => '✅',
                'pending'   => '⏳',
                'paid'      => '💚',
                default     => '📅',
            };

            $title = "{$statusEmoji} {$booking->client_name}{$studentPart} — {$servicePart}";

            $description = implode("\n", array_filter([
                "Client: {$booking->client_name}",
                $booking->client_email ? "Email: {$booking->client_email}" : null,
                $booking->client_phone ? "Phone: {$booking->client_phone}" : null,
                $booking->student      ? "Student: {$booking->student->first_name} {$booking->student->last_name}" : null,
                "Service: {$servicePart}",
                $booking->price_paid   ? "Price: \${$booking->price_paid}" : null,
                "Status: " . ucfirst($booking->status),
                $booking->confirmation_code ? "Confirmation: {$booking->confirmation_code}" : null,
                $booking->notes        ? "Notes: {$booking->notes}" : null,
            ]));

            $event = Event::create()
                ->name($title)
                ->description($description)
                ->startsAt(new \DateTime($date->format('Y-m-d') . ' ' . $startTime->format('H:i:s')))
                ->endsAt(new \DateTime($date->format('Y-m-d') . ' ' . $endTime->format('H:i:s')))
                ->url(url("/admin/bookings/{$booking->id}"));

            if ($rinkPart) {
                $event->address($rinkPart);
            }

            $calendar->event($event);
        }

        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="kristine-bookings.ics"');
    }
}
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
