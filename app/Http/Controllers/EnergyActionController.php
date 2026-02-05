<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\EnergyRecord;
use App\Models\EnergyAction;
use App\Models\MaintenanceHistory;

class EnergyActionController extends Controller
{
    public function index(Request $request)
    {
        $facilityId = $request->query('facility');
        if ($facilityId) {
            $actions = \App\Models\EnergyAction::with('facility')->where('facility_id', $facilityId)->orderByDesc('created_at')->get();
        } else {
            $actions = \App\Models\EnergyAction::with('facility')->orderByDesc('created_at')->get();
        }
        return view('modules.energy-actions.index', compact('actions'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'action_type' => 'required',
            'description' => 'required',
            'priority' => 'required',
            'target_date' => 'required|date',
        ]);

        // Add context fields
        $data['risk_score'] = $request->input('risk_score');
        $data['alert_level'] = $request->input('alertLevel');
        $data['trigger_reason'] = $request->input('triggerReason');
        $data['current_kwh'] = $request->input('currentKwh');
        $data['baseline_kwh'] = $request->input('baseline');
        $data['deviation'] = $request->input('deviation');

        $data['status'] = 'Active';

        $action = \App\Models\EnergyAction::create($data);

        // Optionally, flag facility as UNDER ACTION
        $facility = \App\Models\Facility::find($data['facility_id']);
        if ($facility) {
            $facility->status = 'UNDER ACTION';
            $facility->save();
        }

        return redirect('/modules/energy-monitoring/index')->with('success', 'Energy Action created and facility flagged as UNDER ACTION.');
    }
    public function create(Request $request)
    {
        $facilityId = $request->query('facility');
        $facility = Facility::findOrFail($facilityId);
        $latestRecord = $facility->energyRecords()->orderByDesc('year')->orderByDesc('month')->first();
        $baseline = $facility->baseline_kwh;
        $currentKwh = $latestRecord ? $latestRecord->actual_kwh : null;
        $deviation = ($baseline && $currentKwh) ? round((($currentKwh - $baseline) / $baseline) * 100, 2) : null;
        // Alert level logic
        if ($deviation === null) {
            $alertLevel = '-';
        } elseif ($deviation > 20) {
            $alertLevel = 'HIGH';
        } elseif ($deviation > 10) {
            $alertLevel = 'MEDIUM';
        } else {
            $alertLevel = 'LOW';
        }
        // Risk score (simple example)
        $riskScore = $deviation !== null ? min(100, max(0, abs($deviation))) : null;
        // Trigger reason
        $triggerReason = null;
        if ($deviation !== null && $deviation > 20) {
            $triggerReason = 'High energy deviation detected (' . $deviation . '% above baseline)';
        } elseif ($deviation !== null && $deviation < -10) {
            $triggerReason = 'Unusually low consumption';
        } else {
            $triggerReason = 'Normal monitoring';
        }
        return view('modules.energy-monitoring.energy-actions.create', [
            'facility' => $facility,
            'currentKwh' => $currentKwh,
            'baseline' => $baseline,
            'deviation' => $deviation,
            'alertLevel' => $alertLevel,
            'riskScore' => $riskScore,
            'triggerReason' => $triggerReason,
        ]);
    }
}
