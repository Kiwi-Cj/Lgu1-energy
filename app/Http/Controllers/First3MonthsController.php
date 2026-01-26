<?php
// app/Http/Controllers/First3MonthsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;

class First3MonthsController extends Controller
{
    public function create()
    {
        $facilities = Facility::orderBy('name')->get();
        return view('modules.facilities.first3months', compact('facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'month1' => 'required|numeric|min:0',
            'month2' => 'required|numeric|min:0',
            'month3' => 'required|numeric|min:0',
        ]);

        // Store the 3 months data in a new table (first3months_data)
        DB::table('first3months_data')->updateOrInsert(
            ['facility_id' => $request->facility_id],
            [
                'month1' => $request->month1,
                'month2' => $request->month2,
                'month3' => $request->month3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Redirect to facility index with a query param to trigger the modal for this facility
        return redirect()->route('modules.facilities.index', ['show3mo' => $request->facility_id])->with('success', 'First 3 months data saved!');
    }
}
