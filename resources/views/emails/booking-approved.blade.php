@extends('emails.layout')
@section('content')
<h1 class="greeting">Your Lesson is Confirmed!</h1>

@if($booking->student && $booking->student->random_photo_url)
<div style="text-align:center;margin-bottom:16px;">
  <img src="{{ $booking->student->random_photo_url }}" alt="{{ $booking->student->first_name }}"
    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #001F5B;">
  <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">{{ $booking->student->first_name }}</div>
</div>
@endif

<p class="text">{{ $templateBody ?? "Great news, {$booking->client_name}! Coach Kristine has approved your skating lesson request." }}</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Service</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->service->name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Date</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->date->format('l, F j, Y') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Time</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Location</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->timeSlot->rink->name ?? '' }}@if($booking->timeSlot->rink->address ?? null) &mdash; {{ $booking->timeSlot->rink->address }}@endif</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Price</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">${{ number_format($booking->price_paid, 2) }}</td></tr>
    @if($booking->student_name)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Skater</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->student_name }}@if($booking->student_age), age {{ $booking->student_age }}@endif</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Confirmation</td><td style="padding:5px 0;color:#001F5B;font-size:14px;font-weight:700;">{{ $booking->confirmation_code }}</td></tr>
  </table>
</div>

<div class="info-band">
  <h3>What to Bring</h3>
  <table style="font-size:14px;color:#374151;line-height:2;">
    <tr><td style="padding:0 10px 0 0;">⛸️</td><td>Skates (and hockey gear if applicable)</td></tr>
    <tr><td style="padding:0 10px 0 0;">💧</td><td>Water bottle</td></tr>
    <tr><td style="padding:0 10px 0 0;">⏰</td><td>Arrive 10 minutes early</td></tr>
  </table>
</div>

<div class="highlight-box">
  <strong>Payment:</strong> Venmo, cash, or check accepted at the lesson. Lesson price does not include rink admission fee.
</div>

<p style="text-align:center;">
  <a href="{{ $booking->venmo_link }}" class="cta-btn">Pay via Venmo</a>
</p>
<p style="text-align:center;">
  <a href="https://kristineskates.com/pay/{{ $booking->confirmation_code }}" class="cta-btn-secondary">📅 Add to Calendar</a>
</p>
<p class="text" style="text-align:center;color:#6b7280;font-size:13px;margin-top:4px;">The calendar file (.ics) is also attached to this email.</p>

<div class="warn-band">
  <p><strong>Cancellation Policy:</strong> Cancellations made less than 24 hours before your scheduled lesson time, or no-shows, will be invoiced for the full lesson price.</p>
</div>

<div class="closing-band">
  <p>See You on the Ice!</p>
</div>
@endsection
