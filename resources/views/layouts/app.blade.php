<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Kristine Skates — Hockey Skating Lessons')</title>
  <meta property="og:title" content="@yield('title', 'Kristine Skates — Private Skating Lessons in St. Louis')">
  <meta name="description" content="Kristine Humphrey — private hockey skating lessons in St. Louis, MO. 1-on-1 instruction for all ages and skill levels. Book online at kristineskates.com.">
  <meta property="og:description" content="Kristine Humphrey — private hockey skating lessons in St. Louis. 1-on-1 instruction for all ages. Book online.">
  <meta property="og:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset('images/og-preview.png') }}@endif">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="@hasSection('og_image')@yield('og_image')@else{{ asset('images/og-preview.png') }}@endif">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root { --navy:#001F5B; --red:#C8102E; }
    a { text-decoration: none; }
    .mobile-menu-item{display:inline-block;background:rgba(0,31,91,.75);color:#fff;font-size:.82rem;padding:.3rem .55rem;font-weight:600;border-radius:5px;text-decoration:none;text-align:center;transition:background .15s;}
    .mobile-menu-item:hover{background:rgba(0,31,91,1);}
    .mobile-menu-item.cta{background:rgba(200,16,46,.75);}
    .mobile-menu-item.cta:hover{background:rgba(200,16,46,1);}
  </style>
  @stack('head')
</head>
<body class="bg-gray-50">
  <!-- Navigation -->
  <nav style="background:#fff;border-bottom:1px solid #e5eaf2;position:sticky;top:0;z-index:50;box-shadow:0 2px 12px rgba(0,31,91,.06);overflow:visible;">
    {{-- Inner wrapper to sit above the menu --}}
    <div style="position:relative;z-index:2;background:#fff;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Desktop: single row -->
      <div class="hidden md:flex justify-between h-16 items-center">
        <a href="/" style="font-family:'Bebas Neue',sans-serif;font-size:1.6rem;color:var(--navy);letter-spacing:.05em;display:flex;align-items:center;gap:8px;white-space:nowrap;">
          <img src="/images/HOCKEY_SKATER.webp" style="width:30px;height:30px;display:inline-block;vertical-align:middle;margin-right:6px;object-fit:contain;">Kristine Skates
        </a>
        <div class="flex items-center gap-1">
          <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Home</a>
          <a href="/rinks" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Rink Info</a>
          <a href="/book" style="background:var(--red);color:#fff;padding:.5rem 1.25rem;border-radius:6px;font-weight:600;font-size:.9rem;transition:background .2s;"
             onmouseover="this.style.background='#a50d24'" onmouseout="this.style.background='var(--red)'">
            Book a Lesson
          </a>
          @auth('client')
            <a href="{{ route('client.dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">My Dashboard</a>
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
      <div class="md:hidden flex justify-between items-center h-14" style="position:relative;">
        <a href="/" style="font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--navy);letter-spacing:.05em;display:flex;align-items:center;gap:8px;white-space:nowrap;">
          <img src="/images/HOCKEY_SKATER.webp" style="width:26px;height:26px;object-fit:contain;">Kristine Skates
        </a>
        <button onclick="toggleMobileMenu()"
                style="padding:6px;border:none;background:none;cursor:pointer;">
          <svg style="width:24px;height:24px;color:var(--navy)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      <script>
      var menuOpen = false;
      function toggleMobileMenu() {
        menuOpen ? closeMobileMenu() : openMobileMenu();
      }
      function openMobileMenu() {
        var menu = document.getElementById('mobile-menu');
        menu.style.transform = 'translateY(0)';
        menu.style.pointerEvents = 'auto';
        menuOpen = true;
        setTimeout(function() { document.addEventListener('click', closeMobileMenuOutside); }, 0);
      }
      function closeMobileMenu() {
        var menu = document.getElementById('mobile-menu');
        menu.style.transform = 'translateY(calc(-100% - 10px))';
        menu.style.pointerEvents = 'none';
        menuOpen = false;
        document.removeEventListener('click', closeMobileMenuOutside);
      }
      function closeMobileMenuOutside(e) {
        var menu = document.getElementById('mobile-menu');
        if (!menu.contains(e.target) && !e.target.closest('button[onclick*="toggleMobileMenu"]')) {
          closeMobileMenu();
        }
      }
      </script>
      </div>{{-- /hamburger row --}}
    </div>{{-- /max-w-7xl --}}
    </div>{{-- /inner wrapper z-index:2 --}}
    {{-- Mobile menu: z-index:1, slides out from behind the header --}}
    <div id="mobile-menu" class="sm:hidden" style="position:absolute;top:100%;right:6px;z-index:1;transform:translateY(calc(-100% - 10px));transition:transform .3s ease;pointer-events:none;">
      <div style="overflow:hidden;position:relative;width:220px;filter:drop-shadow(0 10px 20px rgba(0,31,91,.18));">
        <img src="/images/ice-rink-menu-bg.webp" style="display:block;width:100%;height:auto;" alt="">
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;gap:6px;padding:8% 10% 16%;text-align:right;white-space:nowrap;">
          <a href="/" class="mobile-menu-item">Home</a>
          <a href="/book" class="mobile-menu-item cta">Book a Lesson</a>
          <a href="/rinks" class="mobile-menu-item">Rink Information</a>
          @auth('client')
            <a href="{{ route('client.dashboard') }}" class="mobile-menu-item">My Dashboard</a>
            <form method="POST" action="{{ route('client.logout') }}" style="text-align:right;">
              @csrf
              <button type="submit" class="mobile-menu-item" style="border:none;cursor:pointer;width:auto;">Sign Out</button>
            </form>
          @else
            <a href="{{ route('client.login') }}" class="mobile-menu-item">Client Login</a>
          @endauth
          @if(session()->has('admin_authenticated'))
            <a href="/admin/dashboard" class="mobile-menu-item">Admin ⚡</a>
          @endif
        </div>
      </div>
    </div>
  </nav>

  {{-- Impersonation banner --}}
  @if(session('impersonating_admin_id'))
  <div style="background:#1e40af;color:#fff;padding:.6rem 1.5rem;text-align:center;font-size:.88rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:.75rem;flex-wrap:wrap;">
    <span>👤 Viewing as <strong>{{ session('impersonating_client_name', 'Client') }}</strong></span>
    <form method="POST" action="{{ route('admin.impersonate.stop') }}" style="display:inline;">
      @csrf
      <button type="submit" style="background:#fff;color:#1e40af;border:none;border-radius:5px;padding:3px 12px;font-size:.8rem;font-weight:700;cursor:pointer;">
        Stop Impersonating
      </button>
    </form>
  </div>
  @endif

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
        <div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:#fff;"><img src="/images/HOCKEY_SKATER.webp" style="width:30px;height:30px;display:inline-block;vertical-align:middle;margin-right:6px;object-fit:contain;filter:brightness(0) invert(1);">Kristine Skates</div>
        <div class="text-sm">Elite hockey skating instruction · St. Louis, MO</div>
        <div class="text-sm">&copy; {{ date('Y') }} Kristine Skates. All rights reserved.</div>
        <div class="text-sm" style="display:flex;gap:1.25rem;">
          <a href="{{ route('privacy') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">Privacy Policy</a>
          <a href="{{ route('terms') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">Terms &amp; Conditions</a>
          <a href="{{ route('sms.optin') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">SMS Opt-In</a>
        </div>
      </div>
    </div>
  </footer>

  @include('components.vcard-modal')
</body>
</html>
