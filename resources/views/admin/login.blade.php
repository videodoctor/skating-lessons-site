<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h1 class="text-3xl font-bold text-center mb-8">Admin Login</h1>
            
            <form method="POST" action="{{ route('admin.authenticate') }}">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" name="password" required autofocus
                           class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('password')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-blue-600">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
