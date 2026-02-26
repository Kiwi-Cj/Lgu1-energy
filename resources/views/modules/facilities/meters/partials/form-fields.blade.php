@php
    $prefix = ($mode ?? 'add') === 'edit' ? 'edit' : 'add';
@endphp

<div>
    <label for="{{ $prefix }}_meter_name" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
    <input id="{{ $prefix }}_meter_name" type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;" placeholder="e.g. Main Meter, 2nd Floor Panel">
</div>

<div>
    <label for="{{ $prefix }}_meter_number" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
    <input id="{{ $prefix }}_meter_number" type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;" placeholder="Utility / meter serial no.">
</div>

<div>
    <label for="{{ $prefix }}_meter_type" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Type <span style="color:#e11d48;">*</span></label>
    <select id="{{ $prefix }}_meter_type" name="meter_type" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
        <option value="main" @selected(old('meter_type') === 'main')>Main Meter</option>
        <option value="sub" @selected(old('meter_type', 'sub') === 'sub')>Sub-meter</option>
    </select>
</div>

<div>
    <label for="{{ $prefix }}_parent_meter_id" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Parent Meter</label>
    <select id="{{ $prefix }}_parent_meter_id" name="parent_meter_id" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
        <option value="">None</option>
        @foreach(($parentMeterOptions ?? collect()) as $parentMeter)
            <option value="{{ $parentMeter->id }}">
                {{ $parentMeter->meter_name }} ({{ strtoupper((string) $parentMeter->meter_type) }})
            </option>
        @endforeach
    </select>
    <div style="font-size:.82rem;color:#64748b;margin-top:4px;">Usually blank for Main Meter. Optional for sub-meter hierarchy.</div>
</div>

<div>
    <label for="{{ $prefix }}_location" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
    <input id="{{ $prefix }}_location" type="text" name="location" maxlength="255" value="{{ old('location') }}" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;" placeholder="e.g. 2nd Floor Electrical Room">
</div>

<div>
    <label for="{{ $prefix }}_status" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
    <select id="{{ $prefix }}_status" name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
    </select>
</div>

<div>
    <label for="{{ $prefix }}_multiplier" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier <span style="color:#e11d48;">*</span></label>
    <input id="{{ $prefix }}_multiplier" type="number" step="0.0001" min="0.0001" max="999999" name="multiplier" value="{{ old('multiplier', '1') }}" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;" placeholder="1.0000">
    <div style="font-size:.82rem;color:#64748b;margin-top:4px;">Use 1.0 unless your meter uses CT/PT multiplier.</div>
</div>

<div style="grid-column:1/-1;">
    <label for="{{ $prefix }}_baseline_kwh" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Baseline kWh</label>
    <input id="{{ $prefix }}_baseline_kwh" type="number" step="0.01" min="0" name="baseline_kwh" value="{{ old('baseline_kwh') }}" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;" placeholder="Recommended for sub-meters (e.g. 4200.00)">
    <div style="font-size:.82rem;color:#64748b;margin-top:4px;">Used for sub-meter variance monitoring in Monthly Records. Leave blank if not set yet.</div>
</div>

<div style="grid-column:1/-1;">
    <label for="{{ $prefix }}_notes" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
    <textarea id="{{ $prefix }}_notes" name="notes" rows="3" maxlength="2000" style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;" placeholder="Optional notes (assigned area, purpose, feeder remarks, etc.)">{{ old('notes') }}</textarea>
</div>
