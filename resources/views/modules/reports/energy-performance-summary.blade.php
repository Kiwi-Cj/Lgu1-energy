@extends('layouts.qc-admin')
@section('title', 'Energy Performance Summary')

@section('content')
<style>
    .performance-shell{display:grid;gap:18px;padding:25px;border:1px solid #dbe5f1;border-radius:22px;background:radial-gradient(circle at 90% 0,rgba(37,99,235,.08),transparent 28%),linear-gradient(145deg,#f8fbff,#f1f5f9);box-shadow:0 14px 36px rgba(15,23,42,.08)}
    .performance-head{display:flex;align-items:stretch;justify-content:space-between;gap:20px}.performance-head-main{display:flex;gap:14px;align-items:flex-start}.performance-title-icon{flex:0 0 48px;height:48px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(145deg,#2563eb,#0ea5e9);color:#fff;font-size:1.2rem;box-shadow:0 8px 18px rgba(37,99,235,.22)}.performance-head h1{margin:0;color:#0f172a;font-size:1.75rem}.performance-head p{margin:6px 0 0;color:#64748b;line-height:1.5}
    .performance-kpis{display:grid;grid-template-columns:repeat(5,minmax(130px,1fr));gap:11px}.performance-kpi{position:relative;overflow:hidden;padding:15px 15px 15px 58px;border:1px solid #dbe5f1;border-radius:16px;background:#fff}.performance-kpi-icon{position:absolute;left:14px;top:15px;width:34px;height:34px;display:grid!important;place-items:center;border-radius:11px;background:#eff6ff;color:#2563eb;font-size:.9rem!important}.performance-kpi.action .performance-kpi-icon{background:#fff1f2;color:#e11d48}.performance-kpi.warning .performance-kpi-icon{background:#fff7ed;color:#ea580c}.performance-kpi.ready .performance-kpi-icon{background:#ecfdf5;color:#059669}.performance-kpi .label{display:block;color:#64748b;font-size:.68rem;font-weight:900;text-transform:uppercase}.performance-kpi strong{display:block;margin-top:5px;color:#0f172a;font-size:1.5rem}.performance-kpi small{display:block;margin-top:2px;color:#94a3b8;font-size:.63rem}
    .performance-filters{display:grid;grid-template-columns:minmax(210px,1fr) minmax(170px,.65fr) minmax(190px,.75fr) auto auto;gap:10px;align-items:end;padding:14px;border:1px solid #dbe5f1;border-radius:15px;background:#fff}.performance-filters label{display:grid;gap:6px;color:#334155;font-size:.7rem;font-weight:900;text-transform:uppercase}.performance-filters select,.performance-filters input{min-height:42px;padding:8px 11px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a}.performance-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:8px 15px;border:0;border-radius:10px;background:linear-gradient(90deg,#2563eb,#3b82f6);color:#fff;font-weight:900;text-decoration:none;box-shadow:0 5px 12px rgba(37,99,235,.17)}.performance-btn.reset{border:1px solid #cbd5e1;background:#fff;color:#334155;box-shadow:none}
    .performance-legend{display:flex;align-items:center;gap:14px;flex-wrap:wrap;color:#64748b;font-size:.7rem}.performance-legend strong{color:#334155}.legend-item{display:inline-flex;align-items:center;gap:5px}.legend-dot{width:8px;height:8px;border-radius:50%;background:#10b981}.legend-dot.action{background:#e11d48}.legend-dot.data{background:#f97316}
    .performance-table-wrap{overflow:auto;border:1px solid #dbe5f1;border-radius:16px;background:#fff}.performance-table{width:100%;min-width:1180px;border-collapse:collapse}.performance-table th,.performance-table td{padding:14px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top}.performance-table th{position:sticky;top:0;background:#f8fafc;color:#475569;font-size:.66rem;text-transform:uppercase;z-index:1}.performance-table td{color:#334155;font-size:.78rem}.performance-table strong{color:#0f172a}.performance-table tbody tr:hover{background:#f8fbff}.facility-link{color:#0f172a;text-decoration:none;font-weight:900}.facility-link:hover{color:#2563eb}.sub{display:block;margin-top:4px;color:#64748b;font-size:.68rem;line-height:1.4}.source-badge{display:inline-flex;margin-top:6px;padding:3px 7px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:.61rem;font-weight:900}.pill,.alert-pill{display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:999px;font-size:.66rem;font-weight:900}.pill.action{background:#fff1f2;color:#be123c}.pill.monitoring{background:#ecfdf5;color:#047857}.pill.data{background:#fff7ed;color:#c2410c}.alert-pill{margin-top:5px;background:#f1f5f9;color:#475569}.alert-critical,.alert-very-high,.alert-high,.alert-drop-critical,.alert-drop-high{background:#fff1f2;color:#be123c}.alert-warning,.alert-drop-warning{background:#fff7ed;color:#b45309}.alert-normal{background:#ecfdf5;color:#047857}.data-checks{display:grid;gap:5px}.data-check{display:inline-flex;align-items:center;gap:5px;color:#64748b;font-size:.66rem}.data-check i{color:#10b981}.data-check.missing i{color:#f97316}.row-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}.row-action{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border:1px solid #dbeafe;border-radius:8px;color:#2563eb;text-decoration:none;font-size:.63rem;font-weight:900}.row-action:hover{background:#eff6ff}.empty{padding:34px!important;text-align:center;color:#64748b!important}
    :is(html.dark-mode, body.dark-mode) .performance-shell{background:#0f172a;border-color:#334155}
    :is(html.dark-mode, body.dark-mode) .performance-head h1,:is(html.dark-mode, body.dark-mode) .performance-kpi strong,:is(html.dark-mode, body.dark-mode) .performance-table strong,:is(html.dark-mode, body.dark-mode) .facility-link{color:#f8fafc}
    :is(html.dark-mode, body.dark-mode) .performance-head p,:is(html.dark-mode, body.dark-mode) .sub,:is(html.dark-mode, body.dark-mode) .performance-kpi .label,:is(html.dark-mode, body.dark-mode) .performance-kpi small{color:#94a3b8}
    :is(html.dark-mode, body.dark-mode) .performance-kpi,:is(html.dark-mode, body.dark-mode) .performance-filters,:is(html.dark-mode, body.dark-mode) .performance-table-wrap{background:#111827;border-color:#334155}
    :is(html.dark-mode, body.dark-mode) .performance-kpi-icon{background:#1e3a8a!important;color:#93c5fd!important;border:1px solid #3b82f6}
    :is(html.dark-mode, body.dark-mode) .performance-kpi.ready .performance-kpi-icon{background:#052e16!important;color:#86efac!important;border:1px solid #166534}
    :is(html.dark-mode, body.dark-mode) .performance-kpi.action .performance-kpi-icon{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c}
    :is(html.dark-mode, body.dark-mode) .performance-kpi.warning .performance-kpi-icon{background:#451a03!important;color:#fdba74!important;border:1px solid #9a3412}
    :is(html.dark-mode, body.dark-mode) .performance-table th{background:linear-gradient(180deg,#1e293b 0%,#111827 100%)!important;color:#cbd5e1!important;border-color:#334155!important}
    :is(html.dark-mode, body.dark-mode) .performance-table td{color:#cbd5e1;border-color:#1f2937}
    :is(html.dark-mode, body.dark-mode) .performance-table tbody tr:hover{background:#1e293b}
    :is(html.dark-mode, body.dark-mode) .performance-filters label,:is(html.dark-mode, body.dark-mode) .performance-legend strong{color:#cbd5e1}
    :is(html.dark-mode, body.dark-mode) .performance-legend{color:#94a3b8}
    :is(html.dark-mode, body.dark-mode) #performanceVisibleCount{color:#cbd5e1}
    :is(html.dark-mode, body.dark-mode) .performance-filters select,:is(html.dark-mode, body.dark-mode) .performance-filters input{background:#0f172a;border-color:#475569;color:#e2e8f0}
    :is(html.dark-mode, body.dark-mode) .performance-btn.reset{background:#1e293b!important;border-color:#334155!important;color:#cbd5e1!important}
    :is(html.dark-mode, body.dark-mode) .performance-btn.reset:hover{background:#334155!important;color:#f8fafc!important}
    :is(html.dark-mode, body.dark-mode) .source-badge{background:#1e3a8a!important;color:#bfdbfe!important;border:1px solid #3b82f6}
    :is(html.dark-mode, body.dark-mode) .pill.action{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c}
    :is(html.dark-mode, body.dark-mode) .pill.monitoring{background:#052e16!important;color:#86efac!important;border:1px solid #166534}
    :is(html.dark-mode, body.dark-mode) .pill.data{background:#451a03!important;color:#fdba74!important;border:1px solid #9a3412}
    :is(html.dark-mode, body.dark-mode) .alert-pill{background:#1e293b!important;color:#cbd5e1!important;border:1px solid #475569}
    :is(html.dark-mode, body.dark-mode) .alert-critical,:is(html.dark-mode, body.dark-mode) .alert-very-high,:is(html.dark-mode, body.dark-mode) .alert-high,:is(html.dark-mode, body.dark-mode) .alert-drop-critical,:is(html.dark-mode, body.dark-mode) .alert-drop-high{background:#4c0519!important;color:#fda4af!important;border:1px solid #be123c!important}
    :is(html.dark-mode, body.dark-mode) .alert-warning,:is(html.dark-mode, body.dark-mode) .alert-drop-warning{background:#451a03!important;color:#fdba74!important;border:1px solid #9a3412!important}
    :is(html.dark-mode, body.dark-mode) .alert-normal{background:#052e16!important;color:#86efac!important;border:1px solid #166534!important}
    :is(html.dark-mode, body.dark-mode) .data-check{color:#94a3b8}
    :is(html.dark-mode, body.dark-mode) .data-check.missing{color:#fca5a5}
    :is(html.dark-mode, body.dark-mode) .row-action{background:#1e293b;border-color:#3b82f6;color:#93c5fd}
    :is(html.dark-mode, body.dark-mode) .row-action:hover{background:#2563eb;color:#fff;border-color:#3b82f6}
    @media(max-width:1100px){.performance-kpis{grid-template-columns:repeat(3,1fr)}.performance-filters{grid-template-columns:1fr 1fr 1fr}}@media(max-width:800px){.performance-head{display:grid}.performance-kpis{grid-template-columns:1fr 1fr}.performance-filters{grid-template-columns:1fr 1fr}}@media(max-width:560px){.performance-shell{padding:16px}.performance-head-main{display:grid}.performance-kpis,.performance-filters{grid-template-columns:1fr}}
</style>

<section class="performance-shell">
    <header class="performance-head">
        <div class="performance-head-main">
            <span class="performance-title-icon"><i class="fa-solid fa-chart-line"></i></span>
            <div>
                <h1>Energy Performance Summary</h1>
                <p>Facility-wide view of approved consumption, baselines, deviations, and recommended actions.</p>
            </div>
        </div>
    </header>

    <div class="performance-kpis">
        <div class="performance-kpi"><span class="performance-kpi-icon"><i class="fa-solid fa-building"></i></span><span class="label">Facilities</span><strong>{{ $summary['facilities_count'] ?? 0 }}</strong></div>
        <div class="performance-kpi ready"><span class="performance-kpi-icon"><i class="fa-solid fa-circle-check"></i></span><span class="label">Assessment Ready</span><strong>{{ $summary['assessment_ready_count'] ?? 0 }}</strong><small>Reading + baseline</small></div>
        <div class="performance-kpi action"><span class="performance-kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></span><span class="label">Requires Action</span><strong>{{ $summary['requires_action_count'] ?? 0 }}</strong></div>
        <div class="performance-kpi warning"><span class="performance-kpi-icon"><i class="fa-solid fa-gauge"></i></span><span class="label">Missing Readings</span><strong>{{ $summary['missing_reading_count'] ?? 0 }}</strong></div>
        <div class="performance-kpi warning"><span class="performance-kpi-icon"><i class="fa-solid fa-sliders"></i></span><span class="label">Missing Baseline</span><strong>{{ $summary['missing_baseline_count'] ?? 0 }}</strong></div>
    </div>

    <form method="GET" class="performance-filters">
        <label>Facility
            <select name="facility_id">
                <option value="">All Facilities</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="all" @selected($selectedStatus === 'all')>All Statuses</option>
                <option value="requires_action" @selected($selectedStatus === 'requires_action')>Requires Action</option>
                <option value="monitoring" @selected($selectedStatus === 'monitoring')>Monitoring</option>
                <option value="data_required" @selected($selectedStatus === 'data_required')>Data Required</option>
            </select>
        </label>
        <label>Quick Search
            <input type="search" id="performanceSearch" placeholder="Search facility..." autocomplete="off">
        </label>
        <button class="performance-btn" type="submit"><i class="fa-solid fa-filter"></i>&nbsp; Apply</button>
        <a class="performance-btn reset" href="{{ route('reports.performance-summary') }}">Reset</a>
    </form>

    <div class="performance-legend">
        <strong>Priority order:</strong>
        <span class="legend-item"><span class="legend-dot action"></span> Requires Action</span>
        <span class="legend-item"><span class="legend-dot data"></span> Data Required</span>
        <span class="legend-item"><span class="legend-dot"></span> Monitoring</span>
        <span id="performanceVisibleCount">{{ count($performanceRows) }} facility result(s)</span>
    </div>

    <div class="performance-table-wrap">
        <table class="performance-table">
            <thead><tr><th>Facility</th><th>Latest Approved Use</th><th>Baseline</th><th>Deviation / Alert</th><th>Data Coverage</th><th>Status</th><th>Assessment & Recommendation</th></tr></thead>
            <tbody>
            @forelse($performanceRows as $row)
                @php $alertClass = 'alert-'.strtolower(str_replace(' ', '-', $row['alert'])); @endphp
                <tr class="performance-row" data-search="{{ strtolower($row['facility'].' '.$row['source'].' '.($row['reading_source'] ?? '').' '.$row['status_label'].' '.$row['alert']) }}">
                    <td>
                        <a class="facility-link" href="{{ route('modules.facilities.show', ['id' => $row['facility_id']]) }}">{{ $row['facility'] }}</a>
                        <span class="sub">
                            {{ $row['source'] }} facility
                            @if($row['reading_source'])
                                &bull; Reading: {{ $row['reading_source'] }}
                            @endif
                        </span>
                        @if($row['reading_source'])
                            <span class="source-badge"><i class="fa-solid fa-link"></i>&nbsp; {{ $row['reading_source'] }} synced</span>
                        @endif
                    </td>
                    <td><strong>{{ is_numeric($row['actual_kwh']) ? number_format((float) $row['actual_kwh'], 2).' kWh' : '-' }}</strong><span class="sub">{{ $row['latest_period'] }}</span></td>
                    <td><strong>{{ is_numeric($row['baseline_kwh']) ? number_format((float) $row['baseline_kwh'], 2).' kWh' : '-' }}</strong></td>
                    <td><strong>{{ is_numeric($row['deviation']) ? number_format((float) $row['deviation'], 2).'%' : '-' }}</strong><span class="alert-pill {{ $alertClass }}">{{ $row['alert'] }}</span></td>
                    <td>
                        <strong>{{ $row['months_count'] }} month(s)</strong>
                        <div class="data-checks">
                            <span class="data-check {{ $row['actual_kwh'] === null ? 'missing' : '' }}"><i class="fa-solid {{ $row['actual_kwh'] === null ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i> Approved reading</span>
                            <span class="data-check {{ $row['baseline_kwh'] === null ? 'missing' : '' }}"><i class="fa-solid {{ $row['baseline_kwh'] === null ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i> Valid baseline</span>
                        </div>
                    </td>
                    <td><span class="pill {{ $row['status'] === 'requires_action' ? 'action' : ($row['status'] === 'monitoring' ? 'monitoring' : 'data') }}">{{ $row['status_label'] }}</span></td>
                    <td>
                        {{ $row['assessment'] }}
                        <span class="sub"><strong>Recommended:</strong> {{ $row['recommendation'] }}</span>
                        <div class="row-actions">
                            <a class="row-action" href="{{ route('facilities.monthly-records', ['facility' => $row['facility_id']]) }}"><i class="fa-solid fa-file-invoice"></i> Readings</a>
                            @if($row['baseline_kwh'] === null)
                                <a class="row-action" href="{{ route('modules.facilities.energy-profile.index', ['facility' => $row['facility_id']]) }}"><i class="fa-solid fa-sliders"></i> Set Baseline</a>
                            @else
                                <a class="row-action" href="{{ route('modules.energy-monitoring.index', ['facility_id' => $row['facility_id']]) }}"><i class="fa-solid fa-chart-line"></i> Monitor</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">No facilities match the selected filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('performanceSearch');
    const rows = Array.from(document.querySelectorAll('.performance-row'));
    const count = document.getElementById('performanceVisibleCount');
    const applySearch = () => {
        const query = (search?.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            const show = query === '' || (row.dataset.search || '').includes(query);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (count) count.textContent = `${visible} facility result(s)`;
    };
    search?.addEventListener('input', applySearch);
});
</script>
@endsection
