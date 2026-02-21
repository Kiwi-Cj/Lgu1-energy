<?php $__env->startSection('title', 'Efficiency Summary Report'); ?>

<?php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);

    $rows = collect($efficiencyRows ?? []);
    $highCount = $rows->where('rating', 'High')->count();
    $mediumCount = $rows->where('rating', 'Medium')->count();
    $lowCount = $rows->where('rating', 'Low')->count();
    $flaggedCount = $rows->where('flag', true)->count();
    $numericEui = $rows->pluck('eui')->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (float) $v);
    $avgEui = $numericEui->count() ? number_format($numericEui->avg(), 2) : '-';
?>

<?php $__env->startSection('content'); ?>
<style>
    .eff-page {
        width: 100%;
    }

    .eff-shell {
        background: linear-gradient(145deg, #f8fbff 0%, #eef6ff 45%, #f8fafc 100%);
        border: 1px solid #e2ebf6;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        padding: 24px 20px;
    }

    .eff-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .eff-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.6rem;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .eff-subtitle {
        margin: 6px 0 0;
        color: #475569;
        font-size: 0.93rem;
    }

    .eff-help {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #334155;
        background: #ffffffcc;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 12px 14px;
        max-width: 760px;
    }

    .eff-help b {
        color: #0f172a;
    }

    .eff-help .formula {
        color: #0369a1;
        font-weight: 700;
    }

    .eff-kpis {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .eff-kpi {
        border-radius: 12px;
        border: 1px solid transparent;
        padding: 12px 12px;
    }

    .eff-kpi-label {
        display: block;
        font-size: 0.71rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
    }

    .eff-kpi-value {
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1;
    }

    .eff-kpi.total { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .eff-kpi.avg { background: #ecfeff; border-color: #a5f3fc; color: #0e7490; }
    .eff-kpi.high { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
    .eff-kpi.medium { background: #fffbeb; border-color: #fde68a; color: #a16207; }
    .eff-kpi.low { background: #fff1f2; border-color: #fecdd3; color: #be123c; }

    .eff-filters {
        background: #ffffff;
        padding: 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 14px;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto auto;
        gap: 10px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-size: 0.73rem;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .filter-group select,
    .filter-group input {
        padding: 9px 11px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        background: #fff;
        font-size: 0.92rem;
        color: #0f172a;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
    }

    .btn-filter {
        background: linear-gradient(90deg, #0369a1, #2563eb);
        color: #fff;
        border: none;
        padding: 10px 14px;
        border-radius: 9px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 5px 14px rgba(3, 105, 161, 0.24);
    }

    .btn-reset {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        background: #fff;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 10px 14px;
        border-radius: 9px;
        font-weight: 800;
    }

    .eff-table-wrap {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .eff-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 840px;
    }

    .eff-table thead {
        background: #f8fafc;
    }

    .eff-table th {
        padding: 12px 14px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        white-space: nowrap;
    }

    .eff-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
        vertical-align: middle;
        text-align: left;
    }

    .eff-table tr:hover {
        background: #f8fbff;
    }

    .facility-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #0f172a;
    }

    .facility-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        flex-shrink: 0;
    }

    .eui-value {
        font-weight: 800;
        color: #0f172a;
    }

    .rating-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 4px 11px;
        font-size: 0.74rem;
        font-weight: 800;
        border: 1px solid transparent;
        text-transform: uppercase;
    }

    .rating-high { background: #dcfce7; color: #166534; border-color: #86efac; }
    .rating-medium { background: #fef9c3; color: #854d0e; border-color: #fde68a; }
    .rating-low { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .rating-na { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        font-size: 0.78rem;
        border-radius: 999px;
        padding: 5px 11px;
        border: 1px solid transparent;
    }

    .status-flag {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }

    .status-ok {
        background: #ecfeff;
        border-color: #a5f3fc;
        color: #0e7490;
    }

    .empty-state {
        text-align: center;
        padding: 34px 18px;
        color: #94a3b8;
        font-style: italic;
    }

    @media (max-width: 1100px) {
        .eff-kpis {
            grid-template-columns: repeat(3, minmax(120px, 1fr));
        }
        .eff-filters {
            grid-template-columns: 1fr 1fr;
        }
        .btn-filter,
        .btn-reset {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 760px) {
        .eff-shell {
            padding: 14px 10px;
        }
        .eff-header {
            flex-direction: column;
            align-items: stretch;
        }
        .eff-kpis {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
        }
        .eff-filters {
            grid-template-columns: 1fr;
        }
        .eff-help {
            max-width: none;
        }
    }
</style>

<div class="eff-page">
    <div class="eff-shell">
        <div class="eff-header">
            <div>
                <h2 class="eff-title"><i class="fa-solid fa-gauge-high"></i> Efficiency Summary Report</h2>
                <p class="eff-subtitle">Real-time overview of EUI and maintenance-readiness across active facilities.</p>
                <div class="eff-help">
                    <b>Computation:</b>
                    <span class="formula">EUI (kWh/sqm) = Average Monthly kWh / Facility Floor Area</span><br>
                    Rating rule:
                    <b>High</b> if EUI &lt; 5,
                    <b>Medium</b> if EUI &gt;= 5 and &lt; 10,
                    <b>Low</b> if EUI &gt;= 10.
                </div>
            </div>
        </div>

        <div class="eff-kpis">
            <div class="eff-kpi total">
                <span class="eff-kpi-label">Facilities</span>
                <div class="eff-kpi-value"><?php echo e($rows->count()); ?></div>
            </div>
            <div class="eff-kpi avg">
                <span class="eff-kpi-label">Average EUI</span>
                <div class="eff-kpi-value"><?php echo e($avgEui); ?></div>
            </div>
            <div class="eff-kpi high">
                <span class="eff-kpi-label">High Rating</span>
                <div class="eff-kpi-value"><?php echo e($highCount); ?></div>
            </div>
            <div class="eff-kpi medium">
                <span class="eff-kpi-label">Medium Rating</span>
                <div class="eff-kpi-value"><?php echo e($mediumCount); ?></div>
            </div>
            <div class="eff-kpi low">
                <span class="eff-kpi-label">Needs Attention</span>
                <div class="eff-kpi-value"><?php echo e($flaggedCount); ?></div>
            </div>
        </div>

        <form method="GET" action="" class="eff-filters">
            <div class="filter-group">
                <label for="facility_id">Facility</label>
                <select name="facility_id" id="facility_id">
                    <option value="">All Facilities</option>
                    <?php $__currentLoopData = $facilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($facility->id); ?>" <?php echo e((isset($selectedFacility) && $selectedFacility == $facility->id) ? 'selected' : ''); ?>>
                            <?php echo e($facility->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="rating">Efficiency Rating</label>
                <select name="rating" id="rating">
                    <option value="all" <?php echo e((isset($selectedRating) && ($selectedRating == 'all' || $selectedRating == '')) ? 'selected' : ''); ?>>All Ratings</option>
                    <option value="High" <?php echo e((isset($selectedRating) && $selectedRating == 'High') ? 'selected' : ''); ?>>High</option>
                    <option value="Medium" <?php echo e((isset($selectedRating) && $selectedRating == 'Medium') ? 'selected' : ''); ?>>Medium</option>
                    <option value="Low" <?php echo e((isset($selectedRating) && $selectedRating == 'Low') ? 'selected' : ''); ?>>Low</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="table_search">Quick Search</label>
                <input type="text" id="table_search" placeholder="Search facility in table..." />
            </div>

            <button type="submit" class="btn-filter">
                <i class="fa fa-filter"></i> Apply
            </button>

            <a href="<?php echo e(url()->current()); ?>" class="btn-reset">Reset</a>
        </form>

        <div class="eff-table-wrap">
            <table class="eff-table" id="efficiencyTable">
                <thead>
                    <tr>
                        <th>Facility Name</th>
                        <th>EUI (kWh/sqm)</th>
                        <th>Efficiency Rating</th>
                        <th>Last Audit</th>
                        <th>Maintenance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $efficiencyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $ratingText = (string) ($row['rating'] ?? '-');
                            $ratingClass = 'rating-na';
                            if ($ratingText === 'High') $ratingClass = 'rating-high';
                            if ($ratingText === 'Medium') $ratingClass = 'rating-medium';
                            if ($ratingText === 'Low') $ratingClass = 'rating-low';
                        ?>
                        <tr class="eff-row" data-search="<?php echo e(strtolower((string) ($row['facility'] ?? ''))); ?>">
                            <td>
                                <div class="facility-cell">
                                    <span class="facility-dot"></span>
                                    <span><?php echo e($row['facility']); ?></span>
                                </div>
                            </td>
                            <td><span class="eui-value"><?php echo e($row['eui']); ?></span></td>
                            <td>
                                <span class="rating-badge <?php echo e($ratingClass); ?>"><?php echo e($ratingText); ?></span>
                            </td>
                            <td><?php echo e($row['last_audit']); ?></td>
                            <td>
                                <?php if($row['flag']): ?>
                                    <span class="status-pill status-flag"><i class="fa fa-flag"></i> Needs Maintenance</span>
                                <?php else: ?>
                                    <span class="status-pill status-ok"><i class="fa fa-check-circle"></i> Operational</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                No efficiency data found for the selected filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr id="effNoMatchRow" style="display:none;">
                        <td colspan="5" class="empty-state">No matching facilities in current result.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('table_search');
    const rows = Array.from(document.querySelectorAll('#efficiencyTable .eff-row'));
    const noMatchRow = document.getElementById('effNoMatchRow');

    const applySearch = () => {
        const q = (searchInput?.value || '').toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach((row) => {
            const text = (row.dataset.search || '').toLowerCase();
            const visible = q === '' || text.includes(q);
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        if (noMatchRow) {
            noMatchRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', applySearch);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/reports/efficiency-summary.blade.php ENDPATH**/ ?>