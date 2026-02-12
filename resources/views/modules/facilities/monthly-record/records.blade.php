@extends('layouts.qc-admin')
@section('title', 'Monthly Records')
@section('content')
<style>
    .report-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(31,38,135,0.08);
        margin-bottom: 1.2rem;
        padding: 24px;
    }
</style>
@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@php
    $sortedRecords = $records->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
    // Use baseline_kwh from record if set, else fallback to energy profile/facility
    $energyProfile = \App\Models\EnergyProfile::where('facility_id', $facility->id)->latest()->first();
    $hasBaseline = false;
    if ($sortedRecords->count() > 0 && $sortedRecords->first()->baseline_kwh !== null) {
        $baselineAvg = floatval($sortedRecords->first()->baseline_kwh);
        $hasBaseline = $baselineAvg > 0;
    } elseif ($energyProfile && is_numeric($energyProfile->baseline_kwh) && $energyProfile->baseline_kwh > 0) {
        $baselineAvg = floatval($energyProfile->baseline_kwh);
        $hasBaseline = true;
    } else {
        $baselineAvg = $facility->baseline_kwh;
        $hasBaseline = $baselineAvg > 0;
    }
@endphp
@php
    $currentYear = date('Y');
    $showAll = request('show_all') === '1';
    $selectedYear = request('year') ?? ($showAll ? $currentYear - 1 : $currentYear);
    $years = $records->pluck('year')->unique()->sortDesc()->values();
    $filteredRecords = $records->where('year', $selectedYear);
    $sortedRecords = $filteredRecords->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
@endphp


<div class="report-card">
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif
<!-- ...existing content... -->
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<div>


    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div>
            <span style="font-size:2rem; color:#2563eb; font-weight:700; letter-spacing:0.5px;">Facility: </span>
            <span style="font-size:2rem; color:#222; font-weight:600;">{{ $facility->name }}</span>
        </div>
        <button onclick="openAddModal()" class="btn btn-primary" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 22px; font-size:1.05rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.18s; @if(!$hasBaseline) opacity:0.5; pointer-events:none; @endif" @if(!$hasBaseline) disabled title="You need at least 3 months of data before adding monthly records." @endif>+ Monthly Energy Records</button>
    </div>

    <div style="margin-bottom:10px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <h2 style="font-size:1.15rem; font-weight:700; color:#222; margin:0;">Monthly Energy Records</h2>
        @if($showAll)
        <form method="get" style="display: flex; align-items: center; gap: 8px;">
            <input type="hidden" name="show_all" value="1">
            <label for="year" style="font-weight:600; margin-right:4px;">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" style="padding:6px 12px; border-radius:7px; border:1px solid #c3cbe5; font-size:1rem;">
                @foreach($years as $year)
                    @if($year != $currentYear)
                        <option value="{{ $year }}" @if($year == $selectedYear) selected @endif>{{ $year }}</option>
                    @endif
                @endforeach
            </select>
        </form>
        @endif
    </div>



    <!-- Baseline kWh Card -->

    @if(!$hasBaseline)
        <div style="margin-bottom:1.2rem;color:#e11d48;font-weight:500;">You need to set a baseline kWh in the energy profile before you can add a monthly energy record.</div>
    @endif
    @php
        $sizeLabel = '';
        if ($hasBaseline) {
            if ($baselineAvg <= 1000) {
                $sizeLabel = 'Small';
            } elseif ($baselineAvg <= 3000) {
                $sizeLabel = 'Medium';
            } elseif ($baselineAvg <= 10000) {
                $sizeLabel = 'Large';
            } else {
                $sizeLabel = 'Extra Large';
            }
        }
    @endphp

    @if($hasBaseline && $energyProfile && !$energyProfile->engineer_approved)
        <div style="margin-bottom:18px; display:flex; align-items:center; font-size:1.08rem;font-weight:600;color:#6366f1;">
            <span>
                Facility Size: <span style="font-weight:700;">{{ $sizeLabel }}</span>
            </span>
            <span style="margin-left:auto; font-size:1.08rem; color:#222; font-weight:600;">
                Baseline kWh: <span style="font-weight:700; color:#2563eb;">Pending approval</span>
            </span>
        </div>
    @elseif($hasBaseline)
        <div style="margin-bottom:18px; display:flex; align-items:center; font-size:1.08rem;font-weight:600;color:#6366f1;">
            <span>
                Facility Size: <span style="font-weight:700;">{{ $sizeLabel }}</span>
            </span>
            <span style="margin-left:auto; font-size:1.08rem; color:#222; font-weight:600;">
                Baseline kWh ({{ $energyProfile && $energyProfile->baseline_source ? $energyProfile->baseline_source : 'Energy Profile' }}): <span style="font-weight:700; color:#2563eb;">{{ number_format($baselineAvg, 2) }} kWh</span>
            </span>
        </div>
    @endif

    <!-- Monthly Records Table -->
    <div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead style="background:#f1f5f9;">
                <tr style="text-align:center;">
                    <th style="padding:10px 14px; text-align:center;">Year</th>
                    <th style="padding:10px 14px; text-align:center;">Month</th>
                    <th style="padding:10px 14px; text-align:center;">Day</th>
                    <th style="padding:10px 14px; text-align:center;">Actual kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Baseline kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Deviation (%)</th>
                    <th style="padding:10px 14px; text-align:center;">Alert</th>
                    <th style="padding:10px 14px; text-align:center;">Energy Cost</th>
                    <th style="padding:10px 14px; text-align:center;">Bill Image</th>
                    <th style="padding:10px 14px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($sortedRecords as $record)
                <tr style="border-bottom:1px solid #e5e7eb; text-align:center;">
                    <td style="padding:10px 14px; text-align:center;">{{ $record->year }}</td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        @endphp
                        {{ $months[$record->month - 1] ?? $record->month }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">{{ $record->day ?? '-' }}</td>
                    <td style="padding:10px 14px; text-align:center;">{{ number_format($record->actual_kwh, 2) }}</td>
                    <td style="padding:10px 14px; text-align:center;">{{ number_format($baselineAvg, 2) }}</td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            $actual = $record->actual_kwh;
                            $baseline = $baselineAvg;
                            $deviation = $baseline > 0 ? round((($actual - $baseline) / $baseline) * 100, 2) : null;
                        @endphp
                        {{ $deviation !== null ? $deviation . '%' : '' }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            // LevelAlert Table
                            // Level 1: Normal / Low (Green)
                            // Level 2: Warning / Moderate (Yellow)
                            // Level 3: High (Orange)
                            // Level 4: Critical / Excessive (Red)
                            // Level 5: Extreme / Danger (Dark Red / Maroon)
                            $alert = '';
                            $alertColor = '#22c55e'; // default green
                            $alertLabel = 'Normal';
                            if ($deviation === null) {
                                $alert = '';
                                $alertColor = '#64748b';
                                $alertLabel = '';
                            } else {
                                // Fetch thresholds from settings for each size
                                $thresholds = [];
                                if ($sizeLabel === 'Small') {
                                    $thresholds = [
                                        'level5' => (float) \App\Models\Setting::getValue('alert_level5_small', 80),
                                        'level4' => (float) \App\Models\Setting::getValue('alert_level4_small', 50),
                                        'level3' => (float) \App\Models\Setting::getValue('alert_level3_small', 30),
                                        'level2' => (float) \App\Models\Setting::getValue('alert_level2_small', 15),
                                    ];
                                } elseif ($sizeLabel === 'Medium') {
                                    $thresholds = [
                                        'level5' => (float) \App\Models\Setting::getValue('alert_level5_medium', 60),
                                        'level4' => (float) \App\Models\Setting::getValue('alert_level4_medium', 40),
                                        'level3' => (float) \App\Models\Setting::getValue('alert_level3_medium', 20),
                                        'level2' => (float) \App\Models\Setting::getValue('alert_level2_medium', 10),
                                    ];
                                } elseif ($sizeLabel === 'Large') {
                                    $thresholds = [
                                        'level5' => (float) \App\Models\Setting::getValue('alert_level5_large', 30),
                                        'level4' => (float) \App\Models\Setting::getValue('alert_level4_large', 20),
                                        'level3' => (float) \App\Models\Setting::getValue('alert_level3_large', 12),
                                        'level2' => (float) \App\Models\Setting::getValue('alert_level2_large', 5),
                                    ];
                                } elseif ($sizeLabel === 'Extra Large') {
                                    $thresholds = [
                                        'level5' => (float) \App\Models\Setting::getValue('alert_level5_xlarge', 20),
                                        'level4' => (float) \App\Models\Setting::getValue('alert_level4_xlarge', 12),
                                        'level3' => (float) \App\Models\Setting::getValue('alert_level3_xlarge', 7),
                                        'level2' => (float) \App\Models\Setting::getValue('alert_level2_xlarge', 3),
                                    ];
                                } else {
                                    $thresholds = [
                                        'level5' => 60,
                                        'level4' => 40,
                                        'level3' => 20,
                                        'level2' => 10,
                                    ];
                                }
                                if ($deviation > $thresholds['level5']) {
                                    $alert = 'Level 5'; $alertColor = '#7c1d1d'; $alertLabel = 'Extreme / level 5';
                                } elseif ($deviation > $thresholds['level4']) {
                                    $alert = 'Level 4'; $alertColor = '#e11d48'; $alertLabel = 'Critical / level 4';
                                } elseif ($deviation > $thresholds['level3']) {
                                    $alert = 'Level 3'; $alertColor = '#f59e42'; $alertLabel = 'High / level 3';
                                } elseif ($deviation > $thresholds['level2']) {
                                    $alert = 'Level 2'; $alertColor = '#fde047'; $alertLabel = 'Warning / level 2';
                                } else {
                                    $alert = 'Level 1'; $alertColor = '#22c55e'; $alertLabel = 'Normal / Low';
                                }
                            }
                        @endphp
                        @if($alert)
                            <span style="color:{{ $alertColor }};font-weight:700;">{{ $alertLabel }}</span>
                        @else
                            <span style="color:#64748b;">&nbsp;</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @php
                            // Use DB value if present, else compute using default rate
                            $rate = isset($record->rate_per_kwh) && $record->rate_per_kwh ? $record->rate_per_kwh : 12.00; // default 12.00
                            $computedCost = $record->actual_kwh * $rate;
                        @endphp
                        ₱{{ number_format($computedCost, 2) }}
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        @if($record->bill_image)
                            <a href="{{ asset('storage/' . $record->bill_image) }}" target="_blank" style="display:inline-block;">
                                <img src="{{ asset('storage/' . $record->bill_image) }}" alt="Bill Image" style="max-width:60px;max-height:60px;border-radius:7px;box-shadow:0 2px 8px #2563eb22;object-fit:cover;">
                            </a>
                        @else
                            &nbsp;
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                        @if($alert === 'High')
                            <button type="button" title="Create Energy Action (High)" style="background: none; border: none; color: #e11d48; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal({{ $record->id }}, 'High')">
                                <span style="font-size:1.3rem;">⚠️</span>
                            </button>
                        @elseif($alert === 'Medium')
                            <button type="button" title="Create Energy Action (Medium)" style="background: none; border: none; color: #f59e42; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal({{ $record->id }}, 'Medium')">
                                <span style="font-size:1.3rem;">⚡</span>
                            </button>
                         @elseif($alert === 'Low')
                            <button type="button" title="View Recommendation (Low)" style="background: none; border: none; color: #22c55e; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal({{ $record->id }}, 'Low')">
                                <span style="font-size:1.3rem;">💡</span>
                            </button>
                        @endif
                       
                        <form id="deleteMonthlyRecordForm-{{ $record->id }}" action="{{ route('energy-records.delete', ['facility' => $facility->id, 'record' => $record->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" title="Delete"
                                style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;"
                                onclick="openDeleteMonthlyRecordModal({{ $record->id }}, '{{ $months[$record->month-1] }}', {{ $record->year }})"
                                data-id="{{ $record->id }}"
                            >
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                <!-- Energy Action Modal -->
                <div id="energyActionModal" style="display:none;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
                    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
                        <div style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                            <button type="button" onclick="closeEnergyActionModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                            <h2 id="energyModalTitle" style="margin-bottom:12px;font-size:1.3rem;font-weight:700;color:#2563eb;"></h2>
                            <ul id="energyRecommendations" style="margin:0 0 10px 18px;padding:0;font-size:1.08rem;color:#222;"></ul>
                            <div style="text-align:right;margin-top:18px;">
                                <button type="button" onclick="closeEnergyActionModal()" style="background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-weight:600;font-size:1rem;">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                function openEnergyActionModal(recordId, level) {
                    const modal = document.getElementById('energyActionModal');
                    const title = document.getElementById('energyModalTitle');
                    const list  = document.getElementById('energyRecommendations');
                    list.innerHTML = '';
                    if (level === 'High') {
                        title.innerHTML = '⚠ High Energy Consumption';
                        list.innerHTML = `
                            <li>Immediately inspect major energy-consuming equipment</li>
                            <li>Limit non-essential electrical usage</li>
                            <li>Notify facility manager</li>
                            <li>Schedule urgent maintenance</li>
                        `;
                    } else if (level === 'Medium') {
                        title.innerHTML = '⚡ Medium Energy Alert';
                        list.innerHTML = `
                            <li>Review monthly energy usage trends</li>
                            <li>Check operating hours of equipment</li>
                            <li>Apply basic energy-saving measures</li>
                        `;
                    } else if (level === 'Low') {
                        title.innerHTML = '💡 Low Deviation - Good Practice';
                        list.innerHTML = `
                            <li>Maintain current energy-saving practices</li>
                            <li>Continue monitoring for unusual changes</li>
                            <li>Encourage staff to sustain efficiency</li>
                        `;
                    }
                    modal.style.display = 'flex';
                    setTimeout(() => { modal.classList.add('show'); }, 10);
                }

                function closeEnergyActionModal() {
                    const modal = document.getElementById('energyActionModal');
                    modal.classList.remove('show');
                    setTimeout(() => { modal.style.display = 'none'; }, 200);
                }
                </script>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="padding:18px 0;text-align:center;color:#b91c1c;">No records found for this facility.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:18px; display: flex; align-items: center; justify-content: flex-end;">
        @if(!$showAll)
            <a href="?show_all=1" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:10px;padding:10px 18px;font-size:1.01rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Past Records</a>
        @else
            <a href="?" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:10px;padding:10px 18px;font-size:1.01rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Current Year Only</a>
        @endif
    </div>
</div>


<!-- ADD MONTHLY RECORD MODAL (Consistent UI) -->

<!-- ADD MONTHLY RECORD MODAL (Centered Overlay) -->
<div id="addModal" style="display:none;position:fixed;z-index:10050;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:420px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
            <button type="button" onclick="closeAddModal()" style="position:absolute;top:12px;right:12px;font-size:1.5rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Add Monthly Record</h2>
            <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Enter new monthly record details below.</div>
            @if($errors->has('duplicate'))
                <div id="duplicateModal" style="display:flex;justify-content:center;align-items:center;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
                    <div class="modal-content" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                        <button type="button" onclick="closeDuplicateModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                        <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Existing Data</h3>
                        <div style="margin-bottom:18px;font-size:1.05rem;color:#222;">{{ $errors->first('duplicate') }}</div>
                        <div style="display:flex;gap:10px;">
                            <button type="button" onclick="closeDuplicateModal()" style="background:#2563eb;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">OK</button>
                        </div>
                    </div>
                </div>
                <script>
                function closeDuplicateModal() {
                    document.getElementById('duplicateModal').style.display = 'none';
                }
                window.onload = function() {
                    if(document.getElementById('duplicateModal')) {
                        document.getElementById('addModal').style.display = 'block';
                    }
                };
                </script>
            @endif
            <form id="addMonthlyRecordForm" method="POST" action="{{ route('energy-records.store', ['facility' => $facility->id]) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_date" style="font-weight:500;">Date</label>
                    <input type="date" id="add_date" name="date" value="{{ date('Y-m-d') }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_actual_kwh" style="font-weight:500;">Actual kWh</label>
                        <input type="number" step="0.01" id="add_actual_kwh" name="actual_kwh" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_rate_per_kwh" style="font-weight:500;">Rate per kWh</label>
                        <input type="number" step="0.01" id="add_rate_per_kwh" name="rate_per_kwh" value="12.00" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_baseline_kwh" style="font-weight:500;">Baseline kWh</label>
                    <input type="number" step="0.01" id="add_baseline_kwh" name="baseline_kwh" value="{{ isset($baselineAvg) ? number_format($baselineAvg, 2, '.', '') : '' }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_energy_cost" style="font-weight:500;">Energy Cost</label>
                    <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" required readonly style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;background:#f3f4f6;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_bill_image" style="font-weight:500;">Bill Image</label>
                    <input type="file" id="add_bill_image" name="bill_image" accept="image/*" style="border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                {{-- ❌ WALANG AVERAGE INPUT DITO --}}
                {{-- Monthly = RAW DATA LANG --}}
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="background:#2563eb;color:#fff;padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;flex:1;">Save</button>
                    <button type="button" onclick="closeAddModal()" style="background:#f3f4f6;color:#222;padding:12px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    setTimeout(computeEnergyCost, 100); // compute on open
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function computeEnergyCost() {
    const kwh = parseFloat(document.getElementById('add_actual_kwh').value) || 0;
    const rate = parseFloat(document.getElementById('add_rate_per_kwh').value) || 0;
    const cost = kwh * rate;
    document.getElementById('add_energy_cost').value = cost ? cost.toFixed(2) : '';
}
document.getElementById('add_actual_kwh').addEventListener('input', computeEnergyCost);
document.getElementById('add_rate_per_kwh').addEventListener('input', computeEnergyCost);
</script>

<!-- DELETE MONTHLY RECORD MODAL -->
<div id="deleteMonthlyRecordModal" style="display:none;position:fixed;z-index:10050;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:380px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
            <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Delete Monthly Record</h3>
            <div id="deleteMonthlyRecordText" style="margin-bottom:18px;font-size:1.05rem;color:#222;"></div>
            <div style="display:flex;gap:10px;">
                <button id="confirmDeleteMonthlyRecordBtn" type="button" style="background:#e11d48;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">Delete</button>
                <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="background:#f3f4f6;color:#222;padding:10px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteMonthlyRecordId = null;
function openDeleteMonthlyRecordModal(recordId, monthName, year) {
    deleteMonthlyRecordId = recordId;
    document.getElementById('deleteMonthlyRecordText').innerText = `Are you sure you want to delete the record for ${monthName} ${year}?`;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'flex';
}
function closeDeleteMonthlyRecordModal() {
    deleteMonthlyRecordId = null;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'none';
}
document.getElementById('confirmDeleteMonthlyRecordBtn').onclick = function() {
    if (deleteMonthlyRecordId) {
        document.getElementById('deleteMonthlyRecordForm-' + deleteMonthlyRecordId).submit();
    }
};
</script>

@endsection