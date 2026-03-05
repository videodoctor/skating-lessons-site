@extends('layouts.admin')
@section('title', 'Export — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .stat-card{background:#fff;border-radius:10px;border:1.5px solid #e5eaf2;padding:1.5rem;}
  .stat-num{font-family:'Bebas Neue',sans-serif;font-size:2.5rem;color:var(--navy);line-height:1;}
  .stat-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-top:4px;}
  .export-card{background:#fff;border-radius:12px;border:1.5px solid #e5eaf2;padding:1.75rem;}
  .form-label{display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.3rem;}
  .form-input{padding:.6rem .9rem;border:2px solid #e5eaf2;border-radius:6px;font-size:.9rem;transition:border .15s;}
  .form-input:focus{outline:none;border-color:var(--navy);}
  .btn-export{display:inline-block;background:var(--navy);color:#fff;padding:.75rem 1.75rem;border-radius:8px;font-weight:600;transition:background .2s;text-decoration:none;}
  .btn-export:hover{background:var(--red);}
</style>

<h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy)" class="mb-6">Reports &amp; Export</h1>

<!-- Stats summary -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="stat-card">
    <div class="stat-num">${{ number_format($stats['total_revenue'], 0) }}</div>
    <div class="stat-label">Total Revenue</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">{{ $stats['total_bookings'] }}</div>
    <div class="stat-label">Total Bookings</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">{{ $stats['total_clients'] }}</div>
    <div class="stat-label">Registered Clients</div>
  </div>
  <div class="stat-card" style="border-color:#a7f3d0;background:#ecfdf5">
    <div class="stat-num" style="color:#065f46">${{ number_format($stats['this_month_rev'], 0) }}</div>
    <div class="stat-label">This Month</div>
  </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
  <!-- Bookings export -->
  <div class="export-card">
    <h2 class="font-bold text-gray-900 mb-1">Bookings CSV</h2>
    <p class="text-gray-500 text-sm mb-4">Export all bookings in a date range. Includes client info, service, rink, price, status.</p>
    <form action="{{ route('admin.export.bookings') }}" method="GET" class="flex gap-3 items-end flex-wrap">
      <div>
        <label class="form-label">From</label>
        <input type="date" name="from" value="{{ now()->subDays(90)->format('Y-m-d') }}" class="form-input">
      </div>
      <div>
        <label class="form-label">To</label>
        <input type="date" name="to" value="{{ now()->format('Y-m-d') }}" class="form-input">
      </div>
      <button type="submit" class="btn-export">⬇ Download</button>
    </form>
  </div>

  <!-- Clients export -->
  <div class="export-card">
    <h2 class="font-bold text-gray-900 mb-1">Clients CSV</h2>
    <p class="text-gray-500 text-sm mb-4">Export all registered clients with total booking count and revenue.</p>
    <a href="{{ route('admin.export.clients') }}" class="btn-export">⬇ Download Clients</a>
  </div>
</div>
@endsection
