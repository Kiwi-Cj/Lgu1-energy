@extends('layouts.qc-admin')
@section('title', 'Energy Report')

@php
    $user = auth()->user();
    $roleKey = $user?->role_key ?? str_replace(' ', '_', strtolower((string) ($user?->role ?? '')));
    $canExportReports = \App\Support\RoleAccess::can($user, 'export_reports');

    $rows = collect($energyRows ?? []);
    $toNumber = function ($value) {
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return is_numeric($clean) ? (float) $clean : null;
    };

    $totalActual = $rows->sum(function ($row) use ($toNumber) {
        return $toNumber($row['actual_kwh'] ?? 0) ?? 0;
    });
    $totalBaseline = $rows->sum(function ($row) use ($toNumber) {
        return $toNumber($row['baseline_kwh'] ?? 0) ?? 0;
    });
    $totalVariance = $rows->sum(function ($row) use ($toNumber) {
        return $toNumber($row['variance'] ?? 0) ?? 0;
    });
    $increasingCount = $rows->filter(fn ($row) => str_starts_with((string) ($row['trend'] ?? ''), 'up'))->count();
    $decreasingCount = $rows->filter(fn ($row) => str_starts_with((string) ($row['trend'] ?? ''), 'down'))->count();
    $selectedMonthValue = isset($selectedMonth)
        ? (string) $selectedMonth
        : (request()->has('month') ? (string) request('month') : (string) date('n'));
    $exportFilters = request()->all();
    if (! request()->has('year')) {
        $exportFilters['year'] = date('Y');
    }
    if (! request()->has('month') && $selectedMonthValue !== '') {
        $exportFilters['month'] = $selectedMonthValue;
    }
    $exportCsvFilters = array_merge($exportFilters, ['format' => 'csv']);
    $exportXlsxFilters = array_merge($exportFilters, ['format' => 'xlsx']);
@endphp

@section('content')
<style>
.energy-report-page {
    width: 100%;
}

.energy-report-shell {
    background: linear-gradient(160deg, #f8fbff 0%, #eef5ff 45%, #f8fafc 100%);
    border: 1px solid #e2ebf7;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    padding: 24px 20px;
}

.energy-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}

.energy-header h2 {
    margin: 0;
    color: #0f172a;
    font-size: 1.62rem;
    font-weight: 900;
}

.energy-header p {
    margin: 6px 0 0;
    color: #475569;
    font-size: 0.93rem;
}

.energy-actions {
    display: inline-flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    text-decoration: none;
    font-weight: 800;
    font-size: 0.86rem;
    border-radius: 10px;
    padding: 10px 14px;
}

.btn-excel {
    background: linear-gradient(90deg, #15803d, #16a34a);
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25);
}

.btn-csv {
    background: linear-gradient(90deg, #0f766e, #14b8a6);
    box-shadow: 0 6px 16px rgba(20, 184, 166, 0.22);
}

.btn-pdf {
    background: linear-gradient(90deg, #be123c, #e11d48);
    box-shadow: 0 6px 16px rgba(225, 29, 72, 0.25);
}

.energy-kpis {
    display: grid;
    grid-template-columns: repeat(5, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.kpi-card {
    border-radius: 12px;
    border: 1px solid transparent;
    padding: 12px 12px;
    min-height: 96px;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.kpi-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.71rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
}

.kpi-value {
    font-size: 1.36rem;
    font-weight: 900;
    line-height: 1;
}

.kpi-note {
    font-size: 0.72rem;
    font-weight: 700;
    opacity: 0.9;
    margin-top: 6px;
}

.kpi-total { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.kpi-actual { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
.kpi-baseline { background: #f8fafc; border-color: #cbd5e1; color: #334155; }
.kpi-var { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
.kpi-trend { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

.energy-filters {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 14px;
    display: grid;
    grid-template-columns: 1.6fr 0.8fr 0.8fr 1fr auto auto;
    gap: 10px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 0.73rem;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.filter-group select,
.filter-group input {
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 9px 11px;
    font-size: 0.92rem;
    color: #0f172a;
    background: #fff;
}

.month-picker {
    position: relative;
}

.month-picker-toggle {
    width: 100%;
    min-height: 45px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 9px 11px;
    font-size: 0.92rem;
    color: #0f172a;
    background: #fff;
    cursor: pointer;
}

.month-picker-toggle:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
}

.month-picker-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 50;
    display: none;
    max-height: 230px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.16);
    padding: 4px;
}

.month-picker.open .month-picker-menu {
    display: block;
}

.month-picker-option {
    width: 100%;
    display: flex;
    align-items: center;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #0f172a;
    cursor: pointer;
    font-size: 0.9rem;
    padding: 8px 10px;
    text-align: left;
}

.month-picker-option:hover,
.month-picker-option.is-selected {
    background: #eaf1ff;
    color: #1d4ed8;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
}

.btn-filter,
.btn-reset {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 9px;
    padding: 10px 14px;
    font-weight: 800;
    font-size: 0.85rem;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    white-space: nowrap;
}

.btn-filter {
    background: linear-gradient(90deg, #2563eb, #6366f1);
    color: #fff;
}

.btn-reset {
    background: #fff;
    color: #334155;
    border-color: #cbd5e1;
}

.energy-table-wrap {
    overflow: auto;
    max-height: calc(100vh - 320px);
    min-height: 420px;
    overscroll-behavior: contain;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
}

.energy-table {
    width: 100%;
    min-width: 860px;
    border-collapse: collapse;
}

.energy-table thead {
    background: #f8fafc;
}

.energy-table th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
    padding: 12px 14px;
    text-align: left;
    vertical-align: middle;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

.energy-table td {
    padding: 12px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.9rem;
}

.energy-table tr:hover {
    background: #f8fbff;
}

.energy-row {
    cursor: pointer;
    transition: background-color 0.15s ease, box-shadow 0.15s ease;
}

.energy-row:hover,
.energy-row:focus-visible {
    background: #eff6ff;
    box-shadow: inset 4px 0 0 #2563eb;
    outline: none;
}

.facility-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    color: #0f172a;
}

.facility-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
}

.energy-table th.num,
.energy-table td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.energy-table th.trend-col,
.energy-table td.trend-col {
    text-align: center;
    white-space: nowrap;
}

.trend-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 999px;
    border: 1px solid transparent;
    padding: 4px 10px;
    min-width: 140px;
    font-size: 0.75rem;
    font-weight: 800;
    line-height: 1.15;
    text-transform: uppercase;
}

.trend-pill i {
    font-size: 0.74rem;
}

.trend-up { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.trend-down { background: #dcfce7; color: #166534; border-color: #86efac; }
.trend-stable { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

.empty-row {
    text-align: center;
    color: #94a3b8;
    font-style: italic;
    padding: 30px 16px;
}

.annual-summary-modal {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, 0.62);
    backdrop-filter: blur(3px);
}

.annual-summary-modal[hidden] {
    display: none;
}

.annual-summary-dialog {
    width: min(1120px, 96vw);
    max-height: 92vh;
    overflow-y: auto;
    border: 1px solid #dbe4f0;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
}

.annual-summary-head {
    position: sticky;
    top: 0;
    z-index: 3;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: rgba(255, 255, 255, 0.97);
}

.annual-summary-head h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1.28rem;
    font-weight: 900;
}

.annual-summary-head p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 0.86rem;
}

.annual-summary-head-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.annual-download-btn {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 12px;
    border-radius: 10px;
    color: #fff;
    text-decoration: none;
    font-size: 0.76rem;
    font-weight: 900;
}

.annual-download-btn.csv { background: #0f766e; }
.annual-download-btn.pdf { background: #be123c; }

.annual-summary-close {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #475569;
    cursor: pointer;
    font-size: 1.3rem;
    line-height: 1;
}

.annual-summary-body {
    padding: 18px 20px 22px;
}

.annual-summary-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(160px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.annual-summary-kpi {
    padding: 12px 14px;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    background: #f8fbff;
}

.annual-summary-kpi span {
    display: block;
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.annual-summary-kpi strong {
    display: block;
    margin-top: 5px;
    color: #0f172a;
    font-size: 1.08rem;
    font-weight: 900;
    font-variant-numeric: tabular-nums;
}

.annual-summary-kpi strong.is-up { color: #b91c1c; }
.annual-summary-kpi strong.is-down { color: #15803d; }
.annual-summary-kpi strong.is-stable { color: #475569; }

.annual-chart-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    overflow: hidden;
}

.annual-chart-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 13px 15px;
    border-bottom: 1px solid #e2e8f0;
}

.annual-chart-head strong {
    color: #1e293b;
    font-size: 0.94rem;
}

.annual-chart-legend {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 700;
}

.annual-chart-legend span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.annual-legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 3px;
}

.annual-legend-dot.actual { background: #2563eb; }
.annual-legend-dot.baseline { background: #94a3b8; }

.annual-chart-scroll {
    overflow-x: auto;
    padding: 18px 14px 12px;
}

.annual-chart {
    min-width: 900px;
    height: 330px;
    display: grid;
    grid-template-columns: repeat(12, minmax(62px, 1fr));
    align-items: end;
    gap: 10px;
    border-bottom: 1px solid #cbd5e1;
    background: repeating-linear-gradient(to top, transparent 0, transparent 64px, #eef2f7 65px);
}

.annual-chart-month {
    height: 100%;
    display: grid;
    grid-template-rows: minmax(0, 1fr) 23px 20px;
    gap: 5px;
    align-items: end;
    text-align: center;
}

.annual-bars {
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 5px;
}

.annual-bar {
    width: 24px;
    min-height: 0;
    border-radius: 7px 7px 2px 2px;
    transition: filter 0.15s ease, transform 0.15s ease;
}

.annual-bar.actual {
    background: linear-gradient(180deg, #3b82f6, #1d4ed8);
    box-shadow: 0 6px 12px rgba(37, 99, 235, 0.2);
}

.annual-bar.baseline {
    background: #94a3b8;
}

.annual-chart-month:hover .annual-bar {
    filter: brightness(1.08);
    transform: scaleX(1.06);
}

.annual-change {
    justify-self: center;
    min-width: 50px;
    padding: 3px 6px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 900;
    font-variant-numeric: tabular-nums;
}

.annual-change.is-up { background: #fee2e2; color: #b91c1c; }
.annual-change.is-down { background: #dcfce7; color: #166534; }
.annual-change.is-stable { background: #f1f5f9; color: #475569; }
.annual-change.is-none { color: #94a3b8; }

.annual-month-label {
    color: #334155;
    font-size: 0.72rem;
    font-weight: 900;
    text-transform: uppercase;
}

.annual-chart-note {
    margin: 10px 2px 0;
    color: #64748b;
    font-size: 0.75rem;
}

/* Page-level dark mode */
body.dark-mode .energy-report-shell {
    background: linear-gradient(160deg, #0f172a 0%, #111827 100%);
    border-color: #1f2937;
}
body.dark-mode .energy-header h2 { color: #e2e8f0; }
body.dark-mode .energy-header p { color: #94a3b8; }
body.dark-mode .kpi-card {
    border-color: #334155;
    box-shadow: none;
}
body.dark-mode .kpi-total { background: rgba(37, 99, 235, 0.2); color: #93c5fd; border-color: rgba(147, 197, 253, 0.3); }
body.dark-mode .kpi-actual { background: rgba(14, 116, 144, 0.2); color: #67e8f9; border-color: rgba(125, 211, 252, 0.28); }
body.dark-mode .kpi-baseline { background: rgba(51, 65, 85, 0.32); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.25); }
body.dark-mode .kpi-var { background: rgba(194, 65, 12, 0.2); color: #fdba74; border-color: rgba(251, 146, 60, 0.28); }
body.dark-mode .kpi-trend { background: rgba(22, 101, 52, 0.24); color: #86efac; border-color: rgba(74, 222, 128, 0.25); }
body.dark-mode .btn-csv { background: linear-gradient(90deg, #115e59, #0f766e); }
body.dark-mode .energy-filters,
body.dark-mode .energy-table-wrap { background: #111827; border-color: #1f2937; }
body.dark-mode .filter-group label { color: #cbd5e1; }
body.dark-mode .filter-group select,
body.dark-mode .filter-group input {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .month-picker-toggle,
body.dark-mode .month-picker-menu {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .month-picker-option {
    color: #e2e8f0;
}
body.dark-mode .month-picker-option:hover,
body.dark-mode .month-picker-option.is-selected {
    background: #1f2937;
    color: #93c5fd;
}
body.dark-mode .btn-reset {
    background: #1f2937;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .energy-table thead { background: #0f172a; }
body.dark-mode .energy-table th { color: #94a3b8; border-bottom-color: #1f2937; background: #0f172a; }
body.dark-mode .energy-table td { color: #e2e8f0; border-bottom-color: #1f2937; }
body.dark-mode .energy-table tr:hover { background: #1f2937; }
body.dark-mode .energy-row:hover,
body.dark-mode .energy-row:focus-visible { background: #172033; }
body.dark-mode .facility-cell { color: #f8fafc; }
body.dark-mode .empty-row { color: #94a3b8; }
body.dark-mode .trend-up { background: rgba(239, 68, 68, 0.14); color: #fca5a5; border-color: rgba(248, 113, 113, 0.32); }
body.dark-mode .trend-down { background: rgba(34, 197, 94, 0.14); color: #86efac; border-color: rgba(74, 222, 128, 0.32); }
body.dark-mode .trend-stable { background: rgba(148, 163, 184, 0.12); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.26); }
body.dark-mode .annual-summary-dialog,
body.dark-mode .annual-chart-card { background: #111827; border-color: #334155; }
body.dark-mode .annual-summary-head { background: rgba(17, 24, 39, 0.97); border-bottom-color: #334155; }
body.dark-mode .annual-summary-head h3,
body.dark-mode .annual-summary-kpi strong,
body.dark-mode .annual-chart-head strong { color: #f8fafc; }
body.dark-mode .annual-summary-head p,
body.dark-mode .annual-summary-kpi span,
body.dark-mode .annual-chart-legend,
body.dark-mode .annual-chart-note { color: #94a3b8; }
body.dark-mode .annual-summary-close { background: #1f2937; border-color: #475569; color: #e2e8f0; }
body.dark-mode .annual-summary-kpi { background: #0f172a; border-color: #334155; }
body.dark-mode .annual-chart-head { border-bottom-color: #334155; }
body.dark-mode .annual-chart { border-bottom-color: #475569; background: repeating-linear-gradient(to top, transparent 0, transparent 64px, #1f2937 65px); }
body.dark-mode .annual-month-label { color: #cbd5e1; }

@media (max-width: 1100px) {
    .energy-kpis {
        grid-template-columns: repeat(3, minmax(120px, 1fr));
    }
    .energy-filters {
        grid-template-columns: 1fr 1fr;
    }
    .btn-filter,
    .btn-reset {
        width: 100%;
    }
    .energy-table-wrap {
        max-height: 58vh;
        min-height: 340px;
    }
    .annual-summary-kpis {
        grid-template-columns: repeat(2, minmax(140px, 1fr));
    }
}

@media (max-width: 760px) {
    .energy-report-shell {
        padding: 14px 10px;
    }
    .energy-header {
        flex-direction: column;
        align-items: stretch;
    }
    .energy-actions {
        width: 100%;
    }
    .btn-action {
        flex: 1;
        justify-content: center;
    }
    .energy-kpis {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
    .energy-filters {
        grid-template-columns: 1fr;
    }
    .energy-table-wrap {
        max-height: 60vh;
        min-height: 300px;
    }
    .annual-summary-modal {
        padding: 8px;
    }
    .annual-summary-dialog {
        width: 100%;
        max-height: 96vh;
    }
    .annual-summary-head,
    .annual-summary-body {
        padding-left: 14px;
        padding-right: 14px;
    }
    .annual-summary-head {
        align-items: stretch;
        flex-direction: column;
    }
    .annual-summary-head-actions {
        justify-content: flex-start;
    }
    .annual-download-btn {
        flex: 1;
    }
    .annual-summary-kpis {
        grid-template-columns: 1fr 1fr;
    }
    .annual-chart-head {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<div class="energy-report-page">
    <div class="energy-report-shell">
        <div class="energy-header">
            <div>
                <h2>Energy Report</h2>
                <p>Track facility consumption variance and trend behavior by period.</p>
            </div>
            <div class="energy-actions">
                @if($canExportReports)
                <a href="{{ route('reports.energy-export', $exportCsvFilters) }}" class="btn-action btn-csv" data-secure-download>
                    <i class="fa fa-file-text-o"></i> Export CSV
                </a>
                <a href="{{ route('reports.energy-export', $exportXlsxFilters) }}" class="btn-action btn-excel" data-secure-download>
                    <i class="fa fa-download"></i> Export Excel
                </a>
                <a href="{{ route('modules.energy.export-pdf', array_filter($exportFilters, fn ($value) => $value !== null && $value !== '')) }}" class="btn-action btn-pdf" data-secure-download>
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </a>
                @endif
            </div>
        </div>

        <div class="energy-kpis">
            <div class="kpi-card kpi-total">
                <span class="kpi-label"><i class="fa fa-table"></i> Rows</span>
                <div class="kpi-value">{{ $rows->count() }}</div>
                <span class="kpi-note">records loaded</span>
            </div>
            <div class="kpi-card kpi-actual">
                <span class="kpi-label"><i class="fa fa-bolt"></i> Total Actual</span>
                <div class="kpi-value">{{ number_format($totalActual, 2) }}</div>
                <span class="kpi-note">kWh consumed</span>
            </div>
            <div class="kpi-card kpi-baseline">
                <span class="kpi-label"><i class="fa fa-balance-scale"></i> Total Baseline</span>
                <div class="kpi-value">{{ number_format($totalBaseline, 2) }}</div>
                <span class="kpi-note">kWh target</span>
            </div>
            <div class="kpi-card kpi-var">
                <span class="kpi-label"><i class="fa fa-exchange"></i> Total Variance</span>
                <div class="kpi-value">{{ number_format($totalVariance, 2) }}</div>
                <span class="kpi-note">actual vs baseline</span>
            </div>
            <div class="kpi-card kpi-trend">
                <span class="kpi-label"><i class="fa fa-line-chart"></i> Trend (Up/Down)</span>
                <div class="kpi-value">{{ $increasingCount }}/{{ $decreasingCount }}</div>
                <span class="kpi-note">increasing/decreasing</span>
            </div>
        </div>

        <form method="GET" action="" class="energy-filters">
            <div class="filter-group">
                <label for="facility_id">Facility</label>
                <select name="facility_id" id="facility_id">
                    <option value="">All Facilities</option>
                    @foreach($facilities ?? [] as $facility)
                        <option value="{{ $facility->id }}" {{ (request('facility_id') == $facility->id) ? 'selected' : '' }}>
                            {{ $facility->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="year">Year</label>
                <select name="year" id="year">
                    @foreach($years ?? [] as $year)
                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="month">Month</label>
                @php
                    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                    $selectedMonthLabel = $selectedMonthValue !== ''
                        ? ($months[(int) $selectedMonthValue] ?? 'All Months')
                        : 'All Months';
                @endphp
                <div class="month-picker" id="monthPicker">
                    <input type="hidden" name="month" id="month" value="{{ $selectedMonthValue }}">
                    <button type="button" class="month-picker-toggle" id="monthPickerToggle" aria-haspopup="listbox" aria-expanded="false">
                        <span id="monthPickerLabel">{{ $selectedMonthLabel }}</span>
                        <i class="fa fa-chevron-down"></i>
                    </button>
                    <div class="month-picker-menu" id="monthPickerMenu" role="listbox">
                        <button type="button" class="month-picker-option {{ $selectedMonthValue === '' ? 'is-selected' : '' }}" data-value="" role="option">All Months</button>
                        @foreach($months as $num => $name)
                            <button type="button" class="month-picker-option {{ $selectedMonthValue !== '' && (int) $selectedMonthValue === $num ? 'is-selected' : '' }}" data-value="{{ $num }}" role="option">{{ $name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <label for="tableSearch">Quick Search</label>
                <input id="tableSearch" type="text" placeholder="Search facility..." />
            </div>

            <button type="submit" class="btn-filter">
                <i class="fa fa-filter"></i> Apply
            </button>
            <a href="{{ url()->current() }}" class="btn-reset">Reset</a>
        </form>

        <div class="energy-table-wrap">
            <table class="energy-table" id="energyTable">
                <thead>
                    <tr>
                        <th>Facility</th>
                        <th>Month</th>
                        <th class="num">Actual</th>
                        <th class="num">Baseline</th>
                        <th class="num">Variance</th>
                        <th class="trend-col">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($energyRows ?? [] as $row)
                        @php
                            $trend = (string) ($row['trend'] ?? 'stable');
                            $trendBase = str_starts_with($trend, 'up') ? 'up' : (str_starts_with($trend, 'down') ? 'down' : (str_starts_with($trend, 'insufficient') ? 'insufficient' : 'stable'));
                            $trendClass = $trendBase === 'up' ? 'trend-up' : ($trendBase === 'down' ? 'trend-down' : 'trend-stable');
                            $trendLabel = $trendBase === 'up' ? 'Increasing' : ($trendBase === 'down' ? 'Decreasing' : ($trendBase === 'insufficient' ? 'Insufficient Data' : 'Stable'));
                            $trendIcon = $trendBase === 'up' ? 'fa-arrow-up' : ($trendBase === 'down' ? 'fa-arrow-down' : ($trendBase === 'insufficient' ? 'fa-circle-question' : 'fa-minus'));
                            $trendSpike = str_contains($trend, '3-Month Spike');
                        @endphp
                        <tr class="energy-row"
                            data-search="{{ strtolower((string)($row['facility'] ?? '')) }}"
                            data-summary-key="{{ $row['summary_key'] ?? '' }}"
                            tabindex="0"
                            role="button"
                            aria-label="View {{ $row['facility'] }} annual energy summary for {{ substr((string) ($row['month'] ?? ''), -4) }}">
                            <td>
                                <div class="facility-cell">
                                    <span class="facility-dot"></span>
                                    <span>{{ $row['facility'] }}</span>
                                </div>
                            </td>
                            <td>{{ $row['month'] }}</td>
                            <td class="num">{{ $row['actual_kwh'] }}</td>
                            <td class="num">{{ $row['baseline_kwh'] }}</td>
                            <td class="num">{{ $row['variance'] }}</td>
                            <td class="trend-col">
                                <span class="trend-pill {{ $trendClass }}">
                                    <i class="fa {{ $trendIcon }}"></i> {{ $trendLabel }}
                                </span>
                                @if($trendSpike)
                                    <div style="margin-top:6px;font-size:0.78rem;font-weight:800;color:#b91c1c;">3-Month Spike</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">No records found.</td>
                        </tr>
                    @endforelse
                    <tr id="energyNoMatch" style="display:none;">
                        <td colspan="6" class="empty-row">No matching facility in current result.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="annualSummaryModal"
     class="annual-summary-modal"
     hidden
     role="dialog"
     aria-modal="true"
     aria-labelledby="annualSummaryTitle">
    <div class="annual-summary-dialog">
        <div class="annual-summary-head">
            <div>
                <h3 id="annualSummaryTitle">Annual Energy Summary</h3>
                <p id="annualSummarySubtitle">Monthly actual usage, baseline, and changes.</p>
            </div>
            <div class="annual-summary-head-actions">
                @if($canExportReports)
                    <a id="annualDownloadCsv" href="#" class="annual-download-btn csv" data-secure-download>
                        <i class="fa fa-file-csv"></i> CSV
                    </a>
                    <a id="annualDownloadPdf" href="#" class="annual-download-btn pdf" data-secure-download>
                        <i class="fa fa-file-pdf"></i> PDF
                    </a>
                @endif
                <button type="button" id="annualSummaryClose" class="annual-summary-close" aria-label="Close annual summary">&times;</button>
            </div>
        </div>
        <div class="annual-summary-body">
            <div class="annual-summary-kpis">
                <div class="annual-summary-kpi">
                    <span>Yearly Actual</span>
                    <strong id="annualTotalActual">—</strong>
                </div>
                <div class="annual-summary-kpi">
                    <span>Yearly Baseline</span>
                    <strong id="annualTotalBaseline">—</strong>
                </div>
                <div class="annual-summary-kpi">
                    <span>Yearly Variance</span>
                    <strong id="annualTotalVariance">—</strong>
                </div>
                <div class="annual-summary-kpi">
                    <span>Average / Recorded Month</span>
                    <strong id="annualAverageActual">—</strong>
                </div>
                <div class="annual-summary-kpi">
                    <span>Latest Monthly Change</span>
                    <strong id="annualLatestChange">—</strong>
                </div>
                <div class="annual-summary-kpi">
                    <span>Data Completeness</span>
                    <strong id="annualDataCompleteness">—</strong>
                </div>
            </div>

            <div class="annual-chart-card">
                <div class="annual-chart-head">
                    <strong>Monthly Consumption Trend</strong>
                    <div class="annual-chart-legend" aria-label="Chart legend">
                        <span><i class="annual-legend-dot actual"></i> Actual kWh</span>
                        <span><i class="annual-legend-dot baseline"></i> Baseline kWh</span>
                    </div>
                </div>
                <div class="annual-chart-scroll">
                    <div id="annualSummaryChart" class="annual-chart" aria-label="Monthly energy bar graph"></div>
                </div>
            </div>
            <p class="annual-chart-note">
                Percentages compare each recorded month with the previous available recorded month.
                Red means usage increased; green means usage decreased.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('tableSearch');
    const rows = Array.from(document.querySelectorAll('.energy-row'));
    const noMatch = document.getElementById('energyNoMatch');
    const monthPicker = document.getElementById('monthPicker');
    const monthToggle = document.getElementById('monthPickerToggle');
    const monthInput = document.getElementById('month');
    const monthLabel = document.getElementById('monthPickerLabel');
    const monthOptions = Array.from(document.querySelectorAll('.month-picker-option'));
    const annualSummaries = @json($annualSummaries ?? []);
    const annualModal = document.getElementById('annualSummaryModal');
    const annualClose = document.getElementById('annualSummaryClose');
    const annualTitle = document.getElementById('annualSummaryTitle');
    const annualSubtitle = document.getElementById('annualSummarySubtitle');
    const annualChart = document.getElementById('annualSummaryChart');
    const annualTotalActual = document.getElementById('annualTotalActual');
    const annualTotalBaseline = document.getElementById('annualTotalBaseline');
    const annualTotalVariance = document.getElementById('annualTotalVariance');
    const annualAverageActual = document.getElementById('annualAverageActual');
    const annualLatestChange = document.getElementById('annualLatestChange');
    const annualDataCompleteness = document.getElementById('annualDataCompleteness');
    const annualDownloadCsv = document.getElementById('annualDownloadCsv');
    const annualDownloadPdf = document.getElementById('annualDownloadPdf');
    let annualTrigger = null;

    const numberFormatter = new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const toNullableNumber = (value) => {
        if (value === null || value === undefined || value === '') return null;
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const formatKwh = (value) => {
        const number = toNullableNumber(value);
        return number !== null ? `${numberFormatter.format(number)} kWh` : '—';
    };

    const formatChange = (value) => {
        const number = toNullableNumber(value);
        if (number === null) return '—';
        return `${number > 0 ? '+' : ''}${numberFormatter.format(number)}%`;
    };

    const formatSignedKwh = (value) => {
        const number = toNullableNumber(value);
        if (number === null) return '—';
        return `${number > 0 ? '+' : ''}${numberFormatter.format(number)} kWh`;
    };

    const renderAnnualChart = (summary) => {
        if (!annualChart) return;
        annualChart.replaceChildren();

        const months = Array.isArray(summary.months) ? summary.months : [];
        const values = months.flatMap((month) => [toNullableNumber(month.actual), toNullableNumber(month.baseline)])
            .filter((value) => value !== null && value >= 0);
        const maxValue = Math.max(...values, 1);

        months.forEach((month) => {
            const actual = toNullableNumber(month.actual);
            const baseline = toNullableNumber(month.baseline);
            const hasActual = actual !== null;
            const hasBaseline = baseline !== null;
            const change = toNullableNumber(month.change_percent);
            const hasChange = change !== null;
            const direction = hasChange
                ? (change > 0 ? 'up' : (change < 0 ? 'down' : 'stable'))
                : 'none';

            const column = document.createElement('div');
            column.className = 'annual-chart-month';

            const bars = document.createElement('div');
            bars.className = 'annual-bars';

            const actualBar = document.createElement('div');
            actualBar.className = 'annual-bar actual';
            actualBar.style.height = hasActual ? `${Math.max((actual / maxValue) * 100, 1.5)}%` : '0';
            actualBar.title = `${month.label} actual: ${formatKwh(month.actual)}`;

            const baselineBar = document.createElement('div');
            baselineBar.className = 'annual-bar baseline';
            baselineBar.style.height = hasBaseline ? `${Math.max((baseline / maxValue) * 100, 1.5)}%` : '0';
            baselineBar.title = `${month.label} baseline: ${formatKwh(month.baseline)}`;

            const changeBadge = document.createElement('span');
            changeBadge.className = `annual-change is-${direction}`;
            changeBadge.textContent = hasActual ? formatChange(month.change_percent) : 'No data';
            changeBadge.title = hasChange
                ? `${formatChange(month.change_percent)} versus the previous recorded month`
                : (hasActual ? 'No earlier recorded month for comparison' : 'No reading');

            const label = document.createElement('span');
            label.className = 'annual-month-label';
            label.textContent = month.label;

            bars.append(actualBar, baselineBar);
            column.append(bars, changeBadge, label);
            annualChart.appendChild(column);
        });
    };

    const openAnnualSummary = (row) => {
        const summary = annualSummaries[row.dataset.summaryKey || ''];
        if (!summary || !annualModal) return;

        annualTrigger = row;
        annualTitle.textContent = `${summary.facility} — ${summary.year}`;
        annualSubtitle.textContent = `${summary.months_recorded} of 12 months recorded`;
        annualTotalActual.textContent = formatKwh(summary.total_actual);
        annualTotalBaseline.textContent = formatKwh(summary.total_baseline);
        annualTotalVariance.textContent = formatSignedKwh(summary.total_variance);
        annualTotalVariance.className = summary.total_variance > 0
            ? 'is-up'
            : (summary.total_variance < 0 ? 'is-down' : 'is-stable');
        annualAverageActual.textContent = formatKwh(summary.average_actual);
        annualLatestChange.textContent = formatChange(summary.latest_change_percent);
        annualLatestChange.className = `is-${summary.latest_direction || 'stable'}`;
        annualDataCompleteness.textContent = `${summary.months_recorded} / 12 (${Math.round((summary.months_recorded / 12) * 100)}%)`;

        if (annualDownloadCsv) annualDownloadCsv.href = summary.csv_url;
        if (annualDownloadPdf) annualDownloadPdf.href = summary.pdf_url;
        renderAnnualChart(summary);

        annualModal.hidden = false;
        document.body.style.overflow = 'hidden';
        annualClose?.focus();
    };

    const closeAnnualSummary = () => {
        if (!annualModal || annualModal.hidden) return;
        annualModal.hidden = true;
        document.body.style.overflow = '';
        annualTrigger?.focus();
        annualTrigger = null;
    };

    rows.forEach((row) => {
        row.addEventListener('click', () => openAnnualSummary(row));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openAnnualSummary(row);
            }
        });
    });

    annualClose?.addEventListener('click', closeAnnualSummary);
    annualModal?.addEventListener('click', (event) => {
        if (event.target === annualModal) {
            closeAnnualSummary();
        }
    });

    const applySearch = () => {
        const q = (input?.value || '').toLowerCase().trim();
        let visible = 0;
        rows.forEach((row) => {
            const hay = (row.dataset.search || '').toLowerCase();
            const show = q === '' || hay.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (noMatch) {
            noMatch.style.display = visible === 0 && rows.length ? '' : 'none';
        }
    };

    if (input) {
        input.addEventListener('input', applySearch);
    }

    if (monthPicker && monthToggle && monthInput && monthLabel) {
        const closeMonthPicker = () => {
            monthPicker.classList.remove('open');
            monthToggle.setAttribute('aria-expanded', 'false');
        };

        monthToggle.addEventListener('click', () => {
            const isOpen = monthPicker.classList.toggle('open');
            monthToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        monthOptions.forEach((option) => {
            option.addEventListener('click', () => {
                monthInput.value = option.dataset.value || '';
                monthLabel.textContent = option.textContent.trim();
                monthOptions.forEach((item) => item.classList.toggle('is-selected', item === option));
                closeMonthPicker();
            });
        });

        document.addEventListener('click', (event) => {
            if (!monthPicker.contains(event.target)) {
                closeMonthPicker();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMonthPicker();
                closeAnnualSummary();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAnnualSummary();
        }
    });
});
</script>
@endsection
