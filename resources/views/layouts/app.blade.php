<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kristine Skates - Hockey Skating Lessons')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Hockey theme colors */
        :root {
            --hockey-blue: #003087;
            --hockey-red: #C8102E;
            --ice-blue: #E8F4F8;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-900">
                        ⛸️ Kristine Skates
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-700 hover:text-blue-900 px-3 py-2 rounded-md">Home</a>
                    <a href="/book" class="bg-blue-900 text-white px-4 py-2 rounded-md hover:bg-blue-800">Request Time Slot</a>
                    
                    @auth('client')
                        <a href="{{ route('client.dashboard') }}" class="text-gray-700 hover:text-blue-900 px-3 py-2 rounded-md">My Dashboard</a>
                    @else
                        <a href="{{ route('client.login') }}" class="text-gray-700 hover:text-blue-900 px-3 py-2 rounded-md">Client Login</a>
                    @endauth
                    
                    @auth('web')
                        <a href="/admin/dashboard" class="text-gray-700 hover:text-blue-900 px-3 py-2 rounded-md">Admin</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-6 text-center">
            <p>&copy; {{ date('Y') }} Kristine Skates. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
