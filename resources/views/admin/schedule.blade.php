@extends('layouts.app')

@section('title', 'Manage Schedule')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Manage Schedule</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700">
            ← Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-600">Schedule management coming soon...</p>
        <p class="text-sm text-gray-500 mt-2">Time slots are automatically generated from your availability template.</p>
    </div>
</div>
@endsection
