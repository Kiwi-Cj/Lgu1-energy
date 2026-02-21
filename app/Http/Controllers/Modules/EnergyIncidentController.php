<?php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnergyIncident;

class EnergyIncidentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = strtolower((string) $request->query('status', 'all'));
        $severity = strtolower((string) $request->query('severity', 'all'));

        $user = auth()->user();
        $role = strtolower((string) ($user->role ?? ''));
        $facilityIds = ($role === 'staff' && $user)
            ? $user->facilities()->pluck('facilities.id')->all()
            : null;

        $monthsRange = [];
        $periodCutoff = now()->subMonths(5)->startOfMonth()->toDateString();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthsRange[] = [
                'year' => (int) $date->year,
                'month' => (int) $date->month,
            ];
        }

        $deviationExpr = 'COALESCE(energy_incidents.deviation_percent, 0)';
        $baselineExpr = 'COALESCE(er.baseline_kwh, f.baseline_kwh, 0)';
        $severityExpr = $this->severityCaseExpression($deviationExpr, $baselineExpr);

        $incidentQuery = EnergyIncident::query()
            ->from('energy_incidents')
            ->leftJoin('energy_records as er', 'er.id', '=', 'energy_incidents.energy_record_id')
            ->leftJoin('facilities as f', 'f.id', '=', 'energy_incidents.facility_id')
            ->select('energy_incidents.*')
            ->selectRaw("{$severityExpr} as severity_key")
            ->with([
                'facility:id,name,baseline_kwh',
                'energyRecord:id,facility_id,baseline_kwh,alert',
            ])
            ->where(function ($query) use ($monthsRange, $periodCutoff) {
                foreach ($monthsRange as $m) {
                    $query->orWhere(function ($subQuery) use ($m) {
                        $subQuery->where('energy_incidents.year', $m['year'])
                            ->where('energy_incidents.month', $m['month']);
                    });
                }
                $query->orWhere(function ($subQuery) use ($periodCutoff) {
                    $subQuery->whereNull('energy_incidents.month')
                        ->whereNull('energy_incidents.year')
                        ->whereDate('energy_incidents.date_detected', '>=', $periodCutoff);
                });
            });

        if ($role === 'staff') {
            if (is_array($facilityIds) && count($facilityIds) > 0) {
                $incidentQuery->whereIn('energy_incidents.facility_id', $facilityIds);
            } else {
                $incidentQuery->whereRaw('1 = 0');
            }
        }

        // Incident records page shows active incidents only.
        $incidentQuery->where(function ($query) {
            $query->where('energy_incidents.status', 'not like', '%resolved%')
                ->where('energy_incidents.status', 'not like', '%closed%');
        });

        if ($q !== '') {
            $like = '%' . $q . '%';
            $incidentQuery->where(function ($query) use ($like, $severityExpr) {
                $query->where('energy_incidents.description', 'like', $like)
                    ->orWhere('energy_incidents.status', 'like', $like)
                    ->orWhere('f.name', 'like', $like)
                    ->orWhereRaw("CONCAT(LPAD(energy_incidents.month, 2, '0'), '/', energy_incidents.year) LIKE ?", [$like])
                    ->orWhereRaw("{$severityExpr} LIKE ?", [strtolower($like)]);
            });
        }

        if ($status !== 'all') {
            if ($status === 'ongoing') {
                $incidentQuery->where('energy_incidents.status', 'like', '%ongoing%');
            } elseif ($status === 'pending') {
                $incidentQuery->where('energy_incidents.status', 'like', '%pending%');
            } elseif ($status === 'open') {
                $incidentQuery->where('energy_incidents.status', 'like', '%open%');
            }
        }

        $allowedSeverities = ['critical', 'very-high'];
        if (in_array($severity, $allowedSeverities, true)) {
            $incidentQuery->whereRaw("{$severityExpr} = ?", [$severity]);
        }

        $incidents = $incidentQuery
            ->orderByDesc('energy_incidents.year')
            ->orderByDesc('energy_incidents.month')
            ->orderByDesc('energy_incidents.date_detected')
            ->orderByDesc('energy_incidents.created_at')
            ->paginate(20)
            ->withQueryString();

        $incidents->getCollection()->transform(function (EnergyIncident $incident) {
            $severityKey = strtolower((string) ($incident->getAttribute('severity_key') ?: 'normal'));
            $incident->setAttribute('severity_key', $severityKey);
            $incident->setAttribute('severity_label', $this->severityLabel($severityKey));
            return $incident;
        });

        $filters = [
            'q' => $q,
            'status' => $status,
            'severity' => $severity,
        ];

        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        return view('modules.energy-incident.incidents', compact(
            'incidents',
            'role',
            'user',
            'notifications',
            'unreadNotifCount',
            'filters'
        ));
    }

    public function create()
    {
        return redirect()->route('energy-incidents.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'energy_record_id' => 'nullable|exists:energy_records,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'deviation_percent' => 'nullable|numeric',
            'description' => 'required|string|max:1000',
            'status' => 'required|string|max:50',
            'date_detected' => 'required|date',
        ]);

        $validated['created_by'] = auth()->id();
        EnergyIncident::create($validated);

        return redirect()->route('energy-incidents.index')->with('success', 'Incident created!');
    }

    public function show(EnergyIncident $energyIncident)
    {
        return redirect()->route('energy-incidents.index');
    }

    public function edit(EnergyIncident $energyIncident)
    {
        return redirect()->route('energy-incidents.index');
    }

    public function update(Request $request, EnergyIncident $energyIncident)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'date_detected' => 'nullable|date',
            'resolved_at' => 'nullable|date',
        ]);

        $energyIncident->update($validated);

        return redirect()->route('energy-incidents.index')->with('success', 'Incident updated!');
    }

    public function history()
    {
        $histories = EnergyIncident::with([
                'facility:id,name,baseline_kwh',
                'energyRecord:id,facility_id,baseline_kwh,alert',
            ])
            ->where(function ($query) {
                $query->where('status', 'like', '%resolved%')
                    ->orWhere('status', 'like', '%closed%');
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('resolved_at')
            ->orderByDesc('date_detected')
            ->orderByDesc('created_at')
            ->get();

        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        return view('modules.energy-incident.history', compact('histories', 'role', 'user', 'notifications', 'unreadNotifCount'));
    }

    private function severityCaseExpression(string $deviationExpr, string $baselineExpr): string
    {
        return "
            CASE
                WHEN {$baselineExpr} <= 0 AND {$deviationExpr} > 60 THEN 'critical'
                WHEN {$baselineExpr} <= 0 AND {$deviationExpr} > 40 THEN 'very-high'
                WHEN {$baselineExpr} <= 0 AND {$deviationExpr} > 20 THEN 'high'
                WHEN {$baselineExpr} <= 0 AND {$deviationExpr} > 10 THEN 'warning'
                WHEN {$baselineExpr} <= 1000 AND {$deviationExpr} > 80 THEN 'critical'
                WHEN {$baselineExpr} <= 1000 AND {$deviationExpr} > 50 THEN 'very-high'
                WHEN {$baselineExpr} <= 1000 AND {$deviationExpr} > 30 THEN 'high'
                WHEN {$baselineExpr} <= 1000 AND {$deviationExpr} > 15 THEN 'warning'
                WHEN {$baselineExpr} <= 3000 AND {$deviationExpr} > 60 THEN 'critical'
                WHEN {$baselineExpr} <= 3000 AND {$deviationExpr} > 40 THEN 'very-high'
                WHEN {$baselineExpr} <= 3000 AND {$deviationExpr} > 20 THEN 'high'
                WHEN {$baselineExpr} <= 3000 AND {$deviationExpr} > 10 THEN 'warning'
                WHEN {$baselineExpr} <= 10000 AND {$deviationExpr} > 30 THEN 'critical'
                WHEN {$baselineExpr} <= 10000 AND {$deviationExpr} > 20 THEN 'very-high'
                WHEN {$baselineExpr} <= 10000 AND {$deviationExpr} > 12 THEN 'high'
                WHEN {$baselineExpr} <= 10000 AND {$deviationExpr} > 5 THEN 'warning'
                WHEN {$deviationExpr} > 20 THEN 'critical'
                WHEN {$deviationExpr} > 12 THEN 'very-high'
                WHEN {$deviationExpr} > 7 THEN 'high'
                WHEN {$deviationExpr} > 3 THEN 'warning'
                ELSE 'normal'
            END
        ";
    }

    private function severityLabel(string $severityKey): string
    {
        return match ($severityKey) {
            'critical' => 'Critical',
            'very-high' => 'Very High',
            'high' => 'High',
            'warning' => 'Warning',
            default => 'Normal',
        };
    }
}
