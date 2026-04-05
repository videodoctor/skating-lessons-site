<!-- TESTIMONIALS -->
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
