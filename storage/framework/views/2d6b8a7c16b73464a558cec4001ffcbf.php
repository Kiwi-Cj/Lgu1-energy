<?php $__env->startSection('title', 'Annual Energy Monitoring'); ?>

<?php $__env->startSection('content'); ?>
<div class="report card" style="padding:32px 24px; background:#f8fafc; border-radius:18px; box-shadow:0 8px 32px rgba(37,99,235,0.09);">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:18px;">
        <div>
            <h1 style="font-size:1.9rem; font-weight:800; color:#1e293b; margin:0;">Annual Energy Monitoring</h1>
            <p style="margin:6px 0 0; color:#64748b;">Year-level summary of actual vs baseline energy usage.</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="<?php echo e(route('modules.energy.annual.export-pdf', request()->query())); ?>"
               style="background:#0f172a; color:#fff; padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:700;">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <a href="<?php echo e(route('modules.energy.annual.export-excel', request()->query())); ?>"
               style="background:linear-gradient(90deg,#2563eb,#6366f1); color:#fff; padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:700;">
                <i class="fa-solid fa-file-export"></i> Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('modules.energy.annual')); ?>"
          style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px; margin-bottom:18px;">
        <div style="min-width:180px;">
            <label for="year" style="display:block; margin-bottom:6px; color:#334155; font-weight:600;">Year</label>
            <select id="year" name="year" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 10px;">
                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($year); ?>" <?php echo e((string)$selectedYear === (string)$year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div style="min-width:220px;">
            <label for="facility_id" style="display:block; margin-bottom:6px; color:#334155; font-weight:600;">Facility</label>
            <select id="facility_id" name="facility_id" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:9px 10px;">
                <option value="">All Facilities</option>
                <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php echo e((string)$selectedFacility === (string)$facility->id ? 'selected' : ''); ?>>
                        <?php echo e($facility->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <button type="submit"
                style="background:#2563eb; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-weight:700; cursor:pointer;">
            Apply
        </button>
    </form>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:18px;">
        <div style="background:#fff; border-left:4px solid #2563eb; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Actual kWh</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;"><?php echo e(number_format($totalActualKwh ?? 0, 2)); ?></div>
        </div>
        <div style="background:#fff; border-left:4px solid #16a34a; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Baseline kWh</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;"><?php echo e(number_format($annualBaseline ?? 0, 2)); ?></div>
        </div>
        <div style="background:#fff; border-left:4px solid #f59e0b; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Difference</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;"><?php echo e(number_format($annualDifference ?? 0, 2)); ?></div>
        </div>
        <div style="background:#fff; border-left:4px solid #7c3aed; border-radius:12px; padding:14px;">
            <div style="font-size:0.78rem; color:#64748b; font-weight:700; text-transform:uppercase;">Annual Status</div>
            <div style="font-size:1.35rem; color:#1e293b; font-weight:800;"><?php echo e($annualStatus ?? '-'); ?></div>
        </div>
    </div>

    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:760px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:12px; text-align:left;">Month</th>
                    <th style="padding:12px; text-align:right;">Actual kWh</th>
                    <th style="padding:12px; text-align:right;">Baseline kWh</th>
                    <th style="padding:12px; text-align:right;">Difference</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $monthlyBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $status = $row['status'] ?? '-';
                        $badgeMap = [
                            'Critical' => ['bg' => '#7f1d1d', 'color' => '#ffffff'],
                            'Very High' => ['bg' => '#e11d48', 'color' => '#ffffff'],
                            'High' => ['bg' => '#f59e42', 'color' => '#ffffff'],
                            'Warning' => ['bg' => '#fde68a', 'color' => '#1f2937'],
                            'Normal' => ['bg' => '#bbf7d0', 'color' => '#14532d'],
                            '-' => ['bg' => '#f8fafc', 'color' => '#64748b'],
                        ];
                        $badgeBg = $badgeMap[$status]['bg'] ?? '#f8fafc';
                        $badgeColor = $badgeMap[$status]['color'] ?? '#64748b';
                    ?>
                    <tr style="border-top:1px solid #f1f5f9;">
                        <td style="padding:12px; font-weight:700; color:#334155;"><?php echo e($row['label'] ?? '-'); ?></td>
                        <td style="padding:12px; text-align:right;"><?php echo e(number_format($row['actual'] ?? 0, 2)); ?></td>
                        <td style="padding:12px; text-align:right;"><?php echo e(number_format($row['baseline'] ?? 0, 2)); ?></td>
                        <td style="padding:12px; text-align:right;"><?php echo e(number_format($row['diff'] ?? 0, 2)); ?></td>
                        <td style="padding:12px; text-align:center;">
                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; background:<?php echo e($badgeBg); ?>; color:<?php echo e($badgeColor); ?>; font-weight:700; font-size:0.82rem;">
                                <?php echo e($status); ?>

                            </span>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" style="padding:28px; text-align:center; color:#94a3b8;">No annual data available for selected filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/energy-monitoring/annual.blade.php ENDPATH**/ ?>