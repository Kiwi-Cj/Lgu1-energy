@php
    $prefix = ($mode ?? 'add') === 'edit' ? 'edit' : 'add';
    $allowSubMeter = (bool) ($hasApprovedMainForSub ?? false);
    $disableSubOption = $prefix === 'add' && ! $allowSubMeter;
    $selectedMeterType = $disableSubOption ? 'main' : old('meter_type', 'sub');
@endphp

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_name">Meter Name <span class="meter-required">*</span></label>
    <input class="meter-form-control" id="{{ $prefix }}_meter_name" type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}" placeholder="e.g. Main Meter, 2nd Floor Panel">
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_number">Meter Number</label>
    <input class="meter-form-control" id="{{ $prefix }}_meter_number" type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}" placeholder="Utility / meter serial no.">
</div>

<div class="meter-form-field">
    <label class="meter-form-label" for="{{ $prefix }}_meter_type">Meter Type <span class="meter-required">*</span></label>
    <select class="meter-form-control" id="{{ $prefix }}_meter_type" name="meter_type" required>
        <option value="main" @selected($selectedMeterType === 'main')>Main Meter</option>
        <option value="sub" @selected($selectedMeterType === 'sub') @disabled($disableSubOption)>
            Sub-meter{{ $disableSubOption ? ' (Need approved main first)' : '' }}
        </option>
    </select>
    @if($disableSubOption)
        <div class="meter-form-hint meter-form-hint-warning">Add and approve at least one Main Meter first to enable Sub-meter.</div>
    @endif
</div>

<div class="meter-form-field">
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

<div class="meter-form-field full">
    <label class="meter-form-label" for="{{ $prefix }}_baseline_kwh">Meter Baseline kWh</label>
    <input class="meter-form-control" id="{{ $prefix }}_baseline_kwh" type="number" step="0.01" min="0" name="baseline_kwh" value="{{ old('baseline_kwh') }}" placeholder="Recommended for sub-meters (e.g. 4200.00)">
    <div class="meter-form-hint">Used for sub-meter variance monitoring in Monthly Records. Leave blank if not set yet.</div>
</div>

<div class="meter-form-field full">
    <label class="meter-form-label" for="{{ $prefix }}_notes">Notes</label>
    <textarea class="meter-form-control meter-form-textarea" id="{{ $prefix }}_notes" name="notes" rows="3" maxlength="2000" placeholder="Optional notes (assigned area, purpose, feeder remarks, etc.)">{{ old('notes') }}</textarea>
</div>
