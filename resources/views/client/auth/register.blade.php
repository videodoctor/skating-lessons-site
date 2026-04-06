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
        <label class="block text-gray-700 font-bold mb-2">Phone <span class="font-normal text-gray-400 text-sm">(optional, for SMS reminders)</span></label>
        <input type="tel" name="phone" value="{{ old('phone') }}"
          placeholder="(314) 555-0000" oninput="formatPhone(this)"
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

      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">How did you hear about us? <span class="font-normal text-gray-400">(optional)</span></label>
        <input type="text" name="referred_by" value="{{ old('referred_by') }}" placeholder="e.g. Mike G., Google, Instagram"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
      </div>

      <div style="border-top:1px solid #f3f4f6;padding-top:1rem;margin-bottom:1rem;">
        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;padding:.6rem .75rem;border-radius:7px;background:#f8fafc;border:1.5px solid #e5eaf2;">
          <input type="checkbox" name="email_consent" id="reg_email_consent" required
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_email_consent" style="font-size:.85rem;color:#374151;line-height:1.5;">
            I agree to receive emails from Kristine Skates regarding my skating lessons, bookings, and account updates. *
          </label>
        </div>
        @error('email_consent')<p class="text-red-500 text-sm mb-2">{{ $message }}</p>@enderror

        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;padding:.6rem .75rem;border-radius:7px;background:#f0f4ff;border:1.5px solid #dbe4ff;">
          <input type="checkbox" name="sms_consent" id="reg_sms_consent" value="1"
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_sms_consent" style="font-size:.85rem;color:#374151;line-height:1.5;">
            <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders, booking confirmations, schedule changes, payment reminders, availability notifications, and public skate schedules.
            You will receive a confirmation text upon opting in.
            Message frequency varies. Message and data rates may apply.
            Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
            View our <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
          </label>
        </div>

        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;padding:.6rem .75rem;border-radius:7px;background:#fffbeb;border:1.5px solid #fde68a;">
          <input type="checkbox" name="waiver_accepted" id="reg_waiver" required
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_waiver" style="font-size:.85rem;color:#374151;line-height:1.5;">
            I have read and agree to the <a href="{{ route('waiver.show') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Liability Waiver</a>. I understand that skating involves inherent risks. *
          </label>
        </div>

        <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem .75rem;border-radius:7px;background:#f8fafc;border:1.5px solid #e5eaf2;">
          <input type="checkbox" name="terms_accepted" id="reg_terms" required
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_terms" style="font-size:.85rem;color:#374151;line-height:1.5;">
            I have read and agree to the <a href="{{ route('terms') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Terms &amp; Conditions</a>
            and <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>. *
          </label>
        </div>
      </div>

      {{-- Cloudflare Turnstile --}}
      <div class="mb-6">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light" data-callback="onTurnstilePass"></div>
        @error('captcha')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <button type="submit" id="register-submit" disabled class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-lg transition" style="opacity:.4;cursor:not-allowed;">
        Create Account
      </button>
      <script>
      function onTurnstilePass() {
        var btn = document.getElementById('register-submit');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
      }
      </script>
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
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer>function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6)      v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0)  v = '(' + v;
  input.value = v;
}
</script>
@endsection
{{-- NOTE: privacy policy link added to email consent checkbox above --}}
