<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Booking;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');

        $clients = Client::withCount('bookings')
            ->withSum('bookings', 'price_paid')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(30);

        // Also handle guest bookings (no client_id)
        $guestCount = Booking::whereNull('client_id')->distinct('client_email')->count('client_email');

        return view('admin.clients.index', compact('clients', 'search', 'guestCount'));
    }

    public function show(Client $client)
    {
        $bookings = $client->bookings()->with(['service', 'timeSlot.rink'])
            ->orderByDesc('created_at')->get();

        return view('admin.clients.show', compact('client', 'bookings'));
    }
}
