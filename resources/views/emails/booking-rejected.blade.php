@extends('emails.layout')
@section('content')
<h1 class="greeting">Lesson Request Update</h1>
<p class="text">{{ $templateBody ?? "Hi {$booking->client_name}, unfortunately we're unable to accommodate your lesson request at this time." }}</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Service</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->service->name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Date</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->date->format('l, F j, Y') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Time</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td></tr>
  </table>
</div>

<p class="text">We'd love to find another time that works for you!</p>

<p style="text-align:center;">
  <a href="https://kristineskates.com/book" class="cta-btn">Book a Different Time</a>
</p>

<p class="text">If you have any questions, please reply to this email.</p>

<div class="closing-band">
  <p>Hope to See You on the Ice Soon!</p>
</div>
@endsection
