@extends('layouts.app')
@section('title', 'Book a Lesson')
@section('content')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<style>
:root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#EAF4FB; }

/* ── Layout ── */
.wizard-wrap {
  display: grid;
  grid-template-columns: 280px 1fr;
  min-height: calc(100vh - 65px);
  background: #f8fafc;
}

/* ── Sidebar ── */
.wizard-sidebar {
  background: var(--navy);
  padding: 2.25rem 1.75rem;
  position: sticky;
  top: 65px;
  height: calc(100vh - 65px);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.sidebar-logo {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.4rem;
  color: #fff;
  letter-spacing: .06em;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Step nav */
.step-item {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  padding: .65rem 0;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.step-item:last-child { border-bottom: none; }
.step-num {
  width: 26px; height: 26px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,.2);
  color: rgba(255,255,255,.3);
  font-size: .72rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: all .25s;
}
.step-item.active .step-num { background: var(--red); border-color: var(--red); color: #fff; }
.step-item.done .step-num { background: #059669; border-color: #059669; color: #fff; }
.step-label {
  font-size: .78rem; font-weight: 700; color: rgba(255,255,255,.3);
  text-transform: uppercase; letter-spacing: .07em;
  padding-top: 4px; transition: color .25s; line-height: 1.3;
}
.step-item.active .step-label { color: #fff; }
.step-item.done .step-label   { color: rgba(255,255,255,.6); }
.step-sublabel {
  font-size: .71rem; color: rgba(255,255,255,.3);
  margin-top: 1px; font-weight: 400; letter-spacing: 0;
  transition: color .2s;
}
.step-item.done .step-sublabel { color: var(--gold); }
.step-item.active .step-sublabel { color: rgba(255,255,255,.55); }

/* Live summary card */
.live-summary {
  margin-top: auto;
  padding-top: 1.5rem;
}
.summary-card {
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 10px;
  overflow: hidden;
  display: none;
}
.summary-card.visible { display: block; }
.summary-card-head {
  background: rgba(255,255,255,.1);
  padding: .6rem 1rem;
  font-size: .65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: rgba(255,255,255,.5);
}
.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: .45rem 1rem;
  border-top: 1px solid rgba(255,255,255,.06);
  gap: .5rem;
}
.summary-row:first-child { border-top: none; }
.sr-label { font-size: .68rem; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; flex-shrink: 0; padding-top: 1px; }
.sr-val { font-size: .78rem; color: #fff; font-weight: 600; text-align: right; }
.sr-price { color: var(--gold) !important; font-family: 'Bebas Neue', sans-serif; font-size: 1.1rem !important; }

/* ── Main ── */
.wizard-main {
  padding: 2.5rem 3rem 4rem;
  max-width: 700px;
}
.step-panel { display: none; }
.step-panel.active { display: block; animation: fadeIn .22s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

.step-heading {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 2rem; color: var(--navy);
  letter-spacing: .04em; margin-bottom: .2rem;
}
.step-sub { color: #6b7280; font-size: .87rem; margin-bottom: 1.75rem; }

/* Service cards */
.svc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px,1fr)); gap: .875rem; }
.svc-card {
  border: 2px solid #e5eaf2; border-radius: 12px; background: #fff;
  padding: 1.35rem; cursor: pointer; transition: all .18s; position: relative;
}
.svc-card:hover { border-color: #93c5fd; transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,31,91,.09); }
.svc-card.selected { border-color: var(--red); background: #fff8f8; box-shadow: 0 0 0 3px rgba(200,16,46,.1); }
.svc-card.cs { opacity:.6; cursor:default; pointer-events:none; }
.svc-check {
  position:absolute; top:9px; right:9px;
  width:20px; height:20px; background:var(--red);
  border-radius:50%; display:none; align-items:center; justify-content:center;
}
.svc-card.selected .svc-check { display:flex; }
.svc-price { font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--navy); line-height:1; margin:6px 0 2px; }
.svc-dur { font-size:.72rem; color:#9ca3af; }
.svc-name { font-weight:700; color:#111; font-size:.95rem; margin-bottom:.2rem; }
.svc-desc { font-size:.75rem; color:#6b7280; margin-bottom:.6rem; line-height:1.4; }
.svc-feat { font-size:.73rem; color:#374151; list-style:none; padding:0; margin:.6rem 0 0; }
.svc-feat li::before { content:"✓ "; color:#059669; }

/* Calendar */
.cal-outer { background:#fff; border-radius:12px; border:1.5px solid #e5eaf2; max-width:380px; }
.cal-hdr { display:flex; align-items:center; justify-content:space-between; padding:.875rem 1.1rem; border-bottom:1px solid #f3f4f6; }
.cal-mo { font-family:'Bebas Neue',sans-serif; font-size:1.25rem; color:var(--navy); letter-spacing:.05em; }
.cal-btn { background:none; border:1.5px solid #e5eaf2; border-radius:6px; width:30px; height:30px; cursor:pointer; font-size:1rem; color:#374151; }
.cal-btn:hover { background:var(--ice); }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); padding:.4rem .5rem .6rem; gap:2px; }
.cal-dow { text-align:center; font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; padding:.35rem 0; }
.cal-day { text-align:center; padding:.45rem 0; font-size:.82rem; color:#d1d5db; border-radius:6px; cursor:default; }
.cal-day.av { color:var(--navy); font-weight:600; cursor:pointer; transition:background .12s; }
.cal-day.av:hover { background:var(--ice); }
.cal-day.sel { background:var(--red) !important; color:#fff !important; font-weight:700; }
.cal-day.tod { box-shadow:inset 0 0 0 1.5px var(--gold); }

/* Time slots */
.slot-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(120px,1fr)); gap:.65rem; }
.slot-card {
  border:2px solid #e5eaf2; border-radius:10px; padding:.75rem .875rem;
  cursor:pointer; text-align:center; background:#fff; transition:all .15s;
}
.slot-card:hover { border-color:#93c5fd; }
.slot-card.selected { border-color:var(--red); background:#fff8f8; }
.slot-time { font-family:'Bebas Neue',sans-serif; font-size:1.2rem; color:var(--navy); }
.slot-rink { font-size:.67rem; color:#6b7280; margin-top:1px; line-height:1.3; }

/* Form */
.form-group { margin-bottom:1.1rem; }
.form-lbl { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#374151; margin-bottom:.35rem; }
.form-inp {
  width:100%; border:1.5px solid #e5eaf2; border-radius:8px;
  padding:.6rem .85rem; font-size:.88rem; color:#111;
  background:#fff; transition:border-color .15s; outline:none;
}
.form-inp:focus { border-color:var(--navy); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:.875rem; }
.chk-row {
  display:flex; align-items:flex-start; gap:.6rem;
  padding:.8rem .9rem; background:#f8fafc;
  border-radius:8px; border:1.5px solid #e5eaf2; margin-bottom:.65rem;
}
.chk-row input[type=checkbox] { flex-shrink:0; margin-top:2px; accent-color:var(--navy); width:15px; height:15px; }
.chk-row label { font-size:.78rem; color:#374151; line-height:1.5; cursor:pointer; }
.chk-row.sms-opt { border-color:#bfdbfe; background:#eff6ff; }
.chk-row.required-mark label::after { content:" *"; color:var(--red); }

/* Buttons */
.btn-next {
  background:var(--red); color:#fff; border:none; border-radius:8px;
  padding:.8rem 2.25rem; font-size:.95rem; font-weight:700; cursor:pointer;
  transition:background .18s,transform .15s; margin-top:1.25rem;
}
.btn-next:hover:not(:disabled) { background:#a50d24; transform:translateY(-1px); }
.btn-next:disabled { background:#d1d5db; cursor:not-allowed; }
.btn-back {
  background:none; border:1.5px solid #e5eaf2; color:#374151;
  border-radius:8px; padding:.7rem 1.35rem; font-size:.88rem; font-weight:600;
  cursor:pointer; margin-right:.6rem; margin-top:1.25rem;
}
.btn-back:hover { border-color:var(--navy); color:var(--navy); }

/* Review table */
.rev-table { width:100%; border-collapse:collapse; font-size:.88rem; }
.rev-table td { padding:.5rem 0; vertical-align:top; }
.rev-table td:first-child { color:#6b7280; width:110px; }
.rev-table td:last-child { font-weight:600; color:#111; }
.rev-table tr { border-bottom:1px solid #f3f4f6; }
.rev-table tr:last-child { border-bottom:none; }

.err-box { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:.75rem 1rem; font-size:.84rem; margin-bottom:1rem; }
.empty-msg { text-align:center; padding:2rem; color:#9ca3af; font-size:.88rem; }

/* ── Mobile ── */
@media (max-width: 768px) {
  .wizard-wrap { grid-template-columns:1fr; }
  .wizard-sidebar { display:none; }
  .wizard-main { padding:1.25rem 1rem 6rem; max-width:100%; }
  .form-row { grid-template-columns:1fr; gap:.65rem; }
  .svc-grid { grid-template-columns:1fr; }

  /* Mobile sticky bottom bar */
  .mobile-bar {
    display: flex !important;
    position: fixed; bottom:0; left:0; right:0;
    background: var(--navy);
    padding: .75rem 1rem;
    z-index: 100;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 -4px 20px rgba(0,31,91,.2);
    gap: .5rem;
  }
  .mobile-bar-info { flex:1; overflow:hidden; }
  .mobile-bar-step { font-size:.65rem; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.5); }
  .mobile-bar-val { font-size:.82rem; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .mobile-bar-price { font-family:'Bebas Neue',sans-serif; font-size:1.3rem; color:var(--gold); flex-shrink:0; }
}
@media (min-width: 769px) {
  .mobile-bar { display: none !important; }
}
</style>

{{-- Mobile bottom bar --}}
<div class="mobile-bar" id="mobile-bar" style="display:none;">
  <div class="mobile-bar-info">
    <div class="mobile-bar-step" id="mb-step">Step 1 of 5</div>
    <div class="mobile-bar-val" id="mb-val">Choose a lesson →</div>
  </div>
  <div class="mobile-bar-price" id="mb-price"></div>
</div>

<div class="wizard-wrap">

  {{-- SIDEBAR --}}
  <aside class="wizard-sidebar">
    <div class="sidebar-logo">⛸ Book a Lesson</div>

    <div id="step-nav">
      @foreach(['Service','Date','Time & Rink','Your Info','Review'] as $i => $lbl)
      <div class="step-item {{ $i===0?'active':'' }}" id="nav-{{ $i+1 }}">
        <div class="step-num" id="num-{{ $i+1 }}">{{ $i+1 }}</div>
        <div>
          <div class="step-label">{{ $lbl }}</div>
          <div class="step-sublabel" id="sub-{{ $i+1 }}"></div>
        </div>
      </div>
      @endforeach
    </div>


  </aside>

  {{-- MAIN --}}
  <main class="wizard-main">

    @if($errors->any())<div class="err-box">{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="err-box">{{ session('error') }}</div>@endif

    {{-- STEP 1: Service --}}
    <div class="step-panel active" id="panel-1">
      <div class="step-heading">Choose Your Lesson</div>
      <p class="step-sub">Tap a package to continue.</p>
      <div class="svc-grid">
        @forelse($services as $svc)
        <div class="svc-card" data-id="{{ $svc->id }}"
             onclick="pickSvc({{ $svc->id }},'{{ addslashes($svc->name) }}','{{ number_format($svc->effectivePrice(),0) }}')">
          <div class="svc-check"><svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></div>
          @if($svc->hasActiveDiscount())
          <span style="background:#fef3c7;color:#92400e;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:3px;text-transform:uppercase;">{{ $svc->discountLabel() }}</span>
          @endif
          <div class="svc-name" style="margin-top:{{ $svc->hasActiveDiscount()?'.35rem':'.1rem' }}">{{ $svc->name }}</div>
          <div class="svc-desc">{{ $svc->description }}</div>
          <div class="svc-price">
            @if($svc->hasActiveDiscount())
              <span style="color:#9ca3af;font-size:.95rem;text-decoration:line-through;">${{ number_format($svc->price,0) }}</span>
              ${{ number_format($svc->effectivePrice(),0) }}
            @else
              <span style="font-size:1.1rem;vertical-align:top;margin-top:4px;display:inline-block;">$</span>{{ number_format($svc->price,0) }}
            @endif
          </div>
          <div class="svc-dur">{{ $svc->duration_minutes }} min · per session</div>
          @if($svc->features)
          <ul class="svc-feat">@foreach($svc->features as $f)<li>{{ $f }}</li>@endforeach</ul>
          @endif
        </div>
        @empty
        <div class="empty-msg" style="grid-column:1/-1;">No services currently available. Check back soon!</div>
        @endforelse

        @foreach($comingSoonServices??[] as $s)
        <div class="svc-card cs">
          <span style="background:#fef3c7;color:#92400e;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:3px;text-transform:uppercase;">🔒 Coming Soon</span>
          <div class="svc-name" style="margin-top:.35rem;color:#9ca3af;">{{ $s->name }}</div>
          @if($s->show_description)<div class="svc-desc">{{ $s->description }}</div>@endif
          @if($s->show_price)<div class="svc-price" style="color:#9ca3af;"><span style="font-size:1.1rem;vertical-align:top;margin-top:4px;display:inline-block;">$</span>{{ number_format($s->price,0) }}</div>@endif
        </div>
        @endforeach
      </div>
    </div>

    {{-- STEP 2: Date --}}
    <div class="step-panel" id="panel-2">
      <div class="step-heading">Pick a Date</div>
      <p class="step-sub">Highlighted dates have availability. Tap to continue.</p>
      <div id="cal-loading" style="color:#9ca3af;padding:.75rem 0;display:none;font-size:.85rem;">Loading available dates...</div>
      <div class="cal-outer">
        <div class="cal-hdr">
          <button class="cal-btn" onclick="calNav(-1)">‹</button>
          <div class="cal-mo" id="cal-mo"></div>
          <button class="cal-btn" onclick="calNav(1)">›</button>
        </div>
        <div class="cal-grid" id="cal-grid">
          <div class="cal-dow">Su</div><div class="cal-dow">Mo</div><div class="cal-dow">Tu</div>
          <div class="cal-dow">We</div><div class="cal-dow">Th</div><div class="cal-dow">Fr</div><div class="cal-dow">Sa</div>
        </div>
      </div>
      <div style="margin-top:1.25rem;">
        <button class="btn-back" onclick="goTo(1)">← Back</button>
      </div>
    </div>

    {{-- STEP 3: Time --}}
    <div class="step-panel" id="panel-3">
      <div class="step-heading">Choose a Time</div>
      <p class="step-sub" id="step3-sub">Select a slot to continue.</p>
      <div id="slots-loading" style="color:#9ca3af;padding:.75rem 0;display:none;font-size:.85rem;">Loading times...</div>
      <div class="slot-grid" id="slot-grid"></div>
      <div id="slots-empty" class="empty-msg" style="display:none;">No times available on this date. Go back and pick another date.</div>
      <div style="margin-top:1.25rem;">
        <button class="btn-back" onclick="goTo(2)">← Back</button>
      </div>
    </div>

    {{-- STEP 4: Info --}}
    <div class="step-panel" id="panel-4">
      <div class="step-heading">Your Information</div>
      <p class="step-sub">Tell us about you and your skater.</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-lbl">Your Name *</label>
          <input class="form-inp" type="text" id="f-name" placeholder="Jane Smith" oninput="chk4()">
        </div>
        <div class="form-group">
          <label class="form-lbl">Email Address *</label>
          <input class="form-inp" type="email" id="f-email" placeholder="jane@example.com" oninput="chk4()">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-lbl">Phone (optional)</label>
          <input class="form-inp" type="tel" id="f-phone" placeholder="(314) 555-0100" oninput="formatPhone(this);toggleSms()">
        </div>
        <div class="form-group">
          <label class="form-lbl">Skater's First Name</label>
          <input class="form-inp" type="text" id="f-student" placeholder="If different from above">
        </div>
      </div>
      <div class="form-group">
        <label class="form-lbl">Notes (optional)</label>
        <textarea class="form-inp" id="f-notes" rows="2" placeholder="Skill level, goals, anything Kristine should know..."></textarea>
      </div>

      {{-- SMS opt-in — always visible, disabled until valid phone entered --}}
      @guest('client')
      <div class="chk-row sms-opt" id="sms-row" style="opacity:.4;pointer-events:none;">
        <input type="checkbox" id="f-sms" disabled>
        <label for="f-sms">
          <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders and availability notifications.
          You will receive a confirmation text upon opting in. Message frequency varies.
          Message and data rates may apply. Reply STOP to opt out or HELP for help.
        </label>
      </div>
      @endguest

      <div class="chk-row required-mark">
        <input type="checkbox" id="f-email-consent" onchange="chk4()">
        <label for="f-email-consent">I agree to receive email communications about my booking from Kristine Skates.</label>
      </div>
      <div class="chk-row required-mark">
        <input type="checkbox" id="f-policy" onchange="chk4()">
        <label for="f-policy">I understand that cancellations less than 24 hours before the lesson will be billed at the full rate.</label>
      </div>

      <div>
        <button class="btn-back" onclick="goTo(3)">← Back</button>
        <button class="btn-next" id="btn-4" disabled onclick="goTo(5)">Review Booking →</button>
      </div>
    </div>

    {{-- STEP 5: Review --}}
    <div class="step-panel" id="panel-5">
      <div class="step-heading">Review &amp; Submit</div>
      <p class="step-sub">Everything look right? Send your request to Coach Kristine.</p>

      <div style="background:#fff;border-radius:12px;border:1.5px solid #e5eaf2;overflow:hidden;margin-bottom:1.25rem;">
        <div style="background:var(--navy);padding:.875rem 1.35rem;display:flex;justify-content:space-between;align-items:center;">
          <span style="font-family:'Bebas Neue',sans-serif;font-size:1.25rem;color:#fff;letter-spacing:.05em;">Lesson Request</span>
          <span style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--gold);" id="rv-price-head">—</span>
        </div>
        <div style="padding:1.25rem 1.35rem;">
          <table class="rev-table">
            <tr><td>Service</td><td id="rv-svc">—</td></tr>
            <tr><td>Date</td><td id="rv-date">—</td></tr>
            <tr><td>Time</td><td id="rv-time">—</td></tr>
            <tr><td>Rink</td><td id="rv-rink">—</td></tr>
            <tr><td>Name</td><td id="rv-name">—</td></tr>
            <tr><td>Email</td><td id="rv-email">—</td></tr>
            <tr><td>Phone</td><td id="rv-phone">—</td></tr>
          </table>
        </div>
      </div>
      <p style="font-size:.72rem;color:#9ca3af;margin-bottom:1.25rem;">* Price does not include rink admission fee. Payment at end of lesson — cash, Venmo, or check.</p>

      <form method="POST" action="{{ route('booking.submit') }}" id="booking-form">
        @csrf
        <input type="hidden" name="service_id"          id="h-svc">
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
  step:1,
  svcId:null, svcName:null, svcPrice:null,
  date:null, dateLabel:null,
  slotId:null, slotTime:null, slotRink:null,
  availDates:[],
  calY:new Date().getFullYear(), calM:new Date().getMonth()
};
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAYS_S = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const MONTHS_S = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// ── Navigation ────────────────────────────────────────────────────────────
function goTo(n) {
  document.querySelectorAll('.step-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('panel-'+n).classList.add('active');
  document.querySelectorAll('.step-item').forEach((el,i)=>{
    el.classList.remove('active','done');
    const num = document.getElementById('num-'+(i+1));
    if(i+1 < n){
      el.classList.add('done');
      num.innerHTML='<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>';
    } else {
      if(i+1===n) el.classList.add('active');
      num.textContent=i+1;
    }
  });
  S.step=n;
  syncSidebar();
  syncMobileBar();
  if(n===2) loadDates();
  if(n===3) loadSlots();
  if(n===5) fillReview();
  window.scrollTo({top:0,behavior:'smooth'});
}

// ── Step 1: Service ───────────────────────────────────────────────────────
function pickSvc(id, name, price) {
  S.svcId=id; S.svcName=name; S.svcPrice=price;
  // Reset downstream
  S.date=null; S.dateLabel=null;
  S.slotId=null; S.slotTime=null; S.slotRink=null;
  S.availDates=[];
  document.querySelectorAll('.svc-card:not(.cs)').forEach(c=>c.classList.remove('selected'));
  document.querySelector('.svc-card[data-id="'+id+'"]')?.classList.add('selected');
  document.getElementById('sub-1').textContent=name;
  syncSidebar(); syncMobileBar();
  // Auto-advance after brief highlight
  setTimeout(()=>goTo(2), 200);
}

// ── Step 2: Calendar ──────────────────────────────────────────────────────
async function loadDates(){
  document.getElementById('cal-loading').style.display='block';
  S.availDates=[];
  try{ S.availDates=await fetch('/book/ajax/dates/'+S.svcId).then(r=>r.json()); }catch(e){}
  document.getElementById('cal-loading').style.display='none';
  renderCal();
}

function renderCal(){
  document.getElementById('cal-mo').textContent=MONTHS[S.calM]+' '+S.calY;
  const first=new Date(S.calY,S.calM,1).getDay();
  const total=new Date(S.calY,S.calM+1,0).getDate();
  const todayStr=new Date().toISOString().split('T')[0];
  const grid=document.getElementById('cal-grid');
  const hdrs=Array.from(grid.children).slice(0,7);
  grid.innerHTML=''; hdrs.forEach(h=>grid.appendChild(h));
  for(let i=0;i<first;i++){const b=document.createElement('div');b.className='cal-day';grid.appendChild(b);}
  for(let d=1;d<=total;d++){
    const ds=`${S.calY}-${String(S.calM+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const el=document.createElement('div');
    let cls='cal-day';
    if(S.availDates.includes(ds)) cls+=' av';
    if(ds===S.date) cls+=' sel';
    if(ds===todayStr) cls+=' tod';
    el.className=cls; el.textContent=d;
    if(S.availDates.includes(ds)) el.onclick=()=>pickDate(ds);
    grid.appendChild(el);
  }
}

function calNav(dir){
  S.calM+=dir;
  if(S.calM>11){S.calM=0;S.calY++;}
  if(S.calM<0){S.calM=11;S.calY--;}
  renderCal();
}

function pickDate(ds){
  S.date=ds; S.slotId=null; S.slotTime=null; S.slotRink=null;
  const dt=new Date(ds+'T12:00:00');
  S.dateLabel=DAYS_S[dt.getDay()]+' '+MONTHS_S[dt.getMonth()]+' '+dt.getDate();
  document.getElementById('sub-2').textContent=S.dateLabel;
  document.getElementById('sub-3').textContent='';
  renderCal(); syncSidebar(); syncMobileBar();
  // Auto-advance
  setTimeout(()=>goTo(3), 200);
}

// ── Step 3: Time ──────────────────────────────────────────────────────────
async function loadSlots(){
  document.getElementById('step3-sub').textContent='Available times on '+S.dateLabel+' — tap to continue.';
  document.getElementById('slots-loading').style.display='block';
  document.getElementById('slot-grid').innerHTML='';
  document.getElementById('slots-empty').style.display='none';
  let slots=[];
  try{ slots=await fetch('/book/ajax/slots/'+S.svcId+'/'+S.date).then(r=>r.json()); }catch(e){}
  document.getElementById('slots-loading').style.display='none';
  if(!slots.length){ document.getElementById('slots-empty').style.display='block'; return; }
  const grid=document.getElementById('slot-grid');
  slots.forEach(s=>{
    const el=document.createElement('div');
    el.className='slot-card'+(s.id===S.slotId?' selected':'');
    el.innerHTML='<div class="slot-time">'+s.time+'</div><div class="slot-rink">'+s.rink+'</div>';
    el.onclick=()=>pickSlot(s.id,s.time,s.rink,el);
    grid.appendChild(el);
  });
}

function pickSlot(id,time,rink,el){
  S.slotId=id; S.slotTime=time; S.slotRink=rink;
  document.querySelectorAll('.slot-card').forEach(c=>c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('sub-3').textContent=time+' · '+rink;
  syncSidebar(); syncMobileBar();
  // Auto-advance
  setTimeout(()=>goTo(4), 200);
}

// ── Step 4: Info ──────────────────────────────────────────────────────────
function toggleSms(){
  const phone=document.getElementById('f-phone').value.trim();
  const valid=phone.replace(/\D/g,'').length>=10;
  const row=document.getElementById('sms-row');
  const chk=document.getElementById('f-sms');
  if(!row) return;
  if(valid){
    row.style.opacity='1'; row.style.pointerEvents='auto';
    chk.disabled=false;
  } else {
    row.style.opacity='.4'; row.style.pointerEvents='none';
    chk.disabled=true; chk.checked=false;
  }
}
function chk4(){
  const ok=document.getElementById('f-name').value.trim()
        && document.getElementById('f-email').value.includes('@')
        && document.getElementById('f-email-consent').checked
        && document.getElementById('f-policy').checked;
  document.getElementById('btn-4').disabled=!ok;
}

// ── Step 5: Review ────────────────────────────────────────────────────────
function fillReview(){
  const name=document.getElementById('f-name').value.trim();
  const email=document.getElementById('f-email').value.trim();
  const phone=document.getElementById('f-phone').value.trim();
  set('rv-svc',S.svcName); set('rv-date',S.dateLabel); set('rv-time',S.slotTime);
  set('rv-rink',S.slotRink); set('rv-name',name); set('rv-email',email);
  set('rv-phone',phone||'—'); set('rv-price-head','$'+S.svcPrice);
  document.getElementById('h-svc').value=S.svcId;
  document.getElementById('h-slot').value=S.slotId;
  document.getElementById('h-name').value=name;
  document.getElementById('h-email').value=email;
  document.getElementById('h-phone').value=phone;
  document.getElementById('h-notes').value=document.getElementById('f-notes').value;
  document.getElementById('h-email-consent').value=document.getElementById('f-email-consent').checked?'1':'';
  document.getElementById('h-policy').value=document.getElementById('f-policy').checked?'1':'';
  document.getElementById('h-sms').value=document.getElementById('f-sms').checked?'1':'0';
}
function set(id,val){ const el=document.getElementById(id); if(el) el.textContent=val||'—'; }

// ── Sidebar sublabels only (no redundant summary card on desktop) ─────────
function syncSidebar(){
  // sublabels are already updated inline — nothing else needed
}

// ── Mobile bar sync ───────────────────────────────────────────────────────
function syncMobileBar(){
  const bar=document.getElementById('mobile-bar');
  const stepLabels=['Choose Service','Pick a Date','Choose a Time','Your Info','Review'];
  document.getElementById('mb-step').textContent='Step '+S.step+' of 5 — '+stepLabels[S.step-1];
  let val='';
  if(S.slotRink) val=S.slotTime+' · '+S.slotRink;
  else if(S.slotTime) val=S.slotTime;
  else if(S.dateLabel) val=S.dateLabel;
  else if(S.svcName) val=S.svcName;
  else val='Choose a lesson to begin';
  document.getElementById('mb-val').textContent=val;
  document.getElementById('mb-price').textContent=S.svcPrice?'$'+S.svcPrice:'';
  bar.style.display='flex';
}

// ── Pre-fill logged-in client ─────────────────────────────────────────────
@auth('client')
document.addEventListener('DOMContentLoaded',()=>{
  const c=@json(Auth::guard('client')->user());
  if(c){
    document.getElementById('f-name').value=((c.first_name||'')+' '+(c.last_name||'')).trim();
    document.getElementById('f-email').value=c.email||'';
    document.getElementById('f-phone').value=c.phone||'';
    toggleSms(); chk4();
  }
});
@endauth

// Init mobile bar
syncMobileBar();

// ── Phone formatter ───────────────────────────────────────────────────────
function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6)      v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0)  v = '(' + v;
  input.value = v;
}
</script>

@endsection
