@extends('layouts.qc-admin')
@section('title', 'Energy Report')

@php
    $user = auth()->user();
    $roleKey = $user?->role_key ?? str_replace(' ', '_', strtolower((string) ($user?->role ?? '')));
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);

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
    $increasingCount = $rows->where('trend', 'up')->count();
    $decreasingCount = $rows->where('trend', 'down')->count();
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
    overflow-x: auto;
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
    padding: 12px 14px;
    text-align: left;
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
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.9rem;
}

.energy-table tr:hover {
    background: #f8fbff;
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

.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.trend-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    border: 1px solid transparent;
    padding: 4px 10px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
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
body.dark-mode .energy-filters,
body.dark-mode .energy-table-wrap { background: #111827; border-color: #1f2937; }
body.dark-mode .filter-group label { color: #cbd5e1; }
body.dark-mode .filter-group select,
body.dark-mode .filter-group input {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .btn-reset {
    background: #1f2937;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .energy-table thead { background: #0f172a; }
body.dark-mode .energy-table th { color: #94a3b8; border-bottom-color: #1f2937; }
body.dark-mode .energy-table td { color: #e2e8f0; border-bottom-color: #1f2937; }
body.dark-mode .energy-table tr:hover { background: #1f2937; }
body.dark-mode .facility-cell { color: #f8fafc; }
body.dark-mode .empty-row { color: #94a3b8; }

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
                @if($roleKey !== 'staff')
                <a href="{{ route('reports.energy-export', request()->all()) }}" class="btn-action btn-excel">
                    <i class="fa fa-download"></i> Export Excel
                </a>
                @endif
                <a href="{{ route('modules.energy.export-pdf', array_filter(request()->all())) }}" class="btn-action btn-pdf">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </a>
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
                <select name="month" id="month">
                    <option value="">All Months</option>
                    @php $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec']; @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
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
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($energyRows ?? [] as $row)
                        @php
                            $trend = $row['trend'] ?? 'stable';
                            $trendClass = $trend === 'up' ? 'trend-up' : ($trend === 'down' ? 'trend-down' : 'trend-stable');
                            $trendLabel = $trend === 'up' ? 'Increasing' : ($trend === 'down' ? 'Decreasing' : 'Stable');
                            $trendIcon = $trend === 'up' ? 'fa-arrow-up' : ($trend === 'down' ? 'fa-arrow-down' : 'fa-minus');
                        @endphp
                        <tr class="energy-row" data-search="{{ strtolower((string)($row['facility'] ?? '')) }}">
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
                            <td>
                                <span class="trend-pill {{ $trendClass }}">
                                    <i class="fa {{ $trendIcon }}"></i> {{ $trendLabel }}
                                </span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('tableSearch');
    const rows = Array.from(document.querySelectorAll('.energy-row'));
    const noMatch = document.getElementById('energyNoMatch');

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
});
</script>
@endsection
