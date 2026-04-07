<!-- HOW IT WORKS -->
<section style="background:url('{{ asset('images/ice-texture-bg.webp') }}') center/cover no-repeat #f0f4f8;position:relative;" class="py-24 pb-32">
  <div style="position:absolute;inset:0;background:rgba(240,244,248,.88);"></div>
  <div class="max-w-7xl mx-auto px-6 lg:px-8" style="position:relative;z-index:1;">
    <div class="text-center mb-14">
      <p class="section-label mb-2">Simple Process</p>
      <h2 class="section-title">From Request to Ice</h2>
    </div>
    <div class="grid md:grid-cols-4 gap-8">
      @foreach([['Pick a Service','Choose the lesson package that fits your goals.'],['Select a Date & Time','Browse available slots at your preferred rink.'],['Submit Your Request','Fill in your details and agree to the booking policy.'],['Skate!','Once confirmed, show up 10 minutes early and lace up.']] as $n => $step)
      <div class="text-center">
        <div style="width:52px;height:52px;border-radius:50%;background:#001F5B;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;margin:0 auto .75rem;box-shadow:0 4px 12px rgba(0,31,91,.2);">{{ $n+1 }}</div>
        <h3 class="font-bold text-lg mb-2">{{ $step[0] }}</h3>
        <p class="text-gray-600 text-sm">{{ $step[1] }}</p>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-12"><a href="/book" class="hero-cta">Request a Time Slot →</a></div>
  </div>
</section>
