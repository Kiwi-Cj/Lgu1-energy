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

// Move a monthly energy record to the archive (soft delete)
Route::delete('/modules/facilities/{facility}/monthly-records/{record}', function (Request $request, $facilityId, $recordId) {
    $validated = $request->validate([
        'archive_reason' => ['required', 'string', 'max:500'],
    ]);

    $record = EnergyRecord::where('facility_id', $facilityId)->where('id', $recordId)->firstOrFail();
    $facility = Facility::findOrFail($facilityId);
    if (strtolower((string) $record->input_source) === 'cprf') {
        $message = 'Legacy CPRF-supplied monthly records are read-only in this system.';

        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('facilities.monthly-records', ['facility' => $facility->id])
            ->with('error', $message);
    }

    $record->deleted_by = auth()->id();
    $record->archive_reason = trim($validated['archive_reason']);
    $record->save();
    $record->delete();
    if (request()->expectsJson() || request()->isJson() || request()->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Monthly record moved to archive successfully.']);
    }
    // Redirect to the monthly records list for the facility
    return redirect('/modules/facilities/' . $facilityId . '/monthly-records');
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

// Permanently delete an archived monthly energy record for a facility
Route::delete('/modules/facilities/{facility}/monthly-records/{record}/force-delete', function ($facilityId, $recordId) {
    $record = EnergyRecord::onlyTrashed()
        ->where('facility_id', $facilityId)
        ->where('id', $recordId)
        ->firstOrFail();

    $record->forceDelete();

    return redirect('/modules/facilities/' . $facilityId . '/monthly-records/archive')
        ->with('success', 'Monthly record permanently deleted.');
})->middleware(['auth', 'verified'])->name('energy-records.force-delete');

// Store new monthly energy record for a facility (for modal form)
Route::post('/modules/facilities/{facility}/monthly-records', function ($facilityId, Request $request) use ($resolvePublicUploadRoot) {
    $facility = Facility::findOrFail($facilityId);
    $validated = $request->validate([
        'date' => 'required|date',
        'meter_id' => 'required|integer',
        'previous_reading_kwh' => 'nullable|numeric|min:0',
        'current_reading_kwh' => 'nullable|numeric|min:0',
        'actual_kwh' => 'required|numeric|min:0',
        'rate_per_kwh' => 'nullable|numeric|min:0',
        'bill_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
    ]);

    $prevReading = isset($validated['previous_reading_kwh']) && is_numeric($validated['previous_reading_kwh'])
        ? (float) $validated['previous_reading_kwh']
        : null;
    $currReading = isset($validated['current_reading_kwh']) && is_numeric($validated['current_reading_kwh'])
        ? (float) $validated['current_reading_kwh']
        : null;

    if ($prevReading !== null && $currReading !== null && $currReading >= $prevReading) {
        $validated['previous_reading_kwh'] = $prevReading;
        $validated['current_reading_kwh'] = $currReading;
        $validated['actual_kwh'] = round($currReading - $prevReading, 2);
    } else {
        $validated['previous_reading_kwh'] = $prevReading;
        $validated['current_reading_kwh'] = $currReading;
    }

    $date = date_create($validated['date']);
    $validated['year'] = $date->format('Y');
    $validated['month'] = $date->format('n');
    $validated['day'] = $date->format('j');
    $validated['facility_id'] = $facilityId;
    $validated['recorded_by'] = auth()->id();
    $validated['input_source'] = 'manual';
    $validated['meter_id'] = (int) $validated['meter_id'];

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
    $allowedTypes = config('features.submeters_enabled', false) ? ['main', 'sub'] : ['main'];
    if (! in_array(strtolower((string) ($selectedMeter->meter_type ?? '')), $allowedTypes, true)) {
        return redirect()->back()->withInput()->withErrors([
            'meter_id' => 'Monthly Energy Records in this module accept Main Meter only.',
        ]);
    }
    $validated['meter_id'] = $selectedMeter->id;

    // Prevent duplicate entry for the same facility, month/year, and meter.
    $existsQuery = EnergyRecord::where('facility_id', $facilityId)
        ->where('month', $validated['month'])
        ->where('year', $validated['year'])
        ->where('meter_id', $validated['meter_id']);
    $exists = $existsQuery->exists();
    if ($exists) {
        $targetLabel = $selectedMeter->meter_type === 'sub' ? 'the selected sub-meter' : 'the selected main meter';
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
    return redirect()->back();
})->middleware(['auth', 'verified'])->name('energy-records.store');

// Store new weekly meter reading
Route::post('/modules/facilities/{facility}/weekly-readings', function ($facilityId, Request $request) {
    $facility = Facility::findOrFail($facilityId);
    $validated = $request->validate([
        'meter_id' => 'required|integer',
        'year' => 'required|integer|min:2000|max:2100',
        'month' => 'required|integer|min:1|max:12',
        'week_number' => 'required|integer|min:1|max:4',
        'reading_date' => 'nullable|date',
        'previous_reading_kwh' => 'nullable|numeric|min:0',
        'current_reading_kwh' => 'nullable|numeric|min:0',
        'actual_kwh' => 'required|numeric|min:0',
        'rate_per_kwh' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ]);

    $selectedMeter = FacilityMeter::where('facility_id', $facilityId)
        ->whereKey($validated['meter_id'])
        ->first();

    if (! $selectedMeter) {
        return redirect()->back()->withInput()->withErrors(['meter_id' => 'Selected meter does not belong to this facility.']);
    }
    if (! $selectedMeter->approved_at) {
        return redirect()->back()->withInput()->withErrors(['meter_id' => 'Selected meter is not approved.']);
    }

    $prevReading = isset($validated['previous_reading_kwh']) && is_numeric($validated['previous_reading_kwh'])
        ? (float) $validated['previous_reading_kwh']
        : null;
    $currReading = isset($validated['current_reading_kwh']) && is_numeric($validated['current_reading_kwh'])
        ? (float) $validated['current_reading_kwh']
        : null;

    if ($prevReading !== null && $currReading !== null && $currReading >= $prevReading) {
        $actualKwh = round($currReading - $prevReading, 2);
    } else {
        $actualKwh = (float) $validated['actual_kwh'];
    }

    $ratePerKwh = isset($validated['rate_per_kwh']) && is_numeric($validated['rate_per_kwh']) && (float) $validated['rate_per_kwh'] > 0
        ? (float) $validated['rate_per_kwh']
        : 12.00;

    $cost = round($actualKwh * $ratePerKwh, 2);

    // Check duplicate week
    $existing = \App\Models\FacilityMeterWeeklyReading::where('facility_id', $facilityId)
        ->where('meter_id', $selectedMeter->id)
        ->where('year', (int) $validated['year'])
        ->where('month', (int) $validated['month'])
        ->where('week_number', (int) $validated['week_number'])
        ->first();

    if ($existing) {
        return redirect()->back()->withInput()->withErrors([
            'duplicate_week' => "A weekly reading for Week {$validated['week_number']} of this month already exists.",
        ]);
    }

    $reading = \App\Models\FacilityMeterWeeklyReading::create([
        'facility_id' => $facilityId,
        'meter_id' => $selectedMeter->id,
        'year' => (int) $validated['year'],
        'month' => (int) $validated['month'],
        'week_number' => (int) $validated['week_number'],
        'reading_date' => $validated['reading_date'] ?? null,
        'previous_reading_kwh' => $prevReading,
        'current_reading_kwh' => $currReading,
        'actual_kwh' => $actualKwh,
        'rate_per_kwh' => $ratePerKwh,
        'cost' => $cost,
        'notes' => $validated['notes'] ?? null,
        'encoded_by' => auth()->id(),
    ]);

    // Dispatch weekly spike notification if reading triggers an alert level
    $monthlyBaseline = (float) ($selectedMeter->baseline_kwh ?? $facility->baseline_kwh ?? 0);
    if ($monthlyBaseline > 0 && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
        $daysInMonth = \Carbon\Carbon::createFromDate((int) $validated['year'], (int) $validated['month'], 1)->daysInMonth;
        $weekDays = ((int) $validated['week_number']) < 4 ? 7 : max(1, $daysInMonth - 21);
        $weeklyBaseline = round($monthlyBaseline * ($weekDays / $daysInMonth), 2);
        $deviation = $weeklyBaseline > 0 ? round((($actualKwh - $weeklyBaseline) / $weeklyBaseline) * 100, 2) : null;
        $alertLevel = $deviation !== null ? \App\Models\EnergyRecord::resolveAlertLevel($deviation, $weeklyBaseline) : 'Normal';

        if (! in_array($alertLevel, ['Normal', 'No Data'], true)) {
            $monthName = \Carbon\Carbon::create(2000, (int) $validated['month'], 1)->format('F');
            $alertTitle = "⚡ Weekly Energy Alert: {$facility->name}";
            $alertMessage = "Week {$validated['week_number']} ({$monthName} {$validated['year']}) for {$selectedMeter->meter_name} recorded " . number_format($actualKwh, 2) . " kWh ({$alertLevel}: " . ($deviation > 0 ? '+' : '') . number_format($deviation, 2) . "% vs baseline).";
            $notifType = in_array($alertLevel, ['High', 'Critical']) ? 'critical' : 'warning';
            $targetUrl = route('facilities.monthly-records', [
                'facility' => $facilityId,
                'year' => (int) $validated['year'],
                'timeframe' => 'weekly',
                'table_month' => (int) $validated['month'],
            ]);

            \App\Models\User::query()
                ->with('facilities:id')
                ->get()
                ->filter(function (\App\Models\User $u) use ($facilityId) {
                    $role = \App\Support\RoleAccess::normalize($u);
                    if (in_array($role, ['super_admin', 'admin', 'energy_officer'], true)) {
                        return true;
                    }
                    if ($role === 'staff' && $facilityId) {
                        return $u->facilities->contains('id', (int) $facilityId);
                    }
                    return false;
                })
                ->each(function (\App\Models\User $recipient) use ($alertTitle, $alertMessage, $notifType, $targetUrl) {
                    $exists = $recipient->notifications()
                        ->where('type', $notifType)
                        ->where('message', $alertMessage)
                        ->exists();

                    if (! $exists) {
                        $recipient->notifications()->create([
                            'title' => $alertTitle,
                            'message' => $alertMessage,
                            'type' => $notifType,
                            'target_url' => $targetUrl,
                        ]);
                    }
                });
        }
    }

    // Sync monthly aggregate
    $syncService = app(\App\Services\WeeklyReadingSyncService::class);
    $syncResult = $syncService->syncMonthlyAggregate($facilityId, $selectedMeter->id, (int) $validated['year'], (int) $validated['month']);

    $message = $syncResult['is_complete']
        ? "Week {$validated['week_number']} saved! All 4 weeks are now complete and synchronized to the Monthly Record (" . number_format($syncResult['total_kwh'], 2) . " kWh)."
        : "Week {$validated['week_number']} saved (" . number_format($actualKwh, 2) . " kWh). Progress: {$syncResult['weeks_count']}/4 weeks logged.";

    return redirect()->back()->with('success', $message);
})->middleware(['auth', 'verified'])->name('facility-meter-weekly-readings.store');

// Delete weekly meter reading
Route::delete('/modules/facilities/{facility}/weekly-readings/{reading}', function ($facilityId, $readingId) {
    $reading = \App\Models\FacilityMeterWeeklyReading::where('facility_id', $facilityId)
        ->whereKey($readingId)
        ->firstOrFail();

    $meterId = $reading->meter_id;
    $year = $reading->year;
    $month = $reading->month;
    $weekNumber = $reading->week_number;

    $reading->delete();

    // Re-sync monthly aggregate
    $syncService = app(\App\Services\WeeklyReadingSyncService::class);
    $syncService->syncMonthlyAggregate($facilityId, $meterId, $year, $month);

    return redirect()->back()->with('success', "Weekly reading for Week {$weekNumber} has been deleted.");
})->middleware(['auth', 'verified'])->name('facility-meter-weekly-readings.destroy');

Route::middleware(['auth', 'verified'])->group(function () {
    // Engineer Approval Toggle (AJAX)
    Route::post('/modules/facilities/{id}/toggle-engineer-approval', [FacilityController::class, 'toggleEngineerApproval'])->name('modules.facilities.toggle-engineer-approval');

    // Facility modal detail for AJAX
    Route::get('/modules/facilities/{facility}/modal-detail', [FacilityController::class, 'modalDetail'])->name('modules.facilities.modal-detail');
});

