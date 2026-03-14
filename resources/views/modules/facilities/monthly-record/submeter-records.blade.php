@extends('layouts.qc-admin')
@section('title', 'Sub-meter Monthly Records')

@section('content')
@php
    $months = $monthLabels ?? [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $mainMeterOptions = collect($mainMeterOptions ?? []);
    $selectedMainMeterId = (int) ($selectedMainMeterId ?? 0);
@endphp

<div style="padding:14px;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('error') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <div>
            <h2 style="margin:0;color:#1e40af;font-weight:800;">Sub-meter Monthly Records</h2>
            <div style="margin-top:4px;color:#64748b;">Facility: <strong style="color:#1e293b;">{{ $facility->name }}</strong></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'record_scope' => 'submeters']) }}"
                style="text-decoration:none;background:#eff6ff;color:#1d4ed8;padding:10px 14px;border-radius:10px;border:1px solid #bfdbfe;font-weight:700;">
                Back to Monthly Records
            </a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;margin-bottom:12px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
            <div style="font-size:.78rem;color:#64748b;font-weight:800;">TOTAL RECORDS</div>
            <div style="font-size:1.35rem;color:#1e293b;font-weight:800;">{{ $totalRecords }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
            <div style="font-size:.78rem;color:#64748b;font-weight:800;">TOTAL KWH</div>
            <div style="font-size:1.35rem;color:#1e293b;font-weight:800;">{{ number_format($totalKwh, 2) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
            <div style="font-size:.78rem;color:#64748b;font-weight:800;">TOTAL COST</div>
            <div style="font-size:1.35rem;color:#166534;font-weight:800;">PHP {{ number_format($totalCost, 2) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
            <div style="font-size:.78rem;color:#64748b;font-weight:800;">YEAR</div>
            <div style="font-size:1.35rem;color:#1e293b;font-weight:800;">{{ $selectedYear }}</div>
        </div>
    </div>

    @if((int) $selectedMeterId === 0 && collect($submeterGroups)->isNotEmpty())
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
            @foreach($submeterGroups as $group)
                <a href="#submeter-group-{{ (int) ($group['meter_id'] ?? 0) }}"
                   style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;font-size:.8rem;font-weight:700;">
                    {{ $group['meter_name'] ?? 'Unknown Sub-meter' }}
                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:2px 7px;font-size:.72rem;font-weight:800;">{{ (int) ($group['record_count'] ?? 0) }}</span>
                    <span style="color:#64748b;font-weight:700;">{{ number_format((float) ($group['total_kwh'] ?? 0), 2) }} kWh</span>
                </a>
            @endforeach
        </div>
    @endif

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
        <form method="GET" action="{{ route('facilities.monthly-records.submeters', $facility->id) }}" style="padding:12px;border-bottom:1px solid #e2e8f0;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:#f8fafc;">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:150px;">
                <label style="font-size:.8rem;color:#475569;font-weight:700;">Year</label>
                <select name="year" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    @foreach($yearOptions as $year)
                        <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:150px;">
                <label style="font-size:.8rem;color:#475569;font-weight:700;">Month</label>
                <select name="month" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="0" @selected($selectedMonth === 0)>All Months</option>
                    @foreach($months as $monthNumber => $monthLabel)
                        <option value="{{ $monthNumber }}" @selected($selectedMonth === (int) $monthNumber)>{{ $monthLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:230px;">
                <label style="font-size:.8rem;color:#475569;font-weight:700;">Main Meter</label>
                <select name="main_meter_id" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="0" @selected($selectedMainMeterId === 0)>All Main Meters</option>
                    @foreach($mainMeterOptions as $mainMeter)
                        <option value="{{ $mainMeter->id }}" @selected($selectedMainMeterId === (int) $mainMeter->id)>{{ $mainMeter->meter_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:250px;flex:1;">
                <label style="font-size:.8rem;color:#475569;font-weight:700;">Sub-meter</label>
                <select name="meter_id" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="0" @selected((int) $selectedMeterId === 0)>All Sub-meters</option>
                    @foreach($subMeterOptions as $meter)
                        <option value="{{ $meter->id }}" @selected((int) $selectedMeterId === (int) $meter->id)>{{ $meter->meter_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Apply</button>
                <a href="{{ route('facilities.monthly-records.submeters', ['facility' => $facility->id]) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:980px;border-collapse:collapse;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Year</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Month</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Day</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Sub-meter</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Actual kWh</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Baseline kWh</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Deviation %</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Alert</th>
                        <th style="padding:11px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submeterGroups as $group)
                        @if((int) $selectedMeterId === 0)
                            <tr id="submeter-group-{{ (int) ($group['meter_id'] ?? 0) }}" style="background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">
                                <td colspan="9" style="padding:10px 12px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <span style="font-weight:800;color:#1e293b;">{{ $group['meter_name'] ?? 'Unknown Sub-meter' }}</span>
                                        <span style="font-size:.82rem;color:#475569;font-weight:700;">
                                            {{ (int) ($group['record_count'] ?? 0) }} record(s) | {{ number_format((float) ($group['total_kwh'] ?? 0), 2) }} kWh | PHP {{ number_format((float) ($group['total_cost'] ?? 0), 2) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @foreach(($group['records'] ?? []) as $record)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 12px;text-align:center;">{{ (int) ($record['year'] ?? 0) }}</td>
                                <td style="padding:10px 12px;text-align:center;">{{ $months[(int) ($record['month'] ?? 0)] ?? ($record['month'] ?? '-') }}</td>
                                <td style="padding:10px 12px;text-align:center;">{{ $record['day'] ?? '-' }}</td>
                                <td style="padding:10px 12px;text-align:center;font-weight:700;color:#1e293b;">{{ $record['meter_name'] ?? '-' }}</td>
                                <td style="padding:10px 12px;text-align:center;font-weight:700;color:#0f172a;">{{ isset($record['actual_kwh']) ? number_format((float) $record['actual_kwh'], 2) : '-' }}</td>
                                <td style="padding:10px 12px;text-align:center;color:#334155;">{{ isset($record['baseline_kwh']) ? number_format((float) $record['baseline_kwh'], 2) : '-' }}</td>
                                <td style="padding:10px 12px;text-align:center;color:#334155;">{{ isset($record['deviation']) ? number_format((float) $record['deviation'], 2) . '%' : '-' }}</td>
                                <td style="padding:10px 12px;text-align:center;">
                                    <span style="display:inline-flex;padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:800;background:{{ $record['alert_bg'] ?? '#f1f5f9' }};color:{{ $record['alert_color'] ?? '#475569' }};">
                                        {{ $record['alert_label'] ?? '-' }}
                                    </span>
                                </td>
                                <td style="padding:10px 12px;text-align:center;color:#166534;font-weight:700;">PHP {{ number_format((float) ($record['cost'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" style="padding:20px 12px;text-align:center;color:#64748b;">No sub-meter monthly records found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
