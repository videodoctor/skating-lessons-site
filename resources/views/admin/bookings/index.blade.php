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
  .status-pill { padding:2px 9px; border-radius:10px; font-size:.7rem; font-weight:700; white-space:nowrap; }
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
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Manage Bookings</h1>
</div>

{{-- Filter tabs --}}
<div style="border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;display:flex;gap:.25rem;">
  <a href="?status=all"       class="filter-tab {{ $status==='all'       ? 'active' : '' }}">All</a>
  <a href="?status=pending"   class="filter-tab {{ $status==='pending'   ? 'active' : '' }}">Pending</a>
  <a href="?status=confirmed" class="filter-tab {{ $status==='confirmed' ? 'active' : '' }}">Confirmed</a>
  <a href="?status=cancelled" class="filter-tab {{ $status==='cancelled' ? 'active' : '' }}">Cancelled</a>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Client / Student</th>
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
    <tr>
      {{-- Client / Student column --}}
      <td>
        @if($booking->client_id && trim($booking->client_name))
          <div class="font-semibold text-gray-900">{{ $booking->client_name }}</div>
          @if($booking->student)
            <div style="font-size:.74rem;color:#6b7280;">⛸️ {{ $booking->student->full_name }}</div>
          @endif
        @elseif($booking->student)
          <div class="font-semibold" style="color:var(--navy);">⛸️ {{ $booking->student->full_name }}</div>
          <div class="no-client">
            ⚠ No client
            <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link</button>
          </div>
        @elseif($booking->client_email)
          <div class="font-semibold text-gray-900">{{ $booking->display_name }}</div>
          <div style="font-size:.74rem;color:#9ca3af;">{{ $booking->client_email }}</div>
          <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link to Client</button>
        @else
          <span class="no-client">⚠ No client</span>
          <button class="btn-xs btn-link" onclick="openLinkModal({{ $booking->id }})">Link</button>
        @endif
        @if($booking->notes)
          <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($booking->notes, 45) }}</div>
        @endif
      </td>

      <td style="font-size:.82rem;">{{ $booking->service->name ?? '—' }}</td>

      <td style="white-space:nowrap;">
        @php
          $date = $booking->date ?? $booking->timeSlot?->date;
          $time = $booking->start_time ?? $booking->timeSlot?->start_time;
        @endphp
        <div class="font-semibold">{{ $date ? \Carbon\Carbon::parse($date)->format('M j, Y') : '—' }}</div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $time ? \Carbon\Carbon::parse($time)->format('g:i A') : '—' }}</div>
      </td>

      <td style="font-size:.8rem;color:#6b7280;">{{ $booking->timeSlot->rink->name ?? '—' }}</td>

      <td class="font-semibold">${{ number_format($booking->price_paid, 0) }}</td>

      {{-- Payment status --}}
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
              <form method="POST" action="{{ route('admin.bookings.cash-paid', $booking) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-xs btn-cash">💵 Cash</button>
              </form>
              <button class="btn-xs btn-venmo" onclick="openVenmoModal({{ $booking->id }})">💜 Venmo</button>
            </div>
          </div>
        @endif
      </td>

      {{-- Status --}}
      <td>
        @if($booking->status === 'pending')
          <span class="status-pill pill-pending">Pending</span>
        @elseif($booking->status === 'confirmed')
          <span class="status-pill pill-confirmed">Confirmed</span>
        @else
          <span class="status-pill pill-cancelled">{{ ucfirst($booking->status) }}</span>
        @endif
      </td>

      {{-- Actions --}}
      <td style="white-space:nowrap;">
        @if($booking->status === 'pending')
          <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}" style="display:inline">
            @csrf
            <button type="submit" class="btn-xs btn-approve">✓ Approve</button>
          </form>
          <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" style="display:inline;margin-left:3px;"
                onsubmit="return confirm('Reject this booking?')">
            @csrf
            <button type="submit" class="btn-xs btn-reject">✕ Reject</button>
          </form>
        @endif
        @if(!$booking->client_id)
          <button class="btn-xs btn-link" style="margin-top:3px;" onclick="openLinkModal({{ $booking->id }})">👤 Link Client</button>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-10 text-gray-400">No bookings found.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $bookings->appends(['status' => $status])->links() }}</div>
</div>

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

<script>
function openLinkModal(bookingId) {
  document.getElementById('linkClientForm').action = `/admin/bookings/${bookingId}/link-client`;
  document.getElementById('linkClientModal').classList.add('open');
}
function openVenmoModal(bookingId) {
  document.getElementById('venmoForm').action = `/admin/bookings/${bookingId}/venmo-paid`;
  document.getElementById('venmoModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
