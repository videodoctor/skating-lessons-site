<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLessonReminders extends Command
{
    protected $signature   = 'reminders:send {--dry-run : Show what would be sent without actually sending}';
    protected $description = 'Send SMS + email lesson reminders for lessons starting in ~30 hours';

    public function handle(SmsService $sms): int
    {
        $dryRun      = $this->option('dry-run');
        $windowStart = now()->addHours(28);
        $windowEnd   = now()->addHours(32);

        $bookings = Booking::with(['client', 'student', 'timeSlot.rink', 'service'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('reminder_sent', false)
            ->where(function ($q) use ($windowStart, $windowEnd) {
                $q->whereBetween('start_time', [$windowStart->toTimeString(), $windowEnd->toTimeString()])
                  ->whereBetween('date', [$windowStart->toDateString(), $windowEnd->toDateString()]);
            })
            ->get()
            ->filter(function ($booking) use ($windowStart, $windowEnd) {
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
            $phone    = $booking->client?->sms_phone ?? $booking->client?->phone ?? $booking->client_phone ?? null;
            $email    = $booking->client?->email ?? $booking->client_email ?? null;
            $student  = $booking->student?->first_name ?? $booking->client_name ?? 'there';
            $lessonAt = Carbon::parse(
                ($booking->date ?? $booking->timeSlot?->date) . ' ' .
                ($booking->start_time ?? $booking->timeSlot?->start_time)
            );

            $this->line("  Booking #{$booking->id} — {$student} — {$lessonAt->format('M j g:i A')}");

            if ($dryRun) {
                $this->line('  [DRY RUN] SMS: ' . $sms->buildReminderMessage($booking));
                $this->line('  [DRY RUN] Email to: ' . ($email ?? 'no email'));
                continue;
            }

            $smsSent = $emailSent = false;

            // ── SMS ───────────────────────────────────────────────────────────
            if ($phone) {
                // Only send SMS if phone is verified (A2P compliance)
                if ($booking->client && !$booking->client->phone_verified_at) {
                    $this->line('  — Phone not verified, skipping SMS (will send email only)');
                } else {
                    $reminder = $sms->sendLessonReminder($booking);
                    if ($reminder) {
                        $smsSent = true;
                        $this->info("  ✅ SMS → {$phone} (SID: {$reminder->twilio_sid})");
                    } else {
                        $this->warn("  ⚠ SMS failed for booking #{$booking->id}");
                    }
                }
            } else {
                $this->line('  — No phone, skipping SMS');
            }

            // ── Email ─────────────────────────────────────────────────────────
            if ($email) {
                try {
                    $this->sendEmailReminder($booking, $lessonAt, $email, $student);
                    $emailSent = true;
                    $this->info("  ✅ Email → {$email}");
                } catch (\Exception $e) {
                    Log::error("Email reminder failed booking #{$booking->id}: " . $e->getMessage());
                    $this->warn('  ⚠ Email failed: ' . $e->getMessage());
                }
            } else {
                $this->line('  — No email, skipping email');
            }

            if ($smsSent || $emailSent) {
                $booking->update(['reminder_sent' => true]);
            }
        }

        return 0;
    }

    private function sendEmailReminder(Booking $booking, Carbon $lessonAt, string $email, string $student): void
    {
        $rinkName    = $booking->timeSlot?->rink?->name ?? 'the rink';
        $price       = $booking->price_paid ? '$' . number_format($booking->price_paid, 2) : null;
        $code        = $booking->confirmation_code ?? '';
        $venmoHandle = ltrim(config('services.venmo.handle', 'Kristine-Humphrey'), '@');
        $venmoLink   = $price
            ? 'https://venmo.com/' . $venmoHandle . '?txn=pay&amount=' . ltrim($price, '$') . '&note=' . urlencode('Lesson ' . $code)
            : null;

        $when = $lessonAt->isTomorrow()
            ? 'tomorrow, ' . $lessonAt->format('F j \a\t g:i A')
            : $lessonAt->format('l, F j \a\t g:i A');

        $subject = "Reminder: Your skating lesson is {$when}";

        $venmoBlock = $venmoLink ? "
            <div style='text-align:center;margin:1.5rem 0;'>
              <a href='{$venmoLink}' style='display:inline-block;background:#3D95CE;color:#fff;font-weight:700;padding:.85rem 2rem;border-radius:8px;text-decoration:none;font-size:.95rem;'>
                💜 Pay {$price} via Venmo
              </a>
              <p style='font-size:.75rem;color:#9ca3af;margin-top:.5rem;'>
                Or send to @{$venmoHandle} and include <strong>{$code}</strong> in the note
              </p>
            </div>" : '';

        $priceRow = $price
            ? "<tr><td style='color:#6b7280;padding:4px 0;'>Amount Due</td><td style='font-weight:700;text-align:right;color:#065f46;'>{$price}</td></tr>"
            : '';
        $codeRow = $code
            ? "<tr><td style='color:#6b7280;padding:4px 0;'>Confirmation #</td><td style='font-weight:600;text-align:right;font-family:monospace;'>{$code}</td></tr>"
            : '';

        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
        <body style='font-family:Arial,sans-serif;background:#f8fafc;margin:0;padding:0;'>
          <div style='max-width:560px;margin:0 auto;padding:2rem 1rem;'>
            <div style='background:#001F5B;border-radius:12px 12px 0 0;padding:2rem;text-align:center;'>
              <div style='font-size:2.5rem;'>⛸️</div>
              <h1 style='color:#fff;font-size:1.6rem;margin:.5rem 0 0;'>Lesson Reminder</h1>
              <p style='color:rgba(255,255,255,.7);font-size:.9rem;margin:.4rem 0 0;'>Coach Kristine · kristineskates.com</p>
            </div>
            <div style='background:#fff;border-radius:0 0 12px 12px;padding:2rem;box-shadow:0 4px 24px rgba(0,31,91,.08);'>
              <p style='font-size:1rem;color:#374151;'>Hi {$student},</p>
              <p style='font-size:1rem;color:#374151;'>Your skating lesson is coming up <strong>{$when}</strong> at <strong>{$rinkName}</strong>.</p>
              <div style='background:#f0f4ff;border-radius:8px;padding:1rem 1.25rem;margin:1.25rem 0;'>
                <table style='width:100%;font-size:.88rem;border-collapse:collapse;'>
                  <tr><td style='color:#6b7280;padding:4px 0;'>Date &amp; Time</td><td style='font-weight:600;text-align:right;'>{$when}</td></tr>
                  <tr><td style='color:#6b7280;padding:4px 0;'>Location</td><td style='font-weight:600;text-align:right;'>{$rinkName}</td></tr>
                  {$priceRow}{$codeRow}
                </table>
              </div>
              {$venmoBlock}
              <div style='background:#fef3c7;border:1.5px solid #fcd34d;border-radius:8px;padding:.85rem 1rem;margin:1rem 0;font-size:.83rem;color:#92400e;'>
                ⚠️ <strong>Cancellation policy:</strong> Cancellations less than 24 hours before the lesson will be billed at the full rate.
              </div>
              <p style='font-size:.85rem;color:#374151;margin-top:1.25rem;'>See you on the ice! 🏒<br>
              <strong>Coach Kristine</strong><br>
              <a href='https://kristineskates.com' style='color:#001F5B;'>kristineskates.com</a> · 314-314-SKATE</p>
            </div>
            <p style='text-align:center;font-size:.72rem;color:#9ca3af;margin-top:1rem;'>© " . date('Y') . " Kristine Skates · St. Louis, MO</p>
          </div>
        </body></html>";

        Mail::send([], [], function ($msg) use ($email, $student, $subject, $html) {
            $msg->to($email, $student)->subject($subject)->html($html);
        });
    }
}
