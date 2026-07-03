@extends('layouts.qc-admin')

@section('content')
@php
    $filterLabels = ['all' => 'All', 'unread' => 'Unread', 'critical' => 'High and above'];
@endphp

<style>
    .notifications-page { max-width:1500px; margin:0 auto; padding:22px 18px 44px; }
    .notifications-page-head { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:28px; padding:0 0 22px; border-bottom:1px solid #e2e8f0; }
    .notifications-page h1 { margin:0; color:#172b4d; font-size:2rem; font-weight:900; letter-spacing:-.04em; }
    .notifications-page h1 span { color:#2d62f3; }
    .notifications-page p { margin:6px 0 0; color:#64748b; }
    .notification-period { color:#1d4ed8; background:#dbeafe; border-radius:999px; padding:10px 14px; font-size:.8rem; font-weight:900; white-space:nowrap; }
    .notification-report-card { padding:36px 38px; border:1px solid #e2e8f0; border-radius:22px; background:#fff; box-shadow:0 12px 34px rgba(15,23,42,.06); }
    .notification-summary-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; margin:0 0 28px; }
    .notification-summary-card { min-height:128px; padding:24px 26px; border:1px solid #e2e8f0; border-radius:17px; background:#f8fafc; }
    .notification-summary-card.total { background:#eef4ff; color:#2455d6; }.notification-summary-card.unread { background:#fff8df; color:#a16207; }.notification-summary-card.high { background:#fff0f1; color:#e11d48; }.notification-summary-card.read { background:#ecfdf3; color:#15803d; }
    .notification-summary-label { display:flex; align-items:center; gap:8px; font-size:.84rem; font-weight:900; text-transform:uppercase; }.notification-summary-value { display:block; margin-top:22px; color:#172b4d; font-size:2.45rem; font-weight:900; line-height:1; }
    .notification-filters { display:flex; flex-wrap:wrap; gap:10px; margin:0 0 20px; padding:18px 20px; border:1px solid #dbe4f0; border-radius:16px; background:#f8fafc; }
    .notification-filter { border:1px solid #cbd5e1; border-radius:999px; padding:7px 13px; color:#334155; background:#fff; font-size:.82rem; font-weight:800; text-decoration:none; }
    .notification-filter:hover, .notification-filter.active { background:#dbeafe; border-color:#93c5fd; color:#1d4ed8; }
    .notification-list { overflow:hidden; border:1px solid #dbe4f0; border-radius:16px; background:#fff; }
    .notification-row { display:flex; align-items:flex-start; gap:14px; padding:18px 20px; border-bottom:1px solid #eef2f7; color:inherit; text-decoration:none; transition:background .16s ease, transform .16s ease; }
    .notification-row:last-child { border-bottom:0; }
    .notification-row.unread { background:#f0f7ff; border-left:4px solid #2563eb; padding-left:14px; }
    .notification-row:hover { background:#f8fafc; }
    .notification-icon { flex:0 0 40px; height:40px; border-radius:13px; display:grid; place-items:center; background:#dbeafe; color:#1d4ed8; }
    .notification-icon.critical { background:#fee2e2; color:#dc2626; }
    .notification-icon.high { background:#ffedd5; color:#c2410c; }
    .notification-copy { min-width:0; flex:1; }
    .notification-title-row { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
    .notification-title { color:#1e293b; font-weight:800; font-size:1rem; line-height:1.35; }
    .notification-badge { display:inline-flex; align-items:center; padding:4px 8px; border-radius:999px; background:#e2e8f0; color:#475569; font-size:.64rem; font-weight:900; letter-spacing:.04em; line-height:1; text-transform:uppercase; }
    .notification-badge.critical { background:#fee2e2; color:#dc2626; }
    .notification-badge.high { background:#ffedd5; color:#c2410c; }
    .notification-message { display:block; margin-top:7px; color:#475569; font-size:.93rem; line-height:1.5; }
    .notification-time { display:block; margin-top:9px; color:#94a3b8; font-size:.8rem; font-weight:600; }
    .notification-empty { padding:50px 22px; color:#64748b; text-align:center; }
    .notification-pagination { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:18px; color:#64748b; font-size:.84rem; }
    .notification-page-links { display:flex; align-items:center; flex-wrap:wrap; gap:6px; }
    .notification-page-link { min-width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; padding:0 10px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; color:#334155; font-weight:800; text-decoration:none; }
    .notification-page-link:hover { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
    .notification-page-link.active { border-color:#2563eb; background:#2563eb; color:#fff; }
    .notification-page-link.disabled { cursor:not-allowed; opacity:.45; }
    body.dark-mode .notifications-page-head { border-color:#334155; }
    body.dark-mode .notification-report-card { background:#111827; border-color:#334155; }
    body.dark-mode .notification-list, body.dark-mode .notification-filters { background:#111827; border-color:#334155; }
    body.dark-mode .notification-page-link { background:#111827; border-color:#475569; color:#cbd5e1; }
    body.dark-mode .notification-page-link.active { background:#2563eb; border-color:#2563eb; color:#fff; }
    body.dark-mode .notification-row { border-color:#334155; }
    body.dark-mode .notification-row:hover { background:#1f2937; }
    body.dark-mode .notification-row.unread { background:#172554; }
    body.dark-mode .notification-title { color:#e2e8f0; }
    body.dark-mode .notification-message { color:#cbd5e1; }
    @media (max-width:900px) { .notification-summary-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:600px) { .notifications-page { padding:14px 10px 30px; } .notifications-page-head { flex-direction:column; padding:2px 2px 16px; margin-bottom:20px; } .notifications-page h1 { font-size:1.55rem; } .notification-report-card { padding:18px; border-radius:17px; } .notification-summary-grid { grid-template-columns:1fr; gap:11px; margin-bottom:20px; }.notification-summary-card { min-height:auto; padding:18px; }.notification-summary-value { margin-top:14px; font-size:2rem; } .notification-period { white-space:normal; } .notification-row { padding:15px; gap:11px; } .notification-icon { flex-basis:36px; height:36px; border-radius:11px; } .notification-title { font-size:.94rem; } .notification-message { font-size:.88rem; } .notification-pagination { align-items:flex-start; flex-direction:column; } }
</style>

<main class="notifications-page">
    <section class="notification-report-card" aria-label="Notification report">
        <div class="notifications-page-head">
            <div>
                <h1><i class="fa-solid fa-bell"></i> All <span>Notifications</span></h1>
                <p>Your notifications for the past one month only.</p>
            </div>
            <span class="notification-period"><i class="fa-regular fa-calendar"></i> Last 1 month</span>
        </div>

        <div class="notification-summary-grid">
            <article class="notification-summary-card total"><span class="notification-summary-label"><i class="fa-solid fa-bell"></i> Total alerts</span><strong class="notification-summary-value">{{ $notificationSummary['total'] }}</strong></article>
            <article class="notification-summary-card unread"><span class="notification-summary-label"><i class="fa-solid fa-envelope"></i> Unread</span><strong class="notification-summary-value">{{ $notificationSummary['unread'] }}</strong></article>
            <article class="notification-summary-card high"><span class="notification-summary-label"><i class="fa-solid fa-triangle-exclamation"></i> High and above</span><strong class="notification-summary-value">{{ $notificationSummary['high'] }}</strong></article>
            <article class="notification-summary-card read"><span class="notification-summary-label"><i class="fa-solid fa-circle-check"></i> Read</span><strong class="notification-summary-value">{{ $notificationSummary['read'] }}</strong></article>
        </div>

        <nav class="notification-filters" aria-label="Notification filters">
            @foreach($filterLabels as $key => $label)
                <a href="{{ route('notifications.index', ['filter' => $key]) }}" class="notification-filter {{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="notification-list">
        @forelse($notifications as $notification)
            @php
                $text = strtolower((string) $notification->message);
                $level = str_contains($text, 'critical') ? 'critical' : ((str_contains($text, 'very high') || str_contains($text, 'high')) ? 'high' : 'info');
                $label = $level === 'critical' ? 'Critical' : ($level === 'high' ? 'High' : 'Info');
                $icon = $level === 'critical' ? 'fa-circle-exclamation' : ($level === 'high' ? 'fa-triangle-exclamation' : 'fa-bell');
            @endphp
            <a href="{{ route('dashboard.index') }}" class="notification-row {{ $notification->read_at ? '' : 'unread' }}" data-read-url="{{ route('notifications.markRead', $notification) }}">
                <span class="notification-icon {{ $level }}"><i class="fa-solid {{ $icon }}"></i></span>
                <span class="notification-copy">
                    <span class="notification-title-row">
                        <span class="notification-title">{{ $notification->title ?: 'System Alert' }}</span>
                        <span class="notification-badge {{ $level }}">{{ $label }}</span>
                    </span>
                    <span class="notification-message">{{ $notification->message }}</span>
                    <span class="notification-time"><i class="fa-regular fa-clock"></i> {{ $notification->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="notification-empty"><i class="fa-regular fa-bell-slash"></i><br><br>No notifications found for the past month.</div>
        @endforelse
        </div>

        @if($notifications->hasPages())
            <nav class="notification-pagination" aria-label="Notification pages">
                <span>Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} results</span>
                <span class="notification-page-links">
                    @if($notifications->onFirstPage())
                        <span class="notification-page-link disabled"><i class="fa-solid fa-chevron-left"></i></span>
                    @else
                        <a class="notification-page-link" href="{{ $notifications->previousPageUrl() }}" aria-label="Previous page"><i class="fa-solid fa-chevron-left"></i></a>
                    @endif

                    @foreach($notifications->getUrlRange(max(1, $notifications->currentPage() - 1), min($notifications->lastPage(), $notifications->currentPage() + 1)) as $page => $url)
                        <a class="notification-page-link {{ $page === $notifications->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
                    @endforeach

                    @if($notifications->hasMorePages())
                        <a class="notification-page-link" href="{{ $notifications->nextPageUrl() }}" aria-label="Next page"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <span class="notification-page-link disabled"><i class="fa-solid fa-chevron-right"></i></span>
                    @endif
                </span>
            </nav>
        @endif
    </section>
</main>

<script>
document.querySelectorAll('.notification-row[data-read-url]').forEach((row) => {
    row.addEventListener('click', () => {
        if (!row.classList.contains('unread')) return;
        fetch(row.dataset.readUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            keepalive: true,
        });
    });
});
</script>
@endsection
