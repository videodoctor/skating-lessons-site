@extends('layouts.admin')
@section('title', 'Planner OCR')
@section('content')
<style>
  :root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#E8F5FB; }

  .planner-hero { background:linear-gradient(135deg,var(--navy) 0%,#1a3a6b 100%); border-radius:16px; padding:2.5rem; color:#fff; margin-bottom:2rem; position:relative; overflow:hidden; }
  .planner-hero::after { content:'📓'; position:absolute; right:2rem; top:50%; transform:translateY(-50%); font-size:5rem; opacity:.1; }

  .upload-zone { border:2.5px dashed #cbd5e1; border-radius:14px; padding:3rem 2rem; text-align:center; background:#fff; cursor:pointer; transition:all .2s; }
  .upload-zone:hover, .upload-zone.dragover { border-color:var(--navy); background:#f0f4ff; }
  .upload-zone input[type=file] { display:none; }

  .preview-grid { display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem; }
  .preview-item { position:relative; }
  .preview-item img { height:160px; border-radius:8px; border:2px solid #e5e7eb; object-fit:cover; }
  .preview-badge { position:absolute; top:6px; left:6px; background:var(--navy); color:#fff; border-radius:4px; padding:2px 8px; font-size:.7rem; font-weight:700; }
  .preview-remove { position:absolute; top:6px; right:6px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:22px; height:22px; cursor:pointer; font-size:.75rem; display:flex; align-items:center; justify-content:center; }

  .btn-analyze { background:var(--navy); color:#fff; border:none; border-radius:10px; padding:.9rem 2.5rem; font-weight:700; font-size:1rem; cursor:pointer; transition:background .2s; display:flex; align-items:center; gap:.6rem; }
  .btn-analyze:hover { background:var(--red); }
  .btn-analyze:disabled { background:#9ca3af; cursor:not-allowed; }

  .scan-card { background:#fff; border:1.5px solid #e5eaf2; border-radius:12px; padding:1.25rem; margin-bottom:.75rem; display:flex; align-items:center; gap:1rem; }
  .scan-badge { width:44px; height:44px; border-radius:10px; background:var(--ice); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
  .scan-meta { flex:1; }
  .scan-title { font-weight:700; color:var(--navy); font-size:.95rem; }
  .scan-sub { font-size:.78rem; color:#6b7280; margin-top:2px; }
  .scan-stats { display:flex; gap:.5rem; }
  .stat-pill { padding:2px 8px; border-radius:10px; font-size:.72rem; font-weight:600; }
  .pill-blue { background:#dbeafe; color:#1e40af; }
  .pill-green { background:#d1fae5; color:#065f46; }
  .pill-red { background:#fee2e2; color:#991b1b; }
  .pill-yellow { background:#fef3c7; color:#92400e; }

  .section-head { font-family:'Bebas Neue',sans-serif; font-size:1.3rem; color:var(--navy); margin-bottom:1rem; }
</style>

<div class="max-w-4xl mx-auto">

  {{-- Hero --}}
  <div class="planner-hero">
    <div style="font-size:.75rem;font-weight:700;letter-spacing:.15em;opacity:.6;text-transform:uppercase;margin-bottom:.5rem;">Admin Tool</div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;margin:0;line-height:1;">Planner OCR</h1>
    <p style="opacity:.7;margin-top:.5rem;font-size:.95rem;">Upload Kristine's paper planner photos. Claude will extract lessons, classes, and cancellations — then match them against bookings.</p>
  </div>

  @if(session('success'))
  <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.88rem;font-weight:600;">
    ✓ {{ session('success') }}
  </div>
  @endif

  @if($errors->any())
  <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.88rem;">
    @foreach($errors->all() as $error)
      <div>✕ {{ $error }}</div>
    @endforeach
  </div>
  @endif

  {{-- Upload form --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:14px;padding:2rem;margin-bottom:2rem;">
    <div class="section-head">📷 Upload Planner Pages</div>
    <p style="color:#6b7280;font-size:.88rem;margin-bottom:1.5rem;">Upload 1 or 2 photos — one for each half of the week (Sun–Tue and Wed–Sat).</p>

    <form method="POST" action="{{ route('admin.planner.analyze') }}" enctype="multipart/form-data" id="planner-form">
      @csrf

      <div class="upload-zone" id="upload-zone" onclick="document.getElementById('image-input').click()">
        <input type="file" id="image-input" name="images[]" accept="image/*" multiple>
        <div style="font-size:2.5rem;margin-bottom:.75rem;">📸</div>
        <div style="font-weight:700;color:#374151;font-size:1.05rem;">Drop planner photos here or click to browse</div>
        <div style="color:#9ca3af;font-size:.82rem;margin-top:.3rem;">JPEG or PNG • Up to 2 images • Max 10MB each</div>
      </div>

      <div class="preview-grid" id="preview-grid"></div>

      <div style="margin-top:1.5rem;display:flex;align-items:center;gap:1rem;">
        <button type="submit" class="btn-analyze" id="analyze-btn" disabled>
          🔍 Analyze with Claude Vision
        </button>
        <span style="color:#9ca3af;font-size:.82rem;" id="file-count">No files selected</span>
      </div>
    </form>
  </div>

  {{-- Recent scans --}}
  @if($recentScans->isNotEmpty())
  <div>
    <div class="section-head">🕐 Recent Scans</div>
    @foreach($recentScans as $scan)
    @php
      $confirmed = $scan->entries->whereNotNull('confirmed_at')->count();
      $total     = $scan->entries->count();
      $unmatched = $scan->entries->where('match_status', 'unmatched')->count();
      $needsWork = $scan->entries->filter(fn($e) => $e->needsReview())->count();
    @endphp
    <div class="scan-card">
      <div class="scan-badge">📓</div>
      <div class="scan-meta">
        <div class="scan-title">{{ $scan->month }} {{ $scan->year }}</div>
        <div class="scan-sub">Scanned {{ $scan->created_at->diffForHumans() }} · {{ $total }} entries extracted</div>
        <div class="scan-stats" style="margin-top:.4rem;">
          <span class="stat-pill pill-blue">{{ $total }} total</span>
          <span class="stat-pill pill-green">{{ $confirmed }} confirmed</span>
          @if($unmatched > 0)<span class="stat-pill pill-red">{{ $unmatched }} unmatched</span>@endif
          @if($needsWork > 0)<span class="stat-pill pill-yellow">{{ $needsWork }} needs review</span>@endif
        </div>
      </div>
      <a href="{{ route('admin.planner.scan', $scan->id) }}"
         style="background:var(--navy);color:#fff;padding:.5rem 1.25rem;border-radius:7px;font-size:.82rem;font-weight:700;text-decoration:none;white-space:nowrap;">
        Review →
      </a>
    </div>
    @endforeach
  </div>
  @endif

</div>

<script>
const input    = document.getElementById('image-input');
const zone     = document.getElementById('upload-zone');
const grid     = document.getElementById('preview-grid');
const btn      = document.getElementById('analyze-btn');
const countLbl = document.getElementById('file-count');
let   files    = [];

function updatePreviews() {
  grid.innerHTML = '';
  files.forEach((f, i) => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'preview-item';
      div.innerHTML = `
        <img src="${e.target.result}" alt="Page ${i+1}">
        <div class="preview-badge">Page ${i+1}</div>
        <button class="preview-remove" onclick="removeFile(${i})">✕</button>
      `;
      grid.appendChild(div);
    };
    reader.readAsDataURL(f);
  });
  btn.disabled = files.length === 0;
  countLbl.textContent = files.length === 0 ? 'No files selected' : `${files.length} file${files.length>1?'s':''} selected`;
}

function removeFile(i) {
  files.splice(i, 1);
  updatePreviews();
}

input.addEventListener('change', e => {
  files = Array.from(e.target.files).slice(0, 2);
  updatePreviews();
});

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.classList.remove('dragover');
  files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')).slice(0, 2);
  updatePreviews();
});

// Override form submit to use our files array
document.getElementById('planner-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const dt = new DataTransfer();
  files.forEach(f => dt.items.add(f));
  input.files = dt.files;
  btn.disabled = true;
  btn.textContent = '⏳ Analyzing...';
  this.submit();
});
</script>
@endsection
