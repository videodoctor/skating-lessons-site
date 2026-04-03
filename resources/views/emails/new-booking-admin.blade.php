@extends('emails.layout')
@section('content')
<h1 class="greeting">New Lesson Request!</h1>
<p class="text">A new lesson request has been submitted and needs your review.</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Client</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:700;">{{ $booking->client_name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Email</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;"><a href="mailto:{{ $booking->client_email }}" style="color:#001F5B;">{{ $booking->client_email }}</a></td></tr>
    @if($booking->client_phone)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Phone</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->client_phone }}</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Service</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->service->name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Date</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->date->format('l, F j, Y') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Time</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Rink</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->timeSlot->rink->name ?? '' }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Price</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">${{ number_format($booking->price_paid, 2) }}</td></tr>
    @if($booking->student_name)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Skater</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->student_name }}@if($booking->student_age), age {{ $booking->student_age }}@endif @if($booking->skill_level)&bull; {{ ucfirst($booking->skill_level) }}@endif</td></tr>
    @endif
    @if($booking->referred_by)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Referred by</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->referred_by }}</td></tr>
    @endif
    @if($booking->notes)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Notes</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->notes }}</td></tr>
    @endif
  </table>
</div>

<p style="text-align:center;">
  <a href="https://kristineskates.com/admin/bookings" class="cta-btn">Review Booking</a>
</p>
@endsection
