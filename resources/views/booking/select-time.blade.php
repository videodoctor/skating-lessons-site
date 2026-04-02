@extends('layouts.app')
@section('title', 'Select Time — ' . $service->name)
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;}
  body{font-family:'DM Sans',sans-serif;}
  .page-header{background:var(--navy);padding:3rem 0 2rem;}
  .page-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(2rem,5vw,3rem);color:#fff;line-height:1;}
  .breadcrumb{font-family:'Bebas Neue',sans-serif;letter-spacing:.2em;font-size:.85rem;color:rgba(255,255,255,.5);}
  .breadcrumb a{color:rgba(255,255,255,.5);}
  .breadcrumb span{color:rgba(255,255,255,.25);margin:0 .5rem;}
  .progress-bar{display:flex;gap:0;margin:0 auto;max-width:480px;}
  .progress-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
  .progress-step:not(:last-child)::after{content:'';position:absolute;top:16px;left:calc(50% + 16px);right:calc(-50% + 16px);height:2px;background:rgba(255,255,255,.15);}
  .step-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;border:2px solid rgba(255,255,255,.2);color:rgba(255,255,255,.4);background:transparent;position:relative;z-index:1;}
  .step-circle.active{border-color:#fff;color:#fff;background:var(--red);}
  .step-circle.done{border-color:var(--red);background:var(--red);color:#fff;}
  .step-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-top:5px;}
  .step-label.active,.step-label.done{color:rgba(255,255,255,.9);}
  /* Time slots */
  .rink-section{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,31,91,.06);border:1.5px solid #e5eaf2;padding:1.5rem;}
  .rink-name{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;color:var(--navy);}
  .time-slot-btn{padding:.7rem .5rem;border:2px solid #e5eaf2;border-radius:8px;background:#fff;
    color:var(--navy);font-weight:600;font-size:.95rem;cursor:pointer;transition:all .15s;text-align:center;}
  .time-slot-btn:hover{border-color:var(--navy);background:#eff6ff;}
  .time-slot-btn.selected{border-color:var(--red);background:var(--red);color:#fff;}
  /* Form */
  .booking-form{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,31,91,.06);border:1.5px solid #e5eaf2;padding:2rem;}
  .form-label{display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;}
  .form-input{width:100%;padding:.7rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.95rem;
    font-family:'DM Sans',sans-serif;transition:border-color .15s;}
  .form-input:focus{outline:none;border-color:var(--navy);}
  .submit-btn{width:100%;background:var(--navy);color:#fff;font-weight:700;font-size:1.05rem;
    padding:1rem;border-radius:8px;border:none;cursor:pointer;transition:background .2s;font-family:'DM Sans',sans-serif;}
  .submit-btn:hover{background:var(--red);}
  .summary-box{background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;padding:1rem 1.25rem;}
  .policy-check{display:flex;align-items:flex-start;gap:.75rem;}
  .policy-check input{margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);}
</style>

<div class="page-header">
  <div class="max-w-5xl mx-auto px-6">
    <p class="breadcrumb mb-3">
      <a href="/">Home</a><span>›</span>
      <a href="/book">Book</a><span>›</span>
      <a href="{{ route('booking.select-date', $service) }}">{{ $service->name }}</a><span>›</span>
      {{ $date->format('M j') }}
    </p>
    <h1 class="page-title mb-1">Choose a Time</h1>
    <p style="color:rgba(255,255,255,.6);font-size:.95rem" class="mb-6">{{ $date->format('l, F j, Y') }}</p>
    <div class="progress-bar mb-2">
      <div class="progress-step"><div class="step-circle done">✓</div><div class="step-label done">Service</div></div>
      <div class="progress-step"><div class="step-circle done">✓</div><div class="step-label done">Date</div></div>
      <div class="progress-step"><div class="step-circle active">3</div><div class="step-label active">Time</div></div>
      <div class="progress-step"><div class="step-circle">4</div><div class="step-label">Confirm</div></div>
    </div>
  </div>
</div>

<div class="max-w-5xl mx-auto px-6 py-10">
  @if($timeSlots->count() > 0)
  <div class="grid lg:grid-cols-5 gap-8">
    <!-- Left: slot selection -->
    <div class="lg:col-span-3 space-y-5">
      @foreach($timeSlots as $rinkId => $slots)
      <div class="rink-section">
        <h2 class="rink-name mb-1">{{ $slots->first()->rink->name }}</h2>
        @if($slots->first()->rink->address)
        <p class="text-gray-400 text-sm mb-4">📍 {{ $slots->first()->rink->address }}</p>
        @else
        <div class="mb-4"></div>
        @endif
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
          @foreach($slots as $slot)
          <button type="button"
            onclick="selectSlot({{ $slot->id }}, '{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}', '{{ $slots->first()->rink->name }}')"
            class="time-slot-btn" id="slot-{{ $slot->id }}">
            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
          </button>
          @endforeach
        </div>
      </div>
      @endforeach

      <div class="mt-4">
        <a href="{{ route('booking.select-date', $service) }}" class="text-sm text-gray-400 hover:text-gray-600">← Pick a different date</a>
      </div>
    </div>

    <!-- Right: booking form -->
    <div class="lg:col-span-2">
      <div id="form-placeholder" class="booking-form text-center py-12 text-gray-400">
        <div style="font-size:3rem" class="mb-3">👆</div>
        <p class="font-semibold text-gray-500">Select a time slot to continue</p>
      </div>

      <div id="booking-form" class="booking-form hidden">
        <h2 class="font-bold text-xl text-gray-900 mb-4">Complete Your Request</h2>

        <div class="summary-box mb-5">
          <div class="text-sm text-gray-600 space-y-1">
            <div><strong>Service:</strong> {{ $service->name }}</div>
            <div><strong>Date:</strong> {{ $date->format('l, F j, Y') }}</div>
            <div><strong>Time:</strong> <span id="selected-time-display" class="font-bold text-navy" style="color:var(--navy)">—</span></div>
            <div><strong>Rink:</strong> <span id="selected-rink-display">—</span></div>
            <div><strong>Price:</strong> ${{ number_format($service->price, 2) }}</div>
          </div>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
          @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('booking.submit') }}">
          @csrf
          <input type="hidden" name="service_id" value="{{ $service->id }}">
          <input type="hidden" name="time_slot_id" id="selected_slot_id">

          <div class="grid grid-cols-1 gap-4 mb-4">
            <div>
              <label class="form-label">Full Name *</label>
              <input type="text" name="client_name" required class="form-input"
                value="{{ $client ? $client->name : old('client_name') }}" placeholder="Your full name">
            </div>
            <div>
              <label class="form-label">Email *</label>
              <input type="email" name="client_email" required class="form-input"
                value="{{ $client ? $client->email : old('client_email') }}" placeholder="you@email.com">
            </div>
            <div>
              <label class="form-label">Phone <span class="font-normal text-gray-400">(optional — needed for SMS reminders)</span></label>
              <input type="tel" name="client_phone" class="form-input"
                value="{{ $client ? $client->phone : old('client_phone') }}" placeholder="(314) 555-0000" oninput="formatPhone(this)">
            </div>
            <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:10px;padding:1rem 1.25rem;">
              <div style="font-weight:700;font-size:.88rem;color:#0c4a6e;margin-bottom:.6rem;">Skater Information</div>
              <div class="grid grid-cols-1 gap-3">
                <div>
                  <label class="form-label">Skater Name *</label>
                  <input type="text" name="student_name" required class="form-input"
                    value="{{ old('student_name') }}" placeholder="Name of the person skating">
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="form-label">Skater Age *</label>
                    <input type="number" name="student_age" required min="2" max="99" class="form-input"
                      value="{{ old('student_age') }}" placeholder="Age">
                  </div>
                  <div>
                    <label class="form-label">Skill Level *</label>
                    <select name="skill_level" required class="form-input">
                      <option value="">Select...</option>
                      <option value="beginner" {{ old('skill_level') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                      <option value="intermediate" {{ old('skill_level') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                      <option value="advanced" {{ old('skill_level') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div>
              <label class="form-label">Notes <span class="font-normal text-gray-400">(optional)</span></label>
              <textarea name="notes" rows="2" class="form-input" placeholder="Goals, anything Coach Kristine should know…">{{ old('notes') }}</textarea>
            </div>
          </div>

          <div class="space-y-3 mb-5">
            @guest('client')
            <div style="background:#f0f4ff;border:1.5px solid #dbe4ff;border-radius:8px;padding:1rem 1.25rem;">
              <div class="policy-check">
                <input type="checkbox" name="guest_sms_consent" id="guest_sms_consent" value="1">
                <label for="guest_sms_consent" class="text-sm text-gray-700" style="line-height:1.6;">
                  <strong>Optional:</strong> I agree to receive SMS text message lesson reminders from Kristine Skates.
                  You will receive a confirmation text upon opting in. Message frequency varies.
                  Message and data rates may apply. Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
                  View our <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
                </label>
              </div>
            </div>
            @endguest
            <div class="policy-check">
              <input type="checkbox" name="email_consent" id="email_consent" required>
              <label for="email_consent" class="text-sm text-gray-600">I agree to receive booking confirmation and update emails. *</label>
            </div>
            <div class="policy-check">
              <input type="checkbox" name="cancellation_policy" id="cancel_policy" required>
              <label for="cancel_policy" class="text-sm text-gray-600">I understand that cancellations within 24 hours of the lesson will be charged the full lesson price. *</label>
            </div>
          </div>

          @guest('client')
          <div style="display:flex;justify-content:center;margin-bottom:1rem;">
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-callback="onTurnstilePass"></div>
          </div>
          @endguest

          <button type="submit" class="submit-btn" id="submit-btn" disabled style="opacity:.4;cursor:not-allowed">
            Request This Slot →
          </button>
          <p class="text-xs text-gray-400 text-center mt-3">You'll receive a confirmation email once Coach Kristine approves.</p>
        </form>
      </div>
    </div>
  </div>

  @else
  <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
    <div class="text-5xl mb-4">⏰</div>
    <p class="text-xl font-semibold text-gray-700 mb-2">No Slots Available</p>
    <p class="text-gray-500 mb-6">There are no open time slots for this date.</p>
    <a href="{{ route('booking.select-date', $service) }}" class="inline-block text-white px-6 py-3 rounded-lg font-semibold" style="background:var(--navy)">← Choose Another Date</a>
  </div>
  @endif
</div>

<script>
let slotSelected = false;
let turnstilePassed = {{ auth('client')->check() ? 'true' : 'false' }};

function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0) v = '(' + v;
  input.value = v;
}

function updateSubmitBtn() {
  const btn = document.getElementById('submit-btn');
  if (slotSelected && turnstilePassed) {
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';
  }
}

function onTurnstilePass() {
  turnstilePassed = true;
  updateSubmitBtn();
}

function selectSlot(slotId, timeDisplay, rinkName) {
  document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
  document.getElementById('slot-' + slotId).classList.add('selected');
  document.getElementById('selected_slot_id').value = slotId;
  document.getElementById('selected-time-display').textContent = timeDisplay;
  document.getElementById('selected-rink-display').textContent = rinkName;
  document.getElementById('form-placeholder').classList.add('hidden');
  document.getElementById('booking-form').classList.remove('hidden');
  slotSelected = true;
  updateSubmitBtn();
  if (window.innerWidth < 1024) {
    setTimeout(() => document.getElementById('booking-form').scrollIntoView({behavior:'smooth',block:'start'}), 100);
  }
}
</script>
@endsection
