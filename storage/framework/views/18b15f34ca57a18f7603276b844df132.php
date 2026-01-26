
<?php $__env->startSection('title', 'Efficiency Summary Report'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1100px;margin:0 auto;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">⚡ 3️⃣ Efficiency Summary Report</h2>
    <p style="color:#555;margin-bottom:24px;">Summary of energy efficiency across all facilities.</p>
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
    <table class="table" style="width:100%;margin-top:18px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;box-shadow:0 2px 8px rgba(55,98,200,0.07);">
        <thead style="background:#e9effc;">
            <tr>
                <th style="padding:12px;">Facility</th>
                <th style="padding:12px;">EUI</th>
                <th style="padding:12px;">Rating</th>
                <th style="padding:12px;">Last Audit</th>
                <th style="padding:12px;">Flag</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $efficiencyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="padding:10px 8px;"><?php echo e($row['facility']); ?></td>
                <td style="padding:10px 8px;"><?php echo e($row['eui']); ?></td>
                <td style="padding:10px 8px;"><?php echo e($row['rating']); ?></td>
                <td style="padding:10px 8px;"><?php echo e($row['last_audit']); ?></td>
                <td style="padding:10px 8px;">
                    <?php if($row['flag']): ?>
                        <span style="color:#e11d48;font-weight:700;">🚩 For Maintenance</span>
                    <?php else: ?>
                        <span style="color:#22c55e;font-weight:700;">OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="text-center" style="padding:16px;">No efficiency data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/reports/efficiency-summary.blade.php ENDPATH**/ ?>