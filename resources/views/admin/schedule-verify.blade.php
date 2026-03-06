@extends('layouts.admin')
@section('title', 'Schedule Verification')
@section('content')
<style>
  :root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#E8F5FB; }

  /* ── Layout ── */
  .verify-wrap { display:flex; flex-direction:column; height:calc(100vh - 120px); min-height:600px; }
  .verify-toolbar { background:#fff; border-bottom:1.5px solid #e5eaf2; padding:12px 20px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; flex-shrink:0; }
  .verify-panels { display:grid; grid-template-columns:1fr 1fr; flex:1; overflow:hidden; gap:0; }
  .panel { display:flex; flex-direction:column; overflow:hidden; border-right:1.5px solid #e5eaf2; }
  .panel:last-child { border-right:none; }
  .panel-header { background:var(--navy); color:#fff; padding:10px 16px; font-weight:700; font-size:.9rem; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
  .panel-header.right { background:#1a3a6b; }
  .panel-body { flex:1; overflow:auto; }

  /* ── Toolbar controls ── */
  .tb-select { border:1.5px solid #dbe4ff; border-radius:6px; padding:6px 10px; font-size:.88rem; color:var(--navy); background:#f8faff; }
  .tb-btn { padding:6px 14px; border-radius:6px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; }
  .tb-btn-primary { background:var(--navy); color:#fff; }
  .tb-btn-primary:hover { background:var(--red); }
  .tb-btn-danger { background:#fee2e2; color:#991b1b; border:1.5px solid #fecaca; }
  .tb-btn-danger:hover { background:#fca5a5; }
  .tb-btn-green { background:#d1fae5; color:#065f46; border:1.5px solid #a7f3d0; }
  .tb-label { font-size:.8rem; font-weight:600; color:#6b7280; }

  /* ── Source panel (left) ── */
  .source-iframe { width:100%; height:100%; border:none; display:block; }
  .source-img { max-width:100%; height:auto; display:block; }
  .source-missing { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap:12px; color:#9ca3af; }
  .source-missing svg { width:48px; height:48px; opacity:.3; }

  /* ── Calendar panel (right) ── */
  .cal-wrap { padding:16px; }
  .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
  .cal-dow { text-align:center; font-size:.72rem; font-weight:700; color:#9ca3af; padding:4px 0; text-transform:uppercase; letter-spacing:.05em; }
  .cal-day { min-height:80px; background:#f8faff; border:1.5px solid #e5eaf2; border-radius:6px; padding:5px; position:relative; }
  .cal-day.today { border-color:var(--navy); background:#f0f4ff; }
  .cal-day.empty { background:transparent; border-color:transparent; }
  .cal-day-num { font-size:.75rem; font-weight:700; color:#374151; margin-bottom:3px; }
  .session-chip { font-size:.68rem; background:var(--navy); color:#fff; border-radius:4px; padding:2px 5px; margin-bottom:2px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:4px; }
  .session-chip:hover { background:var(--red); }
  .session-chip .del { opacity:.6; font-size:.7rem; }
  .session-chip .del:hover { opacity:1; }
  .add-session-btn { font-size:.65rem; color:#9ca3af; border:1px dashed #d1d5db; border-radius:4px; padding:2px 4px; cursor:pointer; width:100%; text-align:center; margin-top:2px; background:none; }
  .add-session-btn:hover { color:var(--navy); border-color:var(--navy); }

  /* ── Scrape info bar ── */
  .scrape-info { background:#f0f4ff; border-bottom:1.5px solid #dbe4ff; padding:8px 16px; font-size:.8rem; color:#374151; display:flex; align-items:center; gap:12px; flex-wrap:wrap; flex-shrink:0; }
  .scrape-info .badge { padding:2px 8px; border-radius:12px; font-weight:600; font-size:.72rem; }
  .badge-ok { background:#d1fae5; color:#065f46; }
  .badge-error { background:#fee2e2; color:#991b1b; }
  .badge-none { background:#f3f4f6; color:#6b7280; }

  /* ── Edit modal ── */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#fff; border-radius:12px; padding:24px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,31,.15); }
  .modal-title { font-family:'Bebas Neue',sans-serif; font-size:1.4rem; color:var(--navy); margin-bottom:16px; }
  .form-group { margin-bottom:14px; }
  .form-group label { display:block; font-size:.82rem; font-weight:600; color:#374151; margin-bottom:4px; }
  .form-group input, .form-group select { width:100%; border:1.5px solid #dbe4ff; border-radius:6px; padding:8px 10px; font-size:.9rem; }
  .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--navy); }
  .modal-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:16px; }

  /* ── Scrape output ── */
  .scrape-output { background:#1e1e2e; color:#a6e3a1; font-family:monospace; font-size:.78rem; padding:16px; border-radius:8px; white-space:pre-wrap; max-height:200px; overflow-y:auto; margin:12px 16px; }

  @media(max-width:900px) { .verify-panels { grid-template-columns:1fr; } .panel:first-child { height:300px; } }
</style>

<div class="verify-wrap">

  {{-- TOOLBAR --}}
  <form method="GET" action="{{ route('admin.schedule.verify') }}" class="verify-toolbar">
    <span class="tb-label">RINK</span>
    <select name="rink_id" class="tb-select" onchange="this.form.submit()">
      @foreach($rinks as $r)
        <option value="{{ $r->id }}" @selected($r->id == $rinkId)>{{ $r->name }}</option>
      @endforeach
    </select>

    <span class="tb-label">MONTH</span>
    <select name="month" class="tb-select" onchange="this.form.submit()">
      @foreach($monthOptions as $num => $name)
        <option value="{{ $num }}" @selected($num == $month)>{{ $name }}</option>
      @endforeach
    </select>

    <select name="year" class="tb-select" onchange="this.form.submit()">
      @foreach($yearOptions as $y)
        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
      @endforeach
    </select>

    {{-- Re-scrape button --}}
    <button type="button" class="tb-btn tb-btn-green" onclick="openAddModal()">+ Add Session</button>
  </form>
  @if($selectedRink)
  <form method="POST" action="{{ route('admin.schedule.rescrape') }}" style="display:inline;margin-left:8px;">
    @csrf
    <input type="hidden" name="rink_id" value="{{ $selectedRink->id }}">
    <button type="submit" class="tb-btn tb-btn-danger" onclick="return confirm('Re-scrape {{ $selectedRink->name }}? This will clear and rebuild all future unbooked slots.')">
      ↻ Re-scrape
    </button>
  </form>
  @endif

  {{-- SCRAPE INFO BAR --}}
  @if($scrapeRun)
  <div class="scrape-info">
    <span>Last scraped: <strong>{{ $scrapeRun->scraped_at->diffForHumans() }}</strong></span>
    <span class="badge {{ $scrapeRun->had_errors ? 'badge-error' : 'badge-ok' }}">
      {{ $scrapeRun->had_errors ? '⚠ Had Errors' : '✓ Clean' }}
    </span>
    <span>{{ $scrapeRun->sessions_found }} sessions found</span>
    <span>Source: <code style="font-size:.75rem;">{{ $scrapeRun->source_url }}</code></span>
  </div>
  @else
  <div class="scrape-info">
    <span class="badge badge-none">No scrape run recorded for {{ $monthOptions[$month] }} {{ $year }}</span>
  </div>
  @endif

  {{-- Flash messages --}}
  @if(session('success'))
  <div style="background:#d1fae5;border-bottom:1.5px solid #a7f3d0;color:#065f46;padding:8px 16px;font-size:.85rem;font-weight:600;flex-shrink:0;">
    ✓ {{ session('success') }}
  </div>
  @endif

  {{-- Scrape output --}}
  @if(session('scrape_output'))
  <div class="scrape-output">{{ session('scrape_output') }}</div>
  @endif

  {{-- PANELS --}}
  <div class="verify-panels">

    {{-- LEFT: Raw source --}}
    <div class="panel">
      <div class="panel-header">
        📄 Published Source
        <!-- DEBUG: run={{ $scrapeRun?->id }} hasFile={{ $scrapeRun?->hasSourceFile() ? 'YES' : 'NO' }} path={{ $scrapeRun?->source_file_path }} -->
        @if($scrapeRun && $scrapeRun->hasSourceFile())
          <a href="{{ route('admin.schedule.source', $scrapeRun->id) }}" target="_blank"
             style="color:rgba(255,255,255,.7);font-size:.75rem;text-decoration:none;">↗ Open full</a>
        @endif
      </div>
      <div class="panel-body">
        @if($scrapeRun && $scrapeRun->hasSourceFile())
          @if($scrapeRun->source_type === 'image')
            <img src="{{ route('admin.schedule.source', $scrapeRun->id) }}" class="source-img" alt="Schedule image">
          @else
            <iframe src="{{ route('admin.schedule.source', $scrapeRun->id) }}" class="source-iframe"></iframe>
          @endif
        @elseif($scrapeRun && $scrapeRun->source_url)
          {{-- Fallback: embed live URL if no stored file --}}
          <iframe src="{{ $scrapeRun->source_url }}" class="source-iframe"></iframe>
        @else
          <div class="source-missing">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No stored source file for this month.</p>
            <p style="font-size:.8rem;">Run a scrape to capture the raw schedule.</p>
          </div>
        @endif
      </div>
    </div>

    {{-- RIGHT: Parsed calendar --}}
    <div class="panel">
      <div class="panel-header right">
        📅 Parsed Sessions — {{ $monthOptions[$month] }} {{ $year }}
        <span style="font-size:.78rem;opacity:.7;">{{ $sessions->count() }} session(s)</span>
      </div>
      <div class="panel-body">
        <div class="cal-wrap">

          {{-- Day of week headers --}}
          <div class="cal-grid" style="margin-bottom:4px;">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
              <div class="cal-dow">{{ $dow }}</div>
            @endforeach
          </div>

          {{-- Calendar grid --}}
          @php
            $firstDay  = \Carbon\Carbon::create($year, $month, 1);
            $startDow  = $firstDay->dayOfWeek;
            $daysInMo  = $firstDay->daysInMonth;
            $today     = now()->toDateString();
            $dayNum    = 1;
          @endphp

          <div class="cal-grid">
            {{-- Empty cells before first day --}}
            @for($e = 0; $e < $startDow; $e++)
              <div class="cal-day empty"></div>
            @endfor

            {{-- Day cells --}}
            @while($dayNum <= $daysInMo)
              @php
                $dateStr  = \Carbon\Carbon::create($year, $month, $dayNum)->toDateString();
                $daySessions = $calendarDays[$dateStr] ?? collect();
              @endphp
              <div class="cal-day {{ $dateStr === $today ? 'today' : '' }}">
                <div class="cal-day-num">{{ $dayNum }}</div>

                @foreach($daySessions as $session)
                  <div class="session-chip" onclick="openEditModal({{ $session->id }}, '{{ $session->start_time }}', '{{ $session->end_time }}')">
                    <span>{{ \Carbon\Carbon::parse($session->start_time)->format('g:ia') }}-{{ \Carbon\Carbon::parse($session->end_time)->format('g:ia') }}</span>
                    <form method="POST" action="{{ route('admin.schedule.session.destroy', $session->id) }}" style="display:inline" onsubmit="return confirm('Delete this session?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="del" title="Delete">✕</button>
                    </form>
                  </div>
                @endforeach

                <button class="add-session-btn" onclick="openAddModal('{{ $dateStr }}')">+ add</button>
              </div>
              @php $dayNum++ @endphp
            @endwhile

            {{-- Fill remaining cells --}}
            @php $remaining = (7 - (($startDow + $daysInMo) % 7)) % 7; @endphp
            @for($r = 0; $r < $remaining; $r++)
              <div class="cal-day empty"></div>
            @endfor
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- ADD SESSION MODAL --}}
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div class="modal-title">Add Session</div>
    <form method="POST" action="{{ route('admin.schedule.session.store') }}">
      @csrf
      <input type="hidden" name="rink_id" value="{{ $rinkId }}">
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="date" id="addDate" required>
      </div>
      <div class="form-group">
        <label>Start Time</label>
        <input type="time" name="start_time" required>
      </div>
      <div class="form-group">
        <label>End Time</label>
        <input type="time" name="end_time" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="tb-btn" style="background:#f3f4f6;color:#374151;" onclick="closeModals()">Cancel</button>
        <button type="submit" class="tb-btn tb-btn-primary">Add Session</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT SESSION MODAL --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Session</div>
    <form method="POST" id="editForm">
      @csrf @method('PATCH')
      <div class="form-group">
        <label>Start Time</label>
        <input type="time" name="start_time" id="editStart" required>
      </div>
      <div class="form-group">
        <label>End Time</label>
        <input type="time" name="end_time" id="editEnd" required>
      </div>
      <div class="modal-actions">
        <button type="button" class="tb-btn" style="background:#f3f4f6;color:#374151;" onclick="closeModals()">Cancel</button>
        <button type="submit" class="tb-btn tb-btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal(date = '') {
  document.getElementById('addDate').value = date;
  document.getElementById('addModal').classList.add('open');
}
function openEditModal(id, start, end) {
  document.getElementById('editForm').action = `/admin/schedule/session/${id}`;
  // Convert HH:MM:SS to HH:MM for time input
  document.getElementById('editStart').value = start.substring(0,5);
  document.getElementById('editEnd').value   = end.substring(0,5);
  document.getElementById('editModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
// Close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
