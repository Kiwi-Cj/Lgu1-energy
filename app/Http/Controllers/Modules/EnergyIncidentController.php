<?php
namespace App\Http\Controllers\Modules;

use App\Exports\EnergyIncidentReportExport;
use App\Http\Controllers\Controller;
use App\Models\EnergyIncident;
use App\Services\IncidentNotificationService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EnergyIncidentController extends Controller
{
    public function index(Request $request)
    {
        [$incidentQuery, $filters, $role, $user] = $this->buildActiveIncidentQuery($request);

        $incidents = $incidentQuery
            ->orderByDesc('energy_incidents.year')
            ->orderByDesc('energy_incidents.month')
            ->orderByDesc('energy_incidents.date_detected')
            ->orderByDesc('energy_incidents.created_at')
            ->paginate(20)
            ->withQueryString();

        $incidents->setCollection($this->withSeverityLabels($incidents->getCollection()));
        $this->syncIncidentNotifications($incidents->getCollection());
        $yearOptions = $this->incidentYearOptions($role, $user);

        return view('modules.energy-incident.incidents', compact(
            'incidents',
            'role',
            'user',
            'filters',
            'yearOptions'
        ));
    }

    public function export(Request $request)
    {
        [$incidentQuery, $filters] = $this->buildActiveIncidentQuery($request);

        $incidents = $this->withSeverityLabels(
            $incidentQuery
                ->orderByDesc('energy_incidents.year')
                ->orderByDesc('energy_incidents.month')
                ->orderByDesc('energy_incidents.date_detected')
                ->orderByDesc('energy_incidents.created_at')
                ->get()
        );

        $incidentRows = $incidents->map(fn (EnergyIncident $incident) => $this->exportRow($incident));
        $suffix = collect([$filters['year'] ?: null, $filters['month'] ? str_pad((string) $filters['month'], 2, '0', STR_PAD_LEFT) : null, $filters['date_detected'] ?: null])
            ->filter()
            ->implode('-');
        $filename = 'incident_report' . ($suffix !== '' ? '_' . $suffix : '') . '.xlsx';

        return Excel::download(new EnergyIncidentReportExport($incidentRows), $filename);
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
        $role = RoleAccess::normalize($user);

        return view('modules.energy-incident.history', compact('histories', 'role', 'user'));
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

    private function buildActiveIncidentQuery(Request $request): array
    {
        $filters = $this->incidentFilters($request);

        $user = auth()->user();
        $role = RoleAccess::normalize($user);
        $facilityIds = ($role === 'staff' && $user)
            ? $user->facilities()->pluck('facilities.id')->all()
            : null;

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
            ]);

        if ($role === 'staff') {
            if (is_array($facilityIds) && count($facilityIds) > 0) {
                $incidentQuery->whereIn('energy_incidents.facility_id', $facilityIds);
            } else {
                $incidentQuery->whereRaw('1 = 0');
            }
        }

        $incidentQuery->where(function ($query) {
            $query->where('energy_incidents.status', 'not like', '%resolved%')
                ->where('energy_incidents.status', 'not like', '%closed%');
        });

        if ($filters['year'] > 0) {
            $incidentQuery->where('energy_incidents.year', $filters['year']);
        }

        if ($filters['month'] > 0) {
            $incidentQuery->where('energy_incidents.month', $filters['month']);
        }

        if ($filters['date_detected'] !== '') {
            $incidentQuery->whereDate('energy_incidents.date_detected', $filters['date_detected']);
        }

        if ($filters['year'] === 0 && $filters['month'] === 0 && $filters['date_detected'] === '') {
            $this->applyRecentIncidentWindow($incidentQuery);
        }

        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $incidentQuery->where(function ($query) use ($like, $severityExpr) {
                $query->where('energy_incidents.description', 'like', $like)
                    ->orWhere('energy_incidents.status', 'like', $like)
                    ->orWhere('f.name', 'like', $like)
                    ->orWhereRaw("CONCAT(LPAD(energy_incidents.month, 2, '0'), '/', energy_incidents.year) LIKE ?", [$like])
                    ->orWhereRaw("{$severityExpr} LIKE ?", [strtolower($like)]);
            });
        }

        if ($filters['status'] !== 'all') {
            if ($filters['status'] === 'ongoing') {
                $incidentQuery->where('energy_incidents.status', 'like', '%ongoing%');
            } elseif ($filters['status'] === 'pending') {
                $incidentQuery->where('energy_incidents.status', 'like', '%pending%');
            } elseif ($filters['status'] === 'open') {
                $incidentQuery->where('energy_incidents.status', 'like', '%open%');
            }
        }

        if (in_array($filters['severity'], ['critical', 'very-high'], true)) {
            $incidentQuery->whereRaw("{$severityExpr} = ?", [$filters['severity']]);
        }

        return [$incidentQuery, $filters, $role, $user];
    }

    private function incidentFilters(Request $request): array
    {
        $status = strtolower((string) $request->query('status', 'all'));
        $severity = strtolower((string) $request->query('severity', 'all'));
        $year = (int) $request->query('year', 0);
        $month = (int) $request->query('month', 0);
        $dateDetected = trim((string) $request->query('date_detected', ''));

        if (! in_array($status, ['all', 'open', 'pending', 'ongoing'], true)) {
            $status = 'all';
        }

        if (! in_array($severity, ['all', 'critical', 'very-high'], true)) {
            $severity = 'all';
        }

        if ($year < 2000 || $year > 2100) {
            $year = 0;
        }

        if ($month < 1 || $month > 12) {
            $month = 0;
        }

        try {
            $dateDetected = $dateDetected !== ''
                ? Carbon::parse($dateDetected)->toDateString()
                : '';
        } catch (\Throwable) {
            $dateDetected = '';
        }

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => $status,
            'severity' => $severity,
            'year' => $year,
            'month' => $month,
            'date_detected' => $dateDetected,
        ];
    }

    private function applyRecentIncidentWindow(Builder $incidentQuery): void
    {
        $monthsRange = [];
        $periodCutoff = now()->subMonths(5)->startOfMonth()->toDateString();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthsRange[] = [
                'year' => (int) $date->year,
                'month' => (int) $date->month,
            ];
        }

        $incidentQuery->where(function ($query) use ($monthsRange, $periodCutoff) {
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
    }

    private function withSeverityLabels($incidents)
    {
        return $incidents->transform(function (EnergyIncident $incident) {
            $severityKey = strtolower((string) ($incident->getAttribute('severity_key') ?: 'normal'));
            $incident->setAttribute('severity_key', $severityKey);
            $incident->setAttribute('severity_label', $this->severityLabel($severityKey));
            return $incident;
        });
    }

    private function syncIncidentNotifications($incidents): void
    {
        $notifier = app(IncidentNotificationService::class);

        $incidents->each(function (EnergyIncident $incident) use ($notifier) {
            try {
                $notifier->notify($incident);
            } catch (\Throwable) {
                // The incident list should remain available even if notification sync fails.
            }
        });
    }

    private function incidentYearOptions(string $role, $user)
    {
        $query = EnergyIncident::query()
            ->whereNotNull('year')
            ->where(function ($query) {
                $query->where('status', 'not like', '%resolved%')
                    ->where('status', 'not like', '%closed%');
            });

        if ($role === 'staff') {
            $facilityIds = $user ? $user->facilities()->pluck('facilities.id')->all() : [];
            if (count($facilityIds) > 0) {
                $query->whereIn('facility_id', $facilityIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->filter(fn ($year) => $year > 0)
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function exportRow(EnergyIncident $incident): array
    {
        $monthNum = (int) ($incident->month ?? 0);
        $period = $monthNum >= 1 && $monthNum <= 12 && $incident->year
            ? date('M Y', mktime(0, 0, 0, $monthNum, 1, (int) $incident->year))
            : '-';

        $description = trim((string) ($incident->description ?? ''));

        return [
            'facility' => $incident->facility->name ?? 'Unknown Facility',
            'period' => $period,
            'date_detected' => $incident->date_detected ? $incident->date_detected->format('M d, Y') : '',
            'status' => $incident->status ?? 'Open',
            'severity' => $incident->severity_label,
            'deviation' => $incident->deviation_percent !== null ? number_format((float) $incident->deviation_percent, 2) . '%' : '',
            'description' => $description,
            'probable_cause' => is_array($incident->probable_cause ?? null)
                ? implode(', ', $incident->probable_cause)
                : (string) ($incident->probable_cause ?? ''),
            'immediate_action' => (string) ($incident->immediate_action ?? ''),
            'resolution' => (string) ($incident->resolution_summary ?? ''),
            'preventive_recommendation' => (string) ($incident->preventive_recommendation ?? ''),
        ];
    }
}
