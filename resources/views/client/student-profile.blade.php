@extends('layouts.app')
@section('title', $student->full_name . ' — Profile')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .profile-header{display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;}
  .profile-photo{width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--navy);flex-shrink:0;}
  .profile-photo-placeholder{width:100px;height:100px;border-radius:50%;background:#e5eaf2;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#9ca3af;border:3px solid #e5eaf2;flex-shrink:0;}
  .media-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;}
  @media(max-width:768px){.media-grid{grid-template-columns:repeat(2,1fr);}}
  .media-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:8px;overflow:hidden;}
  .media-card .media-frame{width:100%;aspect-ratio:1/1;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f1f5f9;}
  .media-card .media-frame img,.media-card .media-frame video{width:100%;height:100%;object-fit:contain;display:block;}
  .media-card-body{padding:.4rem .6rem;}
  .media-card-caption{font-size:.75rem;color:#6b7280;}
  .upload-zone{border:2.5px dashed #bfdbfe;border-radius:10px;background:#eff6ff;text-align:center;padding:1.5rem;cursor:pointer;transition:all .2s;}
  .upload-zone:hover{border-color:var(--navy);background:#dbeafe;}
</style>

<div class="max-w-4xl mx-auto px-6 py-10">
  <div class="profile-header" style="margin-bottom:2rem;">
    @if($student->profile_photo_url)
      <img src="{{ $student->profile_photo_url }}" alt="{{ $student->first_name }}" class="profile-photo">
    @else
      <div class="profile-photo-placeholder">{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>
    @endif
    <div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--navy);margin:0;">{{ $student->full_name }}</h1>
      @if($student->age)<div style="font-size:.88rem;color:#6b7280;">Age {{ $student->age }}@if($student->skill_level) · {{ ucfirst($student->skill_level) }}@endif</div>@endif
      <div style="margin-top:.5rem;">
        <a href="{{ route('client.dashboard') }}" style="font-size:.85rem;color:var(--navy);">← Back to Dashboard</a>
      </div>
    </div>
  </div>

  {{-- Upload --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1.5rem;">
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--navy);margin:0 0 .75rem;">Upload Photos & Videos</h2>
    <form method="POST" action="{{ route('client.student.upload', $student) }}" enctype="multipart/form-data">
      @csrf
      <div class="upload-zone" onclick="document.getElementById('client-file-input').click()">
        <input type="file" id="client-file-input" name="files[]" multiple accept="image/*,video/*,.zip" style="display:none;"
          onchange="document.getElementById('client-file-count').textContent = this.files.length + ' file(s) selected'">
        <p style="font-weight:600;color:#1e40af;">📸 Tap to add photos or videos</p>
        <p style="font-size:.82rem;color:#6b7280;">JPG, PNG, MP4, MOV, or ZIP — up to 500MB</p>
        <p id="client-file-count" style="font-size:.82rem;color:var(--navy);font-weight:600;margin-top:.25rem;"></p>
      </div>
      <div style="display:flex;gap:.5rem;align-items:flex-end;margin-top:.75rem;">
        <div style="flex:1;">
          <input type="text" name="caption" placeholder="Caption (optional)"
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.85rem;">
        </div>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:8px 20px;font-weight:700;font-size:.85rem;cursor:pointer;">Upload</button>
      </div>
    </form>
  </div>

  {{-- Media --}}
  @if($media->isNotEmpty())
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--navy);margin:0 0 .75rem;">Photos & Videos</h2>
  <div class="media-grid">
    @foreach($media as $item)
    <div class="media-card">
      <div class="media-frame">
        @if($item->type === 'photo')
          <img src="{{ $item->url }}" alt="{{ $item->caption ?? $student->first_name }}" loading="lazy">
        @else
          <video src="{{ $item->url }}" preload="metadata" controls></video>
        @endif
      </div>
      @if($item->caption)
        <div class="media-card-body"><div class="media-card-caption">{{ $item->caption }}</div></div>
      @endif
    </div>
    @endforeach
  </div>
  <div style="margin-top:1rem;">{{ $media->links() }}</div>
  @else
  <div style="text-align:center;padding:2rem;color:#9ca3af;">
    No photos or videos yet — upload some to build {{ $student->first_name }}'s gallery!
  </div>
  @endif
</div>
@endsection
