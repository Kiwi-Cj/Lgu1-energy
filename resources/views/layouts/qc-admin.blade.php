<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
(() => {
    try {
        if (localStorage.getItem('darkMode') === 'on') {
            document.documentElement.classList.add('dark-mode');
            document.documentElement.style.colorScheme = 'dark';
        }
    } catch (e) {}
})();
</script>
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
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.header-left h1{
    font-size:1.4rem;
    font-weight:600;
    color:#1f2937;
    line-height: 1.1;
}
.header-sub{
    font-size:.85rem;
    color:#6b7280;
    margin-top: 3px;
}

.header-right{
    display:flex;
    align-items:center;
    gap:10px;
}

.header-user{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:.9rem;
    color:#374151;
}

.header-icon-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #dbe4f0;
    border-radius: 11px;
    background: #f8fbff;
    color: #3762c8;
    font-size: 1.08rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .2s ease, border-color .2s ease, color .2s ease, transform .15s ease;
}

.header-icon-btn:hover {
    background: #ecf3ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.header-icon-btn:active {
    transform: scale(0.96);
}

.has-hover-label {
    position: relative;
}

.has-hover-label::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    top: calc(100% + 10px);
    transform: translateX(-50%) translateY(-4px);
    background: #0f172a;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 5px 8px;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.24);
    transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
    z-index: 1200;
}

.has-hover-label::before {
    content: '';
    position: absolute;
    left: 50%;
    top: calc(100% + 4px);
    transform: translateX(-50%);
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid #0f172a;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .16s ease, visibility .16s ease;
    z-index: 1200;
}

.has-hover-label:hover::after,
.has-hover-label:hover::before,
.has-hover-label:focus-visible::after,
.has-hover-label:focus-visible::before {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.notif-btn {
    position: relative;
}

.notif-count {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    padding: 2px 6px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 0.73rem;
    font-weight: 700;
    line-height: 1.15;
    text-align: center;
    border: 2px solid #fff;
}

.notif-count.is-hidden {
    display: none;
}

.user-menu-wrap {
    position: relative;
}

.user-menu-btn {
    border: 1px solid #dbe4f0;
    background: #f8fbff;
    border-radius: 999px;
    height: 40px;
    padding: 0 12px 0 6px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    cursor: pointer;
    transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.user-menu-btn:hover {
    background: #ecf3ff;
    border-color: #bfdbfe;
}

.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #dbeafe;
    background: #fff;
}

.user-name {
    font-size: 0.88rem;
    font-weight: 600;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-menu-caret {
    font-size: 0.85rem;
    color: #64748b;
}

.user-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    min-width: 220px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.14);
    overflow: hidden;
    z-index: 999;
}

.user-dropdown-head {
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
}

.user-dropdown-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}

.user-dropdown-role {
    color: #64748b;
    font-size: 0.78rem;
}

.user-dropdown-link {
    display: block;
    padding: 10px 14px;
    color: #1f2937;
    text-decoration: none;
    font-size: 0.9rem;
    border-bottom: 1px solid #eef2f7;
}

.user-dropdown-link:hover {
    background: #f8fafc;
}

.user-dropdown-link i {
    margin-right: 8px;
    color: #3762c8;
}

.user-dropdown .logout-btn {
    margin: 0;
    border-radius: 0;
    width: 100%;
    background: #2563eb;
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
    transition: background 0.3s ease, color 0.3s ease, margin-left 0.3s ease;
}

.main-content-inner {
    width: 100%;
    background: #e9f1f7;
    padding: 32px 28px;
    min-height: calc(100vh - 70px);
    color: #0f172a;
    transition: background 0.3s ease, color 0.3s ease;
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
html.dark-mode body { background: #111827; }
html.dark-mode .main-content { background: #0b1220; }
html.dark-mode .main-content-inner {
    background: linear-gradient(160deg, #111827 0%, #0f172a 100%);
    color: #e5e7eb;
}
body.dark-mode { background: #111827; }
body.dark-mode .top-header, body.dark-mode .sidebar-nav{ background:rgba(24,24,27,.96); color: white; }
body.dark-mode .header-left h1, body.dark-mode .header-user, body.dark-mode .nav-link { color: #f9fafb; }
body.dark-mode .header-icon-btn {
    background: #1f2937;
    border-color: #374151;
    color: #93c5fd;
}
body.dark-mode .header-icon-btn:hover {
    background: #111827;
    border-color: #475569;
}
body.dark-mode .notif-count {
    border-color: #1f2937;
}
body.dark-mode .user-menu-btn {
    background: #1f2937;
    border-color: #374151;
    color: #f1f5f9;
}
body.dark-mode .user-menu-btn:hover {
    background: #111827;
    border-color: #475569;
}
body.dark-mode .user-avatar {
    border-color: #334155;
}
body.dark-mode .user-menu-caret {
    color: #94a3b8;
}
body.dark-mode .user-dropdown {
    background: #111827;
    border-color: #334155;
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.55);
}
body.dark-mode .user-dropdown-head,
body.dark-mode .user-dropdown-link {
    border-color: #1f2937;
}
body.dark-mode .user-dropdown-name {
    color: #f8fafc;
}
body.dark-mode .user-dropdown-role {
    color: #94a3b8;
}
body.dark-mode .user-dropdown-link {
    color: #e2e8f0;
}
body.dark-mode .user-dropdown-link:hover {
    background: #1f2937;
}
body.dark-mode .user-dropdown .logout-btn {
    background: #1d4ed8;
}
body.dark-mode .main-content { background: #0b1220; }
body.dark-mode .main-content-inner {
    background: linear-gradient(160deg, #111827 0%, #0f172a 100%);
    color: #e5e7eb;
}
body.dark-mode .header-sub { color: #9ca3af; }
body.dark-mode .nav-link:hover { background: #374151; }

/* ===== GLOBAL MAIN CONTENT DARK OVERRIDES ===== */
body.dark-mode .main-content-inner h1,
body.dark-mode .main-content-inner h2,
body.dark-mode .main-content-inner h3,
body.dark-mode .main-content-inner h4,
body.dark-mode .main-content-inner h5,
body.dark-mode .main-content-inner h6 {
    color: #f8fafc;
}

body.dark-mode .main-content-inner p,
body.dark-mode .main-content-inner span,
body.dark-mode .main-content-inner label,
body.dark-mode .main-content-inner li,
body.dark-mode .main-content-inner td,
body.dark-mode .main-content-inner th,
body.dark-mode .main-content-inner small,
body.dark-mode .main-content-inner strong,
body.dark-mode .main-content-inner b {
    color: inherit;
}

body.dark-mode .main-content-inner a {
    color: #93c5fd;
}

body.dark-mode .main-content-inner input,
body.dark-mode .main-content-inner select,
body.dark-mode .main-content-inner textarea {
    background: #0b1220 !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

body.dark-mode .main-content-inner table {
    background: #0f172a !important;
    color: #e5e7eb !important;
}

body.dark-mode .main-content-inner table thead {
    background: #111827 !important;
}

body.dark-mode .main-content-inner table th,
body.dark-mode .main-content-inner table td,
body.dark-mode .main-content-inner tr {
    border-color: #334155 !important;
}

body.dark-mode .main-content-inner .report-card,
body.dark-mode .main-content-inner .card,
body.dark-mode .main-content-inner .table-responsive,
body.dark-mode .main-content-inner .incident-shell,
body.dark-mode .main-content-inner .history-shell,
body.dark-mode .main-content-inner .eff-shell,
body.dark-mode .main-content-inner .energy-report-shell {
    background: #0f172a !important;
    color: #e5e7eb !important;
    border-color: #1f2937 !important;
}

/* Fallback for pages using inline light backgrounds/colors in main content */
body.dark-mode .main-content-inner [style*="background:#fff"],
body.dark-mode .main-content-inner [style*="background: #fff"],
body.dark-mode .main-content-inner [style*="background:#ffffff"],
body.dark-mode .main-content-inner [style*="background: #ffffff"],
body.dark-mode .main-content-inner [style*="background:#f8fafc"],
body.dark-mode .main-content-inner [style*="background: #f8fafc"],
body.dark-mode .main-content-inner [style*="background:#f1f5f9"],
body.dark-mode .main-content-inner [style*="background: #f1f5f9"],
body.dark-mode .main-content-inner [style*="background:#e9effc"],
body.dark-mode .main-content-inner [style*="background: #e9effc"],
body.dark-mode .main-content-inner [style*="background:#e9f1f7"],
body.dark-mode .main-content-inner [style*="background: #e9f1f7"],
body.dark-mode .main-content-inner [style*="background:#f5f5f5"],
body.dark-mode .main-content-inner [style*="background: #f5f5f5"] {
    background: #0f172a !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}

body.dark-mode .main-content-inner [style*="color:#222"],
body.dark-mode .main-content-inner [style*="color: #222"],
body.dark-mode .main-content-inner [style*="color:#1e293b"],
body.dark-mode .main-content-inner [style*="color: #1e293b"],
body.dark-mode .main-content-inner [style*="color:#334155"],
body.dark-mode .main-content-inner [style*="color: #334155"],
body.dark-mode .main-content-inner [style*="color:#3762c8"],
body.dark-mode .main-content-inner [style*="color: #3762c8"],
body.dark-mode .main-content-inner [style*="color:#555"],
body.dark-mode .main-content-inner [style*="color: #555"],
body.dark-mode .main-content-inner [style*="color:#64748b"],
body.dark-mode .main-content-inner [style*="color: #64748b"],
body.dark-mode .main-content-inner [style*="color:#6b7280"],
body.dark-mode .main-content-inner [style*="color: #6b7280"] {
    color: #e5e7eb !important;
}

body.dark-mode .main-content-inner [style*="box-shadow"] {
    box-shadow: 0 8px 24px rgba(2, 6, 23, 0.45) !important;
}

/* ===== RESPONSIVE BREAKPOINTS ===== */
@media(max-width:991px){
    .sidebar-nav { transform: translateX(-100%); }
    .sidebar-nav.open { transform: translateX(0); }
    .top-header { left: 0; padding-left: 70px; }
    .main-content { margin-left: 0; }
    .sidebar-hamburger { display: block; }
    .sidebar-backdrop.active { display: block; }
}

@media(max-width:480px){
    .header-sub { display: none; }
    .main-content-inner { padding: 20px 15px; }
    .header-left h1 { font-size: 1.1rem; }
    .user-name { display: none; }
    .user-menu-btn { padding-right: 8px; }
}

/* Additional Styles from Original */
.user-info{ padding:16px; text-align:center; border-top:1px solid rgba(0,0,0,.1); }
.logout-btn{ margin-top:8px; background:#3762c8; border:none; color:#fff; padding:8px 18px; border-radius:8px; cursor:pointer; width: 100%; }
.notif-btn:hover .fa-bell { color: #1d4ed8; }

.notif-dropdown {
    display: none;
    position: absolute;
    right: 60px;
    top: 60px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
    border-radius: 12px;
    min-width: 340px;
    max-width: 92vw;
    z-index: 999;
    max-height: 430px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
}

.notif-header {
    padding: 12px 14px;
    border-bottom: 1px solid #eef2f7;
    font-size: 0.97rem;
    font-weight: 700;
    color: #3762c8;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 2;
}

.notif-mark-btn {
    border: 1px solid #dbeafe;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    cursor: pointer;
}

.notif-mark-btn:hover {
    background: #dbeafe;
}

.notif-filter-bar {
    padding: 8px 12px;
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #eef2f7;
    position: sticky;
    top: 46px;
    background: #fff;
    z-index: 2;
}

.notif-filter-btn {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    cursor: pointer;
}

.notif-filter-btn:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.notif-filter-btn.active {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1d4ed8;
}

.notif-item {
    display: block;
    padding: 12px 14px;
    border-bottom: 1px solid #eef2f7;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease;
}

.notif-item:hover {
    background: #f8fafc;
}

.notif-item.is-read {
    background: #f8fafc;
    opacity: 0.85;
}

.notif-item.is-unread {
    background: #ffffff;
}

.notif-item.is-hidden {
    display: none;
}

.notif-item-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.notif-icon {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.notif-head-copy {
    min-width: 0;
    flex: 1;
}

.notif-title {
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 700;
    display: block;
    line-height: 1.2;
}

.notif-level-badge {
    display: inline-block;
    margin-top: 4px;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 2px 8px;
}

.notif-unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #2563eb;
    margin-top: 4px;
    flex-shrink: 0;
}

.notif-message {
    margin: 8px 0 0 38px;
    font-size: 0.92rem;
    line-height: 1.38;
    word-break: break-word;
}

.notif-time {
    margin: 6px 0 0 38px;
    font-size: 0.8rem;
    color: #64748b;
}

.notif-filter-empty {
    padding: 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 0.9rem;
}

.notif-sev-critical .notif-icon {
    background: #fee2e2;
    color: #b91c1c;
}

.notif-sev-critical .notif-level-badge {
    background: #fee2e2;
    color: #b91c1c;
}

.notif-sev-critical .notif-message {
    color: #b91c1c;
}

.notif-sev-very-high .notif-icon {
    background: #ffe4e6;
    color: #be123c;
}

.notif-sev-very-high .notif-level-badge {
    background: #ffe4e6;
    color: #be123c;
}

.notif-sev-very-high .notif-message {
    color: #be123c;
}

.notif-sev-high .notif-icon {
    background: #ffedd5;
    color: #c2410c;
}

.notif-sev-high .notif-level-badge {
    background: #ffedd5;
    color: #c2410c;
}

.notif-sev-high .notif-message {
    color: #c2410c;
}

.notif-sev-warning .notif-icon {
    background: #fef9c3;
    color: #a16207;
}

.notif-sev-warning .notif-level-badge {
    background: #fef9c3;
    color: #a16207;
}

.notif-sev-warning .notif-message {
    color: #92400e;
}

.notif-sev-info .notif-icon {
    background: #dbeafe;
    color: #1d4ed8;
}

.notif-sev-info .notif-level-badge {
    background: #dbeafe;
    color: #1d4ed8;
}

.notif-sev-info .notif-message {
    color: #1e3a8a;
}

/* Notification dropdown dark mode */
body.dark-mode .notif-dropdown {
    background: #0f172a;
    border-color: #253043;
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.6);
}
body.dark-mode .notif-header {
    background: #0f172a;
    border-bottom-color: #1f2a3d;
    color: #93c5fd;
}
body.dark-mode .notif-mark-btn {
    background: #1e293b;
    border-color: #334155;
    color: #bfdbfe;
}
body.dark-mode .notif-mark-btn:hover {
    background: #334155;
}
body.dark-mode .notif-filter-bar {
    background: #0f172a;
    border-bottom-color: #1f2a3d;
}
body.dark-mode .notif-filter-btn {
    background: #111827;
    border-color: #334155;
    color: #cbd5e1;
}
body.dark-mode .notif-filter-btn:hover {
    background: #1e293b;
    border-color: #475569;
    color: #dbeafe;
}
body.dark-mode .notif-filter-btn.active {
    background: #1d4ed8;
    border-color: #2563eb;
    color: #eff6ff;
}
body.dark-mode .notif-item {
    border-bottom-color: #1f2a3d;
}
body.dark-mode .notif-item:hover {
    background: #162235;
}
body.dark-mode .notif-item.is-read {
    background: #111827;
    opacity: 0.78;
}
body.dark-mode .notif-item.is-unread {
    background: #0f172a;
}
body.dark-mode .notif-title {
    color: #e2e8f0;
}
body.dark-mode .notif-time {
    color: #94a3b8;
}
body.dark-mode .notif-filter-empty,
body.dark-mode #notifEmpty {
    color: #94a3b8 !important;
}
body.dark-mode .notif-unread-dot {
    background: #60a5fa;
}
body.dark-mode .notif-sev-critical .notif-icon,
body.dark-mode .notif-sev-critical .notif-level-badge {
    background: #3f1517;
    color: #fca5a5;
}
body.dark-mode .notif-sev-critical .notif-message {
    color: #fca5a5;
}
body.dark-mode .notif-sev-very-high .notif-icon,
body.dark-mode .notif-sev-very-high .notif-level-badge {
    background: #3b1420;
    color: #f9a8d4;
}
body.dark-mode .notif-sev-very-high .notif-message {
    color: #f9a8d4;
}
body.dark-mode .notif-sev-high .notif-icon,
body.dark-mode .notif-sev-high .notif-level-badge {
    background: #3a200f;
    color: #fdba74;
}
body.dark-mode .notif-sev-high .notif-message {
    color: #fdba74;
}
body.dark-mode .notif-sev-warning .notif-icon,
body.dark-mode .notif-sev-warning .notif-level-badge {
    background: #3a3012;
    color: #fde68a;
}
body.dark-mode .notif-sev-warning .notif-message {
    color: #fde68a;
}
body.dark-mode .notif-sev-info .notif-icon,
body.dark-mode .notif-sev-info .notif-level-badge {
    background: #132a4a;
    color: #93c5fd;
}
body.dark-mode .notif-sev-info .notif-message {
    color: #bfdbfe;
}

@media(max-width:640px) {
    .notif-dropdown {
        right: 12px;
        top: 58px;
        left: 12px;
        min-width: 0;
        max-width: unset;
    }
}
</style>
</head>

<body>
<script>
if (document.documentElement.classList.contains('dark-mode')) {
    document.body.classList.add('dark-mode');
}
</script>

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
        <button class="header-icon-btn notif-btn has-hover-label" id="notifBtn" aria-label="Notifications" data-tooltip="Notifications">
            <i class="fa fa-bell"></i>
            <span id="notifCount" class="notif-count {{ ($unreadNotifCount ?? 0) > 0 ? '' : 'is-hidden' }}">{{ $unreadNotifCount ?? 0 }}</span>
        </button>

        <div id="notifDropdown" class="notif-dropdown">
            <div class="notif-header">
                <span>Notifications</span>
                <button type="button" id="notifMarkAll" class="notif-mark-btn" {{ ($unreadNotifCount ?? 0) > 0 ? '' : 'style=display:none;' }}>
                    Mark all read
                </button>
            </div>
            <div class="notif-filter-bar">
                <button type="button" class="notif-filter-btn active" data-filter="all">All</button>
                <button type="button" class="notif-filter-btn" data-filter="unread">Unread</button>
                <button type="button" class="notif-filter-btn" data-filter="critical">Critical</button>
            </div>
            <div id="notifList">
                @forelse($notifications ?? [] as $notif)
                    @php
                        $notifMessage = (string) ($notif->message ?? '');
                        $notifLower = strtolower($notifMessage);
                        $notifType = strtolower((string) ($notif->type ?? 'alert'));
                        if ($notifType === 'alert') {
                            if (\Illuminate\Support\Str::contains($notifLower, 'incident:')) {
                                $notifType = 'incident';
                            } elseif (\Illuminate\Support\Str::contains($notifLower, 'maintenance:')) {
                                $notifType = 'maintenance';
                            } elseif (\Illuminate\Support\Str::contains($notifLower, 'baseline')) {
                                $notifType = 'consumption';
                            } elseif (\Illuminate\Support\Str::contains($notifLower, 'alert:')) {
                                $notifType = 'record';
                            }
                        }
                        $notifSeverity = 'info';
                        if (\Illuminate\Support\Str::contains($notifLower, 'critical')) {
                            $notifSeverity = 'critical';
                        } elseif (\Illuminate\Support\Str::contains($notifLower, 'very high')) {
                            $notifSeverity = 'very-high';
                        } elseif (\Illuminate\Support\Str::contains($notifLower, 'high')) {
                            $notifSeverity = 'high';
                        } elseif (\Illuminate\Support\Str::contains($notifLower, 'warning') || \Illuminate\Support\Str::contains($notifLower, 'pending')) {
                            $notifSeverity = 'warning';
                        }

                        $notifSeverityLabel = [
                            'critical' => 'Critical',
                            'very-high' => 'Very High',
                            'high' => 'High',
                            'warning' => 'Warning',
                            'info' => 'Info',
                        ][$notifSeverity] ?? 'Info';

                        $notifIcon = [
                            'critical' => 'fa-circle-exclamation',
                            'very-high' => 'fa-triangle-exclamation',
                            'high' => 'fa-fire-flame-curved',
                            'warning' => 'fa-bell',
                            'info' => 'fa-circle-info',
                        ][$notifSeverity] ?? 'fa-circle-info';

                        $notifDisplayTitle = match ($notifType) {
                            'incident' => 'Incident Alert',
                            'maintenance' => 'Maintenance Alert',
                            'consumption' => 'Consumption Alert',
                            'record' => 'Energy Alert',
                            default => 'System Alert',
                        };
                        $notifStoredTitle = trim((string) ($notif->title ?? ''));
                        if ($notifStoredTitle !== '' && strtolower($notifStoredTitle) !== 'system alert') {
                            $notifDisplayTitle = $notifStoredTitle;
                        }

                        $notifTargetUrl = route('dashboard.index');
                        if ($notifType === 'incident' || \Illuminate\Support\Str::contains($notifLower, 'incident:')) {
                            $notifTargetUrl = route('energy-incidents.index');
                        } elseif ($notifType === 'maintenance' || \Illuminate\Support\Str::contains($notifLower, 'maintenance:')) {
                            $notifTargetUrl = route('modules.maintenance.index');
                        } elseif ($notifType === 'record' || $notifType === 'consumption' || \Illuminate\Support\Str::contains($notifLower, 'alert:') || \Illuminate\Support\Str::contains($notifLower, 'baseline')) {
                            $notifTargetUrl = route('dashboard.index');
                        }
                    @endphp
                    <a href="{{ $notifTargetUrl }}" class="notif-item notif-sev-{{ $notifSeverity }} {{ $notif->read_at ? 'is-read' : 'is-unread' }}" data-id="{{ $notif->id }}" data-level="{{ $notifSeverity }}" data-read="{{ $notif->read_at ? 'read' : 'unread' }}">
                        <div class="notif-item-head">
                            <span class="notif-icon"><i class="fa-solid {{ $notifIcon }}"></i></span>
                            <div class="notif-head-copy">
                                <strong class="notif-title">{{ $notifDisplayTitle }}</strong>
                                <span class="notif-level-badge">{{ $notifSeverityLabel }}</span>
                            </div>
                            @if(!$notif->read_at)
                                <span class="notif-unread-dot"></span>
                            @endif
                        </div>
                        <div class="notif-message">{{ $notif->message }}</div>
                        <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div id="notifEmpty" style="padding:16px;text-align:center;color:#888;font-size:0.95rem;">No notifications</div>
                @endforelse
                <div id="notifFilterEmpty" class="notif-filter-empty" style="display:none;">No notifications for this filter.</div>
            </div>
        </div>

        <button id="darkToggleHeader" class="header-icon-btn has-hover-label" aria-label="Toggle dark mode" data-tooltip="Toggle theme">
            <i id="darkModeIcon" class="fa fa-moon"></i>
        </button>

        <div class="header-user user-menu-wrap">
            <button id="userMenuBtn" class="user-menu-btn has-hover-label" data-tooltip="Account menu">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile Photo" class="user-avatar">
                <span class="user-name">{{ $user->full_name ?? $user->name ?? 'Admin' }}</span>
                <i class="fa fa-caret-down user-menu-caret"></i>
            </button>
            <div id="userDropdown" class="user-dropdown">
                <div class="user-dropdown-head">
                    <div class="user-dropdown-name">{{ $user->full_name ?? $user->name ?? 'Admin' }}</div>
                    <div class="user-dropdown-role">{{ ucwords(str_replace('_', ' ', is_string(auth()->user()?->role) ? auth()->user()->role : 'User')) }}</div>
                </div>
                <a href="{{ route('profile.show') }}" class="user-dropdown-link">
                    <i class="fa fa-user"></i> My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-btn">Logout</button>
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
    const notifList = document.getElementById('notifList');
    const notifCountEl = document.getElementById('notifCount');
    const notifMarkAllBtn = document.getElementById('notifMarkAll');
    const notifFilterButtons = document.querySelectorAll('.notif-filter-btn');
    const notifFilterEmpty = document.getElementById('notifFilterEmpty');
    const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');
    const notifRoutes = {
        incident: "{{ route('energy-incidents.index') }}",
        maintenance: "{{ route('modules.maintenance.index') }}",
        dashboard: "{{ route('dashboard.index') }}"
    };
    let activeNotifFilter = 'all';

    const inferNotifSeverity = (message) => {
        const text = String(message || '').toLowerCase();
        if (text.includes('critical')) return 'critical';
        if (text.includes('very high')) return 'very-high';
        if (text.includes('high')) return 'high';
        if (text.includes('warning') || text.includes('pending')) return 'warning';
        return 'info';
    };

    const resolveNotifUrl = (message, type) => {
        const t = String(type || '').toLowerCase();
        const text = String(message || '').toLowerCase();
        if (t === 'incident' || text.includes('incident:')) return notifRoutes.incident;
        if (t === 'maintenance' || text.includes('maintenance:')) return notifRoutes.maintenance;
        return notifRoutes.dashboard;
    };

    const resolveNotifTitle = (title, message, type) => {
        const rawTitle = String(title || '').trim();
        const text = String(message || '').toLowerCase();
        const t = String(type || '').toLowerCase();
        const isGeneric = rawTitle === '' || rawTitle.toLowerCase() === 'system alert';
        if (!isGeneric) return rawTitle;
        if (t === 'incident' || text.includes('incident:')) return 'Incident Alert';
        if (t === 'maintenance' || text.includes('maintenance:')) return 'Maintenance Alert';
        if (t === 'consumption' || text.includes('baseline')) return 'Consumption Alert';
        if (t === 'record' || text.includes('alert:')) return 'Energy Alert';
        return 'System Alert';
    };

    const severityLabel = (severity) => ({
        'critical': 'Critical',
        'very-high': 'Very High',
        'high': 'High',
        'warning': 'Warning',
        'info': 'Info'
    })[severity] || 'Info';

    const severityIcon = (severity) => ({
        'critical': 'fa-circle-exclamation',
        'very-high': 'fa-triangle-exclamation',
        'high': 'fa-fire-flame-curved',
        'warning': 'fa-bell',
        'info': 'fa-circle-info'
    })[severity] || 'fa-circle-info';

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const updateMarkAllVisibility = () => {
        if (!notifMarkAllBtn || !notifCountEl) return;
        const unread = parseInt(notifCountEl.innerText, 10) || 0;
        notifMarkAllBtn.style.display = unread > 0 ? 'inline-flex' : 'none';
    };

    const renderNotifItem = (title, message, timeLabel, severity, unread = true, targetUrl = '#') => {
        const unreadDot = unread ? '<span class=\"notif-unread-dot\"></span>' : '';
        const stateClass = unread ? 'is-unread' : 'is-read';
        return `<a href=\"${escapeHtml(targetUrl)}\" class=\"notif-item notif-sev-${severity} ${stateClass}\" data-level=\"${severity}\" data-read=\"${unread ? 'unread' : 'read'}\">
                    <div class=\"notif-item-head\">
                        <span class=\"notif-icon\"><i class=\"fa-solid ${severityIcon(severity)}\"></i></span>
                        <div class=\"notif-head-copy\">
                            <strong class=\"notif-title\">${escapeHtml(title)}</strong>
                            <span class=\"notif-level-badge\">${escapeHtml(severityLabel(severity))}</span>
                        </div>
                        ${unreadDot}
                    </div>
                    <div class=\"notif-message\">${escapeHtml(message)}</div>
                    <div class=\"notif-time\">${escapeHtml(timeLabel)}</div>
                </a>`;
    };

    const isCriticalSeverity = (level) => ['critical', 'very-high', 'high'].includes(String(level || '').toLowerCase());

    const applyNotifFilter = () => {
        if (!notifList) return;
        const items = Array.from(notifList.querySelectorAll('.notif-item'));
        let visibleCount = 0;
        items.forEach((item) => {
            const level = item.dataset.level || 'info';
            const readState = item.dataset.read || (item.classList.contains('is-unread') ? 'unread' : 'read');
            let show = true;
            if (activeNotifFilter === 'unread') {
                show = readState === 'unread';
            } else if (activeNotifFilter === 'critical') {
                show = isCriticalSeverity(level);
            }
            item.classList.toggle('is-hidden', !show);
            if (show) visibleCount++;
        });
        if (notifFilterEmpty) {
            notifFilterEmpty.style.display = items.length > 0 && visibleCount === 0 ? 'block' : 'none';
        }
    };

    notifFilterButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            activeNotifFilter = btn.dataset.filter || 'all';
            notifFilterButtons.forEach((b) => b.classList.toggle('active', b === btn));
            applyNotifFilter();
        });
    });

    userBtn.onclick = (e) => { e.stopPropagation(); userDrop.style.display = userDrop.style.display === 'block' ? 'none' : 'block'; notifDrop.style.display = 'none'; };
    notifBtn.onclick = (e) => { e.stopPropagation(); notifDrop.style.display = notifDrop.style.display === 'block' ? 'none' : 'block'; userDrop.style.display = 'none'; applyNotifFilter(); };
    userDrop.addEventListener('click', (e) => e.stopPropagation());
    notifDrop.addEventListener('click', (e) => e.stopPropagation());
    
    document.addEventListener('click', () => {
        userDrop.style.display = 'none';
        notifDrop.style.display = 'none';
    });

    if (notifMarkAllBtn) {
        notifMarkAllBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            try {
                const res = await fetch("{{ route('notifications.markAllRead') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                document.querySelectorAll('#notifList .notif-item').forEach((el) => {
                    el.classList.remove('is-unread');
                    el.classList.add('is-read');
                    el.dataset.read = 'read';
                    const dot = el.querySelector('.notif-unread-dot');
                    if (dot) dot.remove();
                });
                if (notifCountEl) {
                    notifCountEl.innerText = '0';
                    notifCountEl.style.display = 'none';
                }
                updateMarkAllVisibility();
                applyNotifFilter();
            } catch (err) {
                console.error('Failed to mark notifications as read', err);
            }
        });
    }
    updateMarkAllVisibility();
    applyNotifFilter();

    // 4. Dark Mode Logic
    const darkBtn = document.getElementById('darkToggleHeader');
    const darkIcon = document.getElementById('darkModeIcon');
    const applyDarkMode = (isDark) => {
        document.documentElement.classList.toggle('dark-mode', isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
        document.body.classList.toggle('dark-mode', isDark);
        localStorage.setItem('darkMode', isDark ? 'on' : 'off');
        if (darkIcon) darkIcon.className = isDark ? 'fa fa-sun' : 'fa fa-moon';
    };
    const initialDark = document.documentElement.classList.contains('dark-mode') || localStorage.getItem('darkMode') === 'on';
    applyDarkMode(initialDark);
    if (darkBtn) {
        darkBtn.onclick = () => {
            applyDarkMode(!document.body.classList.contains('dark-mode'));
        };
    }

    // 5. Session Timeout Logic
    var timeoutMinutes = {{ (int) config('session.lifetime', 20) }};
    var timeoutMs = timeoutMinutes * 60 * 1000;
    var timeoutModal = document.getElementById('sessionTimeoutModal');
    var timer = null;

    var resetSessionTimer = () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            if (timeoutModal) {
                timeoutModal.style.display = 'flex';
            }
        }, timeoutMs);
    };

    resetSessionTimer();

    // Track more real user activity to avoid false timeout while actively using the app.
    ['click', 'mousemove', 'keydown', 'scroll', 'wheel', 'touchstart', 'touchmove'].forEach(evt => {
        window.addEventListener(evt, resetSessionTimer, { passive: true });
    });
});

// Pusher/Echo Notification logic (kept as per your original)
@if(auth()->check())
    if (window.Echo) {
        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
            .notification((notif) => {
                const notifListEl = document.getElementById('notifList');
                const notifCountElem = document.getElementById('notifCount');
                const notifMarkAll = document.getElementById('notifMarkAll');
                const rawTitle = notif?.data?.title || notif?.title || 'System Alert';
                const message = notif?.data?.message || notif?.message || 'New alert received.';
                const type = notif?.data?.type || notif?.type || '';
                const title = resolveNotifTitle(rawTitle, message, type);
                const severity = inferNotifSeverity(message);
                const targetUrl = resolveNotifUrl(message, type);
                const html = renderNotifItem(title, message, 'just now', severity, true, targetUrl);
                const notifEmpty = document.getElementById('notifEmpty');
                if (notifEmpty) notifEmpty.remove();
                if (notifListEl) notifListEl.insertAdjacentHTML('afterbegin', html);

                let count = parseInt(notifCountElem?.innerText, 10) || 0;
                if (notifCountElem) {
                    notifCountElem.innerText = count + 1;
                    notifCountElem.style.display = 'inline-block';
                }
                if (notifMarkAll) {
                    notifMarkAll.style.display = 'inline-flex';
                }
                applyNotifFilter();
            });
    }
@endif
</script>

</body>
</html>
