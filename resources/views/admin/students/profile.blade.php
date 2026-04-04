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
  .media-card .profile-star{position:absolute;top:6px;left:6px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .media-card .profile-star.active{background:var(--red);}
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

      {{-- Set as profile photo + Edit --}}
      @if($item->type === 'photo')
      <form method="POST" action="{{ route('admin.students.set-profile-photo', [$student, $item]) }}" style="display:inline;">
        @csrf
        <button type="submit" class="profile-star {{ $student->profile_photo_id === $item->id ? 'active' : '' }}" title="Set as profile photo">
          {{ $student->profile_photo_id === $item->id ? '★' : '☆' }}
        </button>
      </form>
      <button onclick="openEditor('{{ $item->url }}', {{ $item->id }})" title="Edit photo"
        style="position:absolute;top:6px;left:36px;background:rgba(0,31,91,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">✎</button>
      @if($item->is_edited)
      <form method="POST" action="{{ route('admin.students.revert-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Revert to original photo? The edited version will be deleted.')">
        @csrf
        <button type="submit" title="Revert to original"
          style="position:absolute;top:6px;left:68px;background:rgba(200,16,46,.8);color:#fff;border:none;border-radius:50%;width:28px;height:28px;font-size:.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">↩</button>
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
    <div style="flex:1;overflow:hidden;background:#1a1a2e;display:flex;align-items:center;justify-content:center;min-height:300px;">
      <img id="editorImage" crossorigin="anonymous" style="max-width:100%;display:block;">
    </div>
    <div style="padding:.75rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;gap:.5rem;flex-wrap:wrap;justify-content:space-between;align-items:center;">
      <div style="display:flex;gap:.4rem;">
        <button onclick="editorAction('rotateCW')" class="btn-sm btn-ghost" title="Rotate right">↻ 90°</button>
        <button onclick="editorAction('rotateCCW')" class="btn-sm btn-ghost" title="Rotate left">↺ 90°</button>
        <button onclick="editorAction('flipH')" class="btn-sm btn-ghost" title="Flip horizontal">↔ Flip</button>
        <button onclick="editorAction('flipV')" class="btn-sm btn-ghost" title="Flip vertical">↕ Flip</button>
        <button onclick="editorAction('reset')" class="btn-sm btn-ghost" title="Reset">Reset</button>
      </div>
      <div style="display:flex;gap:.4rem;align-items:center;">
        <span style="font-size:.75rem;color:#6b7280;">Aspect:</span>
        <button onclick="setAspect(NaN)" class="btn-sm btn-ghost" style="font-size:.72rem;">Free</button>
        <button onclick="setAspect(1)" class="btn-sm btn-ghost" style="font-size:.72rem;">1:1</button>
        <button onclick="setAspect(4/3)" class="btn-sm btn-ghost" style="font-size:.72rem;">4:3</button>
        <button onclick="setAspect(16/9)" class="btn-sm btn-ghost" style="font-size:.72rem;">16:9</button>
      </div>
      <div style="display:flex;gap:.4rem;">
        <button onclick="closeEditor()" class="btn-sm btn-ghost">Cancel</button>
        <button onclick="saveEdit()" class="btn-sm btn-navy" id="saveEditBtn">Save</button>
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
  // Cache-bust to ensure CORS headers are fresh
  img.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
  document.getElementById('editorModal').style.display = 'flex';
  document.getElementById('saveEditBtn').disabled = false;
  document.getElementById('saveEditBtn').textContent = 'Save';
  document.getElementById('saveEditBtn').style.opacity = '1';

  img.onload = function() {
    if (cropper) cropper.destroy();
    cropper = new Cropper(img, {
      viewMode: 1,
      autoCropArea: 1,
      responsive: true,
      background: true,
    });
  };
  img.onerror = function() {
    alert('Could not load image. Try hard-refreshing the page (Ctrl+Shift+R).');
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

async function saveEdit() {
  if (!cropper || !editingMediaId) return;
  var btn = document.getElementById('saveEditBtn');
  btn.disabled = true; btn.textContent = 'Saving...'; btn.style.opacity = '.5';

  try {
    // Get cropped canvas
    var canvas = cropper.getCroppedCanvas({ maxWidth: 4096, maxHeight: 4096 });
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
</script>
@endsection
