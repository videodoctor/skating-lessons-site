<!-- BIO -->
<section class="bio-section py-20 pb-32">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid md:grid-cols-2 gap-16 items-center">
      <div style="padding-bottom:2rem;padding-right:2rem;">
        <div class="bio-photo-wrap" id="bioPhotoWrap">
          @if($bioMedia->isNotEmpty())
            @foreach($bioMedia as $i => $bm)
              <img src="{{ $bm->url }}" alt="Coach Kristine" class="bio-photo {{ $i === 0 ? 'active' : '' }}" loading="lazy">
            @endforeach
          @else
            <img src="{{ asset('images/kristine_and_mick_004.webp') }}" alt="Coach Kristine" class="bio-photo active" loading="lazy">
            <img src="{{ asset('images/kristine_and_mick_005.webp') }}" alt="Coach Kristine" class="bio-photo" loading="lazy">
          @endif
        </div>
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
