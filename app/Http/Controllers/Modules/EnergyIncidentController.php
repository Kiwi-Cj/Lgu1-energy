<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnergyIncident;
use Illuminate\Support\Str;

class EnergyIncidentController extends Controller
{
    public function index()
    {
        // Fetch actual incident records from the energy_incidents table
        // Filter by reporting period: current and previous 5 months
        $monthsRange = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthsRange[] = [
                'year' => $date->year,
                'month' => $date->month
            ];
        }
        $incidents = \App\Models\EnergyIncident::with('facility')
            ->where(function($q) use ($monthsRange) {
                foreach ($monthsRange as $m) {
                    $q->orWhere(function($sub) use ($m) {
                        $sub->whereYear('date_detected', $m['year'])->whereMonth('date_detected', $m['month']);
                    });
                }
            })
            ->orderByDesc('date_detected')
            ->orderByDesc('created_at')
            ->paginate(20);
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-incident.incidents', compact('incidents', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function create()
    {
        $facilities = \App\Models\Facility::all();
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-incident.create', compact('facilities', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'incident_type' => 'required',
            'severity' => 'required',
            'date_detected' => 'required|date',
            'time_detected' => 'required',
            'detected_by' => 'required',
            'current_consumption' => 'required|numeric',
            'baseline_consumption' => 'required|numeric',
            'deviation_percent' => 'required|numeric',
            'threshold_exceeded' => 'required',
            'billing_period' => 'required',
            'description' => 'required',
            'probable_cause' => 'nullable',
            'immediate_action' => 'nullable',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:4096',
            'status' => 'required',
        ]);
        $validated['incident_id'] = 'INC-' . strtoupper(Str::random(8));
        // Handle attachments
        $files = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $files[] = $file->store('incident_attachments', 'public');
            }
        }
        $validated['attachments'] = $files;
        $incident = EnergyIncident::create($validated);
        return redirect()->route('energy-incidents.index')->with('success', 'Incident created!');
    }

    public function show(EnergyIncident $energyIncident)
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-incident.show', compact('energyIncident', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function edit(EnergyIncident $energyIncident)
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-incident.edit', compact('energyIncident', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    public function update(Request $request, EnergyIncident $energyIncident)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'incident_type' => 'required',
            'severity' => 'required',
            'date_detected' => 'required|date',
            'time_detected' => 'required',
            'detected_by' => 'required',
            'current_consumption' => 'required|numeric',
            'baseline_consumption' => 'required|numeric',
            'deviation_percent' => 'required|numeric',
            'threshold_exceeded' => 'required',
            'billing_period' => 'required',
            'description' => 'required',
            'probable_cause' => 'nullable',
            'immediate_action' => 'nullable',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:4096',
            'status' => 'required',
        ]);
        // Handle attachments
        $files = $energyIncident->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $files[] = $file->store('incident_attachments', 'public');
            }
        }
        $validated['attachments'] = $files;
        $energyIncident->update($validated);
        return redirect()->route('energy-incidents.index')->with('success', 'Incident updated!');
    }
    public function history()
    {
        $histories = \App\Models\EnergyIncident::with('facility')
            ->orderByDesc('date_detected')
            ->orderByDesc('created_at')
            ->get();
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;
        return view('modules.energy-incident.history', compact('histories', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }
}
