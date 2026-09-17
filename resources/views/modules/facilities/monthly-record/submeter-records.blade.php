@extends('layouts.qc-admin')
@section('title', ($timeframe ?? 'monthly') === 'weekly' ? 'Sub-meter Weekly Records' : 'Sub-meter Monthly Records')

@section('content')
<style>
    .monthly-shell {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding-bottom: 6px;
    }

    .report-card-container.monthly-report-card-container {
        width: 100%;
        padding: 26px;
        border: 1px solid #dbe5f2;
        border-radius: 26px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 58%, #eef4ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
        box-sizing: border-box;
    }

    .report-card-container.monthly-report-card-container,
    .report-card-container.monthly-report-card-container * {
        box-sizing: border-box;
    }

    .monthly-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .monthly-card-body {
        padding: 16px 18px;
    }

    .monthly-alert {
        padding: 12px 14px;
        border-radius: 12px;
        font-weight: 700;
    }

    .monthly-alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .monthly-alert.error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .monthly-alert.warn {
        background: #fff7ed;
        color: #9a3412;
    }

    .monthly-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .monthly-header h1 {
        margin: 0;
        color: #2563eb;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .monthly-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .92rem;
    }

    .monthly-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-action-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        min-height: 50px;
        padding: 0 16px;
        font-weight: 800;
        font-size: .92rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
        box-sizing: border-box;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .monthly-action-btn:hover {
        transform: translateY(-1px);
    }

    .monthly-action-btn.is-info {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .monthly-action-btn.is-primary {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        border: none;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.24);
    }

    .monthly-header-identity { display:flex; align-items:flex-start; gap:14px; }
    .monthly-header-icon { width:48px; height:48px; flex:0 0 48px; display:grid; place-items:center; border-radius:14px; color:#fff; background:linear-gradient(135deg,#2563eb,#6366f1); box-shadow:0 9px 20px rgba(37,99,235,.2); font-size:1.25rem; }
    .monthly-header-context { display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
    .monthly-context-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border:1px solid #dbe5f2; border-radius:999px; color:#475569; background:#fff; font-size:.68rem; font-weight:800; }

    .monthly-performance-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:14px; }
    .monthly-performance-card { position:relative; overflow:hidden; min-height:108px; padding:16px 17px; border:1px solid #dbe5f2; border-radius:16px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.05); }
    .monthly-performance-card::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:var(--monthly-accent,#2563eb); }
    .monthly-performance-top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .monthly-performance-label { color:#64748b; font-size:.69rem; font-weight:850; text-transform:uppercase; letter-spacing:.045em; }
    .monthly-performance-icon { width:33px; height:33px; display:grid; place-items:center; border-radius:10px; color:var(--monthly-accent,#2563eb); background:var(--monthly-soft,#eff6ff); font-size:1rem; }
    .monthly-performance-value { margin-top:10px; color:#0f172a; font-size:1.42rem; line-height:1; font-weight:950; }
    .monthly-performance-note { margin-top:6px; color:#64748b; font-size:.66rem; font-weight:650; }
    .monthly-performance-card.records { --monthly-accent:#2563eb; --monthly-soft:#eff6ff; }
    .monthly-performance-card.approved { --monthly-accent:#059669; --monthly-soft:#ecfdf5; }
    .monthly-performance-card.pending { --monthly-accent:#f59e0b; --monthly-soft:#fffbeb; }
    .monthly-performance-card.attention { --monthly-accent:#8b5cf6; --monthly-soft:#f5f3ff; }

    .monthly-table-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        background: #fcfdff;
    }

    .monthly-table-title {
        color: #1e293b;
        font-weight: 800;
        font-size: 1rem;
    }

    .monthly-table-subtitle {
        color: #64748b;
        font-size: .84rem;
        margin-top: 2px;
    }

    .monthly-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 800;
        padding: 4px 10px;
    }

    .monthly-chip.is-success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #166534;
    }

    .monthly-record-table-filter {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .monthly-filter-heading { min-width: 110px; padding-bottom: 4px; }
    .monthly-filter-heading strong { display:flex; align-items:center; gap:7px; color:#334155; font-size:.73rem; }
    .monthly-filter-heading span { display:block; margin-top:3px; color:#94a3b8; font-size:.6rem; }

    .monthly-record-table-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
    }

    .monthly-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .monthly-field label {
        color: #475569;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .monthly-field select,
    .monthly-field input {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: .85rem;
        color: #1e293b;
        background: #fff;
        min-height: 38px;
    }

    .monthly-apply-btn {
        border: 1px solid #2563eb;
        background: linear-gradient(105deg, #2563eb, #4f46e5);
        color: #fff;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: .82rem;
        font-weight: 800;
        cursor: pointer;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .monthly-reset-btn {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: .82rem;
        font-weight: 800;
        text-decoration: none;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
    }

    .monthly-table-wrap {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 480px;
        border-top: 1px solid #dbe4f0;
        background: #ffffff;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .monthly-table-wrap::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .monthly-table-wrap::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .monthly-table-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .monthly-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .monthly-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .monthly-table th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8fafc;
        color: #475569;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
        text-align: left;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .monthly-table td {
        color: #1e293b;
        font-size: .82rem;
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .monthly-table tbody tr:hover {
        background: #f1f7ff;
    }

    .monthly-dial-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        font-size: 0.72rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .monthly-dial-chip i {
        color: #15803d;
        font-size: 0.72rem;
    }

    .monthly-dial-chip .dial-arrow {
        color: #22c55e;
        font-weight: 900;
        font-size: 0.74rem;
    }

    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip {
        background: #052e16 !important;
        border-color: #166534 !important;
        color: #86efac !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip i {
        color: #4ade80 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip .dial-arrow {
        color: #86efac !important;
    }

    .monthly-modal-overlay {
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15,23,42,0.6);
        backdrop-filter: blur(4px);
    }

    .monthly-modal-overlay.is-active {
        display: flex;
    }

    .monthly-modal-card {
        width: min(580px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        background: #ffffff;
        border: 1px solid #dbe5f2;
        border-radius: 20px;
        box-shadow: 0 28px 80px rgba(15,23,42,.30);
        overflow-y: auto;
        padding: 24px;
    }

    @media (max-width: 900px) {
        .monthly-performance-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .monthly-record-table-filter { flex-direction: column; align-items: stretch; }
    }

    @media (max-width: 600px) {
        .monthly-performance-grid { grid-template-columns: 1fr; }
        .report-card-container.monthly-report-card-container { padding: 14px; border-radius: 18px; }
    }
</style>

@php
    $months = $monthLabels ?? [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $mainMeterOptions = collect($mainMeterOptions ?? []);
    $selectedMainMeterId = (int) ($selectedMainMeterId ?? 0);
    $latestSubmeterDialsJson = json_encode($latestSubmeterDials ?? []);
@endphp

<div class="monthly-shell">
    <div class="report-card-container monthly-report-card-container">

        @if(session('success'))
            <div class="monthly-alert success" style="margin-bottom:14px;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="monthly-alert error" style="margin-bottom:14px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ session('error') ?? 'Please check the form for errors:' }}
                @if(isset($errors) && $errors->any())
                    <ul style="margin:6px 0 0 20px;font-size:0.84rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        {{-- Header Card --}}
        <div class="monthly-card" style="margin-bottom:14px;">
            <div class="monthly-card-body">
                <div class="monthly-header">
                    <div class="monthly-header-identity">
                        <span class="monthly-header-icon"><i class="fa-solid {{ ($timeframe ?? 'monthly') === 'weekly' ? 'fa-chart-column' : 'fa-code-branch' }}"></i></span>
                        <div>
                            <h1>Sub-meter {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly' : 'Monthly' }} Records</h1>
                            <p>{{ ($timeframe ?? 'monthly') === 'weekly' ? 'Downstream submeter weekly consumption, week-by-week baseline variance, and dial progression tracking.' : 'Downstream submeter energy consumption, baseline performance, and dial tracking.' }}</p>
                            <div class="monthly-header-context">
                                <span class="monthly-context-chip"><i class="fa-solid fa-building"></i> {{ $facility->name }}</span>
                                <span class="monthly-context-chip"><i class="fa-solid fa-code-branch"></i> {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly Accounting' : 'Sub-meter Accounting' }}</span>
                                <span class="monthly-context-chip"><i class="fa-solid fa-calendar"></i> {{ $selectedYear }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="monthly-actions">
                        {{-- Timeframe Switcher Tabs --}}
                        <div style="display:inline-flex;background:#f1f5f9;padding:3px;border-radius:12px;border:1px solid #cbd5e1;gap:3px;">
                            <a href="{{ route('facilities.monthly-records.submeters', array_merge(request()->query(), ['facility' => $facility->id, 'timeframe' => 'monthly'])) }}"
                               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:0.82rem;font-weight:800;text-decoration:none;transition:all 0.2s;{{ ($timeframe ?? 'monthly') === 'monthly' ? 'background:#fff;color:#2563eb;box-shadow:0 2px 6px rgba(0,0,0,0.08);' : 'color:#64748b;' }}">
                                <i class="fa-solid fa-calendar-days"></i> Monthly Records
                            </a>
                            <a href="{{ route('facilities.weekly-records.submeters', array_merge(request()->query(), ['facility' => $facility->id, 'timeframe' => 'weekly'])) }}"
                               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;font-size:0.82rem;font-weight:800;text-decoration:none;transition:all 0.2s;{{ ($timeframe ?? 'monthly') === 'weekly' ? 'background:#fff;color:#2563eb;box-shadow:0 2px 6px rgba(0,0,0,0.08);' : 'color:#64748b;' }}">
                                <i class="fa-solid fa-chart-column"></i> Weekly Records
                            </a>
                        </div>

                        <a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'record_scope' => 'submeters']) }}" class="monthly-action-btn is-info">
                            <i class="fa-solid fa-arrow-left"></i> Back to Main Records
                        </a>
                        @if($subMeterOptions->isNotEmpty())
                        <button type="button" onclick="openAddSubmeterModal()" class="monthly-action-btn is-primary">
                            <i class="fa fa-plus"></i> {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Add Weekly Record' : 'Add Sub-meter Record' }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance KPIs Grid --}}
        <section class="monthly-performance-grid" aria-label="Sub-meter performance summary">
            <article class="monthly-performance-card records">
                <div class="monthly-performance-top">
                    <span class="monthly-performance-label">Filtered {{ ($timeframe ?? 'monthly') === 'weekly' ? 'weekly' : '' }} records</span>
                    <span class="monthly-performance-icon"><i class="fa-solid fa-file-lines"></i></span>
                </div>
                <div class="monthly-performance-value">{{ number_format($totalRecords) }}</div>
                <div class="monthly-performance-note">{{ $subMeterOptions->count() }} active submeter(s) tracked</div>
            </article>

            <article class="monthly-performance-card approved">
                <div class="monthly-performance-top">
                    <span class="monthly-performance-label">Total {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly ' : '' }}Energy (kWh)</span>
                    <span class="monthly-performance-icon"><i class="fa-solid fa-bolt"></i></span>
                </div>
                <div class="monthly-performance-value">{{ number_format($totalKwh, 2) }}</div>
                <div class="monthly-performance-note">Cumulative submeter consumption</div>
            </article>

            <article class="monthly-performance-card pending">
                <div class="monthly-performance-top">
                    <span class="monthly-performance-label">Total {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly ' : '' }}Cost</span>
                    <span class="monthly-performance-icon"><i class="fa-solid fa-peso-sign"></i></span>
                </div>
                <div class="monthly-performance-value">PHP {{ number_format($totalCost, 2) }}</div>
                <div class="monthly-performance-note">Computed at facility tariff</div>
            </article>

            <article class="monthly-performance-card attention">
                <div class="monthly-performance-top">
                    <span class="monthly-performance-label">Monitoring {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Period' : 'Year' }}</span>
                    <span class="monthly-performance-icon"><i class="fa-solid fa-calendar"></i></span>
                </div>
                <div class="monthly-performance-value">{{ $selectedYear }} @if(($timeframe ?? 'monthly') === 'weekly' && $selectedWeek > 0)<span style="font-size:0.9rem;font-weight:700;color:#64748b;">(W{{ $selectedWeek }})</span>@endif</div>
                <div class="monthly-performance-note">Active accounting period</div>
            </article>
        </section>

        {{-- Table Card --}}
        <div class="monthly-card">
            <div class="monthly-table-header">
                <div>
                    <div class="monthly-table-title">Sub-meter {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly' : 'Monthly' }} Records Table</div>
                    <div class="monthly-table-subtitle">
                        {{ $totalRecords }} {{ ($timeframe ?? 'monthly') === 'weekly' ? 'weekly' : 'monthly' }} record(s) for {{ $selectedYear }} under Sub-meter Accounting
                    </div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span class="monthly-chip">Total kWh: {{ number_format($totalKwh, 2) }}</span>
                    <span class="monthly-chip is-success">Total Cost: PHP {{ number_format($totalCost, 2) }}</span>
                </div>
            </div>

            <div class="monthly-record-table-filter">
                <div class="monthly-filter-heading">
                    <strong><i class="fa-solid fa-filter"></i> Filter records</strong>
                    <span>Narrow the table view</span>
                </div>
                <form method="GET" action="{{ route(($timeframe ?? 'monthly') === 'weekly' ? 'facilities.weekly-records.submeters' : 'facilities.monthly-records.submeters', $facility->id) }}" class="monthly-record-table-filter-form">
                    <input type="hidden" name="timeframe" value="{{ $timeframe ?? 'monthly' }}">

                    <div class="monthly-field" style="min-width:110px;">
                        <label>Year</label>
                        <select name="year">
                            @foreach($yearOptions as $year)
                                <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="monthly-field" style="min-width:130px;">
                        <label>Month</label>
                        <select name="month">
                            <option value="0" @selected($selectedMonth === 0)>All Months</option>
                            @foreach($months as $monthNumber => $monthLabel)
                                <option value="{{ $monthNumber }}" @selected($selectedMonth === (int) $monthNumber)>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(($timeframe ?? 'monthly') === 'weekly')
                    <div class="monthly-field" style="min-width:160px;">
                        <label>Week</label>
                        <select name="week">
                            <option value="0" @selected(($selectedWeek ?? 0) === 0)>All Weeks (1–4)</option>
                            @foreach(($weekLabels ?? [1 => 'Week 1 (Days 1–7)', 2 => 'Week 2 (Days 8–14)', 3 => 'Week 3 (Days 15–21)', 4 => 'Week 4 (Days 22–31)']) as $wNum => $wLabel)
                                <option value="{{ $wNum }}" @selected(($selectedWeek ?? 0) === (int) $wNum)>{{ $wLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="monthly-field" style="min-width:210px;">
                        <label>Main Meter</label>
                        <select name="main_meter_id" required>
                            @if($mainMeterOptions->count() > 1)
                                <option value="" disabled @selected($selectedMainMeterId === 0)>Select Main Meter</option>
                            @endif
                            @foreach($mainMeterOptions as $mainMeter)
                                <option value="{{ $mainMeter->id }}" @selected($selectedMainMeterId === (int) $mainMeter->id)>{{ $mainMeter->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="monthly-field" style="min-width:230px;flex:1;">
                        <label>Sub-meter</label>
                        <select name="meter_id" @disabled($selectedMainMeterId === 0)>
                            @if($selectedMainMeterId === 0)
                                <option value="">Select a Main Meter first</option>
                            @else
                                <option value="0" @selected((int) $selectedMeterId === 0)>All Sub-meters</option>
                            @endif
                            @foreach($subMeterOptions as $meter)
                                <option value="{{ $meter->id }}" @selected((int) $selectedMeterId === (int) $meter->id)>{{ $meter->meter_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex;gap:6px;">
                        <button type="submit" class="monthly-apply-btn">
                            <i class="fa-solid fa-filter"></i> Apply
                        </button>
                        <a href="{{ route(($timeframe ?? 'monthly') === 'weekly' ? 'facilities.weekly-records.submeters' : 'facilities.monthly-records.submeters', ['facility' => $facility->id]) }}" class="monthly-reset-btn">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="monthly-table-wrap">
                <table class="monthly-table">
                    <thead>
                        <tr>
                            <th style="padding-left:18px;">Period / Sub-meter</th>
                            <th>Consumption</th>
                            <th>Performance</th>
                            <th>Billing</th>
                            <th>Status</th>
                            <th style="text-align:center;">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submeterGroups as $group)
                            @if((int) $selectedMeterId === 0)
                                <tr id="submeter-group-{{ (int) ($group['meter_id'] ?? 0) }}" style="background:#f8fafc;border-top:2px solid #e2e8f0;border-bottom:1px solid #cbd5e1;">
                                    <td colspan="6" style="padding:10px 18px;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                                             <div style="display:flex;align-items:center;gap:8px;">
                                                <span style="background:#3b82f6;color:#fff;width:22px;height:22px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:0.7rem;"><i class="fa-solid fa-code-branch"></i></span>
                                                <strong style="color:#0f172a;font-size:0.9rem;">{{ $group['meter_name'] ?? 'Unknown Sub-meter' }}</strong>
                                            </div>
                                            <div style="font-size:0.78rem;color:#475569;font-weight:700;display:flex;gap:10px;">
                                                <span><strong>{{ (int) ($group['record_count'] ?? 0) }}</strong> {{ ($timeframe ?? 'monthly') === 'weekly' ? 'week(s)' : 'record(s)' }}</span>
                                                <span>&bull;</span>
                                                <span style="color:#2563eb;"><strong>{{ number_format((float) ($group['total_kwh'] ?? 0), 2) }}</strong> kWh</span>
                                                <span>&bull;</span>
                                                <span style="color:#15803d;"><strong>PHP {{ number_format((float) ($group['total_cost'] ?? 0), 2) }}</strong></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @foreach(($group['records'] ?? []) as $record)
                                @php
                                    $prevReading = $record['previous_reading_kwh'] ?? null;
                                    $currReading = $record['current_reading_kwh'] ?? null;
                                    $hasDials = ($prevReading !== null && $currReading !== null);
                                @endphp
                                <tr>
                                    <td style="padding-left:18px;">
                                        <div style="font-weight:800;color:#1e293b;font-size:0.88rem;">
                                            @if(($timeframe ?? 'monthly') === 'weekly')
                                                {{ $record['period_display'] ?? ($record['year'] . ' ' . ($months[$record['month']] ?? '-') . ' • ' . ($record['week_label'] ?? '')) }}
                                            @else
                                                {{ (int) ($record['year'] ?? 0) }} {{ $months[(int) ($record['month'] ?? 0)] ?? ($record['month'] ?? '-') }}
                                            @endif
                                        </div>
                                        <div style="font-size:0.75rem;color:#64748b;font-weight:700;margin-top:2px;">
                                            <i class="fa-solid fa-code-branch" style="color:#3b82f6;font-size:0.7rem;"></i> {{ $record['meter_name'] ?? '-' }}
                                        </div>
                                    </td>

                                    {{-- Consumption with embedded Dial chip --}}
                                    <td>
                                        <div style="font-weight:900;color:#0f172a;font-size:0.92rem;">
                                            {{ isset($record['actual_kwh']) ? number_format((float) $record['actual_kwh'], 2) : '-' }} <small style="color:#64748b;font-weight:700;">kWh</small>
                                        </div>
                                        @if($hasDials)
                                            <div class="monthly-dial-chip" title="Previous: {{ number_format((float) $prevReading, 2) }} kWh &rarr; Current: {{ number_format((float) $currReading, 2) }} kWh">
                                                <i class="fa-solid fa-gauge-high"></i>
                                                <span>{{ number_format((float) $prevReading, 0) }}</span>
                                                <span class="dial-arrow">&rarr;</span>
                                                <span>{{ number_format((float) $currReading, 0) }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Performance --}}
                                    <td>
                                        <div style="font-size:0.8rem;color:#334155;font-weight:700;">
                                            {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Weekly Baseline:' : 'Baseline:' }} {{ isset($record['baseline_kwh']) ? number_format((float) $record['baseline_kwh'], 2) : '-' }} <small style="color:#94a3b8;">kWh</small>
                                        </div>
                                        <div style="font-size:0.75rem;font-weight:800;margin-top:2px;color:{{ isset($record['deviation']) && $record['deviation'] > 0 ? '#b91c1c' : '#15803d' }};">
                                            Variance: {{ isset($record['deviation']) ? ($record['deviation'] > 0 ? '+' : '') . number_format((float) $record['deviation'], 2) . '%' : '-' }}
                                        </div>
                                    </td>

                                    {{-- Billing --}}
                                    <td>
                                        <div style="color:#15803d;font-weight:900;font-size:0.9rem;">
                                            PHP {{ number_format((float) ($record['cost'] ?? 0), 2) }}
                                        </div>
                                        <div style="font-size:0.72rem;color:#64748b;font-weight:700;margin-top:2px;">
                                            PHP 12.00 / kWh
                                        </div>
                                    </td>

                                    {{-- Status / Alert --}}
                                    <td>
                                        <span style="display:inline-flex;padding:4px 10px;border-radius:999px;font-size:0.72rem;font-weight:900;background:{{ $record['alert_bg'] ?? '#f1f5f9' }};color:{{ $record['alert_color'] ?? '#475569' }};">
                                            {{ $record['alert_label'] ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- Source --}}
                                    <td style="text-align:center;">
                                        @php
                                            $sourceLabel = (string) ($record['source_label'] ?? 'Manual');
                                            $sourceStyle = $sourceLabel === 'Sensor'
                                                ? 'background:#ecfeff;color:#0f766e;border:1px solid #a5f3fc;'
                                                : 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;';
                                        @endphp
                                        <span style="display:inline-flex;padding:3px 9px;border-radius:999px;font-size:0.72rem;font-weight:800;{{ $sourceStyle }}">
                                            {{ $sourceLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" style="padding:32px 16px;text-align:center;color:#64748b;">
                                    <div style="font-weight:800;color:#334155;margin-bottom:4px;">No sub-meter {{ ($timeframe ?? 'monthly') === 'weekly' ? 'weekly' : 'monthly' }} records found</div>
                                    <div>Try changing your filter criteria or click <strong>+ {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Add Weekly Record' : 'Add Sub-meter Record' }}</strong> above.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Sub-meter Record Modal --}}
<div id="addSubmeterModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:38px;height:38px;border-radius:12px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 4px 10px rgba(37,99,235,0.15);">
                    <i class="fa-solid fa-plus"></i>
                </span>
                <div>
                    <h3 style="margin:0;font-size:1.15rem;font-weight:800;color:#0f172a;">{{ ($timeframe ?? 'monthly') === 'weekly' ? 'Add Weekly Sub-meter Record' : 'Add Sub-meter Record' }}</h3>
                    <p style="margin:0;font-size:0.8rem;color:#64748b;">{{ ($timeframe ?? 'monthly') === 'weekly' ? 'Encode weekly dial reading for downstream submeter.' : 'Encode monthly dial reading for downstream submeter.' }}</p>
                </div>
            </div>
            <button type="button" onclick="closeAddSubmeterModal()" style="background:none;border:none;color:#94a3b8;font-size:1.3rem;cursor:pointer;padding:4px 8px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('energy-records.store', ['facility' => $facility->id]) }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf
            
            <div class="monthly-field">
                <label style="color:#1e293b;font-weight:800;">Select Sub-meter <span style="color:#ef4444;">*</span></label>
                <select name="meter_id" id="modalSubmeterSelect" required onchange="handleSubmeterChange()" style="border-radius:12px;padding:10px 12px;font-weight:700;">
                    <option value="" disabled selected>Choose a Sub-meter</option>
                    @foreach($subMeterOptions as $subMeter)
                        <option value="{{ $subMeter->id }}">{{ $subMeter->meter_name }} ({{ $subMeter->meter_number ?: 'Submeter' }})</option>
                    @endforeach
                </select>
            </div>

            @if(($timeframe ?? 'monthly') === 'weekly')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="monthly-field">
                    <label style="color:#1e293b;font-weight:800;">Week Period</label>
                    <select id="modalWeekPeriodSelect" onchange="handleWeekPeriodChange()" style="border-radius:12px;padding:10px 12px;font-weight:700;">
                        <option value="1">Week 1 (Days 1–7)</option>
                        <option value="2">Week 2 (Days 8–14)</option>
                        <option value="3">Week 3 (Days 15–21)</option>
                        <option value="4" id="modalWeek4Option">Week 4 (Days 22–End of Month)</option>
                    </select>
                </div>
                <div class="monthly-field">
                    <label style="color:#1e293b;font-weight:800;">Reading Cutoff Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="date" id="modalSubmeterDate" onchange="updateWeek4Label()" required value="{{ date('Y-m-d') }}" style="border-radius:12px;padding:10px 12px;">
                </div>
            </div>
            @else
            <div class="monthly-field">
                <label style="color:#1e293b;font-weight:800;">Billing Reading Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="date" id="modalSubmeterDate" required value="{{ date('Y-m-d') }}" style="border-radius:12px;padding:10px 12px;">
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="monthly-field">
                    <label style="color:#1e293b;font-weight:800;">Previous Reading (kWh)</label>
                    <input type="number" step="0.01" min="0" name="previous_reading_kwh" id="modalSubmeterPrev" oninput="calculateSubmeterKwh()"
                           placeholder="0.00" style="border-radius:12px;padding:10px 12px;font-family:monospace;font-weight:700;">
                    <span id="prevDialAutoBadge" style="display:none;font-size:0.72rem;color:#2563eb;font-weight:800;margin-top:4px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-filled from last reading
                    </span>
                </div>
                <div class="monthly-field">
                    <label style="color:#1e293b;font-weight:800;">Current Reading (kWh)</label>
                    <input type="number" step="0.01" min="0" name="current_reading_kwh" id="modalSubmeterCurr" oninput="calculateSubmeterKwh()"
                           placeholder="0.00" style="border-radius:12px;padding:10px 12px;font-family:monospace;font-weight:700;">
                </div>
            </div>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="monthly-field">
                    <label style="color:#475569;font-weight:800;">Computed Usage (kWh) <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="actual_kwh" id="modalSubmeterActual" required oninput="calculateSubmeterCost()"
                           placeholder="0.00" style="border-radius:10px;padding:8px 12px;font-size:1rem;font-weight:900;color:#0f172a;background:#fff;font-family:monospace;">
                </div>
                <div class="monthly-field">
                    <label style="color:#475569;font-weight:800;">Rate (PHP / kWh) <small style="font-size:0.73rem;color:#64748b;font-weight:600;">(QC Commercial Rate)</small></label>
                    <input type="number" step="0.01" min="0" name="rate_per_kwh" id="modalSubmeterRate" value="12.00" oninput="calculateSubmeterCost()"
                           style="border-radius:10px;padding:8px 12px;background:#fff;font-weight:700;">
                </div>
                <div style="grid-column: span 2; display:flex;justify-content:space-between;align-items:center;padding-top:6px;border-top:1px dashed #cbd5e1;">
                    <span style="font-size:0.84rem;font-weight:800;color:#475569;">Estimated Energy Cost:</span>
                    <strong id="modalSubmeterCostDisplay" style="font-size:1.15rem;color:#15803d;font-weight:900;">PHP 0.00</strong>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
                <button type="button" onclick="closeAddSubmeterModal()"
                        style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:12px;padding:10px 18px;font-weight:800;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:12px;padding:10px 22px;font-weight:800;cursor:pointer;box-shadow:0 6px 16px rgba(37,99,235,0.25);">
                    <i class="fa-solid fa-floppy-disk"></i> {{ ($timeframe ?? 'monthly') === 'weekly' ? 'Save Weekly Record' : 'Save Sub-meter Record' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const latestSubmeterDials = {!! $latestSubmeterDialsJson !!};

function openAddSubmeterModal() {
    const modal = document.getElementById('addSubmeterModal');
    if (modal) {
        modal.classList.add('is-active');
        handleSubmeterChange();
        updateWeek4Label();
    }
}

function closeAddSubmeterModal() {
    const modal = document.getElementById('addSubmeterModal');
    if (modal) {
        modal.classList.remove('is-active');
    }
}

function updateWeek4Label() {
    const dateInput = document.getElementById('modalSubmeterDate');
    const w4Option = document.getElementById('modalWeek4Option');
    if (!dateInput || !w4Option) return;

    const parts = (dateInput.value || '').split('-');
    if (parts.length === 3) {
        const y = parseInt(parts[0], 10);
        const m = parseInt(parts[1], 10);
        const lastDayOfMonth = new Date(y, m, 0).getDate();
        w4Option.innerText = `Week 4 (Days 22–${lastDayOfMonth})`;
    }
}

function handleWeekPeriodChange() {
    const weekSelect = document.getElementById('modalWeekPeriodSelect');
    const dateInput = document.getElementById('modalSubmeterDate');
    if (!weekSelect || !dateInput) return;

    const wNum = parseInt(weekSelect.value, 10);
    const parts = (dateInput.value || '').split('-');
    let y = new Date().getFullYear();
    let m = new Date().getMonth() + 1;
    if (parts.length === 3) {
        y = parseInt(parts[0], 10);
        m = parseInt(parts[1], 10);
    }

    // Get exact last day of that specific month (28, 29, 30, or 31)
    const lastDayOfMonth = new Date(y, m, 0).getDate();

    let day = 7;
    if (wNum === 2) day = 14;
    else if (wNum === 3) day = 21;
    else if (wNum === 4) day = lastDayOfMonth;

    const formattedMonth = m < 10 ? '0' + m : '' + m;
    const formattedDay = day < 10 ? '0' + day : '' + day;
    dateInput.value = `${y}-${formattedMonth}-${formattedDay}`;

    updateWeek4Label();
}

function handleSubmeterChange() {
    const select = document.getElementById('modalSubmeterSelect');
    const prevInput = document.getElementById('modalSubmeterPrev');
    const badge = document.getElementById('prevDialAutoBadge');
    if (!select || !prevInput) return;

    const meterId = select.value;
    if (meterId && latestSubmeterDials[meterId] !== undefined && latestSubmeterDials[meterId] !== null) {
        prevInput.value = parseFloat(latestSubmeterDials[meterId]).toFixed(2);
        if (badge) badge.style.display = 'inline-block';
    } else {
        if (badge) badge.style.display = 'none';
    }
    calculateSubmeterKwh();
}

function calculateSubmeterKwh() {
    const prev = parseFloat(document.getElementById('modalSubmeterPrev')?.value || '0');
    const curr = parseFloat(document.getElementById('modalSubmeterCurr')?.value || '0');
    const actualInput = document.getElementById('modalSubmeterActual');

    if (!isNaN(prev) && !isNaN(curr) && curr >= prev && curr > 0) {
        const diff = curr - prev;
        if (actualInput) actualInput.value = diff.toFixed(2);
    }
    calculateSubmeterCost();
}

function calculateSubmeterCost() {
    const actual = parseFloat(document.getElementById('modalSubmeterActual')?.value || '0');
    const rate = parseFloat(document.getElementById('modalSubmeterRate')?.value || '12.00');
    const display = document.getElementById('modalSubmeterCostDisplay');

    if (display) {
        const cost = Math.max(0, actual * rate);
        display.innerText = 'PHP ' + cost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}
</script>
@endsection
