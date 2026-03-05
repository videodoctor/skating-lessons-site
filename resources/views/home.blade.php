@extends('layouts.app')

@section('title', 'Skating Lessons with Coach Kristine - Professional Hockey Skating Instruction')

@section('content')
<style>
/* Blue streak background - FORCED stretch with ::after pseudo-element */
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    min-height: 100vh;
}

body {
    position: relative;
    min-height: 100vh;
}

body::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-image: url('{{ asset('images/blue-streak-bg.svg') }}');
    background-size: 100% 100%; /* Force stretch/distort to fill exactly */
    background-repeat: no-repeat;
    background-position: 0 0;
    z-index: -2;
    pointer-events: none;
}

/* Ice texture ONLY visible through knockout sections */
.ice-knockout {
    background-image: url('{{ asset('images/ice-texture.jpg') }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
}

.ice-knockout::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.85);
    z-index: 0;
}

.ice-knockout > * {
    position: relative;
    z-index: 1;
}

/* Main content */
main {
    position: relative;
    padding-top: 70px;
}

@media (max-width: 768px) {
    main {
        padding-top: 60px;
    }
}

/* Service cards - white, no ice */
.card-white {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.card-white:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

/* Rink cards - ice texture */
.card-ice {
    background-image: url('{{ asset('images/ice-texture.jpg') }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    position: relative;
}

.card-ice::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.88);
    border-radius: 0.75rem;
    z-index: 0;
}

.card-ice > * {
    position: relative;
    z-index: 1;
}

.card-ice:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

/* Hockey red buttons */
.btn-primary {
    background: linear-gradient(135deg, #C8102E 0%, #A00D25 100%);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(200, 16, 46, 0.4);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #A00D25 0%, #7A0A1C 100%);
    transform: scale(1.05);
}

/* Hockey blue buttons */
.btn-secondary {
    background: linear-gradient(135deg, #003087 0%, #002066 100%);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 48, 135, 0.4);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #002066 0%, #001447 100%);
    transform: scale(1.05);
}
</style>

<!-- Hero Section - Ice knockout, reduced bottom padding -->
<div class="ice-knockout py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center py-8">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-blue-900">
                Elite Hockey Skating Instruction
            </h1>
            <p class="text-2xl md:text-3xl mb-8 text-gray-800">
                with Coach Kristine
            </p>
            <p class="text-xl mb-10 max-w-3xl mx-auto text-gray-700">
                Professional one-on-one skating lessons focused on power, speed, and edge control at St. Louis area ice rinks
            </p>
            <a href="/book" class="btn-primary inline-block text-xl font-bold py-4 px-10 rounded-full">
                Book Your Lesson Now
            </a>
        </div>
    </div>
</div>

<!-- Blue streak shows here -->
<div style="height: 4rem;"></div>

<!-- Services Section - White cards on blue streak -->
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-center text-white drop-shadow-lg mb-12">Lesson Options</h2>
        
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($services as $service)
            <div class="card-white rounded-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $service->name }}</h3>
                <p class="text-gray-600 mb-4">{{ $service->description }}</p>
                <div class="text-4xl font-bold text-blue-900 mb-4">${{ number_format($service->price, 0) }}</div>
                <div class="text-sm text-gray-500 mb-6">{{ $service->duration_minutes }} minutes</div>
                <ul class="space-y-2 mb-6">
                    @foreach($service->features as $feature)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                <a href="/book/service/{{ $service->id }}" class="btn-primary block text-center font-bold py-3 px-6 rounded-lg">
                    Book This Lesson
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Blue streak shows here -->
<div style="height: 4rem;"></div>

<!-- Calendar Section - Ice knockout -->
<div class="ice-knockout py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4 text-gray-900">📅 Subscribe to Public Skating Sessions</h2>
            <p class="text-xl text-gray-700">Never miss a public skate! Add the schedule directly to your iPhone or Calendar app.</p>
        </div>
        
        <div class="mb-8">
            <div class="card-ice rounded-xl p-8 text-center">
                <h3 class="text-3xl font-bold text-blue-900 mb-3">🏒 All Rinks</h3>
                <p class="text-gray-600 text-lg mb-6">Get all public skating sessions from every rink in one calendar</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/public-skating.ics')) }}" class="btn-primary inline-block font-bold py-4 px-10 rounded-lg text-lg">
                    Subscribe to All Rinks
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card-ice rounded-xl p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Creve Coeur</h3>
                <p class="text-gray-600 mb-4 flex-grow text-sm">Creve Coeur Ice Arena</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/creve-coeur.ics')) }}" class="btn-secondary inline-block font-bold py-3 px-6 rounded-lg">
                    Subscribe
                </a>
            </div>
            
            <div class="card-ice rounded-xl p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Webster Groves</h3>
                <p class="text-gray-600 mb-4 flex-grow text-sm">Webster Groves Ice Arena</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/webster-groves.ics')) }}" class="btn-secondary inline-block font-bold py-3 px-6 rounded-lg">
                    Subscribe
                </a>
            </div>
            
            <div class="card-ice rounded-xl p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Brentwood</h3>
                <p class="text-gray-600 mb-4 flex-grow text-sm">Brentwood Ice Rink</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/brentwood.ics')) }}" class="btn-secondary inline-block font-bold py-3 px-6 rounded-lg">
                    Subscribe
                </a>
            </div>
            
            <div class="card-ice rounded-xl p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Chesterfield</h3>
                <p class="text-gray-600 mb-4 flex-grow text-sm">Maryville University Hockey Center</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/maryville.ics')) }}" class="btn-secondary inline-block font-bold py-3 px-6 rounded-lg">
                    Subscribe
                </a>
            </div>
        </div>
        
        <div class="text-center mt-8 text-gray-700">
            <p class="mb-2"><strong>How to subscribe on iPhone:</strong></p>
            <p>Tap the link above → Add to Calendar → Done! Updates automatically every hour.</p>
        </div>
    </div>
</div>

<div style="height: 4rem;"></div>
@endsection
