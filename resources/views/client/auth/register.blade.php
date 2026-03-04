@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="max-w-md mx-auto py-12">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-blue-900 mb-6 text-center">Create Your Account</h2>
        
        <form method="POST" action="{{ route('client.register') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-900">
            </div>

            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" name="email_consent" required class="mt-1 mr-3">
                    <span class="text-gray-700 text-sm">
                        I agree to receive emails from Kristine Skates regarding my skating lessons, bookings, and account updates. *
                    </span>
                </label>
                @error('email_consent')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <button type="submit" class="w-full bg-blue-900 hover:bg-blue-800 text-white font-bold py-3 rounded-lg transition">
                Create Account
            </button>
        </form>
        
        <p class="text-center text-gray-600 mt-4">
            Already have an account? 
            <a href="{{ route('client.login') }}" class="text-blue-900 hover:underline font-bold">Login</a>
        </p>
    </div>
</div>
@endsection
