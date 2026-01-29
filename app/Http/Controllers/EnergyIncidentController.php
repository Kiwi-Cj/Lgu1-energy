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

        $highAlerts = $records->filter(function ($record) {
            $facility = $record->facility;
            $baseline = $facility->baseline_kwh ?? 0;
            $actual = $record->actual_kwh;
            if ($baseline <= 0) return false;
            $deviation = round((($actual - $baseline) / $baseline) * 100, 2);
            $sizeLabel = '';
            if ($baseline <= 1000) {
                $sizeLabel = 'Small';
            } elseif ($baseline <= 3000) {
                $sizeLabel = 'Medium';
            } elseif ($baseline <= 10000) {
                $sizeLabel = 'Large';
            } else {
                $sizeLabel = 'Extra Large';
            }
            if (
                ($sizeLabel === 'Small' && $deviation > 30) ||
                ($sizeLabel === 'Medium' && $deviation > 20) ||
                (($sizeLabel === 'Large' || $sizeLabel === 'Extra Large') && $deviation > 15)
            ) {
                // Attach extra info for the view
                $record->deviation_percent = $deviation;
                $record->alert_level = 'High';
                $record->date_detected = $record->created_at ? $record->created_at->toDateString() : null;
                return true;
            }
            return false;
        });
        return view('modules.energy-incident.incidents', ['incidents' => $highAlerts]);
    }
}
