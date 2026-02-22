<?php

use App\Http\Controllers\Modules\FacilityController;
use App\Models\EnergyRecord;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Delete a monthly energy record for a facility
Route::delete('/modules/facilities/{facility}/monthly-records/{record}', function ($facilityId, $recordId) {
    $record = EnergyRecord::where('facility_id', $facilityId)->where('id', $recordId)->firstOrFail();
    $record->delete();
    if (request()->expectsJson() || request()->isJson() || request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Monthly record deleted!']);
    }
    // Redirect to the monthly records list for the facility
    return redirect('/modules/facilities/' . $facilityId . '/monthly-records')->with('success', 'Monthly record deleted!');
})->name('energy-records.delete');

// Store new monthly energy record for a facility (for modal form)
Route::post('/modules/facilities/{facility}/monthly-records', function ($facilityId, Request $request) {
    $validated = $request->validate([
        'date' => 'required|date',
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
    // Prevent duplicate entry for the same facility, month, and year
    $exists = EnergyRecord::where('facility_id', $facilityId)
        ->where('month', $validated['month'])
        ->where('year', $validated['year'])
        ->exists();
    if ($exists) {
        return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
    }
    if ($request->hasFile('bill_image')) {
        $path = $request->file('bill_image')->store('meralco_bills', 'public');
        $validated['bill_image'] = $path;
    }
    // Make sure baseline_kwh is set (from input or fallback)
    if (!isset($validated['baseline_kwh']) || $validated['baseline_kwh'] === null || $validated['baseline_kwh'] === '') {
        // Try to get from latest energy profile or facility
        $facility = Facility::find($facilityId);
        $profile = $facility ? $facility->energyProfiles()->latest()->first() : null;
        $validated['baseline_kwh'] = $profile && $profile->baseline_kwh !== null ? $profile->baseline_kwh : ($facility ? $facility->baseline_kwh : null);
    }
    EnergyRecord::create($validated);
    return redirect()->back()->with('success', 'Monthly record added!');
})->name('energy-records.store');

Route::middleware(['auth', 'verified'])->group(function () {
    // Engineer Approval Toggle (AJAX)
    Route::post('/modules/facilities/{id}/toggle-engineer-approval', [FacilityController::class, 'toggleEngineerApproval'])->name('modules.facilities.toggle-engineer-approval');

    // Facility modal detail for AJAX
    Route::get('/modules/facilities/{facility}/modal-detail', [FacilityController::class, 'modalDetail'])->name('modules.facilities.modal-detail');
});
