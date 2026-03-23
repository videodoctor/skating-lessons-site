@extends('layouts.admin')
@section('title', 'Scraper Status — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .rink-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;margin-bottom:1rem;overflow:hidden;}
  .rink-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;cursor:pointer;gap:.75rem;flex-wrap:wrap;}
  .rink-header:hover{background:#f8fafc;}
  .rink-name{font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--navy);}
  .rink-meta{font-size:.78rem;color:#6b7280;margin-top:1px;}
  .status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
  .dot-ok{background:#10b981;} .dot-warn{background:#f59e0b;} .dot-error{background:#ef4444;} .dot-never{background:#d1d5db;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;} .pill-red{background:#fee2e2;color:#991b1b;}
  .pill-yellow{background:#fef3c7;color:#92400e;} .pill-gray{background:#f3f4f6;color:#6b7280;}
  .pill-blue{background:#dbeafe;color:#1e40af;}
  .run-table th{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.5rem .75rem;text-align:left;}
  .run-table td{padding:.55rem .75rem;border-bottom:1px solid #f3f4f6;font-size:.82rem;vertical-align:top;}
  .run-table tr:last-child td{border-bottom:none;}
  .log-pre{background:#0f172a;color:#94a3b8;font-size:.7rem;padding:.75rem 1rem;border-radius:6px;max-height:200px;overflow-y:auto;white-space:pre-wrap;word-break:break-all;margin-top:.5rem;}
  .btn-run{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.45rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;}
  .btn-run:hover{background:var(--red);}
  .collapse-body{display:none;border-top:1px solid #f3f4f6;padding:1rem 1.25rem;}
  .collapse-body.open{display:block;}
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:.6rem;margin-bottom:1rem;}
  .stat-box{background:#f8fafc;border-radius:8px;padding:.6rem .75rem;text-align:center;}
  .stat-num{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);}
  .stat-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Scraper Status</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">Daily scrapes run at 3:00 AM · {{ $rinks->count() }} active rinks</p>
  </div>
  <form method="POST" action="{{ route('admin.scraper.run-all') }}"
        onsubmit="return confirm('Run all scrapers now? This may take 30-60 seconds.')">
    @csrf
    <button type="submit" class="btn-run">▶ Run All Scrapers Now</button>
  </form>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">✕ {{ session('error') }}</div>
@endif

@foreach($rinks as $rink)
@php
  $latestRun = $rinkRuns[$rink->id]->first();
  $allRuns   = $rinkRuns[$rink->id];
  $sessions  = $rinkSessions[$rink->id] ?? collect();

  if (!$latestRun) {
    $dotClass = 'dot-never'; $statusLabel = 'Never scraped'; $pillClass = 'pill-gray';
  } elseif ($latestRun->had_errors) {
    $dotClass = 'dot-error'; $statusLabel = 'Last run had errors'; $pillClass = 'pill-red';
  } elseif ($latestRun->scraped_at->lt(now()->subDays(2))) {
    $dotClass = 'dot-warn'; $statusLabel = 'Stale — over 2 days old'; $pillClass = 'pill-yellow';
  } else {
    $dotClass = 'dot-ok'; $statusLabel = 'OK'; $pillClass = 'pill-green';
  }
@endphp

<div class="rink-card">
  <div class="rink-header" onclick="toggleCard('rink-{{ $rink->id }}')">
    <div style="display:flex;align-items:center;gap:.75rem;flex:1;">
      <div class="status-dot {{ $dotClass }}"></div>
      <div>
        <div class="rink-name">{{ $rink->name }}</div>
        <div class="rink-meta">
          @if($latestRun)
            Last scraped {{ $latestRun->scraped_at->diffForHumans() }}
            · {{ $sessions->count() }} upcoming sessions
          @else
            Never scraped
          @endif
        </div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
      <span class="pill {{ $pillClass }}">{{ $statusLabel }}</span>
      @if($latestRun)
        <span class="pill pill-blue">{{ $latestRun->sessions_found }} found</span>
        @if($latestRun->sessions_added > 0)<span class="pill pill-green">+{{ $latestRun->sessions_added }} added</span>@endif
        @if($latestRun->sessions_removed > 0)<span class="pill pill-yellow">-{{ $latestRun->sessions_removed }} removed</span>@endif
        @if($latestRun->had_errors)<span class="pill pill-red">⚠ Errors</span>@endif
      @endif
      <form method="POST" action="{{ route('admin.scraper.run-one', $rink->slug) }}" style="display:inline"
            onsubmit="event.stopPropagation();return confirm('Re-scrape {{ $rink->name }} now?')">
        @csrf
        <button type="submit" class="btn-run" style="font-size:.72rem;padding:.35rem .75rem;">▶ Run</button>
      </form>
      <span style="color:#9ca3af;font-size:.85rem;">{{ session('open_rink') === $rink->id ? '▲' : '▼' }}</span>
    </div>
  </div>

  <div class="collapse-body {{ $latestRun?->had_errors ? 'open' : '' }}" id="rink-{{ $rink->id }}">

    {{-- Stats --}}
    @if($latestRun)
    <div class="stat-grid">
      <div class="stat-box"><div class="stat-num">{{ $latestRun->sessions_found }}</div><div class="stat-lbl">Found</div></div>
      <div class="stat-box"><div class="stat-num">{{ $latestRun->sessions_added }}</div><div class="stat-lbl">Added</div></div>
      <div class="stat-box"><div class="stat-num">{{ $latestRun->sessions_removed }}</div><div class="stat-lbl">Removed</div></div>
      <div class="stat-box"><div class="stat-num">{{ $sessions->count() }}</div><div class="stat-lbl">Upcoming</div></div>
      <div class="stat-box"><div class="stat-num">{{ $allRuns->count() }}</div><div class="stat-lbl">Total Runs</div></div>
    </div>
    @endif

    {{-- Upcoming sessions --}}
    @if($sessions->isNotEmpty())
    <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.07em;">Upcoming Sessions (next 14 days)</div>
    <table class="run-table w-full" style="margin-bottom:1rem;">
      <thead><tr><th>Date</th><th>Day</th><th>Time</th><th>Type</th></tr></thead>
      <tbody>
      @foreach($sessions->take(14) as $s)
      <tr>
        <td>{{ \Carbon\Carbon::parse($s->date)->format('M j') }}</td>
        <td style="color:#6b7280;">{{ \Carbon\Carbon::parse($s->date)->format('D') }}</td>
        <td>{{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}</td>
        <td><span class="pill pill-blue">{{ str_replace('_',' ',ucfirst($s->session_type)) }}</span></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @endif

    {{-- Settings: URL + OCR Provider --}}
    @if($rink->slug === 'creve-coeur')
    <form method="POST" action="{{ route('admin.scraper.save-settings', $rink->slug) }}" style="margin-bottom:1rem;">
      @csrf @method('PATCH')
      <div style="background:#f8fafc;border:1.5px solid #e5eaf2;border-radius:8px;padding:1rem;">
        <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.07em;">⚙️ Scraper Settings</div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:end;">
          <div>
            <label style="font-size:.75rem;font-weight:600;color:#374151;display:block;margin-bottom:3px;">Schedule URL</label>
            <input type="text" name="schedule_url" value="{{ $rink->schedule_url }}"
                   style="width:100%;border:1.5px solid #e5eaf2;border-radius:6px;padding:.45rem .7rem;font-size:.82rem;">
          </div>
          <div>
            <label style="font-size:.75rem;font-weight:600;color:#374151;display:block;margin-bottom:3px;">OCR Provider</label>
            <select name="ocr_provider" style="border:1.5px solid #e5eaf2;border-radius:6px;padding:.45rem .7rem;font-size:.82rem;background:#fff;">
              <option value="claude" {{ ($rink->ocr_provider ?? 'claude') === 'claude' ? 'selected' : '' }}>🤖 Claude Vision</option>
              <option value="paddleocr" {{ ($rink->ocr_provider ?? '') === 'paddleocr' ? 'selected' : '' }}>🏓 PaddleOCR</option>
            </select>
          </div>
        </div>
        <button type="submit" style="margin-top:.75rem;background:var(--navy);color:#fff;border:none;border-radius:6px;padding:.4rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer;">Save Settings</button>
        @if(session('settings_saved_' . $rink->id))
        <span style="color:#065f46;font-size:.78rem;margin-left:.5rem;">✓ Saved</span>
        @endif
      </div>
    </form>
    @endif

    {{-- Run history --}}
    <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.07em;">Run History</div>
    <table class="run-table w-full">
      <thead><tr><th>Date</th><th>Month</th><th>Found</th><th>Added</th><th>Removed</th><th>Status</th><th>Log</th></tr></thead>
      <tbody>
      @forelse($allRuns->take(6) as $run)
      <tr>
        <td style="white-space:nowrap;">{{ $run->scraped_at?->format('M j, g:i A') ?? '—' }}</td>
        <td>{{ $run->month_name }} {{ $run->year }}</td>
        <td>{{ $run->sessions_found }}</td>
        <td style="color:#065f46;font-weight:600;">+{{ $run->sessions_added }}</td>
        <td style="color:#92400e;font-weight:600;">-{{ $run->sessions_removed }}</td>
        <td>
          @if($run->had_errors)
            <span class="pill pill-red">⚠ Errors</span>
          @else
            <span class="pill pill-green">✓ OK</span>
          @endif
        </td>
        <td>
          @if($run->scrape_log)
          <button class="pill pill-gray" style="cursor:pointer;border:none;" onclick="toggleLog('log-{{ $run->id }}')">View Log</button>
          <div id="log-{{ $run->id }}" style="display:none;">
            <pre class="log-pre">{{ $run->scrape_log }}</pre>
          </div>
          @else —
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="7" style="color:#9ca3af;text-align:center;padding:1rem;">No runs recorded.</td></tr>
      @endforelse
      </tbody>
    </table>

  </div>
</div>
@endforeach

<script>
function toggleCard(id) {
  const el = document.getElementById(id);
  el.classList.toggle('open');
}
function toggleLog(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
