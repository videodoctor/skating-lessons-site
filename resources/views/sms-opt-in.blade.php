@extends('layouts.app')
@section('title', 'SMS Opt-In Disclosure — Kristine Skates')
@section('content')
<div style="max-width:760px;margin:0 auto;padding:3rem 1.5rem;font-family:Arial,sans-serif;">

  <div style="background:#001F5B;border-radius:12px;padding:2rem;text-align:center;margin-bottom:2rem;">
    <h1 style="color:#fff;font-size:1.6rem;margin:0 0 .5rem;">SMS Opt-In Disclosure</h1>
    <p style="color:rgba(255,255,255,.7);margin:0;font-size:.9rem;">Kristine Skates · kristineskates.com · St. Louis, MO</p>
    <p style="color:rgba(255,255,255,.5);margin:.5rem 0 0;font-size:.8rem;">This page is provided for compliance verification purposes.</p>
  </div>

  {{-- Program Description --}}
  <div style="background:#f8fafc;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:#001F5B;margin:0 0 .75rem;">Program Description</h2>
    <p style="color:#374151;font-size:.92rem;line-height:1.7;margin:0 0 .75rem;">
      <strong>Kristine Skates</strong> is a private skating instruction business based in St. Louis, MO.
      This SMS program sends automated lesson reminders to clients approximately 30 hours before scheduled skating lessons.
      Users may also receive lesson confirmations, one-time passcodes for account verification, and responses to keyword messages (LESSONS, SKATE, HELP).
      All messages identify the sender as <strong>Kristine Skates</strong>.
    </p>
    <p style="color:#374151;font-size:.92rem;line-height:1.7;margin:0;">
      <strong>No mobile information will be shared with third parties or affiliates for marketing or promotional purposes at any time.</strong>
      Phone numbers are transmitted to Twilio solely as a delivery service provider and are not used for any marketing purposes.
    </p>
  </div>

  {{-- Method 1: Booking Form --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:#001F5B;margin:0 0 .25rem;">Opt-In Method 1: Lesson Booking Form</h2>
    <p style="font-size:.82rem;color:#6b7280;margin:0 0 .5rem;">
      URL: <a href="{{ url('/book') }}" style="color:#001F5B;">https://kristineskates.com/book</a>
      (select any service → date → time to reach the booking form)
    </p>
    <p style="font-size:.88rem;color:#374151;margin:0 0 1rem;">
      During the lesson booking process, users provide their name, email, and an <strong>optional</strong> phone number.
      The SMS consent checkbox below is <strong>optional and unchecked by default</strong> — it is never pre-checked and is not required to complete a booking.
      Upon opting in, users immediately receive a confirmation SMS.
    </p>

    <div style="background:#f0f4ff;border:1.5px solid #c7d2fe;border-radius:8px;padding:1.25rem;margin-bottom:1rem;">
      <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;margin:0 0 .75rem;">Phone Field (optional, as shown to users):</p>
      <div style="margin-bottom:.5rem;">
        <label style="display:block;font-weight:600;font-size:.9rem;color:#374151;margin-bottom:.4rem;">
          Phone <span style="font-weight:400;color:#9ca3af;font-size:.85rem;">(optional, for SMS reminders)</span>
        </label>
        <input type="tel" disabled placeholder="(314) 555-0000"
               style="width:100%;padding:.7rem 1rem;border:2px solid #e5eaf2;border-radius:8px;font-size:.95rem;background:#f9fafb;">
      </div>
    </div>

    <div style="background:#f0f4ff;border:1.5px solid #c7d2fe;border-radius:8px;padding:1.25rem;">
      <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;margin:0 0 .75rem;">SMS Consent Checkbox (optional, unchecked by default, as shown to users):</p>
      <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:default;">
        <input type="checkbox" disabled
               style="margin-top:3px;width:18px;height:18px;flex-shrink:0;">
        <span style="font-size:.85rem;color:#374151;line-height:1.6;">
          <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders and availability notifications.
          You will receive a confirmation text upon opting in. Message frequency varies.
          Message and data rates may apply. Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
          View our <a href="{{ url('/privacy-policy') }}" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
        </span>
      </label>
    </div>
  </div>

  {{-- Method 2: Registration Form --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:#001F5B;margin:0 0 .25rem;">Opt-In Method 2: Account Registration</h2>
    <p style="font-size:.82rem;color:#6b7280;margin:0 0 .5rem;">
      URL: <a href="{{ url('/client/register') }}" style="color:#001F5B;">https://kristineskates.com/client/register</a>
    </p>
    <p style="font-size:.88rem;color:#374151;margin:0 0 1rem;">
      During account creation, users provide an <strong>optional</strong> phone number.
      The SMS consent checkbox below is <strong>optional and unchecked by default</strong> — it is never pre-checked and is not required to create an account.
      Upon opting in, users immediately receive a confirmation SMS.
    </p>

    <div style="background:#f0f4ff;border:1.5px solid #c7d2fe;border-radius:8px;padding:1.25rem;margin-bottom:1rem;">
      <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;margin:0 0 .75rem;">Phone Field (optional, as shown to users):</p>
      <div style="margin-bottom:.5rem;">
        <label style="display:block;font-weight:700;color:#374151;margin-bottom:.4rem;">
          Phone <span style="font-weight:400;color:#9ca3af;font-size:.85rem;">(optional, for SMS reminders)</span>
        </label>
        <input type="tel" disabled placeholder="(314) 555-0000"
               style="width:100%;padding:.6rem 1rem;border:1.5px solid #e5eaf2;border-radius:8px;font-size:.95rem;background:#f9fafb;">
      </div>
    </div>

    <div style="background:#f0f4ff;border:1.5px solid #c7d2fe;border-radius:8px;padding:1.25rem;">
      <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;margin:0 0 .75rem;">SMS Consent Checkbox (optional, unchecked by default, as shown to users):</p>
      <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:default;">
        <input type="checkbox" disabled
               style="margin-top:3px;width:18px;height:18px;flex-shrink:0;">
        <span style="font-size:.85rem;color:#374151;line-height:1.6;">
          <strong>Optional:</strong> I agree to receive SMS text messages from Kristine Skates, including lesson reminders and availability notifications.
          You will receive a confirmation text upon opting in. Message frequency varies.
          Message and data rates may apply. Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
          View our <a href="{{ url('/privacy-policy') }}" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
        </span>
      </label>
    </div>
  </div>

  {{-- Required disclosures --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;color:#001F5B;margin:0 0 .75rem;">Required Disclosures</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;width:40%;">Program Name</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Kristine Skates Lesson Reminders</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Sender</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Kristine Skates (kristineskates.com)</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Message Frequency</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Varies based on scheduled lessons (typically 1 reminder per lesson booked)</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Message &amp; Data Rates</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">May apply depending on carrier</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Opt-In Confirmation</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Users receive an immediate confirmation SMS upon opting in</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Opt-Out (STOP)</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Reply <strong>STOP</strong> — user receives confirmation that no further messages will be sent and is unsubscribed immediately</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Help (HELP)</td>
        <td style="padding:.6rem .75rem;color:#6b7280;">Reply <strong>HELP</strong> — user receives brand name, available keywords, and support email</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Support Contact</td>
        <td style="padding:.6rem .75rem;color:#6b7280;"><a href="mailto:kristine@kristineskates.com" style="color:#001F5B;">kristine@kristineskates.com</a> · 314-314-7528</td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Third-Party Sharing</td>
        <td style="padding:.6rem .75rem;color:#6b7280;"><strong>No mobile information will be shared with third parties or affiliates for marketing or promotional purposes at any time.</strong></td>
      </tr>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Privacy Policy</td>
        <td style="padding:.6rem .75rem;"><a href="{{ url('/privacy-policy') }}" style="color:#001F5B;">https://kristineskates.com/privacy-policy</a></td>
      </tr>
      <tr>
        <td style="padding:.6rem .75rem;font-weight:600;color:#374151;">Terms &amp; Conditions</td>
        <td style="padding:.6rem .75rem;"><a href="{{ url('/terms-and-conditions') }}" style="color:#001F5B;">https://kristineskates.com/terms-and-conditions</a></td>
      </tr>
    </table>
  </div>

  {{-- Sample messages --}}
  <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:10px;padding:1.5rem;">
    <h2 style="font-size:1.1rem;color:#001F5B;margin:0 0 .75rem;">Sample Messages</h2>
    @foreach([
      'Opt-In Confirmation (sent immediately upon opting in)' => 'You are now opted in to SMS lesson reminders from Kristine Skates. Msg frequency varies. Msg & data rates may apply. Reply STOP to cancel or HELP for help. — Kristine Skates',
      'Lesson Reminder' => 'Reminder: Your skating lesson for [Student] is tomorrow at 3:30 PM at Creve Coeur Ice Arena. $55 due at lesson. Reply YES to confirm or NO to cancel. Cancellations less than 24 hours before the lesson will be billed at the full rate. — Kristine Skates',
      'YES Confirmation + Payment Link' => 'Confirmed! Your skating lesson for [Student] is Saturday, March 15 at 12:00 PM at Creve Coeur Ice Arena. Pay $55 via Venmo: venmo.com/Kristine-Humphrey Reply STOP to opt out. — Kristine Skates',
      'LESSONS Keyword Reply' => 'Upcoming lessons for Jane: Sat Mar 15 12:00PM Creve Coeur, Wed Mar 19 3:30PM Brentwood. Reply HELP for assistance or STOP to opt out. — Kristine Skates',
      'SKATE Keyword Reply' => "Today's public skate times: Creve Coeur 9:15AM-1:00PM, Brentwood 2:00PM-4:00PM, Webster Groves 1:00PM-3:00PM. Book a lesson at kristineskates.com — Kristine Skates",
      'STOP Response' => 'You have been unsubscribed from Kristine Skates SMS messages. No further messages will be sent. Reply START to resubscribe or HELP for assistance.',
      'HELP Response' => 'Kristine Skates help: Reply YES to confirm a lesson, NO to cancel, LESSONS for upcoming lessons, SKATE for today\'s public skate times. Contact: kristine@kristineskates.com Reply STOP to opt out. — Kristine Skates',
      'One-Time Passcode' => 'Your Kristine Skates verification code is: 456431. Reply STOP to opt out. — Kristine Skates',
    ] as $label => $msg)
    <div style="margin-bottom:.85rem;">
      <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.25rem;">{{ $label }}</div>
      <div style="background:#f8fafc;border-radius:8px;padding:.75rem 1rem;font-size:.85rem;color:#374151;font-family:monospace;line-height:1.6;">{{ $msg }}</div>
    </div>
    @endforeach
  </div>

</div>
@endsection
