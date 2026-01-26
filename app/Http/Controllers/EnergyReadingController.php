<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnergyReading;
use App\Models\Facility;
use App\Services\BaselineService;

class EnergyReadingController extends Controller
{
    public function store(Request $request)
    {
        $reading = EnergyReading::create($request->all());

        $facility = Facility::find($reading->facility_id);

        if ($facility && $facility->baseline_status === 'collecting') {
            BaselineService::process($facility);
        }

        return back()->with('success', 'Energy data saved');
    }
}
