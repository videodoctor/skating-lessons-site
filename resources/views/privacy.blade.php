@extends('layouts.app')
@section('title', 'Privacy Policy — Kristine Skates')
@section('content')
<style>
  :root { --navy:#001F5B; --red:#C8102E; --gold:#C9A84C; --ice:#E8F5FB; }

  .policy-hero { background:var(--navy); color:#fff; padding:4rem 2rem 3rem; text-align:center; }
  .policy-hero h1 { font-family:'Bebas Neue',sans-serif; font-size:3.5rem; margin:0; letter-spacing:.02em; }
  .policy-hero p { color:rgba(255,255,255,.6); font-size:.95rem; margin-top:.5rem; }

  .policy-body { max-width:780px; margin:0 auto; padding:3rem 1.5rem 5rem; }

  .policy-section { margin-bottom:2.5rem; }
  .policy-section h2 {
    font-family:'Bebas Neue',sans-serif;
    font-size:1.5rem;
    color:var(--navy);
    border-left:4px solid var(--gold);
    padding-left:.75rem;
    margin-bottom:1rem;
  }
  .policy-section p, .policy-section li {
    font-size:.95rem;
    line-height:1.75;
    color:#374151;
  }
  .policy-section ul {
    padding-left:1.25rem;
    margin:.5rem 0;
  }
  .policy-section li { margin-bottom:.3rem; }

  .policy-updated {
    background:var(--ice);
    border:1.5px solid #bfdbfe;
    border-radius:8px;
    padding:.75rem 1.25rem;
    font-size:.83rem;
    color:#1e40af;
    margin-bottom:2rem;
    font-weight:600;
  }

  .contact-box {
    background:var(--navy);
    color:#fff;
    border-radius:12px;
    padding:1.75rem;
    margin-top:2rem;
  }
  .contact-box h3 { font-family:'Bebas Neue',sans-serif; font-size:1.4rem; margin:0 0 .5rem; }
  .contact-box p { font-size:.9rem; opacity:.8; margin:.25rem 0; }
  .contact-box a { color:var(--gold); }
</style>

<div class="policy-hero">
  <h1>Privacy Policy</h1>
  <p>Kristine Skates — Effective March 7, 2026</p>
</div>

<div class="policy-body">

  <div class="policy-updated">
    Last updated: March 7, 2026. This policy applies to all users of kristineskates.com and clients of Coach Kristine Humphrey.
  </div>

  <div class="policy-section">
    <h2>Who We Are</h2>
    <p>Kristine Skates is a private skating instruction business operated by Coach Kristine Humphrey in the St. Louis, Missouri area. We provide one-on-one skating lessons at area rinks including Creve Coeur Ice Arena, Kirkwood Ice Arena, Maryville University Hockey Center, Brentwood Ice Rink, and Webster Groves Ice Arena.</p>
    <p>This Privacy Policy explains how we collect, use, and protect your personal information when you use our website at kristineskates.com or book a skating lesson with us.</p>
  </div>

  <div class="policy-section">
    <h2>Information We Collect</h2>
    <p>We collect information you provide directly to us, including:</p>
    <ul>
      <li><strong>Account information:</strong> Your first and last name, email address, phone number, and password when you create an account.</li>
      <li><strong>Student information:</strong> Names and ages of children or students you register for lessons.</li>
      <li><strong>Booking information:</strong> Lesson dates, times, rink locations, and service selections.</li>
      <li><strong>Payment information:</strong> Payment type (cash or Venmo), Venmo username if applicable. We do not store credit card numbers.</li>
      <li><strong>Communications:</strong> Messages you send us and SMS replies to lesson reminders.</li>
    </ul>
  </div>

  <div class="policy-section">
    <h2>How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
      <li>Create and manage your account and lesson bookings</li>
      <li>Send booking confirmations and lesson reminders by email</li>
      <li>Send SMS text message reminders about upcoming lessons (with your consent)</li>
      <li>Process lesson confirmations and cancellations via SMS reply</li>
      <li>Contact you regarding your lessons, schedule changes, or cancellations</li>
      <li>Maintain records of lessons and payments for our business operations</li>
      <li>Improve our website and services</li>
    </ul>
  </div>

  <div class="policy-section">
    <h2>SMS Text Message Communications</h2>
    <p>By providing your phone number and creating an account, you may opt in to receive SMS text message reminders about your upcoming skating lessons. Specifically:</p>
    <ul>
      <li>You will receive a reminder approximately 30 hours before each scheduled lesson</li>
      <li>You may reply YES to confirm your lesson or NO to cancel</li>
      <li>Cancellations received via SMS less than 24 hours before the lesson may be subject to a cancellation fee at the quoted lesson rate</li>
      <li>Message and data rates may apply depending on your mobile carrier</li>
      <li>You may opt out at any time by replying STOP to any SMS message. Reply HELP for assistance.</li>
      <li>SMS reminders are sent from a dedicated Kristine Skates phone number via Twilio</li>
    </ul>
    <p>We will never sell your phone number or use it for marketing unrelated to your skating lessons. <strong>No mobile information (including phone numbers collected via SMS opt-in) will be shared with third parties or affiliates for marketing or promotional purposes at any time.</strong></p>
  </div>

  <div class="policy-section">
    <h2>Email Communications</h2>
    <p>When you create an account, you agree to receive transactional emails including booking confirmations, lesson reminders, and account notifications. These emails are necessary for the operation of our service and cannot be opted out of while maintaining an active account.</p>
    <p>We do not send promotional or marketing emails without your explicit consent.</p>
  </div>

  <div class="policy-section">
    <h2>Payment Information</h2>
    <p>Kristine Skates accepts payment via cash or Venmo. We do not process credit cards and do not store any financial account information. Venmo transactions are processed through Venmo's platform and are subject to Venmo's own privacy policy. We may record your Venmo username solely for the purpose of matching payments to bookings.</p>
  </div>

  <div class="policy-section">
    <h2>Information Sharing</h2>
    <p>We do not sell, trade, or rent your personal information to third parties. We may share limited information with:</p>
    <ul>
      <li><strong>Twilio (SMS delivery):</strong> Your phone number is transmitted to Twilio solely as a service provider to deliver SMS lesson reminders on our behalf. Twilio does not use this information for their own marketing purposes. Twilio's privacy policy is available at twilio.com/legal/privacy. This data sharing is solely for service delivery and does not constitute sharing for marketing or promotional purposes.</li>
      <li><strong>Service providers:</strong> We use Cloudflare for security and bot protection on our registration forms.</li>
      <li><strong>Legal requirements:</strong> We may disclose information if required by law or to protect the rights and safety of our clients.</li>
    </ul>
  </div>

  <div class="policy-section">
    <h2>Children's Privacy</h2>
    <p>Many of our students are minors. We collect information about child students (name, age) only as provided by a parent or guardian who has created an account and accepted our terms. We do not knowingly collect personal information directly from children under 13. Parents and guardians are responsible for the accuracy of information provided about their children.</p>
  </div>

  <div class="policy-section">
    <h2>Liability Waiver</h2>
    <p>Before participating in skating lessons, clients (or parents/guardians of minor students) are required to sign an electronic liability waiver. The signed waiver, including the date, time, and IP address of signing, is stored securely as a record of consent.</p>
  </div>

  <div class="policy-section">
    <h2>Data Security</h2>
    <p>We take reasonable measures to protect your personal information, including encrypted connections (HTTPS), hashed passwords, and access controls on our systems. However, no method of transmission over the internet is 100% secure.</p>
  </div>

  <div class="policy-section">
    <h2>Data Retention</h2>
    <p>We retain your account information and booking history for as long as your account is active or as needed to provide services. You may request deletion of your account and associated data by contacting us directly.</p>
  </div>

  <div class="policy-section">
    <h2>Your Rights</h2>
    <p>You have the right to:</p>
    <ul>
      <li>Access the personal information we hold about you</li>
      <li>Request correction of inaccurate information</li>
      <li>Request deletion of your account and data</li>
      <li>Opt out of SMS communications at any time by replying STOP. Reply HELP for assistance.</li>
      <li>Contact us with any privacy concerns</li>
    </ul>
  </div>

  <div class="policy-section">
    <h2>Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting the new policy on this page with an updated effective date. Continued use of our services after changes constitutes acceptance of the updated policy.</p>
  </div>

  <div class="contact-box">
    <h3>Questions About This Policy?</h3>
    <p>Contact Coach Kristine directly:</p>
    <p>📧 <a href="mailto:kristine@kristineskates.com">kristine@kristineskates.com</a></p>
    <p>🌐 <a href="https://kristineskates.com">kristineskates.com</a></p>
    <p style="margin-top:1rem;font-size:.8rem;opacity:.5;">Kristine Skates · St. Louis, Missouri</p>
  </div>

</div>
@endsection
