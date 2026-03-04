@extends('layouts.app')

@section('title', 'Book a Lesson - Kristine Skates')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-blue-900 mb-2">Book Your Skating Lesson</h1>
    <p class="text-gray-600 mb-8">Choose a service to get started</p>

    <div class="grid md:grid-cols-2 gap-6">
        @foreach($services as $service)
        <a href="{{ route('booking.select-date', $service) }}" 
           class="block bg-white rounded-lg shadow-md hover:shadow-xl transition p-6 border-2 border-transparent hover:border-blue-900">
            <h3 class="text-xl font-bold text-blue-900 mb-2">{{ $service->name }}</h3>
            <p class="text-gray-600 mb-4">{{ $service->description }}</p>
            <div class="flex justify-between items-center">
                <span class="text-2xl font-bold text-blue-900">${{ number_format($service->price, 2) }}</span>
                <span class="text-gray-500">{{ $service->duration }} min</span>
            </div>
            @if($service->features)
            <ul class="mt-4 space-y-1 text-sm text-gray-600">
                @foreach($service->features ?? [] as $feature)
                <li>✓ {{ $feature }}</li>
                @endforeach
            </ul>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endsection
