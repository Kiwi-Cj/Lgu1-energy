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
                <option value="cprf" {{ ($filters['source'] ?? 'all') === 'cprf' ? 'selected' : '' }}>CPRF Integrated</option>
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
                    $sourceLabel = $isManual ? 'Manual Report' : ($isCprf ? 'CPRF Integrated' : 'Auto Detected');
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
                    <div class="incident-modal-content">
                        <button class="incident-modal-close" onclick="closeIncidentModal({{ $incident->id }})" aria-label="Close modal">&times;</button>
                        <div class="modal-top">
                            <h3>Energy Incident Report</h3>
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

                        <div class="detail-grid">
                            <div class="detail-item"><span>Facility</span><strong>{{ $facilityName }}</strong></div>
                            <div class="detail-item"><span>Month/Year</span><strong>{{ $monthLabel }}/{{ $yearNum ?? '-' }}</strong></div>
                            <div class="detail-item"><span>Deviation</span><strong>{{ $deviationText }}</strong></div>
                            <div class="detail-item"><span>Date Detected</span><strong>{{ $dateDetected }}</strong></div>
                            <div class="detail-item"><span>Actual Reading</span><strong>{{ $actualKwh }}</strong></div>
                            <div class="detail-item"><span>Baseline</span><strong>{{ $baselineKwh }}</strong></div>
                            <div class="detail-item"><span>Data Source</span><strong>{{ $sourceLabel }}</strong></div>
                            <div class="detail-item"><span>Action Owner</span><strong>CIMM Maintenance Integration</strong></div>
                            <div class="detail-item"><span>Category</span><strong>{{ $categoryLabel }}</strong></div>
                            <div class="detail-item"><span>Affected Asset</span><strong>{{ $incident->affected_asset ?: 'Not specified' }}</strong></div>
                        </div>

                        <div class="detail-block"><span>Description</span><p>{{ $descriptionText }}</p></div>
                        <div class="detail-block"><span>Probable Cause</span><p>{{ $probableCause }}</p></div>
                        <div class="detail-block"><span>Immediate Action</span><p>{{ $immediateAction }}</p></div>
                        <div class="detail-block"><span>Resolution</span><p>{{ $resolutionSummary }}</p></div>
                        <div class="detail-block"><span>Preventive Recommendation</span><p>{{ $preventiveRecommendation }}</p></div>

                        @if($incident->evidence_path)
                            <div class="detail-block">
                                <span>Reporter Evidence</span>
                                <p><a href="{{ asset('storage/' . ltrim($incident->evidence_path, '/')) }}" target="_blank" rel="noopener">View attached photo</a></p>
                            </div>
                        @endif

                        @if(count($attachments))
                            <div class="detail-block">
                                <span>Attachments</span>
                                <ul class="attachment-list">
                                    @foreach($attachments as $attachment)
                                        @if(is_string($attachment) && trim($attachment) !== '')
                                            <li>
                                                <a href="{{ asset('storage/' . ltrim($attachment, '/')) }}" target="_blank" rel="noopener">
                                                    {{ basename($attachment) }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="modal-actions">
                            <a href="{{ route('modules.maintenance.index') }}?facility_id={{ $incident->facility->id ?? '' }}" class="maintenance-btn">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> View CIMM Maintenance
                            </a>
                            <div class="cimm-managed-note">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                Status updates automatically from CIMM
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
            <span class="cimm-owner-badge"><i class="fa-solid fa-arrows-rotate"></i> CIMM Managed</span>
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
                <select id="report_facility_id" name="facility_id" required>
                    <option value="">Select facility</option>
                    @foreach($reportFacilities ?? [] as $facility)
                        <option value="{{ $facility->id }}" @selected((string) old('facility_id') === (string) $facility->id)>{{ $facility->name }}</option>
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
            <div class="report-field">
                <label for="report_detected_at">Date and Time Detected *</label>
                <input id="report_detected_at" type="datetime-local" name="detected_at" value="{{ old('detected_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <div class="report-field">
                <label for="report_affected_asset">Affected Meter/Equipment</label>
                <input id="report_affected_asset" type="text" name="affected_asset" value="{{ old('affected_asset') }}" maxlength="255" placeholder="e.g. Main panel, AHU-02, Main meter">
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

document.addEventListener('DOMContentLoaded', function () {
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
    @if(count($incidentFormErrors)) toggleReportModal(true); @endif

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
.incident-page {
    width: 100%;
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

.cimm-owner-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #99f6e4;
    border-radius: 999px;
    background: #f0fdfa;
    color: #0f766e;
    padding: 6px 9px;
    font-size: 0.68rem;
    font-weight: 900;
    text-transform: uppercase;
    white-space: nowrap;
}

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
    background: rgba(15, 23, 42, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.incident-modal-content {
    width: min(760px, 94vw);
    max-height: 88vh;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.22);
    padding: 22px 20px 18px;
    position: relative;
}

.incident-modal-close {
    position: absolute;
    top: 10px;
    right: 14px;
    border: none;
    background: none;
    font-size: 2rem;
    color: #64748b;
    cursor: pointer;
}

.incident-modal-close:hover {
    color: #dc2626;
}

.modal-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding-right: 20px;
    margin-bottom: 14px;
}

.modal-top h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 900;
}

.modal-chip-group {
    display: flex;
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
    padding: 5px 10px;
    font-size: 0.7rem;
    font-weight: 900;
    text-decoration: none;
    text-transform: uppercase;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.detail-item {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 12px;
    background: #f8fafc;
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
    color: #0f172a;
}

.detail-block {
    margin-bottom: 12px;
}

.detail-block span {
    display: block;
    color: #334155;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 800;
    margin-bottom: 3px;
}

.detail-block p {
    margin: 0;
    color: #475569;
    line-height: 1.45;
    font-size: 0.94rem;
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
    margin-top: 16px;
    display: flex;
    align-items: flex-end;
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
    padding: 9px 12px;
    font-size: 0.78rem;
    font-weight: 800;
}

.maintenance-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-weight: 800;
    padding: 10px 14px;
    border-radius: 10px;
    color: #fff;
    background: linear-gradient(90deg, #2563eb, #6366f1);
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
body.dark-mode .incident-page .incident-modal-close {
    color: #94a3b8;
}
body.dark-mode .incident-page .incident-modal-close:hover {
    color: #fda4af;
}
body.dark-mode .incident-page .detail-item {
    background: #0f172a;
    border-color: #334155;
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
        padding: 20px 16px 16px;
        border-radius: 14px;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .modal-top {
        flex-direction: column;
        align-items: flex-start;
        padding-right: 22px;
    }
}
</style>
@endsection
