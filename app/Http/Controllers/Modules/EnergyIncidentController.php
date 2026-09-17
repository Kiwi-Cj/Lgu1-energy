<?php
namespace App\Http\Controllers\Modules;

use App\Exports\EnergyIncidentReportExport;
use App\Http\Controllers\Controller;
use App\Models\EnergyIncident;
use App\Models\Facility;
use App\Models\Maintenance;
use App\Services\IncidentNotificationService;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class EnergyIncidentController extends Controller
{
    public function index(Request $request)
    {
        [$incidentQuery, $filters, $role, $user] = $this->buildActiveIncidentQuery($request);

        $incidents = $incidentQuery
            ->orderByRaw("CASE severity_key WHEN 'critical' THEN 1 WHEN 'very-high' THEN 2 WHEN 'high' THEN 3 WHEN 'warning' THEN 4 ELSE 5 END")
            ->orderBy('energy_incidents.date_detected')
            ->orderByDesc('energy_incidents.year')
            ->orderByDesc('energy_incidents.month')
            ->orderByDesc('energy_incidents.created_at')
            ->paginate(20)
            ->withQueryString();

        $incidents->setCollection($this->withSeverityLabels($incidents->getCollection()));
        $this->syncIncidentNotifications($incidents->getCollection());
        $yearOptions = $this->incidentYearOptions($role, $user);
        $reportFacilities = $role === 'staff'
            ? $user->facilities()->with(['submeters' => fn ($q) => $q->orderBy('submeter_name')])->orderBy('name')->get(['facilities.id', 'facilities.name'])
            : Facility::query()->with(['submeters' => fn ($q) => $q->orderBy('submeter_name')])->orderBy('name')->get(['id', 'name']);
        $selectableCategoryKeys = [
            'critical_usage_spike',
            'peak_demand_surge',
            'meter_defect_discrepancy',
            'phantom_load_waste',
            'circuit_overload',
            'grounded_power_leak',
            'abnormal_consumption_drop',
            'power_outage',
            'energy_audit_check',
            'other_energy_issue',
        ];
        $allCategories = $this->manualIncidentCategories();
        $manualIncidentCategories = collect($selectableCategoryKeys)
            ->map(fn (string $key) => ['key' => $key, 'label' => $allCategories[$key]['label'] ?? $key])
            ->values();

        return view('modules.energy-incident.incidents', compact(
            'incidents',
            'role',
            'user',
            'filters',
            'yearOptions',
            'reportFacilities',
            'manualIncidentCategories'
        ));
    }

    public function export(Request $request)
    {
        abort_unless(RoleAccess::can($request->user(), 'export_reports'), 403);

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

    public function download(Request $request, EnergyIncident $energyIncident)
    {
        abort_unless(RoleAccess::can($request->user(), 'export_reports'), 403);

        $energyIncident->load([
            'facility:id,name,baseline_kwh,source,external_ref',
            'energyRecord:id,facility_id,actual_kwh,baseline_kwh,input_source,alert',
            'maintenance:id,energy_incident_id,maintenance_status,scheduled_date,assigned_to,completed_date,remarks',
            'creator:id,full_name,name,username',
        ]);

        $baseline = $energyIncident->energyRecord?->baseline_kwh ?? $energyIncident->facility?->baseline_kwh;
        $actual = $energyIncident->energyRecord?->actual_kwh;
        $category = $this->manualIncidentCategories()[$energyIncident->category ?? ''] ?? null;
        $detectedAt = $energyIncident->detected_at ?? $energyIncident->date_detected ?? $energyIncident->created_at;
        $sourceLabel = $this->incidentSourceLabel($energyIncident);
        $status = str_contains(strtolower((string) $energyIncident->status), 'pending')
            ? 'Open'
            : ($energyIncident->status ?: 'Open');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.energy-incident.incident-pdf', [
            'incident' => $energyIncident,
            'facilityName' => $energyIncident->facility?->name ?? 'Unknown Facility',
            'categoryLabel' => $category['label'] ?? ($energyIncident->category ? Str::headline($energyIncident->category) : 'Energy Anomaly'),
            'sourceLabel' => $sourceLabel,
            'statusLabel' => $status,
            'severityLabel' => $energyIncident->severity_label,
            'actualKwh' => is_numeric($actual) ? (float) $actual : null,
            'baselineKwh' => is_numeric($baseline) ? (float) $baseline : null,
            'detectedAt' => $detectedAt ? Carbon::parse($detectedAt) : null,
            'preparedBy' => $request->user()?->full_name ?? $request->user()?->name ?? $request->user()?->username ?? 'System User',
            'generatedAt' => now(),
        ])->setPaper('a4');

        $filename = Str::slug($energyIncident->facility?->name ?? 'facility')
            . '-incident-' . $energyIncident->id . '.pdf';

        return $pdf->download($filename);
    }

    public function create()
    {
        return redirect()->route('energy-incidents.index');
    }

    public function store(Request $request)
    {
        abort_unless(RoleAccess::can($request->user(), 'manage_energy_incidents'), 403);

        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'category' => ['required', 'string', Rule::in(array_keys($this->manualIncidentCategories()))],
            'description' => 'required|string|max:2000',
            'detected_at' => 'required|date|before_or_equal:now',
            'affected_asset' => 'nullable|string|max:255',
            'evidence' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $detectedAt = Carbon::parse($validated['detected_at']);
        $duplicate = EnergyIncident::query()
            ->where('facility_id', $validated['facility_id'])
            ->where('category', $validated['category'])
            ->where(function ($query) {
                $query->where('status', 'not like', '%resolved%')
                    ->where('status', 'not like', '%closed%')
                    ->where('status', 'not like', '%dismissed%');
            })
            ->whereDate('date_detected', '>=', $detectedAt->copy()->subDays(7)->toDateString())
            ->latest('id')
            ->first();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'category' => "A similar active incident (#{$duplicate->id}) was already reported for this facility within the last 7 days.",
            ]);
        }

        $category = $this->manualIncidentCategories()[$validated['category']];
        $evidencePath = $request->hasFile('evidence')
            ? $request->file('evidence')->store('incident-evidence', 'public')
            : null;

        try {
            DB::transaction(function () use ($validated, $detectedAt, $category, $evidencePath, $request) {
                $incident = EnergyIncident::create([
                    'facility_id' => $validated['facility_id'],
                    'category' => $validated['category'],
                    'source' => 'manual',
                    'affected_asset' => $validated['affected_asset'] ?? null,
                    'evidence_path' => $evidencePath,
                    'detected_at' => $detectedAt,
                    'month' => (int) $detectedAt->month,
                    'year' => (int) $detectedAt->year,
                    'description' => $validated['description'],
                    'severity' => $category['severity'],
                    'status' => 'Open',
                    'date_detected' => $detectedAt->toDateString(),
                    'created_by' => $request->user()->id,
                ]);

                $asset = trim((string) ($validated['affected_asset'] ?? ''));
                $remarks = "Manual incident #{$incident->id}: {$validated['description']}";
                if ($asset !== '') {
                    $remarks .= " Affected meter/equipment: {$asset}.";
                }
                if ($evidencePath) {
                    $remarks .= " Evidence: {$evidencePath}.";
                }

                Maintenance::create([
                    'facility_id' => $validated['facility_id'],
                    'energy_incident_id' => $incident->id,
                    'issue_type' => $category['maintenance_issue'],
                    'trigger_month' => $detectedAt->format('M Y'),
                    'trend' => 'Reported',
                    'maintenance_type' => 'Corrective',
                    'maintenance_status' => 'Pending',
                    'scheduled_date' => null,
                    'assigned_to' => null,
                    'completed_date' => null,
                    'photo_requirement' => 'Optional',
                    'remarks' => $remarks,
                ]);
            });
        } catch (\Throwable $e) {
            if ($evidencePath) {
                Storage::disk('public')->delete($evidencePath);
            }
            throw $e;
        }

        return redirect()->route('energy-incidents.index')->with(
            'success',
            'Incident reported and forwarded to CIMM for maintenance action.'
        );
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
        abort_unless(RoleAccess::can($request->user(), 'manage_energy_incidents'), 403);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['Open', 'Ongoing', 'Resolved', 'Closed'])],
            'triage_action' => ['nullable', 'string', Rule::in(['escalate_maintenance', 'operational_event', 'direct_update'])],
            'operational_reason' => 'nullable|string|max:500',
            'immediate_action' => 'nullable|string|max:1000',
            'resolution_summary' => 'nullable|string|max:2000',
            'preventive_recommendation' => 'nullable|string|max:1000',
            'affected_asset' => 'nullable|string|max:255',
        ]);

        $triageAction = $validated['triage_action'] ?? 'direct_update';

        if ($triageAction === 'operational_event') {
            $status = 'Resolved';
            $validated['resolution_summary'] = $validated['resolution_summary']
                ?? ('Operational Event (Non-Defect): ' . ($validated['operational_reason'] ?? 'Approved high-usage event. No physical equipment repair required.'));
            $validated['preventive_recommendation'] = $validated['preventive_recommendation']
                ?? 'Monitor baseline during future scheduled community events.';
        } elseif ($triageAction === 'escalate_maintenance') {
            $status = 'Ongoing';
            $validated['immediate_action'] = $validated['immediate_action']
                ?? 'Escalated and dispatched to CIMM Maintenance team for physical inspection.';
        } else {
            $status = $validated['status'] ?? $energyIncident->status ?? 'Open';
        }

        $isResolved = in_array(strtolower($status), ['resolved', 'closed'], true);
        $isOngoing = strtolower($status) === 'ongoing';

        DB::transaction(function () use ($energyIncident, $validated, $status, $isResolved, $isOngoing, $triageAction) {
            $updateData = [
                'status' => $status,
                'immediate_action' => $validated['immediate_action'] ?? $energyIncident->immediate_action,
                'resolution_summary' => $validated['resolution_summary'] ?? $energyIncident->resolution_summary,
                'preventive_recommendation' => $validated['preventive_recommendation'] ?? $energyIncident->preventive_recommendation,
            ];

            if (!empty($validated['affected_asset'])) {
                $updateData['affected_asset'] = $validated['affected_asset'];
            }

            if ($isResolved) {
                $updateData['resolved_at'] = now();
            } else {
                $updateData['resolved_at'] = null;
            }

            $energyIncident->update($updateData);

            // Dual-Sync with linked Maintenance (CIMM) record
            $maintenance = $energyIncident->maintenance;

            if ($triageAction === 'escalate_maintenance') {
                if (!$maintenance && $energyIncident->facility_id) {
                    $triggerMonth = ($energyIncident->month && $energyIncident->year)
                        ? date('M Y', mktime(0, 0, 0, (int) $energyIncident->month, 1, (int) $energyIncident->year))
                        : date('M Y');

                    Maintenance::create([
                        'facility_id' => $energyIncident->facility_id,
                        'energy_incident_id' => $energyIncident->id,
                        'issue_type' => 'Auto-flagged: Critical Consumption',
                        'trigger_month' => $triggerMonth,
                        'maintenance_type' => 'Corrective',
                        'maintenance_status' => 'Pending',
                        'remarks' => "Dispatched from Energy Incident #{$energyIncident->id}. On-site technician inspection required.",
                    ]);
                } elseif ($maintenance) {
                    $maintenance->update([
                        'maintenance_status' => in_array($maintenance->maintenance_status, ['Completed'], true) ? 'Pending' : ($maintenance->maintenance_status ?: 'Pending'),
                        'remarks' => trim(($maintenance->remarks ? $maintenance->remarks . "\n" : '') . "Dispatched from Energy Incident #{$energyIncident->id}."),
                    ]);
                }
            } elseif ($triageAction === 'operational_event') {
                if ($maintenance) {
                    $maintenance->update([
                        'maintenance_status' => 'Completed',
                        'completed_date' => now()->toDateString(),
                        'remarks' => trim(($maintenance->remarks ? $maintenance->remarks . "\n" : '') . "Closed: Verified as operational event by Energy Team. No physical repair needed."),
                    ]);
                }
            } elseif ($maintenance) {
                if ($isResolved) {
                    $maintUpdate = [
                        'maintenance_status' => 'Completed',
                        'completed_date' => now()->toDateString(),
                    ];
                    if (!empty($validated['resolution_summary'])) {
                        $maintUpdate['remarks'] = trim(($maintenance->remarks ? $maintenance->remarks . "\n" : '') . "Resolution: " . $validated['resolution_summary']);
                    }
                    $maintenance->update($maintUpdate);
                } elseif ($isOngoing && $maintenance->maintenance_status === 'Pending') {
                    $maintenance->update([
                        'maintenance_status' => 'In Progress',
                    ]);
                } elseif (strtolower($status) === 'open' && $maintenance->maintenance_status === 'Completed') {
                    $maintenance->update([
                        'maintenance_status' => 'Pending',
                        'completed_date' => null,
                    ]);
                }
            }
        });

        $msg = $triageAction === 'operational_event'
            ? "Incident #{$energyIncident->id} validated as Operational Event and resolved without dispatching maintenance."
            : ($triageAction === 'escalate_maintenance'
                ? "Incident #{$energyIncident->id} escalated and dispatched to CIMM Maintenance."
                : ($isResolved
                    ? "Incident #{$energyIncident->id} marked as Resolved and moved to History."
                    : "Incident #{$energyIncident->id} status updated to {$status}."));

        return redirect()->route('energy-incidents.index')->with('success', $msg);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $role = RoleAccess::normalize($user);
        $historiesQuery = EnergyIncident::with([
                'facility:id,name,baseline_kwh,source,external_ref',
                'energyRecord:id,facility_id,baseline_kwh,actual_kwh,alert,input_source',
                'creator:id,full_name,name,username',
                'maintenance',
            ])
            ->where(function ($query) {
                $query->where('status', 'like', '%resolved%')
                    ->orWhere('status', 'like', '%closed%')
                    ->orWhere('status', 'like', '%dismissed%');
            });

        if ($role === 'staff') {
            $facilityIds = $user?->facilities()->pluck('facilities.id')->all() ?? [];
            $facilityIds !== []
                ? $historiesQuery->whereIn('facility_id', $facilityIds)
                : $historiesQuery->whereRaw('1 = 0');
        }

        $histories = $historiesQuery
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('resolved_at')
            ->orderByDesc('date_detected')
            ->orderByDesc('created_at')
            ->get();

        return view('modules.energy-incident.history', compact('histories', 'role', 'user'));
    }

    private function severityCaseExpression(string $deviationExpr, string $baselineExpr): string
    {
        return "
            CASE
                WHEN LOWER(COALESCE(energy_incidents.severity, '')) = 'critical' THEN 'critical'
                WHEN LOWER(COALESCE(energy_incidents.severity, '')) IN ('very-high', 'very high') THEN 'very-high'
                WHEN LOWER(COALESCE(energy_incidents.severity, '')) = 'high' THEN 'high'
                WHEN LOWER(COALESCE(energy_incidents.severity, '')) = 'warning' THEN 'warning'
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
                'facility:id,name,baseline_kwh,source,external_ref',
                'energyRecord:id,facility_id,baseline_kwh,actual_kwh,alert,input_source',
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
                ->where('energy_incidents.status', 'not like', '%closed%')
                ->where('energy_incidents.status', 'not like', '%dismissed%');
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
            } elseif ($filters['status'] === 'open') {
                $incidentQuery->where(function ($query) {
                    $query->where('energy_incidents.status', 'like', '%open%')
                        ->orWhere('energy_incidents.status', 'like', '%pending%');
                });
            }
        }

        if (in_array($filters['severity'], ['critical', 'very-high', 'high', 'warning'], true)) {
            $incidentQuery->whereRaw("{$severityExpr} = ?", [$filters['severity']]);
        }

        if ($filters['source'] === 'manual') {
            $incidentQuery->whereRaw("LOWER(COALESCE(energy_incidents.source, 'auto')) = 'manual'");
        } elseif ($filters['source'] === 'cprf') {
            $incidentQuery->where(function ($query) {
                $query->whereRaw('LOWER(COALESCE(er.input_source, ?)) = ?', ['', 'cprf'])
                    ->orWhereRaw('LOWER(COALESCE(f.source, ?)) = ?', ['', 'cprf']);
            })->whereRaw("LOWER(COALESCE(energy_incidents.source, 'auto')) <> 'manual'");
        } elseif ($filters['source'] === 'auto') {
            $incidentQuery
                ->whereRaw('LOWER(COALESCE(er.input_source, ?)) <> ?', ['local', 'cprf'])
                ->whereRaw('LOWER(COALESCE(f.source, ?)) <> ?', ['local', 'cprf'])
                ->whereRaw("LOWER(COALESCE(energy_incidents.source, 'auto')) <> 'manual'");
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
        $source = strtolower((string) $request->query('source', 'all'));

        if ($status === 'pending') {
            $status = 'open';
        }
        if (! in_array($status, ['all', 'open', 'ongoing'], true)) {
            $status = 'all';
        }

        if (! in_array($severity, ['all', 'critical', 'very-high', 'high', 'warning'], true)) {
            $severity = 'all';
        }

        if (! in_array($source, ['all', 'auto', 'manual', 'cprf'], true)) {
            $source = 'all';
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
            'source' => $source,
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
                    ->where('status', 'not like', '%closed%')
                    ->where('status', 'not like', '%dismissed%');
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
            'source' => $this->incidentSourceLabel($incident),
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

    private function incidentSourceLabel(EnergyIncident $incident): string
    {
        if (strtolower((string) ($incident->source ?? '')) === 'manual') {
            return 'Manual Report';
        }

        if (
            strtolower((string) ($incident->energyRecord?->input_source ?? '')) === 'cprf'
            || strtolower((string) ($incident->facility?->source ?? '')) === 'cprf'
        ) {
            return 'External System';
        }

        return 'Auto Detected';
    }

    private function manualIncidentCategories(): array
    {
        return [
            'critical_usage_spike' => ['label' => 'Critical Consumption Spike (>20% Baseline)', 'severity' => 'critical', 'maintenance_issue' => 'Critical Usage Spike (>20% Baseline)'],
            'peak_demand_surge' => ['label' => 'Peak Demand Load Surge', 'severity' => 'critical', 'maintenance_issue' => 'Peak Demand Load Surge'],
            'meter_defect_discrepancy' => ['label' => 'Meter Defect / Reading Discrepancy', 'severity' => 'high', 'maintenance_issue' => 'Meter Defect / Reading Discrepancy'],
            'phantom_load_waste' => ['label' => 'Off-Hours / Phantom Load Waste', 'severity' => 'high', 'maintenance_issue' => 'Off-Hours / Phantom Load Waste'],
            'circuit_overload' => ['label' => 'Circuit Overload / Phase Imbalance', 'severity' => 'critical', 'maintenance_issue' => 'Electrical - Circuit Overload'],
            'grounded_power_leak' => ['label' => 'Grounded Power Leak / Current Loss', 'severity' => 'critical', 'maintenance_issue' => 'Electrical - Grounded Line'],
            'abnormal_consumption_drop' => ['label' => 'Abnormal Consumption Drop / Meter Stoppage', 'severity' => 'high', 'maintenance_issue' => 'Abnormal Consumption Drop / Meter Stoppage'],
            'power_outage' => ['label' => 'Power Outage / Line Trip', 'severity' => 'high', 'maintenance_issue' => 'Electrical - Power Outage'],
            'energy_audit_check' => ['label' => 'Energy Audit & Verification Check', 'severity' => 'warning', 'maintenance_issue' => 'Energy Audit & Verification Check'],
            'other_energy_issue' => ['label' => 'Other Energy / Electrical Anomaly', 'severity' => 'warning', 'maintenance_issue' => 'General - Other'],

            // Legacy mappings to preserve compatibility with existing historical test data
            'equipment_overheating' => ['label' => 'Equipment High Temperature / Overheating', 'severity' => 'high', 'maintenance_issue' => 'General - Other'],
            'meter_issue' => ['label' => 'Meter Issue / Calibration Defect', 'severity' => 'warning', 'maintenance_issue' => 'General - Other'],
            'smoke_or_burning_smell' => ['label' => 'Electrical Arcing / Burning', 'severity' => 'critical', 'maintenance_issue' => 'General - Other'],
            'damaged_equipment' => ['label' => 'Damaged Electrical Equipment', 'severity' => 'high', 'maintenance_issue' => 'General - Other'],
            'other' => ['label' => 'Other', 'severity' => 'warning', 'maintenance_issue' => 'General - Other'],
        ];
    }
}
