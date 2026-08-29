@extends('layouts.qc-admin')
@section('title', 'Facility Load Tracking & Equipment Energy Computation')

@section('content')
@php
    $selectedFacilityId = $selectedFacility ? $selectedFacility->id : 0;
    $ratePerKwh = $summary['rate_per_kwh'] ?? 12.00;
@endphp

<style>
    /* Scope Styles for Load Tracking Module */
    .lt-shell {
        display: grid;
        gap: 24px;
        width: 100%;
    }

    /* Hero / Top Facility Banner */
    .lt-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f3d3e 100%);
        color: #fff;
        padding: 28px 32px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .lt-hero:before {
        content: "";
        position: absolute;
        top: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }
    .lt-hero-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 20px;
    }
    .lt-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #34d399;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .lt-title {
        margin: 6px 0 4px;
        font-size: clamp(1.4rem, 2.2vw, 1.9rem);
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #f8fafc;
    }
    .lt-subtitle {
        color: #94a3b8;
        font-size: 0.88rem;
        max-width: 680px;
        line-height: 1.5;
    }

    /* Facility Switcher Select in Hero */
    .lt-facility-picker {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.08);
        padding: 8px 14px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
    }
    .lt-facility-picker label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .lt-facility-select {
        background: #1e293b;
        color: #f8fafc;
        border: 1px solid #475569;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.84rem;
        font-weight: 700;
        outline: none;
        cursor: pointer;
        min-width: 200px;
    }
    .lt-facility-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    }

    .lt-facility-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.8rem;
        color: #cbd5e1;
    }
    .lt-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.06);
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .lt-meta-pill i {
        color: #34d399;
    }

    /* KPI Summary Cards Grid */
    .lt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .lt-kpi-card {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        background: #fff;
        padding: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .lt-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }
    .lt-kpi-card:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
    }
    .lt-kpi-card.blue:before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .lt-kpi-card.emerald:before { background: linear-gradient(90deg, #10b981, #34d399); }
    .lt-kpi-card.amber:before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .lt-kpi-card.indigo:before { background: linear-gradient(90deg, #6366f1, #818cf8); }

    .lt-kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .lt-kpi-label {
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }
    .lt-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 0.95rem;
    }
    .lt-kpi-card.blue .lt-kpi-icon { background: #eff6ff; color: #2563eb; }
    .lt-kpi-card.emerald .lt-kpi-icon { background: #ecfdf5; color: #059669; }
    .lt-kpi-card.amber .lt-kpi-icon { background: #fffbe6; color: #d97706; }
    .lt-kpi-card.indigo .lt-kpi-icon { background: #eef2ff; color: #4f46e5; }

    .lt-kpi-value {
        font-size: 1.7rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1.1;
    }
    .lt-kpi-unit {
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        margin-left: 2px;
    }
    .lt-kpi-sub {
        margin-top: 8px;
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .lt-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .lt-badge-pill.success { background: #d1fae5; color: #047857; }
    .lt-badge-pill.warning { background: #fef3c7; color: #b45309; }
    .lt-badge-pill.danger { background: #fee2e2; color: #b91c1c; }
    .lt-badge-pill.neutral { background: #f1f5f9; color: #475569; }

    /* Analytics Row: 2 Columns (Donut Chart + Top Consumers / What-if) */
    .lt-analytics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .lt-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        padding: 22px;
        display: flex;
        flex-direction: column;
    }
    .lt-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .lt-card-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 900;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .lt-card-title i {
        color: #10b981;
    }
    .lt-card-desc {
        font-size: 0.74rem;
        color: #64748b;
        margin-top: 2px;
    }

    /* Donut Chart Container */
    .lt-chart-box {
        position: relative;
        height: 220px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .lt-category-table {
        margin-top: 14px;
        width: 100%;
        border-collapse: collapse;
        font-size: 0.77rem;
    }
    .lt-category-table th {
        text-align: left;
        color: #64748b;
        font-weight: 800;
        padding: 6px 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    .lt-category-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #f8fafc;
        color: #1e293b;
        font-weight: 600;
    }
    .lt-cat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    /* What-If Simulator Card */
    .lt-sim-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .lt-sim-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .lt-slider-wrap {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
    }
    .lt-slider-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 700;
    }
    .lt-range {
        width: 100%;
        accent-color: #10b981;
        cursor: pointer;
    }
    .lt-sim-results {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        background: #fff;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .lt-sim-kpi {
        text-align: center;
    }
    .lt-sim-kpi-val {
        font-size: 1.1rem;
        font-weight: 900;
        color: #059669;
    }
    .lt-sim-kpi-lbl {
        font-size: 0.68rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Equipment Inventory Section */
    .lt-inventory-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .lt-actions-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .lt-search-input {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px 8px 34px;
        font-size: 0.82rem;
        outline: none;
        width: 220px;
        transition: border-color 0.15s ease;
    }
    .lt-search-wrap {
        position: relative;
    }
    .lt-search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .lt-search-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    .lt-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .lt-btn-primary {
        background: #059669;
        color: #fff;
    }
    .lt-btn-primary:hover {
        background: #047857;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }
    .lt-btn-secondary {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .lt-btn-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Category Filter Pills */
    .lt-pills-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    .lt-filter-pill {
        padding: 5px 12px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        border: 1px solid transparent;
        transition: all 0.15s ease;
    }
    .lt-filter-pill:hover,
    .lt-filter-pill.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    /* Equipment Table */
    .lt-table-responsive {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .lt-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.8rem;
    }
    .lt-table th {
        background: #f8fafc;
        padding: 12px 14px;
        color: #475569;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .lt-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
    }
    .lt-table tbody tr:hover {
        background: #f8fafc;
    }
    .lt-eq-name {
        font-weight: 800;
        color: #0f172a;
        display: block;
    }
    .lt-eq-sub {
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 2px;
    }
    .lt-cat-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #334155;
        white-space: nowrap;
    }
    .lt-cat-badge.hvac { background: #e0f2fe; color: #0369a1; }
    .lt-cat-badge.lighting { background: #fef9c3; color: #a16207; }
    .lt-cat-badge.it { background: #ede9fe; color: #6d28d9; }
    .lt-cat-badge.pumps { background: #ffedd5; color: #c2410c; }
    .lt-cat-badge.appliances { background: #dcfce7; color: #15803d; }

    .lt-kwh-highlight {
        font-weight: 800;
        color: #0f172a;
    }
    .lt-cost-highlight {
        font-weight: 900;
        color: #059669;
    }

    /* Mini Progress Bar for % Share */
    .lt-share-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 85px;
    }
    .lt-progress {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .lt-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #10b981, #059669);
    }

    .lt-action-btn {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-grid;
        place-items: center;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .lt-action-btn:hover {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }
    .lt-action-btn.delete:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    /* Modal Styles */
    .lt-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        place-items: center;
        z-index: 9999;
        padding: 20px;
    }
    .lt-modal-overlay.is-active {
        display: grid;
    }
    .lt-modal {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 580px;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.2);
        overflow: hidden;
        border: 1px solid #e2e8f0;
        animation: ltModalIn 0.2s ease-out;
    }
    @keyframes ltModalIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .lt-modal-head {
        padding: 18px 24px;
        background: #0f172a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .lt-modal-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #f8fafc;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .lt-modal-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 4px;
        transition: color 0.15s ease;
    }
    .lt-modal-close:hover { color: #fff; }
    .lt-modal-body {
        padding: 22px 24px;
        max-height: 75vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .lt-form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .lt-form-group label {
        font-size: 0.76rem;
        font-weight: 800;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .lt-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .lt-form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }
    .lt-input, .lt-select, .lt-textarea {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.84rem;
        color: #0f172a;
        outline: none;
        width: 100%;
        transition: border-color 0.15s ease;
    }
    .lt-input:focus, .lt-select:focus, .lt-textarea:focus {
        border-color: #10b981;
        background: #fff;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    .lt-calc-preview {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 12px;
        padding: 12px 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        text-align: center;
    }
    .lt-calc-preview-val {
        font-size: 1.05rem;
        font-weight: 900;
        color: #047857;
    }
    .lt-calc-preview-lbl {
        font-size: 0.67rem;
        font-weight: 700;
        color: #065f46;
        text-transform: uppercase;
    }
    .lt-modal-foot {
        padding: 14px 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Dark Mode Adjustments */
    body.dark-mode .lt-kpi-card,
    body.dark-mode .lt-card,
    body.dark-mode .lt-table-responsive {
        background: #18181b;
        border-color: #334155;
    }
    body.dark-mode .lt-kpi-value,
    body.dark-mode .lt-card-title,
    body.dark-mode .lt-eq-name,
    body.dark-mode .lt-kwh-highlight {
        color: #f8fafc;
    }
    body.dark-mode .lt-table th {
        background: #27272a;
        color: #94a3b8;
        border-color: #334155;
    }
    body.dark-mode .lt-table td {
        border-color: #27272a;
        color: #cbd5e1;
    }
    body.dark-mode .lt-table tbody tr:hover {
        background: #27272a;
    }
    body.dark-mode .lt-sim-box {
        background: #27272a;
        border-color: #3f3f46;
    }
    body.dark-mode .lt-sim-results {
        background: #18181b;
        border-color: #3f3f46;
    }
    body.dark-mode .lt-modal {
        background: #18181b;
        border-color: #3f3f46;
    }
    body.dark-mode .lt-modal-foot {
        background: #27272a;
        border-color: #3f3f46;
    }
    body.dark-mode .lt-input,
    body.dark-mode .lt-select,
    body.dark-mode .lt-textarea {
        background: #27272a;
        border-color: #3f3f46;
        color: #f8fafc;
    }
    body.dark-mode .lt-action-btn {
        background: #27272a;
        border-color: #3f3f46;
        color: #cbd5e1;
    }

    @media (max-width: 1024px) {
        .lt-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        .lt-analytics-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .lt-kpi-grid { grid-template-columns: 1fr; }
        .lt-hero { padding: 20px 18px; }
        .lt-facility-picker { width: 100%; }
        .lt-facility-select { width: 100%; min-width: unset; }
    }
</style>

<div class="lt-shell">

    <!-- Hero / Facility Header Banner -->
    <header class="lt-hero">
        <div class="lt-hero-top">
            <div>
                <div class="lt-kicker"><i class="fa-solid fa-plug-circle-bolt"></i> Facility Load Tracking & Equipment Computation</div>
                <h1 class="lt-title">
                    {{ $selectedFacility ? $selectedFacility->name : 'No Facility Available' }}
                </h1>
                <div class="lt-subtitle">
                    Track connected loads, compute daily & monthly energy consumption (kWh) per equipment, and compare with facility baseline and electricity billing.
                </div>
            </div>

            <!-- Facility Switcher & Export -->
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                @if($facilities->count() > 0)
                <div class="lt-facility-picker">
                    <label for="ltFacilitySwitcher"><i class="fa-solid fa-building"></i> Switch Facility:</label>
                    <select id="ltFacilitySwitcher" class="lt-facility-select" onchange="window.location.href='{{ route('modules.load-tracking.index') }}?facility_id=' + this.value;">
                        @foreach($facilities as $fac)
                            <option value="{{ $fac->id }}" {{ $selectedFacilityId === $fac->id ? 'selected' : '' }}>
                                {{ $fac->name }} ({{ $fac->type ?? 'Facility' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedFacility)
                    <a href="{{ route('modules.load-tracking.export', $selectedFacility->id) }}" class="lt-btn lt-btn-secondary" title="Export Load Schedule as CSV">
                        <i class="fa-solid fa-file-csv"></i> Export CSV
                    </a>
                @endif
                @endif
            </div>
        </div>

        @if($selectedFacility)
        <div class="lt-facility-meta">
            <span class="lt-meta-pill"><i class="fa-solid fa-location-dot"></i> {{ $selectedFacility->address ?: 'Quezon City' }}</span>
            <span class="lt-meta-pill"><i class="fa-solid fa-layer-group"></i> {{ $selectedFacility->type ?: 'Government Office' }}</span>
            <span class="lt-meta-pill"><i class="fa-solid fa-chart-simple"></i> Baseline: <strong>{{ is_numeric($selectedFacility->baseline_kwh) ? number_format((float)$selectedFacility->baseline_kwh, 2) . ' kWh' : 'Not Set' }}</strong></span>
            <span class="lt-meta-pill"><i class="fa-solid fa-peso-sign"></i> Applied Rate: <strong>₱{{ number_format($ratePerKwh, 2) }}/kWh</strong></span>
            <span class="lt-meta-pill"><i class="fa-solid fa-box-archive"></i> Total Registered Items: <strong>{{ $summary['total_items'] }} ({{ $summary['total_units'] }} units)</strong></span>
        </div>
        @endif
    </header>

    @if(! $selectedFacility)
        <div class="lt-card" style="text-align: center; padding: 40px;">
            <i class="fa-solid fa-building-circle-exclamation" style="font-size: 3rem; color: #94a3b8; margin-bottom: 12px;"></i>
            <h2 style="margin: 0 0 6px;">No Facilities Accessible</h2>
            <p style="color: #64748b;">Please register or assign a facility first in the Facility Registry.</p>
        </div>
    @else

        <!-- 4 Metric KPI Cards -->
        <section class="lt-kpi-grid">
            <!-- 1. Total Connected Load (kW) -->
            <div class="lt-kpi-card blue">
                <div class="lt-kpi-head">
                    <span class="lt-kpi-label">Connected Load</span>
                    <span class="lt-kpi-icon"><i class="fa-solid fa-bolt-lightning"></i></span>
                </div>
                <div class="lt-kpi-value">
                    {{ number_format($summary['total_connected_kw'], 2) }}
                    <span class="lt-kpi-unit">kW</span>
                </div>
                <div class="lt-kpi-sub">
                    <span><i class="fa-solid fa-calculator"></i> {{ number_format($summary['total_connected_watts'], 0) }} Total Watts across {{ $summary['total_units'] }} units</span>
                </div>
            </div>

            <!-- 2. Daily Energy Consumption -->
            <div class="lt-kpi-card indigo">
                <div class="lt-kpi-head">
                    <span class="lt-kpi-label">Daily Consumption</span>
                    <span class="lt-kpi-icon"><i class="fa-solid fa-calendar-day"></i></span>
                </div>
                <div class="lt-kpi-value">
                    {{ number_format($summary['total_daily_kwh'], 2) }}
                    <span class="lt-kpi-unit">kWh/day</span>
                </div>
                <div class="lt-kpi-sub">
                    <span><i class="fa-solid fa-coins"></i> ₱{{ number_format($summary['total_daily_kwh'] * $ratePerKwh, 2) }} estimated daily cost</span>
                </div>
            </div>

            <!-- 3. Monthly Energy Consumption -->
            <div class="lt-kpi-card emerald">
                <div class="lt-kpi-head">
                    <span class="lt-kpi-label">Monthly Consumption</span>
                    <span class="lt-kpi-icon"><i class="fa-solid fa-calendar-alt"></i></span>
                </div>
                <div class="lt-kpi-value">
                    {{ number_format($summary['total_monthly_kwh'], 2) }}
                    <span class="lt-kpi-unit">kWh/mo</span>
                </div>
                <div class="lt-kpi-sub">
                    @if($summary['baseline_kwh'])
                        @if($summary['baseline_variance'] <= 0)
                            <span class="lt-badge-pill success"><i class="fa-solid fa-check"></i> {{ abs($summary['baseline_variance']) }} kWh below baseline</span>
                        @else
                            <span class="lt-badge-pill warning"><i class="fa-solid fa-arrow-trend-up"></i> +{{ $summary['baseline_variance'] }} kWh over baseline</span>
                        @endif
                    @else
                        <span>Sum of equipment duty cycles</span>
                    @endif
                </div>
            </div>

            <!-- 4. Estimated Monthly Cost -->
            <div class="lt-kpi-card amber">
                <div class="lt-kpi-head">
                    <span class="lt-kpi-label">Est. Monthly Cost</span>
                    <span class="lt-kpi-icon"><i class="fa-solid fa-peso-sign"></i></span>
                </div>
                <div class="lt-kpi-value">
                    ₱{{ number_format($summary['total_monthly_cost'], 2) }}
                </div>
                <div class="lt-kpi-sub">
                    <span>Rate: ₱{{ number_format($ratePerKwh, 2) }} / kWh (Meralco standard)</span>
                </div>
            </div>
        </section>

        <!-- Visual Analytics & What-If Simulator -->
        <section class="lt-analytics-grid">
            
            <!-- Donut Chart: Load Breakdown by Category -->
            <div class="lt-card">
                <div class="lt-card-header">
                    <div>
                        <h2 class="lt-card-title"><i class="fa-solid fa-chart-pie"></i> Load Distribution by Category</h2>
                        <div class="lt-card-desc">Share of monthly kWh consumption by equipment type</div>
                    </div>
                </div>

                @if(empty($categoryBreakdown))
                    <div style="text-align: center; padding: 30px; color: #94a3b8;">
                        <i class="fa-solid fa-chart-pie" style="font-size: 2rem; margin-bottom: 8px;"></i>
                        <p>No equipment registered yet. Click <strong>+ Add Equipment</strong> below.</p>
                    </div>
                @else
                    <div class="lt-chart-box">
                        <canvas id="categoryDonutChart"></canvas>
                    </div>

                    <table class="lt-category-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Load (kW)</th>
                                <th>Monthly (kWh)</th>
                                <th>Est. Cost</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryBreakdown as $idx => $cat)
                                @php
                                    $palette = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
                                    $color = $palette[$idx % count($palette)];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="lt-cat-dot" style="background: {{ $color }};"></span>
                                        <strong>{{ $cat['category'] }}</strong>
                                        <small style="color: #94a3b8;">({{ $cat['units'] }} units)</small>
                                    </td>
                                    <td>{{ number_format($cat['connected_kw'], 2) }} kW</td>
                                    <td>{{ number_format($cat['monthly_kwh'], 2) }} kWh</td>
                                    <td>₱{{ number_format($cat['monthly_cost'], 2) }}</td>
                                    <td><strong>{{ $cat['percentage'] }}%</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- What-if Energy Savings Simulator -->
            <div class="lt-card">
                <div class="lt-card-header">
                    <div>
                        <h2 class="lt-card-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Energy Savings Simulator ("What-If")</h2>
                        <div class="lt-card-desc">Simulate operational adjustments to reduce facility electricity costs</div>
                    </div>
                </div>

                <div class="lt-sim-box">
                    <div style="font-size: 0.8rem; font-weight: 800; color: #1e293b;">
                        Scenario: Reduce daily operating hours of all cooling & lighting loads
                    </div>

                    <div class="lt-slider-wrap">
                        <div class="lt-slider-labels">
                            <span>Hours Reduction per Equipment:</span>
                            <strong id="ltReductionDisplay" style="color: #059669; font-size: 0.9rem;">1.0 hour/day</strong>
                        </div>
                        <input type="range" id="ltReductionSlider" class="lt-range" min="0.5" max="4.0" step="0.5" value="1.0" oninput="updateSimulation(this.value)">
                        <div class="lt-slider-labels">
                            <span>0.5 hr</span>
                            <span>2.0 hrs</span>
                            <span>4.0 hrs</span>
                        </div>
                    </div>

                    <div class="lt-sim-results">
                        <div class="lt-sim-kpi">
                            <div class="lt-sim-kpi-val" id="simDailyKwhSaved">0.00</div>
                            <div class="lt-sim-kpi-lbl">Daily kWh Saved</div>
                        </div>
                        <div class="lt-sim-kpi">
                            <div class="lt-sim-kpi-val" id="simMonthlyKwhSaved">0.00</div>
                            <div class="lt-sim-kpi-lbl">Monthly kWh Saved</div>
                        </div>
                        <div class="lt-sim-kpi">
                            <div class="lt-sim-kpi-val" id="simMonthlyPesosSaved">₱0.00</div>
                            <div class="lt-sim-kpi-lbl">Monthly ₱ Savings</div>
                        </div>
                    </div>

                    <div style="font-size: 0.73rem; color: #64748b; line-height: 1.4;">
                        <i class="fa-solid fa-lightbulb" style="color: #f59e0b;"></i>
                        <strong>Pro-Tip:</strong> Turning off non-essential air conditioners 1 hour before office closing saves thousands of pesos annually without sacrificing comfort.
                    </div>
                </div>

                <!-- Top 5 Power Consuming Loads Bar Chart -->
                <div style="margin-top: 18px;">
                    <div style="font-size: 0.82rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                        <i class="fa-solid fa-arrow-down-wide-short"></i> Top 5 Highest Consuming Equipment
                    </div>
                    @forelse($topConsumers as $top)
                        <div style="margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 700; margin-bottom: 2px;">
                                <span>{{ $top['name'] }} <small style="color: #64748b;">({{ $top['category'] }})</small></span>
                                <span style="color: #059669;">{{ number_format($top['monthly_kwh'], 1) }} kWh (₱{{ number_format($top['monthly_cost'], 0) }}) &bull; {{ $top['percentage'] }}%</span>
                            </div>
                            <div class="lt-progress">
                                <div class="lt-progress-fill" style="width: {{ max(5, min(100, $top['percentage'])) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 0.75rem; color: #94a3b8;">No equipment data to rank.</div>
                    @endforelse
                </div>

            </div>

        </section>

        <!-- Equipment Inventory & Computation Table -->
        <section class="lt-card" style="margin-top: 6px;">
            
            <div class="lt-inventory-header">
                <div>
                    <h2 class="lt-card-title"><i class="fa-solid fa-list-check"></i> Equipment Load Schedule & Calculations</h2>
                    <div class="lt-card-desc">Detailed energy consumption formula: $(W \times Q \times \text{Hours/Day} \times \text{Days/Month}) / 1000$</div>
                </div>

                <div class="lt-actions-group">
                    <form method="GET" action="{{ route('modules.load-tracking.index') }}" style="display: flex; gap: 8px; align-items: center;">
                        <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                        <input type="hidden" name="category" value="{{ $categoryFilter }}">
                        <div class="lt-search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" class="lt-search-input" placeholder="Search equipment..." value="{{ $search }}" onchange="this.form.submit()">
                        </div>
                    </form>

                    @if($canManage)
                        <button type="button" class="lt-btn lt-btn-primary" onclick="openAddModal()">
                            <i class="fa-solid fa-plus"></i> Add Equipment
                        </button>
                    @endif
                </div>
            </div>

            <!-- Category Filter Pills -->
            <div class="lt-pills-bar">
                <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => 'all', 'search' => $search]) }}"
                   class="lt-filter-pill {{ $categoryFilter === 'all' || empty($categoryFilter) ? 'active' : '' }}">
                    All Categories ({{ $summary['total_items'] }})
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('modules.load-tracking.index', ['facility_id' => $selectedFacilityId, 'category' => $cat, 'search' => $search]) }}"
                       class="lt-filter-pill {{ $categoryFilter === $cat ? 'active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>

            <!-- Table -->
            <div class="lt-table-responsive">
                <table class="lt-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipment / Load Name</th>
                            <th>Category</th>
                            <th>Location / Area</th>
                            <th>Assigned Meter</th>
                            <th style="text-align: right;">Qty</th>
                            <th style="text-align: right;">Rated (W)</th>
                            <th style="text-align: right;">Total Load</th>
                            <th style="text-align: right;">Duty Cycle</th>
                            <th style="text-align: right;">Daily kWh</th>
                            <th style="text-align: right;">Monthly kWh</th>
                            <th style="text-align: right;">Est. Cost</th>
                            <th style="text-align: center;">% Share</th>
                            @if($canManage)
                            <th style="text-align: center;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipments as $idx => $eq)
                            @php
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
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td style="color: #94a3b8; font-weight: 700;">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="lt-eq-name">{{ $eq->equipment_name }}</span>
                                    @if($eq->notes)
                                        <div class="lt-eq-sub"><i class="fa-solid fa-circle-info"></i> {{ $eq->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="lt-cat-badge {{ $catClass }}">{{ $eq->category ?: 'General' }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #475569;"><i class="fa-solid fa-map-pin" style="color: #94a3b8;"></i> {{ $eq->location ?: 'Facility Wide' }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 0.74rem; font-weight: 700; color: #0284c7;">
                                        <i class="fa-solid fa-gauge"></i> {{ $eq->meter_name }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 800;">{{ $eq->quantity }}</td>
                                <td style="text-align: right;">{{ number_format($eq->rated_watts, 0) }} W</td>
                                <td style="text-align: right; font-weight: 800; color: #2563eb;">{{ number_format($totalKw, 2) }} kW</td>
                                <td style="text-align: right; font-size: 0.75rem;">
                                    <strong>{{ number_format($eq->operating_hours_per_day, 1) }}h</strong> / day<br>
                                    <span style="color: #64748b;">{{ $eq->operating_days_per_month }} days/mo</span>
                                </td>
                                <td style="text-align: right; font-weight: 800;">{{ number_format($dailyKwh, 2) }}</td>
                                <td style="text-align: right;" class="lt-kwh-highlight">{{ number_format($monthlyKwh, 2) }}</td>
                                <td style="text-align: right;" class="lt-cost-highlight">₱{{ number_format($monthlyCost, 2) }}</td>
                                <td>
                                    <div class="lt-share-bar">
                                        <div class="lt-progress">
                                            <div class="lt-progress-fill" style="width: {{ min(100, $sharePct) }}%;"></div>
                                        </div>
                                        <span style="font-size: 0.72rem; font-weight: 800; min-width: 32px;">{{ $sharePct }}%</span>
                                    </div>
                                </td>
                                @if($canManage)
                                <td style="text-align: center; white-space: nowrap;">
                                    <button type="button" class="lt-action-btn" title="Edit Equipment" onclick='openEditModal(@json($eq))'>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="lt-action-btn delete" title="Delete Equipment" onclick="confirmDeleteEquipment({{ $eq->id }}, '{{ addslashes($eq->equipment_name) }}')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? 14 : 13 }}" style="text-align: center; padding: 36px; color: #94a3b8;">
                                    <i class="fa-solid fa-box-open" style="font-size: 2.2rem; margin-bottom: 10px; display: block;"></i>
                                    <strong>No equipment records found for this filter.</strong>
                                    <div style="font-size: 0.78rem; margin-top: 4px;">Click <strong>+ Add Equipment</strong> above or select a preset to begin calculating load consumption.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($equipments->count() > 0)
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 900;">
                            <td colspan="5">TOTAL LOAD & CONSUMPTION</td>
                            <td style="text-align: right;">{{ $summary['total_units'] }}</td>
                            <td style="text-align: right;">-</td>
                            <td style="text-align: right; color: #2563eb;">{{ number_format($summary['total_connected_kw'], 2) }} kW</td>
                            <td style="text-align: right;">-</td>
                            <td style="text-align: right;">{{ number_format($summary['total_daily_kwh'], 2) }}</td>
                            <td style="text-align: right; color: #047857;">{{ number_format($summary['total_monthly_kwh'], 2) }}</td>
                            <td style="text-align: right; color: #059669;">₱{{ number_format($summary['total_monthly_cost'], 2) }}</td>
                            <td style="text-align: center;">100%</td>
                            @if($canManage) <td></td> @endif
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

        </section>

    @endif

</div>

<!-- ADD EQUIPMENT MODAL -->
@if($canManage && $selectedFacility)
<div id="ltAddModal" class="lt-modal-overlay">
    <div class="lt-modal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
        <div class="lt-modal-head">
            <h3 id="addModalTitle" class="lt-modal-title"><i class="fa-solid fa-plus-circle"></i> Add Equipment Load</h3>
            <button type="button" class="lt-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('modules.load-tracking.equipment.store') }}" id="ltAddForm">
            @csrf
            <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">

            <div class="lt-modal-body">
                
                <!-- Quick Preset Selector -->
                <div class="lt-form-group" style="background: #f0fdf4; border: 1px dashed #86efac; padding: 10px 12px; border-radius: 10px;">
                    <label style="color: #047857;"><i class="fa-solid fa-bolt"></i> Quick Auto-Fill Appliance Preset:</label>
                    <select id="ltPresetSelector" class="lt-select" onchange="applyPreset(this.value, 'add')">
                        <option value="">-- Choose Common Appliance to Auto-fill --</option>
                        @foreach($presets as $pIdx => $preset)
                            <option value="{{ $pIdx }}">{{ $preset['name'] }} ({{ $preset['rated_watts'] }}W)</option>
                        @endforeach
                    </select>
                </div>

                <div class="lt-form-group">
                    <label>Equipment / Load Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="equipment_name" id="add_equipment_name" class="lt-input" placeholder="e.g. Split Type Inverter AC (Admin Office)" required>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Category</label>
                        <select name="category" id="add_category" class="lt-select">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lt-form-group">
                        <label>Location / Room</label>
                        <input type="text" name="location" id="add_location" class="lt-input" placeholder="e.g. 2nd Floor Admin Room">
                    </div>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Assigned Meter Scope</label>
                        <select name="meter_scope" id="add_meter_scope" class="lt-select" onchange="toggleMeterInputs('add')">
                            <option value="facility">Facility General / Unmetered</option>
                            @if($mainMeters->count() > 0)
                                <option value="main">Main Utility Meter</option>
                            @endif
                            @if($subMeters->count() > 0)
                                <option value="sub">Submeter</option>
                            @endif
                        </select>
                    </div>

                    <div class="lt-form-group" id="add_main_meter_wrap" style="display: none;">
                        <label>Select Main Meter</label>
                        <select name="facility_meter_id" id="add_facility_meter_id" class="lt-select">
                            @foreach($mainMeters as $mm)
                                <option value="{{ $mm->id }}">{{ $mm->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lt-form-group" id="add_sub_meter_wrap" style="display: none;">
                        <label>Select Submeter</label>
                        <select name="submeter_id" id="add_submeter_id" class="lt-select">
                            @foreach($subMeters as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->submeter_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Load Specification Inputs -->
                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Quantity <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="quantity" id="add_quantity" class="lt-input" value="1" min="1" max="9999" required oninput="recalcModalPreview('add')">
                    </div>

                    <div class="lt-form-group">
                        <label>Rated Power (Watts) <span style="color: #dc2626;">*</span></label>
                        <input type="number" step="0.1" name="rated_watts" id="add_rated_watts" class="lt-input" placeholder="e.g. 1200" min="0.1" required oninput="recalcModalPreview('add')">
                    </div>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Operating Hours / Day <span style="color: #dc2626;">*</span></label>
                        <input type="number" step="0.1" name="operating_hours_per_day" id="add_hours" class="lt-input" value="8" min="0.1" max="24" required oninput="recalcModalPreview('add')">
                    </div>

                    <div class="lt-form-group">
                        <label>Operating Days / Month <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="operating_days_per_month" id="add_days" class="lt-input" value="22" min="1" max="31" required oninput="recalcModalPreview('add')">
                    </div>
                </div>

                <!-- Live Computation Preview -->
                <div class="lt-calc-preview">
                    <div>
                        <div class="lt-calc-preview-val" id="add_prev_daily">0.00 kWh</div>
                        <div class="lt-calc-preview-lbl">Daily Energy</div>
                    </div>
                    <div>
                        <div class="lt-calc-preview-val" id="add_prev_monthly">0.00 kWh</div>
                        <div class="lt-calc-preview-lbl">Monthly Energy</div>
                    </div>
                    <div>
                        <div class="lt-calc-preview-val" id="add_prev_cost">₱0.00</div>
                        <div class="lt-calc-preview-lbl">Est. Monthly Cost</div>
                    </div>
                </div>

                <div class="lt-form-group">
                    <label>Notes / Asset Tag (Optional)</label>
                    <textarea name="notes" id="add_notes" class="lt-textarea" rows="2" placeholder="e.g. Asset #EQ-2026-004, Energy star compliant inverter unit"></textarea>
                </div>

            </div>
            <div class="lt-modal-foot">
                <button type="button" class="lt-btn lt-btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="lt-btn lt-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Equipment</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT EQUIPMENT MODAL -->
<div id="ltEditModal" class="lt-modal-overlay">
    <div class="lt-modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="lt-modal-head">
            <h3 id="editModalTitle" class="lt-modal-title"><i class="fa-solid fa-pen-to-square"></i> Edit Equipment Load</h3>
            <button type="button" class="lt-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="" id="ltEditForm">
            @csrf
            @method('PUT')

            <div class="lt-modal-body">
                
                <div class="lt-form-group">
                    <label>Equipment / Load Name <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="equipment_name" id="edit_equipment_name" class="lt-input" required>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Category</label>
                        <select name="category" id="edit_category" class="lt-select">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lt-form-group">
                        <label>Location / Room</label>
                        <input type="text" name="location" id="edit_location" class="lt-input">
                    </div>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Assigned Meter Scope</label>
                        <select name="meter_scope" id="edit_meter_scope" class="lt-select" onchange="toggleMeterInputs('edit')">
                            <option value="facility">Facility General / Unmetered</option>
                            @if($mainMeters->count() > 0)
                                <option value="main">Main Utility Meter</option>
                            @endif
                            @if($subMeters->count() > 0)
                                <option value="sub">Submeter</option>
                            @endif
                        </select>
                    </div>

                    <div class="lt-form-group" id="edit_main_meter_wrap" style="display: none;">
                        <label>Select Main Meter</label>
                        <select name="facility_meter_id" id="edit_facility_meter_id" class="lt-select">
                            @foreach($mainMeters as $mm)
                                <option value="{{ $mm->id }}">{{ $mm->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lt-form-group" id="edit_sub_meter_wrap" style="display: none;">
                        <label>Select Submeter</label>
                        <select name="submeter_id" id="edit_submeter_id" class="lt-select">
                            @foreach($subMeters as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->submeter_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Load Specification Inputs -->
                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Quantity <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="quantity" id="edit_quantity" class="lt-input" min="1" max="9999" required oninput="recalcModalPreview('edit')">
                    </div>

                    <div class="lt-form-group">
                        <label>Rated Power (Watts) <span style="color: #dc2626;">*</span></label>
                        <input type="number" step="0.1" name="rated_watts" id="edit_rated_watts" class="lt-input" min="0.1" required oninput="recalcModalPreview('edit')">
                    </div>
                </div>

                <div class="lt-form-row">
                    <div class="lt-form-group">
                        <label>Operating Hours / Day <span style="color: #dc2626;">*</span></label>
                        <input type="number" step="0.1" name="operating_hours_per_day" id="edit_hours" class="lt-input" min="0.1" max="24" required oninput="recalcModalPreview('edit')">
                    </div>

                    <div class="lt-form-group">
                        <label>Operating Days / Month <span style="color: #dc2626;">*</span></label>
                        <input type="number" name="operating_days_per_month" id="edit_days" class="lt-input" min="1" max="31" required oninput="recalcModalPreview('edit')">
                    </div>
                </div>

                <!-- Live Computation Preview -->
                <div class="lt-calc-preview">
                    <div>
                        <div class="lt-calc-preview-val" id="edit_prev_daily">0.00 kWh</div>
                        <div class="lt-calc-preview-lbl">Daily Energy</div>
                    </div>
                    <div>
                        <div class="lt-calc-preview-val" id="edit_prev_monthly">0.00 kWh</div>
                        <div class="lt-calc-preview-lbl">Monthly Energy</div>
                    </div>
                    <div>
                        <div class="lt-calc-preview-val" id="edit_prev_cost">₱0.00</div>
                        <div class="lt-calc-preview-lbl">Est. Monthly Cost</div>
                    </div>
                </div>

                <div class="lt-form-group">
                    <label>Notes / Asset Tag</label>
                    <textarea name="notes" id="edit_notes" class="lt-textarea" rows="2"></textarea>
                </div>

            </div>
            <div class="lt-modal-foot">
                <button type="button" class="lt-btn lt-btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="lt-btn lt-btn-primary"><i class="fa-solid fa-check"></i> Update Equipment</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE CONFIRMATION FORM -->
<form id="ltDeleteForm" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endif

<!-- Scripts for Dynamic Charts, Modals, Presets, and Simulator -->
<script>
    const ratePerKwh = {{ (float)$ratePerKwh }};
    const totalConnectedWatts = {{ (float)($summary['total_connected_watts'] ?? 0) }};
    const presetsData = @json($presets);

    // Initial Donut Chart Setup
    document.addEventListener('DOMContentLoaded', function () {
        const catLabels = @json(array_column($categoryBreakdown, 'category'));
        const catData = @json(array_column($categoryBreakdown, 'monthly_kwh'));

        const donutCanvas = document.getElementById('categoryDonutChart');
        if (donutCanvas && catData.length > 0) {
            new Chart(donutCanvas, {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{
                        data: catData,
                        backgroundColor: [
                            '#10b981',
                            '#3b82f6',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ec4899',
                            '#06b6d4',
                            '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
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
                                    return ` ${context.label}: ${val.toLocaleString()} kWh (₱${cost})`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // Initialize Simulator
        updateSimulation(1.0);
    });

    // What-If Simulator Calculation
    function updateSimulation(hoursReduction) {
        const hours = parseFloat(hoursReduction) || 0;
        document.getElementById('ltReductionDisplay').textContent = hours.toFixed(1) + ' hours/day';

        // Approximate total facility duty cycle reduction
        // Daily kWh saved = (Total Watts * Hours Saved) / 1000
        const dailyKwhSaved = (totalConnectedWatts * hours) / 1000;
        const monthlyDays = 22; // standard monthly working days
        const monthlyKwhSaved = dailyKwhSaved * monthlyDays;
        const monthlyPesosSaved = monthlyKwhSaved * ratePerKwh;

        document.getElementById('simDailyKwhSaved').textContent = dailyKwhSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kWh';
        document.getElementById('simMonthlyKwhSaved').textContent = monthlyKwhSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kWh';
        document.getElementById('simMonthlyPesosSaved').textContent = '₱' + monthlyPesosSaved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    // Delete Confirmation
    function confirmDeleteEquipment(id, name) {
        if (confirm(`Are you sure you want to remove "${name}" from this facility's load schedule?`)) {
            const form = document.getElementById('ltDeleteForm');
            form.action = "{{ url('modules/load-tracking/equipment') }}/" + id;
            form.submit();
        }
    }
</script>
@endsection
