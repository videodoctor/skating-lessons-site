@extends('layouts.admin')
@section('title', 'Planner Scan')
@section('content')
<style>
  :root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#E8F5FB; }

  .entry-card { background:#fff; border:1.5px solid #e5eaf2; border-left:4px solid #e5eaf2; border-radius:10px; margin-bottom:.5rem; overflow:hidden; transition:box-shadow .15s; }
  .entry-card:hover { box-shadow:0 3px 10px rgba(0,0,31,.07); }
  .entry-card.state-matched   { border-left-color:#10b981; }
  .entry-card.state-needs     { border-left-color:#f59e0b; }
  .entry-card.state-unmatched { border-left-color:#ef4444; }
  .entry-card.state-info      { border-left-color:#93c5fd; opacity:.85; }
  .entry-card.state-ignored   { border-left-color:#e5e7eb; opacity:.45; }

  .entry-header { display:flex; align-items:center; gap:.6rem; padding:.65rem 1rem; flex-wrap:wrap; }
  .entry-type { padding:2px 9px; border-radius:5px; font-size:.7rem; font-weight:700; white-space:nowrap; }
  .type-private   { background:#dbeafe; color:#1e40af; }
  .type-lts       { background:#d1fae5; color:#065f46; }
  .type-ltp       { background:#fef3c7; color:#92400e; }
  .type-cancelled { background:#fee2e2; color:#991b1b; }
  .type-personal  { background:#f3f4f6; color:#6b7280; }
  .type-note      { background:#f5f3ff; color:#6d28d9; }

  .state-badge { padding:2px 8px; border-radius:10px; font-size:.67rem; font-weight:700; }
  .badge-matched   { background:#d1fae5; color:#065f46; }
  .badge-needs     { background:#fef3c7; color:#92400e; }
  .badge-unmatched { background:#fee2e2; color:#991b1b; }
  .badge-info      { background:#dbeafe; color:#1e40af; }
  .badge-ignored   { background:#f3f4f6; color:#9ca3af; }

  .entry-actions { padding:.4rem 1rem .6rem; display:flex; gap:.4rem; flex-wrap:wrap; border-top:1px solid #f3f4f6; }
  .btn-sm { padding:4px 11px; border-radius:6px; font-size:.75rem; font-weight:600; cursor:pointer; border:none; transition:all .15s; }
  .btn-green  { background:#d1fae5; color:#065f46; } .btn-green:hover  { background:#6ee7b7; }
  .btn-blue   { background:#dbeafe; color:#1e40af; } .btn-blue:hover   { background:#bfdbfe; }
  .btn-purple { background:#f5f3ff; color:#6d28d9; } .btn-purple:hover { background:#ede9fe; }
  .btn-gray   { background:#f3f4f6; color:#374151; } .btn-gray:hover   { background:#e5e7eb; }
  .btn-red    { background:#fee2e2; color:#991b1b; } .btn-red:hover    { background:#fecaca; }
  .btn-amber  { background:#fef3c7; color:#92400e; } .btn-amber:hover  { background:#fde68a; }

  .day-header { background:var(--navy); color:#fff; padding:.45rem 1rem; border-radius:8px; font-weight:700; font-size:.82rem; margin-bottom:.4rem; margin-top:1.1rem; display:flex; align-items:center; justify-content:space-between; }
  .section-divider { border:none; border-top:2px dashed #e5e7eb; margin:2rem 0 1rem; }
  .section-label-lg { font-family:'Bebas Neue',sans-serif; font-size:1.2rem; color:#9ca3af; margin-bottom:.75rem; }

  .create-booking-form { background:#f8faff; border-top:1.5px solid #dbe4ff; padding:.85rem 1rem; display:none; }
  .create-booking-form.open { display:block; }
  .form-row { display:flex; gap:.6rem; flex-wrap:wrap; align-items:flex-end; }
  .form-group { flex:1; min-width:110px; }
  .form-group label { display:block; font-size:.73rem; font-weight:600; color:#374151; margin-bottom:3px; }
  .form-group input, .form-group select { width:100%; border:1.5px solid #dbe4ff; border-radius:6px; padding:5px 8px; font-size:.84rem; }

  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:14px; padding:1.75rem; width:100%; max-width:460px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,31,.2); }
  .modal-title { font-family:'Bebas Neue',sans-serif; font-size:1.5rem; color:var(--navy); margin-bottom:1.1rem; }
  .mfg { margin-bottom:.85rem; }
  .mfg label { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:3px; }
  .mfg input, .mfg select, .mfg textarea { width:100%; border:1.5px solid #dbe4ff; border-radius:7px; padding:6px 10px; font-size:.87rem; }
  .modal-actions { display:flex; gap:.5rem; justify-content:flex-end; margin-top:1rem; }
  .btn-primary { background:var(--navy); color:#fff; border:none; border-radius:7px; padding:.55rem 1.4rem; font-weight:700; cursor:pointer; font-size:.87rem; }
  .btn-ghost   { background:#f3f4f6; color:#374151; border:none; border-radius:7px; padding:.55rem 1.2rem; font-weight:600; cursor:pointer; font-size:.87rem; }

  .summary-bar { display:flex; gap:.6rem; flex-wrap:wrap; margin-bottom:1.5rem; }
  .summary-pill { padding:4px 12px; border-radius:20px; font-size:.77rem; font-weight:700; }
  .booking-linked { background:#f0fdf4; border-top:1px solid #bbf7d0; padding:.45rem 1rem; font-size:.76rem; color:#166534; }

  .bbox-crop {
    width:140px; height:60px; flex-shrink:0;
    border-radius:6px; overflow:hidden; border:1.5px solid #e5e7eb;
    cursor:pointer; position:relative; background:#f8fafc;
  }
  .bbox-crop img {
    position:absolute;
    transform-origin: top left;
  }
  .bbox-crop:hover { border-color:var(--navy); }
  .bbox-expand { display:none; position:fixed; inset:0; background:rgba(0,0,0,.8); z-index:2000; align-items:center; justify-content:center; cursor:pointer; }
  .bbox-expand.open { display:flex; }
  .bbox-expand img { max-width:95vw; max-height:90vh; border-radius:8px; object-fit:contain; }
</style>

<div class="max-w-4xl mx-auto">

  {{-- Header --}}
  <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
      <a href="{{ route('admin.planner') }}" style="color:#6b7280;font-size:.8rem;text-decoration:none;">← Back to Planner</a>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:.2rem 0 0;">{{ $scan->month }} {{ $scan->year }} Scan</h1>
      <div style="font-size:.78rem;color:#6b7280;">Scanned {{ $scan->created_at->diffForHumans() }} · {{ $scan->entries->count() }} entries</div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
      {{-- Rescan --}}
      @php $hasImages = collect($scan->image_paths ?? [])->every(fn($p) => \Illuminate\Support\Facades\Storage::disk('public')->exists($p)); @endphp
      @if($hasImages)
      <form method="POST" action="{{ route('admin.planner.rescan', $scan->id) }}"
            onsubmit="return confirm('Rescan will wipe all current entries and re-analyze with updated rink session context. Continue?')">
        @csrf
        <button type="submit" style="background:#f59e0b;color:#fff;border:none;border-radius:8px;padding:.6rem 1.2rem;font-weight:700;cursor:pointer;font-size:.82rem;">
          🔄 Rescan
        </button>
      </form>
      @else
      <span style="font-size:.75rem;color:#9ca3af;padding:.6rem 0;">⚠ Images unavailable — upload new scan to rescan</span>
      @endif

      {{-- Mark reviewed / finalized --}}
      @if(!$scan->is_finalized)
      <form method="POST" action="{{ route('admin.planner.finalize', $scan->id) }}">
        @csrf
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.6rem 1.2rem;font-weight:700;cursor:pointer;font-size:.82rem;">✓ Mark Reviewed</button>
      </form>
      @else
      <span style="background:#d1fae5;color:#065f46;padding:.4rem .9rem;border-radius:8px;font-size:.8rem;font-weight:700;">✓ Reviewed</span>
      @endif

      {{-- Delete scan --}}
      <form method="POST" action="{{ route('admin.planner.destroy', $scan->id) }}"
            onsubmit="return confirm('Delete this entire scan and all its entries? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" style="background:#fee2e2;color:#991b1b;border:none;border-radius:8px;padding:.6rem 1rem;font-weight:700;cursor:pointer;font-size:.82rem;">🗑 Delete</button>
      </form>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
  <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.6rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
  <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.6rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">
    @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
  </div>
  @endif

  {{-- Summary --}}
  @php
    $countMatched  = $scan->entries->where('match_status','matched')->count();
    $countNeeds    = $scan->entries->where('match_status','no_booking_found')->where('type','private_lesson')->count();
    $countUnmatch  = $scan->entries->where('match_status','unmatched')->count();
    $countIgnored  = $scan->entries->where('match_status','ignored')->count();
    $countInfo     = $scan->entries->whereIn('type',['lts','ltp','cancelled_public','cancelled_class','note','personal_block'])->where('match_status','!=','ignored')->count();
  @endphp
  <div class="summary-bar">
    <span class="summary-pill" style="background:#d1fae5;color:#065f46;">✓ {{ $countMatched }} matched</span>
    @if($countNeeds>0)<span class="summary-pill" style="background:#fef3c7;color:#92400e;">⚡ {{ $countNeeds }} needs booking</span>@endif
    @if($countUnmatch>0)<span class="summary-pill" style="background:#fee2e2;color:#991b1b;">⚠ {{ $countUnmatch }} unmatched</span>@endif
    @if($countInfo>0)<span class="summary-pill" style="background:#dbeafe;color:#1e40af;">ℹ {{ $countInfo }} info</span>@endif
    @if($countIgnored>0)<span class="summary-pill" style="background:#f3f4f6;color:#9ca3af;">— {{ $countIgnored }} ignored</span>@endif
  </div>

  {{-- Active entries by date --}}
  @php
    $activeEntries = $scan->entries
      ->where('match_status','!=','ignored')
      ->sortBy('date')
      ->groupBy(fn($e) => \Carbon\Carbon::parse($e->date)->toDateString());
    // Build public URLs for scan images
    $imageUrls = collect($scan->image_paths ?? [])->map(fn($p) => Storage::disk('public')->exists($p) ? asset('storage/' . $p) : null)->values()->toArray();
  @endphp

  @foreach($activeEntries as $dateStr => $entries)
  @php $d = \Carbon\Carbon::parse($dateStr); @endphp
  <div class="day-header">
    <span>{{ $d->format('l, F j') }}</span>
    <span style="opacity:.6;font-size:.7rem;">{{ $entries->count() }} entr{{ $entries->count()===1?'y':'ies' }}</span>
  </div>
  @foreach($entries as $entry)
    @php
      $isPrivate = $entry->type === 'private_lesson';
      $stateClass = match(true) {
        $entry->match_status === 'matched'          => 'state-matched',
        $entry->match_status === 'unmatched'        => 'state-unmatched',
        $isPrivate && $entry->match_status === 'no_booking_found' => 'state-needs',
        default => 'state-info',
      };
      $badgeClass = match(true) {
        $entry->match_status === 'matched'   => 'badge-matched',
        $entry->match_status === 'unmatched' => 'badge-unmatched',
        $isPrivate && $entry->match_status === 'no_booking_found' => 'badge-needs',
        default => 'badge-info',
      };
      $badgeLabel = match(true) {
        $entry->match_status === 'matched'   => '✓ Matched',
        $entry->match_status === 'unmatched' => '⚠ No student match',
        $isPrivate && $entry->match_status === 'no_booking_found' => '⚡ No booking',
        $entry->match_status === 'no_booking_expected' => '— N/A',
        default => '— Info',
      };
      $typeClass = match($entry->type) {
        'private_lesson'   => 'type-private',
        'lts'              => 'type-lts',
        'ltp'              => 'type-ltp',
        'cancelled_public','cancelled_class' => 'type-cancelled',
        'personal_block'   => 'type-personal',
        default            => 'type-note',
      };
      $typeLabel = match($entry->type) {
        'private_lesson'   => '⛸️ Private Lesson',
        'lts'              => '🏒 Learn to Skate',
        'ltp'              => '🏑 Learn to Play',
        'cancelled_public' => '✕ Cancelled P/S',
        'cancelled_class'  => '✕ Cancelled Class',
        'personal_block'   => '🚫 Personal',
        default            => '📝 Note',
      };
      $rinkNames = ['creve-coeur'=>'Creve Coeur','kirkwood'=>'Kirkwood','maryville'=>'Maryville','brentwood'=>'Brentwood','webster-groves'=>'Webster Groves'];
    @endphp
    <div class="entry-card {{ $stateClass }}" id="entry-{{ $entry->id }}">
      <div class="entry-header">
        <span class="entry-type {{ $typeClass }}">{{ $typeLabel }}</span>
        @if($entry->time)<span style="font-weight:700;color:#374151;font-size:.88rem;">{{ \Carbon\Carbon::parse($entry->time)->format('g:i A') }}</span>@endif
        @if($entry->extracted_name)
          <span style="font-weight:600;color:var(--navy);font-size:.9rem;">{{ $entry->extracted_name }}</span>
          @if($entry->student)<span style="color:#6b7280;font-size:.75rem;">→ {{ $entry->student->full_name }}</span>@endif
        @endif
        @if($entry->rink && $entry->rink !== 'unknown')<span style="color:#9ca3af;font-size:.72rem;">@ {{ $rinkNames[$entry->rink] ?? $entry->rink }}</span>@endif
        @if($entry->notes)<span style="color:#9ca3af;font-size:.72rem;font-style:italic;">{{ $entry->notes }}</span>@endif
        <div style="margin-left:auto;display:flex;align-items:center;gap:.6rem;">
          {{-- BBox crop thumbnail --}}
          @if($entry->bbox && isset($imageUrls[$entry->image_index ?? 0]) && $imageUrls[$entry->image_index ?? 0])
          @php
            $bbox = $entry->bbox;
            $imgUrl = $imageUrls[$entry->image_index ?? 0];
            // We render the image at a fixed display width and crop to bbox
            // Thumbnail container: 140x60px
            // We scale the image so the bbox region fills the container
            $thumbW = 140; $thumbH = 60;
            $scaleX = $thumbW / ($bbox['w'] / 100);
            $scaleY = $thumbH / ($bbox['h'] / 100);
            $scale  = min($scaleX, $scaleY);
            $imgW   = round($scale);
            $imgH   = round($scale * (3024/4032)); // portrait correction for 4032x3024
            $offX   = -round(($bbox['x'] / 100) * $imgW);
            $offY   = -round(($bbox['y'] / 100) * $imgH);
          @endphp
          <div class="bbox-crop" onclick="expandImage('{{ $imgUrl }}', {{ json_encode($bbox) }})" title="Click to expand">
            <img src="{{ $imgUrl }}"
                 width="{{ $imgW }}"
                 height="{{ $imgH }}"
                 style="left:{{ $offX }}px;top:{{ $offY }}px;"
                 loading="lazy">
          </div>
          @endif
          <span class="state-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
      </div>

      @if($entry->booking)
      <div class="booking-linked">
        ✓ Booking #{{ $entry->booking->confirmation_code ?? $entry->booking->id }}
        — {{ $entry->booking->service->name ?? 'Lesson' }}
        — {{ ucfirst($entry->booking->status) }}
        @if($entry->booking->price_paid) — ${{ number_format($entry->booking->price_paid, 2) }}@endif
      </div>
      @endif

      @if($isPrivate && $entry->match_status === 'no_booking_found' && $entry->student_id)
      <div class="create-booking-form" id="booking-form-{{ $entry->id }}">
        <form method="POST" action="{{ route('admin.planner.create-booking') }}">
          @csrf
          <input type="hidden" name="entry_id" value="{{ $entry->id }}">
          <input type="hidden" name="student_id" value="{{ $entry->student_id }}">
          <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($entry->date)->toDateString() }}">
          <input type="hidden" name="time" value="{{ $entry->time }}">
          <input type="hidden" name="rink" value="{{ $entry->rink }}">
          <div style="font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:.6rem;">Create Booking for {{ $entry->student->full_name }}</div>
          <div class="form-row">
            <div class="form-group">
              <label>Service</label>
              <select name="service_id">
                @foreach(\App\Models\Service::where('is_active',true)->get() as $svc)
                <option value="{{ $svc->id }}" {{ $svc->id === 1 ? 'selected' : '' }}>{{ $svc->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>Price ($)</label>
              <input type="number" name="price" step="0.01" value="50.00" min="0">
            </div>
            <div class="form-group">
              <label>Payment</label>
              <select name="payment_type">
                <option value="cash">Cash</option>
                <option value="venmo">Venmo</option>
              </select>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
              </select>
            </div>
            <div style="display:flex;align-items:flex-end;padding-bottom:1px;">
              <button type="submit" class="btn-sm btn-green">✓ Create</button>
            </div>
          </div>
        </form>
      </div>
      @endif

      <div class="entry-actions">
        @if($isPrivate && $entry->match_status === 'no_booking_found' && $entry->student_id)
        <button class="btn-sm btn-amber" onclick="toggleBookingForm({{ $entry->id }})">⚡ Create Booking</button>
        @endif
        @if($entry->match_status === 'unmatched' && $isPrivate)
        <button class="btn-sm btn-blue" onclick="openLinkModal({{ $entry->id }}, '{{ addslashes($entry->extracted_name) }}')">👤 Link Student</button>
        <button class="btn-sm btn-purple" onclick="openNewStudentModal({{ $entry->id }}, '{{ addslashes($entry->extracted_name) }}')">+ New Student</button>
        @endif
        <button class="btn-sm btn-gray" onclick="openEditModal({{ $entry->id }}, '{{ $entry->type }}', '{{ \Carbon\Carbon::parse($entry->date)->toDateString() }}', '{{ $entry->time }}', '{{ addslashes($entry->extracted_name ?? '') }}', '{{ $entry->rink }}', '{{ addslashes($entry->notes ?? '') }}')">✏ Edit</button>
        <form method="POST" action="{{ route('admin.planner.entry.ignore', $entry->id) }}" style="display:inline">
          @csrf
          <button type="submit" class="btn-sm btn-gray" style="opacity:.6;">— Ignore</button>
        </form>
      </div>
    </div>
  @endforeach
  @endforeach

  {{-- ⚠ Missing from planner — possible cancellations --}}
  @if($missingBookings->isNotEmpty())
  <hr class="section-divider">
  <div style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:#dc2626;margin-bottom:.5rem;">⚠ Bookings Not Found in Planner ({{ $missingBookings->count() }})</div>
  <p style="font-size:.8rem;color:#6b7280;margin-bottom:.75rem;">These confirmed/pending bookings exist in the database for {{ $scan->month }} {{ $scan->year }} but were not detected in this planner scan. They may have been cancelled, rescheduled, or missed by OCR.</p>
  @foreach($missingBookings as $b)
  <div style="background:#fff;border:1.5px solid #fecaca;border-left:4px solid #ef4444;border-radius:10px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div style="flex:1;">
      <div style="font-weight:700;color:#991b1b;font-size:.88rem;">
        {{ \Carbon\Carbon::parse($b->date)->format('l, M j') }}
        @if($b->start_time) · {{ \Carbon\Carbon::parse($b->start_time)->format('g:i A') }}@endif
      </div>
      <div style="font-size:.8rem;color:#374151;margin-top:2px;">
        {{ $b->student?->full_name ?? $b->client_name ?? 'Unknown' }}
        @if($b->service) · {{ $b->service->name }}@endif
        · <span style="font-weight:600;">${{ number_format($b->price_paid, 0) }}</span>
        · <span style="text-transform:capitalize;">{{ $b->status }}</span>
      </div>
    </div>
    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
      {{-- Mark as cancelled --}}
      <form method="POST" action="{{ route('admin.bookings.cancel', $b->id) }}" style="display:inline"
            onsubmit="return confirm('Mark booking #{{ $b->id }} for {{ addslashes($b->student?->first_name ?? $b->client_name) }} as cancelled?')">
        @csrf @method('PATCH')
        <button type="submit" class="btn-sm btn-red">✕ Mark Cancelled</button>
      </form>
      {{-- Dismiss — it was just missed by OCR --}}
      <form method="POST" action="{{ route('admin.planner.dismiss-missing', ['scan' => $scan->id, 'booking' => $b->id]) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn-sm btn-gray">— Dismiss</button>
      </form>
    </div>
  </div>
  @endforeach
  @endif

  {{-- Ignored section --}}
  @php $ignoredEntries = $scan->entries->where('match_status','ignored')->sortBy('date'); @endphp
  @if($ignoredEntries->count() > 0)
  <hr class="section-divider">
  <div class="section-label-lg">— Ignored ({{ $ignoredEntries->count() }})</div>
  @foreach($ignoredEntries as $entry)
  <div class="entry-card state-ignored">
    <div class="entry-header">
      <span style="font-size:.72rem;color:#9ca3af;">{{ \Carbon\Carbon::parse($entry->date)->format('M j') }}</span>
      @if($entry->time)<span style="font-size:.8rem;color:#9ca3af;">{{ \Carbon\Carbon::parse($entry->time)->format('g:i A') }}</span>@endif
      <span style="font-size:.82rem;color:#9ca3af;">{{ $entry->extracted_name ?? $entry->notes ?? $entry->type }}</span>
      <div style="margin-left:auto;">
        <form method="POST" action="{{ route('admin.planner.entry.unignore', $entry->id) }}" style="display:inline">
          @csrf
          <button type="submit" class="btn-sm btn-gray" style="font-size:.68rem;">↩ Restore</button>
        </form>
      </div>
    </div>
  </div>
  @endforeach
  @endif

</div>

{{-- Edit modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Entry</div>
    <form method="POST" id="editForm">
      @csrf @method('PATCH')
      <div class="mfg"><label>Type</label>
        <select name="type" id="edit-type">
          <option value="private_lesson">⛸️ Private Lesson</option>
          <option value="lts">🏒 Learn to Skate</option>
          <option value="ltp">🏑 Learn to Play</option>
          <option value="cancelled_public">✕ Cancelled Public Session</option>
          <option value="cancelled_class">✕ Cancelled Class</option>
          <option value="personal_block">🚫 Personal / Blocked</option>
          <option value="note">📝 Note</option>
        </select>
      </div>
      <div class="mfg"><label>Date</label><input type="date" name="date" id="edit-date"></div>
      <div class="mfg"><label>Time</label><input type="time" name="time" id="edit-time"></div>
      <div class="mfg"><label>Student / Name</label><input type="text" name="extracted_name" id="edit-name"></div>
      <div class="mfg"><label>Rink</label>
        <select name="rink" id="edit-rink">
          <option value="creve-coeur">Creve Coeur</option>
          <option value="kirkwood">Kirkwood</option>
          <option value="maryville">Maryville</option>
          <option value="brentwood">Brentwood</option>
          <option value="webster-groves">Webster Groves</option>
          <option value="unknown">Unknown</option>
        </select>
      </div>
      <div class="mfg"><label>Notes</label><textarea name="notes" id="edit-notes" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

{{-- Link student modal --}}
<div class="modal-overlay" id="linkModal">
  <div class="modal-box">
    <div class="modal-title">Link to Student</div>
    <p style="color:#6b7280;font-size:.83rem;margin-bottom:1rem;">Map "<span id="link-name-display" style="font-weight:700;color:var(--navy);"></span>" to an existing student and save as alias.</p>
    <form method="POST" action="{{ route('admin.planner.add-alias') }}">
      @csrf
      <input type="hidden" name="entry_id" id="link-entry-id">
      <input type="hidden" name="alias" id="link-alias">
      <div class="mfg"><label>Select Student</label>
        <select name="student_id" required>
          <option value="">— Choose student —</option>
          @foreach($students as $s)
          <option value="{{ $s->id }}">{{ $s->full_name }}@if($s->client) ({{ $s->client->full_name }})@endif</option>
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

{{-- New student modal --}}
<div class="modal-overlay" id="newStudentModal">
  <div class="modal-box">
    <div class="modal-title">Create New Student</div>
    <form method="POST" action="{{ route('admin.planner.create-student') }}">
      @csrf
      <input type="hidden" name="entry_id" id="new-entry-id">
      <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="new-first-name" required></div>
      <div class="mfg"><label>Last Name</label><input type="text" name="last_name"></div>
      <div class="mfg"><label>Age</label><input type="number" name="age" min="3" max="80"></div>
      <div class="mfg"><label>Link to Parent/Client (optional)</label>
        <select name="client_id">
          <option value="">— No client yet —</option>
          @foreach(\App\Models\Client::orderBy('name')->get() as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email }})</option>
          @endforeach
        </select>
      </div>
      <div class="mfg"><label>Save planner name as alias</label><input type="text" name="alias" id="new-alias"></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Create Student</button>
      </div>
    </form>
  </div>
</div>

{{-- BBox image expander lightbox --}}
<div class="bbox-expand" id="bboxExpand" onclick="closeExpand()">
  <img id="bboxExpandImg" src="" alt="Planner scan">
</div>

<script>
function expandImage(url, bbox) {
  document.getElementById('bboxExpandImg').src = url;
  document.getElementById('bboxExpand').classList.add('open');
}
function closeExpand() {
  document.getElementById('bboxExpand').classList.remove('open');
}
document.addEventListener('keydown', e => { if(e.key==='Escape') { closeExpand(); closeModals(); } });

function openEditModal(id, type, date, time, name, rink, notes) {
  document.getElementById('editForm').action = `/admin/planner/entry/${id}`;
  document.getElementById('edit-type').value  = type || '';
  document.getElementById('edit-date').value  = date || '';
  document.getElementById('edit-time').value  = time ? time.substring(0,5) : '';
  document.getElementById('edit-name').value  = name || '';
  document.getElementById('edit-rink').value  = rink || 'unknown';
  document.getElementById('edit-notes').value = notes || '';
  document.getElementById('editModal').classList.add('open');
}
function openLinkModal(entryId, name) {
  document.getElementById('link-entry-id').value = entryId;
  document.getElementById('link-alias').value    = name;
  document.getElementById('link-name-display').textContent = name;
  document.getElementById('linkModal').classList.add('open');
}
function openNewStudentModal(entryId, name) {
  document.getElementById('new-entry-id').value   = entryId;
  const parts = name.split(' ');
  document.getElementById('new-first-name').value = parts[0] || name;
  document.getElementById('new-alias').value      = name;
  document.getElementById('newStudentModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
function toggleBookingForm(id) {
  document.getElementById('booking-form-' + id).classList.toggle('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
