@extends('layouts.admin')
@section('title', 'Clients — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.9rem;}
  .tbl tr:hover td{background:#fafafa;}
  .search-input{padding:.6rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.9rem;width:100%;max-width:320px;transition:border .15s;}
  .search-input:focus{outline:none;border-color:var(--navy);}
</style>

<div class="flex justify-between items-center mb-6">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy)">Clients</h1>
    <p class="text-gray-500 text-sm">{{ $clients->total() }} registered · {{ $guestCount }} guest email(s)</p>
  </div>
  <a href="{{ route('admin.export') }}" class="text-sm text-blue-700 hover:underline">Export CSV →</a>
</div>

<form method="GET" class="mb-4">
  <input type="text" name="q" value="{{ $search }}" placeholder="Search name or email…" class="search-input">
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Total Paid</th><th>Since</th><th></th>
    </tr></thead>
    <tbody>
    @forelse($clients as $client)
    <tr>
      <td class="font-semibold text-gray-900">{{ $client->name }}</td>
      <td class="text-gray-500">{{ $client->email }}</td>
      <td class="text-gray-500">{{ $client->phone }}</td>
      <td class="text-center">
        <span class="inline-block bg-blue-50 text-blue-800 font-bold text-xs px-2 py-1 rounded-full">{{ $client->bookings_count }}</span>
      </td>
      <td class="font-semibold">${{ number_format($client->bookings_sum_price_paid ?? 0, 0) }}</td>
      <td class="text-gray-400 text-xs">{{ $client->created_at->format('M j, Y') }}</td>
      <td><a href="{{ route('admin.clients.show', $client) }}" class="text-blue-700 text-xs hover:underline">View →</a></td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center py-10 text-gray-400">No clients found.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $clients->appends(['q'=>$search])->links() }}</div>
</div>
@endsection
