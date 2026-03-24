@extends('layouts.app')

@section('title', 'Kristine Skates — Elite Hockey Skating Instruction, St. Louis')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap');
  :root { --navy:#001F5B; --red:#C8102E; --ice:#E8F5FB; --gold:#C9A84C; }
  body { font-family:'DM Sans',sans-serif; }

  .hero { background:var(--navy); position:relative; overflow:hidden; min-height:600px; max-height:700px; height:92vh; display:flex; align-items:stretch; }
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
  .bio-photo { width:100%;aspect-ratio:4/5;object-fit:cover;object-position:top;border-radius:8px;box-shadow:16px 16px 0 var(--navy); }
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
  @media(max-width:768px) { .hero-photo-wrap{position:absolute;inset:0;width:100%;display:block;} .hero-photo-wrap video,.hero-photo-wrap img{object-position:center top;opacity:.4;width:100%;} .hero-video-secondary{display:none!important;} .hero-accent{width:100%;clip-path:none;opacity:.6;right:0;left:0;} .hero-photo-wrap::before{display:none;} .hero-photo-wrap::after{background:linear-gradient(to bottom,transparent 40%,var(--navy) 100%);} }
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-lines"></div>
  <div class="hero-accent"></div>
  <div class="hero-photo-wrap">
    <video id="hero-video" autoplay muted playsinline preload="auto"
      poster="{{ asset('images/kristine_and_mick_001.webp') }}">
      <source id="hero-video-src" src="{{ asset('videos/mick_reel_001_web.mp4') }}" type="video/mp4">
      <img src="{{ asset('images/kristine_and_mick_001.webp') }}" alt="Coach Kristine on ice">
    </video>
    <video id="hero-video-2" class="hero-video-secondary" autoplay muted playsinline preload="none">
      <source id="hero-video-src-2" src="{{ asset('videos/mick_reel_002_web.mp4') }}" type="video/mp4">
    </video>
    <video id="hero-video-3" class="hero-video-secondary" autoplay muted playsinline preload="none">
      <source id="hero-video-src-3" src="{{ asset('videos/mick_reel_003_web.mp4') }}" type="video/mp4">
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

      {{-- Coming Soon services --}}
      @foreach($comingSoonServices ?? [] as $service)
      <div class="service-card p-8 relative" style="opacity:.75;cursor:default;background:#fafafa;">
        <div class="absolute -top-3 left-6">
          <span style="background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:4px 10px;border-radius:4px;white-space:nowrap;">🔒 Coming Soon</span>
        </div>
        <h3 class="text-xl font-bold text-gray-600 mb-1 mt-2">{{ $service->name }}</h3>
        @if($service->show_description && $service->description)
        <p class="text-gray-400 text-sm mb-4">{{ $service->description }}</p>
        @endif
        @if($service->coming_soon_teaser)
        <p class="text-sm mb-4" style="color:#92400e;font-style:italic;">{{ $service->coming_soon_teaser }}</p>
        @endif
        @if($service->show_price || $service->show_duration)
        <div class="flex items-end gap-3 mb-4">
          @if($service->show_price)
          <div class="service-price" style="color:#9ca3af;"><span>$</span>{{ number_format($service->price, 0) }}</div>
          @endif
          @if($service->show_duration)
          <div class="text-gray-300 text-sm pb-2">/ {{ $service->duration_minutes }} min</div>
          @endif
        </div>
        @endif
        @if($service->show_features && $service->features)
        <ul class="space-y-2 mb-5">
          @foreach($service->features as $feature)
          <li class="flex items-start gap-2 text-sm text-gray-400">
            <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>{{ $feature }}
          </li>
          @endforeach
        </ul>
        @endif
        {{-- Waitlist form --}}
        @if(session('waitlist_joined_' . $service->id))
          <div style="background:#d1fae5;color:#065f46;border-radius:7px;padding:.65rem 1rem;font-size:.83rem;font-weight:600;text-align:center;">✓ You're on the waitlist!</div>
        @else
          <form method="POST" action="{{ route('waitlist.join', $service) }}" style="display:flex;gap:.5rem;">
            @csrf
            <input type="email" name="email" required placeholder="your@email.com"
              style="flex:1;border:1.5px solid #e5e7eb;border-radius:6px;padding:.5rem .75rem;font-size:.83rem;">
            <button type="submit" style="background:#001F5B;color:#fff;border:none;border-radius:6px;padding:.5rem .9rem;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;">Join Waitlist</button>
          </form>
        @endif
      </div>
      @endforeach
    </div>
    <p class="text-center text-gray-400 text-sm mt-8">* Lesson price does not include rink admission fee. Payment accepted at end of lesson.</p>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-24 pb-32 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2">Simple Process</p>
      <h2 class="section-title">From Request to Ice</h2>
    </div>
    <div class="grid md:grid-cols-4 gap-8">
      @foreach([['Pick a Service','Choose the lesson package that fits your goals.'],['Select a Date & Time','Browse available slots at your preferred rink.'],['Submit Your Request','Fill in your details and agree to the booking policy.'],['Skate!','Once confirmed, show up 10 minutes early and lace up.']] as $n => $step)
      <div class="text-center">
        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-bold mx-auto mb-4">{{ $n+1 }}</div>
        <h3 class="font-bold text-lg mb-2">{{ $step[0] }}</h3>
        <p class="text-gray-600 text-sm">{{ $step[1] }}</p>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-12"><a href="/book" class="hero-cta">Request a Time Slot →</a></div>
  </div>
</section>

<!-- BIO -->
<section class="bio-section py-20 pb-32">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div style="padding-bottom:2rem;padding-right:2rem;">
        @php $bioPhotos = ['images/kristine_and_mick_004.webp','images/kristine_and_mick_005.webp']; $bioPhoto = $bioPhotos[array_rand($bioPhotos)]; @endphp
        <img src="{{ asset($bioPhoto) }}" alt="Coach Kristine" class="bio-photo" loading="lazy"
             onerror="this.style.cssText='display:flex;align-items:center;justify-content:center;background:#dbeafe;border-radius:8px;width:100%;aspect-ratio:4/5;font-size:5rem;'">
      </div>
      <div>
        <p class="section-label mb-3">Meet Your Coach</p>
        <h2 class="section-title mb-6">Coach Kristine Humphrey</h2>
        <blockquote class="bio-quote mb-6">"Every skater has untapped speed and power waiting to be unlocked. I'm here to help them find it."</blockquote>
        <p class="text-gray-600 leading-relaxed mb-4">A St. Louis native and Nerinx Hall graduate, Kristine began her skating career at Creve Coeur Ice Arena — where she spent eight years and discovered her passion for the game. That passion turned into a calling: teaching others to skate, compete, and love hockey the way she does.</p>
        <p class="text-gray-600 leading-relaxed mb-6">Kristine has taught Learn to Skate and Learn to Play at Hardee's Ice Plex, Pacific Ice Rink, Kirkwood Ice Arena, and Creve Coeur Ice Arena. She is the <strong>lead instructor for CHA Learn to Play</strong> and has coached with the Lady Cyclones and served as assistant coach for Lady Liberty's 14U and 19U programs. Whether it's a toddler's first steps on ice or a teenager chasing a roster spot, Kristine meets every skater exactly where they are.</p>
        <div class="flex flex-wrap gap-3 mb-8">
          <span class="credential-chip">🏒 CHA Learn to Play Lead Instructor</span>
          <span class="credential-chip">⛸️ Power Skating Specialist</span>
          <span class="credential-chip">👶 Ages 4+ &amp; All Levels</span>
          <span class="credential-chip">📍 St. Louis Native</span>
        </div>
        <a href="/book" class="hero-cta">Book With Kristine →</a>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
</div>
</section>
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="section-label mb-2">What Parents &amp; Players Say</p>
      <h2 class="section-title">From the Ice</h2>
    </div>
    @php $testimonials = \App\Models\Testimonial::active()->get(); $cols = min($testimonials->count(), 4); @endphp
    <div class="grid md:grid-cols-{{ $cols }} gap-8">
      @foreach($testimonials as $t)
      <div class="testi-card">
        <div class="stars mb-3">★★★★★</div>
        <p class="text-gray-700 italic mb-4 leading-relaxed">"{{ $t->quote }}"</p>
        <p class="text-sm font-semibold text-gray-500">— {{ $t->author }}{{ $t->source_type ? ', ' . App\Models\Testimonial::sourceTypes()[$t->source_type] : '' }}</p>
      </div>
      @endforeach
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
  const clips = [
    '{{ asset("videos/mick_reel_001_web.mp4") }}',
    '{{ asset("videos/mick_reel_002_web.mp4") }}',
    '{{ asset("videos/mick_reel_003_web.mp4") }}',
  ];
  const isMobile = window.innerWidth <= 768;

  const players = [
    { v: document.getElementById('hero-video'),   s: document.getElementById('hero-video-src') },
    { v: document.getElementById('hero-video-2'),  s: document.getElementById('hero-video-src-2') },
    { v: document.getElementById('hero-video-3'),  s: document.getElementById('hero-video-src-3') },
  ];

  // Each slot tracks which clip index it's currently playing
  // Start: slot 0 = clip 0, slot 1 = clip 1, slot 2 = clip 2
  const current = [0, 1, 2];

  function otherSlotClips(slotIdx) {
    return current.filter((_, i) => i !== slotIdx);
  }

  function pickNext(slotIdx) {
    const inUse = otherSlotClips(slotIdx);
    const prev = current[slotIdx];
    // Find a clip not used by other slots AND not the same as current
    for (let i = 1; i < clips.length; i++) {
      const candidate = (prev + i) % clips.length;
      if (!inUse.includes(candidate)) return candidate;
    }
    // Fallback: just advance
    return (prev + 1) % clips.length;
  }

  if (isMobile) {
    // Mobile: defer video load — show poster first, load on user interaction
    const p = players[0];
    let idx = 0;
    p.v.preload = 'none';
    function startMobileVideo() {
      p.s.src = clips[idx];
      p.v.load();
      p.v.play().catch(() => {});
      p.v.addEventListener('ended', function() {
        idx = (idx + 1) % clips.length;
        p.s.src = clips[idx];
        p.v.load();
        p.v.play().catch(() => {});
      });
    }
    // Start loading after first user interaction or after 3s idle
    document.addEventListener('touchstart', function once() {
      startMobileVideo();
      document.removeEventListener('touchstart', once);
    });
    setTimeout(startMobileVideo, 3000);
  } else {
    // Desktop: all 3 slots, each rotates independently
    players.forEach((p, i) => {
      p.s.src = clips[current[i]];
      p.v.load();
      p.v.play().catch(() => {});
      p.v.addEventListener('ended', function() {
        current[i] = pickNext(i);
        p.s.src = clips[current[i]];
        p.v.load();
        p.v.play().catch(() => {});
      });
    });
  }
})();
</script>

<style>#main-footer{margin-top:0!important;}</style>
@endsection
