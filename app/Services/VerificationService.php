<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VerificationService
{
    // ── Email verification ─────────────────────────────────────────────────────

    public function sendEmailVerification(Client $client): void
    {
        $token = Str::random(32);
        $client->update(['email_verify_token' => $token]);

        $verifyUrl = route('client.verify-email', ['token' => $token]);
        $name      = $client->first_name ?? 'there';

        $html = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;background:#f8fafc;margin:0;padding:0;'>
        <div style='max-width:520px;margin:0 auto;padding:2rem 1rem;'>
          <div style='background:#001F5B;border-radius:12px 12px 0 0;padding:1.5rem;text-align:center;'>
            <h1 style='color:#fff;font-size:1.4rem;margin:0;'>⛸️ Verify Your Email</h1>
          </div>
          <div style='background:#fff;border-radius:0 0 12px 12px;padding:2rem;box-shadow:0 4px 24px rgba(0,31,91,.08);'>
            <p style='color:#374151;'>Hi {$name},</p>
            <p style='color:#374151;'>Please verify your email address to complete your Kristine Skates account setup.</p>
            <div style='text-align:center;margin:1.5rem 0;'>
              <a href='{$verifyUrl}' style='background:#001F5B;color:#fff;padding:.85rem 2rem;border-radius:8px;text-decoration:none;font-weight:700;font-size:1rem;display:inline-block;'>
                ✓ Verify Email Address
              </a>
            </div>
            <p style='font-size:.82rem;color:#9ca3af;'>This link expires in 24 hours. If you didn't create a Kristine Skates account, you can ignore this email.</p>
          </div>
        </div></body></html>";

        Mail::send([], [], function ($msg) use ($client, $html) {
            $msg->to($client->email, $client->full_name)
                ->subject('Verify your email — Kristine Skates')
                ->html($html);
        });

        Log::info("Email verification sent to {$client->email}");
    }

    public function verifyEmail(string $token): ?Client
    {
        $client = Client::where('email_verify_token', $token)
            ->whereNull('email_verified_at')
            ->first();

        if (!$client) return null;

        $client->update([
            'email_verified_at'  => now(),
            'email_verify_token' => null,
        ]);

        return $client;
    }

    // ── Phone verification ─────────────────────────────────────────────────────

    public function sendPhoneVerification(Client $client, SmsService $sms): bool
    {
        if (!$client->sms_phone) return false;

        // Rate limit — don't resend within 60 seconds
        if ($client->phone_verify_sent_at && $client->phone_verify_sent_at->diffInSeconds(now()) < 60) {
            return false;
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $client->update([
            'phone_verify_code'    => $code,
            'phone_verify_sent_at' => now(),
        ]);

        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
            $twilio->messages->create($client->sms_phone, [
                'from' => config('services.twilio.from'),
                'body' => "Your Kristine Skates verification code is: {$code}. Reply STOP to opt out.",
            ]);
            Log::info("Phone verification sent to {$client->sms_phone}");
            return true;
        } catch (\Exception $e) {
            Log::error("Phone verification failed for client #{$client->id}: " . $e->getMessage());
            return false;
        }
    }

    public function verifyPhone(Client $client, string $code): bool
    {
        if (!$client->phone_verify_code) return false;
        if ($client->phone_verify_code !== $code) return false;

        // Code expires after 10 minutes
        if ($client->phone_verify_sent_at->diffInMinutes(now()) > 10) return false;

        $client->update([
            'phone_verified_at' => now(),
            'phone_verify_code' => null,
        ]);

        return true;
    }

    public function isEmailVerified(Client $client): bool
    {
        return $client->email_verified_at !== null;
    }

    public function isPhoneVerified(Client $client): bool
    {
        return $client->phone_verified_at !== null;
    }
}
