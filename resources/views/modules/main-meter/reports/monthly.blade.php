@extends('layouts.qc-admin')
@section('title', 'Main Meter Monthly Report')

@section('content')
<div style="padding:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;color:#1e3a8a;font-size:1.45rem;font-weight:800;">Monthly Energy Report (Main Meter)</h2>
            <div style="margin-top:4px;color:#64748b;">Monthly kWh, peak demand, power factor, and approval status.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.main-meter.monitoring', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Monitoring</a>
            <a href="{{ route('modules.main-meter.reports.baseline-comparison', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Baseline Comparison</a>
            <a href="{{ route('modules.main-meter.reports.demand-spikes', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Demand Spikes</a>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <form method="GET" action="{{ route('modules.main-meter.reports.monthly') }}" style="padding:12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:220px;flex:1;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Facility</label>
                <select name="facility_id" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Filter</button>
                <a href="{{ route('modules.main-meter.reports.monthly') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div style="padding:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;border-bottom:1px solid #e2e8f0;">
            <div style="background:#eff6ff;border:1px solid #dbeafe;border-radius:12px;padding:10px;">
                <div style="font-size:.78rem;color:#1e40af;font-weight:800;">TOTAL KWH</div>
                <div style="font-size:1.2rem;font-weight:900;color:#0f172a;">{{ number_format((float) $totalKwh, 2) }}</div>
            </div>
            <div style="background:#ecfeff;border:1px solid #bae6fd;border-radius:12px;padding:10px;">
                <div style="font-size:.78rem;color:#0f766e;font-weight:800;">AVERAGE POWER FACTOR</div>
                <div style="font-size:1.2rem;font-weight:900;color:#0f172a;">{{ $avgPf > 0 ? number_format((float) $avgPf, 3) : '-' }}</div>
            </div>
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:10px;">
                <div style="font-size:.78rem;color:#9a3412;font-weight:800;">MAX PEAK DEMAND (KW)</div>
                <div style="font-size:1.2rem;font-weight:900;color:#0f172a;">{{ number_format((float) $maxPeak, 2) }}</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:1100px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Facility</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Period</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">kWh Used</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Peak Demand (kW)</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Power Factor</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Status</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Alert</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $level = strtolower((string) ($row->alert?->alert_level ?? 'none')); @endphp
                        <tr>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $row->facility?->name ?? '-' }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;color:#334155;">{{ $row->periodLabel() }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format((float) $row->kwh_used, 2) }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;">{{ $row->peak_demand_kw !== null ? number_format((float) $row->peak_demand_kw, 2) : '-' }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">{{ $row->power_factor !== null ? number_format((float) $row->power_factor, 3) : '-' }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                <span style="background:#dcfce7;color:#166534;border-radius:999px;padding:5px 10px;font-size:.75rem;font-weight:800;">RECORDED</span>
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                @if($level === 'critical')
                                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.75rem;font-weight:800;">CRITICAL</span>
                                @elseif($level === 'warning')
                                    <span style="background:#fef3c7;color:#92400e;border-radius:999px;padding:5px 10px;font-size:.75rem;font-weight:800;">WARNING</span>
                                @else
                                    <span style="background:#e2e8f0;color:#334155;border-radius:999px;padding:5px 10px;font-size:.75rem;font-weight:800;">NONE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding:20px;text-align:center;color:#64748b;">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
