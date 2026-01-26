
<?php $__env->startSection('title', 'Billing Management'); ?>
<?php $__env->startSection('content'); ?>
<div class="billing-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
	<div style="display:flex;flex-direction:column;">
		<h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin:0;">Billing Management</h2>
		<p class="text-muted" style="margin:0;">View, filter, and manage all bills per facility and month. Track payment status and billing details easily.</p>
	</div>
	<a href="<?php echo e(route('modules.billing.create')); ?>" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:7px 22px;font-size:1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);text-decoration:none;">+ Add New Bill</a>
</div>
<div class="row" style="display:flex;gap:18px;flex-wrap:wrap;margin-bottom:2rem;">
	<div class="card" style="flex:1 1 180px;min-width:180px;background:#f5f8ff;padding:18px 14px;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
		<div style="font-size:1.05rem;font-weight:500;color:#3762c8;">Total Facilities Billed</div>
		<div style="font-size:1.5rem;font-weight:700;margin:8px 0;"><?php echo e($totalFacilitiesBilled ?? '-'); ?></div>
	</div>
	<div class="card" style="flex:1 1 180px;min-width:180px;background:#f5f8ff;padding:18px 14px;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
		<div style="font-size:1.05rem;font-weight:500;color:#3762c8;">Total Amount Billed (PHP)</div>
		<div style="font-size:1.5rem;font-weight:700;margin:8px 0;"><?php echo e($totalAmountBilled ?? '-'); ?></div>
	</div>
	<div class="card" style="flex:1 1 180px;min-width:180px;background:#f5f8ff;padding:18px 14px;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
		<div style="font-size:1.05rem;font-weight:500;color:#3762c8;">Facilities with Pending Bills</div>
		<div style="font-size:1.5rem;font-weight:700;margin:8px 0;"><?php echo e($pendingFacilities ?? '-'); ?></div>
	</div>
</div>
<form method="GET" action="" style="margin-bottom:18px;display:flex;gap:14px;align-items:end;flex-wrap:wrap;">
	<div style="display:flex;flex-direction:column;">
		<label for="facility_id" style="font-weight:600;margin-bottom:4px;">Facility</label>
		<select name="facility_id" id="facility_id" class="form-control" style="min-width:140px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
			<option value="">All Facilities</option>
			<?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<option value="<?php echo e($facility->id); ?>" <?php if(isset($filterFacilityId) && $filterFacilityId == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</select>
	</div>
	<div style="display:flex;flex-direction:column;">
		<label for="month" style="font-weight:600;margin-bottom:4px;">Month</label>
		<input type="month" name="month" id="month" class="form-control" value="<?php echo e($filterMonth ?? ''); ?>" style="min-width:110px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
	</div>
	<div style="display:flex;flex-direction:column;">
		<label for="status" style="font-weight:600;margin-bottom:4px;">Status</label>
		<select name="status" id="status" class="form-control" style="min-width:110px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
			<option value="">All Status</option>
			<option value="Paid" <?php if(request('status')=='Paid'): ?> selected <?php endif; ?>>Paid</option>
			<option value="Pending" <?php if(request('status')=='Pending'): ?> selected <?php endif; ?>>Pending</option>
			<option value="Overdue" <?php if(request('status')=='Overdue'): ?> selected <?php endif; ?>>Overdue</option>
		</select>
	</div>
	<div style="display:flex;flex-direction:column;justify-content:flex-end;">
		<button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;">Filter</button>
	</div>
</form>
<table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
	<thead style="background:#e9effc;">
		<tr>
			<th style="text-align:center;">Facility Name</th>
			<th style="text-align:center;">Month / Year</th>
			<th style="text-align:center;">kWh Consumed</th>
			<th style="text-align:center;">Unit Cost (PHP)</th>
			<th style="text-align:center;">Total Bill (PHP)</th>
			<th style="text-align:center;">Status</th>
			<th style="text-align:center;">Action</th>
		</tr>
	</thead>
	<tbody>
		<?php $__empty_1 = true; $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
		<tr>
			<td><?php echo e($bill->facility->name ?? '-'); ?></td>
			<td><?php echo e($bill->month ? date('M Y', strtotime($bill->month.'-01')) : '-'); ?></td>
			<td><?php echo e($bill->kwh_consumed); ?></td>
			<td><?php echo e($bill->unit_cost); ?></td>
			<td><?php echo e($bill->total_bill); ?></td>
			<td>
				<span style="font-weight:600;color:
					<?php if($bill->status == 'Paid'): ?> #22c55e;
					<?php elseif($bill->status == 'Pending'): ?> #eab308;
					<?php else: ?> #e11d48;
					<?php endif; ?>">
					<?php echo e($bill->status); ?>

				</span>
			</td>
			<td style="display:flex;gap:10px;align-items:center;justify-content:center;">
				   <div class="action-btn-group" style="display:flex;gap:10px;align-items:center;position:relative;">
					   <div class="action-tooltip-container" style="position:relative;">
						   <a href="<?php echo e(route('modules.billing.show', $bill->id)); ?>" class="action-btn-view action-btn-tooltip" data-tooltip="View" style="color:#6366f1;font-size:1.2rem;display:inline-flex;align-items:center;text-decoration:none;position:relative;">
							   <i class="fa fa-eye"></i>
							   <span class="action-tooltip-label" style="display:none;position:absolute;left:-90px;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;white-space:nowrap;z-index:10;">View</span>
						   </a>
					   </div>
					   <div class="action-tooltip-container" style="position:relative;">
						   <a href="<?php echo e(route('modules.billing.edit', $bill->id)); ?>" class="action-btn-edit action-btn-tooltip" data-tooltip="Edit" style="color:#6366f1;font-size:1.2rem;display:inline-flex;align-items:center;text-decoration:none;position:relative;">
							   <i class="fa fa-pen"></i>
							   <span class="action-tooltip-label" style="display:none;position:absolute;left:-90px;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;white-space:nowrap;z-index:10;">Edit</span>
						   </a>
					   </div>
					   <div class="action-tooltip-container" style="position:relative;">
						   <form action="<?php echo e(route('modules.billing.destroy', $bill->id)); ?>" method="POST" style="display:inline;margin:0;">
							   <?php echo csrf_field(); ?>
							   <?php echo method_field('DELETE'); ?>
							   <button type="submit" class="action-btn-delete action-btn-tooltip" data-tooltip="Delete" title="Delete" onclick="return confirm('Are you sure you want to delete this bill?');" style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;padding:0;margin:0;display:inline-flex;align-items:center;position:relative;">
								   <i class="fa fa-trash"></i>
								   <span class="action-tooltip-label" style="display:none;position:absolute;left:-90px;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;white-space:nowrap;z-index:10;">Delete</span>
							   </button>
						   </form>
					   </div>
				   </div>
			</td>
		</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
		<tr><td colspan="7" class="text-center">No bills found.</td></tr>
		<?php endif; ?>
	</tbody>
</table>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Tooltip hover logic for action buttons (show label on hover, left side)
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.action-btn-tooltip').forEach(function(btn) {
		btn.addEventListener('mouseenter', function() {
			var label = btn.querySelector('.action-tooltip-label');
			if(label) label.style.display = 'block';
		});
		btn.addEventListener('mouseleave', function() {
			var label = btn.querySelector('.action-tooltip-label');
			if(label) label.style.display = 'none';
		});
	});
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/billing/index.blade.php ENDPATH**/ ?>