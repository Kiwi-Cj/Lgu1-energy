@extends('layouts.qc-admin')
@section('title', 'Audit Logs')

@section('content')
<style>
.audit-page { width: 100%; }
.audit-shell {
    background: #fff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    padding: 24px;
}
.audit-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 18px;
}
.audit-header h2 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
}
.audit-sub {
    color: #64748b;
    font-size: 0.92rem;
    margin-top: 6px;
}
.audit-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.metric {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 12px;
}
.metric .label {
    font-size: 0.72rem;
    color: #64748b;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    font-weight: 700;
}
.metric .value {
    margin-top: 6px;
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
}
.audit-filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.audit-filters .field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.audit-filters label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.audit-filters input,
.audit-filters select {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 9px 10px;
    font-size: 0.88rem;
    color: #1e293b;
    background: #fff;
}
.filter-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 10px;
    padding: 9px 12px;
    font-size: 0.84rem;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
}
.btn-primary {
    background: #2563eb;
    color: #fff;
    border-color: #1d4ed8;
}
.btn-muted {
    background: #fff;
    color: #334155;
    border-color: #cbd5e1;
}
.audit-table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    overflow-x: auto;
    background: #fff;
}
.audit-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}
.audit-table th {
    background: #f8fafc;
    color: #1e40af;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 800;
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.audit-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.88rem;
    vertical-align: top;
}
.audit-table tr:hover {
    background: #f8fbff;
}
.chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid transparent;
    padding: 3px 9px;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.chip.method-post, .chip.method-put, .chip.method-patch, .chip.method-delete {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}
.chip.method-delete {
    background: #fef2f2;
    color: #b91c1c;
    border-color: #fecaca;
}
.empty-cell {
    text-align: center;
    color: #64748b;
    font-weight: 600;
    padding: 30px !important;
}
.audit-pagination {
    margin-top: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.audit-pagination .meta {
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 600;
}
.audit-pagination nav {
    margin-left: auto;
}
.audit-pagination ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
.audit-pagination .pagination {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.audit-pagination .page-item {
    margin: 0 !important;
}
.audit-pagination .page-link {
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    font-size: 0.84rem;
    font-weight: 700;
    min-width: 36px;
    min-height: 36px;
    padding: 6px 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: all .15s ease;
}
.audit-pagination .page-link:hover {
    border-color: #93c5fd;
    color: #1d4ed8;
    background: #eff6ff;
}
.audit-pagination .page-item.active .page-link {
    border-color: #1d4ed8;
    background: #2563eb;
    color: #fff;
}
.audit-pagination .page-item.disabled .page-link {
    border-color: #e2e8f0;
    color: #94a3b8;
    background: #f8fafc;
    box-shadow: none;
    cursor: not-allowed;
}

@media (max-width: 1100px) {
    .audit-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .audit-metrics { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .audit-shell { padding: 14px; border-radius: 14px; }
    .audit-header h2 { font-size: 1.15rem; }
    .audit-filters { grid-template-columns: 1fr; }
    .filter-actions { justify-content: stretch; }
    .btn { width: 100%; justify-content: center; }
    .audit-pagination { flex-direction: column; align-items: stretch; }
    .audit-pagination nav { margin-left: 0; }
}
</style>

<div class="audit-page">
    <div class="audit-shell">
        <div class="audit-header">
            <div>
                <h2><i class="fa-solid fa-clipboard-list"></i> Audit Logs</h2>
                <p class="audit-sub">
                    {{ ($filters['scope'] ?? 'essential') === 'essential'
                        ? 'Showing essential audit events only: real logins/logouts and state-changing actions.'
                        : 'Showing all stored audit events across the system modules.' }}
                </p>
            </div>
        </div>

        <div class="audit-metrics">
            <div class="metric">
                <div class="label">{{ ($filters['scope'] ?? 'essential') === 'essential' ? 'Essential Logs' : 'Total Logs' }}</div>
                <div class="value">{{ number_format($totalLogs ?? 0) }}</div>
            </div>
            <div class="metric">
                <div class="label">Today</div>
                <div class="value">{{ number_format($todayLogs ?? 0) }}</div>
            </div>
            <div class="metric">
                <div class="label">Active Users (30d)</div>
                <div class="value">{{ number_format($activeUsers ?? 0) }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('modules.audit.index') }}" class="audit-filters">
            <div class="field" style="grid-column: span 2;">
                <label for="q">Search</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Action, module, path, user...">
            </div>

            <div class="field">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id">
                    <option value="">All Users</option>
                    @foreach($userOptions ?? [] as $u)
                        @php
                            $displayName = $u->full_name ?: ($u->name ?: $u->username);
                        @endphp
                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') === (string) $u->id)>
                            {{ $displayName }} (#{{ $u->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="module">Module</label>
                <select id="module" name="module">
                    <option value="">All Modules</option>
                    @foreach($moduleOptions ?? [] as $module)
                        <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="scope">Scope</label>
                <select id="scope" name="scope">
                    <option value="essential" @selected(($filters['scope'] ?? 'essential') === 'essential')>Essential Only</option>
                    <option value="all" @selected(($filters['scope'] ?? '') === 'all')>All Logs</option>
                </select>
            </div>

            <div class="field">
                <label for="action">Action</label>
                <select id="action" name="action">
                    <option value="">All Actions</option>
                    @foreach($actionOptions ?? [] as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="method">Method</label>
                <select id="method" name="method">
                    <option value="">All</option>
                    @foreach(['POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(strtoupper((string) ($filters['method'] ?? '')) === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="date_from">Date From</label>
                <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div class="field">
                <label for="date_to">Date To</label>
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply</button>
                <a href="{{ route('modules.audit.index') }}" class="btn btn-muted"><i class="fa fa-rotate-left"></i> Reset</a>
            </div>
        </form>

        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Method</th>
                        <th>Path</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actor = $log->user;
                            $name = $actor?->full_name ?: ($actor?->name ?: ($actor?->username ?: 'System'));
                            $roleName = $log->role ? ucwords(str_replace('_', ' ', (string) $log->role)) : '-';
                            $methodClass = 'method-' . strtolower((string) $log->method);
                        @endphp
                        <tr>
                            <td>{{ optional($log->created_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                <strong>{{ $name }}</strong><br>
                                <span style="color:#64748b;">#{{ $log->user_id ?? '-' }}</span>
                            </td>
                            <td>{{ $roleName }}</td>
                            <td>{{ $log->module ?: '-' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td><span class="chip {{ $methodClass }}">{{ $log->method ?: '-' }}</span></td>
                            <td><code>{{ $log->path ?: '-' }}</code></td>
                            <td>{{ $log->description ?: '-' }}</td>
                            <td>{{ $log->ip_address ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-cell">No audit logs found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="audit-pagination">
            <div class="meta">
                @if($logs->count() > 0)
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                @else
                    0 logs found
                @endif
            </div>
            <div>
                {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
