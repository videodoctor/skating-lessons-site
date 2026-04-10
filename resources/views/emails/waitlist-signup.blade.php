@extends('emails.layout')
@section('content')
<h1 class="greeting">New Waitlist Sign-up!</h1>
<p class="text">Someone just joined the waitlist and is interested in skating lessons.</p>

<div class="detail-box">
  <table style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;width:110px;">Name</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:700;">{{ $interest->name }}</td></tr>
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Email</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;"><a href="mailto:{{ $interest->email }}" style="color:#001F5B;">{{ $interest->email }}</a></td></tr>
    @if($interest->phone)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Phone</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->phone }}</td></tr>
    @endif
    @if($interest->student_name)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Skater</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->student_name }}@if($interest->student_age), age {{ $interest->student_age }}@endif @if($interest->skill_level)· {{ ucfirst($interest->skill_level) }}@endif</td></tr>
    @endif
    @if($interest->referred_by)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Referred by</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->referred_by }}</td></tr>
    @endif
    @if($interest->message)
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Message</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->message }}</td></tr>
    @endif
    <tr><td style="padding:5px 0;color:#6b7280;font-size:14px;font-weight:600;">Source</td><td style="padding:5px 0;color:#111827;font-size:14px;font-weight:500;">{{ $interest->source === 'service_waitlist' ? 'Service waitlist' : 'Booking paused page' }}@if($interest->service) — {{ $interest->service->name }}@endif</td></tr>
  </table>
</div>

<p style="text-align:center;">
  <a href="https://kristineskates.com/admin/waitlist" class="cta-btn">View Waitlist</a>
</p>
@endsection
