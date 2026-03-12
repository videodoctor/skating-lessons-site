@extends('layouts.admin')
@section('title', 'Venmo Payments — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:middle;}
  .tbl tr:hover td{background:#fafafa;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;}
  .pill-yellow{background:#fef3c7;color:#92400e;}
  .pill-red{background:#fee2e2;color:#991b1b;}
  .pill-blue{background:#dbeafe;color:#1e40af;}
  .pill-gray{background:#f3f4f6;color:#6b7280;}
  .stat-card{background:#fff;border-radius:10px;border:1.5px solid #e5eaf2;padding:1.25rem;}
  .stat-num{font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);line-height:1;}
  .stat-lbl{font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-top:2px;}
  .btn-sm{padding:4px 10px;border-radius:6px;font-size:.73rem;font-weight:600;cursor:pointer;border:none;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Venmo Payments</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">Parsed from venmo@kristineskates.com · runs every 15 min</p>
  </div>
  <form method="POST" action="{{ route('admin.venmo.parse-now') }}"
        onsubmit="return confirm('Run Venmo email parser now?')">
    @csrf
    <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer;">
      📬 Parse Now
    </button>
  </form>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">✕ {{ session('error') }}</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-4 gap-4 mb-6">
  <div class="stat-card">
    <div class="stat-num">{{ $stats['total'] }}</div>
    <div class="stat-lbl">Total Payments</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">${{ number_format($stats['total_amount'], 0) }}</div>
    <div class="stat-lbl">Total Received</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">{{ $stats['matched'] }}</div>
    <div class="stat-lbl">Auto-Matched</div>
  </div>
  <div class="stat-card" style="border-color:{{ $stats['unmatched'] > 0 ? '#fecaca' : '#e5eaf2' }}">
    <div class="stat-num" style="color:{{ $stats['unmatched'] > 0 ? '#dc2626' : 'var(--navy)' }}">{{ $stats['unmatched'] }}</div>
    <div class="stat-lbl">Unmatched</div>
  </div>
</div>

{{-- Unmatched alert --}}
@if($stats['unmatched'] > 0)
<div style="background:#fff8ed;border:2px solid #fcd34d;border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1.25rem;font-size:.83rem;color:#92400e;">
  ⚠️ <strong>{{ $stats['unmatched'] }} payment(s)</strong> could not be matched to a booking or client. Review below and link manually.
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Date</th>
      <th>From</th>
      <th>Amount</th>
      <th>Note</th>
      <th>Client</th>
      <th>Booking</th>
      <th>Status</th>
      <th></th>
    </tr></thead>
    <tbody>
    @forelse($payments as $payment)
    <tr>
      <td style="white-space:nowrap;">
        <div>{{ $payment->paid_at->format('M j, Y') }}</div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $payment->paid_at->format('g:i A') }}</div>
      </td>
      <td style="font-weight:600;">{{ $payment->sender_name }}</td>
      <td style="font-weight:700;color:#065f46;font-size:1rem;">${{ number_format($payment->amount, 2) }}</td>
      <td style="color:#6b7280;font-size:.82rem;max-width:160px;">
        {{ $payment->note ?: '—' }}
      </td>
      <td>
        @if($payment->client)
          <a href="{{ route('admin.clients.show', $payment->client) }}"
             style="color:var(--navy);font-weight:600;font-size:.85rem;text-decoration:none;">
            {{ $payment->client->full_name }}
          </a>
        @else
          <span style="color:#9ca3af;font-size:.78rem;">— unlinked —</span>
        @endif
      </td>
      <td>
        @if($payment->booking)
          <div style="font-size:.8rem;font-weight:600;color:var(--navy);">
            #{{ $payment->booking->confirmation_code }}
          </div>
          <div style="font-size:.72rem;color:#6b7280;">
            {{ \Carbon\Carbon::parse($payment->booking->date)->format('M j') }}
            · {{ $payment->booking->student?->first_name ?? '?' }}
          </div>
        @else
          <span style="color:#9ca3af;font-size:.78rem;">— no booking —</span>
        @endif
      </td>
      <td>
        @if($payment->match_status === 'matched')
          <span class="pill pill-green">✓ Matched</span>
        @elseif($payment->match_status === 'client_only')
          <span class="pill pill-yellow">~ Client only</span>
        @else
          <span class="pill pill-red">⚠ Unmatched</span>
        @endif
      </td>
      <td>
        @if($payment->match_status !== 'matched')
        <button class="btn-sm" style="background:#dbeafe;color:#1e40af;"
          onclick="openLinkModal({{ $payment->id }}, '{{ addslashes($payment->sender_name) }}', '{{ $payment->amount }}')">
          Link
        </button>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-10 text-gray-400">No Venmo payments recorded yet.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $payments->links() }}</div>
</div>

{{-- Link modal --}}
<div style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;" id="linkModal" class="modal-overlay">
  <div style="background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:460px;box-shadow:0 20px 60px rgba(0,0,31,.2);">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;">Link Payment</div>
    <p style="font-size:.83rem;color:#6b7280;margin-bottom:1rem;">
      Manually link <strong id="link-sender"></strong> ($<span id="link-amount"></span>) to a booking.
    </p>
    <form method="POST" id="linkForm" action="">
      @csrf @method('PATCH')
      <div style="margin-bottom:.85rem;">
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Booking Confirmation Code</label>
        <input type="text" name="confirmation_code" placeholder="e.g. AB12CD34"
               style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.87rem;text-transform:uppercase;">
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
        <button type="button" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;" onclick="closeModal()">Cancel</button>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Link Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
function openLinkModal(id, sender, amount) {
  document.getElementById('link-sender').textContent = sender;
  document.getElementById('link-amount').textContent = amount;
  document.getElementById('linkForm').action = `/admin/venmo/${id}`;
  document.getElementById('linkModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('linkModal').style.display = 'none';
}
document.getElementById('linkModal').addEventListener('click', e => {
  if (e.target === document.getElementById('linkModal')) closeModal();
});
</script>
@endsection
