@extends('layouts.qc-admin')
@section('title', 'Sub-meter Equipment')

@php
    $backUrl = $mainMeter
        ? route('modules.facilities.meters.main-submeters', [$facility->id, $mainMeter->id])
        : route('modules.facilities.energy-profile.index', $facility->id);
@endphp

@section('content')
<div style="width:100%;margin:0 auto;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
            Please check the equipment form fields and try again.
        </div>
    @endif

    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.08);padding:18px 20px;margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;color:#1d4ed8;font-size:1.55rem;font-weight:800;">
                    <i class="fa fa-plug" style="margin-right:8px;"></i>Sub-meter Equipment
                </h2>
                <div style="margin-top:5px;color:#475569;font-weight:600;">
                    {{ $facility->name }} | Sub-meter: <span style="color:#0f172a;font-weight:800;">{{ $subMeter->meter_name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Items: {{ (int) $equipmentCount }}</span>
                    <span style="background:#ecfeff;color:#0f766e;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Total Watts: {{ number_format((float) $totalWatts, 2) }}</span>
                    <span style="background:#eef2ff;color:#4338ca;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Est. kWh: {{ number_format((float) $totalEstimatedKwh, 2) }}</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                @if($submeterEntity)
                    <a href="{{ route('modules.load-tracking.index', ['month' => now()->format('Y-m'), 'facility_id' => $facility->id, 'meter_scope' => 'sub', 'submeter_id' => (int) $submeterEntity->id]) }}"
                       style="text-decoration:none;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:10px;padding:9px 12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fa fa-chart-line"></i> Open in Load Tracking
                    </a>
                @endif
                <a href="{{ $backUrl }}"
                   style="text-decoration:none;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;border-radius:10px;padding:9px 12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.08);padding:16px 18px;margin-bottom:14px;">
        <div style="font-weight:800;color:#1e293b;margin-bottom:10px;">Sub-meter Details</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Meter No.</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ $subMeter->meter_number ?: 'N/A' }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Parent Main Meter</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ $mainMeter?->meter_name ?? 'N/A' }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Location</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ $subMeter->location ?: 'N/A' }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Baseline</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">
                    {{ is_numeric($subMeter->baseline_kwh) ? number_format((float) $subMeter->baseline_kwh, 2) . ' kWh' : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    @if(! $submeterEntity)
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
            No linked submeters record found for this meter name yet. Equipment list is unavailable until it is mapped.
        </div>
    @endif

    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.08);padding:16px 18px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
            <div style="font-weight:800;color:#1e293b;">Equipment List</div>
            @if($canManageEquipment && $submeterEntity)
                <button type="button"
                        onclick="openAddEquipmentModal()"
                        style="border:none;background:#2563eb;color:#fff;border-radius:10px;padding:9px 12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa fa-plus"></i> Add Equipment
                </button>
            @endif
        </div>
        @if($equipmentRows->isEmpty())
            <div style="border:1px dashed #cbd5e1;border-radius:12px;padding:14px;color:#64748b;font-weight:700;">
                No equipment found for this sub-meter.
            </div>
        @else
            <div style="overflow:auto;">
                <table style="width:100%;min-width:900px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#eef2ff;">
                            <th style="padding:10px;text-align:left;color:#1e293b;border-bottom:1px solid #dbeafe;">Equipment</th>
                            <th style="padding:10px;text-align:center;color:#1e293b;border-bottom:1px solid #dbeafe;">Qty</th>
                            <th style="padding:10px;text-align:right;color:#1e293b;border-bottom:1px solid #dbeafe;">Unit Watts</th>
                            <th style="padding:10px;text-align:right;color:#1e293b;border-bottom:1px solid #dbeafe;">Total Watts</th>
                            <th style="padding:10px;text-align:center;color:#1e293b;border-bottom:1px solid #dbeafe;">Hours/Day</th>
                            <th style="padding:10px;text-align:center;color:#1e293b;border-bottom:1px solid #dbeafe;">Days/Month</th>
                            <th style="padding:10px;text-align:right;color:#1e293b;border-bottom:1px solid #dbeafe;">Est. kWh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipmentRows as $equipment)
                            @php
                                $qty = (int) ($equipment->quantity ?? 0);
                                $ratedWatts = (float) ($equipment->rated_watts ?? 0);
                            @endphp
                            <tr>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;color:#0f172a;font-weight:700;">{{ $equipment->equipment_name }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:center;color:#334155;">{{ number_format($qty) }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#334155;">{{ number_format($ratedWatts, 2) }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#334155;font-weight:700;">{{ number_format($qty * $ratedWatts, 2) }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:center;color:#334155;">{{ number_format((float) ($equipment->operating_hours_per_day ?? 0), 2) }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:center;color:#334155;">{{ number_format((int) ($equipment->operating_days_per_month ?? 0)) }}</td>
                                <td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#1d4ed8;font-weight:800;">{{ number_format((float) ($equipment->estimated_kwh ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($canManageEquipment && $submeterEntity)
<div id="addEquipmentModal"
     style="display:none;position:fixed;inset:0;z-index:10070;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeAddEquipmentModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Add Equipment</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Target Sub-meter: <span style="color:#0f172a;font-weight:800;">{{ $subMeter->meter_name ?? 'N/A' }}</span>
        </div>

        <form method="POST"
              action="{{ route('modules.facilities.meters.submeter-equipment.store', [$facility->id, $subMeter->id]) }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf

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

<script>
function openAddEquipmentModal() {
    const modal = document.getElementById('addEquipmentModal');
    if (!modal) return;
    modal.style.display = 'flex';
}

function closeAddEquipmentModal() {
    const modal = document.getElementById('addEquipmentModal');
    if (!modal) return;
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('addEquipmentModal');
    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeAddEquipmentModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAddEquipmentModal();
        }
    });
});
</script>

@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    openAddEquipmentModal();
});
</script>
@endif
@endif
@endsection
