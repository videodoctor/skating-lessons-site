@extends('emails.layout')
@section('content')
<h1 class="greeting">Lesson Request Received!</h1>

@if($booking->student && $booking->student->random_photo_url)
<div style="text-align:center;margin-bottom:16px;">
  <img src="{{ $booking->student->random_photo_url }}" alt="{{ $booking->student->first_name }}"
    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #001F5B;">
</div>
@endif

<p class="text">{{ $templateBody ?? "Thank you for your lesson request, {$booking->client_name}! Coach Kristine will review your request and email you confirmation shortly." }}</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Service</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->service->name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Date</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->date->format('l, F j, Y') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Time</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Location</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->timeSlot->rink->name ?? '' }}</td></tr>
    @if($booking->student_name)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Skater</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $booking->student_name }}@if($booking->student_age), age {{ $booking->student_age }}@endif</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Confirmation</td><td style="padding:5px 0;color:#001F5B;font-size:14px;font-weight:700;">{{ $booking->confirmation_code }}</td></tr>
  </table>
</div>

<div class="highlight-box">
  <strong>Note:</strong> Lesson price does not include rink admission fee. Payment accepted at end of lesson.
</div>

<p class="text">If you have any questions, please reply to this email.</p>

<div class="closing-band">
  <p>See You on the Ice!</p>
</div>
@endsection
