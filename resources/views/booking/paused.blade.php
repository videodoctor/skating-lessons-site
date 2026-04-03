@extends('layouts.app')
@section('title', 'Book a Lesson — Kristine Skates')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;--ice:#E8F5FB;}
  .wl-label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:2px;}
  .wl-input{width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.87rem;font-family:inherit;}
  .wl-input:focus{outline:none;border-color:var(--navy);}
  .wl-row{margin-bottom:.65rem;}
  .wl-grid2{display:grid;grid-template-columns:1fr 1fr;gap:.65rem;}
  .policy-check{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;}
  .policy-check input{margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);}
  .policy-check label{font-size:.82rem;color:#374151;line-height:1.5;}
</style>

<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
  <div style="max-width:520px;width:100%;text-align:center;">
    <div style="font-size:3rem;margin-bottom:.5rem;">⛸️</div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:var(--navy);margin:0 0 .75rem;">Booking Is Paused</h1>
    <p style="color:#6b7280;font-size:1rem;line-height:1.6;margin-bottom:.5rem;">{{ str_replace(':month', now()->format('F'), $message) }}</p>
    @if($opensAt)
      <p style="color:var(--navy);font-weight:700;font-size:.95rem;margin-bottom:1.5rem;">
        Lessons resume: {{ \Carbon\Carbon::parse($opensAt)->format('F j, Y') }}
      </p>
    @endif

    @if(session('success'))
      <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:600;">
        {{ session('success') }}
      </div>
    @else
      <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:1.5rem;text-align:left;margin-top:1.5rem;">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--navy);margin:0 0 .75rem;">Join the Waitlist</h2>

        @if ($errors->any())
        <div style="background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b;padding:.6rem .85rem;border-radius:7px;margin-bottom:.75rem;font-size:.82rem;">
          @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('booking.interest') }}">
          @csrf

          {{-- Contact info --}}
          <div class="wl-row">
            <label class="wl-label">Your Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="wl-input" placeholder="Parent/guardian name">
            @error('name')<span style="color:#dc2626;font-size:.75rem;">{{ $message }}</span>@enderror
          </div>
          <div class="wl-row">
            <label class="wl-label">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="wl-input">
            @error('email')<span style="color:#dc2626;font-size:.75rem;">{{ $message }}</span>@enderror
          </div>
          <div class="wl-row">
            <label class="wl-label">Phone <span style="color:#9ca3af;">(for SMS reminders)</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" class="wl-input" placeholder="(314) 555-0000" oninput="formatPhone(this)">
          </div>

          {{-- Skater info --}}
          <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:8px;padding:.85rem 1rem;margin-bottom:.75rem;">
            <div style="font-weight:700;font-size:.85rem;color:#0c4a6e;margin-bottom:.5rem;">Skater Information</div>
            <div class="wl-row">
              <label class="wl-label">Skater Name *</label>
              <input type="text" name="student_name" value="{{ old('student_name') }}" required class="wl-input" placeholder="Name of the person skating">
            </div>
            <div class="wl-grid2">
              <div class="wl-row">
                <label class="wl-label">Skater Age *</label>
                <input type="number" name="student_age" value="{{ old('student_age') }}" required min="2" max="99" class="wl-input" placeholder="Age">
              </div>
              <div class="wl-row">
                <label class="wl-label">Skill Level *</label>
                <select name="skill_level" required class="wl-input">
                  <option value="">Select...</option>
                  <option value="beginner" {{ old('skill_level') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                  <option value="intermediate" {{ old('skill_level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                  <option value="advanced" {{ old('skill_level') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
              </div>
            </div>
          </div>

          {{-- Referred by --}}
          <div class="wl-row">
            <label class="wl-label">How did you hear about us? <span style="color:#9ca3af;">(optional)</span></label>
            <input type="text" name="referred_by" value="{{ old('referred_by') }}" class="wl-input" placeholder="e.g. Mike G., Google, Instagram">
          </div>

          {{-- Notes --}}
          <div class="wl-row">
            <label class="wl-label">Anything else? <span style="color:#9ca3af;">(optional)</span></label>
            <textarea name="message" rows="2" class="wl-input" placeholder="Goals, availability preferences, questions..."
              style="resize:vertical;">{{ old('message') }}</textarea>
          </div>

          {{-- Consent --}}
          <div style="border-top:1px solid #f3f4f6;padding-top:.75rem;margin-bottom:.75rem;">
            <div class="policy-check">
              <input type="checkbox" name="email_consent" id="email_consent" required>
              <label for="email_consent">I agree to receive email notifications about lesson availability from Kristine Skates. *</label>
            </div>
            <div class="policy-check" style="background:#f0f4ff;border:1.5px solid #dbe4ff;border-radius:7px;padding:.6rem .75rem;">
              <input type="checkbox" name="sms_consent" id="sms_consent" value="1">
              <label for="sms_consent">
                <strong>Optional:</strong> I agree to receive SMS text message notifications.
                Message frequency varies. Message and data rates may apply.
                Reply <strong>STOP</strong> to opt out. View our <a href="{{ route('privacy') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Privacy Policy</a>.
              </label>
            </div>
            <div class="policy-check" style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:7px;padding:.6rem .75rem;">
              <input type="checkbox" name="waiver_accepted" id="waiver_accepted" required>
              <label for="waiver_accepted">
                I have read and agree to the <a href="{{ route('waiver.show') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Liability Waiver</a>. I understand that skating involves inherent risks. *
              </label>
            </div>
            <div class="policy-check">
              <input type="checkbox" name="terms_accepted" id="terms_accepted" required>
              <label for="terms_accepted">
                I have read and agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Terms &amp; Conditions</a>
                and <a href="{{ route('privacy') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Privacy Policy</a>. *
              </label>
            </div>
          </div>

          {{-- Turnstile --}}
          <div style="display:flex;justify-content:center;margin-bottom:.75rem;">
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-callback="onTurnstilePass"></div>
          </div>

          <button type="submit" id="wl-submit" disabled style="width:100%;background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.7rem;font-size:.9rem;font-weight:700;cursor:pointer;opacity:.4;">
            Join Waitlist
          </button>
        </form>
      </div>
    @endif

    <a href="/" style="display:inline-block;margin-top:1.5rem;color:var(--navy);font-size:.85rem;text-decoration:none;">&larr; Back to Home</a>
  </div>
</div>

<script>
function onTurnstilePass() {
  const btn = document.getElementById('wl-submit');
  btn.disabled = false;
  btn.style.opacity = '1';
}

function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0) v = '(' + v;
  input.value = v;
}
</script>
@endsection
