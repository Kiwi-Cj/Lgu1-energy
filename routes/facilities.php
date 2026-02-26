<?php

use App\Http\Controllers\Modules\FacilityController;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$resolvePublicUploadRoot = function (): string {
    $configured = (string) env('PUBLIC_UPLOAD_ROOT', '');
    if ($configured !== '' && is_dir($configured)) {
        return rtrim($configured, DIRECTORY_SEPARATOR);
    }

    $cpanelPublicHtml = dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html';
    if (is_dir($cpanelPublicHtml)) {
        return rtrim($cpanelPublicHtml, DIRECTORY_SEPARATOR);
    }

    return public_path();
};

// Delete a monthly energy record for a facility
Route::delete('/modules/facilities/{facility}/monthly-records/{record}', function ($facilityId, $recordId) {
    $record = EnergyRecord::where('facility_id', $facilityId)->where('id', $recordId)->firstOrFail();
    $record->delete();
    if (request()->expectsJson() || request()->isJson() || request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Monthly record archived successfully!']);
    }
    // Redirect to the monthly records list for the facility
    return redirect('/modules/facilities/' . $facilityId . '/monthly-records')->with('success', 'Monthly record moved to archive.');
})->middleware(['auth', 'verified'])->name('energy-records.delete');

// Restore an archived monthly energy record for a facility
Route::post('/modules/facilities/{facility}/monthly-records/{record}/restore', function ($facilityId, $recordId) {
    $record = EnergyRecord::onlyTrashed()
        ->where('facility_id', $facilityId)
        ->where('id', $recordId)
        ->firstOrFail();

    $duplicateActiveRecord = EnergyRecord::where('facility_id', $facilityId)
        ->where('month', $record->month)
        ->where('year', $record->year)
        ->when(
            $record->meter_id,
            fn ($q) => $q->where('meter_id', $record->meter_id),
            fn ($q) => $q->whereNull('meter_id')
        )
        ->exists();

    if ($duplicateActiveRecord) {
        return redirect()
            ->back()
            ->with('error', 'Cannot restore this record because an active record for the same month and year already exists.');
    }

    $record->restore();

    return redirect('/modules/facilities/' . $facilityId . '/monthly-records/archive')
        ->with('success', 'Monthly record restored successfully.');
})->middleware(['auth', 'verified'])->name('energy-records.restore');

// Store new monthly energy record for a facility (for modal form)
Route::post('/modules/facilities/{facility}/monthly-records', function ($facilityId, Request $request) use ($resolvePublicUploadRoot) {
    $validated = $request->validate([
        'date' => 'required|date',
        'meter_id' => 'nullable',
        'actual_kwh' => 'required|numeric',
        'energy_cost' => 'nullable|numeric',
        'bill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        'baseline_kwh' => 'nullable|numeric',
    ]);
    $date = date_create($validated['date']);
    $validated['year'] = $date->format('Y');
    $validated['month'] = $date->format('n');
    $validated['day'] = $date->format('j');
    $validated['facility_id'] = $facilityId;
    $validated['recorded_by'] = auth()->id();
    $validated['meter_id'] = null;

    $meterIdRaw = $request->input('meter_id');
    if ($meterIdRaw !== null && $meterIdRaw !== '') {
        $meterId = (int) $meterIdRaw;
        $meter = FacilityMeter::where('facility_id', $facilityId)->whereKey($meterId)->first();
        if (! $meter) {
            return redirect()->back()->withInput()->withErrors(['meter_id' => 'Selected meter does not belong to this facility.']);
        }
        $validated['meter_id'] = $meter->id;
    }

    // Prevent duplicate entry for the same facility, month, and year
    $existsQuery = EnergyRecord::where('facility_id', $facilityId)
        ->where('month', $validated['month'])
        ->where('year', $validated['year']);
    if (!empty($validated['meter_id'])) {
        $existsQuery->where('meter_id', $validated['meter_id']);
    } else {
        $existsQuery->whereNull('meter_id');
    }
    $exists = $existsQuery->exists();
    if ($exists) {
        $targetLabel = !empty($validated['meter_id']) ? ('the selected meter') : 'the facility aggregate';
        return redirect()->back()->withInput()->withErrors(['duplicate' => "An energy record for {$targetLabel} and month/year already exists."]);
    }
    if ($request->hasFile('bill_image')) {
        $directory = $resolvePublicUploadRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'meralco_bills';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('bill_image');
        $filename = uniqid('bill_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);
        $path = 'uploads/meralco_bills/' . $filename;
        $validated['bill_image'] = $path;
    }
    // Make sure baseline_kwh is set (from input or fallback)
    if (!isset($validated['baseline_kwh']) || $validated['baseline_kwh'] === null || $validated['baseline_kwh'] === '') {
        // Prefer selected meter baseline (for main/sub-meter records), then fallback to energy profile/facility baseline.
        if (! empty($validated['meter_id'])) {
            $selectedMeter = FacilityMeter::where('facility_id', $facilityId)->whereKey($validated['meter_id'])->first();
            if ($selectedMeter && $selectedMeter->baseline_kwh !== null && $selectedMeter->baseline_kwh !== '') {
                $validated['baseline_kwh'] = $selectedMeter->baseline_kwh;
            }
        }

        if (! array_key_exists('baseline_kwh', $validated) || $validated['baseline_kwh'] === null || $validated['baseline_kwh'] === '') {
            $facility = Facility::find($facilityId);
            $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
            $validated['baseline_kwh'] = $profile && $profile->baseline_kwh !== null ? $profile->baseline_kwh : ($facility ? $facility->baseline_kwh : null);
        }
    }
    EnergyRecord::create($validated);
    return redirect()->back()->with('success', 'Monthly record added!');
})->middleware(['auth', 'verified'])->name('energy-records.store');

Route::middleware(['auth', 'verified'])->group(function () {
    // Engineer Approval Toggle (AJAX)
    Route::post('/modules/facilities/{id}/toggle-engineer-approval', [FacilityController::class, 'toggleEngineerApproval'])->name('modules.facilities.toggle-engineer-approval');

    // Facility modal detail for AJAX
    Route::get('/modules/facilities/{facility}/modal-detail', [FacilityController::class, 'modalDetail'])->name('modules.facilities.modal-detail');
});

