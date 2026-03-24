<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use App\Notifications\BookingRequestedNotification;
use App\Notifications\NewBookingNotification;
use App\Services\SmsService;
use App\Services\VerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    // Step 1: Select Service
    public function index()
    {
        $services = Service::where('is_active', true)->orderBy('price')->get();
        $comingSoonServices = Service::where('coming_soon', true)->orderBy('price')->get();
        return view('booking.index', compact('services', 'comingSoonServices'));
    }

    public function ajaxDates(Service $service)
    {
        $now   = Carbon::now();
        $dates = TimeSlot::where('is_available', true)
            ->whereNull('booking_id')
            ->whereBetween('date', [Carbon::today(), Carbon::today()->addDays(60)])
            ->whereHas('rink', fn($q) => $q->where('is_active', true))
            ->where(function ($q) use ($now) {
                $q->where('date', '>', $now->toDateString())
                  ->orWhere(fn($q2) => $q2->where('date', $now->toDateString())
                      ->where('start_time', '>', $now->format('H:i:s')));
            })
            ->select('date')->distinct()->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'));

        return response()->json($dates);
    }

    public function ajaxSlots(Service $service, $date)
    {
        $slots = TimeSlot::with('rink')
            ->where('is_available', true)
            ->whereNull('booking_id')
            ->whereDate('date', $date)
            ->whereHas('rink', fn($q) => $q->where('is_active', true))
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'id'        => $s->id,
                'time'      => Carbon::parse($s->start_time)->format('g:i A'),
                'end_time'  => Carbon::parse($s->end_time)->format('g:i A'),
                'rink'      => $s->rink?->name ?? '',
                'rink_id'   => $s->rink_id,
            ]);

        return response()->json($slots);
    }

    // Step 2: Select Date
    public function selectDate(Service $service)
    {
        $startDate = Carbon::today();
        $endDate   = Carbon::today()->addDays(60);
        $now       = Carbon::now();

        $availableDates = TimeSlot::where('is_available', true)
            ->whereNull('booking_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('rink', fn($q) => $q->where('is_active', true))
            ->where(function ($query) use ($now) {
                $query->where('date', '>', $now->toDateString())
                      ->orWhere(function ($q) use ($now) {
                          $q->where('date', '=', $now->toDateString())
                            ->where('start_time', '>', $now->format('H:i:s'));
                      });
            })
            ->select('date')->distinct()->pluck('date')
            ->map(fn($date) => Carbon::parse($date));

        return view('booking.select-date', compact('service', 'availableDates'));
    }

    // Step 3: Select Time & Rink
    public function selectTime(Service $service, $date)
    {
        $date = Carbon::parse($date);
        $now  = Carbon::now();

        $timeSlots = TimeSlot::with(['rink'])
            ->where('date', $date)
            ->where('is_available', true)
            ->whereNull('booking_id')
            ->whereHas('rink', fn($q) => $q->where('is_active', true))
            ->when($date->isToday(), fn($query) => $query->where('start_time', '>', $now->format('H:i:s')))
            ->orderBy('start_time')
            ->get()
            ->groupBy('rink_id');

        $client = Auth::guard('client')->user();
        return view('booking.select-time', compact('service', 'date', 'timeSlots', 'client'));
    }

    // Step 4: Submit Booking
    public function submit(Request $request, SmsService $sms)
    {
        // ── Turnstile verification (guests only — logged-in clients skip) ──────
        if (!Auth::guard('client')->check()) {
            $turnstile = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => config('services.turnstile.secret'),
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip(),
            ]);
            if (!$turnstile->json('success')) {
                return back()->withErrors(['captcha' => 'Security check failed. Please try again.'])->withInput();
            }
        }

        $validated = $request->validate([
            'service_id'          => 'required|exists:services,id',
            'time_slot_id'        => 'required|exists:time_slots,id',
            'client_name'         => 'required|string|max:255',
            'client_email'        => 'required|email',
            'client_phone'        => 'nullable|string',
            'notes'               => 'nullable|string',
            'email_consent'       => 'required|accepted',
            'cancellation_policy' => 'required|accepted',
        ]);

        // Check slot still available
        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
        if (!$timeSlot->is_available || $timeSlot->booking_id) {
            return back()->with('error', 'Sorry, that time slot is no longer available.');
        }

        $guestSmsConsent = $request->boolean('guest_sms_consent');
        $normalizedPhone = $validated['client_phone'] ? $sms->normalizePhone($validated['client_phone']) : null;

        // Create booking
        $booking = Booking::create([
            'client_id'          => Auth::guard('client')->id(),
            'service_id'         => $validated['service_id'],
            'time_slot_id'       => $validated['time_slot_id'],
            'client_name'        => $validated['client_name'],
            'client_email'       => $validated['client_email'],
            'client_phone'       => $normalizedPhone,
            'notes'              => $validated['notes'] ?? null,
            'status'             => 'pending',
            'price_paid'         => Service::find($validated['service_id'])->effectivePrice(),
            'date'               => $timeSlot->date,
            'email_consent_at'   => now(),
            'start_time'         => $timeSlot->start_time,
            'end_time'           => $timeSlot->end_time,
            'guest_sms_consent'  => $guestSmsConsent,
            'guest_convert_token'=> Str::random(32),
        ]);

        // Mark slot unavailable
        $timeSlot->update(['booking_id' => $booking->id, 'is_available' => false]);

        // Send opt-in confirmation SMS if guest opted in
        if ($guestSmsConsent && $normalizedPhone) {
            $sms->sendOptInConfirmation($normalizedPhone);
        }

        // Send confirmation email
        if ($booking->client_id && $booking->client?->email) {
            $booking->client->notify(new BookingRequestedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingRequestedNotification($booking));
        }

        // Notify admins
        User::all()->each(fn($admin) => $admin->notify(new NewBookingNotification($booking)));

        return redirect()->route('booking.confirmation', $booking);
    }

    // Step 5: Confirmation
    public function confirmation(Booking $booking)
    {
        // Check if guest can convert (not already a client account)
        $canConvert = !$booking->client_id
            && $booking->guest_convert_token
            && !Client::where('email', $booking->client_email)->exists();

        return view('booking.confirmation', compact('booking', 'canConvert'));
    }

    // Public payment page (linked from SMS)
    public function pay(string $code)
    {
        $booking = Booking::with(['service', 'timeSlot.rink'])
            ->where('confirmation_code', $code)
            ->firstOrFail();

        return view('booking.pay', compact('booking'));
    }

    // Step 6: Guest → Account conversion
    public function convertGuest(Request $request, VerificationService $verification)
    {
        $token   = $request->input('token');
        $booking = Booking::where('guest_convert_token', $token)->firstOrFail();

        // Already converted
        if ($booking->client_id) {
            return redirect()->route('client.dashboard')
                ->with('success', 'You already have an account!');
        }

        // Email already taken
        if (Client::where('email', $booking->client_email)->exists()) {
            return redirect()->route('client.login')
                ->with('error', 'An account with this email already exists. Please log in.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $nameParts = explode(' ', $booking->client_name, 2);
        $smsService = app(\App\Services\SmsService::class);

        $client = Client::create([
            'first_name'        => $nameParts[0],
            'last_name'         => $nameParts[1] ?? null,
            'name'              => $booking->client_name,
            'email'             => $booking->client_email,
            'phone'             => $booking->client_phone,
            'password'          => Hash::make($validated['password']),
            'email_consent_at'  => now(),
            'sms_consent'       => $booking->guest_sms_consent,
            'sms_phone'         => $booking->guest_sms_consent ? $booking->client_phone : null,
        ]);

        // Link booking to new client and clear conversion token
        $booking->update([
            'client_id'           => $client->id,
            'guest_convert_token' => null,
        ]);

        // Send email verification
        $verification->sendEmailVerification($client);

        // Send phone verification if SMS consent given
        if ($booking->guest_sms_consent && $client->sms_phone) {
            $verification->sendPhoneVerification($client, $smsService);
        }

        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard')
            ->with('success', 'Account created! Please check your email to verify your address.');
    }
}
