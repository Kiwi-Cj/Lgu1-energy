@extends('layouts.qc-admin')
@section('title', 'Utility Cash Flow & Outflow Tracking')

@section('content')
@php
    $facilityId = $selectedFacility ? $selectedFacility->id : 'all';
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
                setTimeout(() => el.remove(), 400);
            }, 4500);
        }
    });
});
</script>

<div class="cashflow-page">
    <div class="report-card-container">

        <!-- TOP HEADER & CONTROLS -->
        <div class="cf-header">
            <div class="cf-title-wrap">
                <h2><i class="fa-solid fa-money-bill-trend-up" style="color:#059669;"></i> Utility Cash Flow &amp; Outflow Tracking</h2>
                <p>Monitor actual electricity cash outflows, billing statements, effective unit rates, and expenditure projections across <strong>Local LGU</strong> and <strong>CPRF Integration</strong>.</p>
                
                <div class="cf-badge-row">
                    <!-- Quick Scope Switcher Pills -->
                    <div class="cf-scope-pills">
                        <a href="{{ route('modules.cashflow.index', ['facility_id' => 'local', 'year' => $fiscalYear]) }}" 
                           class="cf-scope-pill {{ $selectedFacilityId === 'local' || ($selectedFacility && !$selectedFacility->isCprfManaged()) ? 'active-local' : '' }}">
                            <i class="fa-solid fa-building-flag"></i> Local Facilities ({{ $localFacilities->count() }})
                        </a>
                        <a href="{{ route('modules.cashflow.index', ['facility_id' => 'cprf', 'year' => $fiscalYear]) }}" 
                           class="cf-scope-pill {{ $selectedFacilityId === 'cprf' || ($selectedFacility && $selectedFacility->isCprfManaged()) ? 'active-cprf' : '' }}">
                            <i class="fa-solid fa-bolt-lightning"></i> CPRF Integrated ({{ $cprfFacilities->count() }})
                        </a>
                    </div>

                    <!-- Tracking Mode Chip -->
                    @if($hasEnactedBudget)
                        <span class="cf-mode-chip budget-mode" title="{{ $budgetNotes }}">
                            <i class="fa-solid fa-wallet"></i> Enacted Budget Active (₱{{ number_format($annualBudget, 2) }})
                        </span>
                    @else
                        <span class="cf-mode-chip outflow-mode" title="Operating in pure Cash Flow / Actual Expense tracking mode">
                            <i class="fa-solid fa-receipt"></i> Actual Outflow Mode (No Budget Imposed)
                        </span>
                    @endif
                </div>
            </div>

            <div class="cf-controls">
                <!-- Facility Switcher -->
                <div class="cf-control-group">
                    <label for="cfFacilitySwitcher"><i class="fa-solid fa-filter"></i> Facility / System Scope</label>
                    <select id="cfFacilitySwitcher" class="cf-select" onchange="switchCashFlowScope()">
                        <optgroup label="Aggregated Scopes">
                            <option value="local" {{ $selectedFacilityId === 'local' ? 'selected' : '' }}>
                                🏢 All Local Facilities (Aggregated)
                            </option>
                            <option value="cprf" {{ $selectedFacilityId === 'cprf' ? 'selected' : '' }}>
                                ⚡ All CPRF Integrated Facilities (Aggregated)
                            </option>
                        </optgroup>

                        @if($localFacilities->isNotEmpty())
                            <optgroup label="Local Facilities ({{ $localFacilities->count() }})">
                                @foreach($localFacilities as $fac)
                                    <option value="{{ $fac->id }}" {{ (string)$selectedFacilityId === (string)$fac->id ? 'selected' : '' }}>
                                        {{ $fac->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if($cprfFacilities->isNotEmpty())
                            <optgroup label="CPRF Integrated Facilities ({{ $cprfFacilities->count() }})">
                                @foreach($cprfFacilities as $fac)
                                    <option value="{{ $fac->id }}" {{ (string)$selectedFacilityId === (string)$fac->id ? 'selected' : '' }}>
                                        {{ $fac->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Fiscal Year Switcher -->
                <div class="cf-control-group">
                    <label for="cfYearSwitcher"><i class="fa-regular fa-calendar"></i> Fiscal Year</label>
                    <select id="cfYearSwitcher" class="cf-select" onchange="switchCashFlowScope()">
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ (int)$fiscalYear === (int)$yr ? 'selected' : '' }}>
                                FY {{ $yr }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="cf-actions">
                    <a href="{{ route('modules.cashflow.export', ['facility_id' => $selectedFacilityId, 'year' => $fiscalYear]) }}" class="cf-btn-secondary" data-secure-download title="Download CSV Statement">
                        <i class="fa-solid fa-file-csv"></i> Export Statement
                    </a>

                    @if($canManageBudget && $selectedFacility)
                        <button type="button" class="cf-btn-primary" onclick="openBudgetModal()">
                            <i class="fa-solid fa-sliders"></i> {{ $hasEnactedBudget ? 'Manage Budget' : 'Set Budget (Optional)' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- HERO KPI CARDS -->
        <div class="cf-kpi-grid">
            
            @if($hasEnactedBudget)
                <!-- BUDGET MODE CARDS -->
                <!-- CARD 1: ANNUAL BUDGET -->
                <div class="cf-kpi-card border-indigo">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Annual Utility Budget</span>
                        <div class="cf-kpi-icon bg-indigo"><i class="fa-solid fa-wallet"></i></div>
                    </div>
                    <div class="cf-kpi-value">
                        ₱{{ number_format($annualBudget, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        <span>₱{{ number_format($annualBudget / 12, 2) }} / month allocation</span>
                        <span class="cf-badge badge-custom"><i class="fa-solid fa-check"></i> Enacted Budget</span>
                    </div>
                </div>

                <!-- CARD 2: YTD ACTUAL CASH OUTFLOW -->
                <div class="cf-kpi-card border-rose">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">YTD Actual Outflow</span>
                        <div class="cf-kpi-icon bg-rose"><i class="fa-solid fa-receipt"></i></div>
                    </div>
                    <div class="cf-kpi-value text-rose">
                        ₱{{ number_format($ytdActualOutflow, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        <span><strong>{{ $approvedBillsCount }}</strong> approved monthly bills paid</span>
                        <span>{{ number_format($ytdActualKwh, 1) }} kWh used</span>
                    </div>
                </div>

                <!-- CARD 3: REMAINING BUDGET BALANCE -->
                <div class="cf-kpi-card border-emerald">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Remaining Budget Balance</span>
                        <div class="cf-kpi-icon bg-emerald"><i class="fa-solid fa-scale-balanced"></i></div>
                    </div>
                    <div class="cf-kpi-value {{ ($remainingBudget ?? 0) >= 0 ? 'text-emerald' : 'text-danger' }}">
                        ₱{{ number_format($remainingBudget ?? 0, 2) }}
                    </div>
                    <div class="cf-progress-wrap">
                        <div class="cf-progress-info">
                            <span>Budget Burn Rate</span>
                            <strong>{{ $burnRatePct ?? 0 }}%</strong>
                        </div>
                        <div class="cf-progress-bar-bg">
                            <div class="cf-progress-bar-fill {{ ($burnRatePct ?? 0) > 100 ? 'over' : (($burnRatePct ?? 0) > 80 ? 'warn' : '') }}" style="width: {{ min(100, (float)($burnRatePct ?? 0)) }}%;"></div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: FINANCIAL COST SAVINGS -->
                <div class="cf-kpi-card border-cyan">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Financial Cost Savings</span>
                        <div class="cf-kpi-icon bg-cyan"><i class="fa-solid fa-piggy-bank"></i></div>
                    </div>
                    <div class="cf-kpi-value text-cyan">
                        ₱{{ number_format($costSavingsYtd, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        <span>Retained savings from energy conservation</span>
                        <span class="cf-badge badge-success"><i class="fa-solid fa-arrow-trend-down"></i> Cost Avoidance</span>
                    </div>
                </div>

            @else
                <!-- PURE CASH FLOW MODE CARDS (NO BUDGET IMPOSED) -->
                <!-- CARD 1: YTD ACTUAL CASH OUTFLOW -->
                <div class="cf-kpi-card border-rose">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">YTD Actual Outflow</span>
                        <div class="cf-kpi-icon bg-rose"><i class="fa-solid fa-receipt"></i></div>
                    </div>
                    <div class="cf-kpi-value text-rose">
                        ₱{{ number_format($ytdActualOutflow, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        <span><strong>{{ $approvedBillsCount }}</strong> approved bills paid</span>
                        <span>{{ number_format($ytdActualKwh, 1) }} kWh used</span>
                    </div>
                </div>

                <!-- CARD 2: AVERAGE MONTHLY OUTFLOW -->
                <div class="cf-kpi-card border-indigo">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Average Monthly Outflow</span>
                        <div class="cf-kpi-icon bg-indigo"><i class="fa-solid fa-calendar-days"></i></div>
                    </div>
                    <div class="cf-kpi-value text-indigo">
                        ₱{{ number_format($avgMonthlyOutflow, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        <span>Computed across {{ $approvedBillsCount }} billed month(s)</span>
                        <span class="cf-badge badge-neutral">Monthly Outflow</span>
                    </div>
                </div>

                <!-- CARD 3: EFFECTIVE UNIT RATE -->
                <div class="cf-kpi-card border-amber">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Effective Unit Rate</span>
                        <div class="cf-kpi-icon bg-amber"><i class="fa-solid fa-bolt-lightning"></i></div>
                    </div>
                    <div class="cf-kpi-value text-amber">
                        ₱{{ number_format($avgRatePerKwh, 2) }} <small style="font-size:0.85rem; font-weight:700; color:#64748b;">/ kWh</small>
                    </div>
                    <div class="cf-kpi-meta">
                        <span>Weighted average unit cost for FY {{ $fiscalYear }}</span>
                        <span class="cf-badge badge-neutral">Tariff Rate</span>
                    </div>
                </div>

                <!-- CARD 4: PROJECTED ANNUAL OUTFLOW / SAVINGS -->
                <div class="cf-kpi-card border-emerald">
                    <div class="cf-kpi-top">
                        <span class="cf-kpi-title">Projected Year-End Outflow</span>
                        <div class="cf-kpi-icon bg-emerald"><i class="fa-solid fa-chart-line"></i></div>
                    </div>
                    <div class="cf-kpi-value text-emerald">
                        ₱{{ number_format($projectedYearEndOutflow, 2) }}
                    </div>
                    <div class="cf-kpi-meta">
                        @if($costSavingsYtd > 0)
                            <span style="color:#047857; font-weight:700;">₱{{ number_format($costSavingsYtd, 2) }} retained savings</span>
                            <span class="cf-badge badge-success"><i class="fa-solid fa-piggy-bank"></i> Conserved</span>
                        @else
                            <span>Annualized 12-month expense projection</span>
                            <span class="cf-badge badge-estimated"><i class="fa-solid fa-calculator"></i> Forecast</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- PROJECTION & FORECAST BANNER -->
        @if($hasEnactedBudget)
            <div class="cf-projection-banner {{ ($projectedDeficitOrSurplus ?? 0) >= 0 ? 'surplus' : 'deficit' }}">
                <div class="cf-proj-icon">
                    <i class="fa-solid {{ ($projectedDeficitOrSurplus ?? 0) >= 0 ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                </div>
                <div class="cf-proj-text">
                    <h4>Fiscal Year-End Expenditure Projection &bull; {{ $scopeTitle }} (FY {{ $fiscalYear }})</h4>
                    <p>
                        Based on the average monthly outflow of <strong>₱{{ number_format($avgMonthlyOutflow, 2) }}</strong>, 
                        the projected year-end expenditure is <strong>₱{{ number_format($projectedYearEndOutflow, 2) }}</strong>.
                        @if(($projectedDeficitOrSurplus ?? 0) >= 0)
                            This facility is projected to operate <strong style="color:#047857;">within budget</strong> with an estimated year-end surplus of <strong>₱{{ number_format($projectedDeficitOrSurplus ?? 0, 2) }}</strong>.
                        @else
                            This facility is currently tracking toward an estimated budget deficit of <strong style="color:#dc2626;">₱{{ number_format(abs($projectedDeficitOrSurplus ?? 0), 2) }}</strong> without intervention.
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="cf-projection-banner info">
                <div class="cf-proj-icon text-indigo">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div class="cf-proj-text">
                    <h4>Fiscal Year-End Outflow Forecast &bull; {{ $scopeTitle }} (FY {{ $fiscalYear }})</h4>
                    <p>
                        Based on the actual average monthly outflow of <strong>₱{{ number_format($avgMonthlyOutflow, 2) }}</strong> across {{ $approvedBillsCount }} approved billing month(s), 
                        the projected total electricity expenditure for FY {{ $fiscalYear }} is <strong>₱{{ number_format($projectedYearEndOutflow, 2) }}</strong>.
                        @if($costSavingsYtd > 0)
                            This includes <strong>₱{{ number_format($costSavingsYtd, 2) }}</strong> in retained financial savings from energy conservation.
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <!-- CASH FLOW TREND CHART -->
        <div class="cf-card">
            <div class="cf-card-header">
                <div>
                    <h3><i class="fa-solid fa-chart-column" style="color:#2563eb;"></i> Monthly Utility Cash Outflow Trend</h3>
                    <p>
                        @if($hasEnactedBudget)
                            Comparison of monthly utility budget appropriation versus actual paid electricity outflow.
                        @else
                            Month-by-month actual electricity bill expenditures and energy conservation savings.
                        @endif
                    </p>
                </div>
            </div>
            <div class="cf-chart-container">
                <canvas id="cashFlowTrendChart" height="90"></canvas>
            </div>
        </div>

        <!-- COMPREHENSIVE MONTHLY CASH FLOW LEDGER TABLE -->
        <div class="cf-card">
            <div class="cf-card-header">
                <div>
                    <h3><i class="fa-solid fa-table-list" style="color:#059669;"></i> Fiscal Year {{ $fiscalYear }} Cash Flow Ledger &bull; {{ $scopeTitle }}</h3>
                    <p>
                        @if($hasEnactedBudget)
                            Detailed month-by-month accounting of allocated funds, utility bills paid, rate analysis, and net variance.
                        @else
                            Detailed month-by-month accounting of electricity bills paid, energy consumption, effective rates, and month-over-month trend.
                        @endif
                    </p>
                </div>
                <div class="cf-legend">
                    @if($hasEnactedBudget)
                        <span class="legend-item"><span class="dot within"></span> Within Budget</span>
                        <span class="legend-item"><span class="dot warning"></span> Near Limit (&plusmn;10%)</span>
                        <span class="legend-item"><span class="dot danger"></span> Over Budget</span>
                        <span class="legend-item"><span class="dot upcoming"></span> Upcoming</span>
                    @else
                        <span class="legend-item"><span class="dot paid"></span> Paid &amp; Approved</span>
                        <span class="legend-item"><span class="dot upcoming"></span> Pending / Upcoming</span>
                    @endif
                </div>
            </div>

            <div class="cf-table-wrap">
                <table class="cf-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            @if($hasEnactedBudget)
                                <th style="text-align:right;">Budget Allocation</th>
                            @endif
                            <th style="text-align:right;">Actual Bill Outflow</th>
                            <th style="text-align:right;">Consumption</th>
                            <th style="text-align:center;">Unit Rate</th>
                            @if($hasEnactedBudget)
                                <th style="text-align:right;">Net Variance</th>
                            @else
                                <th style="text-align:center;">MoM Change</th>
                            @endif
                            <th style="text-align:right;">Cost Savings</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyBreakdown as $row)
                            <tr class="{{ $row['has_record'] ? 'row-active' : 'row-upcoming' }}">
                                <td>
                                    <strong>{{ $row['month_name'] }} {{ $fiscalYear }}</strong>
                                </td>

                                @if($hasEnactedBudget)
                                    <td style="text-align:right; font-weight:700; color:#475569;">
                                        ₱{{ number_format($row['allocated_budget'], 2) }}
                                    </td>
                                @endif

                                <td style="text-align:right; font-weight:800; color:{{ $row['has_record'] ? '#0f172a' : '#94a3b8' }};">
                                    @if($row['has_record'])
                                        ₱{{ number_format($row['actual_cost'], 2) }}
                                    @else
                                        <span style="color:#94a3b8; font-weight:500;">&mdash;</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if($row['has_record'])
                                        {{ number_format($row['actual_kwh'], 1) }} <small style="color:#64748b;">kWh</small>
                                    @else
                                        <span style="color:#94a3b8;">&mdash;</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($row['has_record'])
                                        <span class="cf-rate-chip">₱{{ number_format($row['avg_rate'], 2) }}/kWh</span>
                                    @else
                                        <span style="color:#94a3b8;">&mdash;</span>
                                    @endif
                                </td>

                                @if($hasEnactedBudget)
                                    <td style="text-align:right; font-weight:800;">
                                        @if($row['has_record'] && $row['variance'] !== null)
                                            <span class="{{ $row['variance'] >= 0 ? 'text-emerald' : 'text-danger' }}">
                                                {{ $row['variance'] >= 0 ? '+' : '' }}₱{{ number_format($row['variance'], 2) }}
                                            </span>
                                        @else
                                            <span style="color:#94a3b8;">&mdash;</span>
                                        @endif
                                    </td>
                                @else
                                    <td style="text-align:center;">
                                        @if($row['has_record'] && $row['mom_change_pct'] !== null)
                                            @if($row['mom_change_pct'] > 0)
                                                <span class="cf-mom-badge mom-up" title="+₱{{ number_format($row['mom_change_amount'], 2) }} vs prev month">
                                                    <i class="fa-solid fa-arrow-trend-up"></i> +{{ $row['mom_change_pct'] }}%
                                                </span>
                                            @elseif($row['mom_change_pct'] < 0)
                                                <span class="cf-mom-badge mom-down" title="₱{{ number_format($row['mom_change_amount'], 2) }} vs prev month">
                                                    <i class="fa-solid fa-arrow-trend-down"></i> {{ $row['mom_change_pct'] }}%
                                                </span>
                                            @else
                                                <span class="cf-mom-badge mom-flat">0.0%</span>
                                            @endif
                                        @else
                                            <span style="color:#94a3b8;">&mdash;</span>
                                        @endif
                                    </td>
                                @endif

                                <td style="text-align:right; font-weight:750; color:#047857;">
                                    @if($row['has_record'] && $row['savings'] > 0)
                                        ₱{{ number_format($row['savings'], 2) }}
                                    @elseif($row['has_record'])
                                        ₱0.00
                                    @else
                                        <span style="color:#94a3b8;">&mdash;</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    <span class="cf-status-pill {{ $row['status_badge'] }}">
                                        @if($row['status_badge'] === 'status-paid')
                                            <i class="fa-solid fa-circle-check"></i>
                                        @elseif($row['status_badge'] === 'status-upcoming')
                                            <i class="fa-regular fa-clock"></i>
                                        @endif
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    @if($selectedFacility)
                                        <a href="{{ url('/modules/facilities/' . $selectedFacility->id . '/monthly-records') }}?year={{ $fiscalYear }}" class="cf-action-link" title="Open in Monthly Records">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @else
                                        <span style="color:#cbd5e1;">&bull;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="cf-table-total-row">
                            <td>TOTAL / YTD</td>
                            @if($hasEnactedBudget)
                                <td style="text-align:right;">₱{{ number_format($annualBudget, 2) }}</td>
                            @endif
                            <td style="text-align:right;">₱{{ number_format($ytdActualOutflow, 2) }}</td>
                            <td style="text-align:right;">{{ number_format($ytdActualKwh, 1) }} kWh</td>
                            <td style="text-align:center;">₱{{ number_format($avgRatePerKwh, 2) }}/kWh</td>
                            @if($hasEnactedBudget)
                                <td style="text-align:right;" class="{{ ($remainingBudget ?? 0) >= 0 ? 'text-emerald' : 'text-danger' }}">
                                    {{ ($remainingBudget ?? 0) >= 0 ? '+' : '' }}₱{{ number_format($remainingBudget ?? 0, 2) }}
                                </td>
                            @else
                                <td style="text-align:center;">&mdash;</td>
                            @endif
                            <td style="text-align:right; color:#047857;">₱{{ number_format($costSavingsYtd, 2) }}</td>
                            <td colspan="2" style="text-align:center;">
                                <strong>{{ $approvedBillsCount }} / 12 Months Billed</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- BUDGET ENCODING / EDIT MODAL -->
@if($canManageBudget && $selectedFacility)
<div id="cfBudgetModal" class="cf-modal-overlay" style="display:none;">
    <div class="cf-modal-card">
        <div class="cf-modal-header">
            <h3><i class="fa-solid fa-wallet" style="color:#2563eb;"></i> Manage Facility Utility Budget</h3>
            <button type="button" class="cf-modal-close" onclick="closeBudgetModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('modules.cashflow.budget.update') }}">
            @csrf
            <input type="hidden" name="facility_id" value="{{ $selectedFacility->id }}">
            <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">

            <div class="cf-modal-body">
                <div class="cf-modal-info">
                    <div class="cf-info-row">
                        <span>Facility:</span>
                        <strong>{{ $selectedFacility->name }}</strong>
                    </div>
                    <div class="cf-info-row">
                        <span>Fiscal Year:</span>
                        <strong>FY {{ $fiscalYear }}</strong>
                    </div>
                    <div class="cf-info-row">
                        <span>Current Status:</span>
                        <strong>{{ $hasEnactedBudget ? 'Enacted Budget (₱' . number_format($annualBudget, 2) . ')' : 'Pure Cash Flow Mode (No Budget)' }}</strong>
                    </div>
                </div>

                <div class="cf-modal-hint-box">
                    <i class="fa-solid fa-circle-info" style="color:#2563eb;"></i>
                    <span><strong>Note:</strong> Setting a budget is completely optional. If no official budget was allocated to your office, leave this empty or set to 0 to keep tracking pure cash outflows and paid bills.</span>
                </div>

                <div class="cf-form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label for="annual_budget_amount">Annual Utility Budget (PHP)</label>
                        <button type="button" class="cf-suggest-btn" onclick="clearBudgetInput()">
                            <i class="fa-solid fa-eraser"></i> Clear / Pure Outflow
                        </button>
                    </div>
                    <div class="cf-input-currency-wrap">
                        <span class="cf-currency-symbol">₱</span>
                        <input type="number" step="0.01" min="0" name="annual_budget_amount" id="annual_budget_amount" 
                               class="cf-modal-input" 
                               value="{{ $hasEnactedBudget ? number_format($annualBudget, 2, '.', '') : '' }}" 
                               placeholder="0.00 (Leave empty for pure cash flow)">
                    </div>
                    <span class="cf-form-hint">Official electricity budget appropriation under the General Appropriations Ordinance.</span>
                </div>

                <div class="cf-form-group">
                    <label for="budget_notes">Appropriation Notes / Ordinance Reference (Optional)</label>
                    <textarea name="notes" id="budget_notes" class="cf-modal-textarea" rows="2" placeholder="e.g. City Ordinance No. SP-2026-04, Utility Operating Fund">{{ $budgetNotes }}</textarea>
                </div>
            </div>

            <div class="cf-modal-footer">
                <button type="button" class="cf-btn-secondary" onclick="closeBudgetModal()">Cancel</button>
                <button type="submit" class="cf-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- CHART.JS & JAVASCRIPT LOGIC -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function switchCashFlowScope() {
    const facId = document.getElementById('cfFacilitySwitcher').value;
    const year = document.getElementById('cfYearSwitcher').value;
    window.location.href = "{{ route('modules.cashflow.index') }}?facility_id=" + encodeURIComponent(facId) + "&year=" + encodeURIComponent(year);
}

function openBudgetModal() {
    const modal = document.getElementById('cfBudgetModal');
    if (modal) modal.style.display = 'flex';
}

function closeBudgetModal() {
    const modal = document.getElementById('cfBudgetModal');
    if (modal) modal.style.display = 'none';
}

function clearBudgetInput() {
    const input = document.getElementById('annual_budget_amount');
    const notes = document.getElementById('budget_notes');
    if (input) input.value = '';
    if (notes) notes.value = '';
    if (input) input.focus();
}

// Render Trend Chart
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('cashFlowTrendChart');
    if (!ctx) return;

    const labels = {!! json_encode($chartLabels) !!};
    const hasEnactedBudget = {!! json_encode($hasEnactedBudget) !!};
    const budgetData = {!! json_encode($chartBudgetData) !!};
    const outflowData = {!! json_encode($chartOutflowData) !!};
    const savingsData = {!! json_encode($chartSavingsData) !!};

    const datasets = [
        {
            label: 'Actual Bill Outflow (PHP)',
            data: outflowData,
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: '#dc2626',
            borderWidth: 1.5,
            borderRadius: 6,
            order: 2
        },
        {
            label: 'Cost Savings (PHP)',
            data: savingsData,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: '#059669',
            borderWidth: 1.5,
            borderRadius: 6,
            order: 3
        }
    ];

    if (hasEnactedBudget) {
        datasets.push({
            label: 'Monthly Budget Limit (PHP)',
            data: budgetData,
            type: 'line',
            borderColor: '#2563eb',
            borderWidth: 2.5,
            borderDash: [5, 5],
            pointBackgroundColor: '#2563eb',
            pointRadius: 3.5,
            fill: false,
            order: 1
        });
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += '₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value);
                        },
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    }
                },
                x: {
                    grid: { display: false },
                    font: { size: 11, weight: '600' }
                }
            }
        }
    });
});
</script>

<style>
.cashflow-page {
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
}

.report-card-container {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(31, 38, 135, 0.06);
    padding: 26px 28px;
    margin-bottom: 2rem;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-sizing: border-box;
    width: 100%;
}

body.dark-mode .cashflow-page .report-card-container {
    background: #111827;
    border: 1px solid #334155;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
}

/* HEADER */
.cf-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}
body.dark-mode .cf-header {
    border-bottom-color: #334155;
}
.cf-title-wrap h2 {
    font-size: 1.55rem;
    font-weight: 850;
    color: #0f172a;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cf-title-wrap p {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}
.cf-badge-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
    flex-wrap: wrap;
}
.cf-scope-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.cf-scope-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    color: #475569;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 750;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    transition: all 0.15s ease;
}
.cf-scope-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-1px);
}
.cf-scope-pill.active-local {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #93c5fd;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.15);
}
.cf-scope-pill.active-cprf {
    background: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
    box-shadow: 0 1px 3px rgba(16, 185, 129, 0.15);
}
.cf-mode-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 750;
}
.cf-mode-chip.outflow-mode {
    background: #fef2f2;
    color: #be123c;
    border: 1px solid #fecdd3;
}
.cf-mode-chip.budget-mode {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

body.dark-mode .cf-header {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
body.dark-mode .cf-title-wrap h2 {
    color: #f8fafc;
}
body.dark-mode .cf-title-wrap p {
    color: #94a3b8;
}
body.dark-mode .cf-scope-pill {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}
body.dark-mode .cf-scope-pill.active-local {
    background: #1e3a8a;
    border-color: #3b82f6;
    color: #bfdbfe;
}
body.dark-mode .cf-scope-pill.active-cprf {
    background: #064e3b;
    border-color: #059669;
    color: #a7f3d0;
}
body.dark-mode .cf-mode-chip.outflow-mode {
    background: #4c0519;
    color: #fecdd3;
    border-color: #9f1239;
}
body.dark-mode .cf-mode-chip.budget-mode {
    background: #1e3a8a;
    color: #bfdbfe;
    border-color: #3b82f6;
}

.cf-controls {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
}
.cf-control-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cf-control-group label {
    font-size: 0.72rem;
    font-weight: 750;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.cf-select {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #0f172a;
    background: #ffffff;
    cursor: pointer;
    min-width: 170px;
}
.cf-actions {
    display: flex;
    gap: 8px;
}
.cf-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #2563eb;
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 750;
    cursor: pointer;
    transition: all .15s ease;
}
.cf-btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
.cf-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    color: #334155;
    border: 1px solid #cbd5e1;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 750;
    cursor: pointer;
    text-decoration: none;
    transition: all .15s ease;
}
.cf-btn-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
body.dark-mode .cf-control-group label {
    color: #94a3b8;
}
body.dark-mode .cf-select {
    background: #0f172a;
    border-color: #334155;
    color: #f8fafc;
}
body.dark-mode .cf-btn-secondary {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .cf-btn-secondary:hover {
    background: #1e293b;
    border-color: #475569;
}

/* KPI CARDS */
.cf-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
@media (max-width: 1024px) {
    .cf-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .cf-kpi-grid { grid-template-columns: 1fr; }
}
.cf-kpi-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
    border-top: 4px solid #cbd5e1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.cf-kpi-card.border-indigo { border-top-color: #6366f1; }
.cf-kpi-card.border-rose { border-top-color: #f43f5e; }
.cf-kpi-card.border-emerald { border-top-color: #10b981; }
.cf-kpi-card.border-cyan { border-top-color: #06b6d4; }
.cf-kpi-card.border-amber { border-top-color: #f59e0b; }

.cf-kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.cf-kpi-title {
    font-size: 0.74rem;
    font-weight: 750;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.cf-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}
.bg-indigo { background: #e0e7ff; color: #4338ca; }
.bg-rose { background: #ffe4e6; color: #e11d48; }
.bg-emerald { background: #d1fae5; color: #047857; }
.bg-cyan { background: #cffafe; color: #0e7490; }
.bg-amber { background: #fef3c7; color: #b45309; }

.cf-kpi-value {
    font-size: 1.6rem;
    font-weight: 850;
    color: #0f172a;
    margin-bottom: 8px;
}
.text-rose { color: #e11d48; }
.text-indigo { color: #4338ca; }
.text-emerald { color: #047857; }
.text-cyan { color: #0e7490; }
.text-amber { color: #b45309; }
.text-danger { color: #dc2626; }

.cf-kpi-meta {
    font-size: 0.73rem;
    color: #64748b;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    border-top: 1px dashed #f1f5f9;
    padding-top: 8px;
}
.cf-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.68rem;
    font-weight: 750;
    padding: 2px 7px;
    border-radius: 6px;
}
.badge-custom { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.badge-estimated { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.badge-neutral { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

/* PROGRESS WRAP */
.cf-progress-wrap {
    margin-top: 4px;
}
.cf-progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.71rem;
    color: #64748b;
    margin-bottom: 4px;
}
.cf-progress-bar-bg {
    width: 100%;
    height: 7px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
}
.cf-progress-bar-fill {
    height: 100%;
    background: #10b981;
    border-radius: 999px;
    transition: width 0.4s ease;
}
.cf-progress-bar-fill.warn { background: #f59e0b; }
.cf-progress-bar-fill.over { background: #dc2626; }

/* PROJECTION BANNER */
.cf-projection-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.cf-projection-banner.info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a8a;
}
.cf-projection-banner.surplus {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}
.cf-projection-banner.deficit {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.cf-proj-icon {
    font-size: 1.5rem;
}
.cf-proj-text h4 {
    margin: 0 0 3px;
    font-size: 0.88rem;
    font-weight: 800;
}
.cf-proj-text p {
    margin: 0;
    font-size: 0.78rem;
}

/* CARDS & TABLES */
.cf-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
}
.cf-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}
.cf-card-header h3 {
    margin: 0 0 3px;
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cf-card-header p {
    margin: 0;
    font-size: 0.76rem;
    color: #64748b;
}
.cf-legend {
    display: flex;
    gap: 12px;
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
}
.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.dot.paid { background: #10b981; }
.dot.within { background: #10b981; }
.dot.warning { background: #f59e0b; }
.dot.danger { background: #dc2626; }
.dot.upcoming { background: #cbd5e1; }

.cf-chart-container {
    height: 280px;
    position: relative;
}

.cf-table-wrap {
    overflow-x: auto;
}
.cf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.78rem;
}
.cf-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.68rem;
    padding: 10px 12px;
    border-bottom: 2px solid #e2e8f0;
    letter-spacing: 0.03em;
}
.cf-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
}
.cf-table tr:hover td {
    background: #f8fafc;
}
.cf-table-total-row td {
    background: #f1f5f9;
    font-weight: 850;
    border-top: 2px solid #cbd5e1;
    border-bottom: none;
    font-size: 0.82rem;
}
.cf-rate-chip {
    display: inline-block;
    background: #f1f5f9;
    color: #334155;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 750;
}
.cf-mom-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 750;
}
.cf-mom-badge.mom-up { background: #fee2e2; color: #dc2626; }
.cf-mom-badge.mom-down { background: #dcfce7; color: #16a34a; }
.cf-mom-badge.mom-flat { background: #f1f5f9; color: #64748b; }

.cf-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 800;
}
.status-paid { background: #dcfce7; color: #15803d; }
.status-within { background: #dcfce7; color: #15803d; }
.status-warning { background: #fef3c7; color: #b45309; }
.status-danger { background: #fee2e2; color: #b91c1c; }
.status-upcoming { background: #f1f5f9; color: #64748b; }
.cf-action-link {
    color: #2563eb;
    text-decoration: none;
    font-size: 0.85rem;
    padding: 4px 6px;
    border-radius: 6px;
    transition: background 0.15s ease;
}
.cf-action-link:hover {
    background: #eff6ff;
}

/* MODAL */
.cf-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 16px;
}
.cf-modal-card {
    background: #ffffff;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    overflow: hidden;
}
.cf-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}
.cf-modal-header h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cf-modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    color: #94a3b8;
    cursor: pointer;
}
.cf-modal-close:hover { color: #0f172a; }
.cf-modal-body {
    padding: 20px;
}
.cf-modal-info {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 14px;
}
.cf-info-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    margin-bottom: 4px;
}
.cf-info-row:last-child { margin-bottom: 0; }
.cf-info-row span { color: #64748b; }
.cf-info-row strong { color: #0f172a; }

.cf-modal-hint-box {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 14px;
    font-size: 0.75rem;
    color: #1e40af;
    line-height: 1.4;
}

.cf-form-group {
    margin-bottom: 14px;
}
.cf-form-group label {
    display: block;
    font-size: 0.76rem;
    font-weight: 750;
    color: #334155;
    margin-bottom: 4px;
}
.cf-suggest-btn {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 750;
    cursor: pointer;
}
.cf-suggest-btn:hover { background: #e2e8f0; color: #0f172a; }

.cf-input-currency-wrap {
    display: flex;
    align-items: center;
    position: relative;
}
.cf-currency-symbol {
    position: absolute;
    left: 12px;
    font-weight: 800;
    color: #64748b;
}
.cf-modal-input {
    width: 100%;
    padding: 9px 12px 9px 28px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 750;
    color: #0f172a;
}
.cf-modal-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.8rem;
    color: #0f172a;
    box-sizing: border-box;
}
.cf-form-hint {
    display: block;
    font-size: 0.68rem;
    color: #64748b;
    margin-top: 4px;
}
.cf-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

/* DARK MODE */
body.dark-mode .cf-title-wrap h2 { color: #f8fafc; }
body.dark-mode .cf-card { background: #1e293b; border-color: #334155; }
body.dark-mode .cf-card-header h3 { color: #f8fafc; }
body.dark-mode .cf-kpi-card { background: #1e293b; }
body.dark-mode .cf-kpi-value { color: #f8fafc; }
body.dark-mode .cf-table th { background: #0f172a; color: #94a3b8; border-color: #334155; }
body.dark-mode .cf-table td { border-color: #334155; color: #e2e8f0; }
body.dark-mode .cf-table-total-row td { background: #0f172a; border-color: #334155; }
body.dark-mode .cf-select { background: #1e293b; color: #f8fafc; border-color: #475569; }
body.dark-mode .cf-modal-card { background: #1e293b; }
body.dark-mode .cf-modal-header { border-color: #334155; }
body.dark-mode .cf-modal-header h3 { color: #f8fafc; }
body.dark-mode .cf-modal-info { background: #0f172a; border-color: #334155; }
body.dark-mode .cf-modal-hint-box { background: #1e3a8a; border-color: #3b82f6; color: #bfdbfe; }
body.dark-mode .cf-info-row strong { color: #f8fafc; }
body.dark-mode .cf-modal-input { background: #0f172a; border-color: #475569; color: #f8fafc; }
body.dark-mode .cf-modal-textarea { background: #0f172a; border-color: #475569; color: #f8fafc; }
body.dark-mode .cf-modal-footer { background: #0f172a; border-color: #334155; }
body.dark-mode .cf-projection-banner.info { background: #1e3a8a; border-color: #3b82f6; color: #bfdbfe; }
body.dark-mode .cf-rate-chip { background: #334155; color: #e2e8f0; }
body.dark-mode .cf-badge-neutral { background: #334155; color: #cbd5e1; border-color: #475569; }
</style>
@endsection
