@extends('layouts.admin')

@section('title', 'Manage Bookings')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-8">Manage Bookings</h1>
    
    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-8">
            <a href="?status=all" class="pb-4 px-1 {{ $status === 'all' ? 'border-b-2 border-blue-900 text-blue-900 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                All
            </a>
            <a href="?status=pending" class="pb-4 px-1 {{ $status === 'pending' ? 'border-b-2 border-blue-900 text-blue-900 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                Pending
            </a>
            <a href="?status=confirmed" class="pb-4 px-1 {{ $status === 'confirmed' ? 'border-b-2 border-blue-900 text-blue-900 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                Confirmed
            </a>
            <a href="?status=cancelled" class="pb-4 px-1 {{ $status === 'cancelled' ? 'border-b-2 border-blue-900 text-blue-900 font-bold' : 'text-gray-500 hover:text-gray-700' }}">
                Cancelled
            </a>
        </nav>
    </div>
    
    @if(session('success'))
        <div class="bg-green-50 border-2 border-green-500 text-green-800 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Bookings List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rink</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $booking->client_name }}</div>
                        @if($booking->notes)
                            <div class="text-xs text-gray-500 mt-1">Note: {{ Str::limit($booking->notes, 50) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div>{{ $booking->client_email }}</div>
                        <div class="text-gray-500">{{ $booking->client_phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $booking->service->name }}</td>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium">{{ $booking->timeSlot->date->format('M d, Y') }}</div>
                        <div class="text-gray-500">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $booking->timeSlot->rink->name }}</td>
                    <td class="px-6 py-4 text-sm font-medium">${{ number_format($booking->price_paid, 2) }}</td>
                    <td class="px-6 py-4">
                        @if($booking->status === 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">Pending</span>
                        @elseif($booking->status === 'confirmed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">Confirmed</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($booking->status === 'pending')
                            <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 font-medium mr-3">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="inline" 
                                  onsubmit="return confirm('Reject this booking?')">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        No bookings found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
