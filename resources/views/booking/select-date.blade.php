@extends('layouts.app')
@section('title', 'Select a Date — ' . $service->name)
@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap');
  :root{--navy:#001F5B;--red:#C8102E;}
  body{font-family:'DM Sans',sans-serif;}
  .page-header{background:var(--navy);padding:3rem 0 2rem;}
  .page-title{font-family:'Bebas Neue',sans-serif;font-size:clamp(2rem,5vw,3rem);color:#fff;line-height:1;}
  .breadcrumb{font-family:'Bebas Neue',sans-serif;letter-spacing:.2em;font-size:.85rem;color:rgba(255,255,255,.5);}
  .breadcrumb a{color:rgba(255,255,255,.5);}
  .breadcrumb span{color:rgba(255,255,255,.25);margin:0 .5rem;}
  .progress-bar{display:flex;gap:0;margin:0 auto;max-width:480px;}
  .progress-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
  .progress-step:not(:last-child)::after{content:'';position:absolute;top:16px;left:calc(50% + 16px);right:calc(-50% + 16px);height:2px;background:rgba(255,255,255,.15);}
  .step-circle{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;border:2px solid rgba(255,255,255,.2);color:rgba(255,255,255,.4);background:transparent;position:relative;z-index:1;}
  .step-circle.active{border-color:#fff;color:#fff;background:var(--red);}
  .step-circle.done{border-color:var(--red);background:var(--red);color:#fff;}
  .step-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-top:5px;}
  .step-label.active,.step-label.done{color:rgba(255,255,255,.9);}
  /* Calendar grid */
  .cal-header{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);}
  .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
  .cal-dow{text-align:center;font-size:.75rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;padding:6px 0;}
  .cal-day{aspect-ratio:1;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.95rem;cursor:default;}
  .cal-day.empty{background:transparent;}
  .cal-day.past{color:#d1d5db;background:#f9fafb;}
  .cal-day.available{background:#eff6ff;color:var(--navy);cursor:pointer;border:2px solid transparent;transition:all .15s;}
  .cal-day.available:hover{background:var(--navy);color:#fff;border-color:var(--navy);}
  .cal-day.today{border:2px solid var(--red);}
  .cal-day.has-slots::after{content:'';display:block;width:5px;height:5px;border-radius:50%;background:var(--red);
    position:absolute;bottom:4px;left:50%;transform:translateX(-50%);}
  .cal-day.available{position:relative;}
  .month-nav{background:none;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;color:var(--navy);font-size:1.2rem;transition:background .15s;}
  .month-nav:hover{background:#f0f4ff;}
</style>

<!-- Header -->
<div class="page-header">
  <div class="max-w-4xl mx-auto px-6">
    <p class="breadcrumb mb-3">
      <a href="/">Home</a><span>›</span>
      <a href="/book">Book</a><span>›</span>
      {{ $service->name }}
    </p>
    <h1 class="page-title mb-1">Select a Date</h1>
    <p style="color:rgba(255,255,255,.6);font-size:.95rem" class="mb-6">{{ $service->name }} · ${{ number_format($service->price, 0) }} · {{ $service->duration_minutes }} min</p>
    <div class="progress-bar mb-2">
      <div class="progress-step"><div class="step-circle done">✓</div><div class="step-label done">Service</div></div>
      <div class="progress-step"><div class="step-circle active">2</div><div class="step-label active">Date</div></div>
      <div class="progress-step"><div class="step-circle">3</div><div class="step-label">Time</div></div>
      <div class="progress-step"><div class="step-circle">4</div><div class="step-label">Confirm</div></div>
    </div>
  </div>
</div>

<div class="max-w-3xl mx-auto px-6 py-10">
  @if($availableDates->isEmpty())
    <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center">
      <div class="text-5xl mb-4">📅</div>
      <h2 class="text-xl font-bold text-gray-700 mb-2">No Available Dates</h2>
      <p class="text-gray-500 mb-6">There are no available time slots in the next 60 days. Check back soon — Coach Kristine adds new sessions regularly.</p>
      <a href="/book" class="inline-block bg-navy text-white px-6 py-3 rounded-lg font-semibold" style="background:var(--navy)">← Back to Services</a>
    </div>
  @else

  {{-- Build a lookup set for available date strings --}}
  @php
    $availableDateStrings = $availableDates->map(fn($d) => $d->format('Y-m-d'))->toArray();
    $today = \Carbon\Carbon::today();
    // Figure out calendar range: first available month through last available month
    $firstDate = $availableDates->first();
    $lastDate  = $availableDates->last();
    // Months to display (group by month)
    $months = [];
    $cur = $firstDate->copy()->startOfMonth();
    while ($cur->lte($lastDate->startOfMonth())) {
      $months[] = $cur->copy();
      $cur->addMonth();
    }
  @endphp

  @foreach($months as $monthStart)
  @php
    $monthDates = $availableDates->filter(fn($d) => $d->format('Y-m') === $monthStart->format('Y-m'));
    if($monthDates->isEmpty()) continue;
    $startDow = $monthStart->dayOfWeek; // 0=Sun
    $daysInMonth = $monthStart->daysInMonth;
  @endphp

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="cal-header">{{ $monthStart->format('F Y') }}</h2>
      <span class="text-sm text-gray-400">{{ $monthDates->count() }} day{{ $monthDates->count() !== 1 ? 's' : '' }} available</span>
    </div>

    <div class="cal-grid mb-1">
      @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
      <div class="cal-dow">{{ $dow }}</div>
      @endforeach
    </div>

    <div class="cal-grid">
      {{-- Empty cells before month start --}}
      @for($e = 0; $e < $startDow; $e++)
      <div class="cal-day empty"></div>
      @endfor

      @for($day = 1; $day <= $daysInMonth; $day++)
      @php
        $date = $monthStart->copy()->setDay($day);
        $dateStr = $date->format('Y-m-d');
        $isAvail = in_array($dateStr, $availableDateStrings);
        $isPast  = $date->lt($today);
        $isToday = $date->isToday();
      @endphp
      @if($isAvail)
      <a href="/book/service/{{ $service->id }}/date/{{ $dateStr }}"
         class="cal-day available {{ $isToday ? 'today' : '' }} has-slots no-underline">
        {{ $day }}
      </a>
      @elseif($isPast)
      <div class="cal-day past">{{ $day }}</div>
      @else
      <div class="cal-day" style="color:#d1d5db">{{ $day }}</div>
      @endif
      @endfor
    </div>
  </div>
  @endforeach

  <div class="flex items-center gap-6 text-sm text-gray-500 justify-center mt-2">
    <span class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-blue-50 border-2 border-gray-200 inline-block"></span> Available</span>
    <span class="flex items-center gap-2"><span class="w-4 h-4 rounded-full bg-red-500 inline-block"></span> Has slots</span>
    <span class="flex items-center gap-2"><span class="w-5 h-5 rounded bg-gray-50 inline-block" style="color:#d1d5db"></span> Unavailable</span>
  </div>
  @endif

  <div class="mt-8 text-center">
    <a href="/book" class="text-sm text-gray-400 hover:text-gray-600">← Change service</a>
  </div>
</div>
@endsection
