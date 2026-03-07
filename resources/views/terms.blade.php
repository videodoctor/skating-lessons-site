@extends('layouts.app')
@section('title', 'Terms & Conditions — Kristine Skates')
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

  .highlight-box {
    background:#fff7ed;
    border:1.5px solid #fed7aa;
    border-radius:8px;
    padding:1rem 1.25rem;
    margin:1rem 0;
    font-size:.9rem;
    color:#92400e;
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
  <h1>Terms & Conditions</h1>
  <p>Kristine Skates — Effective March 7, 2026</p>
</div>

<div class="policy-body">

  <div class="policy-updated">
    Last updated: March 7, 2026. Please read these terms carefully before booking a lesson or creating an account.
  </div>

  <div class="policy-section">
    <h2>Agreement to Terms</h2>
    <p>By accessing kristineskates.com, creating an account, or booking a skating lesson with Coach Kristine Humphrey ("Kristine Skates," "we," "us," or "our"), you agree to be bound by these Terms and Conditions. If you are booking on behalf of a minor, you represent that you are the parent or legal guardian of that child and agree to these terms on their behalf.</p>
  </div>

  <div class="policy-section">
    <h2>Services</h2>
    <p>Kristine Skates provides private one-on-one skating instruction at various ice rinks in the St. Louis, Missouri area. Lessons are provided by Coach Kristine Humphrey, an experienced skating instructor.</p>
    <p>All lesson services, pricing, and availability are subject to change at any time. We reserve the right to decline or cancel bookings at our discretion.</p>
  </div>

  <div class="policy-section">
    <h2>Booking & Confirmation</h2>
    <p>Lesson bookings are not confirmed until you receive a confirmation from Coach Kristine. Submitting a booking request does not guarantee availability. You will receive a booking confirmation via email once your lesson is approved.</p>
    <ul>
      <li>Bookings are subject to rink availability and Coach Kristine's schedule</li>
      <li>You must provide accurate contact information including a valid phone number</li>
      <li>Lesson times are in Central Time (CT)</li>
      <li>You are responsible for arriving at the correct rink location at the scheduled time</li>
    </ul>
  </div>

  <div class="policy-section">
    <h2>Payment</h2>
    <p>Payment for lessons is due at the time of the lesson unless otherwise arranged. We accept:</p>
    <ul>
      <li><strong>Venmo:</strong> @Kristine-Humphrey — please include your booking confirmation code in the Venmo note</li>
      <li><strong>Cash:</strong> Exact amount appreciated</li>
    </ul>
    <p>We do not accept credit cards, checks, or other forms of payment. Failure to pay for a completed lesson may result in suspension of booking privileges.</p>
  </div>

  <div class="policy-section">
    <h2>Cancellation Policy</h2>
    <div class="highlight-box">
      ⚠️ Cancellations made less than 24 hours before a scheduled lesson will be billed at the full quoted lesson rate.
    </div>
    <p>To cancel a lesson without charge, you must cancel at least 24 hours before the scheduled start time. You may cancel by:</p>
    <ul>
      <li>Replying NO to your SMS lesson reminder (if received more than 24 hours before the lesson)</li>
      <li>Contacting Coach Kristine directly by phone or email</li>
    </ul>
    <p>Lessons cancelled by Kristine Skates due to rink closures, ice emergencies, or coach illness will be rescheduled or refunded at no charge to the client.</p>
    <p>Repeated no-shows or late cancellations may result in a requirement for advance payment or suspension of booking privileges.</p>
  </div>

  <div class="policy-section">
    <h2>SMS Lesson Reminders</h2>
    <p>By providing your phone number and opting in to SMS communications, you consent to receive text message lesson reminders approximately 30 hours before each scheduled lesson. You may reply YES to confirm or NO to cancel your lesson. Standard message and data rates may apply. Reply STOP at any time to opt out of SMS reminders.</p>
    <p>Cancellations via SMS reply received less than 24 hours before the scheduled lesson are subject to the cancellation fee described above.</p>
  </div>

  <div class="policy-section">
    <h2>Liability Waiver Requirement</h2>
    <p>Before participating in any skating lesson, all clients (or parents/guardians of minor students) must sign our electronic Liability Waiver. Ice skating is a physical activity that carries inherent risks including falls, collisions, and injury. By signing the waiver, you acknowledge these risks and release Kristine Skates and Coach Kristine Humphrey from liability for injuries sustained during lessons, except in cases of gross negligence.</p>
    <p>No lesson will be conducted until a valid, signed waiver is on file for the student.</p>
  </div>

  <div class="policy-section">
    <h2>Student Conduct & Safety</h2>
    <p>All students are expected to:</p>
    <ul>
      <li>Wear properly fitted ice skates and appropriate protective gear (helmets strongly recommended for beginners and all youth skaters)</li>
      <li>Follow all rink rules and the instructions of Coach Kristine at all times</li>
      <li>Disclose any medical conditions, injuries, or physical limitations prior to beginning lessons</li>
      <li>Arrive warmed up and ready to skate at the scheduled time</li>
    </ul>
    <p>Kristine Skates reserves the right to end a lesson early without refund if a student's behavior poses a safety risk to themselves, the coach, or other rink users.</p>
  </div>

  <div class="policy-section">
    <h2>Minor Students</h2>
    <p>Parents or legal guardians must accompany minor students to their first lesson and remain available (on-site or by phone) during all lessons. By booking a lesson for a minor, you represent that you have legal authority to do so and accept full responsibility for the minor's participation.</p>
  </div>

  <div class="policy-section">
    <h2>Intellectual Property</h2>
    <p>All content on kristineskates.com, including text, images, logos, and design, is the property of Kristine Skates and may not be reproduced without written permission.</p>
  </div>

  <div class="policy-section">
    <h2>Limitation of Liability</h2>
    <p>To the fullest extent permitted by law, Kristine Skates and Coach Kristine Humphrey shall not be liable for any indirect, incidental, special, or consequential damages arising out of or related to your use of our services. Our total liability shall not exceed the amount paid for the lesson in question.</p>
  </div>

  <div class="policy-section">
    <h2>Governing Law</h2>
    <p>These Terms and Conditions are governed by the laws of the State of Missouri. Any disputes shall be resolved in the courts of St. Louis County, Missouri.</p>
  </div>

  <div class="policy-section">
    <h2>Changes to These Terms</h2>
    <p>We reserve the right to update these Terms and Conditions at any time. Continued use of our services after changes are posted constitutes acceptance of the revised terms. We recommend reviewing this page periodically.</p>
  </div>

  <div class="policy-section">
    <h2>Privacy</h2>
    <p>Your use of Kristine Skates is also governed by our <a href="{{ route('privacy') }}" style="color:var(--navy);font-weight:600;">Privacy Policy</a>, which is incorporated into these Terms by reference.</p>
  </div>

  <div class="contact-box">
    <h3>Questions About These Terms?</h3>
    <p>Contact Coach Kristine directly:</p>
    <p>📧 <a href="mailto:kristine@kristineskates.com">kristine@kristineskates.com</a></p>
    <p>🌐 <a href="https://kristineskates.com">kristineskates.com</a></p>
    <p style="margin-top:1rem;font-size:.8rem;opacity:.5;">Kristine Skates · St. Louis, Missouri</p>
  </div>

</div>
@endsection
