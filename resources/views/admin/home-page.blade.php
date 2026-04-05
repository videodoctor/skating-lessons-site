@extends('layouts.admin')
@section('title', 'Home Page — Admin')
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .media-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;max-height:300px;overflow-y:auto;padding:.25rem;}
  .media-pick{position:relative;border-radius:6px;overflow:hidden;cursor:pointer;border:3px solid transparent;transition:border-color .15s;}
  .media-pick.selected{border-color:var(--red);}
  .media-pick .thumb{width:100%;aspect-ratio:1/1;object-fit:cover;display:block;}
  .media-pick .pick-check{position:absolute;top:3px;right:3px;width:20px;height:20px;border-radius:50%;background:rgba(0,0,0,.5);color:#fff;font-size:.65rem;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s;}
  .media-pick.selected .pick-check{opacity:1;background:var(--red);}
  .media-pick:hover .pick-check{opacity:1;}
  .media-pick .pick-order{position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:var(--navy);color:#fff;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;}
  .media-pick .pick-label{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.6);color:#fff;font-size:.6rem;padding:2px 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .selected-strip{display:flex;gap:.4rem;flex-wrap:wrap;min-height:50px;padding:.4rem;background:#f8fafc;border:1.5px dashed #e5eaf2;border-radius:6px;margin-bottom:.75rem;}
  .btn-navy{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:7px 18px;font-weight:700;font-size:.85rem;cursor:pointer;}
  .btn-navy:hover{background:var(--red);}
  .btn-ghost{background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:7px 14px;font-weight:600;font-size:.82rem;cursor:pointer;}
  .preview-tab{padding:.4rem .9rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;border-radius:6px 6px 0 0;background:#e5eaf2;color:#6b7280;}
  .preview-tab.active{background:var(--navy);color:#fff;}
  .preview-container{border:1.5px solid #e5eaf2;border-radius:0 0 8px 8px;overflow:hidden;background:#f8fafc;display:flex;justify-content:center;}
  .preview-container iframe{border:none;background:#fff;}
  .section-label{font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.35rem;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Home Page Content</h1>
    <p style="color:#6b7280;font-size:.85rem;">Manage hero videos, bio photos, and preview the home page</p>
  </div>
  <a href="/" target="_blank" class="btn-ghost" style="text-decoration:none;">View Live Site →</a>
</div>

{{-- ═══ HERO MEDIA ═══ --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.15rem;color:var(--navy);margin:0 0 .5rem;">Hero Videos</h2>
  <p style="font-size:.8rem;color:#6b7280;margin-bottom:.6rem;">Select videos for the hero background rotation. Click to toggle, numbers show order.</p>

  <form method="POST" action="{{ route('admin.homepage.update-hero') }}" id="heroForm">
    @csrf
    <div id="hero-selected-ids"></div>
    <div class="section-label">Selected ({{ count($heroMediaIds) }})</div>
    <div class="selected-strip" id="heroStrip">
      @if(empty($heroMediaIds))<span style="color:#9ca3af;font-size:.8rem;padding:.4rem;">None selected</span>@endif
    </div>
    <div class="media-picker">
      @foreach($availableVideos as $v)
      <div class="media-pick {{ in_array($v->id, $heroMediaIds) ? 'selected' : '' }}" data-id="{{ $v->id }}" data-url="{{ $v->url }}" data-group="hero" onclick="togglePick(this,'hero')">
        <video src="{{ $v->url }}" class="thumb" preload="metadata"></video>
        <div class="pick-check">✓</div>
        @if(in_array($v->id, $heroMediaIds))<div class="pick-order">{{ array_search($v->id, $heroMediaIds) + 1 }}</div>@endif
        <div class="pick-label">{{ $v->student->first_name ?? '' }} · {{ $v->duration ? gmdate('i:s', (int)$v->duration) : '' }}</div>
      </div>
      @endforeach
    </div>
    <div style="display:flex;gap:.5rem;margin-top:.75rem;">
      <button type="submit" class="btn-navy">Save Hero Videos</button>
      <button type="button" class="btn-ghost" onclick="clearGroup('hero')">Clear</button>
    </div>
  </form>
</div>

{{-- ═══ BIO PHOTOS ═══ --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.15rem;color:var(--navy);margin:0 0 .5rem;">Bio Photos</h2>
  <p style="font-size:.8rem;color:#6b7280;margin-bottom:.6rem;">Click a photo to crop it to the 4:5 bio frame, then it's added to the rotation.</p>

  <form method="POST" action="{{ route('admin.homepage.update-bio') }}" id="bioForm">
    @csrf
    <div id="bio-selected-ids">
      @foreach($bioMediaIds as $bid)
        <input type="hidden" name="media_ids[]" value="{{ $bid }}">
      @endforeach
    </div>
    <div class="section-label">Selected ({{ count($bioMediaIds) }})</div>
    <div class="selected-strip" id="bioStrip">
      @if($bioMedia->isEmpty())
        <span style="color:#9ca3af;font-size:.8rem;padding:.4rem;">None — click a photo below to crop & add</span>
      @else
        @foreach($bioMedia as $bm)
          <div style="position:relative;display:inline-block;">
            <img src="{{ $bm->url }}" style="width:48px;height:60px;border-radius:5px;object-fit:cover;border:2px solid #001F5B;">
            <button type="button" onclick="removeBioPhoto({{ $bm->id }})"
              style="position:absolute;top:-4px;right:-4px;width:16px;height:16px;border-radius:50%;background:#dc2626;color:#fff;border:none;font-size:.6rem;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;">✕</button>
          </div>
        @endforeach
      @endif
    </div>
    <div class="media-picker">
      @foreach($availablePhotos as $p)
      @php $bioIdx = array_search($p->id, $bioMediaIds); @endphp
      <div class="media-pick {{ $bioIdx !== false ? 'selected' : '' }}" data-id="{{ $p->id }}" data-url="{{ $p->url }}" data-student="{{ $p->student->first_name ?? '' }}" onclick="openBioCrop(this)">
        <img src="{{ $p->url }}" class="thumb" loading="lazy">
        @if($bioIdx !== false)
          <div class="pick-order">{{ $bioIdx + 1 }}</div>
          <div class="pick-check">✓</div>
        @endif
        <div class="pick-label">{{ $p->student->first_name ?? '' }}</div>
      </div>
      @endforeach
      {{-- Also show bio crops that aren't in the main available list --}}
      @foreach($bioMedia as $bm)
        @if(!$availablePhotos->contains('id', $bm->id))
        @php $bioIdx = array_search($bm->id, $bioMediaIds); @endphp
        <div class="media-pick selected" data-id="{{ $bm->id }}" data-url="{{ $bm->url }}">
          <img src="{{ $bm->url }}" class="thumb" loading="lazy">
          @if($bioIdx !== false)<div class="pick-order">{{ $bioIdx + 1 }}</div>@endif
          <div class="pick-check">✓</div>
          <div class="pick-label">Bio crop</div>
        </div>
        @endif
      @endforeach
    </div>
    <div style="display:flex;gap:.5rem;margin-top:.75rem;">
      <button type="submit" class="btn-navy">Save Bio Photos</button>
      <button type="button" class="btn-ghost" onclick="clearGroup('bio')">Clear</button>
    </div>
  </form>
</div>

{{-- ═══ LIVE PREVIEW ═══ --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.15rem;color:var(--navy);margin:0 0 .75rem;">Live Preview</h2>
  <div style="display:flex;gap:0;">
    <button class="preview-tab active" onclick="setPreview('desktop', this)">Desktop (1280px)</button>
    <button class="preview-tab" onclick="setPreview('tablet', this)">Tablet (768px)</button>
    <button class="preview-tab" onclick="setPreview('mobile', this)">Mobile (375px)</button>
  </div>
  <div class="preview-container" id="previewContainer" style="height:600px;">
    <iframe src="/" id="previewIframe" style="width:1280px;height:600px;transform:scale(1);transform-origin:top center;"></iframe>
  </div>
  <div style="margin-top:.5rem;display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:.75rem;color:#9ca3af;" id="previewLabel">Desktop 1280px</span>
    <a href="/" target="_blank" style="font-size:.78rem;color:var(--navy);">Open in new tab →</a>
  </div>
</div>

<script>
const groups = {
  hero: { ids: @json($heroMediaIds), max: 99 },
  bio:  { ids: @json($bioMediaIds), max: 99 },
};

function togglePick(el, group) {
  const id = parseInt(el.dataset.id);
  const g = groups[group];
  const idx = g.ids.indexOf(id);
  if (idx > -1) {
    g.ids.splice(idx, 1);
    el.classList.remove('selected');
  } else {
    if (g.ids.length >= g.max) return; // enforce max
    g.ids.push(id);
    el.classList.add('selected');
  }
  updateGroup(group);
}

function clearGroup(group) {
  groups[group].ids = [];
  document.querySelectorAll(`.media-pick[data-group="${group}"]`).forEach(el => el.classList.remove('selected'));
  updateGroup(group);
}

function updateGroup(group) {
  const g = groups[group];
  // Hidden inputs
  document.getElementById(group + '-selected-ids').innerHTML =
    g.ids.map(id => `<input type="hidden" name="media_ids[]" value="${id}">`).join('');

  // Order numbers
  document.querySelectorAll(`.media-pick[data-group="${group}"]`).forEach(el => {
    const id = parseInt(el.dataset.id);
    const idx = g.ids.indexOf(id);
    let orderEl = el.querySelector('.pick-order');
    if (idx > -1) {
      if (!orderEl) { orderEl = document.createElement('div'); orderEl.className = 'pick-order'; el.appendChild(orderEl); }
      orderEl.textContent = idx + 1;
    } else if (orderEl) { orderEl.remove(); }
  });

  // Strip
  const strip = document.getElementById(group + 'Strip');
  if (g.ids.length === 0) {
    strip.innerHTML = '<span style="color:#9ca3af;font-size:.8rem;padding:.4rem;">None selected</span>';
  } else {
    strip.innerHTML = g.ids.map(id => {
      const el = document.querySelector(`.media-pick[data-group="${group}"][data-id="${id}"]`);
      const url = el ? el.dataset.url : '';
      const isVideo = el && el.querySelector('video');
      if (isVideo) {
        return `<video src="${url}" preload="metadata" style="width:48px;height:48px;border-radius:5px;object-fit:cover;border:2px solid #001F5B;"></video>`;
      }
      return `<img src="${url}" style="width:48px;height:48px;border-radius:5px;object-fit:cover;border:2px solid #001F5B;">`;
    }).join('');
  }
}

// Init
// Only init hero strip via JS — bio strip is server-rendered
updateGroup('hero');

// Preview tabs
const previewSizes = {
  desktop: { width: 1280, label: 'Desktop 1280px' },
  tablet:  { width: 768,  label: 'Tablet 768px' },
  mobile:  { width: 375,  label: 'Mobile 375px' },
};

function setPreview(mode, btn) {
  document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');

  const container = document.getElementById('previewContainer');
  const iframe = document.getElementById('previewIframe');
  const cfg = previewSizes[mode];
  const containerWidth = container.clientWidth;
  const scale = Math.min(1, containerWidth / cfg.width);

  iframe.style.width = cfg.width + 'px';
  iframe.style.height = (600 / scale) + 'px';
  iframe.style.transform = `scale(${scale})`;
  iframe.style.transformOrigin = 'top center';
  container.style.height = '600px';

  document.getElementById('previewLabel').textContent = cfg.label + (scale < 1 ? ` (scaled ${Math.round(scale * 100)}%)` : '');
}

// Set initial scale
window.addEventListener('load', () => setPreview('desktop', document.querySelector('.preview-tab.active')));
</script>
{{-- ═══ BIO CROP MODAL ═══ --}}
<div id="bioCropModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:200;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeBioCrop()">
  <div style="background:#fff;border-radius:12px;max-width:700px;width:100%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;">Crop for Bio Section</h3>
        <p style="font-size:.78rem;color:#6b7280;margin:2px 0 0;">Position the 4:5 frame over the area you want shown</p>
      </div>
      <button onclick="closeBioCrop()" style="background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    </div>
    <div style="flex:1;overflow:hidden;background:#1a1a2e;display:flex;align-items:center;justify-content:center;min-height:300px;position:relative;">
      <div id="bioCropLoading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;z-index:5;">
        <div style="font-size:1.2rem;margin-bottom:.5rem;">Loading...</div>
        <div style="width:200px;height:6px;background:rgba(255,255,255,.2);border-radius:3px;overflow:hidden;">
          <div id="bioCropLoadBar" style="width:0%;height:100%;background:#fff;border-radius:3px;transition:width .3s;"></div>
        </div>
      </div>
      <img id="bioCropImage" crossorigin="anonymous" style="max-width:100%;display:block;opacity:0;">
    </div>
    <div style="padding:.75rem 1.25rem;border-top:1px solid #e5eaf2;display:flex;justify-content:flex-end;gap:.4rem;">
      <button onclick="closeBioCrop()" class="btn-ghost" style="font-size:.82rem;padding:6px 14px;">Cancel</button>
      <button onclick="saveBioCrop()" class="btn-navy" id="saveBioCropBtn" style="padding:6px 18px;">Crop & Add to Bio</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
var bioCropper = null;
var bioCropSourceId = null;
var bioCropStudentId = null;

function removeBioPhoto(id) {
  if (!confirm('Remove this photo from the bio rotation?')) return;
  var ids = @json($bioMediaIds).filter(function(i) { return i !== id; });
  // Update via form submit
  var form = document.getElementById('bioForm');
  document.getElementById('bio-selected-ids').innerHTML = ids.map(function(i) {
    return '<input type="hidden" name="media_ids[]" value="' + i + '">';
  }).join('');
  form.submit();
}

function openBioCrop(el) {
  bioCropSourceId = parseInt(el.dataset.id);
  // Find the student_id from the available photos data
  @foreach($availablePhotos as $p)
  if (bioCropSourceId === {{ $p->id }}) bioCropStudentId = {{ $p->student_id }};
  @endforeach

  var url = el.dataset.url;
  var img = document.getElementById('bioCropImage');
  var loader = document.getElementById('bioCropLoading');
  var loadBar = document.getElementById('bioCropLoadBar');

  img.style.opacity = '0';
  loader.style.display = 'flex';
  loadBar.style.width = '10%';
  document.getElementById('bioCropModal').style.display = 'flex';
  document.getElementById('saveBioCropBtn').disabled = false;
  document.getElementById('saveBioCropBtn').textContent = 'Crop & Add to Bio';
  document.getElementById('saveBioCropBtn').style.opacity = '1';

  var xhr = new XMLHttpRequest();
  xhr.open('GET', url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now(), true);
  xhr.responseType = 'blob';
  xhr.onprogress = function(e) {
    if (e.lengthComputable) loadBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
  };
  xhr.onload = function() {
    loadBar.style.width = '100%';
    img.src = URL.createObjectURL(xhr.response);
    img.onload = function() {
      loader.style.display = 'none';
      img.style.opacity = '1';
      if (bioCropper) bioCropper.destroy();
      bioCropper = new Cropper(img, {
        aspectRatio: 4 / 5,
        viewMode: 1,
        autoCropArea: 0.9,
        responsive: true,
        background: true,
      });
    };
  };
  xhr.onerror = function() {
    loader.innerHTML = '<div style="color:#fca5a5;">Failed to load image.</div>';
  };
  xhr.send();
}

function closeBioCrop() {
  document.getElementById('bioCropModal').style.display = 'none';
  if (bioCropper) { bioCropper.destroy(); bioCropper = null; }
}

async function saveBioCrop() {
  if (!bioCropper || !bioCropSourceId) return;
  var btn = document.getElementById('saveBioCropBtn');
  btn.disabled = true; btn.textContent = 'Saving...'; btn.style.opacity = '.5';

  try {
    var canvas = bioCropper.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2560 });
    var blob = await new Promise(function(res) { canvas.toBlob(res, 'image/jpeg', 0.92); });

    // Get presigned URL
    var csrfToken = '{{ csrf_token() }}';
    var resp = await fetch('{{ route("admin.media.presigned") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({
        student_id: bioCropStudentId,
        filename: 'bio_crop_' + bioCropSourceId + '.jpg',
        mime_type: 'image/jpeg',
        file_size: blob.size,
      })
    });
    var presign = await resp.json();

    // Upload to S3
    await fetch(presign.upload_url, { method: 'PUT', headers: { 'Content-Type': 'image/jpeg' }, body: blob });

    // Register the bio crop
    var regResp = await fetch('{{ route("admin.homepage.bio-crop") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({
        s3_path: presign.s3_path,
        source_media_id: bioCropSourceId,
        width: canvas.width,
        height: canvas.height,
        file_size: blob.size,
      })
    });

    var result = await regResp.json();
    closeBioCrop();
    window.location.reload();
  } catch(e) {
    btn.textContent = 'Failed — try again';
    btn.style.opacity = '1'; btn.disabled = false;
  }
}
</script>
@endsection
