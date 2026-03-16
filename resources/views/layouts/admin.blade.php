<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Admin — Kristine Skates')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{--navy:#001F5B;--red:#C8102E;}
    body{font-family:'DM Sans',sans-serif;background:#f8fafc;}

    /* ── Sidebar (desktop) ── */
    .sidebar{width:220px;background:var(--navy);min-height:100vh;position:fixed;top:0;left:0;z-index:40;padding:1.5rem 0;display:flex;flex-direction:column;}
    .sidebar-logo{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:#fff;padding:0 1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:1rem;}
    .sidebar-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.3);padding:.5rem 1.25rem .25rem;margin-top:.5rem;}
    .nav-link{display:flex;align-items:center;gap:.6rem;padding:.6rem 1.25rem;color:rgba(255,255,255,.7);font-size:.9rem;font-weight:500;transition:all .15s;text-decoration:none;}
    .nav-link:hover,.nav-link.active{background:rgba(255,255,255,.1);color:#fff;}
    .nav-link.active{border-left:3px solid var(--red);}
    .sidebar-footer{margin-top:auto;padding-top:1rem;border-top:1px solid rgba(255,255,255,.1);}
    .main-area{margin-left:220px;padding:2rem;}

    /* ── Mobile topbar ── */
    .mobile-topbar{display:none;background:var(--navy);color:#fff;padding:.75rem 1rem;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
    .mobile-logo{font-family:'Bebas Neue',sans-serif;font-size:1.3rem;display:flex;align-items:center;gap:.5rem;}
    .hamburger{background:none;border:none;cursor:pointer;padding:.25rem;display:flex;flex-direction:column;gap:5px;}
    .hamburger span{display:block;width:24px;height:2px;background:#fff;border-radius:2px;transition:all .2s;}
    .hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg);}
    .hamburger.open span:nth-child(2){opacity:0;}
    .hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg);}

    /* ── Mobile drawer ── */
    .mobile-drawer{display:none;position:fixed;inset:0;z-index:45;}
    .mobile-drawer.open{display:block;}
    .drawer-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.5);}
    .drawer-panel{position:absolute;top:0;left:0;width:260px;height:100%;background:var(--navy);overflow-y:auto;padding:1.5rem 0;transform:translateX(-100%);transition:transform .25s ease;}
    .mobile-drawer.open .drawer-panel{transform:translateX(0);}
    .drawer-close{position:absolute;top:.75rem;right:.75rem;background:none;border:none;color:rgba(255,255,255,.6);font-size:1.5rem;cursor:pointer;line-height:1;}

    @media(max-width:768px){
      .sidebar{display:none;}
      .main-area{margin-left:0;padding:1rem;}
      .mobile-topbar{display:flex;}
    }
  </style>
</head>
<body>

{{-- Mobile topbar --}}
<div class="mobile-topbar">
  <div class="mobile-logo">
    <img src="/images/HOCKEY_SKATER.png" style="width:24px;height:24px;object-fit:contain;filter:brightness(0) invert(1);">
    KRISTINE SKATES
    <span style="font-size:.65rem;opacity:.5;font-family:sans-serif;font-weight:400;margin-left:.25rem;">Admin</span>
  </div>
  <button class="hamburger" id="hamburger-btn" onclick="toggleDrawer()">
    <span></span><span></span><span></span>
  </button>
</div>

{{-- Mobile drawer --}}
<div class="mobile-drawer" id="mobile-drawer">
  <div class="drawer-backdrop" onclick="closeDrawer()"></div>
  <div class="drawer-panel">
    <button class="drawer-close" onclick="closeDrawer()">✕</button>
    <div class="sidebar-logo" style="padding-top:.5rem;">
      <img src="/images/HOCKEY_SKATER.png" style="width:24px;height:24px;display:inline-block;vertical-align:middle;margin-right:6px;object-fit:contain;filter:brightness(0) invert(1);">
      KRISTINE SKATES
    </div>
    <div class="sidebar-label">Main</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" onclick="closeDrawer()">📊 Dashboard</a>
    <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" onclick="closeDrawer()">📋 Bookings</a>
    <a href="{{ route('admin.schedule') }}" class="nav-link {{ request()->routeIs('admin.schedule') ? 'active' : '' }}" onclick="closeDrawer()">📅 Schedule</a>
    <a href="{{ route('admin.schedule.verify') }}" class="nav-link {{ request()->routeIs('admin.schedule.verify') ? 'active' : '' }}" onclick="closeDrawer()">🔍 Verify Schedule</a>
    <div class="sidebar-label">People</div>
    <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" onclick="closeDrawer()">👥 Clients</a>
    <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" onclick="closeDrawer()">⛸️ Students</a>
    <a href="{{ route('admin.waivers.index') }}" class="nav-link {{ request()->routeIs('admin.waivers.*') ? 'active' : '' }}" onclick="closeDrawer()">📋 Waivers</a>
    <div class="sidebar-label">Payments</div>
    <a href="{{ route('admin.venmo.index') }}" class="nav-link {{ request()->routeIs('admin.venmo*') ? 'active' : '' }}" onclick="closeDrawer()">💜 Venmo</a>
    <div class="sidebar-label">Content</div>
    <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}" onclick="closeDrawer()">📦 Packages</a>
    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}" onclick="closeDrawer()">⭐ Testimonials</a>
    <div class="sidebar-label">Tools</div>
    <a href="{{ route('admin.planner') }}" class="nav-link {{ request()->routeIs('admin.planner*') ? 'active' : '' }}" onclick="closeDrawer()">✍️ Planner OCR</a>
    <a href="{{ route('admin.scraper.index') }}" class="nav-link {{ request()->routeIs('admin.scraper*') ? 'active' : '' }}" onclick="closeDrawer()">🔧 Scraper</a>
    <a href="{{ route('admin.export') }}" class="nav-link {{ request()->routeIs('admin.export') ? 'active' : '' }}" onclick="closeDrawer()">📤 Export</a>
    <div style="margin-top:auto;padding:1rem 0 0;border-top:1px solid rgba(255,255,255,.1);">
      <div class="sidebar-label">Account</div>
      <a href="/" class="nav-link" onclick="closeDrawer()">🏠 View Site</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link w-full text-left" style="background:none;border:none;cursor:pointer;">🔓 Sign Out</button>
      </form>
    </div>
  </div>
</div>

{{-- Desktop sidebar --}}
<div class="sidebar">
  <div class="sidebar-logo">
    <img src="/images/HOCKEY_SKATER.png" style="width:28px;height:28px;display:inline-block;vertical-align:middle;margin-right:8px;object-fit:contain;filter:brightness(0) invert(1);">
    KRISTINE SKATES<br>
    <span style="font-size:.7rem;opacity:.5;font-family:sans-serif;font-weight:400;">Admin Panel</span>
  </div>
  <div class="sidebar-label">Main</div>
  <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
  <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">📋 Bookings</a>
  <a href="{{ route('admin.schedule') }}" class="nav-link {{ request()->routeIs('admin.schedule') ? 'active' : '' }}">📅 Schedule</a>
  <a href="{{ route('admin.schedule.verify') }}" class="nav-link {{ request()->routeIs('admin.schedule.verify') ? 'active' : '' }}">🔍 Verify Schedule</a>
  <div class="sidebar-label">People</div>
  <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">👥 Clients</a>
  <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">⛸️ Students</a>
  <a href="{{ route('admin.waivers.index') }}" class="nav-link {{ request()->routeIs('admin.waivers.*') ? 'active' : '' }}">📋 Waivers</a>
  <div class="sidebar-label">Payments</div>
  <a href="{{ route('admin.venmo.index') }}" class="nav-link {{ request()->routeIs('admin.venmo*') ? 'active' : '' }}">💜 Venmo</a>
  <div class="sidebar-label">Content</div>
  <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">📦 Packages</a>
  <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}">⭐ Testimonials</a>
  <div class="sidebar-label">Tools</div>
  <a href="{{ route('admin.planner') }}" class="nav-link {{ request()->routeIs('admin.planner*') ? 'active' : '' }}">✍️ Planner OCR</a>
  <a href="{{ route('admin.scraper.index') }}" class="nav-link {{ request()->routeIs('admin.scraper*') ? 'active' : '' }}">🔧 Scraper</a>
  <a href="{{ route('admin.export') }}" class="nav-link {{ request()->routeIs('admin.export') ? 'active' : '' }}">📤 Export</a>
  <div class="sidebar-footer">
    <div class="sidebar-label">Account</div>
    <a href="/" class="nav-link">🏠 View Site</a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="nav-link w-full text-left" style="background:none;border:none;cursor:pointer;">🔓 Sign Out</button>
    </form>
  </div>
</div>

<div class="main-area">
  @if(session('success'))
  <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:8px;padding:.75rem 1.25rem;margin-bottom:1.5rem;font-size:.9rem;">✓ {{ session('success') }}</div>
  @endif
  @if(session('error'))
  <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:.75rem 1.25rem;margin-bottom:1.5rem;font-size:.9rem;">⚠ {{ session('error') }}</div>
  @endif
  @yield('content')
</div>

@stack('scripts')

<script>
function toggleDrawer() {
  const drawer = document.getElementById('mobile-drawer');
  const btn    = document.getElementById('hamburger-btn');
  drawer.classList.toggle('open');
  btn.classList.toggle('open');
  document.body.style.overflow = drawer.classList.contains('open') ? 'hidden' : '';
}
function closeDrawer() {
  document.getElementById('mobile-drawer').classList.remove('open');
  document.getElementById('hamburger-btn').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });
</script>
</body>
</html>
