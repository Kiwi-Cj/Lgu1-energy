<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;

class EnergyMonitoringController extends Controller
{
    public function index()
    {
        $totalKwh = EnergyRecord::sum('kwh_consumed');
        $peakLoad = EnergyRecord::max('kwh_consumed');
        $activeFacilities = Facility::where('status', 1)->count();
        $recentUsages = EnergyRecord::with('facility')->orderByDesc('year')->orderByDesc('month')->limit(10)->get();
        return view('modules.energy.index', compact('totalKwh', 'peakLoad', 'activeFacilities', 'recentUsages'));
    }

    public function show($id)
    {
        $record = \App\Models\EnergyRecord::with('facility')->findOrFail($id);
        return view('modules.energy.show', compact('record'));
    }

    public function create()
    {
        $facilities = \App\Models\Facility::all();
        return view('modules.energy.create', compact('facilities'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month' => 'required',
            'year' => 'required',
            'kwh_consumed' => 'required|numeric',
            'meralco_bill' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        // Prevent duplicate entry for the same facility, month, and year
        $exists = \App\Models\EnergyRecord::where('facility_id', $validated['facility_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withInput()->withErrors(['duplicate' => 'An energy record for this facility and month/year already exists.']);
        }

        $data = $validated;
        if ($request->hasFile('meralco_bill')) {
            $path = $request->file('meralco_bill')->store('meralco_bills', 'public');
            $data['meralco_bill'] = $path;
        }

        \App\Models\EnergyRecord::create($data);
        return redirect()->route('modules.energy.index')->with('success', 'Energy record added!');
    }
}
