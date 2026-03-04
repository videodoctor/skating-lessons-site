@extends('layouts.app')

@section('title', 'Booking Confirmed - Kristine Skates')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-3xl font-bold text-blue-900 mb-4">Booking Request Received!</h1>
        
        <div class="bg-blue-50 border-2 border-blue-900 rounded-lg p-6 mb-6 text-left">
            <h2 class="font-bold text-blue-900 mb-3">Booking Details:</h2>
            <p class="mb-2"><strong>Service:</strong> {{ $booking->service->name }}</p>
            <p class="mb-2"><strong>Date:</strong> {{ $booking->timeSlot->date->format('l, F j, Y') }}</p>
            <p class="mb-2"><strong>Time:</strong> {{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}</p>
            <p class="mb-2"><strong>Location:</strong> {{ $booking->timeSlot->rink->name }}</p>
            <p class="mb-2"><strong>Price:</strong> ${{ number_format($booking->price_paid, 2) }}</p>
            <p class="mb-2"><strong>Status:</strong> <span class="text-yellow-600 font-bold">Pending Approval</span></p>
        </div>

        <div class="text-left bg-gray-50 p-6 rounded-lg mb-6">
            <h3 class="font-bold text-gray-900 mb-3">What's Next?</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Coach Kristine will review your request within 24 hours</li>
                <li>You'll receive an email confirmation once approved</li>
                <li>Payment details will be included in the confirmation email</li>
                <li>Arrive 10 minutes early with your skates and gear!</li>
            </ol>
        </div>

        <div class="text-sm text-gray-500 mb-6">
            A confirmation email has been sent to: <strong>{{ $booking->client_email }}</strong>
        </div>

        <a href="/" class="inline-block bg-blue-900 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-800 transition">
            Return to Home
        </a>
    </div>
</div>
@endsection
