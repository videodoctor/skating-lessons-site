@extends('layouts.app')

@section('title', 'Select Date - Kristine Skates')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="mb-8">
        <a href="/book" class="text-blue-900 hover:underline">← Back to Services</a>
    </div>

    <h1 class="text-3xl font-bold text-blue-900 mb-2">Select a Date</h1>
    <p class="text-gray-600 mb-2">Service: <strong>{{ $service->name }}</strong></p>
    <p class="text-gray-600 mb-8">Duration: {{ $service->duration }} minutes • Price: ${{ number_format($service->price, 2) }}</p>

    <div class="bg-white rounded-lg shadow-md p-6">
        @if($availableDates->isEmpty())
            <p class="text-gray-500 text-center py-8">No available dates at this time. Please check back later.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @forelse($availableDates as $date)
                    <a href="/book/service/{{ $service->id }}/date/{{ $date->format('Y-m-d') }}"
                        class="block p-6 bg-white rounded-lg shadow hover:shadow-xl border-2 border-transparent hover:border-blue-900 transition text-center">
                        <div class="text-3xl font-bold text-blue-900 mb-1">{{ $date->format('d') }}</div>
                        <div class="text-gray-600">{{ $date->format('M') }}</div>
                        <div class="text-sm text-gray-500">{{ $date->format('l') }}</div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-xl text-gray-600">No available time slots in the next 60 days.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
@endsection
