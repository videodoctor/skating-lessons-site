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
