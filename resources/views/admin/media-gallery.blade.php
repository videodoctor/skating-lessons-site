@extends('layouts.admin')
@section('title', 'Media Gallery — Admin')
@section('content')
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
  .media-card .type-tag{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.5);color:#fff;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:3px;}
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
    <div style="margin-top:.6rem;"><button type="submit" class="btn-navy">Upload</button></div>
  </form>
</div>

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
        @endif
      </div>
      <span class="type-tag">{{ strtoupper($item->type) }}</span>
      @if($item->student)
        <a href="{{ route('admin.students.profile', $item->student) }}" class="student-tag" style="text-decoration:none;">{{ $item->student->first_name }}</a>
      @endif
      <form method="POST" action="{{ route('admin.students.delete-media', $item) }}" style="display:inline;"
        onsubmit="return confirm('Delete this {{ $item->type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="delete-btn" title="Delete">✕</button>
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
@endsection
