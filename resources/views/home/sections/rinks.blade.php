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
      <div class="rink-card" @unless($rink->is_active) style="opacity:.55;" @endunless>
        <div class="text-xs font-bold uppercase tracking-widest text-blue-300 mb-1">{{ $rink->name }}</div>
        <div class="text-lg font-bold mb-3">{{ $rink->name }}</div>
        @if($rink->is_active)
          <a href="{{ str_replace('https://', 'webcal://', url('/calendar/' . $rink->slug . '.ics')) }}" class="rink-subscribe-btn">+ Subscribe</a>
        @else
          <span style="font-size:.75rem;letter-spacing:.1em;color:var(--gold);font-weight:600;">{{ $rink->inactive_message ?? 'Coming Soon' }}</span>
        @endif
      </div>
      @endforeach
    </div>
    <p class="text-center text-gray-500 text-sm mt-8"><strong class="text-gray-400">How to add on iPhone:</strong> Tap the link → "Add to Calendar" → Done. Calendar syncs hourly.</p>
  </div>
</section>
