@extends('layouts.app')
@section('title', 'Book a Lesson — Kristine Skates')
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;--ice:#E8F5FB;}
  body{font-family:'DM Sans',sans-serif;}
  .page-header{background:var(--navy);padding:3rem 0 2rem;}
  .breadcrumb{font-family:'Bebas Neue',sans-serif;letter-spacing:.2em;font-size:.85rem;color:rgba(255,255,255,.5);}
  .breadcrumb span{color:rgba(255,255,255,.25);margin:0 .5rem;}
  .page-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(2.2rem,5vw,3.5rem);color:#fff;line-height:1;}
  .progress-bar{display:flex;gap:0;margin:0 auto;max-width:480px;}
  .progress-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
  .progress-step:not(:last-child)::after{content:'';position:absolute;top:16px;left:calc(50% + 16px);
    right:calc(-50% + 16px);height:2px;background:rgba(255,255,255,.15);}
  .step-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:.85rem;border:2px solid rgba(255,255,255,.2);color:rgba(255,255,255,.4);
    background:transparent;position:relative;z-index:1;}
  .step-circle.active{border-color:#fff;color:#fff;background:var(--red);}
  .step-circle.done{border-color:var(--red);background:var(--red);color:#fff;}
  .step-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-top:5px;}
  .step-label.active{color:rgba(255,255,255,.9);}
  .service-card{border:2px solid #e5eaf2;border-radius:12px;background:#fff;transition:all .2s;display:block;text-decoration:none;}
  .service-card:hover{border-color:var(--navy);box-shadow:0 16px 40px rgba(0,31,91,.12);transform:translateY(-3px);}
  .service-price-big{font-family:'Bebas Neue',sans-serif;font-size:2.8rem;color:var(--navy);line-height:1;}
  .service-book-link{display:flex;align-items:center;gap:6px;color:var(--red);font-weight:600;font-size:.95rem;margin-top:auto;}
</style>

<!-- Header -->
<div class="page-header">
  <div class="max-w-4xl mx-auto px-6">
    <p class="breadcrumb mb-3">
      <a href="/" style="color:rgba(255,255,255,.5)">Home</a>
      <span>›</span> Book a Lesson
    </p>
    <h1 class="page-title mb-6">Book a Lesson</h1>
    <!-- Progress -->
    <div class="progress-bar mb-2">
      <div class="progress-step">
        <div class="step-circle active">1</div>
        <div class="step-label active">Service</div>
      </div>
      <div class="progress-step">
        <div class="step-circle">2</div>
        <div class="step-label">Date</div>
      </div>
      <div class="progress-step">
        <div class="step-circle">3</div>
        <div class="step-label">Time</div>
      </div>
      <div class="progress-step">
        <div class="step-circle">4</div>
        <div class="step-label">Confirm</div>
      </div>
    </div>
  </div>
</div>

<div class="max-w-4xl mx-auto px-6 py-12">
  <p class="text-gray-500 mb-8 text-center">Choose the lesson type that's right for you. All sessions are one-on-one with Coach Kristine.</p>

  <div class="grid md:grid-cols-2 gap-6">
    @forelse($services as $service)
    <a href="{{ route('booking.select-date', $service) }}" class="service-card p-7 flex flex-col">
      <div class="flex justify-between items-start mb-3">
        <h3 class="text-xl font-bold text-gray-900">{{ $service->name }}</h3>
        <div class="text-sm text-gray-400">{{ $service->duration_minutes }} min</div>
      </div>
      <p class="text-gray-500 text-sm mb-5 flex-grow">{{ $service->description }}</p>
      @if($service->features)
      <ul class="space-y-1.5 mb-5">
        @foreach($service->features as $feature)
        <li class="flex items-start gap-2 text-sm text-gray-600">
          <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
          </svg>{{ $feature }}
        </li>
        @endforeach
      </ul>
      @endif
      <div class="flex justify-between items-end">
        <div class="service-price-big"><span style="font-size:1.4rem;vertical-align:top;margin-top:.3rem;display:inline-block">$</span>{{ number_format($service->price, 0) }}</div>
        <div class="service-book-link">Select →</div>
      </div>
    </a>
    @empty
    <div class="col-span-2 text-center py-12 text-gray-400 bg-white rounded-xl border-2 border-dashed border-gray-200">
      <p class="text-lg">No services available at this time.</p>
      <p class="text-sm mt-2">Please check back soon.</p>
    </div>
    @endforelse
  </div>

  <p class="text-center text-gray-400 text-sm mt-8">* Rink admission fee not included. Payment at end of lesson by cash, Venmo, or check.</p>
</div>
@endsection
