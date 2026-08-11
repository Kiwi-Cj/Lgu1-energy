<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyProfile;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Services\MainMeterBaselineEstablishmentService;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EnergyProfileController extends Controller
{
    public function __construct(private readonly MainMeterBaselineEstablishmentService $baselineEstablishment)
    {
    }

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
            ->whereNotNull('approved_at')
            ->whereKey($meterId)
            ->first();

        if (! $meter) {
            throw ValidationException::withMessages([
                'primary_meter_id' => 'Selected primary main meter is not approved or does not belong to this facility.',
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
            ->whereNotNull('approved_at')
            ->exists();
    }

    private function energyProfileValidationRules(int $facilityId): array
    {
        return [
            'primary_meter_id' => $this->hasMainMeters($facilityId) ? 'required' : 'nullable',
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'baseline_kwh' => 'nullable|numeric|min:0',
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

        $meter = FacilityMeter::where('facility_id', $facilityId)
            ->whereNotNull('approved_at')
            ->whereKey($primaryMeterId)
            ->first();
        if (! $meter) {
            return $validated;
        }

        if (! empty($meter->meter_number)) {
            $validated['electric_meter_no'] = (string) $meter->meter_number;
        }

        if (is_numeric($meter->baseline_kwh)) {
            $validated['baseline_kwh'] = round((float) $meter->baseline_kwh, 2);
            $validated['baseline_source'] = 'main_meter';
        }

        $activeMainMeterCount = FacilityMeter::where('facility_id', $facilityId)
            ->where('meter_type', 'main')
            ->where('status', 'active')
            ->whereNotNull('approved_at')
            ->count();
        if ($activeMainMeterCount > 0) {
            $validated['number_of_meters'] = $activeMainMeterCount;
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

    private function ensureEnergyProfileApprovalAccess()
    {
        if (! RoleAccess::can(auth()->user(), 'approve_energy_profile')) {
            abort(403, 'Only super admin, admin, or engineer can approve Energy Profiles.');
        }
    }

    public function update(Request $request, $facilityId, $profileId)
    {
        $this->ensureEnergyProfileWriteAccess();
        $facilityId = (int) $facilityId;
        Facility::findOrFail($facilityId);
        $profile = EnergyProfile::where('facility_id', $facilityId)->findOrFail($profileId);

        $validated = $request->validate($this->energyProfileValidationRules($facilityId), [
            'primary_meter_id.required' => 'Primary Main Meter is required because this facility already has a main meter.',
        ]);
        $validated['primary_meter_id'] = $this->resolvePrimaryMainMeterId($request, $facilityId);
        $validated = $this->applyPrimaryMeterSync($validated, $facilityId);

        $profile->update($validated);

        return redirect()->route('modules.facilities.energy-profile.index', $facilityId)
            ->with('success', 'Energy Profile updated!');
    }
    public function store(Request $request, $facilityId)
    {
        $this->ensureEnergyProfileWriteAccess();
        $facilityId = (int) $facilityId;

        Facility::findOrFail($facilityId);

        if (EnergyProfile::where('facility_id', $facilityId)->exists()) {
            return redirect()
                ->route('modules.facilities.energy-profile.index', $facilityId)
                ->with('error', 'This facility already has an Energy Profile. Use Edit Profile to update it.');
        }

        \Log::info('EnergyProfileController@store called', ['facilityId' => $facilityId, 'request' => $request->all()]);
        $validated = $request->validate($this->energyProfileValidationRules($facilityId), [
            'primary_meter_id.required' => 'Primary Main Meter is required because this facility already has a main meter.',
        ]);
        $validated['primary_meter_id'] = $this->resolvePrimaryMainMeterId($request, $facilityId);
        $validated = $this->applyPrimaryMeterSync($validated, $facilityId);

        $validated['facility_id'] = $facilityId;
        \Log::info('EnergyProfileController@store validated', ['validated' => $validated]);

        try {
            $profile = EnergyProfile::create($validated);
        } catch (QueryException $exception) {
            if (EnergyProfile::where('facility_id', $facilityId)->exists()) {
                return redirect()
                    ->route('modules.facilities.energy-profile.index', $facilityId)
                    ->with('error', 'This facility already has an Energy Profile. Use Edit Profile to update it.');
            }

            throw $exception;
        }
        \Log::info('EnergyProfileController@store created', ['profile' => $profile]);

        return redirect()->back()->with('success', 'Energy Profile added!');
    }
    public function destroy($facilityId, $profileId)
    {
        $this->ensureEnergyProfileDeleteAccess();

        $profile = EnergyProfile::where('facility_id', (int) $facilityId)->findOrFail($profileId);
        $profile->delete();
        return redirect()->route('modules.facilities.show', $facilityId)
            ->with('success', 'Energy profile deleted successfully!');
    }
    /**
     * Toggle engineer approval for an energy profile.
     */
    public function toggleEngineerApproval($facilityId, $profileId)
    {
        $this->ensureEnergyProfileApprovalAccess();

        $profile = EnergyProfile::where('facility_id', (int) $facilityId)->findOrFail($profileId);
        $profile->engineer_approved = !$profile->engineer_approved;
        $profile->save();
        return redirect()->back()->with('success', 'Engineer approval status updated.');
    }

    public function establishBaseline(Request $request, $facilityId, $meterId)
    {
        $this->ensureEnergyProfileApprovalAccess();

        $facility = Facility::findOrFail((int) $facilityId);
        $meter = FacilityMeter::query()
            ->where('facility_id', $facility->id)
            ->where('meter_type', 'main')
            ->findOrFail((int) $meterId);

        $validated = $request->validate([
            'baseline_months' => ['required', 'integer', 'min:3', 'max:6'],
        ]);
        $result = $this->baselineEstablishment->establish($meter, (int) $validated['baseline_months']);

        DB::transaction(function () use ($facility, $meter, $result) {
            $meter->update(['baseline_kwh' => $result['baseline_kwh']]);

            $profile = EnergyProfile::query()
                ->where('facility_id', $facility->id)
                ->where(function ($query) use ($meter) {
                    $query->where('primary_meter_id', $meter->id)
                        ->orWhereNull('primary_meter_id');
                })
                ->latest('id')
                ->first();

            if ($profile) {
                $profile->update([
                    'baseline_kwh' => $result['baseline_kwh'],
                    'baseline_locked' => true,
                    'baseline_source' => 'computed_'.$result['months'].'_month_average',
                    'engineer_approved' => true,
                ]);
            }
        });

        return redirect()
            ->route('modules.facilities.energy-profile.index', $facility->id)
            ->with('success', sprintf(
                '%d-month baseline approved at %s kWh using %s to %s readings.',
                $result['months'],
                number_format($result['baseline_kwh'], 2),
                $result['start_period'],
                $result['end_period'],
            ));
    }
}


