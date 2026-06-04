@extends('layouts.qc-admin')
@section('title', 'Energy Conservation')

@section('content')
@php
    $rows = $rows ?? collect();
    $totals = $totals ?? [];
    $topEquipment = $topEquipment ?? collect();
    $facilitiesWithoutCurrentRecord = $facilitiesWithoutCurrentRecord ?? collect();
    $levelClass = fn ($level) => strtolower(str_replace(' ', '-', (string) $level));
    $priorityRows = $rows->take(5);
@endphp

<style>
    .conservation-page {
        display: grid;
        gap: 18px;
    }
    .conservation-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .conservation-kicker {
        color: #2563eb;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .conservation-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.45rem, 2vw, 2rem);
        font-weight: 900;
    }
    .conservation-subtitle {
        color: #475569;
        margin-top: 6px;
        max-width: 780px;
        line-height: 1.45;
    }
    .conservation-filter {
        display: flex;
        gap: 8px;
        align-items: center;
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        padding: 10px;
    }
    .conservation-filter input {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 10px;
        color: #0f172a;
    }
    .conservation-btn {
        border: 0;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
        padding: 10px 13px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        justify-content: center;
    }
    .conservation-btn.secondary {
        background: #eef2ff;
        color: #1e40af;
        border: 1px solid #c7d2fe;
    }
    .conservation-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .metric-card {
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 8px;
        padding: 15px;
        min-height: 104px;
    }
    .metric-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ecfdf5;
        color: #047857;
        margin-bottom: 9px;
    }
    .metric-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .metric-value {
        color: #0f172a;
        font-size: 1.3rem;
        font-weight: 900;
        margin-top: 3px;
    }
    .conservation-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(290px, .9fr);
        gap: 14px;
        align-items: start;
    }
    .conservation-panel {
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .panel-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .panel-title {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
        margin: 0;
    }
    .panel-note {
        color: #64748b;
        font-size: .8rem;
        font-weight: 600;
    }
    .panel-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #2563eb;
        font-size: .78rem;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }
    .panel-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .panel-actions {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .panel-link-button {
        border: 0;
        background: transparent;
        padding: 0;
        cursor: pointer;
        font: inherit;
    }
    .conservation-table-wrap {
        overflow-x: auto;
    }
    .conservation-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 880px;
    }
    .conservation-table th,
    .conservation-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: top;
    }
    .conservation-table th {
        color: #475569;
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #f8fafc;
    }
    .facility-name {
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 3px;
    }
    .facility-type,
    .recommendation-text {
        color: #64748b;
        font-size: .82rem;
        line-height: 1.35;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
        background: #e0f2fe;
        color: #0369a1;
    }
    .status-critical { background: #fee2e2; color: #b91c1c; }
    .status-very-high { background: #ffedd5; color: #c2410c; }
    .status-high { background: #fef3c7; color: #a16207; }
    .status-warning { background: #fef9c3; color: #854d0e; }
    .status-normal { background: #dcfce7; color: #15803d; }
    .side-list {
        display: grid;
        gap: 10px;
        padding: 14px;
    }
    .equipment-compact-list {
        gap: 0;
        padding: 0;
    }
    .side-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        background: #f8fafc;
    }
    .equipment-compact-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 11px 14px;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }
    .equipment-compact-item:last-child {
        border-bottom: 0;
    }
    .side-title {
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .equipment-compact-title {
        color: #0f172a;
        font-size: .88rem;
        font-weight: 900;
        line-height: 1.25;
    }
    .side-meta {
        color: #64748b;
        font-size: .8rem;
        line-height: 1.35;
    }
    .equipment-compact-meta {
        color: #64748b;
        font-size: .74rem;
        line-height: 1.3;
        margin-top: 2px;
    }
    .equipment-compact-kwh {
        color: #0f172a;
        font-size: .82rem;
        font-weight: 900;
        white-space: nowrap;
        text-align: right;
    }
    .equipment-limit-note {
        padding: 10px 14px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: .75rem;
        font-weight: 800;
        background: #f8fafc;
    }
    .conservation-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 22px;
        background: rgba(15, 23, 42, .55);
    }
    .conservation-modal.is-open {
        display: flex;
    }
    .conservation-modal-panel {
        width: min(1120px, 96vw);
        max-height: 88vh;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }
    .conservation-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .conservation-modal-title {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }
    .conservation-modal-body {
        overflow: auto;
    }
    .conservation-modal-close {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
    }
    .empty-state {
        padding: 28px 16px;
        color: #64748b;
        text-align: center;
        font-weight: 700;
    }
    body.dark-mode .conservation-title,
    body.dark-mode .metric-value,
    body.dark-mode .panel-title,
    body.dark-mode .conservation-modal-title,
    body.dark-mode .facility-name,
    body.dark-mode .side-title,
    body.dark-mode .equipment-compact-title,
    body.dark-mode .equipment-compact-kwh {
        color: #f8fafc;
    }
    body.dark-mode .conservation-subtitle,
    body.dark-mode .metric-label,
    body.dark-mode .panel-note,
    body.dark-mode .facility-type,
    body.dark-mode .recommendation-text,
    body.dark-mode .side-meta,
    body.dark-mode .equipment-compact-meta,
    body.dark-mode .equipment-limit-note,
    body.dark-mode .empty-state {
        color: #cbd5e1;
    }
    body.dark-mode .conservation-filter,
    body.dark-mode .metric-card,
    body.dark-mode .conservation-panel,
    body.dark-mode .conservation-modal-panel,
    body.dark-mode .conservation-modal-close,
    body.dark-mode .side-item {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .equipment-compact-item {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .equipment-limit-note {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .conservation-table th {
        background: #111827;
        color: #cbd5e1;
    }
    @media (max-width: 980px) {
        .conservation-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 560px) {
        .conservation-filter {
            width: 100%;
            align-items: stretch;
            flex-direction: column;
        }
        .conservation-btn {
            width: 100%;
        }
    }
</style>

<div class="conservation-page">
    <div class="conservation-header">
        <div>
            <div class="conservation-kicker">Energy Conservation</div>
            <h1 class="conservation-title">Conservation Opportunity Dashboard</h1>
            <div class="conservation-subtitle">
                Shows where actual consumption is above baseline, estimates avoidable cost, and highlights practical actions for reducing wasted energy.
            </div>
        </div>
        <form method="GET" action="{{ route('modules.energy-conservation.index') }}" class="conservation-filter">
            <input type="month" name="month" value="{{ $selectedMonth }}" aria-label="Select month">
            <button class="conservation-btn" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
            <a class="conservation-btn secondary" href="{{ route('modules.energy-conservation.index') }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </form>
    </div>

    <div class="conservation-metrics">
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-building-circle-check"></i></div>
            <div class="metric-label">Monitored Facilities</div>
            <div class="metric-value">{{ number_format((float) ($totals['monitored_facilities'] ?? 0)) }} / {{ number_format((float) ($totals['facilities'] ?? 0)) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-bolt"></i></div>
            <div class="metric-label">Actual kWh</div>
            <div class="metric-value">{{ number_format((float) ($totals['actual_kwh'] ?? 0), 2) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-leaf"></i></div>
            <div class="metric-label">Excess kWh</div>
            <div class="metric-value">{{ number_format((float) ($totals['excess_kwh'] ?? 0), 2) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-peso-sign"></i></div>
            <div class="metric-label">Avoidable Cost</div>
            <div class="metric-value">PHP {{ number_format((float) ($totals['avoidable_cost'] ?? 0), 2) }}</div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="metric-label">Priority Facilities</div>
            <div class="metric-value">{{ number_format((float) ($totals['priority_count'] ?? 0)) }}</div>
        </div>
    </div>

    <div class="conservation-grid">
        <section class="conservation-panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Facility Conservation Priorities</h2>
                    <div class="panel-note">Top 5 for {{ $periodLabel }}</div>
                </div>
                <div class="panel-actions">
                    @if($rows->count() > 5)
                        <button class="panel-link panel-link-button" type="button" data-open-priorities-modal>
                            <i class="fa-solid fa-list"></i> View all priorities
                        </button>
                    @endif
                    <a class="conservation-btn secondary" href="{{ route('modules.energy-monitoring.index', ['month' => $selectedMonth]) }}"><i class="fa-solid fa-chart-line"></i> Monitoring</a>
                </div>
            </div>
            <div class="conservation-table-wrap">
                <table class="conservation-table">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Actual</th>
                            <th>Baseline</th>
                            <th>Excess</th>
                            <th>Status</th>
                            <th>Recommendation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priorityRows as $row)
                            <tr>
                                <td>
                                    <div class="facility-name">{{ $row['facility_name'] }}</div>
                                    <div class="facility-type">{{ $row['facility_type'] }}</div>
                                </td>
                                <td>{{ number_format((float) $row['actual_kwh'], 2) }} kWh</td>
                                <td>{{ number_format((float) $row['baseline_kwh'], 2) }} kWh</td>
                                <td>
                                    <strong>{{ number_format((float) $row['excess_kwh'], 2) }} kWh</strong><br>
                                    <span class="facility-type">PHP {{ number_format((float) $row['avoidable_cost'], 2) }}</span>
                                </td>
                                <td>
                                    <span class="status-pill status-{{ $levelClass($row['alert_level']) }}">{{ $row['alert_level'] }}</span>
                                </td>
                                <td><div class="recommendation-text">{{ $row['recommendation'] }}</div></td>
                                <td>
                                    <a class="conservation-btn secondary" href="{{ $row['monthly_records_url'] }}"><i class="fa-solid fa-clipboard-list"></i> Records</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">No main meter records found for {{ $periodLabel }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="conservation-panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">High Load Equipment</h2>
                    <div class="panel-note">Largest estimated monthly kWh</div>
                </div>
                <a class="panel-link" href="{{ route('modules.load-tracking.index', ['month' => $selectedMonth]) }}">
                    <i class="fa-solid fa-list"></i> View full list
                </a>
            </div>
            <div class="side-list equipment-compact-list">
                @forelse($topEquipment->take(5) as $equipment)
                    <div class="equipment-compact-item">
                        <div>
                            <div class="equipment-compact-title">{{ $equipment['equipment_name'] }}</div>
                            <div class="equipment-compact-meta">
                                {{ $equipment['facility_name'] }}<br>
                                {{ $equipment['scope_label'] }}: {{ $equipment['meter_name'] }}
                            </div>
                        </div>
                        <div class="equipment-compact-kwh">{{ number_format((float) $equipment['estimated_kwh'], 2) }} kWh/mo</div>
                    </div>
                @empty
                    <div class="empty-state">No equipment inventory yet.</div>
                @endforelse
                @if($topEquipment->count() > 5)
                    <div class="equipment-limit-note">Showing top 5 of {{ number_format($topEquipment->count()) }} high-load equipment items.</div>
                @endif
            </div>
        </aside>
    </div>

    @if($facilitiesWithoutCurrentRecord->isNotEmpty())
        <section class="conservation-panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Facilities Needing Current Readings</h2>
                    <div class="panel-note">These facilities cannot produce conservation findings for {{ $periodLabel }} yet.</div>
                </div>
            </div>
            <div class="side-list">
                @foreach($facilitiesWithoutCurrentRecord->take(12) as $facility)
                    <div class="side-item">
                        <div class="side-title">{{ $facility->name }}</div>
                        <div class="side-meta">Add main meter monthly record to include this facility in conservation analysis.</div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<div class="conservation-modal" id="prioritiesModal" aria-hidden="true">
    <div class="conservation-modal-panel" role="dialog" aria-modal="true" aria-labelledby="prioritiesModalTitle">
        <div class="conservation-modal-head">
            <div>
                <h2 class="conservation-modal-title" id="prioritiesModalTitle">All Conservation Priorities</h2>
                <div class="panel-note">{{ $periodLabel }}</div>
            </div>
            <button class="conservation-modal-close" type="button" aria-label="Close" data-close-priorities-modal>&times;</button>
        </div>
        <div class="conservation-modal-body">
            <table class="conservation-table">
                <thead>
                    <tr>
                        <th>Facility</th>
                        <th>Actual</th>
                        <th>Baseline</th>
                        <th>Excess</th>
                        <th>Status</th>
                        <th>Recommendation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                <div class="facility-name">{{ $row['facility_name'] }}</div>
                                <div class="facility-type">{{ $row['facility_type'] }}</div>
                            </td>
                            <td>{{ number_format((float) $row['actual_kwh'], 2) }} kWh</td>
                            <td>{{ number_format((float) $row['baseline_kwh'], 2) }} kWh</td>
                            <td>
                                <strong>{{ number_format((float) $row['excess_kwh'], 2) }} kWh</strong><br>
                                <span class="facility-type">PHP {{ number_format((float) $row['avoidable_cost'], 2) }}</span>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $levelClass($row['alert_level']) }}">{{ $row['alert_level'] }}</span>
                            </td>
                            <td><div class="recommendation-text">{{ $row['recommendation'] }}</div></td>
                            <td>
                                <a class="conservation-btn secondary" href="{{ $row['monthly_records_url'] }}"><i class="fa-solid fa-clipboard-list"></i> Records</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">No main meter records found for {{ $periodLabel }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('prioritiesModal');
    const openButton = document.querySelector('[data-open-priorities-modal]');
    const closeButton = document.querySelector('[data-close-priorities-modal]');

    function openModal() {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    openButton?.addEventListener('click', openModal);
    closeButton?.addEventListener('click', closeModal);
    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});
</script>
@endsection
