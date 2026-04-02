@extends('layouts.admin')
@section('title', 'Schedule — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;}
  .cal-dow{text-align:center;font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;padding:6px 0;}
  .cal-cell{min-height:90px;background:#fff;border-radius:6px;border:1.5px solid #e5eaf2;padding:4px;cursor:pointer;transition:border-color .15s;position:relative;}
  .cal-cell:hover{border-color:#93c5fd;}
  .cal-cell.today{border-color:var(--red);}
  .cal-cell.has-pending{border-color:#f59e0b;}
  .cal-date{font-size:.8rem;font-weight:700;color:#374151;margin-bottom:3px;}
  .cal-date.today-num{color:var(--red);}
  .booking-chip{font-size:.65rem;font-weight:600;border-radius:3px;padding:2px 5px;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;}
  .chip-pending{background:#fef3c7;color:#92400e;}
  .chip-confirmed{background:#d1fae5;color:#065f46;}
  .chip-slot{background:#eff6ff;color:#1e40af;}
  .slot-more{font-size:.62rem;color:#9ca3af;padding:1px 4px;}
  /* Modal */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;display:none;align-items:center;justify-content:center;}
  .modal-overlay.active{display:flex;}
  .modal-box{background:#fff;border-radius:12px;padding:2rem;max-width:420px;width:90%;max-height:80vh;overflow-y:auto;}
  .modal-title{font-family:'Bebas Neue',sans-serif;font-size:1.6rem;color:var(--navy);margin-bottom:1rem;}
  .form-label{display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.3rem;}
  .form-input{width:100%;padding:.6rem .9rem;border:2px solid #e5eaf2;border-radius:6px;font-size:.9rem;transition:border .15s;}
  .form-input:focus{outline:none;border-color:var(--navy);}
  .btn-primary{background:var(--navy);color:#fff;padding:.7rem 1.5rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;transition:background .2s;}
  .btn-primary:hover{background:var(--red);}
  .btn-danger{background:#fee2e2;color:#991b1b;padding:.6rem 1.2rem;border-radius:6px;font-weight:600;border:1.5px solid #fca5a5;cursor:pointer;}
  .btn-ghost{background:#f3f4f6;color:#374151;padding:.6rem 1.2rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;}
</style>

<div class="flex justify-between items-center mb-6">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy)">Schedule</h1>
    <p class="text-gray-500 text-sm">Click any day to view bookings or add/block slots</p>
  </div>
  <div class="flex items-center gap-3">
    <a href="?month={{ $prevMonth }}" class="btn-ghost" style="padding:.5rem .9rem">‹ Prev</a>
    <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy)">{{ $currentMonth->format('F Y') }}</span>
    <a href="?month={{ $nextMonth }}" class="btn-ghost" style="padding:.5rem .9rem">Next ›</a>
  </div>
</div>

<!-- Block/Open Date Range -->
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1rem;">
  <form method="POST" action="{{ route('admin.slots.block-range') }}" class="flex flex-wrap items-end gap-3"
        onsubmit="return confirm(this.action_type.value === 'block' ? 'Block all open slots in this range?' : 'Re-open all unbooked slots in this range?')">
    @csrf
    <div>
      <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px;">From</label>
      <input type="date" name="start_date" required style="border:1.5px solid #dbe4ff;border-radius:6px;padding:5px 8px;font-size:.85rem;">
    </div>
    <div>
      <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px;">To</label>
      <input type="date" name="end_date" required style="border:1.5px solid #dbe4ff;border-radius:6px;padding:5px 8px;font-size:.85rem;">
    </div>
    <div>
      <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:2px;">Action</label>
      <select name="action" style="border:1.5px solid #dbe4ff;border-radius:6px;padding:5px 8px;font-size:.85rem;">
        <option value="block">Block slots</option>
        <option value="open">Open slots</option>
      </select>
    </div>
    <button type="submit" class="btn-danger" style="padding:.5rem 1rem;font-size:.82rem;">Apply to Range</button>
  </form>
</div>

<!-- Legend -->
<div class="flex gap-4 text-xs mb-4">
  <span class="booking-chip chip-pending px-2 py-1">Pending</span>
  <span class="booking-chip chip-confirmed px-2 py-1">Confirmed</span>
  <span class="booking-chip chip-slot px-2 py-1">Open Slot</span>
  <span style="font-size:.75rem;color:#6b7280">Today border = <span style="color:var(--red)">red</span> · Pending border = <span style="color:#f59e0b">amber</span></span>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
  <div class="cal-grid mb-1">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
    <div class="cal-dow">{{ $dow }}</div>
    @endforeach
  </div>
  <div class="cal-grid">
    @for($empty = 0; $empty < $startDow; $empty++)
    <div></div>
    @endfor

    @for($day = 1; $day <= $daysInMonth; $day++)
    @php
      $d = $currentMonth->copy()->setDay($day);
      $dateStr = $d->format('Y-m-d');
      $dayBookings = $bookingsByDate[$dateStr] ?? collect();
      $daySlots    = $slotsByDate[$dateStr] ?? collect();
      $isToday     = $d->isToday();
      $hasPending  = $dayBookings->where('status','pending')->count() > 0;
    @endphp
    <div class="cal-cell {{ $isToday ? 'today' : '' }} {{ $hasPending ? 'has-pending' : '' }}"
         onclick="openDay('{{ $dateStr }}', {{ $dayBookings->count() }}, {{ $daySlots->count() }})">
      <div class="cal-date {{ $isToday ? 'today-num' : '' }}">{{ $day }}</div>

      @foreach($dayBookings->take(3) as $b)
      <span class="booking-chip {{ $b->status === 'pending' ? 'chip-pending' : 'chip-confirmed' }}">
        {{ \Carbon\Carbon::parse($b->start_time)->format('g:ia') }} {{ Str::limit($b->client_name, 10) }}
      </span>
      @endforeach

      @foreach($daySlots->take(3) as $s)
      <span class="booking-chip chip-slot">{{ \Carbon\Carbon::parse($s->start_time)->format('g:ia') }}</span>
      @endforeach
      @if($daySlots->count() > 3)
      <div class="slot-more">+{{ $daySlots->count() - 3 }} more slots</div>
      @endif

      @if($dayBookings->count() > 3)
      <div class="slot-more">+{{ $dayBookings->count() - 3 }} more</div>
      @endif
    </div>
    @endfor
  </div>
</div>

<!-- Day detail modal -->
<div id="day-modal" class="modal-overlay" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <h2 class="modal-title" id="modal-date-title">Date</h2>
    <div id="modal-content"></div>
    <div class="flex gap-3 mt-4">
      <button onclick="showAddSlot()" class="btn-primary text-sm">+ Add Slot</button>
      <button onclick="showBlockDay()" class="btn-danger text-sm">🚫 Block Day</button>
      <button onclick="closeModal()" class="btn-ghost text-sm">Close</button>
    </div>
  </div>
</div>

<!-- Add Slot modal -->
<div id="add-slot-modal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('active')">
  <div class="modal-box">
    <h2 class="modal-title">Add Open Slot</h2>
    <form method="POST" action="{{ route('admin.slots.store') }}">
      @csrf
      <input type="hidden" name="date" id="add-slot-date">
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div>
          <label class="form-label">Start Time</label>
          <input type="time" name="start_time" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Duration</label>
          <select name="duration_minutes" class="form-input">
            <option value="30">30 min</option>
            <option value="60">60 min</option>
          </select>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Rink</label>
        <select name="rink_id" class="form-input" required>
          @foreach($rinks as $rink)
          <option value="{{ $rink->id }}">{{ $rink->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex gap-3">
        <button type="submit" class="btn-primary">Add Slot</button>
        <button type="button" onclick="document.getElementById('add-slot-modal').classList.remove('active')" class="btn-ghost">Cancel</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const bookingData = @json($bookingsJson);
const slotData = @json($slotsJson);
let currentDate = '';

function openDay(dateStr, bookingCount, slotCount) {
  currentDate = dateStr;
  document.getElementById('modal-date-title').textContent = new Date(dateStr + 'T12:00:00').toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric'});
  const bookings = bookingData[dateStr] || [];
  const slots    = slotData[dateStr]    || [];
  let html = '';
  if (bookings.length) {
    html += '<h3 class="font-semibold text-gray-700 text-sm mb-2">Bookings</h3>';
    bookings.forEach(b => {
      const badge = b.status === 'pending' ? 'chip-pending' : 'chip-confirmed';
      html += `<div class="flex justify-between items-center py-2 border-b border-gray-100">
        <div>
          <span class="booking-chip ${badge} inline-block mr-2">${b.status}</span>
          <span class="font-semibold text-sm">${b.client_name}</span>
          <span class="text-gray-400 text-xs ml-2">${formatTime(b.start_time)}</span>
        </div>
        <a href="/admin/bookings?date=${dateStr}" class="text-xs text-blue-600 hover:underline">View →</a>
      </div>`;
    });
  }
  if (slots.length) {
    html += '<h3 class="font-semibold text-gray-700 text-sm mb-2 mt-3">Open Slots</h3>';
    slots.forEach(s => {
      html += `<div class="flex justify-between items-center py-2 border-b border-gray-100">
        <span class="text-sm">${formatTime(s.start_time)}</span>
        <form method="POST" action="/admin/slots/${s.id}/delete" class="inline">
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="text-xs text-red-500 hover:text-red-700" onclick="return confirm('Remove this slot?')">Remove</button>
        </form>
      </div>`;
    });
  }
  if (!bookings.length && !slots.length) html = '<p class="text-gray-400 text-sm">No bookings or slots for this day.</p>';
  document.getElementById('modal-content').innerHTML = html;
  document.getElementById('day-modal').classList.add('active');
}

function closeModal() { document.getElementById('day-modal').classList.remove('active'); }

function showAddSlot() {
  document.getElementById('add-slot-date').value = currentDate;
  document.getElementById('add-slot-modal').classList.add('active');
}

function showBlockDay() {
  if (confirm('Block all open slots for ' + currentDate + '?')) {
    const form = document.createElement('form');
    form.method = 'POST'; form.action = '/admin/slots/block-day';
    form.innerHTML = `<input name="_token" value="{{ csrf_token() }}"><input name="date" value="${currentDate}">`;
    document.body.appendChild(form); form.submit();
  }
}

function formatTime(t) {
  const [h, m] = t.split(':');
  const hr = parseInt(h); const ampm = hr >= 12 ? 'PM' : 'AM';
  return `${hr % 12 || 12}:${m} ${ampm}`;
}
</script>
@endpush
@endsection
