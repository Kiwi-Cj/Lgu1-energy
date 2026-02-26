<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyProfile;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnergyProfileController extends Controller
{
    private function resolvePrimaryMainMeterId(Request $request, int $facilityId): ?int
    {
        $value = $request->input('primary_meter_id');
        if ($value === null || $value === '') {
            return null;
        }

        $meterId = (int) $value;
        if ($meterId <= 0) {
            return null;
        }

        $meter = FacilityMeter::where('facility_id', $facilityId)
            ->whereKey($meterId)
            ->first();

        if (! $meter) {
            throw ValidationException::withMessages([
                'primary_meter_id' => 'Selected primary main meter does not belong to this facility.',
            ]);
        }

        if (strtolower((string) $meter->meter_type) !== 'main') {
            throw ValidationException::withMessages([
                'primary_meter_id' => 'Only Main Meter can be linked as the primary meter.',
            ]);
        }

        return $meter->id;
    }

    private function hasMainMeters(int $facilityId): bool
    {
        return FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->exists();
    }

    private function energyProfileValidationRules(int $facilityId): array
    {
        return [
            'primary_meter_id' => $this->hasMainMeters($facilityId) ? 'required' : 'nullable',
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'baseline_kwh' => 'required|numeric',
            'main_energy_source' => 'required',
            'backup_power' => 'required',
            'number_of_meters' => 'required|integer',
            'transformer_capacity' => 'nullable',
            'baseline_source' => 'nullable|string',
        ];
    }

    private function applyPrimaryMeterSync(array $validated, int $facilityId): array
    {
        $primaryMeterId = $validated['primary_meter_id'] ?? null;
        if (! $primaryMeterId) {
            return $validated;
        }

        $meter = FacilityMeter::where('facility_id', $facilityId)->whereKey($primaryMeterId)->first();
        if (! $meter) {
            return $validated;
        }

        if (! empty($meter->meter_number)) {
            $validated['electric_meter_no'] = (string) $meter->meter_number;
        }

        $activeMeterCount = FacilityMeter::where('facility_id', $facilityId)->count();
        if ($activeMeterCount > 0) {
            $validated['number_of_meters'] = $activeMeterCount;
        }

        return $validated;
    }

    private function ensureEnergyProfileWriteAccess()
    {
        if (! RoleAccess::can(auth()->user(), 'manage_energy_profile')) {
            abort(403, 'You do not have permission to manage Energy Profiles.');
        }
    }

    private function ensureEnergyProfileDeleteAccess()
    {
        if (! RoleAccess::can(auth()->user(), 'delete_energy_profile')) {
            abort(403, 'You do not have permission to delete Energy Profiles.');
        }
    }

    public function update(Request $request, $facilityId, $profileId)
    {
        $this->ensureEnergyProfileWriteAccess();
        $facilityId = (int) $facilityId;

        $validated = $request->validate($this->energyProfileValidationRules($facilityId), [
            'primary_meter_id.required' => 'Primary Main Meter is required because this facility already has a main meter.',
        ]);
        $validated['primary_meter_id'] = $this->resolvePrimaryMainMeterId($request, $facilityId);
        $validated = $this->applyPrimaryMeterSync($validated, $facilityId);

        $profile = \App\Models\EnergyProfile::findOrFail($profileId);
        $profile->update($validated);
        if (RoleAccess::is(auth()->user(), 'energy_officer') && ! $profile->engineer_approved) {
            $profile->engineer_approved = true;
            $profile->save();
        }

        return redirect()->route('modules.facilities.energy-profile.index', $facilityId)
            ->with('success', 'Energy Profile updated!');
    }
    public function store(Request $request, $facilityId)
    {
        $this->ensureEnergyProfileWriteAccess();
        $facilityId = (int) $facilityId;

        \Log::info('EnergyProfileController@store called', ['facilityId' => $facilityId, 'request' => $request->all()]);
        $validated = $request->validate($this->energyProfileValidationRules($facilityId), [
            'primary_meter_id.required' => 'Primary Main Meter is required because this facility already has a main meter.',
        ]);
        $validated['primary_meter_id'] = $this->resolvePrimaryMainMeterId($request, $facilityId);
        $validated = $this->applyPrimaryMeterSync($validated, $facilityId);

        $validated['facility_id'] = $facilityId;
        \Log::info('EnergyProfileController@store validated', ['validated' => $validated]);

        // Duplicate check: same facility, meter no, or contract account no
        $duplicate = EnergyProfile::where('facility_id', $facilityId)
            ->where(function($q) use ($validated) {
                $q->where('electric_meter_no', $validated['electric_meter_no'])
                  ->orWhere('contract_account_no', $validated['contract_account_no']);
            })
            ->first();
        if ($duplicate) {
            return redirect()->back()->withErrors(['duplicate' => 'Duplicate energy profile for this facility (meter no or contract account no already exists).']);
        }

        $profile = EnergyProfile::create($validated);
        if (RoleAccess::is(auth()->user(), 'energy_officer')) {
            EnergyProfile::whereKey($profile->id)->update(['engineer_approved' => true]);
            $profile->refresh();
        }
        \Log::info('EnergyProfileController@store created', ['profile' => $profile]);

        return redirect()->back()->with('success', 'Energy Profile added!');
    }
    public function destroy($facilityId, $profileId)
    {
        $this->ensureEnergyProfileDeleteAccess();

        $profile = EnergyProfile::findOrFail($profileId);
        $profile->delete();
        return redirect()->route('modules.facilities.show', $facilityId)
            ->with('success', 'Energy profile deleted successfully!');
    }
    /**
     * Toggle engineer approval for an energy profile.
     */
    public function toggleEngineerApproval($facilityId, $profileId)
    {
        $this->ensureEnergyProfileWriteAccess();

        $profile = EnergyProfile::findOrFail($profileId);
        $profile->engineer_approved = !$profile->engineer_approved;
        $profile->save();
        return redirect()->back()->with('success', 'Engineer approval status updated.');
    }
}


