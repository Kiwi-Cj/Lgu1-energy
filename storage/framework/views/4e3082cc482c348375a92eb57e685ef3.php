<?php $__env->startSection('title', 'Energy Monitoring'); ?>
<?php $__env->startSection('content'); ?>


<div class="energy-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
	<div style="display:flex; flex-direction:column;">
		<h2 style="font-size:2rem; font-weight:700; color:#3762c8; margin:0;">Energy Monitoring Dashboard</h2>
		<p class="text-muted" style="margin:0;">Track and analyze energy usage across all facilities. View trends, total consumption, and identify opportunities for conservation.</p>
	</div>
	<div style="display:flex;gap:12px;">
		   <a href="<?php echo e(route('modules.energy.annual')); ?>" class="btn btn-info" style="background: linear-gradient(90deg,#22c55e,#2563eb); color:#fff; font-weight:600; border:none; border-radius:5px; padding:2px 10px; font-size:0.85rem; min-width:0; height:32px; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; display:flex; align-items:center; gap:4px;">
			   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H3zm0-1h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2z"/><path d="M8 4a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 8.293V4.5A.5.5 0 0 1 8 4z"/></svg>
			   <span style="display:none;display:inline-block;">Annual</span>
		   </a>
		   <a href="<?php echo e(route('modules.energy.create')); ?>" class="btn-add-facility" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:5px; padding:2px 10px; font-size:0.85rem; min-width:0; height:32px; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; display:flex; align-items:center; gap:4px;">
			   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
			   <span style="display:none;display:inline-block;">Add</span>
		   </a>
		   <a
			   href="<?php echo e(route('modules.energy.export-excel', array_filter([
				   'facility_id' => request('facility_id'),
				   'month' => request('month'),
				   'year' => request('year'),
			   ]))); ?>"
			   class="btn btn-success"
			   style="background: linear-gradient(90deg,#22c55e,#16a34a); color:#fff; font-weight:600; border:none; border-radius:5px; padding:2px 10px; font-size:0.85rem; min-width:0; height:32px; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; display:flex; align-items:center; gap:4px;"
		   >
			   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5V13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2.6a.5.5 0 0 1 1 0V13a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3v-2.6a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
			   <span style="display:none;display:inline-block;">Excel</span>
		   </a>
	</div>
	<!-- Monthly kWh Graph -->
	<div class="mb-5" style="margin-top:32px;">
		<h4 style="font-weight:600;color:#3762c8;margin-bottom:12px;">Monthly kWh Graph (<?php echo e($graphYear); ?>)</h4>
		<canvas id="monthlyKwhChart" height="120"></canvas>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxMonthly = document.getElementById('monthlyKwhChart').getContext('2d');
const monthlyKwhChart = new Chart(ctxMonthly, {
	type: 'line',
	data: {
		labels: [
			'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
			'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
		],
		datasets: [
			{
				label: 'Actual kWh',
				data: <?php echo json_encode(array_values($monthlyKwh), 15, 512) ?>,
				borderColor: '#2563eb',
				backgroundColor: 'rgba(37,99,235,0.08)',
				fill: true,
				tension: 0.3,
			},
			{
				label: 'Baseline kWh',
				data: <?php echo json_encode(array_values($baselineKwh), 15, 512) ?>,
				borderColor: '#22c55e',
				backgroundColor: 'rgba(34,197,94,0.08)',
				fill: false,
				// borderDash removed for solid line
				pointRadius: 0,
				tension: 0.1,
			}
		]
	},
	options: {
		responsive: true,
		plugins: {
			legend: { position: 'top' },
		},
		scales: {
			y: { beginAtZero: true }
		}
	}
});
</script>

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

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/energy/index.blade.php ENDPATH**/ ?>