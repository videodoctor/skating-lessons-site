@extends('layouts.admin')
@section('title', 'Analytics — Admin')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Analytics</h1>
  <div style="display:flex;gap:.5rem;align-items:center;">
    <span style="font-size:.85rem;color:#666;">Period:</span>
    @foreach([7 => '7d', 30 => '30d', 90 => '90d'] as $d => $label)
      <a href="?days={{ $d }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;{{ $days == $d ? 'background:var(--navy);color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">{{ $label }}</a>
    @endforeach
    <a href="{{ route('admin.analytics.activity') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;margin-left:.5rem;">Client Activity</a>
    <a href="{{ route('admin.analytics.funnel') }}" style="padding:.35rem .75rem;border-radius:6px;font-size:.85rem;font-weight:500;text-decoration:none;background:#e2e8f0;color:#334155;">Funnel</a>
  </div>
</div>

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
  <div style="background:#fff;border-radius:10px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;">Today</div>
    <div style="font-size:2rem;font-weight:700;color:var(--navy);margin-top:.25rem;">{{ number_format($todayVisits) }}</div>
    <div style="font-size:.8rem;color:#64748b;">homepage hits</div>
  </div>
  <div style="background:#fff;border-radius:10px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;">{{ $days }}-Day Visits</div>
    <div style="font-size:2rem;font-weight:700;color:var(--navy);margin-top:.25rem;">{{ number_format($totalVisits) }}</div>
    <div style="font-size:.8rem;color:#64748b;">total homepage hits</div>
  </div>
  <div style="background:#fff;border-radius:10px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;">Unique Visitors</div>
    <div style="font-size:2rem;font-weight:700;color:var(--navy);margin-top:.25rem;">{{ number_format($uniqueVisitors) }}</div>
    <div style="font-size:.8rem;color:#64748b;">by IP ({{ $days }}d)</div>
  </div>
  <div style="background:#fff;border-radius:10px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div style="font-size:.75rem;font-weight:600;text-transform:uppercase;color:#94a3b8;letter-spacing:.05em;">New Clients</div>
    <div style="font-size:2rem;font-weight:700;color:var(--navy);margin-top:.25rem;">{{ number_format($newClients) }}</div>
    <div style="font-size:.8rem;color:#64748b;">registrations ({{ $days }}d)</div>
  </div>
</div>

{{-- Daily visits chart --}}
<div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);margin-bottom:2rem;">
  <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">Daily Homepage Visits</h2>
  @if($dailyVisits->count())
    @php $maxVisits = $dailyVisits->max('visits') ?: 1; @endphp
    <div style="display:flex;align-items:flex-end;gap:2px;height:150px;overflow-x:auto;">
      @foreach($dailyVisits as $day)
        <div style="flex:1;min-width:12px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;" title="{{ $day->date }}: {{ $day->visits }} visits ({{ $day->unique_visitors }} unique)">
          <div style="width:100%;background:var(--navy);border-radius:3px 3px 0 0;min-height:2px;height:{{ ($day->visits / $maxVisits) * 100 }}%;"></div>
        </div>
      @endforeach
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:.5rem;font-size:.7rem;color:#94a3b8;">
      <span>{{ $dailyVisits->first()->date }}</span>
      <span>{{ $dailyVisits->last()->date }}</span>
    </div>
  @else
    <p style="color:#94a3b8;font-size:.9rem;">No data yet.</p>
  @endif
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;margin-bottom:2rem;">
  {{-- Referrer breakdown --}}
  <div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">Traffic Sources</h2>
    @if($referrers->count())
      @php $totalRef = $referrers->sum('visits') ?: 1; @endphp
      <table style="width:100%;font-size:.85rem;">
        <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
          <th style="padding:.25rem 0;">Source</th><th style="text-align:right;padding:.25rem 0;">Visits</th><th style="text-align:right;padding:.25rem 0;">%</th>
        </tr></thead>
        <tbody>
          @foreach($referrers as $ref)
          <tr style="border-top:1px solid #f1f5f9;">
            <td style="padding:.4rem 0;font-weight:500;">{{ ucfirst($ref->referrer_source) }}</td>
            <td style="padding:.4rem 0;text-align:right;">{{ $ref->visits }}</td>
            <td style="padding:.4rem 0;text-align:right;color:#64748b;">{{ round($ref->visits / $totalRef * 100) }}%</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:#94a3b8;font-size:.9rem;">No referrer data yet.</p>
    @endif
  </div>

  {{-- Top locations --}}
  <div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">Top Locations</h2>
    @if($cities->count())
      <table style="width:100%;font-size:.85rem;">
        <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
          <th style="padding:.25rem 0;">Location</th><th style="text-align:right;padding:.25rem 0;">Visits</th>
        </tr></thead>
        <tbody>
          @foreach($cities as $city)
          <tr style="border-top:1px solid #f1f5f9;">
            <td style="padding:.4rem 0;">{{ $city->location }}</td>
            <td style="padding:.4rem 0;text-align:right;font-weight:500;">{{ $city->visits }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:#94a3b8;font-size:.9rem;">No geo data yet.</p>
    @endif
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;margin-bottom:2rem;">
  {{-- Top pages --}}
  <div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">Top Pages</h2>
    @if($topPages->count())
      <table style="width:100%;font-size:.85rem;">
        <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
          <th style="padding:.25rem 0;">Path</th><th style="text-align:right;padding:.25rem 0;">Visits</th>
        </tr></thead>
        <tbody>
          @foreach($topPages as $page)
          <tr style="border-top:1px solid #f1f5f9;">
            <td style="padding:.4rem 0;font-family:monospace;font-size:.8rem;">{{ $page->path }}</td>
            <td style="padding:.4rem 0;text-align:right;font-weight:500;">{{ $page->visits }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:#94a3b8;font-size:.9rem;">No data yet.</p>
    @endif
  </div>

  {{-- UTM campaigns --}}
  <div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">UTM Campaigns</h2>
    @if($campaigns->count())
      <table style="width:100%;font-size:.85rem;">
        <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
          <th style="padding:.25rem 0;">Source</th><th style="padding:.25rem 0;">Medium</th><th style="padding:.25rem 0;">Campaign</th><th style="text-align:right;padding:.25rem 0;">Visits</th>
        </tr></thead>
        <tbody>
          @foreach($campaigns as $c)
          <tr style="border-top:1px solid #f1f5f9;">
            <td style="padding:.4rem 0;">{{ $c->utm_source }}</td>
            <td style="padding:.4rem 0;color:#64748b;">{{ $c->utm_medium ?? '-' }}</td>
            <td style="padding:.4rem 0;color:#64748b;">{{ $c->utm_campaign ?? '-' }}</td>
            <td style="padding:.4rem 0;text-align:right;font-weight:500;">{{ $c->visits }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p style="color:#94a3b8;font-size:.9rem;">No UTM traffic yet. Share links with <code style="background:#f1f5f9;padding:.1rem .3rem;border-radius:3px;font-size:.8rem;">?utm_source=instagram&utm_medium=bio</code></p>
    @endif
  </div>
</div>

{{-- Recent visits --}}
<div style="background:#fff;border-radius:10px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
  <h2 style="font-size:1.1rem;font-weight:600;color:var(--navy);margin:0 0 1rem;">Recent Visits</h2>
  <div style="margin-bottom:.75rem;font-size:.8rem;color:#94a3b8;display:flex;gap:1rem;flex-wrap:wrap;">
    <span><span style="display:inline-block;width:10px;height:10px;background:#fef3c7;border:1px solid #f59e0b;border-radius:2px;vertical-align:middle;margin-right:3px;"></span> = out-of-area</span>
    <span><span style="display:inline-block;width:10px;height:10px;background:#fce7f3;border:1px solid #ec4899;border-radius:2px;vertical-align:middle;margin-right:3px;"></span> = datacenter/cloud (possible bot/reviewer)</span>
    <span><span style="display:inline-block;width:10px;height:10px;background:#dbeafe;border:1px solid #3b82f6;border-radius:2px;vertical-align:middle;margin-right:3px;"></span> = admin</span>
  </div>
  <div style="overflow-x:auto;">
    <table style="width:100%;font-size:.85rem;border-collapse:collapse;">
      <thead><tr style="text-align:left;color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">
        <th style="padding:.4rem .5rem;">Time</th>
        <th style="padding:.4rem .5rem;">Status</th>
        <th style="padding:.4rem .5rem;">Page</th>
        <th style="padding:.4rem .5rem;">IP</th>
        <th style="padding:.4rem .5rem;">Location</th>
        <th style="padding:.4rem .5rem;">Source</th>
        <th style="padding:.4rem .5rem;">Ref / UTM</th>
      </tr></thead>
      <tbody>
        @forelse($recentVisits as $visit)
        @php
          $stlMetro = ['St. Louis', 'Saint Louis', 'Clayton', 'Kirkwood', 'Webster Groves', 'Creve Coeur', 'Brentwood', 'Maryville', 'Chesterfield', 'Ballwin', 'Manchester', 'Wildwood', 'Eureka', 'Fenton', 'Arnold', 'Affton', 'Florissant', 'Maryland Heights', 'Hazelwood', 'Bridgeton', 'OFallon', "O'Fallon", 'St. Charles', 'Saint Charles', 'St. Peters', 'Belleville', 'Edwardsville', 'Collinsville', 'University City', 'Richmond Heights', 'Ladue', 'Frontenac', 'Town and Country', 'Olivette', 'Overland', 'Des Peres', 'Sunset Hills', 'Mehlville', 'Oakville', 'Lemay', 'Concord'];
          $isLocal = $visit->city && (in_array($visit->city, $stlMetro) || str_contains($visit->city, 'St. Louis') || str_contains($visit->city, 'Saint Louis'));
          $isOutOfArea = $visit->city && !$isLocal;
        @endphp
        @php
          $isAdmin = (bool) $visit->admin_user_id;
          $isHosting = (bool) $visit->is_hosting;
          $isTwilioLikely = $isHosting && $visit->org && (
            str_contains(strtolower($visit->org), 'twilio') ||
            str_contains(strtolower($visit->org), 'amazon') ||
            str_contains(strtolower($visit->org), 'aws') ||
            str_contains(strtolower($visit->isp ?? ''), 'amazon') ||
            str_contains(strtolower($visit->isp ?? ''), 'aws')
          );
        @endphp
        <tr style="border-top:1px solid #f1f5f9;{{ $isAdmin ? 'background:#eff6ff;' : ($isHosting ? 'background:#fce7f3;' : ($isOutOfArea ? 'background:#fffbeb;' : '')) }}">
          <td style="padding:.4rem .5rem;white-space:nowrap;color:#64748b;">
            {{ $visit->created_at->format('M j g:ia') }}
            @if($isAdmin)
              <div style="font-size:.68rem;font-weight:700;color:#2563eb;">{{ $visit->adminUser?->name ?? 'Admin' }} — Admin</div>
            @elseif($isTwilioLikely)
              <div style="font-size:.68rem;font-weight:700;color:#db2777;">Likely Twilio/AWS reviewer</div>
            @elseif($isHosting)
              <div style="font-size:.68rem;font-weight:700;color:#be185d;">Datacenter IP</div>
            @endif
          </td>
          <td style="padding:.4rem .5rem;text-align:center;">
            @if($visit->http_status)
              <span style="display:inline-block;padding:1px 6px;border-radius:3px;font-size:.72rem;font-weight:700;font-family:monospace;{{ $visit->http_status >= 500 ? 'background:#fee2e2;color:#991b1b;' : ($visit->http_status >= 400 ? 'background:#fef3c7;color:#92400e;' : ($visit->http_status >= 300 ? 'background:#dbeafe;color:#1e40af;' : 'background:#d1fae5;color:#065f46;')) }}">{{ $visit->http_status }}</span>
            @else
              <span style="color:#d1d5db;">—</span>
            @endif
          </td>
          <td style="padding:.4rem .5rem;font-family:monospace;font-size:.8rem;">{{ $visit->path }}</td>
          <td style="padding:.4rem .5rem;font-family:monospace;font-size:.8rem;" title="{{ $visit->org ?? '' }}{{ $visit->isp ? ' / ' . $visit->isp : '' }}">
            {{ $visit->ip_address }}
            @if($isHosting && $visit->org)
              <div style="font-size:.65rem;color:#be185d;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $visit->org }}</div>
            @endif
          </td>
          <td style="padding:.4rem .5rem;">
            @if($visit->city)
              {{ $visit->city }}, {{ $visit->region }}
              @if($isOutOfArea && $visit->region !== 'Missouri' && $visit->region !== 'Illinois')
                <span style="display:inline-block;padding:.1rem .4rem;border-radius:9999px;font-size:.65rem;font-weight:600;background:#fef3c7;color:#92400e;margin-left:.25rem;">OUT OF AREA</span>
              @endif
            @else
              <span style="color:#cbd5e1;">—</span>
            @endif
          </td>
          <td style="padding:.4rem .5rem;">
            @if($visit->referrer_source && $visit->referrer_source !== 'direct')
              <span style="display:inline-block;padding:.15rem .5rem;border-radius:9999px;font-size:.75rem;font-weight:500;
                {{ in_array($visit->referrer_source, ['google','bing','yahoo']) ? 'background:#dbeafe;color:#1e40af;' : 'background:#ede9fe;color:#5b21b6;' }}">{{ ucfirst($visit->referrer_source) }}</span>
            @else
              <span style="color:#cbd5e1;">Direct</span>
            @endif
          </td>
          <td style="padding:.4rem .5rem;font-size:.8rem;color:#64748b;">
            @if($visit->ref_tag)ref={{ $visit->ref_tag }}@endif
            @if($visit->utm_source)utm={{ $visit->utm_source }}@endif
            @if(!$visit->ref_tag && !$visit->utm_source)<span style="color:#cbd5e1;">—</span>@endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" style="padding:1rem;text-align:center;color:#94a3b8;">No visits recorded yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
