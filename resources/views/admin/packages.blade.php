@extends('layouts.admin')
@section('title', 'Lesson Packages — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .pkg-card{background:#fff;border-radius:12px;border:1.5px solid #e5eaf2;padding:1.5rem;margin-bottom:1.25rem;position:relative;}
  .pkg-card.inactive{opacity:.55;border-style:dashed;}
  .pkg-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:1rem;flex-wrap:wrap;}
  .pkg-name{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);}
  .pkg-price{font-size:1.6rem;font-weight:800;color:var(--navy);}
  .pkg-meta{font-size:.78rem;color:#9ca3af;}
  .form-label{display:block;font-size:.75rem;font-weight:700;color:#374151;margin-bottom:3px;text-transform:uppercase;letter-spacing:.05em;}
  .form-input{width:100%;border:1.5px solid #e5eaf2;border-radius:7px;padding:.55rem .85rem;font-size:.88rem;color:#111;}
  .form-input:focus{outline:none;border-color:var(--navy);}
  textarea.form-input{min-height:80px;resize:vertical;}
  .btn-sm{padding:5px 12px;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;border:none;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;}
  .pill-gray{background:#f3f4f6;color:#6b7280;}
  .feature-item{display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem;}
  .feature-input{flex:1;border:1.5px solid #e5eaf2;border-radius:6px;padding:.4rem .7rem;font-size:.85rem;}
  .feature-input:focus{outline:none;border-color:var(--navy);}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
  .modal-box{background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,31,91,.2);}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Lesson Packages</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">{{ $services->where('is_active',true)->count() }} active · {{ $services->where('coming_soon',true)->count() }} coming soon · {{ $services->count() }} total</p>
  </div>
  <button onclick="openAdd()" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer;">+ New Package</button>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">{{ session('error') }}</div>
@endif

@foreach($services as $service)
<div class="pkg-card {{ !$service->is_active && !$service->coming_soon ? 'inactive' : '' }}">
  <div class="pkg-card-header">
    <div>
      <div style="display:flex;align-items:center;gap:.75rem;">
        <span class="pkg-name">{{ $service->name }}</span>
        @if($service->is_active)
          <span class="pill pill-green">Active</span>
        @elseif($service->coming_soon)
          <span class="pill" style="background:#fef9c3;color:#713f12;">🔒 Coming Soon</span>
        @else
          <span class="pill pill-gray">Hidden</span>
        @endif
      </div>
      <div class="pkg-meta">{{ $service->duration_minutes }} min · slug: {{ $service->slug }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;">
      <span class="pkg-price">${{ number_format($service->price, 0) }}</span>
      @if($service->hasActiveDiscount())
        <span style="background:#fee2e2;color:#991b1b;font-size:.72rem;font-weight:800;padding:2px 8px;border-radius:6px;text-decoration:line-through;">${{ number_format($service->price, 0) }}</span>
        <span style="background:#d1fae5;color:#065f46;font-size:.9rem;font-weight:800;">${{ number_format($service->discountedPrice(), 0) }}</span>
        <span style="background:#fef3c7;color:#92400e;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:6px;">⚡ {{ $service->discountLabel() }}
          @if($service->discount_ends_at) · ends {{ $service->discount_ends_at->format('M j') }}@endif
        </span>
      @endif
      <button class="btn-sm" style="background:#dbeafe;color:#1e40af;"
        onclick="openEdit({{ $service->id }})">Edit</button>
      @if($waitlistCounts[$service->id] ?? 0)
      <a href="{{ route('admin.packages.waitlist', $service) }}"
         style="background:#ede9fe;color:#5b21b6;border-radius:6px;padding:5px 10px;font-size:.72rem;font-weight:700;text-decoration:none;">
        📋 {{ $waitlistCounts[$service->id] }} waitlist
      </a>
      @endif
      <form method="POST" action="{{ route('admin.packages.toggle', $service) }}" style="display:inline;">
        @csrf @method('PATCH')
        <button type="submit" class="btn-sm" style="background:{{ $service->is_active ? '#fef3c7' : '#d1fae5' }};color:{{ $service->is_active ? '#92400e' : '#065f46' }};">
          {{ $service->is_active ? 'Hide' : 'Show' }}
        </button>
      </form>
    </div>
  </div>

  <p style="font-size:.88rem;color:#6b7280;margin:0 0 .75rem;">{{ $service->description }}</p>

  @if($service->features)
  <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
    @foreach($service->features as $feature)
    <span style="background:#f0f4ff;color:#1e40af;font-size:.73rem;padding:2px 8px;border-radius:10px;">✓ {{ $feature }}</span>
    @endforeach
  </div>
  @endif
</div>
@endforeach

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;">Edit Package</div>
    <form method="POST" id="editForm" action="">
      @csrf @method('PATCH')

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
        <div>
          <label class="form-label">Package Name *</label>
          <input type="text" name="name" id="edit_name" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Slug *</label>
          <input type="text" name="slug" id="edit_slug" class="form-input" required>
        </div>
      </div>

      <div style="margin-bottom:.75rem;">
        <label class="form-label">Description *</label>
        <textarea name="description" id="edit_description" class="form-input" required></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
        <div>
          <label class="form-label">Price ($) *</label>
          <input type="number" name="price" id="edit_price" class="form-input" step="0.01" min="0" required>
        </div>
        <div>
          <label class="form-label">Duration (minutes) *</label>
          <input type="number" name="duration_minutes" id="edit_duration" class="form-input" min="1" required>
        </div>
      </div>

      <div style="margin-bottom:.75rem;">
        <label class="form-label">Features (bullet points)</label>
        <div id="edit_features_list"></div>
        <button type="button" onclick="addFeatureRow()" style="background:#f0f4ff;color:#1e40af;border:none;border-radius:6px;padding:4px 12px;font-size:.78rem;font-weight:700;cursor:pointer;margin-top:.4rem;">+ Add Feature</button>
      </div>

      <div style="margin-bottom:1rem;">
        <label style="font-size:.75rem;font-weight:700;color:#374151;display:block;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.05em;">Status</label>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;">
            <input type="radio" name="status" value="active" id="edit_status_active"> Active
          </label>
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;">
            <input type="radio" name="status" value="coming_soon" id="edit_status_cs" onchange="toggleCsOptions(true)"> 🔒 Coming Soon
          </label>
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;">
            <input type="radio" name="status" value="hidden" id="edit_status_hidden" onchange="toggleCsOptions(false)"> Hidden
          </label>
        </div>
      </div>
      <div id="edit_cs_options" style="display:none;background:#fef9c3;border-radius:8px;padding:1rem;margin-bottom:1rem;">
        <div style="font-size:.75rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">🔒 Coming Soon Settings</div>
        <div style="margin-bottom:.75rem;">
          <label class="form-label">Teaser Text (optional)</label>
          <input type="text" name="coming_soon_teaser" id="edit_cs_teaser" class="form-input" placeholder="e.g. Launching this spring!" maxlength="200">
        </div>
        <div style="font-size:.75rem;font-weight:700;color:#374151;margin-bottom:.5rem;">Show to visitors:</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;font-size:.83rem;">
          <label style="display:flex;align-items:center;gap:.4rem;"><input type="checkbox" name="show_price" value="1" id="edit_show_price"> Price</label>
          <label style="display:flex;align-items:center;gap:.4rem;"><input type="checkbox" name="show_duration" value="1" id="edit_show_duration"> Duration</label>
          <label style="display:flex;align-items:center;gap:.4rem;"><input type="checkbox" name="show_features" value="1" id="edit_show_features"> Features</label>
          <label style="display:flex;align-items:center;gap:.4rem;"><input type="checkbox" name="show_description" value="1" id="edit_show_description"> Description</label>
        </div>
      </div>

      {{-- Discount --}}
      <div style="background:#f8fafc;border-radius:8px;padding:1rem;border:1.5px solid #e5eaf2;margin-bottom:1rem;">
        <div style="font-size:.75rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">⚡ Time-Limited Discount (optional)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
          <div>
            <label class="form-label">Discount Amount</label>
            <input type="number" name="discount_amount" id="edit_discount_amount" class="form-input" step="0.01" min="0" placeholder="e.g. 10">
          </div>
          <div>
            <label class="form-label">Type</label>
            <select name="discount_type" id="edit_discount_type" class="form-input">
              <option value="percent">% Percent</option>
              <option value="dollar">$ Dollar</option>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
          <div>
            <label class="form-label">Start Date (optional)</label>
            <input type="date" name="discount_starts_at" id="edit_discount_starts_at" class="form-input">
          </div>
          <div>
            <label class="form-label">End Date (optional)</label>
            <input type="date" name="discount_ends_at" id="edit_discount_ends_at" class="form-input">
          </div>
        </div>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:.5rem;">Leave amount blank to remove discount. Leave dates blank for an open-ended discount.</p>
      </div>

      <div style="display:flex;gap:.5rem;justify-content:flex-end;">
        <button type="button" onclick="closeModals()" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- Add Modal --}}
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;">New Package</div>
    <form method="POST" action="{{ route('admin.packages.store') }}">
      @csrf

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
        <div>
          <label class="form-label">Package Name *</label>
          <input type="text" name="name" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Slug *</label>
          <input type="text" name="slug" class="form-input" placeholder="e.g. private-lesson" required>
        </div>
      </div>

      <div style="margin-bottom:.75rem;">
        <label class="form-label">Description *</label>
        <textarea name="description" class="form-input" required></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
        <div>
          <label class="form-label">Price ($) *</label>
          <input type="number" name="price" class="form-input" step="0.01" min="0" required>
        </div>
        <div>
          <label class="form-label">Duration (minutes) *</label>
          <input type="number" name="duration_minutes" class="form-input" min="1" value="30" required>
        </div>
      </div>

      <div style="margin-bottom:.75rem;">
        <label class="form-label">Features (bullet points)</label>
        <div id="add_features_list">
          <div class="feature-item">
            <input type="text" name="features[]" class="feature-input" placeholder="e.g. 30 minutes of personalized instruction">
            <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:3px 8px;cursor:pointer;font-weight:700;">✕</button>
          </div>
        </div>
        <button type="button" onclick="addAddFeatureRow()" style="background:#f0f4ff;color:#1e40af;border:none;border-radius:6px;padding:4px 12px;font-size:.78rem;font-weight:700;cursor:pointer;margin-top:.4rem;">+ Add Feature</button>
      </div>

      <div style="margin-bottom:1rem;">
        <label style="font-size:.75rem;font-weight:700;color:#374151;display:block;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.05em;">Status</label>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;"><input type="radio" name="status" value="active" checked> Active</label>
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;"><input type="radio" name="status" value="coming_soon"> 🔒 Coming Soon</label>
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;"><input type="radio" name="status" value="hidden"> Hidden</label>
        </div>
      </div>

      {{-- Discount --}}
      <div style="background:#f8fafc;border-radius:8px;padding:1rem;border:1.5px solid #e5eaf2;margin-bottom:1rem;">
        <div style="font-size:.75rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">⚡ Time-Limited Discount (optional)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
          <div>
            <label class="form-label">Discount Amount</label>
            <input type="number" name="discount_amount" class="form-input" step="0.01" min="0" placeholder="e.g. 10">
          </div>
          <div>
            <label class="form-label">Type</label>
            <select name="discount_type" class="form-input">
              <option value="percent">% Percent</option>
              <option value="dollar">$ Dollar</option>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
          <div>
            <label class="form-label">Start Date (optional)</label>
            <input type="date" name="discount_starts_at" class="form-input">
          </div>
          <div>
            <label class="form-label">End Date (optional)</label>
            <input type="date" name="discount_ends_at" class="form-input">
          </div>
        </div>
        <p style="font-size:.72rem;color:#9ca3af;margin-top:.5rem;">Leave amount blank for no discount. Leave dates blank for open-ended.</p>
      </div>

      <div style="display:flex;gap:.5rem;justify-content:flex-end;">
        <button type="button" onclick="closeModals()" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Create Package</button>
      </div>
    </form>
  </div>
</div>

{{-- Service data for JS --}}
<script>
const services = @json($services->keyBy('id'));

function openEdit(id) {
  const s = services[id];
  document.getElementById('editForm').action = `/admin/packages/${id}`;
  document.getElementById('edit_name').value = s.name;
  document.getElementById('edit_slug').value = s.slug;
  document.getElementById('edit_description').value = s.description;
  document.getElementById('edit_price').value = s.price;
  document.getElementById('edit_duration').value = s.duration_minutes;
  document.getElementById('edit_discount_amount').value = s.discount_amount || '';
  document.getElementById('edit_discount_type').value = s.discount_type || 'percent';
  document.getElementById('edit_discount_starts_at').value = s.discount_starts_at || '';
  document.getElementById('edit_discount_ends_at').value = s.discount_ends_at || '';

  // Status radio
  const status = s.coming_soon ? 'coming_soon' : (s.is_active ? 'active' : 'hidden');
  document.querySelector(`input[name="status"][value="${status}"]`).checked = true;

  // Coming soon options
  document.getElementById('edit_cs_teaser').value = s.coming_soon_teaser || '';
  document.getElementById('edit_show_price').checked = !!s.show_price;
  document.getElementById('edit_show_duration').checked = !!s.show_duration;
  document.getElementById('edit_show_features').checked = !!s.show_features;
  document.getElementById('edit_show_description').checked = !!s.show_description;
  toggleCsOptions(status === 'coming_soon');

  // Populate features
  const list = document.getElementById('edit_features_list');
  list.innerHTML = '';
  const features = s.features || [];
  features.forEach(f => addFeatureRow(f));

  document.getElementById('editModal').style.display = 'flex';
}

function toggleCsOptions(show) {
  const el = document.getElementById('edit_cs_options');
  if (el) el.style.display = show ? 'block' : 'none';
  // Also wire the radio buttons
  document.querySelectorAll('input[name="status"]').forEach(r => {
    r.addEventListener('change', () => toggleCsOptions(r.value === 'coming_soon' && r.checked));
  });
}

function addFeatureRow(value = '') {
  const list = document.getElementById('edit_features_list');
  const div = document.createElement('div');
  div.className = 'feature-item';
  div.innerHTML = `<input type="text" name="features[]" class="feature-input" value="${value.replace(/"/g, '&quot;')}" placeholder="e.g. 30 minutes of personalized instruction">
    <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:3px 8px;cursor:pointer;font-weight:700;">✕</button>`;
  list.appendChild(div);
}

function addAddFeatureRow() {
  const list = document.getElementById('add_features_list');
  const div = document.createElement('div');
  div.className = 'feature-item';
  div.innerHTML = `<input type="text" name="features[]" class="feature-input" placeholder="e.g. 30 minutes of personalized instruction">
    <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:3px 8px;cursor:pointer;font-weight:700;">✕</button>`;
  list.appendChild(div);
}

function openAdd() {
  document.getElementById('addModal').style.display = 'flex';
}

function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
}

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
