<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','LGU Employee Portal')</title>
 <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}" />

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

<style>
/* ===== BASE STYLES ===== */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}

body {
    min-height: 100vh;
    background: #f4f6fa;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
.top-header{
    position:fixed;
    top:0;
    left:260px; /* Default sidebar width */
    right:0;
    height:70px;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(18px);
    box-shadow:0 4px 25px rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 32px;
    z-index:900;
    transition: all 0.3s ease;
}

.header-left h1{
    font-size:1.4rem;
    font-weight:600;
    color:#1f2937;
}
.header-sub{
    font-size:.85rem;
    color:#6b7280;
}

.header-right{
    display:flex;
    align-items:center;
    gap:18px;
}

@media(max-width:991px){
    .header-right form { display: none !important; }
    .header-user span { display: none !important; }
}


<style>
/* ===== BASE STYLES ===== */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}

body {
    min-height: 100vh;
    background: #f4f6fa;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
}

/* ===== HEADER ===== */
.top-header{
    position:fixed;
    top:0;
    left:260px; /* Default sidebar width */
    right:0;
    height:70px;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(18px);
    box-shadow:0 4px 25px rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 32px;
    z-index:900;
    transition: all 0.3s ease;
}

.header-left h1{
    font-size:1.4rem;
    font-weight:600;
    color:#1f2937;
}
.header-sub{
    font-size:.85rem;
    color:#6b7280;
}

.header-right{
    display:flex;
    align-items:center;
    gap:18px;
}

.header-user{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:.9rem;
    color:#374151;
}

.header-user i{
    font-size:1.4rem;
    color:#3762c8;
}

/* ===== SIDEBAR ===== */
.sidebar-nav{
    position:fixed;
    top:0;left:0;
    width:260px;
    height:100vh;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(18px);
    box-shadow:0 4px 25px rgba(0,0,0,.18);
    z-index:1000;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    transition: transform 0.3s ease;
}

.sidebar-top{padding:26px 0;overflow-y:auto; flex: 1;}
.site-logo{text-align:center;margin-bottom:12px}
.site-logo img{width:110px}
.sidebar-divider{
    width:80%;
    height:2px;
    background:rgba(0,0,0,.12);
    margin:14px auto;
}

/* ===== NAV ===== */
.nav-list{list-style:none;padding:0 18px}
.nav-link{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 18px;
    border-radius:10px;
    color:#222;
    text-decoration:none;
    font-size:.95rem;
    transition:.2s;
}
.nav-link i{min-width:20px;text-align:center}
.nav-link:hover{background:#b8c6e6}
.nav-link.active{background:#3762c8;color:#fff}

/* Submenu */
.nav-item-has-submenu>.nav-link{justify-content:space-between}
.nav-submenu{
    display:none;
    list-style:none;
    padding:6px;
    margin-top:6px;
    background:rgba(0,0,0,.05);
    border-radius:10px;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 260px;
    padding-top: 70px;
    min-height: 100vh;
    background: #f5f5f5;
    transition: all 0.3s ease;
}

.main-content-inner {
    width: 100%;
    background: #e9f1f7;
    padding: 32px 28px;
    min-height: calc(100vh - 70px);
}

/* ===== MOBILE ELEMENTS ===== */
.sidebar-hamburger{
    display:none;
    position:fixed;
    top:15px; left:15px;
    width:40px; height:40px;
    background:#3762c8;
    color: white;
    border:none; border-radius:8px;
    z-index:1100;
    cursor:pointer;
}
.sidebar-hamburger span {
    display:block; width:22px; height:2px;
    background:white; margin:5px auto; transition: 0.3s;
}

.sidebar-backdrop{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    backdrop-filter: blur(4px);
    z-index:950;
}

/* ===== DARK MODE ===== */
body.dark-mode { background: #111827; }
body.dark-mode .top-header, body.dark-mode .sidebar-nav{ background:rgba(24,24,27,.96); color: white; }
body.dark-mode .header-left h1, body.dark-mode .header-user, body.dark-mode .nav-link { color: #f9fafb; }
body.dark-mode .main-content-inner { background: #1f2937; color: #e5e7eb; }
body.dark-mode .header-sub { color: #9ca3af; }
body.dark-mode .nav-link:hover { background: #374151; }

/* ===== RESPONSIVE BREAKPOINTS ===== */
@media(max-width:991px){
    .sidebar-nav { transform: translateX(-100%); }
    .sidebar-nav.open { transform: translateX(0); }
    .top-header { left: 0; padding-left: 70px; }
    .main-content { margin-left: 0; }
    .sidebar-hamburger { display: block; }
    .sidebar-backdrop.active { display: block; }
}

@media(max-width:991px){
    .header-right form { display: none !important; }
}
@media(max-width:480px){
    .header-sub { display: none; }
    .main-content-inner { padding: 20px 15px; }
    .header-left h1 { font-size: 1.1rem; }
}

/* Additional Styles from Original */
.user-info{ padding:16px; text-align:center; border-top:1px solid rgba(0,0,0,.1); }
.logout-btn{ margin-top:8px; background:#3762c8; border:none; color:#fff; padding:8px 18px; border-radius:8px; cursor:pointer; width: 100%; }
.notif-btn:hover .fa-bell { color: #ef4444; }
</style>
</head>

<body>

<div class="sidebar-backdrop" id="backdrop"></div>

<button class="sidebar-hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
</button>

<header class="top-header">
    <div class="header-left">
        <div>
            <h1>Energy System</h1>
            <div class="header-sub">LGU Employee Portal</div>
        </div>
    </div>
    <div class="header-right">
        <form action="#" method="get" style="display:flex;align-items:center;gap:8px;background:#f1f5f9;padding:6px 12px;border-radius:8px;">
            <input type="text" placeholder="Search..." style="border:none;background:transparent;outline:none;font-size:1rem; width: 100px;">
            <button type="submit" style="background:none;border:none;color:#3762c8;font-size:1.1rem;cursor:pointer;"><i class="fa fa-search"></i></button>
        </form>


        <button class="notif-btn" id="notifBtn" style="background:none;border:none;color:#3762c8;font-size:1.5rem;cursor:pointer;position:relative;">
            <i class="fa fa-bell"></i>
            <span id="notifCount" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;font-size:0.75rem;font-weight:700;padding:2px 6px;border-radius:12px;{{ ($unreadNotifCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $unreadNotifCount ?? 0 }}</span>
        </button>

        <div id="notifDropdown" style="display:none;position:absolute;right:60px;top:60px;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.12);border-radius:8px;min-width:320px;max-width:90vw;z-index:999;max-height:380px;overflow-y:auto;">
            <div style="padding:12px 16px;border-bottom:1px solid #eee;font-size:0.97rem;font-weight:600;color:#3762c8;">Notifications</div>
            <div id="notifList">
                @forelse($notifications ?? [] as $notif)
                    <a href="#" class="notif-item" data-id="{{ $notif->id }}" style="display:block;padding:12px 16px;border-bottom:1px solid #eee;font-size:0.95rem;{{ $notif->read_at ? 'background:#f3f4f6;' : '' }};color:inherit;text-decoration:none;">
                        <strong>{{ $notif->title }}</strong><br>
                        <span style="color:#ef4444;">{{ $notif->message }}</span>
                        <div style="font-size:0.85rem;color:#888;margin-top:2px;">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div id="notifEmpty" style="padding:16px;text-align:center;color:#888;font-size:0.95rem;">No notifications</div>
                @endforelse
            </div>
        </div>

        <button id="darkToggleHeader" style="background:none;border:none;color:#3762c8;font-size:1.4rem;cursor:pointer;">
            <i id="darkModeIcon" class="fa fa-moon"></i>
        </button>

        <div class="header-user" style="position:relative;">
            <button id="userMenuBtn" style="background:none;border:none;display:flex;align-items:center;gap:8px;cursor:pointer; color: inherit;">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile Photo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e8f0; background: #fff;"> 
                <span>{{ $user->full_name ?? $user->name ?? 'Admin' }}</span>
                <i class="fa fa-caret-down"></i>
            </button>
            <div id="userDropdown" style="display:none;position:absolute;right:0;top:120%;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.12);border-radius:8px;min-width:180px;z-index:999;">
                <div style="padding:12px 16px;border-bottom:1px solid #eee; color: #333;">
                    <strong>{{ $user->full_name ?? $user->name ?? 'Admin' }}</strong><br>
                    <small style="color:#666;">{{ ucfirst($role ?? 'User') }}</small>
                </div>
                <a href="{{ route('profile.show') }}" style="display:block;padding:10px 16px;color:#222;text-decoration:none;font-size:0.97rem;">
                    <i class="fa fa-user" style="margin-right:8px;color:#3762c8;"></i> My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="logout-btn" style="border-radius:0 0 8px 8px;">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

<div class="sidebar-nav" id="sidebar">
    <div class="sidebar-top">
        <div class="site-logo">
            <img src="/img/logocityhall.jpg" alt="Logo" style="border-radius:50%;object-fit:cover;width:60px;height:60px;box-shadow:0 2px 8px rgba(49,46,129,0.10);">
        </div>
        <div class="sidebar-divider"></div>

        @php
            $user = auth()->user();
            $role = strtolower($user?->role ?? '');
        @endphp

        <ul class="nav-list">
            <li><a href="/modules/dashboard/index" class="nav-link{{ request()->is('modules/dashboard/index') ? ' active' : '' }}"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>

            @if($role=='energy_officer')
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">OPERATIONS</li>
                <li><a href="/modules/facilities/index" class="nav-link{{ request()->is('modules/facilities*') ? ' active' : '' }}"><i class="fa-solid fa-building"></i> Facilities</a></li>
                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span><i class="fa-solid fa-bolt"></i> Energy Monitoring</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li><a href="{{ route('energy.dashboard') }}" class="nav-link"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                        <li><a href="{{ route('energy.trend') }}" class="nav-link"><i class="fa-solid fa-chart-line"></i> Trend</a></li>
                        <li><a href="{{ route('modules.energy.annual') }}" class="nav-link{{ request()->routeIs('modules.energy.annual') ? ' active' : '' }}"><i class="fa-solid fa-calendar-days"></i> Annual Energy Monitoring</a></li>
                        <!-- Removed Export Report submenu -->
                       
                    </ul>
                </li>
                <li><a href="/modules/maintenance/index" class="nav-link{{ request()->is('modules/maintenance*') ? ' active' : '' }}"><i class="fa-solid fa-wrench"></i> Maintenance</a></li>
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">ANALYTICS</li>
                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span><i class="fa-solid fa-chart-bar"></i> Reports</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li><a href="/modules/reports/energy" class="nav-link"><i class="fa-solid fa-bolt"></i> Energy Report</a></li>
                        <li><a href="/modules/reports/efficiency-summary" class="nav-link"><i class="fa-solid fa-chart-line"></i> Efficiency Summary</a></li>
                        <li><a href="{{ route('energy-incidents.index') }}" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Incidents</a></li>
                    </ul>
                </li>
            @else
                <!-- ...existing code for other roles... -->
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">OPERATIONS</li>
                @if($role=='super admin'||$role=='admin'||$role=='staff')
                    <li><a href="/modules/facilities/index" class="nav-link{{ request()->is('modules/facilities*') ? ' active' : '' }}"><i class="fa-solid fa-building"></i> Facilities</a></li>
                    @if($role=='super admin'||$role=='admin'||$role=='energy_officer'||$role=='staff')
                        <li class="nav-item-has-submenu">
                            <a href="#" class="nav-link submenu-toggle">
                                <span><i class="fa-solid fa-bolt"></i> Energy Monitoring</span>
                                <i class="fa fa-caret-down"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li><a href="{{ route('energy.dashboard') }}" class="nav-link"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                                <li><a href="{{ route('energy.trend') }}" class="nav-link"><i class="fa-solid fa-chart-line"></i> Trend</a></li>
                                <li><a href="{{ route('modules.energy.annual') }}" class="nav-link{{ request()->routeIs('modules.energy.annual') ? ' active' : '' }}"><i class="fa-solid fa-calendar-days"></i> Annual Energy Monitoring</a></li>
                                <!-- Removed Export Report sidebar link -->
                             
                            </ul>
                        </li>
                    @endif
                    <li><a href="/modules/maintenance/index" class="nav-link{{ request()->is('modules/maintenance*') ? ' active' : '' }}"><i class="fa-solid fa-wrench"></i> Maintenance</a></li>
                @endif

                @if($role=='super admin'||$role=='admin'||$role=='energy_officer')
                    <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">ANALYTICS</li>
                    <li class="nav-item-has-submenu">
                        <a href="#" class="nav-link submenu-toggle">
                            <span><i class="fa-solid fa-chart-bar"></i> Reports</span>
                            <i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li><a href="/modules/reports/energy" class="nav-link"><i class="fa-solid fa-bolt"></i> Energy Report</a></li>
                            <li><a href="/modules/reports/efficiency-summary" class="nav-link"><i class="fa-solid fa-chart-line"></i> Efficiency Summary</a></li>
                            <li><a href="{{ route('energy-incidents.index') }}" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Incidents</a></li>
                        </ul>
                    </li>
                @endif
            @endif

            @if($role=='super admin'||$role=='admin')
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">ADMIN</li>
                <li><a href="/modules/users/index" class="nav-link{{ request()->is('modules/users*') ? ' active' : '' }}"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="/modules/settings/index" class="nav-link{{ request()->is('modules/settings*') ? ' active' : '' }}"><i class="fa-solid fa-gear"></i> Settings</a></li>
            @endif
        </ul>
    </div>

    <div class="user-info">
        Welcome, {{ $user->name ?? 'Admin' }}<br>
        <small style="color:#666;font-size:0.8rem;">{{ ucfirst($role ?? 'User') }}</small>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="main-content-inner">
        @yield('content')
    </div>
</div>

<div id="sessionTimeoutModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:44px 36px 36px 36px;border-radius:20px;max-width:430px;text-align:center;box-shadow:0 12px 40px rgba(37,99,235,0.15);">
        <div style="font-size:3rem;line-height:1;color:#e11d48;margin-bottom:8px;"><i class="fa fa-lock"></i></div>
        <div style="font-size:2rem;font-weight:900;color:#e11d48;margin-bottom:10px;letter-spacing:-1px;">Session Ended for Security</div>
        <div style="color:#334155;font-size:1.15rem;margin-bottom:18px;font-weight:600;">Your session has timed out due to inactivity or a security update.<br>To protect your account, we've signed you out automatically.</div>
        <div style="color:#64748b;font-size:1.01rem;margin-bottom:24px;">Please log in again to continue your work. If you need help, contact your system administrator.</div>
        <a href="/login" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:13px 36px;border-radius:10px;text-decoration:none;display:inline-block;font-weight:800;font-size:1.13rem;box-shadow:0 2px 8px #2563eb22;transition:background 0.18s;">Log In</a>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="/js/echo.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar & Backdrop Logic
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');
    const backdrop = document.getElementById('backdrop');

    hamburger.onclick = () => {
        sidebar.classList.toggle('open');
        backdrop.classList.toggle('active');
    };

    backdrop.onclick = () => {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
    };

    // 2. Submenu Logic
    document.querySelectorAll('.submenu-toggle').forEach(btn => {
        btn.onclick = e => {
            e.preventDefault();
            const menu = btn.nextElementSibling;
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';
        }
    });

    // 3. User & Notif Dropdown
    const userBtn = document.getElementById('userMenuBtn');
    const userDrop = document.getElementById('userDropdown');
    const notifBtn = document.getElementById('notifBtn');
    const notifDrop = document.getElementById('notifDropdown');

    userBtn.onclick = (e) => { e.stopPropagation(); userDrop.style.display = userDrop.style.display === 'block' ? 'none' : 'block'; notifDrop.style.display = 'none'; };
    notifBtn.onclick = (e) => { e.stopPropagation(); notifDrop.style.display = notifDrop.style.display === 'block' ? 'none' : 'block'; userDrop.style.display = 'none'; };
    
    document.addEventListener('click', () => {
        userDrop.style.display = 'none';
        notifDrop.style.display = 'none';
    });

    // 4. Dark Mode Logic
    const darkBtn = document.getElementById('darkToggleHeader');
    const darkIcon = document.getElementById('darkModeIcon');
    if(localStorage.getItem('darkMode') === 'on') document.body.classList.add('dark-mode');

    darkBtn.onclick = () => {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDark ? 'on' : 'off');
        darkIcon.className = isDark ? 'fa fa-sun' : 'fa fa-moon';
    };

    // 5. Session Timeout Logic
    var timeoutMinutes = {{ env('SESSION_LIFETIME', 15) }};
    var timeoutMs = timeoutMinutes * 60 * 1000;
    var timer = setTimeout(() => { document.getElementById('sessionTimeoutModal').style.display='flex'; }, timeoutMs);

    ['click','mousemove','keydown'].forEach(evt => {
        window.addEventListener(evt, () => {
            clearTimeout(timer);
            timer = setTimeout(() => { document.getElementById('sessionTimeoutModal').style.display='flex'; }, timeoutMs);
        });
    });
});

// Pusher/Echo Notification logic (kept as per your original)
@if(auth()->check())
    if (window.Echo) {
        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .notification((notif) => {
                const notifList = document.getElementById('notifList');
                const html = `<div style="padding:12px 16px;border-bottom:1px solid #eee;">
                                <strong>${notif.data.title}</strong><br>${notif.data.message}
                              </div>`;
                notifList.insertAdjacentHTML('afterbegin', html);
                let count = parseInt(document.getElementById('notifCount').innerText) || 0;
                document.getElementById('notifCount').innerText = count + 1;
                document.getElementById('notifCount').style.display = 'inline-block';
            });
    }
@endif
</script>

</body>
</html>
