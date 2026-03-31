@extends('layouts.admin')
@section('title', 'Manage Rinks — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;--ice:#E8F5FB;}
  .rink-card{background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:1.25rem;margin-bottom:1rem;}
  .rink-card.inactive{opacity:.65;border-color:#fecaca;}
  .rink-title{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0;}
  .rink-addr{font-size:.8rem;color:#6b7280;margin-top:2px;}
  .toggle-row{display:flex;align-items:center;gap:1.5rem;margin-top:.75rem;flex-wrap:wrap;}
  .toggle{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:#374151;}
  .toggle input[type="checkbox"]{width:18px;height:18px;accent-color:var(--navy);cursor:pointer;}
  .field{margin-top:.65rem;}
  .field label{display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;}
  .field input,.field textarea{width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:6px 10px;font-size:.85rem;font-family:inherit;}
  .field textarea{resize:vertical;min-height:50px;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;}
  .pill-red{background:#fee2e2;color:#991b1b;}
  .pill-gray{background:#f3f4f6;color:#6b7280;}
  .btn-save{background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.45rem 1.1rem;font-size:.78rem;font-weight:700;cursor:pointer;margin-top:.75rem;}
</style>

<div style="margin-bottom:1.5rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Manage Rinks</h1>
  <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">{{ $rinks->count() }} rinks · {{ $rinks->where('is_active', true)->count() }} active</p>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.83rem;font-weight:600;">{{ session('success') }}</div>
@endif

@foreach($rinks as $rink)
<div class="rink-card {{ !$rink->is_active ? 'inactive' : '' }}">
  <form method="POST" action="{{ route('admin.rinks.update', $rink) }}">
    @csrf @method('PATCH')

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
      <div>
        <div class="rink-title">{{ $rink->name }}</div>
        <div class="rink-addr">{{ $rink->address }}</div>
        @if($rink->website_url)
          <a href="{{ $rink->website_url }}" target="_blank" style="font-size:.75rem;color:var(--navy);">{{ $rink->website_url }}</a>
        @endif
      </div>
      <div style="display:flex;gap:.4rem;">
        @if($rink->is_active)
          <span class="pill pill-green">Active</span>
        @else
          <span class="pill pill-red">Inactive</span>
        @endif
        @if($rink->is_bookable)
          <span class="pill pill-blue">Bookable</span>
        @endif
        @if($rink->is_displayed)
          <span class="pill pill-green">Displayed</span>
        @else
          <span class="pill pill-gray">Hidden</span>
        @endif
      </div>
    </div>

    <div class="toggle-row">
      <label class="toggle">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ $rink->is_active ? 'checked' : '' }}>
        Active (scraping & calendars)
      </label>
      <label class="toggle">
        <input type="hidden" name="is_bookable" value="0">
        <input type="checkbox" name="is_bookable" value="1" {{ $rink->is_bookable ? 'checked' : '' }}>
        Bookable (lessons available)
      </label>
      <label class="toggle">
        <input type="hidden" name="is_displayed" value="0">
        <input type="checkbox" name="is_displayed" value="1" {{ $rink->is_displayed ? 'checked' : '' }}>
        Displayed on public site
      </label>
    </div>

    <div class="field">
      <label>Inactive Message (shown on public site when not displayed)</label>
      <textarea name="inactive_message" placeholder="e.g. Closed for summer maintenance. Reopening October 2026.">{{ $rink->inactive_message }}</textarea>
    </div>

    <button type="submit" class="btn-save">Save</button>
  </form>
</div>
@endforeach

@endsection
