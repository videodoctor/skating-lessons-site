@extends('layouts.admin')
@section('title', 'Planner Scan — {{ $scan->month }} {{ $scan->year }}')
@section('content')
<style>
  :root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#E8F5FB; }

  .entry-card { background:#fff; border:1.5px solid #e5eaf2; border-radius:10px; margin-bottom:.6rem; overflow:hidden; transition:box-shadow .15s; }
  .entry-card:hover { box-shadow:0 4px 12px rgba(0,0,31,.08); }
  .entry-card.confirmed { border-color:#a7f3d0; }
  .entry-card.needs-review { border-color:#fcd34d; }
  .entry-card.unmatched { border-color:#fca5a5; }

  .entry-header { display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; }
  .entry-type { padding:3px 10px; border-radius:5px; font-size:.72rem; font-weight:700; white-space:nowrap; }
  .type-private { background:#dbeafe; color:#1e40af; }
  .type-lts { background:#d1fae5; color:#065f46; }
  .type-ltp { background:#fef3c7; color:#92400e; }
  .type-cancelled { background:#fee2e2; color:#991b1b; }
  .type-personal { background:#f3f4f6; color:#6b7280; }
  .type-note { background:#f5f3ff; color:#6d28d9; }

  .match-badge { padding:2px 8px; border-radius:10px; font-size:.68rem; font-weight:700; }
  .match-matched { background:#d1fae5; color:#065f46; }
  .match-unmatched { background:#fee2e2; color:#991b1b; }
  .match-no-booking { background:#fef3c7; color:#92400e; }
  .match-personal { background:#f3f4f6; color:#6b7280; }

  .confidence-bar { height:4px; border-radius:2px; background:#e5e7eb; width:60px; overflow:hidden; }
  .confidence-fill { height:100%; border-radius:2px; }

  .entry-actions { padding:.5rem 1rem .75rem; display:flex; gap:.5rem; flex-wrap:wrap; border-top:1px solid #f3f4f6; }
  .btn-sm { padding:4px 12px; border-radius:6px; font-size:.78rem; font-weight:600; cursor:pointer; border:none; }
  .btn-confirm { background:#d1fae5; color:#065f46; }
  .btn-confirm:hover { background:#6ee7b7; }
  .btn-edit { background:#f3f4f6; color:#374151; }
  .btn-edit:hover { background:#e5e7eb; }
  .btn-link-student { background:#dbeafe; color:#1e40af; }
  .btn-danger { background:#fee2e2; color:#991b1b; }

  .day-header { background:var(--navy); color:#fff; padding:.5rem 1rem; border-radius:8px; font-weight:700; font-size:.85rem; margin-bottom:.5rem; margin-top:1.25rem; display:flex; align-items:center; justify-content:space-between; }

  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:14px; padding:1.75rem; width:100%; max-width:480px; max-height:85vh; overflow-y:auto; }
  .modal-title { font-family:'Bebas Neue',sans-serif; font-size:1.5rem; color:var(--navy); margin-bottom:1.25rem; }
  .form-group { margin-bottom:1rem; }
  .form-group label { display:block; font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.3rem; }
  .form-group input, .form-group select, .form-group textarea { width:100%; border:1.5px solid #dbe4ff; border-radius:7px; padding:7px 10px; font-size:.9rem; }
  .modal-actions { display:flex; gap:.5rem; justify-content:flex-end; margin-top:1.25rem; }
  .btn-primary { background:var(--navy); color:#fff; border:none; border-radius:7px; padding:.6rem 1.5rem; font-weight:700; cursor:pointer; }
  .btn-ghost { background:#f3f4f6; color:#374151; border:none; border-radius:7px; padding:.6rem 1.25rem; font-weight:600; cursor:pointer; }

  .summary-bar { display:flex; gap:.75rem; flex-wrap:wrap; margin-bottom:1.5rem; }
  .summary-pill { padding:5px 14px; border-radius:20px; font-size:.8rem; font-weight:700; }
</style>

<div class="max-w-4xl mx-auto">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <a href="{{ route('admin.planner') }}" style="color:#6b7280;font-size:.82rem;text-decoration:none;">← Back to Planner</a>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:.25rem 0 0;">
        {{ $scan->month }} {{ $scan->year }} Scan
      </h1>
      <div style="font-size:.82rem;color:#6b7280;">Scanned {{ $scan->created_at->diffForHumans() }} · {{ $scan->entries->count() }} entries</div>
    </div>
    @if(!$scan->is_finalized)
    <form method="POST" action="{{ route('admin.planner.finalize', $scan->id) }}">
      @csrf
      <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.7rem 1.5rem;font-weight:700;cursor:pointer;"
              onclick="return confirm('Mark this scan as finalized?')">
        ✓ Finalize Scan
      </button>
    </form>
    @else
    <span style="background:#d1fae5;color:#065f46;padding:.5rem 1rem;border-radius:8px;font-size:.85rem;font-weight:700;">✓ Finalized</span>
    @endif
  </div>

  {{-- Flash --}}
  @if(session('success'))
  <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.7rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;font-weight:600;">
    ✓ {{ session('success') }}
  </div>
  @endif

  {{-- Summary bar --}}
  <div class="summary-bar">
    <span class="summary-pill" style="background:#dbeafe;color:#1e40af;">{{ $scan->entries->count() }} total</span>
    <span class="summary-pill" style="background:#d1fae5;color:#065f46;">{{ $matched->count() }} matched</span>
    @if($unmatched->count() > 0)
    <span class="summary-pill" style="background:#fee2e2;color:#991b1b;">{{ $unmatched->count() }} unmatched names</span>
    @endif
    @if($noBooking->count() > 0)
    <span class="summary-pill" style="background:#fef3c7;color:#92400e;">{{ $noBooking->count() }} no booking found</span>
    @endif
    @if($needsReview->count() > 0)
    <span class="summary-pill" style="background:#fde68a;color:#92400e;">⚠ {{ $needsReview->count() }} needs review</span>
    @endif
  </div>

  {{-- Entries by date --}}
  @foreach($entriesByDate as $dateStr => $entries)
  @php $d = \Carbon\Carbon::parse($dateStr); @endphp
  <div class="day-header">
    <span>{{ $d->format('l, F j') }}</span>
    <span style="opacity:.6;font-size:.75rem;">{{ $entries->count() }} entr{{ $entries->count() === 1 ? 'y' : 'ies' }}</span>
  </div>

  @foreach($entries as $entry)
  @php
    $typeClass  = match($entry->type) {
      'private_lesson'  => 'type-private',
      'lts'             => 'type-lts',
      'ltp'             => 'type-ltp',
      'cancelled_public','cancelled_class' => 'type-cancelled',
      'personal_block'  => 'type-personal',
      default           => 'type-note',
    };
    $typeLabel  = match($entry->type) {
      'private_lesson'  => '⛸️ Private Lesson',
      'lts'             => '🏒 Learn to Skate',
      'ltp'             => '🏑 Learn to Play',
      'cancelled_public'=> '✕ Cancelled P/S',
      'cancelled_class' => '✕ Cancelled Class',
      'personal_block'  => '🚫 Personal',
      default           => '📝 Note',
    };
    $matchClass = match($entry->match_status) {
      'matched'          => 'match-matched',
      'unmatched'        => 'match-unmatched',
      'no_booking_found' => 'match-no-booking',
      default            => 'match-personal',
    };
    $matchLabel = match($entry->match_status) {
      'matched'          => '✓ Matched',
      'unmatched'        => '⚠ No student match',
      'no_booking_found' => '? No booking found',
      'no_booking_expected' => '— N/A',
      'personal'         => '— Personal',
      default            => $entry->match_status ?? '—',
    };
    $cardClass = $entry->isConfirmed() ? 'confirmed' : ($entry->needsReview() ? ($entry->match_status === 'unmatched' ? 'unmatched' : 'needs-review') : '');
    $confColor = $entry->confidence >= 90 ? '#10b981' : ($entry->confidence >= 70 ? '#f59e0b' : '#ef4444');
  @endphp

  <div class="entry-card {{ $cardClass }}" id="entry-{{ $entry->id }}">
    <div class="entry-header">
      <span class="entry-type {{ $typeClass }}">{{ $typeLabel }}</span>

      @if($entry->time)
      <span style="font-weight:700;color:#374151;font-size:.9rem;">{{ \Carbon\Carbon::parse($entry->time)->format('g:i A') }}</span>
      @endif

      @if($entry->extracted_name)
      <span style="font-weight:600;color:var(--navy);">{{ $entry->extracted_name }}</span>
      @if($entry->student)
      <span style="color:#6b7280;font-size:.78rem;">→ {{ $entry->student->full_name }}</span>
      @endif
      @endif

      @if($entry->rink && $entry->rink !== 'unknown')
      <span style="color:#9ca3af;font-size:.75rem;">@ {{ ucwords(str_replace('-', ' ', $entry->rink)) }}</span>
      @endif

      <div style="margin-left:auto;display:flex;align-items:center;gap:.6rem;">
        <span class="match-badge {{ $matchClass }}">{{ $matchLabel }}</span>

        <div title="Confidence: {{ $entry->confidence }}%">
          <div class="confidence-bar">
            <div class="confidence-fill" style="width:{{ $entry->confidence }}%;background:{{ $confColor }};"></div>
          </div>
          <div style="font-size:.62rem;color:#9ca3af;text-align:center;">{{ $entry->confidence }}%</div>
        </div>

        @if($entry->isConfirmed())
        <span style="color:#10b981;font-size:.8rem;font-weight:700;">✓ Confirmed</span>
        @endif
      </div>
    </div>

    @if($entry->notes)
    <div style="padding:0 1rem .6rem;font-size:.78rem;color:#6b7280;font-style:italic;">{{ $entry->notes }}</div>
    @endif

    @if($entry->booking)
    <div style="padding:0 1rem .6rem;font-size:.78rem;background:#f0fdf4;border-top:1px solid #bbf7d0;color:#166534;">
      ✓ Booking #{{ $entry->booking->confirmation_code ?? $entry->booking->id }} — {{ $entry->booking->service->name ?? 'Lesson' }} — {{ ucfirst($entry->booking->status) }}
    </div>
    @endif

    {{-- Actions --}}
    @if(!$entry->isConfirmed())
    <div class="entry-actions">
      {{-- Confirm --}}
      <form method="POST" action="{{ route('admin.planner.entry.confirm', $entry->id) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn-sm btn-confirm">✓ Confirm</button>
      </form>

      {{-- Edit --}}
      <button class="btn-sm btn-edit" onclick="openEditModal({{ $entry->id }}, '{{ $entry->type }}', '{{ $entry->time }}', '{{ $entry->extracted_name }}', '{{ $entry->rink }}', '{{ $entry->notes }}')">
        ✏ Edit
      </button>

      {{-- Unmatched: link to student or create new --}}
      @if($entry->match_status === 'unmatched' && $entry->type === 'private_lesson')
      <button class="btn-sm btn-link-student" onclick="openLinkModal({{ $entry->id }}, '{{ addslashes($entry->extracted_name) }}')">
        👤 Link Student
      </button>
      <button class="btn-sm" style="background:#f5f3ff;color:#6d28d9;" onclick="openNewStudentModal({{ $entry->id }}, '{{ addslashes($entry->extracted_name) }}')">
        + New Student
      </button>
      @endif
    </div>
    @endif
  </div>
  @endforeach
  @endforeach

</div>

{{-- EDIT ENTRY MODAL --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Entry</div>
    <form method="POST" id="editForm">
      @csrf @method('PATCH')
      <div class="form-group">
        <label>Type</label>
        <select name="type" id="edit-type">
          <option value="private_lesson">Private Lesson</option>
          <option value="lts">Learn to Skate</option>
          <option value="ltp">Learn to Play</option>
          <option value="cancelled_public">Cancelled Public Session</option>
          <option value="cancelled_class">Cancelled Class</option>
          <option value="personal_block">Personal / Blocked</option>
          <option value="note">Note</option>
        </select>
      </div>
      <div class="form-group">
        <label>Time</label>
        <input type="time" name="time" id="edit-time">
      </div>
      <div class="form-group">
        <label>Student / Name</label>
        <input type="text" name="extracted_name" id="edit-name">
      </div>
      <div class="form-group">
        <label>Rink</label>
        <select name="rink" id="edit-rink">
          <option value="creve-coeur">Creve Coeur</option>
          <option value="kirkwood">Kirkwood</option>
          <option value="maryville">Maryville</option>
          <option value="brentwood">Brentwood</option>
          <option value="webster-groves">Webster Groves</option>
          <option value="unknown">Unknown</option>
        </select>
      </div>
      <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" id="edit-notes" rows="2"></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- LINK STUDENT MODAL --}}
<div class="modal-overlay" id="linkModal">
  <div class="modal-box">
    <div class="modal-title">Link to Student</div>
    <p style="color:#6b7280;font-size:.85rem;margin-bottom:1rem;">Map "<span id="link-name-display" style="font-weight:700;color:var(--navy);"></span>" to an existing student and save as an alias.</p>
    <form method="POST" action="{{ route('admin.planner.add-alias') }}">
      @csrf
      <input type="hidden" name="entry_id" id="link-entry-id">
      <input type="hidden" name="alias" id="link-alias">
      <div class="form-group">
        <label>Select Student</label>
        <select name="student_id" required>
          <option value="">— Choose student —</option>
          @foreach($students as $s)
          <option value="{{ $s->id }}">{{ $s->full_name }} @if($s->client)({{ $s->client->full_name }})@endif</option>
          @endforeach
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Link & Save Alias</button>
      </div>
    </form>
  </div>
</div>

{{-- NEW STUDENT MODAL --}}
<div class="modal-overlay" id="newStudentModal">
  <div class="modal-box">
    <div class="modal-title">Create New Student</div>
    <form method="POST" action="{{ route('admin.planner.create-student') }}">
      @csrf
      <input type="hidden" name="entry_id" id="new-entry-id">
      <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" id="new-first-name" required>
      </div>
      <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name">
      </div>
      <div class="form-group">
        <label>Age</label>
        <input type="number" name="age" min="3" max="80">
      </div>
      <div class="form-group">
        <label>Link to Parent/Client (optional)</label>
        <select name="client_id">
          <option value="">— No client yet —</option>
          @foreach(\App\Models\Client::orderBy('name')->get() as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email }})</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Save planner name as alias</label>
        <input type="text" name="alias" id="new-alias">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Create Student</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(id, type, time, name, rink, notes) {
  document.getElementById('editForm').action = `/admin/planner/entry/${id}`;
  document.getElementById('edit-type').value = type || '';
  document.getElementById('edit-time').value = time ? time.substring(0,5) : '';
  document.getElementById('edit-name').value = name || '';
  document.getElementById('edit-rink').value = rink || 'unknown';
  document.getElementById('edit-notes').value = notes || '';
  document.getElementById('editModal').classList.add('open');
}

function openLinkModal(entryId, name) {
  document.getElementById('link-entry-id').value = entryId;
  document.getElementById('link-alias').value = name;
  document.getElementById('link-name-display').textContent = name;
  document.getElementById('linkModal').classList.add('open');
}

function openNewStudentModal(entryId, name) {
  document.getElementById('new-entry-id').value = entryId;
  // Pre-fill first name from extracted name
  const parts = name.split(' ');
  document.getElementById('new-first-name').value = parts[0] || name;
  document.getElementById('new-alias').value = name;
  document.getElementById('newStudentModal').classList.add('open');
}

function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
