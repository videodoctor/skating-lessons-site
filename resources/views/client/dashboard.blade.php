@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-blue-900">My Dashboard</h1>
        <div>
            <span class="text-gray-600">Welcome, {{ Auth::guard('client')->user()->name }}!</span>
            <form method="POST" action="{{ route('client.logout') }}" class="inline ml-4">
                @csrf
                <button type="submit" class="text-blue-900 hover:underline font-bold">Logout</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-blue-900 mb-4">Quick Actions</h3>
            <a href="/book" class="block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded text-center transition mb-3">
                Request a New Lesson
            </a>
            @php $calToken = Auth::guard('client')->user()->calendar_token; @endphp
            @if($calToken)
            <a href="webcal://kristineskates.com/my/lessons.ics?token={{ $calToken }}"
               class="block bg-blue-50 hover:bg-blue-100 text-blue-900 font-bold py-3 px-4 rounded text-center transition border border-blue-200">
                📅 Subscribe to My Lesson Calendar
            </a>
            <p class="text-xs text-gray-400 text-center mt-1">Opens in Apple Calendar, Google Calendar, or Outlook</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-blue-900 mb-2">Account Info</h3>
            <p class="text-gray-600 mb-1"><strong>Email:</strong> {{ Auth::guard('client')->user()->email }}</p>
            <p class="text-gray-600"><strong>Phone:</strong> {{ Auth::guard('client')->user()->phone }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-blue-900 mb-6">My Lesson Requests</h2>
        
        @if($bookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rink</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $booking->date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td>
                            <td class="px-6 py-4">{{ $booking->service->name }}</td>
                            <td class="px-6 py-4">{{ $booking->timeSlot->rink->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($booking->status === 'confirmed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                @elseif($booking->status === 'cancelled')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ number_format($booking->price_paid, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">You haven't requested any lessons yet.</p>
            <a href="/book" class="inline-block mt-4 bg-blue-900 hover:bg-blue-800 text-white font-bold py-2 px-6 rounded transition">
                Request Your First Lesson
            </a>
        @endif
    </div>
</div>
@endsection
