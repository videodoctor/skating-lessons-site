@extends('layouts.app')
@section('title', 'Player Highlight: Grant Schaible — Kristine Skates')
@section('og_image', asset('images/grant_schaible_001.jpg'))
@section('og_image', asset('images/grant_schaible_001.jpg'))
@section('og_image', asset('images/grant_schaible_001.jpg'))
@section('content')
<style>
  :root {
    --navy: #001F5B;
    --red: #C8102E;
    --gold: #C9A84C;
    --ice: #e8f4fd;
  }

  .highlight-hero {
    background: var(--navy);
    position: relative;
    overflow: hidden;
    padding: 5rem 1.5rem 4rem;
    text-align: center;
  }

  .highlight-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      repeating-linear-gradient(90deg, rgba(255,255,255,.03) 0 1px, transparent 1px 80px),
      repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 60px);
  }

  .highlight-hero::after {
    content: '⛸️';
    position: absolute;
    font-size: 18rem;
    opacity: .04;
    bottom: -3rem;
    right: -2rem;
    line-height: 1;
    pointer-events: none;
  }

  .league-badge {
    display: inline-block;
    background: var(--red);
    color: #fff;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
    padding: .35rem .85rem;
    border-radius: 2px;
    margin-bottom: 1.25rem;
  }

  .player-name {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(3rem, 10vw, 6rem);
    color: #fff;
    line-height: .95;
    margin: 0 0 .5rem;
    letter-spacing: .02em;
  }

  .player-sub {
    color: var(--gold);
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 2rem;
  }

  .stat-bar {
    display: flex;
    justify-content: center;
    gap: 0;
    flex-wrap: wrap;
    max-width: 600px;
    margin: 0 auto;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 8px;
    overflow: hidden;
  }

  .stat-item {
    flex: 1;
    min-width: 120px;
    padding: 1.1rem .75rem;
    border-right: 1px solid rgba(255,255,255,.12);
    text-align: center;
  }

  .stat-item:last-child { border-right: none; }

  .stat-val {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.8rem;
    color: var(--gold);
    line-height: 1;
    display: block;
  }

  .stat-lbl {
    font-size: .65rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: rgba(255,255,255,.5);
    margin-top: .2rem;
    display: block;
  }

  .content-section {
    max-width: 760px;
    margin: 0 auto;
    padding: 4rem 1.5rem;
  }

  .section-eyebrow {
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--red);
    margin-bottom: .75rem;
  }

  .story-heading {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.4rem;
    color: var(--navy);
    line-height: 1.05;
    margin: 0 0 1.5rem;
  }

  .story-body {
    font-size: 1.05rem;
    color: #374151;
    line-height: 1.85;
    margin-bottom: 1.25rem;
  }

  .pullquote {
    border-left: 4px solid var(--gold);
    padding: 1.25rem 1.5rem;
    margin: 2.5rem 0;
    background: #fffbf0;
    border-radius: 0 8px 8px 0;
  }

  .pullquote p {
    font-family: 'DM Serif Display', Georgia, serif;
    font-style: italic;
    font-size: 1.25rem;
    color: var(--navy);
    line-height: 1.6;
    margin: 0 0 .5rem;
  }

  .pullquote cite {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--red);
    font-style: normal;
  }

  .full-quote {
    background: var(--navy);
    border-radius: 12px;
    padding: 2.5rem;
    margin: 3rem 0;
    position: relative;
    overflow: hidden;
  }

  .full-quote::before {
    content: '"';
    font-family: Georgia, serif;
    font-size: 12rem;
    color: rgba(255,255,255,.04);
    position: absolute;
    top: -2rem;
    left: 1rem;
    line-height: 1;
    pointer-events: none;
  }

  .full-quote p {
    color: rgba(255,255,255,.88);
    font-size: .97rem;
    line-height: 1.85;
    margin-bottom: 1rem;
    position: relative;
  }

  .full-quote p:last-of-type { margin-bottom: 1.5rem; }

  .full-quote cite {
    display: block;
    font-style: normal;
    font-weight: 700;
    color: var(--gold);
    font-size: .85rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    position: relative;
  }

  .timeline {
    border-left: 3px solid var(--gold);
    padding-left: 1.5rem;
    margin: 2.5rem 0;
  }

  .timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
  }

  .timeline-item:last-child { padding-bottom: 0; }

  .timeline-item::before {
    content: '';
    width: 11px;
    height: 11px;
    background: var(--gold);
    border-radius: 50%;
    position: absolute;
    left: -1.93rem;
    top: .35rem;
  }

  .timeline-year {
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
    color: var(--red);
    margin-bottom: .2rem;
  }

  .timeline-text {
    font-size: .92rem;
    color: #374151;
    line-height: 1.6;
  }

  .cta-section {
    background: var(--ice);
    border-radius: 12px;
    padding: 2.5rem;
    text-align: center;
    margin-top: 3rem;
  }

  .cta-section h3 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2rem;
    color: var(--navy);
    margin: 0 0 .75rem;
  }

  .cta-section p {
    color: #6b7280;
    font-size: .92rem;
    margin-bottom: 1.5rem;
  }

  .btn-primary {
    display: inline-block;
    background: var(--navy);
    color: #fff;
    font-weight: 700;
    padding: .85rem 2.25rem;
    border-radius: 7px;
    text-decoration: none;
    font-size: .95rem;
    transition: background .2s;
  }

  .btn-primary:hover { background: #002580; }

  @media (max-width: 640px) {
    .stat-item { min-width: 100px; padding: .85rem .5rem; }
    .stat-val { font-size: 1.4rem; }
    .full-quote { padding: 1.75rem 1.25rem; }
    .timeline-grid { grid-template-columns: 1fr !important; }
    .timeline-photo-grid { grid-template-columns: 1fr !important; }
  }
</style>

{{-- Hero --}}
<div class="highlight-hero">
  <div style="position:relative;z-index:1;">
    <span class="league-badge">⭐ Player Highlight</span>
    <h1 class="player-name">Grant<br>Schaible</h1>
    <p class="player-sub">Eastern Hockey League · Forward · St. Louis, MO</p>
    <div class="stat-bar">
      <div class="stat-item">
        <span class="stat-val">EHL</span>
        <span class="stat-lbl">Current League</span>
      </div>
      <div class="stat-item">
        <span class="stat-val">17+</span>
        <span class="stat-lbl">Years with Kristine</span>
      </div>
      <div class="stat-item">
        <span class="stat-val">Age 2</span>
        <span class="stat-lbl">First Lesson</span>
      </div>
    </div>
  </div>
</div>



{{-- Story --}}
<div class="content-section">

  <div style="float:right;margin:0 0 1.5rem 2rem;width:280px;max-width:45%;">
    <img src="{{ asset('images/grant_schaible_001.jpg') }}"
         alt="Grant Schaible — Eastern Hockey League"
         style="width:100%;border-radius:10px;box-shadow:8px 8px 0 var(--navy);display:block;">
    <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.6rem;">Grant Schaible · EHL</p>
  </div>

  <p class="section-eyebrow">The Story</p>
  <h2 class="story-heading">From First Steps on Ice<br>to Junior Hockey</h2>

  <p class="story-body">
    Some athletes find their coach. Grant Schaible got lucky enough to have his coach find him at age two — before he could even lace up his own skates. What followed was more than two decades of development, discipline, and a bond that shaped not just a hockey player, but a person.
  </p>

  <div class="pullquote">
    <p>"I've known Coach Kristine since I was two years old, and I truly wouldn't be the player I am today without her."</p>
    <cite>— Grant Schaible, EHL Player</cite>
  </div>

  <p class="story-body">
    Grant's journey from wobbly first steps on St. Louis ice to competing in the Eastern Hockey League is the kind of story Coach Kristine gets out of bed for. The EHL is one of the top junior hockey development leagues in the country — a stepping stone for players with serious aspirations. Getting there doesn't happen by accident.
  </p>

  <p class="story-body">
    What sets Coach Kristine apart, according to Grant, isn't just technical knowledge. It's her ability to connect with players at every stage — from the three-year-old just learning to balance, to the teenager chasing a junior roster spot. That range is rare, and it's exactly what Grant experienced over the years.
  </p>

  {{-- Timeline --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:center;margin:2.5rem 0;" class="timeline-photo-grid">
    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-year">Age 2 — The Beginning</div>
        <div class="timeline-text">Grant takes his first steps on the ice under Coach Kristine's guidance. The foundation is set.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-year">Youth Hockey</div>
        <div class="timeline-text">Private lessons sharpen edge work, skating mechanics, and hockey-specific skills through youth development years.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-year">Teen Years</div>
        <div class="timeline-text">Coach Kristine helps Grant push to the next level — refining the technical details that separate good skaters from great ones.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-year">Today — EHL</div>
        <div class="timeline-text">Grant competes in the Eastern Hockey League, one of the premier junior leagues in the country. The journey continues.</div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:1rem;">
      <div>
        <img src="{{ asset('images/kristine_and_grant_young_002.jpg') }}"
             alt="Young Grant with Coach Kristine"
             style="width:100%;border-radius:10px;box-shadow:6px 6px 0 var(--navy);display:block;">
        <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.5rem;">Coach Kristine &amp; Grant — early days on the ice</p>
      </div>
      {{-- <div>
         <img src="{{ asset('images/kristine_and_grant_young_003.jpg') }}"
              alt="Coach Kristine giving Grant a pep talk"
              style="width:100%;border-radius:10px;box-shadow:6px 6px 0 var(--navy);display:block;"> --}}
      {{-- <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.5rem;">Coach Kristine giving Grant a pep talk</p> --}}
      {{-- </div> --}}
    </div>
  </div>
    "She's phenomenal with young kids who are just learning to skate, and she's just as incredible with young teenagers who are trying to take their game to the next level," Grant says. That adaptability — meeting each athlete exactly where they are — is the throughline of Coach Kristine's approach.
  </p>

  {{-- Full quote --}}
  <div class="full-quote">
    <p>"I've known Coach Kristine since I was two years old, and I truly wouldn't be the player I am today without her. From my very first steps on the ice to now playing junior hockey in the Eastern Hockey League, she has been a constant source of guidance, support, and inspiration.</p>
    <p>What makes Coach Kristine so special isn't just her knowledge of skating and hockey, it's the way she connects with every player she works with. She's phenomenal with young kids who are just learning to skate, and she's just as incredible with young teenagers who are trying to take their game to the next level. She knows how to build both skill and confidence, and she pushes you to become the best version of yourself on the ice.</p>
    <p>Over the years, she's helped me develop my skating, discipline, and love for the game. Her passion for hockey and her dedication to every player she coaches truly sets her apart. She cares about each athlete's growth not only as players, but as people.</p>
    <p>I'm so grateful to have had Coach Kristine guiding me all these years, and I can't recommend her enough to anyone looking to improve their skating and grow in the sport."</p>
    <cite>— Grant Schaible · Eastern Hockey League</cite>
  </div>

  <p class="story-body">
    Grant's trajectory is proof of what's possible with the right foundation. If you're looking for lessons for a young child or a developing teen player, Grant's story is the answer to the question of whether private coaching makes a difference.
  </p>

  {{-- Group photo --}}
  <div style="margin:2rem 0;">
    <div style="border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,31,91,.15);">
      <img src="{{ asset('images/kristine_grant_mick_001.jpg') }}"
           alt="Coach Kristine, Grant, and Mick"
           style="width:100%;display:block;">
    </div>
    <p style="font-size:.78rem;color:#6b7280;text-align:center;margin-top:.75rem;font-style:italic;">From left to right: Grant, Coach Kristine, and up-and-coming star Mick Murray</p>
  </div>

  {{-- CTA --}}
  <div class="cta-section">
    <h3>Start Your Story</h3>
    <p>Every elite player started somewhere. Book a session with Coach Kristine and build the foundation that lasts.</p>
    <a href="{{ route('booking.index') }}" class="btn-primary">Book a Lesson →</a>
  </div>

</div>
@endsection
