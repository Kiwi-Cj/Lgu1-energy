<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnergyProfile;

class EnergyProfileController extends Controller
{
    public function update(Request $request, $facilityId, $profileId)
    {
        $validated = $request->validate([
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'average_monthly_kwh' => 'required|numeric',
            'main_energy_source' => 'required',
            'backup_power' => 'required',
            'number_of_meters' => 'required|integer',
            'transformer_capacity' => 'nullable',
        ]);

        $profile = \App\Models\EnergyProfile::findOrFail($profileId);
        $profile->update($validated);

        return redirect()->route('modules.facilities.energy-profile.index', $facilityId)
            ->with('success', 'Energy Profile updated!');
    }
    public function store(Request $request, $facilityId)
    {
        \Log::info('EnergyProfileController@store called', ['facilityId' => $facilityId, 'request' => $request->all()]);
        $validated = $request->validate([
            'electric_meter_no' => 'required',
            'utility_provider' => 'required',
            'contract_account_no' => 'required',
            'average_monthly_kwh' => 'required|numeric',
            'main_energy_source' => 'required',
            'backup_power' => 'required',
            'number_of_meters' => 'required|integer',
            'transformer_capacity' => 'nullable',
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
        \Log::info('EnergyProfileController@store created', ['profile' => $profile]);

        return redirect()->back()->with('success', 'Energy Profile added!');
    }
    public function destroy($facilityId, $profileId)
    {
        $profile = EnergyProfile::findOrFail($profileId);
        $profile->delete();
        return redirect()->route('modules.facilities.show', $facilityId)
            ->with('success', 'Energy profile deleted successfully!');
    }
}
