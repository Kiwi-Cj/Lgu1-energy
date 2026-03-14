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
    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 16px;border-radius:12px;font-weight:700;">Please check the equipment form fields and try again.</div>
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
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if($canManageInventory ?? false)
                <button type="button" onclick="openAddEquipmentModal()" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;">
                    <i class="fa fa-plus" style="margin-right:5px;"></i>Add Equipment
                </button>
            @endif
            <a href="{{ route('modules.load-tracking.index', ['facility_id' => (int) $facility->id]) }}" style="text-decoration:none;background:#eff6ff;color:#1d4ed8;padding:10px 14px;border-radius:10px;border:1px solid #bfdbfe;font-weight:700;">Open Load Tracking</a>
            <a href="{{ route('modules.facilities.index') }}" style="text-decoration:none;background:#f8fafc;color:#334155;padding:10px 14px;border-radius:10px;border:1px solid #cbd5e1;font-weight:700;">Back to Facilities</a>
        </div>
    </div>

    <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:12px;">
        <form method="GET" action="{{ route('modules.facilities.equipment-inventory', $facility->id) }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:end;">
            <div>
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Meter Scope</label>
                <select id="filter_meter_scope" name="meter_scope" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="all" @selected($selectedMeterScope === 'all')>All</option>
                    <option value="sub" @selected($selectedMeterScope === 'sub')>Sub Meter</option>
                    <option value="main" @selected($selectedMeterScope === 'main')>Main Meter</option>
                </select>
            </div>
            <div id="filter_sub_group">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Sub Meter</label>
                <select name="submeter_id" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Sub Meters</option>
                    @foreach($submeters as $submeter)
                        <option value="{{ $submeter->id }}" @selected((string) $selectedSubmeter === (string) $submeter->id)>{{ $submeter->submeter_name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="filter_main_group">
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
                        <td style="padding:10px 12px;border-top:1px solid #f1f5f9;">
                            <span style="display:inline-flex;align-items:center;border-radius:999px;padding:3px 9px;font-size:.74rem;font-weight:800;background:{{ $scope === 'main' ? '#eef2ff' : '#ecfeff' }};color:{{ $scope === 'main' ? '#4338ca' : '#0f766e' }};border:1px solid {{ $scope === 'main' ? '#c7d2fe' : '#a5f3fc' }};">
                                {{ $scope === 'main' ? 'Main Meter' : 'Sub Meter' }}
                            </span>
                        </td>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var filterScope = document.getElementById('filter_meter_scope');
    var filterSub = document.getElementById('filter_sub_group');
    var filterMain = document.getElementById('filter_main_group');
    var addScope = document.getElementById('add_meter_scope');
    var addSub = document.getElementById('add_submeter_group');
    var addMain = document.getElementById('add_main_meter_group');

    function toggleGroups(scopeEl, subEl, mainEl, mode) {
        if (!scopeEl) return;
        var scope = String(scopeEl.value || mode);
        if (subEl) subEl.style.display = scope === 'main' ? 'none' : 'block';
        if (mainEl) mainEl.style.display = scope === 'sub' ? 'none' : 'block';
    }

    filterScope?.addEventListener('change', function () {
        toggleGroups(filterScope, filterSub, filterMain, 'all');
    });
    addScope?.addEventListener('change', function () {
        toggleGroups(addScope, addSub, addMain, 'sub');
    });

    toggleGroups(filterScope, filterSub, filterMain, 'all');
    toggleGroups(addScope, addSub, addMain, 'sub');
});

function openAddEquipmentModal() {
    var modal = document.getElementById('addEquipmentModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddEquipmentModal() {
    var modal = document.getElementById('addEquipmentModal');
    if (modal) modal.style.display = 'none';
}
</script>

@if($canManageInventory ?? false)
<div id="addEquipmentModal"
     style="display:none;position:fixed;inset:0;z-index:10070;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeAddEquipmentModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Add Equipment</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Facility: <span style="color:#0f172a;font-weight:800;">{{ $facility->name }}</span>
        </div>

        <form method="POST"
              action="{{ route('modules.load-tracking.equipment.store') }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf
            <input type="hidden" name="return_to" value="facility_inventory">
            <input type="hidden" name="facility_context_id" value="{{ (int) $facility->id }}">

            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Scope <span style="color:#e11d48;">*</span></label>
                <select id="add_meter_scope" name="meter_scope" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="sub" @selected(old('meter_scope', 'sub') === 'sub')>Sub Meter</option>
                    <option value="main" @selected(old('meter_scope') === 'main')>Main Meter</option>
                </select>
            </div>

            <div id="add_submeter_group" style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Sub Meter <span style="color:#e11d48;">*</span></label>
                <select name="submeter_id" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="">Select Sub Meter</option>
                    @foreach($submeters as $submeter)
                        <option value="{{ $submeter->id }}" @selected((string) old('submeter_id') === (string) $submeter->id)>{{ $submeter->submeter_name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="add_main_meter_group" style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Main Meter <span style="color:#e11d48;">*</span></label>
                <select name="main_meter_id" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="">Select Main Meter</option>
                    @foreach($mainMeters as $mainMeter)
                        <option value="{{ $mainMeter->id }}" @selected((string) old('main_meter_id') === (string) $mainMeter->id)>{{ $mainMeter->meter_name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Equipment Name <span style="color:#e11d48;">*</span></label>
                <input type="text" name="equipment_name" required maxlength="120" value="{{ old('equipment_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. Aircon Unit">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Qty <span style="color:#e11d48;">*</span></label>
                <input type="number" name="quantity" required min="1" max="100000" step="1" value="{{ old('quantity', 1) }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="1">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Unit Watts <span style="color:#e11d48;">*</span></label>
                <input type="number" name="rated_watts" required min="0.01" max="99999999.99" step="0.01" value="{{ old('rated_watts') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 1200">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Hours/Day <span style="color:#e11d48;">*</span></label>
                <input type="number" name="operating_hours_per_day" required min="0.01" max="24" step="0.01" value="{{ old('operating_hours_per_day') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 8">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Days/Month <span style="color:#e11d48;">*</span></label>
                <input type="number" name="operating_days_per_month" required min="1" max="31" step="1" value="{{ old('operating_days_per_month') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 26">
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeAddEquipmentModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Save Equipment</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    openAddEquipmentModal();
});
</script>
@endif
@endif
@endsection
