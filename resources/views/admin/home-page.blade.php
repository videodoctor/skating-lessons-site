@extends('layouts.admin')
@section('title', 'Home Page — Admin')
@section('content')
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
  <p style="font-size:.8rem;color:#6b7280;margin-bottom:.6rem;">Select 2 photos for the cross-dissolve in the "Meet Your Coach" section.</p>

  <form method="POST" action="{{ route('admin.homepage.update-bio') }}" id="bioForm">
    @csrf
    <div id="bio-selected-ids"></div>
    <div class="section-label">Selected ({{ count($bioMediaIds) }}/2)</div>
    <div class="selected-strip" id="bioStrip">
      @if(empty($bioMediaIds))<span style="color:#9ca3af;font-size:.8rem;padding:.4rem;">None selected</span>@endif
    </div>
    <div class="media-picker">
      @foreach($availablePhotos as $p)
      <div class="media-pick {{ in_array($p->id, $bioMediaIds) ? 'selected' : '' }}" data-id="{{ $p->id }}" data-url="{{ $p->url }}" data-group="bio" onclick="togglePick(this,'bio')">
        <img src="{{ $p->url }}" class="thumb" loading="lazy">
        <div class="pick-check">✓</div>
        @if(in_array($p->id, $bioMediaIds))<div class="pick-order">{{ array_search($p->id, $bioMediaIds) + 1 }}</div>@endif
        <div class="pick-label">{{ $p->student->first_name ?? '' }}</div>
      </div>
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
  bio:  { ids: @json($bioMediaIds), max: 2 },
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
['hero', 'bio'].forEach(g => updateGroup(g));

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
@endsection
