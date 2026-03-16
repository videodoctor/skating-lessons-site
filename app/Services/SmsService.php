<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\SmsReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SmsService
{
    protected TwilioClient $twilio;
    protected string $from;

    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->from = config('services.twilio.from');
    }

    // ── Send a lesson reminder ─────────────────────────────────────────────────

    public function sendLessonReminder(Booking $booking): ?SmsReminder
    {
        $phone = $this->resolvePhone($booking);

        if (!$phone) {
            Log::warning("SmsService: no phone for booking #{$booking->id}");
            return null;
        }

        $message = $this->buildReminderMessage($booking);

        try {
            $msg = $this->twilio->messages->create($phone, [
                'from' => $this->from,
                'body' => $message,
            ]);

            $reminder = SmsReminder::create([
                'booking_id'    => $booking->id,
                'client_id'     => $booking->client_id,
                'to_number'     => $phone,
                'message_body'  => $message,
                'twilio_sid'    => $msg->sid,
                'status'        => 'sent',
                'sent_at'       => now(),
                'scheduled_for' => now(),
            ]);

            $booking->update(['reminder_sent' => true]);

            Log::info("SMS reminder sent for booking #{$booking->id} to {$phone}");
            return $reminder;

        } catch (\Exception $e) {
            Log::error("SmsService error for booking #{$booking->id}: " . $e->getMessage());
            return null;
        }
    }

    // ── Build the reminder message ─────────────────────────────────────────────

    public function buildReminderMessage(Booking $booking): string
    {
        $date     = Carbon::parse($booking->date ?? $booking->timeSlot?->date);
        $time     = Carbon::parse($booking->start_time ?? $booking->timeSlot?->start_time);
        $rinkName = $booking->timeSlot?->rink?->name ?? 'the rink';
        $student  = $booking->student;

        $when = $date->isTomorrow()
            ? 'tomorrow at ' . $time->format('g:i A')
            : 'on ' . $date->format('l, F j') . ' at ' . $time->format('g:i A');

        $studentPart = $student ? " for {$student->first_name}" : '';

        $price = $booking->price_paid
            ? ' $' . number_format($booking->price_paid, 0) . ' due at lesson.'
            : '';

        $cancellationNote = ' Reply YES to confirm, NO to cancel (cancellations <24hrs will be billed).';

        return "Reminder: Your skating lesson{$studentPart} is {$when} at {$rinkName}.{$price}{$cancellationNote} — Kristine Skates";
    }

    // ── Handle inbound YES/NO/LESSONS/SKATE/HELP reply ───────────────────────

    public function handleReply(string $fromNumber, string $body): string
    {
        $normalized = $this->normalizePhone($fromNumber);
        $reply      = strtoupper(trim($body));

        // ── HELP ──────────────────────────────────────────────────────────────
        if ($reply === 'HELP') {
            return "Kristine Skates help: Reply YES to confirm a lesson, NO to cancel, LESSONS for upcoming lessons, SKATE for today's public skate times. Contact: kristine@kristineskates.com or kristineskates.com. Reply STOP to opt out.";
        }

        // ── LESSONS — upcoming bookings for this number ───────────────────────
        if ($reply === 'LESSONS') {
            $client = Client::where('sms_phone', $normalized)
                ->orWhere('phone', $normalized)
                ->first();

            if (!$client) {
                return "We couldn't find an account for this number. Visit kristineskates.com to book a lesson. Reply STOP to opt out.";
            }

            $bookings = Booking::where('client_id', $client->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->where('date', '>=', today())
                ->orderBy('date')
                ->take(5)
                ->get();

            if ($bookings->isEmpty()) {
                return "Hi {$client->first_name}! No upcoming lessons found. Book at kristineskates.com — Coach Kristine";
            }

            $lines = $bookings->map(function ($b) {
                $date = Carbon::parse($b->date)->format('D M j');
                $time = Carbon::parse($b->start_time)->format('g:iA');
                $rink = $b->timeSlot?->rink?->name ?? 'TBD';
                // Shorten rink name
                $rink = str_replace(['Ice Arena', 'Ice Rink', 'Hockey Center'], '', $rink);
                return "{$date} {$time} " . trim($rink);
            })->implode(', ');

            return "Upcoming lessons for {$client->first_name}: {$lines}. Reply HELP for assistance or STOP to opt out. — Kristine Skates";
        }

        // ── SKATE — today's public skate sessions ─────────────────────────────
        if ($reply === 'SKATE') {
            $sessions = \App\Models\RinkSession::with('rink')
                ->where('date', today())
                ->where('is_cancelled', false)
                ->orderBy('start_time')
                ->get();

            if ($sessions->isEmpty()) {
                return "No public skate sessions found for today. Check kristineskates.com/rinks for the full schedule. — Kristine Skates";
            }

            $lines = $sessions->map(function ($s) {
                $start = Carbon::parse($s->start_time)->format('g:iA');
                $end   = Carbon::parse($s->end_time)->format('g:iA');
                $rink  = str_replace(['Ice Arena', 'Ice Rink', 'Hockey Center'], '', $s->rink->name);
                return trim($rink) . " {$start}-{$end}";
            })->implode(', ');

            return "Today's public skate: {$lines}. Book a lesson at kristineskates.com — Kristine Skates";
        }

        // ── YES / NO — lesson confirmation ────────────────────────────────────
        $reminder = SmsReminder::where('to_number', $normalized)
            ->whereNull('reply')
            ->whereHas('booking', fn($q) => $q->whereIn('status', ['pending', 'confirmed']))
            ->latest()
            ->first();

        if (!$reminder) {
            return "We couldn't find an upcoming lesson for this number. Reply LESSONS for your schedule, SKATE for today's public skate times, or HELP for assistance. — Kristine Skates";
        }

        $reminder->update([
            'reply'      => $reply,
            'replied_at' => now(),
            'status'     => 'replied',
        ]);

        $booking = $reminder->booking;

        if ($reply === 'YES') {
            $booking->update([
                'confirmed_via_sms' => true,
                'sms_confirmed_at'  => now(),
                'status'            => 'confirmed',
            ]);
            $studentName = $booking->student?->first_name;
            $time        = Carbon::parse($booking->start_time ?? $booking->timeSlot?->start_time)->format('g:i A');
            $date        = Carbon::parse($booking->date ?? $booking->timeSlot?->date)->format('M j');
            $who         = $studentName ? " for {$studentName}" : '';

            // Include Venmo link on confirmation
            $venmoHandle = ltrim(config('services.venmo.handle', 'Kristine-Humphrey'), '@');
            $amount      = $booking->price_paid ? number_format($booking->price_paid, 2) : '';
            $note        = 'Lesson ' . ($booking->confirmation_code ?? '');
            $venmoLink   = $amount
                ? " Pay via Venmo: venmo.com/{$venmoHandle}?txn=pay&amount={$amount}&note=" . urlencode($note)
                : '';

            return "Confirmed! We'll see you{$who} on {$date} at {$time}.{$venmoLink} — Coach Kristine";

        } elseif ($reply === 'NO') {
            $booking->update([
                'status'              => 'cancelled',
                'sms_cancelled_at'    => now(),
                'cancellation_reason' => 'Cancelled via SMS reply',
            ]);

            if ($booking->timeSlot) {
                $booking->timeSlot->update(['booking_id' => null, 'is_available' => true]);
            }

            Log::info("Booking #{$booking->id} cancelled via SMS by {$fromNumber}");
            return "Your lesson has been cancelled. If this was a mistake, please contact Coach Kristine directly at kristine@kristineskates.com. Thank you! — Kristine Skates";

        } else {
            return "Sorry, we didn't understand that. Reply YES to confirm, NO to cancel, LESSONS for your schedule, SKATE for today's public skate, or HELP for assistance. — Kristine Skates";
        }
    }

    // ── Normalize phone to E.164 ───────────────────────────────────────────────

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) return '+1' . $digits;
        if (strlen($digits) === 11 && $digits[0] === '1') return '+' . $digits;
        return '+' . $digits;
    }

    // ── Resolve best phone for a booking ──────────────────────────────────────

    private function resolvePhone(Booking $booking): ?string
    {
        // Try client sms_phone first, then client phone, then booking phone
        $raw = $booking->client?->sms_phone
            ?? $booking->client?->phone
            ?? $booking->client_phone
            ?? null;

        if (!$raw) return null;
        return $this->normalizePhone($raw);
    }
}
