<?php
namespace App\Http\Controllers;

        use Illuminate\Http\Request;
        use App\Models\EnergyIncidentHistory;

        class EnergyIncidentHistoryController extends Controller
        {
            public function indexHighAlerts()
            {
                return redirect()->route('modules.energy-incident.history');
            }

            public function log(Request $request)
            {
                $validated = $request->validate([
                    'energy_record_id' => 'required|integer|exists:energy_records,id',
                    'deviation' => 'required|numeric',
                ]);
                $incident = EnergyIncidentHistory::logHighAlert($validated['energy_record_id'], $validated['deviation']);
                return response()->json(['success' => true, 'incident' => $incident]);
            }
        }
