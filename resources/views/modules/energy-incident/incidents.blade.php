@extends('layouts.qc-admin')
@section('title', 'Energy Incidents')

@php
    $user = auth()->user();
    $canReportIncidents = \App\Support\RoleAccess::can($user, 'manage_energy_incidents');
    $canExportReports = \App\Support\RoleAccess::can($user, 'export_reports');
    $categoryLabels = collect($manualIncidentCategories ?? [])->pluck('label', 'key');
    $incidentFormErrors = isset($errors) ? $errors->all() : [];

    $incidentRows = collect(method_exists($incidents, 'items') ? $incidents->items() : $incidents);
    $totalOnPage = $incidentRows->count();
    $openCount = $incidentRows->filter(function ($incident) {
        $status = strtolower((string) ($incident->status ?? 'open'));
        return str_contains($status, 'open') || str_contains($status, 'pending');
    })->count();
    $ongoingCount = $incidentRows->filter(function ($incident) {
        return str_contains(strtolower((string) ($incident->status ?? '')), 'ongoing');
    })->count();
    $criticalCount = $incidentRows->filter(function ($incident) {
        $level = strtolower((string) ($incident->severity_key ?? 'normal'));
        return in_array($level, ['critical', 'very-high'], true);
    })->count();
    $monthOptions = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];
    $exportQuery = array_filter($filters ?? [], function ($value) {
        return $value !== null && $value !== '' && $value !== 'all' && $value !== 0;
    });
@endphp

@section('content')
@if(session('success'))
<div id="successAlert" class="alert-toast success">
    <i class="fa fa-check-circle"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div id="errorAlert" class="alert-toast error">
    <i class="fa fa-times-circle"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

<script>
window.addEventListener('DOMContentLoaded', function() {
    ['successAlert', 'errorAlert'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 400);
            }, 4500);
        }
    });
});
</script>

<div class="incident-page">
    <div class="incident-shell">
        <div class="incident-header">
            <div>
                <h2>Incident Records</h2>
                <p>Track active energy anomalies and inspect details for immediate action.</p>
            </div>
            <div class="header-actions">
                @if($canReportIncidents)
                <button type="button" class="report-btn" id="openReportIncident">
                    <i class="fa-solid fa-triangle-exclamation"></i> Report Incident
                </button>
                @endif
                <a href="{{ route('energy-incidents.export', $exportQuery) }}" class="download-btn" data-secure-download>
                    <i class="fa-solid fa-download"></i> Download
                </a>
                <a href="{{ route('energy-incidents.history') }}" class="history-btn">
                    <i class="fa-solid fa-clock-rotate-left"></i> View History
                </a>
            </div>
        </div>

        <div class="incident-metrics">
            <div class="metric-card total">
                <span class="metric-label">On This Page</span>
                <strong class="metric-value">{{ $totalOnPage }}</strong>
            </div>
            <div class="metric-card critical">
                <span class="metric-label">Critical/Very High</span>
                <strong class="metric-value">{{ $criticalCount }}</strong>
            </div>
            <div class="metric-card open">
                <span class="metric-label">Open</span>
                <strong class="metric-value">{{ $openCount }}</strong>
            </div>
            <div class="metric-card ongoing">
                <span class="metric-label">Ongoing</span>
                <strong class="metric-value">{{ $ongoingCount }}</strong>
            </div>
        </div>

        <form class="incident-filters" method="GET" action="{{ route('energy-incidents.index') }}">
            <input type="text" name="q" id="incidentSearch" placeholder="Search facility, description, status..." value="{{ $filters['q'] ?? '' }}" />
            <select name="status" id="incidentStatusFilter">
                <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="open" {{ ($filters['status'] ?? 'all') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="ongoing" {{ ($filters['status'] ?? 'all') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            </select>
            <select name="severity" id="incidentSeverityFilter">
                <option value="all" {{ ($filters['severity'] ?? 'all') === 'all' ? 'selected' : '' }}>All Severity</option>
                <option value="critical" {{ ($filters['severity'] ?? 'all') === 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="very-high" {{ ($filters['severity'] ?? 'all') === 'very-high' ? 'selected' : '' }}>Very High</option>
                <option value="high" {{ ($filters['severity'] ?? 'all') === 'high' ? 'selected' : '' }}>High</option>
                <option value="warning" {{ ($filters['severity'] ?? 'all') === 'warning' ? 'selected' : '' }}>Warning</option>
            </select>
            <select name="source" id="incidentSourceFilter">
                <option value="all" {{ ($filters['source'] ?? 'all') === 'all' ? 'selected' : '' }}>All Sources</option>
                <option value="auto" {{ ($filters['source'] ?? 'all') === 'auto' ? 'selected' : '' }}>Auto Detected</option>
                <option value="manual" {{ ($filters['source'] ?? 'all') === 'manual' ? 'selected' : '' }}>Manual Report</option>
                <option value="cprf" {{ ($filters['source'] ?? 'all') === 'cprf' ? 'selected' : '' }}>External System</option>
            </select>
            <select name="year" id="incidentYearFilter">
                <option value="">All Years</option>
                @foreach(($yearOptions ?? collect([now()->year])) as $year)
                    <option value="{{ $year }}" {{ (int) ($filters['year'] ?? 0) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            <select name="month" id="incidentMonthFilter">
                <option value="">All Months</option>
                @foreach($monthOptions as $monthNumber => $monthName)
                    <option value="{{ $monthNumber }}" {{ (int) ($filters['month'] ?? 0) === (int) $monthNumber ? 'selected' : '' }}>{{ $monthName }}</option>
                @endforeach
            </select>
            <input type="date" name="date_detected" id="incidentDateFilter" value="{{ $filters['date_detected'] ?? '' }}" />
            <div class="filter-actions">
                <button type="submit" class="filter-btn apply">Apply</button>
                <a href="{{ route('energy-incidents.index') }}" class="filter-btn clear">Reset</a>
            </div>
        </form>

        <div class="incident-list-container">
            @forelse($incidents as $incident)
                @php
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    $monthNum = (int) ($incident->month ?? 0);
                    $yearNum = $incident->year ?? null;
                    $monthLabel = $monthNum >= 1 && $monthNum <= 12 ? $months[$monthNum - 1] : '-';
                    $facilityName = $incident->facility->name ?? 'Unknown Facility';
                    $isManual = strtolower((string) ($incident->source ?? '')) === 'manual';
                    $isCprf = !$isManual && (strtolower((string) ($incident->energyRecord?->input_source ?? '')) === 'cprf'
                        || strtolower((string) ($incident->facility?->source ?? '')) === 'cprf');
                    $sourceLabel = $isManual ? 'Manual Report' : ($isCprf ? 'External System' : 'Auto Detected');
                    $sourceClass = $isManual ? 'manual' : ($isCprf ? 'cprf' : 'auto');
                    $categoryLabel = $categoryLabels->get((string) ($incident->category ?? ''), $isManual ? 'Other' : 'Energy Anomaly');
                    $deviation = $incident->deviation_percent;
                    $deviationText = $deviation !== null ? number_format((float) $deviation, 2) . '%' : 'N/A';
                    $dateDetected = $incident->detected_at
                        ? \Carbon\Carbon::parse($incident->detected_at)->format('M d, Y h:i A')
                        : ($incident->date_detected ? \Carbon\Carbon::parse($incident->date_detected)->format('M d, Y') : ($incident->created_at ? $incident->created_at->format('M d, Y') : 'N/A'));

                    $levelKey = strtolower((string) ($incident->severity_key ?? 'normal'));
                    if (!in_array($levelKey, ['critical', 'very-high', 'high', 'warning', 'normal'], true)) {
                        $normalizedLevel = str_replace(' ', '-', $levelKey);
                        $levelKey = in_array($normalizedLevel, ['critical', 'very-high', 'high', 'warning', 'normal'], true)
                            ? $normalizedLevel
                            : 'normal';
                    }
                    $levelLabel = (string) ($incident->severity_label ?? '');
                    if ($levelLabel === '') {
                        $levelLabel = $levelKey === 'very-high'
                            ? 'Very High'
                            : ucfirst(str_replace('-', ' ', $levelKey));
                    }

                    $statusRaw = strtolower((string) ($incident->status ?? 'Open'));
                    $statusKey = 'open';
                    $statusLabel = 'Open';
                    if (str_contains($statusRaw, 'resolved') || str_contains($statusRaw, 'closed')) {
                        $statusKey = 'resolved';
                        $statusLabel = 'Resolved';
                    } elseif (str_contains($statusRaw, 'ongoing')) {
                        $statusKey = 'ongoing';
                        $statusLabel = 'Ongoing';
                    }

                    $defaultDescription = match ($statusKey) {
                        'resolved' => $levelKey === 'critical'
                            ? 'Critical energy spike for this billing period was resolved after corrective action.'
                            : 'Very high energy deviation for this billing period has been resolved and stabilized.',
                        'ongoing' => $levelKey === 'critical'
                            ? 'CIMM is actively handling the critical energy spike and corrective maintenance is in progress.'
                            : 'CIMM corrective maintenance is in progress for this energy deviation.',
                        default => $levelKey === 'critical'
                            ? 'Critical energy spike detected and forwarded to CIMM for urgent maintenance action.'
                            : 'Energy deviation detected and forwarded to CIMM for maintenance assessment.',
                    };
                    $legacyDescriptions = [
                        'High energy consumption detected for this billing period.',
                        'System detected unusually high energy consumption for this period. Please review and validate.',
                        'Critical energy spike detected for this billing period and queued for urgent review.',
                        'Very high energy deviation detected for this billing period and queued for validation.',
                        'High energy deviation detected for this billing period and queued for validation.',
                    ];
                    $descriptionText = trim((string) ($incident->description ?? ''));
                    if ($descriptionText === '' || in_array($descriptionText, $legacyDescriptions, true)) {
                        $descriptionText = $defaultDescription;
                    }
                    $descriptionPreview = \Illuminate\Support\Str::limit($descriptionText, 140);
                    $searchText = strtolower($facilityName . ' ' . $statusLabel . ' ' . $levelLabel . ' ' . $sourceLabel . ' ' . $descriptionText);

                    $probableCause = $incident->probable_cause;
                    if (is_array($probableCause)) {
                        $probableCause = implode(', ', $probableCause);
                    }
                    $probableCause = $probableCause ?: 'Automated system analysis: Abnormal usage pattern detected based on recent records.';

                    $immediateAction = $incident->immediate_action ?: 'Incident forwarded to CIMM for maintenance assessment and action.';
                    $resolutionSummary = $incident->resolution_summary ?: 'Awaiting maintenance status and resolution updates from CIMM.';
                    $defaultRecommendation = match ($statusKey) {
                        'resolved' => $levelKey === 'critical'
                            ? 'Keep weekly load audits and retain corrective controls to prevent another critical spike.'
                            : 'Continue monthly variance checks and maintain current demand-control adjustments.',
                        'ongoing' => $levelKey === 'critical'
                            ? 'Continue technical mitigation, monitor demand in near-real time, and verify equipment stability daily.'
                            : 'Continue corrective maintenance and validate consumption trend every operating shift.',
                        default => $levelKey === 'critical'
                            ? 'CIMM should prioritize urgent inspection and apply temporary load controls while investigating the cause.'
                            : 'CIMM should validate equipment operation and apply corrective controls until consumption stabilizes.',
                    };
                    $preventiveRecommendation = trim((string) ($incident->preventive_recommendation ?? ''));
                    if ($preventiveRecommendation === '') {
                        $preventiveRecommendation = $defaultRecommendation;
                    }
                    $actualKwh = is_numeric($incident->energyRecord?->actual_kwh)
                        ? number_format((float) $incident->energyRecord->actual_kwh, 2) . ' kWh'
                        : 'N/A';
                    $baselineValue = $incident->energyRecord?->baseline_kwh ?? $incident->facility?->baseline_kwh;
                    $baselineKwh = is_numeric($baselineValue)
                        ? number_format((float) $baselineValue, 2) . ' kWh'
                        : 'N/A';

                    $attachments = [];
                    if (is_array($incident->attachments)) {
                        $attachments = $incident->attachments;
                    } elseif (is_string($incident->attachments) && trim($incident->attachments) !== '') {
                        $decoded = json_decode($incident->attachments, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $attachments = $decoded;
                        } else {
                            $attachments = [$incident->attachments];
                        }
                    }
                @endphp
                <div class="incident-list-row"
                    tabindex="0"
                    data-id="{{ $incident->id }}"
                    data-status="{{ $statusKey }}"
                    data-level="{{ $levelKey }}"
                    data-search="{{ $searchText }}"
                    onclick="openIncidentModal({{ $incident->id }})">
                    <div class="row-main">
                        <div class="facility-col">
                            <div class="facility-heading">
                                <div class="facility-name">{{ $facilityName }}</div>
                                <span class="source-chip {{ $sourceClass }}">
                                    <i class="fa-solid {{ $isManual ? 'fa-user-pen' : ($isCprf ? 'fa-link' : 'fa-bolt') }}"></i> {{ $sourceLabel }}
                                </span>
                            </div>
                            <div class="facility-desc">{{ $descriptionPreview }}</div>
                        </div>
                        <div class="meta-col">
                            <span class="chip severity {{ $levelKey }}">{{ $levelLabel }}</span>
                            <span class="chip status {{ $statusKey }}">{{ $statusLabel }}</span>
                            <div class="incident-pipeline-stepper {{ $statusKey }}">
                                <span class="pipe-step {{ $statusKey === 'open' ? 'current' : 'done' }}" title="1. Detected"><i class="fa-solid fa-circle-dot"></i> Detected</span>
                                <i class="fa-solid fa-chevron-right pipe-arrow"></i>
                                <span class="pipe-step {{ $statusKey === 'ongoing' ? 'current' : ($statusKey === 'resolved' ? 'done' : 'next') }}" title="2. In Progress"><i class="fa-solid fa-wrench"></i> In Progress</span>
                                <i class="fa-solid fa-chevron-right pipe-arrow"></i>
                                <span class="pipe-step {{ $statusKey === 'resolved' ? 'done' : 'next' }}" title="3. Resolved"><i class="fa-solid fa-circle-check"></i> Resolved</span>
                            </div>
                        </div>
                        <div class="value-col">
                            <div class="value-label">Deviation</div>
                            <div class="value-main {{ $deviation !== null && $deviation >= 0 ? 'up' : 'down' }}">{{ $deviationText }}</div>
                        </div>
                        <div class="value-col">
                            <div class="value-label">Detected</div>
                            <div class="value-main">{{ $dateDetected }}</div>
                            <div class="value-sub">{{ $monthLabel }}/{{ $yearNum ?? '-' }}</div>
                        </div>
                        <div class="action-col">
                            <button type="button" class="detail-btn" onclick="event.stopPropagation(); openIncidentModal({{ $incident->id }})">
                                View Details <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="incident-modal-{{ $incident->id }}" class="incident-modal" style="display:none;" aria-hidden="true">
                    <div class="incident-modal-content" role="dialog" aria-modal="true" aria-labelledby="incident-modal-title-{{ $incident->id }}">
                        <button class="incident-modal-close" onclick="closeIncidentModal({{ $incident->id }})" aria-label="Close modal">&times;</button>
                        <div class="modal-top">
                            <div class="modal-title-group">
                                <span class="modal-title-icon"><i class="fa-solid fa-bolt"></i></span>
                                <div>
                                    <span class="modal-eyebrow">Incident #{{ $incident->id }}</span>
                                    <h3 id="incident-modal-title-{{ $incident->id }}">Energy Incident Report</h3>
                                    <p>{{ $facilityName }} &bull; Detected {{ $dateDetected }}</p>
                                </div>
                            </div>
                            <div class="modal-chip-group">
                                <span class="chip severity {{ $levelKey }}">{{ $levelLabel }}</span>
                                <span class="chip status {{ $statusKey }}">{{ $statusLabel }}</span>
                                @if($canExportReports)
                                    <a href="{{ route('energy-incidents.download', $incident) }}" class="incident-pdf-btn" data-secure-download onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="incident-modal-body">
                            <!-- Progress Lifecycle Pipeline -->
                            <div class="modal-lifecycle-pipeline {{ $statusKey }}">
                                <div class="pipe-item {{ $statusKey === 'open' ? 'active' : 'done' }}">
                                    <div class="pipe-icon"><i class="fa-solid {{ $statusKey === 'open' ? 'fa-triangle-exclamation' : 'fa-check' }}"></i></div>
                                    <div class="pipe-text"><strong>1. Detected</strong><span>Anomaly logged</span></div>
                                </div>
                                <div class="pipe-connector {{ $statusKey !== 'open' ? 'done' : '' }}"></div>
                                <div class="pipe-item {{ $statusKey === 'ongoing' ? 'active' : ($statusKey === 'resolved' ? 'done' : 'pending') }}">
                                    <div class="pipe-icon"><i class="fa-solid {{ $statusKey === 'ongoing' ? 'fa-screwdriver-wrench' : ($statusKey === 'resolved' ? 'fa-check' : 'fa-clock') }}"></i></div>
                                    <div class="pipe-text"><strong>2. In Progress</strong><span>Maintenance action</span></div>
                                </div>
                                <div class="pipe-connector {{ $statusKey === 'resolved' ? 'done' : '' }}"></div>
                                <div class="pipe-item {{ $statusKey === 'resolved' ? 'done' : 'pending' }}">
                                    <div class="pipe-icon"><i class="fa-solid {{ $statusKey === 'resolved' ? 'fa-circle-check' : 'fa-flag-checkered' }}"></i></div>
                                    <div class="pipe-text"><strong>3. Resolved</strong><span>Stabilized & archived</span></div>
                                </div>
                            </div>

                            @if($canReportIncidents)
                            <!-- Triage & Action Form -->
                            <section class="incident-detail-section incident-action-section">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                    <h4 class="incident-section-title" style="margin: 0;"><i class="fa-solid fa-sliders"></i> Triage Decision &amp; Workflow Action</h4>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="triage-quick-btn dispatch" onclick="setTriageAction('{{ $incident->id }}', 'escalate_maintenance')">
                                            <i class="fa-solid fa-screwdriver-wrench"></i> Dispatch Maintenance
                                        </button>
                                        <button type="button" class="triage-quick-btn event" onclick="setTriageAction('{{ $incident->id }}', 'operational_event')">
                                            <i class="fa-solid fa-calendar-check"></i> Valid Event (No Repair)
                                        </button>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('energy-incidents.update', $incident) }}" class="incident-action-form" id="incident_form_{{ $incident->id }}" onclick="event.stopPropagation()">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="triage_action" id="triage_action_{{ $incident->id }}" value="direct_update">

                                    <div id="operational_event_box_{{ $incident->id }}" class="operational-event-box" style="display: none; margin-bottom: 14px; padding: 12px 14px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                                        <div style="font-weight: 800; font-size: 0.85rem; color: #065f46; margin-bottom: 6px;">
                                            <i class="fa-solid fa-circle-info"></i> Resolving as Operational Event (Non-Defect)
                                        </div>
                                        <p style="font-size: 0.78rem; color: #047857; margin: 0 0 8px 0;">
                                            This will resolve the incident as a known high-usage activity and prevent sending an unnecessary repair order to the maintenance team.
                                        </p>
                                        <label for="op_reason_{{ $incident->id }}" style="font-size: 0.75rem; font-weight: 700; color: #065f46; display: block; margin-bottom: 4px;">Reason / Event Name:</label>
                                        <select name="operational_reason" id="op_reason_{{ $incident->id }}" class="action-select" style="background: #ffffff;">
                                            <option value="Community / Sports Tournament Event">Community / Sports Tournament Event</option>
                                            <option value="Barangay Fiesta / Cultural Celebration">Barangay Fiesta / Cultural Celebration</option>
                                            <option value="Emergency Disaster Response / Evacuation Center">Emergency Disaster Response / Evacuation Center</option>
                                            <option value="Seasonal Weather Overload (Extreme Summer Heat)">Seasonal Weather Overload (Extreme Summer Heat)</option>
                                            <option value="Authorized System Load Testing / Calibration">Authorized System Load Testing / Calibration</option>
                                        </select>
                                    </div>

                                    <div class="action-form-grid">
                                        <div class="action-field">
                                            <label for="incident_status_{{ $incident->id }}">Workflow Status *</label>
                                            <select name="status" id="incident_status_{{ $incident->id }}" class="action-select" required>
                                                <option value="Open" {{ $statusKey === 'open' ? 'selected' : '' }}>🟡 Open / Under Evaluation</option>
                                                <option value="Ongoing" {{ $statusKey === 'ongoing' ? 'selected' : '' }}>🔵 Ongoing / Dispatched to CIMM</option>
                                                <option value="Resolved" {{ $statusKey === 'resolved' ? 'selected' : '' }}>🟢 Resolved &amp; Stabilized</option>
                                            </select>
                                        </div>
                                        <div class="action-field">
                                            <label for="incident_asset_{{ $incident->id }}">Affected Meter / Sub-meter</label>
                                            <select name="affected_asset" id="incident_asset_{{ $incident->id }}" class="action-select">
                                                <option value="">-- Select Meter / Sub-meter --</option>
                                                <optgroup label="⚡ Main Meter &amp; Panel">
                                                    <option value="Main Utility Meter" {{ $incident->affected_asset === 'Main Utility Meter' ? 'selected' : '' }}>Main Utility Meter</option>
                                                    <option value="Main Distribution Panel (MDP)" {{ $incident->affected_asset === 'Main Distribution Panel (MDP)' ? 'selected' : '' }}>Main Distribution Panel (MDP)</option>
                                                </optgroup>
                                                @if($incident->facility && $incident->facility->submeters && $incident->facility->submeters->count() > 0)
                                                    <optgroup label="📊 Facility Sub-meters">
                                                        @foreach($incident->facility->submeters as $sub)
                                                            <option value="Sub-meter: {{ $sub->submeter_name }}" {{ $incident->affected_asset === 'Sub-meter: ' . $sub->submeter_name ? 'selected' : '' }}>Sub-meter: {{ $sub->submeter_name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                                <optgroup label="🔌 Other Electrical Line">
                                                    <option value="Other Electrical Line / Feeder" {{ $incident->affected_asset === 'Other Electrical Line / Feeder' ? 'selected' : '' }}>Other Electrical Line / Feeder</option>
                                                </optgroup>
                                                @if(!empty($incident->affected_asset) && !in_array($incident->affected_asset, [
                                                    'Main Utility Meter', 'Main Distribution Panel (MDP)', 'Other Electrical Line / Feeder'
                                                ], true) && !str_starts_with($incident->affected_asset, 'Sub-meter:'))
                                                    <option value="{{ $incident->affected_asset }}" selected>{{ $incident->affected_asset }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="action-field full">
                                            <label for="incident_action_{{ $incident->id }}">Immediate Action Taken</label>
                                            <input type="text" name="immediate_action" id="incident_action_{{ $incident->id }}" class="action-input" value="{{ $incident->immediate_action }}" placeholder="e.g. Isolated faulty sub-panel and dispatched technician">
                                        </div>
                                        <div class="action-field full">
                                            <label for="incident_resolution_{{ $incident->id }}">Resolution Summary / Technical Findings</label>
                                            <textarea name="resolution_summary" id="incident_resolution_{{ $incident->id }}" rows="2" class="action-textarea" placeholder="Detail the root cause and repair action completed...">{{ $incident->resolution_summary }}</textarea>
                                        </div>
                                        <div class="action-field full">
                                            <label for="incident_prev_{{ $incident->id }}">Preventive Recommendation</label>
                                            <input type="text" name="preventive_recommendation" id="incident_prev_{{ $incident->id }}" class="action-input" value="{{ $incident->preventive_recommendation }}" placeholder="e.g. Schedule bi-weekly load audit">
                                        </div>
                                    </div>
                                    <div class="action-form-footer">
                                        <span class="sync-hint"><i class="fa-solid fa-arrows-rotate"></i> Syncs directly with CIMM Maintenance</span>
                                        <button type="submit" class="action-submit-btn"><i class="fa-solid fa-floppy-disk"></i> Save &amp; Sync Workflow</button>
                                    </div>
                                </form>
                            </section>
                            @endif

                            <section class="incident-detail-section">
                                <h4 class="incident-section-title"><i class="fa-solid fa-chart-simple"></i> Incident snapshot</h4>
                                <div class="detail-grid">
                                    <div class="detail-item"><span>Facility</span><strong>{{ $facilityName }}</strong></div>
                                    <div class="detail-item"><span>Reporting Period</span><strong>{{ $monthLabel }}/{{ $yearNum ?? '-' }}</strong></div>
                                    <div class="detail-item is-deviation"><span>Deviation</span><strong>{{ $deviationText }}</strong></div>
                                    <div class="detail-item"><span>Date Detected</span><strong>{{ $dateDetected }}</strong></div>
                                    <div class="detail-item"><span>Actual Reading</span><strong>{{ $actualKwh }}</strong></div>
                                    <div class="detail-item"><span>Baseline</span><strong>{{ $baselineKwh }}</strong></div>
                                    <div class="detail-item"><span>Data Source</span><strong>{{ $sourceLabel }}</strong></div>
                                    <div class="detail-item"><span>Action Owner</span><strong>CIMM Maintenance Integration</strong></div>
                                    <div class="detail-item"><span>Category</span><strong>{{ $categoryLabel }}</strong></div>
                                    <div class="detail-item"><span>Affected Asset</span><strong>{{ $incident->affected_asset ?: 'Not specified' }}</strong></div>
                                </div>
                            </section>

                            <section class="incident-detail-section">
                                <h4 class="incident-section-title"><i class="fa-solid fa-clipboard-check"></i> Assessment and response</h4>
                                <div class="incident-narrative-grid">
                                    <div class="detail-block is-wide"><span>Description</span><p>{{ $descriptionText }}</p></div>
                                    <div class="detail-block"><span>Probable Cause</span><p>{{ $probableCause }}</p></div>
                                    <div class="detail-block"><span>Immediate Action</span><p>{{ $immediateAction }}</p></div>
                                    <div class="detail-block"><span>Resolution</span><p>{{ $resolutionSummary }}</p></div>
                                    <div class="detail-block"><span>Preventive Recommendation</span><p>{{ $preventiveRecommendation }}</p></div>
                                </div>
                            </section>

                            @if($incident->evidence_path)
                                <div class="detail-block attachment-block">
                                    <span>Reporter Evidence</span>
                                    <p><a href="{{ asset('storage/' . ltrim($incident->evidence_path, '/')) }}" target="_blank" rel="noopener"><i class="fa-solid fa-image"></i> View attached photo</a></p>
                                </div>
                            @endif

                            @if(count($attachments))
                                <div class="detail-block attachment-block">
                                    <span>Attachments</span>
                                    <ul class="attachment-list">
                                        @foreach($attachments as $attachment)
                                            @if(is_string($attachment) && trim($attachment) !== '')
                                                <li>
                                                    <a href="{{ asset('storage/' . ltrim($attachment, '/')) }}" target="_blank" rel="noopener">
                                                        <i class="fa-solid fa-paperclip"></i> {{ basename($attachment) }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="modal-actions">
                                <a href="{{ route('modules.maintenance.index') }}?facility_id={{ $incident->facility->id ?? '' }}" class="maintenance-btn">
                                    View CIMM Maintenance <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <div class="cimm-managed-note">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                    <span><strong>CIMM synchronized</strong>Status updates automatically</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">No incidents found for the selected period.</div>
            @endforelse

        </div>

        @if(method_exists($incidents, 'links'))
            <div class="incident-pagination">
                {{ $incidents->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>

@if($canReportIncidents)
<div id="reportIncidentModal" class="report-modal" hidden aria-hidden="true">
    <div class="report-modal-content" role="dialog" aria-modal="true" aria-labelledby="reportIncidentTitle">
        <button type="button" class="incident-modal-close" id="closeReportIncident" aria-label="Close report form">&times;</button>
        <div class="report-modal-heading">
            <div>
                <h3 id="reportIncidentTitle">Report Incident</h3>
                <p>This creates an Open incident and forwards a Pending corrective-maintenance request to CIMM.</p>
            </div>
        </div>

        @if(count($incidentFormErrors))
            <div class="report-errors">
                @foreach($incidentFormErrors as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('energy-incidents.store') }}" enctype="multipart/form-data" class="report-form">
            @csrf
            <div class="report-field">
                <label for="report_facility_id">Facility *</label>
                <select id="report_facility_id" name="facility_id" required onchange="onReportFacilityChange()">
                    <option value="">Select facility</option>
                    @foreach($reportFacilities ?? [] as $facility)
                        <option value="{{ $facility->id }}" 
                                data-submeters="{{ json_encode($facility->submeters?->map(fn($s) => ['id' => $s->id, 'name' => $s->submeter_name]) ?? []) }}"
                                @selected((string) old('facility_id', request('facility_id')) === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-field">
                <label for="report_category">Incident Category *</label>
                <select id="report_category" name="category" required>
                    <option value="">Select category</option>
                    @foreach($manualIncidentCategories ?? [] as $category)
                        <option value="{{ $category['key'] }}" @selected(old('category') === $category['key'])>{{ $category['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="report-field full">
                <label for="report_affected_asset">Affected Meter / Equipment / Panel</label>
                <select id="report_affected_asset" name="affected_asset" class="action-select">
                    <option value="Main Utility Meter">⚡ Main Utility Meter (Facility Main)</option>
                    <option value="Main Distribution Panel (MDP)">Main Distribution Panel (MDP)</option>
                </select>
                <small class="field-help" style="font-size: 0.73rem; color: #64748b; margin-top: 4px; display: block;">
                    Choose <strong>Main Meter</strong> or select the specific <strong>Sub-meter</strong> / equipment affected.
                </small>
            </div>
            <div class="report-field">
                <label for="report_detected_at">Date and Time Detected *</label>
                <input id="report_detected_at" type="datetime-local" name="detected_at" value="{{ old('detected_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="report-field full">
                <label for="report_description">Observed Problem *</label>
                <textarea id="report_description" name="description" rows="4" maxlength="2000" required placeholder="Describe what happened, where it was observed, and any immediate safety concern.">{{ old('description') }}</textarea>
            </div>
            <div class="report-field full">
                <label for="report_evidence">Photo Evidence <span>(optional, max 5 MB)</span></label>
                <input id="report_evidence" type="file" name="evidence" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="report-form-note"><i class="fa-solid fa-circle-info"></i> CIMM will own scheduling, assignment, action, and completion.</div>
            <div class="report-form-actions">
                <button type="button" class="report-cancel" id="cancelReportIncident">Cancel</button>
                <button type="submit" class="report-submit"><i class="fa-solid fa-paper-plane"></i> Submit to CIMM</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function onReportFacilityChange() {
    const facSelect = document.getElementById('report_facility_id');
    const assetSelect = document.getElementById('report_affected_asset');
    if (!facSelect || !assetSelect) return;
    
    assetSelect.innerHTML = '';

    const selectedOpt = facSelect.options[facSelect.selectedIndex];
    const facName = selectedOpt && selectedOpt.value ? selectedOpt.textContent.trim() : '';

    if (!facName) {
        assetSelect.add(new Option('-- Select Facility first --', ''));
        assetSelect.add(new Option('⚡ Main Utility Meter (Facility Main)', 'Main Utility Meter'));
        return;
    }

    // Category 1: Main Meter
    const optMain = document.createElement('optgroup');
    optMain.label = '⚡ Main Facility Meter';
    const mainOpt = new Option('Main Utility Meter (' + facName + ')', 'Main Utility Meter');
    mainOpt.selected = true;
    optMain.appendChild(mainOpt);
    optMain.appendChild(new Option('Main Distribution Panel (MDP)', 'Main Distribution Panel (MDP)'));
    assetSelect.appendChild(optMain);

    // Category 2: Facility Sub-meters
    let submeters = [];
    if (selectedOpt.dataset.submeters) {
        try {
            submeters = JSON.parse(selectedOpt.dataset.submeters);
        } catch(e){}
    }

    const optSub = document.createElement('optgroup');
    optSub.label = '📊 Sub-meters in ' + facName;
    if (submeters && submeters.length > 0) {
        submeters.forEach(s => {
            optSub.appendChild(new Option('Sub-meter: ' + s.name, 'Sub-meter: ' + s.name));
        });
    } else {
        const noSubOpt = new Option('(No sub-meters registered for this facility)', '');
        noSubOpt.disabled = true;
        optSub.appendChild(noSubOpt);
    }
    assetSelect.appendChild(optSub);

    // Category 3: General Electrical Line
    const optEq = document.createElement('optgroup');
    optEq.label = '🔌 Other Electrical Line';
    optEq.appendChild(new Option('Other Electrical Line / Feeder', 'Other Electrical Line / Feeder'));
    assetSelect.appendChild(optEq);
}

function openIncidentModal(id) {
    const modal = document.getElementById('incident-modal-' + id);
    if (!modal) return;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeIncidentModal(id) {
    const modal = document.getElementById('incident-modal-' + id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function setTriageAction(id, action) {
    const hiddenInput = document.getElementById('triage_action_' + id);
    const opBox = document.getElementById('operational_event_box_' + id);
    const statusSelect = document.getElementById('incident_status_' + id);

    if (hiddenInput) hiddenInput.value = action;

    if (action === 'operational_event') {
        if (opBox) opBox.style.display = 'block';
        if (statusSelect) statusSelect.value = 'Resolved';
    } else if (action === 'escalate_maintenance') {
        if (opBox) opBox.style.display = 'none';
        if (statusSelect) statusSelect.value = 'Ongoing';
        const actionInput = document.getElementById('incident_action_' + id);
        if (actionInput && !actionInput.value) {
            actionInput.value = 'Dispatched to CIMM Maintenance for physical inspection and repair.';
        }
    } else {
        if (opBox) opBox.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    onReportFacilityChange();
    const rows = Array.from(document.querySelectorAll('.incident-list-row'));
    const reportModal = document.getElementById('reportIncidentModal');
    const openReportButton = document.getElementById('openReportIncident');
    const closeReportButton = document.getElementById('closeReportIncident');
    const cancelReportButton = document.getElementById('cancelReportIncident');
    const toggleReportModal = (show) => {
        if (!reportModal) return;
        reportModal.hidden = !show;
        reportModal.setAttribute('aria-hidden', show ? 'false' : 'true');
        document.body.style.overflow = show ? 'hidden' : '';
    };
    openReportButton?.addEventListener('click', () => toggleReportModal(true));
    closeReportButton?.addEventListener('click', () => toggleReportModal(false));
    cancelReportButton?.addEventListener('click', () => toggleReportModal(false));
    reportModal?.addEventListener('click', (event) => {
        if (event.target === reportModal) toggleReportModal(false);
    });
    @if(count($incidentFormErrors) || (request()->boolean('report') && $canReportIncidents)) toggleReportModal(true); @endif

    rows.forEach((row) => {
        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const id = row.dataset.id;
                if (id) openIncidentModal(id);
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        toggleReportModal(false);
        document.querySelectorAll('.incident-modal').forEach((modal) => {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        });
        document.body.style.overflow = '';
    });

    document.querySelectorAll('.incident-modal').forEach((modal) => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
        });
    });
});
</script>

<style>
.alert-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
}
.alert-toast.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.alert-toast.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.incident-page {
    width: 100%;
}

.triage-quick-btn {
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 0.76rem;
    font-weight: 750;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.triage-quick-btn.dispatch {
    background: #eff6ff;
    border: 1px solid #93c5fd;
    color: #1d4ed8;
}
.triage-quick-btn.dispatch:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
.triage-quick-btn.event {
    background: #f0fdf4;
    border: 1px solid #86efac;
    color: #15803d;
}
.triage-quick-btn.event:hover {
    background: #16a34a;
    color: #ffffff;
    border-color: #16a34a;
}
body.dark-mode .triage-quick-btn.dispatch {
    background: #1e293b;
    border-color: #3b82f6;
    color: #93c5fd;
}
body.dark-mode .triage-quick-btn.event {
    background: #064e3b;
    border-color: #10b981;
    color: #6ee7b7;
}

.incident-shell {
    background: #f8fafc;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(37, 99, 235, 0.09);
    padding: 28px 22px;
}

.incident-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 16px;
}

.incident-header h2 {
    margin: 0;
    color: #1e293b;
    font-size: 1.55rem;
    font-weight: 800;
}

.incident-header p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 0.93rem;
}

.history-btn {
    background: linear-gradient(90deg, #6366f1, #2563eb);
    color: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.header-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.download-btn {
    background: #0f766e;
    color: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.report-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #b91c1c;
    border-radius: 10px;
    background: #dc2626;
    color: #fff;
    padding: 10px 14px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}

.report-btn:hover { background: #b91c1c; }

.report-modal {
    position: fixed;
    inset: 0;
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(15, 23, 42, 0.55);
}

.report-modal[hidden] { display: none; }

.report-modal-content {
    position: relative;
    width: min(760px, 96vw);
    max-height: 92vh;
    overflow-y: auto;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    padding: 22px;
}

.report-modal-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding-right: 30px;
    margin-bottom: 18px;
}

.report-modal-heading h3 { margin: 0; color: #0f172a; font-size: 1.3rem; font-weight: 900; }
.report-modal-heading p { margin: 5px 0 0; color: #64748b; font-size: 0.88rem; line-height: 1.4; }

.report-errors {
    margin-bottom: 14px;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #b91c1c;
    padding: 10px 12px;
    font-size: 0.84rem;
    font-weight: 700;
}

.report-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.report-field { display: flex; flex-direction: column; gap: 6px; }
.report-field.full, .report-form-note, .report-form-actions { grid-column: 1 / -1; }
.report-field label { color: #334155; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; }
.report-field label span { color: #94a3b8; font-weight: 700; text-transform: none; }
.report-field input, .report-field select, .report-field textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #1e293b;
    padding: 10px 11px;
    font: inherit;
}
.report-field textarea { resize: vertical; min-height: 105px; }
.report-field input:focus, .report-field select:focus, .report-field textarea:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
}

.report-form-note {
    display: flex;
    align-items: center;
    gap: 7px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 10px 12px;
    font-size: 0.82rem;
    font-weight: 700;
}

.report-form-actions { display: flex; justify-content: flex-end; gap: 9px; }
.report-form-actions button { border-radius: 10px; padding: 10px 14px; font-weight: 800; cursor: pointer; }
.report-cancel { border: 1px solid #cbd5e1; background: #fff; color: #334155; }
.report-submit { border: 1px solid #1d4ed8; background: #2563eb; color: #fff; }
.report-submit:hover { background: #1d4ed8; }

.download-btn:hover {
    background: #0d9488;
}

.incident-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.metric-card {
    border-radius: 12px;
    padding: 12px 14px;
    border: 1px solid transparent;
}

.metric-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
}

.metric-value {
    font-size: 1.45rem;
    font-weight: 900;
    line-height: 1;
}

.metric-card.total { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.metric-card.critical { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
.metric-card.open { background: #fffbeb; border-color: #fde68a; color: #a16207; }
.metric-card.pending { background: #fff7ed; border-color: #fdba74; color: #c2410c; }
.metric-card.ongoing { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }

.incident-filters {
    display: grid;
    grid-template-columns: minmax(170px, 1.5fr) repeat(3, minmax(115px, 0.75fr)) minmax(100px, 0.65fr) minmax(110px, 0.65fr) minmax(145px, 0.8fr) auto;
    gap: 10px;
    margin-bottom: 14px;
}

.incident-filters input,
.incident-filters select {
    border: 1px solid #dbe2ef;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.92rem;
    color: #1f2937;
    background: #fff;
}

.incident-filters input:focus,
.incident-filters select:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.22);
}

.filter-actions {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}

.filter-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    padding: 9px 12px;
    font-size: 0.85rem;
    font-weight: 800;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    white-space: nowrap;
}

.filter-btn.apply {
    background: #2563eb;
    color: #fff;
    border-color: #1d4ed8;
}

.filter-btn.apply:hover {
    background: #1d4ed8;
}

.filter-btn.clear {
    background: #fff;
    color: #334155;
    border-color: #cbd5e1;
}

.filter-btn.clear:hover {
    background: #f8fafc;
}

.incident-list-container {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.incident-list-row {
    border-bottom: 1px solid #edf2f7;
    cursor: pointer;
    transition: background 0.16s ease, transform 0.16s ease;
}

.incident-list-row:hover,
.incident-list-row:focus {
    background: #f8fbff;
    transform: translateY(-1px);
    outline: none;
}

.incident-list-row:last-child {
    border-bottom: none;
}

.row-main {
    display: grid;
    grid-template-columns: 2.2fr 1.25fr 0.9fr 1fr 0.7fr;
    gap: 12px;
    align-items: center;
    padding: 14px 16px;
}

.facility-name {
    font-size: 1.02rem;
    font-weight: 800;
    color: #0f172a;
}

.facility-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.source-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 7px;
    border-radius: 999px;
    border: 1px solid;
    font-size: 0.62rem;
    font-weight: 900;
    line-height: 1;
    text-transform: uppercase;
    white-space: nowrap;
}

.source-chip.cprf { background: #f0fdfa; border-color: #99f6e4; color: #0f766e; }
.source-chip.auto { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.source-chip.manual { background: #faf5ff; border-color: #d8b4fe; color: #7e22ce; }

.facility-desc {
    margin-top: 4px;
    color: #64748b;
    font-size: 0.86rem;
    line-height: 1.35;
}

.meta-col {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid transparent;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.35px;
}

.chip.severity.critical { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.chip.severity.very-high { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
.chip.severity.high { background: #ffedd5; color: #c2410c; border-color: #fdba74; }
.chip.severity.warning { background: #fffbeb; color: #a16207; border-color: #fde68a; }
.chip.severity.normal { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

.chip.status.open { background: #fffbeb; color: #a16207; border-color: #fde68a; }
.chip.status.pending { background: #fff7ed; color: #c2410c; border-color: #fdba74; }
.chip.status.ongoing { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.chip.status.resolved { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

.value-label {
    color: #64748b;
    font-size: 0.73rem;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    font-weight: 700;
}

.value-main {
    color: #1e293b;
    font-weight: 800;
    margin-top: 2px;
}

.value-main.up { color: #dc2626; }
.value-main.down { color: #16a34a; }

.value-sub {
    color: #94a3b8;
    font-size: 0.78rem;
    margin-top: 2px;
}

.action-col {
    text-align: right;
}

.detail-btn {
    background: #eef2ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 0.78rem;
    font-weight: 800;
    cursor: pointer;
}

.detail-btn:hover {
    background: #e0e7ff;
}

.empty-state {
    text-align: center;
    color: #64748b;
    padding: 20px 16px;
}

.incident-modal {
    position: fixed;
    inset: 0;
    padding: 24px;
    background: rgba(15, 23, 42, 0.58);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1200;
}

.incident-modal-content {
    width: min(940px, 100%);
    max-height: calc(100dvh - 48px);
    overflow: hidden;
    background: #ffffff;
    border: 1px solid rgba(255, 255, 255, .72);
    border-radius: 22px;
    box-shadow: 0 28px 80px rgba(15, 23, 42, 0.34);
    position: relative;
    display: flex;
    flex-direction: column;
}

.incident-modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    z-index: 3;
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #f8fafc;
    font-size: 1.65rem;
    line-height: 1;
    color: #64748b;
    cursor: pointer;
    transition: .16s ease;
}

.incident-modal-close:hover {
    color: #be123c;
    border-color: #fecdd3;
    background: #fff1f2;
    transform: rotate(4deg);
}

.modal-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    flex: 0 0 auto;
    padding: 20px 68px 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #ffffff 35%, #f8fbff);
}

.modal-title-group {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 13px;
}

.modal-title-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: linear-gradient(145deg, #dc2626, #f97316);
    color: #fff;
    box-shadow: 0 8px 20px rgba(220, 38, 38, .22);
}

.modal-eyebrow {
    display: block;
    margin-bottom: 2px;
    color: #dc2626;
    font-size: .66rem;
    font-weight: 900;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.modal-top h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1.22rem;
    font-weight: 900;
}

.modal-top p {
    margin: 3px 0 0;
    color: #64748b;
    font-size: .75rem;
}

.modal-chip-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
}

.incident-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #fecaca;
    border-radius: 999px;
    background: #fff1f2;
    color: #be123c;
    min-height: 32px;
    padding: 6px 11px;
    font-size: 0.7rem;
    font-weight: 900;
    text-decoration: none;
    text-transform: uppercase;
}

.incident-modal-body {
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 20px 24px 22px;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 transparent;
}

.incident-detail-section + .incident-detail-section {
    margin-top: 20px;
}

.incident-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 11px;
    color: #334155;
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.incident-section-title i {
    color: #2563eb;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 9px;
}

.detail-item {
    min-width: 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 11px 13px;
    background: #f8fafc;
}

.detail-item.is-deviation {
    border-color: #fecdd3;
    background: #fff7f7;
}

.detail-item.is-deviation strong {
    color: #be123c;
}

.detail-item span {
    display: block;
    color: #64748b;
    font-size: 0.75rem;
    margin-bottom: 3px;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    font-weight: 700;
}

.detail-item strong {
    display: block;
    color: #0f172a;
    font-size: .91rem;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.incident-narrative-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.detail-block {
    min-width: 0;
    padding: 13px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
}

.detail-block.is-wide {
    grid-column: 1 / -1;
}

.detail-block span {
    display: block;
    color: #334155;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 800;
    margin-bottom: 5px;
}

.detail-block p {
    margin: 0;
    color: #475569;
    line-height: 1.55;
    font-size: 0.88rem;
}

.attachment-block {
    margin-top: 10px;
    background: #f8fafc;
}

.attachment-list {
    margin: 0;
    padding-left: 18px;
}

.attachment-list a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 700;
}

.attachment-list a:hover {
    text-decoration: underline;
}

.modal-actions {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.cimm-managed-note {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #99f6e4;
    border-radius: 10px;
    background: #f0fdfa;
    color: #0f766e;
    padding: 8px 11px;
    font-size: 0.78rem;
    font-weight: 800;
}

.cimm-managed-note > i {
    width: 28px;
    height: 28px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #ccfbf1;
}

.cimm-managed-note span {
    display: grid;
    gap: 1px;
    font-size: .69rem;
}

.cimm-managed-note strong {
    color: #115e59;
    font-size: .74rem;
}

.maintenance-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-weight: 800;
    min-height: 42px;
    padding: 10px 15px;
    border-radius: 11px;
    color: #fff;
    background: linear-gradient(90deg, #2563eb, #6366f1);
    box-shadow: 0 7px 16px rgba(37, 99, 235, .2);
}

.incident-pagination {
    margin-top: 14px;
    display: flex;
    justify-content: flex-end;
}

/* Page-level dark mode */
body.dark-mode .incident-page .incident-shell {
    background: #0f172a;
    border: 1px solid #334155;
    box-shadow: 0 18px 34px rgba(2, 6, 23, 0.5);
}
body.dark-mode .incident-page .incident-header h2,
body.dark-mode .incident-page .facility-name,
body.dark-mode .incident-page .value-main,
body.dark-mode .incident-page .modal-top h3,
body.dark-mode .incident-page .detail-item strong {
    color: #e2e8f0;
}
body.dark-mode .incident-page .incident-header p,
body.dark-mode .incident-page .facility-desc,
body.dark-mode .incident-page .value-label,
body.dark-mode .incident-page .value-sub,
body.dark-mode .incident-page .empty-state,
body.dark-mode .incident-page .detail-item span,
body.dark-mode .incident-page .detail-block span,
body.dark-mode .incident-page .detail-block p {
    color: #94a3b8;
}
body.dark-mode .incident-page .metric-card {
    border-color: #334155;
}
body.dark-mode .incident-page .metric-card.total {
    background: rgba(37, 99, 235, 0.22);
    color: #93c5fd;
    border-color: rgba(147, 197, 253, 0.3);
}
body.dark-mode .incident-page .metric-card.critical {
    background: rgba(190, 24, 93, 0.24);
    color: #fda4af;
    border-color: rgba(244, 114, 182, 0.3);
}
body.dark-mode .incident-page .metric-card.open {
    background: rgba(146, 64, 14, 0.26);
    color: #fde68a;
    border-color: rgba(251, 191, 36, 0.35);
}
body.dark-mode .incident-page .metric-card.pending {
    background: rgba(194, 65, 12, 0.24);
    color: #fdba74;
    border-color: rgba(251, 146, 60, 0.3);
}
body.dark-mode .incident-page .metric-card.ongoing {
    background: rgba(14, 116, 144, 0.24);
    color: #67e8f9;
    border-color: rgba(125, 211, 252, 0.3);
}
body.dark-mode .incident-page .incident-filters input,
body.dark-mode .incident-page .incident-filters select {
    background: #0b1220;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .incident-page .incident-filters input::placeholder {
    color: #64748b;
}
body.dark-mode .incident-page .filter-btn.clear {
    background: #111827;
    color: #e2e8f0;
    border-color: #475569;
}
body.dark-mode .incident-page .download-btn {
    background: #0f766e;
}
body.dark-mode .incident-page .download-btn:hover {
    background: #14b8a6;
}
body.dark-mode .incident-page .incident-list-container {
    background: #111827;
    border-color: #334155;
}
body.dark-mode .incident-page .incident-list-row {
    border-bottom-color: #334155;
}
body.dark-mode .incident-page .incident-list-row:hover,
body.dark-mode .incident-page .incident-list-row:focus {
    background: #1f2937;
}
body.dark-mode .incident-page .chip.severity.critical {
    background: rgba(127, 29, 29, 0.32);
    color: #fca5a5;
    border-color: rgba(248, 113, 113, 0.35);
}
body.dark-mode .incident-page .chip.severity.very-high {
    background: rgba(190, 24, 93, 0.28);
    color: #f9a8d4;
    border-color: rgba(244, 114, 182, 0.34);
}
body.dark-mode .incident-page .chip.severity.high {
    background: rgba(146, 64, 14, 0.3);
    color: #fdba74;
    border-color: rgba(251, 146, 60, 0.34);
}
body.dark-mode .incident-page .chip.severity.warning {
    background: rgba(146, 64, 14, 0.24);
    color: #fde68a;
    border-color: rgba(251, 191, 36, 0.35);
}
body.dark-mode .incident-page .chip.severity.normal {
    background: rgba(22, 101, 52, 0.24);
    color: #86efac;
    border-color: rgba(74, 222, 128, 0.3);
}
body.dark-mode .incident-page .chip.status.open {
    background: rgba(146, 64, 14, 0.26);
    color: #fde68a;
    border-color: rgba(251, 191, 36, 0.35);
}
body.dark-mode .incident-page .chip.status.pending {
    background: rgba(194, 65, 12, 0.24);
    color: #fdba74;
    border-color: rgba(251, 146, 60, 0.3);
}
body.dark-mode .incident-page .chip.status.ongoing {
    background: rgba(14, 116, 144, 0.24);
    color: #67e8f9;
    border-color: rgba(125, 211, 252, 0.3);
}
body.dark-mode .incident-page .chip.status.resolved {
    background: rgba(22, 101, 52, 0.24);
    color: #86efac;
    border-color: rgba(74, 222, 128, 0.3);
}
body.dark-mode .incident-page .detail-btn {
    background: #1e3a8a;
    border-color: #1d4ed8;
    color: #dbeafe;
}
body.dark-mode .incident-page .detail-btn:hover {
    background: #1d4ed8;
}
body.dark-mode .incident-page .incident-modal {
    background: rgba(2, 6, 23, 0.7);
}
body.dark-mode .incident-page .incident-modal-content {
    background: #111827;
    border: 1px solid #334155;
}
body.dark-mode .incident-page .modal-top {
    border-color: #334155;
    background: linear-gradient(135deg, #111827 35%, #0f172a);
}
body.dark-mode .incident-page .modal-top p {
    color: #94a3b8;
}
body.dark-mode .incident-page .incident-modal-close {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
body.dark-mode .incident-page .incident-modal-close:hover {
    color: #fda4af;
}
body.dark-mode .incident-page .detail-item {
    background: #0f172a;
    border-color: #334155;
}
body.dark-mode .incident-page .detail-item.is-deviation {
    background: rgba(190, 24, 93, .12);
    border-color: rgba(251, 113, 133, .35);
}
body.dark-mode .incident-page .detail-item.is-deviation strong {
    color: #fda4af;
}
body.dark-mode .incident-page .incident-section-title {
    color: #cbd5e1;
}
body.dark-mode .incident-page .incident-section-title i {
    color: #60a5fa;
}
body.dark-mode .incident-page .detail-block {
    background: #0f172a;
    border-color: #334155;
}
body.dark-mode .incident-page .modal-actions {
    border-color: #334155;
}
body.dark-mode .incident-page .cimm-managed-note {
    background: rgba(13, 148, 136, .12);
    border-color: rgba(45, 212, 191, .3);
    color: #99f6e4;
}
body.dark-mode .incident-page .cimm-managed-note > i {
    background: rgba(13, 148, 136, .2);
}
body.dark-mode .incident-page .cimm-managed-note strong {
    color: #ccfbf1;
}
body.dark-mode .incident-page .attachment-list a {
    color: #93c5fd;
}
body.dark-mode .report-modal-content {
    background: #111827;
    border: 1px solid #334155;
}
body.dark-mode .report-modal-heading h3,
body.dark-mode .report-field label {
    color: #e2e8f0;
}
body.dark-mode .report-modal-heading p {
    color: #94a3b8;
}
body.dark-mode .report-field input,
body.dark-mode .report-field select,
body.dark-mode .report-field textarea {
    background: #0b1220;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .report-cancel {
    background: #1f2937;
    border-color: #475569;
    color: #e2e8f0;
}

@media (max-width: 1024px) {
    .incident-metrics {
        grid-template-columns: repeat(3, minmax(120px, 1fr));
    }
    .row-main {
        grid-template-columns: 1.8fr 1.2fr 0.9fr 1fr;
    }
    .action-col {
        grid-column: 1 / -1;
        text-align: left;
    }
}

@media (max-width: 760px) {
    .incident-shell {
        padding: 16px 12px;
    }
    .incident-header {
        flex-direction: column;
        align-items: stretch;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions a {
        flex: 1;
        justify-content: center;
    }
    .report-btn {
        flex: 1 0 100%;
        justify-content: center;
    }
    .report-form {
        grid-template-columns: 1fr;
    }
    .report-field,
    .report-field.full,
    .report-form-note,
    .report-form-actions {
        grid-column: 1;
    }
    .report-modal-heading {
        flex-direction: column;
    }
    .history-btn {
        justify-content: center;
    }
    .incident-metrics {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
    .incident-filters {
        grid-template-columns: 1fr;
    }
    .filter-actions {
        width: 100%;
    }
    .filter-btn {
        flex: 1;
    }
    .row-main {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        padding: 0;
    }
    .incident-list-container {
        display: grid;
        gap: 12px;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
    }
    .incident-list-row {
        position: relative;
        overflow: hidden;
        border: 1px solid #dbe4f2;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }
    .incident-list-row::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: #ef4444;
    }
    .incident-list-row[data-level="very-high"]::before { background: #f43f5e; }
    .incident-list-row[data-level="high"]::before { background: #f97316; }
    .incident-list-row[data-level="warning"]::before { background: #eab308; }
    .incident-list-row[data-level="normal"]::before { background: #22c55e; }
    .incident-list-row:hover,
    .incident-list-row:focus {
        transform: none;
        border-color: #93c5fd;
        box-shadow: 0 7px 18px rgba(37, 99, 235, .12);
    }
    .facility-col {
        grid-column: 1 / -1;
        padding: 14px 14px 10px 17px;
    }
    .facility-name { font-size: .98rem; }
    .facility-desc {
        margin-top: 5px;
        font-size: .82rem;
        line-height: 1.45;
    }
    .meta-col {
        grid-column: 1 / -1;
        padding: 0 14px 12px 17px;
    }
    .value-col {
        min-width: 0;
        padding: 11px 14px;
        border-top: 1px solid #edf2f7;
        background: #fcfdff;
    }
    .value-col + .value-col { border-left: 1px solid #edf2f7; }
    .value-main { font-size: .93rem; }
    .action-col {
        grid-column: 1 / -1;
        padding: 11px 14px 13px;
        border-top: 1px solid #edf2f7;
        text-align: center;
    }
    .detail-btn {
        width: 100%;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: .82rem;
    }
    .detail-btn i { transition: transform .16s ease; }
    .detail-btn:hover i { transform: translateX(3px); }
    body.dark-mode .incident-page .incident-list-container { background: transparent; border-color: transparent; }
    body.dark-mode .incident-page .incident-list-row { background: #111827; border-color: #334155; }
    body.dark-mode .incident-page .value-col,
    body.dark-mode .incident-page .action-col { background: #0f172a; border-color: #334155; }
    body.dark-mode .incident-page .value-col + .value-col { border-left-color: #334155; }
    .incident-modal { padding: 12px; }
    .incident-modal-content {
        width: 100%;
        max-height: calc(100dvh - 24px);
        border-radius: 14px;
    }
    .incident-modal-close {
        top: 13px;
        right: 13px;
    }
    .incident-modal-body {
        padding: 16px;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .incident-narrative-grid {
        grid-template-columns: 1fr;
    }
    .detail-block.is-wide {
        grid-column: auto;
    }
    .modal-top {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 58px 15px 16px;
    }
    .modal-chip-group {
        justify-content: flex-start;
    }
    .modal-actions,
    .cimm-managed-note,
    .maintenance-btn {
        width: 100%;
    }
    .maintenance-btn {
        justify-content: center;
    }
}

/* PIPELINE STEPPER (CARD & MODAL) */
.incident-pipeline-stepper {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 750;
    color: #64748b;
    border: 1px solid #e2e8f0;
    margin-top: 5px;
}
.incident-pipeline-stepper .pipe-step {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.incident-pipeline-stepper .pipe-step.current {
    color: #2563eb;
    font-weight: 850;
}
.incident-pipeline-stepper .pipe-step.done {
    color: #059669;
}
.incident-pipeline-stepper .pipe-step.next {
    color: #94a3b8;
    opacity: 0.7;
}
.incident-pipeline-stepper .pipe-arrow {
    font-size: 0.6rem;
    color: #cbd5e1;
}

/* MODAL LIFECYCLE PIPELINE */
.modal-lifecycle-pipeline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 20px;
    margin-bottom: 20px;
    gap: 12px;
}
.modal-lifecycle-pipeline .pipe-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.modal-lifecycle-pipeline .pipe-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    background: #e2e8f0;
    color: #64748b;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.modal-lifecycle-pipeline .pipe-item.active .pipe-icon {
    background: #eff6ff;
    color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.modal-lifecycle-pipeline .pipe-item.done .pipe-icon {
    background: #ecfdf5;
    color: #059669;
}
.modal-lifecycle-pipeline .pipe-text {
    display: flex;
    flex-direction: column;
}
.modal-lifecycle-pipeline .pipe-text strong {
    font-size: 0.84rem;
    color: #0f172a;
    font-weight: 800;
}
.modal-lifecycle-pipeline .pipe-text span {
    font-size: 0.72rem;
    color: #64748b;
}
.modal-lifecycle-pipeline .pipe-connector {
    flex: 0 0 30px;
    height: 2px;
    background: #e2e8f0;
}
.modal-lifecycle-pipeline .pipe-connector.done {
    background: #059669;
}

/* INCIDENT ACTION FORM SECTION */
.incident-action-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 20px;
}
.incident-action-form .action-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 10px;
}
.incident-action-form .action-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.incident-action-form .action-field.full {
    grid-column: 1 / -1;
}
.incident-action-form label {
    font-size: 0.74rem;
    font-weight: 800;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.incident-action-form .action-select,
.incident-action-form .action-input,
.incident-action-form .action-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #0f172a;
    background: #ffffff;
    box-sizing: border-box;
}
.incident-action-form .action-select:focus,
.incident-action-form .action-input:focus,
.incident-action-form .action-textarea:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.incident-action-form .action-form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 14px;
    flex-wrap: wrap;
}
.incident-action-form .sync-hint {
    font-size: 0.76rem;
    font-weight: 700;
    color: #059669;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.incident-action-form .action-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #2563eb;
    color: #ffffff;
    border: none;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.15s ease;
}
.incident-action-form .action-submit-btn:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* DARK MODE */
body.dark-mode .incident-pipeline-stepper {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}
body.dark-mode .incident-pipeline-stepper .pipe-step.current { color: #60a5fa; }
body.dark-mode .incident-pipeline-stepper .pipe-step.done { color: #34d399; }
body.dark-mode .modal-lifecycle-pipeline {
    background: #1e293b;
    border-color: #334155;
}
body.dark-mode .modal-lifecycle-pipeline .pipe-text strong { color: #f8fafc; }
body.dark-mode .modal-lifecycle-pipeline .pipe-text span { color: #94a3b8; }
body.dark-mode .incident-action-section {
    background: #1e293b;
    border-color: #334155;
}
body.dark-mode .incident-action-form label { color: #cbd5e1; }
body.dark-mode .incident-action-form .action-select,
body.dark-mode .incident-action-form .action-input,
body.dark-mode .incident-action-form .action-textarea {
    background: #0f172a;
    border-color: #334155;
    color: #f8fafc;
}
</style>
@endsection
