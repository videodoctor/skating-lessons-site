<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\SmsService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Services\ActivityLogger;

class ClientAuthController extends Controller
{
    public function showRegister()
    {
        return view('client.auth.register');
    }

    public function register(Request $request, VerificationService $verification, SmsService $sms)
    {
        $turnstile = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);
        if (!$turnstile->json('success')) {
            return back()->withErrors(['captcha' => 'Security check failed. Please try again.'])->withInput();
        }

        $validated = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'email'            => 'required|email|unique:clients,email',
            'phone'            => 'nullable|string',
            'password'         => 'required|string|min:8|confirmed',
            'referred_by'      => 'nullable|string|max:255',
            'email_consent'    => 'required|accepted',
            'waiver_accepted'  => 'required|accepted',
            'terms_accepted'   => 'required|accepted',
        ]);

        $normalizedPhone = !empty($validated['phone']) ? $sms->normalizePhone($validated['phone']) : '';
        $smsConsent      = $request->boolean('sms_consent');

        $client = Client::create([
            'first_name'        => $validated['first_name'],
            'last_name'         => $validated['last_name'] ?? null,
            'name'              => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'             => $validated['email'],
            'phone'             => $normalizedPhone,
            'password'          => Hash::make($validated['password']),
            'email_consent_at'  => now(),
            'terms_accepted_at' => now(),
            'sms_consent'       => $smsConsent,
            'sms_phone'         => $smsConsent ? $normalizedPhone : null,
        ]);

        // Sign waiver
        \App\Models\LiabilityWaiver::create([
            'client_id'      => $client->id,
            'waiver_version' => \App\Models\LiabilityWaiver::CURRENT_VERSION,
            'waiver_text'    => \App\Models\LiabilityWaiver::currentWaiverText(),
            'signed_name'    => $client->full_name,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'signed_at'      => now(),
        ]);
        $client->update([
            'waiver_signed_at' => now(),
            'waiver_version'   => \App\Models\LiabilityWaiver::CURRENT_VERSION,
            'waiver_ip'        => $request->ip(),
        ]);

        // Persist UTM/referral attribution from session + form
        $client->update(array_filter([
            'referral_source' => $validated['referred_by'] ?? session('analytics.ref'),
            'utm_source'      => session('analytics.utm_source'),
            'utm_medium'      => session('analytics.utm_medium'),
            'utm_campaign'    => session('analytics.utm_campaign'),
        ]));

        ActivityLogger::log($client->id, 'register', "{$client->full_name} created account");

        // Send email verification
        $verification->sendEmailVerification($client);

        // Send phone verification SMS if opted in, then opt-in confirmation
        if ($smsConsent && $client->sms_phone) {
            $sms->sendOptInConfirmation($client->sms_phone);
            $verification->sendPhoneVerification($client, $sms);
        }

        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard')
            ->with('success', 'Account created! Please check your email to verify your address.'
                . ($smsConsent ? ' A 6-digit code was sent to your phone to verify your number.' : ''));
    }

    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('client')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $client = Auth::guard('client')->user();
            $client->update(['last_login_at' => now()]);
            ActivityLogger::log($client->id, 'login', "{$client->full_name} logged in");
            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ── First-login terms acceptance (admin-created accounts) ────────────────

    public function showAcceptTerms()
    {
        return view('client.auth.accept-terms');
    }

    public function acceptTerms(Request $request, SmsService $sms)
    {
        $client = auth('client')->user();
        $needsPassword = $client->must_accept_terms && !$client->terms_accepted_at;

        $rules = [];
        if ($needsPassword) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        if (!$client->email_consent_at) {
            $rules['email_consent'] = 'required|accepted';
        }
        if (!$client->terms_accepted_at) {
            $rules['terms_accepted'] = 'required|accepted';
        }
        if (!$client->hasSignedCurrentWaiver()) {
            $rules['waiver_accepted'] = 'required|accepted';
        }
        $request->validate($rules);

        $updates = [];
        $smsConsent = $request->boolean('sms_consent');
        $normalizedPhone = $client->phone ? $sms->normalizePhone($client->phone) : null;

        if ($needsPassword) {
            $updates['password'] = Hash::make($request->password);
            $updates['must_accept_terms'] = false;
        }
        if (!$client->email_consent_at) {
            $updates['email_consent_at'] = now();
        }
        if (!$client->terms_accepted_at) {
            $updates['terms_accepted_at'] = now();
        }
        if (!$client->sms_consent && $smsConsent) {
            $updates['sms_consent'] = true;
            $updates['sms_phone'] = $normalizedPhone;
        }

        $client->update($updates);

        // Sign waiver if accepted
        if ($request->has('waiver_accepted')) {
            \App\Models\LiabilityWaiver::create([
                'client_id'       => $client->id,
                'waiver_version'  => \App\Models\LiabilityWaiver::CURRENT_VERSION,
                'waiver_text'     => \App\Models\LiabilityWaiver::currentWaiverText(),
                'signed_name'     => $client->full_name,
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'signed_at'       => now(),
            ]);
            $client->update([
                'waiver_signed_at' => now(),
                'waiver_version'   => \App\Models\LiabilityWaiver::CURRENT_VERSION,
                'waiver_ip'        => $request->ip(),
            ]);
        }

        if ($smsConsent && $client->sms_phone) {
            $sms->sendOptInConfirmation($client->sms_phone);
        }

        $desc = $needsPassword
            ? "{$client->full_name} accepted terms and set password"
            : "{$client->full_name} completed consent requirements";
        ActivityLogger::log($client->id, 'accept_terms', $desc);

        return redirect()->route('client.dashboard')
            ->with('success', 'Welcome! Your account is all set up.');
    }

    // ── Email verification ─────────────────────────────────────────────────────

    public function verifyEmail(string $token, VerificationService $verification)
    {
        $client = $verification->verifyEmail($token);

        if (!$client) {
            return redirect()->route('client.login')
                ->withErrors(['email' => 'Invalid or expired verification link. Please log in and request a new one.']);
        }

        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard')
            ->with('success', '✓ Email verified! Your account is fully set up.');
    }

    // ── Phone verification ─────────────────────────────────────────────────────

    public function showVerifyPhone()
    {
        return view('client.auth.verify-phone');
    }

    public function verifyPhone(Request $request, VerificationService $verification)
    {
        $client = Auth::guard('client')->user();
        if (!$client) return redirect()->route('client.login');

        $request->validate(['code' => 'required|string|size:6']);

        if ($verification->verifyPhone($client, $request->input('code'))) {
            return redirect()->route('client.dashboard')
                ->with('success', '✓ Phone verified! You\'ll now receive SMS lesson reminders.');
        }

        return back()->withErrors(['code' => 'Invalid or expired code. Codes expire after 10 minutes.']);
    }

    public function resendPhoneCode(VerificationService $verification, SmsService $sms)
    {
        $client = Auth::guard('client')->user();
        if (!$client || !$client->sms_phone) {
            return back()->withErrors(['code' => 'No phone number on file.']);
        }

        $sent = $verification->sendPhoneVerification($client, $sms);

        return back()->with($sent ? 'success' : 'error',
            $sent ? 'Verification code resent to your phone.' : 'Please wait at least 60 seconds before requesting a new code.');
    }
}
