@extends('layouts.admin')
@section('title', 'Media Gallery — Admin')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .stat-row{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;}
  .stat-chip{background:#fff;border:1.5px solid #e5eaf2;border-radius:8px;padding:.6rem 1rem;font-size:.85rem;}
  .stat-chip strong{color:var(--navy);font-size:1.1rem;}
  .filter-bar{background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem;}
  .filter-bar form{display:flex;flex-wrap:wrap;gap:.6rem;align-items:flex-end;}
  .filter-group label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#9ca3af;margin-bottom:2px;}
  .filter-group select,.filter-group input{border:1.5px solid #dbe4ff;border-radius:6px;padding:5px 8px;font-size:.85rem;}
  .media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:.75rem;}
  @media(max-width:768px){.media-grid{grid-template-columns:repeat(2,1fr);}}
  .media-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:8px;overflow:hidden;position:relative;}
  .media-card .media-frame{width:100%;aspect-ratio:1/1;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f1f5f9;}
  .media-card .media-frame img,.media-card .media-frame video{width:100%;height:100%;object-fit:contain;display:block;}
  .media-card-body{padding:.5rem .65rem;}
  .media-card-meta{font-size:.65rem;color:#9ca3af;line-height:1.5;}
  .media-card .student-tag{position:absolute;bottom:60px;left:6px;background:rgba(0,31,91,.85);color:#fff;font-size:.65rem;font-weight:600;padding:2px 7px;border-radius:4px;}
  .media-card .type-icon{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.5);color:#fff;font-size:.75rem;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
  .media-card .edit-btn{position:absolute;top:6px;left:34px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:24px;height:24px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .media-card .delete-btn{position:absolute;top:6px;right:6px;background:rgba(220,38,38,.8);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:.65rem;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s;}
  .media-card:hover .delete-btn{opacity:1;}
  .upload-section{background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;}
  .upload-zone{border:2px dashed #bfdbfe;border-radius:8px;background:#eff6ff;text-align:center;padding:1.5rem;cursor:pointer;transition:all .15s;}
  .upload-zone:hover{border-color:var(--navy);background:#dbeafe;}
  .btn-navy{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:7px 18px;font-weight:700;font-size:.85rem;cursor:pointer;}
  .btn-navy:hover{background:var(--red);}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Media Gallery</h1>
    <p style="color:#6b7280;font-size:.85rem;">All student photos and videos</p>
  </div>
</div>

{{-- Stats --}}
<div class="stat-row">
  <div class="stat-chip"><strong>{{ $stats['total'] }}</strong> total files</div>
  <div class="stat-chip"><strong>{{ $stats['photos'] }}</strong> photos</div>
  <div class="stat-chip"><strong>{{ $stats['videos'] }}</strong> videos</div>
  <div class="stat-chip"><strong>{{ number_format($stats['size'] / 1048576, 1) }}</strong> MB stored</div>
</div>

{{-- Upload --}}
<div class="upload-section">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--navy);margin:0 0 .6rem;">Upload</h2>
  <form method="POST" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data">
    @csrf
    <div style="display:flex;flex-wrap:wrap;gap:.6rem;align-items:flex-end;margin-bottom:.6rem;">
      <div class="filter-group">
        <label>Student *</label>
        <select name="student_id" required style="min-width:180px;">
          <option value="">— Select student —</option>
          @foreach($students as $s)
            <option value="{{ $s->id }}">{{ $s->full_name }}@if($s->client) ({{ $s->client->first_name }})@endif</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group" style="flex:1;min-width:150px;">
        <label>Caption (optional)</label>
        <input type="text" name="caption" placeholder="e.g. Practice at Creve Coeur" style="width:100%;">
      </div>
    </div>
    <div class="upload-zone" onclick="document.getElementById('gallery-upload').click()">
      <input type="file" id="gallery-upload" name="files[]" multiple accept="image/*,video/*,.zip" style="display:none;"
        onchange="document.getElementById('gallery-file-count').textContent = this.files.length + ' file(s) selected'">
      <p style="font-weight:600;color:#1e40af;font-size:.9rem;">📸 Drop files or tap to browse</p>
      <p style="font-size:.78rem;color:#6b7280;">JPG, PNG, WebP, HEIC, MP4, MOV, or ZIP — up to 500MB, 20 files max</p>
      <p id="gallery-file-count" style="font-size:.82rem;color:var(--navy);font-weight:600;margin-top:.25rem;"></p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center;margin-top:.6rem;">
      <button type="submit" class="btn-navy" id="galleryUploadBtn">Upload</button>
      <div id="galleryProgress" style="display:none;flex:1;">
        <div style="display:flex;align-items:center;gap:.5rem;">
          <div style="flex:1;background:#e5eaf2;border-radius:4px;height:8px;overflow:hidden;">
            <div id="galleryBar" style="width:0%;height:100%;background:var(--navy);border-radius:4px;transition:width .3s;"></div>
          </div>
          <span id="galleryText" style="font-size:.78rem;color:#6b7280;white-space:nowrap;">0%</span>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.querySelector('form[action="{{ route("admin.media.upload") }}"]').addEventListener('submit', function(e) {
  e.preventDefault();
  var form = this;
  var btn = document.getElementById('galleryUploadBtn');
  var prog = document.getElementById('galleryProgress');
  var bar = document.getElementById('galleryBar');
  var text = document.getElementById('galleryText');

  btn.disabled = true; btn.textContent = 'Uploading...'; btn.style.opacity = '.5';
  prog.style.display = 'block';

  var xhr = new XMLHttpRequest();
  xhr.open('POST', form.action, true);
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      var pct = Math.round((e.loaded / e.total) * 100);
      bar.style.width = pct + '%';
      var mb = function(b) { return (b/1048576).toFixed(1) + 'MB'; };
      text.textContent = pct + '% — ' + mb(e.loaded) + ' / ' + mb(e.total);
      if (pct >= 100) text.textContent = 'Processing on server...';
    }
  };
  xhr.onload = function() { window.location.reload(); };
  xhr.onerror = function() {
    text.textContent = 'Upload failed.'; text.style.color = '#dc2626';
    btn.disabled = false; btn.textContent = 'Upload'; btn.style.opacity = '1';
  };
  xhr.send(new FormData(form));
});
</script>

{{-- Filters --}}
<div class="filter-bar">
  <form method="GET">
    <div class="filter-group">
      <label>Student</label>
      <select name="student_id" onchange="this.form.submit()">
        <option value="">All students</option>
        @foreach($students as $s)
          <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->full_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <label>Parent</label>
      <select name="client_id" onchange="this.form.submit()">
        <option value="">All parents</option>
        @foreach($clients as $c)
          <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->full_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <label>Type</label>
      <select name="type" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="photo" {{ request('type') === 'photo' ? 'selected' : '' }}>Photos</option>
        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
      </select>
    </div>
    <div class="filter-group" style="flex:1;min-width:120px;">
      <label>Search</label>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Caption, filename, name..." style="width:100%;" onchange="this.form.submit()">
    </div>
    @if(request()->hasAny(['student_id', 'client_id', 'type', 'q']))
      <a href="{{ route('admin.media.index') }}" style="font-size:.82rem;color:var(--red);font-weight:600;text-decoration:none;padding-bottom:6px;">Clear filters</a>
    @endif
  </form>
</div>

{{-- Grid --}}
@if($media->isEmpty())
  <div style="background:#fff;border:1.5px dashed #e5eaf2;border-radius:10px;padding:3rem;text-align:center;color:#9ca3af;">
    No media found{{ request()->hasAny(['student_id', 'client_id', 'type', 'q']) ? ' matching your filters' : '' }}.
  </div>
@else
  <div class="media-grid">
    @foreach($media as $item)
    <div class="media-card">
      <div class="media-frame">
        @if($item->type === 'photo')
          <img src="{{ $item->url }}" alt="{{ $item->caption ?? '' }}" loading="lazy">
        @else
          <video src="{{ $item->url }}" preload="metadata" style="cursor:pointer;" onclick="this.paused ? this.play() : this.pause()"></video>
          <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;font-size:1.5rem;text-shadow:0 2px 8px rgba(0,0,0,.4);">▶️</div>
          <button onclick="openTrimmer('{{ $item->url }}', {{ $item->id }}, {{ $item->duration ?? 0 }}, {{ $item->student_id }})" title="Trim video"
            style="position:absolute;top:6px;left:6px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:24px;height:24px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">✂</button>
        @endif
      </div>
      @if($item->type === 'photo')
        <span class="type-icon" title="Photo">📷</span>
        <button onclick="openEditor('{{ $item->url }}', {{ $item->id }}, {{ $item->student_id }})" title="Edit photo" class="edit-btn">✎</button>
      @else
        <span class="type-icon" title="Video">🎬</span>
        <button onclick="openTrimmer('{{ $item->url }}', {{ $item->id }}, {{ $item->duration ?? 0 }}, {{ $item->student_id }})" title="Trim video" class="edit-btn">✂</button>
      @endif
      @if($item->student)
        <a href="{{ route('admin.students.profile', $item->student) }}" class="student-tag" style="text-decoration:none;">{{ $item->student->first_name }}</a>
      @endif
      <form method="POST" action="{{ route('admin.students.delete-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Delete this {{ $item->type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="delete-btn" title="Delete">✕</button>
      </form>
      <form method="POST" action="{{ route('admin.students.reassign-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Move to ' + this.student_id.options[this.student_id.selectedIndex].text + '?')">
        @csrf @method('PATCH')
        <select name="student_id" onchange="if(this.value)this.form.submit()"
          style="position:absolute;bottom:6px;right:6px;font-size:.6rem;padding:2px 3px;border-radius:3px;background:rgba(0,31,91,.85);color:#fff;border:none;cursor:pointer;max-width:90px;opacity:0;transition:opacity .15s;"
          onmouseenter="this.style.opacity=1" onmouseleave="if(!this.matches(':focus'))this.style.opacity=0"
          onfocus="this.style.opacity=1" onblur="this.style.opacity=0">
          <option value="">Move...</option>
          @foreach($students as $s)
            @if($item->student_id !== $s->id)
            <option value="{{ $s->id }}">{{ $s->full_name }}</option>
            @endif
          @endforeach
        </select>
      </form>
      <div class="media-card-body">
        <form method="POST" action="{{ route('admin.students.update-caption', $item) }}" style="margin-bottom:3px;">
          @csrf @method('PATCH')
          <input type="text" name="caption" value="{{ $item->caption }}" placeholder="Add caption..."
            style="width:100%;border:none;border-bottom:1px solid transparent;font-size:.75rem;color:#6b7280;padding:1px 0;background:transparent;outline:none;"
            onfocus="this.style.borderBottomColor='#001F5B'" onblur="this.style.borderBottomColor='transparent';if(this.defaultValue!==this.value)this.form.submit()">
        </form>
        <div class="media-card-meta">
          {{ $item->student->first_name ?? '?' }}
          @if($item->width) · {{ $item->width }}×{{ $item->height }}@endif
          @if($item->duration) · {{ gmdate($item->duration >= 3600 ? 'H:i:s' : 'i:s', (int)$item->duration) }}@endif
          · {{ number_format(($item->file_size ?? 0) / 1048576, 1) }}MB
          <br>{{ $item->created_at->format('M j, Y') }}
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div style="margin-top:1rem;">{{ $media->links() }}</div>
@endif
{{-- ═══ VIDEO TRIMMER MODAL ═══ --}}
<div id="trimmerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:200;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeTrimmer()">
  <div style="background:#fff;border-radius:12px;max-width:800px;width:100%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;">Trim Video</h3>
      <button onclick="closeTrimmer()" style="background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    </div>
    <div style="background:#000;position:relative;">
      <video id="trimVideo" style="width:100%;max-height:400px;display:block;" preload="auto" crossorigin="anonymous"></video>
      <div id="trimPlayBtn" onclick="toggleTrimPlay()" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:3rem;cursor:pointer;text-shadow:0 2px 12px rgba(0,0,0,.5);">▶️</div>
    </div>
    <div style="padding:1rem 1.25rem;background:#f8fafc;">
      <div style="position:relative;height:40px;background:#e5eaf2;border-radius:6px;overflow:hidden;cursor:pointer;" id="timeline" onclick="seekTimeline(event)">
        <div id="trimRange" style="position:absolute;top:0;bottom:0;background:rgba(0,31,91,.2);border-left:3px solid var(--navy);border-right:3px solid var(--navy);"></div>
        <div id="playhead" style="position:absolute;top:0;bottom:0;width:2px;background:var(--red);z-index:2;"></div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:.75rem;gap:1rem;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;gap:.75rem;align-items:center;">
          <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Start</label>
            <input type="number" id="trimStart" value="0" min="0" step="0.1"
              style="width:80px;border:1.5px solid #dbe4ff;border-radius:5px;padding:4px 8px;font-size:.88rem;font-weight:600;" onchange="updateTrimRange()">
          </div>
          <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#6b7280;text-transform:uppercase;">End</label>
            <input type="number" id="trimEnd" value="0" min="0" step="0.1"
              style="width:80px;border:1.5px solid #dbe4ff;border-radius:5px;padding:4px 8px;font-size:.88rem;font-weight:600;" onchange="updateTrimRange()">
          </div>
          <div style="padding-top:14px;"><span style="font-size:.82rem;color:#6b7280;">Duration: <strong id="trimDuration">0.0</strong>s</span></div>
        </div>
        <div style="display:flex;gap:.4rem;">
          <button onclick="setTrimStart()" class="btn-ghost" style="font-size:.78rem;padding:4px 10px;">[ In</button>
          <button onclick="setTrimEnd()" class="btn-ghost" style="font-size:.78rem;padding:4px 10px;">Out ]</button>
          <button onclick="previewTrim()" class="btn-ghost" style="font-size:.78rem;padding:4px 10px;">▶ Preview</button>
        </div>
      </div>
    </div>
    {{-- Video adjustments --}}
    <div style="padding:.5rem 1.25rem;background:#fff;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Bright</label>
        <input type="range" id="vidBrightness" min="50" max="150" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyVideoAdjustments()">
        <span id="vidValB" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Contrast</label>
        <input type="range" id="vidContrast" min="50" max="150" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyVideoAdjustments()">
        <span id="vidValC" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Saturate</label>
        <input type="range" id="vidSaturation" min="0" max="200" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyVideoAdjustments()">
        <span id="vidValS" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
      <button onclick="resetVideoAdjustments()" class="btn-ghost" style="font-size:.72rem;padding:4px 10px;">Reset</button>
    </div>
    <div style="padding:.75rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <div id="trimStatus" style="font-size:.82rem;color:#6b7280;"></div>
      <div style="display:flex;gap:.4rem;">
        <button onclick="closeTrimmer()" class="btn-ghost" style="font-size:.82rem;padding:5px 12px;">Cancel</button>
        <button onclick="saveTrim()" class="btn-navy" id="saveTrimBtn" style="padding:5px 16px;">Save Trim</button>
      </div>
    </div>
  </div>
</div>

<script>
var trimMediaId = null;
var trimStudentId = null;
var trimTotalDuration = 0;
var trimVideoEl = null;
var trimAnimFrame = null;

function applyVideoAdjustments() {
  var b = document.getElementById('vidBrightness').value;
  var c = document.getElementById('vidContrast').value;
  var s = document.getElementById('vidSaturation').value;
  document.getElementById('vidValB').textContent = b;
  document.getElementById('vidValC').textContent = c;
  document.getElementById('vidValS').textContent = s;
  if (trimVideoEl) trimVideoEl.style.filter = 'brightness(' + (b/100) + ') contrast(' + (c/100) + ') saturate(' + (s/100) + ')';
}
function resetVideoAdjustments() {
  document.getElementById('vidBrightness').value = 100;
  document.getElementById('vidContrast').value = 100;
  document.getElementById('vidSaturation').value = 100;
  applyVideoAdjustments();
}

function openTrimmer(url, mediaId, duration, studentId) {
  trimMediaId = mediaId;
  trimStudentId = studentId;
  trimTotalDuration = duration || 30;
  trimVideoEl = document.getElementById('trimVideo');
  trimVideoEl.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
  document.getElementById('trimmerModal').style.display = 'flex';
  document.getElementById('saveTrimBtn').disabled = false;
  document.getElementById('saveTrimBtn').textContent = 'Save Trim';
  document.getElementById('saveTrimBtn').style.opacity = '1';
  document.getElementById('trimStatus').textContent = '';
  resetVideoAdjustments();

  trimVideoEl.onloadedmetadata = function() {
    trimTotalDuration = trimVideoEl.duration;
    document.getElementById('trimStart').value = '0';
    document.getElementById('trimStart').max = trimTotalDuration;
    document.getElementById('trimEnd').value = trimTotalDuration.toFixed(1);
    document.getElementById('trimEnd').max = trimTotalDuration;
    updateTrimRange();
    startPlayheadUpdate();
  };
}
function closeTrimmer() {
  document.getElementById('trimmerModal').style.display = 'none';
  if (trimVideoEl) { trimVideoEl.pause(); trimVideoEl.src = ''; }
  if (trimAnimFrame) cancelAnimationFrame(trimAnimFrame);
}
function toggleTrimPlay() {
  if (!trimVideoEl) return;
  if (trimVideoEl.paused) { trimVideoEl.play(); document.getElementById('trimPlayBtn').style.display = 'none'; }
  else { trimVideoEl.pause(); document.getElementById('trimPlayBtn').style.display = 'block'; }
}
function startPlayheadUpdate() {
  (function update() {
    if (trimVideoEl && trimTotalDuration > 0) document.getElementById('playhead').style.left = ((trimVideoEl.currentTime / trimTotalDuration) * 100) + '%';
    trimAnimFrame = requestAnimationFrame(update);
  })();
  trimVideoEl.onclick = toggleTrimPlay;
  trimVideoEl.onpause = function() { document.getElementById('trimPlayBtn').style.display = 'block'; };
  trimVideoEl.onplay = function() { document.getElementById('trimPlayBtn').style.display = 'none'; };
}
function seekTimeline(e) {
  if (!trimVideoEl) return;
  var rect = document.getElementById('timeline').getBoundingClientRect();
  trimVideoEl.currentTime = ((e.clientX - rect.left) / rect.width) * trimTotalDuration;
}
function updateTrimRange() {
  var s = parseFloat(document.getElementById('trimStart').value) || 0;
  var e = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;
  document.getElementById('trimRange').style.left = ((s / trimTotalDuration) * 100) + '%';
  document.getElementById('trimRange').style.right = (((trimTotalDuration - e) / trimTotalDuration) * 100) + '%';
  document.getElementById('trimDuration').textContent = (e - s).toFixed(1);
}
function setTrimStart() { document.getElementById('trimStart').value = trimVideoEl.currentTime.toFixed(1); updateTrimRange(); }
function setTrimEnd() { document.getElementById('trimEnd').value = trimVideoEl.currentTime.toFixed(1); updateTrimRange(); }
function previewTrim() {
  var s = parseFloat(document.getElementById('trimStart').value) || 0;
  var e = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;
  trimVideoEl.currentTime = s; trimVideoEl.play();
  var check = function() { if (trimVideoEl.currentTime >= e) { trimVideoEl.pause(); trimVideoEl.removeEventListener('timeupdate', check); } };
  trimVideoEl.addEventListener('timeupdate', check);
}
async function saveTrim() {
  var s = parseFloat(document.getElementById('trimStart').value) || 0;
  var e = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;
  if (e - s < 0.5) { document.getElementById('trimStatus').textContent = 'Clip must be at least 0.5s'; document.getElementById('trimStatus').style.color = '#dc2626'; return; }
  var btn = document.getElementById('saveTrimBtn');
  btn.disabled = true; btn.textContent = 'Trimming...'; btn.style.opacity = '.5';
  document.getElementById('trimStatus').textContent = 'Processing on server...';
  document.getElementById('trimStatus').style.color = '#6b7280';
  try {
    var resp = await fetch('{{ route("admin.media.trim-video") }}', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({
        media_id: trimMediaId, start_time: s, end_time: e,
        brightness: parseInt(document.getElementById('vidBrightness').value),
        contrast: parseInt(document.getElementById('vidContrast').value),
        saturation: parseInt(document.getElementById('vidSaturation').value),
      })
    });
    var result = await resp.json();
    if (result.success) {
      document.getElementById('trimStatus').textContent = 'Trimmed! ' + parseFloat(result.duration).toFixed(1) + 's. Reloading...';
      document.getElementById('trimStatus').style.color = '#065f46';
      setTimeout(function() { window.location.reload(); }, 1500);
    } else { throw new Error(result.error || 'Trim failed'); }
  } catch(err) {
    document.getElementById('trimStatus').textContent = 'Failed: ' + err.message;
    document.getElementById('trimStatus').style.color = '#dc2626';
    btn.disabled = false; btn.textContent = 'Save Trim'; btn.style.opacity = '1';
  }
}
</script>

{{-- ═══ IMAGE EDITOR MODAL ═══ --}}
<div id="editorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:200;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeEditor()">
  <div style="background:#fff;border-radius:12px;max-width:800px;width:100%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;">Edit Photo</h3>
      <button onclick="closeEditor()" style="background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    </div>
    <div style="flex:1;overflow:hidden;background:#1a1a2e;display:flex;align-items:center;justify-content:center;min-height:300px;">
      <img id="editorImage" crossorigin="anonymous" style="max-width:100%;display:block;">
    </div>
    {{-- Tools --}}
    <div style="padding:.6rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;">
      <button onclick="editorAction('rotateCW')" class="btn-ghost" style="font-size:.82rem;padding:5px 10px;">↻ 90°</button>
      <button onclick="editorAction('rotateCCW')" class="btn-ghost" style="font-size:.82rem;padding:5px 10px;">↺ 90°</button>
      <button onclick="editorAction('flipH')" class="btn-ghost" style="font-size:.82rem;padding:5px 10px;">↔ Flip</button>
      <button onclick="editorAction('flipV')" class="btn-ghost" style="font-size:.82rem;padding:5px 10px;">↕ Flip</button>
      <button onclick="editorAction('reset');resetAdjustments()" class="btn-ghost" style="font-size:.82rem;padding:5px 10px;">Reset</button>
      <span style="color:#e5eaf2;margin:0 2px;">|</span>
      <span style="font-size:.72rem;color:#6b7280;">Aspect:</span>
      <button onclick="setAspect(NaN)" class="btn-ghost" style="font-size:.72rem;padding:4px 8px;">Free</button>
      <button onclick="setAspect(1)" class="btn-ghost" style="font-size:.72rem;padding:4px 8px;">1:1</button>
      <button onclick="setAspect(4/3)" class="btn-ghost" style="font-size:.72rem;padding:4px 8px;">4:3</button>
      <button onclick="setAspect(16/9)" class="btn-ghost" style="font-size:.72rem;padding:4px 8px;">16:9</button>
    </div>
    {{-- Adjustments --}}
    <div style="padding:.5rem 1.25rem;background:#f8fafc;display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Bright</label>
        <input type="range" id="adjustBrightness" min="50" max="150" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyAdjustments()">
        <span id="valBrightness" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Contrast</label>
        <input type="range" id="adjustContrast" min="50" max="150" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyAdjustments()">
        <span id="valContrast" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
      <div style="display:flex;align-items:center;gap:.4rem;flex:1;min-width:140px;">
        <label style="font-size:.68rem;font-weight:700;color:#6b7280;width:55px;">Saturate</label>
        <input type="range" id="adjustSaturation" min="0" max="200" value="100" style="flex:1;accent-color:#001F5B;" oninput="applyAdjustments()">
        <span id="valSaturation" style="font-size:.7rem;color:#9ca3af;width:30px;">100</span>
      </div>
    </div>
    {{-- Actions --}}
    <div style="padding:.6rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;justify-content:flex-end;gap:.4rem;">
      <button onclick="closeEditor()" class="btn-ghost" style="font-size:.82rem;padding:5px 12px;">Cancel</button>
      <button onclick="saveEdit()" class="btn-navy" id="saveEditBtn" style="padding:5px 16px;">Save</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
var cropper = null;
var editingMediaId = null;
var editingStudentId = null;

function openEditor(url, mediaId, studentId) {
  editingMediaId = mediaId;
  editingStudentId = studentId;
  var img = document.getElementById('editorImage');
  img.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
  document.getElementById('editorModal').style.display = 'flex';
  resetAdjustments();
  document.getElementById('saveEditBtn').disabled = false;
  document.getElementById('saveEditBtn').textContent = 'Save';
  document.getElementById('saveEditBtn').style.opacity = '1';

  img.onload = function() {
    if (cropper) cropper.destroy();
    cropper = new Cropper(img, { viewMode: 1, autoCropArea: 1, responsive: true, background: true });
  };
  img.onerror = function() {
    alert('Could not load image. Try hard-refreshing (Ctrl+Shift+R).');
    closeEditor();
  };
}

function closeEditor() {
  document.getElementById('editorModal').style.display = 'none';
  if (cropper) { cropper.destroy(); cropper = null; }
}

function editorAction(action) {
  if (!cropper) return;
  switch(action) {
    case 'rotateCW': cropper.rotate(90); break;
    case 'rotateCCW': cropper.rotate(-90); break;
    case 'flipH': cropper.scaleX(cropper.getData().scaleX === -1 ? 1 : -1); break;
    case 'flipV': cropper.scaleY(cropper.getData().scaleY === -1 ? 1 : -1); break;
    case 'reset': cropper.reset(); break;
  }
}

function setAspect(ratio) { if (cropper) cropper.setAspectRatio(ratio); }

function applyAdjustments() {
  var b = document.getElementById('adjustBrightness').value;
  var c = document.getElementById('adjustContrast').value;
  var s = document.getElementById('adjustSaturation').value;
  document.getElementById('valBrightness').textContent = b;
  document.getElementById('valContrast').textContent = c;
  document.getElementById('valSaturation').textContent = s;
  var f = 'brightness(' + (b/100) + ') contrast(' + (c/100) + ') saturate(' + (s/100) + ')';
  document.querySelectorAll('.cropper-canvas img, .cropper-view-box img').forEach(function(el) { el.style.filter = f; });
}

function resetAdjustments() {
  document.getElementById('adjustBrightness').value = 100;
  document.getElementById('adjustContrast').value = 100;
  document.getElementById('adjustSaturation').value = 100;
  applyAdjustments();
}

async function saveEdit() {
  if (!cropper || !editingMediaId) return;
  var btn = document.getElementById('saveEditBtn');
  btn.disabled = true; btn.textContent = 'Saving...'; btn.style.opacity = '.5';
  var csrfToken = '{{ csrf_token() }}';

  try {
    var srcCanvas = cropper.getCroppedCanvas({ maxWidth: 4096, maxHeight: 4096 });
    var b = document.getElementById('adjustBrightness').value;
    var c = document.getElementById('adjustContrast').value;
    var s = document.getElementById('adjustSaturation').value;
    var canvas = srcCanvas;
    if (b != 100 || c != 100 || s != 100) {
      canvas = document.createElement('canvas');
      canvas.width = srcCanvas.width; canvas.height = srcCanvas.height;
      var ctx = canvas.getContext('2d');
      ctx.filter = 'brightness(' + (b/100) + ') contrast(' + (c/100) + ') saturate(' + (s/100) + ')';
      ctx.drawImage(srcCanvas, 0, 0);
    }
    var blob = await new Promise(function(res) { canvas.toBlob(res, 'image/jpeg', 0.92); });

    var resp = await fetch('{{ route("admin.media.presigned") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ student_id: editingStudentId, filename: 'edited_' + editingMediaId + '.jpg', mime_type: 'image/jpeg', file_size: blob.size })
    });
    var presign = await resp.json();

    await fetch(presign.upload_url, { method: 'PUT', headers: { 'Content-Type': 'image/jpeg' }, body: blob });

    await fetch('{{ route("admin.media.register") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ student_id: editingStudentId, s3_path: presign.s3_path, type: 'photo', filename: 'edited_' + editingMediaId + '.jpg', mime_type: 'image/jpeg', file_size: blob.size, width: canvas.width, height: canvas.height, replace_media_id: editingMediaId, caption: null })
    });

    closeEditor();
    window.location.reload();
  } catch(e) {
    btn.textContent = 'Failed — try again';
    btn.style.opacity = '1'; btn.disabled = false;
  }
}
</script>
@endsection
