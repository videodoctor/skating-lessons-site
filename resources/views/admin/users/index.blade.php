@extends('layouts.admin')
@section('title', 'Admin Users')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .user-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem;margin-bottom:1rem;}
  .user-card-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;}
  .user-avatar{width:44px;height:44px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;font-family:'Bebas Neue',sans-serif;}
  .user-info{flex:1;margin-left:1rem;}
  .user-name{font-weight:700;font-size:1rem;color:#111827;}
  .user-email{font-size:.85rem;color:#6b7280;}
  .user-meta{font-size:.75rem;color:#9ca3af;margin-top:2px;}
  .badge-you{background:#dbeafe;color:#1e40af;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:10px;margin-left:.5rem;}
  .form-row{display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;}
  .form-group{flex:1;min-width:150px;}
  .form-group label{display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px;}
  .form-group input{width:100%;border:1.5px solid #dbe4ff;border-radius:6px;padding:6px 10px;font-size:.88rem;}
  .btn-sm{padding:6px 14px;border-radius:6px;font-size:.82rem;font-weight:600;border:none;cursor:pointer;}
  .btn-navy{background:var(--navy);color:#fff;} .btn-navy:hover{background:var(--red);}
  .btn-outline{background:#fff;color:#374151;border:1.5px solid #e5eaf2;} .btn-outline:hover{background:#f3f4f6;}
  .btn-danger-sm{background:#fee2e2;color:#991b1b;border:1.5px solid #fca5a5;} .btn-danger-sm:hover{background:#fecaca;}

  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
  .modal-overlay.open{display:flex;}
  .modal-box{background:#fff;border-radius:14px;padding:1.75rem;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,31,.2);}
  .modal-title{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Admin Users</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">Manage who has access to the admin panel</p>
  </div>
  <button onclick="document.getElementById('addModal').classList.add('open')" class="btn-sm btn-navy">+ Add Admin</button>
</div>

@foreach($admins as $admin)
<div class="user-card">
  <div class="user-card-header">
    <div style="display:flex;align-items:center;">
      <div class="user-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
      <div class="user-info">
        <div class="user-name">
          {{ $admin->name }}
          @if($admin->id === auth()->id())
            <span class="badge-you">You</span>
          @endif
        </div>
        <div class="user-email">{{ $admin->email }}</div>
        <div class="user-meta">
          Joined {{ $admin->created_at->format('M j, Y') }}
          @if($admin->email_verified_at) · Verified @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Edit Name/Email --}}
  <form method="POST" action="{{ route('admin.users.update', $admin) }}" style="margin-bottom:.75rem;">
    @csrf @method('PATCH')
    <div class="form-row">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="{{ $admin->name }}" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ $admin->email }}" required>
      </div>
      <button type="submit" class="btn-sm btn-outline">Save</button>
    </div>
  </form>

  {{-- Reset Password --}}
  <form method="POST" action="{{ route('admin.users.reset-password', $admin) }}">
    @csrf
    <div class="form-row">
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="password" minlength="8" required placeholder="Min 8 characters">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" minlength="8" required>
      </div>
      <button type="submit" class="btn-sm btn-outline" onclick="return confirm('Reset password for {{ addslashes($admin->name) }}?')">Reset Password</button>
    </div>
  </form>

  {{-- Delete (not self) --}}
  @if($admin->id !== auth()->id())
  <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid #f3f4f6;">
    <form method="POST" action="{{ route('admin.users.destroy', $admin) }}" onsubmit="return confirm('Remove {{ addslashes($admin->name) }} as admin? This cannot be undone.')">
      @csrf @method('DELETE')
      <button type="submit" class="btn-sm btn-danger-sm">Remove Admin</button>
    </form>
  </div>
  @endif
</div>
@endforeach

{{-- Add Admin Modal --}}
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-title">Add Admin User</div>
    <form method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div style="margin-bottom:.75rem;">
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Name</label>
        <input type="text" name="name" required style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
      </div>
      <div style="margin-bottom:.75rem;">
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Email</label>
        <input type="email" name="email" required style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Password</label>
        <input type="password" name="password" required minlength="8" style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
      </div>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('addModal').classList.remove('open')" style="background:#f3f4f6;color:#374151;border:none;border-radius:7px;padding:.55rem 1.2rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn-sm btn-navy" style="padding:.55rem 1.4rem;">Create Admin</button>
      </div>
    </form>
  </div>
</div>
@endsection
