<?php

namespace App\Http\Controllers\Admin;

use App\Notifications\BookingApprovedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Student;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Booking::with(['service', 'timeSlot.rink', 'client', 'student.client'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $bookings = $query->paginate(20);
        $clients  = Client::orderBy('name')->get(['id', 'first_name', 'last_name', 'name', 'email']);

        return view('admin.bookings.index', compact('bookings', 'status', 'clients'));
    }

    public function approve(Booking $booking)
    {
        $booking->update(['status' => 'confirmed']);

        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingApprovedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingApprovedNotification($booking));
        }

        return back()->with('success', 'Booking approved successfully!');
    }

    public function reject(Booking $booking)
    {
        if ($booking->timeSlot) {
            $booking->timeSlot->update(['booking_id' => null, 'is_available' => true]);
        }

        $booking->update(['status' => 'rejected']);

        if ($booking->client_id && $booking->client->email) {
            $booking->client->notify(new BookingRejectedNotification($booking));
        } elseif ($booking->client_email) {
            \Illuminate\Support\Facades\Notification::route('mail', $booking->client_email)
                ->notify(new BookingRejectedNotification($booking));
        }

        return back()->with('success', 'Booking rejected and time slot released.');
    }

    public function linkClient(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        $client = Client::findOrFail($validated['client_id']);

        // Update booking with client info
        $booking->update([
            'client_id'    => $client->id,
            'client_name'  => $client->full_name,
            'client_email' => $client->email,
            'client_phone' => $client->phone ?? $booking->client_phone,
        ]);

        // If booking has a student, link student to client too if not already linked
        if ($booking->student && !$booking->student->client_id) {
            $booking->student->update(['client_id' => $client->id]);
        }

        return back()->with('success', "Booking linked to {$client->full_name}.");
    }

    public function markCashPaid(Request $request, Booking $booking)
    {
        $booking->update([
            'payment_type'   => 'cash',
            'payment_status' => 'paid',
            'cash_paid_at'   => now(),
            'cash_marked_by' => auth()->user()->name ?? 'admin',
        ]);

        return back()->with('success', 'Booking marked as cash paid.');
    }

    public function markVenmoPaid(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'venmo_username' => 'nullable|string',
        ]);

        $booking->update([
            'payment_type'       => 'venmo',
            'payment_status'     => 'paid',
            'venmo_confirmed_at' => now(),
            'venmo_username'     => $validated['venmo_username'] ?? null,
        ]);

        return back()->with('success', 'Booking marked as Venmo paid.');
    }

    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);
        if ($booking->time_slot_id) {
            \App\Models\TimeSlot::where('id', $booking->time_slot_id)
                ->update(['is_available' => true, 'booking_id' => null]);
        }
        return back()->with('success', "Booking for " . ($booking->student?->first_name ?? $booking->client_name) . " marked as cancelled.");
    }

    // ── Suggest a different time slot ─────────────────────────────────────────

    public function suggestTime(Request $request, Booking $booking)
    {
        $request->validate([
            'suggested_time_slot_id' => 'required|exists:time_slots,id',
            'suggestion_message'     => 'nullable|string|max:500',
        ]);

        $slot = \App\Models\TimeSlot::with('rink')->findOrFail($request->suggested_time_slot_id);

        // Generate a unique token for accept/decline links
        $token = bin2hex(random_bytes(24));

        $booking->update([
            'status'                 => 'suggestion_pending',
            'suggested_time_slot_id' => $slot->id,
            'suggestion_message'     => $request->suggestion_message,
            'suggestion_token'       => $token,
            'suggestion_sent_at'     => now(),
        ]);

        // Send email
        $this->sendSuggestionEmail($booking, $slot, $token);

        // Send SMS if opted in
        if ($booking->client?->sms_phone && $booking->client?->phone_verified_at) {
            $this->sendSuggestionSms($booking, $slot);
        }

        return back()->with('success', 'Time suggestion sent to ' . $booking->client_name . '!');
    }

    public function slotsForDate(Request $request)
    {
        $date = $request->query('date');
        if (!$date) return response()->json([]);

        $slots = \App\Models\TimeSlot::with('rink')
            ->whereDate('date', $date)
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'time'       => \Carbon\Carbon::parse($s->start_time)->format('g:i A'),
                'rink'       => $s->rink?->name ?? 'Unknown Rink',
                'label'      => \Carbon\Carbon::parse($s->start_time)->format('g:i A') . ' — ' . ($s->rink?->name ?? ''),
            ]);

        return response()->json($slots);
    }

    public function acceptSuggestion(Request $request, string $token)
    {
        $booking = \App\Models\Booking::where('suggestion_token', $token)
            ->where('status', 'suggestion_pending')
            ->firstOrFail();

        $oldSlot  = $booking->timeSlot;
        $newSlot  = $booking->suggestedTimeSlot;

        if (!$newSlot || !$newSlot->is_available) {
            return view('booking.suggestion-response', [
                'success' => false,
                'message' => 'Sorry, that time slot is no longer available. Please contact Coach Kristine directly.',
            ]);
        }

        // Release old slot
        if ($oldSlot) {
            $oldSlot->update(['is_available' => true, 'booking_id' => null]);
        }

        // Claim new slot
        $newSlot->update(['is_available' => false, 'booking_id' => $booking->id]);

        $booking->update([
            'time_slot_id'            => $newSlot->id,
            'date'                    => $newSlot->date,
            'start_time'              => $newSlot->start_time,
            'status'                  => 'confirmed',
            'suggested_time_slot_id'  => null,
            'suggestion_token'        => null,
            'suggestion_responded_at' => now(),
        ]);

        return view('booking.suggestion-response', [
            'success' => true,
            'message' => 'Your lesson has been confirmed for ' .
                \Carbon\Carbon::parse($newSlot->date)->format('l, F j') . ' at ' .
                \Carbon\Carbon::parse($newSlot->start_time)->format('g:i A') . ' at ' .
                ($newSlot->rink?->name ?? 'the rink') . '. See you on the ice!',
            'booking' => $booking,
        ]);
    }

    public function declineSuggestion(Request $request, string $token)
    {
        $booking = \App\Models\Booking::where('suggestion_token', $token)
            ->where('status', 'suggestion_pending')
            ->firstOrFail();

        $booking->update([
            'status'                  => 'pending',
            'suggested_time_slot_id'  => null,
            'suggestion_token'        => null,
            'suggestion_responded_at' => now(),
        ]);

        return view('booking.suggestion-response', [
            'success' => false,
            'message' => 'You have declined the suggested time. Coach Kristine will be in touch to find another time that works.',
            'declined' => true,
        ]);
    }

    private function sendSuggestionEmail(Booking $booking, \App\Models\TimeSlot $slot, string $token)
    {
        $acceptUrl  = url("/booking/suggestion/{$token}/accept");
        $declineUrl = url("/booking/suggestion/{$token}/decline");
        $date       = \Carbon\Carbon::parse($slot->date)->format('l, F j, Y');
        $time       = \Carbon\Carbon::parse($slot->start_time)->format('g:i A');
        $rink       = $slot->rink?->name ?? 'the rink';

        $html = view('emails.suggestion', compact(
            'booking', 'slot', 'date', 'time', 'rink', 'acceptUrl', 'declineUrl'
        ))->render();

        try {
            app(\App\Mail\MicrosoftGraphTransport::class)->sendRaw(
                to: $booking->client_email,
                subject: "Coach Kristine suggests a new lesson time",
                html: $html
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Suggestion email failed: " . $e->getMessage());
        }
    }

    private function sendSuggestionSms(Booking $booking, \App\Models\TimeSlot $slot)
    {
        $date  = \Carbon\Carbon::parse($slot->date)->format('D M j');
        $time  = \Carbon\Carbon::parse($slot->start_time)->format('g:i A');
        $rink  = str_replace(['Ice Arena', 'Ice Rink', 'Hockey Center'], '', $slot->rink?->name ?? '');
        $token = $booking->suggestion_token;

        $msg = "Coach Kristine suggests a new lesson time: {$date} at {$time} at " . trim($rink) . ". " .
               "Accept: " . url("/booking/suggestion/{$token}/accept") . " " .
               "Decline: " . url("/booking/suggestion/{$token}/decline") . " — Kristine Skates";

        try {
            app(\App\Services\SmsService::class)->sendRaw(
                $booking->client->sms_phone,
                $msg
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Suggestion SMS failed: " . $e->getMessage());
        }
    }
}