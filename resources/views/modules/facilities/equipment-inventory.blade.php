@extends('layouts.qc-admin')
@section('title', 'Facility Equipment Inventory')

@section('content')
@php
    $totals = $totals ?? ['items' => 0, 'total_watts' => 0, 'estimated_kwh' => 0];
    $selectedMeterScope = $selectedMeterScope ?? 'all';
@endphp

<div style="padding:14px;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;color:#1e3a8a;font-size:1.4rem;font-weight:800;">
                <i class="fa fa-cubes" style="margin-right:7px;"></i>
                Facility Equipment Inventory
            </h2>
            <div style="margin-top:4px;color:#64748b;">
                Showing equipment for <strong>{{ $facility->name }}</strong> only.
            </div>
        </div>
        <a href="{{ route('modules.facilities.index') }}" style="text-decoration:none;background:#f8fafc;color:#334155;padding:10px 14px;border-radius:10px;border:1px solid #cbd5e1;font-weight:700;">Back to Facilities</a>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:12px;">
        <form method="GET" action="{{ route('modules.facilities.equipment-inventory', $facility->id) }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:end;">
            <div>
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Meter Scope</label>
                <select name="meter_scope" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="all" @selected($selectedMeterScope === 'all')>All</option>
                    <option value="sub" @selected($selectedMeterScope === 'sub')>Sub Meter</option>
                    <option value="main" @selected($selectedMeterScope === 'main')>Main Meter</option>
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Sub Meter</label>
                <select name="submeter_id" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Sub Meters</option>
                    @foreach($submeters as $submeter)
                        <option value="{{ $submeter->id }}" @selected((string) $selectedSubmeter === (string) $submeter->id)>{{ $submeter->submeter_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Main Meter</label>
                <select name="main_meter_id" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Main Meters</option>
                    @foreach($mainMeters as $mainMeter)
                        <option value="{{ $mainMeter->id }}" @selected((string) $selectedMainMeter === (string) $mainMeter->id)>{{ $mainMeter->meter_name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Apply</button>
                <a href="{{ route('modules.facilities.equipment-inventory', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>
    </div>

    <div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
        <div style="background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#1e40af;font-weight:800;">TOTAL ITEMS</div><div style="font-size:1.45rem;font-weight:900;">{{ number_format((int) $totals['items']) }}</div></div>
        <div style="background:#fff;border:1px solid #bae6fd;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#0f766e;font-weight:800;">TOTAL WATTS</div><div style="font-size:1.45rem;font-weight:900;">{{ number_format((float) $totals['total_watts'], 2) }}</div></div>
        <div style="background:#fff;border:1px solid #c7d2fe;border-radius:12px;padding:12px;"><div style="font-size:.78rem;color:#4338ca;font-weight:800;">TOTAL ESTIMATED KWH</div><div style="font-size:1.45rem;font-weight:900;color:#1d4ed8;">{{ number_format((float) $totals['estimated_kwh'], 2) }}</div></div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow-x:auto;">
        <table style="width:100%;min-width:1200px;border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:10px 12px;text-align:left;">Equipment</th>
                    <th style="padding:10px 12px;text-align:left;">Type</th>
                    <th style="padding:10px 12px;text-align:left;">Meter</th>
                    <th style="padding:10px 12px;text-align:center;">Qty</th>
                    <th style="padding:10px 12px;text-align:right;">Unit Watts</th>
                    <th style="padding:10px 12px;text-align:right;">Total Watts</th>
                    <th style="padding:10px 12px;text-align:center;">Hours/Day</th>
                    <th style="padding:10px 12px;text-align:center;">Days/Month</th>
                    <th style="padding:10px 12px;text-align:right;">Estimated kWh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipmentRows as $equipment)
                    @php
                        $scope = strtolower((string) ($equipment->meter_scope ?? 'sub'));
                        $meterName = $scope === 'main'
                            ? (string) ($equipment->mainMeter?->meter_name ?? 'Main Meter')
                            : (string) ($equipment->submeter?->submeter_name ?? 'Sub Meter');
                        $quantity = (int) ($equipment->quantity ?? 0);
                        $ratedWatts = (float) ($equipment->rated_watts ?? 0);
                        $totalWatts = $quantity * $ratedWatts;
                    @endphp
                    <tr>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;font-weight:700;color:#0f172a;">{{ $equipment->equipment_name }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;">{{ $scope === 'main' ? 'Main Meter' : 'Sub Meter' }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;">{{ $meterName }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">{{ number_format($quantity) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;">{{ number_format($ratedWatts, 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format($totalWatts, 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">{{ number_format((float) ($equipment->operating_hours_per_day ?? 0), 2) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:center;">{{ number_format((int) ($equipment->operating_days_per_month ?? 0)) }}</td>
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;text-align:right;color:#1d4ed8;font-weight:800;">{{ number_format((float) ($equipment->estimated_kwh ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:16px;text-align:center;color:#64748b;">
                            No equipment found for selected filters in this facility.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($equipmentRows, 'hasPages') && $equipmentRows->hasPages())
        <div style="margin-top:12px;">
            {{ $equipmentRows->links() }}
        </div>
    @endif

    <div style="margin-top:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
        Equipment inventory is now read-only.
    </div>
</div>
@endsection
