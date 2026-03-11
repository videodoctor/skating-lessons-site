@extends('layouts.admin')
@section('title', 'Waivers — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .tbl th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;padding:.6rem 1rem;text-align:left;}
  .tbl td{padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;font-size:.88rem;vertical-align:middle;}
  .tbl tr:hover td{background:#fafafa;}
  .pill{padding:2px 9px;border-radius:10px;font-size:.7rem;font-weight:700;}
  .pill-green{background:#d1fae5;color:#065f46;}
  .pill-gray{background:#f3f4f6;color:#6b7280;}
  .search-input{padding:.55rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.88rem;width:100%;max-width:300px;}
  .search-input:focus{outline:none;border-color:var(--navy);}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Liability Waivers</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">{{ $waivers->total() }} signed · Current version: {{ \App\Models\LiabilityWaiver::CURRENT_VERSION }}</p>
  </div>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <span style="font-size:.8rem;color:#6b7280;">{{ $unsignedCount }} client(s) have not signed</span>
    <a href="{{ route('waiver.show') }}" target="_blank"
       style="background:var(--navy);color:#fff;padding:.5rem 1.1rem;border-radius:7px;font-size:.82rem;font-weight:700;text-decoration:none;">
      View Waiver →
    </a>
  </div>
</div>

@if($unsignedCount > 0)
<div style="background:#fff8ed;border:2px solid #fcd34d;border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1.25rem;font-size:.83rem;color:#92400e;">
  ⚠️ <strong>{{ $unsignedCount }} registered client(s)</strong> have not signed the current waiver (v{{ \App\Models\LiabilityWaiver::CURRENT_VERSION }}).
  <a href="{{ route('admin.clients.index') }}" style="color:#92400e;font-weight:700;margin-left:.5rem;">View Clients →</a>
</div>
@endif

<form method="GET" class="mb-4">
  <input type="text" name="q" value="{{ $search }}" placeholder="Search by name or email…" class="search-input" onchange="this.form.submit()">
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="tbl w-full">
    <thead class="bg-gray-50"><tr>
      <th>Client</th>
      <th>Signed As</th>
      <th>Version</th>
      <th>Signed At</th>
      <th>IP Address</th>
      <th>Status</th>
    </tr></thead>
    <tbody>
    @forelse($waivers as $waiver)
    <tr>
      <td>
        @if($waiver->client)
          <a href="{{ route('admin.clients.show', $waiver->client) }}"
             style="font-weight:700;color:var(--navy);text-decoration:none;">
            {{ $waiver->client->full_name }}
          </a>
          <div style="font-size:.72rem;color:#9ca3af;">{{ $waiver->client->email }}</div>
        @else
          <span style="color:#9ca3af;">— deleted client —</span>
        @endif
      </td>
      <td style="font-weight:600;">{{ $waiver->signed_name }}</td>
      <td><span class="pill pill-gray">v{{ $waiver->version }}</span></td>
      <td>
        <div>{{ $waiver->signed_at->format('M j, Y') }}</div>
        <div style="font-size:.72rem;color:#9ca3af;">{{ $waiver->signed_at->format('g:i A') }}</div>
      </td>
      <td style="font-size:.78rem;color:#9ca3af;font-family:monospace;">{{ $waiver->signed_ip ?? '—' }}</td>
      <td>
        @if($waiver->version === \App\Models\LiabilityWaiver::CURRENT_VERSION)
          <span class="pill pill-green">✓ Current</span>
        @else
          <span class="pill" style="background:#fef3c7;color:#92400e;">Outdated v{{ $waiver->version }}</span>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center py-10 text-gray-400">No waivers signed yet.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="p-4">{{ $waivers->appends(['q' => $search])->links() }}</div>
</div>
@endsection
