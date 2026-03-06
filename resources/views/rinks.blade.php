@extends('layouts.app')
@section('title', 'Rink Information — Kristine Skates')
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;--gold:#C9A84C;--ice:#E8F5FB;}
  body{font-family:'DM Sans',sans-serif;}

  /* ── PAGE HEADER ── */
  .rinks-header {
    background: var(--navy);
    position: relative;
    overflow: hidden;
    padding: 5rem 0 4rem;
  }
  .rinks-header::before {
    content: '';
    position: absolute; inset: 0;
    background-image: repeating-linear-gradient(90deg,rgba(255,255,255,.04) 0 1px,transparent 1px 80px),
                      repeating-linear-gradient(0deg,rgba(255,255,255,.04) 0 1px,transparent 1px 60px);
  }
  .rinks-header::after {
    content: '🏒';
    position: absolute; right: 4rem; top: 50%;
    transform: translateY(-50%) rotate(-20deg);
    font-size: 12rem; opacity: .05; pointer-events: none;
  }
  .header-eyebrow {
    font-family:'Bebas Neue',sans-serif;
    letter-spacing:.3em;font-size:.9rem;color:var(--gold);
  }
  .header-title {
    font-family:'Bebas Neue',sans-serif;
    font-size:clamp(3rem,7vw,5.5rem);
    color:#fff;line-height:.95;
  }
  .header-sub {
    font-family:'DM Serif Display',serif;
    font-style:italic;
    color:rgba(255,255,255,.65);
    font-size:1.2rem;
    margin-top:.75rem;
  }

  /* ── RINK CARDS ── */
  .rink-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 30px rgba(0,31,91,.08);
    border: 1.5px solid #e5eaf2;
    transition: transform .25s, box-shadow .25s;
    position: relative;
  }
  .rink-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 50px rgba(0,31,91,.13);
  }
  .rink-card-accent {
    height: 6px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--red) 100%);
  }
  .rink-number {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 5rem;
    color: var(--navy);
    opacity: .06;
    position: absolute;
    top: .5rem; right: 1.5rem;
    line-height: 1;
    pointer-events: none;
  }
  .rink-name {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.9rem;
    color: var(--navy);
    line-height: 1.1;
  }
  .rink-address {
    display: flex; align-items: flex-start; gap: .5rem;
    color: #6b7280; font-size: .9rem; margin-top: .5rem;
  }
  .rink-address svg { flex-shrink: 0; margin-top: 2px; }
  .info-pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--ice); color: var(--navy);
    border-radius: 20px; padding: 4px 12px;
    font-size: .8rem; font-weight: 600;
  }
  .rink-divider { border: none; border-top: 1.5px solid #f0f4ff; margin: 1.25rem 0; }

  /* Action buttons */
  .btn-directions {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--navy); color: #fff;
    padding: .65rem 1.25rem; border-radius: 7px;
    font-weight: 600; font-size: .88rem;
    transition: background .2s;
    text-decoration: none;
  }
  .btn-directions:hover { background: var(--red); color: #fff; }
  .btn-website {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f0f4ff; color: var(--navy);
    padding: .65rem 1.25rem; border-radius: 7px;
    font-weight: 600; font-size: .88rem;
    border: 1.5px solid #dbe4ff;
    transition: all .2s;
    text-decoration: none;
  }
  .btn-website:hover { background: var(--navy); color: #fff; border-color: var(--navy); }
  .btn-calendar {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff0f3; color: var(--red);
    padding: .65rem 1.25rem; border-radius: 7px;
    font-weight: 600; font-size: .88rem;
    border: 1.5px solid #fecdd3;
    transition: all .2s;
    text-decoration: none;
  }
  .btn-calendar:hover { background: var(--red); color: #fff; border-color: var(--red); }

  /* ── MAP EMBED ── */
  .map-wrap {
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid #e5eaf2;
    box-shadow: 0 4px 20px rgba(0,31,91,.06);
  }
  .map-wrap iframe { display: block; width: 100%; border: none; }

  /* ── ALL RINKS CALENDAR BANNER ── */
  .cal-banner {
    background: linear-gradient(135deg, var(--navy) 0%, #0a2a70 100%);
    border-radius: 16px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
  }
  .cal-banner::after {
    content: '📅';
    position: absolute; right: 2rem; top: 50%;
    transform: translateY(-50%);
    font-size: 6rem; opacity: .08;
  }
  .cal-banner-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem; color: #fff;
  }
  .cal-subscribe-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--red); color: #fff;
    padding: .85rem 2rem; border-radius: 8px;
    font-weight: 700; font-size: 1rem;
    transition: background .2s;
    text-decoration: none;
  }
  .cal-subscribe-btn:hover { background: #a50d24; color: #fff; }
  .cal-subscribe-small {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.12); color: #fff;
    border: 1.5px solid rgba(255,255,255,.25);
    padding: .5rem 1rem; border-radius: 6px;
    font-size: .82rem; font-weight: 600;
    transition: all .2s;
    text-decoration: none;
  }
  .cal-subscribe-small:hover { background: rgba(255,255,255,.22); }

  /* ── SECTION LABEL ── */
  .section-label {
    font-family:'Bebas Neue',sans-serif;
    letter-spacing:.25em;font-size:.85rem;color:var(--red);
  }
  .section-title {
    font-family:'Bebas Neue',sans-serif;
    font-size:clamp(2rem,4vw,3rem);color:var(--navy);line-height:1.05;
  }

  /* ── INACTIVE BADGE ── */
  .inactive-badge {
    display: inline-block;
    background: #f3f4f6; color: #9ca3af;
    font-size: .72rem; font-weight: 700;
    padding: 2px 10px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: .08em;
  }
</style>

<!-- HEADER -->
<div class="rinks-header">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <p class="header-eyebrow mb-3">Where We Skate</p>
    <h1 class="header-title">St. Louis<br>Area Rinks</h1>
    <p class="header-sub">Coach Kristine teaches at four rinks across the St. Louis metro.</p>
  </div>
</div>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-14">

  <!-- ALL RINKS CALENDAR BANNER -->
  <div class="cal-banner mb-14">
    <div class="flex flex-wrap justify-between items-center gap-6 relative z-10">
      <div>
        <div class="section-label mb-1" style="color:var(--gold)">Never Miss a Public Skate</div>
        <div class="cal-banner-title">Subscribe to All Rink Schedules</div>
        <p style="color:rgba(255,255,255,.6);font-size:.9rem;margin-top:.4rem;">One calendar feed with every public skating session from all rinks. Updates automatically.</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="{{ str_replace('https://', 'webcal://', url('/calendar/public-skating.ics')) }}" class="cal-subscribe-btn">
          + Subscribe to All Rinks
        </a>
        <a href="/book" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.25);padding:.85rem 1.5rem;border-radius:8px;font-weight:600;font-size:.95rem;text-decoration:none;">
          Book a Lesson →
        </a>
      </div>
    </div>
  </div>

  <!-- ON THE ICE TODAY -->
  @if($todaySessions->isNotEmpty())
  <div style="margin-bottom:3.5rem;">
    <p class="section-label mb-1">Live Now</p>
    <h2 class="section-title mb-6">On the Ice Today</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
      @foreach($todaySessions as $rinkId => $sessions)
        @php $rink = $sessions->first()->rink; @endphp
        <div style="background:#fff;border:1.5px solid #e5eaf2;border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,31,.05);">
          <div style="font-weight:700;color:var(--navy);font-size:.95rem;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
            🏒 {{ $rink->name }}
          </div>
          @foreach($sessions as $session)
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.4rem;">
            <span style="background:var(--ice);color:var(--navy);border-radius:6px;padding:3px 10px;font-size:.82rem;font-weight:600;">
              {{ \Carbon\Carbon::parse($session->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($session->end_time)->format('g:i A') }}
            </span>
            @php
              $now = \Carbon\Carbon::now();
              $start = \Carbon\Carbon::parse(today()->toDateString() . ' ' . $session->start_time);
              $end   = \Carbon\Carbon::parse(today()->toDateString() . ' ' . $session->end_time);
            @endphp
            @if($now->between($start, $end))
              <span style="background:#d1fae5;color:#065f46;border-radius:12px;padding:2px 8px;font-size:.72rem;font-weight:700;">● LIVE</span>
            @endif
          </div>
          @endforeach
          @if($rink->is_active)
          <a href="/book" style="display:inline-block;margin-top:.75rem;font-size:.82rem;font-weight:600;color:var(--red);text-decoration:none;">Book a lesson here →</a>
          @endif
        </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- RINK CARDS -->
  <div class="mb-6">
    <p class="section-label mb-1">The Rinks</p>
    <h2 class="section-title mb-10">Where Lessons Happen</h2>
  </div>

  <div class="space-y-10">
    @php
      $rinkData = [
        'creve-coeur'  => ['color'=>'#003087','mapq'=>'Creve+Coeur+Ice+Arena+11250+Olde+Cabin+Rd+Creve+Coeur+MO','notes'=>'Large facility with multiple sheets. Ample parking. Great for early morning sessions.'],
        'brentwood'    => ['color'=>'#C8102E','mapq'=>'Brentwood+Ice+Rink+2505+S+Brentwood+Blvd+Brentwood+MO','notes'=>'Community rink with a friendly atmosphere. Central location — easy to reach from most of St. Louis County.'],
        'webster-groves'=>['color'=>'#1a5276','mapq'=>'Webster+Groves+Ice+Arena+33+E+Glendale+Rd+Webster+Groves+MO','notes'=>'Cozy neighborhood rink with dedicated public skate sessions and a loyal local crowd.'],
        'maryville'    => ['color'=>'#1e8449','mapq'=>'Maryville+University+Hockey+Center+18383+Chesterfield+Airport+Rd+Chesterfield+MO','notes'=>'Premier hockey facility in Chesterfield. Full-size NHL ice sheet with top-notch amenities.'],
        'kirkwood'     => ['color'=>'#7f8c8d','mapq'=>'Kirkwood+Ice+Arena+111+S+Geyer+Rd+Kirkwood+MO','notes'=>'Currently not available for lessons. Check back for updates.'],
      ];
      $n = 0;
    @endphp

    @foreach($rinks as $rink)
    @php
      $extra = $rinkData[$rink->slug] ?? ['color'=>'#003087','mapq'=>urlencode($rink->name),'notes'=>''];
      $n++;
      $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $extra['mapq'];
      $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $extra['mapq'];
      $webcalUrl = str_replace('https://', 'webcal://', url('/calendar/' . $rink->slug . '.ics'));
    @endphp

    <div class="rink-card">
      <div class="rink-card-accent" style="background:linear-gradient(90deg,{{ $extra['color'] }} 0%,var(--red) 100%)"></div>
      <div class="p-8">
        <div class="rink-number">{{ $n }}</div>

        <div class="grid lg:grid-cols-5 gap-8">
          <!-- Info column -->
          <div class="lg:col-span-2">
            <div class="flex items-start gap-3 mb-1">
              <h2 class="rink-name">{{ $rink->name }}</h2>
            </div>
            @if(!$rink->is_active)
            <span class="inactive-badge mb-2 inline-block">Currently Inactive</span>
            @endif

            <div class="rink-address">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>{{ $rink->address }}</span>
            </div>

            @if($extra['notes'])
            <p style="color:#6b7280;font-size:.88rem;margin-top:1rem;line-height:1.6;font-style:italic;">
              "{{ $extra['notes'] }}"
            </p>
            @endif

            <hr class="rink-divider">

            <!-- Availability pills -->
            <div class="flex flex-wrap gap-2 mb-4">
              @if($rink->is_active)
              <span class="info-pill">✓ Lessons Available</span>
              @endif
              <span class="info-pill">⛸️ Public Skating</span>
              <span class="info-pill">🏒 Hockey Sessions</span>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap gap-2">
              <a href="{{ $directionsUrl }}" target="_blank" class="btn-directions">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Directions
              </a>
              @if($rink->website_url)
              <a href="{{ $rink->website_url }}" target="_blank" class="btn-website">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Rink Website
              </a>
              @endif
              @if($rink->is_active)
              <a href="{{ $webcalUrl }}" class="btn-calendar">
                📅 Subscribe
              </a>
              @endif
            </div>

            @if($rink->is_active)
            <div style="margin-top:1.25rem;">
              <a href="/book" style="display:inline-flex;align-items:center;gap:6px;background:var(--red);color:#fff;padding:.7rem 1.5rem;border-radius:7px;font-weight:700;font-size:.9rem;text-decoration:none;transition:background .2s;"
                 onmouseover="this.style.background='#a50d24'" onmouseout="this.style.background='var(--red)'">
                Book a Lesson Here →
              </a>
            </div>
            @endif
          </div>

          <!-- Map column -->
          <div class="lg:col-span-3">
            <div class="map-wrap">
              <iframe
                src="https://maps.google.com/maps?q={{ urlencode($rink->address) }}&output=embed&z=15"
                height="280"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
              </iframe>
            </div>
            <div style="margin-top:.75rem;display:flex;justify-content:flex-end;">
              <a href="{{ $mapsUrl }}" target="_blank" style="color:#6b7280;font-size:.8rem;text-decoration:none;hover:underline;">
                Open in Google Maps ↗
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <!-- BOTTOM CTA -->
  <div style="margin-top:4rem;background:var(--ice);border-radius:16px;padding:3rem;text-align:center;border:1.5px solid #bfdbfe;">
    <p class="section-label mb-2">Ready to Get on the Ice?</p>
    <h2 class="section-title mb-4">Book Your First Lesson</h2>
    <p style="color:#6b7280;max-width:480px;margin:0 auto 2rem;line-height:1.7;">
      Choose your preferred rink when you book — Coach Kristine will meet you there.
      All skill levels welcome, ages 6 and up.
    </p>
    <a href="/book" style="display:inline-block;background:var(--navy);color:#fff;padding:1rem 3rem;border-radius:8px;font-weight:700;font-size:1.1rem;text-decoration:none;transition:background .2s;"
       onmouseover="this.style.background='var(--red)'" onmouseout="this.style.background='var(--navy)'">
      Book a Lesson →
    </a>
  </div>

</div>
@endsection
