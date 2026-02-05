
<?php $__env->startSection('title', 'Energy Efficiency Analysis'); ?>
<?php $__env->startSection('content'); ?>
<div style="width:100%;margin:0;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">Energy Efficiency Analysis</h2>
    <p style="color:#555;margin-bottom:24px;">Analyze energy usage trends, compare actual vs baseline kWh, and identify opportunities for efficiency improvements across all facilities.</p>
    <!-- TOP SUMMARY CARDS -->
    <div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 High Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($highCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fef9c3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#eab308;">🟡 Medium Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($mediumCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🔴 Low Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($lowCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#f59e42;">⚠️ Auto-Flagged for Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($flaggedCount ?? 0); ?></div>
        </div>
    </div>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Facilities</option>
                <?php $__currentLoopData = $facilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php if(request('facility_id') == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;min-width:120px;">
            <label for="month" style="font-weight:700;margin-bottom:4px;">Month</label>
            <select name="month" id="month" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="all" <?php if(request('month') == 'all' || !request('month')): ?> selected <?php endif; ?>>All Months</option>
                <?php
                    $availableMonths = isset($availableMonths) && is_array($availableMonths) && count($availableMonths)
                        ? $availableMonths
                        : range(1,12);
                ?>
                <?php $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;min-width:100px;">
            <label for="year" style="font-weight:700;margin-bottom:4px;">Year</label>
            <select name="year" id="year" class="form-control" required style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Year</option>
                <?php $currentYear = date('Y'); ?>
                <?php $__currentLoopData = range($currentYear, $currentYear-10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($y); ?>" <?php if(request('year') == $y): ?> selected <?php endif; ?>><?php echo e($y); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="rating" style="font-weight:700;margin-bottom:4px;">Rating</label>
            <select name="rating" id="rating" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Rating</option>
                <option value="all" <?php if(request('rating') == 'all' || request('rating') == ''): ?> selected <?php endif; ?>>All Ratings</option>
                <option value="High" <?php if(request('rating') == 'High'): ?> selected <?php endif; ?>>High</option>
                <option value="Medium" <?php if(request('rating') == 'Medium'): ?> selected <?php endif; ?>>Medium</option>
                <option value="Low" <?php if(request('rating') == 'Low'): ?> selected <?php endif; ?>>Low</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
        </div>
    </form>
    <!-- MAIN TABLE -->
    <div style="overflow-x:auto;width:100%;">
        <table class="table" style="width:100%;min-width:100%;margin-top:12px;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(55,98,200,0.07);">
            <thead style="background:linear-gradient(90deg,#e9effc 60%,#f0fdf4 100%);">
                <tr style="font-size:1.08rem;">
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Facility</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Month</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Actual kWh</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Avg kWh</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Variance</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">EUI</th>
                    <th style="text-align:center;padding:14px 8px;font-weight:700;color:#2563eb;">Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $currentMonth = date('M');
                    $currentYear = date('Y');
                    $filteredRows = collect($efficiencyRows ?? [])->filter(function($row) use ($currentMonth, $currentYear) {
                        // $row['month'] is like 'Feb 2026', so split
                        $parts = explode(' ', $row['month']);
                        if(count($parts) == 2) {
                            return $parts[0] === $currentMonth && $parts[1] == $currentYear;
                        }
                        return false;
                    });
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $filteredRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr style="font-size:1.05rem;border-bottom:1px solid #e9effc;transition:background 0.2s;">
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['facility']); ?></td>
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['month']); ?></td>
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['actual_kwh']); ?></td>
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['avg_kwh']); ?></td>
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['variance']); ?></td>
                    <td style="padding:12px 8px;text-align:center;"><?php echo e($row['eui']); ?></td>
                    <td style="padding:12px 8px;text-align:center;">
                        <?php if($row['rating'] === 'High'): ?>
                            <span style="color:#22c55e;font-weight:700;background:#f0fdf4;padding:4px 14px;border-radius:8px;">🟢 High</span>
                        <?php elseif($row['rating'] === 'Medium'): ?>
                            <span style="color:#eab308;font-weight:700;background:#fef9c3;padding:4px 14px;border-radius:8px;">🟡 Medium</span>
                        <?php else: ?>
                            <span style="color:#e11d48;font-weight:700;background:#fff0f3;padding:4px 14px;border-radius:8px;">🔴 Low</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="text-center" style="padding:18px 0;color:#888;font-size:1.1rem;">No efficiency data found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>




</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-efficiency-analysis/index.blade.php ENDPATH**/ ?>