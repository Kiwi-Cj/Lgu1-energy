<?php

namespace App\Http\Controllers;

use App\Models\EnergyIncident;
use Illuminate\Http\Request;

class EnergyIncidentController extends Controller
{
    public function index(Request $request)
    {
        // Get all high alert records from EnergyRecord
        $records = \App\Models\EnergyRecord::with('facility')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        // ...existing code...
        return view('modules.energy-incidents.index', [
            'highAlerts' => collect(), // No high alerts, first3months_data removed
        ]);
    }
}
