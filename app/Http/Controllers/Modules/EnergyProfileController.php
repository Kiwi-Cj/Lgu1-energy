<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyProfile;
use App\Support\RoleAccess;
use Illuminate\Http\Request;

class EnergyProfileController extends Controller
{
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

        $validated = $request->validate([
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'baseline_kwh' => 'required|numeric',
            'main_energy_source' => 'required',
            'backup_power' => 'required',
            'number_of_meters' => 'required|integer',
            'transformer_capacity' => 'nullable',
            'baseline_source' => 'nullable|string',
        ]);

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

        \Log::info('EnergyProfileController@store called', ['facilityId' => $facilityId, 'request' => $request->all()]);
        $validated = $request->validate([
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'baseline_kwh' => 'required|numeric',
            'main_energy_source' => 'required',
            'backup_power' => 'required',
            'number_of_meters' => 'required|integer',
            'transformer_capacity' => 'nullable',
            'baseline_source' => 'nullable|string',
        ]);

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


