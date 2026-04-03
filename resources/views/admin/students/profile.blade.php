@extends('layouts.admin')
@section('title', $student->full_name . ' — Student Profile')
@section('content')
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
  <form method="POST" action="{{ route('admin.students.upload', $student) }}" enctype="multipart/form-data">
    @csrf
    <div class="upload-zone" onclick="document.getElementById('file-input').click()">
      <input type="file" id="file-input" name="files[]" multiple accept="image/*,video/*" style="display:none;"
        onchange="document.getElementById('file-count').textContent = this.files.length + ' file(s) selected'">
      <div style="font-size:2rem;margin-bottom:.5rem;">📸</div>
      <p style="font-weight:600;color:#1e40af;">Drop files here or tap to browse</p>
      <p style="font-size:.82rem;color:#6b7280;margin-top:.25rem;">JPG, PNG, WebP, HEIC, MP4, MOV — up to 100MB each</p>
      <p id="file-count" style="font-size:.82rem;color:var(--navy);font-weight:600;margin-top:.5rem;"></p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:flex-end;margin-top:.75rem;">
      <div style="flex:1;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#6b7280;margin-bottom:3px;">Caption (optional)</label>
        <input type="text" name="caption" placeholder="e.g. Practice session at Creve Coeur"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.85rem;">
      </div>
      <button type="submit" class="btn-sm btn-navy" style="padding:8px 20px;">Upload</button>
    </div>
  </form>
</div>

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

      {{-- Set as profile photo --}}
      @if($item->type === 'photo')
      <form method="POST" action="{{ route('admin.students.set-profile-photo', [$student, $item]) }}" style="display:inline;">
        @csrf
        <button type="submit" class="profile-star {{ $student->profile_photo_id === $item->id ? 'active' : '' }}" title="Set as profile photo">
          {{ $student->profile_photo_id === $item->id ? '★' : '☆' }}
        </button>
      </form>
      @endif

      {{-- Delete --}}
      <form method="POST" action="{{ route('admin.students.delete-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Delete this {{ $item->type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="delete-btn" title="Delete">✕</button>
      </form>

      <div class="media-card-body">
        @if($item->caption)
          <div class="media-card-caption">{{ $item->caption }}</div>
        @endif
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
@endsection
