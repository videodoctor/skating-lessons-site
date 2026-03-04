@extends('layouts.app')

@section('title', 'Select Time - ' . $service->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-2">{{ $service->name }}</h1>
    <p class="text-gray-600 mb-8">{{ $date->format('l, F j, Y') }}</p>

    @if($timeSlots->count() > 0)
        @foreach($timeSlots as $rinkId => $slots)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-blue-900 mb-4">{{ $slots->first()->rink->name }}</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    @foreach($slots as $slot)
                        <button type="button" 
                            onclick="selectSlot({{ $slot->id }}, '{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}')"
                            class="time-slot-btn px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-blue-900 hover:bg-blue-50 transition text-center font-semibold">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Booking Form (hidden until slot selected) -->
        <div id="booking-form" class="bg-white rounded-lg shadow-lg p-6 hidden">
            <h2 class="text-2xl font-bold text-blue-900 mb-4">Complete Your Request</h2>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-gray-700 mb-2"><strong>Service:</strong> {{ $service->name }}</p>
                <p class="text-gray-700 mb-2"><strong>Date:</strong> {{ $date->format('l, F j, Y') }}</p>
                <p class="text-gray-700 mb-2"><strong>Time:</strong> <span id="selected-time-display">-</span></p>
                <p class="text-gray-700"><strong>Price:</strong> ${{ number_format($service->price, 2) }}</p>
            </div>

            <form method="POST" action="{{ route('booking.submit') }}">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                <input type="hidden" name="time_slot_id" id="selected_slot_id">

                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Your Name *</label>
                        <input type="text" name="client_name" required
                            value="{{ $client ? $client->name : old('client_name') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Phone Number *</label>
                        <input type="tel" name="client_phone" required
                            value="{{ $client ? $client->phone : old('client_phone') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Contact Email *</label>
                    <input type="email" name="client_email" required
                        value="{{ $client ? $client->email : old('client_email') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900"
                        placeholder="Any special requests or information for Coach Kristine...">{{ old('notes') }}</textarea>
                </div>

                <!-- Email Consent -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="email_consent" required class="mt-1 mr-3">
                        <span class="text-gray-700 text-sm">
                            I agree to receive emails regarding this lesson request and booking updates. *
                        </span>
                    </label>
                    @error('email_consent')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Cancellation Policy -->
                <div class="mb-6">
                    <label class="flex items-start">
                    <input type="checkbox" name="cancellation_policy" class="mt-1 mr-2" required>
                    <span class="text-sm text-gray-700">
                        I understand that if this lesson request is approved, I am responsible for payment by the end of the lesson unless other arrangements have been approved. Cancellations less than 24 hours before the lesson time slot will be invoiced for the full price of the lesson. *
                    </span>
                </label>
                @error('cancellation_policy')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>
                <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-4 rounded-lg text-lg transition">
                    Request This Time Slot
                </button>
            </form>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <p class="text-xl text-gray-600 mb-4">No available time slots for this date.</p>
            <a href="{{ route('booking.select-date', $service) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                Choose Another Date
            </a>
        </div>
    @endif
</div>

<script>
function selectSlot(slotId, timeDisplay) {
    // Remove selection from all buttons
    document.querySelectorAll('.time-slot-btn').forEach(btn => {
        btn.classList.remove('border-blue-900', 'bg-blue-100');
        btn.classList.add('border-gray-300');
    });
    
    // Highlight selected button
    event.target.classList.remove('border-gray-300');
    event.target.classList.add('border-blue-900', 'bg-blue-100');
    
    // Set hidden field value
    document.getElementById('selected_slot_id').value = slotId;
    document.getElementById('selected-time-display').textContent = timeDisplay;
    
    // Show form
    document.getElementById('booking-form').classList.remove('hidden');
    
    // Smooth scroll to form
    setTimeout(() => {
        document.getElementById('booking-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}
</script>
@endsection
