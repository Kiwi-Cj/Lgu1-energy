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
            // Use first3months_data as baseline if available
            $first3mo = $facility ? \DB::table('first3months_data')->where('facility_id', $facility->id)->first() : null;
            if ($first3mo && isset($first3mo->month1) && isset($first3mo->month2) && isset($first3mo->month3)) {
                $baseline = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
            } else {
                $baseline = $facility->baseline_kwh ?? 0;
            }
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
            $alert = 'Low';
            if ($deviation !== null) {
                switch ($sizeLabel) {
                    case 'Small':
                        if ($deviation > 30) {
                            $alert = 'High';
                        } elseif ($deviation > 15) {
                            $alert = 'Medium';
                        }
                        break;
                    case 'Medium':
                        if ($deviation > 20) {
                            $alert = 'High';
                        } elseif ($deviation > 10) {
                            $alert = 'Medium';
                        }
                        break;
                    case 'Large':
                        if ($deviation > 15) {
                            $alert = 'High';
                        } elseif ($deviation > 5) {
                            $alert = 'Medium';
                        }
                        break;
                    case 'Extra Large':
                        if ($deviation > 10) {
                            $alert = 'High';
                        } elseif ($deviation > 3) {
                            $alert = 'Medium';
                        }
                        break;
                }
            }
            if ($alert === 'High') {
                $record->deviation_percent = $deviation;
                $record->alert_level = $alert;
                $record->size = $sizeLabel;
                $record->date_detected = $record->created_at ? $record->created_at->toDateString() : null;
                return true;
            }
            return false;
        });
        return view('modules.energy-incident.incidents', ['incidents' => $highAlerts]);
    }
}
