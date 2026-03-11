@extends('layouts.app')
@section('title', 'Request Received — Kristine Skates')
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  body{font-family:'DM Sans',sans-serif;}
  .confetti-header{background:linear-gradient(135deg,var(--navy) 0%,#002b87 100%);text-align:center;padding:4rem 1rem;}
  .big-check{font-size:4rem;animation:pop .4s ease-out;}
  @keyframes pop{0%{transform:scale(0);opacity:0}70%{transform:scale(1.2)}100%{transform:scale(1);opacity:1}}
  .confirm-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(2rem,5vw,3.5rem);color:#fff;}
  .confirm-subtitle{color:rgba(255,255,255,.7);font-size:1.1rem;margin-top:.5rem;}
  .detail-card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,31,91,.08);padding:2rem;}
  .detail-row{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;border-bottom:1px solid #f3f4f6;}
  .detail-row:last-child{border-bottom:none;}
  .detail-label{font-size:.85rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;}
  .detail-val{font-weight:600;color:#111827;}
  .status-badge{display:inline-block;background:#fef3c7;color:#92400e;border:1.5px solid #fcd34d;
    border-radius:20px;padding:4px 14px;font-size:.82rem;font-weight:700;letter-spacing:.05em;}
  .next-step{display:flex;align-items:flex-start;gap:.75rem;}
  .step-dot{width:24px;height:24px;border-radius:50%;background:var(--navy);color:#fff;
    font-weight:700;font-size:.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;}
  .home-btn{display:inline-block;background:var(--navy);color:#fff;font-weight:600;
    padding:.9rem 2.5rem;border-radius:8px;transition:background .2s;text-decoration:none;}
  .home-btn:hover{background:var(--red);}

  /* Venmo button */
  .venmo-btn{display:flex;align-items:center;justify-content:center;gap:.75rem;
    background:#3D95CE;color:#fff;border-radius:10px;padding:1rem 2rem;
    font-weight:700;font-size:1rem;text-decoration:none;transition:background .2s;margin-bottom:.75rem;}
  .venmo-btn:hover{background:#2d7ab0;}
  .venmo-logo{width:24px;height:24px;fill:#fff;}
  .venmo-fallback{background:#e8f4fd;border:1.5px solid #bfdbfe;border-radius:10px;
    padding:1rem;text-align:center;font-size:.85rem;color:#1e40af;}

  /* Waiver prompt */
  .waiver-prompt{background:#fff8ed;border:2px solid #fcd34d;border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;}
  .waiver-prompt-title{font-weight:700;color:#92400e;font-size:.95rem;margin-bottom:.4rem;}
  .waiver-btn{display:inline-block;background:#f59e0b;color:#fff;font-weight:700;
    padding:.65rem 1.5rem;border-radius:8px;text-decoration:none;font-size:.88rem;transition:background .2s;}
  .waiver-btn:hover{background:#d97706;}
  .waiver-signed{background:#d1fae5;border:1.5px solid #a7f3d0;border-radius:8px;
    padding:.65rem 1rem;font-size:.83rem;color:#065f46;font-weight:600;}
</style>

<div class="confetti-header">
  <div class="big-check">🎉</div>
  <h1 class="confirm-title">Request Received!</h1>
  <p class="confirm-subtitle">Coach Kristine will review and confirm within 24 hours.</p>
</div>

<div class="max-w-xl mx-auto px-6 py-10">

  {{-- Waiver prompt if not signed --}}
  @if(auth('client')->check() && !auth('client')->user()->waiver_signed_at)
  <div class="waiver-prompt">
    <div class="waiver-prompt-title">⚠️ Liability Waiver Required</div>
    <p style="font-size:.83rem;color:#78350f;margin-bottom:.75rem;">Please sign the liability waiver before your first lesson. Your booking won't be confirmed until this is complete.</p>
    <a href="{{ route('waiver.show') }}" class="waiver-btn">✍️ Sign Waiver Now</a>
  </div>
  @elseif(auth('client')->check() && auth('client')->user()->waiver_signed_at)
  <div class="waiver-signed">✓ Liability waiver on file — you're all set!</div>
  @endif

  {{-- Booking details --}}
  <div class="detail-card mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-3">Booking Details</h2>
    <div class="detail-row">
      <span class="detail-label">Confirmation</span>
      <span class="detail-val" style="font-family:monospace;letter-spacing:.1em;">{{ $booking->confirmation_code }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Service</span>
      <span class="detail-val">{{ $booking->service->name }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Date</span>
      <span class="detail-val">{{ \Carbon\Carbon::parse($booking->date ?? $booking->timeSlot?->date)->format('l, F j, Y') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Time</span>
      <span class="detail-val">{{ \Carbon\Carbon::parse($booking->start_time ?? $booking->timeSlot?->start_time)->format('g:i A') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Location</span>
      <span class="detail-val">{{ $booking->timeSlot?->rink?->name ?? '—' }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Price</span>
      <span class="detail-val">${{ number_format($booking->price_paid, 2) }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Status</span>
      <span><span class="status-badge">Pending Approval</span></span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Confirmation Email</span>
      <span class="detail-val text-sm">{{ $booking->client_email }}</span>
    </div>
  </div>

  {{-- Venmo payment --}}
  @if($booking->price_paid > 0)
  <div class="detail-card mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-1">Payment</h2>
    <p style="font-size:.83rem;color:#6b7280;margin-bottom:1.25rem;">Payment is due at the time of your lesson. You can pay with Venmo or cash.</p>

    @php
      $venmoHandle  = ltrim(config('services.venmo.handle', env('VENMO_HANDLE', 'Kristine-Humphrey')), '@');
      $venmoDisplay = config('services.venmo.display_name', env('VENMO_DISPLAY_NAME', 'Kristine Humphrey'));
      $amount       = number_format($booking->price_paid, 2);
      $note         = 'Skating lesson ' . ($booking->confirmation_code ?? '');
      $venmoDeepLink = 'venmo://paycharge?txn=pay&recipients=' . urlencode($venmoHandle) . '&amount=' . $amount . '&note=' . urlencode($note);
      $venmoWebLink  = 'https://venmo.com/' . urlencode($venmoHandle) . '?txn=pay&amount=' . $amount . '&note=' . urlencode($note);
    @endphp

    {{-- Deep link (opens Venmo app) --}}
    <a href="{{ $venmoDeepLink }}" class="venmo-btn" id="venmo-deep-link">
      <svg class="venmo-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M19.5 2c.8 1.3 1.1 2.6 1.1 4.3 0 5.4-4.6 12.4-8.3 17.3H4.5L1.5 2.9l7.2-.7 1.6 12.8c1.5-2.5 3.3-6.4 3.3-9.1 0-1.5-.3-2.5-.7-3.3L19.5 2z"/>
      </svg>
      Pay ${{ $amount }} via Venmo
    </a>

    {{-- Web fallback --}}
    <div class="venmo-fallback">
      <div style="font-weight:600;margin-bottom:.3rem;">Don't have the Venmo app?</div>
      <a href="{{ $venmoWebLink }}" target="_blank" style="color:#1e40af;font-weight:700;">
        Pay on Venmo.com →
      </a>
      <div style="margin-top:.5rem;font-size:.78rem;">Or send to <strong>@{{ $venmoHandle }}</strong> — include <strong>{{ $booking->confirmation_code }}</strong> in the note.</div>
    </div>

    <p style="font-size:.75rem;color:#9ca3af;margin-top:.75rem;text-align:center;">
      * Rink admission fee is separate and paid at the rink.
    </p>
  </div>
  @endif

  {{-- What's next --}}
  <div class="detail-card mb-8">
    <h2 class="font-bold text-gray-900 text-lg mb-4">What Happens Next</h2>
    <div class="space-y-3">
      @foreach([
        ['Coach Kristine reviews your request', 'Usually within a few hours — she\'ll check her schedule and the rink time.'],
        ['You\'ll get a confirmation email', 'Once approved, you\'ll receive an email with full details.'],
        ['Show up ready to skate', 'Arrive 10 minutes early with your skates and gear.'],
      ] as $n => $step)
      <div class="next-step">
        <div class="step-dot">{{ $n + 1 }}</div>
        <div>
          <div class="font-semibold text-gray-800">{{ $step[0] }}</div>
          <div class="text-sm text-gray-500">{{ $step[1] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <div class="text-center">
    <a href="/" class="home-btn">Back to Home</a>
  </div>
</div>

<script>
// Try Venmo app deep link, fall back to web after 1.5s
document.getElementById('venmo-deep-link')?.addEventListener('click', function(e) {
  setTimeout(function() {
    window.location.href = '{{ $venmoWebLink }}';
  }, 1500);
});
</script>
@endsection
