@extends('layouts.admin')
@section('title', $student->full_name . ' — Student Profile')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .profile-header{display:flex;gap:1.5rem;align-items:flex-start;margin-bottom:2rem;flex-wrap:wrap;}
  .profile-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid var(--navy);flex-shrink:0;}
  .profile-photo-placeholder{width:120px;height:120px;border-radius:50%;background:#e5eaf2;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#9ca3af;border:3px solid #e5eaf2;flex-shrink:0;}
  .stat-pill{display:inline-block;background:#f0f9ff;border:1px solid #bae6fd;border-radius:20px;padding:4px 14px;font-size:.78rem;font-weight:600;color:#0c4a6e;margin-right:6px;margin-bottom:4px;}
  .skill-badge{padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:700;}
  .skill-beginner{background:#dcfce7;color:#166534;}
  .skill-intermediate{background:#dbeafe;color:#1e40af;}
  .skill-advanced{background:#fef3c7;color:#92400e;}
  .media-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;}
  @media(max-width:1024px){.media-grid{grid-template-columns:repeat(3,1fr);}}
  @media(max-width:768px){.media-grid{grid-template-columns:repeat(2,1fr);}}
  .media-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:8px;overflow:hidden;position:relative;}
  .media-card .media-frame{width:100%;aspect-ratio:1/1;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f1f5f9;}
  .media-card .media-frame img,.media-card .media-frame video{width:100%;height:100%;object-fit:contain;display:block;}
  .media-card-body{padding:.5rem .65rem;}
  .media-card-caption{font-size:.75rem;color:#6b7280;margin-bottom:.3rem;}
  .media-card-meta{font-size:.65rem;color:#9ca3af;}
  .media-card .type-icon{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.5);color:#fff;font-size:.8rem;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
  .media-card .edit-btn-card{position:absolute;top:6px;left:38px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .media-card .profile-star{position:absolute;top:6px;left:70px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .media-card .profile-star.active{background:var(--red);}
  .media-card .revert-btn{position:absolute;top:6px;left:102px;background:rgba(200,16,46,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .media-card .delete-btn{position:absolute;top:6px;right:6px;background:rgba(220,38,38,.8);color:#fff;border:none;border-radius:50%;width:24px;height:24px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .upload-zone{border:2.5px dashed #bfdbfe;border-radius:10px;background:#eff6ff;text-align:center;padding:2rem;cursor:pointer;transition:all .2s;}
  .upload-zone:hover{border-color:var(--navy);background:#dbeafe;}
  .btn-sm{padding:4px 12px;border-radius:6px;font-size:.78rem;font-weight:600;border:none;cursor:pointer;}
  .btn-navy{background:var(--navy);color:#fff;}.btn-navy:hover{background:var(--red);}
  .btn-ghost{background:#f3f4f6;color:#374151;}.btn-ghost:hover{background:#e5e7eb;}
</style>

{{-- Header --}}
<div class="profile-header">
  @if($student->profile_photo_url)
    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->first_name }}" class="profile-photo">
  @else
    <div class="profile-photo-placeholder">{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>
  @endif
  <div style="flex:1;">
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem;">
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">{{ $student->full_name }}</h1>
      @if($student->skill_level)
        <span class="skill-badge skill-{{ $student->skill_level }}">{{ ucfirst($student->skill_level) }}</span>
      @endif
      @if($student->is_active)
        <span style="color:#065f46;font-size:.75rem;font-weight:700;">Active</span>
      @else
        <span style="color:#9ca3af;font-size:.75rem;">Inactive</span>
      @endif
    </div>
    @if($student->client)
      <div style="font-size:.88rem;color:#6b7280;margin-bottom:.4rem;">
        Parent: <a href="{{ route('admin.clients.show', $student->client) }}" style="color:var(--navy);font-weight:600;text-decoration:none;">{{ $student->client->full_name }}</a>
      </div>
    @endif
    <div style="margin-bottom:.5rem;">
      @if($student->age)<span class="stat-pill">Age {{ $student->age }}</span>@endif
      <span class="stat-pill">{{ $photoCount }} photos</span>
      <span class="stat-pill">{{ $videoCount }} videos</span>
      <span class="stat-pill">{{ $lessonCount }} lessons</span>
    </div>
    @if($student->notes)
      <div style="font-size:.85rem;color:#6b7280;">{{ $student->notes }}</div>
    @endif
    <div style="margin-top:.75rem;display:flex;gap:.5rem;">
      <a href="{{ route('admin.students.index') }}" class="btn-sm btn-ghost" style="text-decoration:none;">← All Students</a>
    </div>
  </div>
</div>

{{-- Upload --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1.5rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--navy);margin:0 0 .75rem;">Upload Photos & Videos</h2>
  <form method="POST" action="{{ route('admin.students.upload', $student) }}" enctype="multipart/form-data" id="uploadForm" onsubmit="showUploadProgress()">
    @csrf
    <div class="upload-zone" onclick="document.getElementById('file-input').click()">
      <input type="file" id="file-input" name="files[]" multiple accept="image/*,video/*,.zip" style="display:none;"
        onchange="document.getElementById('file-count').textContent = this.files.length + ' file(s) selected — ' + formatBytes(totalSize(this.files))"">
      <div style="font-size:2rem;margin-bottom:.5rem;">📸</div>
      <p style="font-weight:600;color:#1e40af;">Drop files here or tap to browse</p>
      <p style="font-size:.82rem;color:#6b7280;margin-top:.25rem;">JPG, PNG, WebP, HEIC, MP4, MOV, or ZIP — up to 500MB</p>
      <p id="file-count" style="font-size:.82rem;color:var(--navy);font-weight:600;margin-top:.5rem;"></p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:flex-end;margin-top:.75rem;">
      <div style="flex:1;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#6b7280;margin-bottom:3px;">Caption (optional)</label>
        <input type="text" name="caption" placeholder="e.g. Practice session at Creve Coeur"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.85rem;">
      </div>
      <button type="submit" class="btn-sm btn-navy" id="uploadBtn" style="padding:8px 20px;">Upload</button>
    </div>
    <div id="uploadProgress" style="display:none;margin-top:.75rem;">
      <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="flex:1;background:#e5eaf2;border-radius:4px;height:8px;overflow:hidden;">
          <div id="progressBar" style="width:0%;height:100%;background:var(--navy);border-radius:4px;transition:width .3s;"></div>
        </div>
        <span id="progressText" style="font-size:.78rem;color:#6b7280;white-space:nowrap;">Uploading...</span>
      </div>
    </div>
  </form>
</div>

<script>
function totalSize(files) {
  let s = 0; for (let f of files) s += f.size; return s;
}
function formatBytes(b) {
  if (b < 1024) return b + ' B';
  if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
  return (b / 1048576).toFixed(1) + ' MB';
}
async function showUploadProgress() {
  event.preventDefault();
  var btn = document.getElementById('uploadBtn');
  var prog = document.getElementById('uploadProgress');
  var bar = document.getElementById('progressBar');
  var text = document.getElementById('progressText');
  var files = document.getElementById('file-input').files;
  var caption = document.querySelector('#uploadForm input[name="caption"]').value;
  if (!files.length) return;

  btn.disabled = true; btn.textContent = 'Uploading...'; btn.style.opacity = '.5';
  prog.style.display = 'block';

  var total = files.length;
  var done = 0;
  var errors = 0;
  var csrfToken = document.querySelector('input[name="_token"]').value;

  for (var i = 0; i < files.length; i++) {
    var file = files[i];
    var isZip = file.name.toLowerCase().endsWith('.zip');
    text.textContent = 'File ' + (i + 1) + '/' + total + ': ' + file.name + ' (' + formatBytes(file.size) + ')';
    bar.style.width = Math.round((i / total) * 100) + '%';

    // All files go direct to S3 (bypasses Cloudflare 100MB limit)
    try {
      // 1. Get presigned URL
      var presignResp = await fetch('{{ route("admin.media.presigned") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
          student_id: {{ $student->id }},
          filename: file.name,
          mime_type: file.type || 'application/octet-stream',
          file_size: file.size,
        })
      });
      if (!presignResp.ok) { errors++; continue; }
      var presign = await presignResp.json();

      // 2. Upload directly to S3
      await new Promise(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open('PUT', presign.upload_url, true);
        xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');
        xhr.upload.onprogress = function(e) {
          if (e.lengthComputable) {
            var filePct = Math.round((e.loaded / e.total) * 100);
            var totalPct = Math.round(((i + e.loaded / e.total) / total) * 100);
            bar.style.width = totalPct + '%';
            text.textContent = 'File ' + (i + 1) + '/' + total + ': ' + file.name + ' — ' + filePct + '%';
          }
        };
        xhr.onload = function() { xhr.status < 300 ? resolve() : reject(); };
        xhr.onerror = reject;
        xhr.send(file);
      });

      // 3. Get dimensions from browser for images
      var w = null, h = null, dur = null;
      if (file.type.startsWith('image/')) {
        var dims = await new Promise(function(res) {
          var img = new Image();
          img.onload = function() { res({ w: img.naturalWidth, h: img.naturalHeight }); };
          img.onerror = function() { res(null); };
          img.src = URL.createObjectURL(file);
        });
        if (dims) { w = dims.w; h = dims.h; }
      } else if (file.type.startsWith('video/')) {
        var vdims = await new Promise(function(res) {
          var vid = document.createElement('video');
          vid.preload = 'metadata';
          vid.onloadedmetadata = function() { res({ w: vid.videoWidth, h: vid.videoHeight, d: vid.duration }); };
          vid.onerror = function() { res(null); };
          vid.src = URL.createObjectURL(file);
        });
        if (vdims) { w = vdims.w; h = vdims.h; dur = vdims.d; }
      }

      // 4. Register in DB
      await fetch('{{ route("admin.media.register") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
          student_id: {{ $student->id }},
          s3_path: presign.s3_path,
          type: presign.type,
          filename: file.name,
          mime_type: file.type || 'application/octet-stream',
          file_size: file.size,
          width: w, height: h, duration: dur,
          caption: caption,
        })
      });
      done++;
    } catch(e) { errors++; }
  }

  bar.style.width = '100%';
  text.textContent = done + ' file(s) uploaded' + (errors ? ', ' + errors + ' failed' : '') + '. Reloading...';
  text.style.color = errors ? '#f59e0b' : '#065f46';
  setTimeout(function() { window.location.reload(); }, 1000);
}
</script>

{{-- Media Grid --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--navy);margin:0;">Media ({{ $photoCount + $videoCount }})</h2>
</div>

@if($media->isEmpty())
  <div style="background:#fff;border:1.5px dashed #e5eaf2;border-radius:10px;padding:3rem;text-align:center;color:#9ca3af;">
    No photos or videos yet. Upload some above!
  </div>
@else
  <div class="media-grid">
    @foreach($media as $item)
    <div class="media-card">
      <div class="media-frame">
        @if($item->type === 'photo')
          <img src="{{ $item->url }}" alt="{{ $item->caption ?? $student->first_name }}" loading="lazy">
        @else
          <video src="{{ $item->url }}" preload="metadata" style="cursor:pointer;" onclick="this.paused ? this.play() : this.pause()"></video>
          <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;font-size:2rem;text-shadow:0 2px 8px rgba(0,0,0,.4);">▶️</div>
        @endif
      </div>

      {{-- Type icon + Edit/Trim --}}
      @if($item->type === 'photo')
        <span class="type-icon" title="Photo">📷</span>
        <button onclick="openEditor('{{ $item->url }}', {{ $item->id }})" title="Edit photo" class="edit-btn-card">✎</button>
        <form method="POST" action="{{ route('admin.students.set-profile-photo', [$student, $item]) }}" style="display:inline;">
          @csrf
          <button type="submit" class="profile-star {{ $student->profile_photo_id === $item->id ? 'active' : '' }}" title="Set as profile photo">
            {{ $student->profile_photo_id === $item->id ? '★' : '☆' }}
          </button>
        </form>
        @if($item->is_edited)
        <form method="POST" action="{{ route('admin.students.revert-media', $item) }}" style="display:inline;"
          onsubmit="return confirm('Revert to original photo?')">
          @csrf
          <button type="submit" title="Revert to original" class="revert-btn">↩</button>
        </form>
        @endif
      @else
        <span class="type-icon" title="Video">🎬</span>
        <button onclick="openTrimmer('{{ $item->url }}', {{ $item->id }}, {{ $item->duration ?? 0 }})" title="Trim video" class="edit-btn-card">✂</button>
        @if($item->is_edited)
        <form method="POST" action="{{ route('admin.students.revert-media', $item) }}" style="display:inline;"
          onsubmit="return confirm('Revert to original video?')">
          @csrf
          <button type="submit" title="Revert to original" class="profile-star" style="background:rgba(200,16,46,.8);">↩</button>
        </form>
        @endif
      @endif

      {{-- Delete --}}
      <form method="POST" action="{{ route('admin.students.delete-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Delete this {{ $item->type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="delete-btn" title="Delete">✕</button>
      </form>

      {{-- Reassign --}}
      <form method="POST" action="{{ route('admin.students.reassign-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Move this file to ' + this.student_id.options[this.student_id.selectedIndex].text + '?')">
        @csrf @method('PATCH')
        <select name="student_id" onchange="if(this.value)this.form.submit()"
          style="position:absolute;bottom:6px;left:6px;font-size:.65rem;padding:2px 4px;border-radius:4px;background:rgba(0,31,91,.85);color:#fff;border:none;cursor:pointer;max-width:100px;">
          <option value="">Move to...</option>
          @foreach($allStudents as $s)
            <option value="{{ $s->id }}">{{ $s->full_name }}</option>
          @endforeach
        </select>
      </form>

      <div class="media-card-body">
        <form method="POST" action="{{ route('admin.students.update-caption', $item) }}" style="display:flex;gap:3px;align-items:center;">
          @csrf @method('PATCH')
          <input type="text" name="caption" value="{{ $item->caption }}" placeholder="Add caption..."
            style="flex:1;border:none;border-bottom:1px solid transparent;font-size:.75rem;color:#6b7280;padding:2px 0;background:transparent;outline:none;"
            onfocus="this.style.borderBottomColor='#001F5B'" onblur="this.style.borderBottomColor='transparent';if(this.defaultValue!==this.value)this.form.submit()">
        </form>
        <div class="media-card-meta">
          {{ $item->created_at->format('M j, Y') }}
          @if($item->width && $item->height) · {{ $item->width }}×{{ $item->height }}@endif
          @if($item->type === 'video' && $item->duration) · {{ gmdate($item->duration >= 3600 ? 'H:i:s' : 'i:s', (int)$item->duration) }}@endif
          · {{ number_format(($item->file_size ?? 0) / 1048576, 1) }}MB
          @if($item->versions->count() > 1)
            · <button onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'" style="background:none;border:none;color:#001F5B;font-size:.65rem;font-weight:700;cursor:pointer;padding:0;">{{ $item->versions->count() }} versions ▾</button>
            <div style="display:none;margin-top:4px;border-top:1px solid #f3f4f6;padding-top:4px;">
              @foreach($item->versions as $ver)
              <div style="display:flex;justify-content:space-between;align-items:center;padding:2px 0;font-size:.62rem;">
                <span>
                  <strong>v{{ $ver->version }}</strong> · {{ $ver->edit_type }}
                  @if($ver->file_size) · {{ number_format($ver->file_size / 1048576, 1) }}MB @endif
                  @if($ver->duration) · {{ number_format($ver->duration, 1) }}s @endif
                  · {{ $ver->created_at->format('M j g:ia') }}
                </span>
                @if($ver->path !== $item->path)
                <form method="POST" action="{{ route('admin.students.revert-media', $item) }}" style="display:inline;"
                  onsubmit="return confirm('Restore version {{ $ver->version }}?')">
                  @csrf
                  <input type="hidden" name="version_id" value="{{ $ver->id }}">
                  <button type="submit" style="background:#dbeafe;color:#1e40af;border:none;border-radius:3px;font-size:.6rem;padding:1px 5px;cursor:pointer;font-weight:600;">Restore</button>
                </form>
                @else
                <span style="color:#065f46;font-weight:700;">Current</span>
                @endif
              </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div style="margin-top:1rem;">{{ $media->links() }}</div>
@endif
{{-- ═══ IMAGE EDITOR MODAL ═══ --}}
<div id="editorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:200;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeEditor()">
  <div style="background:#fff;border-radius:12px;max-width:800px;width:100%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;">Edit Photo</h3>
      <button onclick="closeEditor()" style="background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    </div>
    <div style="flex:1;overflow:hidden;background:#1a1a2e;display:flex;align-items:center;justify-content:center;min-height:300px;position:relative;">
      <div id="editorLoading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;z-index:5;">
        <div style="font-size:1.5rem;margin-bottom:.5rem;">Loading photo...</div>
        <div style="width:200px;height:6px;background:rgba(255,255,255,.2);border-radius:3px;overflow:hidden;">
          <div id="editorLoadBar" style="width:0%;height:100%;background:#fff;border-radius:3px;transition:width .3s;"></div>
        </div>
      </div>
      <img id="editorImage" crossorigin="anonymous" style="max-width:100%;display:block;opacity:0;">
    </div>
    {{-- Tools row --}}
    <div style="padding:.6rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;">
      <button onclick="editorAction('rotateCW')" class="btn-sm btn-ghost">↻ 90°</button>
      <button onclick="editorAction('rotateCCW')" class="btn-sm btn-ghost">↺ 90°</button>
      <button onclick="editorAction('flipH')" class="btn-sm btn-ghost">↔ Flip</button>
      <button onclick="editorAction('flipV')" class="btn-sm btn-ghost">↕ Flip</button>
      <button onclick="editorAction('reset');resetAdjustments()" class="btn-sm btn-ghost">Reset</button>
      <span style="color:#e5eaf2;margin:0 2px;">|</span>
      <span style="font-size:.72rem;color:#6b7280;">Aspect:</span>
      <button onclick="setAspect(NaN)" class="btn-sm btn-ghost" style="font-size:.72rem;">Free</button>
      <button onclick="setAspect(1)" class="btn-sm btn-ghost" style="font-size:.72rem;">1:1</button>
      <button onclick="setAspect(4/3)" class="btn-sm btn-ghost" style="font-size:.72rem;">4:3</button>
      <button onclick="setAspect(16/9)" class="btn-sm btn-ghost" style="font-size:.72rem;">16:9</button>
    </div>
    {{-- Adjustments row --}}
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
    {{-- Actions row --}}
    <div style="padding:.6rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;justify-content:flex-end;gap:.4rem;">
      <button onclick="closeEditor()" class="btn-sm btn-ghost">Cancel</button>
      <button onclick="saveEdit()" class="btn-sm btn-navy" id="saveEditBtn">Save</button>
    </div>
  </div>
</div>

{{-- ═══ VIDEO TRIMMER MODAL ═══ --}}
<div id="trimmerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:200;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeTrimmer()">
  <div style="background:#fff;border-radius:12px;max-width:800px;width:100%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;">Trim Video</h3>
      <button onclick="closeTrimmer()" style="background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    </div>

    {{-- Video preview --}}
    <div style="background:#000;position:relative;min-height:200px;">
      <div id="trimLoading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;z-index:5;">
        <div style="font-size:1.2rem;margin-bottom:.5rem;">Loading video...</div>
        <div style="width:200px;height:6px;background:rgba(255,255,255,.2);border-radius:3px;overflow:hidden;">
          <div id="trimLoadBar" style="width:0%;height:100%;background:#fff;border-radius:3px;transition:width .3s;"></div>
        </div>
        <div id="trimLoadText" style="font-size:.78rem;margin-top:.4rem;color:rgba(255,255,255,.6);"></div>
      </div>
      <video id="trimVideo" style="width:100%;max-height:400px;display:block;" preload="auto" crossorigin="anonymous"></video>
      <div id="trimPlayBtn" onclick="toggleTrimPlay()" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:3rem;cursor:pointer;text-shadow:0 2px 12px rgba(0,0,0,.5);pointer-events:auto;display:none;">▶️</div>
    </div>

    {{-- Timeline / Range --}}
    <div style="padding:1rem 1.25rem;background:#f8fafc;">
      <div style="position:relative;height:40px;background:#e5eaf2;border-radius:6px;overflow:hidden;cursor:pointer;" id="timeline" onclick="seekTimeline(event)">
        <div id="trimRange" style="position:absolute;top:0;bottom:0;background:rgba(0,31,91,.2);border-left:3px solid var(--navy);border-right:3px solid var(--navy);"></div>
        <div id="playhead" style="position:absolute;top:0;bottom:0;width:2px;background:var(--red);z-index:2;"></div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:.75rem;gap:1rem;align-items:center;">
        <div style="display:flex;gap:.75rem;align-items:center;">
          <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#6b7280;text-transform:uppercase;">Start</label>
            <input type="number" id="trimStart" value="0" min="0" step="0.1"
              style="width:80px;border:1.5px solid #dbe4ff;border-radius:5px;padding:4px 8px;font-size:.88rem;font-weight:600;"
              onchange="updateTrimRange()">
          </div>
          <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#6b7280;text-transform:uppercase;">End</label>
            <input type="number" id="trimEnd" value="0" min="0" step="0.1"
              style="width:80px;border:1.5px solid #dbe4ff;border-radius:5px;padding:4px 8px;font-size:.88rem;font-weight:600;"
              onchange="updateTrimRange()">
          </div>
          <div style="padding-top:14px;">
            <span style="font-size:.82rem;color:#6b7280;">Duration: <strong id="trimDuration">0.0</strong>s</span>
          </div>
        </div>
        <div style="display:flex;gap:.4rem;">
          <button onclick="setTrimStart()" class="btn-sm btn-ghost" title="Set start to current position">[ In</button>
          <button onclick="setTrimEnd()" class="btn-sm btn-ghost" title="Set end to current position">Out ]</button>
          <button onclick="previewTrim()" class="btn-sm btn-ghost" title="Preview trimmed section">▶ Preview</button>
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
      <button onclick="resetVideoAdjustments()" class="btn-sm btn-ghost" style="font-size:.72rem;">Reset</button>
    </div>
    {{-- Actions --}}
    <div style="padding:.75rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <div id="trimStatus" style="font-size:.82rem;color:#6b7280;"></div>
      <div style="display:flex;gap:.4rem;">
        <button onclick="closeTrimmer()" class="btn-sm btn-ghost">Cancel</button>
        <button onclick="saveTrim()" class="btn-sm btn-navy" id="saveTrimBtn">Save Trim</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
var cropper = null;
var editingMediaId = null;

function openEditor(url, mediaId) {
  editingMediaId = mediaId;
  var img = document.getElementById('editorImage');
  var loader = document.getElementById('editorLoading');
  var loadBar = document.getElementById('editorLoadBar');

  img.style.opacity = '0';
  loader.style.display = 'flex';
  loadBar.style.width = '10%';
  document.getElementById('editorModal').style.display = 'flex';
  document.getElementById('saveEditBtn').disabled = false;
  document.getElementById('saveEditBtn').textContent = 'Save';
  document.getElementById('saveEditBtn').style.opacity = '1';

  var xhr = new XMLHttpRequest();
  xhr.open('GET', url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now(), true);
  xhr.responseType = 'blob';
  xhr.onprogress = function(e) {
    if (e.lengthComputable) loadBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
  };
  xhr.onload = function() {
    loadBar.style.width = '100%';
    var blobUrl = URL.createObjectURL(xhr.response);
    img.src = blobUrl;
    img.onload = function() {
      loader.style.display = 'none';
      img.style.opacity = '1';
      if (cropper) cropper.destroy();
      cropper = new Cropper(img, { viewMode: 1, autoCropArea: 1, responsive: true, background: true });
    };
  };
  xhr.onerror = function() {
    loader.innerHTML = '<div style="color:#fca5a5;">Failed to load. Try hard-refreshing (Ctrl+Shift+R).</div>';
  };
  xhr.send();
}

function closeEditor() {
  document.getElementById('editorModal').style.display = 'none';
  if (cropper) { cropper.destroy(); cropper = null; }
}

function editorAction(action) {
  if (!cropper) return;
  switch(action) {
    case 'rotateCW':  cropper.rotate(90); break;
    case 'rotateCCW': cropper.rotate(-90); break;
    case 'flipH':     cropper.scaleX(cropper.getData().scaleX === -1 ? 1 : -1); break;
    case 'flipV':     cropper.scaleY(cropper.getData().scaleY === -1 ? 1 : -1); break;
    case 'reset':     cropper.reset(); break;
  }
}

function setAspect(ratio) {
  if (cropper) cropper.setAspectRatio(ratio);
}

function applyAdjustments() {
  var b = document.getElementById('adjustBrightness').value;
  var c = document.getElementById('adjustContrast').value;
  var s = document.getElementById('adjustSaturation').value;
  document.getElementById('valBrightness').textContent = b;
  document.getElementById('valContrast').textContent = c;
  document.getElementById('valSaturation').textContent = s;
  // Apply to the cropper container (Cropper.js clones the image, so we filter the wrapper)
  var f = 'brightness(' + (b/100) + ') contrast(' + (c/100) + ') saturate(' + (s/100) + ')';
  // Apply to both the cropper canvas image and the view-box image
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

  try {
    // Get cropped canvas and apply adjustments
    var srcCanvas = cropper.getCroppedCanvas({ maxWidth: 4096, maxHeight: 4096 });
    var b = document.getElementById('adjustBrightness').value;
    var c = document.getElementById('adjustContrast').value;
    var s = document.getElementById('adjustSaturation').value;

    var canvas = srcCanvas;
    if (b != 100 || c != 100 || s != 100) {
      canvas = document.createElement('canvas');
      canvas.width = srcCanvas.width;
      canvas.height = srcCanvas.height;
      var ctx = canvas.getContext('2d');
      ctx.filter = 'brightness(' + (b/100) + ') contrast(' + (c/100) + ') saturate(' + (s/100) + ')';
      ctx.drawImage(srcCanvas, 0, 0);
    }
    var blob = await new Promise(function(res) { canvas.toBlob(res, 'image/jpeg', 0.92); });

    // Get presigned URL
    var csrfToken = document.querySelector('input[name="_token"]').value;
    var resp = await fetch('{{ route("admin.media.presigned") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({
        student_id: {{ $student->id }},
        filename: 'edited_' + editingMediaId + '.jpg',
        mime_type: 'image/jpeg',
        file_size: blob.size,
      })
    });
    var presign = await resp.json();

    // Upload edited image to S3
    await fetch(presign.upload_url, { method: 'PUT', headers: { 'Content-Type': 'image/jpeg' }, body: blob });

    // Register as replacement (update existing record)
    await fetch('{{ route("admin.media.register") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({
        student_id: {{ $student->id }},
        s3_path: presign.s3_path,
        type: 'photo',
        filename: 'edited_' + editingMediaId + '.jpg',
        mime_type: 'image/jpeg',
        file_size: blob.size,
        width: canvas.width,
        height: canvas.height,
        replace_media_id: editingMediaId,
        caption: null,
      })
    });

    closeEditor();
    window.location.reload();
  } catch(e) {
    btn.textContent = 'Failed — try again';
    btn.style.opacity = '1'; btn.disabled = false;
  }
}

// ═══ VIDEO TRIMMER ═══
var trimMediaId = null;
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

function openTrimmer(url, mediaId, duration) {
  trimMediaId = mediaId;
  trimTotalDuration = duration || 30;
  trimVideoEl = document.getElementById('trimVideo');
  var loader = document.getElementById('trimLoading');
  var loadBar = document.getElementById('trimLoadBar');
  var loadText = document.getElementById('trimLoadText');

  loader.style.display = 'flex';
  loadBar.style.width = '0%';
  loadText.textContent = '';
  document.getElementById('trimPlayBtn').style.display = 'none';
  document.getElementById('trimmerModal').style.display = 'flex';
  document.getElementById('saveTrimBtn').disabled = false;
  document.getElementById('saveTrimBtn').textContent = 'Save Trim';
  document.getElementById('saveTrimBtn').style.opacity = '1';
  document.getElementById('trimStatus').textContent = '';
  resetVideoAdjustments();

  // Load video with fetch (avoids Range request CORS issues)
  loadText.textContent = 'Downloading...';
  fetch(url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now())
    .then(function(resp) {
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      var total = parseInt(resp.headers.get('content-length') || '0');
      var loaded = 0;
      var reader = resp.body.getReader();
      var chunks = [];

      function pump() {
        return reader.read().then(function(result) {
          if (result.done) return new Blob(chunks, { type: 'video/mp4' });
          chunks.push(result.value);
          loaded += result.value.length;
          if (total > 0) {
            var pct = Math.round((loaded / total) * 100);
            loadBar.style.width = pct + '%';
            loadText.textContent = (loaded / 1048576).toFixed(1) + ' / ' + (total / 1048576).toFixed(1) + ' MB';
          } else {
            loadText.textContent = (loaded / 1048576).toFixed(1) + ' MB downloaded';
          }
          return pump();
        });
      }
      return pump();
    })
    .then(function(blob) {
      loadBar.style.width = '100%';
      loadText.textContent = 'Buffering...';
      trimVideoEl.src = URL.createObjectURL(blob);
      trimVideoEl.onloadedmetadata = function() {
        loader.style.display = 'none';
        document.getElementById('trimPlayBtn').style.display = 'block';
        trimTotalDuration = trimVideoEl.duration;
        document.getElementById('trimStart').value = '0';
        document.getElementById('trimStart').max = trimTotalDuration;
        document.getElementById('trimEnd').value = trimTotalDuration.toFixed(1);
        document.getElementById('trimEnd').max = trimTotalDuration;
        updateTrimRange();
        startPlayheadUpdate();
      };
    })
    .catch(function(err) {
      loadText.textContent = 'Failed to load: ' + err.message;
      loadText.style.color = '#fca5a5';
    });
}

function closeTrimmer() {
  document.getElementById('trimmerModal').style.display = 'none';
  if (trimVideoEl) { trimVideoEl.pause(); trimVideoEl.src = ''; }
  if (trimAnimFrame) cancelAnimationFrame(trimAnimFrame);
}

function toggleTrimPlay() {
  if (!trimVideoEl) return;
  if (trimVideoEl.paused) {
    trimVideoEl.play();
    document.getElementById('trimPlayBtn').style.display = 'none';
  } else {
    trimVideoEl.pause();
    document.getElementById('trimPlayBtn').style.display = 'block';
  }
}

function startPlayheadUpdate() {
  function update() {
    if (trimVideoEl && trimTotalDuration > 0) {
      var pct = (trimVideoEl.currentTime / trimTotalDuration) * 100;
      document.getElementById('playhead').style.left = pct + '%';
    }
    trimAnimFrame = requestAnimationFrame(update);
  }
  update();

  trimVideoEl.onclick = function() { toggleTrimPlay(); };
  trimVideoEl.onpause = function() { document.getElementById('trimPlayBtn').style.display = 'block'; };
  trimVideoEl.onplay = function() { document.getElementById('trimPlayBtn').style.display = 'none'; };
}

function seekTimeline(e) {
  if (!trimVideoEl || !trimTotalDuration) return;
  var rect = document.getElementById('timeline').getBoundingClientRect();
  var pct = (e.clientX - rect.left) / rect.width;
  trimVideoEl.currentTime = pct * trimTotalDuration;
}

function updateTrimRange() {
  var start = parseFloat(document.getElementById('trimStart').value) || 0;
  var end = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;
  if (end > trimTotalDuration) end = trimTotalDuration;
  if (start >= end) start = Math.max(0, end - 0.5);

  var leftPct = (start / trimTotalDuration) * 100;
  var rightPct = ((trimTotalDuration - end) / trimTotalDuration) * 100;
  document.getElementById('trimRange').style.left = leftPct + '%';
  document.getElementById('trimRange').style.right = rightPct + '%';
  document.getElementById('trimDuration').textContent = (end - start).toFixed(1);
}

function setTrimStart() {
  if (!trimVideoEl) return;
  document.getElementById('trimStart').value = trimVideoEl.currentTime.toFixed(1);
  updateTrimRange();
}

function setTrimEnd() {
  if (!trimVideoEl) return;
  document.getElementById('trimEnd').value = trimVideoEl.currentTime.toFixed(1);
  updateTrimRange();
}

function previewTrim() {
  if (!trimVideoEl) return;
  var start = parseFloat(document.getElementById('trimStart').value) || 0;
  var end = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;
  trimVideoEl.currentTime = start;
  trimVideoEl.play();

  // Stop at end point
  var checkEnd = function() {
    if (trimVideoEl.currentTime >= end) {
      trimVideoEl.pause();
      trimVideoEl.removeEventListener('timeupdate', checkEnd);
    }
  };
  trimVideoEl.addEventListener('timeupdate', checkEnd);
}

async function saveTrim() {
  var start = parseFloat(document.getElementById('trimStart').value) || 0;
  var end = parseFloat(document.getElementById('trimEnd').value) || trimTotalDuration;

  if (end - start < 0.5) {
    document.getElementById('trimStatus').textContent = 'Clip must be at least 0.5 seconds';
    document.getElementById('trimStatus').style.color = '#dc2626';
    return;
  }

  var btn = document.getElementById('saveTrimBtn');
  btn.disabled = true; btn.textContent = 'Trimming...'; btn.style.opacity = '.5';

  // Animated progress bar
  var hasAdjust = parseInt(document.getElementById('vidBrightness').value) !== 100 ||
                  parseInt(document.getElementById('vidContrast').value) !== 100 ||
                  parseInt(document.getElementById('vidSaturation').value) !== 100;
  var clipDuration = end - start;
  var estimatedSec = hasAdjust ? Math.max(5, clipDuration * 0.8) : Math.max(2, clipDuration * 0.2);

  var statusEl = document.getElementById('trimStatus');
  var progressSteps = [
    { pct: 10, msg: 'Downloading from storage...' },
    { pct: 25, msg: hasAdjust ? 'Re-encoding with adjustments...' : 'Trimming video...' },
    { pct: 50, msg: 'Processing: ' + clipDuration.toFixed(1) + 's of video...' },
    { pct: 75, msg: 'Almost done...' },
    { pct: 85, msg: 'Uploading trimmed video...' },
  ];
  var stepIdx = 0;
  statusEl.style.color = '#6b7280';
  statusEl.innerHTML = '<div style="margin-bottom:4px;">Starting...</div><div style="background:#e5eaf2;border-radius:4px;height:6px;overflow:hidden;"><div id="trimProgressBar" style="width:0%;height:100%;background:#001F5B;border-radius:4px;transition:width .5s;"></div></div>';
  var progBar = document.getElementById('trimProgressBar');

  var progInterval = setInterval(function() {
    if (stepIdx < progressSteps.length) {
      var step = progressSteps[stepIdx];
      progBar.style.width = step.pct + '%';
      statusEl.querySelector('div').textContent = step.msg;
      stepIdx++;
    }
  }, (estimatedSec * 1000) / progressSteps.length);

  try {
    var resp = await fetch('{{ route("admin.media.trim-video") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
      body: JSON.stringify({
        media_id: trimMediaId, start_time: start, end_time: end,
        brightness: parseInt(document.getElementById('vidBrightness').value),
        contrast: parseInt(document.getElementById('vidContrast').value),
        saturation: parseInt(document.getElementById('vidSaturation').value),
      })
    });

    clearInterval(progInterval);
    var result = await resp.json();
    if (result.success) {
      progBar.style.width = '100%';
      statusEl.querySelector('div').textContent = 'Done! ' + parseFloat(result.duration).toFixed(1) + 's. Reloading...';
      statusEl.style.color = '#065f46';
      setTimeout(function() { window.location.reload(); }, 1500);
    } else {
      throw new Error(result.message || result.error || 'Trim failed');
    }
  } catch(e) {
    clearInterval(progInterval);
    statusEl.innerHTML = 'Failed: ' + e.message;
    statusEl.style.color = '#dc2626';
    btn.disabled = false; btn.textContent = 'Save Trim'; btn.style.opacity = '1';
  }
}
</script>
@endsection
