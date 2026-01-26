
<?php $__env->startSection('title', 'Facility Details'); ?>
<?php $__env->startSection('content'); ?>

<div class="facility-details-wrapper" style="max-width:600px;margin:0 auto;">
   <div style="background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
	   <div style="display:flex;align-items:center;gap:22px;margin-bottom:1.5rem;">
		   <?php if($facility->image): ?>
			   <img src="<?php echo e(asset('storage/' . $facility->image)); ?>" alt="Facility" style="width:140px;height:100px;object-fit:cover;border-radius:10px;">
		   <?php else: ?>
			   <div style="width:140px;height:100px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2.2rem;">
				   <i class="fa fa-image" title="No Image"></i>
			   </div>
		   <?php endif; ?>
		   <div>
			   <h2 style="font-size:1.7rem;font-weight:700;color:#222;margin:0 0 8px 0;"><?php echo e($facility->name ?? '-'); ?></h2>
			   <div style="color:#6366f1;font-weight:500;margin-bottom:4px;"><?php echo e($facility->type ?? '-'); ?></div>
			   <div style="font-size:1rem;color:#555;"><?php echo e($facility->department ?? '-'); ?></div>
		   </div>
	   </div>
	   <div style="margin-bottom:1.1rem;"><strong>Address:</strong> <?php echo e($facility->address ?? '-'); ?></div>
	   <div style="margin-bottom:1.1rem;"><strong>Barangay:</strong> <?php echo e($facility->barangay ?? '-'); ?></div>
	   <div style="display:flex;gap:18px;margin-bottom:1.1rem;">
		   <div><strong>Floor Area:</strong> <?php echo e($facility->floor_area ?? '-'); ?> sqm</div>
		   <div><strong>Floors:</strong> <?php echo e($facility->floors ?? '-'); ?></div>
	   </div>
	   <div style="display:flex;gap:18px;margin-bottom:1.1rem;">
		   <div><strong>Year Built:</strong> <?php echo e($facility->year_built ?? '-'); ?></div>
		   <div><strong>Operating Hours:</strong> <?php echo e($facility->operating_hours ?? '-'); ?></div>
	   </div>
	   <div style="margin-bottom:1.1rem;"><strong>Status:</strong> <span style="color:#2563eb;font-weight:600;"><?php echo e(ucfirst($facility->status) ?? '-'); ?></span></div>

	   <div style="margin: 24px 0 0 0; font-size:1.15rem;">
		   <strong>Average kWh (First 3 Months):</strong>
		   <?php if($showAvg): ?>
			   <span style="color:#2563eb;font-weight:600;"><?php echo e(number_format($avgKwh, 2)); ?> kWh</span>
		   <?php else: ?>
			   <span style="color:#e11d48;font-weight:500;">Insufficient data (at least 3 months required)</span>
		   <?php endif; ?>
	   </div>

	   <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:2.2rem;">
		   <a href="<?php echo e(route('facilities.edit', $facility->id)); ?>" class="btn" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Edit</a>
		   <button type="button" onclick="openDeleteFacilityModal(<?php echo e($facility->id); ?>)" style="background:#e11d48;color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 22px;cursor:pointer;">Delete</button>
	   </div>
   </div>
</div>

<!-- Delete Facility Modal -->
<div id="deleteFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="closeDeleteFacilityModal()" style="top:12px;right:12px;position:absolute;font-size:1.5rem;background:none;border:none;color:#e11d48;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Facility</h2>
        <div style="font-size:0.98rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this facility? This action cannot be undone.</div>
        <form id="deleteFacilityForm" method="POST" action="<?php echo e(route('facilities.destroy', $facility->id)); ?>" style="display:flex;flex-direction:column;gap:16px;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="energy-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;background:#e11d48;color:#fff;">Yes, Delete</button>
            <button type="button" class="energy-modal-btn cancel" onclick="closeDeleteFacilityModal()" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;background:#f3f4f6;color:#222;">Cancel</button>
        </form>
    </div>
</div>
<script>
function openDeleteFacilityModal(id) {
    document.getElementById('deleteFacilityModal').style.display = 'flex';
}
function closeDeleteFacilityModal() {
    document.getElementById('deleteFacilityModal').style.display = 'none';
}
</script>

	
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/show.blade.php ENDPATH**/ ?>