@extends('layouts.admin')
@section('title', $client->name . ' — Client')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .stat-card{background:#fff;border-radius:10px;border:1.5px solid #e5eaf2;padding:1.25rem;}
  .stat-num{font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:var(--navy);line-height:1;}
  .stat-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-top:2px;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.7rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.9rem;}
</style>

<div class="mb-6">
  <a href="{{ route('admin.clients.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← All Clients</a>
</div>

<div class="flex justify-between items-start mb-6">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy)">{{ $client->name }}</h1>
    <div class="text-gray-500 text-sm">{{ $client->email }} · {{ $client->phone }}</div>
    <div class="text-gray-400 text-xs mt-1">Member since {{ $client->created_at->format('M j, Y') }}</div>
  </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="stat-card">
    <div class="stat-num">{{ $bookings->count() }}</div>
    <div class="stat-label">Total Bookings</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">${{ number_format($bookings->sum('price_paid'), 0) }}</div>
    <div class="stat-label">Total Paid</div>
  </div>
  <div class="stat-card">
    <div class="stat-num">{{ $bookings->where('status','confirmed')->count() }}</div>
    <div class="stat-label">Confirmed</div>
  </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="p-4 border-b border-gray-100 font-semibold text-gray-700">Booking History</div>
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Date</th><th>Time</th><th>Service</th><th>Rink</th><th>Price</th><th>Status</th>
    </tr></thead>
    <tbody>
    @forelse($bookings as $b)
    <tr>
      <td class="font-semibold">{{ \Carbon\Carbon::parse($b->date ?? $b->timeSlot->date)->format('M j, Y') }}</td>
      <td>{{ \Carbon\Carbon::parse($b->start_time ?? $b->timeSlot->start_time)->format('g:i A') }}</td>
      <td>{{ $b->service->name ?? '—' }}</td>
      <td class="text-gray-500 text-xs">{{ $b->timeSlot->rink->name ?? '—' }}</td>
      <td>${{ number_format($b->price_paid, 0) }}</td>
      <td>
        @if($b->status==='confirmed')<span class="text-xs font-bold text-green-700 bg-green-50 px-2 py-1 rounded-full">Confirmed</span>
        @elseif($b->status==='pending')<span class="text-xs font-bold text-yellow-700 bg-yellow-50 px-2 py-1 rounded-full">Pending</span>
        @else<span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ ucfirst($b->status) }}</span>@endif
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center py-8 text-gray-400">No bookings.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@endsection
