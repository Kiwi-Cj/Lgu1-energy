@extends('layouts.qc-admin')
@section('title', 'Monthly Records')
@section('content')
<style>
    .report-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(31,38,135,0.08);
        margin-bottom: 1.2rem;
        padding: 24px;
    }

    body.dark-mode .monthly-record-page.report-card {
        background: #0f172a !important;
        border: 1px solid #1f2937;
        box-shadow: 0 12px 30px rgba(2, 6, 23, 0.55);
    }

    body.dark-mode .monthly-record-page [style*="background:#fff"],
    body.dark-mode .monthly-record-page [style*="background: #fff"],
    body.dark-mode .monthly-record-page [style*="background:#ffffff"],
    body.dark-mode .monthly-record-page [style*="background: #ffffff"],
    body.dark-mode .monthly-record-page [style*="background:#f1f5f9"],
    body.dark-mode .monthly-record-page [style*="background: #f1f5f9"],
    body.dark-mode .monthly-record-page [style*="background:#f3f4f6"],
    body.dark-mode .monthly-record-page [style*="background: #f3f4f6"] {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page [style*="color:#222"],
    body.dark-mode .monthly-record-page [style*="color: #222"],
    body.dark-mode .monthly-record-page [style*="color:#1e293b"],
    body.dark-mode .monthly-record-page [style*="color: #1e293b"],
    body.dark-mode .monthly-record-page [style*="color:#475569"],
    body.dark-mode .monthly-record-page [style*="color: #475569"],
    body.dark-mode .monthly-record-page [style*="color:#64748b"],
    body.dark-mode .monthly-record-page [style*="color: #64748b"] {
        color: #e2e8f0 !important;
    }

    body.dark-mode .monthly-record-page table thead,
    body.dark-mode .monthly-record-page table thead tr {
        background: #111827 !important;
    }

    body.dark-mode .monthly-record-page table th,
    body.dark-mode .monthly-record-page table td,
    body.dark-mode .monthly-record-page table tr {
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode #addModal .modal-content,
    body.dark-mode #duplicateModal .modal-content,
    body.dark-mode #energyActionModal .modal-content,
    body.dark-mode #monthlyRecommendationModalBox,
    body.dark-mode #deleteMonthlyRecordModal .modal-content {
        background: #111827 !important;
        color: #e2e8f0 !important;
        border: 1px solid #334155;
    }

    body.dark-mode #addModal input,
    body.dark-mode #addModal select,
    body.dark-mode #addModal textarea,
    body.dark-mode #addModal input[type="file"] {
        background: #0b1220 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    .monthly-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .monthly-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(15,23,42,0.05);
        overflow: hidden;
    }

    .monthly-panel-inner {
        padding: 14px 16px;
    }

    .monthly-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .monthly-hero-title {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .monthly-hero-title h1 {
        margin: 0;
        color: #2563eb;
        font-size: 1.3rem;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .monthly-hero-title p {
        margin: 0;
        color: #64748b;
        font-size: .92rem;
    }

    .monthly-hero-title .facility-name {
        color: #1e293b;
        font-weight: 800;
    }

    .monthly-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .monthly-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
    }

    .monthly-meta-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .monthly-meta-label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 4px;
    }

    .monthly-meta-value {
        color: #1e293b;
        font-size: 1.02rem;
        font-weight: 800;
    }

    .monthly-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
        align-items: start;
    }

    .monthly-filter-block {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }

    .monthly-filter-block h3 {
        margin: 0 0 10px;
        color: #1e293b;
        font-size: .95rem;
        font-weight: 800;
    }

    .monthly-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .monthly-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
        font-size: .85rem;
    }

    .monthly-tab.inactive {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #475569;
    }

    .monthly-tab .count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        padding: 0 6px;
        background: rgba(255,255,255,.9);
        border: 1px solid rgba(29,78,216,.15);
        color: inherit;
        font-size: .75rem;
        font-weight: 800;
    }

    .monthly-inline-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #475569;
        font-size: .86rem;
        line-height: 1.35;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .monthly-baseline-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 10px;
    }

    .monthly-baseline-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .monthly-baseline-card .label {
        color: #64748b;
        font-weight: 700;
        font-size: .84rem;
        margin-bottom: 4px;
    }

    .monthly-baseline-card .value {
        color: #1e293b;
        font-weight: 800;
        font-size: 1.02rem;
    }

    .monthly-table-panel {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(15,23,42,0.05);
        overflow: hidden;
    }

    .monthly-table-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        background: #fcfdff;
    }

    .monthly-table-header .title {
        color: #1e293b;
        font-weight: 800;
        font-size: 1rem;
    }

    .monthly-table-header .subtitle {
        color: #64748b;
        font-size: .84rem;
        margin-top: 2px;
    }

    .monthly-table-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-table-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .monthly-footer-actions {
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .monthly-footer-help {
        color: #64748b;
        font-size: .85rem;
    }

    .monthly-group-row td {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px !important;
    }

    .monthly-group-row .group-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .monthly-group-row .group-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        color: #1e293b;
    }

    .monthly-group-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .76rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .monthly-group-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #dbeafe;
        background: #fff;
        color: #1d4ed8;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
    }

    .monthly-group-toggle-btn .chevron {
        display: inline-block;
        transition: transform .16s ease;
    }

    .monthly-group-toggle-btn.collapsed .chevron {
        transform: rotate(-90deg);
    }

    tr.monthly-group-hidden {
        display: none;
    }

    body.dark-mode .monthly-record-page .monthly-panel,
    body.dark-mode .monthly-record-page .monthly-table-panel,
    body.dark-mode .monthly-record-page .monthly-baseline-card {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-meta-card,
    body.dark-mode .monthly-record-page .monthly-filter-block {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-hero-title h1,
    body.dark-mode .monthly-record-page .monthly-meta-value,
    body.dark-mode .monthly-record-page .monthly-filter-block h3,
    body.dark-mode .monthly-record-page .monthly-table-header .title,
    body.dark-mode .monthly-record-page .monthly-baseline-card .value {
        color: #e2e8f0 !important;
    }

    body.dark-mode .monthly-record-page .monthly-hero-title p,
    body.dark-mode .monthly-record-page .monthly-hero-title .facility-name,
    body.dark-mode .monthly-record-page .monthly-meta-label,
    body.dark-mode .monthly-record-page .monthly-inline-note,
    body.dark-mode .monthly-record-page .monthly-baseline-card .label,
    body.dark-mode .monthly-record-page .monthly-table-header .subtitle,
    body.dark-mode .monthly-record-page .monthly-footer-help {
        color: #cbd5e1 !important;
    }

    body.dark-mode .monthly-record-page .monthly-inline-note {
        background: #0b1220 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-table-header {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-table-badge {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #bfdbfe !important;
    }

    body.dark-mode .monthly-record-page .monthly-tab {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #bfdbfe !important;
    }

    body.dark-mode .monthly-record-page .monthly-tab.inactive {
        color: #cbd5e1 !important;
    }

    body.dark-mode .monthly-record-page .monthly-tab .count {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-group-row td {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .monthly-record-page .monthly-group-row .group-title {
        color: #e2e8f0 !important;
    }

    body.dark-mode .monthly-record-page .monthly-group-toggle-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #bfdbfe !important;
    }
</style>
@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $archivedCount = $archivedCount ?? 0;
    $meterOptions = $meterOptions ?? collect();
    $selectedRecordScope = $selectedRecordScope ?? 'facility';
    $reconciliationRecords = $reconciliationRecords ?? collect();
@endphp
@php
    $sortedRecords = $records->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
    // Use baseline_kwh from record if set, else fallback to energy profile/facility
    $energyProfile = \App\Models\EnergyProfile::where('facility_id', $facility->id)->latest()->first();
    $hasBaseline = false;
    if ($energyProfile && is_numeric($energyProfile->baseline_kwh) && $energyProfile->baseline_kwh > 0) {
        $baselineAvg = floatval($energyProfile->baseline_kwh);
        $hasBaseline = true;
    } else {
        $baselineAvg = $facility->baseline_kwh;
        $hasBaseline = $baselineAvg > 0;
    }
@endphp
@php
    $currentYear = date('Y');
    $showAll = request('show_all') === '1';
    $selectedYear = request('year') ?? ($showAll ? $currentYear - 1 : $currentYear);
    $years = $records->pluck('year')->unique()->sortDesc()->values();
    $filteredRecords = $records->where('year', $selectedYear);
    $sortedRecords = $filteredRecords->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
    $groupableScopes = ['all', 'main', 'submeters'];
    $isGroupedDisplay = in_array($selectedRecordScope, $groupableScopes, true);

    $monthlyComputedCost = function ($record): float {
        $actual = (float) ($record->actual_kwh ?? 0);
        $rate = (isset($record->rate_per_kwh) && $record->rate_per_kwh) ? (float) $record->rate_per_kwh : 12.00;
        return round($actual * $rate, 2);
    };

    $monthlyRecordGroupMeta = function ($record): array {
        if (empty($record->meter)) {
            return [
                'key' => 'facility-aggregate',
                'sort_key' => '0|facility aggregate',
                'label' => 'Facility Aggregate',
                'count_label' => 'Facility-level records',
                'pill_label' => 'FACILITY',
                'pill_bg' => '#f1f5f9',
                'pill_color' => '#334155',
                'pill_border' => '#cbd5e1',
                'meter_number' => null,
                'meter_type' => null,
            ];
        }

        $meterType = strtolower((string) ($record->meter->meter_type ?? 'sub'));
        $meterName = (string) ($record->meter->meter_name ?? 'Meter');

        if ($meterType === 'main') {
            return [
                'key' => 'main-' . (int) $record->meter->id,
                'sort_key' => '1|' . strtolower($meterName),
                'label' => $meterName,
                'count_label' => 'Main meter records',
                'pill_label' => 'MAIN',
                'pill_bg' => '#eff6ff',
                'pill_color' => '#1d4ed8',
                'pill_border' => '#bfdbfe',
                'meter_number' => (string) ($record->meter->meter_number ?? ''),
                'meter_type' => 'main',
            ];
        }

        return [
            'key' => 'sub-' . (int) $record->meter->id,
            'sort_key' => '2|' . strtolower($meterName),
            'label' => $meterName,
            'count_label' => 'Sub-meter records',
            'pill_label' => 'SUB',
            'pill_bg' => '#f3e8ff',
            'pill_color' => '#7c3aed',
            'pill_border' => '#ddd6fe',
            'meter_number' => (string) ($record->meter->meter_number ?? ''),
            'meter_type' => 'sub',
        ];
    };

    if ($isGroupedDisplay) {
        $sortedRecords = $filteredRecords->sortBy(function ($record) use ($monthlyRecordGroupMeta) {
            $meta = $monthlyRecordGroupMeta($record);
            $year = str_pad((string) ((int) ($record->year ?? 0)), 4, '0', STR_PAD_LEFT);
            $month = str_pad((string) ((int) ($record->month ?? 0)), 2, '0', STR_PAD_LEFT);
            $day = str_pad((string) ((int) ($record->day ?? 0)), 2, '0', STR_PAD_LEFT);

            return $meta['sort_key'] . '|' . $year . $month . $day . '|' . str_pad((string) ((int) ($record->id ?? 0)), 10, '0', STR_PAD_LEFT);
        })->values();
    }

    $selectedScopeLabel = 'Facility Aggregate';
    if ($selectedRecordScope === 'all') {
        $selectedScopeLabel = 'All Records';
    } elseif ($selectedRecordScope === 'main') {
        $selectedScopeLabel = 'Main Meter Records';
    } elseif ($selectedRecordScope === 'submeters') {
        $selectedScopeLabel = 'Sub-meter Records';
    } elseif (str_starts_with((string) $selectedRecordScope, 'meter:')) {
        $selectedScopeMeterId = (int) substr((string) $selectedRecordScope, strlen('meter:'));
        $selectedScopeMeter = collect($meterOptions)->first(fn ($meter) => (int) $meter->id === $selectedScopeMeterId);
        if ($selectedScopeMeter) {
            $selectedScopeLabel = strtoupper((string) ($selectedScopeMeter->meter_type ?? 'meter')) . ' - ' . (string) $selectedScopeMeter->meter_name;
        }
    }

    $selectedRecordCount = $sortedRecords->count();
    $selectedActualKwhTotal = round((float) $sortedRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
    $selectedCostTotal = round((float) $sortedRecords->sum(fn ($record) => (float) $monthlyComputedCost($record)), 2);

    $monthlyGroupSummaries = [];
    if ($isGroupedDisplay) {
        foreach ($sortedRecords as $record) {
            $meta = $monthlyRecordGroupMeta($record);
            $groupKey = (string) $meta['key'];
            if (! isset($monthlyGroupSummaries[$groupKey])) {
                $monthlyGroupSummaries[$groupKey] = array_merge($meta, [
                    'record_count' => 0,
                    'total_kwh' => 0.0,
                    'total_cost' => 0.0,
                ]);
            }

            $monthlyGroupSummaries[$groupKey]['record_count']++;
            $monthlyGroupSummaries[$groupKey]['total_kwh'] += (float) ($record->actual_kwh ?? 0);
            $monthlyGroupSummaries[$groupKey]['total_cost'] += (float) $monthlyComputedCost($record);
        }
    }
@endphp
@php
    $monthLabelsShort = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $monthLabelsFull = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];

    $reconYearRecords = collect($reconciliationRecords)->filter(function ($record) use ($selectedYear) {
        return (string) ($record->year ?? '') === (string) $selectedYear;
    });

    $tabScopeCounts = [
        'facility' => $reconYearRecords->filter(fn ($record) => empty($record->meter_id))->count(),
        'main' => $reconYearRecords->filter(fn ($record) => !empty($record->meter) && strtolower((string) ($record->meter->meter_type ?? '')) === 'main')->count(),
        'submeters' => $reconYearRecords->filter(fn ($record) => !empty($record->meter) && strtolower((string) ($record->meter->meter_type ?? '')) === 'sub')->count(),
        'all' => $reconYearRecords->count(),
    ];

    $reconciliationRows = collect();
    $reconciliationWarningThresholdPercent = 5.0;
    $reconciliationCriticalThresholdPercent = 15.0;

    for ($m = 1; $m <= 12; $m++) {
        $monthRecords = $reconYearRecords->filter(fn ($record) => (int) ($record->month ?? 0) === $m);
        if ($monthRecords->isEmpty()) {
            continue;
        }

        $aggregateRecord = $monthRecords->first(fn ($record) => empty($record->meter_id));
        $aggregateKwh = $aggregateRecord?->actual_kwh !== null ? (float) $aggregateRecord->actual_kwh : null;

        $mainMeterRecords = $monthRecords->filter(function ($record) {
            return !empty($record->meter) && strtolower((string) ($record->meter->meter_type ?? '')) === 'main';
        });
        $subMeterRecords = $monthRecords->filter(function ($record) {
            return !empty($record->meter) && strtolower((string) ($record->meter->meter_type ?? '')) === 'sub';
        });

        $mainKwh = $mainMeterRecords->isNotEmpty() ? (float) $mainMeterRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)) : null;
        $subKwh = $subMeterRecords->isNotEmpty() ? (float) $subMeterRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)) : null;

        $referenceLabel = null;
        $referenceKwh = null;
        if ($aggregateKwh !== null) {
            $referenceLabel = 'Facility Aggregate';
            $referenceKwh = $aggregateKwh;
        } elseif ($mainKwh !== null) {
            $referenceLabel = 'Main Meter';
            $referenceKwh = $mainKwh;
        }

        $varianceKwh = null;
        $variancePercent = null;
        if ($referenceKwh !== null && $subKwh !== null) {
            $varianceKwh = round($referenceKwh - $subKwh, 2); // positive = unmetered/unassigned load, negative = sub-meter sum exceeds reference
            $variancePercent = $referenceKwh > 0 ? round(($varianceKwh / $referenceKwh) * 100, 2) : null;
        }

        $status = 'No Comparison';
        $statusColor = '#64748b';
        $statusBg = '#f1f5f9';
        if ($referenceKwh === null) {
            $status = 'No Reference';
            $statusColor = '#92400e';
            $statusBg = '#fef3c7';
        } elseif ($subKwh === null) {
            $status = 'No Sub-meter Data';
            $statusColor = '#1d4ed8';
            $statusBg = '#dbeafe';
        } elseif ($variancePercent !== null) {
            $absVariancePercent = abs($variancePercent);
            if ($absVariancePercent > $reconciliationCriticalThresholdPercent) {
                $status = 'Critical Variance';
                $statusColor = '#991b1b';
                $statusBg = '#fee2e2';
            } elseif ($absVariancePercent > $reconciliationWarningThresholdPercent) {
                $status = 'Warning';
                $statusColor = '#9a3412';
                $statusBg = '#ffedd5';
            } else {
                $status = 'Within Range';
                $statusColor = '#166534';
                $statusBg = '#dcfce7';
            }
        }

        $reconciliationRows->push([
            'month' => $m,
            'month_label_short' => $monthLabelsShort[$m] ?? (string) $m,
            'month_label_full' => $monthLabelsFull[$m] ?? (string) $m,
            'aggregate_kwh' => $aggregateKwh,
            'main_kwh' => $mainKwh,
            'sub_kwh' => $subKwh,
            'reference_label' => $referenceLabel,
            'reference_kwh' => $referenceKwh,
            'variance_kwh' => $varianceKwh,
            'variance_percent' => $variancePercent,
            'status' => $status,
            'status_color' => $statusColor,
            'status_bg' => $statusBg,
        ]);
    }

    $reconciliationSummary = [
        'months_checked' => $reconciliationRows->count(),
        'warnings' => $reconciliationRows->filter(fn ($row) => in_array($row['status'], ['Warning', 'Critical Variance'], true))->count(),
        'critical' => $reconciliationRows->where('status', 'Critical Variance')->count(),
        'no_reference' => $reconciliationRows->where('status', 'No Reference')->count(),
    ];
@endphp
@php
    // Load Energy Monitoring alert thresholds from System Settings (single query for page render).
    $alertThresholdDefaults = [
        'small' => ['level1' => 3, 'level2' => 5, 'level3' => 10, 'level4' => 20, 'level5' => 30],
        'medium' => ['level1' => 5, 'level2' => 7, 'level3' => 13, 'level4' => 23, 'level5' => 35],
        'large' => ['level1' => 7, 'level2' => 10, 'level3' => 16, 'level4' => 26, 'level5' => 40],
        'xlarge' => ['level1' => 10, 'level2' => 12, 'level3' => 18, 'level4' => 28, 'level5' => 45],
    ];

    $alertThresholdKeys = [];
    foreach (array_keys($alertThresholdDefaults) as $sizeKey) {
        for ($lvl = 1; $lvl <= 5; $lvl++) {
            $alertThresholdKeys[] = "alert_level{$lvl}_{$sizeKey}";
        }
    }

    $alertThresholdSettings = \App\Models\Setting::whereIn('key', $alertThresholdKeys)->pluck('value', 'key');

    $alertThresholdsBySize = [];
    foreach ($alertThresholdDefaults as $sizeKey => $levels) {
        $alertThresholdsBySize[$sizeKey] = [];
        foreach ($levels as $levelKey => $defaultValue) {
            $settingKey = "alert_{$levelKey}_{$sizeKey}";
            $rawValue = $alertThresholdSettings[$settingKey] ?? $defaultValue;
            $alertThresholdsBySize[$sizeKey][$levelKey] = is_numeric($rawValue) ? (float) $rawValue : (float) $defaultValue;
        }
    }

@endphp


<div class="report-card monthly-record-page">
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif



<!-- ...existing content... -->
@php
    $monthlyFormFacilityBaseline = (isset($baselineAvg) && $baselineAvg !== null)
        ? number_format((float) $baselineAvg, 2, '.', '')
        : '';
@endphp
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<div class="monthly-stack">

    <div class="monthly-panel">
        <div class="monthly-panel-inner">
            <div class="monthly-hero">
                <div class="monthly-hero-title">
                    <h1>Monthly Energy Records</h1>
                    <p>Facility: <span class="facility-name">{{ $facility->name }}</span></p>
                </div>
                <div class="monthly-actions">
            <a href="{{ route('modules.facilities.meters.index', $facility->id) }}"
               class="btn"
               style="background:#eff6ff;color:#1d4ed8;font-weight:600;border:1px solid #bfdbfe;border-radius:10px;padding:10px 16px;font-size:0.98rem;text-decoration:none;">
                <i class="fa fa-gauge-high" style="margin-right:6px;"></i>Meters
            </a>
            <a href="{{ route('facilities.monthly-records.archive', $facility->id) }}"
               class="btn"
               style="background:#f8fafc;color:#1e293b;font-weight:600;border:1px solid #cbd5e1;border-radius:10px;padding:10px 16px;font-size:0.98rem;text-decoration:none;">
                <i class="fa fa-box-archive" style="margin-right:6px;"></i>Archive
                @if($archivedCount > 0)
                    <span style="margin-left:6px;background:#e11d48;color:#fff;border-radius:999px;padding:2px 8px;font-size:0.82rem;">{{ $archivedCount }}</span>
                @endif
            </a>
            <button onclick="openAddModal()" class="btn btn-primary" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 22px; font-size:1.05rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.18s; @if(!$hasBaseline) opacity:0.5; pointer-events:none; @endif" @if(!$hasBaseline) disabled title="You need at least 3 months of data before adding monthly records." @endif>+ Monthly Energy Records</button>
                </div>
            </div>
        </div>
        <div style="padding:0 16px 16px;">
            <div class="monthly-meta-grid">
                <div class="monthly-meta-card">
                    <div class="monthly-meta-label">View Mode</div>
                    <div class="monthly-meta-value">{{ $showAll ? 'Historical (Filtered by Year)' : 'Current Year' }}</div>
                </div>
                <div class="monthly-meta-card">
                    <div class="monthly-meta-label">Selected Year</div>
                    <div class="monthly-meta-value">{{ $selectedYear }}</div>
                </div>
                <div class="monthly-meta-card">
                    <div class="monthly-meta-label">Record Scope</div>
                    <div class="monthly-meta-value">{{ $selectedScopeLabel }}</div>
                </div>
                <div class="monthly-meta-card">
                    <div class="monthly-meta-label">Selected Total kWh</div>
                    <div class="monthly-meta-value">{{ number_format($selectedActualKwhTotal, 2) }}</div>
                    <div style="font-size:.78rem;color:#64748b;margin-top:3px;">{{ $selectedRecordCount }} record(s)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="monthly-panel">
        <div class="monthly-panel-inner">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                <div style="font-size:1rem;font-weight:800;color:#1e293b;">Viewing & Filters</div>
                @if($showAll)
                    <form method="get" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <input type="hidden" name="show_all" value="1">
                        <input type="hidden" name="record_scope" value="{{ $selectedRecordScope }}">
                        <label for="year" style="font-weight:700;color:#475569;font-size:.84rem;">Year</label>
                        <select name="year" id="year" onchange="this.form.submit()" style="padding:8px 12px;border-radius:10px;border:1px solid #cbd5e1;font-weight:600;">
                            @foreach($years as $year)
                                @if($year != $currentYear)
                                    <option value="{{ $year }}" @if($year == $selectedYear) selected @endif>{{ $year }}</option>
                                @endif
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            @php
                $monthlyScopeTabs = [
                    'facility' => ['label' => 'Facility Aggregate', 'count' => $tabScopeCounts['facility'] ?? 0],
                    'main' => ['label' => 'Main Meter', 'count' => $tabScopeCounts['main'] ?? 0],
                    'submeters' => ['label' => 'Sub-meters', 'count' => $tabScopeCounts['submeters'] ?? 0],
                    'all' => ['label' => 'All', 'count' => $tabScopeCounts['all'] ?? 0],
                ];
            @endphp
            <div class="monthly-tabs" aria-label="Record scope tabs">
                @foreach($monthlyScopeTabs as $tabKey => $tab)
                    @php
                        $tabQuery = ['facility' => $facility->id, 'record_scope' => $tabKey];
                        if ($showAll) {
                            $tabQuery['show_all'] = 1;
                        }
                        if (request('year')) {
                            $tabQuery['year'] = request('year');
                        }
                        $isActiveTab = $selectedRecordScope === $tabKey;
                    @endphp
                    <a href="{{ route('facilities.monthly-records', $tabQuery) }}"
                       class="monthly-tab {{ $isActiveTab ? '' : 'inactive' }}"
                       aria-current="{{ $isActiveTab ? 'page' : 'false' }}">
                        <span>{{ $tab['label'] }}</span>
                        <span class="count">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="monthly-filter-grid">
                <div class="monthly-filter-block">
                    <h3>Record Scope</h3>
                    <form method="GET" action="{{ route('facilities.monthly-records', $facility->id) }}" style="display:flex;flex-direction:column;gap:10px;">
                        @if($showAll)
                            <input type="hidden" name="show_all" value="1">
                            @if(request('year'))
                                <input type="hidden" name="year" value="{{ request('year') }}">
                            @endif
                        @endif
                        <select id="record_scope" name="record_scope" onchange="this.form.submit()" style="padding:10px 12px;border-radius:10px;border:1px solid #cbd5e1;">
                            <option value="facility" @selected($selectedRecordScope === 'facility')>Facility Aggregate (default)</option>
                            <option value="main" @selected($selectedRecordScope === 'main')>Main Meter Records</option>
                            <option value="submeters" @selected($selectedRecordScope === 'submeters')>Sub-meter Records</option>
                            <option value="all" @selected($selectedRecordScope === 'all')>All Records (Facility + Meters)</option>
                            @foreach($meterOptions as $meterOption)
                                <option value="meter:{{ $meterOption->id }}" @selected($selectedRecordScope === ('meter:' . $meterOption->id))>
                                    {{ strtoupper((string) $meterOption->meter_type) }} - {{ $meterOption->meter_name }}
                                </option>
                            @endforeach
                        </select>
                        <div style="font-size:.82rem;color:#64748b;">
                            `Main`, `Sub-meters`, and `All` are grouped by meter. Pick a specific meter for a single-stream view.
                        </div>
                    </form>
                </div>

                <div class="monthly-filter-block">
                    <h3>Notes</h3>
                    <div class="monthly-inline-note" style="margin-bottom:8px;">
                        <i class="fa fa-circle-info" style="color:#2563eb;margin-top:2px;"></i>
                        <span>Sub-meter records are tracked separately from facility aggregate records to avoid duplicate month/year conflicts.</span>
                    </div>
                    <div class="monthly-inline-note" style="background:#f8fafc;border-color:#e2e8f0;">
                        <i class="fa fa-filter" style="color:#64748b;margin-top:2px;"></i>
                        <span>Year filter affects reconciliation and table display when `Show Past Records` is enabled.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasBaseline)
        <div style="margin-bottom:1.2rem;color:#e11d48;font-weight:700;background:#fff1f2;border:1px solid #fecdd3;border-radius:12px;padding:12px 14px;">
            You need to set a baseline kWh in the energy profile before you can add a monthly energy record.
        </div>
    @endif
    @php
        $sizeLabel = '';
        if ($hasBaseline) {
            $sizeLabel = \App\Models\Facility::resolveSizeLabelFromBaseline($baselineAvg) ?? '';
        }
    @endphp

    @if($hasBaseline)
        <div class="monthly-baseline-row">
            <div class="monthly-baseline-card">
                <div class="label">Facility Size</div>
                <div class="value" style="color:#4338ca;">{{ $sizeLabel ?: 'N/A' }}</div>
            </div>
            <div class="monthly-baseline-card">
                <div class="label">Facility Baseline kWh</div>
                @if($energyProfile && !$energyProfile->engineer_approved)
                    <div class="value" style="color:#6366f1;">Pending approval</div>
                @else
                    <div class="value">{{ number_format($baselineAvg, 2) }} kWh</div>
                    <div style="font-size:.8rem;color:#64748b;margin-top:3px;">
                        Source: {{ $energyProfile && $energyProfile->baseline_source ? $energyProfile->baseline_source : 'Energy Profile / Facility' }}
                    </div>
                @endif
            </div>
            <div class="monthly-baseline-card">
                <div class="label">Sub-meter Baseline Strategy</div>
                <div class="value" style="font-size:.96rem;">Per Meter (Configured in Meters)</div>
                <div style="font-size:.8rem;color:#64748b;margin-top:3px;">Used for sub-meter variance in this page; facility baseline remains separate.</div>
            </div>
        </div>
    @endif

    <div style="margin-bottom:18px;background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(15,23,42,0.06);border:1px solid #e5e7eb;overflow:hidden;">
        <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
            <div>
                <div style="font-size:1.02rem;font-weight:800;color:#1e293b;">
                    <i class="fa fa-scale-balanced" style="margin-right:6px;color:#2563eb;"></i>
                    Main vs Sub-meter Reconciliation ({{ $selectedYear }})
                </div>
                <div style="font-size:.88rem;color:#64748b;margin-top:3px;">
                    Compares total sub-meter usage against the best available reference: <strong>Facility Aggregate</strong> (preferred) or <strong>Main Meter</strong>.
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:5px 10px;font-size:.8rem;font-weight:800;">Months Checked: {{ $reconciliationSummary['months_checked'] }}</span>
                <span style="background:#ffedd5;color:#9a3412;border-radius:999px;padding:5px 10px;font-size:.8rem;font-weight:800;">Warnings: {{ $reconciliationSummary['warnings'] }}</span>
                @if($reconciliationSummary['critical'] > 0)
                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.8rem;font-weight:800;">Critical: {{ $reconciliationSummary['critical'] }}</span>
                @endif
            </div>
        </div>

        @if($reconciliationRows->isEmpty())
            <div style="padding:14px 16px;color:#64748b;">
                No records found for {{ $selectedYear }} yet. Add facility aggregate or meter-specific records to start reconciliation.
            </div>
        @else
            <div style="padding:10px 16px;background:#fcfdff;border-bottom:1px solid #e5e7eb;color:#475569;font-size:.86rem;">
                Warning threshold: <strong>{{ number_format($reconciliationWarningThresholdPercent, 0) }}%</strong>
                &nbsp;|&nbsp; Critical threshold: <strong>{{ number_format($reconciliationCriticalThresholdPercent, 0) }}%</strong>
                &nbsp;|&nbsp; Unmetered/Common Load = <strong>Reference - Sub-meter Sum</strong> (positive may indicate unmetered load; negative may indicate sub-meter total exceeds reference).
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;min-width:1150px;border-collapse:collapse;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:10px 12px;text-align:left;border-bottom:1px solid #e5e7eb;">Month</th>
                            <th style="padding:10px 12px;text-align:right;border-bottom:1px solid #e5e7eb;">Facility Aggregate</th>
                            <th style="padding:10px 12px;text-align:right;border-bottom:1px solid #e5e7eb;">Main Meter Total</th>
                            <th style="padding:10px 12px;text-align:right;border-bottom:1px solid #e5e7eb;">Sub-meter Total</th>
                            <th style="padding:10px 12px;text-align:left;border-bottom:1px solid #e5e7eb;">Reference Used</th>
                            <th style="padding:10px 12px;text-align:right;border-bottom:1px solid #e5e7eb;">Unmetered / Common Load (kWh)</th>
                            <th style="padding:10px 12px;text-align:right;border-bottom:1px solid #e5e7eb;">Variance %</th>
                            <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reconciliationRows as $row)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 12px;font-weight:700;color:#1e293b;">{{ $row['month_label_full'] }}</td>
                                <td style="padding:10px 12px;text-align:right;color:#334155;">{{ $row['aggregate_kwh'] !== null ? number_format($row['aggregate_kwh'], 2) : '-' }}</td>
                                <td style="padding:10px 12px;text-align:right;color:#334155;">{{ $row['main_kwh'] !== null ? number_format($row['main_kwh'], 2) : '-' }}</td>
                                <td style="padding:10px 12px;text-align:right;color:#334155;font-weight:700;">{{ $row['sub_kwh'] !== null ? number_format($row['sub_kwh'], 2) : '-' }}</td>
                                <td style="padding:10px 12px;color:#334155;">{{ $row['reference_label'] ?? '-' }}</td>
                                <td style="padding:10px 12px;text-align:right;color:{{ ($row['variance_kwh'] ?? 0) === 0 ? '#334155' : (($row['variance_kwh'] ?? 0) > 0 ? '#b45309' : '#7c3aed') }};font-weight:700;">
                                    @if($row['variance_kwh'] !== null)
                                        {{ number_format($row['variance_kwh'], 2) }}
                                        <div style="font-size:.72rem;font-weight:600;color:#64748b;">
                                            {{ $row['variance_kwh'] >= 0 ? 'Unmetered/Common' : 'Sub > Reference' }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding:10px 12px;text-align:right;color:#334155;">
                                    {{ $row['variance_percent'] !== null ? number_format($row['variance_percent'], 2) . '%' : '-' }}
                                </td>
                                <td style="padding:10px 12px;text-align:center;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:800;background:{{ $row['status_bg'] }};color:{{ $row['status_color'] }};">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @php
                            $totalAggregate = $reconciliationRows->sum(fn($row) => (float) ($row['aggregate_kwh'] ?? 0));
                            $totalMain = $reconciliationRows->sum(fn($row) => (float) ($row['main_kwh'] ?? 0));
                            $totalSub = $reconciliationRows->sum(fn($row) => (float) ($row['sub_kwh'] ?? 0));
                            $totalReference = $reconciliationRows->sum(fn($row) => (float) ($row['reference_kwh'] ?? 0));
                            $totalVariance = $totalReference > 0 || $totalSub > 0 ? round($totalReference - $totalSub, 2) : null;
                            $totalVariancePercent = ($totalReference && $totalReference > 0 && $totalVariance !== null)
                                ? round(($totalVariance / $totalReference) * 100, 2)
                                : null;
                        @endphp
                        <tr style="background:#f8fafc;">
                            <td style="padding:11px 12px;font-weight:800;color:#1e293b;border-top:2px solid #e5e7eb;">Selected Year Total</td>
                            <td style="padding:11px 12px;text-align:right;font-weight:800;color:#334155;border-top:2px solid #e5e7eb;">{{ $totalAggregate > 0 ? number_format($totalAggregate, 2) : '-' }}</td>
                            <td style="padding:11px 12px;text-align:right;font-weight:800;color:#334155;border-top:2px solid #e5e7eb;">{{ $totalMain > 0 ? number_format($totalMain, 2) : '-' }}</td>
                            <td style="padding:11px 12px;text-align:right;font-weight:800;color:#334155;border-top:2px solid #e5e7eb;">{{ $totalSub > 0 ? number_format($totalSub, 2) : '-' }}</td>
                            <td style="padding:11px 12px;color:#475569;border-top:2px solid #e5e7eb;">Mixed monthly reference</td>
                            <td style="padding:11px 12px;text-align:right;font-weight:800;color:#334155;border-top:2px solid #e5e7eb;">
                                @if($totalVariance !== null)
                                    {{ number_format($totalVariance, 2) }}
                                    <div style="font-size:.72rem;font-weight:600;color:#64748b;">
                                        {{ $totalVariance >= 0 ? 'Unmetered/Common' : 'Sub > Reference' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding:11px 12px;text-align:right;font-weight:800;color:#334155;border-top:2px solid #e5e7eb;">{{ $totalVariancePercent !== null ? number_format($totalVariancePercent, 2) . '%' : '-' }}</td>
                            <td style="padding:11px 12px;text-align:center;border-top:2px solid #e5e7eb;">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Monthly Records Table -->
    <div class="monthly-table-panel">
        <div class="monthly-table-header">
            <div>
                <div class="title">Records Table</div>
                <div class="subtitle">
                    Showing {{ $selectedRecordCount }} record(s) for {{ $selectedYear }} under {{ $selectedScopeLabel }}
                    @if($isGroupedDisplay)
                        (grouped by facility/main/sub-meter)
                    @endif
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                <div class="monthly-table-badges">
                    <span class="monthly-table-badge">{{ $showAll ? 'Historical Mode' : 'Current Year Mode' }}</span>
                    <span class="monthly-table-badge" style="background:#f8fafc;color:#334155;border-color:#e2e8f0;">{{ number_format($selectedActualKwhTotal, 2) }} kWh</span>
                    <span class="monthly-table-badge" style="background:#ecfdf5;color:#166534;border-color:#bbf7d0;">Cost: PHP {{ number_format($selectedCostTotal, 2) }}</span>
                </div>
                @if(!$showAll)
                    <a href="{{ request()->fullUrlWithQuery(['show_all' => 1, 'record_scope' => $selectedRecordScope]) }}" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:700;border:none;border-radius:10px;padding:9px 14px;font-size:.92rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Past Records</a>
                @else
                    <a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'record_scope' => $selectedRecordScope]) }}" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:700;border:none;border-radius:10px;padding:9px 14px;font-size:.92rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Current Year Only</a>
                @endif
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead style="background:#f1f5f9;">
                <tr style="text-align:center;">
                    <th style="padding:10px 14px; text-align:center;">Year</th>
                    <th style="padding:10px 14px; text-align:center;">Month</th>
                    <th style="padding:10px 14px; text-align:center;">Day</th>
                    <th style="padding:10px 14px; text-align:center;">Record Scope</th>
                    <th style="padding:10px 14px; text-align:center;">Actual kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Baseline kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Meter Baseline kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Deviation (%)</th>
                    <th style="padding:10px 14px; text-align:center;">Meter Deviation (%)</th>
                    <th style="padding:10px 14px; text-align:center;">Alert</th>
                    <th style="padding:10px 14px; text-align:center;">Energy Cost</th>
                    <th style="padding:10px 14px; text-align:center;">Bill Image</th>
                    <th style="padding:10px 14px; text-align:center;">Recommendation</th>
                    <th style="padding:10px 14px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            @php
                $__monthlyCurrentGroupKey = null;
            @endphp
            @forelse($sortedRecords as $record)
                @php
                    $recordGroupMeta = $monthlyRecordGroupMeta($record);
                    $recordGroupKey = (string) $recordGroupMeta['key'];
                    $recordGroupSummary = $monthlyGroupSummaries[$recordGroupKey] ?? null;
                @endphp

                @if($isGroupedDisplay && $__monthlyCurrentGroupKey !== $recordGroupKey)
                    <tr class="monthly-group-row">
                        <td colspan="14">
                            <div class="group-wrap">
                                <div class="group-title">
                                    <button type="button"
                                            class="monthly-group-toggle-btn"
                                            id="monthly-group-toggle-{{ $recordGroupKey }}"
                                            onclick="toggleMonthlyRecordGroup(@js($recordGroupKey))"
                                            aria-expanded="true">
                                        <span class="chevron" id="monthly-group-toggle-icon-{{ $recordGroupKey }}">▾</span>
                                        <span>Collapse</span>
                                    </button>
                                    <span class="monthly-group-pill" style="background:{{ $recordGroupMeta['pill_bg'] }};color:{{ $recordGroupMeta['pill_color'] }};border-color:{{ $recordGroupMeta['pill_border'] }};">
                                        {{ $recordGroupMeta['pill_label'] }}
                                    </span>
                                    <span>{{ $recordGroupMeta['label'] }}</span>
                                    @if(!empty($recordGroupMeta['meter_number']))
                                        <span style="font-weight:700;color:#64748b;font-size:.82rem;">({{ $recordGroupMeta['meter_number'] }})</span>
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;justify-content:flex-end;">
                                    <span style="font-size:.82rem;color:#64748b;font-weight:700;">{{ $recordGroupMeta['count_label'] }}</span>
                                    @if($recordGroupSummary)
                                        <span class="monthly-group-pill" style="background:#ffffff;color:#334155;border-color:#e2e8f0;">
                                            {{ $recordGroupSummary['record_count'] }} rec
                                        </span>
                                        <span class="monthly-group-pill" style="background:#ffffff;color:#334155;border-color:#e2e8f0;">
                                            {{ number_format((float) $recordGroupSummary['total_kwh'], 2) }} kWh
                                        </span>
                                        <span class="monthly-group-pill" style="background:#ecfdf5;color:#166534;border-color:#bbf7d0;">
                                            PHP {{ number_format((float) $recordGroupSummary['total_cost'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @php
                        $__monthlyCurrentGroupKey = $recordGroupKey;
                    @endphp
                @endif

                <tr style="border-bottom:1px solid #e5e7eb; text-align:center;"
                    data-monthly-group-row="{{ $recordGroupKey }}">
                    <td style="padding:10px 14px; text-align:center;">{{ $record->year }}</td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        @endphp
                        {{ $months[$record->month - 1] ?? $record->month }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">{{ $record->day ?? '-' }}</td>
                    <td style="padding:10px 14px; text-align:center;">
                        @if($record->meter)
                            @php
                                $meterTypeColor = strtolower((string) $record->meter->meter_type) === 'main' ? '#1d4ed8' : '#7c3aed';
                                $meterTypeBg = strtolower((string) $record->meter->meter_type) === 'main' ? '#eff6ff' : '#f3e8ff';
                            @endphp
                            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                <span style="display:inline-flex;padding:3px 9px;border-radius:999px;font-size:.74rem;font-weight:800;background:{{ $meterTypeBg }};color:{{ $meterTypeColor }};">
                                    {{ strtoupper((string) $record->meter->meter_type) }}
                                </span>
                                <span style="font-weight:600;color:#334155;">{{ $record->meter->meter_name }}</span>
                            </div>
                        @else
                            <span style="display:inline-flex;padding:4px 10px;border-radius:999px;font-size:.76rem;font-weight:800;background:#f1f5f9;color:#334155;">
                                FACILITY AGGREGATE
                            </span>
                        @endif
                    </td>
                    @php
                        $meterBaselineKwh = ($record->meter && $record->meter->baseline_kwh !== null && $record->meter->baseline_kwh !== '')
                            ? (float) $record->meter->baseline_kwh
                            : null;
                        $meterDeviationPercent = ($meterBaselineKwh !== null && $meterBaselineKwh > 0 && $record->actual_kwh !== null)
                            ? round((((float) $record->actual_kwh - $meterBaselineKwh) / $meterBaselineKwh) * 100, 2)
                            : null;
                    @endphp
                    <td style="padding:10px 14px; text-align:center;">{{ $record->actual_kwh !== null ? number_format((float) $record->actual_kwh, 2) : '-' }}</td>
                    <td style="padding:10px 14px; text-align:center;">{{ $record->baseline_kwh !== null ? number_format((float) $record->baseline_kwh, 2) : '-' }}</td>
                    <td style="padding:10px 14px; text-align:center;color:#475569;">
                        @if($record->meter)
                            {{ $meterBaselineKwh !== null ? number_format($meterBaselineKwh, 2) : '-' }}
                            @if(strtolower((string) ($record->meter->meter_type ?? '')) === 'sub')
                                <div style="font-size:.72rem;color:#7c3aed;font-weight:700;">SUB-METER BASELINE</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        {{ $record->deviation !== null ? $record->deviation . '%' : '' }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;color:{{ ($meterDeviationPercent ?? 0) >= 0 ? '#334155' : '#7c3aed' }};">
                        @if($meterDeviationPercent !== null)
                            <span style="font-weight:700;">{{ number_format($meterDeviationPercent, 2) }}%</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $alert = '-';
                            $deviation = $record->deviation;
                            $baseline = $record->baseline_kwh;
                            $alertColor = '#2563eb'; // Default color
                            if ($deviation !== null && $baseline !== null) {
                                $size = \App\Models\Facility::resolveSizeLabelFromBaseline($baseline) ?? 'Small';
                                $sizeThresholdKey = match ($size) {
                                    'Small' => 'small',
                                    'Medium' => 'medium',
                                    'Large' => 'large',
                                    'Extra Large' => 'xlarge',
                                    default => 'small',
                                };
                                $t = $alertThresholdsBySize[$sizeThresholdKey] ?? $alertThresholdsBySize['small'];
                                if ($deviation > $t['level5']) {
                                    $alert = 'Critical';
                                    $alertColor = '#7c1d1d'; // dark red
                                } elseif ($deviation > $t['level4']) {
                                    $alert = 'Very High';
                                    $alertColor = '#e11d48'; // red
                                } elseif ($deviation > $t['level3']) {
                                    $alert = 'High';
                                    $alertColor = '#f59e42'; // orange
                                } elseif ($deviation > $t['level2']) {
                                    $alert = 'Warning';
                                    $alertColor = '#f59e42'; // orange
                                } else {
                                    $alert = 'Normal';
                                    $alertColor = '#16a34a'; // green
                                }
                            }
                        @endphp
                        <span style="color:{{ $alertColor }}; font-weight:600;">{{ $alert }}</span>
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            // Use DB value if present, else compute using default rate
                            $rate = isset($record->rate_per_kwh) && $record->rate_per_kwh ? $record->rate_per_kwh : 12.00; // default 12.00
                            $computedCost = $record->actual_kwh * $rate;
                        @endphp
                        ₱{{ number_format($computedCost, 2) }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $billPath = ltrim((string) ($record->bill_image ?? ''), '/');
                            if (str_starts_with($billPath, 'http://') || str_starts_with($billPath, 'https://')) {
                                $billImageUrl = $billPath;
                            } elseif (str_starts_with($billPath, 'uploads/')) {
                                $billImageUrl = asset($billPath);
                            } elseif (str_starts_with($billPath, 'storage/')) {
                                $billPath = substr($billPath, strlen('storage/'));
                                $billImageUrl = ($billPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($billPath))
                                    ? asset('storage/' . $billPath)
                                    : null;
                            } else {
                                $billImageUrl = ($billPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($billPath))
                                    ? asset('storage/' . $billPath)
                                    : null;
                            }
                        @endphp
                        @if($billImageUrl)
                            <a href="{{ $billImageUrl }}" target="_blank" style="display:inline-block;">
                                <img src="{{ $billImageUrl }}" alt="Bill Image" style="max-width:60px;max-height:60px;border-radius:7px;box-shadow:0 2px 8px #2563eb22;object-fit:cover;">
                            </a>
                        @else
                            &nbsp;
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $alertIcons = [
                                'Critical' => ['icon' => '🚨', 'color' => '#7c1d1d'],
                                'Very High' => ['icon' => '🚩', 'color' => '#e11d48'],
                                'High' => ['icon' => '⚡', 'color' => '#f59e42'],
                                'Warning' => ['icon' => '🔔', 'color' => '#f59e42'],
                                'Normal' => ['icon' => '💡', 'color' => '#16a34a'],
                                '-' => ['icon' => 'ℹ️', 'color' => '#64748b'],
                            ];
                            $recommendations = [
                                'Critical' => 'Critical: Take urgent action to reduce energy use. Investigate immediately.',
                                'Very High' => 'Very high deviation: Investigate and address immediately.',
                                'High' => 'High deviation: Review and optimize energy usage.',
                                'Warning' => 'Warning: Monitor and plan improvements.',
                                'Normal' => 'Normal: Maintain current practices.',
                                '-' => 'No recommendation.'
                            ];
                            $iconData = $alertIcons[$alert] ?? ['icon' => 'ℹ️', 'color' => '#64748b'];
                            $recommendation = $recommendations[$alert] ?? 'No recommendation.';
                        @endphp
                        <button type="button" title="View Recommendation" style="background: none; border: none; color: {{ $iconData['color'] }}; font-size: 1.3rem; cursor: pointer;" onclick="openMonthlyRecommendationModal('{{ $facility->name }}', '{{ $iconData['icon'] }}', '{{ addslashes($recommendation) }}', '{{ $alert }}')">
                            <span style="font-size:1.3rem;">{{ $iconData['icon'] }}</span>
                        </button>
                    </td>
                    <!-- Monthly Recommendation Modal -->
                    <div id="monthlyRecommendationModal" style="display:none;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
                        <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
                            <div id="monthlyRecommendationModalBox" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                                <button type="button" onclick="closeMonthlyRecommendationModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                                <h2 id="monthlyRecommendationModalTitle" style="margin-bottom:12px;font-size:1.3rem;font-weight:700;"></h2>
                                <div id="monthlyRecommendationText" style="margin:0 0 10px 0;padding:0;font-size:1.08rem;"></div>
                                <div style="text-align:right;margin-top:18px;">
                                    <button type="button" onclick="closeMonthlyRecommendationModal()" style="background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-weight:600;font-size:1rem;">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function openMonthlyRecommendationModal(facilityName, icon, recommendation, alert) {
                        const modal = document.getElementById('monthlyRecommendationModal');
                        const title = document.getElementById('monthlyRecommendationModalTitle');
                        const text  = document.getElementById('monthlyRecommendationText');
                        const box   = document.getElementById('monthlyRecommendationModalBox');
                        const isDark = document.body.classList.contains('dark-mode');

                        const alertStyles = {
                            'Critical': { color: '#7f1d1d', bg: '#fee2e2', border: '#fca5a5', icon: '!', darkBg: 'rgba(127,29,29,0.26)', darkBorder: 'rgba(248,113,113,0.35)' },
                            'Very High': { color: '#9f1239', bg: '#ffe4e6', border: '#fda4af', icon: '!', darkBg: 'rgba(190,18,60,0.22)', darkBorder: 'rgba(244,114,182,0.35)' },
                            'High': { color: '#9a3412', bg: '#ffedd5', border: '#fdba74', icon: '!', darkBg: 'rgba(194,65,12,0.22)', darkBorder: 'rgba(251,146,60,0.35)' },
                            'Warning': { color: '#92400e', bg: '#fef3c7', border: '#fcd34d', icon: 'i', darkBg: 'rgba(146,64,14,0.20)', darkBorder: 'rgba(251,191,36,0.30)' },
                            'Normal': { color: '#1d4ed8', bg: '#dbeafe', border: '#93c5fd', icon: 'i', darkBg: 'rgba(37,99,235,0.18)', darkBorder: 'rgba(147,197,253,0.28)' },
                            '-': { color: '#475569', bg: '#e2e8f0', border: '#cbd5e1', icon: 'i', darkBg: 'rgba(51,65,85,0.25)', darkBorder: 'rgba(148,163,184,0.22)' },
                        };

                        const style = alertStyles[alert] || { color: '#475569', bg: '#f1f5f9', border: '#e2e8f0', icon: 'i', darkBg: 'rgba(51,65,85,0.25)', darkBorder: 'rgba(148,163,184,0.22)' };
                        const badgeIcon = style.icon;

                        title.innerHTML = `<span style='display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:999px;margin-right:8px;font-size:1rem;font-weight:800;background:${isDark ? '#1f2937' : '#ffffff'};border:1px solid ${isDark ? style.darkBorder : style.border};color:${isDark ? '#e2e8f0' : style.color};'>${badgeIcon}</span> Recommendation for ${facilityName}`;
                        text.textContent = recommendation || 'No recommendation.';
                        text.style.color = isDark ? '#e2e8f0' : style.color;
                        text.style.background = isDark ? style.darkBg : style.bg;
                        text.style.border = `1px solid ${isDark ? style.darkBorder : style.border}`;
                        text.style.padding = '12px 16px';
                        text.style.borderRadius = '10px';
                        text.style.lineHeight = '1.45';
                        text.style.fontWeight = '600';
                        box.style.background = isDark ? '#111827' : '#fff';
                        modal.style.display = 'flex';
                    }
                    function closeMonthlyRecommendationModal() {
                        document.getElementById('monthlyRecommendationModal').style.display = 'none';
                    }
                    </script>
                    <td style="padding:10px 14px; text-align:center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                        @php
                            $alertText = strtolower((string) $record->alert);
                            $isHighAction = in_array($record->alert, ['Critical', 'Very High', 'High']) || str_contains($alertText, 'level 5') || str_contains($alertText, 'level 4') || str_contains($alertText, 'level 3');
                            $isWarningAction = $record->alert === 'Warning' || str_contains($alertText, 'level 2');
                            $isNormalAction = $record->alert === 'Normal' || (str_contains($alertText, 'normal') && str_contains($alertText, 'low'));
                        @endphp
                        @if($isHighAction)
                            <button type="button" title="Create Energy Action (High)" style="background: none; border: none; color: #e11d48; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal({{ $record->id }}, 'High')">
                                <span style="font-size:1.3rem;">⚠️</span>
                            </button>
                        @elseif($isWarningAction)
                            <button type="button" title="Create Energy Action (Medium)" style="background: none; border: none; color: #f59e42; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal({{ $record->id }}, 'Medium')">
                                <span style="font-size:1.3rem;">⚡</span>
                            </button>
                        @elseif($isNormalAction)
                            <!-- No recommendation button in Action column -->
                        @endif
                       
                        <form id="deleteMonthlyRecordForm-{{ $record->id }}" action="{{ route('energy-records.delete', ['facility' => $facility->id, 'record' => $record->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" title="Delete"
                                style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;"
                                onclick="openDeleteMonthlyRecordModal({{ $record->id }}, @js($months[$record->month-1] ?? ''), {{ (int) $record->year }})"
                                data-id="{{ $record->id }}"
                            >
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                <!-- Energy Action Modal -->
                <div id="energyActionModal" style="display:none;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
                    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
                        <div style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                            <button type="button" onclick="closeEnergyActionModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                            <h2 id="energyModalTitle" style="margin-bottom:12px;font-size:1.3rem;font-weight:700;color:#2563eb;"></h2>
                            <ul id="energyRecommendations" style="margin:0 0 10px 18px;padding:0;font-size:1.08rem;color:#222;"></ul>
                            <div style="text-align:right;margin-top:18px;">
                                <button type="button" onclick="closeEnergyActionModal()" style="background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-weight:600;font-size:1rem;">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                function openEnergyActionModal(recordId, level) {
                    const modal = document.getElementById('energyActionModal');
                    const title = document.getElementById('energyModalTitle');
                    const list  = document.getElementById('energyRecommendations');
                    list.innerHTML = '';
                    if (level === 'High') {
                        title.innerHTML = '⚠ High Energy Consumption';
                        list.innerHTML = `
                            <li>Immediately inspect major energy-consuming equipment</li>
                            <li>Limit non-essential electrical usage</li>
                            <li>Notify facility manager</li>
                            <li>Schedule urgent maintenance</li>
                        `;
                    } else if (level === 'Medium') {
                        title.innerHTML = '⚡ Medium Energy Alert';
                        list.innerHTML = `
                            <li>Review monthly energy usage trends</li>
                            <li>Check operating hours of equipment</li>
                            <li>Apply basic energy-saving measures</li>
                        `;
                    } else if (level === 'Low') {
                        title.innerHTML = '💡 Low Deviation - Good Practice';
                        list.innerHTML = `
                            <li>Maintain current energy-saving practices</li>
                            <li>Continue monitoring for unusual changes</li>
                            <li>Encourage staff to sustain efficiency</li>
                        `;
                    }
                    modal.style.display = 'flex';
                    setTimeout(() => { modal.classList.add('show'); }, 10);
                }

                function closeEnergyActionModal() {
                    const modal = document.getElementById('energyActionModal');
                    modal.classList.remove('show');
                    setTimeout(() => { modal.style.display = 'none'; }, 200);
                }
                </script>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="padding:18px 0;text-align:center;color:#b91c1c;">No records found for the selected scope.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="monthly-footer-actions">
        <div class="monthly-footer-help">
            Tip: Use `All Records` scope when reviewing reconciliation, then switch to a specific meter to inspect sub-meter trends.
        </div>
        <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:.85rem;">
            <i class="fa fa-circle-info" style="color:#2563eb;"></i>
            Table and reconciliation are both filtered by the selected year.
        </div>
    </div>
</div>


<!-- ADD MONTHLY RECORD MODAL (Consistent UI) -->

<!-- ADD MONTHLY RECORD MODAL (Centered Overlay) -->
<div id="addModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:420px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
            <button type="button" onclick="closeAddModal()" style="position:absolute;top:12px;right:12px;font-size:1.5rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Add Monthly Record</h2>
            <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Enter new monthly record details below.</div>
            @if($errors->has('duplicate'))
                <div id="duplicateModal" style="display:flex;justify-content:center;align-items:center;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
                    <div class="modal-content" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                        <button type="button" onclick="closeDuplicateModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                        <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Existing Data</h3>
                        <div style="margin-bottom:18px;font-size:1.05rem;color:#222;">{{ $errors->first('duplicate') }}</div>
                        <div style="display:flex;gap:10px;">
                            <button type="button" onclick="closeDuplicateModal()" style="background:#2563eb;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">OK</button>
                        </div>
                    </div>
                </div>
                <script>
                function closeDuplicateModal() {
                    document.getElementById('duplicateModal').style.display = 'none';
                }
                window.onload = function() {
                    if(document.getElementById('duplicateModal')) {
                        document.getElementById('addModal').style.display = 'block';
                    }
                };
                </script>
            @endif
            <form id="addMonthlyRecordForm" method="POST" action="{{ route('energy-records.store', ['facility' => $facility->id]) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_date" style="font-weight:500;">Date</label>
                    <input type="date" id="add_date" name="date" value="{{ date('Y-m-d') }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_meter_id" style="font-weight:500;">Record Scope / Meter</label>
                    <select id="add_meter_id" name="meter_id" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                        <option value="">Facility Aggregate (No Meter)</option>
                        @foreach($meterOptions as $meterOption)
                            <option value="{{ $meterOption->id }}"
                                data-meter-type="{{ strtolower((string) ($meterOption->meter_type ?? '')) }}"
                                data-meter-baseline="{{ $meterOption->baseline_kwh !== null && $meterOption->baseline_kwh !== '' ? number_format((float) $meterOption->baseline_kwh, 2, '.', '') : '' }}"
                                @selected((string) old('meter_id') === (string) $meterOption->id)>
                                {{ strtoupper((string) $meterOption->meter_type) }} - {{ $meterOption->meter_name }}
                                @if($meterOption->meter_number) ({{ $meterOption->meter_number }}) @endif
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size:.82rem;color:#64748b;">Choose a meter for main/sub-meter tracking, or leave blank for facility aggregate record.</div>
                    @error('meter_id')
                        <div style="font-size:.85rem;color:#b91c1c;font-weight:600;">{{ $message }}</div>
                    @enderror
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_actual_kwh" style="font-weight:500;">Actual kWh</label>
                        <input type="number" step="0.01" id="add_actual_kwh" name="actual_kwh" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_rate_per_kwh" style="font-weight:500;">Rate per kWh</label>
                        <input type="number" step="0.01" id="add_rate_per_kwh" name="rate_per_kwh" value="12.00" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_baseline_kwh" style="font-weight:500;">Baseline kWh</label>
                    <input type="number" step="0.01" id="add_baseline_kwh" name="baseline_kwh" value="{{ isset($baselineAvg) ? number_format($baselineAvg, 2, '.', '') : '' }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    <div id="add_baseline_kwh_hint" style="font-size:.82rem;color:#64748b;">
                        Default: Facility baseline (Energy Profile / Facility baseline).
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_energy_cost" style="font-weight:500;">Energy Cost</label>
                    <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" required readonly style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;background:#f3f4f6;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_bill_image" style="font-weight:500;">Bill Image</label>
                    <input type="file" id="add_bill_image" name="bill_image" accept="image/*" style="border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                {{-- ❌ WALANG AVERAGE INPUT DITO --}}
                {{-- Monthly = RAW DATA LANG --}}
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="background:#2563eb;color:#fff;padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;flex:1;">Save</button>
                    <button type="button" onclick="closeAddModal()" style="background:#f3f4f6;color:#222;padding:12px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    syncMonthlyBaselineFromScope();
    setTimeout(computeEnergyCost, 100); // compute on open
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function toggleMonthlyRecordGroup(groupKey) {
    const rows = document.querySelectorAll(`[data-monthly-group-row="${groupKey}"]`);
    const btn = document.getElementById(`monthly-group-toggle-${groupKey}`);
    const icon = document.getElementById(`monthly-group-toggle-icon-${groupKey}`);
    if (!rows.length || !btn) return;

    const willCollapse = !btn.classList.contains('collapsed');
    rows.forEach((row) => row.classList.toggle('monthly-group-hidden', willCollapse));
    btn.classList.toggle('collapsed', willCollapse);
    btn.setAttribute('aria-expanded', willCollapse ? 'false' : 'true');
    const label = btn.querySelector('span:last-child');
    if (label) label.textContent = willCollapse ? 'Expand' : 'Collapse';
    if (icon) icon.textContent = willCollapse ? '▸' : '▾';
}
function computeEnergyCost() {
    const kwh = parseFloat(document.getElementById('add_actual_kwh').value) || 0;
    const rate = parseFloat(document.getElementById('add_rate_per_kwh').value) || 0;
    const cost = kwh * rate;
    document.getElementById('add_energy_cost').value = cost ? cost.toFixed(2) : '';
}
function syncMonthlyBaselineFromScope() {
    const meterSelect = document.getElementById('add_meter_id');
    const baselineInput = document.getElementById('add_baseline_kwh');
    const hint = document.getElementById('add_baseline_kwh_hint');
    if (!meterSelect || !baselineInput) return;

    const selected = meterSelect.options[meterSelect.selectedIndex];
    const facilityBaseline = @json($monthlyFormFacilityBaseline);

    if (!selected || !selected.value) {
        if (facilityBaseline !== '') baselineInput.value = facilityBaseline;
        if (hint) hint.textContent = 'Default: Facility baseline (Energy Profile / Facility baseline).';
        return;
    }

    const meterType = String(selected.dataset.meterType || '').toLowerCase();
    const meterBaseline = String(selected.dataset.meterBaseline || '').trim();

    if (meterBaseline !== '') {
        baselineInput.value = meterBaseline;
        if (hint) {
            hint.textContent = (meterType === 'sub' ? 'Sub-meter baseline loaded from Meters Management.' : 'Meter baseline loaded from Meters Management.') + ' You can still edit this before saving.';
        }
        return;
    }

    if (facilityBaseline !== '') {
        baselineInput.value = facilityBaseline;
    }
    if (hint) {
        hint.textContent = meterType === 'sub'
            ? 'No sub-meter baseline configured yet. Using facility baseline as fallback.'
            : 'No meter baseline configured yet. Using facility baseline as fallback.';
    }
}
document.getElementById('add_actual_kwh').addEventListener('input', computeEnergyCost);
document.getElementById('add_rate_per_kwh').addEventListener('input', computeEnergyCost);
document.getElementById('add_meter_id')?.addEventListener('change', syncMonthlyBaselineFromScope);
syncMonthlyBaselineFromScope();
</script>

<!-- DELETE MONTHLY RECORD MODAL -->
<div id="deleteMonthlyRecordModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:380px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
            <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Delete Monthly Record</h3>
            <div id="deleteMonthlyRecordText" style="margin-bottom:18px;font-size:1.05rem;color:#222;"></div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="background:#f3f4f6;color:#222;padding:10px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
                <button id="confirmDeleteMonthlyRecordBtn" type="button" style="background:#e11d48;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteMonthlyRecordId = null;
function openDeleteMonthlyRecordModal(recordId, monthName, year) {
    deleteMonthlyRecordId = recordId;
    document.getElementById('deleteMonthlyRecordText').innerText = `Are you sure you want to delete the record for ${monthName} ${year}?`;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'flex';
}
function closeDeleteMonthlyRecordModal() {
    deleteMonthlyRecordId = null;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'none';
}
document.getElementById('confirmDeleteMonthlyRecordBtn').onclick = function() {
    if (deleteMonthlyRecordId) {
        document.getElementById('deleteMonthlyRecordForm-' + deleteMonthlyRecordId).submit();
    }
};
</script>

@endsection
