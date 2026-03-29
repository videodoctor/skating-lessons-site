@extends('layouts.admin')
@section('title', 'Client Activity — Admin')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Client Activity</h1>
  <div style="display:flex;gap:.5rem;">
    <a href="{{ route('admin.analytics') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;">Overview</a>
    <a href="{{ route('admin.analytics.funnel') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;">Funnel</a>
  </div>
</div>

{{-- Filters --}}
<form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:end;">
  <div>
    <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Action</label>
    <select name="action" style="padding:.4rem .75rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.85rem;">
      <option value="">All actions</option>
      @foreach($actions as $a)
        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:.25rem;">Client</label>
    <select name="client_id" style="padding:.4rem .75rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.85rem;">
      <option value="">All clients</option>
      @foreach($clients as $c)
        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
      @endforeach
    </select>
  </div>
  <button type="submit" style="padding:.4rem 1rem;border-radius:6px;font-size:.85rem;font-weight:500;background:var(--navy);color:#fff;border:none;cursor:pointer;">Filter</button>
  @if(request()->hasAny(['action','client_id']))
    <a href="{{ route('admin.analytics.activity') }}" style="font-size:.85rem;color:#64748b;text-decoration:none;">Clear</a>
  @endif
</form>

<div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
  <div style="overflow-x:auto;">
    <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
      <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
        <th style="padding:.4rem .5rem;">Time</th>
        <th style="padding:.4rem .5rem;">Client</th>
        <th style="padding:.4rem .5rem;">Action</th>
        <th style="padding:.4rem .5rem;">Details</th>
        <th style="padding:.4rem .5rem;">IP</th>
      </tr></thead>
      <tbody>
        @forelse($logs as $log)
        <tr style="border-top:1px solid #f1f5f9;">
          <td style="padding:.4rem .5rem;white-space:nowrap;color:#64748b;">{{ $log->created_at->format('M j g:ia') }}</td>
          <td style="padding:.4rem .5rem;font-weight:500;">
            @if($log->client)
              <a href="{{ route('admin.clients.show', $log->client_id) }}" style="color:var(--navy);text-decoration:none;">{{ $log->client->display_name }}</a>
            @else
              <span style="color:#cbd5e1;">Deleted</span>
            @endif
          </td>
          <td style="padding:.4rem .5rem;">
            @php
              $colors = [
                'login' => 'background:#dbeafe;color:#1e40af;',
                'register' => 'background:#dcfce7;color:#166534;',
                'make_booking' => 'background:#fef3c7;color:#92400e;',
                'cancel_booking' => 'background:#fecaca;color:#991b1b;',
                'view_dashboard' => 'background:#f1f5f9;color:#475569;',
              ];
            @endphp
            <span style="display:inline-block;padding:.15rem .5rem;border-radius:9999px;font-size:.75rem;font-weight:500;{{ $colors[$log->action] ?? 'background:#f1f5f9;color:#475569;' }}">{{ $log->action }}</span>
          </td>
          <td style="padding:.4rem .5rem;color:#64748b;">{{ $log->description ?? '—' }}</td>
          <td style="padding:.4rem .5rem;font-family:monospace;font-size:.8rem;color:#94a3b8;">{{ $log->ip_address }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="padding:1rem;text-align:center;color:#94a3b8;">No activity logged yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="margin-top:1rem;">{{ $logs->links() }}</div>
</div>
@endsection
