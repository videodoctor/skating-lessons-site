@extends('layouts.app')
@section('title', 'Request Received — Kristine Skates')
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;}
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
    padding:.9rem 2.5rem;border-radius:8px;transition:background .2s;}
  .home-btn:hover{background:var(--red);}
</style>

<div class="confetti-header">
  <div class="big-check">🎉</div>
  <h1 class="confirm-title">Request Received!</h1>
  <p class="confirm-subtitle">Coach Kristine will review and confirm within 24 hours.</p>
</div>

<div class="max-w-xl mx-auto px-6 py-10">
  <!-- Booking details -->
  <div class="detail-card mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-3">Booking Details</h2>
    <div class="detail-row">
      <span class="detail-label">Service</span>
      <span class="detail-val">{{ $booking->service->name }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Date</span>
      <span class="detail-val">{{ $booking->timeSlot->date->format('l, F j, Y') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Time</span>
      <span class="detail-val">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('g:i A') }}</span>
    </div>
    <div class="detail-row">
      <span class="detail-label">Location</span>
      <span class="detail-val">{{ $booking->timeSlot->rink->name }}</span>
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

  <!-- What's next -->
  <div class="detail-card mb-8">
    <h2 class="font-bold text-gray-900 text-lg mb-4">What Happens Next</h2>
    <div class="space-y-3">
      @foreach([
        ['Coach Kristine reviews your request', 'Usually within a few hours — she\'ll check her schedule and the rink time.'],
        ['You\'ll get a confirmation email', 'Once approved, you\'ll receive an email with full details and payment instructions.'],
        ['Show up ready to skate', 'Arrive 10 minutes early with your skates and gear. Payment is accepted at end of lesson.'],
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
    <p class="text-xs text-gray-400 mt-4">* Lesson price does not include rink admission fee.</p>
  </div>

  <div class="text-center">
    <a href="/" class="home-btn">Back to Home</a>
  </div>
</div>
@endsection
