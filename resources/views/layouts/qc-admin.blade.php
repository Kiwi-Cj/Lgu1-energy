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
 <link rel="icon" href="{{ $systemFaviconUrl }}" />

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
    .top-header {
        padding-left: 68px;
        padding-right: 10px;
    }
    .header-left {
        min-width: 0;
        flex: 1;
    }
    .header-left h1 { font-size: 1.1rem; }
    .header-right { gap: 6px; }
    .header-icon-btn {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
    }
    .main-content-inner { padding: 18px 12px; }
    .user-name { display: none; }
    .user-menu-btn {
        width: 44px;
        padding: 0 5px;
        justify-content: center;
        gap: 3px;
    }
    .user-menu-caret { font-size: .72rem; }
}

/* ===== APP-WIDE MOBILE UI SAFEGUARDS ===== */
.main-content-inner {
    min-width: 0;
}

.main-content-inner img,
.main-content-inner video,
.main-content-inner canvas,
.main-content-inner svg {
    max-width: 100%;
}

.main-content-inner input,
.main-content-inner select,
.main-content-inner textarea,
.main-content-inner button {
    max-width: 100%;
}

@media (max-width: 768px) {
    .top-header {
        height: 64px;
        padding: 0 10px 0 64px;
        gap: 8px;
    }

    .main-content {
        padding-top: 64px;
    }

    .main-content-inner {
        min-height: calc(100vh - 64px);
        padding: 16px 12px;
    }

    .header-left {
        min-width: 0;
        flex: 1 1 auto;
    }

    .header-left h1 {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .header-right {
        flex: 0 0 auto;
        gap: 5px;
    }

    .header-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 9px;
    }

    .main-content-inner h1 { font-size: clamp(1.35rem, 6vw, 1.75rem); }
    .main-content-inner h2 { font-size: clamp(1.2rem, 5vw, 1.5rem); }
    .main-content-inner h3 { font-size: clamp(1.05rem, 4.5vw, 1.3rem); }

    .main-content-inner table {
        display: block;
        width: 100% !important;
        max-width: 100%;
        overflow-x: auto;
        overscroll-behavior-inline: contain;
        -webkit-overflow-scrolling: touch;
    }

    .main-content-inner .table-responsive,
    .main-content-inner .table-scroll,
    .main-content-inner [class*="table-wrap"],
    .main-content-inner [class*="table-container"] {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .main-content-inner [style*="display:grid"],
    .main-content-inner [style*="display: grid"] {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .main-content-inner [style*="display:flex"],
    .main-content-inner [style*="display: flex"] {
        min-width: 0;
    }

    .main-content-inner form [style*="display:flex"],
    .main-content-inner form [style*="display: flex"],
    .main-content-inner [class*="filter"] {
        flex-wrap: wrap;
    }

    .main-content-inner input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
    .main-content-inner select,
    .main-content-inner textarea {
        width: 100%;
        min-width: 0 !important;
    }

    .main-content-inner .modal-overlay,
    .main-content-inner [id$="Modal"][style*="position:fixed"],
    .main-content-inner [id$="Modal"][style*="position: fixed"] {
        padding: 12px !important;
        overflow-y: auto;
    }

    .main-content-inner .modal-content,
    .main-content-inner [class*="modal-dialog"],
    .main-content-inner [id$="Modal"] > div {
        width: 100% !important;
        max-width: calc(100vw - 24px) !important;
        max-height: calc(100dvh - 24px);
        margin: auto !important;
        padding: 20px 16px !important;
        overflow-y: auto;
    }

    .main-content-inner [style*="position:fixed"][style*="right:"],
    .main-content-inner [style*="position: fixed"][style*="right:"] {
        max-width: calc(100vw - 24px) !important;
    }

    .global-toast-stack {
        top: 74px;
        right: 12px;
        left: 12px;
        width: auto;
    }

    .has-hover-label::before,
    .has-hover-label::after {
        display: none;
    }
}

@media (max-width: 480px) {
    .sidebar-hamburger {
        top: 12px;
        left: 12px;
    }

    .header-user .user-menu-btn .fa-chevron-down {
        display: none;
    }

    .main-content-inner .btn,
    .main-content-inner button,
    .main-content-inner a[class*="btn"] {
        min-height: 40px;
    }

    .session-timeout-dialog {
        padding: 28px 18px 22px !important;
    }

    .session-timeout-title {
        font-size: 1.45rem !important;
        line-height: 1.2;
    }

    .session-timeout-message {
        font-size: .95rem !important;
    }
}

/* Additional Styles from Original */
.user-info{ padding:16px; text-align:center; border-top:1px solid rgba(0,0,0,.1); }
.logout-btn{ margin-top:8px; background:#3762c8; border:none; color:#fff; padding:8px 18px; border-radius:8px; cursor:pointer; width: 100%; }
.notif-btn:hover .fa-bell { color: #1d4ed8; }

/* ===== GLOBAL FLASH TOASTS ===== */
.global-toast-stack {
    position: fixed;
    top: 22px;
    right: 22px;
    z-index: 100000;
    display: grid;
    gap: 10px;
    width: min(420px, calc(100vw - 32px));
    pointer-events: none;
}

.global-toast {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 12px;
    padding: 14px 16px;
    font-weight: 800;
    border: 1px solid transparent;
    box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
    opacity: 1;
    transform: translateY(0);
    transition: opacity .22s ease, transform .22s ease;
    pointer-events: auto;
}

.global-toast-success {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.global-toast-error {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}

.global-toast.is-hidden {
    opacity: 0;
    transform: translateY(-8px);
    pointer-events: none;
}

body.dark-mode .global-toast-success {
    background: #052e1a;
    color: #bbf7d0;
    border-color: #166534;
}

body.dark-mode .global-toast-error {
    background: #450a0a;
    color: #fecaca;
    border-color: #991b1b;
}

.notif-dropdown {
    display: none;
    position: absolute;
    right: 60px;
    top: 60px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
    border-radius: 12px;
    width: clamp(320px, 34vw, 410px);
    max-width: calc(100vw - 20px);
    z-index: 999;
    max-height: 380px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
}

.notif-header {
    padding: 10px 12px;
    border-bottom: 1px solid #eef2f7;
    font-size: 0.9rem;
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
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 9px;
    cursor: pointer;
}

.notif-mark-btn:hover {
    background: #dbeafe;
}

.notif-filter-bar {
    padding: 6px 10px;
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #eef2f7;
    position: sticky;
    top: 42px;
    background: #fff;
    z-index: 2;
}

.notif-filter-btn {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 4px 9px;
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

.notif-view-all {
    position: sticky;
    bottom: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 11px 12px;
    border-top: 1px solid #dbeafe;
    background: #ffffff;
    color: #2563eb;
    font-size: 0.82rem;
    font-weight: 800;
    text-decoration: none;
}

.notif-view-all:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

.notif-item {
    display: block;
    padding: 10px 12px;
    border-bottom: 1px solid #eef2f7;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    position: relative;
}

.notif-item:hover {
    background: #f8fafc;
}

.notif-item.is-read {
    background: #f8fafc;
    opacity: 0.84;
}

.notif-item.is-unread {
    background: linear-gradient(90deg, #eff6ff 0%, #ffffff 22%);
    box-shadow: inset 3px 0 0 #2563eb;
}

.notif-item.is-unread::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    border-radius: 999px;
    background: linear-gradient(180deg, #3b82f6, #1d4ed8);
    opacity: 0.95;
}

.notif-item.is-unread:hover {
    background: linear-gradient(90deg, #dbeafe 0%, #f8fbff 24%);
    transform: translateY(-1px);
    box-shadow: inset 3px 0 0 #1d4ed8, 0 4px 12px rgba(37, 99, 235, 0.12);
}

.notif-item.is-hidden {
    display: none;
}

.notif-item-head {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.notif-icon {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.75rem;
}

.notif-head-copy {
    min-width: 0;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.notif-title {
    color: #0f172a;
    font-size: 0.84rem;
    font-weight: 700;
    line-height: 1.2;
}

.notif-level-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    border-radius: 999px;
    padding: 2px 7px;
}

.notif-unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #2563eb;
    margin-top: 3px;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
}

.notif-message {
    margin: 6px 0 0 32px;
    font-size: 0.82rem;
    line-height: 1.32;
    word-break: break-word;
}

.notif-facility {
    margin: 5px 0 0 32px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: calc(100% - 32px);
    padding: 4px 10px;
    border-radius: 999px;
    background: #eef4ff;
    border: 1px solid #cfe0ff;
    color: #1e40af;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.02em;
}

.notif-facility i {
    font-size: 0.68rem;
}

.notif-time {
    margin: 5px 0 0 32px;
    font-size: 0.74rem;
    color: #64748b;
}

.notif-item.is-unread .notif-title {
    font-weight: 800;
    color: #0b1f4a;
}

.notif-item.is-unread .notif-time {
    color: #1d4ed8;
    font-weight: 600;
}

.notif-item.is-read .notif-level-badge {
    opacity: 0.82;
}

@media (max-width: 520px) {
    .notif-head-copy {
        gap: 6px;
    }

    .notif-title {
        font-size: 0.82rem;
    }
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
    background: linear-gradient(90deg, rgba(30, 58, 138, 0.28) 0%, #0f172a 26%);
    box-shadow: inset 3px 0 0 #60a5fa;
}
body.dark-mode .notif-item.is-unread::before {
    background: linear-gradient(180deg, #60a5fa, #2563eb);
}
body.dark-mode .notif-item.is-unread:hover {
    background: linear-gradient(90deg, rgba(30, 64, 175, 0.35) 0%, #162235 28%);
    box-shadow: inset 3px 0 0 #93c5fd, 0 8px 18px rgba(2, 6, 23, 0.35);
}
body.dark-mode .notif-title {
    color: #e2e8f0;
}
body.dark-mode .notif-item.is-unread .notif-title {
    color: #eff6ff;
}
body.dark-mode .notif-time {
    color: #94a3b8;
}
body.dark-mode .notif-facility {
    background: #13233d;
    border-color: #27436c;
    color: #bfdbfe;
}
body.dark-mode .notif-item.is-unread .notif-time {
    color: #93c5fd;
}
body.dark-mode .notif-filter-empty,
body.dark-mode #notifEmpty {
    color: #94a3b8 !important;
}
body.dark-mode .notif-unread-dot {
    background: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
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
body.dark-mode .notif-view-all {
    background: #111827;
    border-color: #334155;
    color: #93c5fd;
}

.secure-download-modal {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.48);
    padding: 18px;
}

.secure-download-dialog {
    width: min(420px, 94vw);
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 22px 60px rgba(15, 23, 42, 0.24);
    padding: 24px 22px 22px;
    position: relative;
}

.secure-download-close {
    position: absolute;
    top: 10px;
    right: 14px;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 1.8rem;
    cursor: pointer;
}

.secure-download-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    color: #1d4ed8;
    background: #dbeafe;
    margin-bottom: 12px;
}

.secure-download-dialog h2 {
    margin: 0;
    color: #0f172a;
    font-size: 1.22rem;
    font-weight: 900;
}

.secure-download-dialog p {
    margin: 7px 0 16px;
    color: #64748b;
    line-height: 1.45;
}

.secure-download-dialog label {
    display: block;
    color: #334155;
    font-size: 0.82rem;
    font-weight: 800;
    margin-bottom: 6px;
}

.secure-download-dialog input[type="password"] {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 12px;
    color: #0f172a;
    background: #fff;
}

.secure-download-feedback {
    margin-top: 10px;
    border-radius: 10px;
    padding: 9px 11px;
    font-size: 0.86rem;
    font-weight: 800;
    line-height: 1.35;
}

.secure-download-feedback.is-error {
    display: block;
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.secure-download-feedback.is-success {
    display: block;
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.secure-download-dialog button[type="submit"] {
    width: 100%;
    margin-top: 14px;
    border: none;
    border-radius: 10px;
    background: #2563eb;
    color: #fff;
    padding: 10px 14px;
    font-weight: 800;
    cursor: pointer;
}

.secure-download-dialog button[type="submit"]:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

body.dark-mode .secure-download-dialog {
    background: #111827;
    border: 1px solid #334155;
}
body.dark-mode .secure-download-dialog h2 {
    color: #e2e8f0;
}
body.dark-mode .secure-download-dialog p,
body.dark-mode .secure-download-dialog label {
    color: #94a3b8;
}
body.dark-mode .secure-download-dialog input[type="password"] {
    background: #0b1220;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .secure-download-feedback.is-error {
    background: #450a0a;
    color: #fecaca;
    border-color: #991b1b;
}
body.dark-mode .secure-download-feedback.is-success {
    background: #052e1a;
    color: #bbf7d0;
    border-color: #166534;
}

@media(max-width:640px) {
    .notif-dropdown {
        right: 12px;
        top: 58px;
        left: auto;
        width: min(360px, calc(100vw - 24px));
        max-width: calc(100vw - 24px);
    }
}
</style>
</head>

<body>
@include('layouts.partials.flash-toast')

<div id="secureDownloadModal" class="secure-download-modal" style="display:none;" aria-hidden="true">
    <div class="secure-download-dialog" role="dialog" aria-modal="true" aria-labelledby="secureDownloadTitle">
        <button type="button" class="secure-download-close" id="secureDownloadClose" aria-label="Close">&times;</button>
        <div class="secure-download-icon"><i class="fa-solid fa-lock"></i></div>
        <h2 id="secureDownloadTitle">Confirm Download</h2>
        <p>Enter your account password before downloading this report.</p>
        <form method="POST" action="{{ route('downloads.authorize') }}" id="secureDownloadForm">
            @csrf
            <input type="hidden" name="target" id="secureDownloadTarget">
            <label for="secureDownloadPassword">Password</label>
            <input type="password" name="download_password" id="secureDownloadPassword" autocomplete="current-password" required>
            <div id="secureDownloadFeedback" class="secure-download-feedback" style="display:none;"></div>
            <button type="submit" id="secureDownloadSubmit">Continue Download</button>
        </form>
    </div>
</div>

@php
    $currentUser = auth()->user();
    $currentUserName = $currentUser?->username ?? $currentUser?->name ?? 'User';
    $currentUserRole = ucwords(str_replace('_', ' ', (string) ($currentUser?->role_key ?? $currentUser?->role ?? 'User')));
    $currentUserAvatar = $currentUser?->profile_photo_url ?? asset('img/default-avatar.png');
@endphp
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
                        $notifTargetUrl = trim((string) ($notif->target_url ?? ''));
                        $notifFacility = null;
                        foreach ([
                            '/\bfor\s+(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i',
                            '/\bat\s+(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i',
                            '/^(?:maintenance|incident)\s*:\s*(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i',
                        ] as $facilityPattern) {
                            if (preg_match($facilityPattern, $notifMessage, $matches)) {
                                $notifFacility = trim((string) ($matches[1] ?? ''));
                                break;
                            }
                        }
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
                        if (in_array($notifType, ['energy_record_alert', 'main_meter_alert', 'submeter_alert'], true)) {
                            $notifType = 'record';
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
                        $notifStoredTitle = trim((string) ($notif->title ?? ''));

                        $isChecklistNotif = $notifType === 'maintenance'
                            && (
                                \Illuminate\Support\Str::contains($notifLower, 'checklist item')
                                || \Illuminate\Support\Str::contains($notifLower, 'checklist')
                                || \Illuminate\Support\Str::contains(strtolower($notifStoredTitle), 'checklist')
                            );

                        $notifDisplayTitle = match ($notifType) {
                            'incident' => 'Incident Alert',
                            'maintenance' => 'Maintenance Alert',
                            'consumption' => 'Consumption Alert',
                            'record' => 'Energy Alert',
                            'contact' => 'Contact Inbox',
                            default => 'System Alert',
                        };
                        if ($notifStoredTitle !== '' && strtolower($notifStoredTitle) !== 'system alert') {
                            $notifDisplayTitle = $notifStoredTitle;
                        }

                        if ($notifTargetUrl === '') {
                            $notifTargetUrl = route('dashboard.index');
                        }
                        if ($notifType === 'incident' || \Illuminate\Support\Str::contains($notifLower, 'incident:')) {
                            $notifTargetUrl = route('energy-incidents.index');
                        } elseif ($isChecklistNotif) {
                            $notifTargetUrl = route('modules.energy-conservation.feature', [
                                'feature' => 'daily-checklist',
                                'month' => now()->format('Y-m'),
                            ]);
                        } elseif ($notifType === 'maintenance' || \Illuminate\Support\Str::contains($notifLower, 'maintenance:')) {
                            if ($notifTargetUrl === '' || $notifTargetUrl === route('dashboard.index')) {
                                $notifTargetUrl = \Illuminate\Support\Str::contains($notifLower, 'completed')
                                    ? route('maintenance.history')
                                    : route('modules.maintenance.index');
                            }
                        } elseif ($notifType === 'contact' || \Illuminate\Support\Str::contains($notifLower, 'contact message')) {
                            $canAccessContactInbox = \App\Support\RoleAccess::in(auth()->user(), ['super_admin', 'admin']);
                            $notifTargetUrl = $canAccessContactInbox
                                ? route('modules.contact-messages.index')
                                : route('dashboard.index');
                        } elseif ($notifType === 'record' || $notifType === 'consumption' || \Illuminate\Support\Str::contains($notifLower, 'alert:') || \Illuminate\Support\Str::contains($notifLower, 'baseline')) {
                            if ($notifTargetUrl === '' || $notifTargetUrl === route('dashboard.index')) {
                                $notifTargetUrl = route('dashboard.index');
                            }
                        }
                    @endphp
                    <a href="{{ $notifTargetUrl }}" class="notif-item notif-sev-{{ $notifSeverity }} {{ $notif->read_at ? 'is-read' : 'is-unread' }}" data-id="{{ $notif->id }}" data-read-url="{{ route('notifications.markRead', $notif) }}" data-level="{{ $notifSeverity }}" data-read="{{ $notif->read_at ? 'read' : 'unread' }}">
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
                        @if($notifFacility)
                            <div class="notif-facility"><i class="fa-solid fa-building"></i> {{ $notifFacility }}</div>
                        @endif
                        <div class="notif-message">{{ $notif->message }}</div>
                        <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                @empty
                    <div id="notifEmpty" style="padding:16px;text-align:center;color:#888;font-size:0.95rem;">No notifications</div>
                @endforelse
                <div id="notifFilterEmpty" class="notif-filter-empty" style="display:none;">No notifications for this filter.</div>
            </div>
            <a class="notif-view-all" href="{{ route('notifications.index') }}">View all notifications <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <button id="darkToggleHeader" class="header-icon-btn has-hover-label" aria-label="Toggle dark mode" data-tooltip="Toggle theme">
            <i id="darkModeIcon" class="fa fa-moon"></i>
        </button>

        @auth
            <div class="header-user user-menu-wrap">
                <button id="userMenuBtn" class="user-menu-btn has-hover-label" data-tooltip="Account menu">
                    <img src="{{ $currentUserAvatar }}" alt="Profile Photo" class="user-avatar" onerror="this.onerror=null;this.src='{{ asset('img/default-avatar.png') }}';">
                    <span class="user-name">{{ $currentUserName }}</span>
                    <i class="fa fa-caret-down user-menu-caret"></i>
                </button>
                <div id="userDropdown" class="user-dropdown">
    <div class="user-dropdown-head">
        <div class="user-dropdown-name">{{ $currentUserName }}</div>
        <div class="user-dropdown-role">{{ $currentUserRole ?: 'Guest' }}</div>
    </div>
    <a href="{{ route('profile.show') }}" class="user-dropdown-link">
        <i class="fa fa-user"></i> My Profile
    </a>
    <a href="{{ route('about.index') }}" class="user-dropdown-link">
        <i class="fa fa-info-circle"></i> About LGU Energy System
    </a>
    <a href="{{ route('faqs.index') }}" class="user-dropdown-link">
        <i class="fa fa-question-circle"></i> FAQs
    </a>
    <a href="{{ route('privacy.index') }}" class="user-dropdown-link">
        <i class="fa fa-shield-alt"></i> Privacy Notice
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="logout-btn">Logout</button>
    </form>
</div>
            </div>
        @endauth
    </div>
</header>

@auth
<div class="sidebar-nav" id="sidebar">
    <div class="sidebar-top">
        <div class="site-logo">
            <img src="{{ $systemLogoUrl }}" alt="Logo" style="border-radius:50%;object-fit:cover;width:60px;height:60px;box-shadow:0 2px 8px rgba(49,46,129,0.10);">
        </div>
        <div class="sidebar-divider"></div>

        @php
            $user = auth()->user();
            $role = strtolower($user?->role ?? '');
            $roleKey = \App\Support\RoleAccess::normalize($user);
            if (!isset($p) || !is_callable($p)) {
                $p = fn ($path = '') => url($path);
            }
            $isEnergyMonitoringMenuActive = request()->routeIs('modules.energy-monitoring.*')
                || request()->routeIs('modules.energy-conservation.*')
                || request()->routeIs('energy.dashboard')
                || request()->routeIs('modules.submeters.*');
            $isReportsMenuActive = request()->is('modules/reports*')
                || request()->routeIs('modules.reports.*')
                || request()->routeIs('reports.*')
                || request()->routeIs('energy-incidents.*');
            $canViewFacilities = \App\Support\RoleAccess::can($user, 'view_facilities');
            $canViewEnergy = \App\Support\RoleAccess::can($user, 'view_energy_monitoring');
            $canViewConservation = \App\Support\RoleAccess::can($user, 'access_energy_conservation');
            $canViewSubmeters = \App\Support\RoleAccess::can($user, 'view_submeter_monitoring');
            $canViewMaintenance = \App\Support\RoleAccess::can($user, 'view_maintenance');
            $canViewReports = \App\Support\RoleAccess::can($user, 'access_reports');
            $canAccessUsers = \App\Support\RoleAccess::can($user, 'access_users');
            $canAccessSettings = \App\Support\RoleAccess::can($user, 'access_settings');
        @endphp

        <ul class="nav-list">
            <li><a href="{{ $p('modules/dashboard/index') }}" class="nav-link{{ request()->is('modules/dashboard/index') ? ' active' : '' }}"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>

            @if($canViewFacilities || $canViewEnergy || $canViewConservation || $canViewSubmeters || $canViewMaintenance)
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">OPERATIONS</li>
                @if($canViewFacilities)
                <li><a href="{{ $p('modules/facilities/index') }}" class="nav-link{{ request()->is('modules/facilities*') ? ' active' : '' }}"><i class="fa-solid fa-building"></i> Facilities</a></li>
                @endif
                @if($canViewEnergy || $canViewConservation || $canViewSubmeters)
                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle{{ $isEnergyMonitoringMenuActive ? ' active' : '' }}">
                        <span><i class="fa-solid fa-bolt"></i> Energy Monitoring</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        @if($canViewEnergy)
                        <li><a href="{{ route('modules.energy-monitoring.index') }}" class="nav-link{{ request()->routeIs('modules.energy-monitoring.*') || request()->routeIs('energy.dashboard') ? ' active' : '' }}"><i class="fa-solid fa-building"></i> Facility Monitoring</a></li>
                        @endif
                        @if($canViewConservation)
                        <li><a href="{{ route('modules.energy-conservation.index') }}" class="nav-link{{ request()->routeIs('modules.energy-conservation.*') ? ' active' : '' }}"><i class="fa-solid fa-leaf"></i> Energy Conservation</a></li>
                        @endif
                        @if($canViewSubmeters)
                        <li><a href="{{ route('modules.submeters.monitoring') }}" class="nav-link{{ request()->routeIs('modules.submeters.*') ? ' active' : '' }}"><i class="fa-solid fa-network-wired"></i> Submeter Monitoring</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                @if($canViewMaintenance)
                <li><a href="{{ $p('modules/maintenance/index') }}" class="nav-link{{ request()->is('modules/maintenance*') ? ' active' : '' }}"><i class="fa-solid fa-wrench"></i> Maintenance</a></li>
                @endif
            @endif

            @if($canViewReports)
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">ANALYTICS</li>
                <li class="nav-item-has-submenu">
                    <a href="#" class="nav-link submenu-toggle{{ $isReportsMenuActive ? ' active' : '' }}">
                        <span><i class="fa-solid fa-chart-bar"></i> Reports</span>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li><a href="{{ $p('modules/reports/energy') }}" class="nav-link{{ (request()->routeIs('modules.reports.energy') || request()->routeIs('reports.energy') || request()->is('modules/reports/energy')) ? ' active' : '' }}"><i class="fa-solid fa-bolt"></i> Energy Report</a></li>
                        <li><a href="{{ $p('modules/reports/efficiency-summary') }}" class="nav-link{{ request()->routeIs('reports.efficiency-summary') ? ' active' : '' }}"><i class="fa-solid fa-chart-line"></i> Efficiency Summary</a></li>
                        <li><a href="{{ route('energy-incidents.index') }}" class="nav-link{{ request()->routeIs('energy-incidents.*') ? ' active' : '' }}"><i class="fa-solid fa-triangle-exclamation"></i> Incidents</a></li>
                    </ul>
                </li>
            @endif

            @if($canAccessUsers || $canAccessSettings)
                <li style="margin: 18px 0 6px 8px; font-size:0.8rem; color:#888; font-weight:600; letter-spacing:1px;">ADMIN</li>
                @if($canAccessUsers)
                <li><a href="{{ $p('modules/users/index') }}" class="nav-link{{ request()->is('modules/users*') ? ' active' : '' }}"><i class="fa-solid fa-users"></i> Users</a></li>
                @if(in_array($roleKey, ['super_admin', 'admin'], true))
                <li><a href="{{ route('modules.contact-messages.index') }}" class="nav-link{{ request()->routeIs('modules.contact-messages.*') ? ' active' : '' }}"><i class="fa-solid fa-envelope"></i> Contact Inbox</a></li>
                @endif
                @endif
                @if($canAccessSettings)
                <li><a href="{{ $p('modules/settings/index') }}" class="nav-link{{ request()->is('modules/settings*') ? ' active' : '' }}"><i class="fa-solid fa-gear"></i> Settings</a></li>
                @endif
            @endif
        </ul>
    </div>

    <div class="user-info">
        Welcome, {{ auth()->user()?->username ?? auth()->user()?->name ?? 'Guest' }}<br>
        <small style="color:#666;font-size:0.8rem;">{{ ucwords(str_replace('_', ' ', (string) ($role ?: auth()->user()?->role_key ?: 'Guest'))) }}</small>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
    </div>
</div>
@endauth

<div class="main-content">
    <div class="main-content-inner">
        @yield('content')
    </div>
</div>

<div id="sessionTimeoutModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center;padding:12px;">
    <div class="session-timeout-dialog" style="width:100%;background:#fff;padding:44px 36px 36px 36px;border-radius:20px;max-width:430px;text-align:center;box-shadow:0 12px 40px rgba(37,99,235,0.15);">
        <div style="font-size:3rem;line-height:1;color:#e11d48;margin-bottom:8px;"><i class="fa fa-lock"></i></div>
        <div class="session-timeout-title" style="font-size:2rem;font-weight:900;color:#e11d48;margin-bottom:10px;letter-spacing:-1px;">Session Ended for Security</div>
        <div class="session-timeout-message" style="color:#334155;font-size:1.15rem;margin-bottom:18px;font-weight:600;">Your session has timed out due to inactivity or a security update.<br>To protect your account, we've signed you out automatically.</div>
        <div style="color:#64748b;font-size:1.01rem;margin-bottom:24px;">Please log in again to continue your work. If you need help, contact your system administrator.</div>
        <a href="/login" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:13px 36px;border-radius:10px;text-decoration:none;display:inline-block;font-weight:800;font-size:1.13rem;box-shadow:0 2px 8px #2563eb22;transition:background 0.18s;">Log In</a>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="/js/echo.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-global-toast]').forEach((toast) => {
        setTimeout(() => {
            toast.classList.add('is-hidden');
        }, 2800);

        setTimeout(() => {
            toast.remove();
        }, 3300);
    });

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
    document.querySelectorAll('.nav-item-has-submenu').forEach((item) => {
        const btn = item.querySelector('.submenu-toggle');
        const menu = item.querySelector('.nav-submenu');
        if (!btn || !menu) return;

        const hasActiveChild = !!menu.querySelector('.nav-link.active');
        if (hasActiveChild || btn.classList.contains('active')) {
            menu.style.display = 'block';
        }

        btn.onclick = e => {
            e.preventDefault();
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';
        };
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
        maintenanceHistory: "{{ route('maintenance.history') }}",
        contactInbox: "{{ route('modules.contact-messages.index') }}",
        dashboard: "{{ route('dashboard.index') }}"
    };
    let activeNotifFilter = 'all';

    const secureDownloadModal = document.getElementById('secureDownloadModal');
    const secureDownloadTarget = document.getElementById('secureDownloadTarget');
    const secureDownloadPassword = document.getElementById('secureDownloadPassword');
    const secureDownloadClose = document.getElementById('secureDownloadClose');
    const secureDownloadForm = document.getElementById('secureDownloadForm');
    const secureDownloadFeedback = document.getElementById('secureDownloadFeedback');
    const secureDownloadSubmit = document.getElementById('secureDownloadSubmit');
    let secureDownloadLockTimer = null;

    const showGlobalToast = (message, type = 'success') => {
        let stack = document.querySelector('.global-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'global-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('aria-atomic', 'true');
            document.body.appendChild(stack);
        }

        const toast = document.createElement('div');
        toast.className = `global-toast global-toast-${type}`;
        toast.dataset.globalToast = '';
        toast.innerHTML = `<i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i><span></span>`;
        toast.querySelector('span').textContent = message;
        stack.appendChild(toast);

        setTimeout(() => toast.classList.add('is-hidden'), 2800);
        setTimeout(() => toast.remove(), 3300);
    };

    const setSecureDownloadFeedback = (message, type = 'error') => {
        if (!secureDownloadFeedback) return;
        secureDownloadFeedback.textContent = message;
        secureDownloadFeedback.classList.remove('is-error', 'is-success');
        secureDownloadFeedback.classList.add(type === 'success' ? 'is-success' : 'is-error');
        secureDownloadFeedback.style.display = 'block';
    };

    const clearSecureDownloadFeedback = () => {
        if (!secureDownloadFeedback) return;
        secureDownloadFeedback.textContent = '';
        secureDownloadFeedback.classList.remove('is-error', 'is-success');
        secureDownloadFeedback.style.display = 'none';
    };

    const applySecureDownloadLock = (seconds) => {
        if (!secureDownloadSubmit || !secureDownloadPassword) return;
        let remaining = parseInt(seconds, 10) || 0;
        if (remaining <= 0) return;

        clearInterval(secureDownloadLockTimer);
        secureDownloadSubmit.disabled = true;
        secureDownloadPassword.disabled = true;

        const tick = () => {
            if (remaining <= 0) {
                clearInterval(secureDownloadLockTimer);
                secureDownloadSubmit.disabled = false;
                secureDownloadPassword.disabled = false;
                secureDownloadSubmit.textContent = 'Continue Download';
                setSecureDownloadFeedback('You can try again now.', 'success');
                return;
            }

            secureDownloadSubmit.textContent = `Try again in ${remaining}s`;
            remaining -= 1;
        };

        tick();
        secureDownloadLockTimer = setInterval(tick, 1000);
    };

    window.requestSecureDownload = (targetUrl) => {
        if (!secureDownloadModal || !secureDownloadTarget) {
            window.location.href = targetUrl;
            return;
        }

        clearInterval(secureDownloadLockTimer);
        clearSecureDownloadFeedback();
        secureDownloadTarget.value = targetUrl;
        if (secureDownloadPassword) {
            secureDownloadPassword.value = '';
            secureDownloadPassword.disabled = false;
        }
        if (secureDownloadSubmit) {
            secureDownloadSubmit.disabled = false;
            secureDownloadSubmit.textContent = 'Continue Download';
        }
        secureDownloadModal.style.display = 'flex';
        secureDownloadModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => secureDownloadPassword?.focus(), 50);
    };

    const closeSecureDownloadModal = () => {
        if (!secureDownloadModal) return;
        clearInterval(secureDownloadLockTimer);
        clearSecureDownloadFeedback();
        if (secureDownloadPassword) {
            secureDownloadPassword.disabled = false;
        }
        if (secureDownloadSubmit) {
            secureDownloadSubmit.disabled = false;
            secureDownloadSubmit.textContent = 'Continue Download';
        }
        secureDownloadModal.style.display = 'none';
        secureDownloadModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    secureDownloadClose?.addEventListener('click', closeSecureDownloadModal);
    secureDownloadModal?.addEventListener('click', (event) => {
        if (event.target === secureDownloadModal) {
            closeSecureDownloadModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && secureDownloadModal?.style.display === 'flex') {
            closeSecureDownloadModal();
        }
    });

    secureDownloadPassword?.addEventListener('input', () => {
        if (!secureDownloadPassword.disabled) {
            clearSecureDownloadFeedback();
        }
    });

    secureDownloadForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!secureDownloadSubmit || !secureDownloadPassword) return;

        clearSecureDownloadFeedback();
        secureDownloadSubmit.disabled = true;
        secureDownloadSubmit.textContent = 'Checking...';

        try {
            const response = await fetch(secureDownloadForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(secureDownloadForm)
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok || !payload.success) {
                const message = payload.message || 'Invalid password. Download was not started.';
                setSecureDownloadFeedback(message, 'error');
                secureDownloadPassword.value = '';
                secureDownloadPassword.focus();

                if (payload.retry_after) {
                    applySecureDownloadLock(payload.retry_after);
                } else {
                    secureDownloadSubmit.disabled = false;
                    secureDownloadSubmit.textContent = 'Continue Download';
                }
                return;
            }

            setSecureDownloadFeedback(payload.message || 'Password confirmed. Download starting...', 'success');
            showGlobalToast(payload.message || 'Password confirmed. Download starting...', 'success');
            secureDownloadSubmit.textContent = 'Starting...';

            setTimeout(() => {
                closeSecureDownloadModal();
                window.location.href = payload.redirect_url;
            }, 650);
        } catch (error) {
            setSecureDownloadFeedback('Unable to verify password right now. Please try again.', 'error');
            secureDownloadSubmit.disabled = false;
            secureDownloadSubmit.textContent = 'Continue Download';
        }
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-secure-download]');
        if (!link) return;

        event.preventDefault();
        window.requestSecureDownload(link.href);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter;
        const secureSubmitter = submitter instanceof HTMLElement && submitter.matches('[data-secure-download-submit]');
        if (!(form instanceof HTMLFormElement) || (!form.matches('form[data-secure-download-form]') && !secureSubmitter)) {
            return;
        }

        event.preventDefault();
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (key === '_token' || value === '') continue;
            params.append(key, value);
        }
        if (secureSubmitter && submitter.getAttribute('name')) {
            params.set(submitter.getAttribute('name'), submitter.getAttribute('value') || '');
        }

        const action = form.getAttribute('action') || window.location.href;
        const target = new URL(action, window.location.origin);
        target.search = params.toString();
        window.requestSecureDownload(target.toString());
    });

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
        if (t === 'maintenance' || text.includes('maintenance:')) {
            return text.includes('completed') ? notifRoutes.maintenanceHistory : notifRoutes.maintenance;
        }
        if (t === 'contact' || text.includes('contact message')) return notifRoutes.contactInbox;
        if (['energy_record_alert', 'main_meter_alert', 'submeter_alert', 'record', 'consumption'].includes(t) || text.includes('alert:') || text.includes('baseline')) return notifRoutes.dashboard;
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
        if (t === 'contact' || text.includes('contact message')) return 'Contact Inbox';
        if (t === 'consumption' || text.includes('baseline')) return 'Consumption Alert';
        if (['energy_record_alert', 'main_meter_alert', 'submeter_alert', 'record'].includes(t) || text.includes('alert:')) return 'Energy Alert';
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
        const facility = extractNotifFacility(message);
        const unreadDot = unread ? '<span class=\"notif-unread-dot\"></span>' : '';
        const stateClass = unread ? 'is-unread' : 'is-read';
        const facilityHtml = facility
            ? `<div class=\"notif-facility\"><i class=\"fa-solid fa-building\"></i> ${escapeHtml(facility)}</div>`
            : '';
        return `<a href=\"${escapeHtml(targetUrl)}\" class=\"notif-item notif-sev-${severity} ${stateClass}\" data-level=\"${severity}\" data-read=\"${unread ? 'unread' : 'read'}\">
                    <div class=\"notif-item-head\">
                        <span class=\"notif-icon\"><i class=\"fa-solid ${severityIcon(severity)}\"></i></span>
                        <div class=\"notif-head-copy\">
                            <strong class=\"notif-title\">${escapeHtml(title)}</strong>
                            <span class=\"notif-level-badge\">${escapeHtml(severityLabel(severity))}</span>
                        </div>
                        ${unreadDot}
                    </div>
                    ${facilityHtml}
                    <div class=\"notif-message\">${escapeHtml(message)}</div>
                    <div class=\"notif-time\">${escapeHtml(timeLabel)}</div>
                </a>`;
    };

    const extractNotifFacility = (message) => {
        const text = String(message || '');
        const patterns = [
            /\bfor\s+(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i,
            /\bat\s+(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i,
            /^(?:maintenance|incident)\s*:\s*(.+?)\s+\([A-Za-z]{3,9}\s+\d{4}\)/i,
        ];
        for (const pattern of patterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                return match[1].trim();
            }
        }
        return '';
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

    const markNotifItemAsReadUi = (item) => {
        if (!item || item.dataset.read === 'read') return;
        item.classList.remove('is-unread');
        item.classList.add('is-read');
        item.dataset.read = 'read';
        const dot = item.querySelector('.notif-unread-dot');
        if (dot) dot.remove();

        if (notifCountEl) {
            const current = parseInt(notifCountEl.innerText, 10) || 0;
            const next = Math.max(0, current - 1);
            notifCountEl.innerText = String(next);
            notifCountEl.style.display = next > 0 ? 'inline-block' : 'none';
        }

        updateMarkAllVisibility();
        applyNotifFilter();
    };

    if (notifList) {
        notifList.addEventListener('click', async (e) => {
            const item = e.target.closest('.notif-item');
            if (!item) return;

            const href = item.getAttribute('href') || '#';
            const readUrl = item.dataset.readUrl || '';
            const alreadyRead = (item.dataset.read || '') === 'read';
            const isPrimaryPlainClick = e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey;

            if (!isPrimaryPlainClick || alreadyRead || !readUrl) {
                return;
            }

            e.preventDefault();

            try {
                const res = await fetch(readUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    markNotifItemAsReadUi(item);
                }
            } catch (err) {
                console.error('Failed to mark notification as read', err);
            } finally {
                window.location.href = href;
            }
        });
    }

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
    const timeoutMinutes = {{ max(1, (int) config('session.lifetime', 60)) }};
    const timeoutMs = timeoutMinutes * 60 * 1000;
    const keepAliveIntervalMs = 60 * 1000;
    const keepAliveUrl = @json(route('session.keep-alive'));
    const logoutUrl = @json(route('logout'));
    const expiredLoginUrl = @json(route('login')) + '?session=expired';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let sessionTimer = null;
    let lastKeepAliveAt = Date.now();
    let lastActivityHandledAt = 0;
    let keepAlivePending = false;
    let logoutStarted = false;

    const redirectToExpiredLogin = () => {
        window.location.replace(expiredLoginUrl);
    };

    const endIdleSession = async () => {
        if (logoutStarted) return;
        logoutStarted = true;
        clearTimeout(sessionTimer);

        try {
            await fetch(logoutUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason: 'idle' }),
            });
        } catch (error) {
            // The server session may already be expired or the network may be unavailable.
        } finally {
            redirectToExpiredLogin();
        }
    };

    const scheduleIdleLogout = () => {
        if (logoutStarted) return;
        clearTimeout(sessionTimer);
        sessionTimer = setTimeout(endIdleSession, timeoutMs);
    };

    const renewServerSession = async () => {
        if (keepAlivePending || logoutStarted || !csrfToken) return;
        keepAlivePending = true;
        lastKeepAliveAt = Date.now();

        try {
            const response = await fetch(keepAliveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            });

            if (response.status === 401 || response.status === 419 || response.redirected) {
                logoutStarted = true;
                redirectToExpiredLogin();
            }
        } catch (error) {
            // Do not interrupt active work for a temporary network failure.
        } finally {
            keepAlivePending = false;
        }
    };

    const recordUserActivity = () => {
        if (logoutStarted) return;
        const now = Date.now();
        if (now - lastActivityHandledAt < 1000) return;
        lastActivityHandledAt = now;
        scheduleIdleLogout();

        if (now - lastKeepAliveAt >= keepAliveIntervalMs) {
            renewServerSession();
        }
    };

    scheduleIdleLogout();

    ['click', 'mousemove', 'keydown', 'scroll', 'wheel', 'touchstart', 'touchmove'].forEach((eventName) => {
        window.addEventListener(eventName, recordUserActivity, { passive: true });
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
