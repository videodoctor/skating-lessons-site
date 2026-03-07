@extends('layouts.app')
@section('title', 'Create Account')
@section('content')
<div class="max-w-md mx-auto py-12">
  <div class="bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-3xl font-bold text-blue-900 mb-6 text-center">Create Your Account</h2>

    @if($errors->any())
    <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
      @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('client.register') }}">
      @csrf

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;" class="mb-4">
        <div>
          <label class="block text-gray-700 font-bold mb-2">First Name *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('first_name') border-red-500 @enderror">
          @error('first_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-gray-700 font-bold mb-2">Last Name</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Email *</label>
        <input type="email" name="email" value="{{ old('email') }}" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('email') border-red-500 @enderror">
        @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Phone *</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" required
          placeholder="(314) 555-0000"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('phone') border-red-500 @enderror">
        @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Password *</label>
        <input type="password" name="password" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('password') border-red-500 @enderror">
        @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Confirm Password *</label>
        <input type="password" name="password_confirmation" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
      </div>

      <div class="mb-6">
        <label class="flex items-start">
          <input type="checkbox" name="email_consent" required class="mt-1 mr-3">
          <span class="text-gray-700 text-sm">
            I agree to receive emails from Kristine Skates regarding my skating lessons, bookings, and account updates. *
          </span>
        </label>
        @error('email_consent')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="mb-4">
        <label class="flex items-start">
          <input type="checkbox" name="sms_consent" class="mt-1 mr-3">
          <span class="text-gray-700 text-sm">
            I agree to receive SMS text message lesson reminders from Kristine Skates. Message and data rates may apply. Reply STOP at any time to opt out.
          </span>
        </label>
      </div>

      {{-- Cloudflare Turnstile --}}
      <div class="mb-6">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
        @error('captcha')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-lg transition">
        Create Account
      </button>
    </form>

    <p class="text-center text-gray-500 text-xs mt-4">
      By creating an account you agree to our
      <a href="{{ route('terms') }}" class="text-blue-900 hover:underline" target="_blank">Terms &amp; Conditions</a>
      and
      <a href="{{ route('privacy') }}" class="text-blue-900 hover:underline" target="_blank">Privacy Policy</a>.
    </p>

    <p class="text-center text-gray-600 mt-4">
      Already have an account?
      <a href="{{ route('client.login') }}" class="text-blue-900 hover:underline font-bold">Login</a>
    </p>
  </div>
</div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endsection
{{-- NOTE: privacy policy link added to email consent checkbox above --}}
