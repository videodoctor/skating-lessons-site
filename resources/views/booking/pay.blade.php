@extends('layouts.app')
@section('title', 'Pay for Lesson — Kristine Skates')
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;--ice:#E8F5FB;}
  body{font-family:'DM Sans',sans-serif;}
  .pay-header{background:linear-gradient(135deg,var(--navy) 0%,#002b87 100%);text-align:center;padding:3rem 1rem;}
  .pay-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(2rem,5vw,3rem);color:#fff;}
  .pay-sub{color:rgba(255,255,255,.7);font-size:1rem;margin-top:.4rem;}
  .detail-card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,31,91,.08);padding:2rem;}
  .detail-row{display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid #f3f4f6;}
  .detail-row:last-child{border-bottom:none;}
  .detail-label{font-size:.82rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;}
  .detail-val{font-weight:600;color:#111827;}
  .amount-big{font-family:'Bebas Neue',sans-serif;font-size:3rem;color:var(--navy);text-align:center;line-height:1;}
  .venmo-btn{display:flex;align-items:center;justify-content:center;gap:.75rem;
    background:#3D95CE;color:#fff;border-radius:10px;padding:1rem 2rem;
    font-weight:700;font-size:1.05rem;text-decoration:none;transition:background .2s;width:100%;}
  .venmo-btn:hover{background:#2d7ab0;color:#fff;}
  .venmo-logo{width:24px;height:24px;fill:#fff;}
  .venmo-fallback{background:#e8f4fd;border:1.5px solid #bfdbfe;border-radius:10px;
    padding:1rem;text-align:center;font-size:.85rem;color:#1e40af;}
  .cash-option{background:var(--ice);border:1.5px solid #bfdbfe;border-radius:10px;padding:1.25rem;text-align:center;}
  .paid-badge{display:inline-block;background:#d1fae5;color:#065f46;border:1.5px solid #a7f3d0;
    border-radius:20px;padding:6px 18px;font-size:.9rem;font-weight:700;}
  .cancelled-badge{display:inline-block;background:#fee2e2;color:#991b1b;border:1.5px solid #fecaca;
    border-radius:20px;padding:6px 18px;font-size:.9rem;font-weight:700;}
</style>

<div class="pay-header">
  <h1 class="pay-title">Pay for Your Lesson</h1>
  <p class="pay-sub">Booking {{ $booking->confirmation_code }}</p>
</div>

<div class="max-w-md mx-auto px-6 py-8">

  @php
    $isPaid = in_array($booking->payment_status, ['paid']) || $booking->cash_paid_at || $booking->venmo_confirmed_at;
    $isCancelled = in_array($booking->status, ['cancelled', 'rejected']);
    $date = \Carbon\Carbon::parse($booking->date ?? $booking->timeSlot?->date);
    $time = \Carbon\Carbon::parse($booking->start_time ?? $booking->timeSlot?->start_time);
    $rink = $booking->timeSlot?->rink?->name ?? 'TBD';
  @endphp

  @if($isCancelled)
    <div class="text-center mb-6">
      <span class="cancelled-badge">This booking has been cancelled</span>
    </div>
  @elseif($isPaid)
    <div class="text-center mb-6">
      <span class="paid-badge">Payment received — thank you!</span>
    </div>
  @endif

  {{-- Lesson details --}}
  <div class="detail-card mb-6">
    <div class="detail-row">
      <span class="detail-label">Lesson</span>
      <span class="detail-val">{{ $booking->service?->name ?? 'Skating Lesson' }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Date</span>
      <span class="detail-val">{{ $date->format('l, M j, Y') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Time</span>
      <span class="detail-val">{{ $time->format('g:i A') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Location</span>
      <span class="detail-val">{{ $rink }}</span>
    </div>
    @if($booking->client_name)
    <div class="detail-row">
      <span class="detail-label">Student</span>
      <span class="detail-val">{{ $booking->student?->first_name ?? $booking->client_name }}</span>
    </div>
    @endif
  </div>

  @if(!$isCancelled && !$isPaid && $booking->price_paid > 0)
    {{-- Amount due --}}
    <div class="detail-card mb-6 text-center">
      <div style="font-size:.82rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Amount Due</div>
      <div class="amount-big">${{ number_format($booking->price_paid, 2) }}</div>
    </div>

    @php
      $venmoHandle  = ltrim(config('services.venmo.handle', 'Kristine-Humphrey'), '@');
      $amount       = number_format($booking->price_paid, 2);
      $note         = 'Skating lesson ' . $booking->confirmation_code;
      $venmoDeepLink = 'venmo://paycharge?txn=pay&recipients=' . urlencode($venmoHandle) . '&amount=' . $amount . '&note=' . urlencode($note);
      $venmoWebLink  = 'https://venmo.com/' . urlencode($venmoHandle) . '?txn=pay&amount=' . $amount . '&note=' . urlencode($note);
    @endphp

    {{-- Venmo button --}}
    <a href="{{ $venmoDeepLink }}" class="venmo-btn mb-3" id="venmo-deep-link">
      <svg class="venmo-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M19.5 2c.8 1.3 1.1 2.6 1.1 4.3 0 5.4-4.6 12.4-8.3 17.3H4.5L1.5 2.9l7.2-.7 1.6 12.8c1.5-2.5 3.3-6.4 3.3-9.1 0-1.5-.3-2.5-.7-3.3L19.5 2z"/>
      </svg>
      Pay ${{ $amount }} with Venmo
    </a>

    {{-- Venmo web fallback --}}
    <div class="venmo-fallback mb-4">
      <div style="font-weight:600;margin-bottom:.3rem;">No Venmo app?</div>
      <a href="{{ $venmoWebLink }}" target="_blank" style="color:#1e40af;font-weight:700;">Pay on Venmo.com &rarr;</a>
      <div style="margin-top:.4rem;font-size:.78rem;">Or send to <strong>@{{ $venmoHandle }}</strong> with note <strong>{{ $booking->confirmation_code }}</strong></div>
    </div>

    {{-- Cash option --}}
    <div class="cash-option">
      <div style="font-weight:600;color:var(--navy);margin-bottom:.25rem;">Prefer Cash?</div>
      <div style="font-size:.85rem;color:#6b7280;">You can pay Coach Kristine in cash at the rink before your lesson.</div>
    </div>

    <p style="font-size:.72rem;color:#9ca3af;margin-top:1rem;text-align:center;">
      * Rink admission fee is separate and paid at the rink.
    </p>

    <script>
    document.getElementById('venmo-deep-link')?.addEventListener('click', function(e) {
      setTimeout(function() { window.location.href = '{{ $venmoWebLink }}'; }, 1500);
    });
    </script>
  @elseif(!$isCancelled && !$isPaid)
    <div class="detail-card text-center">
      <div style="font-size:.9rem;color:#6b7280;">No payment required for this booking.</div>
    </div>
  @endif

  <div class="text-center" style="margin-top:2rem;">
    <a href="/" style="color:var(--navy);font-weight:600;text-decoration:none;font-size:.9rem;">&larr; Back to kristineskates.com</a>
  </div>
</div>
@endsection
