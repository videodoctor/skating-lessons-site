@extends('emails.layout')
@section('content')
<h1 class="greeting">You're on the Waitlist!</h1>
<p class="text">Hi {{ $interest->name }}, thank you for your interest in skating lessons with Coach Kristine! You've been added to our waitlist.</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    @if($interest->student_name)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Skater</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->student_name }}@if($interest->student_age), age {{ $interest->student_age }}@endif @if($interest->skill_level)· {{ ucfirst($interest->skill_level) }}@endif</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Email</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->email }}</td></tr>
    @if($interest->phone)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Phone</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->phone }}</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Signed Up</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->created_at->format('F j, Y') }}</td></tr>
  </table>
</div>

<div class="info-band">
  <h3>What Happens Next?</h3>
  <p style="font-size:14px;color:#374151;line-height:1.6;margin:0;">Coach Kristine will reach out when lesson times become available. You'll receive an email notification so you can book your preferred time slot.</p>
</div>

<p class="text">In the meantime, if you have any questions, feel free to reply to this email.</p>

<div class="closing-band">
  <p>See You on the Ice!</p>
</div>
@endsection
