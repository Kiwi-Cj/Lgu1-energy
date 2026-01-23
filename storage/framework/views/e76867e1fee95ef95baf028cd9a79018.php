
<?php $__env->startSection('title', 'Facilities'); ?>
<?php $__env->startSection('content'); ?>
<?php
	$userRole = strtolower(auth()->user()->role ?? '');
?>
<div class="facilities-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
	<h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Facilities</h2>
	<?php if($userRole !== 'staff'): ?>
		<a href="<?php echo e(route('facilities.create')); ?>" class="btn-add-facility" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s;">+ Add Facility</a>
	<?php endif; ?>
</div>

<div class="facility-summary-cards" style="display:flex;gap:24px;margin-bottom:2.2rem;flex-wrap:wrap;">
	<div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
		<div style="font-size:1.1rem;font-weight:500;color:#3762c8;">🏢 Total Facilities</div>
		<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($totalFacilities ?? '-'); ?> Buildings</div>
	</div>
	<div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
		<div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 Active Facilities</div>
		<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($activeFacilities ?? '-'); ?></div>
	</div>
	<div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
		<div style="font-size:1.1rem;font-weight:500;color:#f59e42;">🛠 Maintenance</div>
		<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($maintenanceFacilities ?? '-'); ?></div>
	</div>
	<div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
		<div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🚫 Inactive Facilities</div>
		<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($inactiveFacilities ?? '-'); ?></div>
	</div>
</div>

<div class="facilities-list" style="display: flex; flex-wrap: wrap; gap: 28px;">
	<?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
		<div class="facility-card" style="background:#fff; border-radius:16px; box-shadow:0 4px 18px rgba(0,0,0,0.08); padding:22px 18px; width:320px; display:flex; flex-direction:column; align-items:center;">
			<?php if($facility->image): ?>
				<img src="<?php echo e(asset('storage/' . $facility->image)); ?>" alt="Facility" style="width:100%; height:140px; object-fit:cover; border-radius:10px; margin-bottom:18px;">
			<?php else: ?>
				<div style="width:100%; height:140px; background:#f1f5f9; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:2.2rem; margin-bottom:18px;">
					<i class="fa fa-image"></i>
				</div>
			<?php endif; ?>
			<h3 style="font-size:1.25rem; font-weight:600; margin-bottom:8px; color:#222;"><?php echo e($facility->name ?? '-'); ?></h3>
			<div style="color:#6366f1; font-weight:500; margin-bottom:6px;"><?php echo e($facility->type ?? '-'); ?></div>
			<div style="font-size:0.98rem; color:#555; margin-bottom:10px;"><?php echo e($facility->address ?? '-'); ?></div>
				<div style="display:flex; gap:10px; align-items:center;">
					<a href="<?php echo e(route('facilities.show', $facility->id)); ?>" class="action-btn-view" style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
						<i class="fa fa-eye"></i>
						<span class="action-label-view" style="visibility:hidden;opacity:0;position:absolute;left:120%;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:3px 12px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.12);">View</span>
					</a>
					<?php if($userRole !== 'staff'): ?>
						<a href="<?php echo e(route('facilities.edit', $facility->id)); ?>" class="action-btn-edit" style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-pen"></i>
							<span class="action-label-edit" style="visibility:hidden;opacity:0;position:absolute;left:120%;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:3px 12px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Edit</span>
						</a>
						<form action="<?php echo e(route('facilities.destroy', $facility->id)); ?>" method="POST" style="display:inline; margin:0; position:relative;">
							<?php echo csrf_field(); ?>
							<?php echo method_field('DELETE'); ?>
							<button type="submit" class="action-btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this facility?');" style="background:none; border:none; color:#e11d48; font-size:1.2rem; cursor:pointer; padding:0; margin:0; display:inline-flex; align-items:center; position:relative;">
								<i class="fa fa-trash"></i>
								<span class="action-label-delete" style="visibility:hidden;opacity:0;position:absolute;left:120%;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:3px 12px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Delete</span>
							</button>
						</form>
					<?php endif; ?>
					<!-- Energy Profile Icon Button -->
					<a href="<?php echo e(url('/modules/facilities/' . $facility->id . '/energy-profile')); ?>" class="energy-profile-btn" style="position:relative; color:#f59e42; font-size:1.25rem; display:inline-flex; align-items:center; text-decoration:none;">
						<i class="fa fa-bolt"></i>
						<span class="energy-profile-label" style="visibility:hidden;opacity:0;position:absolute;left:120%;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:3px 12px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Energy Profile</span>
					</a>
					<script>
					// Add hover effect for all action labels (View, Edit, Delete, Energy Profile)
					document.addEventListener('DOMContentLoaded', function() {
						// View
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
						// Edit
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
						// Delete
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
						// Energy Profile (existing)
						document.querySelectorAll('.energy-profile-btn').forEach(function(btn) {
							btn.addEventListener('mouseenter', function() {
								var label = btn.querySelector('.energy-profile-label');
								if(label) { label.style.visibility = 'visible'; label.style.opacity = '1'; }
							});
							btn.addEventListener('mouseleave', function() {
								var label = btn.querySelector('.energy-profile-label');
								if(label) { label.style.visibility = 'hidden'; label.style.opacity = '0'; }
							});
						});
					});
					</script>
				</div>
		</div>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
		<div style="width:100%;text-align:center;color:#94a3b8;font-size:1.1rem;padding:32px 0;">No facilities found.</div>
	<?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/facilities/index.blade.php ENDPATH**/ ?>