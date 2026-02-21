@extends('layouts.qc-admin')
@section('title', 'Energy Incidents')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);

    $incidentRows = collect(method_exists($incidents, 'items') ? $incidents->items() : $incidents);
    $totalOnPage = $incidentRows->count();
    $openCount = $incidentRows->filter(function ($incident) {
        return str_contains(strtolower((string) ($incident->status ?? 'open')), 'open');
    })->count();
    $pendingCount = $incidentRows->filter(function ($incident) {
        return str_contains(strtolower((string) ($incident->status ?? '')), 'pending');
    })->count();
    $ongoingCount = $incidentRows->filter(function ($incident) {
        return str_contains(strtolower((string) ($incident->status ?? '')), 'ongoing');
    })->count();
    $criticalCount = $incidentRows->filter(function ($incident) {
        $level = strtolower((string) ($incident->severity_key ?? 'normal'));
        return in_array($level, ['critical', 'very-high'], true);
    })->count();
@endphp

@section('content')
<div class="incident-page">
    <div class="incident-shell">
        <div class="incident-header">
            <div>
                <h2>Incident Records</h2>
                <p>Track active energy anomalies and inspect details for immediate action.</p>
            </div>
            <a href="{{ route('energy-incidents.history') }}" class="history-btn">
                <i class="fa-solid fa-clock-rotate-left"></i> View History
            </a>
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
            <div class="metric-card pending">
                <span class="metric-label">Pending</span>
                <strong class="metric-value">{{ $pendingCount }}</strong>
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
                <option value="pending" {{ ($filters['status'] ?? 'all') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="ongoing" {{ ($filters['status'] ?? 'all') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
            </select>
            <select name="severity" id="incidentSeverityFilter">
                <option value="all" {{ ($filters['severity'] ?? 'all') === 'all' ? 'selected' : '' }}>All Severity</option>
                <option value="critical" {{ ($filters['severity'] ?? 'all') === 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="very-high" {{ ($filters['severity'] ?? 'all') === 'very-high' ? 'selected' : '' }}>Very High</option>
            </select>
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
                    $deviation = $incident->deviation_percent;
                    $deviationText = $deviation !== null ? number_format((float) $deviation, 2) . '%' : 'N/A';
                    $dateDetected = $incident->date_detected ? \Carbon\Carbon::parse($incident->date_detected)->format('M d, Y') : ($incident->created_at ? $incident->created_at->format('M d, Y') : 'N/A');

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
                    } elseif (str_contains($statusRaw, 'pending')) {
                        $statusKey = 'pending';
                        $statusLabel = 'Pending';
                    }

                    $defaultDescription = match ($statusKey) {
                        'resolved' => $levelKey === 'critical'
                            ? 'Critical energy spike for this billing period was resolved after corrective action.'
                            : 'Very high energy deviation for this billing period has been resolved and stabilized.',
                        'ongoing' => $levelKey === 'critical'
                            ? 'Critical energy spike is under active mitigation and continuous monitoring.'
                            : 'Very high energy deviation is undergoing corrective action and close monitoring.',
                        'pending' => $levelKey === 'critical'
                            ? 'Critical energy spike detected for this billing period and queued for urgent review.'
                            : 'Very high energy deviation detected for this billing period and queued for validation.',
                        default => $levelKey === 'critical'
                            ? 'Critical energy spike is active and requires immediate intervention.'
                            : 'Very high energy deviation is active and under close monitoring.',
                    };
                    $legacyDescriptions = [
                        'High energy consumption detected for this billing period.',
                        'System detected unusually high energy consumption for this period. Please review and validate.',
                    ];
                    $descriptionText = trim((string) ($incident->description ?? ''));
                    if ($descriptionText === '' || in_array($descriptionText, $legacyDescriptions, true)) {
                        $descriptionText = $defaultDescription;
                    }
                    $descriptionPreview = \Illuminate\Support\Str::limit($descriptionText, 140);
                    $searchText = strtolower($facilityName . ' ' . $statusLabel . ' ' . $levelLabel . ' ' . $descriptionText);

                    $probableCause = $incident->probable_cause;
                    if (is_array($probableCause)) {
                        $probableCause = implode(', ', $probableCause);
                    }
                    $probableCause = $probableCause ?: 'Automated system analysis: Abnormal usage pattern detected based on recent records.';

                    $immediateAction = $incident->immediate_action ?: 'Incident flagged for LGU review and action.';
                    $resolutionSummary = $incident->resolution_summary ?: 'Pending review by LGU energy officer or facility manager.';
                    $defaultRecommendation = match ($statusKey) {
                        'resolved' => $levelKey === 'critical'
                            ? 'Keep weekly load audits and retain corrective controls to prevent another critical spike.'
                            : 'Continue monthly variance checks and maintain current demand-control adjustments.',
                        'ongoing' => $levelKey === 'critical'
                            ? 'Continue technical mitigation, monitor demand in near-real time, and verify equipment stability daily.'
                            : 'Continue corrective maintenance and validate consumption trend every operating shift.',
                        'pending' => $levelKey === 'critical'
                            ? 'Dispatch urgent technical inspection, isolate suspect equipment, and validate meter data immediately.'
                            : 'Prioritize equipment checks, verify operating schedules, and monitor peak-hour consumption.',
                        default => $levelKey === 'critical'
                            ? 'Apply immediate load-shedding controls and assign a response owner for 24-hour follow-up.'
                            : 'Implement short-term consumption controls and track daily usage until variance drops.',
                    };
                    $preventiveRecommendation = trim((string) ($incident->preventive_recommendation ?? ''));
                    if ($preventiveRecommendation === '') {
                        $preventiveRecommendation = $defaultRecommendation;
                    }

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
                            <div class="facility-name">{{ $facilityName }}</div>
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
                                Details
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
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item"><span>Facility</span><strong>{{ $facilityName }}</strong></div>
                            <div class="detail-item"><span>Month/Year</span><strong>{{ $monthLabel }}/{{ $yearNum ?? '-' }}</strong></div>
                            <div class="detail-item"><span>Deviation</span><strong>{{ $deviationText }}</strong></div>
                            <div class="detail-item"><span>Date Detected</span><strong>{{ $dateDetected }}</strong></div>
                        </div>

                        <div class="detail-block"><span>Description</span><p>{{ $descriptionText }}</p></div>
                        <div class="detail-block"><span>Probable Cause</span><p>{{ $probableCause }}</p></div>
                        <div class="detail-block"><span>Immediate Action</span><p>{{ $immediateAction }}</p></div>
                        <div class="detail-block"><span>Resolution</span><p>{{ $resolutionSummary }}</p></div>
                        <div class="detail-block"><span>Preventive Recommendation</span><p>{{ $preventiveRecommendation }}</p></div>

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
                                <i class="fa-solid fa-screwdriver-wrench"></i> Open Maintenance
                            </a>
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

.incident-metrics {
    display: grid;
    grid-template-columns: repeat(5, minmax(120px, 1fr));
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
    grid-template-columns: 1.8fr 1fr 1fr auto;
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
    text-align: right;
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
        grid-template-columns: 1fr;
        gap: 8px;
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
