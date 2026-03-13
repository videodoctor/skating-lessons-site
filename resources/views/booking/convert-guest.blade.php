@if($canConvert ?? false)
<div style="background:#f0f4ff;border:2px solid #c7d2fe;border-radius:12px;padding:1.5rem;margin-top:1.5rem;">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
    <div style="font-size:1.5rem;">🔑</div>
    <div>
      <div style="font-weight:700;color:#001F5B;font-size:1rem;">Save your booking history</div>
      <div style="font-size:.82rem;color:#6b7280;">Create a free account to manage bookings, view history, and book faster next time.</div>
    </div>
  </div>
  <form method="POST" action="{{ route('booking.convert-guest') }}" id="convertForm">
    @csrf
    <input type="hidden" name="token" value="{{ $booking->guest_convert_token }}">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
      <div>
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Password *</label>
        <input type="password" name="password" required minlength="8"
               placeholder="Min. 8 characters"
               style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:.55rem .85rem;font-size:.87rem;">
        @error('password')<p style="color:#dc2626;font-size:.72rem;margin-top:3px;">{{ $message }}</p>@enderror
      </div>
      <div>
        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Confirm Password *</label>
        <input type="password" name="password_confirmation" required
               placeholder="Repeat password"
               style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:.55rem .85rem;font-size:.87rem;">
      </div>
    </div>
    <button type="submit"
            style="background:#001F5B;color:#fff;border:none;border-radius:8px;padding:.65rem 1.5rem;font-weight:700;font-size:.88rem;cursor:pointer;width:100%;">
      ✓ Create My Account
    </button>
  </form>
  <p style="font-size:.72rem;color:#9ca3af;margin-top:.75rem;text-align:center;">
    Your email <strong>{{ $booking->client_email }}</strong> will be used to log in. A verification email will be sent.
  </p>
</div>
@endif
