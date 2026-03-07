<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);
        
        if ($request->password === config('app.admin_password')) {
            session(['admin_authenticated' => true]);
            return redirect()->route('admin.dashboard');
        }
        
        return back()->withErrors(['password' => 'Invalid password']);
    }
    
    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect()->route('admin.login');
    }
    
    public function dashboard()
    {
        $stats = [
            'today' => Booking::whereDate('date', today())->count(),
            'upcoming' => Booking::where('date', '>=', today())
                ->whereIn('status', ['pending', 'confirmed', 'paid'])
                ->count(),
            'pending_payment' => Booking::where('payment_status', 'pending')->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')
                ->sum('price_paid'),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
    
    public function bookings()
    {
        $bookings = Booking::with(['client', 'service'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(50);
        
        return view('admin.bookings', compact('bookings'));
    }
    
    public function markPaid($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
        
        return back()->with('success', 'Booking marked as paid');
    }
    
    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Free up the time slot
        $booking->timeSlot->update([
            'is_available' => true,
            'booking_id' => null,
        ]);
        
        $booking->update(['status' => 'cancelled']);
        
        return back()->with('success', 'Booking cancelled');
    }
    
    public function schedule()
    {
        // TODO: Schedule management view
        return view('admin.schedule');
    }
}
