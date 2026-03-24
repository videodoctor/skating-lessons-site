@extends('layouts.app')
@section('title', 'Book a Lesson')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<style>
:root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#EAF4FB; }

.wizard-wrap {
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: calc(100vh - 65px);
  background: var(--ice);
}

/* Sidebar */
.wizard-sidebar {
  background: var(--navy);
  padding: 2.5rem 1.75rem;
  position: sticky;
  top: 65px;
  height: calc(100vh - 65px);
  overflow-y: auto;
}
.wizard-sidebar h2 {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.5rem;
  color: #fff;
  letter-spacing: .05em;
  margin-bottom: 2rem;
}
.step-item {
  display: flex;
  align-items: flex-start;
  gap: .875rem;
  padding: .75rem 0;
  border-bottom: 1px solid rgba(255,255,255,.08);
  transition: opacity .2s;
}
.step-item:last-child { border-bottom: none; }
.step-num {
  width: 28px; height: 28px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,.25);
  color: rgba(255,255,255,.4);
  font-size: .75rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: all .25s;
}
.step-item.active .step-num { background: var(--red); border-color: var(--red); color: #fff; }
.step-item.done .step-num   { background: #10b981; border-color: #10b981; color: #fff; }
.step-label {
  font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.35);
  text-transform: uppercase; letter-spacing: .07em;
  line-height: 1.3; padding-top: 5px; transition: color .25s;
}
.step-item.active .step-label { color: #fff; }
.step-item.done .step-label   { color: rgba(255,255,255,.65); }
.step-sublabel {
  font-size: .72rem; color: rgba(255,255,255,.35);
  margin-top: 2px; letter-spacing: 0; font-weight: 400;
}
.step-item.done .step-sublabel { color: var(--gold); }
.summary-box {
  margin-top: 2rem; background: rgba(255,255,255,.06);
  border-radius: 10px; padding: 1.1rem;
  font-size: .8rem; color: rgba(255,255,255,.7); display: none;
}
.summary-box.visible { display: block; }
.summary-row { margin-bottom: .45rem; line-height: 1.4; }
.summary-row strong { color: #fff; display: block; font-size: .7rem; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1px; }

/* Main */
.wizard-main { padding: 3rem 3.5rem; max-width: 720px; }
.step-panel { display: none; }
.step-panel.active { display: block; animation: fadeSlide .25s ease; }
@keyframes fadeSlide { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.step-heading { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: var(--navy); letter-spacing: .04em; margin-bottom: .25rem; }
.step-sub { color: #6b7280; font-size: .9rem; margin-bottom: 2rem; }

/* Service cards */
.service-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
.svc-card {
  border: 2px solid #e5eaf2; border-radius: 12px; background: #fff;
  padding: 1.5rem; cursor: pointer; transition: all .2s; position: relative;
}
.svc-card:hover { border-color: var(--navy); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,31,91,.1); }
.svc-card.selected { border-color: var(--red); background: #fff8f8; box-shadow: 0 0 0 4px rgba(200,16,46,.08); }
.svc-card.coming-soon { opacity: .6; cursor: default; pointer-events: none; }
.svc-price { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: var(--navy); line-height: 1; }
.svc-check {
  position: absolute; top: 10px; right: 10px;
  width: 22px; height: 22px; background: var(--red);
  border-radius: 50%; display: none; align-items: center; justify-content: center;
}
.svc-card.selected .svc-check { display: flex; }

/* Calendar */
.cal-wrap { background: #fff; border-radius: 12px; border: 1.5px solid #e5eaf2; overflow: hidden; max-width: 400px; }
.cal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6; }
.cal-month { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; color: var(--navy); letter-spacing: .05em; }
.cal-nav { background: none; border: 1.5px solid #e5eaf2; border-radius: 6px; width: 32px; height: 32px; cursor: pointer; font-size: 1.1rem; color: #374151; }
.cal-nav:hover { background: var(--ice); }
.cal-grid { display: grid; grid-template-columns: repeat(7,1fr); padding: .5rem; gap: 2px; }
.cal-dow { text-align: center; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; padding: .4rem 0; }
.cal-day { text-align: center; padding: .5rem 0; font-size: .85rem; color: #d1d5db; border-radius: 6px; cursor: default; }
.cal-day.avail { color: var(--navy); font-weight: 600; cursor: pointer; }
.cal-day.avail:hover { background: var(--ice); }
.cal-day.picked { background: var(--red) !important; color: #fff !important; font-weight: 700; }
.cal-day.today-ring { box-shadow: inset 0 0 0 1.5px var(--gold); }

/* Slots */
.slot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: .75rem; }
.slot-card {
  border: 2px solid #e5eaf2; border-radius: 10px; padding: .85rem 1rem;
  cursor: pointer; text-align: center; background: #fff; transition: all .2s;
}
.slot-card:hover { border-color: var(--navy); }
.slot-card.selected { border-color: var(--red); background: #fff8f8; }
.slot-time { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; color: var(--navy); }
.slot-rink { font-size: .7rem; color: #6b7280; margin-top: 2px; }

/* Form */
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #374151; margin-bottom: .4rem; }
.form-input {
  width: 100%; border: 1.5px solid #e5eaf2; border-radius: 8px;
  padding: .65rem .9rem; font-size: .9rem; color: #111; background: #fff;
  transition: border-color .15s; outline: none;
}
.form-input:focus { border-color: var(--navy); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.check-row {
  display: flex; align-items: flex-start; gap: .65rem;
  margin-bottom: .75rem; padding: .85rem 1rem;
  background: #f8fafc; border-radius: 8px; border: 1.5px solid #e5eaf2;
}
.check-row input[type=checkbox] { flex-shrink: 0; margin-top: 3px; accent-color: var(--navy); }
.check-row label { font-size: .8rem; color: #374151; line-height: 1.5; cursor: pointer; }

/* Buttons */
.btn-next {
  background: var(--red); color: #fff; border: none; border-radius: 8px;
  padding: .85rem 2.5rem; font-size: 1rem; font-weight: 700; cursor: pointer;
  margin-top: 1.5rem; transition: background .2s, transform .15s;
}
.btn-next:hover:not(:disabled) { background: #a50d24; transform: translateY(-1px); }
.btn-next:disabled { background: #d1d5db; cursor: not-allowed; }
.btn-back {
  background: none; border: 1.5px solid #e5eaf2; color: #374151;
  border-radius: 8px; padding: .75rem 1.5rem; font-size: .9rem; font-weight: 600;
  cursor: pointer; margin-right: .75rem; margin-top: 1.5rem;
}
.btn-back:hover { border-color: var(--navy); color: var(--navy); }
.empty-msg { text-align: center; padding: 2.5rem; color: #9ca3af; font-size: .9rem; }
.err-box { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1rem; }

@media (max-width: 768px) {
  .wizard-wrap { grid-template-columns: 1fr; }
  .wizard-sidebar { position: static; height: auto; padding: 1.25rem 1rem; }
  .wizard-main { padding: 1.5rem 1rem; }
  .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="wizard-wrap">

  {{-- SIDEBAR --}}
  <aside class="wizard-sidebar">
    <h2>⛸ Book a Lesson</h2>
    <div id="step-nav">
      @foreach(['Service','Date','Time','Your Info','Review'] as $i => $label)
      <div class="step-item {{ $i===0 ? 'active' : '' }}" id="nav-{{ $i+1 }}">
        <div class="step-num" id="num-{{ $i+1 }}">{{ $i+1 }}</div>
        <div>
          <div class="step-label">{{ $label }}</div>
          <div class="step-sublabel" id="sub-{{ $i+1 }}"></div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="summary-box" id="summary-box">
      <div id="sr-service" style="display:none" class="summary-row"><strong>Service</strong><span id="sv-service"></span></div>
      <div id="sr-date"    style="display:none" class="summary-row"><strong>Date</strong><span id="sv-date"></span></div>
      <div id="sr-time"    style="display:none" class="summary-row"><strong>Time</strong><span id="sv-time"></span></div>
      <div id="sr-rink"    style="display:none" class="summary-row"><strong>Rink</strong><span id="sv-rink"></span></div>
      <div id="sr-price"   style="display:none" class="summary-row"><strong>Price</strong><span id="sv-price"></span></div>
    </div>
  </aside>

  {{-- MAIN --}}
  <main class="wizard-main">

    @if($errors->any())
    <div class="err-box">{{ $errors->first() }}</div>
    @endif
    @if(session('error'))
    <div class="err-box">{{ session('error') }}</div>
    @endif

    {{-- STEP 1: Service --}}
    <div class="step-panel active" id="panel-1">
      <div class="step-heading">Choose Your Lesson</div>
      <p class="step-sub">Select the package that fits your goals.</p>
      <div class="service-grid">
        @forelse($services as $service)
        <div class="svc-card" data-id="{{ $service->id }}"
             onclick="pickService({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ number_format($service->effectivePrice(),0) }}')">
          <div class="svc-check"><svg width="12" height="12" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></div>
          @if($service->hasActiveDiscount())
          <span style="background:#fef3c7;color:#92400e;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:4px;">{{ $service->discountLabel() }}</span>
          @endif
          <h3 style="font-weight:700;color:#111;font-size:1rem;margin:.5rem 0 .25rem;">{{ $service->name }}</h3>
          <p style="font-size:.78rem;color:#6b7280;margin-bottom:.75rem;">{{ $service->description }}</p>
          <div class="svc-price">
            @if($service->hasActiveDiscount())
              <span style="color:#9ca3af;font-size:1rem;text-decoration:line-through;">${{ number_format($service->price,0) }}</span>
              <span style="font-size:2.2rem;">${{ number_format($service->effectivePrice(),0) }}</span>
            @else
              <span style="font-size:1.2rem;vertical-align:top;margin-top:4px;display:inline-block;">$</span>{{ number_format($service->price,0) }}
            @endif
          </div>
          <div style="font-size:.73rem;color:#9ca3af;margin-top:2px;">{{ $service->duration_minutes }} min · per session</div>
          @if($service->features)
          <ul style="margin-top:.75rem;font-size:.78rem;color:#374151;list-style:none;padding:0;">
            @foreach($service->features as $f)<li style="margin-bottom:3px;">✓ {{ $f }}</li>@endforeach
          </ul>
          @endif
        </div>
        @empty
        <div class="empty-msg" style="grid-column:1/-1;">No services currently available.</div>
        @endforelse

        @foreach($comingSoonServices ?? [] as $s)
        <div class="svc-card coming-soon">
          <span style="background:#fef3c7;color:#92400e;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:4px;">🔒 Coming Soon</span>
          <h3 style="font-weight:700;color:#9ca3af;font-size:1rem;margin:.5rem 0 .25rem;">{{ $s->name }}</h3>
          @if($s->show_description)<p style="font-size:.78rem;color:#9ca3af;">{{ $s->description }}</p>@endif
          @if($s->show_price)<div class="svc-price" style="color:#9ca3af;"><span style="font-size:1.2rem;vertical-align:top;margin-top:4px;display:inline-block;">$</span>{{ number_format($s->price,0) }}</div>@endif
        </div>
        @endforeach
      </div>
      <button class="btn-next" id="btn-1" disabled onclick="goTo(2)">Continue →</button>
    </div>

    {{-- STEP 2: Date --}}
    <div class="step-panel" id="panel-2">
      <div class="step-heading">Pick a Date</div>
      <p class="step-sub">Available dates are highlighted in navy.</p>
      <div id="cal-loading" style="color:#9ca3af;padding:1rem;display:none;">Loading available dates...</div>
      <div class="cal-wrap">
        <div class="cal-header">
          <button class="cal-nav" onclick="calNav(-1)">‹</button>
          <div class="cal-month" id="cal-month"></div>
          <button class="cal-nav" onclick="calNav(1)">›</button>
        </div>
        <div class="cal-grid" id="cal-grid">
          <div class="cal-dow">Su</div><div class="cal-dow">Mo</div><div class="cal-dow">Tu</div>
          <div class="cal-dow">We</div><div class="cal-dow">Th</div><div class="cal-dow">Fr</div><div class="cal-dow">Sa</div>
        </div>
      </div>
      <div style="margin-top:1.5rem;">
        <button class="btn-back" onclick="goTo(1)">← Back</button>
        <button class="btn-next" id="btn-2" disabled onclick="goTo(3)">Continue →</button>
      </div>
    </div>

    {{-- STEP 3: Time --}}
    <div class="step-panel" id="panel-3">
      <div class="step-heading">Choose a Time</div>
      <p class="step-sub" id="step3-sub">Select a time slot.</p>
      <div id="slots-loading" style="color:#9ca3af;padding:1rem;display:none;">Loading available times...</div>
      <div class="slot-grid" id="slot-grid"></div>
      <div id="slots-empty" class="empty-msg" style="display:none;">No times available on this date. Please go back and choose another date.</div>
      <div style="margin-top:1.5rem;">
        <button class="btn-back" onclick="goTo(2)">← Back</button>
        <button class="btn-next" id="btn-3" disabled onclick="goTo(4)">Continue →</button>
      </div>
    </div>

    {{-- STEP 4: Your Info --}}
    <div class="step-panel" id="panel-4">
      <div class="step-heading">Your Information</div>
      <p class="step-sub">Tell us about you and your skater.</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Your Name *</label>
          <input class="form-input" type="text" id="f-name" placeholder="Jane Smith" oninput="chk4()">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input class="form-input" type="email" id="f-email" placeholder="jane@example.com" oninput="chk4()">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Phone (optional)</label>
          <input class="form-input" type="tel" id="f-phone" placeholder="(314) 555-0100" oninput="chkSms()">
        </div>
        <div class="form-group">
          <label class="form-label">Skater's First Name</label>
          <input class="form-input" type="text" id="f-student" placeholder="If different from above">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes / Questions (optional)</label>
        <textarea class="form-input" id="f-notes" rows="3" placeholder="Skill level, goals, anything Kristine should know..."></textarea>
      </div>
      <div id="sms-row" style="display:none;">
        <div class="check-row">
          <input type="checkbox" id="f-sms">
          <label for="f-sms">I agree to receive SMS text message lesson reminders from Kristine Skates. Message frequency varies. Msg & data rates may apply. Reply STOP to opt out or HELP for help.</label>
        </div>
      </div>
      <div class="check-row">
        <input type="checkbox" id="f-email-consent" onchange="chk4()">
        <label for="f-email-consent">I agree to receive email communications about my booking from Kristine Skates. *</label>
      </div>
      <div class="check-row">
        <input type="checkbox" id="f-policy" onchange="chk4()">
        <label for="f-policy">I understand that cancellations less than 24 hours before the lesson will be billed at the full rate. *</label>
      </div>
      <div style="margin-top:1.5rem;">
        <button class="btn-back" onclick="goTo(3)">← Back</button>
        <button class="btn-next" id="btn-4" disabled onclick="goTo(5)">Continue →</button>
      </div>
    </div>

    {{-- STEP 5: Review --}}
    <div class="step-panel" id="panel-5">
      <div class="step-heading">Review & Submit</div>
      <p class="step-sub">Double-check everything before sending your request.</p>
      <div style="background:#fff;border-radius:12px;border:1.5px solid #e5eaf2;overflow:hidden;margin-bottom:1.5rem;">
        <div style="background:var(--navy);padding:1rem 1.5rem;">
          <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:#fff;letter-spacing:.05em;">Lesson Request Summary</span>
        </div>
        <div style="padding:1.5rem;">
          <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;width:110px;">Service</td><td style="padding:.5rem 0;font-weight:600;" id="rv-service">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Date</td><td style="padding:.5rem 0;font-weight:600;" id="rv-date">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Time</td><td style="padding:.5rem 0;font-weight:600;" id="rv-time">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Rink</td><td style="padding:.5rem 0;font-weight:600;" id="rv-rink">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Name</td><td style="padding:.5rem 0;font-weight:600;" id="rv-name">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Email</td><td style="padding:.5rem 0;font-weight:600;" id="rv-email">—</td></tr>
            <tr style="border-bottom:1px solid #f3f4f6;"><td style="padding:.5rem 0;color:#6b7280;">Phone</td><td style="padding:.5rem 0;font-weight:600;" id="rv-phone">—</td></tr>
            <tr><td style="padding:.5rem 0;color:#6b7280;">Price</td><td style="padding:.5rem 0;font-weight:700;color:var(--red);font-size:1.05rem;" id="rv-price">—</td></tr>
          </table>
        </div>
      </div>
      <p style="font-size:.75rem;color:#9ca3af;margin-bottom:1.5rem;">* Price does not include rink admission fee. Payment accepted at end of lesson by cash, Venmo, or check.</p>

      <form method="POST" action="{{ route('booking.submit') }}" id="booking-form">
        @csrf
        <input type="hidden" name="service_id"          id="h-service">
        <input type="hidden" name="time_slot_id"        id="h-slot">
        <input type="hidden" name="client_name"         id="h-name">
        <input type="hidden" name="client_email"        id="h-email">
        <input type="hidden" name="client_phone"        id="h-phone">
        <input type="hidden" name="notes"               id="h-notes">
        <input type="hidden" name="email_consent"       id="h-email-consent">
        <input type="hidden" name="cancellation_policy" id="h-policy">
        <input type="hidden" name="guest_sms_consent"   id="h-sms" value="0">
        @guest('client')
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light" style="margin-bottom:1rem;"></div>
        @endguest
        <button class="btn-back" type="button" onclick="goTo(4)">← Back</button>
        <button type="submit" class="btn-next">🏒 Send Booking Request</button>
      </form>
    </div>

  </main>
</div>

<script>
const S = {
  step:1, svcId:null, svcName:null, svcPrice:null,
  date:null, dateLabel:null,
  slotId:null, slotTime:null, slotRink:null,
  availDates:[],
  calY: new Date().getFullYear(), calM: new Date().getMonth()
};

// ── Navigation ────────────────────────────────────────────────────────────
function goTo(n) {
  if (n > S.step) {
    if (S.step === 1 && !S.svcId)  return;
    if (S.step === 2 && !S.date)   return;
    if (S.step === 3 && !S.slotId) return;
  }
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-'+n).classList.add('active');
  document.querySelectorAll('.step-item').forEach((el, i) => {
    el.classList.remove('active','done');
    const num = document.getElementById('num-'+(i+1));
    if (i+1 < n) {
      el.classList.add('done');
      num.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    } else {
      if (i+1 === n) el.classList.add('active');
      num.textContent = i+1;
    }
  });
  S.step = n;
  syncSummary();
  if (n===2) loadDates();
  if (n===3) loadSlots();
  if (n===4) maybeShowSms();
  if (n===5) fillReview();
  window.scrollTo({top:0,behavior:'smooth'});
}

// ── Step 1 ────────────────────────────────────────────────────────────────
function pickService(id, name, price) {
  S.svcId=id; S.svcName=name; S.svcPrice=price;
  S.date=null; S.dateLabel=null; S.slotId=null; S.slotTime=null; S.slotRink=null; S.availDates=[];
  document.querySelectorAll('.svc-card:not(.coming-soon)').forEach(c=>c.classList.remove('selected'));
  document.querySelector('.svc-card[data-id="'+id+'"]')?.classList.add('selected');
  document.getElementById('btn-1').disabled = false;
  document.getElementById('sub-1').textContent = name;
  syncSummary();
}

// ── Step 2: Calendar ──────────────────────────────────────────────────────
async function loadDates() {
  document.getElementById('cal-loading').style.display='block';
  S.availDates=[];
  try { S.availDates = await fetch('/book/ajax/dates/'+S.svcId).then(r=>r.json()); } catch(e){}
  document.getElementById('cal-loading').style.display='none';
  renderCal();
}

function renderCal() {
  const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  document.getElementById('cal-month').textContent = months[S.calM] + ' ' + S.calY;
  const firstDay = new Date(S.calY, S.calM, 1).getDay();
  const days = new Date(S.calY, S.calM+1, 0).getDate();
  const todayStr = new Date().toISOString().split('T')[0];
  const grid = document.getElementById('cal-grid');
  const hdr = Array.from(grid.children).slice(0,7);
  grid.innerHTML=''; hdr.forEach(h=>grid.appendChild(h));
  for(let i=0;i<firstDay;i++){const b=document.createElement('div');b.className='cal-day';grid.appendChild(b);}
  for(let d=1;d<=days;d++){
    const ds=`${S.calY}-${String(S.calM+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const el=document.createElement('div');
    el.className='cal-day'+(S.availDates.includes(ds)?' avail':'')+(ds===S.date?' picked':'')+(ds===todayStr?' today-ring':'');
    el.textContent=d;
    if(S.availDates.includes(ds)) el.onclick=()=>pickDate(ds,d);
    grid.appendChild(el);
  }
}

function calNav(dir){
  S.calM+=dir;
  if(S.calM>11){S.calM=0;S.calY++;}
  if(S.calM<0){S.calM=11;S.calY--;}
  renderCal();
}

function pickDate(ds,d){
  S.date=ds; S.slotId=null; S.slotTime=null; S.slotRink=null;
  const dt=new Date(ds+'T12:00:00');
  const days=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  const mos=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  S.dateLabel=days[dt.getDay()]+' '+mos[dt.getMonth()]+' '+dt.getDate();
  document.getElementById('btn-2').disabled=false;
  document.getElementById('sub-2').textContent=S.dateLabel;
  document.getElementById('sub-3').textContent='';
  renderCal(); syncSummary();
}

// ── Step 3: Slots ─────────────────────────────────────────────────────────
async function loadSlots(){
  document.getElementById('step3-sub').textContent='Available times on '+S.dateLabel;
  document.getElementById('slots-loading').style.display='block';
  document.getElementById('slot-grid').innerHTML='';
  document.getElementById('slots-empty').style.display='none';
  document.getElementById('btn-3').disabled=true;
  let slots=[];
  try{slots=await fetch('/book/ajax/slots/'+S.svcId+'/'+S.date).then(r=>r.json());}catch(e){}
  document.getElementById('slots-loading').style.display='none';
  if(!slots.length){document.getElementById('slots-empty').style.display='block';return;}
  const grid=document.getElementById('slot-grid');
  slots.forEach(s=>{
    const el=document.createElement('div');
    el.className='slot-card'+(s.id===S.slotId?' selected':'');
    el.innerHTML='<div class="slot-time">'+s.time+'</div><div class="slot-rink">'+s.rink+'</div>';
    el.onclick=()=>{
      S.slotId=s.id;S.slotTime=s.time;S.slotRink=s.rink;
      document.querySelectorAll('.slot-card').forEach(c=>c.classList.remove('selected'));
      el.classList.add('selected');
      document.getElementById('btn-3').disabled=false;
      document.getElementById('sub-3').textContent=s.time;
      syncSummary();
    };
    grid.appendChild(el);
  });
}

// ── Step 4 ────────────────────────────────────────────────────────────────
function chkSms(){
  const phone=document.getElementById('f-phone').value.trim();
  @guest('client')
  document.getElementById('sms-row').style.display=phone?'block':'none';
  @endguest
}
function chk4(){
  const ok=document.getElementById('f-name').value.trim()
        && document.getElementById('f-email').value.trim()
        && document.getElementById('f-email-consent').checked
        && document.getElementById('f-policy').checked;
  document.getElementById('btn-4').disabled=!ok;
}
function maybeShowSms(){
  chkSms();
}

// ── Step 5: Review ────────────────────────────────────────────────────────
function fillReview(){
  const name=document.getElementById('f-name').value.trim();
  const email=document.getElementById('f-email').value.trim();
  const phone=document.getElementById('f-phone').value.trim();
  document.getElementById('rv-service').textContent=S.svcName;
  document.getElementById('rv-date').textContent=S.dateLabel;
  document.getElementById('rv-time').textContent=S.slotTime;
  document.getElementById('rv-rink').textContent=S.slotRink;
  document.getElementById('rv-name').textContent=name;
  document.getElementById('rv-email').textContent=email;
  document.getElementById('rv-phone').textContent=phone||'—';
  document.getElementById('rv-price').textContent='$'+S.svcPrice;
  document.getElementById('h-service').value=S.svcId;
  document.getElementById('h-slot').value=S.slotId;
  document.getElementById('h-name').value=name;
  document.getElementById('h-email').value=email;
  document.getElementById('h-phone').value=phone;
  document.getElementById('h-notes').value=document.getElementById('f-notes').value;
  document.getElementById('h-email-consent').value=document.getElementById('f-email-consent').checked?'1':'';
  document.getElementById('h-policy').value=document.getElementById('f-policy').checked?'1':'';
  document.getElementById('h-sms').value=document.getElementById('f-sms').checked?'1':'0';
}

// ── Sidebar summary ───────────────────────────────────────────────────────
function syncSummary(){
  const show=S.svcId||S.date||S.slotId;
  document.getElementById('summary-box').classList.toggle('visible',!!show);
  const set=(row,val)=>{
    document.getElementById('sr-'+row).style.display=val?'':'none';
    document.getElementById('sv-'+row).textContent=val||'';
  };
  set('service',S.svcName);
  set('date',S.dateLabel);
  set('time',S.slotTime);
  set('rink',S.slotRink);
  set('price',S.svcPrice?'$'+S.svcPrice:null);
}

// ── Pre-fill for logged-in client ─────────────────────────────────────────
@auth('client')
document.addEventListener('DOMContentLoaded',()=>{
  const c=@json(Auth::guard('client')->user());
  if(c){
    document.getElementById('f-name').value=(c.first_name||'')+' '+(c.last_name||'');
    document.getElementById('f-email').value=c.email||'';
    document.getElementById('f-phone').value=c.phone||'';
  }
  chk4();
});
@endauth
</script>

@endsection
