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
    .sidebar{width:220px;background:var(--navy);min-height:100vh;position:fixed;top:0;left:0;z-index:40;padding:1.5rem 0;}
    .sidebar-logo{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:#fff;padding:0 1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:1rem;}
    .sidebar-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:rgba(255,255,255,.3);padding:.5rem 1.25rem .25rem;margin-top:.5rem;}
    .nav-link{display:flex;align-items:center;gap:.6rem;padding:.6rem 1.25rem;color:rgba(255,255,255,.7);font-size:.9rem;font-weight:500;transition:all .15s;text-decoration:none;}
    .nav-link:hover,.nav-link.active{background:rgba(255,255,255,.1);color:#fff;}
    .nav-link.active{border-left:3px solid var(--red);}
    .main-area{margin-left:220px;padding:2rem;}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;}
    .topbar-title{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--navy);}
    @media(max-width:768px){.sidebar{display:none}.main-area{margin-left:0}}
  </style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-logo"><img src="/images/HOCKEY_SKATER.png" style="width:28px;height:28px;display:inline-block;vertical-align:middle;margin-right:8px;object-fit:contain;filter:brightness(0) invert(1);">KRISTINE SKATES<br><span style="font-size:.7rem;opacity:.5;font-family:sans-serif;font-weight:400;">Admin Panel</span></div>
  <div class="sidebar-label">Main</div>
  <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    📊 Dashboard
  </a>
  <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
    📋 Bookings
  </a>
  <a href="{{ route('admin.schedule') }}" class="nav-link {{ request()->routeIs('admin.schedule') ? 'active' : '' }}">
    📅 Schedule
  </a>
  <a href="{{ route('admin.schedule.verify') }}" class="nav-link {{ request()->routeIs('admin.schedule.verify') ? 'active' : '' }}">
    🔍 Verify Schedule
  </a>
  <div class="sidebar-label">People</div>
  <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
    👥 Clients
  </a>
  <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    ⛸️ Students
  </a>
  <div class="sidebar-label">Tools</div>
  <a href="{{ route('admin.planner') }}" class="nav-link {{ request()->routeIs('admin.planner*') ? 'active' : '' }}">
    ✍️ Planner OCR
  </a>
  <a href="{{ route('admin.export') }}" class="nav-link {{ request()->routeIs('admin.export') ? 'active' : '' }}">
    📤 Export
  </a>
  <div class="mt-auto pt-8">
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
</body>
</html>
