@extends('layouts.qc-admin')
@section('title', 'Load Tracking')

@section('content')
@php
    $totals = $totals ?? ['estimated_kwh' => 0, 'actual_kwh' => 0, 'variance_percent' => null, 'flagged_submeters' => 0];
    $rows = $rows ?? collect();
    $topEquipment = $topEquipment ?? collect();
    $pieLabels = $pieLabels ?? [];
    $pieValues = $pieValues ?? [];
    $comparisonLabels = $comparisonLabels ?? [];
    $comparisonEstimated = $comparisonEstimated ?? [];
    $comparisonActual = $comparisonActual ?? [];
    $varianceThreshold = $varianceThreshold ?? 20;
    $warningThreshold = $warningThreshold ?? 10;
    $mainMeters = $mainMeters ?? collect();
    $submeters = $submeters ?? collect();
    $selectedMeterScope = $selectedMeterScope ?? 'all';
    $selectedConsumptionFilter = $selectedConsumptionFilter ?? 'warning_high';
@endphp

<style>
    .lt-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .lt-filter-field { display: grid; gap: 6px; }
    .lt-filter-label { font-size: .8rem; font-weight: 700; color: #475569; }
    .lt-control {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
    }
    .lt-control:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }
    .lt-filter-actions { display: flex; gap: 8px; }
    .lt-btn {
        text-decoration: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 700;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .lt-btn.apply { background: #1d4ed8; color: #fff; }
    .lt-btn.reset { background: #f1f5f9; color: #334155; border-color: #e2e8f0; }
    .lt-filter-meta {
        margin-top: 10px;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        color: #334155;
        font-size: .86rem;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .lt-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e3a8a;
        border-radius: 999px;
        padding: 4px 10px;
        font-weight: 700;
    }

    .lt-data-table {
        width: 100%;
        min-width: 1020px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .lt-data-table thead th {
        padding: 10px 10px !important;
        text-align: center !important;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-size: .74rem;
        color: #475569;
        font-weight: 800;
        background: #f8fafc !important;
        border-bottom: 1px solid #dbe4f2 !important;
    }

    .lt-data-table td {
        padding: 10px 10px !important;
        text-align: center !important;
        border-top: 1px solid #edf2f7 !important;
        font-size: .94rem;
    }

    .lt-data-table td span[style*="border-radius:999px"] {
        padding: 4px 10px !important;
        font-size: .73rem !important;
    }
</style>

<div class="em-page">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 16px;border-radius:12px;font-weight:700;">Please check the form fields and try again.</div>
    @endif

    @php
        $energyTab = 'load';
    @endphp
    @include('layouts.partials.energy_monitoring_switcher')

    <div class="em-header">
        <div>
            <h2>Main/Sub Meter Load Tracking</h2>
            <div class="em-header-subtitle">Track watts and estimated kWh per equipment under Sub Meters and Main Meters.</div>
        </div>
        <div class="em-header-actions">
            <a href="{{ route('modules.facilities.index') }}" class="em-action-btn soft">Facility Equipment Inventory</a>
        </div>
    </div>

    <div class="em-panel" style="padding:12px;">
        <form method="GET" action="{{ route('modules.load-tracking.index') }}" class="lt-filter-grid">
            <div class="lt-filter-field">
                <label class="lt-filter-label">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" class="lt-control">
            </div>
            <div class="lt-filter-field">
                <label class="lt-filter-label">Facility</label>
                <select name="facility_id" class="lt-control">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lt-filter-field">
                <label class="lt-filter-label">Meter Scope</label>
                <select id="filter_meter_scope" name="meter_scope" class="lt-control">
                    <option value="all" @selected($selectedMeterScope === 'all')>All</option>
                    <option value="sub" @selected($selectedMeterScope === 'sub')>Sub Meter</option>
                    <option value="main" @selected($selectedMeterScope === 'main')>Main Meter</option>
                </select>
            </div>
            <div class="lt-filter-field">
                <label class="lt-filter-label">Consumption</label>
                <select name="consumption_filter" class="lt-control">
                    <option value="warning_high" @selected($selectedConsumptionFilter === 'warning_high')>Warning + High Only</option>
                    <option value="all" @selected($selectedConsumptionFilter === 'all')>All Levels</option>
                </select>
            </div>
            <div id="filter_sub_group" class="lt-filter-field">
                <label class="lt-filter-label">Sub Meter</label>
                <select name="submeter_id" class="lt-control">
                    <option value="">All Sub Meters</option>
                    @foreach($submeters as $submeter)
                        <option value="{{ $submeter->id }}" @selected((string) $selectedSubmeter === (string) $submeter->id)>{{ $submeter->submeter_name }} ({{ $submeter->facility?->name ?? 'Facility' }})</option>
                    @endforeach
                </select>
            </div>
            <div id="filter_main_group" class="lt-filter-field">
                <label class="lt-filter-label">Main Meter</label>
                <select name="main_meter_id" class="lt-control">
                    <option value="">All Main Meters</option>
                    @foreach($mainMeters as $mainMeter)
                        <option value="{{ $mainMeter->id }}" @selected((string) $selectedMainMeter === (string) $mainMeter->id)>{{ $mainMeter->meter_name }} ({{ $mainMeter->facility?->name ?? 'Facility' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="lt-filter-actions">
                <button type="submit" class="lt-btn apply">Apply</button>
                <a href="{{ route('modules.load-tracking.index') }}" class="lt-btn reset">Reset</a>
            </div>
        </form>
        <div class="lt-filter-meta">
            <span>Showing <strong>{{ number_format((int) $rows->count()) }}</strong> row(s)</span>
            <span class="lt-meta-chip">Warning &gt; {{ number_format((float) $warningThreshold, 0) }}%</span>
            <span class="lt-meta-chip">High &gt; {{ number_format((float) $varianceThreshold, 0) }}%</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
        <div style="background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#1e40af;font-weight:800;">TOTAL ESTIMATED KWH</div><div style="font-size:1.45rem;font-weight:900;">{{ number_format((float) $totals['estimated_kwh'], 2) }}</div></div>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#0f766e;font-weight:800;">TOTAL ACTUAL KWH</div><div style="font-size:1.45rem;font-weight:900;">{{ number_format((float) $totals['actual_kwh'], 2) }}</div></div>
        <div style="background:#fff;border:1px solid #fde68a;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#92400e;font-weight:800;">TOTAL VARIANCE %</div><div style="font-size:1.45rem;font-weight:900;">{{ $totals['variance_percent'] !== null ? number_format((float) $totals['variance_percent'], 2).'%' : '-' }}</div></div>
        <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#991b1b;font-weight:800;">FLAGGED METERS (&gt; {{ number_format((float) $varianceThreshold, 0) }}%)</div><div style="font-size:1.45rem;font-weight:900;color:#991b1b;">{{ number_format((int) $totals['flagged_submeters']) }}</div></div>
    </div>

    <div class="em-panel" style="padding:10px 12px;color:#475569;font-size:.84rem;">
        <strong style="color:#1e293b;">How to read this table:</strong>
        Rows are highlighted by alert level. If estimated kWh is zero but actual kWh exists, row is treated as High Consumption.
    </div>

    <div class="em-panel em-table-wrap">
        <table class="lt-data-table">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="text-align:left;">Facility</th>
                    <th style="text-align:left;">Type</th>
                    <th style="text-align:left;">Meter</th>
                    <th style="text-align:center;">Equipment</th>
                    <th style="text-align:right;">Total Watts</th>
                    <th style="text-align:right;">Estimated kWh</th>
                    <th style="text-align:right;">Actual kWh</th>
                    <th style="text-align:center;">Variance %</th>
                    <th style="text-align:center;">Alert Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $facilityLabel = trim((string) ($row['facility_name'] ?? '')) !== ''
                            ? (string) $row['facility_name']
                            : 'Unknown Facility';
                        $level = strtolower((string) ($row['consumption_level'] ?? 'normal'));
                        $pillBg = $level === 'high'
                            ? '#fee2e2'
                            : ($level === 'warning' ? '#fffbeb' : ($level === 'no_estimate' ? '#eff6ff' : '#f1f5f9'));
                        $pillColor = $level === 'high'
                            ? '#b91c1c'
                            : ($level === 'warning' ? '#a16207' : ($level === 'no_estimate' ? '#1d4ed8' : '#334155'));
                        $pillBorder = $level === 'high'
                            ? '#fecaca'
                            : ($level === 'warning' ? '#fde68a' : ($level === 'no_estimate' ? '#bfdbfe' : '#cbd5e1'));
                        $rowBg = $level === 'high'
                            ? '#fff7f7'
                            : ($level === 'warning' ? '#fffdf2' : ($level === 'no_estimate' ? '#f8fbff' : '#ffffff'));
                    @endphp
                    <tr style="background:{{ $rowBg }};">
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;">{{ $facilityLabel }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;">{{ $row['meter_scope_label'] }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;font-weight:700;">{{ $row['meter_name'] }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">{{ number_format((int) $row['equipment_count']) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;">{{ number_format((float) ($row['total_watts'] ?? 0), 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;color:#1d4ed8;font-weight:700;">{{ number_format((float) $row['estimated_kwh'], 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format((float) $row['actual_kwh'], 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">{{ $row['variance_percent'] !== null ? number_format((float) $row['variance_percent'], 2).'%' : '-' }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">
                            <span style="display:inline-flex;align-items:center;border-radius:999px;padding:2px 9px;font-size:.74rem;font-weight:800;background:{{ $pillBg }};color:{{ $pillColor }};border:1px solid {{ $pillBorder }};">
                                {{ $row['consumption_label'] ?? 'Normal' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="padding:16px;text-align:center;color:#64748b;">
                        {{ $selectedConsumptionFilter === 'warning_high'
                            ? 'No warning/high consumption meter found for selected filters. Meters with "No Estimate" are excluded in this view.'
                            : 'No meter data available for selected filters.' }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <div style="color:#1e3a8a;font-weight:700;">
            <i class="fa fa-cubes" style="margin-right:6px;"></i>
            Equipment Inventory is now under Facilities. Open a facility card and click the Equipment Inventory icon.
        </div>
        @if($selectedFacility)
            <a href="{{ route('modules.facilities.equipment-inventory', $selectedFacility) }}" style="text-decoration:none;background:#1d4ed8;color:#fff;border-radius:10px;padding:9px 12px;font-weight:700;">
                Open Selected Facility Inventory
            </a>
        @else
            <a href="{{ route('modules.facilities.index') }}" style="text-decoration:none;background:#1d4ed8;color:#fff;border-radius:10px;padding:9px 12px;font-weight:700;">
                Open Facilities
            </a>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var filterScope = document.getElementById('filter_meter_scope');
    var filterSub = document.getElementById('filter_sub_group');
    var filterMain = document.getElementById('filter_main_group');

    function toggleFilterGroups() {
        if (!filterScope) return;
        var scope = String(filterScope.value || 'all');
        if (filterSub) filterSub.style.display = scope === 'main' ? 'none' : 'block';
        if (filterMain) filterMain.style.display = scope === 'sub' ? 'none' : 'block';
    }

    filterScope?.addEventListener('change', toggleFilterGroups);
    toggleFilterGroups();
});
</script>
@endsection
