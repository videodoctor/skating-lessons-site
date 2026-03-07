@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-8">Admin Dashboard</h1>
    
    <!-- Stats -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-yellow-50 border-2 border-yellow-500 rounded-lg p-6">
            <div class="text-3xl font-bold text-yellow-700">{{ $stats['pending'] }}</div>
            <div class="text-gray-600">Pending Approval</div>
        </div>
        <div class="bg-green-50 border-2 border-green-500 rounded-lg p-6">
            <div class="text-3xl font-bold text-green-700">{{ $stats['confirmed'] }}</div>
            <div class="text-gray-600">Confirmed Bookings</div>
        </div>
        <div class="bg-blue-50 border-2 border-blue-500 rounded-lg p-6">
            <div class="text-3xl font-bold text-blue-700">{{ $stats['upcoming'] }}</div>
            <div class="text-gray-600">Upcoming Lessons</div>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-blue-900">Recent Bookings</h2>
            <a href="{{ route('admin.bookings.index') }}" class="text-blue-900 hover:underline">View All →</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Client</th>
                        <th class="px-4 py-2 text-left">Service</th>
                        <th class="px-4 py-2 text-left">Date/Time</th>
                        <th class="px-4 py-2 text-left">Rink</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($recentBookings as $booking)
                    <tr>
                        <td class="px-4 py-3">{{ $booking->client_name }}</td>
                        <td class="px-4 py-3">{{ $booking->service->name }}</td>
                        <td class="px-4 py-3">
                            {{ $booking->timeSlot->date->format('M d') }}<br>
                            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $booking->timeSlot->rink->name }}</td>
                        <td class="px-4 py-3">
                            @if($booking->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Pending</span>
                            @elseif($booking->status === 'confirmed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">Confirmed</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">{{ ucfirst($booking->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
