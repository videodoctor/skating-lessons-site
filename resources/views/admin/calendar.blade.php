@extends('layouts.admin')
@section('title', 'Booking Calendar — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}

  .cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;}
  .cal-nav{display:flex;align-items:center;gap:.75rem;}
  .cal-nav a{background:#f3f4f6;color:#374151;border-radius:7px;padding:.4rem .9rem;font-weight:600;font-size:.85rem;text-decoration:none;}
  .cal-nav a:hover{background:#e5e7eb;}
  .cal-month{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--navy);}
  .cal-legend{display:flex;gap:1rem;flex-wrap:wrap;font-size:.75rem;}
  .legend-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:4px;}

  .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);border:1.5px solid #e5eaf2;border-radius:12px;overflow:hidden;}
  .cal-dow{background:#f8fafc;padding:.5rem;text-align:center;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;border-bottom:1.5px solid #e5eaf2;}
  .cal-cell{min-height:110px;padding:.4rem;border-right:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;background:#fff;vertical-align:top;position:relative;}
  .cal-cell:nth-child(7n){border-right:none;}
  .cal-cell.other-month{background:#fafafa;}
  .cal-cell.today{background:#fffbf0;}
  .cal-cell.today .cal-day-num{background:var(--red);color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;}
  .cal-day-num{font-size:.8rem;font-weight:700;color:#374151;margin-bottom:.3rem;width:24px;height:24px;display:flex;align-items:center;justify-content:center;}
  .cal-cell.other-month .cal-day-num{color:#d1d5db;}

  .booking-pill{display:block;font-size:.7rem;padding:2px 5px;border-radius:4px;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;line-height:1.4;}
  .booking-pill.status-pending{background:#fef3c7;color:#92400e;}
  .booking-pill.status-confirmed{background:#d1fae5;color:#065f46;}
  .booking-pill.status-cancelled{background:#fee2e2;color:#991b1b;text-decoration:line-through;}
  .booking-pill.status-paid{background:#dbeafe;color:#1e40af;}
  .more-pill{font-size:.68rem;color:#6b7280;padding:1px 4px;cursor:pointer;}

  /* Detail panel */
  .detail-panel{position:fixed;right:0;top:0;bottom:0;width:340px;background:#fff;box-shadow:-4px 0 24px rgba(0,0,0,.1);z-index:200;transform:translateX(100%);transition:transform .25s;overflow-y:auto;}
  .detail-panel.open{transform:translateX(0);}
  .detail-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:199;}
  .detail-overlay.open{display:block;}

  .view-toggle{display:flex;gap:.5rem;margin-bottom:1rem;}
  .view-btn{padding:.4rem .9rem;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;border:1.5px solid #e5eaf2;background:#fff;color:#374151;}
  .view-btn.active{background:var(--navy);color:#fff;border-color:var(--navy);}

  /* List view */
  .list-group{margin-bottom:1.5rem;}
  .list-group-header{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;padding:.4rem 0;border-bottom:1.5px solid #f3f4f6;margin-bottom:.5rem;}
  .list-item{display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:8px;margin-bottom:.25rem;cursor:pointer;}
  .list-item:hover{background:#f8fafc;}
  .list-time{font-size:.78rem;font-weight:700;color:var(--navy);min-width:55px;}
  .list-info{flex:1;min-width:0;}
  .list-name{font-size:.85rem;font-weight:600;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .list-meta{font-size:.72rem;color:#6b7280;}
  .status-badge{font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:8px;}
</style>

<div class="cal-header">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Booking Calendar</h1>
    <p style="color:#6b7280;font-size:.82rem;margin:0;">{{ $bookings->count() }} upcoming bookings</p>
  </div>
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div class="view-toggle">
      <button class="view-btn active" id="btn-month" onclick="setView('month')">Month</button>
      <button class="view-btn" id="btn-list" onclick="setView('list')">List</button>
    </div>
    <div class="cal-nav">
      <a href="{{ route('admin.calendar', ['month' => $prevMonth->format('Y-m')]) }}">← Prev</a>
      <span class="cal-month">{{ $currentMonth->format('F Y') }}</span>
      <a href="{{ route('admin.calendar', ['month' => $nextMonth->format('Y-m')]) }}">Next →</a>
    </div>
    <a href="{{ route('admin.calendar') }}" style="font-size:.8rem;color:#6b7280;">Today</a>
    <a href="{{ route('admin.calendar.ical', ['token' => config('services.calendar.admin_token')]) }}"
       style="background:#f0f4ff;color:#1e40af;border-radius:7px;padding:.4rem .9rem;font-size:.82rem;font-weight:600;text-decoration:none;"
       title="Subscribe in Apple Calendar / Google Calendar">
      📅 Subscribe
    </a>
  </div>
</div>

<div class="cal-legend" style="margin-bottom:1rem;">
  <span><span class="legend-dot" style="background:#92400e;"></span>Pending</span>
  <span><span class="legend-dot" style="background:#065f46;"></span>Confirmed</span>
  <span><span class="legend-dot" style="background:#1e40af;"></span>Paid</span>
  <span><span class="legend-dot" style="background:#991b1b;"></span>Cancelled</span>
</div>

{{-- Month View --}}
<div id="view-month">
  <div class="cal-grid">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
    <div class="cal-dow">{{ $dow }}</div>
    @endforeach

    @foreach($calendarWeeks as $week)
      @foreach($week as $day)
      @php
        $dateKey   = $day['date']->format('Y-m-d');
        $dayBookings = $bookingsByDate[$dateKey] ?? collect();
        $isToday   = $day['date']->isToday();
        $isThisMonth = $day['currentMonth'];
      @endphp
      <div class="cal-cell {{ !$isThisMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
        <div class="cal-day-num">{{ $day['date']->format('j') }}</div>
        @foreach($dayBookings->take(3) as $booking)
        <span class="booking-pill status-{{ $booking->status }}"
              onclick="showDetail({{ $booking->id }})"
              title="{{ $booking->client_name }} — {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}">
          {{ \Carbon\Carbon::parse($booking->start_time)->format('g:i') }} {{ Str::limit($booking->client_name, 12) }}
        </span>
        @endforeach
        @if($dayBookings->count() > 3)
        <span class="more-pill" onclick="showDayList('{{ $dateKey }}')">+{{ $dayBookings->count() - 3 }} more</span>
        @endif
      </div>
      @endforeach
    @endforeach
  </div>
</div>

{{-- List View --}}
<div id="view-list" style="display:none;">
  @forelse($bookingsByDate as $date => $dayBookings)
  @php $d = \Carbon\Carbon::parse($date); @endphp
  <div class="list-group">
    <div class="list-group-header">
      {{ $d->format('l, F j, Y') }}
      @if($d->isToday()) <span style="color:var(--red);margin-left:.5rem;">TODAY</span> @endif
    </div>
    @foreach($dayBookings as $booking)
    <div class="list-item" onclick="showDetail({{ $booking->id }})">
      <div class="list-time">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</div>
      <div class="list-info">
        <div class="list-name">{{ $booking->client_name }}</div>
        <div class="list-meta">
          {{ $booking->service->name ?? 'Lesson' }}
          @if($booking->timeSlot?->rink) · {{ $booking->timeSlot->rink->name }} @endif
          @if($booking->student) · {{ $booking->student->first_name }} @endif
        </div>
      </div>
      <span class="status-badge status-{{ $booking->status }}"
        style="background:{{ ['pending'=>'#fef3c7','confirmed'=>'#d1fae5','cancelled'=>'#fee2e2','paid'=>'#dbeafe'][$booking->status] ?? '#f3f4f6' }};
               color:{{ ['pending'=>'#92400e','confirmed'=>'#065f46','cancelled'=>'#991b1b','paid'=>'#1e40af'][$booking->status] ?? '#374151' }}">
        {{ ucfirst($booking->status) }}
      </span>
    </div>
    @endforeach
  </div>
  @empty
  <div style="text-align:center;padding:3rem;color:#9ca3af;">No upcoming bookings this month.</div>
  @endforelse
</div>

{{-- Detail Panel --}}
<div class="detail-overlay" id="detailOverlay" onclick="closeDetail()"></div>
<div class="detail-panel" id="detailPanel">
  <div style="padding:1.25rem;border-bottom:1.5px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);">Booking Detail</span>
    <button onclick="closeDetail()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#6b7280;">✕</button>
  </div>
  <div id="detailContent" style="padding:1.25rem;">
    <div style="text-align:center;color:#9ca3af;padding:2rem;">Select a booking to view details</div>
  </div>
</div>

{{-- Booking data for JS --}}
<script>
const bookings = @json($bookingsJson);

function setView(v) {
  document.getElementById('view-month').style.display = v === 'month' ? '' : 'none';
  document.getElementById('view-list').style.display  = v === 'list'  ? '' : 'none';
  document.getElementById('btn-month').classList.toggle('active', v === 'month');
  document.getElementById('btn-list').classList.toggle('active', v === 'list');
}

function showDetail(id) {
  const b = bookings[id];
  if (!b) return;
  const statusColors = {pending:'#fef3c7',confirmed:'#d1fae5',cancelled:'#fee2e2',paid:'#dbeafe'};
  const statusText   = {pending:'#92400e',confirmed:'#065f46',cancelled:'#991b1b',paid:'#1e40af'};
  document.getElementById('detailContent').innerHTML = `
    <div style="margin-bottom:1rem;">
      <span style="background:${statusColors[b.status]||'#f3f4f6'};color:${statusText[b.status]||'#374151'};
                   font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:8px;">${b.status.toUpperCase()}</span>
    </div>
    <h2 style="font-size:1.1rem;font-weight:700;color:#111;margin:0 0 .25rem;">${b.client_name}</h2>
    <p style="color:#6b7280;font-size:.82rem;margin:0 0 1.25rem;">${b.client_email || ''}</p>

    <div style="background:#f8fafc;border-radius:8px;padding:1rem;margin-bottom:1rem;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.82rem;">
        <div><span style="color:#9ca3af;">Date</span><br><strong>${b.date}</strong></div>
        <div><span style="color:#9ca3af;">Time</span><br><strong>${b.start_time}</strong></div>
        <div><span style="color:#9ca3af;">Service</span><br><strong>${b.service}</strong></div>
        <div><span style="color:#9ca3af;">Rink</span><br><strong>${b.rink || '—'}</strong></div>
        ${b.student ? `<div><span style="color:#9ca3af;">Student</span><br><strong>${b.student}</strong></div>` : ''}
        ${b.price ? `<div><span style="color:#9ca3af;">Price</span><br><strong>$${b.price}</strong></div>` : ''}
      </div>
    </div>

    ${b.notes ? `<div style="background:#fffbf0;border-left:3px solid #C9A84C;padding:.75rem;border-radius:0 6px 6px 0;font-size:.82rem;color:#374151;margin-bottom:1rem;"><strong>Notes:</strong> ${b.notes}</div>` : ''}

    ${b.confirmation_code ? `<div style="font-size:.75rem;color:#9ca3af;margin-bottom:1rem;">Confirmation: <code>${b.confirmation_code}</code></div>` : ''}

    <div style="display:flex;flex-direction:column;gap:.5rem;">
      <a href="/admin/bookings/${b.id}" style="background:var(--navy);color:#fff;text-align:center;padding:.6rem;border-radius:7px;font-weight:600;font-size:.82rem;text-decoration:none;">View Full Booking</a>
      ${b.client_phone ? `<a href="tel:${b.client_phone}" style="background:#f3f4f6;color:#374151;text-align:center;padding:.6rem;border-radius:7px;font-weight:600;font-size:.82rem;text-decoration:none;">📞 ${b.client_phone}</a>` : ''}
    </div>
  `;
  document.getElementById('detailPanel').classList.add('open');
  document.getElementById('detailOverlay').classList.add('open');
}

function closeDetail() {
  document.getElementById('detailPanel').classList.remove('open');
  document.getElementById('detailOverlay').classList.remove('open');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>
@endsection
