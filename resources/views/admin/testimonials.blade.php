@extends('layouts.admin')
@section('title', 'Testimonials — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.85rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:top;}
  .tbl tr:hover td{background:#fafafa;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;}
  .pill-gray{background:#f3f4f6;color:#6b7280;}
  .btn-sm{padding:4px 10px;border-radius:6px;font-size:.73rem;font-weight:600;cursor:pointer;border:none;}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
  .modal-box{background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:540px;box-shadow:0 20px 60px rgba(0,31,91,.2);}
  .form-label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;}
  .form-input{width:100%;border:1.5px solid #e5eaf2;border-radius:7px;padding:.55rem .85rem;font-size:.87rem;margin-bottom:.85rem;}
  .form-input:focus{outline:none;border-color:var(--navy);}
  textarea.form-input{min-height:100px;resize:vertical;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Testimonials</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">{{ $testimonials->where('is_active',true)->count() }} active · {{ $testimonials->count() }} total</p>
  </div>
  <button onclick="openAdd()" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer;">+ Add Testimonial</button>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th style="width:40px;">#</th>
      <th>Quote</th>
      <th>Author</th>
      <th>Type</th>
      <th>Status</th>
      <th>Order</th>
      <th></th>
    </tr></thead>
    <tbody>
    @forelse($testimonials as $t)
    <tr style="{{ !$t->is_active ? 'opacity:.5;' : '' }}">
      <td style="color:#9ca3af;font-size:.75rem;">{{ $t->id }}</td>
      <td style="max-width:380px;">
        <p style="margin:0;color:#374151;font-style:italic;line-height:1.5;">"{{ Str::limit($t->quote, 120) }}"</p>
      </td>
      <td>
        <div style="font-weight:700;color:var(--navy);">{{ $t->author }}</div>
        @if($t->author_detail)
          <div style="font-size:.72rem;color:#9ca3af;">{{ $t->author_detail }}</div>
        @endif
      </td>
      <td>
        @if($t->source_type)
          <span class="pill pill-blue" style="background:#dbeafe;color:#1e40af;">{{ App\Models\Testimonial::sourceTypes()[$t->source_type] ?? $t->source_type }}</span>
        @else
          <span style="color:#9ca3af;font-size:.75rem;">—</span>
        @endif
      </td>
      <td>
        @if($t->is_active)
          <span class="pill pill-green">✓ Active</span>
        @else
          <span class="pill pill-gray">Hidden</span>
        @endif
      </td>
      <td style="color:#9ca3af;font-size:.82rem;">{{ $t->sort_order }}</td>
      <td style="white-space:nowrap;">
        <button class="btn-sm" style="background:#dbeafe;color:#1e40af;margin-right:3px;"
          onclick="openEdit({{ $t->id }}, {{ json_encode($t->quote) }}, {{ json_encode($t->author) }}, {{ json_encode($t->author_detail) }}, {{ $t->is_active ? 'true' : 'false' }}, {{ $t->sort_order }}, {{ json_encode($t->source_type) }}, {{ json_encode($t->client_id) }})">
          Edit
        </button>
        <form method="POST" action="{{ route('admin.testimonials.toggle', $t) }}" style="display:inline;">
          @csrf @method('PATCH')
          <button type="submit" class="btn-sm" style="background:{{ $t->is_active ? '#fef3c7' : '#d1fae5' }};color:{{ $t->is_active ? '#92400e' : '#065f46' }};margin-right:3px;">
            {{ $t->is_active ? 'Hide' : 'Show' }}
          </button>
        </form>
        <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" style="display:inline;"
              onsubmit="return confirm('Delete this testimonial?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn-sm" style="background:#fee2e2;color:#991b1b;">Delete</button>
        </form>
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center py-10 text-gray-400">No testimonials yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

{{-- Add modal --}}
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;">Add Testimonial</div>
    <form method="POST" action="{{ route('admin.testimonials.store') }}">
      @csrf
      <label class="form-label">Quote *</label>
      <textarea name="quote" class="form-input" placeholder="The testimonial text..." required></textarea>
      <label class="form-label">Author *</label>
      <input type="text" name="author" class="form-input" placeholder="e.g. Chad C." required>
      <label class="form-label">Author Detail (optional)</label>
      <input type="text" name="author_detail" class="form-input" placeholder="e.g. Parent of a 9-year-old">
      <label class="form-label">Type</label>
      <select name="source_type" class="form-input">
        <option value="">— Not specified —</option>
        @foreach(App\Models\Testimonial::sourceTypes() as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>
      <label class="form-label">Link to Client (optional)</label>
      <select name="client_id" class="form-input">
        <option value="">— None —</option>
        @foreach($clients as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email }})</option>
        @endforeach
      </select>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        <div>
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-input" value="0">
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem;">
          <input type="checkbox" name="is_active" value="1" checked id="add_active">
          <label for="add_active" style="font-size:.85rem;color:#374151;">Show on home page</label>
        </div>
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.5rem;">
        <button type="button" onclick="closeModals()" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Add</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit modal --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;">Edit Testimonial</div>
    <form method="POST" id="editForm" action="">
      @csrf @method('PATCH')
      <label class="form-label">Quote *</label>
      <textarea name="quote" id="edit_quote" class="form-input" required></textarea>
      <label class="form-label">Author *</label>
      <input type="text" name="author" id="edit_author" class="form-input" required>
      <label class="form-label">Author Detail (optional)</label>
      <input type="text" name="author_detail" id="edit_author_detail" class="form-input">
      <label class="form-label">Type</label>
      <select name="source_type" id="edit_source_type" class="form-input">
        <option value="">— Not specified —</option>
        @foreach(App\Models\Testimonial::sourceTypes() as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>
      <label class="form-label">Link to Client (optional)</label>
      <select name="client_id" id="edit_client_id" class="form-input">
        <option value="">— None —</option>
        @foreach($clients as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->email }})</option>
        @endforeach
      </select>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        <div>
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" id="edit_sort_order" class="form-input">
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem;">
          <input type="checkbox" name="is_active" value="1" id="edit_active">
          <label for="edit_active" style="font-size:.85rem;color:#374151;">Show on home page</label>
        </div>
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.5rem;">
        <button type="button" onclick="closeModals()" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAdd() {
  document.getElementById('addModal').style.display = 'flex';
}
function openEdit(id, quote, author, detail, active, order, sourceType, clientId) {
  document.getElementById('editForm').action = `/admin/testimonials/${id}`;
  document.getElementById('edit_quote').value = quote;
  document.getElementById('edit_author').value = author;
  document.getElementById('edit_author_detail').value = detail || '';
  document.getElementById('edit_active').checked = active;
  document.getElementById('edit_sort_order').value = order;
  document.getElementById('edit_source_type').value = sourceType || '';
  document.getElementById('edit_client_id').value = clientId || '';
  document.getElementById('editModal').style.display = 'flex';
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
