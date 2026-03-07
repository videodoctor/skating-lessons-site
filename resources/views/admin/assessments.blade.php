@extends('layouts.admin')

@section('title', 'Assessments')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Assessments</h1>
        <div class="flex space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-700">
                ← Back to Dashboard
            </a>
            <a href="{{ route('admin.assessments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + New Assessment
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-600">Assessment tool coming soon...</p>
        <p class="text-sm text-gray-500 mt-2">This will include the full 8-category skills assessment with photo/video upload and PDF generation.</p>
    </div>
</div>
@endsection
