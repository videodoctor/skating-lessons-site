<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class TemplateVars
{
    /**
     * Build a standard variable array from a booking for template rendering.
     */
    public static function fromBooking(Booking $booking): array
    {
        $date = $booking->date ?? $booking->timeSlot?->date;
        $time = $booking->start_time ?? $booking->timeSlot?->start_time;
        $rink = $booking->timeSlot?->rink;

        return [
            'client_name'       => $booking->client_name ?? $booking->client?->full_name ?? '',
            'first_name'        => $booking->client?->first_name ?? explode(' ', $booking->client_name ?? '')[0] ?? '',
            'student_name'      => $booking->student_name ?? $booking->student?->first_name ?? '',
            'service_name'      => $booking->service?->name ?? '',
            'lesson_date'       => $date ? Carbon::parse($date)->format('l, F j, Y') : '',
            'lesson_time'       => $time ? Carbon::parse($time)->format('g:i A') : '',
            'rink_name'         => $rink?->name ?? '',
            'rink_address'      => $rink?->address ?? '',
            'price'             => number_format($booking->price_paid ?? 0, 2),
            'confirmation_code' => $booking->confirmation_code ?? '',
            'venmo_link'        => $booking->venmo_link ?? '',
        ];
    }
}
