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
        {{-- Waitlist --}}
        @if(session('waitlist_joined_' . $service->id))
          <div style="background:#d1fae5;color:#065f46;border-radius:7px;padding:.65rem 1rem;font-size:.83rem;font-weight:600;text-align:center;">✓ You're on the waitlist!</div>
        @else
          <button onclick="openWaitlistModal('{{ $service->id }}', '{{ addslashes($service->name) }}')"
            style="width:100%;background:#001F5B;color:#fff;border:none;border-radius:6px;padding:.6rem .9rem;font-size:.85rem;font-weight:700;cursor:pointer;">
            Join Waitlist
          </button>
        @endif
      </div>
      @endforeach
    </div>
    <p class="text-center text-gray-400 text-sm mt-8">* Lesson price does not include rink admission fee. Payment accepted at end of lesson.</p>
  </div>
</section>
