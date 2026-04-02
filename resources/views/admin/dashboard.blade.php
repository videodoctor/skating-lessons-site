@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .dash-greeting{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;}
  .dash-sub{color:#6b7280;font-size:.88rem;}
  .section-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;margin-bottom:1.25rem;overflow:hidden;}
  .section-header{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:1px solid #f3f4f6;cursor:pointer;user-select:none;}
  .section-title{font-family:'Bebas Neue',sans-serif;font-size:1.15rem;color:var(--navy);display:flex;align-items:center;gap:.5rem;}
  .section-toggle{width:36px;height:20px;border-radius:10px;background:#d1d5db;position:relative;cursor:pointer;transition:background .2s;flex-shrink:0;}
  .section-toggle.on{background:var(--navy);}
  .section-toggle::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;}
  .section-toggle.on::after{transform:translateX(16px);}
  .section-body{padding:1rem 1.25rem;}
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.75rem;}
  .stat-box{text-align:center;padding:.75rem .5rem;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;}
  .stat-num{font-size:1.6rem;font-weight:800;color:var(--navy);line-height:1.2;}
  .stat-num.warn{color:#f59e0b;}
  .stat-num.good{color:#10b981;}
  .stat-label{font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-top:2px;}
  .section-link{font-size:.82rem;color:var(--navy);font-weight:600;text-decoration:none;}
  .section-link:hover{text-decoration:underline;}
  /* Mini booking cards for mobile */
  .mini-booking{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #f3f4f6;font-size:.85rem;}
  .mini-booking:last-child{border-bottom:none;}
  .pill{padding:2px 8px;border-radius:10px;font-size:.68rem;font-weight:700;}
  .pill-pending{background:#fef3c7;color:#92400e;}
  .pill-confirmed{background:#d1fae5;color:#065f46;}
  .pill-cancelled{background:#f3f4f6;color:#6b7280;}
  .scraper-row{display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f3f4f6;font-size:.85rem;}
  .scraper-row:last-child{border-bottom:none;}
  .scraper-ok{color:#10b981;font-weight:600;font-size:.75rem;}
  .scraper-stale{color:#f59e0b;font-weight:600;font-size:.75rem;}
  .scraper-fail{color:#ef4444;font-weight:600;font-size:.75rem;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem;">
  <div>
    <h1 class="dash-greeting">Dashboard</h1>
    <p class="dash-sub">{{ now()->format('l, F j, Y') }}</p>
  </div>
  <div style="font-size:.78rem;color:#9ca3af;">Toggle sections with the switches</div>
</div>

@php
  $user = Auth::user();
  $sections = [
    'bookings'  => ['icon' => '📋', 'label' => 'Bookings'],
    'schedule'  => ['icon' => '📅', 'label' => 'Schedule'],
    'clients'   => ['icon' => '👥', 'label' => 'Clients & Students'],
    'analytics' => ['icon' => '📈', 'label' => 'Analytics'],
    'payments'  => ['icon' => '💰', 'label' => 'Payments'],
    'scraper'   => ['icon' => '🔧', 'label' => 'Scraper'],
    'waitlist'  => ['icon' => '📋', 'label' => 'Waitlist'],
  ];
@endphp

{{-- ═══ BOOKINGS ═══ --}}
@php $show = $user->dashboardPref('bookings'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('bookings', this)">
    <div class="section-title">📋 Bookings</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="bookings"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div class="stat-grid" style="margin-bottom:1rem;">
      <div class="stat-box">
        <div class="stat-num {{ ($bookings['pending'] ?? 0) > 0 ? 'warn' : '' }}">{{ $bookings['pending'] ?? 0 }}</div>
        <div class="stat-label">Pending</div>
      </div>
      <div class="stat-box">
        <div class="stat-num good">{{ $bookings['confirmed'] ?? 0 }}</div>
        <div class="stat-label">Confirmed</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $bookings['upcoming'] ?? 0 }}</div>
        <div class="stat-label">Upcoming</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $bookings['today'] ?? 0 }}</div>
        <div class="stat-label">Today</div>
      </div>
      <div class="stat-box">
        <div class="stat-num {{ ($bookings['unpaid'] ?? 0) > 0 ? 'warn' : '' }}">{{ $bookings['unpaid'] ?? 0 }}</div>
        <div class="stat-label">Unpaid</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $bookings['this_week'] ?? 0 }}</div>
        <div class="stat-label">This Week</div>
      </div>
    </div>
    @if(isset($recentBookings) && $recentBookings->count())
    <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">Recent</div>
    @foreach($recentBookings as $b)
    <div class="mini-booking">
      <div>
        <span style="font-weight:600;">{{ $b->client_name ?: 'Guest' }}</span>
        <span style="color:#9ca3af;margin-left:.25rem;">{{ $b->service->name ?? '' }}</span>
      </div>
      <div style="display:flex;align-items:center;gap:.5rem;">
        <span style="color:#6b7280;font-size:.8rem;">{{ $b->date?->format('M j') }} {{ $b->start_time ? \Carbon\Carbon::parse($b->start_time)->format('g:ia') : '' }}</span>
        <span class="pill pill-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
      </div>
    </div>
    @endforeach
    @endif
    <div style="margin-top:.75rem;"><a href="{{ route('admin.bookings.index') }}" class="section-link">View All Bookings →</a></div>
  </div>
  @endif
</div>

{{-- ═══ SCHEDULE ═══ --}}
@php $show = $user->dashboardPref('schedule'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('schedule', this)">
    <div class="section-title">📅 Schedule</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="schedule"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div class="stat-grid">
      <div class="stat-box">
        <div class="stat-num good">{{ $schedule['open_slots'] ?? 0 }}</div>
        <div class="stat-label">Open Slots</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $schedule['blocked_slots'] ?? 0 }}</div>
        <div class="stat-label">Blocked</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $schedule['next_7_days'] ?? 0 }}</div>
        <div class="stat-label">Next 7 Days</div>
      </div>
    </div>
    <div style="margin-top:.75rem;"><a href="{{ route('admin.schedule') }}" class="section-link">Manage Schedule →</a></div>
  </div>
  @endif
</div>

{{-- ═══ CLIENTS & STUDENTS ═══ --}}
@php $show = $user->dashboardPref('clients'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('clients', this)">
    <div class="section-title">👥 Clients & Students</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="clients"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <div>
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Clients</div>
        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(80px,1fr));">
          <div class="stat-box"><div class="stat-num">{{ $clients['total'] ?? 0 }}</div><div class="stat-label">Total</div></div>
          <div class="stat-box"><div class="stat-num good">{{ $clients['new_7d'] ?? 0 }}</div><div class="stat-label">New (7d)</div></div>
          <div class="stat-box"><div class="stat-num">{{ $clients['with_bookings'] ?? 0 }}</div><div class="stat-label">Booked</div></div>
        </div>
        <div style="margin-top:.5rem;"><a href="{{ route('admin.clients.index') }}" class="section-link">All Clients →</a></div>
      </div>
      <div>
        <div style="font-size:.78rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Students</div>
        <div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(80px,1fr));">
          <div class="stat-box"><div class="stat-num">{{ $students['total'] ?? 0 }}</div><div class="stat-label">Total</div></div>
          <div class="stat-box"><div class="stat-num {{ ($students['orphaned'] ?? 0) > 0 ? 'warn' : '' }}">{{ $students['orphaned'] ?? 0 }}</div><div class="stat-label">Orphaned</div></div>
          <div class="stat-box"><div class="stat-num good">{{ $students['active'] ?? 0 }}</div><div class="stat-label">Active</div></div>
        </div>
        <div style="margin-top:.5rem;"><a href="{{ route('admin.students.index') }}" class="section-link">All Students →</a></div>
      </div>
    </div>
  </div>
  @endif
</div>

{{-- ═══ ANALYTICS ═══ --}}
@php $show = $user->dashboardPref('analytics'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('analytics', this)">
    <div class="section-title">📈 Analytics</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="analytics"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div class="stat-grid">
      <div class="stat-box">
        <div class="stat-num">{{ $analytics['today'] ?? 0 }}</div>
        <div class="stat-label">Today Visits</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $analytics['week'] ?? 0 }}</div>
        <div class="stat-label">7-Day Visits</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $analytics['unique_7d'] ?? 0 }}</div>
        <div class="stat-label">Unique (7d)</div>
      </div>
    </div>
    <div style="margin-top:.75rem;"><a href="{{ route('admin.analytics') }}" class="section-link">Full Analytics →</a></div>
  </div>
  @endif
</div>

{{-- ═══ PAYMENTS ═══ --}}
@php $show = $user->dashboardPref('payments'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('payments', this)">
    <div class="section-title">💰 Payments</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="payments"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div class="stat-grid">
      <div class="stat-box">
        <div class="stat-num {{ ($payments['unpaid_bookings'] ?? 0) > 0 ? 'warn' : '' }}">{{ $payments['unpaid_bookings'] ?? 0 }}</div>
        <div class="stat-label">Unpaid</div>
      </div>
      <div class="stat-box">
        <div class="stat-num {{ ($payments['unlinked_venmo'] ?? 0) > 0 ? 'warn' : '' }}">{{ $payments['unlinked_venmo'] ?? 0 }}</div>
        <div class="stat-label">Unlinked Venmo</div>
      </div>
      <div class="stat-box">
        <div class="stat-num good">${{ number_format($payments['revenue_30d'] ?? 0, 0) }}</div>
        <div class="stat-label">Revenue (30d)</div>
      </div>
    </div>
    <div style="margin-top:.75rem;"><a href="{{ route('admin.venmo.index') }}" class="section-link">Venmo Dashboard →</a></div>
  </div>
  @endif
</div>

{{-- ═══ SCRAPER ═══ --}}
@php $show = $user->dashboardPref('scraper'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('scraper', this)">
    <div class="section-title">🔧 Scraper</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="scraper"></div>
  </div>
  @if($show)
  <div class="section-body">
    @if(isset($scraperRuns) && $scraperRuns->count())
      @foreach($scraperRuns as $run)
      <div class="scraper-row">
        <div>
          <span style="font-weight:600;">{{ $run->rink->name ?? 'Unknown' }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;">
          <span style="color:#6b7280;font-size:.78rem;">{{ $run->created_at->diffForHumans() }}</span>
          @if($run->status === 'success')
            <span class="scraper-ok">OK</span>
          @elseif($run->created_at->lt(now()->subDays(2)))
            <span class="scraper-stale">Stale</span>
          @else
            <span class="scraper-fail">{{ ucfirst($run->status ?? 'unknown') }}</span>
          @endif
        </div>
      </div>
      @endforeach
    @else
      <div style="color:#9ca3af;font-size:.85rem;">No scraper runs recorded.</div>
    @endif
    <div style="margin-top:.75rem;"><a href="{{ route('admin.scraper.index') }}" class="section-link">Scraper Dashboard →</a></div>
  </div>
  @endif
</div>

{{-- ═══ WAITLIST ═══ --}}
@php $show = $user->dashboardPref('waitlist'); @endphp
<div class="section-card">
  <div class="section-header" onclick="toggleSection('waitlist', this)">
    <div class="section-title">📋 Waitlist</div>
    <div class="section-toggle {{ $show ? 'on' : '' }}" data-section="waitlist"></div>
  </div>
  @if($show)
  <div class="section-body">
    <div class="stat-grid">
      <div class="stat-box">
        <div class="stat-num {{ ($waitlist['paused'] ?? false) ? 'warn' : 'good' }}">{{ ($waitlist['paused'] ?? false) ? 'Paused' : 'Open' }}</div>
        <div class="stat-label">Booking Status</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $waitlist['entries'] ?? 0 }}</div>
        <div class="stat-label">Total Sign-ups</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ $waitlist['recent'] ?? 0 }}</div>
        <div class="stat-label">New (7d)</div>
      </div>
    </div>
    <div style="margin-top:.75rem;"><a href="{{ route('admin.waitlist.index') }}" class="section-link">Manage Waitlist →</a></div>
  </div>
  @endif
</div>

<script>
function toggleSection(section, headerEl) {
  const toggle = headerEl.querySelector('.section-toggle');
  const isOn = toggle.classList.contains('on');
  const newState = !isOn;

  toggle.classList.toggle('on');

  fetch('{{ route("admin.dashboard.prefs") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ section: section, visible: newState })
  }).then(() => {
    // Reload to show/hide section data
    window.location.reload();
  });
}
</script>
@endsection
