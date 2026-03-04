@extends('layouts.app')

@section('title', "Skating Lessons with Coach Kristine - Professional Skating Instruction")

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <h1 class="text-5xl font-bold mb-6">Professional Skating Instruction</h1>
            <p class="text-xl mb-8">Personalized coaching for skaters of all ages and skill levels</p>
            <div class="flex justify-center gap-4">
                <a href="/book" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100">
                    Request a Time Slot
                </a>
                <a href="#services" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-white hover:text-blue-600">
                    View Services
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Trust Indicators -->
<div class="bg-white py-12 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-3xl font-bold text-blue-600">20+</div>
                <div class="text-gray-600">Years Experience</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-blue-600">500+</div>
                <div class="text-gray-600">Students Taught</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-blue-600">100%</div>
                <div class="text-gray-600">Satisfaction</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-blue-600">★★★★★</div>
                <div class="text-gray-600">5-Star Rated</div>
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<div id="services" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4">Services & Pricing</h2>
            <p class="text-xl text-gray-600">Choose the option that's right for you</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($services as $service)
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition @if($service->slug === 'assessment-premium') border-2 border-blue-500 @endif">
                @if($service->slug === 'assessment-premium')
                <div class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full inline-block mb-4">
                    MOST POPULAR
                </div>
                @endif
                
                <h3 class="text-xl font-bold mb-2">{{ $service->name }}</h3>
                <div class="text-3xl font-bold text-blue-600 mb-4">${{ number_format($service->price, 0) }}</div>
                <p class="text-gray-600 mb-4">{{ $service->description }}</p>
                
                <ul class="space-y-2 mb-6">
                    @foreach($service->features as $feature)
                    <li class="flex items-start text-sm">
                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                
                <a href="/book" class="block text-center bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Request a Time Slot
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- About Section -->
<div class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl font-bold mb-6">About Coach Kristine</h2>
                <p class="text-lg text-gray-700 mb-4">
                    With over 20 years of experience in skating instruction, I have helped hundreds of students develop their skills and confidence on the ice.
                </p>
                <p class="text-lg text-gray-700 mb-4">
Teaching for multiple St. Louis area programs, I bring professional expertise and a passion for helping skaters of all ages reach their potential.
                </p>
                <div class="mt-6">
                    <h3 class="font-bold mb-2">Specializations:</h3>
                    <ul class="space-y-1 text-gray-700">
                        <li>• Learn to Skate USA Certified</li>
                        <li>• Hockey Skating Specialist</li>
                        <li>• Youth Development (Ages 4-18)</li>
                        <li>• Skills Assessment & Progress Tracking</li>
                    </ul>
                </div>
            </div>
		<div class="rounded-lg overflow-hidden shadow-lg h-[600px]">
    			<img src="{{ asset('images/kristine_and_mick_001.jpg') }}" 
         			alt="Coach Kristine" 
         			class="w-full h-full object-cover"
				style="object-position: center 20%;">
		</div>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl font-bold text-center mb-12">What Parents Say</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-yellow-400 mb-4">★★★★★</div>
                <p class="text-gray-700 mb-4">"Kristine's assessment helped us understand exactly where our daughter needed to focus. Her progress has been incredible!"</p>
                <p class="font-semibold">- Sarah M.</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-yellow-400 mb-4">★★★★★</div>
                <p class="text-gray-700 mb-4">"Patient, knowledgeable, and great with kids. Our son looks forward to every lesson!"</p>
                <p class="font-semibold">- Mike D.</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-yellow-400 mb-4">★★★★★</div>
                <p class="text-gray-700 mb-4">"Best skating instructor in St. Louis! The detailed assessment report was worth every penny."</p>
                <p class="font-semibold">- Jennifer K.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-xl mb-8">Book your lesson or assessment today and take your skating to the next level!</p>
        <a href="/book" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 inline-block">
            Request a Time Slot Now
        </a>
    </div>
</div>
<!-- Calendar Subscription Section -->
<div class="bg-blue-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-blue-900 mb-4">📅 Subscribe to Public Skating Sessions</h2>
            <p class="text-xl text-gray-700">Never miss a public skate! Add the schedule directly to your iPhone or Calendar app.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">All Rinks</h3>
                <p class="text-gray-600 mb-4 flex-grow">Get all public skating sessions in one calendar</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/public-skating.ics')) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                    Subscribe to All
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Creve Coeur</h3>
                <p class="text-gray-600 mb-4 flex-grow">Creve Coeur Ice Arena only</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/creve-coeur.ics')) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                    Subscribe
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Webster Groves</h3>
                <p class="text-gray-600 mb-4 flex-grow">Webster Groves Ice Arena only</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/webster-groves.ics')) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                    Subscribe
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Brentwood</h3>
                <p class="text-gray-600 mb-4 flex-grow">Brentwood Ice Rink only</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/brentwood.ics')) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                    Subscribe
                </a>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6 text-center flex flex-col">
                <h3 class="text-xl font-bold text-blue-900 mb-2">Chesterfield</h3>
                <p class="text-gray-600 mb-4 flex-grow">Maryville University Hockey Center only</p>
                <a href="{{ str_replace('https://', 'webcal://', url('/calendar/maryville.ics')) }}" class="inline-block bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg transition">
                    Subscribe
                </a>
            </div>
        </div>
        
        <div class="text-center mt-8 text-gray-600">
            <p class="mb-2"><strong>How to subscribe on iPhone:</strong></p>
            <p>Tap the link above → Add to Calendar → Done! Updates automatically every hour.</p>
        </div>
    </div>
</div>
@endsection
