<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','LGU Employee Portal')</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}

/* ===== BACKGROUND ===== */
body{
    min-height:100vh;
    background:url('/assets/img/cityhall.jpeg') center/cover no-repeat fixed;
    overflow-x:hidden;
}
body::before{
    content:"";
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.35);
    backdrop-filter:blur(6px);
    z-index:0;
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
    margin-left: 260px;
    padding: 0;
    position: relative;
    z-index: 10;
    height: 100vh;
    display: flex;
    flex-direction: column;
}
.main-header {
    background: #2c3e50;
    color: #fff;
    padding: 18px 32px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 8px solid #2c3e50;
    margin-left: -32px;
    position: sticky;
    top: 0;
    z-index: 20;
}
.main-content-inner {
    width: 100%;
    max-width: 1100px;
    margin: auto;
    background: #fff;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 6px 24px rgba(0,0,0,.12);
    flex: 1 1 auto;
    overflow-y: auto;
    height: calc(100vh - 70px);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
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
        padding:14px;
    }
    .main-content-inner{
        padding:18px;
        border-radius:14px;
    }
}

@media(max-width:480px){
    .main-content-inner{
        padding:14px;
        border-radius:12px;
    }
    h1{font-size:1.5rem}
    h2{font-size:1.3rem}
    h3{font-size:1.1rem}
}
</style>
</head>

<body>

<!-- HAMBURGER BUTTON -->
<button class="sidebar-hamburger" id="hamburger">
    <span></span><span></span><span></span>
</button>
<div class="sidebar-backdrop" id="backdrop"></div>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar-nav" id="sidebar">
    <div class="sidebar-top">
        <div class="site-logo">
            <img src="/img/logocityhall.png" alt="Logo">
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
                        <li><a href="/modules/reports/billing" class="nav-link">Billing Report</a></li>
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


<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
    <div class="main-header" style="background:rgba(44,62,80,.92); color:#fff; padding:10px 28px; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-left:8px solid #2c3e50; margin-left:-32px; position:sticky; top:0; z-index:20; display:flex; align-items:center; justify-content:space-between; min-height:48px; margin-bottom:14px;">
        <div style="display:flex; align-items:center; gap:12px; min-width:160px; justify-content:flex-end;">
            <!-- Reserved space for header title (QC Admin Panel) -->
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <button id="notifBtn" title="Notifications" style="background:none; border:none; color:#fff; font-size:1.35rem; cursor:pointer; position:relative; padding:6px; border-radius:50%; transition:background 0.15s, color 0.15s;">
                <i class="fa-regular fa-bell" id="notifBell" style="transition: color 0.2s;"></i>
                @php $alerts = $alerts ?? []; @endphp
                <span id="notifDot" style="{{ !empty($alerts) && count($alerts) > 0 ? 'display:flex;' : 'display:none;' }} align-items:center; justify-content:center; position:absolute; top:-4px; right:-4px; min-width:14px; height:14px; background:#e74c3c; color:#fff; font-size:0.72em; font-weight:700; border-radius:50%; box-shadow:0 0 4px 1px #e74c3c99; border:1.5px solid #fff; padding:0 3px; text-align:center; line-height:1; z-index:2;">
                    {{ !empty($alerts) && count($alerts) > 0 ? count($alerts) : '' }}
                </span>
            </button>
            <button id="darkToggleHeader" class="dark-toggle" title="Toggle dark mode" style="margin:0; background:none; border:none; color:#fff; font-size:1.5rem; padding:7px; border-radius:50%; transition:background 0.18s, color 0.18s; display:flex; align-items:center; justify-content:center;">
                <span id="darkModeIconWrap" style="display:inline-block; transition:transform 0.3s cubic-bezier(.4,2,.6,1);">
                    <i id="darkModeIcon" class="fa-regular fa-moon"></i>
                </span>
            </button>
            <div class="user-menu" style="position:relative; margin-left:10px;">
                @php
                    $user = auth()->user();
                    $userName = $user->full_name ?? $user->name ?? 'User';
                    $initial = strtoupper(mb_substr($userName,0,1));
                @endphp
                <button id="userMenuBtn" style="display:flex; align-items:center; background:none; border:none; color:#fff; cursor:pointer; padding:4px 10px 4px 4px; border-radius:18px; transition:background 0.15s; gap:8px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#3762c8; color:#fff; border-radius:50%; font-weight:600; font-size:1.1rem; letter-spacing:1px;">{{ $initial }}</span>
                    <span style="font-size:1rem; font-weight:500; max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $userName }}</span>
                    <i class="fa fa-caret-down" style="font-size:1rem;"></i>
                </button>
                <div id="userDropdown" style="display:none; position:absolute; right:0; top:110%; min-width:160px; background:#fff; color:#222; border-radius:10px; box-shadow:0 4px 18px rgba(0,0,0,0.13); z-index:100; padding:8px 0;">
                    <a href="/modules/users/profile" style="display:block; padding:10px 18px; color:#222; text-decoration:none; font-size:0.98rem; border-radius:6px 6px 0 0; transition:background 0.13s;">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" style="width:100%; text-align:left; background:none; border:none; color:#e74c3c; padding:10px 18px; font-size:0.98rem; border-radius:0 0 6px 6px; cursor:pointer; transition:background 0.13s;">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- User dropdown styles moved to head -->
    </style>
    <style>
    .user-menu:focus-within #userDropdown,
    #userMenuBtn.active + #userDropdown {
        display: block !important;
    }
    #userDropdown a:hover, #userDropdown button:hover {
        background: #f2f4fa;
    }
    /* Professional minimalist notif/dark mode */
    #notifBtn, #darkToggleHeader {
        outline: none;
    }
    #notifBtn:hover, #notifBtn:focus, #darkToggleHeader:hover, #darkToggleHeader:focus {
        background: rgba(255,255,255,0.08);
        color: #f9d423;
    }
    #notifBtn:active, #darkToggleHeader:active {
        background: rgba(255,255,255,0.16);
        color: #ff4e50;
    }
    #notifDot {
        transition: background 0.2s, box-shadow 0.2s;
    }
    #darkModeIcon {
        transition: color 0.3s, transform 0.3s cubic-bezier(.4,2,.6,1);
    }
    .dark-mode #darkModeIcon {
        color: #f9d423;
        transform: rotate(-180deg) scale(1.15);
    }
    </style>
    </div>
    <div class="main-content-inner">
        @yield('content')
    </div>
</div>

<script>
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
const toggle=document.getElementById('darkToggle');
const toggleHeader=document.getElementById('darkToggleHeader');
const notifBtn=document.getElementById('notifBtn');
const notifDot=document.getElementById('notifDot');
if(localStorage.getItem('darkMode')==='on'){
    document.body.classList.add('dark-mode');
}
// Notification dot visibility is now handled by blade (alerts count)
// Professional dark mode icon toggle
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
if(toggle){
    toggle.onclick=()=>{
        document.body.classList.toggle('dark-mode');
        const on=document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode',on?'on':'off');
        syncDarkModeButtons && syncDarkModeButtons();
    };
}
if(toggleHeader){
    toggleHeader.onclick=()=>{
        document.body.classList.toggle('dark-mode');
        const on=document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode',on?'on':'off');
        syncDarkModeButtons && syncDarkModeButtons();
        updateDarkModeIcon();
    };
}
</script>

</body>
</html>
