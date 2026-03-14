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

    if ($record->meter_id === null) {
        return redirect()
            ->back()
            ->with('error', 'Legacy facility aggregate records are no longer supported and cannot be restored.');
    }

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
        'meter_id' => 'required|integer',
        'actual_kwh' => 'required|numeric|min:0',
        'rate_per_kwh' => 'nullable|numeric|min:0',
        'bill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
    ]);

    $date = date_create($validated['date']);
    $validated['year'] = $date->format('Y');
    $validated['month'] = $date->format('n');
    $validated['day'] = $date->format('j');
    $validated['facility_id'] = $facilityId;
    $validated['recorded_by'] = auth()->id();
    $validated['meter_id'] = (int) $validated['meter_id'];

    $facility = Facility::find($facilityId);
    $latestProfile = $facility ? $facility->energyProfiles()->latest()->first() : null;

    $selectedMeter = FacilityMeter::where('facility_id', $facilityId)
        ->whereKey($validated['meter_id'])
        ->first();
    if (! $selectedMeter) {
        return redirect()->back()->withInput()->withErrors(['meter_id' => 'Selected meter does not belong to this facility.']);
    }
    if (! $selectedMeter->approved_at) {
        return redirect()->back()->withInput()->withErrors([
            'meter_id' => 'Selected meter is not approved. Approve the meter first.',
        ]);
    }
    if (strtolower((string) ($selectedMeter->meter_type ?? '')) !== 'main') {
        return redirect()->back()->withInput()->withErrors([
            'meter_id' => 'Monthly Energy Records in this module accept Main Meter only.',
        ]);
    }
    $validated['meter_id'] = $selectedMeter->id;

    // Prevent duplicate entry for the same facility, month/year, and main meter.
    $existsQuery = EnergyRecord::where('facility_id', $facilityId)
        ->where('month', $validated['month'])
        ->where('year', $validated['year'])
        ->where('meter_id', $validated['meter_id']);
    $exists = $existsQuery->exists();
    if ($exists) {
        $targetLabel = 'the selected main meter';
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

    // Server-side computation: do not trust client-provided cost.
    $ratePerKwh = isset($validated['rate_per_kwh']) && $validated['rate_per_kwh'] !== null && $validated['rate_per_kwh'] !== ''
        ? (float) $validated['rate_per_kwh']
        : 12.0;
    $actualKwh = (float) $validated['actual_kwh'];
    $validated['rate_per_kwh'] = $ratePerKwh;
    $validated['energy_cost'] = round($actualKwh * $ratePerKwh, 2);

    // Keep compatibility with existing reports/incidents by deriving baseline from meter/facility setup.
    // Rule priority:
    // 1) Selected meter baseline (if record is meter-specific)
    // 2) Main meter baseline (primary linked main meter, then any main meter baseline)
    // 3) Energy profile baseline
    // 4) Facility baseline
    $validated['baseline_kwh'] = null;
    if ($selectedMeter && is_numeric($selectedMeter->baseline_kwh)) {
        $validated['baseline_kwh'] = (float) $selectedMeter->baseline_kwh;
    }

    if ($validated['baseline_kwh'] === null) {
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;

        $mainMeterBaseline = null;
        if ($profile && ! empty($profile->primary_meter_id)) {
            $primaryMain = FacilityMeter::where('facility_id', $facilityId)
                ->where('meter_type', 'main')
                ->whereNotNull('approved_at')
                ->whereKey($profile->primary_meter_id)
                ->first();
            if ($primaryMain && is_numeric($primaryMain->baseline_kwh)) {
                $mainMeterBaseline = (float) $primaryMain->baseline_kwh;
            }
        }

        if ($mainMeterBaseline === null) {
            $fallbackMain = FacilityMeter::where('facility_id', $facilityId)
                ->where('meter_type', 'main')
                ->whereNotNull('approved_at')
                ->whereNotNull('baseline_kwh')
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->first();
            if ($fallbackMain && is_numeric($fallbackMain->baseline_kwh)) {
                $mainMeterBaseline = (float) $fallbackMain->baseline_kwh;
            }
        }

        if ($mainMeterBaseline !== null) {
            $validated['baseline_kwh'] = $mainMeterBaseline;
        } elseif ($profile && is_numeric($profile->baseline_kwh)) {
            $validated['baseline_kwh'] = (float) $profile->baseline_kwh;
        } elseif ($facility && is_numeric($facility->baseline_kwh)) {
            $validated['baseline_kwh'] = (float) $facility->baseline_kwh;
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

