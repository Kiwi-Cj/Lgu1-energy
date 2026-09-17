@extends('layouts.qc-admin')
@section('title', 'Facility Load Tracking & Equipment Computation')

@section('content')
@php
    $selectedFacilityId = $selectedFacility ? $selectedFacility->id : 0;
    $ratePerKwh = $summary['rate_per_kwh'] ?? 12.00;
@endphp

@if(session('success'))
<div id="successAlert" class="alert-toast success">
    <i class="fa fa-check-circle"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div id="errorAlert" class="alert-toast error">
    <i class="fa fa-times-circle"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

<script>
window.addEventListener('DOMContentLoaded', function() {
    ['successAlert', 'errorAlert'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 300);
            }, 3500);
        }
    });
});
</script>

<style>
    /* Consistent, Highly User-Friendly QC LGU Application UI System */
    .load-tracking-page {
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .report-card-container {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(31,38,135,0.06);
        padding: 26px 28px;
        margin-bottom: 2rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        box-sizing: border-box;
        width: 100%;
    }

    /* Alert Toast */
    .alert-toast {
        position: fixed;
        top: 32px;
        right: 32px;
        z-index: 99999;
        min-width: 300px;
        max-width: 440px;
        padding: 14px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        transition: all 0.3s ease;
    }
    .alert-toast.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-toast.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Page Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        gap: 18px;
        flex-wrap: wrap;
    }
    .facility-heading {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .facility-heading-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #2563eb;
        background: linear-gradient(145deg, #eff6ff, #e0e7ff);
        border: 1px solid #dbeafe;
        font-size: 1.35rem;
        box-shadow: 0 4px 12px rgba(37,99,235,0.1);
    }
    .facility-page-title {
        margin: 0;
        color: #0f2450;
        font-size: clamp(1.4rem, 2.2vw, 1.85rem);
        font-weight: 850;
        line-height: 1.15;
        letter-spacing: -0.03em;
    }
    .facility-page-description {
        margin: 4px 0 0;
        color: #64748b;
        font-weight: 500;
        font-size: 0.88rem;
    }

    /* Facility Switcher & Actions */
    .facility-select-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8fafc;
        padding: 5px 12px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        transition: border-color 0.15s ease;
    }
    .facility-select-box:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37,99,235,0.12);
    }
    .facility-select-input {
        min-height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #1e293b;
        padding: 0 28px 0 10px;
        font-size: 0.84rem;
        font-weight: 700;
        outline: none;
        cursor: pointer;
    }
    .action-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #f8fafc;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 14px;
        font-weight: 700;
        font-size: 0.84rem;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .action-btn-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-1px);
    }

    /* Facility Information Strip Toolbar */
    .facility-toolbar {
        margin: 0 0 22px;
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .facility-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 8px;
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .facility-meta-badge.address {
        max-width: 380px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .facility-meta-badge i {
        color: #2563eb;
    }
    .facility-badge-active {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 800;
    }
    .facility-count-pill {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #2563eb;
        font-size: 0.68rem;
        font-weight: 900;
    }

    /* Facility Baseline Status Badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.73rem;
        font-weight: 800;
    }
    .status-pill.on-track { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .status-pill.warning { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .status-pill.neutral { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* 4 Standard Stat Cards */
    .facility-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }
    .stat-card {
        min-width: 0;
        padding: 18px 20px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-top: 4px solid var(--stat-accent, #2563eb);
        box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        transition: transform .22s ease, box-shadow .22s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, .07);
    }
    .stat-card.blue { --stat-accent: #2563eb; --stat-soft: #eff6ff; }
    .stat-card.indigo { --stat-accent: #6366f1; --stat-soft: #eef2ff; }
    .stat-card.emerald { --stat-accent: #16a34a; --stat-soft: #ecfdf3; }
    .stat-card.amber { --stat-accent: #ea8a00; --stat-soft: #fff8e8; }

    .facility-stat-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }
    .card-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        color: var(--stat-accent, #2563eb);
        background: var(--stat-soft, #eff6ff);
    }
    .facility-stat-label {
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .facility-stat-value {
        color: #0f172a;
        font-size: clamp(1.4rem, 1.8vw, 1.85rem);
        font-weight: 850;
        line-height: 1.1;
        letter-spacing: -.03em;
        margin-bottom: 6px;
    }
    .facility-stat-hint {
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* 2-Column Analytics Grid (Donut Chart + Simulator) */
    .summary-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(0, 1fr);
        gap: 18px;
        margin-bottom: 22px;
    }
    .insight-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .insight-card-header {
        padding: 15px 18px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .insight-card-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .insight-card-desc {
        color: #64748b;
        font-size: 0.76rem;
        margin-top: 2px;
    }

    /* Category Donut & Mini Table */
    .chart-box {
        position: relative;
        height: 200px;
        margin-bottom: 14px;
    }
    .chart-view-toggle {
        display: inline-flex;
        background: #e2e8f0;
        padding: 2px;
        border-radius: 8px;
        gap: 2px;
    }
    .chart-tab-btn {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .chart-tab-btn:hover {
        color: #1e293b;
    }
    .chart-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }
    .donut-center-info {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        max-width: 140px;
    }
    .donut-center-sub {
        font-size: 0.62rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .donut-center-val {
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin: 2px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .donut-center-pct {
        font-size: 0.72rem;
        font-weight: 800;
        color: #2563eb;
    }
    .category-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.78rem;
    }
    .category-table th {
        background: #f8fafc;
        padding: 7px 9px;
        text-align: left;
        color: #64748b;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }
    .category-table td {
        padding: 7px 9px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .cat-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.65rem;
        font-weight: 800;
        flex-shrink: 0;
    }
    .rank-badge.rank-top {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .top-consumer-tag {
        font-size: 0.62rem;
        font-weight: 800;
        color: #b45309;
        background: #fef3c7;
        padding: 1px 5px;
        border-radius: 4px;
        border: 1px solid #fde68a;
        margin-left: 4px;
    }

    /* What-If Simulator Styling */
    .sim-container {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .sim-target-bar {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .sim-target-btn {
        padding: 6px 11px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .sim-target-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
        transform: translateY(-1px);
    }
    .sim-target-btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
        box-shadow: 0 2px 6px rgba(37,99,235,0.25);
    }
    .sim-slider-row {
        background: #fff;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .sim-range {
        width: 100%;
        accent-color: #2563eb;
        cursor: pointer;
        margin: 6px 0;
    }
    .sim-quick-step-btn {
        padding: 2px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .sim-quick-step-btn:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
    .sim-results-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        background: #fff;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .sim-kpi-item {
        text-align: center;
    }
    .sim-kpi-val {
        font-size: 1.05rem;
        font-weight: 900;
        color: #2563eb;
    }
    .sim-kpi-lbl {
        font-size: 0.68rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 2px;
    }

    /* Top Consumers Progress Bars */
    .top-consumer-row {
        margin-bottom: 7px;
    }
    .top-consumer-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.74rem;
        font-weight: 700;
        margin-bottom: 3px;
    }
    .progress-bar-bg {
        width: 100%;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
    }

    /* Clean, Unified Enterprise Table (Matches QC LGU System - Walang Hati) */
    .table-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }
    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e7edf5;
        background: linear-gradient(135deg, #fbfdff 0%, #f5f8ff 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .table-counter-badge {
        display: inline-flex;
        align-items: center;
        background: #e0e7ff;
        color: #2563eb;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 999px;
    }
    .formula-badge-wrap {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.73rem;
        color: #166534;
        margin-top: 5px;
    }
    .formula-badge-wrap strong {
        color: #0f172a;
    }

    .table-responsive-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .table-responsive-wrap::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive-wrap::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .table-responsive-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .table-responsive-wrap::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .custom-table {
        width: 100%;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        text-align: left;
        font-size: 0.82rem;
    }
    .custom-table thead {
        background: #f8fafc;
    }
    .custom-table thead th {
        padding: 10px 8px;
        color: #64748b;
        font-weight: 850;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: left;
        background: #f8fafc;
        border-bottom: 1px solid #dbe5f2;
        white-space: nowrap;
    }
    .custom-table tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        vertical-align: middle;
    }
    .custom-table tbody tr {
        position: relative;
        transition: background-color 0.18s ease, box-shadow 0.18s ease;
    }
    .custom-table tbody tr:hover {
        background: #f8fbff;
        box-shadow: inset 3px 0 0 #93c5fd;
    }
    .custom-table tfoot td {
        background: #f8fafc;
        padding: 11px 8px;
        font-weight: 850;
        color: #0f2450;
        border-top: 2px solid #e2e8f0;
    }

    /* Meter Chip */
    .meter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 3px 8px;
        border-radius: 7px;
        font-size: 0.72rem;
        color: #0369a1;
        font-weight: 700;
        white-space: nowrap;
    }
    .meter-chip i {
        color: #0284c7;
    }
    .meter-chip.sub {
        background: #fdf4ff;
        border-color: #f0abfc;
        color: #a21caf;
    }
    .meter-chip.sub i {
        color: #a21caf;
    }
    .meter-chip.facility {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }
    .meter-chip.facility i {
        color: #64748b;
    }

    /* Share Progress Bar */
    .share-pill-badge {
        font-size: 0.7rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #334155;
        padding: 2px 6px;
        border-radius: 999px;
    }
    .share-track {
        width: 44px;
        height: 4px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .share-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #10b981);
    }

    /* Sortable Headers */
    .th-sortable {
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .th-sortable:hover {
        background: #eef2ff !important;
        color: #2563eb !important;
    }
    .sort-icon {
        font-size: 0.65rem;
        opacity: 0.6;
        margin-left: 4px;
    }
    .th-sortable:hover .sort-icon {
        opacity: 1;
    }

    /* High Load Power Badge */
    .high-power-pill {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 1px 5px;
        border-radius: 4px;
        background: #fff1f2;
        color: #e11d48;
        font-size: 0.64rem;
        font-weight: 800;
        border: 1px solid #fecdd3;
    }

    /* Category Badges in Table */
    .cat-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #475569;
        white-space: nowrap;
    }
    .cat-badge.hvac { background: #e0f2fe; color: #0369a1; }
    .cat-badge.lighting { background: #fef9c3; color: #a16207; }
    .cat-badge.it { background: #ede9fe; color: #6d28d9; }
    .cat-badge.pumps { background: #ffedd5; color: #c2410c; }
    .cat-badge.appliances { background: #dcfce7; color: #15803d; }
    .cat-badge.medical { background: #ffe4e6; color: #be123c; }
    .cat-badge.other { background: #f1f5f9; color: #475569; }

    /* Row Status styling */
    .custom-table tbody tr.is-inactive-row {
        background-color: #fafafa !important;
        opacity: 0.68;
    }
    .custom-table tbody tr.is-inactive-row:hover {
        opacity: 0.95;
        background-color: #f1f5f9 !important;
    }
    .status-badge-active {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 800;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        white-space: nowrap;
    }
    .status-badge-inactive {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    /* Action buttons in Table */
    .action-icon-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        display: inline-grid;
        place-items: center;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .action-icon-btn:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
        transform: scale(1.05);
    }
    .action-icon-btn.deactivate {
        color: #d97706;
    }
    .action-icon-btn.deactivate:hover {
        background: #d97706;
        color: #fff;
        border-color: #d97706;
        transform: scale(1.05);
    }
    .action-icon-btn.activate {
        color: #16a34a;
    }
    .action-icon-btn.activate:hover {
        background: #16a34a;
        color: #fff;
        border-color: #16a34a;
        transform: scale(1.05);
    }
    .action-icon-btn.delete:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
        transform: scale(1.05);
    }

    .quick-add-btn {
        background: #10b981;
        color: #fff;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.84rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.15s ease;
    }
    .quick-add-btn:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16,185,129,0.25);
    }

    .filter-pills-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        padding: 10px 16px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
    }
    .filter-pill {
        padding: 4px 11px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .filter-pill:hover,
    .filter-pill.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
        box-shadow: 0 2px 8px rgba(37,99,235,0.2);
    }

    /* Modal Styles - Adopted from Maintenance Modal Standard */
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.6);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        padding: 16px;
    }
    .modal-overlay.is-active {
        display: flex;
    }
    .modal-card {
        width: min(760px, calc(100vw - 24px));
        background: #fff;
        border: 1px solid #dbe5f2;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15,23,42,.30);
        max-height: min(88vh, 840px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-card > form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    .modal-head {
        flex: 0 0 auto;
        padding: 24px 68px 22px 32px;
        background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
        border-bottom: 1px solid #dbe5f2;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
    }
    .modal-head-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg,#2563eb,#6366f1);
        color: #fff;
        box-shadow: 0 8px 18px rgba(37,99,235,.20);
        font-size: 1.2rem;
        margin-left: 2px;
    }
    .modal-head-heading {
        min-width: 0;
    }
    .modal-head h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -.02em;
    }
    .modal-head-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .84rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .modal-close {
        position: absolute;
        z-index: 5;
        top: 18px;
        right: 18px;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.9);
        border: 1px solid #dbe5f2;
        border-radius: 11px;
        font-size: 1.1rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .modal-close:hover {
        color: #e11d48;
        background: #fff1f2;
        border-color: #fecdd3;
    }
    .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 22px 32px 20px;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-body::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 999px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .modal-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 5px 0 -2px;
        color: #475569;
        font-size: .73rem;
        font-weight: 850;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .modal-section-title i {
        color: #2563eb;
    }
    .modal-foot {
        flex: 0 0 auto;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        border-top: 1px solid #e2e8f0;
        background: rgba(255,255,255,.98);
        backdrop-filter: blur(8px);
        margin: 0;
    }
    .action-btn-secondary {
        border: none;
        border-radius: 9px;
        padding: 10px 16px;
        font-weight: 700;
        cursor: pointer;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .action-btn-secondary:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .action-btn-primary {
        background: #2563eb;
        color: #fff;
        min-width: 170px;
        border: none;
        border-radius: 9px;
        padding: 10px 18px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        box-shadow: 0 7px 16px rgba(37,99,235,.20);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .action-btn-primary:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        box-shadow: 0 9px 20px rgba(37,99,235,.30);
    }
    .action-btn-danger {
        padding: 10px 18px;
        border-radius: 9px;
        font-weight: 700;
        font-size: 0.85rem;
        border: none;
        background: #dc2626;
        color: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
    }
    .action-btn-danger:hover {
        background: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.35);
    }
    .delete-modal-warning-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 12px auto;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .form-required {
        font-size: .58rem;
        font-weight: 850;
        letter-spacing: .02em;
        text-transform: none;
        color: #dc2626;
    }
    .form-optional {
        font-size: .58rem;
        font-weight: 850;
        letter-spacing: .02em;
        text-transform: none;
        color: #94a3b8;
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 11px 12px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        font-size: 0.92rem;
        background: #fff;
        color: #1e293b;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.24);
    }
    .calc-preview-strip {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 10px 14px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        text-align: center;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1400px) {
        .facility-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .summary-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    @media (max-width: 768px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .facility-stat-grid {
            grid-template-columns: 1fr;
        }
        .sim-results-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .report-card-container {
            padding: 18px;
        }
    }

    /* Table Element Classes for Light/Dark Mode Contrast */
    .eq-name {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.9rem;
        line-height: 1.25;
    }
    .eq-name.is-inactive {
        color: #64748b !important;
        text-decoration: line-through;
    }
    .eq-connected-kw {
        font-size: 0.92rem;
        font-weight: 850;
        color: #2563eb;
    }
    .eq-connected-kw.is-inactive {
        color: #64748b !important;
    }
    .eq-duty-hours {
        font-size: 0.84rem;
        font-weight: 800;
        color: #1e293b;
    }
    .eq-energy-kwh {
        font-size: 0.92rem;
        font-weight: 850;
        color: #0f172a;
    }
    .eq-energy-kwh.is-inactive {
        color: #64748b !important;
    }
    .eq-monthly-cost {
        font-size: 0.96rem;
        font-weight: 900;
        color: #059669;
    }
    .eq-monthly-cost.is-inactive {
        color: #64748b !important;
    }
    .eq-subtext {
        color: #64748b;
    }
    .eq-section-title {
        color: #1e293b;
    }
    .sim-target-watts-badge {
        font-size: 0.74rem;
        font-weight: 800;
        color: #2563eb;
        background: #eff6ff;
        padding: 3px 9px;
        border-radius: 6px;
        border: 1px solid #bfdbfe;
    }
    .sim-tip-box {
        font-size: 0.75rem;
        color: #0f766e;
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        padding: 8px 12px;
        border-radius: 8px;
        line-height: 1.45;
    }
    .top-consumer-title {
        font-size: 0.8rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .preset-box {
        background: #f0fdf4;
        border: 1px dashed #86efac;
        padding: 12px 14px;
        border-radius: 12px;
    }
    .preset-box label {
        color: #047857;
    }

    /* Dark Mode Theme Support (matches qc-admin layout) */
    body.dark-mode .report-card-container { background: #111827; border-color: #334155; }
    body.dark-mode .facility-heading-icon {
        background: linear-gradient(135deg, #1e3a8a, #1e293b) !important;
        border-color: #3b82f6 !important;
        color: #93c5fd !important;
        box-shadow: 0 4px 14px rgba(30, 58, 138, 0.4) !important;
    }
    body.dark-mode .status-pill.warning {
        background: #451a03 !important;
        border-color: #9a3412 !important;
        color: #fdba74 !important;
    }
    body.dark-mode .status-pill.on-track {
        background: #052e16 !important;
        border-color: #166534 !important;
        color: #86efac !important;
    }
    body.dark-mode .status-pill.neutral {
        background: #1e293b !important;
        border-color: #475569 !important;
        color: #cbd5e1 !important;
    }
    body.dark-mode .facility-page-title { color: #f8fafc; }
    body.dark-mode .facility-page-description { color: #94a3b8; }
    body.dark-mode .facility-toolbar { background: #111827; border-color: #334155; }
    body.dark-mode .facility-toolbar > div > div:first-child { color: #f8fafc !important; }
    body.dark-mode .facility-meta-badge { background: #1f2937; border-color: #374151; color: #e2e8f0; }
    body.dark-mode .facility-meta-badge strong { color: #f8fafc; }
    body.dark-mode .facility-badge-active { background: #1f2937; border-color: #374151; color: #cbd5e1; }
    body.dark-mode .facility-badge-active strong { color: #f8fafc; }
    body.dark-mode .facility-count-pill { background: #172554; color: #93c5fd; }
    body.dark-mode .stat-card { background: #1f2937; border-color: #374151; }
    body.dark-mode .facility-stat-value { color: #f8fafc; }
    body.dark-mode .facility-stat-label, body.dark-mode .facility-stat-hint { color: #94a3b8; }
    body.dark-mode .insight-card { background: #111827; border-color: #334155; }
    body.dark-mode .insight-card-header { background: #1f2937; border-color: #374151; }
    body.dark-mode .insight-card-title { color: #f8fafc; }
    body.dark-mode .insight-card-desc { color: #94a3b8; }
    body.dark-mode .table-card { background: #111827; border-color: #334155; }
    body.dark-mode .table-card-header { background: #1f2937; border-color: #334155; }
    body.dark-mode .formula-badge-wrap { background: #0f2a22; border-color: #166534; color: #86efac; }
    body.dark-mode .formula-badge-wrap strong { color: #f8fafc; }
    body.dark-mode .table-counter-badge { background: #1e293b; color: #93c5fd; }
    body.dark-mode .custom-table { background: #111827; }
    body.dark-mode .custom-table thead th { background: #1f2937; border-color: #374151; color: #93c5fd; }
    body.dark-mode .custom-table tbody td { border-color: #374151; color: #e2e8f0; }
    body.dark-mode .custom-table tbody tr:hover { background: #1f2937; box-shadow: inset 3px 0 0 #3b82f6; }
    body.dark-mode .custom-table tfoot td { background: #1f2937; border-color: #374151; color: #f8fafc; }
    body.dark-mode .meter-chip { background: #0c4a6e; border-color: #0284c7; color: #bae6fd; }
    body.dark-mode .meter-chip i { color: #38bdf8; }
    body.dark-mode .meter-chip.sub { background: #4a044e; border-color: #c026d3; color: #f5d0fe; }
    body.dark-mode .meter-chip.sub i { color: #e879f9; }
    body.dark-mode .meter-chip.facility { background: #1e293b; border-color: #475569; color: #cbd5e1; }
    body.dark-mode .meter-chip.facility i { color: #94a3b8; }
    body.dark-mode .share-pill-badge { background: #1f2937; color: #e2e8f0; }
    body.dark-mode .share-track { background: #374151; }
    body.dark-mode .th-sortable:hover { background: #172554 !important; color: #93c5fd !important; }
    body.dark-mode #ltQuickSearch { background: #1f2937 !important; border-color: #374151 !important; color: #f8fafc !important; }
    body.dark-mode .facility-select-box { background: #1f2937; border-color: #374151; }
    body.dark-mode .facility-select-box label { color: #94a3b8; }
    body.dark-mode .facility-select-input { background: #1f2937; border-color: #374151; color: #f8fafc; }
    body.dark-mode .action-btn-secondary { background: #1f2937; color: #cbd5e1; border: 1px solid #374151; }
    body.dark-mode .action-btn-secondary:hover { background: #374151; color: #f8fafc; }

    /* Table Typography & States in Dark Mode */
    body.dark-mode .eq-name { color: #f8fafc !important; }
    body.dark-mode .eq-name.is-inactive { color: #94a3b8 !important; }
    body.dark-mode .eq-connected-kw { color: #60a5fa !important; }
    body.dark-mode .eq-connected-kw.is-inactive { color: #94a3b8 !important; }
    body.dark-mode .eq-duty-hours { color: #f8fafc !important; }
    body.dark-mode .eq-energy-kwh { color: #f8fafc !important; }
    body.dark-mode .eq-energy-kwh.is-inactive { color: #94a3b8 !important; }
    body.dark-mode .eq-monthly-cost { color: #34d399 !important; }
    body.dark-mode .eq-monthly-cost.is-inactive { color: #94a3b8 !important; }
    body.dark-mode .eq-subtext { color: #94a3b8 !important; }
    body.dark-mode .eq-section-title { color: #f8fafc !important; }
    body.dark-mode .top-consumer-title { color: #f8fafc !important; }
    body.dark-mode .top-consumer-title small { color: #94a3b8 !important; }
    body.dark-mode .top-consumer-header { color: #e2e8f0; }
    body.dark-mode .top-consumer-header small { color: #94a3b8 !important; }
    body.dark-mode .progress-bar-bg { background: #374151 !important; }
    body.dark-mode #noRecordsRow strong { color: #94a3b8 !important; }

    /* Filter Bar & Pills in Dark Mode */
    body.dark-mode .filter-pills-bar { background: #111827 !important; border-color: #334155 !important; }
    body.dark-mode .filter-pills-bar span { color: #94a3b8 !important; }
    body.dark-mode .filter-pill { background: #1f2937 !important; border-color: #374151 !important; color: #cbd5e1 !important; }
    body.dark-mode .filter-pill:hover,
    body.dark-mode .filter-pill.active { background: #2563eb !important; border-color: #3b82f6 !important; color: #ffffff !important; box-shadow: 0 2px 8px rgba(37,99,235,0.4) !important; }

    /* Category Badges in Dark Mode (High Contrast & Distinct Hues) */
    body.dark-mode .cat-badge { background: #1f2937 !important; color: #cbd5e1 !important; border: 1px solid #374151 !important; }
    body.dark-mode .cat-badge.hvac { background: #082f49 !important; color: #7dd3fc !important; border-color: #0369a1 !important; }
    body.dark-mode .cat-badge.lighting { background: #422006 !important; color: #fde047 !important; border-color: #854d0e !important; }
    body.dark-mode .cat-badge.it { background: #2e1065 !important; color: #c4b5fd !important; border-color: #6d28d9 !important; }
    body.dark-mode .cat-badge.pumps { background: #431407 !important; color: #fdba74 !important; border-color: #c2410c !important; }
    body.dark-mode .cat-badge.appliances { background: #052e16 !important; color: #86efac !important; border-color: #15803d !important; }
    body.dark-mode .cat-badge.medical { background: #4c0519 !important; color: #fda4af !important; border-color: #be123c !important; }
    body.dark-mode .cat-badge.other { background: #1e293b !important; color: #cbd5e1 !important; border-color: #475569 !important; }

    /* Other Badges & Action Buttons in Dark Mode */
    body.dark-mode .high-power-pill { background: #4c0519 !important; color: #fda4af !important; border: 1px solid #be123c !important; }
    body.dark-mode .status-badge-active { background: #052e16 !important; color: #86efac !important; border: 1px solid #15803d !important; }
    body.dark-mode .status-badge-inactive { background: #1e293b !important; color: #94a3b8 !important; border: 1px solid #475569 !important; }
    body.dark-mode .action-icon-btn { background: #1f2937 !important; border-color: #374151 !important; color: #cbd5e1 !important; }
    body.dark-mode .action-icon-btn:hover { background: #2563eb !important; border-color: #2563eb !important; color: #fff !important; }
    body.dark-mode .action-icon-btn.deactivate { color: #fbbf24 !important; }
    body.dark-mode .action-icon-btn.deactivate:hover { background: #d97706 !important; border-color: #d97706 !important; color: #fff !important; }
    body.dark-mode .action-icon-btn.activate { color: #4ade80 !important; }
    body.dark-mode .action-icon-btn.activate:hover { background: #16a34a !important; border-color: #16a34a !important; color: #fff !important; }
    body.dark-mode .custom-table tbody tr.is-inactive-row { background-color: rgba(15, 23, 42, 0.5) !important; opacity: 0.65; }
    body.dark-mode .custom-table tbody tr.is-inactive-row:hover { background-color: #1f2937 !important; opacity: 0.95; }

    /* Visual Analytics & What-If Simulator in Dark Mode */
    body.dark-mode .chart-view-toggle { background: #1f2937; }
    body.dark-mode .chart-tab-btn { color: #94a3b8; }
    body.dark-mode .chart-tab-btn:hover { color: #f8fafc; }
    body.dark-mode .chart-tab-btn.active { background: #111827; color: #93c5fd; }
    body.dark-mode .donut-center-sub { color: #94a3b8; }
    body.dark-mode .donut-center-val { color: #f8fafc; }
    body.dark-mode .category-table th { background: #1f2937; color: #94a3b8; border-color: #374151; }
    body.dark-mode .category-table td { color: #e2e8f0; border-color: #374151; }
    body.dark-mode .rank-badge { background: #1e293b; color: #94a3b8; }
    body.dark-mode .rank-badge.rank-top { background: #78350f; color: #fde68a; border-color: #92400e; }
    body.dark-mode .top-consumer-tag { background: #78350f; color: #fde68a; border-color: #92400e; }
    body.dark-mode .sim-container { background: #1f2937; border-color: #374151; }
    body.dark-mode .sim-target-watts-badge { background: #1e3a8a; border-color: #3b82f6; color: #93c5fd; }
    body.dark-mode .sim-target-btn { background: #111827; border-color: #374151; color: #cbd5e1; }
    body.dark-mode .sim-target-btn:hover { background: #374151; color: #f8fafc; }
    body.dark-mode .sim-target-btn.active { background: #2563eb; border-color: #3b82f6; color: #ffffff; }
    body.dark-mode .sim-slider-row { background: #111827; border-color: #374151; }
    body.dark-mode .sim-slider-row span { color: #94a3b8; }
    body.dark-mode .sim-quick-step-btn { background: #1f2937; border-color: #374151; color: #cbd5e1; }
    body.dark-mode .sim-quick-step-btn:hover { background: #2563eb; border-color: #3b82f6; color: #ffffff; }
    body.dark-mode .sim-results-grid { background: #111827; border-color: #374151; }
    body.dark-mode .sim-kpi-lbl { color: #94a3b8; }
    body.dark-mode .sim-tip-box { background: #042f2e; border-color: #0f766e; color: #99f6e4; }
    body.dark-mode .sim-tip-box i { color: #2dd4bf; }

    /* Modals in Dark Mode */
    body.dark-mode .modal-card { background: #111827; border-color: #374151; color: #f8fafc; }
    body.dark-mode .modal-head { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); border-color: #374151; }
    body.dark-mode .modal-head h3 { color: #f8fafc; }
    body.dark-mode .modal-head-subtitle { color: #94a3b8; }
    body.dark-mode .modal-close { background: #1f2937; border-color: #374151; color: #cbd5e1; }
    body.dark-mode .modal-close:hover { background: #4c0519; color: #fda4af; border-color: #be123c; }
    body.dark-mode .modal-section-title { color: #93c5fd; border-color: #374151; }
    body.dark-mode .modal-foot { background: #1f2937; border-color: #374151; }
    body.dark-mode .form-group label { color: #cbd5e1; }
    body.dark-mode .form-input,
    body.dark-mode .form-select,
    body.dark-mode .form-textarea { background: #1f2937; border-color: #374151; color: #f8fafc; }
    body.dark-mode .form-input:focus,
    body.dark-mode .form-select:focus,
    body.dark-mode .form-textarea:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25); }
    body.dark-mode .preset-box { background: #052e16 !important; border-color: #15803d !important; }
    body.dark-mode .preset-box label { color: #86efac !important; }
    body.dark-mode .calc-preview-strip { background: #1f2937; border-color: #374151; }
    body.dark-mode .calc-preview-strip > div > div:last-child { color: #94a3b8 !important; }
    body.dark-mode #statusModalTitle { color: #f8fafc !important; }
    body.dark-mode #statusEquipmentName { color: #f8fafc !important; }
    body.dark-mode #statusModalNotice { background: #422006 !important; border-color: #854d0e !important; color: #fde047 !important; }
    body.dark-mode #statusModalNoticeIcon { color: #facc15 !important; }
    body.dark-mode #statusModalIconBox { background: #451a03 !important; border-color: #78350f !important; color: #f59e0b !important; }
</style>

<div class="load-tracking-page">
    <div class="report-card-container">

        <!-- 1. Standard QC LGU Dashboard Header -->
        <div class="dashboard-header">
            <div class="facility-heading">
                <span class="facility-heading-icon" aria-hidden="true">
                    <i class="fa-solid fa-plug-circle-bolt"></i>
                </span>
                <div>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <h1 class="facility-page-title">Facility Load Tracking</h1>
                        @if($selectedFacility && is_numeric($selectedFacility->baseline_kwh))
                            @if($summary['baseline_variance'] <= 0)
                                <span class="status-pill on-track" title="Current equipment load is within target baseline">
                                    <i class="fa-solid fa-circle-check"></i> Within Baseline
                                </span>
                            @else
                                <span class="status-pill warning" title="Current load schedule exceeds monthly baseline">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Over Baseline (+{{ number_format($summary['baseline_variance'], 0) }} kWh)
                                </span>
                            @endif
                        @endif
                    </div>
                    <p class="facility-page-description">Track connected equipment loads, compute daily & monthly energy consumption, and compare against baselines.</p>
                </div>
            </div>

            <!-- Header Actions: Switch Facility & Export -->
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                @if($facilities->count() > 0)
                <div class="facility-select-box">
                    <label for="ltFacilitySwitcher" style="font-size:0.75rem;font-weight:700;color:#475569;text-transform:uppercase;margin:0;">
                        <i class="fa-solid fa-building" style="color:#2563eb;"></i> Facility:
                    </label>
                    <select id="ltFacilitySwitcher" class="facility-select-input" onchange="window.location.href='{{ route('modules.load-tracking.index') }}?facility_id=' + this.value;">
                        @foreach($facilities as $fac)
                            <option value="{{ $fac->id }}" {{ $selectedFacilityId === $fac->id ? 'selected' : '' }}>
                                {{ $fac->name }} ({{ $fac->type ?? 'Facility' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedFacility)
                    <a href="{{ route('modules.load-tracking.export', $selectedFacility->id) }}" class="action-btn-secondary" data-secure-download title="Export Equipment Load Schedule as CSV">
                        <i class="fa-solid fa-file-csv" style="color:#059669;"></i> Export CSV
                    </a>
                @endif
                @endif
            </div>
        </div>

        @if(! $selectedFacility)
            <div style="text-align:center; padding:48px 20px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:16px;">
                <i class="fa-solid fa-building-circle-exclamation" style="font-size:3rem; color:#94a3b8; margin-bottom:12px;"></i>
                <h3 style="margin:0 0 6px; color:#1e293b;">No Facilities Accessible</h3>
                <p style="color:#64748b; margin:0;">Please register or assign a facility first in the Facility Registry.</p>
            </div>
        @else

            <!-- 2. Facility Metadata Strip Toolbar -->
            <div class="facility-toolbar">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="font-size:1.05rem; font-weight:850; color:#0f2450; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-building" style="color:#2563eb;"></i>
                        {{ $selectedFacility->name }}
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="facility-meta-badge address" title="{{ $selectedFacility->address ?: 'Quezon City' }}">
                            <i class="fa-solid fa-location-dot"></i> {{ Str::limit($selectedFacility->address ?: 'Quezon City', 36) }}
                        </span>
                        <span class="facility-meta-badge">
                            <i class="fa-solid fa-layer-group"></i> {{ $selectedFacility->type ?: 'Government Office' }}
                        </span>
                        <span class="facility-meta-badge">
                            <i class="fa-solid fa-chart-simple"></i> Baseline: <strong>{{ is_numeric($selectedFacility->baseline_kwh) ? number_format((float)$selectedFacility->baseline_kwh, 2) . ' kWh' : 'Not Set' }}</strong>
                        </span>
                        <span class="facility-meta-badge">
                            <i class="fa-solid fa-peso-sign"></i> Rate: <strong>₱{{ number_format($ratePerKwh, 2) }}/kWh</strong>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="facility-badge-active">
                        <i class="fa-solid fa-box-archive"></i> Total Registered: <strong>{{ $summary['total_items'] }} loads</strong>
                        <span class="facility-count-pill">{{ number_format($summary['total_units']) }} units</span>
                    </span>
                </div>
            </div>

            <!-- 3. 4 Standard Stat KPI Cards with Informative Subtext -->
            <div class="facility-stat-grid">
                <!-- Card 1: Connected Load -->
                <div class="stat-card blue" title="Total rated power wattage of all equipment registered at this facility">
                    <div class="facility-stat-topline">
                        <span class="facility-stat-label">
                            <i class="fa-solid fa-bolt" style="color:#2563eb;"></i> Connected Load
                        </span>
                        <div class="card-icon-box"><i class="fa-solid fa-bolt-lightning"></i></div>
                    </div>
                    <div class="facility-stat-value">
                        {{ number_format($summary['total_connected_kw'], 2) }}
                        <small style="font-size:0.95rem; font-weight:700; color:#64748b;">kW</small>
                    </div>
                    <div class="facility-stat-hint">
                        <i class="fa-solid fa-calculator" style="color:#2563eb;"></i> {{ number_format($summary['total_connected_watts'], 0) }} Watts across {{ number_format($summary['total_units']) }} units
                    </div>
                </div>

                <!-- Card 2: Daily Consumption -->
                <div class="stat-card indigo" title="Calculated energy consumption per 24-hour cycle based on duty hours">
                    <div class="facility-stat-topline">
                        <span class="facility-stat-label">
                            <i class="fa-solid fa-clock" style="color:#6366f1;"></i> Daily Consumption
                        </span>
                        <div class="card-icon-box"><i class="fa-solid fa-calendar-day"></i></div>
                    </div>
                    <div class="facility-stat-value">
                        {{ number_format($summary['total_daily_kwh'], 2) }}
                        <small style="font-size:0.95rem; font-weight:700; color:#64748b;">kWh/day</small>
                    </div>
                    <div class="facility-stat-hint">
                        <i class="fa-solid fa-coins" style="color:#6366f1;"></i> ₱{{ number_format($summary['total_daily_kwh'] * $ratePerKwh, 2) }} estimated daily cost
                    </div>
                </div>

                <!-- Card 3: Monthly Consumption -->
                <div class="stat-card emerald" title="Projected monthly consumption compared against the facility baseline">
                    <div class="facility-stat-topline">
                        <span class="facility-stat-label">
                            <i class="fa-solid fa-chart-line" style="color:#16a34a;"></i> Monthly Consumption
                        </span>
                        <div class="card-icon-box"><i class="fa-solid fa-calendar-alt"></i></div>
                    </div>
                    <div class="facility-stat-value">
                        {{ number_format($summary['total_monthly_kwh'], 2) }}
                        <small style="font-size:0.95rem; font-weight:700; color:#64748b;">kWh/mo</small>
                    </div>
                    <div class="facility-stat-hint">
                        @if($summary['baseline_kwh'])
                            @if($summary['baseline_variance'] <= 0)
                                <span style="color:#16a34a; font-weight:800;"><i class="fa-solid fa-check-circle"></i> {{ number_format(abs($summary['baseline_variance']), 2) }} kWh below baseline</span>
                            @else
                                <span style="color:#ea580c; font-weight:800;"><i class="fa-solid fa-arrow-trend-up"></i> +{{ number_format($summary['baseline_variance'], 2) }} kWh over baseline</span>
                            @endif
                        @else
                            <span>Sum of equipment duty cycles</span>
                        @endif
                    </div>
                </div>

                <!-- Card 4: Est. Monthly Cost -->
                <div class="stat-card amber" title="Estimated monthly electricity bill based on applied electricity tariff">
                    <div class="facility-stat-topline">
                        <span class="facility-stat-label">
                            <i class="fa-solid fa-receipt" style="color:#ea8a00;"></i> Est. Monthly Cost
                        </span>
                        <div class="card-icon-box"><i class="fa-solid fa-peso-sign"></i></div>
                    </div>
                    <div class="facility-stat-value" style="color:#b45309;">
                        ₱{{ number_format($summary['total_monthly_cost'], 2) }}
                    </div>
                    <div class="facility-stat-hint">
                        <i class="fa-solid fa-tag" style="color:#ea8a00;"></i> Applied: ₱{{ number_format($ratePerKwh, 2) }}/kWh (QC Commercial Rate)
                    </div>
                </div>
            </div>

            <!-- 4. Visual Analytics & What-If Simulator (2 Columns) -->
            <div class="summary-grid">
                <!-- Left: Donut Chart: Load Breakdown by Category -->
                <div class="insight-card">
                    <div class="insight-card-header">
                        <div>
                            <h3 class="insight-card-title"><i class="fa-solid fa-chart-pie" style="color:#2563eb;"></i> Load Distribution by Category</h3>
                            <div class="insight-card-desc">Share of monthly kWh consumption & ranking by equipment type</div>
                        </div>
                        @if(!empty($categoryBreakdown))
                        <div class="chart-view-toggle">
                            <button type="button" id="btnToggleDonut" class="chart-tab-btn active" onclick="switchCategoryChart('donut')">
                                <i class="fa-solid fa-chart-pie"></i> Donut
                            </button>
                            <button type="button" id="btnToggleBar" class="chart-tab-btn" onclick="switchCategoryChart('bar')">
                                <i class="fa-solid fa-chart-simple"></i> Ranking
                            </button>
                        </div>
                        @endif
                    </div>

                    <div style="padding:18px;">
                        @if(empty($categoryBreakdown))
                            <div style="text-align:center; padding:32px; color:#94a3b8;">
                                <i class="fa-solid fa-chart-pie" style="font-size:2rem; margin-bottom:8px;"></i>
                                <p style="margin:0;">No equipment registered yet. Click <strong>+ Add Equipment</strong> below.</p>
                            </div>
                        @else
                            <div class="chart-box" id="categoryChartBox">
                                <canvas id="categoryDonutChart"></canvas>
                                <div class="donut-center-info" id="donutCenterInfo">
                                    <div class="donut-center-sub">Top Consumer</div>
                                    <div class="donut-center-val">{{ $categoryBreakdown[0]['category'] ?? 'N/A' }}</div>
                                    <div class="donut-center-pct">{{ $categoryBreakdown[0]['percentage'] ?? 0 }}% of load</div>
                                </div>
                            </div>

                            <table class="category-table">
                                <thead>
                                    <tr>
                                        <th>Rank & Category</th>
                                        <th style="text-align:right;">Connected</th>
                                        <th style="text-align:right;">Monthly</th>
                                        <th style="text-align:right;">Est. Cost</th>
                                        <th style="text-align:right;">Share</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categoryBreakdown as $idx => $cat)
                                        @php
                                            $palette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
                                            $color = $palette[$idx % count($palette)];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                                    <span class="rank-badge {{ $idx === 0 ? 'rank-top' : '' }}">#{{ $idx + 1 }}</span>
                                                    <span class="cat-dot" style="background: {{ $color }};"></span>
                                                    <strong>{{ $cat['category'] }}</strong>
                                                    <small style="color:#94a3b8;">({{ $cat['units'] }}u)</small>
                                                    @if($idx === 0)
                                                        <span class="top-consumer-tag"><i class="fa-solid fa-crown" style="font-size:0.58rem;"></i> Highest</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align:right;">{{ number_format($cat['connected_kw'], 2) }} kW</td>
                                            <td style="text-align:right;">{{ number_format($cat['monthly_kwh'], 2) }} kWh</td>
                                            <td style="text-align:right; font-weight:700; color:#059669;">₱{{ number_format($cat['monthly_cost'], 2) }}</td>
                                            <td style="text-align:right;"><strong>{{ $cat['percentage'] }}%</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- Right: Energy Savings Simulator ("What-If") -->
                <div class="insight-card">
                    <div class="insight-card-header">
                        <div>
                            <h3 class="insight-card-title"><i class="fa-solid fa-wand-magic-sparkles" style="color:#10b981;"></i> Energy Savings Simulator ("What-If")</h3>
                            <div class="insight-card-desc">Simulate operational adjustments to reduce facility electricity costs</div>
                        </div>
                    </div>

                    <div style="padding:18px;">
                        <div class="sim-container">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <span class="eq-section-title" style="font-size:0.76rem; font-weight:800;">
                                    Select Target Load Category:
                                </span>
                                <span id="simTargetWattsBadge" class="sim-target-watts-badge">
                                    Target Load: 0.00 kW
                                </span>
                            </div>

                            <!-- Target Category Selector Buttons -->
                            <div class="sim-target-bar">
                                <button type="button" class="sim-target-btn active" onclick="setSimTarget('cooling_lighting', this)">
                                    <i class="fa-solid fa-snowflake"></i> Cooling + Lighting
                                </button>
                                <button type="button" class="sim-target-btn" onclick="setSimTarget('cooling', this)">
                                    <i class="fa-solid fa-fan"></i> Cooling Only
                                </button>
                                <button type="button" class="sim-target-btn" onclick="setSimTarget('lighting', this)">
                                    <i class="fa-solid fa-lightbulb"></i> Lighting Only
                                </button>
                                <button type="button" class="sim-target-btn" onclick="setSimTarget('all', this)">
                                    <i class="fa-solid fa-bolt"></i> All Loads
                                </button>
                            </div>

                            <div class="sim-slider-row">
                                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.72rem; color:#64748b; font-weight:700;">
                                    <span>Operating Hours Reduction:</span>
                                    <strong id="ltReductionDisplay" style="color:#2563eb; font-size:0.9rem;">1.0 hour/day</strong>
                                </div>
                                <input type="range" id="ltReductionSlider" class="sim-range" min="0.5" max="4.0" step="0.5" value="1.0" oninput="updateSimulation(this.value)">
                                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.68rem; color:#94a3b8; font-weight:700;">
                                    <button type="button" class="sim-quick-step-btn" onclick="setSliderVal(0.5)">0.5 hr</button>
                                    <button type="button" class="sim-quick-step-btn" onclick="setSliderVal(1.0)">1.0 hr</button>
                                    <button type="button" class="sim-quick-step-btn" onclick="setSliderVal(2.0)">2.0 hrs</button>
                                    <button type="button" class="sim-quick-step-btn" onclick="setSliderVal(3.0)">3.0 hrs</button>
                                    <button type="button" class="sim-quick-step-btn" onclick="setSliderVal(4.0)">4.0 hrs</button>
                                </div>
                            </div>

                            <!-- Live Calculated Savings Strip -->
                            <div class="sim-results-grid">
                                <div class="sim-kpi-item">
                                    <div class="sim-kpi-val" id="simDailyKwhSaved">0.00</div>
                                    <div class="sim-kpi-lbl">Daily kWh</div>
                                </div>
                                <div class="sim-kpi-item">
                                    <div class="sim-kpi-val" id="simMonthlyKwhSaved">0.00</div>
                                    <div class="sim-kpi-lbl">Monthly kWh</div>
                                </div>
                                <div class="sim-kpi-item">
                                    <div class="sim-kpi-val" id="simMonthlyPesosSaved">₱0.00</div>
                                    <div class="sim-kpi-lbl">Monthly ₱</div>
                                </div>
                                <div class="sim-kpi-item">
                                    <div class="sim-kpi-val" id="simMonthlyPctSaved" style="color:#059669;">0.0%</div>
                                    <div class="sim-kpi-lbl">Bill Saved</div>
                                </div>
                            </div>

                            <!-- Friendly Actionable Tip -->
                            <div id="simActionTip" class="sim-tip-box">
                                <i class="fa-solid fa-lightbulb" style="color:#0d9488;"></i>
                                <strong>Energy Tip:</strong> Reducing non-essential HVAC & lighting by 1.0 hour/day creates direct budgetary relief without impacting operational efficiency.
                            </div>
                        </div>

                        <!-- Top Consuming Equipment List -->
                        <div style="margin-top:16px;">
                            <div class="top-consumer-title">
                                <span><i class="fa-solid fa-arrow-down-wide-short" style="color:#2563eb;"></i> Top Consuming Equipment</span>
                                <small style="color:#64748b; font-weight:600;">Ranked by monthly kWh</small>
                            </div>
                            @forelse($topConsumers as $top)
                                <div class="top-consumer-row">
                                    <div class="top-consumer-header">
                                        <span>{{ $top['name'] }} <small style="color:#64748b;">({{ $top['category'] }})</small></span>
                                        <span style="color:#2563eb;">{{ number_format($top['monthly_kwh'], 1) }} kWh (₱{{ number_format($top['monthly_cost'], 0) }}) &bull; {{ $top['percentage'] }}%</span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" style="width: {{ max(5, min(100, $top['percentage'])) }}%;"></div>
                                    </div>
                                </div>
                            @empty
                                <div style="font-size:0.75rem; color:#94a3b8;">No equipment data to rank.</div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>

            <!-- 5. Enhanced Equipment Inventory & Computation Table -->
            <div class="table-card" id="equipmentTableCard">
                <div class="table-card-header">
                    <div>
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <h3 class="eq-section-title" style="margin:0; font-size:1.05rem; font-weight:850; display:flex; align-items:center; gap:8px;">
                                <i class="fa-solid fa-list-check" style="color:#2563eb;"></i> Equipment Load Schedule & Energy Computations
                            </h3>
                            <span id="equipmentCountBadge" class="table-counter-badge">
                                {{ $equipments->count() }} item{{ $equipments->count() !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="formula-badge-wrap">
                            <i class="fa-solid fa-calculator" style="color:#2563eb;"></i>
                            <span>Formula: <strong>(Watts &times; Qty &times; Hours/Day &times; Days/Month) &divide; 1,000 = Monthly kWh</strong> &bull; Cost: <strong>kWh &times; &#8369;{{ number_format($ratePerKwh, 2) }}</strong></span>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <!-- Quick Search with Instant Clear Button -->
                        <div style="position:relative; display:flex; align-items:center;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; color:#94a3b8; font-size:0.8rem;"></i>
                            <input type="text" id="ltQuickSearch" placeholder="Quick filter equipment..." oninput="quickFilterTable(this.value)" value="{{ $search }}" style="background:#fff; border:1px solid #cbd5e1; border-radius:10px; padding:8px 30px 8px 34px; font-size:0.82rem; outline:none; width:220px; transition:border-color 0.15s ease;">
                            <button type="button" id="ltSearchClearBtn" onclick="clearQuickSearch()" style="position:absolute; right:10px; background:transparent; border:none; color:#94a3b8; cursor:pointer; font-size:0.85rem; display:none;">
                                &times;
                            </button>
                        </div>

                        @if($canManage)
                            <button type="button" class="quick-add-btn" onclick="openAddModal()">
                                <i class="fa-solid fa-plus"></i> Add Equipment
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filter Pills Bars: Status & Category -->
                <div class="filter-pills-bar">
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-right: 4px;">
                            <i class="fa-solid fa-filter" style="color: #2563eb;"></i> Status:
                        </span>
                        <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => $categoryFilter, 'status' => 'all', 'search' => $search]) }}"
                           class="filter-pill {{ $statusFilter === 'all' || empty($statusFilter) ? 'active' : '' }}">
                            <i class="fa-solid fa-list"></i> All Status ({{ $summary['total_items'] }})
                        </a>
                        <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => $categoryFilter, 'status' => 'active', 'search' => $search]) }}"
                           class="filter-pill {{ $statusFilter === 'active' ? 'active' : '' }}" style="{{ $statusFilter === 'active' ? 'background:#16a34a; border-color:#16a34a;' : '' }}">
                            <i class="fa-solid fa-circle-check" style="color: {{ $statusFilter === 'active' ? '#fff' : '#16a34a' }};"></i> Active ({{ $summary['active_items'] }})
                        </a>
                        <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => $categoryFilter, 'status' => 'inactive', 'search' => $search]) }}"
                           class="filter-pill {{ $statusFilter === 'inactive' ? 'active' : '' }}" style="{{ $statusFilter === 'inactive' ? 'background:#64748b; border-color:#64748b;' : '' }}">
                            <i class="fa-solid fa-power-off" style="color: {{ $statusFilter === 'inactive' ? '#fff' : '#94a3b8' }};"></i> Inactive ({{ $summary['inactive_items'] }})
                        </a>
                    </div>
                </div>

                <div class="filter-pills-bar">
                    <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-right: 4px;">
                        <i class="fa-solid fa-layer-group" style="color: #2563eb;"></i> Category:
                    </span>
                    <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => 'all', 'status' => $statusFilter, 'search' => $search]) }}"
                       class="filter-pill {{ $categoryFilter === 'all' || empty($categoryFilter) ? 'active' : '' }}">
                        All Categories
                    </a>
                    @foreach($categories as $cat)
                        @php
                            $catIcon = match($cat) {
                                'HVAC / Cooling' => 'fa-snowflake',
                                'Lighting' => 'fa-lightbulb',
                                'IT & Office Equipment' => 'fa-laptop',
                                'Pumps & Motors' => 'fa-gears',
                                'Appliances & Pantry' => 'fa-utensils',
                                'Medical & Specialized' => 'fa-heart-pulse',
                                default => 'fa-plug',
                            };
                        @endphp
                        <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => $cat, 'status' => $statusFilter, 'search' => $search]) }}"
                           class="filter-pill {{ $categoryFilter === $cat ? 'active' : '' }}">
                            <i class="fa-solid {{ $catIcon }}"></i> {{ $cat }}
                        </a>
                    @endforeach
                </div>

                <!-- Table (Unified, Seamless Table - Walang Hati) -->
                <div class="table-responsive-wrap">
                    <table class="custom-table" id="ltMainTable">
                        <colgroup>
                            <col style="width: 32px;">
                            <col style="width: 25%;">
                            <col style="width: 13%;">
                            <col style="width: 13%;">
                            <col style="width: 11%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 8%;">
                            @if($canManage)
                            <col style="width: 8%;">
                            @endif
                        </colgroup>
                        <thead>
                            <tr>
                                <th style="text-align:center;">#</th>
                                <th class="th-sortable" onclick="sortTable(1, 'string')" title="Click to sort by equipment name">
                                    <span>Equipment / Load Name</span> <i class="fa-solid fa-sort sort-icon"></i>
                                </th>
                                <th>Assigned Meter</th>
                                <th class="th-sortable" style="text-align:right;" onclick="sortTable(3, 'number')" title="Click to sort by connected kW">
                                    <span>Connected Load</span> <i class="fa-solid fa-sort sort-icon"></i>
                                </th>
                                <th style="text-align:right;">Duty Cycle</th>
                                <th class="th-sortable" style="text-align:right;" onclick="sortTable(5, 'number')" title="Click to sort by monthly kWh">
                                    <span>Energy (kWh)</span> <i class="fa-solid fa-sort sort-icon"></i>
                                </th>
                                <th class="th-sortable" style="text-align:right;" onclick="sortTable(6, 'number')" title="Click to sort by monthly cost">
                                    <span>Monthly Cost</span> <i class="fa-solid fa-sort sort-icon"></i>
                                </th>
                                <th class="th-sortable" style="text-align:center;" onclick="sortTable(7, 'number')" title="Click to sort by share percentage">
                                    <span>Share</span> <i class="fa-solid fa-sort sort-icon"></i>
                                </th>
                                @if($canManage)
                                <th style="text-align:center;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="ltTableBody">
                            @forelse($equipments as $idx => $eq)
                                @php
                                    $isInactive = strtolower((string)($eq->status ?? 'active')) === 'inactive';
                                    $totalKw = round($eq->total_watts / 1000, 3);
                                    $dailyKwh = round($eq->daily_kwh, 2);
                                    $monthlyKwh = round($eq->monthly_kwh, 2);
                                    $monthlyCost = round($monthlyKwh * $ratePerKwh, 2);
                                    $sharePct = $summary['total_monthly_kwh'] > 0
                                        ? round(($monthlyKwh / $summary['total_monthly_kwh']) * 100, 1)
                                        : 0;

                                    $catClass = match($eq->category) {
                                        'HVAC / Cooling' => 'hvac',
                                        'Lighting' => 'lighting',
                                        'IT & Office Equipment' => 'it',
                                        'Pumps & Motors' => 'pumps',
                                        'Appliances & Pantry' => 'appliances',
                                        'Medical & Specialized' => 'medical',
                                        default => 'other',
                                    };

                                    $catIcon = match($eq->category) {
                                        'HVAC / Cooling' => 'fa-snowflake',
                                        'Lighting' => 'fa-lightbulb',
                                        'IT & Office Equipment' => 'fa-laptop',
                                        'Pumps & Motors' => 'fa-gears',
                                        'Appliances & Pantry' => 'fa-utensils',
                                        'Medical & Specialized' => 'fa-heart-pulse',
                                        default => 'fa-plug',
                                    };

                                    $isHighPower = $eq->rated_watts >= 3000 || $eq->total_watts >= 4000;
                                @endphp
                                <tr class="eq-table-row {{ $isInactive ? 'is-inactive-row' : '' }}" data-search="{{ strtolower($eq->equipment_name . ' ' . $eq->category . ' ' . $eq->location . ' ' . $eq->meter_name . ' ' . $eq->notes . ($isInactive ? ' inactive' : ' active')) }}">
                                    <td style="color:#94a3b8; font-weight:700; text-align:center;">
                                        {{ $idx + 1 }}
                                    </td>
                                    
                                    <!-- Equipment Name & Meta -->
                                    <td>
                                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                            <span class="eq-name {{ $isInactive ? 'is-inactive' : '' }}">
                                                {{ $eq->equipment_name }}
                                            </span>
                                            @if($isInactive)
                                                <span class="status-badge-inactive" title="Inactive / Decommissioned equipment (excluded from operational load)">
                                                    <i class="fa-solid fa-power-off"></i> Inactive
                                                </span>
                                            @else
                                                @if($isHighPower)
                                                    <span class="high-power-pill" title="High power equipment (>3kW)">
                                                        <i class="fa-solid fa-bolt"></i> High Load
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <div style="display:flex; align-items:center; gap:6px; margin-top:4px; flex-wrap:wrap;">
                                            <span class="cat-badge {{ $catClass }}">
                                                <i class="fa-solid {{ $catIcon }}"></i> {{ $eq->category ?: 'General' }}
                                            </span>
                                            @if($eq->location)
                                                <span class="eq-subtext" style="font-size:0.72rem;" title="Location: {{ $eq->location }}">
                                                    <i class="fa-solid fa-map-pin" style="color:#94a3b8;"></i> {{ Str::limit($eq->location, 30) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($eq->notes)
                                            <div class="eq-subtext" style="font-size:0.71rem; margin-top:3px;" title="{{ $eq->notes }}">
                                                <i class="fa-solid fa-tag" style="color:#94a3b8;"></i> {{ Str::limit($eq->notes, 40) }}
                                            </div>
                                        @endif
                                    </td>

                                    @php
                                        $meterScope = strtolower((string) ($eq->meter_scope ?? 'main'));
                                        $cleanMeterName = 'Main Meter';
                                        if ($meterScope === 'sub' && $eq->submeter) {
                                            $cleanMeterName = $eq->submeter->submeter_name;
                                        } elseif ($meterScope === 'facility') {
                                            $cleanMeterName = 'Facility General';
                                        } elseif ($eq->mainMeter) {
                                            $rawName = $eq->mainMeter->meter_name ?? 'Main Meter';
                                            $stripped = trim(str_ireplace($selectedFacility->name, '', $rawName));
                                            $cleanMeterName = !empty($stripped) ? trim($stripped, " -_–:") : $rawName;
                                        } else {
                                            $stripped = trim(str_ireplace($selectedFacility->name, '', (string)$eq->meter_name));
                                            $cleanMeterName = !empty($stripped) ? trim($stripped, " -_–:") : 'Main Meter';
                                        }

                                        $meterIcon = match($meterScope) {
                                            'sub' => 'fa-gauge',
                                            'facility' => 'fa-building-circle-check',
                                            default => 'fa-gauge-high',
                                        };
                                    @endphp
                                    <!-- Assigned Meter -->
                                    <td>
                                        <span class="meter-chip {{ $meterScope }}" title="Assigned: {{ $eq->meter_name }} @if($eq->mainMeter?->meter_number) ({{ $eq->mainMeter->meter_number }}) @endif">
                                            <i class="fa-solid {{ $meterIcon }}"></i>
                                            <span>{{ $cleanMeterName }}</span>
                                        </span>
                                    </td>

                                    <!-- Connected Load (kW & Qty x W) -->
                                    <td style="text-align:right;" data-sort-val="{{ $totalKw }}">
                                        <div class="eq-connected-kw {{ $isInactive ? 'is-inactive' : '' }}">
                                            {{ number_format($totalKw, 2) }} <small style="font-size:0.74rem; font-weight:700;">kW</small>
                                        </div>
                                        <div class="eq-subtext" style="font-size:0.72rem; margin-top:2px;">
                                            {{ $eq->quantity }} unit{{ $eq->quantity > 1 ? 's' : '' }} &times; {{ number_format($eq->rated_watts, 0) }} W
                                        </div>
                                    </td>

                                    <!-- Duty Cycle (Hours/Day & Days/Month) -->
                                    <td style="text-align:right;" data-sort-val="{{ $eq->operating_hours_per_day * $eq->operating_days_per_month }}">
                                        <div class="eq-duty-hours">
                                            {{ number_format($eq->operating_hours_per_day, 1) }} hrs<span class="eq-subtext" style="font-size:0.7rem; font-weight:600;">/day</span>
                                        </div>
                                        <div class="eq-subtext" style="font-size:0.71rem; margin-top:2px;">
                                            {{ $eq->operating_days_per_month }} days/mo <span style="color:#94a3b8;">({{ number_format($eq->operating_hours_per_day * $eq->operating_days_per_month, 0) }}h)</span>
                                        </div>
                                    </td>

                                    <!-- Energy Consumption (Monthly & Daily kWh) -->
                                    <td style="text-align:right;" data-sort-val="{{ $monthlyKwh }}">
                                        <div class="eq-energy-kwh {{ $isInactive ? 'is-inactive' : '' }}">
                                            {{ number_format($monthlyKwh, 2) }} <small class="eq-subtext" style="font-size:0.72rem; font-weight:700;">kWh/mo</small>
                                        </div>
                                        <div class="eq-subtext" style="font-size:0.72rem; margin-top:2px;">
                                            {{ number_format($dailyKwh, 2) }} kWh/day
                                        </div>
                                    </td>

                                    <!-- Monthly Cost -->
                                    <td style="text-align:right;" data-sort-val="{{ $monthlyCost }}">
                                        <div class="eq-monthly-cost {{ $isInactive ? 'is-inactive' : '' }}">
                                            &#8369;{{ number_format($monthlyCost, 2) }}
                                        </div>
                                        <div class="eq-subtext" style="font-size:0.7rem; margin-top:2px;">
                                            &#8369;{{ number_format($dailyKwh * $ratePerKwh, 2) }}/day
                                        </div>
                                    </td>

                                    <!-- Facility Share % with Visual Bar -->
                                    <td style="text-align:center;" data-sort-val="{{ $sharePct }}">
                                        @if($isInactive)
                                            <span style="font-size:0.72rem; color:#94a3b8; font-weight:700;">Standby</span>
                                        @else
                                            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                                <span class="share-pill-badge">{{ $sharePct }}%</span>
                                                <div class="share-track">
                                                    <div class="share-fill" style="width: {{ min(100, $sharePct * 2.5) }}%;"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    @if($canManage)
                                    <td style="text-align:center; white-space:nowrap;">
                                        <!-- Quick Deactivate / Reactivate Toggle Action with custom Modal -->
                                        <button type="button" class="action-icon-btn {{ $isInactive ? 'activate' : 'deactivate' }}" title="{{ $isInactive ? 'Reactivate Equipment (Include in Load)' : 'Deactivate Equipment (Mark as Inactive)' }}" onclick="openStatusModal({{ $eq->id }}, '{{ addslashes($eq->equipment_name) }}', {{ $isInactive ? 'true' : 'false' }})">
                                            <i class="fa-solid {{ $isInactive ? 'fa-power-off' : 'fa-ban' }}"></i>
                                        </button>
                                        <button type="button" class="action-icon-btn" title="Edit Equipment" onclick='openEditModal(@json($eq))'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr id="noRecordsRow">
                                    <td colspan="{{ $canManage ? 9 : 8 }}" style="text-align:center; padding:44px 20px; color:#94a3b8;">
                                        <i class="fa-solid fa-box-open" style="font-size:2.4rem; margin-bottom:10px; display:block; color:#cbd5e1;"></i>
                                        <strong style="font-size:0.95rem; color:#475569;">No equipment records found.</strong>
                                        <div style="font-size:0.8rem; margin-top:4px;">Click <strong>+ Add Equipment</strong> above to begin recording loads.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($equipments->count() > 0)
                        <tfoot>
                            <tr>
                                <td style="text-align:center; color:#94a3b8;">&bull;</td>
                                <td class="eq-name" style="font-weight:850;">TOTAL LOAD & CONSUMPTION</td>
                                <td class="eq-subtext" style="font-size:0.78rem;">{{ $summary['total_units'] }} Total Units</td>
                                <td class="eq-connected-kw" style="text-align:right; font-size:0.95rem; font-weight:850;">
                                    {{ number_format($summary['total_connected_kw'], 2) }} kW
                                </td>
                                <td class="eq-subtext" style="text-align:right; font-size:0.76rem;">
                                    {{ number_format($summary['total_daily_kwh'], 2) }} kWh/day
                                </td>
                                <td class="eq-energy-kwh" style="text-align:right; font-size:0.95rem; font-weight:850;">
                                    {{ number_format($summary['total_monthly_kwh'], 2) }} kWh
                                </td>
                                <td class="eq-monthly-cost" style="text-align:right; font-size:1rem; font-weight:900;">
                                    &#8369;{{ number_format($summary['total_monthly_cost'], 2) }}
                                </td>
                                <td style="text-align:center; font-weight:800;">100%</td>
                                @if($canManage) <td></td> @endif
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        @endif

    </div>
</div>

<!-- ADD EQUIPMENT MODAL WITH INSTANT PREVIEW -->
@if($canManage && $selectedFacility)
<div id="ltAddModal" class="modal-overlay">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
        <div class="modal-head">
            <div class="modal-head-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div class="modal-head-heading">
                <h3 id="addModalTitle">Add Equipment Load</h3>
                <p class="modal-head-subtitle">Record equipment wattage, operating schedule, and meter assignment for this facility.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeAddModal()" aria-label="Close add equipment modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('modules.load-tracking.equipment.store') }}" id="ltAddForm">
            @csrf
            <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">

            <div class="modal-body">
                
                <!-- Quick Preset Selector -->
                <div class="form-group preset-box">
                    <label>
                        <span><i class="fa-solid fa-bolt"></i> Auto-Fill Common Appliance Preset:</span>
                        <span class="form-optional">Quick Template</span>
                    </label>
                    <select id="ltPresetSelector" class="form-select" onchange="applyPreset(this.value, 'add')">
                        <option value="">-- Choose Appliance to Auto-fill --</option>
                        @foreach($presets as $pIdx => $preset)
                            <option value="{{ $pIdx }}">{{ $preset['name'] }} ({{ $preset['rated_watts'] }}W)</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-section-title"><i class="fa-solid fa-plug"></i> Equipment Identification</div>

                <div class="form-group">
                    <label for="add_equipment_name">Equipment / Load Name <span class="form-required">Required</span></label>
                    <input type="text" name="equipment_name" id="add_equipment_name" class="form-input" placeholder="e.g. Inverter Split AC (Admin Office)" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_category">Category <span class="form-required">Required</span></label>
                        <select name="category" id="add_category" class="form-select">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add_location">Location / Room <span class="form-optional">Optional</span></label>
                        <input type="text" name="location" id="add_location" class="form-input" placeholder="e.g. 2nd Floor Admin Room">
                    </div>
                </div>

                <div class="form-group">
                    <label for="add_status">Operational Status <span class="form-required">Required</span></label>
                    <select name="status" id="add_status" class="form-select">
                        <option value="active" selected>Active / In-Service (Included in Connected Load)</option>
                        <option value="inactive">Inactive / Standby (Excluded from Connected Load)</option>
                    </select>
                </div>

                <div class="modal-section-title"><i class="fa-solid fa-gauge-high"></i> Meter & Electrical Assignment</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_meter_scope">Assigned Meter Scope <span class="form-required">Required</span></label>
                        <select name="meter_scope" id="add_meter_scope" class="form-select" onchange="toggleMeterInputs('add')">
                            <option value="facility">Facility General / Unmetered</option>
                            @if($mainMeters->count() > 0)
                                <option value="main">Main Utility Meter</option>
                            @endif
                            @if($subMeters->count() > 0)
                                <option value="sub">Submeter</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group" id="add_main_meter_wrap" style="display:none;">
                        <label for="add_facility_meter_id">Select Main Meter <span class="form-required">Required</span></label>
                        <select name="facility_meter_id" id="add_facility_meter_id" class="form-select">
                            @foreach($mainMeters as $mm)
                                <option value="{{ $mm->id }}">{{ $mm->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="add_sub_meter_wrap" style="display:none;">
                        <label for="add_submeter_id">Select Submeter <span class="form-required">Required</span></label>
                        <select name="submeter_id" id="add_submeter_id" class="form-select">
                            @foreach($subMeters as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->submeter_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-section-title"><i class="fa-solid fa-clock"></i> Duty Cycle & Rating</div>

                <!-- Load Specification Inputs -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_quantity">Quantity <span class="form-required">Required</span></label>
                        <input type="number" name="quantity" id="add_quantity" class="form-input" value="1" min="1" max="9999" required oninput="recalcModalPreview('add')">
                    </div>

                    <div class="form-group">
                        <label for="add_rated_watts">Rated Power (Watts) <span class="form-required">Required</span></label>
                        <input type="number" step="0.1" name="rated_watts" id="add_rated_watts" class="form-input" placeholder="e.g. 1200" min="0.1" required oninput="recalcModalPreview('add')">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="add_hours">Operating Hours / Day <span class="form-required">Required</span></label>
                        <input type="number" step="0.1" name="operating_hours_per_day" id="add_hours" class="form-input" value="8" min="0.1" max="24" required oninput="recalcModalPreview('add')">
                    </div>

                    <div class="form-group">
                        <label for="add_days">Operating Days / Month <span class="form-required">Required</span></label>
                        <input type="number" name="operating_days_per_month" id="add_days" class="form-input" value="22" min="1" max="31" required oninput="recalcModalPreview('add')">
                    </div>
                </div>

                <!-- Live Computation Preview -->
                <div class="calc-preview-strip">
                    <div>
                        <div style="font-size:0.95rem; font-weight:850; color:#2563eb;" id="add_prev_daily">0.00 kWh</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Daily Energy</div>
                    </div>
                    <div>
                        <div style="font-size:0.95rem; font-weight:850; color:#2563eb;" id="add_prev_monthly">0.00 kWh</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Monthly Energy</div>
                    </div>
                    <div>
                        <div style="font-size:0.95rem; font-weight:900; color:#059669;" id="add_prev_cost">₱0.00</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Est. Monthly Cost</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="add_notes">Notes / Asset Tag <span class="form-optional">Optional</span></label>
                    <textarea name="notes" id="add_notes" class="form-textarea" rows="2" placeholder="e.g. Asset #EQ-2026-004, Inverter compliant unit"></textarea>
                </div>

            </div>
            <div class="modal-foot">
                <button type="button" class="action-btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="action-btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Equipment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT EQUIPMENT MODAL WITH INSTANT PREVIEW -->
<div id="ltEditModal" class="modal-overlay">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-head">
            <div class="modal-head-icon">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div class="modal-head-heading">
                <h3 id="editModalTitle">Edit Equipment Load</h3>
                <p class="modal-head-subtitle">Modify load wattage, operation cycle, or assigned meter scope.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEditModal()" aria-label="Close edit equipment modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="" id="ltEditForm">
            @csrf
            @method('PUT')

            <div class="modal-body">
                
                <div class="modal-section-title"><i class="fa-solid fa-plug"></i> Equipment Identification</div>

                <div class="form-group">
                    <label for="edit_equipment_name">Equipment / Load Name <span class="form-required">Required</span></label>
                    <input type="text" name="equipment_name" id="edit_equipment_name" class="form-input" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_category">Category <span class="form-required">Required</span></label>
                        <select name="category" id="edit_category" class="form-select">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_location">Location / Room <span class="form-optional">Optional</span></label>
                        <input type="text" name="location" id="edit_location" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_status">Operational Status <span class="form-required">Required</span></label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="active">Active / In-Service (Included in Connected Load)</option>
                        <option value="inactive">Inactive / Standby (Excluded from Connected Load)</option>
                    </select>
                </div>

                <div class="modal-section-title"><i class="fa-solid fa-gauge-high"></i> Meter & Electrical Assignment</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_meter_scope">Assigned Meter Scope <span class="form-required">Required</span></label>
                        <select name="meter_scope" id="edit_meter_scope" class="form-select" onchange="toggleMeterInputs('edit')">
                            <option value="facility">Facility General / Unmetered</option>
                            @if($mainMeters->count() > 0)
                                <option value="main">Main Utility Meter</option>
                            @endif
                            @if($subMeters->count() > 0)
                                <option value="sub">Submeter</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group" id="edit_main_meter_wrap" style="display:none;">
                        <label for="edit_facility_meter_id">Select Main Meter <span class="form-required">Required</span></label>
                        <select name="facility_meter_id" id="edit_facility_meter_id" class="form-select">
                            @foreach($mainMeters as $mm)
                                <option value="{{ $mm->id }}">{{ $mm->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="edit_sub_meter_wrap" style="display:none;">
                        <label for="edit_submeter_id">Select Submeter <span class="form-required">Required</span></label>
                        <select name="submeter_id" id="edit_submeter_id" class="form-select">
                            @foreach($subMeters as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->submeter_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-section-title"><i class="fa-solid fa-clock"></i> Duty Cycle & Rating</div>

                <!-- Load Specification Inputs -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_quantity">Quantity <span class="form-required">Required</span></label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-input" min="1" max="9999" required oninput="recalcModalPreview('edit')">
                    </div>

                    <div class="form-group">
                        <label for="edit_rated_watts">Rated Power (Watts) <span class="form-required">Required</span></label>
                        <input type="number" step="0.1" name="rated_watts" id="edit_rated_watts" class="form-input" min="0.1" required oninput="recalcModalPreview('edit')">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_hours">Operating Hours / Day <span class="form-required">Required</span></label>
                        <input type="number" step="0.1" name="operating_hours_per_day" id="edit_hours" class="form-input" min="0.1" max="24" required oninput="recalcModalPreview('edit')">
                    </div>

                    <div class="form-group">
                        <label for="edit_days">Operating Days / Month <span class="form-required">Required</span></label>
                        <input type="number" name="operating_days_per_month" id="edit_days" class="form-input" min="1" max="31" required oninput="recalcModalPreview('edit')">
                    </div>
                </div>

                <!-- Live Computation Preview -->
                <div class="calc-preview-strip">
                    <div>
                        <div style="font-size:0.95rem; font-weight:850; color:#2563eb;" id="edit_prev_daily">0.00 kWh</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Daily Energy</div>
                    </div>
                    <div>
                        <div style="font-size:0.95rem; font-weight:850; color:#2563eb;" id="edit_prev_monthly">0.00 kWh</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Monthly Energy</div>
                    </div>
                    <div>
                        <div style="font-size:0.95rem; font-weight:900; color:#059669;" id="edit_prev_cost">₱0.00</div>
                        <div style="font-size:0.68rem; color:#64748b; font-weight:800; text-transform:uppercase; margin-top:2px;">Est. Monthly Cost</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_notes">Notes / Asset Tag <span class="form-optional">Optional</span></label>
                    <textarea name="notes" id="edit_notes" class="form-textarea" rows="2"></textarea>
                </div>

            </div>
            <div class="modal-foot">
                <button type="button" class="action-btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="action-btn-primary">
                    <i class="fa-solid fa-check"></i> Update Equipment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- STATUS TOGGLE (DEACTIVATE / REACTIVATE) CONFIRMATION MODAL -->
<div id="ltStatusModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 480px; text-align:center; padding: 26px 24px;" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
        <div id="statusModalIconBox" class="delete-modal-warning-icon" style="background:#fffbeb; border-color:#fef3c7; color:#d97706; margin-bottom: 14px;">
            <i id="statusModalIcon" class="fa-solid fa-ban"></i>
        </div>
        <h3 id="statusModalTitle" style="margin: 0 0 8px 0; font-size: 1.2rem; font-weight: 850;">
            Deactivate Equipment
        </h3>
        <p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin: 0 0 16px 0;">
            Are you sure you want to <span id="statusModalActionWord">deactivate</span> <strong id="statusEquipmentName"></strong>?
        </p>
        <div id="statusModalNotice" style="background: #fefce8; border: 1px solid #fef08a; border-radius: 10px; padding: 12px 14px; margin-bottom: 22px; font-size: 0.78rem; color: #854d0e; text-align: left; display: flex; align-items: flex-start; gap: 10px; line-height: 1.45;">
            <i id="statusModalNoticeIcon" class="fa-solid fa-circle-info" style="color:#d97706; font-size: 1.05rem; margin-top: 2px; flex-shrink: 0;"></i>
            <span id="statusModalNoticeText">This equipment will remain saved in your facility records for historical inventory, but will be <strong>excluded from active connected load (kW), monthly kWh, and electricity cost calculations</strong>.</span>
        </div>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button type="button" class="action-btn-secondary" onclick="closeStatusModal()" style="min-width: 110px; justify-content: center;">
                Cancel
            </button>
            <form id="ltStatusForm" method="POST" action="" style="display:inline; margin:0;">
                @csrf
                <button type="submit" id="statusModalSubmitBtn" class="action-btn-primary" style="background:#d97706; min-width: 160px; justify-content: center; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.25);">
                    <i id="statusModalBtnIcon" class="fa-solid fa-ban"></i> <span id="statusModalBtnText">Yes, Deactivate</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Scripts for Dynamic Charts, Modals, Presets, and Interactive Simulator -->
<script>
    const ratePerKwh = {{ (float)$ratePerKwh }};
    const totalConnectedWatts = {{ (float)($summary['total_connected_watts'] ?? 0) }};
    const totalMonthlyKwh = {{ (float)($summary['total_monthly_kwh'] ?? 0) }};
    const presetsData = @json($presets);

    const simTargets = {
        'cooling_lighting': {{ (float)($summary['cooling_lighting_watts'] ?? 0) }},
        'cooling': {{ (float)($summary['cooling_watts'] ?? 0) }},
        'lighting': {{ (float)($summary['lighting_watts'] ?? 0) }},
        'all': {{ (float)($summary['total_connected_watts'] ?? 0) }}
    };
    let currentSimTargetKey = 'cooling_lighting';

    function setSimTarget(targetKey, btnEl) {
        currentSimTargetKey = targetKey;
        document.querySelectorAll('.sim-target-btn').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        const sliderVal = document.getElementById('ltReductionSlider').value;
        updateSimulation(sliderVal);
    }

    function setSliderVal(val) {
        const slider = document.getElementById('ltReductionSlider');
        if (slider) {
            slider.value = val;
            updateSimulation(val);
        }
    }

    // Category Load Distribution Chart Setup & Switching (Donut vs Ranking Bar)
    let categoryChartInstance = null;
    const catLabels = @json(array_column($categoryBreakdown, 'category'));
    const catData = @json(array_column($categoryBreakdown, 'monthly_kwh'));
    const catColors = [
        '#2563eb',
        '#10b981',
        '#f59e0b',
        '#8b5cf6',
        '#ec4899',
        '#06b6d4',
        '#64748b'
    ];

    function initCategoryChart(type = 'donut') {
        const canvas = document.getElementById('categoryDonutChart');
        if (!canvas || !window.Chart || catData.length === 0) return;

        if (categoryChartInstance) {
            categoryChartInstance.destroy();
            categoryChartInstance = null;
        }

        const centerInfo = document.getElementById('donutCenterInfo');
        if (type === 'donut') {
            if (centerInfo) centerInfo.style.display = 'block';
            categoryChartInstance = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catData,
                        backgroundColor: catColors.slice(0, catData.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed || 0;
                                    const cost = (val * ratePerKwh).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    const pct = totalMonthlyKwh > 0 ? ((val / totalMonthlyKwh) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val.toLocaleString()} kWh (${pct}%) • ₱${cost}`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        } else {
            // Horizontal Bar Ranking View ("Sino mas mataas consume")
            if (centerInfo) centerInfo.style.display = 'none';
            categoryChartInstance = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: catLabels,
                    datasets: [{
                        label: 'Monthly kWh',
                        data: catData,
                        backgroundColor: catColors.slice(0, catData.length),
                        borderRadius: 6,
                        barThickness: 16
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed.x || 0;
                                    const cost = (val * ratePerKwh).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    const pct = totalMonthlyKwh > 0 ? ((val / totalMonthlyKwh) * 100).toFixed(1) : 0;
                                    return ` ${val.toLocaleString()} kWh (${pct}%) • ₱${cost}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 10 },
                                callback: function(v) { return v >= 1000 ? (v / 1000) + 'k' : v; }
                            }
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, weight: '600' },
                                color: '#1e293b'
                            }
                        }
                    }
                }
            });
        }
    }

    function switchCategoryChart(mode) {
        const btnDonut = document.getElementById('btnToggleDonut');
        const btnBar = document.getElementById('btnToggleBar');
        if (mode === 'donut') {
            if (btnDonut) btnDonut.classList.add('active');
            if (btnBar) btnBar.classList.remove('active');
            initCategoryChart('donut');
        } else {
            if (btnBar) btnBar.classList.add('active');
            if (btnDonut) btnDonut.classList.remove('active');
            initCategoryChart('bar');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCategoryChart('donut');

        // Initialize Simulator
        updateSimulation(1.0);
    });

    // What-If Simulator Calculation
    function updateSimulation(hoursReduction) {
        const hours = parseFloat(hoursReduction) || 0;
        document.getElementById('ltReductionDisplay').textContent = hours.toFixed(1) + ' hours/day';

        const targetWatts = simTargets[currentSimTargetKey] ?? simTargets['all'];
        const targetKw = (targetWatts / 1000).toFixed(2);
        const badgeEl = document.getElementById('simTargetWattsBadge');
        if (badgeEl) {
            badgeEl.textContent = `Target: ${parseFloat(targetKw).toLocaleString()} kW`;
        }

        const dailyKwhSaved = (targetWatts * hours) / 1000;
        const monthlyDays = 22; // standard monthly working days
        const monthlyKwhSaved = dailyKwhSaved * monthlyDays;
        const monthlyPesosSaved = monthlyKwhSaved * ratePerKwh;
        const pctSaved = totalMonthlyKwh > 0 ? ((monthlyKwhSaved / totalMonthlyKwh) * 100).toFixed(1) : '0.0';

        document.getElementById('simDailyKwhSaved').textContent = dailyKwhSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kWh';
        document.getElementById('simMonthlyKwhSaved').textContent = monthlyKwhSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kWh';
        document.getElementById('simMonthlyPesosSaved').textContent = '₱' + monthlyPesosSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const pctEl = document.getElementById('simMonthlyPctSaved');
        if (pctEl) {
            pctEl.textContent = pctSaved + '%';
        }

        // Actionable Tip Text
        const tipEl = document.getElementById('simActionTip');
        if (tipEl) {
            tipEl.innerHTML = `<i class="fa-solid fa-lightbulb" style="color:#0d9488;"></i> <strong>Energy Tip:</strong> Reducing operating hours by <strong>${hours.toFixed(1)} hr/day</strong> can save <strong>₱${monthlyPesosSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/month</strong> (${pctSaved}% of electricity bill).`;
        }
    }

    // Modal Control Functions
    function openAddModal() {
        document.getElementById('ltAddModal').classList.add('is-active');
        recalcModalPreview('add');
    }
    function closeAddModal() {
        document.getElementById('ltAddModal').classList.remove('is-active');
    }

    function openEditModal(equipment) {
        const form = document.getElementById('ltEditForm');
        form.action = "{{ url('modules/load-tracking/equipment') }}/" + equipment.id;

        document.getElementById('edit_equipment_name').value = equipment.equipment_name || '';
        document.getElementById('edit_category').value = equipment.category || 'HVAC / Cooling';
        document.getElementById('edit_location').value = equipment.location || '';
        document.getElementById('edit_status').value = equipment.status || 'active';
        document.getElementById('edit_quantity').value = equipment.quantity || 1;
        document.getElementById('edit_rated_watts').value = equipment.rated_watts || '';
        document.getElementById('edit_hours').value = equipment.operating_hours_per_day || 8;
        document.getElementById('edit_days').value = equipment.operating_days_per_month || 22;
        document.getElementById('edit_notes').value = equipment.notes || '';

        // Handle meter scope selection
        if (equipment.facility_meter_id) {
            document.getElementById('edit_meter_scope').value = 'main';
            document.getElementById('edit_facility_meter_id').value = equipment.facility_meter_id;
        } else if (equipment.submeter_id) {
            document.getElementById('edit_meter_scope').value = 'sub';
            document.getElementById('edit_submeter_id').value = equipment.submeter_id;
        } else {
            document.getElementById('edit_meter_scope').value = 'facility';
        }

        toggleMeterInputs('edit');
        recalcModalPreview('edit');
        document.getElementById('ltEditModal').classList.add('is-active');
    }
    function closeEditModal() {
        document.getElementById('ltEditModal').classList.remove('is-active');
    }

    // Preset Applicator
    function applyPreset(presetIndex, mode) {
        if (presetIndex === '' || !presetsData[presetIndex]) return;
        const p = presetsData[presetIndex];
        const prefix = mode === 'edit' ? 'edit_' : 'add_';

        document.getElementById(prefix + 'equipment_name').value = p.name;
        document.getElementById(prefix + 'category').value = p.category;
        document.getElementById(prefix + 'rated_watts').value = p.rated_watts;
        document.getElementById(prefix + 'hours').value = p.hours_per_day;
        document.getElementById(prefix + 'days').value = p.days_per_month;

        recalcModalPreview(mode);
    }

    // Toggle main/sub meter fields
    function toggleMeterInputs(mode) {
        const prefix = mode === 'edit' ? 'edit_' : 'add_';
        const scope = document.getElementById(prefix + 'meter_scope').value;

        const mainWrap = document.getElementById(prefix + 'main_meter_wrap');
        const subWrap = document.getElementById(prefix + 'sub_meter_wrap');

        if (mainWrap) mainWrap.style.display = scope === 'main' ? 'block' : 'none';
        if (subWrap) subWrap.style.display = scope === 'sub' ? 'block' : 'none';
    }

    // Modal Live Preview Recalculator
    function recalcModalPreview(mode) {
        const prefix = mode === 'edit' ? 'edit_' : 'add_';

        const qty = parseFloat(document.getElementById(prefix + 'quantity').value) || 0;
        const watts = parseFloat(document.getElementById(prefix + 'rated_watts').value) || 0;
        const hours = parseFloat(document.getElementById(prefix + 'hours').value) || 0;
        const days = parseFloat(document.getElementById(prefix + 'days').value) || 0;

        const dailyKwh = (watts * qty * hours) / 1000;
        const monthlyKwh = dailyKwh * days;
        const monthlyCost = monthlyKwh * ratePerKwh;

        document.getElementById(prefix + 'prev_daily').textContent = dailyKwh.toFixed(2) + ' kWh';
        document.getElementById(prefix + 'prev_monthly').textContent = monthlyKwh.toFixed(2) + ' kWh';
        document.getElementById(prefix + 'prev_cost').textContent = '₱' + monthlyCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Status Modal (Deactivate / Reactivate) Control
    function openStatusModal(id, name, isInactive) {
        const modal = document.getElementById('ltStatusModal');
        const form = document.getElementById('ltStatusForm');
        const nameEl = document.getElementById('statusEquipmentName');
        const titleEl = document.getElementById('statusModalTitle');
        const actionWordEl = document.getElementById('statusModalActionWord');
        const iconBox = document.getElementById('statusModalIconBox');
        const iconEl = document.getElementById('statusModalIcon');
        const noticeEl = document.getElementById('statusModalNotice');
        const noticeIcon = document.getElementById('statusModalNoticeIcon');
        const noticeText = document.getElementById('statusModalNoticeText');
        const submitBtn = document.getElementById('statusModalSubmitBtn');
        const btnIcon = document.getElementById('statusModalBtnIcon');
        const btnText = document.getElementById('statusModalBtnText');

        if (!modal || !form) return;

        form.action = "{{ url('modules/load-tracking/equipment') }}/" + id + "/toggle-status";
        if (nameEl) nameEl.textContent = name;

        if (isInactive) {
            // Reactivate configuration
            if (titleEl) titleEl.textContent = 'Reactivate Equipment';
            if (actionWordEl) actionWordEl.textContent = 'reactivate';
            if (iconBox) {
                iconBox.style.background = '#f0fdf4';
                iconBox.style.borderColor = '#bbf7d0';
                iconBox.style.color = '#16a34a';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-power-off';
            if (noticeEl) {
                noticeEl.style.background = '#f0fdf4';
                noticeEl.style.borderColor = '#bbf7d0';
                noticeEl.style.color = '#166534';
            }
            if (noticeIcon) noticeIcon.style.color = '#16a34a';
            if (noticeText) noticeText.innerHTML = 'This equipment will be restored to <strong>active operational status</strong> and included in facility connected load (kW), monthly kWh, and electricity cost computations.';
            if (submitBtn) {
                submitBtn.style.background = '#16a34a';
                submitBtn.style.boxShadow = '0 4px 14px rgba(22, 163, 74, 0.25)';
            }
            if (btnIcon) btnIcon.className = 'fa-solid fa-power-off';
            if (btnText) btnText.textContent = 'Yes, Reactivate';
        } else {
            // Deactivate configuration
            if (titleEl) titleEl.textContent = 'Deactivate Equipment';
            if (actionWordEl) actionWordEl.textContent = 'deactivate';
            if (iconBox) {
                iconBox.style.background = '#fffbeb';
                iconBox.style.borderColor = '#fef3c7';
                iconBox.style.color = '#d97706';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-ban';
            if (noticeEl) {
                noticeEl.style.background = '#fefce8';
                noticeEl.style.borderColor = '#fef08a';
                noticeEl.style.color = '#854d0e';
            }
            if (noticeIcon) noticeIcon.style.color = '#d97706';
            if (noticeText) noticeText.innerHTML = 'This equipment will remain saved in your facility records for historical inventory, but will be <strong>excluded from active connected load (kW), monthly kWh, and electricity cost calculations</strong>.';
            if (submitBtn) {
                submitBtn.style.background = '#d97706';
                submitBtn.style.boxShadow = '0 4px 14px rgba(217, 119, 6, 0.25)';
            }
            if (btnIcon) btnIcon.className = 'fa-solid fa-ban';
            if (btnText) btnText.textContent = 'Yes, Deactivate';
        }

        modal.classList.add('is-active');
    }

    function closeStatusModal() {
        const modal = document.getElementById('ltStatusModal');
        if (modal) modal.classList.remove('is-active');
    }

    // Close modals on overlay backdrop click or Escape key
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('is-active');
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.is-active').forEach(m => m.classList.remove('is-active'));
        }
    });

    // Live Client-side Quick Search Filter with Clear Button
    function quickFilterTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#ltTableBody tr.eq-table-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.getAttribute('data-search') || '';
            if (!q || text.includes(q)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const badge = document.getElementById('equipmentCountBadge');
        if (badge) {
            badge.textContent = `${visibleCount} item${visibleCount !== 1 ? 's' : ''}`;
        }

        const clearBtn = document.getElementById('ltSearchClearBtn');
        if (clearBtn) {
            clearBtn.style.display = q ? 'block' : 'none';
        }
    }

    function clearQuickSearch() {
        const input = document.getElementById('ltQuickSearch');
        if (input) {
            input.value = '';
            quickFilterTable('');
            input.focus();
        }
    }

    // Table Column Sorter
    let sortDirection = {};
    function sortTable(colIndex, type) {
        const tbody = document.getElementById('ltTableBody');
        const rows = Array.from(tbody.querySelectorAll('tr.eq-table-row'));
        if (!rows.length) return;

        sortDirection[colIndex] = !sortDirection[colIndex];
        const isAsc = sortDirection[colIndex];

        rows.sort((a, b) => {
            let valA, valB;
            if (type === 'number') {
                const cellA = a.children[colIndex];
                const cellB = b.children[colIndex];
                valA = parseFloat(cellA.getAttribute('data-sort-val') || 0);
                valB = parseFloat(cellB.getAttribute('data-sort-val') || 0);
            } else {
                valA = a.children[colIndex].innerText.trim().toLowerCase();
                valB = b.children[colIndex].innerText.trim().toLowerCase();
            }

            if (valA < valB) return isAsc ? -1 : 1;
            if (valA > valB) return isAsc ? 1 : -1;
            return 0;
        });

        rows.forEach(r => tbody.appendChild(r));

        // Update row numbers in col 1
        rows.forEach((r, idx) => {
            r.children[0].textContent = idx + 1;
        });

        // Update sort icons
        document.querySelectorAll('#ltMainTable th.th-sortable .sort-icon').forEach(icon => {
            icon.className = 'fa-solid fa-sort sort-icon';
        });
        const activeTh = document.querySelectorAll('#ltMainTable th')[colIndex];
        if (activeTh) {
            const icon = activeTh.querySelector('.sort-icon');
            if (icon) {
                icon.className = isAsc ? 'fa-solid fa-sort-up sort-icon' : 'fa-solid fa-sort-down sort-icon';
            }
        }
    }
</script>
@endsection
