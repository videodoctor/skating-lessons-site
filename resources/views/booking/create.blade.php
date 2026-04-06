@extends('layouts.app')
@section('title', 'Book a Session')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-bold mb-8">Book a Session</h1>

    <form method="POST" action="{{ route('booking.store') }}" x-data="bookingForm()" class="space-y-8">
        @csrf

        <!-- Step 1: Select Service -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4">1. Select Service</h2>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($services as $service)
                <label class="cursor-pointer">
                    <input type="radio" name="service_id" value="{{ $service->id }}"
                           x-model="selectedServiceId" class="sr-only peer"
                           @if($selectedService && $selectedService->id === $service->id) checked @endif>
                    <div class="border-2 rounded-lg p-4 peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-blue-300">
                        <div class="font-bold text-lg">{{ $service->name }}</div>
                        @if($service->hasActiveDiscount())
                          <div class="my-2">
                            <span class="text-gray-400 line-through text-lg">${{ number_format($service->price, 0) }}</span>
                            <span class="text-2xl font-bold text-green-600 ml-1">${{ number_format($service->discountedPrice(), 0) }}</span>
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-0.5 rounded ml-1">⚡ {{ $service->discountLabel() }}</span>
                          </div>
                        @else
                          <div class="text-2xl font-bold text-blue-600 my-2">${{ number_format($service->price, 0) }}</div>
                        @endif
                        <div class="text-sm text-gray-600">{{ $service->duration_minutes }} minutes</div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('service_id')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
        </div>

        <!-- Step 2: Select Date & Time -->
        <div class="bg-white rounded-lg shadow-lg p-6" x-show="selectedServiceId">
            <h2 class="text-2xl font-bold mb-4">2. Select Date &amp; Time</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-semibold mb-2">Select Date</label>
                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="date in availableDates" :key="date">
                            <button type="button" @click="selectDate(date)"
                                    :class="selectedDate === date ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-blue-50'"
                                    class="p-2 rounded text-sm">
                                <div class="text-xs" x-text="formatDateShort(date)"></div>
                                <div class="font-bold" x-text="formatDay(date)"></div>
                            </button>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-2">Select Time</label>
                    <div x-show="!selectedDate" class="text-gray-500 text-center py-8">Select a date first</div>
                    <div x-show="selectedDate && timeSlots.length === 0" class="text-gray-500 text-center py-8">No available times for this date</div>
                    <div x-show="selectedDate && timeSlots.length > 0" class="grid grid-cols-2 gap-2 max-h-96 overflow-y-auto">
                        <template x-for="slot in timeSlots" :key="slot.id">
                            <button type="button" @click="selectTimeSlot(slot)"
                                    :class="selectedTimeSlotId === slot.id ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-blue-50'"
                                    class="p-3 rounded">
                                <span x-text="formatTime(slot.start_time)"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="time_slot_id" x-model="selectedTimeSlotId">
                </div>
            </div>
            @error('time_slot_id')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
        </div>

        <!-- Step 3: Your Information -->
        <div class="bg-white rounded-lg shadow-lg p-6" x-show="selectedTimeSlotId">
            <h2 class="text-2xl font-bold mb-4">3. Your Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-2">Your Name *</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600">
                    @error('client_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-semibold mb-2">Email *</label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" required
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600">
                    @error('client_email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-semibold mb-2">Phone *</label>
                    <input type="tel" name="client_phone" id="client_phone"
                           value="{{ old('client_phone') }}" required placeholder="(314) 555-0000"
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600"
                           oninput="formatPhone(this)">
                    @error('client_phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block font-semibold mb-2">Student Age *</label>
                    <input type="number" name="student_age" value="{{ old('student_age') }}" required min="3" max="99"
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600">
                    @error('student_age')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2">Student Name (if different from above)</label>
                    <input type="text" name="student_name" value="{{ old('student_name') }}"
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600">
                    @error('student_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2">Special Requests / Notes</label>
                    <textarea name="notes" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600">{{ old('notes') }}</textarea>
                    @error('notes')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- SMS Consent --}}
                <div class="md:col-span-2" style="background:#f0f4ff;border-radius:8px;padding:1rem 1.25rem;border:1.5px solid #dbe4ff;">
                    <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;">
                        <input type="checkbox" name="guest_sms_consent" value="1"
                               style="margin-top:3px;width:18px;height:18px;flex-shrink:0;"
                               {{ old('guest_sms_consent') ? 'checked' : '' }}>
                        <span style="font-size:.85rem;color:#374151;line-height:1.6;">
                            <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders, booking confirmations, schedule changes, payment reminders, availability notifications, and public skate schedules. You will receive a confirmation text upon opting in. Message frequency varies. Message and data rates may apply. Reply STOP to opt out or HELP for help. View our <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                {{-- Email + cancellation consent --}}
                <div class="md:col-span-2">
                    <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;">
                        <input type="checkbox" name="email_consent" value="1" required
                               style="margin-top:3px;width:18px;height:18px;flex-shrink:0;"
                               {{ old('email_consent') ? 'checked' : '' }}>
                        <span style="font-size:.85rem;color:#374151;line-height:1.6;">
                            I agree to receive booking confirmation and lesson reminder emails from Kristine Skates. *
                        </span>
                    </label>
                    @error('email_consent')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;">
                        <input type="checkbox" name="cancellation_policy" value="1" required
                               style="margin-top:3px;width:18px;height:18px;flex-shrink:0;"
                               {{ old('cancellation_policy') ? 'checked' : '' }}>
                        <span style="font-size:.85rem;color:#374151;line-height:1.6;">
                            I understand that cancellations less than 24 hours before the lesson will be billed at the full rate. *
                        </span>
                    </label>
                    @error('cancellation_policy')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div x-show="selectedTimeSlotId" class="text-center space-y-4">
            {{-- Turnstile captcha --}}
            <div style="display:flex;justify-content:center;">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}"></div>
            </div>
            @error('captcha')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
            <button type="submit" class="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-700">
                Submit Booking Request
            </button>
            <p class="text-xs text-gray-400">* Required. Your information is never sold or shared.</p>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function formatPhone(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 10);
    if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
    else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
    else if (v.length > 0) v = '(' + v;
    input.value = v;
}

function bookingForm() {
    return {
        selectedServiceId: {{ $selectedService ? $selectedService->id : 'null' }},
        selectedDate: null,
        selectedTimeSlotId: null,
        availableDates: [],
        timeSlots: [],
        init() { this.loadAvailableDates(); },
        async loadAvailableDates() {
            const response = await fetch('/api/available-dates');
            this.availableDates = await response.json();
        },
        async selectDate(date) {
            this.selectedDate = date;
            this.selectedTimeSlotId = null;
            const response = await fetch(`/api/time-slots/${date}`);
            this.timeSlots = await response.json();
        },
        selectTimeSlot(slot) { this.selectedTimeSlotId = slot.id; },
        formatDateShort(date) { return new Date(date).toLocaleDateString('en-US', { month: 'short' }); },
        formatDay(date) { return new Date(date).toLocaleDateString('en-US', { day: 'numeric' }); },
        formatTime(time) {
            return new Date('2000-01-01 ' + time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        }
    }
}
</script>
@endpush
