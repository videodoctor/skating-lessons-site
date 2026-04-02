@extends('layouts.admin')
@section('title', 'Manage Bookings')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .filter-tab { padding:.5rem 1rem; font-size:.85rem; font-weight:600; border-bottom:2px solid transparent; color:#6b7280; text-decoration:none; }
  .filter-tab.active { border-bottom-color:var(--navy); color:var(--navy); }
  .filter-tab:hover { color:var(--navy); }
  .tbl th { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; padding:.6rem 1rem; text-align:left; white-space:nowrap; }
  .tbl td { padding:.65rem 1rem; border-bottom:1px solid #f3f4f6; font-size:.86rem; vertical-align:middle; }
  .tbl tr:hover td { background:#fafafa; }
  .status-pill { padding:2px 9px; border-radius:10px; font-size:.7rem; font-weight:700; white-space:nowrap; display:inline-block; }
  .pill-pending   { background:#fef3c7; color:#92400e; }
  .pill-confirmed { background:#d1fae5; color:#065f46; }
  .pill-cancelled { background:#f3f4f6; color:#6b7280; }
  .btn-xs { padding:3px 9px; border-radius:5px; font-size:.72rem; font-weight:600; cursor:pointer; border:none; white-space:nowrap; }
  .btn-approve { background:#d1fae5; color:#065f46; } .btn-approve:hover { background:#6ee7b7; }
  .btn-reject  { background:#fee2e2; color:#991b1b; } .btn-reject:hover  { background:#fecaca; }
  .btn-link    { background:#dbeafe; color:#1e40af; } .btn-link:hover    { background:#bfdbfe; }
  .btn-cash    { background:#fef3c7; color:#92400e; } .btn-cash:hover    { background:#fde68a; }
  .btn-venmo   { background:#ede9fe; color:#5b21b6; } .btn-venmo:hover   { background:#ddd6fe; }
  .no-client   { display:inline-flex; align-items:center; gap:4px; color:#ef4444; font-size:.75rem; font-weight:600; }

  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:14px; padding:1.75rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,31,.2); }
  .modal-title { font-family:'Bebas Neue',sans-serif; font-size:1.4rem; color:var(--navy); margin-bottom:1rem; }
  .mfg { margin-bottom:.85rem; }
  .mfg label { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:3px; }
  .mfg select, .mfg input { width:100%; border:1.5px solid #dbe4ff; border-radius:7px; padding:6px 10px; font-size:.87rem; }
  .modal-actions { display:flex; gap:.5rem; justify-content:flex-end; margin-top:1rem; }
  .btn-primary { background:var(--navy); color:#fff; border:none; border-radius:7px; padding:.5rem 1.3rem; font-weight:700; cursor:pointer; font-size:.86rem; }
  .btn-ghost   { background:#f3f4f6; color:#374151; border:none; border-radius:7px; padding:.5rem 1.1rem; font-weight:600; cursor:pointer; font-size:.86rem; }

  /* ── Mobile card view ── */
  .booking-card { display:none; }

  @media(max-width:768px) {
    .desktop-table { display:none; }
    .booking-card {
      display:block;
      background:#fff;
      border:1.5px solid #e5eaf2;
      border-radius:10px;
      padding:1rem;
      margin-bottom:.75rem;
      position:relative;
    }
    .booking-card.card-pending   { border-left:4px solid #f59e0b; }
    .booking-card.card-confirmed { border-left:4px solid #10b981; }
    .booking-card.card-cancelled { border-left:4px solid #d1d5db; }
    .booking-card.card-suggestion_pending { border-left:4px solid #f59e0b; }
    .card-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.6rem; }
    .card-client { font-weight:700; font-size:.95rem; color:#111827; }
    .card-meta { display:grid; grid-template-columns:1fr 1fr; gap:.4rem .75rem; margin-bottom:.75rem; }
    .card-field-label { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; }
    .card-field-value { font-size:.85rem; color:#374151; font-weight:500; }
    .card-actions { display:flex; flex-wrap:wrap; gap:6px; padding-top:.6rem; border-top:1px solid #f3f4f6; }
    .card-actions .btn-xs { padding:5px 12px; font-size:.76rem; }
  }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Manage Bookings</h1>
</div>

{{-- Filter tabs --}}
<div style="border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;display:flex;gap:.25rem;overflow-x:auto;">
  <a href="?status=all"       class="filter-tab {{ $status==='all'       ? 'active' : '' }}">All</a>
  <a href="?status=pending"   class="filter-tab {{ $status==='pending'   ? 'active' : '' }}">Pending</a>
  <a href="?status=confirmed" class="filter-tab {{ $status==='confirmed' ? 'active' : '' }}">Confirmed</a>
  <a href="?status=cancelled" class="filter-tab {{ $status==='cancelled' ? 'active' : '' }}">Cancelled</a>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif

{{-- ═══════════════ DESKTOP TABLE ═══════════════ --}}
<div class="desktop-table bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Client / Skater</th>
      <th>Service</th>
      <th>Date / Time</th>
      <th>Rink</th>
      <th>Price</th>
      <th>Payment</th>
      <th>Status</th>
      <th>Actions</th>
    </tr></thead>
    <tbody>
    @forelse($bookings as $booking)
    @php
      $date = $booking->date ?? $booking->timeSlot?->date;
      $time = $booking->start_time ?? $booking->timeSlot?->start_time;
    @endphp
    <tr>
      <td>
        @if($booking->client_id && trim($booking->client_name))
          <div class="font-semibold text-gray-900">{{ $booking->client_name }}</div>
          @if($booking->student)
            <div style="font-size:.74rem;color:#6b7280;">⛸️ {{ $booking->student->full_name }}</div>
          @endif
        @elseif($booking->student)
          <div class="font-semibold" style="color:var(--navy);">⛸️ {{ $booking->student->full_name }}</div>
          <div class="no-client">⚠ No client <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link</button></div>
        @elseif($booking->client_email)
          <div class="font-semibold text-gray-900">{{ $booking->display_name }}</div>
          <div style="font-size:.74rem;color:#9ca3af;">{{ $booking->client_email }}</div>
          <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link to Client</button>
        @else
          <span class="no-client">⚠ No client</span>
          <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link</button>
        @endif
        @if($booking->student_name)
          <div style="font-size:.74rem;color:#0c4a6e;margin-top:2px;">⛸️ {{ $booking->student_name }}@if($booking->student_age), age {{ $booking->student_age }}@endif @if($booking->skill_level)· {{ ucfirst($booking->skill_level) }}@endif</div>
        @endif
        @if($booking->notes)
          <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($booking->notes, 45) }}</div>
        @endif
      </td>
      <td style="font-size:.82rem;">{{ $booking->service->name ?? '—' }}</td>
      <td style="white-space:nowrap;">
        <div class="font-semibold">{{ $date ? \Carbon\Carbon::parse($date)->format('M j, Y') : '—' }}</div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $time ? \Carbon\Carbon::parse($time)->format('g:i A') : '—' }}</div>
      </td>
      <td style="font-size:.8rem;color:#6b7280;">{{ $booking->timeSlot->rink->name ?? '—' }}</td>
      <td class="font-semibold">${{ number_format($booking->price_paid, 0) }}</td>
      <td>
        @if($booking->payment_status === 'paid')
          @if($booking->payment_type === 'cash')
            <span class="status-pill pill-confirmed">💵 Cash</span>
          @else
            <span class="status-pill pill-confirmed">💜 Venmo</span>
          @endif
        @else
          <div style="display:flex;flex-direction:column;gap:3px;">
            <span class="status-pill pill-pending">Unpaid</span>
            <div style="display:flex;gap:3px;margin-top:2px;">
              <form method="POST" action="{{ route('admin.bookings.cash-paid', $booking) }}" style="display:inline">@csrf
                <button type="submit" class="btn-xs btn-cash">💵 Cash</button>
              </form>
              <button class="btn-xs btn-venmo" onclick="openVenmoModal({{ $booking->id }})">💜 Venmo</button>
            </div>
          </div>
        @endif
      </td>
      <td>
        @if($booking->status === 'pending')
          <span class="status-pill pill-pending">Pending</span>
        @elseif($booking->status === 'confirmed')
          <span class="status-pill pill-confirmed">Confirmed</span>
        @else
          <span class="status-pill pill-cancelled">{{ ucfirst($booking->status) }}</span>
        @endif
      </td>
      <td style="white-space:nowrap;">
        @if($booking->status === 'pending')
          <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" style="display:inline">@csrf
            <button type="submit" class="btn-xs btn-approve">✓ Approve</button>
          </form>
          <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" style="display:inline;margin-left:3px;" onsubmit="return confirm('Reject this booking?')">@csrf
            <button type="submit" class="btn-xs btn-reject">✕ Reject</button>
          </form>
          <button type="button" class="btn-xs" style="background:#fef3c7;color:#92400e;margin-left:3px;"
            onclick="openSuggestModal({{ $booking->id }}, '{{ addslashes($booking->client_name) }}', '{{ \Carbon\Carbon::parse($booking->date)->format('Y-m-d') }}')">
            🔄 Suggest Time
          </button>
        @endif
        @if($booking->status === 'suggestion_pending')
          <span style="background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:6px;">⏳ Awaiting Response</span>
        @endif
        @if(!$booking->client_id)
          <button class="btn-xs btn-link" style="margin-top:3px;" onclick="openLinkModal({{ $booking->id }})">👤 Link Client</button>
        @endif
        <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn-xs" style="display:inline-block;margin-top:3px;background:#dbeafe;color:#1e40af;text-decoration:none;">✎ Edit</a>
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-10 text-gray-400">No bookings found.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $bookings->appends(['status' => $status])->links() }}</div>
</div>

{{-- ═══════════════ MOBILE CARDS ═══════════════ --}}
@forelse($bookings as $booking)
@php
  $date = $booking->date ?? $booking->timeSlot?->date;
  $time = $booking->start_time ?? $booking->timeSlot?->start_time;
@endphp
<div class="booking-card card-{{ $booking->status }}">
  <div class="card-header">
    <div>
      <div class="card-client">
        @if($booking->client_id && trim($booking->client_name))
          {{ $booking->client_name }}
        @elseif($booking->student)
          ⛸️ {{ $booking->student->full_name }}
        @elseif($booking->client_email)
          {{ $booking->display_name }}
        @else
          <span class="no-client">⚠ No client</span>
        @endif
      </div>
      @if($booking->student && $booking->client_id)
        <div style="font-size:.76rem;color:#6b7280;">⛸️ {{ $booking->student->full_name }}</div>
      @endif
      @if($booking->client_email && !$booking->client_id)
        <div style="font-size:.76rem;color:#9ca3af;">{{ $booking->client_email }}</div>
      @endif
    </div>
    <div style="display:flex;gap:5px;align-items:center;">
      @if($booking->status === 'pending')
        <span class="status-pill pill-pending">Pending</span>
      @elseif($booking->status === 'confirmed')
        <span class="status-pill pill-confirmed">Confirmed</span>
      @elseif($booking->status === 'suggestion_pending')
        <span class="status-pill pill-pending">⏳ Suggested</span>
      @else
        <span class="status-pill pill-cancelled">{{ ucfirst($booking->status) }}</span>
      @endif
    </div>
  </div>

  @if($booking->student_name)
  <div style="font-size:.8rem;color:#0c4a6e;margin-bottom:.5rem;">
    ⛸️ <strong>{{ $booking->student_name }}</strong>@if($booking->student_age), age {{ $booking->student_age }}@endif @if($booking->skill_level)· {{ ucfirst($booking->skill_level) }}@endif
  </div>
  @endif

  <div class="card-meta">
    <div>
      <div class="card-field-label">Date</div>
      <div class="card-field-value">{{ $date ? \Carbon\Carbon::parse($date)->format('M j, Y') : '—' }}</div>
    </div>
    <div>
      <div class="card-field-label">Time</div>
      <div class="card-field-value">{{ $time ? \Carbon\Carbon::parse($time)->format('g:i A') : '—' }}</div>
    </div>
    <div>
      <div class="card-field-label">Service</div>
      <div class="card-field-value">{{ $booking->service->name ?? '—' }}</div>
    </div>
    <div>
      <div class="card-field-label">Rink</div>
      <div class="card-field-value">{{ $booking->timeSlot->rink->name ?? '—' }}</div>
    </div>
    <div>
      <div class="card-field-label">Price</div>
      <div class="card-field-value">${{ number_format($booking->price_paid, 0) }}</div>
    </div>
    <div>
      <div class="card-field-label">Payment</div>
      <div class="card-field-value">
        @if($booking->payment_status === 'paid')
          @if($booking->payment_type === 'cash')
            <span class="status-pill pill-confirmed">💵 Cash</span>
          @else
            <span class="status-pill pill-confirmed">💜 Venmo</span>
          @endif
        @else
          <span class="status-pill pill-pending">Unpaid</span>
        @endif
      </div>
    </div>
  </div>

  @if($booking->notes)
    <div style="font-size:.78rem;color:#9ca3af;margin-bottom:.5rem;">📝 {{ Str::limit($booking->notes, 60) }}</div>
  @endif

  <div class="card-actions">
    @if($booking->status === 'pending')
      <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" style="display:inline">@csrf
        <button type="submit" class="btn-xs btn-approve">✓ Approve</button>
      </form>
      <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" style="display:inline" onsubmit="return confirm('Reject this booking?')">@csrf
        <button type="submit" class="btn-xs btn-reject">✕ Reject</button>
      </form>
      <button type="button" class="btn-xs" style="background:#fef3c7;color:#92400e;"
        onclick="openSuggestModal({{ $booking->id }}, '{{ addslashes($booking->client_name) }}', '{{ \Carbon\Carbon::parse($booking->date)->format('Y-m-d') }}')">
        🔄 Suggest
      </button>
    @endif
    @if($booking->payment_status !== 'paid')
      <form method="POST" action="{{ route('admin.bookings.cash-paid', $booking) }}" style="display:inline">@csrf
        <button type="submit" class="btn-xs btn-cash">💵 Cash</button>
      </form>
      <button class="btn-xs btn-venmo" onclick="openVenmoModal({{ $booking->id }})">💜 Venmo</button>
    @endif
    @if(!$booking->client_id)
      <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">👤 Link</button>
    @endif
    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn-xs" style="background:#dbeafe;color:#1e40af;text-decoration:none;">✎ Edit</a>
  </div>
</div>
@empty
<div class="booking-card" style="text-align:center;color:#9ca3af;padding:2rem;">No bookings found.</div>
@endforelse
<div class="booking-card" style="background:transparent;border:none;padding:0;">
  {{ $bookings->appends(['status' => $status])->links() }}
</div>

{{-- ═══════════════ MODALS ═══════════════ --}}

{{-- Link Client Modal --}}
<div class="modal-overlay" id="linkClientModal">
  <div class="modal-box">
    <div class="modal-title">Link to Client</div>
    <p style="font-size:.83rem;color:#6b7280;margin-bottom:1rem;">Select the parent/guardian account to link to this booking.</p>
    <form method="POST" id="linkClientForm">
      @csrf
      <div class="mfg">
        <label>Select Client</label>
        <select name="client_id" required>
          <option value="">— Choose client —</option>
          @foreach($clients as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email }})</option>
          @endforeach
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Link Client</button>
      </div>
    </form>
  </div>
</div>

{{-- Venmo Paid Modal --}}
<div class="modal-overlay" id="venmoModal">
  <div class="modal-box">
    <div class="modal-title">Mark as Venmo Paid</div>
    <form method="POST" id="venmoForm">
      @csrf
      <div class="mfg">
        <label>Venmo Username (optional)</label>
        <input type="text" name="venmo_username" placeholder="@username">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">💜 Mark Paid</button>
      </div>
    </form>
  </div>
</div>

{{-- Suggest Time Modal --}}
<div class="modal-overlay" id="suggestModal">
  <div class="modal-box" style="max-width:480px;">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:#001F5B;margin-bottom:1rem;">🔄 Suggest a Different Time</div>
    <p style="font-size:.85rem;color:#6b7280;margin-bottom:1rem;">Suggesting a new time for: <strong id="suggest-client-name"></strong></p>
    <form method="POST" id="suggestForm" action="">
      @csrf
      <div style="margin-bottom:.75rem;">
        <label style="font-size:.75rem;font-weight:700;color:#374151;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em;">Date</label>
        <input type="date" id="suggest-date" class="form-input"
               style="width:100%;border:1.5px solid #e5eaf2;border-radius:7px;padding:.55rem .85rem;font-size:.88rem;"
               onchange="loadSlots(this.value)">
      </div>
      <div style="margin-bottom:.75rem;">
        <label style="font-size:.75rem;font-weight:700;color:#374151;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em;">Available Time Slot</label>
        <select name="suggested_time_slot_id" id="suggest-slot"
                style="width:100%;border:1.5px solid #e5eaf2;border-radius:7px;padding:.55rem .85rem;font-size:.88rem;background:#fff;" required>
          <option value="">— Pick a date first —</option>
        </select>
      </div>
      <div style="margin-bottom:1rem;">
        <label style="font-size:.75rem;font-weight:700;color:#374151;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em;">Message to Client (optional)</label>
        <textarea name="suggestion_message" rows="3"
          style="width:100%;border:1.5px solid #e5eaf2;border-radius:7px;padding:.55rem .85rem;font-size:.85rem;resize:vertical;"
          placeholder="e.g. This slot works better with the ice schedule this week!"></textarea>
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;">
        <button type="button" onclick="closeModals()" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:#001F5B;color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Send Suggestion</button>
      </div>
    </form>
  </div>
</div>

<script>
function openLinkModal(bookingId) {
  document.getElementById('linkClientForm').action = `/admin/bookings/${bookingId}/link-client`;
  document.getElementById('linkClientModal').classList.add('open');
}
function openVenmoModal(bookingId) {
  document.getElementById('venmoForm').action = `/admin/bookings/${bookingId}/venmo-paid`;
  document.getElementById('venmoModal').classList.add('open');
}
function openSuggestModal(bookingId, clientName, currentDate) {
  document.getElementById('suggestForm').action = `/admin/bookings/${bookingId}/suggest-time`;
  document.getElementById('suggest-client-name').textContent = clientName;
  document.getElementById('suggest-date').value = currentDate;
  document.getElementById('suggest-slot').innerHTML = '<option value="">Loading...</option>';
  loadSlots(currentDate);
  document.getElementById('suggestModal').style.display = 'flex';
}
function loadSlots(date) {
  if (!date) return;
  const select = document.getElementById('suggest-slot');
  select.innerHTML = '<option value="">Loading...</option>';
  fetch(`/admin/bookings/slots-for-date?date=${date}`)
    .then(r => r.json())
    .then(slots => {
      if (slots.length === 0) {
        select.innerHTML = '<option value="">No available slots on this date</option>';
      } else {
        select.innerHTML = slots.map(s =>
          `<option value="${s.id}">${s.label}</option>`
        ).join('');
      }
    });
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => { m.classList.remove('open'); m.style.display = 'none'; });
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
