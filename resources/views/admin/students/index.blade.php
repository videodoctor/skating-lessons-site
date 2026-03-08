@extends('layouts.admin')
@section('title', 'Students — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.7rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:middle;}
  .tbl tr:hover td{background:#fafafa;}
  .search-input{padding:.55rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.88rem;width:100%;max-width:300px;}
  .search-input:focus{outline:none;border-color:var(--navy);}
  .alias-chip{display:inline-block;background:#f3f4f6;color:#374151;border-radius:10px;padding:1px 7px;font-size:.68rem;margin:1px;}
  .skill-badge{padding:2px 8px;border-radius:10px;font-size:.68rem;font-weight:700;}
  .skill-beginner{background:#dcfce7;color:#166534;}
  .skill-intermediate{background:#dbeafe;color:#1e40af;}
  .skill-advanced{background:#fef3c7;color:#92400e;}
  .btn-sm{padding:3px 10px;border-radius:5px;font-size:.72rem;font-weight:600;cursor:pointer;border:none;}
  .btn-edit{background:#f3f4f6;color:#374151;}.btn-edit:hover{background:#e5e7eb;}
  .btn-danger{background:#fee2e2;color:#991b1b;}.btn-danger:hover{background:#fecaca;}
  .btn-create{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.55rem 1.3rem;font-weight:700;font-size:.86rem;cursor:pointer;}
  .btn-create:hover{background:var(--red);}
  .no-client{color:#ef4444;font-size:.72rem;font-weight:600;}

  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
  .modal-overlay.open{display:flex;}
  .modal-box{background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:460px;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,31,.2);}
  .modal-title{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);margin-bottom:1.1rem;}
  .mfg{margin-bottom:.85rem;}
  .mfg label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;}
  .mfg input,.mfg select,.mfg textarea{width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.87rem;}
  .modal-actions{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;}
  .btn-primary{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;}
  .btn-ghost{background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;}
</style>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">
  @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
</div>
@endif

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Students</h1>
    <p class="text-gray-500 text-sm">{{ $students->total() }} total</p>
  </div>
  <div style="display:flex;gap:.6rem;align-items:center;">
    <a href="{{ route('admin.clients.index') }}" class="text-sm text-blue-700 hover:underline">← All Clients</a>
    <button class="btn-create" onclick="openCreateModal()">+ Add Student</button>
  </div>
</div>

<form method="GET" class="mb-4">
  <input type="text" name="q" value="{{ $search }}" placeholder="Search by name…" class="search-input" onchange="this.form.submit()">
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Student</th>
      <th>Age</th>
      <th>Skill</th>
      <th>Parent / Client</th>
      <th>Aliases</th>
      <th>Lessons</th>
      <th>Active</th>
      <th></th>
    </tr></thead>
    <tbody>
    @forelse($students as $student)
    <tr>
      <td>
        <div class="font-semibold" style="color:var(--navy);">{{ $student->full_name }}</div>
        @if($student->notes)<div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($student->notes,40) }}</div>@endif
      </td>
      <td>{{ $student->age ?? '—' }}</td>
      <td>
        @if($student->skill_level)
          <span class="skill-badge skill-{{ $student->skill_level }}">{{ ucfirst($student->skill_level) }}</span>
        @else —
        @endif
      </td>
      <td>
        @if($student->client)
          <a href="{{ route('admin.clients.show', $student->client) }}" style="color:var(--navy);font-weight:600;font-size:.85rem;text-decoration:none;">{{ $student->client->full_name }}</a>
        @else
          <span class="no-client">⚠ No client</span>
        @endif
      </td>
      <td>
        @foreach($student->aliases as $alias)
          <span class="alias-chip">{{ $alias->alias }}</span>
        @endforeach
      </td>
      <td class="text-center">
        <span style="background:#dbeafe;color:#1e40af;font-weight:700;font-size:.72rem;padding:2px 8px;border-radius:10px;">{{ $student->bookings_count }}</span>
      </td>
      <td>
        @if($student->is_active)
          <span style="color:#065f46;font-size:.75rem;font-weight:700;">✓ Active</span>
        @else
          <span style="color:#9ca3af;font-size:.75rem;">Inactive</span>
        @endif
      </td>
      <td style="white-space:nowrap;">
        <button class="btn-sm btn-edit"
          onclick="openEditModal({{ $student->id }},'{{ addslashes($student->first_name) }}','{{ addslashes($student->last_name ?? '') }}','{{ $student->client_id }}','{{ $student->age }}','{{ $student->skill_level }}','{{ addslashes($student->notes ?? '') }}','{{ $student->is_active ? 1 : 0 }}')">
          ✏ Edit
        </button>
        @if($student->bookings_count === 0)
        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" style="display:inline;margin-left:2px;"
              onsubmit="return confirm('Delete {{ addslashes($student->full_name) }}?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn-sm btn-danger">✕</button>
        </form>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-10 text-gray-400">No students found.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $students->appends(['q'=>$search])->links() }}</div>
</div>

{{-- CREATE MODAL --}}
<div class="modal-overlay" id="createModal">
  <div class="modal-box">
    <div class="modal-title">Add Student</div>
    <form method="POST" action="{{ route('admin.students.store') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name"></div>
      </div>
      <div class="mfg"><label>Parent / Client</label>
        <select name="client_id">
          <option value="">— No client yet —</option>
          @foreach($clients as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>Age</label><input type="number" name="age" min="3" max="80"></div>
        <div class="mfg"><label>Skill Level</label>
          <select name="skill_level">
            <option value="">— Select —</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
        </div>
      </div>
      <div class="mfg"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Add Student</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Student</div>
    <form method="POST" id="editForm">
      @csrf @method('PATCH')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="e-first" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name" id="e-last"></div>
      </div>
      <div class="mfg"><label>Parent / Client</label>
        <select name="client_id" id="e-client">
          <option value="">— No client —</option>
          @foreach($clients as $c)
          <option value="{{ $c->id }}">{{ $c->full_name }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>Age</label><input type="number" name="age" id="e-age" min="3" max="80"></div>
        <div class="mfg"><label>Skill Level</label>
          <select name="skill_level" id="e-skill">
            <option value="">— Select —</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
        </div>
      </div>
      <div class="mfg"><label>Notes</label><textarea name="notes" id="e-notes" rows="2"></textarea></div>
      <div class="mfg">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
          <input type="checkbox" name="is_active" id="e-active" value="1"> Active
        </label>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCreateModal() { document.getElementById('createModal').classList.add('open'); }
function openEditModal(id,first,last,clientId,age,skill,notes,active) {
  document.getElementById('editForm').action=`/admin/students/${id}`;
  document.getElementById('e-first').value=first;
  document.getElementById('e-last').value=last;
  document.getElementById('e-client').value=clientId||'';
  document.getElementById('e-age').value=age||'';
  document.getElementById('e-skill').value=skill||'';
  document.getElementById('e-notes').value=notes;
  document.getElementById('e-active').checked=active=='1';
  document.getElementById('editModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',e=>{if(e.target===m)closeModals();});
});
</script>
@endsection
