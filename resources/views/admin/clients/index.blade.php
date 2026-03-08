@extends('layouts.admin')
@section('title', 'Clients — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.7rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:middle;}
  .tbl tr:hover td{background:#fafafa;}
  .search-input{padding:.55rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.88rem;width:100%;max-width:300px;transition:border .15s;}
  .search-input:focus{outline:none;border-color:var(--navy);}
  .student-chip{display:inline-block;background:#dbeafe;color:#1e40af;border-radius:12px;padding:1px 8px;font-size:.68rem;font-weight:600;margin:1px;}
  .btn-create{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.55rem 1.3rem;font-weight:700;font-size:.86rem;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;}
  .btn-create:hover{background:var(--red);}
  .btn-sm{padding:3px 10px;border-radius:5px;font-size:.72rem;font-weight:600;cursor:pointer;border:none;}
  .btn-edit{background:#f3f4f6;color:#374151;} .btn-edit:hover{background:#e5e7eb;}
  .btn-link{background:#dbeafe;color:#1e40af;} .btn-link:hover{background:#bfdbfe;}
  .btn-danger{background:#fee2e2;color:#991b1b;} .btn-danger:hover{background:#fecaca;}

  .orphan-card{background:#fff;border:1.5px solid #fecaca;border-left:4px solid #ef4444;border-radius:8px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}

  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
  .modal-overlay.open{display:flex;}
  .modal-box{background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:460px;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,31,.2);}
  .modal-title{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);margin-bottom:1.1rem;}
  .mfg{margin-bottom:.85rem;}
  .mfg label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;}
  .mfg input,.mfg select,.mfg textarea{width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.87rem;}
  .modal-actions{display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;}
  .btn-primary{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.55rem 1.4rem;font-weight:700;cursor:pointer;font-size:.87rem;}
  .btn-ghost{background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;font-size:.87rem;}
</style>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">
  @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
</div>
@endif

<div class="flex justify-between items-center mb-5" style="flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Clients</h1>
    <p class="text-gray-500 text-sm">{{ $clients->total() }} registered · {{ $guestCount }} guest · {{ $orphanedStudents->count() }} orphaned student(s)</p>
  </div>
  <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
    <a href="{{ route('admin.export.clients') }}" class="text-sm text-blue-700 hover:underline">Export CSV →</a>
    <a href="{{ route('admin.students.index') }}" class="text-sm text-blue-700 hover:underline">All Students →</a>
    <button class="btn-create" onclick="openCreateModal()">+ Create Client</button>
  </div>
</div>

<form method="GET" class="mb-4">
  <input type="text" name="q" value="{{ $search }}" placeholder="Search name or email…" class="search-input" onchange="this.form.submit()">
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Name</th><th>Email</th><th>Phone</th><th>Students</th><th>Bookings</th><th>Total Paid</th><th>Since</th><th></th>
    </tr></thead>
    <tbody>
    @forelse($clients as $client)
    <tr>
      <td>
        <div class="font-semibold text-gray-900">{{ $client->full_name }}</div>
        @if($client->notes)<div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($client->notes,40) }}</div>@endif
      </td>
      <td class="text-gray-500">{{ $client->email }}</td>
      <td class="text-gray-500">{{ \App\Http\Controllers\Admin\ClientController::displayPhone($client->phone) ?? '—' }}</td>
      <td>
        @forelse($client->students as $s)
          <span class="student-chip">{{ $s->first_name }}</span>
        @empty
          <span style="color:#d1d5db;font-size:.78rem;">none</span>
        @endforelse
      </td>
      <td class="text-center">
        <span style="background:#dbeafe;color:#1e40af;font-weight:700;font-size:.72rem;padding:2px 8px;border-radius:10px;">{{ $client->bookings_count }}</span>
      </td>
      <td class="font-semibold">${{ number_format($client->bookings_sum_price_paid ?? 0, 0) }}</td>
      <td class="text-gray-400 text-xs">{{ $client->created_at->format('M j, Y') }}</td>
      <td style="white-space:nowrap;">
        <button class="btn-sm btn-edit" onclick="openEditModal({{ $client->id }}, '{{ addslashes($client->first_name) }}', '{{ addslashes($client->last_name ?? '') }}', '{{ $client->email }}', '{{ \App\Http\Controllers\Admin\ClientController::displayPhone($client->phone) ?? '' }}', '{{ addslashes($client->notes ?? '') }}')">✏</button>
        <a href="{{ route('admin.clients.show', $client) }}" class="btn-sm btn-link" style="text-decoration:none;margin-left:2px;">View →</a>
        @if($client->bookings_count === 0)
        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" style="display:inline;margin-left:2px;" onsubmit="return confirm('Delete {{ addslashes($client->full_name) }}? This cannot be undone.')">
          @csrf @method('DELETE')
          <button type="submit" class="btn-sm btn-danger">✕</button>
        </form>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-10 text-gray-400">No clients found.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $clients->appends(['q'=>$search])->links() }}</div>
</div>

{{-- Orphaned students --}}
@if($orphanedStudents->isNotEmpty())
<div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:#ef4444;margin-bottom:.75rem;">⚠ Orphaned Students ({{ $orphanedStudents->count() }})</div>
<p style="color:#6b7280;font-size:.83rem;margin-bottom:1rem;">These students have no linked parent/client account.</p>
@foreach($orphanedStudents as $student)
<div class="orphan-card">
  <div style="flex:1;">
    <div style="font-weight:700;color:var(--navy);">{{ $student->full_name }}</div>
    <div style="font-size:.75rem;color:#6b7280;">Age: {{ $student->age ?? '?' }} @if($student->aliases->isNotEmpty()) · Aliases: {{ $student->aliases->pluck('alias')->join(', ') }}@endif</div>
  </div>
  <form method="POST" action="{{ route('admin.clients.link-student') }}" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <select name="client_id" required style="border:1.5px solid #dbe4ff;border-radius:6px;padding:5px 8px;font-size:.82rem;">
      <option value="">— Link to client —</option>
      @foreach($clients->getCollection() as $c)
      <option value="{{ $c->id }}">{{ $c->full_name }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn-sm btn-link">Link</button>
  </form>
  <button class="btn-sm btn-edit" onclick="openCreateModalForStudent({{ $student->id }})">+ New Client</button>
</div>
@endforeach
@endif

{{-- CREATE MODAL --}}
<div class="modal-overlay" id="createModal">
  <div class="modal-box">
    <div class="modal-title">Create Client</div>
    <form method="POST" action="{{ route('admin.clients.store') }}">
      @csrf
      <input type="hidden" name="link_student_id" id="create-link-student-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="create-first" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name"></div>
      </div>
      <div class="mfg"><label>Email *</label><input type="email" name="email" required></div>
      <div class="mfg"><label>Phone</label><input type="tel" name="phone" id="create-phone" placeholder="(314) 555-0000"></div>
      <div class="mfg"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
      <div id="create-link-notice" style="display:none;background:#dbeafe;color:#1e40af;padding:.5rem .75rem;border-radius:6px;font-size:.78rem;margin-bottom:.75rem;">Will link orphaned student to this new client.</div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Create Client</button>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-title">Edit Client</div>
    <form method="POST" id="editForm">
      @csrf @method('PATCH')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="edit-first" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name" id="edit-last"></div>
      </div>
      <div class="mfg"><label>Email *</label><input type="email" name="email" id="edit-email" required></div>
      <div class="mfg"><label>Phone</label><input type="tel" name="phone" id="edit-phone" placeholder="(314) 555-0000"></div>
      <div class="mfg"><label>Notes</label><textarea name="notes" id="edit-notes" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function formatPhone(input) {
  let v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0) v = '(' + v;
  input.value = v;
}
document.querySelectorAll('input[type=tel]').forEach(i => i.addEventListener('input', () => formatPhone(i)));

function openCreateModal() {
  document.getElementById('create-link-student-id').value = '';
  document.getElementById('create-link-notice').style.display = 'none';
  document.getElementById('createModal').classList.add('open');
}
function openCreateModalForStudent(studentId) {
  document.getElementById('create-link-student-id').value = studentId;
  document.getElementById('create-link-notice').style.display = 'block';
  document.getElementById('createModal').classList.add('open');
}
function openEditModal(id, first, last, email, phone, notes) {
  document.getElementById('editForm').action = `/admin/clients/${id}`;
  document.getElementById('edit-first').value = first;
  document.getElementById('edit-last').value  = last;
  document.getElementById('edit-email').value = email;
  document.getElementById('edit-phone').value = phone;
  document.getElementById('edit-notes').value = notes;
  document.getElementById('editModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModals(); });
});
</script>
@endsection
