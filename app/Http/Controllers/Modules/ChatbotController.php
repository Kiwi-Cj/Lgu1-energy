<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\MainMeterAlert;
use App\Models\Maintenance;
use App\Models\User;
use App\Services\ChatbotAiService;
use App\Services\EnergyRecommendationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly EnergyRecommendationService $recommendationService,
        private readonly ChatbotAiService $chatbotAiService,
    ) {
    }

    public function index()
    {
        return view('modules.chatbot.index', [
            'chatbotStats' => $this->buildDashboardStats(),
        ]);
    }

    public function respond(Request $request): JsonResponse
    {
        $rawMessage = trim((string) $request->input('message', ''));
        $message = $this->normalize($rawMessage);
        $user = auth()->user();

        if ($message === '') {
            return $this->reply('help', $this->defaultHelpMessage());
        }

        if ($this->isGreeting($message)) {
            $userName = $user?->full_name ?? $user?->name ?? 'there';
            return $this->reply('greeting', 'Hello, ' . e($userName) . '! Ask me about facilities, meters, reports, alerts, maintenance, monthly cost, or how to use the system.');
        }

        $context = $this->buildAiContext();

        if ($this->matches($message, ['account', 'profile', 'my details', 'who am i', 'my name', 'department', 'role'])) {
            return $this->reply('account', $this->buildAccountSummary($user));
        }

        if ($this->matches($message, ['monthly cost', 'energy cost', 'cost this month', 'bill this month', 'electricity cost', 'total cost'])) {
            return $this->reply('monthly_cost', $this->buildMonthlyCostSummary());
        }

        if ($this->matches($message, ['high alert', 'high alerts', 'critical alert', 'which facility', 'top facility', 'consuming most'])) {
            return $this->reply('high_alerts', $this->getHighAlertFacilities());
        }

        if ($this->matches($message, ['recommend', 'suggestion', 'advice', 'how to reduce', 'save energy', 'improve efficiency', 'conservation'])) {
            return $this->reply('recommendations', $this->getEnergyRecommendations());
        }

        if ($this->matches($message, ['alert', 'alerts', 'warning', 'normal', 'very high', 'anomaly'])) {
            return $this->reply('alerts', $this->buildAlertSummary());
        }

        if ($this->matches($message, ['maintenance', 'repair', 'schedule', 'pending work', 'ongoing work'])) {
            return $this->reply('maintenance', $this->buildMaintenanceSummary());
        }

        if ($this->matches($message, ['facility', 'facilities', 'building', 'office', 'barangay'])) {
            return $this->reply('facilities', $this->buildFacilitySummary());
        }

        if ($this->matches($message, ['meter', 'meters', 'main meter', 'submeter', 'sub-meter', 'reading', 'device'])) {
            return $this->reply('meters', $this->buildMeterSummary());
        }

        if ($this->matches($message, ['report', 'reports', 'export', 'pdf', 'excel', 'download', 'print'])) {
            return $this->reply('reports', $this->buildReportGuide());
        }

        if ($this->matches($message, ['archive', 'restore', 'delete', 'permanent delete', 'recover'])) {
            return $this->reply('archive', $this->buildArchiveGuide());
        }

        if ($this->matches($message, ['dashboard', 'home page', 'overview', 'summary card'])) {
            return $this->reply('dashboard', $this->buildDashboardGuide());
        }

        if ($this->matches($message, ['user', 'users', 'roles', 'permission', 'admin', 'staff', 'energy officer'])) {
            return $this->reply('users', $this->buildUsersGuide());
        }

        if ($this->matches($message, ['kwh', 'kilowatt', 'baseline', 'deviation', 'efficiency score', 'energy efficiency', 'peak demand', 'power factor'])) {
            return $this->reply('energy_terms', $this->buildEnergyTermsGuide($message));
        }

        $localReply = $this->buildSystemGuideReply($message);
        if ($localReply !== '') {
            return $this->reply('system_guide', $localReply);
        }

        if ($this->chatbotAiService->isEnabled()) {
            $aiResponse = $this->chatbotAiService->generateReply($rawMessage, $context);
            if ($aiResponse !== '') {
                return $this->reply('ai', nl2br(e($aiResponse)));
            }
        }

        return $this->reply('help', $this->defaultHelpMessage());
    }

    private function buildDashboardStats(): array
    {
        try {
            $year = (int) now()->year;

            return [
                'total_facilities' => Facility::query()->count(),
                'active_meters' => FacilityMeter::query()->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 'active');
                })->count(),
                'records_this_year' => EnergyRecord::query()->where('year', $year)->count(),
                'high_alerts' => MainMeterAlert::query()->whereIn('alert_level', ['High', 'Very High', 'Critical'])->count(),
            ];
        } catch (\Throwable) {
            return [
                'total_facilities' => 0,
                'active_meters' => 0,
                'records_this_year' => 0,
                'high_alerts' => 0,
            ];
        }
    }

    private function buildAiContext(): array
    {
        $stats = $this->buildDashboardStats();
        $user = auth()->user();

        return [
            'today' => now()->toDateString(),
            'signed_in_user' => [
                'name' => $user?->full_name ?? $user?->name ?? 'User',
                'role' => $user?->role ?? 'Unknown',
                'department' => $user?->department ?? null,
            ],
            'stats' => $stats,
            'modules' => [
                'Facilities',
                'Energy Profile and meters',
                'Monthly Records',
                'Main Meter Monitoring',
                'Sub-meter Monitoring',
                'Energy Conservation',
                'Load Tracking',
                'Maintenance',
                'Reports',
                'Archive',
                'Users and Roles',
            ],
        ];
    }

    private function buildAccountSummary($user): string
    {
        $details = [];
        $details[] = '<strong>Your Account</strong>';
        $details[] = 'Name: ' . e($user?->full_name ?? $user?->name ?? 'User');
        $details[] = 'Role: ' . e((string) ($user?->role ?? 'Unknown'));
        $details[] = 'Department: ' . e((string) ($user?->department ?? 'N/A'));
        $details[] = 'Status: ' . e((string) ($user?->status ?? 'Unknown'));

        return implode('<br>', $details);
    }

    private function buildMonthlyCostSummary(): string
    {
        try {
            $now = now();
            $query = EnergyRecord::query()
                ->where('year', (int) $now->year)
                ->where('month', (int) $now->month);

            $cost = (float) (clone $query)->sum('energy_cost');
            $kwh = (float) (clone $query)->sum('actual_kwh');
            $count = (clone $query)->count();

            if ($count === 0) {
                return 'No monthly energy records found for ' . e($now->format('F Y')) . '. Add monthly records first so I can compute the current cost.';
            }

            $latest = (clone $query)->with('facility')->orderByDesc('updated_at')->first();
            $lines = [
                '<strong>Monthly Cost Summary - ' . e($now->format('F Y')) . '</strong>',
                'Total cost: PHP ' . number_format($cost, 2),
                'Total consumption: ' . number_format($kwh, 2) . ' kWh',
                'Records counted: ' . number_format($count),
            ];

            if ($latest?->facility) {
                $lines[] = 'Latest updated facility: ' . e($latest->facility->name);
            }

            return implode('<br>', $lines);
        } catch (\Throwable) {
            return 'Unable to read monthly cost data right now.';
        }
    }

    private function getHighAlertFacilities(): string
    {
        $lines = ['<strong>High Alert Facilities</strong>'];

        try {
            $highAlerts = MainMeterAlert::query()
                ->with('facility')
                ->whereIn('alert_level', ['High', 'Very High', 'Critical'])
                ->orderByDesc('increase_percent')
                ->limit(10)
                ->get();

            if ($highAlerts->isEmpty()) {
                $lines[] = 'No facilities with high alerts at this moment.';
            } else {
                foreach ($highAlerts as $index => $alert) {
                    $facilityName = $alert->facility?->name ?? 'Unknown facility';
                    $lines[] = ($index + 1) . '. <strong>' . e($facilityName) . '</strong>';
                    $lines[] = 'Alert: ' . e(strtoupper((string) ($alert->alert_level ?? 'Unknown'))) . ' | kWh: ' . number_format((float) ($alert->current_kwh ?? 0), 2);
                    $lines[] = 'Deviation: +' . number_format((float) ($alert->increase_percent ?? 0), 1) . '%';
                }
                $lines[] = 'Open Main Meter Alerts or Energy Conservation to review causes and recommendations.';
            }
        } catch (\Throwable) {
            $lines[] = 'Unable to retrieve alert data at the moment.';
        }

        return implode('<br>', $lines);
    }

    private function getEnergyRecommendations(): string
    {
        $lines = ['<strong>Energy Conservation Recommendations</strong>'];

        try {
            $facilities = Facility::query()->orderBy('name')->limit(5)->get();

            if ($facilities->isEmpty()) {
                $lines[] = 'No facilities available for recommendations at this moment.';
            } else {
                foreach ($facilities as $facility) {
                    $latestRecord = $facility->energyRecords()
                        ->whereHas('meter', fn ($query) => $query->where('meter_type', 'main'))
                        ->orderByDesc('year')
                        ->orderByDesc('month')
                        ->first();

                    $context = [
                        'facility_name' => $facility->name,
                        'facility_type' => $facility->type,
                        'alert_level' => $latestRecord?->alert ?: 'Normal',
                        'trend_percent' => $latestRecord?->deviation ?? 0,
                        'actual_kwh' => $latestRecord?->actual_kwh,
                        'baseline_kwh' => $facility->baseline_kwh,
                        'floor_area' => $facility->floor_area,
                    ];

                    $recommendation = $this->recommendationService->generateFacilityRecommendation($context, true);
                    if ($recommendation) {
                        $lines[] = '<strong>' . e($facility->name) . ':</strong> ' . $recommendation;
                    }
                }
            }
        } catch (\Throwable) {
            $lines[] = 'Unable to generate recommendations at the moment.';
        }

        return implode('<br><br>', $lines);
    }

    private function buildMaintenanceSummary(): string
    {
        try {
            $pending = Maintenance::query()->where('maintenance_status', 'Pending')->count();
            $ongoing = Maintenance::query()->where('maintenance_status', 'Ongoing')->count();
            $completed = Maintenance::query()->where('maintenance_status', 'Completed')->count();

            return implode('<br>', [
                '<strong>Maintenance Module</strong>',
                'Pending: ' . number_format($pending),
                'Ongoing: ' . number_format($ongoing),
                'Completed: ' . number_format($completed),
                'Use Maintenance to schedule work, assign personnel, set dates, add remarks, and move completed items to history.',
            ]);
        } catch (\Throwable) {
            return 'The Maintenance module tracks pending, ongoing, and completed facility work. You can schedule maintenance from the Maintenance page.';
        }
    }

    private function buildFacilitySummary(): string
    {
        try {
            $total = Facility::query()->count();
            $active = Facility::query()->where('status', 'active')->count();
            $sample = Facility::query()->orderBy('name')->limit(5)->pluck('name')->all();

            $lines = [
                '<strong>Facilities</strong>',
                'Total facilities: ' . number_format($total),
                'Active facilities: ' . number_format($active),
            ];

            if (! empty($sample)) {
                $lines[] = 'Examples: ' . e(implode(', ', $sample));
            }

            $lines[] = 'Use Facilities to view facility details, energy profile, meters, monthly records, equipment inventory, and archive.';

            return implode('<br>', $lines);
        } catch (\Throwable) {
            return 'Facilities are the buildings or offices monitored by the system. Each facility can have meters, records, baselines, alerts, and maintenance entries.';
        }
    }

    private function buildMeterSummary(): string
    {
        try {
            $main = FacilityMeter::query()->where('meter_type', 'main')->count();
            $sub = FacilityMeter::query()->where('meter_type', 'sub')->count();
            $unapproved = FacilityMeter::query()->whereNull('approved_at')->count();

            return implode('<br>', [
                '<strong>Meters</strong>',
                'Main meters: ' . number_format($main),
                'Sub-meters: ' . number_format($sub),
                'Unapproved meters: ' . number_format($unapproved),
                'Main meters represent facility-level consumption. Sub-meters track specific areas, panels, or equipment groups under a main meter.',
            ]);
        } catch (\Throwable) {
            return 'Meters store the monitored points for each facility. Use Energy Profile or Facility Meters to add, approve, archive, or review meters.';
        }
    }

    private function buildReportGuide(): string
    {
        return implode('<br>', [
            '<strong>Reports and Exports</strong>',
            '1. Open Reports or Main Meter Reports from the sidebar.',
            '2. Choose the facility, month, year, or report type.',
            '3. Review the table and charts.',
            '4. Use PDF or Excel export when you need a downloadable copy.',
            'Available report areas include Energy Reports, Annual Reports, Monthly Main Meter Reports, Baseline Comparison, and Demand Spikes.',
        ]);
    }

    private function buildArchiveGuide(): string
    {
        return implode('<br>', [
            '<strong>Archive, Restore, and Permanent Delete</strong>',
            'Archive keeps deleted facilities, meters, and monthly records for recovery.',
            'Restore brings an archived item back to active records.',
            'Permanent Delete removes an archived item and cannot be undone.',
            'Archive pages now use confirmation modals for Restore and Delete actions.',
        ]);
    }

    private function buildDashboardGuide(): string
    {
        return implode('<br>', [
            '<strong>Dashboard</strong>',
            'The dashboard gives a quick overview of facility energy status, alerts, consumption, and recent activity.',
            'Use it to spot high usage, see totals, and jump into detailed modules like Facilities, Reports, Main Meter Monitoring, or Energy Conservation.',
        ]);
    }

    private function buildUsersGuide(): string
    {
        return implode('<br>', [
            '<strong>Users and Roles</strong>',
            'Super Admin/Admin can manage users and roles.',
            'Energy Officer users usually manage monitoring, records, and recommendations.',
            'Staff users may have limited access, often scoped to assigned facilities.',
            'Your visible modules depend on your assigned role and permissions.',
        ]);
    }

    private function buildAlertSummary(): string
    {
        $lines = [
            '<strong>Alerts Explained</strong>',
            'Alerts flag unusual or excessive energy use compared with baseline values.',
            'Common levels: Normal, Warning, High, Very High, and Critical.',
            'A higher alert usually means the latest kWh is significantly above the expected baseline.',
        ];

        try {
            $mainAlertCount = MainMeterAlert::query()->count();
            $highCount = MainMeterAlert::query()->whereIn('alert_level', ['High', 'Very High', 'Critical'])->count();
            $lines[] = 'Recorded main meter alerts: ' . number_format($mainAlertCount);
            $lines[] = 'High/Critical alerts: ' . number_format($highCount);
        } catch (\Throwable) {
            $lines[] = 'Alert records were not available to read at the moment.';
        }

        return implode('<br>', $lines);
    }

    private function buildEnergyTermsGuide(string $message): string
    {
        if (str_contains($message, 'kwh') || str_contains($message, 'kilowatt')) {
            return '<strong>kWh</strong><br>kWh means kilowatt-hour. It measures energy consumed. Example: a 1 kW load running for 1 hour uses 1 kWh.';
        }

        if (str_contains($message, 'baseline')) {
            return '<strong>Baseline kWh</strong><br>Baseline is the expected or reference consumption for a facility or meter. The system compares actual kWh against baseline to detect abnormal usage.';
        }

        if (str_contains($message, 'deviation')) {
            return '<strong>Deviation</strong><br>Deviation shows how far actual consumption is from baseline. Formula: ((Actual kWh - Baseline kWh) / Baseline kWh) x 100.';
        }

        if (str_contains($message, 'peak demand')) {
            return '<strong>Peak Demand</strong><br>Peak demand is the highest power demand during a period. It helps identify demand spikes and heavy-load events.';
        }

        if (str_contains($message, 'power factor')) {
            return '<strong>Power Factor</strong><br>Power factor indicates how efficiently electrical power is used. Lower values can mean wasted capacity or inefficient loads.';
        }

        return '<strong>Energy Efficiency</strong><br>Energy efficiency means using less energy for the same operation. In this system, compare actual kWh, baseline kWh, deviation, alerts, and recommendations to find improvement areas.';
    }

    private function buildSystemGuideReply(string $message): string
    {
        if ($this->matches($message, ['how to use', 'navigate', 'where can i', 'paano', 'saan'])) {
            return implode('<br>', [
                '<strong>System Navigation</strong>',
                'Use the left sidebar to open modules.',
                'Facilities: facility details, meters, monthly records, energy profile.',
                'Main Meter/Sub-meter Monitoring: readings, approvals, alerts.',
                'Energy Conservation: recommendations and consumption analysis.',
                'Reports: filtered reports and exports.',
                'Maintenance: schedule and track facility maintenance.',
            ]);
        }

        if ($this->matches($message, ['add record', 'monthly record', 'encode consumption', 'bill'])) {
            return implode('<br>', [
                '<strong>Monthly Records</strong>',
                'Open Facilities, choose a facility, then go to Monthly Records.',
                'Select the billing month/year, meter, kWh, cost, and rate if needed.',
                'The system uses actual kWh, baseline, and deviation to determine alert level.',
            ]);
        }

        return '';
    }

    private function defaultHelpMessage(): string
    {
        return implode('<br>', [
            'I can answer questions about this Energy Monitoring System.',
            'Try asking:',
            '- What is my monthly energy cost?',
            '- Show high alert facilities',
            '- How do I generate a report?',
            '- What is kWh?',
            '- How do I restore archived records?',
            '- Explain main meters and sub-meters',
        ]);
    }

    private function reply(string $type, string $message): JsonResponse
    {
        return response()->json([
            'type' => $type,
            'message' => $message,
        ]);
    }

    private function matches(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $this->normalize($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function isGreeting(string $message): bool
    {
        $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'kumusta', 'kamusta'];
        foreach ($greetings as $greeting) {
            if ($message === $greeting || str_starts_with($message, $greeting . ' ') || str_ends_with($message, ' ' . $greeting)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $message): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $message)));
    }
}
