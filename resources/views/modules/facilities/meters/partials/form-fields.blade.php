@php
    $prefix = ($mode ?? 'add') === 'edit' ? 'edit' : 'add';
    $submetersEnabled = (bool) config('features.submeters_enabled', false);
    $forcedMeterType = in_array(($forceMeterType ?? ''), ['main', 'sub'], true) ? (string) $forceMeterType : null;
    if (! $submetersEnabled) {
        $forcedMeterType = 'main';
    }
    $isMeterTypeForced = $forcedMeterType !== null;
    $allowSubMeter = (bool) ($hasApprovedMainForSub ?? false);
    $disableSubOption = ! $isMeterTypeForced && $prefix === 'add' && ! $allowSubMeter;
    $defaultMeterType = $prefix === 'add' ? 'main' : 'sub';
    $selectedMeterType = $forcedMeterType ?? ($disableSubOption ? 'main' : old('meter_type', $defaultMeterType));
@endphp

@if($showFormSections ?? false)
    <div class="meter-form-section-title"><i class="fa-solid fa-gauge-high"></i> Meter Identity</div>
@endif
<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_name">Meter Name <span class="meter-required">*</span></label>
    <input class="meter-form-control" id="{{ $prefix }}_meter_name" type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}" placeholder="e.g. Main Meter, 2nd Floor Panel">
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_number">Meter Number</label>
    <input class="meter-form-control" id="{{ $prefix }}_meter_number" type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}" placeholder="Utility / meter serial no.">
</div>

@if($showFormSections ?? false)
    <div class="meter-form-section-title"><i class="fa-solid fa-sliders"></i> Meter Configuration</div>
@endif
<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_type">Meter Type <span class="meter-required">*</span></label>
    @if($isMeterTypeForced)
        <input type="hidden" id="{{ $prefix }}_meter_type" name="meter_type" value="{{ $selectedMeterType }}">
        <input class="meter-form-control" type="text" value="{{ $selectedMeterType === 'main' ? 'Main Meter' : 'Sub-meter' }}" readonly>
    @else
        <select class="meter-form-control" id="{{ $prefix }}_meter_type" name="meter_type" required>
            <option value="main" @selected($selectedMeterType === 'main')>Main Meter</option>
            <option value="sub" @selected($selectedMeterType === 'sub') @disabled($disableSubOption)>
                Sub-meter{{ $disableSubOption ? ' (Need approved main first)' : '' }}
            </option>
        </select>
    @endif
    @if(! $isMeterTypeForced && $disableSubOption)
        <div class="meter-form-hint meter-form-hint-warning">Add and approve at least one Main Meter first to enable Sub-meter.</div>
    @endif
</div>

<div class="meter-form-field" id="{{ $prefix }}_parent_meter_field" style="{{ $selectedMeterType === 'sub' ? '' : 'display:none;' }}">
    <label class="meter-form-label" for="{{ $prefix }}_parent_meter_id">Linked Main Meter</label>
    <select class="meter-form-control" id="{{ $prefix }}_parent_meter_id" name="parent_meter_id" {{ $selectedMeterType === 'sub' ? 'required' : '' }}>
        <option value="">{{ $selectedMeterType === 'sub' ? 'Select Main Meter' : 'None' }}</option>
        @foreach(($parentMeterOptions ?? collect()) as $parentMeter)
            <option value="{{ $parentMeter->id }}">
                {{ $parentMeter->meter_name }} ({{ strtoupper((string) $parentMeter->meter_type) }})
            </option>
        @endforeach
    </select>
    <div class="meter-form-hint">Required for Sub-meter. Choose the approved Main Meter where this sub-meter belongs.</div>
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_location">Location</label>
    <input class="meter-form-control" id="{{ $prefix }}_location" type="text" name="location" maxlength="255" value="{{ old('location') }}" placeholder="e.g. 2nd Floor Electrical Room">
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_status">Status <span class="meter-required">*</span></label>
    <select class="meter-form-control" id="{{ $prefix }}_status" name="status" required>
        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
    </select>
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_multiplier">Multiplier <span class="meter-required">*</span></label>
    <input class="meter-form-control" id="{{ $prefix }}_multiplier" type="number" step="0.0001" min="0.0001" max="999999" name="multiplier" value="{{ old('multiplier', '1') }}" required placeholder="1.0000">
    <div class="meter-form-hint">Use 1.0 unless your meter uses CT/PT multiplier.</div>
</div>

@php
    $historyReadings = $historicalMonthlyReadings ?? collect();
    if ($historyReadings->isEmpty() && isset($facilityModel) && $facilityModel->energyRecords) {
        $historyReadings = $facilityModel->energyRecords
            ->where('review_status', 'approved')
            ->where('actual_kwh', '>', 0)
            ->sortByDesc('year')
            ->sortByDesc('month')
            ->take(6)
            ->sortBy(fn($r) => sprintf('%04d-%02d', $r->year, $r->month))
            ->values();
    }

    $facilityEquipments = $facilityEquipments ?? (isset($facilityModel)
        ? \App\Models\SubmeterEquipment::query()
            ->where(function ($q) use ($facilityModel) {
                $q->where('facility_id', $facilityModel->id)
                    ->orWhereHas('mainMeter', fn ($m) => $m->where('facility_id', $facilityModel->id))
                    ->orWhereHas('submeter', fn ($s) => $s->where('facility_id', $facilityModel->id));
            })
            ->orderByDesc('rated_watts')
            ->get()
        : collect());

    $equipTotalMonthlyKwh = round((float) $facilityEquipments->sum(fn ($eq) => $eq->monthly_kwh), 2);
    $equipTotalWatts = (float) $facilityEquipments->sum(fn ($eq) => $eq->total_watts);
    $equipTotalUnits = (int) $facilityEquipments->sum('quantity');
    $equipTotalItems = $facilityEquipments->count();
@endphp

@if($showFormSections ?? false)
    <div class="meter-form-section-title"><i class="fa-solid fa-chart-line"></i> Baseline &amp; Notes</div>
@endif
<div class="meter-form-field full">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px;">
        <label class="meter-form-label" for="{{ $prefix }}_baseline_kwh" style="margin-bottom:0;">Meter Baseline kWh</label>
        <button type="button" class="baseline-calc-toggle-btn" onclick="toggleBaselineCalculator('{{ $prefix }}')">
            <i class="fa-solid fa-calculator" style="color:#2563eb;"></i>
            <span>3–6 Months Baseline Calculator</span>
            <i class="fa-solid fa-chevron-down calc-chevron" id="{{ $prefix }}_calc_chevron"></i>
        </button>
    </div>
    <div style="position:relative;">
        <input class="meter-form-control" id="{{ $prefix }}_baseline_kwh" type="number" step="0.01" min="0" name="baseline_kwh" value="{{ old('baseline_kwh') }}" placeholder="Optional baseline kWh (e.g. 4200.00)">
    </div>
    <div class="meter-form-hint">Used for baseline comparison and variance monitoring. Leave blank if not set yet.</div>

    <!-- 3-6 Months Baseline Calculator Widget (Organized Tabbed System) -->
    <div id="{{ $prefix }}_baseline_calc_panel" class="baseline-calc-panel" style="display:none;">
        <div class="calc-panel-header">
            <div class="calc-panel-title">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:#2563eb;"></i>
                <span>Baseline Computation Assistant</span>
            </div>
            <span class="calc-panel-subtitle">
                Select your preferred computation method: calculate from historical utility bills or from connected equipment loads.
            </span>
        </div>

        <!-- Method Switcher Navigation Tabs -->
        <div class="calc-tab-nav">
            <button type="button" class="calc-tab-btn active" id="{{ $prefix }}_tab_btn_bills" onclick="switchBaselineTab('{{ $prefix }}', 'bills')">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Option A: Past 3–6 Months Bills</span>
            </button>
            <button type="button" class="calc-tab-btn" id="{{ $prefix }}_tab_btn_equip" onclick="switchBaselineTab('{{ $prefix }}', 'equip')">
                <i class="fa-solid fa-plug-circle-bolt"></i>
                <span>Option B: Connected Equipment Load @if($equipTotalMonthlyKwh > 0)({{ number_format($equipTotalMonthlyKwh, 0) }} kWh)@endif</span>
            </button>
        </div>

        <!-- TAB 1: Historical 3-6 Months Billing Records -->
        <div id="{{ $prefix }}_tab_content_bills" class="calc-tab-content">
            @if($historyReadings->count() >= 3)
                @php
                    $threeMoAvg = round($historyReadings->take(3)->avg('actual_kwh'), 2);
                    $sixMoAvg = round($historyReadings->take(6)->avg('actual_kwh'), 2);
                @endphp
                <div class="calc-quick-actions">
                    <span class="calc-quick-label"><i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> Evaluation Period:</span>
                    <div class="calc-quick-btns">
                        <button type="button" class="calc-quick-btn" id="{{ $prefix }}_btn_dur_3" onclick="setBaselineDuration('{{ $prefix }}', 3, {{ $threeMoAvg }})">
                            <i class="fa-solid fa-clock-rotate-left"></i> Apply 3-Mo Avg ({{ number_format($threeMoAvg, 2) }} kWh)
                        </button>
                        @if($historyReadings->count() >= 6)
                            <button type="button" class="calc-quick-btn active" id="{{ $prefix }}_btn_dur_6" onclick="setBaselineDuration('{{ $prefix }}', 6, {{ $sixMoAvg }})">
                                <i class="fa-solid fa-calendar-check"></i> Apply 6-Mo Avg ({{ number_format($sixMoAvg, 2) }} kWh)
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Dynamic Symmetrical Grid (Displays 3 or 6 months based on selection) -->
            <div class="calc-months-grid" id="{{ $prefix }}_calc_months_grid">
                @for($m = 1; $m <= 6; $m++)
                    @php
                        $histItem = $historyReadings->get($m - 1);
                        $monthName = $histItem ? date('M Y', mktime(0, 0, 0, (int)$histItem->month, 1, (int)$histItem->year)) : "Month {$m}";
                        $val = $histItem ? round((float)$histItem->actual_kwh, 2) : '';
                    @endphp
                    <div class="calc-month-card" id="{{ $prefix }}_calc_card_m{{ $m }}">
                        <label class="calc-month-label" for="{{ $prefix }}_calc_m{{ $m }}" title="{{ $monthName }}">
                            <i class="fa-regular fa-calendar"></i> {{ $monthName }}
                        </label>
                        <div class="calc-month-input-wrap">
                            <input type="number" step="0.01" min="0" 
                                   class="calc-month-input" 
                                   id="{{ $prefix }}_calc_m{{ $m }}" 
                                   placeholder="0.00" 
                                   value="{{ $val }}"
                                   oninput="recomputeBaselineCalc('{{ $prefix }}')">
                            <span class="calc-input-unit">kWh</span>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="calc-summary-bar">
                <div class="calc-summary-stat">
                    <span>Active Months</span>
                    <strong id="{{ $prefix }}_calc_count">0</strong>
                </div>
                <div class="calc-summary-divider"></div>
                <div class="calc-summary-stat">
                    <span>Total Sum</span>
                    <strong id="{{ $prefix }}_calc_total">0.00 kWh</strong>
                </div>
                <div class="calc-summary-divider"></div>
                <div class="calc-summary-stat highlight">
                    <span>Computed Monthly Baseline</span>
                    <strong id="{{ $prefix }}_calc_avg">0.00 kWh</strong>
                </div>
                <div class="calc-summary-action">
                    <button type="button" class="calc-apply-btn" onclick="applyCustomCalculatedBaseline('{{ $prefix }}')">
                        <i class="fa-solid fa-check"></i> Apply to Baseline
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 2: Connected Equipment Load Schedule -->
        <div id="{{ $prefix }}_tab_content_equip" class="calc-tab-content" style="display:none;">
            @if($equipTotalMonthlyKwh > 0)
                <div class="calc-equip-card">
                    <div class="calc-equip-header">
                        <div class="calc-equip-badge">
                            <i class="fa-solid fa-plug-circle-bolt"></i> Equipment Load Schedule
                        </div>
                        <span class="calc-equip-meta">
                            {{ $equipTotalItems }} load categories &bull; {{ number_format($equipTotalUnits) }} units &bull; {{ number_format($equipTotalWatts / 1000, 2) }} kW connected
                        </span>
                    </div>

                    <div class="calc-equip-body">
                        <div class="calc-equip-stat-box">
                            <span class="calc-equip-stat-label">1-Month Operating Baseline</span>
                            <strong class="calc-equip-stat-val">{{ number_format($equipTotalMonthlyKwh, 2) }} <small>kWh/month</small></strong>
                        </div>
                        <div class="calc-equip-actions">
                            <button type="button" class="calc-equip-btn" onclick="quickApplyBaseline('{{ $prefix }}', {{ $equipTotalMonthlyKwh }}, 'equipment load inventory')">
                                <i class="fa-solid fa-bolt-lightning"></i> Apply Equipment Baseline
                            </button>
                        </div>
                    </div>

                    <div class="calc-equip-formula-bar">
                        <i class="fa-solid fa-calculator"></i>
                        <span><strong>Formula:</strong> (Quantity &times; Watts &times; Hours/Day &times; Days/Month) &divide; 1,000 = Monthly kWh</span>
                    </div>

                    <div class="calc-equip-table-wrap">
                        <table class="calc-equip-table">
                            <thead>
                                <tr>
                                    <th>Equipment / Load</th>
                                    <th style="text-align:center;">Qty &times; Watts</th>
                                    <th style="text-align:center;">Duty Hours</th>
                                    <th style="text-align:center;">Operating Days</th>
                                    <th style="text-align:right;">Monthly kWh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facilityEquipments as $eq)
                                    <tr>
                                        <td>
                                            <strong>{{ $eq->equipment_name }}</strong>
                                            @if($eq->location)
                                                <small style="display:block; color:#64748b;">{{ $eq->location }}</small>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">{{ $eq->quantity }} &times; {{ number_format($eq->rated_watts, 0) }}W</td>
                                        <td style="text-align:center;">{{ number_format($eq->operating_hours_per_day, 1) }}h/d</td>
                                        <td style="text-align:center;">{{ $eq->operating_days_per_month }} d/mo</td>
                                        <td style="text-align:right; font-weight:800; color:#047857;">{{ number_format($eq->monthly_kwh, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="calc-equip-empty-hint">
                    <i class="fa-solid fa-circle-info" style="font-size:1.2rem; color:#0284c7;"></i>
                    <div>
                        <strong>No equipment registered in Load Tracking yet.</strong>
                        <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">
                            You can register equipment loads in the <strong>Facility Load Tracking</strong> module to establish an equipment-based baseline.
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div id="{{ $prefix }}_calc_feedback" class="calc-feedback" style="display:none;"></div>
    </div>
</div>

<div class="meter-form-field full">
    <label class="meter-form-label" for="{{ $prefix }}_notes">Notes</label>
    <textarea class="meter-form-control meter-form-textarea" id="{{ $prefix }}_notes" name="notes" rows="3" maxlength="2000" placeholder="Optional notes (assigned area, purpose, feeder remarks, etc.)">{{ old('notes') }}</textarea>
</div>

<style>
    .baseline-calc-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0f7ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.76rem;
        font-weight: 750;
        cursor: pointer;
        transition: all .16s ease;
    }
    .baseline-calc-toggle-btn:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1e40af;
        transform: translateY(-1px);
    }
    .calc-chevron {
        transition: transform .2s ease;
        font-size: 0.68rem;
    }
    .baseline-calc-panel {
        background: linear-gradient(135deg, #f8fbff 0%, #f1f5f9 100%);
        border: 1px solid #cbd5e1;
        border-radius: 13px;
        padding: 16px;
        margin-top: 10px;
        box-shadow: inset 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .calc-panel-header {
        margin-bottom: 12px;
    }
    .calc-panel-title {
        display: flex;
        align-items: center;
        gap: 7px;
        font-weight: 800;
        font-size: 0.88rem;
        color: #0f172a;
    }
    .calc-panel-subtitle {
        display: block;
        margin-top: 2px;
        color: #64748b;
        font-size: 0.75rem;
    }
    .calc-quick-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
        padding: 8px 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
    }
    .calc-quick-label {
        font-size: 0.74rem;
        font-weight: 750;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .calc-quick-btns {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .calc-quick-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1.5px solid #2563eb;
        color: #2563eb;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 750;
        cursor: pointer;
        transition: all .15s ease;
    }
    .calc-quick-btn:hover {
        background: #eff6ff;
        transform: translateY(-1px);
    }
    .calc-quick-btn.active {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }
    body.dark-mode .calc-quick-btn {
        background: #1e293b;
        border-color: #3b82f6;
        color: #93c5fd;
    }
    body.dark-mode .calc-quick-btn.active {
        background: #2563eb;
        color: #ffffff;
    }
    .calc-tab-nav {
        display: flex;
        gap: 6px;
        background: #e2e8f0;
        padding: 4px;
        border-radius: 10px;
        margin-bottom: 14px;
    }
    .calc-tab-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 750;
        cursor: pointer;
        transition: all 0.16s ease;
    }
    .calc-tab-btn:hover {
        color: #0f172a;
    }
    .calc-tab-btn.active {
        background: #ffffff;
        color: #2563eb;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
    }
    .calc-months-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }
    @media (max-width: 580px) {
        .calc-months-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .calc-tab-nav {
            flex-direction: column;
        }
    }
    .calc-month-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 10px;
    }
    .calc-month-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 750;
        color: #475569;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .calc-month-input-wrap {
        display: flex;
        align-items: center;
        position: relative;
    }
    .calc-month-input {
        width: 100%;
        padding: 6px 8px;
        padding-right: 32px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
        box-sizing: border-box;
    }
    .calc-month-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
    }
    .calc-input-unit {
        position: absolute;
        right: 8px;
        font-size: 0.68rem;
        color: #94a3b8;
        font-weight: 600;
        pointer-events: none;
    }
    .calc-summary-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 14px;
    }
    .calc-summary-stat {
        display: flex;
        flex-direction: column;
    }
    .calc-summary-stat span {
        font-size: 0.68rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .calc-summary-stat strong {
        font-size: 0.88rem;
        color: #1e293b;
        font-weight: 800;
    }
    .calc-summary-stat.highlight strong {
        color: #2563eb;
        font-size: 0.96rem;
        font-weight: 850;
    }
    .calc-summary-divider {
        width: 1px;
        height: 28px;
        background: #e2e8f0;
    }
    .calc-apply-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 800;
        cursor: pointer;
        transition: background .15s ease, transform .15s ease;
    }
    .calc-apply-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }
    .calc-feedback {
        margin-top: 8px;
        padding: 7px 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 7px;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .calc-equip-card {
        background: #ffffff;
        border: 1.5px solid #a7f3d0;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 6px;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.08);
    }
    .calc-equip-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 10px;
    }
    .calc-equip-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #ecfdf5;
        color: #047857;
        font-size: 0.76rem;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #a7f3d0;
    }
    .calc-equip-meta {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
    }
    .calc-equip-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 10px 12px;
        background: #f0fdf4;
        border-radius: 8px;
        border: 1px solid #bbf7d0;
    }
    .calc-equip-stat-box {
        display: flex;
        flex-direction: column;
    }
    .calc-equip-stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        color: #166534;
        font-weight: 750;
        letter-spacing: 0.03em;
    }
    .calc-equip-stat-val {
        font-size: 1.25rem;
        font-weight: 850;
        color: #047857;
    }
    .calc-equip-stat-val small {
        font-size: 0.8rem;
        color: #059669;
        font-weight: 700;
    }
    .calc-equip-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #059669;
        color: #ffffff;
        border: none;
        padding: 9px 15px;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25);
    }
    .calc-equip-btn:hover {
        background: #047857;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(5, 150, 105, 0.35);
    }
    .calc-equip-formula-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 7px;
        padding: 6px 10px;
        font-size: 0.72rem;
        color: #475569;
        margin: 10px 0;
    }
    .calc-equip-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
    }
    .calc-equip-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.74rem;
    }
    .calc-equip-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
        text-transform: uppercase;
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.67rem;
        letter-spacing: 0.02em;
    }
    .calc-equip-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .calc-equip-table tr:last-child td {
        border-bottom: none;
    }
    .calc-equip-empty-hint {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 0.76rem;
        color: #0369a1;
    }
    body.dark-mode .calc-tab-nav { background: #0f172a; }
    body.dark-mode .calc-tab-btn { color: #94a3b8; }
    body.dark-mode .calc-tab-btn.active { background: #1e293b; color: #93c5fd; }
    body.dark-mode .calc-equip-card { background: #0f172a; border-color: #065f46; }
    body.dark-mode .calc-equip-body { background: #064e3b; border-color: #047857; }
    body.dark-mode .calc-equip-stat-label { color: #a7f3d0; }
    body.dark-mode .calc-equip-stat-val { color: #f0fdf4; }
    body.dark-mode .calc-equip-formula-bar { background: #1e293b; border-color: #334155; color: #cbd5e1; }
    body.dark-mode .calc-equip-table-wrap { border-color: #334155; background: #0f172a; }
    body.dark-mode .calc-equip-table th { background: #1e293b; color: #94a3b8; border-color: #334155; }
    body.dark-mode .calc-equip-table td { border-color: #1e293b; color: #e2e8f0; }
    body.dark-mode .calc-equip-empty-hint { background: #082f49; border-color: #0284c7; color: #bae6fd; }
    body.dark-mode .baseline-calc-panel { background: #1e293b; border-color: #334155; }
    body.dark-mode .calc-panel-title { color: #f8fafc; }
    body.dark-mode .calc-month-card { background: #0f172a; border-color: #334155; }
    body.dark-mode .calc-month-input { background: #1e293b; border-color: #475569; color: #f8fafc; }
    body.dark-mode .calc-summary-bar { background: #0f172a; border-color: #334155; }
    body.dark-mode .calc-summary-stat strong { color: #f8fafc; }
    body.dark-mode .calc-quick-actions { background: #0f172a; border-color: #334155; }
</style>

<script>
if (!window.baselineCalculatorFunctionsLoaded) {
    window.baselineCalculatorFunctionsLoaded = true;

    window.toggleBaselineCalculator = function(prefix) {
        const panel = document.getElementById(prefix + '_baseline_calc_panel');
        const chevron = document.getElementById(prefix + '_calc_chevron');
        if (!panel) return;
        const isHidden = panel.style.display === 'none';
        panel.style.display = isHidden ? 'block' : 'none';
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }
        if (isHidden) {
            window.recomputeBaselineCalc(prefix);
        }
    };

    window.switchBaselineTab = function(prefix, tab) {
        const billsContent = document.getElementById(prefix + '_tab_content_bills');
        const equipContent = document.getElementById(prefix + '_tab_content_equip');
        const billsBtn = document.getElementById(prefix + '_tab_btn_bills');
        const equipBtn = document.getElementById(prefix + '_tab_btn_equip');

        if (tab === 'bills') {
            if (billsContent) billsContent.style.display = 'block';
            if (equipContent) equipContent.style.display = 'none';
            if (billsBtn) billsBtn.classList.add('active');
            if (equipBtn) equipBtn.classList.remove('active');
        } else {
            if (billsContent) billsContent.style.display = 'none';
            if (equipContent) equipContent.style.display = 'block';
            if (billsBtn) billsBtn.classList.remove('active');
            if (equipBtn) equipBtn.classList.add('active');
        }
    };

    window.currentBaselineDuration = window.currentBaselineDuration || {};

    window.setBaselineDuration = function(prefix, duration, maybeValue) {
        window.currentBaselineDuration[prefix] = duration;

        // Show/hide month cards: if 3, show 1-3 and hide 4-6; if 6, show 1-6
        for (let m = 1; m <= 6; m++) {
            const card = document.getElementById(prefix + '_calc_card_m' + m);
            if (card) {
                card.style.display = (m <= duration) ? 'block' : 'none';
            }
        }

        // Toggle active button style
        const btn3 = document.getElementById(prefix + '_btn_dur_3');
        const btn6 = document.getElementById(prefix + '_btn_dur_6');
        if (btn3 && btn6) {
            if (duration === 3) {
                btn3.classList.add('active');
                btn6.classList.remove('active');
            } else {
                btn6.classList.add('active');
                btn3.classList.remove('active');
            }
        }

        // Recompute summary for visible cards
        window.recomputeBaselineCalc(prefix);

        // If value passed, apply to baseline input
        if (maybeValue !== undefined && maybeValue !== null) {
            window.quickApplyBaseline(prefix, maybeValue, duration);
        }
    };

    window.recomputeBaselineCalc = function(prefix) {
        const maxMonths = (window.currentBaselineDuration && window.currentBaselineDuration[prefix]) ? window.currentBaselineDuration[prefix] : 6;
        let count = 0;
        let sum = 0;
        for (let m = 1; m <= maxMonths; m++) {
            const inp = document.getElementById(prefix + '_calc_m' + m);
            if (inp && inp.value !== '' && !isNaN(parseFloat(inp.value))) {
                const v = parseFloat(inp.value);
                if (v > 0) {
                    count++;
                    sum += v;
                }
            }
        }
        const avg = count > 0 ? (sum / count) : 0;
        const countEl = document.getElementById(prefix + '_calc_count');
        const totalEl = document.getElementById(prefix + '_calc_total');
        const avgEl = document.getElementById(prefix + '_calc_avg');
        
        if (countEl) countEl.innerText = count + ' month' + (count !== 1 ? 's' : '');
        if (totalEl) totalEl.innerText = sum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' kWh';
        if (avgEl) avgEl.innerText = avg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' kWh';
    };

    window.applyCustomCalculatedBaseline = function(prefix) {
        const maxMonths = (window.currentBaselineDuration && window.currentBaselineDuration[prefix]) ? window.currentBaselineDuration[prefix] : 6;
        let count = 0;
        let sum = 0;
        for (let m = 1; m <= maxMonths; m++) {
            const inp = document.getElementById(prefix + '_calc_m' + m);
            if (inp && inp.value !== '' && !isNaN(parseFloat(inp.value))) {
                const v = parseFloat(inp.value);
                if (v > 0) {
                    count++;
                    sum += v;
                }
            }
        }
        if (count === 0) {
            alert('Please enter at least 1 monthly reading.');
            return;
        }
        const avg = sum / count;
        window.quickApplyBaseline(prefix, avg, count);
    };

    window.quickApplyBaseline = function(prefix, value, monthsOrSource) {
        const targetInput = document.getElementById(prefix + '_baseline_kwh');
        if (!targetInput) return;
        targetInput.value = parseFloat(value).toFixed(2);
        
        targetInput.style.borderColor = '#16a34a';
        targetInput.style.boxShadow = '0 0 0 4px rgba(22, 163, 74, 0.25)';
        setTimeout(() => {
            targetInput.style.borderColor = '';
            targetInput.style.boxShadow = '';
        }, 1500);

        const feedback = document.getElementById(prefix + '_calc_feedback');
        if (feedback) {
            feedback.style.display = 'block';
            let msg = 'Applied <strong>' + parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' kWh</strong>';
            if (typeof monthsOrSource === 'number') {
                msg += ' based on ' + monthsOrSource + '-month average.';
            } else if (typeof monthsOrSource === 'string') {
                msg += ' based on ' + monthsOrSource + '.';
            }
            feedback.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + msg;
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 4500);
        }
    };
}
</script>
