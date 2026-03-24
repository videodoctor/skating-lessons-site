<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Kristine Skates — Hockey Skating Lessons')</title>
  <meta property="og:title" content="@yield('title', 'Kristine Skates — Private Skating Lessons in St. Louis')">
  <meta name="description" content="Private 1-on-1 skating lessons with Coach Kristine Humphrey in St. Louis. All ages, all skill levels. Book online at kristineskates.com.">
  <meta property="og:description" content="Private 1-on-1 skating lessons with Coach Kristine Humphrey in St. Louis. All ages, all skill levels. Book online.">
  <meta property="og:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset('images/og-preview.png') }}@endif">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset('images/og-preview.png') }}@endif">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    :root { --navy:#001F5B; --red:#C8102E; }
    a { text-decoration: none; }
  </style>
  @stack('head')
</head>
<body class="bg-gray-50">
  <!-- Navigation -->
  <nav style="background:#fff;border-bottom:1px solid #e5eaf2;position:sticky;top:0;z-index:50;box-shadow:0 2px 12px rgba(0,31,91,.06);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Desktop: single row -->
      <div class="hidden sm:flex justify-between h-16 items-center">
        <a href="/" style="font-family:'Bebas Neue',sans-serif;font-size:1.6rem;color:var(--navy);letter-spacing:.05em;display:flex;align-items:center;gap:8px;">
          <img src="/images/HOCKEY_SKATER.png" style="width:30px;height:30px;display:inline-block;vertical-align:middle;margin-right:6px;object-fit:contain;">Kristine Skates
        </a>
        <div class="flex items-center gap-1">
          <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Home</a>
          <a href="/rinks" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Rink Info</a>
          <a href="/book" style="background:var(--red);color:#fff;padding:.5rem 1.25rem;border-radius:6px;font-weight:600;font-size:.9rem;transition:background .2s;"
             onmouseover="this.style.background='#a50d24'" onmouseout="this.style.background='var(--red)'">
            Book a Lesson
          </a>
          @auth('client')
            <a href="{{ route('client.dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">My Bookings</a>
            <form method="POST" action="{{ route('client.logout') }}" class="inline">
              @csrf
              <button type="submit" class="text-gray-400 hover:text-gray-600 px-2 py-2 text-sm">Sign Out</button>
            </form>
          @else
            <a href="{{ route('client.login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm">Client Login</a>
          @endauth
          @if(session()->has('admin_authenticated'))
            <a href="/admin/dashboard" style="background:#f0f4ff;color:var(--navy);padding:.45rem 1rem;border-radius:6px;font-weight:600;font-size:.85rem;border:1px solid #dbe4ff;">
              Admin ⚡
            </a>
          @endif
        </div>
      </div>
      <!-- Mobile: hamburger -->
      <div class="sm:hidden flex justify-between items-center h-14">
        <a href="/" style="font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);letter-spacing:.05em;display:flex;align-items:center;gap:8px;white-space:nowrap;">
          <img src="/images/HOCKEY_SKATER.png" style="width:26px;height:26px;object-fit:contain;">Kristine Skates
        </a>
        <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                style="padding:6px;border:none;background:none;cursor:pointer;">
          <svg style="width:24px;height:24px;color:var(--navy)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
      <!-- Mobile menu dropdown -->
      <div id="mobile-menu" class="hidden sm:hidden pb-3">
        <div style="display:flex;flex-direction:column;gap:2px;border-top:1px solid #e5eaf2;padding-top:10px;">
          <a href="/" style="color:#374151;font-size:.95rem;padding:8px 4px;font-weight:500;">Home</a>
          <a href="/book" style="color:#374151;font-size:.95rem;padding:8px 4px;font-weight:500;">Book a Lesson</a>
          <a href="/rinks" style="color:#374151;font-size:.95rem;padding:8px 4px;font-weight:500;">Rink Information</a>
          @auth('client')
            <a href="{{ route('client.dashboard') }}" style="color:#374151;font-size:.95rem;padding:8px 4px;font-weight:500;">My Bookings</a>
            <form method="POST" action="{{ route('client.logout') }}">
              @csrf
              <button type="submit" style="color:#6b7280;font-size:.9rem;padding:8px 4px;background:none;border:none;cursor:pointer;">Sign Out</button>
            </form>
          @else
            <a href="{{ route('client.login') }}" style="color:#374151;font-size:.95rem;padding:8px 4px;font-weight:500;">Client Login</a>
          @endauth
          @auth('web')
            @if(session()->has('admin_authenticated'))
            <a href="/admin/dashboard" style="color:var(--navy);font-size:.95rem;padding:8px 4px;font-weight:600;">Admin ⚡</a>
            @endif
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <!-- Flash messages -->
  @if(session('success'))
  <div style="background:#ecfdf5;border-bottom:1px solid #a7f3d0;color:#065f46;padding:.75rem 1.5rem;text-align:center;font-size:.9rem;font-weight:500;">
    ✓ {{ session('success') }}
  </div>
  @endif
  @if(session('error'))
  <div style="background:#fef2f2;border-bottom:1px solid #fecaca;color:#991b1b;padding:.75rem 1.5rem;text-align:center;font-size:.9rem;font-weight:500;">
    ⚠ {{ session('error') }}
  </div>
  @endif

  <main>@yield('content')</main>

  <footer id="main-footer" style="background:#0f172a;color:#94a3b8;margin-top:4rem;">
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex flex-wrap justify-between items-center gap-4">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:#fff;"><img src="/images/HOCKEY_SKATER.png" style="width:30px;height:30px;display:inline-block;vertical-align:middle;margin-right:6px;object-fit:contain;filter:brightness(0) invert(1);">Kristine Skates</div>
        <div class="text-sm">Elite hockey skating instruction · St. Louis, MO</div>
        <div class="text-sm">&copy; {{ date('Y') }} Kristine Skates. All rights reserved.</div>
        <div class="text-sm" style="display:flex;gap:1.25rem;">
          <a href="{{ route('privacy') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">Privacy Policy</a>
          <a href="{{ route('terms') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">Terms &amp; Conditions</a>
        </div>
      </div>
    </div>
  </footer>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
</body>
</html>
