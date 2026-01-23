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
.main-content{
    margin-left:260px;
    padding:36px 20px;
    position:relative;
    z-index:10;
}
.main-content-inner{
    width:100%;
    max-width:1100px;
    margin:auto;
    background:#fff;
    border-radius:18px;
    padding:32px;
    box-shadow:0 6px 24px rgba(0,0,0,.12);
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

            @if($role=='admin'||$role=='staff')
                <li><a href="/modules/facilities/index" class="nav-link"><i class="fa-solid fa-building"></i> Facilities</a></li>
                <li><a href="/modules/maintenance/index" class="nav-link"><i class="fa-solid fa-wrench"></i> Maintenance</a></li>
                <li><a href="/modules/billing/index" class="nav-link"><i class="fa-solid fa-money-bill-wave"></i> Billing & Cost</a></li>
            @endif

            {{-- Energy Monitoring: Available for Staff (restricted to assigned facility), Admin, and Energy Officer --}}
            @if($role=='admin'||$role=='energy_officer'||$role=='staff')
                <li><a href="/modules/energy/index" class="nav-link"><i class="fa-solid fa-bolt"></i> Energy Monitoring</a></li>
            @endif

            @if($role=='admin'||$role=='energy_officer')
                <li><a href="/modules/energy-efficiency-analysis" class="nav-link"><i class="fa-solid fa-chart-line"></i> Energy Efficiency Analysis</a></li>

                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span><i class="fa-solid fa-chart-bar"></i> Reports & Analytics</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li><a href="/modules/reports/index" class="nav-link">Main Dashboard</a></li>
                        <li><a href="/modules/reports/energy" class="nav-link">Energy Report</a></li>
                        <li><a href="/modules/reports/billing" class="nav-link">Billing Report</a></li>
                        <li><a href="/modules/reports/efficiency-summary" class="nav-link">Efficiency Summary</a></li>
                    </ul>
                </li>
            @endif

            {{-- Users & Roles and Settings: Admin only --}}
            @if($role=='admin')
                <li><a href="/modules/users/index" class="nav-link"><i class="fa-solid fa-users"></i> Users & Roles</a></li>
                <li><a href="/modules/settings/index" class="nav-link"><i class="fa-solid fa-gear"></i> System Settings</a></li>
            @endif
        </ul>
    </div>

    <div class="user-info">
        Welcome, {{ $user->full_name ?? $user->name ?? 'Admin' }}<br>
        <small style="color:#666;font-size:0.85rem;">{{ ucfirst($role ?? 'User') }}</small>
        <button class="dark-toggle" id="darkToggle">🌙 Dark Mode</button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
    
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

// Dark mode
const toggle=document.getElementById('darkToggle');
const toggleHeader=document.getElementById('darkToggleHeader');

function setDarkModeButtonText(btn) {
    if (!btn) return;
    const on = document.body.classList.contains('dark-mode');
    btn.textContent = on ? '☀ Light Mode' : '🌙 Dark Mode';
}

function syncDarkModeButtons() {
    setDarkModeButtonText(toggle);
    setDarkModeButtonText(toggleHeader);
}

if(localStorage.getItem('darkMode')==='on'){
    document.body.classList.add('dark-mode');
}
syncDarkModeButtons();

if(toggle){
    toggle.onclick=()=>{
        document.body.classList.toggle('dark-mode');
        const on=document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode',on?'on':'off');
        syncDarkModeButtons();
    };
}
if(toggleHeader){
    toggleHeader.onclick=()=>{
        document.body.classList.toggle('dark-mode');
        const on=document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode',on?'on':'off');
        syncDarkModeButtons();
    };
}
</script>

</body>
</html>
