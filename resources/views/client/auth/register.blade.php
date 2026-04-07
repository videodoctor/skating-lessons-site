@extends('layouts.app')
@section('title', 'Create Account')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="max-w-2xl mx-auto py-12 px-4">
  <div class="bg-white rounded-lg shadow-lg p-8">
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#001F5B;margin:0 0 .5rem;text-align:center;">Create Your Account</h2>
    <p style="text-align:center;color:#6b7280;font-size:.88rem;margin-bottom:1.5rem;">Join Kristine Skates to book lessons and manage your family's skating.</p>

    @if($errors->any())
    <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('client.register') }}">
      @csrf

      {{-- Row 1: Name --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">First Name *</label>
          <input type="text" name="first_name" value="{{ old('first_name') }}" required
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
          @error('first_name')<p style="color:#dc2626;font-size:.75rem;margin-top:2px;">{{ $message }}</p>@enderror
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Last Name</label>
          <input type="text" name="last_name" value="{{ old('last_name') }}"
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
        </div>
      </div>

      {{-- Row 2: Email + Phone --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Email *</label>
          <input type="email" name="email" value="{{ old('email') }}" required
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
          @error('email')<p style="color:#dc2626;font-size:.75rem;margin-top:2px;">{{ $message }}</p>@enderror
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Phone <span style="color:#9ca3af;font-weight:400;">(optional, for SMS reminders)</span></label>
          <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="(314) 555-0000" oninput="regFormatPhone(this)"
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
        </div>
      </div>

      {{-- Row 3: Password --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Password *</label>
          <input type="password" name="password" required
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
          @error('password')<p style="color:#dc2626;font-size:.75rem;margin-top:2px;">{{ $message }}</p>@enderror
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Confirm Password *</label>
          <input type="password" name="password_confirmation" required
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
        </div>
      </div>

      {{-- Referred by --}}
      <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">How did you hear about us? <span style="color:#9ca3af;font-weight:400;">(optional)</span></label>
        <input type="text" name="referred_by" value="{{ old('referred_by') }}" placeholder="e.g. Mike G., Google, Instagram"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
      </div>

      {{-- Consent checkboxes --}}
      <div style="border-top:1px solid #f3f4f6;padding-top:1rem;margin-bottom:1rem;">
        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;padding:.6rem .75rem;border-radius:7px;background:#f8fafc;border:1.5px solid #e5eaf2;">
          <input type="checkbox" name="email_consent" id="reg_email_consent" required
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_email_consent" style="font-size:.82rem;color:#374151;line-height:1.5;">
            I agree to receive emails from Kristine Skates regarding my skating lessons, bookings, and account updates. *
          </label>
        </div>
        @error('email_consent')<p style="color:#dc2626;font-size:.75rem;margin-bottom:.5rem;">{{ $message }}</p>@enderror

        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;padding:.6rem .75rem;border-radius:7px;background:#f0f4ff;border:1.5px solid #dbe4ff;">
          <input type="checkbox" name="sms_consent" id="reg_sms_consent" value="1"
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_sms_consent" style="font-size:.82rem;color:#374151;line-height:1.5;">
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
          <label for="reg_waiver" style="font-size:.82rem;color:#374151;line-height:1.5;">
            I have read and agree to the <a href="{{ route('waiver.show') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Liability Waiver</a>. I understand that skating involves inherent risks. *
          </label>
        </div>

        <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem .75rem;border-radius:7px;background:#f8fafc;border:1.5px solid #e5eaf2;">
          <input type="checkbox" name="terms_accepted" id="reg_terms" required
                 style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="reg_terms" style="font-size:.82rem;color:#374151;line-height:1.5;">
            I have read and agree to the <a href="{{ route('terms') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Terms &amp; Conditions</a>
            and <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>. *
          </label>
        </div>
      </div>

      {{-- Turnstile --}}
      <div style="display:flex;justify-content:center;margin-bottom:1rem;">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light" data-callback="onTurnstilePass"></div>
        @error('captcha')<p style="color:#dc2626;font-size:.75rem;margin-top:.25rem;">{{ $message }}</p>@enderror
      </div>

      <button type="submit" id="register-submit" disabled
        style="width:100%;background:#001F5B;color:#fff;font-weight:700;font-size:1rem;padding:.85rem;border:none;border-radius:7px;cursor:not-allowed;opacity:.4;">
        Create Account
      </button>
    </form>

    <p style="text-align:center;color:#6b7280;font-size:.75rem;margin-top:1rem;">
      Already have an account? <a href="{{ route('client.login') }}" style="color:#001F5B;font-weight:700;text-decoration:none;">Login</a>
    </p>
  </div>
</div>

<script>
function onTurnstilePass() {
  var btn = document.getElementById('register-submit');
  btn.disabled = false;
  btn.style.opacity = '1';
  btn.style.cursor = 'pointer';
}
function regFormatPhone(input) {
  var v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0) v = '(' + v;
  input.value = v;
}
</script>
@endsection
