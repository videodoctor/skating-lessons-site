@extends('layouts.admin')
@section('title', 'Booking Waitlist — Admin')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
  <div>
    <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">Booking Waitlist</h1>
    <p style="color:#6b7280;font-size:.85rem;margin-top:2px;">Manage booking pause status and waitlist sign-ups</p>
  </div>
</div>

{{-- Booking Pause Toggle --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
  <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--navy);margin:0 0 .75rem;">Booking Status</h2>
  <form method="POST" action="{{ route('admin.waitlist.toggle-pause') }}">
    @csrf
    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;">
      <div>
        <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Status</label>
        <select name="booking_paused" style="border:1.5px solid #dbe4ff;border-radius:6px;padding:6px 10px;font-size:.88rem;min-width:160px;">
          <option value="0" {{ !$isPaused ? 'selected' : '' }}>Open (accepting bookings)</option>
          <option value="1" {{ $isPaused ? 'selected' : '' }}>Paused (show waitlist)</option>
        </select>
      </div>
      <div style="flex:1;min-width:200px;">
        <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Paused Message</label>
        <input type="text" name="booking_paused_message" value="{{ $pausedMessage }}" placeholder="Booking is currently closed for the season..."
               style="width:100%;border:1.5px solid #dbe4ff;border-radius:6px;padding:6px 10px;font-size:.88rem;">
      </div>
      <div>
        <label style="display:block;font-size:.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:3px;">Opens At (optional)</label>
        <input type="date" name="booking_opens_at" value="{{ $opensAt }}"
               style="border:1.5px solid #dbe4ff;border-radius:6px;padding:6px 10px;font-size:.88rem;">
      </div>
      <button type="submit" style="background:var(--navy);color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:.88rem;font-weight:600;cursor:pointer;">
        Save
      </button>
    </div>
  </form>
  @if($isPaused)
    <div style="margin-top:.75rem;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;padding:.5rem .75rem;font-size:.82rem;color:#92400e;">
      Booking is currently <strong>paused</strong>. Visitors see the waitlist form instead of available slots.
    </div>
  @else
    <div style="margin-top:.75rem;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:.5rem .75rem;font-size:.82rem;color:#065f46;">
      Booking is <strong>open</strong>. Visitors can book lessons normally.
    </div>
  @endif
</div>

{{-- Waitlist Entries --}}
<div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;overflow:hidden;">
  <div style="padding:1rem 1.25rem;border-bottom:1.5px solid #e5eaf2;display:flex;justify-content:space-between;align-items:center;">
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--navy);margin:0;">Waitlist Sign-ups ({{ $entries->total() }})</h2>
  </div>

  @if($entries->isEmpty())
    <div style="padding:3rem;text-align:center;color:#9ca3af;">
      No waitlist sign-ups yet.
    </div>
  @else
    <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
      <thead>
        <tr style="background:#f8fafc;border-bottom:1.5px solid #e5eaf2;">
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">#</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Contact</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Skater</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Source</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Consent</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Message</th>
          <th style="padding:.65rem 1rem;text-align:left;font-weight:700;color:#374151;">Signed Up</th>
          <th style="padding:.65rem 1rem;text-align:center;font-weight:700;color:#374151;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($entries as $entry)
        <tr style="border-bottom:1px solid #f3f4f6;">
          <td style="padding:.6rem 1rem;color:#9ca3af;font-size:.8rem;font-weight:700;">{{ $entries->total() - ($entries->firstItem() - 1) - $loop->index }}</td>
          <td style="padding:.6rem 1rem;">
            <div style="font-weight:600;color:#374151;">{{ $entry->name }}</div>
            <div style="font-size:.78rem;"><a href="mailto:{{ $entry->email }}" style="color:var(--navy);">{{ $entry->email }}</a></div>
            @if($entry->phone)<div style="font-size:.78rem;color:#6b7280;">{{ $entry->phone }}</div>@endif
          </td>
          <td style="padding:.6rem 1rem;">
            @if($entry->student_name)
              <div style="font-weight:600;color:#0c4a6e;">{{ $entry->student_name }}</div>
              <div style="font-size:.76rem;color:#6b7280;">
                @if($entry->student_age)Age {{ $entry->student_age }}@endif
                @if($entry->skill_level) · {{ ucfirst($entry->skill_level) }}@endif
              </div>
            @else
              <span style="color:#d1d5db;font-size:.8rem;">—</span>
            @endif
          </td>
          <td style="padding:.6rem 1rem;font-size:.78rem;">
            @if($entry->service)
              <div style="font-weight:600;color:var(--navy);">{{ $entry->service->name }}</div>
              <div style="font-size:.7rem;color:#9ca3af;">Service waitlist</div>
            @elseif($entry->source === 'booking_paused')
              <div style="font-weight:600;color:#92400e;">Booking Paused</div>
              <div style="font-size:.7rem;color:#9ca3af;">General waitlist</div>
            @else
              <div style="color:#9ca3af;">General</div>
            @endif
          </td>
          <td style="padding:.6rem 1rem;font-size:.78rem;">
            @if($entry->email_consent)<div style="color:#065f46;">✓ Email</div>@endif
            @if($entry->sms_consent)<div style="color:#065f46;">✓ SMS</div>@endif
            @if($entry->waiver_accepted)<div style="color:#065f46;">✓ Waiver</div>@endif
            @if($entry->terms_accepted)<div style="color:#065f46;">✓ Terms</div>@endif
            @if(!$entry->email_consent && !$entry->sms_consent && !$entry->waiver_accepted && !$entry->terms_accepted)<span style="color:#d1d5db;">—</span>@endif
          </td>
          <td style="padding:.6rem 1rem;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $entry->message }}">
            {{ $entry->message ? Str::limit($entry->message, 50) : '—' }}
          </td>
          <td style="padding:.6rem 1rem;color:#6b7280;white-space:nowrap;">{{ $entry->created_at->format('M j, Y g:i A') }}</td>
          <td style="padding:.6rem 1rem;text-align:center;">
            <form method="POST" action="{{ route('admin.waitlist.destroy', $entry) }}" onsubmit="return confirm('Remove this entry?')">
              @csrf @method('DELETE')
              <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:.78rem;font-weight:600;">Remove</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    @if($entries->hasPages())
    <div style="padding:.75rem 1rem;border-top:1px solid #f3f4f6;">
      {{ $entries->links() }}
    </div>
    @endif
  @endif
</div>
@endsection
