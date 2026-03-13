@extends('layouts.app')
@section('title', 'Verify Phone — Kristine Skates')
@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
  <div style="background:#fff;border-radius:14px;box-shadow:0 8px 40px rgba(0,31,91,.12);padding:2.5rem;width:100%;max-width:400px;">
    <div style="text-align:center;margin-bottom:1.5rem;">
      <div style="font-size:2.5rem;">📱</div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:#001F5B;margin:.5rem 0 0;">Verify Your Phone</h1>
      <p style="color:#6b7280;font-size:.88rem;margin-top:.5rem;">Enter the 6-digit code we sent to your phone.</p>
    </div>

    @if(session('success'))
      <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('client.verify-phone.submit') }}">
      @csrf
      <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;">Verification Code</label>
        <input type="text" name="code" maxlength="6" placeholder="000000" autofocus
               style="width:100%;border:2px solid {{ $errors->has('code') ? '#fecaca' : '#e5eaf2' }};border-radius:8px;padding:.75rem 1rem;font-size:1.5rem;text-align:center;letter-spacing:.4em;font-family:monospace;">
        @error('code')<p style="color:#dc2626;font-size:.78rem;margin-top:4px;">{{ $message }}</p>@enderror
      </div>
      <button type="submit" style="width:100%;background:#001F5B;color:#fff;border:none;border-radius:8px;padding:.85rem;font-weight:700;font-size:1rem;cursor:pointer;">
        Verify Phone ✓
      </button>
    </form>

    <form method="POST" action="{{ route('client.verify-phone.resend') }}" style="margin-top:1rem;text-align:center;">
      @csrf
      <button type="submit" style="background:none;border:none;color:#6b7280;font-size:.82rem;cursor:pointer;text-decoration:underline;">
        Resend code
      </button>
    </form>

    <p style="text-align:center;font-size:.75rem;color:#9ca3af;margin-top:1rem;">
      Codes expire after 10 minutes. Reply STOP to opt out of SMS at any time.
    </p>

    <div style="text-align:center;margin-top:1rem;">
      <a href="{{ route('client.dashboard') }}" style="font-size:.8rem;color:#9ca3af;">Skip for now →</a>
    </div>
  </div>
</div>
@endsection
