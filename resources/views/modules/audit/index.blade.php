@extends('layouts.qc-admin')
@section('title', 'Audit Logs')

@section('content')
@php
    $scope = $filters['scope'] ?? 'essential';
    $filterKeys = ['q', 'user_id', 'module', 'action', 'method', 'date_from', 'date_to'];
    $activeFilterCount = collect($filterKeys)->filter(fn ($key) => filled($filters[$key] ?? null))->count();
    $quickQuery = request()->except(['date_from', 'date_to', 'page']);
@endphp
<style>
    .audit-page{width:100%}.audit-shell{display:grid;gap:18px;padding:25px;border:1px solid #dbe5f1;border-radius:22px;background:radial-gradient(circle at 92% 0,rgba(79,70,229,.08),transparent 26%),linear-gradient(145deg,#f8fbff,#f1f5f9);box-shadow:0 14px 36px rgba(15,23,42,.08)}
    .audit-header{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}.audit-heading{display:flex;gap:14px;align-items:flex-start}.audit-title-icon{flex:0 0 48px;height:48px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(145deg,#4f46e5,#2563eb);color:#fff;font-size:1.15rem;box-shadow:0 8px 18px rgba(79,70,229,.22)}.audit-header h1{margin:0;color:#0f172a;font-size:1.75rem}.audit-sub{max-width:720px;margin:6px 0 0;color:#64748b;font-size:.87rem;line-height:1.5}.audit-scope{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid #c7d2fe;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:.7rem;font-weight:900;white-space:nowrap}
    .audit-flow{display:flex;align-items:center;gap:10px;padding:11px 13px;border:1px solid #a7f3d0;border-radius:13px;background:#ecfdf5;color:#065f46;font-size:.74rem;line-height:1.45}.audit-flow.disabled{border-color:#fed7aa;background:#fff7ed;color:#9a3412}.audit-flow strong{font-weight:950}.flow-separator{color:#94a3b8}.audit-flow a{margin-left:auto;color:inherit;font-weight:900;white-space:nowrap}
    .audit-metrics{display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:11px}.audit-metric{position:relative;min-height:96px;padding:16px 15px 14px 60px;border:1px solid #dbe5f1;border-radius:16px;background:#fff}.audit-metric-icon{position:absolute;left:14px;top:15px;width:35px;height:35px;display:grid;place-items:center;border-radius:11px;background:#eef2ff;color:#4f46e5}.audit-metric.today .audit-metric-icon{background:#eff6ff;color:#2563eb}.audit-metric.users .audit-metric-icon{background:#ecfdf5;color:#059669}.audit-metric.risk .audit-metric-icon{background:#fff1f2;color:#e11d48}.audit-metric-label{color:#64748b;font-size:.67rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em}.audit-metric-value{display:block;margin-top:5px;color:#0f172a;font-size:1.5rem;font-weight:950}.audit-metric-note{display:block;margin-top:2px;color:#94a3b8;font-size:.63rem}
    .audit-panel{padding:15px;border:1px solid #dbe5f1;border-radius:16px;background:#fff}.audit-panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:13px}.audit-panel-head h2{margin:0;color:#0f172a;font-size:.82rem;text-transform:uppercase;letter-spacing:.05em}.filter-count{padding:4px 8px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.64rem;font-weight:900}.quick-ranges{display:flex;gap:6px;flex-wrap:wrap}.quick-range{padding:6px 9px;border:1px solid #dbe5f1;border-radius:9px;background:#fff;color:#475569;text-decoration:none;font-size:.66rem;font-weight:850}.quick-range:hover{border-color:#93c5fd;background:#eff6ff;color:#1d4ed8}
    .audit-filters{display:grid;grid-template-columns:2fr repeat(3,minmax(145px,1fr));gap:10px}.audit-field{display:grid;gap:6px}.audit-field label{color:#475569;font-size:.66rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em}.audit-field input,.audit-field select{width:100%;min-height:41px;padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#1e293b;font-size:.82rem}.audit-filter-actions{grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px}.audit-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:39px;padding:8px 13px;border:1px solid transparent;border-radius:10px;font-size:.75rem;font-weight:900;text-decoration:none;cursor:pointer}.audit-btn.primary{background:linear-gradient(90deg,#4f46e5,#2563eb);color:#fff;box-shadow:0 5px 12px rgba(79,70,229,.18)}.audit-btn.muted{border-color:#cbd5e1;background:#fff;color:#334155}
    .audit-table-wrap{overflow:auto;border:1px solid #dbe5f1;border-radius:16px;background:#fff}.audit-table{width:100%;min-width:1180px;table-layout:fixed;border-collapse:collapse}.audit-table th,.audit-table td{padding:14px 16px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top}.audit-table th{position:sticky;top:0;background:#f8fafc;color:#475569;font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;z-index:1}.audit-table td{color:#334155;font-size:.76rem}.audit-table tbody tr:hover{background:#f8fbff}.audit-time{white-space:nowrap}.audit-time strong,.audit-actor strong,.audit-event strong{color:#0f172a}.audit-muted{display:block;margin-top:3px;color:#64748b;font-size:.65rem;line-height:1.4}.audit-actor-wrap{display:flex;align-items:flex-start;gap:10px}.audit-actor-copy{min-width:0}.audit-actor-name{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.audit-avatar{flex:0 0 36px;width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:#eff6ff;color:#2563eb;font-size:.7rem;font-weight:950}.event-line{display:flex;align-items:flex-start;gap:8px}.event-line strong{padding-top:4px;line-height:1.35}.event-icon{flex:0 0 29px;width:29px;height:29px;display:grid;place-items:center;border-radius:9px;background:#eef2ff;color:#4f46e5}.event-icon.danger{background:#fff1f2;color:#e11d48}.event-icon.access{background:#ecfeff;color:#0891b2}.event-icon.change{background:#fff7ed;color:#ea580c}.audit-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;font-size:.61rem;font-weight:900;text-transform:uppercase}.audit-chip.get{background:#f1f5f9;color:#475569}.audit-chip.post,.audit-chip.put,.audit-chip.patch{background:#eff6ff;color:#1d4ed8}.audit-chip.delete{background:#fff1f2;color:#be123c}.module-chip{display:inline-flex;margin:7px 0 0 37px;padding:3px 7px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.6rem;font-weight:850}.audit-path{display:block;max-width:100%;margin-top:7px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#334155;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.68rem}.audit-description{line-height:1.5;overflow-wrap:anywhere}.audit-details{margin-top:7px}.audit-details summary{display:inline-flex;align-items:center;gap:5px;color:#2563eb;font-size:.64rem;font-weight:900;white-space:nowrap;cursor:pointer;list-style:none}.audit-details summary::-webkit-details-marker{display:none}.detail-grid{position:absolute;right:28px;width:min(440px,calc(100vw - 80px));z-index:5;display:grid;gap:6px;margin-top:8px;padding:10px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#475569;font-size:.65rem;box-shadow:0 12px 28px rgba(15,23,42,.16)}.detail-grid code{white-space:pre-wrap;word-break:break-word;color:#334155}.metadata-block{max-height:180px;overflow:auto;margin:0;padding:8px;border-radius:8px;background:#0f172a;color:#cbd5e1;font-size:.61rem;line-height:1.4}.empty-cell{padding:36px!important;text-align:center;color:#64748b!important}.audit-empty-icon{display:block;margin-bottom:8px;color:#94a3b8;font-size:1.5rem}
    .audit-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.audit-pagination-meta{color:#64748b;font-size:.76rem;font-weight:700}.audit-pagination nav{margin-left:auto}.audit-pagination ul{margin:0;padding:0;list-style:none}.audit-pagination .pagination{display:flex;gap:5px;flex-wrap:wrap}.audit-pagination .page-link{min-width:34px;min-height:34px;display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#334155;text-decoration:none;font-size:.75rem;font-weight:800}.audit-pagination .active .page-link{border-color:#4f46e5;background:#4f46e5;color:#fff}.audit-pagination .disabled .page-link{color:#94a3b8;background:#f8fafc}
    body.dark-mode .audit-shell{background:#0f172a;border-color:#334155}
    body.dark-mode .audit-header h1,
    body.dark-mode .audit-metric-value,
    body.dark-mode .audit-panel-head h2,
    body.dark-mode .audit-time strong,
    body.dark-mode .audit-actor strong,
    body.dark-mode .audit-event strong{color:#f8fafc}
    body.dark-mode .audit-sub,
    body.dark-mode .audit-muted,
    body.dark-mode .audit-pagination-meta{color:#94a3b8}
    body.dark-mode .audit-metric-label{color:#94a3b8}
    body.dark-mode .audit-metric-note{color:#64748b}
    body.dark-mode .audit-scope{background:#172554!important;border-color:#1e40af!important;color:#bfdbfe!important}
    body.dark-mode .audit-flow{background:#052e16!important;border-color:#166534!important;color:#86efac!important}
    body.dark-mode .audit-flow.disabled{background:#451a03!important;border-color:#9a3412!important;color:#fdba74!important}
    body.dark-mode .audit-flow a{color:#5eead4!important}
    body.dark-mode .audit-metric,
    body.dark-mode .audit-panel,
    body.dark-mode .audit-table-wrap{background:#111827;border-color:#334155}
    body.dark-mode .audit-metric-icon{background:#1e3a8a!important;color:#93c5fd!important;border:1px solid #3b82f6!important}
    body.dark-mode .audit-metric.today .audit-metric-icon{background:#172554!important;color:#60a5fa!important;border:1px solid #2563eb!important}
    body.dark-mode .audit-metric.users .audit-metric-icon{background:#052e16!important;color:#86efac!important;border:1px solid #166534!important}
    body.dark-mode .audit-metric.risk .audit-metric-icon{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c!important}
    body.dark-mode .filter-count{background:#1e3a8a!important;color:#bfdbfe!important;border:1px solid #3b82f6!important}
    body.dark-mode .quick-range{background:#0f172a;border-color:#475569;color:#cbd5e1}
    body.dark-mode .quick-range:hover{border-color:#3b82f6;background:#1e3a8a;color:#eff6ff}
    body.dark-mode .audit-field label{color:#cbd5e1}
    body.dark-mode .audit-field input,
    body.dark-mode .audit-field select,
    body.dark-mode .audit-btn.muted{background:#0f172a;border-color:#475569;color:#e2e8f0}
    body.dark-mode .audit-btn.muted:hover{background:#1e293b;border-color:#3b82f6;color:#eff6ff}
    body.dark-mode .audit-table th{background:#0f172a;color:#94a3b8;border-color:#334155}
    body.dark-mode .audit-table td{color:#cbd5e1;border-color:#1f2937}
    body.dark-mode .audit-table tbody tr:hover{background:#172033}
    body.dark-mode .audit-avatar{background:#1e3a8a!important;color:#bfdbfe!important;border:1px solid #3b82f6!important}
    body.dark-mode .event-icon{background:#1e293b!important;color:#94a3b8!important;border:1px solid #334155!important}
    body.dark-mode .event-icon.danger{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c!important}
    body.dark-mode .event-icon.access{background:#042f2e!important;color:#5eead4!important;border:1px solid #0d9488!important}
    body.dark-mode .event-icon.change{background:#451a03!important;color:#fdba74!important;border:1px solid #9a3412!important}
    body.dark-mode .module-chip{background:#1e293b!important;color:#94a3b8!important;border:1px solid #334155!important}
    body.dark-mode .audit-chip.get{background:#1e293b!important;color:#cbd5e1!important;border:1px solid #334155!important}
    body.dark-mode .audit-chip.post,
    body.dark-mode .audit-chip.put,
    body.dark-mode .audit-chip.patch{background:#172554!important;color:#bfdbfe!important;border:1px solid #1e40af!important}
    body.dark-mode .audit-chip.delete{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c!important}
    body.dark-mode .audit-path{color:#cbd5e1}
    body.dark-mode .audit-details summary{color:#60a5fa!important}
    body.dark-mode .audit-details summary:hover{color:#93c5fd!important}
    body.dark-mode .detail-grid{background:#0f172a;border-color:#334155;color:#94a3b8}
    body.dark-mode .detail-grid code{color:#cbd5e1}
    body.dark-mode .audit-pagination .page-link{background:#111827;border-color:#334155;color:#cbd5e1}
    body.dark-mode .audit-pagination .page-link:hover{background:#1e293b;color:#ffffff}
    body.dark-mode .audit-pagination .active .page-link{background:#2563eb;border-color:#3b82f6;color:#ffffff}
    body.dark-mode .audit-pagination .disabled .page-link{color:#64748b;background:#0f172a;border-color:#1e293b}
    @media(max-width:1100px){.audit-metrics{grid-template-columns:1fr 1fr}.audit-filters{grid-template-columns:1fr 1fr}}@media(max-width:700px){.audit-shell{padding:16px}.audit-header{display:grid}.audit-heading{display:grid}.audit-scope{width:max-content}.audit-metrics,.audit-filters{grid-template-columns:1fr}.audit-panel-head{align-items:flex-start;display:grid}.audit-filter-actions{justify-content:stretch}.audit-btn{flex:1}.audit-pagination{align-items:stretch;display:grid}.audit-pagination nav{margin-left:0}}
    .audit-table td:last-child{position:relative}
</style>

<section class="audit-page">
    <div class="audit-shell">
        <header class="audit-header">
            <div class="audit-heading">
                <span class="audit-title-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <div>
                    <h1>Audit Trail</h1>
                    <p class="audit-sub">Review access activity and state-changing actions across the Energy Management System. Essential mode removes routine page views and notification noise.</p>
                </div>
            </div>
            <span class="audit-scope"><i class="fa-solid fa-filter"></i> {{ $scope === 'essential' ? 'Essential events' : 'All stored events' }}</span>
        </header>

        <div class="audit-flow {{ ($auditEnabled ?? false) ? '' : 'disabled' }}">
            <i class="fa-solid {{ ($auditEnabled ?? false) ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
            <span>
                <strong>{{ ($auditEnabled ?? false) ? 'Recording active' : 'Recording disabled' }}</strong>
                <span class="flow-separator">•</span>
                Successful login/logout and state-changing actions are recorded
                <span class="flow-separator">•</span>
                Records are retained for {{ $retentionMonths ?? 3 }} month(s)
            </span>
            @if(\App\Support\RoleAccess::can($user, 'access_settings'))
                <a href="{{ route('modules.settings.index') }}">Manage settings <i class="fa-solid fa-arrow-right"></i></a>
            @endif
        </div>

        <div class="audit-metrics">
            <article class="audit-metric"><span class="audit-metric-icon"><i class="fa-solid fa-database"></i></span><span class="audit-metric-label">{{ $scope === 'essential' ? 'Essential Events' : 'Stored Events' }}</span><strong class="audit-metric-value">{{ number_format($totalLogs ?? 0) }}</strong><small class="audit-metric-note">Current scope</small></article>
            <article class="audit-metric today"><span class="audit-metric-icon"><i class="fa-solid fa-calendar-day"></i></span><span class="audit-metric-label">Events Today</span><strong class="audit-metric-value">{{ number_format($todayLogs ?? 0) }}</strong><small class="audit-metric-note">{{ $lastEventAt ? 'Latest '.\Carbon\Carbon::parse($lastEventAt)->diffForHumans() : 'No activity yet' }}</small></article>
            <article class="audit-metric users"><span class="audit-metric-icon"><i class="fa-solid fa-users"></i></span><span class="audit-metric-label">Active Users</span><strong class="audit-metric-value">{{ number_format($activeUsers ?? 0) }}</strong><small class="audit-metric-note">Last 30 days</small></article>
            <article class="audit-metric risk"><span class="audit-metric-icon"><i class="fa-solid fa-user-shield"></i></span><span class="audit-metric-label">Sensitive Changes</span><strong class="audit-metric-value">{{ number_format($destructiveActions ?? 0) }}</strong><small class="audit-metric-note">Delete, archive, status · 30 days</small></article>
        </div>

        <div class="audit-panel">
            <div class="audit-panel-head">
                <div><h2>Search & Filters @if($activeFilterCount > 0)<span class="filter-count">{{ $activeFilterCount }} active</span>@endif</h2></div>
                <div class="quick-ranges">
                    <a class="quick-range" href="{{ route('modules.audit.index', array_merge($quickQuery, ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()])) }}">Today</a>
                    <a class="quick-range" href="{{ route('modules.audit.index', array_merge($quickQuery, ['date_from' => now()->subDays(6)->toDateString(), 'date_to' => now()->toDateString()])) }}">7 Days</a>
                    <a class="quick-range" href="{{ route('modules.audit.index', array_merge($quickQuery, ['date_from' => now()->subDays(29)->toDateString(), 'date_to' => now()->toDateString()])) }}">30 Days</a>
                </div>
            </div>

            <form method="GET" action="{{ route('modules.audit.index') }}" class="audit-filters">
                <div class="audit-field"><label for="q">Search</label><input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="User, action, route, path, IP..."></div>
                <div class="audit-field"><label for="user_id">Actor</label><select id="user_id" name="user_id"><option value="">All Users</option>@foreach($userOptions ?? [] as $u)@php $displayName = $u->full_name ?: ($u->name ?: $u->username); @endphp<option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') === (string) $u->id)>{{ $displayName }} (#{{ $u->id }})</option>@endforeach</select></div>
                <div class="audit-field"><label for="module">Module</label><select id="module" name="module"><option value="">All Modules</option>@foreach($moduleOptions ?? [] as $module)<option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>@endforeach</select></div>
                <div class="audit-field"><label for="scope">Event Scope</label><select id="scope" name="scope"><option value="essential" @selected($scope === 'essential')>Essential Only</option><option value="all" @selected($scope === 'all')>All Events</option></select></div>
                <div class="audit-field"><label for="action">Action</label><select id="action" name="action"><option value="">All Actions</option>@foreach($actionOptions ?? [] as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>@endforeach</select></div>
                <div class="audit-field"><label for="method">HTTP Method</label><select id="method" name="method"><option value="">All Methods</option>@foreach($methodOptions ?? [] as $method)<option value="{{ $method }}" @selected(strtoupper((string) ($filters['method'] ?? '')) === strtoupper($method))>{{ $method }}</option>@endforeach</select></div>
                <div class="audit-field"><label for="date_from">From</label><input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="audit-field"><label for="date_to">To</label><input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="audit-field"><label for="per_page">Rows Per Page</label><select id="per_page" name="per_page">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }}</option>@endforeach</select></div>
                <div class="audit-filter-actions"><button type="submit" class="audit-btn primary"><i class="fa-solid fa-filter"></i> Apply Filters</button><a href="{{ route('modules.audit.index') }}" class="audit-btn muted"><i class="fa-solid fa-rotate-left"></i> Reset</a></div>
            </form>
        </div>

        <div class="audit-table-wrap">
            <table class="audit-table">
                <colgroup><col style="width:14%"><col style="width:18%"><col style="width:22%"><col style="width:17%"><col style="width:17%"><col style="width:12%"></colgroup>
                <thead><tr><th>Date & Time</th><th>Actor</th><th>Event</th><th>Request</th><th>Description</th><th>Network</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    @php
                        $actor = $log->user;
                        $name = $actor?->full_name ?: ($actor?->name ?: ($actor?->username ?: 'System'));
                        $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'S';
                        $method = strtoupper((string) ($log->method ?: 'GET'));
                        $action = strtolower((string) $log->action);
                        $isAccess = str_starts_with($action, 'auth.');
                        $isDanger = $method === 'DELETE' || str_contains($action, 'delete') || str_contains($action, 'archive') || str_contains($action, 'status');
                        $eventTone = $isDanger ? 'danger' : ($isAccess ? 'access' : (in_array($method, ['POST','PUT','PATCH'], true) ? 'change' : ''));
                        $eventIcon = $isDanger ? 'fa-triangle-exclamation' : ($isAccess ? 'fa-right-to-bracket' : (in_array($method, ['POST','PUT','PATCH'], true) ? 'fa-pen' : 'fa-eye'));
                        $moduleLabel = ucwords(str_replace(['-', '_'], ' ', (string) ($log->module ?: 'System')));
                        $actionPart = strtolower((string) collect(explode('.', (string) ($log->route_name ?: $log->action)))->last());
                        $eventLabel = match (true) {
                            $action === 'auth.login' => 'Signed in',
                            $action === 'auth.logout' => 'Signed out',
                            str_contains($action, 'otp') && str_contains($action, 'submit') => 'Verified one-time password',
                            $method === 'DELETE' || str_contains($actionPart, 'delete') || str_contains($actionPart, 'destroy') => 'Deleted '.$moduleLabel,
                            str_contains($actionPart, 'archive') => 'Archived '.$moduleLabel,
                            str_contains($actionPart, 'restore') => 'Restored '.$moduleLabel,
                            str_contains($actionPart, 'approve') || str_contains($actionPart, 'approval') => 'Changed '.$moduleLabel.' approval',
                            str_contains($actionPart, 'review') => 'Reviewed '.$moduleLabel,
                            str_contains($actionPart, 'sync') => 'Synchronized '.$moduleLabel,
                            str_contains($actionPart, 'update') || str_contains($actionPart, 'edit') => 'Updated '.$moduleLabel,
                            str_contains($actionPart, 'store') || str_contains($actionPart, 'create') => 'Created '.$moduleLabel,
                            $method === 'POST' => 'Submitted '.$moduleLabel.' action',
                            $method === 'PUT' || $method === 'PATCH' => 'Updated '.$moduleLabel,
                            default => 'Viewed '.$moduleLabel,
                        };
                        $rawDescription = trim((string) $log->description);
                        $displayDescription = preg_match('/^(GET|POST|PUT|PATCH|DELETE)\s+\//i', $rawDescription)
                            ? $eventLabel.' successfully.'
                            : ($rawDescription !== '' ? $rawDescription : $eventLabel.' successfully.');
                        $metadataJson = !empty($log->metadata) ? json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
                    @endphp
                    <tr>
                        <td class="audit-time"><strong>{{ optional($log->created_at)->format('M d, Y') }}</strong><span class="audit-muted">{{ optional($log->created_at)->format('h:i:s A') }} · {{ optional($log->created_at)->diffForHumans() }}</span></td>
                        <td class="audit-actor"><div class="audit-actor-wrap"><span class="audit-avatar">{{ $initials }}</span><div class="audit-actor-copy"><strong class="audit-actor-name" title="{{ $name }}">{{ $name }}</strong><span class="audit-muted">{{ $log->role ? ucwords(str_replace('_',' ',(string) $log->role)) : 'System' }} · User #{{ $log->user_id ?? '-' }}</span></div></div></td>
                        <td class="audit-event"><div class="event-line"><span class="event-icon {{ $eventTone }}"><i class="fa-solid {{ $eventIcon }}"></i></span><strong>{{ $eventLabel }}</strong></div><span class="module-chip">{{ $moduleLabel }}</span></td>
                        <td><span class="audit-chip {{ strtolower($method) }}">{{ $method }}</span><span class="audit-path" title="{{ $log->path ?: '-' }}">{{ $log->path ?: '-' }}</span></td>
                        <td class="audit-description">{{ $displayDescription }}</td>
                        <td>
                            <strong>{{ $log->ip_address ?: '-' }}</strong>
                            <details class="audit-details">
                                <summary><i class="fa-solid fa-circle-info"></i> Details</summary>
                                <div class="detail-grid">
                                    <div><strong>Action:</strong> <code>{{ $log->action ?: '-' }}</code></div>
                                    <div><strong>Route:</strong> <code>{{ $log->route_name ?: '-' }}</code></div>
                                    <div><strong>User agent:</strong> <code>{{ $log->user_agent ?: '-' }}</code></div>
                                    @if($metadataJson)
                                        <div><strong>Metadata:</strong><pre class="metadata-block">{{ $metadataJson }}</pre></div>
                                    @endif
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-cell"><i class="audit-empty-icon fa-solid fa-magnifying-glass"></i>No audit events match the selected filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="audit-pagination">
            <div class="audit-pagination-meta">@if($logs->count() > 0)Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }} matching events @else 0 matching events @endif</div>
            <div>{{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</section>
<script>
    (() => {
        const resetAuditTablePosition = () => {
            const tableWrap = document.querySelector('.audit-table-wrap');
            if (tableWrap) tableWrap.scrollLeft = 0;
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', resetAuditTablePosition, { once: true });
        } else {
            resetAuditTablePosition();
        }

        window.addEventListener('pageshow', resetAuditTablePosition);
    })();
</script>
@endsection
