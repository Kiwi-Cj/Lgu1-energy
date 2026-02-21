<?php $__env->startSection('title', 'Incident History'); ?>

<?php
    $historyRows = collect($histories ?? []);
    $totalResolved = $historyRows->count();
    $criticalResolved = $historyRows->filter(function ($incident) {
        $level = strtolower((string) ($incident->severity_key ?? 'normal'));
        return $level === 'critical';
    })->count();
    $veryHighResolved = $historyRows->filter(function ($incident) {
        $level = strtolower((string) ($incident->severity_key ?? 'normal'));
        return str_replace(' ', '-', $level) === 'very-high';
    })->count();
    $resolvedThisMonth = $historyRows->filter(function ($incident) {
        $date = $incident->resolved_at ?? $incident->updated_at ?? $incident->created_at;
        if (!$date) {
            return false;
        }
        return \Carbon\Carbon::parse($date)->isSameMonth(now());
    })->count();
?>

<?php $__env->startSection('content'); ?>
<div class="history-page">
    <div class="history-shell">
        <div class="history-header">
            <div>
                <h2>Incident History</h2>
                <p>Archived resolved records with final actions and preventive recommendations.</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo e(route('energy-incidents.index')); ?>" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Back to Active Incidents
                </a>
            </div>
        </div>

        <div class="history-metrics">
            <div class="metric-card total">
                <span class="metric-label">Resolved Records</span>
                <strong class="metric-value"><?php echo e($totalResolved); ?></strong>
            </div>
            <div class="metric-card critical">
                <span class="metric-label">Critical Resolved</span>
                <strong class="metric-value"><?php echo e($criticalResolved); ?></strong>
            </div>
            <div class="metric-card very-high">
                <span class="metric-label">Very High Resolved</span>
                <strong class="metric-value"><?php echo e($veryHighResolved); ?></strong>
            </div>
            <div class="metric-card month">
                <span class="metric-label">Resolved This Month</span>
                <strong class="metric-value"><?php echo e($resolvedThisMonth); ?></strong>
            </div>
        </div>

        <div class="history-filters">
            <input type="text" id="historySearch" placeholder="Search facility, status, description..." />
            <select id="historySeverityFilter">
                <option value="all">All Severity</option>
                <option value="critical">Critical</option>
                <option value="very-high">Very High</option>
                <option value="high">High</option>
                <option value="warning">Warning</option>
                <option value="normal">Normal</option>
            </select>
        </div>

        <div class="history-list-container">
            <?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    $monthNum = isset($incident->month) && $incident->month ? (int) $incident->month : null;
                    $yearNum = isset($incident->year) && $incident->year ? $incident->year : null;
                    $monthLabel = $monthNum && $monthNum >= 1 && $monthNum <= 12 ? $months[$monthNum-1] : '-';
                    $facilityName = $incident->facility->name ?? 'Unknown Facility';
                    $dpn = isset($incident->deviation_percent) ? $incident->deviation_percent : null;
                    $deviationText = $dpn !== null ? number_format((float) $dpn, 2) . '%' : '-';
                    $dateDetected = $incident->date_detected ? \Carbon\Carbon::parse($incident->date_detected)->format('M d, Y') : ($incident->created_at ? $incident->created_at->format('M d, Y') : '-');
                    $resolvedDateRaw = $incident->resolved_at ?? $incident->updated_at ?? $incident->created_at;
                    $resolvedDate = $resolvedDateRaw ? \Carbon\Carbon::parse($resolvedDateRaw)->format('M d, Y h:i A') : '-';

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

                    $statusRaw = strtolower((string) ($incident->status ?? 'resolved'));
                    $statusKey = 'resolved';
                    $statusLabel = 'Resolved';
                    if (str_contains($statusRaw, 'closed')) {
                        $statusKey = 'closed';
                        $statusLabel = 'Closed';
                    }

                    $defaultDescription = $levelKey === 'critical'
                        ? 'Critical energy spike was resolved and corrective controls have been validated.'
                        : 'Energy deviation was resolved and stabilized after mitigation steps.';
                    $descriptionText = trim((string) ($incident->description ?? ''));
                    if ($descriptionText === '') {
                        $descriptionText = $defaultDescription;
                    }
                    $descriptionPreview = \Illuminate\Support\Str::limit($descriptionText, 130);
                    $searchText = strtolower($facilityName . ' ' . $statusLabel . ' ' . $levelLabel . ' ' . $descriptionText);

                    $probableCause = $incident->probable_cause;
                    if (is_array($probableCause)) {
                        $probableCause = implode(', ', $probableCause);
                    }
                    $probableCause = $probableCause ?: 'System-detected abnormal consumption pattern.';

                    $immediateAction = $incident->immediate_action ?: 'Operational controls were applied and validated.';
                    $resolutionSummary = $incident->resolution_summary ?: 'Issue addressed and marked resolved after validation.';
                    $preventiveRecommendation = trim((string) ($incident->preventive_recommendation ?? ''));
                    if ($preventiveRecommendation === '') {
                        $preventiveRecommendation = 'Continue scheduled inspections and variance monitoring to prevent recurrence.';
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
                ?>
                <div class="history-row"
                    tabindex="0"
                    data-id="<?php echo e($incident->id); ?>"
                    data-level="<?php echo e($levelKey); ?>"
                    data-search="<?php echo e($searchText); ?>"
                    onclick="openHistoryModal(<?php echo e($incident->id); ?>)">
                    <div class="row-main">
                        <div class="facility-col">
                            <div class="facility-name"><?php echo e($facilityName); ?></div>
                            <div class="facility-desc"><?php echo e($descriptionPreview); ?></div>
                        </div>
                        <div class="meta-col">
                            <span class="chip severity <?php echo e($levelKey); ?>"><?php echo e($levelLabel); ?></span>
                            <span class="chip status <?php echo e($statusKey); ?>"><?php echo e($statusLabel); ?></span>
                        </div>
                        <div class="value-col">
                            <div class="value-label">Deviation</div>
                            <div class="value-main <?php echo e($dpn !== null && $dpn >= 0 ? 'up' : 'down'); ?>"><?php echo e($deviationText); ?></div>
                        </div>
                        <div class="value-col">
                            <div class="value-label">Detected</div>
                            <div class="value-main"><?php echo e($dateDetected); ?></div>
                            <div class="value-sub"><?php echo e($monthLabel); ?>/<?php echo e($yearNum ?? '-'); ?></div>
                        </div>
                        <div class="value-col">
                            <div class="value-label">Resolved</div>
                            <div class="value-main"><?php echo e($resolvedDate); ?></div>
                        </div>
                    </div>
                </div>

                <div id="history-modal-<?php echo e($incident->id); ?>" class="history-modal" style="display:none;" aria-hidden="true">
                    <div class="history-modal-content">
                        <button class="history-modal-close" onclick="closeHistoryModal(<?php echo e($incident->id); ?>)" aria-label="Close modal">&times;</button>
                        <div class="modal-top">
                            <h3>Resolved Incident Details</h3>
                            <div class="modal-chip-group">
                                <span class="chip severity <?php echo e($levelKey); ?>"><?php echo e($levelLabel); ?></span>
                                <span class="chip status <?php echo e($statusKey); ?>"><?php echo e($statusLabel); ?></span>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item"><span>Facility</span><strong><?php echo e($facilityName); ?></strong></div>
                            <div class="detail-item"><span>Month/Year</span><strong><?php echo e($monthLabel); ?>/<?php echo e($yearNum ?? '-'); ?></strong></div>
                            <div class="detail-item"><span>Deviation</span><strong><?php echo e($deviationText); ?></strong></div>
                            <div class="detail-item"><span>Date Detected</span><strong><?php echo e($dateDetected); ?></strong></div>
                            <div class="detail-item"><span>Date Resolved</span><strong><?php echo e($resolvedDate); ?></strong></div>
                        </div>

                        <div class="detail-block"><span>Description</span><p><?php echo e($descriptionText); ?></p></div>
                        <div class="detail-block"><span>Probable Cause</span><p><?php echo e($probableCause); ?></p></div>
                        <div class="detail-block"><span>Immediate Action</span><p><?php echo e($immediateAction); ?></p></div>
                        <div class="detail-block"><span>Resolution Summary</span><p><?php echo e($resolutionSummary); ?></p></div>
                        <div class="detail-block"><span>Preventive Recommendation</span><p><?php echo e($preventiveRecommendation); ?></p></div>

                        <?php if(count($attachments)): ?>
                            <div class="detail-block">
                                <span>Attachments</span>
                                <ul class="attachment-list">
                                    <?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(is_string($attachment) && trim($attachment) !== ''): ?>
                                            <li>
                                                <a href="<?php echo e(asset('storage/' . ltrim($attachment, '/'))); ?>" target="_blank" rel="noopener">
                                                    <?php echo e(basename($attachment)); ?>

                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="history-empty" id="historyEmptyState">No incident history found.</div>
            <?php endif; ?>
            <div class="history-empty" id="historyNoMatch" style="display:none;">No matching resolved incidents.</div>
        </div>
    </div>
</div>

<script>
function openHistoryModal(id) {
    const modal = document.getElementById('history-modal-' + id);
    if (!modal) return;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeHistoryModal(id) {
    const modal = document.getElementById('history-modal-' + id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('.history-row'));
    const searchInput = document.getElementById('historySearch');
    const severityFilter = document.getElementById('historySeverityFilter');
    const noMatch = document.getElementById('historyNoMatch');
    const defaultEmpty = document.getElementById('historyEmptyState');

    const applyFilters = () => {
        if (!rows.length) return;

        const q = (searchInput?.value || '').toLowerCase().trim();
        const severity = (severityFilter?.value || 'all').toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowLevel = (row.dataset.level || '').toLowerCase();
            const matchSearch = q === '' || rowSearch.includes(q);
            const matchSeverity = severity === 'all' || rowLevel === severity;
            const visible = matchSearch && matchSeverity;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        if (noMatch) {
            noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
        }
        if (defaultEmpty) {
            defaultEmpty.style.display = 'none';
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (severityFilter) {
        severityFilter.addEventListener('change', applyFilters);
    }

    rows.forEach((row) => {
        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const id = row.dataset.id;
                if (id) openHistoryModal(id);
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.history-modal').forEach((modal) => {
            if (modal.style.display === 'flex') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        });
        document.body.style.overflow = '';
    });

    document.querySelectorAll('.history-modal').forEach((modal) => {
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
.history-page {
    width: 100%;
}

.history-shell {
    background: linear-gradient(160deg, #f8fafc 0%, #eef6ff 100%);
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(14, 74, 126, 0.09);
    padding: 26px 20px;
}

.history-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}

.history-header h2 {
    margin: 0;
    color: #0f172a;
    font-size: 1.55rem;
    font-weight: 900;
}

.history-header p {
    margin: 6px 0 0;
    color: #475569;
    font-size: 0.92rem;
}

.back-btn {
    background: linear-gradient(90deg, #0f6b8f, #0e8a9a);
    color: #fff;
    padding: 10px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.history-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.metric-card {
    border-radius: 12px;
    padding: 11px 12px;
    border: 1px solid transparent;
}

.metric-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.metric-value {
    font-size: 1.4rem;
    font-weight: 900;
    line-height: 1;
}

.metric-card.total { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
.metric-card.critical { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
.metric-card.very-high { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
.metric-card.month { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }

.history-filters {
    display: grid;
    grid-template-columns: 1.8fr 0.8fr;
    gap: 10px;
    margin-bottom: 12px;
}

.history-filters input,
.history-filters select {
    border: 1px solid #dbe2ef;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.9rem;
    color: #1f2937;
    background: #fff;
}

.history-filters input:focus,
.history-filters select:focus {
    outline: none;
    border-color: #7dd3fc;
    box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.24);
}

.history-list-container {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.history-row {
    border-bottom: 1px solid #edf2f7;
    cursor: pointer;
    transition: background 0.16s ease, transform 0.16s ease;
}

.history-row:hover,
.history-row:focus {
    background: #f5fbff;
    transform: translateY(-1px);
    outline: none;
}

.history-row:last-child {
    border-bottom: none;
}

.row-main {
    display: grid;
    grid-template-columns: 2.2fr 1.2fr 0.8fr 0.9fr 1fr;
    gap: 10px;
    align-items: center;
    padding: 14px 15px;
}

.facility-name {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}

.facility-desc {
    margin-top: 4px;
    color: #64748b;
    font-size: 0.85rem;
    line-height: 1.35;
}

.meta-col {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
}

.chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid transparent;
    font-size: 0.71rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.chip.severity.critical { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.chip.severity.very-high { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
.chip.severity.high { background: #ffedd5; color: #c2410c; border-color: #fdba74; }
.chip.severity.warning { background: #fffbeb; color: #a16207; border-color: #fde68a; }
.chip.severity.normal { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }

.chip.status.resolved { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.chip.status.closed { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }

.value-label {
    color: #64748b;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 700;
}

.value-main {
    color: #1e293b;
    font-weight: 800;
    margin-top: 2px;
    font-size: 0.9rem;
}

.value-main.up { color: #dc2626; }
.value-main.down { color: #16a34a; }

.value-sub {
    color: #94a3b8;
    font-size: 0.76rem;
    margin-top: 2px;
}

.history-empty {
    text-align: center;
    color: #64748b;
    padding: 20px 16px;
}

.history-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.history-modal-content {
    width: min(760px, 94vw);
    max-height: 88vh;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.22);
    padding: 22px 20px 18px;
    position: relative;
}

.history-modal-close {
    position: absolute;
    top: 10px;
    right: 14px;
    border: none;
    background: none;
    font-size: 2rem;
    color: #64748b;
    cursor: pointer;
}

.history-modal-close:hover {
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
    font-size: 1.24rem;
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
    letter-spacing: 0.3px;
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
    color: #0f6b8f;
    text-decoration: none;
    font-weight: 700;
}

.attachment-list a:hover {
    text-decoration: underline;
}

@media (max-width: 1024px) {
    .history-metrics {
        grid-template-columns: repeat(2, minmax(120px, 1fr));
    }
    .row-main {
        grid-template-columns: 1.8fr 1.2fr 0.9fr 1fr;
    }
}

@media (max-width: 760px) {
    .history-shell {
        padding: 16px 12px;
    }
    .history-header {
        flex-direction: column;
        align-items: stretch;
    }
    .back-btn {
        justify-content: center;
    }
    .history-filters {
        grid-template-columns: 1fr;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/energy-incident/history.blade.php ENDPATH**/ ?>