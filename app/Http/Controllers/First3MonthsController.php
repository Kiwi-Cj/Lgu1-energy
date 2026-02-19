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
        // first3months_data table removed; nothing to delete
        return redirect()->back()->with('error', 'No data to delete.');
    }

    public function create(Request $request)
    {
        // first3months_data table removed; nothing to create
        abort(404);
    }

    public function store(Request $request)
    {
        // first3months_data table removed; nothing to store
        abort(404);
    }
}
