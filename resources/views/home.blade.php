@extends('layouts.app')

@section('title', 'Kristine Humphrey — Private Hockey Skating Lessons, St. Louis | Kristine Skates')

@push('head')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "LocalBusiness",
      "name": "Kristine Skates",
      "description": "Private 1-on-1 hockey skating lessons with Coach Kristine Humphrey in St. Louis, Missouri. All ages and skill levels welcome.",
      "url": "https://kristineskates.com",
      "logo": "https://kristineskates.com/images/HOCKEY_SKATER.png",
      "image": "https://kristineskates.com/images/kristine_and_mick_005.png",
      "telephone": "",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "St. Louis",
        "addressRegion": "MO",
        "addressCountry": "US"
      },
      "areaServed": {
        "@type": "City",
        "name": "St. Louis"
      },
      "founder": {
        "@type": "Person",
        "name": "Kristine Humphrey",
        "jobTitle": "Skating Coach",
        "description": "Private hockey skating instructor in St. Louis. Lead instructor for CHA Learn to Play. Coached with the Lady Cyclones and Lady Liberty programs.",
        "url": "https://kristineskates.com",
        "sameAs": [
          "https://kristineskates.com"
        ]
      },
      "priceRange": "$$",
      "knowsAbout": ["ice skating", "hockey skating", "skating lessons", "learn to skate", "learn to play hockey"]
    },
    {
      "@type": "SportsActivityLocation",
      "name": "Kristine Skates — Hockey Skating Lessons",
      "url": "https://kristineskates.com",
      "sport": "Ice Hockey"
    }
  ]
}
</script>
@endpush

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap');
  :root { --navy:#001F5B; --red:#C8102E; --ice:#E8F5FB; --gold:#C9A84C; }
  body { font-family:'DM Sans',sans-serif; }

  .hero { background:var(--navy); position:relative; overflow:hidden; min-height:600px; max-height:700px; height:92vh; display:flex; align-items:stretch; }
  .hero-inner { position:relative; width:100%; max-width:80rem; margin:0 auto; padding:0 1.5rem; display:flex; align-items:stretch; }
  @media(min-width:1024px) { .hero-inner { padding:0 2rem; } }
  .hero-lines { position:absolute;inset:0;pointer-events:none;opacity:.06;
    background-image:repeating-linear-gradient(90deg,#fff 0 2px,transparent 2px 120px),repeating-linear-gradient(0deg,#fff 0 1px,transparent 1px 80px); }
  .hero-accent { position:absolute;right:-60px;top:0;bottom:0;width:55%;
    background:linear-gradient(135deg,#0a3580 0%,#001240 100%);
    clip-path:polygon(12% 0,100% 0,100% 100%,0% 100%); }
  .hero-photo-wrap { position:absolute;right:0;top:0;bottom:0;overflow:hidden;display:flex;gap:6px;justify-content:flex-end; }
  .hero-photo-wrap video { height:100%;width:auto;object-fit:contain;object-position:center top;opacity:.6;mix-blend-mode:luminosity;flex:0 0 auto; }
  .hero-photo-wrap img { width:100%;height:100%;object-fit:cover;object-position:center top;opacity:.6;mix-blend-mode:luminosity; }
  .hero-video-secondary { display:block; }
  .hero-photo-wrap::after { content:'';position:absolute;inset:0;
    background:linear-gradient(90deg,var(--navy) 0%,rgba(0,15,60,.6) 15%,transparent 40%); }
  .hero-content { position:relative;z-index:2;display:flex;flex-direction:column;justify-content:space-between; }
  .hero-eyebrow { font-family:'Bebas Neue',sans-serif;font-size:1rem;letter-spacing:.35em;color:var(--gold); }
  .mobile-break { display:none; }
  @media(max-width:768px) { .mobile-break { display:inline; } }
  .hero-title { font-family:'Bebas Neue',sans-serif;font-size:clamp(3.5rem,8vw,7rem);line-height:.95;color:#fff; }
  .hero-title span { color:var(--red); }
  .hero-subtitle { font-family:'DM Serif Display',serif;font-style:italic;font-size:1.35rem;color:rgba(255,255,255,.75); }
  .hero-cta { display:inline-block;background:var(--red);color:#fff;font-weight:600;font-size:1.1rem;
    padding:.9rem 2.5rem;border-radius:4px;letter-spacing:.03em;transition:all .2s;
    box-shadow:0 8px 30px rgba(200,16,46,.35); }
  .hero-cta:hover { background:#a50d24;transform:translateY(-2px); }
  .hero-cta-ghost { display:inline-block;border:2px solid rgba(255,255,255,.4);color:rgba(255,255,255,.85);
    font-weight:500;font-size:1rem;padding:.85rem 2.2rem;border-radius:4px;transition:all .2s; }
  .hero-cta-ghost:hover { border-color:#fff;color:#fff;background:rgba(255,255,255,.07); }
  .hero-stat-num { font-family:'Bebas Neue',sans-serif;font-size:2.8rem;color:#fff;line-height:1; }
  .hero-stat-label { font-size:.8rem;color:rgba(255,255,255,.5);letter-spacing:.1em;text-transform:uppercase;margin-top:2px; }
  .ribbon { background:var(--red); }
  .section-label { font-family:'Bebas Neue',sans-serif;letter-spacing:.25em;font-size:.9rem;color:var(--red); }
  .section-title { font-family:'Bebas Neue',sans-serif;font-size:clamp(2.2rem,4vw,3.2rem);color:var(--navy);line-height:1.05; }
  .service-card { border:2px solid #e5eaf2;border-radius:10px;background:#fff;transition:all .25s; }
  .service-card:hover { border-color:var(--navy);box-shadow:0 20px 50px rgba(0,31,91,.1);transform:translateY(-4px); }
  .service-card.featured { border-color:var(--red); }
  .service-badge { display:inline-block;background:var(--red);color:#fff;font-size:.72rem;font-weight:700;
    letter-spacing:.1em;padding:3px 10px;border-radius:20px;text-transform:uppercase; }
  .service-price { font-family:'Bebas Neue',sans-serif;font-size:3rem;color:var(--navy);line-height:1; }
  .service-price span { font-size:1.4rem;vertical-align:top;margin-top:.4rem;display:inline-block; }
  .service-book-btn { display:block;text-align:center;background:var(--navy);color:#fff;
    font-weight:600;padding:.85rem;border-radius:6px;transition:all .2s; }
  .service-book-btn:hover { background:var(--red); }
  .service-book-btn.featured-btn { background:var(--red); }
  .service-book-btn.featured-btn:hover { background:#a50d24; }
  .bio-section { background:var(--ice); overflow:hidden; padding-bottom:5rem; }
  .bio-photo-wrap { position:relative;aspect-ratio:4/5;border-radius:8px;box-shadow:16px 16px 0 var(--navy);overflow:hidden; }
  .bio-photo { position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center;opacity:0;transition:opacity 2s ease-in-out; }
  .bio-photo.active { opacity:1; }
  .bio-quote { font-family:'DM Serif Display',serif;font-style:italic;font-size:1.5rem;color:var(--navy);
    line-height:1.5;border-left:4px solid var(--red);padding-left:1.5rem; }
  .credential-chip { display:inline-flex;align-items:center;gap:6px;background:#fff;
    border:1.5px solid #d0ddf0;border-radius:40px;padding:5px 14px;font-size:.85rem;font-weight:500;color:var(--navy); }
  .rink-card { background:var(--navy);border-radius:10px;padding:1.75rem;color:#fff;transition:transform .2s; }
  .rink-card:hover { transform:translateY(-3px); }
  .rink-subscribe-btn { display:inline-block;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.3);
    color:#fff;font-size:.85rem;font-weight:600;padding:.55rem 1.2rem;border-radius:5px;transition:all .2s; }
  .rink-subscribe-btn:hover { background:rgba(255,255,255,.22);border-color:#fff; }
  .testi-card { background:#fff;border-radius:10px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,31,91,.07);border-top:3px solid var(--red); }
  .stars { color:var(--gold);font-size:1.1rem;letter-spacing:2px; }
  .step-num { font-family:'Bebas Neue',sans-serif;font-size:4rem;color:var(--red);line-height:1;opacity:.25; }
  .cta-banner { background:linear-gradient(135deg,var(--navy) 0%,#002b87 100%);position:relative;overflow:hidden; }
  .cta-banner::before { content:'⛸️';position:absolute;right:3rem;top:50%;transform:translateY(-50%) rotate(-15deg);font-size:10rem;opacity:.07; }
  @media(max-width:768px) { .hero-inner{padding:0 1.25rem;} .hero-photo-wrap{position:absolute;inset:0;width:100%;display:block;} .hero-photo-wrap video,.hero-photo-wrap img{object-fit:cover;object-position:center top;opacity:.4;width:100%;height:100%;} .hero-video-secondary{display:none!important;} .hero-accent{display:none;} .hero-photo-wrap::before{display:none;} .hero-photo-wrap::after{background:linear-gradient(90deg,var(--navy) 0%,rgba(0,15,60,.7) 25%,transparent 55%),linear-gradient(to bottom,transparent 40%,var(--navy) 100%);} }
</style>

{{-- Dynamic sections rendered from admin config --}}
@foreach($sections as $section)
  @if($section['visible'] ?? true)
    @include('home.sections.' . $section['key'])
  @endif
@endforeach

<script>
(function() {
  const clips = [
    @foreach($heroMedia->where('type', 'video') as $hm)
    '{{ $hm->url }}',
    @endforeach
    @if($heroMedia->where('type', 'video')->isEmpty())
    '{{ asset("videos/mick_reel_001_web.mp4") }}',
    @endif
  ];
  const isMobile = window.innerWidth <= 768;

  // Shuffle clips for random initial order
  for (let i = clips.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [clips[i], clips[j]] = [clips[j], clips[i]];
  }

  // 3 fixed viewports
  const players = [
    { v: document.getElementById('hero-video-0'), s: document.getElementById('hero-src-0') },
    { v: document.getElementById('hero-video-1'), s: document.getElementById('hero-src-1') },
    { v: document.getElementById('hero-video-2'), s: document.getElementById('hero-src-2') },
  ];

  // Assign initial clips: first 3 from shuffled pool (or repeat if < 3)
  const numSlots = isMobile ? 1 : Math.min(3, clips.length);
  const current = [];
  for (let i = 0; i < numSlots; i++) current.push(i % clips.length);

  function pickNext(slotIdx) {
    // Pick a random clip not currently showing in any slot
    const inUse = current.filter((_, i) => i !== slotIdx);
    const available = [];
    for (let i = 0; i < clips.length; i++) {
      if (!inUse.includes(i) && i !== current[slotIdx]) available.push(i);
    }
    if (available.length > 0) return available[Math.floor(Math.random() * available.length)];
    return (current[slotIdx] + 1) % clips.length;
  }

  if (isMobile) {
    const p = players[0];
    p.v.preload = 'none';
    function startMobileVideo() {
      p.s.src = clips[current[0]];
      p.v.load();
      p.v.play().catch(() => {});
      p.v.addEventListener('ended', function() {
        current[0] = pickNext(0);
        p.s.src = clips[current[0]];
        p.v.load();
        p.v.play().catch(() => {});
      });
    }
    document.addEventListener('touchstart', function once() {
      startMobileVideo();
      document.removeEventListener('touchstart', once);
    });
    setTimeout(startMobileVideo, 3000);
  } else {
    // Desktop: all slots, each rotates independently through the pool
    for (let i = 0; i < numSlots; i++) {
      (function(slot) {
        var p = players[slot];
        p.s.src = clips[current[slot]];
        p.v.load();
        p.v.play().catch(() => {});
        p.v.addEventListener('ended', function() {
          current[slot] = pickNext(slot);
          p.s.src = clips[current[slot]];
          p.v.load();
          p.v.play().catch(() => {});
        });
      })(i);
    }
  }
})();
</script>

<script>
(function() {
  var wrap = document.getElementById('bioPhotoWrap');
  if (!wrap) return;
  var photos = wrap.querySelectorAll('.bio-photo');
  if (photos.length <= 1) return;
  var current = 0;
  setInterval(function() {
    photos[current].classList.remove('active');
    current = (current + 1) % photos.length;
    photos[current].classList.add('active');
  }, 5000);
})();
</script>

<style>#main-footer{margin-top:0!important;}</style>

{{-- ═══ WAITLIST MODAL ═══ --}}
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onTurnstileLoad" async defer></script>
<div id="waitlist-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this)closeWaitlistModal()">
  <div style="background:#fff;border-radius:12px;padding:1.5rem;max-width:480px;width:100%;max-height:90vh;overflow-y:auto;position:relative;">
    <button onclick="closeWaitlistModal()" style="position:absolute;top:.75rem;right:1rem;background:none;border:none;font-size:1.3rem;color:#9ca3af;cursor:pointer;">✕</button>
    <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:#001F5B;margin:0 0 .25rem;">Join the Waitlist</h2>
    <p style="font-size:.82rem;color:#6b7280;margin-bottom:1rem;">For: <strong id="wl-service-name"></strong></p>

    <div id="wl-errors" style="display:none;background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b;padding:.6rem .85rem;border-radius:7px;margin-bottom:.75rem;font-size:.82rem;"></div>

    <form id="waitlist-form" method="POST" action="">
      @csrf
      <input type="hidden" name="service_id" id="wl-service-id">
      <div style="margin-bottom:.55rem;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Your Name *</label>
        <input type="text" name="name" required placeholder="Parent/guardian name"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
      </div>
      <div style="margin-bottom:.55rem;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Email *</label>
        <input type="email" name="email" required
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
      </div>
      <div style="margin-bottom:.55rem;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Phone <span style="color:#9ca3af;">(for SMS reminders)</span></label>
        <input type="tel" name="phone" placeholder="(314) 555-0000"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;"
          oninput="wlFormatPhone(this)">
      </div>

      <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:8px;padding:.75rem .85rem;margin-bottom:.55rem;">
        <div style="font-weight:700;font-size:.8rem;color:#0c4a6e;margin-bottom:.4rem;">Skater Information</div>
        <div style="margin-bottom:.4rem;">
          <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Skater Name *</label>
          <input type="text" name="student_name" required placeholder="Name of the person skating"
            style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
          <div>
            <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Skater Age *</label>
            <input type="number" name="student_age" required min="2" max="99" placeholder="Age"
              style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
          </div>
          <div>
            <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Skill Level *</label>
            <select name="skill_level" required style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
              <option value="">Select...</option>
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>
        </div>
      </div>

      <div style="margin-bottom:.55rem;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">How did you hear about us? <span style="color:#9ca3af;">(optional)</span></label>
        <input type="text" name="referred_by" placeholder="e.g. Mike G., Google, Instagram"
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;">
      </div>
      <div style="margin-bottom:.55rem;">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#374151;margin-bottom:2px;">Anything else? <span style="color:#9ca3af;">(optional)</span></label>
        <textarea name="message" rows="2" placeholder="Goals, availability preferences..."
          style="width:100%;border:1.5px solid #dbe4ff;border-radius:7px;padding:7px 10px;font-size:.85rem;font-family:inherit;resize:vertical;"></textarea>
      </div>

      <div style="border-top:1px solid #f3f4f6;padding-top:.65rem;margin-bottom:.65rem;">
        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;">
          <input type="checkbox" name="email_consent" id="wl-email" required style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="wl-email" style="font-size:.78rem;color:#374151;line-height:1.4;">I agree to receive email notifications from Kristine Skates. *</label>
        </div>
        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;background:#f0f4ff;border:1.5px solid #dbe4ff;border-radius:7px;padding:.5rem .65rem;">
          <input type="checkbox" name="sms_consent" id="wl-sms" value="1" style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="wl-sms" style="font-size:.78rem;color:#374151;line-height:1.4;">
            <strong>Optional:</strong> I agree to receive SMS text message lesson reminders from Kristine Skates.
            You will receive a confirmation text upon opting in.
            Message frequency varies. Message and data rates may apply.
            Reply <strong>STOP</strong> to opt out or <strong>HELP</strong> for help.
            View our <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>.
          </label>
        </div>
        <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:.65rem;background:#fffbeb;border:1.5px solid #fde68a;border-radius:7px;padding:.5rem .65rem;">
          <input type="checkbox" name="waiver_accepted" id="wl-waiver" required style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="wl-waiver" style="font-size:.78rem;color:#374151;line-height:1.4;">
            I agree to the <a href="{{ route('waiver.show') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Liability Waiver</a>. *
          </label>
        </div>
        <div style="display:flex;align-items:flex-start;gap:.5rem;">
          <input type="checkbox" name="terms_accepted" id="wl-terms" required style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#001F5B;">
          <label for="wl-terms" style="font-size:.78rem;color:#374151;line-height:1.4;">
            I agree to the <a href="{{ route('terms') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Terms</a> &
            <a href="{{ route('privacy') }}" target="_blank" style="color:#001F5B;text-decoration:underline;">Privacy Policy</a>. *
          </label>
        </div>
      </div>

      <div id="wl-turnstile" style="display:flex;justify-content:center;margin-bottom:.65rem;"></div>

      <button type="submit" id="wl-modal-submit" disabled
        style="width:100%;background:#001F5B;color:#fff;border:none;border-radius:7px;padding:.65rem;font-size:.88rem;font-weight:700;cursor:pointer;opacity:.4;">
        Join Waitlist
      </button>
    </form>
  </div>
</div>

<script>
var wlTurnstileId = null;
function openWaitlistModal(serviceId, serviceName) {
  document.getElementById('wl-service-name').textContent = serviceName;
  document.getElementById('wl-service-id').value = serviceId;
  document.getElementById('waitlist-form').action = '/book/interest';
  document.getElementById('wl-errors').style.display = 'none';
  document.getElementById('waitlist-modal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  // Render turnstile if not already
  if (wlTurnstileId === null && window.turnstile) {
    wlTurnstileId = turnstile.render('#wl-turnstile', {
      sitekey: '{{ config("services.turnstile.key") }}',
      callback: function() {
        document.getElementById('wl-modal-submit').disabled = false;
        document.getElementById('wl-modal-submit').style.opacity = '1';
      }
    });
  }
}
function closeWaitlistModal() {
  document.getElementById('waitlist-modal').style.display = 'none';
  document.body.style.overflow = '';
}
function onTurnstileLoad() { /* loaded, render happens in openWaitlistModal */ }
function wlFormatPhone(input) {
  var v = input.value.replace(/\D/g, '').substring(0, 10);
  if (v.length >= 6) v = '(' + v.substring(0,3) + ') ' + v.substring(3,6) + '-' + v.substring(6);
  else if (v.length >= 3) v = '(' + v.substring(0,3) + ') ' + v.substring(3);
  else if (v.length > 0) v = '(' + v;
  input.value = v;
}
</script>

@endsection
