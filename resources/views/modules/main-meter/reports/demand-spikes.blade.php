@extends('layouts.qc-admin')
@section('title', 'Main Meter Demand Spike Report')

@section('content')
<div style="padding:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;color:#1e3a8a;font-size:1.45rem;font-weight:800;">Demand Spike Report</h2>
            <div style="margin-top:4px;color:#64748b;">Peak demand spikes above 15% of baseline peak demand.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.main-meter.monitoring', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Monitoring</a>
            <a href="{{ route('modules.main-meter.reports.monthly', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Monthly Report</a>
            <a href="{{ route('modules.main-meter.reports.baseline-comparison', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Baseline Comparison</a>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <form method="GET" action="{{ route('modules.main-meter.reports.demand-spikes') }}" style="padding:12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
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
                <a href="{{ route('modules.main-meter.reports.demand-spikes') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:1000px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Facility</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Period</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Peak Demand (kW)</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Baseline Peak (kW)</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Spike %</th>
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Alert Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr style="background:#fff7ed;">
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $row['facility_name'] }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;color:#334155;">{{ $row['period_label'] }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;color:#0f172a;">{{ number_format((float) $row['peak_demand_kw'], 2) }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#1d4ed8;font-weight:700;">
                                {{ $row['baseline_peak_kw'] !== null ? number_format((float) $row['baseline_peak_kw'], 2) : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:800;color:#be123c;">
                                {{ $row['spike_percent'] !== null ? number_format((float) $row['spike_percent'], 2) . '%' : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $row['alert_reason'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:20px;text-align:center;color:#64748b;">No demand spikes found for selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
