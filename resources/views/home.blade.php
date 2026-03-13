@extends('layouts.app')

@section('title', 'Kristine Skates — Elite Hockey Skating Instruction, St. Louis')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap');
  :root { --navy:#001F5B; --red:#C8102E; --ice:#E8F5FB; --gold:#C9A84C; }
  body { font-family:'DM Sans',sans-serif; }

  .hero { background:var(--navy); position:relative; overflow:hidden; min-height:92vh; display:flex; align-items:stretch; }
  .hero-lines { position:absolute;inset:0;pointer-events:none;opacity:.06;
    background-image:repeating-linear-gradient(90deg,#fff 0 2px,transparent 2px 120px),repeating-linear-gradient(0deg,#fff 0 1px,transparent 1px 80px); }
  .hero-accent { position:absolute;right:-60px;top:0;bottom:0;width:55%;
    background:linear-gradient(135deg,#0a3580 0%,#001240 100%);
    clip-path:polygon(12% 0,100% 0,100% 100%,0% 100%); }
  .hero-photo-wrap { position:absolute;right:0;top:0;bottom:0;width:52%;overflow:hidden; }
  .hero-photo-wrap video { width:auto;height:100%;max-height:100%;object-fit:cover;object-position:center top;opacity:.6;mix-blend-mode:luminosity; }
  .hero-photo-wrap img { width:100%;height:100%;object-fit:cover;object-position:center top;opacity:.6;mix-blend-mode:luminosity; }
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
  .bio-section { background:var(--ice); }
  .bio-photo { width:100%;aspect-ratio:4/5;object-fit:cover;object-position:top;border-radius:8px;box-shadow:24px 24px 0 var(--navy); }
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
  @media(max-width:768px) { .hero-photo-wrap{position:absolute;inset:0;width:100%;} .hero-photo-wrap video,.hero-photo-wrap img{object-position:center top;opacity:.4;} .hero-accent{width:100%;clip-path:none;opacity:.6;right:0;left:0;} .hero-photo-wrap::before{display:none;} .hero-photo-wrap::after{background:linear-gradient(to bottom,transparent 40%,var(--navy) 100%);} }
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-lines"></div>
  <div class="hero-accent"></div>
  <div class="hero-photo-wrap">
    <video id="hero-video" autoplay muted playsinline preload="auto"
      poster="{{ asset('images/kristine_and_mick_001.jpg') }}">
      <source id="hero-video-src" src="{{ asset('videos/mick_reel_001_optimized.mp4') }}" type="video/mp4">
      <img src="{{ asset('images/kristine_and_mick_001.jpg') }}" alt="Coach Kristine on ice">
    </video>
  </div>
  <div class="hero-content max-w-7xl mx-auto px-6 lg:px-8 w-full pt-10 pb-10">
    {{-- TOP: eyebrow + title --}}
    <div class="max-w-2xl">
      <p class="hero-eyebrow mb-4">St. Louis Area<span class="mobile-break"><br></span> Hockey Skating</p>
      <h1 class="hero-title mb-0">SKATE<br>LIKE A<br><span>PRO.</span></h1>
    </div>
    {{-- BOTTOM: subtitle + CTAs + stats --}}
    <div class="max-w-2xl">
      <p class="hero-subtitle mb-6">Private lessons with Coach Kristine.<br><span style="font-size:1.1rem;opacity:.85;">Power. Edges. Confidence.</span></p>
      <div class="flex flex-wrap gap-4 mb-10">
        <a href="/book" class="hero-cta">Book a Lesson</a>
        <a href="#services" class="hero-cta-ghost">See Lessons ↓</a>
      </div>
      <div class="flex gap-10">
        <div><div class="hero-stat-num">4</div><div class="hero-stat-label">Area Rinks</div></div>
        <div><div class="hero-stat-num">30<span style="font-size:1.4rem">min</span></div><div class="hero-stat-label">Sessions</div></div>
        <div><div class="hero-stat-num">4+</div>
        <div class="hero-stat-label">Ages Welcome</div></div>
      </div>
    </div>
  </div>
</section>

<!-- RIBBON -->
<div class="ribbon py-3 px-6">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-center gap-x-10 gap-y-1 text-white text-sm font-medium">
    <span>✓ Beginner to Advanced</span>
    <span>✓ Youth &amp; Adult</span>
    <span>✓ Power Skating Focus</span>
    <span>✓ Edge Control &amp; Footwork</span>
    <span>✓ Creve Coeur · Kirkwood · Brentwood · Webster Groves</span>
  </div>
</div>

<!-- SERVICES -->
<section id="services" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2">What We Offer</p>
      <h2 class="section-title">Choose Your Lesson</h2>
      <p class="text-gray-500 mt-3 max-w-xl mx-auto">Each session is focused and purposeful. Pick the format that fits your schedule and goals.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @forelse($services as $i => $service)
      @php $isFeatured = $i === 1; @endphp
      <div class="service-card p-8 {{ $isFeatured ? 'featured' : '' }} relative">
        @if($isFeatured)<div class="absolute -top-3 left-6"><span class="service-badge">Most Popular</span></div>@endif
        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $service->name }}</h3>
        <p class="text-gray-500 text-sm mb-5">{{ $service->description }}</p>
        <div class="flex items-end gap-3 mb-5">
          <div class="service-price"><span>$</span>{{ number_format($service->price, 0) }}</div>
          <div class="text-gray-400 text-sm pb-2">/ {{ $service->duration_minutes }} min</div>
        </div>
        @if($service->features)
        <ul class="space-y-2 mb-7">
          @foreach($service->features as $feature)
          <li class="flex items-start gap-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>{{ $feature }}
          </li>
          @endforeach
        </ul>
        @endif
        <a href="/book/service/{{ $service->id }}" class="service-book-btn {{ $isFeatured ? 'featured-btn' : '' }}">Book This Lesson →</a>
      </div>
      @empty
      <div class="col-span-3 text-center py-12 text-gray-400">No services currently available.</div>
      @endforelse
    </div>
    <p class="text-center text-gray-400 text-sm mt-8">* Lesson price does not include rink admission fee. Payment accepted at end of lesson.</p>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2">Simple Process</p>
      <h2 class="section-title">From Request to Ice</h2>
    </div>
    <div class="grid md:grid-cols-4 gap-8">
      @foreach([['Pick a Service','Choose the lesson package that fits your goals.'],['Select a Date & Time','Browse available slots at your preferred rink.'],['Submit Your Request','Fill in your details and agree to the booking policy.'],['Skate!','Once confirmed, show up 10 minutes early and lace up.']] as $n => $step)
      <div class="text-center">
        <div class="step-num">{{ $n + 1 }}</div>
        <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $step[0] }}</h3>
        <p class="text-gray-500 text-sm">{{ $step[1] }}</p>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-12"><a href="/book" class="hero-cta">Request a Time Slot →</a></div>
  </div>
</section>

<!-- BIO -->
<section class="bio-section py-20">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div>
        @php $bioPhotos = ['images/kristine_and_mick_004.png','images/kristine_and_mick_005.png']; $bioPhoto = $bioPhotos[array_rand($bioPhotos)]; @endphp
        <img src="{{ asset($bioPhoto) }}" alt="Coach Kristine" class="bio-photo"
             onerror="this.style.cssText='display:flex;align-items:center;justify-content:center;background:#dbeafe;border-radius:8px;width:100%;aspect-ratio:4/5;font-size:5rem;'">
      </div>
      <div>
        <p class="section-label mb-3">Meet Your Coach</p>
        <h2 class="section-title mb-6">Coach Kristine Humphrey</h2>
        <blockquote class="bio-quote mb-8">"Every skater has untapped speed and power waiting to be unlocked. I'm here to help them find it."</blockquote>
        <p class="text-gray-600 leading-relaxed mb-6">Kristine Humphrey brings years of competitive hockey experience and a genuine passion for teaching to every session. Whether you're stepping on the ice for the first time or refining your edge work for competitive play, her structured, one-on-one approach cuts through the guesswork and gets you results fast.</p>
        <div class="flex flex-wrap gap-3 mb-8">
          <span class="credential-chip">🏒 Hockey Skating Specialist</span>
          <span class="credential-chip">⛸️ Power Skating Focus</span>
          <span class="credential-chip">👶 Youth &amp; Adult</span>
          <span class="credential-chip">📍 St. Louis Area</span>
        </div>
        <a href="/book" class="hero-cta">Book With Kristine →</a>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2">What Parents & Players Say</p>
      <h2 class="section-title">From the Ice</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      @foreach([["Coach Kristine is a great instructor who has helped my child increase his skating ability by leaps and bounds. She helps her students to grow by pushing them to their limits, being both firm and very encouraging. My child loves the sessions.","Nikki C."],["If you can't skate, you can't play. If you can't skate after lessons with Kristine, you don't want to play.","Kyle A."],["Years of working with Kristine and I can confidently say she is the real deal. No-nonsense, elite edge work, and unmatched results. I hunted her down in the parking lot several years ago and I'd do it again! She turns skaters into artists on edges. ⛸️🔥","Chad C."]] as $t)
      <div class="testi-card">
        <div class="stars mb-3">★★★★★</div>
        <p class="text-gray-700 italic mb-4 leading-relaxed">"{{ $t[0] }}"</p>
        <p class="text-sm font-semibold text-gray-500">— {{ $t[1] }}</p>
      </div>
      @endforeach
    </div>
  </div>

    {{-- Player Highlight teaser --}}
    <div class="max-w-7xl mx-auto px-6 lg:px-8" style="margin-top:3rem;">
    <div style="background:#001F5B;border-radius:14px;padding:2rem 2.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
      <div>
        <div style="font-size:.7rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#C9A84C;margin-bottom:.5rem;">&#11088; Player Highlight</div>
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.75rem;color:#fff;margin:0 0 .4rem;line-height:1.1;">Grant Schaible &middot; Eastern Hockey League</h3>
        <p style="color:rgba(255,255,255,.65);font-size:.88rem;margin:0;max-width:520px;">"I've known Coach Kristine since I was two years old, and I truly wouldn't be the player I am today without her."</p>
      </div>
      <a href="/player/grant-schaible" style="background:#C9A84C;color:#001F5B;font-weight:800;padding:.75rem 1.75rem;border-radius:7px;text-decoration:none;font-size:.88rem;white-space:nowrap;flex-shrink:0;">Read His Story &rarr;</a>
    </div>
    </div>

  </div>
</section>
<!-- RINK CALENDARS -->
<section class="py-20 bg-gray-900">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2" style="color:var(--gold)">Public Skating Schedules</p>
      <h2 class="section-title" style="color:#fff">Subscribe to Rink Calendars</h2>
      <p class="text-gray-400 mt-3 max-w-xl mx-auto">Stay on top of public skate times. Add any rink feed directly to your iPhone or Google Calendar — updates automatically.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-4 mb-4">
      <div class="rink-card" style="background:var(--red);grid-column:1/-1;">
        <div class="flex flex-wrap justify-between items-center gap-4">
          <div>
            <div class="text-xs font-bold uppercase tracking-widest text-red-200 mb-1">All Rinks Combined</div>
            <div class="text-2xl font-bold">Every Public Skate Session</div>
            <div class="text-red-200 text-sm mt-1">One feed for all four rinks</div>
          </div>
          <a href="{{ str_replace('https://', 'webcal://', url('/calendar/public-skating.ics')) }}" class="rink-subscribe-btn text-base px-6 py-3" style="border-color:rgba(255,255,255,.6)">+ Subscribe to All</a>
        </div>
      </div>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      @foreach($rinks as $rink)
      <div class="rink-card">
        <div class="text-xs font-bold uppercase tracking-widest text-blue-300 mb-1">{{ $rink->name }}</div>
        <div class="text-lg font-bold mb-3">{{ $rink->name }}</div>
        <a href="{{ str_replace('https://', 'webcal://', url('/calendar/' . $rink->slug . '.ics')) }}" class="rink-subscribe-btn">+ Subscribe</a>
      </div>
      @endforeach
    </div>
    <p class="text-center text-gray-500 text-sm mt-8"><strong class="text-gray-400">How to add on iPhone:</strong> Tap the link → "Add to Calendar" → Done. Calendar syncs hourly.</p>
  </div>
</section>

<!-- CTA BANNER -->
<section class="cta-banner py-20 text-center">
  <div class="max-w-2xl mx-auto px-6 relative z-10">
    <p class="section-label mb-3" style="color:var(--gold)">Ready to Start?</p>
    <h2 class="section-title mb-5" style="color:#fff">Your Next Lesson Is Waiting</h2>
    <p class="text-blue-200 mb-10 text-lg">Browse available time slots at area rinks and submit your booking request in under two minutes.</p>
    <a href="/book" class="hero-cta text-lg px-12 py-4">Book a Lesson Now</a>
  </div>
</section>


<script>
(function() {
  const videos = [
    '{{ asset("videos/mick_reel_001_optimized.mp4") }}',
    '{{ asset("videos/mick_reel_002_optimized.mp4") }}',
    '{{ asset("videos/mick_reel_003_optimized.mp4") }}',
  ];

  function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
  }

  const queue = shuffle([...videos]);
  let current = 0;

  const video = document.getElementById('hero-video');
  const src   = document.getElementById('hero-video-src');

  src.src = queue[0];
  video.load();
  video.play().catch(() => {});

  video.addEventListener('ended', function() {
    current = (current + 1) % queue.length;
    if (current === 0) shuffle(queue);
    src.src = queue[current];
    video.load();
    video.play().catch(() => {});
  });
})();
</script>

@endsection
