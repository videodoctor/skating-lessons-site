@extends('layouts.admin')
@section('title', 'Edit Booking — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;--ice:#E8F5FB;}
  .edit-card{background:#fff;border-radius:12px;border:1.5px solid #e5eaf2;padding:1.5rem;margin-bottom:1.25rem;}
  .edit-card h3{font-family:'Bebas Neue',sans-serif;font-size:1.15rem;color:var(--navy);margin:0 0 1rem;}
  .field{margin-bottom:.85rem;}
  .field label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;text-transform:uppercase;letter-spacing:.04em;}
  .field input,.field select,.field textarea{width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.87rem;background:#fff;font-family:inherit;}
  .field textarea{resize:vertical;min-height:80px;}
  .field select{appearance:auto;}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
  @media(max-width:640px){.row{grid-template-columns:1fr;}}
  .btn{padding:.6rem 1.4rem;border-radius:7px;font-weight:700;font-size:.85rem;cursor:pointer;border:none;text-decoration:none;display:inline-block;}
  .btn-navy{background:var(--navy);color:#fff;}
  .btn-gray{background:#f3f4f6;color:#374151;}
  .btn-red{background:#fee2e2;color:#991b1b;}
  .conf-code{font-family:monospace;font-size:1.1rem;font-weight:700;color:var(--navy);background:var(--ice);padding:4px 12px;border-radius:6px;}
</style>

<div style="max-width:720px;margin:0 auto;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem;">
    <div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--navy);margin:0;">Edit Booking</h1>
      <span class="conf-code">{{ $booking->confirmation_code }}</span>
    </div>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-gray">&larr; Back to Bookings</a>
  </div>

  @if($errors->any())
  <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
  </div>
  @endif

  <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
    @csrf @method('PATCH')

    {{-- Client Info --}}
    <div class="edit-card">
      <h3>Client Information</h3>
      <div class="row">
        <div class="field">
          <label>Client Name</label>
          <input type="text" name="client_name" value="{{ old('client_name', $booking->client_name) }}" required>
        </div>
        <div class="field">
          <label>Linked Account</label>
          <select name="client_id">
            <option value="">— No linked account —</option>
            @foreach($clients as $c)
              <option value="{{ $c->id }}" {{ old('client_id', $booking->client_id) == $c->id ? 'selected' : '' }}>
                {{ $c->full_name }} ({{ $c->email }})
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row">
        <div class="field">
          <label>Email</label>
          <input type="email" name="client_email" value="{{ old('client_email', $booking->client_email) }}" required>
        </div>
        <div class="field">
          <label>Phone</label>
          <input type="text" name="client_phone" value="{{ old('client_phone', $booking->client_phone) }}">
        </div>
      </div>
      <div class="field">
        <label>Student</label>
        <select name="student_id">
          <option value="">— No student —</option>
          @foreach($students as $s)
            <option value="{{ $s->id }}" {{ old('student_id', $booking->student_id) == $s->id ? 'selected' : '' }}>
              {{ $s->first_name }} {{ $s->last_name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Service & Scheduling --}}
    <div class="edit-card">
      <h3>Service & Schedule</h3>
      <div class="field">
        <label>Service</label>
        <select name="service_id" required>
          @foreach($services as $svc)
            <option value="{{ $svc->id }}" {{ old('service_id', $booking->service_id) == $svc->id ? 'selected' : '' }}>
              {{ $svc->name }} — ${{ number_format($svc->effectivePrice(), 0) }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="row">
        <div class="field">
          <label>Rink</label>
          <select id="rinkSelect">
            @foreach($rinks as $r)
              <option value="{{ $r->id }}" {{ ($booking->timeSlot?->rink_id == $r->id) ? 'selected' : '' }}>
                {{ $r->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Date</label>
          <input type="date" id="dateInput" value="{{ $booking->date?->format('Y-m-d') }}">
        </div>
      </div>
      <div class="field">
        <label>Time Slot</label>
        <select name="time_slot_id" id="slotSelect">
          <option value="">— Select a time slot —</option>
          @foreach($availableSlots as $slot)
            <option value="{{ $slot->id }}" {{ old('time_slot_id', $booking->time_slot_id) == $slot->id ? 'selected' : '' }}>
              {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
            </option>
          @endforeach
        </select>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Change rink or date to load available slots.</p>
      </div>
    </div>

    {{-- Payment & Status --}}
    <div class="edit-card">
      <h3>Payment & Status</h3>
      <div class="row">
        <div class="field">
          <label>Status</label>
          <select name="status" required>
            @foreach(['pending','confirmed','cancelled','rejected','suggestion_pending'] as $st)
              <option value="{{ $st }}" {{ old('status', $booking->status) === $st ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $st)) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Price</label>
          <input type="number" name="price_paid" step="0.01" min="0"
                 value="{{ old('price_paid', $booking->price_paid) }}">
        </div>
      </div>
      <div class="row">
        <div class="field">
          <label>Payment Type</label>
          <select name="payment_type">
            <option value="" {{ !$booking->payment_type ? 'selected' : '' }}>—</option>
            <option value="venmo" {{ old('payment_type', $booking->payment_type) === 'venmo' ? 'selected' : '' }}>Venmo</option>
            <option value="cash" {{ old('payment_type', $booking->payment_type) === 'cash' ? 'selected' : '' }}>Cash</option>
          </select>
        </div>
        <div class="field">
          <label>Payment Status</label>
          <select name="payment_status" required>
            <option value="pending" {{ old('payment_status', $booking->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid" {{ old('payment_status', $booking->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
          </select>
        </div>
      </div>
    </div>

    {{-- Notes --}}
    <div class="edit-card">
      <h3>Notes</h3>
      <div class="field">
        <textarea name="notes" placeholder="Internal notes about this booking...">{{ old('notes', $booking->notes) }}</textarea>
      </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;flex-wrap:wrap;margin-top:.5rem;">
      <a href="{{ route('admin.bookings.index') }}" class="btn btn-gray">Cancel</a>
      <button type="submit" class="btn btn-navy">Save Changes</button>
    </div>
  </form>
</div>

<script>
const rinkSelect = document.getElementById('rinkSelect');
const dateInput   = document.getElementById('dateInput');
const slotSelect  = document.getElementById('slotSelect');
const currentSlotId = {{ $booking->time_slot_id ?? 'null' }};

function loadSlots() {
  const rinkId = rinkSelect.value;
  const date   = dateInput.value;
  if (!rinkId || !date) return;

  slotSelect.innerHTML = '<option value="">Loading...</option>';

  fetch(`{{ route('admin.bookings.slots-for-rink-date') }}?rink_id=${rinkId}&date=${date}&current_slot_id=${currentSlotId}`)
    .then(r => r.json())
    .then(slots => {
      slotSelect.innerHTML = '<option value="">— Select a time slot —</option>';
      slots.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.label;
        if (s.id === currentSlotId) opt.selected = true;
        slotSelect.appendChild(opt);
      });
    });
}

rinkSelect.addEventListener('change', () => {
  slotSelect.innerHTML = '<option value="">— Change date or select slot —</option>';
  loadSlots();
});

dateInput.addEventListener('change', loadSlots);
</script>
@endsection
