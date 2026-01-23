
<?php $__env->startSection('title', 'Energy Report'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1100px;margin:0 auto;">
	<h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">📘 1️⃣ Energy Consumption Report</h2>
	<p style="color:#555;margin-bottom:24px;">Detailed energy consumption data and trends for all facilities.</p>
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
			<label for="month" style="font-weight:700;margin-bottom:4px;">Month / Year</label>
			<input type="month" name="month" id="month" class="form-control" style="min-width:140px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" value="<?php echo e(request('month')); ?>">
		</div>
		<div style="display:flex;flex-direction:column;justify-content:flex-end;">
			<button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
		</div>
	</form>
		<table style="width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);">
    <thead style="background:#e9effc;">
        <tr>
            <th style="padding:12px;text-align:left;">Facility</th>
            <th style="padding:12px;text-align:left;">Month</th>
            <th style="padding:12px;text-align:right;">Actual kWh</th>
            <th style="padding:12px;text-align:right;">Avg kWh</th>
            <th style="padding:12px;text-align:right;">Variance</th>
            <th style="padding:12px;text-align:center;">Trend</th>
        </tr>
    </thead>

    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $energyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr style="border-top:1px solid #e5e7eb;">
            <td style="padding:10px;"><?php echo e($row['facility']); ?></td>
            <td style="padding:10px;"><?php echo e($row['month']); ?></td>
            <td style="padding:10px;text-align:right;"><?php echo e($row['actual_kwh']); ?></td>
            <td style="padding:10px;text-align:right;"><?php echo e($row['avg_kwh']); ?></td>
            <td style="padding:10px;text-align:right;"><?php echo e($row['variance']); ?></td>
            <td style="padding:10px;text-align:center;">
    <?php if($row['trend'] === 'up'): ?>
        <span style="color:#dc2626;font-weight:700;">↑ Increasing</span>
    <?php elseif($row['trend'] === 'down'): ?>
        <span style="color:#16a34a;font-weight:700;">↓ Decreasing</span>
    <?php else: ?>
        <span style="color:#6b7280;font-weight:700;">→ Stable</span>
    <?php endif; ?>
</td>

        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="6" style="padding:14px;text-align:center;color:#6b7280;">
                No energy data found.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/reports/energy.blade.php ENDPATH**/ ?>