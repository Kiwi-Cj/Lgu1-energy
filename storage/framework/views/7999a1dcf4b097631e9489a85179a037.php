				borderDash: [6, 6],
			}
		]
	},
	options: {
		responsive: true,
		plugins: {
			legend: { display: true },
		},
		scales: {
			y: {
				beginAtZero: true
			}
		}
	}
});
</script> --}}
</div>

<div class="row" style="display:flex;gap:16px;flex-wrap:wrap;">
	<div class="card" style="flex:1 1 150px;min-width:150px;background:#f5f8ff;padding:14px 10px;border-radius:10px;box-shadow:0 2px 6px rgba(55,98,200,0.07);">
		<div style="font-size:1rem;font-weight:500;color:#3762c8;">Total Energy Consumption</div>
		<div style="font-size:1.4rem;font-weight:700;margin:6px 0;"><?php echo e($totalKwh ?? '0'); ?> kWh</div>
	</div>
	<div class="card" style="flex:1 1 150px;min-width:150px;background:#f5f8ff;padding:14px 10px;border-radius:10px;box-shadow:0 2px 6px rgba(55,98,200,0.07);">
		<div style="font-size:1rem;font-weight:500;color:#3762c8;">Active Facilities</div>
		<div style="font-size:1.4rem;font-weight:700;margin:6px 0;"><?php echo e($activeFacilities ?? '0'); ?></div>
	</div>
</div>

<div class="mt-5">
	<form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
		<div style="display:flex;flex-direction:column;">
			   <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
				   <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
				   <option value="" disabled selected hidden>Select Facility</option>
				   <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					   <option value="<?php echo e($facility->id); ?>" <?php if(isset($filterFacilityId) && $filterFacilityId == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
				   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			   </select>
		</div>
		<div style="display:flex;flex-direction:column;">
			<label for="month" style="font-weight:700;margin-bottom:4px;">Month</label>
			<select name="month" id="month" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
				<option value="" disabled selected hidden>Select Month</option>
				<?php if(isset($availableMonths) && is_array($availableMonths) && count($availableMonths)): ?>
					<option value="all" <?php if(isset($filterMonth) && $filterMonth == 'all'): ?> selected <?php endif; ?>>All Months</option>
					<?php $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(isset($filterMonth) && $filterMonth == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php else: ?>
					<option value="all" <?php if(isset($filterMonth) && $filterMonth == 'all'): ?> selected <?php endif; ?>>All Months</option>
					<?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(isset($filterMonth) && $filterMonth == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php endif; ?>
			</select>
		</div>
		<div style="display:flex;flex-direction:column;">
			<label for="year" style="font-weight:700;margin-bottom:4px;">Year</label>
			<select name="year" id="year" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
				<option value="" disabled hidden>Select Year</option>
				<?php $currentYear = date('Y'); ?>
				<?php $__currentLoopData = range($currentYear, $currentYear-10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<option value="<?php echo e($y); ?>" <?php if((isset($filterYear) && $filterYear == $y) || (!isset($filterYear) && $y == $currentYear)): ?> selected <?php endif; ?>><?php echo e($y); ?></option>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div style="display:flex;flex-direction:column;justify-content:flex-end;">
			<button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
		</div>
	</form>
	<?php
		$filterActive = request()->has('facility_id') && request('facility_id');
	?>
	<?php if($filterActive): ?>
        <h4 style="font-weight:600;color:#3762c8;">Recent Energy Usage</h4>
        <table class="table" style="width:100%;margin-top:12px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
            <thead style="background:#e9effc;">
				   <tr>
					   <th style="text-align:center;">Date</th>
					<th style="text-align:center;">Facility</th>
					<th style="text-align:center;">Size</th>
					<th style="text-align:center;">kWh Consumed</th>
					<th style="text-align:center;">Avg Monthly kWh</th>
					<th style="text-align:center;">Diff (Actual - Avg)</th>
					<th style="text-align:center;">% Change</th>
					<th style="text-align:center;">Status</th>
					<th style="text-align:center;">Actions</th>
				</tr>
            </thead>
            <tbody>
				<?php $__empty_1 = true; $__currentLoopData = $recentUsages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
					   <tr>
						   <td style="text-align:center;">
							   <?php
								   $monthNum = (int) $usage->month;
								   $dateObj = DateTime::createFromFormat('!m', $monthNum);
								   $monthName = $dateObj ? $dateObj->format('M') : $usage->month;
							   ?>
							   <?php echo e($monthName); ?>/<?php echo e($usage->year); ?>

						   </td>
						<td style="text-align:center;"><?php echo e($usage->facility->name ?? '-'); ?></td>
						<td style="text-align:center; text-transform:capitalize;"><?php echo e($usage->facility->size ?? '-'); ?></td>
                        <td style="text-align:center;"><?php echo e($usage->kwh_consumed); ?></td>
                        <td style="text-align:center;"><?php echo e($usage->average_monthly_kwh ?? '-'); ?></td>
                        <td style="text-align:center;">
                            <?php if($usage->kwh_vs_avg !== null): ?>
                                <span style="color:<?php echo e($usage->kwh_vs_avg > 0 ? '#e11d48' : '#22c55e'); ?>;font-weight:600;">
                                    <?php echo e(number_format($usage->kwh_vs_avg, 2)); ?>

                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if($usage->percent_change !== null): ?>
                                <span style="color:<?php echo e($usage->percent_change > 0 ? '#e11d48' : '#22c55e'); ?>;font-weight:600;">
                                    <?php echo e(number_format($usage->percent_change, 2)); ?>%
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if($usage->status): ?>
                                <span style="font-weight:600;color:<?php echo e($usage->status == 'High' ? '#e11d48' : '#22c55e'); ?>;">
                                    <?php echo e($usage->status); ?>

                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="display:flex;gap:10px;align-items:center;justify-content:center;">
							<a href="<?php echo e(route('modules.energy.show', $usage->id)); ?>?facility_id=<?php echo e(request('facility_id')); ?>&month=<?php echo e(request('month')); ?>&year=<?php echo e(request('year')); ?>" class="action-btn-view" style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
                                <i class="fa fa-eye"></i>
								<span class="action-label-view" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">View</span>
                            </a>
							<a href="<?php echo e(route('modules.energy.edit', $usage->id)); ?>?facility_id=<?php echo e(request('facility_id')); ?>&month=<?php echo e(request('month')); ?>&year=<?php echo e(request('year')); ?>" class="action-btn-edit" style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
								<i class="fa fa-pen"></i>
								<span class="action-label-edit" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Edit</span>
							</a>
							<form action="<?php echo e(route('modules.energy.destroy', $usage->id)); ?>" method="POST" style="display:inline; margin:0; position:relative;">
								<?php echo csrf_field(); ?>
								<?php echo method_field('DELETE'); ?>
								<input type="hidden" name="facility_id" value="<?php echo e(request('facility_id')); ?>">
								<input type="hidden" name="month" value="<?php echo e(request('month')); ?>">
								<input type="hidden" name="year" value="<?php echo e(request('year')); ?>">
								<button type="submit" class="action-btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');" style="position:relative;background:none; border:none; color:#e11d48; font-size:1.2rem; cursor:pointer; padding:0; margin:0; display:inline-flex; align-items:center;">
									<i class="fa fa-trash"></i>
									<span class="action-label-delete" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Delete</span>
								</button>
							</form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center">No recent energy usage data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
// Add hover effect for all action labels (View, Edit, Delete)
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.action-btn-view').forEach(function(btn) {
		btn.addEventListener('mouseenter', function() {
			var label = btn.querySelector('.action-label-view');
			if(label) { label.style.visibility = 'visible'; label.style.opacity = '1'; }
		});
		btn.addEventListener('mouseleave', function() {
			var label = btn.querySelector('.action-label-view');
			if(label) { label.style.visibility = 'hidden'; label.style.opacity = '0'; }
		});
	});
	document.querySelectorAll('.action-btn-edit').forEach(function(btn) {
		btn.addEventListener('mouseenter', function() {
			var label = btn.querySelector('.action-label-edit');
			if(label) { label.style.visibility = 'visible'; label.style.opacity = '1'; }
		});
		btn.addEventListener('mouseleave', function() {
			var label = btn.querySelector('.action-label-edit');
			if(label) { label.style.visibility = 'hidden'; label.style.opacity = '0'; }
		});
	});
	document.querySelectorAll('.action-btn-delete').forEach(function(btn) {
		btn.addEventListener('mouseenter', function() {
			var label = btn.querySelector('.action-label-delete');
			if(label) { label.style.visibility = 'visible'; label.style.opacity = '1'; }
		});
		btn.addEventListener('mouseleave', function() {
			var label = btn.querySelector('.action-label-delete');
			if(label) { label.style.visibility = 'hidden'; label.style.opacity = '0'; }
		});
	});
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('modules.energy-monitoring.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy/index.blade.php ENDPATH**/ ?>