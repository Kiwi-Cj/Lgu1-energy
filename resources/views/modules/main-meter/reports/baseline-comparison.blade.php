@extends('layouts.qc-admin')
@section('title', 'Main Meter Baseline Comparison Report')

@section('content')
<div style="padding:14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;color:#1e3a8a;font-size:1.45rem;font-weight:800;">Baseline Comparison Report</h2>
            <div style="margin-top:4px;color:#64748b;">Actual monthly kWh versus computed baseline with deviation percentage.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.main-meter.monitoring', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Monitoring</a>
            <a href="{{ route('modules.main-meter.reports.monthly', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Monthly Report</a>
            <a href="{{ route('modules.main-meter.reports.demand-spikes', ['month' => $selectedMonth, 'facility_id' => $selectedFacility]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;padding:10px 14px;border-radius:10px;font-weight:700;">Demand Spikes</a>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
        <form method="GET" action="{{ route('modules.main-meter.reports.baseline-comparison') }}" style="padding:12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
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
                <a href="{{ route('modules.main-meter.reports.baseline-comparison') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:950px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Facility</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Period</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Actual kWh</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Baseline kWh</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Deviation</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $deviation = $row['deviation_percent'];
                            $status = 'NO BASELINE';
                            $statusColor = '#334155';
                            $statusBg = '#e2e8f0';
                            if ($deviation !== null) {
                                if ($deviation > 20) {
                                    $status = 'CRITICAL';
                                    $statusColor = '#991b1b';
                                    $statusBg = '#fee2e2';
                                } elseif ($deviation > 10) {
                                    $status = 'WARNING';
                                    $statusColor = '#92400e';
                                    $statusBg = '#fef3c7';
                                } else {
                                    $status = 'NORMAL';
                                    $statusColor = '#166534';
                                    $statusBg = '#dcfce7';
                                }
                            }
                        @endphp
                        <tr>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $row['facility_name'] }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;color:#334155;">{{ $row['period_label'] }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format((float) $row['actual_kwh'], 2) }}</td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#1d4ed8;font-weight:700;">
                                {{ $row['baseline_kwh'] !== null ? number_format((float) $row['baseline_kwh'], 2) : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:800;color:{{ ($deviation ?? 0) > 0 ? '#be123c' : '#166534' }};">
                                {{ $deviation !== null ? number_format((float) $deviation, 2) . '%' : '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                <span style="background:{{ $statusBg }};color:{{ $statusColor }};border-radius:999px;padding:5px 10px;font-size:.75rem;font-weight:800;">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:20px;text-align:center;color:#64748b;">No comparison data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
