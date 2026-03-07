<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\SmsReminder;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendLessonReminders extends Command
{
    protected $signature   = 'reminders:send {--dry-run : Show what would be sent without actually sending}';
    protected $description = 'Send SMS lesson reminders for lessons starting in ~30 hours';

    public function handle(SmsService $sms): int
    {
        $dryRun = $this->option('dry-run');

        // Find confirmed bookings that:
        // - start between 28 and 32 hours from now (30hr window with ±2hr buffer)
        // - haven't had a reminder sent yet
        // - have a phone number available
        $windowStart = now()->addHours(28);
        $windowEnd   = now()->addHours(32);

        $bookings = Booking::with(['client', 'student', 'timeSlot.rink'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('reminder_sent', false)
            ->where(function ($q) use ($windowStart, $windowEnd) {
                // Check both direct date/time and via time slot
                $q->whereBetween('start_time', [$windowStart->toTimeString(), $windowEnd->toTimeString()])
                  ->whereBetween('date', [$windowStart->toDateString(), $windowEnd->toDateString()]);
            })
            ->get()
            ->filter(function ($booking) use ($windowStart, $windowEnd) {
                // More precise check combining date + time
                $lessonAt = Carbon::parse(
                    ($booking->date ?? $booking->timeSlot?->date) . ' ' .
                    ($booking->start_time ?? $booking->timeSlot?->start_time)
                );
                return $lessonAt->between($windowStart, $windowEnd);
            });

        if ($bookings->isEmpty()) {
            $this->info('No reminders to send.');
            return 0;
        }

        $this->info("Found {$bookings->count()} reminder(s) to send.");

        foreach ($bookings as $booking) {
            $phone    = $booking->client?->phone ?? $booking->client_phone ?? null;
            $student  = $booking->student?->first_name ?? $booking->client_name ?? 'student';
            $lessonAt = Carbon::parse(
                ($booking->date ?? $booking->timeSlot?->date) . ' ' .
                ($booking->start_time ?? $booking->timeSlot?->start_time)
            );

            $this->line("  Booking #{$booking->id} — {$student} — {$lessonAt->format('M j g:i A')} — {$phone}");

            if ($dryRun) {
                $this->line('  [DRY RUN] Message: ' . $sms->buildReminderMessage($booking));
                continue;
            }

            if (!$phone) {
                $this->warn("  Skipping — no phone number for booking #{$booking->id}");
                continue;
            }

            $reminder = $sms->sendLessonReminder($booking);

            if ($reminder) {
                $this->info("  ✅ Sent to {$phone} (SID: {$reminder->twilio_sid})");
            } else {
                $this->error("  ❌ Failed to send for booking #{$booking->id}");
            }
        }

        return 0;
    }
}
