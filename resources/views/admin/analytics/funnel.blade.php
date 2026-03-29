@extends('layouts.admin')
@section('title', 'Booking Funnel — Admin')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Booking Funnel</h1>
  <div style="display:flex;gap:.5rem;align-items:center;">
    <span style="font-size:.85rem;color:#666;">Period:</span>
    @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $label)
      <a href="?days={{ $d }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;{{ $days == $d ? 'background:var(--navy);color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">{{ $label }}</a>
    @endforeach
    <a href="{{ route('admin.analytics') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;margin-left:.5rem;">Overview</a>
    <a href="{{ route('admin.analytics.activity') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;">Activity</a>
  </div>
</div>

<div style="background:#fff;border-radius:10px;padding:2rem;box-shadow:0 1px 3px rgba(0,0,0,.08);max-width:600px;">
  @php
    $steps = [
      ['label' => 'Homepage Visitors', 'count' => $homepageVisits, 'color' => '#001F5B'],
      ['label' => 'Viewed Booking Page', 'count' => $bookingPageVisits, 'color' => '#1e40af'],
      ['label' => 'Submitted Booking', 'count' => $bookingsSubmitted, 'color' => '#2563eb'],
      ['label' => 'Booking Confirmed', 'count' => $bookingsConfirmed, 'color' => '#16a34a'],
    ];
    $maxCount = max(1, $steps[0]['count']);
  @endphp

  @foreach($steps as $i => $step)
    <div style="margin-bottom:1.5rem;">
      <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.35rem;">
        <span style="font-weight:600;color:#334155;">{{ $step['label'] }}</span>
        <span style="font-size:1.5rem;font-weight:700;color:{{ $step['color'] }};">{{ number_format($step['count']) }}</span>
      </div>
      <div style="height:28px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
        <div style="height:100%;background:{{ $step['color'] }};border-radius:6px;width:{{ $maxCount > 0 ? ($step['count'] / $maxCount) * 100 : 0 }}%;min-width:{{ $step['count'] > 0 ? '4px' : '0' }};transition:width .3s;"></div>
      </div>
      @if($i > 0 && $steps[$i-1]['count'] > 0)
        <div style="font-size:.8rem;color:#94a3b8;margin-top:.25rem;">
          {{ round($step['count'] / $steps[$i-1]['count'] * 100, 1) }}% conversion from previous step
        </div>
      @endif
    </div>
  @endforeach

  <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #f1f5f9;">
    <div style="display:flex;justify-content:space-between;align-items:baseline;">
      <span style="font-weight:600;color:#334155;">New Client Registrations</span>
      <span style="font-size:1.5rem;font-weight:700;color:var(--navy);">{{ number_format($clientRegistrations) }}</span>
    </div>
  </div>
</div>

<p style="margin-top:1.5rem;font-size:.85rem;color:#94a3b8;">
  Unique visitors by IP over the last {{ $days }} days. Booking counts include both guest and registered client bookings.
</p>
@endsection
