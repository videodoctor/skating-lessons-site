@extends('layouts.app')
@section('title', 'Accept Terms — Kristine Skates')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;}
</style>

@php $client = auth('client')->user(); @endphp

<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem;">
  <div style="max-width:560px;width:100%;">
    <div style="text-align:center;margin-bottom:1.5rem;">
      <div style="font-size:2.5rem;margin-bottom:.5rem;">⛸️</div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:var(--navy);margin:0;">
        @if($client->must_accept_terms && !$client->terms_accepted_at)
          Welcome to Kristine Skates!
        @else
          Just a Few Things
        @endif
      </h1>
      <p style="color:#6b7280;font-size:.95rem;margin-top:.5rem;">
        Hi {{ $client->first_name }}! Please review and accept the following to continue.
      </p>
    </div>

    @if ($errors->any())
    <div style="background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
      @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:1.75rem;">
      <form method="POST" action="{{ route('client.accept-terms.submit') }}">
        @csrf

        {{-- Password (only for admin-created accounts) --}}
        @if($client->must_accept_terms && !$client->terms_accepted_at)
        <div style="margin-bottom:1.25rem;">
          <div style="font-weight:700;font-size:.9rem;color:#111827;margin-bottom:.5rem;">Set Your Password</div>
          <p style="font-size:.82rem;color:#6b7280;margin-bottom:.5rem;">Your account was created by Coach Kristine. Please set a password to secure it.</p>
          <div style="display:grid;gap:.5rem;">
            <div>
              <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">New Password</label>
              <input type="password" name="password" required minlength="8"
                     style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
            </div>
            <div>
              <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:3px;">Confirm Password</label>
              <input type="password" name="password_confirmation" required minlength="8"
                     style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:8px 10px;font-size:.88rem;">
            </div>
          </div>
        </div>
        @endif

        {{-- Consent checkboxes --}}
        <div style="border-top:1px solid #f3f4f6;padding-top:1.25rem;margin-bottom:1.25rem;">

          {{-- Email consent (skip if already given) --}}
          @if(!$client->email_consent_at)
          <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;">
            <input type="checkbox" name="email_consent" id="email_consent" required
                   style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);">
            <label for="email_consent" style="font-size:.85rem;color:#374151;line-height:1.5;">
              I agree to receive booking confirmation and update emails from Kristine Skates. *
            </label>
          </div>
          @endif

          {{-- SMS consent (skip if already opted in) --}}
          @if(!$client->sms_consent)
          <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;background:#f0f4ff;border:1.5px solid #dbe4ff;border-radius:8px;padding:.75rem 1rem;">
            <input type="checkbox" name="sms_consent" id="sms_consent" value="1"
                   style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);">
            <label for="sms_consent" style="font-size:.85rem;color:#374151;line-height:1.5;">
              <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders, booking confirmations, schedule changes, payment reminders, availability notifications, and public skate schedules.
              You will receive a confirmation text upon opting in.
              Message frequency varies. Message and data rates may apply.
              Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
              View our <a href="{{ route('privacy') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Privacy Policy</a>.
            </label>
          </div>
          @endif

          {{-- Waiver (skip if already signed current version) --}}
          @if(!$client->hasSignedCurrentWaiver())
          <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;background:#fffbeb;border:1.5px solid #fde68a;border-radius:8px;padding:.75rem 1rem;">
            <input type="checkbox" name="waiver_accepted" id="waiver_accepted" required
                   style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);">
            <label for="waiver_accepted" style="font-size:.85rem;color:#374151;line-height:1.5;">
              I have read and agree to the <a href="{{ route('waiver.show') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Liability Waiver</a>. I understand that skating involves inherent risks. *
            </label>
          </div>
          @endif

          {{-- Terms (skip if already accepted) --}}
          @if(!$client->terms_accepted_at)
          <div style="display:flex;align-items:flex-start;gap:.75rem;">
            <input type="checkbox" name="terms_accepted" id="terms_accepted" required
                   style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:var(--navy);">
            <label for="terms_accepted" style="font-size:.85rem;color:#374151;line-height:1.5;">
              I have read and agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Terms &amp; Conditions</a>
              and <a href="{{ route('privacy') }}" target="_blank" style="color:var(--navy);text-decoration:underline;">Privacy Policy</a>. *
            </label>
          </div>
          @endif
        </div>

        <button type="submit" style="width:100%;background:var(--navy);color:#fff;border:none;border-radius:7px;padding:.8rem;font-size:.95rem;font-weight:700;cursor:pointer;">
          Accept &amp; Continue
        </button>
      </form>
    </div>

    <div style="text-align:center;margin-top:1rem;">
      <form method="POST" action="{{ route('client.logout') }}">
        @csrf
        <button type="submit" style="background:none;border:none;color:#6b7280;font-size:.82rem;cursor:pointer;text-decoration:underline;">Sign out</button>
      </form>
    </div>
  </div>
</div>
@endsection
