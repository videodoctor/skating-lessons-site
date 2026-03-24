@extends('layouts.admin')
@section('title', 'Waitlist — {{ $package->name }}')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#001F5B;margin:0;">Waitlist — {{ $package->name }}</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">{{ $entries->count() }} people waiting</p>
  </div>
  <a href="{{ route('admin.packages.index') }}" style="background:#f3f4f6;color:#374151;border-radius:7px;padding:.5rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none;">← Back to Packages</a>
</div>

@if($entries->isEmpty())
<div style="background:#fff;border:1.5px dashed #e5eaf2;border-radius:10px;padding:3rem;text-align:center;color:#9ca3af;">
  No one on the waitlist yet.
</div>
@else
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:1.5px solid #e5eaf2;">
        <th style="padding:.75rem 1rem;text-align:left;font-weight:700;color:#374151;">#</th>
        <th style="padding:.75rem 1rem;text-align:left;font-weight:700;color:#374151;">Name</th>
        <th style="padding:.75rem 1rem;text-align:left;font-weight:700;color:#374151;">Email</th>
        <th style="padding:.75rem 1rem;text-align:left;font-weight:700;color:#374151;">Joined</th>
      </tr>
    </thead>
    <tbody>
      @foreach($entries as $i => $entry)
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.65rem 1rem;color:#9ca3af;">{{ $i + 1 }}</td>
        <td style="padding:.65rem 1rem;color:#374151;">{{ $entry->name ?: '—' }}</td>
        <td style="padding:.65rem 1rem;">
          <a href="mailto:{{ $entry->email }}" style="color:#001F5B;">{{ $entry->email }}</a>
        </td>
        <td style="padding:.65rem 1rem;color:#6b7280;">{{ $entry->created_at->format('M j, Y g:i A') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div style="margin-top:1rem;text-align:right;">
  <a href="data:text/csv;charset=utf-8,Name,Email,Joined%0A{{ $entries->map(fn($e) => urlencode(($e->name ?: '') . ',' . $e->email . ',' . $e->created_at->format('Y-m-d')))->implode('%0A') }}"
     download="{{ Str::slug($package->name) }}-waitlist.csv"
     style="background:#001F5B;color:#fff;border-radius:7px;padding:.5rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;">
    ⬇ Export CSV
  </a>
</div>
@endif
@endsection
