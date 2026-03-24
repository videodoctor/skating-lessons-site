<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">

        <title>Admin Login — Kristine Skates</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root { --navy: #001F5B; --red: #C8102E; --gold: #C9A84C; }
            body { font-family: 'DM Sans', sans-serif; }
            .login-bg {
                background: linear-gradient(135deg, var(--navy) 0%, #002b87 60%, #0a3a8f 100%);
                position: relative;
            }
            .login-bg::before {
                content: '';
                position: absolute; inset: 0;
                background-image: repeating-linear-gradient(90deg, rgba(255,255,255,.03) 0 1px, transparent 1px 80px),
                                  repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 60px);
            }
        </style>
    </head>
    <body class="text-gray-900 antialiased">
        <div class="login-bg min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-6 text-center relative z-10">
                <a href="/" style="display:flex;flex-direction:column;align-items:center;text-decoration:none;">
                    <img src="/images/HOCKEY_SKATER.png" alt="Kristine Skates" style="width:64px;height:64px;object-fit:contain;margin-bottom:.75rem;">
                    <span style="font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:#fff;letter-spacing:.08em;line-height:1;">Kristine Skates</span>
                    <span style="font-size:.8rem;color:rgba(255,255,255,.5);margin-top:.35rem;letter-spacing:.15em;text-transform:uppercase;">Admin</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-xl overflow-hidden sm:rounded-xl relative z-10" style="border-top:4px solid var(--red);">
                {{ $slot }}
            </div>

            <p style="margin-top:2rem;font-size:.75rem;color:rgba(255,255,255,.3);position:relative;z-index:10;">
                &copy; {{ date('Y') }} Kristine Skates &middot; kristineskates.com
            </p>
        </div>
    </body>
</html>
