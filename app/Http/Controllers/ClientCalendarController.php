<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class ClientCalendarController extends Controller
{
    public function lessonFeed(Request $request)
    {
        $token = $request->query('token');
        if (!$token) abort(403);

        $client = Client::where('calendar_token', $token)->first();
        if (!$client) abort(403);

        $bookings = Booking::with(['service', 'student', 'timeSlot.rink'])
            ->where('client_id', $client->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where('date', '>=', today()->subDays(30))
            ->where('date', '<=', today()->addDays(180))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $calendar = Calendar::create("My Kristine Skates Lessons — {$client->first_name}")
            ->refreshInterval(60);

        foreach ($bookings as $booking) {
            $date      = Carbon::parse($booking->date);
            $startTime = Carbon::parse($booking->start_time);
            $endTime   = $booking->end_time
                ? Carbon::parse($booking->end_time)
                : $startTime->copy()->addMinutes($booking->service?->duration_minutes ?? 30);

            $studentPart = $booking->student ? " — {$booking->student->first_name}" : '';
            $rink        = $booking->timeSlot?->rink?->name ?? '';
            $statusEmoji = match($booking->status) {
                'confirmed' => '✅',
                'pending'   => '⏳',
                'paid'      => '💚',
                default     => '⛸️',
            };

            $title = "{$statusEmoji} Skating Lesson{$studentPart}";

            $description = implode("\n", array_filter([
                "Service: " . ($booking->service?->name ?? 'Lesson'),
                $rink ? "Rink: {$rink}" : null,
                $booking->student ? "Student: {$booking->student->first_name} {$booking->student->last_name}" : null,
                $booking->price_paid ? "Price: \${$booking->price_paid}" : null,
                "Status: " . ucfirst($booking->status),
                $booking->confirmation_code ? "Confirmation #: {$booking->confirmation_code}" : null,
                "",
                "Coach Kristine Skates — kristineskates.com",
            ]));

            $event = Event::create()
                ->name($title)
                ->description($description)
                ->startsAt(new \DateTime($date->format('Y-m-d') . ' ' . $startTime->format('H:i:s')))
                ->endsAt(new \DateTime($date->format('Y-m-d') . ' ' . $endTime->format('H:i:s')))
                ->url('https://kristineskates.com/book');

            if ($rink) {
                $event->address($rink);
            }

            $calendar->event($event);
        }

        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="my-lessons.ics"');
    }
}
