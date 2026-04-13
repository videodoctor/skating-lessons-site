<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use App\Models\VenmoPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class VenmoAdminController extends Controller
{
    public function index(Request $request)
    {
        $showIgnored = $request->boolean('show_ignored');

        $needsAction = VenmoPayment::with(['booking.student', 'client'])
            ->whereIn('match_status', ['unmatched', 'client_only'])
            ->orderByDesc('paid_at')
            ->get();

        $resolved = VenmoPayment::with(['booking.student', 'client'])
            ->where('match_status', 'matched')
            ->when($showIgnored, fn($q) => $q->orWhere('match_status', 'ignored'))
            ->orderByDesc('paid_at')
            ->paginate(30);

        $stats = [
            'total'        => VenmoPayment::where('match_status', '!=', 'ignored')->count(),
            'total_amount' => VenmoPayment::where('match_status', '!=', 'ignored')->sum('amount'),
            'matched'      => VenmoPayment::whereIn('match_status', ['matched', 'client_only'])->count(),
            'unmatched'    => VenmoPayment::where('match_status', 'unmatched')->count(),
        ];

        $bookings = Booking::with('student')
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderByDesc('date')
            ->limit(100)
            ->get();

        $clients = Client::orderBy('first_name')->get();

        return view('admin.venmo', compact('needsAction', 'resolved', 'stats', 'showIgnored', 'bookings', 'clients'));
    }

    public function parseNow()
    {
        try {
            Artisan::call('venmo:parse-emails');
            $output = Artisan::output();
            return redirect()->route('admin.venmo.index')
                ->with('success', 'Venmo emails parsed. ' . substr(strip_tags($output), 0, 150));
        } catch (\Exception $e) {
            return redirect()->route('admin.venmo.index')
                ->with('error', 'Parser failed: ' . $e->getMessage());
        }
    }

    public function link(Request $request, VenmoPayment $payment)
    {
        $clientId   = $request->input('client_id');
        $bookingIds = array_filter($request->input('booking_ids', []));

        if (!$clientId && empty($bookingIds)) {
            return back()->withErrors(['client_id' => 'Please select a client, bookings, or both.']);
        }

        // Resolve client from selection or from first booking
        $client = $clientId ? Client::find($clientId) : null;

        // Link bookings and mark as paid
        $linkedCodes = [];
        if (!empty($bookingIds)) {
            $bookings = Booking::whereIn('id', $bookingIds)->get();
            foreach ($bookings as $booking) {
                $booking->update([
                    'payment_type'       => 'venmo',
                    'payment_status'     => 'paid',
                    'venmo_confirmed_at' => now(),
                ]);
                $linkedCodes[] = $booking->confirmation_code;
                // Use first booking's client if no client selected
                if (!$client && $booking->client_id) {
                    $client = $booking->client;
                }
            }
        }

        $payment->update([
            'booking_id'   => $bookingIds[0] ?? $payment->booking_id,
            'client_id'    => $client?->id,
            'match_status' => !empty($linkedCodes) ? 'matched' : 'client_only',
        ]);

        // Save sender name as Venmo alias for future auto-matching
        $aliasSaved = false;
        if ($client && $request->boolean('save_alias')) {
            $before = $client->venmo_aliases ?? [];
            $client->addVenmoAlias($payment->sender_name);
            $after = $client->fresh()->venmo_aliases ?? [];
            $aliasSaved = count($after) > count($before);
        }

        $clientName = $client?->full_name ?? 'unknown client';
        $msg = "Payment from {$payment->sender_name} linked to {$clientName}.";
        if (!empty($linkedCodes)) {
            $msg .= ' Bookings marked paid: ' . implode(', ', $linkedCodes) . '.';
        }
        if ($aliasSaved) {
            $msg .= " Alias \"{$payment->sender_name}\" saved for future auto-matching.";
        }

        return redirect()->route('admin.venmo.index')->with('success', $msg);
    }

    public function rematch()
    {
        $unmatched = VenmoPayment::where('match_status', 'unmatched')->get();
        $matched = 0;

        foreach ($unmatched as $payment) {
            $nameParts = explode(' ', $payment->sender_name);
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

            // Exact name match
            $client = Client::where('name', $payment->sender_name)
                ->orWhere(fn($q) => $q->where('first_name', $firstName)->where('last_name', $lastName))
                ->first();

            // Alias match
            if (!$client) {
                $senderLower = strtolower(trim($payment->sender_name));
                foreach (Client::whereNotNull('venmo_aliases')->get() as $c) {
                    foreach ($c->venmo_aliases ?? [] as $alias) {
                        if (strtolower($alias) === $senderLower) {
                            $client = $c;
                            break 2;
                        }
                    }
                }
            }

            if ($client) {
                $payment->update([
                    'client_id'    => $client->id,
                    'match_status' => 'client_only',
                ]);
                $matched++;
            }
        }

        $msg = $matched > 0
            ? "Re-matched {$matched} payment(s) to clients."
            : "No new matches found for " . $unmatched->count() . " unmatched payment(s).";

        return redirect()->route('admin.venmo.index')->with('success', $msg);
    }

    public function ignore(VenmoPayment $payment)
    {
        $payment->update(['match_status' => 'ignored']);
        return back()->with('success', "Payment from {$payment->sender_name} ignored.");
    }

    public function unignore(VenmoPayment $payment)
    {
        $payment->update(['match_status' => 'unmatched']);
        return back()->with('success', "Payment from {$payment->sender_name} restored.");
    }
}
