@extends('layouts.app')
@section('title', 'Liability Waiver — Kristine Skates')
@section('content')
<style>
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;}
  .waiver-hero{background:var(--navy);color:#fff;padding:3rem 1.5rem 2.5rem;text-align:center;}
  .waiver-hero h1{font-family:'Bebas Neue',sans-serif;font-size:3rem;margin:0;}
  .waiver-hero p{opacity:.7;margin-top:.5rem;font-size:.95rem;}
  .waiver-body{max-width:760px;margin:0 auto;padding:2.5rem 1.5rem 5rem;}
  .waiver-text{background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:2rem;margin-bottom:2rem;max-height:420px;overflow-y:auto;font-size:.88rem;line-height:1.75;color:#374151;}
  .waiver-text h3{font-size:1rem;font-weight:700;color:var(--navy);margin:1.25rem 0 .4rem;}
  .waiver-text h3:first-child{margin-top:0;}
  .sign-box{background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:1.75rem;}
  .sign-title{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--navy);margin-bottom:1rem;}
  .mfg{margin-bottom:1rem;}
  .mfg label{display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:4px;}
  .mfg input{width:100%;border:2px solid #dbe4ff;border-radius:8px;padding:10px 14px;font-size:.95rem;transition:border .15s;}
  .mfg input:focus{outline:none;border-color:var(--navy);}
  .checkbox-row{display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem;}
  .checkbox-row input[type=checkbox]{margin-top:3px;flex-shrink:0;width:18px;height:18px;cursor:pointer;}
  .checkbox-row label{font-size:.85rem;color:#374151;line-height:1.5;cursor:pointer;}
  .btn-sign{width:100%;background:var(--navy);color:#fff;border:none;border-radius:10px;padding:1rem;font-weight:700;font-size:1rem;cursor:pointer;transition:background .2s;margin-top:.5rem;}
  .btn-sign:hover{background:var(--red);}
  .btn-sign:disabled{background:#9ca3af;cursor:not-allowed;}
  .already-signed{background:#d1fae5;border:1.5px solid #a7f3d0;border-radius:12px;padding:1.5rem;text-align:center;margin-bottom:2rem;}
  .scroll-notice{background:#fef3c7;border:1.5px solid #fcd34d;border-radius:8px;padding:.65rem 1rem;font-size:.8rem;color:#92400e;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;}
</style>

<div class="waiver-hero">
  <h1>Liability Waiver</h1>
  <p>Please read and sign before your first lesson with Coach Kristine.</p>
</div>

<div class="waiver-body">

  @if(session('success'))
  <div style="background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.5rem;font-size:.88rem;font-weight:600;">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
  <div style="background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.5rem;font-size:.88rem;">
    @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
  </div>
  @endif

  @if($alreadySigned)
  <div class="already-signed">
    <div style="font-size:2rem;">✅</div>
    <div style="font-weight:700;color:#065f46;font-size:1rem;margin-top:.5rem;">Waiver Already Signed</div>
    <div style="font-size:.85rem;color:#047857;margin-top:.25rem;">
      Signed as <strong>{{ $existingWaiver->signed_name }}</strong>
      on {{ $existingWaiver->signed_at->format('F j, Y \a\t g:i A') }}
      (v{{ $existingWaiver->version }})
    </div>
    <a href="/" style="display:inline-block;margin-top:1rem;background:var(--navy);color:#fff;padding:.55rem 1.5rem;border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;">Back to Home</a>
  </div>
  @endif

  {{-- Waiver text --}}
  <div class="scroll-notice">📜 Please scroll through and read the full waiver before signing.</div>
  <div class="waiver-text" id="waiver-scroll">
    <h3>1. Assumption of Risk</h3>
    <p>Ice skating is a physical activity that involves inherent risks including, but not limited to, falls, collisions with other skaters or rink barriers, ice conditions, and equipment failure. These risks exist even when reasonable safety precautions are taken.</p>
    <p>I understand and acknowledge that participation in skating lessons with Coach Kristine Humphrey ("Kristine Skates") carries these inherent risks. I voluntarily assume all risks associated with skating activities.</p>

    <h3>2. Release of Liability</h3>
    <p>In consideration of being permitted to participate in skating lessons, I, on behalf of myself and/or my minor child, hereby release, waive, discharge, and covenant not to sue Kristine Humphrey, Kristine Skates, and their respective agents, employees, and representatives (collectively "Released Parties") from any and all liability, claims, demands, actions, or causes of action whatsoever arising out of or related to any loss, damage, injury, or death that may be sustained while participating in skating lessons, whether caused by the negligence of the Released Parties or otherwise, to the fullest extent permitted by law.</p>

    <h3>3. Indemnification</h3>
    <p>I agree to indemnify and hold harmless the Released Parties from any loss, liability, damage, or costs, including attorney's fees, that they may incur due to my participation or my minor child's participation in skating lessons, whether caused by negligence of the Released Parties or otherwise.</p>

    <h3>4. Medical Authorization</h3>
    <p>I authorize Coach Kristine to consent to any necessary emergency medical treatment for my minor child in the event that I cannot be reached. I represent that the participant is in good physical condition and has no medical conditions that would prevent participation in skating activities, or I have disclosed any such conditions to Coach Kristine in advance.</p>

    <h3>5. Rules and Conduct</h3>
    <p>I agree that the participant will follow all rink rules, the instructions of Coach Kristine, and conduct themselves in a safe and respectful manner. I understand that Coach Kristine reserves the right to end a lesson without refund if a participant's behavior poses a safety risk.</p>

    <h3>6. Cancellation Policy</h3>
    <p>I understand that cancellations made less than 24 hours before a scheduled lesson will be billed at the full lesson rate. Lessons cancelled by Kristine Skates due to rink closures or coach illness will be rescheduled or refunded.</p>

    <h3>7. Photography and Media</h3>
    <p>I grant permission for Kristine Skates to photograph or video the participant during lessons for instructional and promotional purposes. I may opt out by notifying Coach Kristine in writing.</p>

    <h3>8. Governing Law</h3>
    <p>This waiver shall be governed by the laws of the State of Missouri. If any provision is found to be unenforceable, the remaining provisions shall remain in full force and effect.</p>

    <h3>9. Entire Agreement</h3>
    <p>This waiver represents the entire agreement between the parties regarding liability and supersedes all prior discussions. I acknowledge that I have read this waiver, understand its terms, and sign it voluntarily.</p>

    <p style="font-size:.78rem;color:#9ca3af;margin-top:1.5rem;border-top:1px solid #f3f4f6;padding-top:1rem;">Waiver Version {{ \App\Models\LiabilityWaiver::CURRENT_VERSION }} · Effective March 2026 · Kristine Skates · St. Louis, Missouri</p>
  </div>

  @if(!$alreadySigned)
  {{-- Sign form --}}
  <div class="sign-box">
    <div class="sign-title">✍️ Sign This Waiver</div>
    <form method="POST" action="{{ route('waiver.sign') }}" id="waiver-form">
      @csrf
      <div class="mfg">
        <label>Your Full Legal Name (typed signature) *</label>
        <input type="text" name="signed_name" placeholder="Type your full name to sign"
               value="{{ old('signed_name', auth('client')->user()?->full_name) }}"
               required autocomplete="name" id="sign-name-input">
        <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Must match the name on your account</div>
      </div>

      @guest('client')
      <div class="mfg">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" required>
        <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Must match your registered account email</div>
      </div>
      @endguest

      <div class="checkbox-row">
        <input type="checkbox" id="confirm-read" onchange="checkReady()">
        <label for="confirm-read">I have read the entire waiver above and understand its contents.</label>
      </div>
      <div class="checkbox-row">
        <input type="checkbox" id="confirm-agree" onchange="checkReady()">
        <label for="confirm-agree">I agree to the terms of this waiver and understand I am waiving certain legal rights. If signing on behalf of a minor, I confirm I am the parent or legal guardian.</label>
      </div>
      <div class="checkbox-row">
        <input type="checkbox" id="confirm-voluntary" onchange="checkReady()">
        <label for="confirm-voluntary">I am signing this waiver voluntarily and acknowledge that my typed name constitutes my legal electronic signature.</label>
      </div>

      <button type="submit" class="btn-sign" id="sign-btn" disabled>
        ✍️ Sign Waiver
      </button>
      <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.75rem;">
        Your IP address and timestamp will be recorded. Version {{ \App\Models\LiabilityWaiver::CURRENT_VERSION }}.
      </p>
    </form>
  </div>
  @endif

</div>

<script>
function checkReady() {
  const allChecked = document.getElementById('confirm-read').checked
    && document.getElementById('confirm-agree').checked
    && document.getElementById('confirm-voluntary').checked;
  const hasName = document.getElementById('sign-name-input').value.trim().length > 3;
  document.getElementById('sign-btn').disabled = !(allChecked && hasName);
}
document.getElementById('sign-name-input')?.addEventListener('input', checkReady);
</script>
@endsection
