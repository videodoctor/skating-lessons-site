<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
<style>
  body { margin:0; padding:0; background:#e8eef4; font-family:'Helvetica Neue',Arial,sans-serif; -webkit-text-size-adjust:100%; }
  .wrapper { background:url('https://kristineskates.com/images/ice-texture-email.jpg') center center / cover no-repeat fixed #e8eef4; padding:32px 16px; min-height:100vh; }
  .container { max-width:560px; margin:0 auto; border:1.5px solid #bfcad6; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,31,91,.1); }
  .header { background:#001F5B; padding:24px 32px; text-align:center; }
  .header-logo { width:36px; height:36px; vertical-align:middle; margin-right:10px; }
  .header-text { font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:28px; font-weight:400; color:#ffffff; letter-spacing:3px; vertical-align:middle; }
  .header-sub { font-family:'Helvetica Neue',Arial,sans-serif; font-size:11px; color:rgba(255,255,255,.45); letter-spacing:2px; text-transform:uppercase; margin-top:6px; }
  .body { background:#ffffff; padding:32px; }
  .greeting { font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:26px; font-weight:400; color:#001F5B; letter-spacing:1px; margin:0 0 16px; }
  .text { font-size:15px; line-height:1.7; color:#374151; margin:0 0 14px; }
  .detail-box { background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:18px 20px; margin:20px 0; }
  .highlight-box { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px 18px; margin:18px 0; font-size:14px; color:#92400e; }
  .info-band { background:#f0f4ff; padding:20px 32px; margin:20px -32px; }
  .info-band h3 { font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:16px; color:#001F5B; letter-spacing:1px; margin:0 0 8px; }
  .warn-band { background:#fef3c7; padding:16px 32px; margin:20px -32px; }
  .warn-band p { font-size:13px; color:#92400e; margin:0; line-height:1.6; }
  .closing-band { background:#001F5B; border-radius:8px; padding:18px 24px; margin:24px 0 0; text-align:center; }
  .closing-band p { color:#ffffff; font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:18px; letter-spacing:1px; margin:0; }
  .cta-btn { display:inline-block; background:#C8102E; color:#ffffff !important; font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:17px; font-weight:400; letter-spacing:1px; padding:14px 36px; border-radius:8px; text-decoration:none; margin:20px 0; }
  .cta-btn-secondary { display:inline-block; background:#001F5B; color:#ffffff !important; font-family:'Bebas Neue','Helvetica Neue',Arial,sans-serif; font-size:15px; font-weight:400; letter-spacing:1px; padding:11px 28px; border-radius:8px; text-decoration:none; margin:8px 4px; }
  .divider { border:none; border-top:1px solid #e5eaf2; margin:24px 0; }
  .accent-bar { height:4px; background:linear-gradient(90deg, #C8102E 0%, #C8102E 30%, #001F5B 100%); }
  .footer { background:#0f172a; padding:28px 32px; text-align:center; }
  .footer-text { font-size:12px; color:#64748b; line-height:1.7; }
  .footer-link { color:#94a3b8; text-decoration:none; font-weight:600; }
  .footer-link:hover { color:#ffffff; }
  .footer-nav { margin-bottom:16px; }
  .footer-nav a { display:inline-block; color:#94a3b8; text-decoration:none; font-size:13px; font-weight:600; margin:0 10px; }
  .footer-divider { border:none; border-top:1px solid rgba(255,255,255,.08); margin:16px 0; }
  @media only screen and (max-width:600px) {
    .body { padding:24px 20px; }
    .header { padding:20px; }
    .footer { padding:20px; }
    .header-text { font-size:22px; }
  }
</style>
</head>
<body>
<div class="wrapper">
  <div class="container">
    {{-- Header --}}
    <div class="header">
      <div>
        <img src="https://kristineskates.com/images/HOCKEY_SKATER.webp" alt="" class="header-logo" style="filter:brightness(0) invert(1);">
        <span class="header-text">KRISTINE SKATES</span>
      </div>
      <div class="header-sub">Private Skating Lessons &bull; St. Louis, MO</div>
    </div>
    <div class="accent-bar"></div>

    {{-- Body --}}
    <div class="body">
      @yield('content')
    </div>

    {{-- Footer --}}
    <div class="footer">
      <div class="footer-nav">
        <a href="https://kristineskates.com">Home</a>
        <a href="https://kristineskates.com/book">Book a Lesson</a>
        <a href="https://kristineskates.com/rinks">Rinks</a>
      </div>
      <hr class="footer-divider">
      <div class="footer-text">
        Kristine Humphrey &mdash; Private Skating Lessons<br>
        St. Louis, Missouri
      </div>
      <div style="margin-top:14px;">
        <a href="https://kristineskates.com/privacy-policy" class="footer-link" style="font-size:11px;">Privacy Policy</a>
        <span style="color:#475569;margin:0 6px;">&bull;</span>
        <a href="https://kristineskates.com/terms-and-conditions" class="footer-link" style="font-size:11px;">Terms</a>
      </div>
      <div class="footer-text" style="margin-top:12px;font-size:11px;color:#475569;">
        You're receiving this because you booked or registered at kristineskates.com
      </div>
    </div>
  </div>
</div>
</body>
</html>
