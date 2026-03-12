<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\VenmoPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class VenmoAdminController extends Controller
{
    public function index()
    {
        $payments = VenmoPayment::with(['booking.student', 'client'])
            ->orderByDesc('paid_at')
            ->paginate(30);

        $stats = [
            'total'        => VenmoPayment::count(),
            'total_amount' => VenmoPayment::sum('amount'),
            'matched'      => VenmoPayment::where('match_status', 'matched')->count(),
            'unmatched'    => VenmoPayment::where('match_status', 'unmatched')->count(),
        ];

        return view('admin.venmo', compact('payments', 'stats'));
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
        $validated = $request->validate([
            'confirmation_code' => 'required|string',
        ]);

        $booking = Booking::where('confirmation_code', strtoupper(trim($validated['confirmation_code'])))->first();

        if (!$booking) {
            return back()->withErrors(['confirmation_code' => 'No booking found with that confirmation code.']);
        }

        $payment->update([
            'booking_id'   => $booking->id,
            'client_id'    => $booking->client_id,
            'match_status' => 'matched',
        ]);

        // Mark booking as venmo paid
        $booking->update([
            'payment_type'       => 'venmo',
            'payment_status'     => 'paid',
            'venmo_confirmed_at' => now(),
        ]);

        return redirect()->route('admin.venmo.index')
            ->with('success', "Payment from {$payment->sender_name} linked to booking #{$booking->confirmation_code} and marked as paid.");
    }
}
