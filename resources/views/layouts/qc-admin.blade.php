<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','LGU Employee Portal')</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

<style>
/* ===== HEADER ===== */
.top-header{
    position:fixed;
    top:0;
    left:260px;
    right:0;
    height:70px;
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(18px);
    box-shadow:0 4px 25px rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 32px;
    z-index:90;
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

.header-right .notif-btn:hover .fa-bell {
    color: #ef4444;
    transition: color 0.18s;
}
.header-right .notif-btn:hover span {
    transform: scale(1.15);
    transition: transform 0.18s;
}

/* Dark header */
body.dark-mode .top-header{
    background:rgba(24,24,27,.96);
}
body.dark-mode .header-left h1,
body.dark-mode .header-user{
    color:#f9fafb;
}
body.dark-mode .header-sub{
    color:#9ca3af;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}

/* ===== BACKGROUND ===== */
body {
    min-height: 100vh;
    background: #f4f6fa;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
body::before {
    display: none;
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
    z-index:100;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}
.sidebar-top{padding:26px 0;overflow-y:auto}
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

/* ===== SUBMENU ===== */
.nav-item-has-submenu>.nav-link{justify-content:space-between}
.nav-submenu{
    display:none;
    list-style:none;
    padding:6px;
    margin-top:6px;
    background:rgba(0,0,0,.05);
    border-radius:10px;
}
.nav-submenu .nav-link{
    padding:8px 14px;
    font-size:.9rem;
}

/* ===== USER ===== */
.user-info{
    padding:16px;
    text-align:center;
    border-top:1px solid rgba(0,0,0,.1);
}
.logout-btn{
    margin-top:8px;
    background:#3762c8;
    border:none;
    color:#fff;
    padding:8px 18px;
    border-radius:8px;
    cursor:pointer;
}

/* ===== DARK MODE ===== */
body.dark-mode::before{background:rgba(0,0,0,.65)}
body.dark-mode .sidebar-nav{background:rgba(24,24,27,.96)}
body.dark-mode .nav-link{color:#e5e7eb}
body.dark-mode .nav-link:hover{background:#374151}
body.dark-mode .nav-link.active{background:#2563eb}
body.dark-mode .nav-submenu{background:rgba(255,255,255,.06)}
body.dark-mode .main-content-inner{background:#111827;color:#e5e7eb}
body.dark-mode h1,body.dark-mode h2{color:#f9fafb}

/* Dark toggle */
.dark-toggle{
    margin-top:10px;
    padding:6px 14px;
    border-radius:20px;
    border:none;
    cursor:pointer;
    font-size:.85rem;
    background:#111827;
    color:#fff;
}
body.dark-mode .dark-toggle{
    background:#f9fafb;
    color:#111827;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 260px;          /* space for sidebar */
    padding-top: 49px;           /* space for header */
    min-height: calc(100vh - 0px);
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    position: relative;
    min-width: 0;
    flex: 1 1 auto;
    height: calc(100vh - 70px);
    overflow: hidden;
}

.main-content-inner {
    width: 100%;
    background: #e9f1f7;
    border-radius: 0;
    box-shadow: 0 4px 24px rgba(55,98,200,0.10);
    padding: 32px 28px;
    margin: 0;
    transition: box-shadow 0.2s;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    height: 100%;
    overflow-y: auto;
    min-height: 0;
}
.main-content-inner:focus-within, .main-content-inner:hover {
    box-shadow: 0 8px 32px rgba(55,98,200,0.16);
}

/* ===== MOBILE RESPONSIVE ===== */
.sidebar-hamburger{
    display:none;
    position:fixed;
    top:18px;left:18px;
    width:40px;height:40px;
    background:#fff;
    border:none;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,.15);
    z-index:200;
}
.sidebar-hamburger span{
    display:block;
    width:24px;height:3px;
    background:#3762c8;
    margin:4px auto;
}
.sidebar-backdrop{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.25);
    z-index:99;
}

@media(max-width:900px){
    .sidebar-nav{left:-260px;transition:.3s}
    .sidebar-nav.open{left:0}
    .sidebar-hamburger{display:block}

    .main-content{
        margin-left:0;
        padding:0;
    }
    .main-content-inner{
        padding: 12px 4px;
        margin: 0;
        max-width: none;
        width: 100%;
        border-radius: 12px;
        text-align: left;
    }
}

@media(max-width:480px){
    .main-content-inner{
        padding: 0 2px;
        margin: 0;
        max-width: none;
        width: 100%;
        border-radius: 0;
        text-align: left;
    }
    h1{font-size:1.5rem}
    h2{font-size:1.3rem}
    h3{font-size:1.1rem}
}
</style>
</head>

<body>

<!-- ===== MODERN HEADER ===== -->
<header class="top-header">
    <div class="header-left" style="display:flex;align-items:center;gap:16px;">
        <div>
            <h1>Energy System</h1>
            <div class="header-sub">LGU Employee Portal</div>
        </div>
    </div>
    <div class="header-right">
        <form action="#" method="get" style="display:flex;align-items:center;gap:8px;background:#f1f5f9;padding:6px 12px;border-radius:8px;">
            <input type="text" placeholder="Search..." style="border:none;background:transparent;outline:none;font-size:1rem;">
            <button type="submit" style="background:none;border:none;color:#3762c8;font-size:1.1rem;cursor:pointer;"><i class="fa fa-search"></i></button>
        </form>

        <!-- Notification Bell -->
        <button class="notif-btn" id="notifBtn" style="background:none;border:none;color:#3762c8;font-size:1.5rem;cursor:pointer;position:relative;" title="Notifications">
            <i class="fa fa-bell"></i>
            <span id="notifCount" style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;font-size:0.75rem;font-weight:700;padding:2px 6px;border-radius:12px;{{ ($unreadNotifCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $unreadNotifCount ?? 0 }}</span>
        </button>
        <div id="notifDropdown" style="display:none;position:absolute;right:60px;top:60px;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.12);border-radius:8px;min-width:320px;max-width:90vw;z-index:999;max-height:380px;overflow-y:auto;">
            <div style="padding:12px 16px;border-bottom:1px solid #eee;font-size:0.97rem;font-weight:600;color:#3762c8;">Notifications</div>
            <div id="notifList" style="max-height:320px;overflow-y:auto;">
                @forelse($notifications ?? [] as $notif)
                    <a href="#" class="notif-item" data-id="{{ $notif->id }}" style="display:block;padding:12px 16px;border-bottom:1px solid #eee;font-size:0.95rem;{{ $notif->read_at ? 'background:#f3f4f6;' : '' }};color:inherit;text-decoration:none;cursor:pointer;">
                        <strong>{{ $notif->title }}</strong><br>
                        <span style="color:#ef4444;">{{ $notif->message }}</span>
                        <div style="font-size:0.85rem;color:#888;margin-top:2px;">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div id="notifEmpty" style="padding:16px;text-align:center;color:#888;font-size:0.95rem;">No notifications</div>
                @endforelse
            </div>
        </div>

        <button id="darkToggleHeader" style="background:none;border:none;color:#3762c8;font-size:1.4rem;cursor:pointer;" title="Toggle dark mode">
            <i id="darkModeIcon" class="fa fa-moon"></i>
        </button>
        <div class="header-user" style="position:relative;">
            <button id="userMenuBtn" style="background:none;border:none;display:flex;align-items:center;gap:8px;cursor:pointer;">
                <i class="fa fa-user-circle"></i>
                <span>{{ $user->full_name ?? $user->name ?? 'Admin' }}</span>
                <i class="fa fa-caret-down"></i>
            </button>
            <div id="userDropdown" style="display:none;position:absolute;right:0;top:120%;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.12);border-radius:8px;min-width:160px;z-index:999;">
                <div style="padding:12px 16px;border-bottom:1px solid #eee;">
                    <strong>{{ $user->full_name ?? $user->name ?? 'Admin' }}</strong><br>
                    <small style="color:#666;">{{ ucfirst($role ?? 'User') }}</small>
                </div>
                <a href="{{ route('profile.show') }}" style="display:block;padding:10px 16px;color:#222;text-decoration:none;font-size:0.97rem;">
                    <i class="fa fa-user" style="margin-right:8px;color:#3762c8;"></i> My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button class="logout-btn" style="width:100%;border-radius:0 0 8px 8px;">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- HAMBURGER BUTTON -->
<button class="sidebar-hamburger" id="hamburger">
    <span></span><span></span><span></span>
</button>
<div class="sidebar-backdrop" id="backdrop"></div>

<!-- ===== SIDEBAR ===== -->
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
            <li><a href="/modules/dashboard/index" class="nav-link"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>

            {{-- OPERATIONS GROUP --}}
            <li style="margin: 18px 0 6px 8px; font-size:0.95rem; color:#888; font-weight:600; letter-spacing:1px;">OPERATIONS</li>
            @if($role=='super admin'||$role=='admin'||$role=='staff')
                <li><a href="/modules/facilities/index" class="nav-link"><i class="fa-solid fa-building"></i> Facilities</a></li>
                @if($role=='super admin'||$role=='admin'||$role=='energy_officer'||$role=='staff')
                    <li class="nav-item-has-submenu">
                        <a href="#" class="nav-link submenu-toggle">
                            <span><i class="fa-solid fa-bolt"></i> Energy Monitoring</span>
                            <i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li><a href="{{ route('energy.dashboard') }}" class="nav-link">Dashboard</a></li>
                            <li><a href="{{ route('energy.trend') }}" class="nav-link">Trend Analysis</a></li>
                            <li><a href="{{ route('energy.exportReport') }}" class="nav-link">Export COA Report</a></li>
                            <li><a href="{{ route('energy-incidents.index') }}" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Incident Records</a></li>
                        </ul>
                    </li>
                @endif
                <li><a href="/modules/maintenance/index" class="nav-link"><i class="fa-solid fa-wrench"></i> Maintenance</a></li>
                <li><a href="/modules/billing/index" class="nav-link"><i class="fa-solid fa-money-bill-wave"></i> Billing & Cost</a></li>
            @endif

            {{-- ANALYTICS GROUP --}}
            @if($role=='super admin'||$role=='admin'||$role=='energy_officer')
                <li style="margin: 18px 0 6px 8px; font-size:0.95rem; color:#888; font-weight:600; letter-spacing:1px;">ANALYTICS</li>
                <li><a href="/modules/energy-efficiency-analysis" class="nav-link"><i class="fa-solid fa-chart-line"></i> Energy Efficiency Analysis</a></li>
                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span><i class="fa-solid fa-chart-bar"></i> Reports & Analytics</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li><a href="/modules/reports/energy" class="nav-link">Energy Report</a></li>
                        <li><a href="/modules/reports/efficiency-summary" class="nav-link">Efficiency Summary</a></li>
                    </ul>
                </li>
            @endif

            {{-- ADMINISTRATION GROUP --}}
            @if($role=='super admin'||$role=='admin')
                <li style="margin: 18px 0 6px 8px; font-size:0.95rem; color:#888; font-weight:600; letter-spacing:1px;">ADMINISTRATION</li>
                <li><a href="/modules/users/index" class="nav-link"><i class="fa-solid fa-users"></i> Users & Roles</a></li>
                <li><a href="/modules/settings/index" class="nav-link"><i class="fa-solid fa-gear"></i> System Settings</a></li>
            @endif
        </ul>
    </div>

  
    <div class="user-info">
        Welcome, {{ $user->full_name ?? $user->name ?? 'Admin' }}<br>
        <small style="color:#666;font-size:0.85rem;">{{ ucfirst($role ?? 'User') }}</small>
   
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<!-- ===== MAIN CONTENT (SIDEBAR + HEADER + CONTENT) ===== -->
<div class="main-content">
    
    <div class="main-content-inner">
        @yield('content')
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="/js/echo.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure dark mode persists across all content
    if(localStorage.getItem('darkMode')==='on'){
        document.body.classList.add('dark-mode');
    }

    const sidebar=document.getElementById('sidebar');
    const hamburger=document.getElementById('hamburger');
    const backdrop=document.getElementById('backdrop');

    hamburger.onclick=()=>{sidebar.classList.add('open');backdrop.style.display='block'}
    backdrop.onclick=()=>{sidebar.classList.remove('open');backdrop.style.display='none'}

    // Submenu toggle
    document.querySelectorAll('.submenu-toggle').forEach(btn=>{
        btn.onclick=e=>{
            e.preventDefault();
            const menu=btn.nextElementSibling;
            menu.style.display=menu.style.display==='block'?'none':'block';
        }
    });

    // User dropdown logic
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if(userMenuBtn && userDropdown){
        userMenuBtn.onclick = (e) => {
            e.stopPropagation();
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
            userMenuBtn.classList.toggle('active');
        };
        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target)) {
                userDropdown.style.display = 'none';
                userMenuBtn.classList.remove('active');
            }
        });
    }
    // Dark mode
    const toggleHeader=document.getElementById('darkToggleHeader');
    function updateDarkModeIcon() {
        const icon = document.getElementById('darkModeIcon');
        if (!icon) return;
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            icon.title = 'Light Mode';
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            icon.title = 'Dark Mode';
        }
    }
    updateDarkModeIcon();
    if(toggleHeader){
        toggleHeader.onclick=()=>{
            document.body.classList.toggle('dark-mode');
            const on=document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode',on?'on':'off');
            updateDarkModeIcon();
        };
    }
    // Notification dropdown logic
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifCount = document.getElementById('notifCount');
    const notifList = document.getElementById('notifList');
    const notifEmpty = document.getElementById('notifEmpty');


    function updateNotifBadge(count) {
        if (count > 0) {
            notifCount.style.display = 'inline-block';
            notifCount.innerText = count;
        } else {
            notifCount.style.display = 'none';
            notifCount.innerText = 0;
        }
    }
    // Set initial badge count from backend
    updateNotifBadge(parseInt(document.getElementById('notifCount').innerText) || 0);

    function addNotification(title, message, time) {
        const html = `<div style="padding:12px 16px;border-bottom:1px solid #eee;font-size:0.95rem;">
                        <strong>${title}</strong><br>
                        <span style="color:#ef4444;">${message}</span>
                        <div style="font-size:0.85rem;color:#888;margin-top:2px;">${time}</div>
                      </div>`;
        notifList.insertAdjacentHTML('afterbegin', html);
        notifEmpty.style.display = 'none';
        let count = parseInt(notifCount.innerText) || 0;
        updateNotifBadge(count + 1);
    }

    notifBtn && notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
        // Mark all as read when opening dropdown
        if (notifDropdown.style.display === 'block') {
            fetch("{{ route('notifications.markAllRead') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            }).then(r => {
                if (r.ok) updateNotifBadge(0);
            });
        }
    });
    document.addEventListener('click', (e) => {
        if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            notifDropdown.style.display = 'none';
        }
    });

    // Optionally, load initial notifications from backend here
    // Example: updateNotifBadge(0);
    // Real-time notification listener (Laravel Echo + Pusher)
    // This must be outside DOMContentLoaded if Echo is loaded after DOMContentLoaded
    @if(auth()->check())
    if (window.Echo) {
        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .notification((notif) => {
                addNotification(notif.data.title, notif.data.message, 'Just now');
            });
    }
    @endif
        // Notification item click handler (future: link to details)
        document.querySelectorAll('.notif-item').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                // Example: show alert or redirect
                // alert('Notification clicked: ' + this.dataset.id);
                // TODO: Implement redirect to details if needed
            });
        });
    });
    </script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var timeoutMinutes = {{ env('SESSION_LIFETIME', 20) }};
    if (timeoutMinutes > 0) {
        var timeoutMs = timeoutMinutes * 60 * 1000;
        var timer = setTimeout(autoLogout, timeoutMs);

        function autoLogout() {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf && csrf.getAttribute('content')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrf.getAttribute('content');

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            } else {
                window.location.href = '{{ route('login') }}';
            }
        }

        ['click','mousemove','keydown','scroll','touchstart'].forEach(function(evt) {
            window.addEventListener(evt, function() {
                clearTimeout(timer);
                timer = setTimeout(autoLogout, timeoutMs);
            });
        });
    }
});
// Session Timeout Modal logic
function showSessionTimeoutModal() {
    var modal = document.getElementById('sessionTimeoutModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}
</script>
<!-- Session Timeout Modal -->
<div id="sessionTimeoutModal" style="display:none;position:fixed;bottom:0;left:0;width:100vw;z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 28px;border-radius:16px;min-width:320px;max-width:98vw;box-shadow:0 8px 32px rgba(37,99,235,0.18);margin-bottom:32px;text-align:center;">
        <div style="font-size:1.25rem;font-weight:700;color:#e11d48;margin-bottom:10px;">Session Timed Out</div>
        <div style="color:#444;margin-bottom:18px;">Your session has expired due to inactivity. Please log in again to continue.</div>
        <a href="/login" style="background:#3762c8;color:#fff;padding:10px 28px;border:none;border-radius:8px;font-size:1.08rem;font-weight:600;text-decoration:none;">Log In</a>
    </div>
</div>
