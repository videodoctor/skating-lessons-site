@extends('layouts.admin')
@section('title', $client->full_name . ' — Client')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .stat-card{background:#fff;border-radius:10px;border:1.5px solid #e5eaf2;padding:1.25rem;}
  .stat-num{font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:var(--navy);line-height:1;}
  .stat-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-top:2px;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.7rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:middle;}
  .section-head{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin-bottom:.75rem;}
  .student-card{background:#fff;border:1.5px solid #e5eaf2;border-left:4px solid var(--gold);border-radius:10px;padding:1rem 1.25rem;margin-bottom:.6rem;}
  .alias-chip{display:inline-block;background:#f3f4f6;color:#374151;border-radius:10px;padding:1px 8px;font-size:.7rem;margin:1px;}
  .skill-badge{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .skill-beginner{background:#dcfce7;color:#166534;}
  .skill-intermediate{background:#dbeafe;color:#1e40af;}
  .skill-advanced{background:#fef3c7;color:#92400e;}
  .btn-sm{padding:3px 10px;border-radius:5px;font-size:.72rem;font-weight:600;cursor:pointer;border:none;}
  .btn-edit{background:#f3f4f6;color:#374151;} .btn-edit:hover{background:#e5e7eb;}
  .btn-danger{background:#fee2e2;color:#991b1b;} .btn-danger:hover{background:#fecaca;}
  .btn-blue{background:#dbeafe;color:#1e40af;} .btn-blue:hover{background:#bfdbfe;}

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

<div class="mb-4">
  <a href="{{ route('admin.clients.index') }}" style="color:#9ca3af;font-size:.82rem;text-decoration:none;">← All Clients</a>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
<div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">
  @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
</div>
@endif

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">{{ $client->full_name }}</h1>
    <div style="font-size:.88rem;color:#6b7280;margin-top:3px;">
      {{ $client->email }}
      @if($client->phone) · {{ \App\Http\Controllers\Admin\ClientController::displayPhone($client->phone) }}@endif
    </div>
    <div style="font-size:.75rem;color:#9ca3af;margin-top:2px;">Member since {{ $client->created_at->format('M j, Y') }}</div>
    @if($client->sms_consent)<div style="font-size:.72rem;color:#065f46;margin-top:3px;">✓ SMS reminders enabled</div>@endif
  </div>
  <button class="btn-sm btn-edit" style="padding:6px 14px;font-size:.82rem;"
    onclick="openEditModal('{{ addslashes($client->first_name) }}','{{ addslashes($client->last_name ?? '') }}','{{ $client->email }}','{{ \App\Http\Controllers\Admin\ClientController::displayPhone($client->phone) ?? '' }}','{{ addslashes($client->notes ?? '') }}')">
    ✏ Edit Client
  </button>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="stat-card"><div class="stat-num">{{ $bookings->count() }}</div><div class="stat-label">Total Bookings</div></div>
  <div class="stat-card"><div class="stat-num">${{ number_format($bookings->sum('price_paid'), 0) }}</div><div class="stat-label">Total Paid</div></div>
  <div class="stat-card"><div class="stat-num">{{ $students->count() }}</div><div class="stat-label">Students</div></div>
</div>

{{-- Students section --}}
<div class="section-head">⛸️ Students / Children</div>

@forelse($students as $student)
<div class="student-card">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <div>
      <span style="font-weight:700;color:var(--navy);font-size:.95rem;">{{ $student->full_name }}</span>
      @if($student->age)<span style="color:#6b7280;font-size:.78rem;margin-left:.5rem;">Age {{ $student->age }}</span>@endif
      @if($student->skill_level)
        <span class="skill-badge skill-{{ $student->skill_level }}" style="margin-left:.4rem;">{{ ucfirst($student->skill_level) }}</span>
      @endif
      <span style="background:#dbeafe;color:#1e40af;font-size:.68rem;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:.4rem;">{{ $student->bookings_count ?? 0 }} lessons</span>
    </div>
    <div style="display:flex;gap:.4rem;">
      <button class="btn-sm btn-edit" onclick="openEditStudentModal({{ $student->id }},'{{ addslashes($student->first_name) }}','{{ addslashes($student->last_name ?? '') }}','{{ $student->age }}','{{ $student->skill_level }}','{{ addslashes($student->notes ?? '') }}')">✏ Edit</button>
      <form method="POST" action="{{ route('admin.clients.unlink-student', [$client, $student]) }}" style="display:inline" onsubmit="return confirm('Unlink {{ $student->first_name }} from {{ $client->first_name }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-sm btn-danger">Unlink</button>
      </form>
    </div>
  </div>
  @if($student->notes)<div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;">{{ $student->notes }}</div>@endif
  @if($student->aliases->isNotEmpty())
  <div style="margin-top:.4rem;">
    <span style="font-size:.7rem;color:#9ca3af;">Aliases: </span>
    @foreach($student->aliases as $alias)
    <span class="alias-chip">{{ $alias->alias }}
      <form method="POST" action="{{ route('admin.students.remove-alias', [$student, $alias]) }}" style="display:inline">
        @csrf @method('DELETE')
        <button type="submit" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:.65rem;padding:0 0 0 2px;">✕</button>
      </form>
    </span>
    @endforeach
  </div>
  @endif
  {{-- Add alias inline --}}
  <form method="POST" action="{{ route('admin.students.add-alias', $student) }}" style="display:flex;gap:.4rem;margin-top:.5rem;align-items:center;">
    @csrf
    <input type="text" name="alias" placeholder="Add alias…" style="border:1px solid #e5e7eb;border-radius:5px;padding:3px 8px;font-size:.75rem;width:130px;">
    <button type="submit" class="btn-sm btn-blue" style="font-size:.68rem;">+ Alias</button>
  </form>
</div>
@empty
<div style="color:#9ca3af;font-size:.88rem;margin-bottom:1rem;">No students linked yet.</div>
@endforelse

{{-- Add student button --}}
<button class="btn-sm btn-blue" style="padding:6px 14px;font-size:.82rem;margin-bottom:2rem;" onclick="openAddStudentModal()">+ Add Student</button>

{{-- Booking history --}}
<div class="section-head">📋 Booking History</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Date</th><th>Time</th><th>Student</th><th>Service</th><th>Rink</th><th>Price</th><th>Payment</th><th>Status</th>
    </tr></thead>
    <tbody>
    @forelse($bookings as $b)
    <tr>
      <td class="font-semibold">{{ \Carbon\Carbon::parse($b->date ?? $b->timeSlot?->date)->format('M j, Y') }}</td>
      <td>{{ \Carbon\Carbon::parse($b->start_time ?? $b->timeSlot?->start_time)->format('g:i A') }}</td>
      <td style="color:var(--navy);font-weight:600;">{{ $b->student?->first_name ?? '—' }}</td>
      <td>{{ $b->service->name ?? '—' }}</td>
      <td style="color:#9ca3af;font-size:.78rem;">{{ $b->timeSlot?->rink?->name ?? '—' }}</td>
      <td>${{ number_format($b->price_paid, 0) }}</td>
      <td>
        @if($b->payment_status === 'paid')
          <span style="font-size:.7rem;font-weight:700;color:#065f46;">{{ $b->payment_type === 'cash' ? '💵 Cash' : '💜 Venmo' }}</span>
        @else
          <span style="font-size:.7rem;color:#9ca3af;">Unpaid</span>
        @endif
      </td>
      <td>
        @if($b->status==='confirmed')<span style="font-size:.7rem;font-weight:700;color:#065f46;background:#d1fae5;padding:2px 8px;border-radius:10px;">Confirmed</span>
        @elseif($b->status==='pending')<span style="font-size:.7rem;font-weight:700;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:10px;">Pending</span>
        @else<span style="font-size:.7rem;font-weight:700;color:#6b7280;background:#f3f4f6;padding:2px 8px;border-radius:10px;">{{ ucfirst($b->status) }}</span>@endif
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center py-8 text-gray-400">No bookings.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

{{-- EDIT CLIENT MODAL --}}
<div class="modal-overlay" id="editClientModal">
  <div class="modal-box">
    <div class="modal-title">Edit Client</div>
    <form method="POST" action="{{ route('admin.clients.update', $client) }}">
      @csrf @method('PATCH')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="ec-first" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name" id="ec-last"></div>
      </div>
      <div class="mfg"><label>Email *</label><input type="email" name="email" id="ec-email" required></div>
      <div class="mfg"><label>Phone</label><input type="tel" name="phone" id="ec-phone" placeholder="(314) 555-0000"></div>
      <div class="mfg"><label>Notes</label><textarea name="notes" id="ec-notes" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- ADD STUDENT MODAL --}}
<div class="modal-overlay" id="addStudentModal">
  <div class="modal-box">
    <div class="modal-title">Add Student</div>
    <form method="POST" action="{{ route('admin.clients.add-student', $client) }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name"></div>
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

{{-- EDIT STUDENT MODAL --}}
<div class="modal-overlay" id="editStudentModal">
  <div class="modal-box">
    <div class="modal-title">Edit Student</div>
    <form method="POST" id="editStudentForm">
      @csrf @method('PATCH')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>First Name *</label><input type="text" name="first_name" id="es-first" required></div>
        <div class="mfg"><label>Last Name</label><input type="text" name="last_name" id="es-last"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
        <div class="mfg"><label>Age</label><input type="number" name="age" id="es-age" min="3" max="80"></div>
        <div class="mfg"><label>Skill Level</label>
          <select name="skill_level" id="es-skill">
            <option value="">— Select —</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
        </div>
      </div>
      <div class="mfg"><label>Notes</label><textarea name="notes" id="es-notes" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-ghost" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn-primary">Save Student</button>
      </div>
    </form>
  </div>
</div>

<script>
function formatPhone(input) {
  let v = input.value.replace(/\D/g,'').substring(0,10);
  if(v.length>=6) v='('+v.substring(0,3)+') '+v.substring(3,6)+'-'+v.substring(6);
  else if(v.length>=3) v='('+v.substring(0,3)+') '+v.substring(3);
  else if(v.length>0) v='('+v;
  input.value=v;
}
document.querySelectorAll('input[type=tel]').forEach(i=>i.addEventListener('input',()=>formatPhone(i)));

function openEditModal(first,last,email,phone,notes) {
  document.getElementById('ec-first').value=first;
  document.getElementById('ec-last').value=last;
  document.getElementById('ec-email').value=email;
  document.getElementById('ec-phone').value=phone;
  document.getElementById('ec-notes').value=notes;
  document.getElementById('editClientModal').classList.add('open');
}
function openAddStudentModal() {
  document.getElementById('addStudentModal').classList.add('open');
}
function openEditStudentModal(id,first,last,age,skill,notes) {
  document.getElementById('editStudentForm').action=`/admin/clients/{{ $client->id }}/students/${id}`;
  document.getElementById('es-first').value=first;
  document.getElementById('es-last').value=last;
  document.getElementById('es-age').value=age||'';
  document.getElementById('es-skill').value=skill||'';
  document.getElementById('es-notes').value=notes;
  document.getElementById('editStudentModal').classList.add('open');
}
function closeModals() {
  document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('open'));
}
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',e=>{if(e.target===m)closeModals();});
});
</script>
@endsection
