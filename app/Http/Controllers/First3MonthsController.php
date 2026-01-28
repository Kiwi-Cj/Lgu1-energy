<?php
// app/Http/Controllers/First3MonthsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class First3MonthsController extends Controller
{
    public function delete(Request $request, $facility_id, $month_no)
    {
        if (!in_array($month_no, [1,2,3])) {
            return redirect()->back()->with('error', 'Invalid month.');
        }
        $facility = Facility::find($facility_id);
        if (!$facility) {
            return redirect()->back()->with('error', 'Facility not found.');
        }
        $first3mo = DB::table('first3months_data')->where('facility_id', $facility_id)->first();
        if (!$first3mo) {
            return redirect()->back()->with('error', 'No data to delete.');
        }
        $update = [
            'updated_at' => now(),
        ];
        // Set to 0 instead of null to avoid NOT NULL error
        $update['month'.$month_no] = 0;
        DB::table('first3months_data')->where('facility_id', $facility_id)->update($update);
        return redirect()->back()->with('success', 'Month '.$month_no.' deleted!');
    }

    public function create(Request $request)
    {
        $facilityId = $request->query('facility_id');
        $facilityModel = null;
        if ($facilityId) {
            $facilityModel = Facility::find($facilityId);
        }
        return view('modules.facilities.first3months', compact('facilityModel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month_no' => 'required|in:1,2,3',
            'kwh' => 'required|numeric|min:0',
        ]);

        $facilityId = $request->facility_id;
        $monthNo = $request->month_no;
        $kwh = $request->kwh;

        // Fetch existing data if any
        $existing = DB::table('first3months_data')->where('facility_id', $facilityId)->first();
        $data = [
            'month1' => $existing?->month1 ?? 0,
            'month2' => $existing?->month2 ?? 0,
            'month3' => $existing?->month3 ?? 0,
            'updated_at' => now(),
        ];
        $data['month'.$monthNo] = $kwh;
        if (!$existing) {
            $data['facility_id'] = $facilityId;
            $data['created_at'] = now();
        }

        DB::table('first3months_data')->updateOrInsert(
            ['facility_id' => $facilityId],
            $data
        );

        return redirect()->back()->with('success', 'Month '.$monthNo.' kWh saved!');
    }
}
