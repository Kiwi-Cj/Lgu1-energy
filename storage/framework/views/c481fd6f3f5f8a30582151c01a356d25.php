
<?php $__env->startSection('title', 'Edit Facility'); ?>
<?php $__env->startSection('content'); ?>
<div class="facility-edit-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:70vh;width:100%;">
	<div style="width:100%;max-width:520px;background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
		<h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;text-align:center;">Edit Facility</h2>
		<form action="<?php echo e(route('facilities.update', $facility->id)); ?>" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<?php echo method_field('PUT'); ?>
			<div style="margin-bottom:1.2rem;">
				<label for="image" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Image</label>
				<input type="file" name="image" id="image" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				<?php if($facility->image): ?>
					<div style="margin-top:8px;"><img src="<?php echo e(asset('storage/' . $facility->image)); ?>" alt="Current Image" style="width:100%;max-width:180px;border-radius:8px;"></div>
				<?php endif; ?>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="name" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Name</label>
				<input type="text" name="name" id="name" class="form-control" required value="<?php echo e(old('name', $facility->name)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="type" style="font-weight:500;display:block;margin-bottom:0.4rem;">Type</label>
				<select name="type" id="type" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Type</option>
					<option value="Market" <?php echo e(old('type', $facility->type)=='Market' ? 'selected' : ''); ?>>Market</option>
					<option value="Office" <?php echo e(old('type', $facility->type)=='Office' ? 'selected' : ''); ?>>Office</option>
					<option value="Warehouse" <?php echo e(old('type', $facility->type)=='Warehouse' ? 'selected' : ''); ?>>Warehouse</option>
					<option value="School" <?php echo e(old('type', $facility->type)=='School' ? 'selected' : ''); ?>>School</option>
					<option value="Hospital" <?php echo e(old('type', $facility->type)=='Hospital' ? 'selected' : ''); ?>>Hospital</option>
					<option value="Other" <?php echo e(old('type', $facility->type)=='Other' ? 'selected' : ''); ?>>Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="department" style="font-weight:500;display:block;margin-bottom:0.4rem;">Department</label>
				<select name="department" id="department" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Department</option>
					<option value="General Services" <?php echo e(old('department', $facility->department)=='General Services' ? 'selected' : ''); ?>>General Services</option>
					<option value="Engineering" <?php echo e(old('department', $facility->department)=='Engineering' ? 'selected' : ''); ?>>Engineering</option>
					<option value="Health" <?php echo e(old('department', $facility->department)=='Health' ? 'selected' : ''); ?>>Health</option>
					<option value="Education" <?php echo e(old('department', $facility->department)=='Education' ? 'selected' : ''); ?>>Education</option>
					<option value="Other" <?php echo e(old('department', $facility->department)=='Other' ? 'selected' : ''); ?>>Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="address" style="font-weight:500;display:block;margin-bottom:0.4rem;">Address</label>
				<input type="text" name="address" id="address" class="form-control" required value="<?php echo e(old('address', $facility->address)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="barangay" style="font-weight:500;display:block;margin-bottom:0.4rem;">Barangay</label>
				<input type="text" name="barangay" id="barangay" class="form-control" required value="<?php echo e(old('barangay', $facility->barangay)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="floor_area" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floor Area (sqm)</label>
					<input type="number" name="floor_area" id="floor_area" class="form-control" value="<?php echo e(old('floor_area', $facility->floor_area)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="floors" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floors</label>
					<input type="number" name="floors" id="floors" class="form-control" value="<?php echo e(old('floors', $facility->floors)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="year_built" style="font-weight:500;display:block;margin-bottom:0.4rem;">Year Built</label>
					<input type="number" name="year_built" id="year_built" class="form-control" value="<?php echo e(old('year_built', $facility->year_built)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="operating_hours" style="font-weight:500;display:block;margin-bottom:0.4rem;">Operating Hours</label>
					<input type="text" name="operating_hours" id="operating_hours" class="form-control" value="<?php echo e(old('operating_hours', $facility->operating_hours)); ?>" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="status" style="font-weight:500;display:block;margin-bottom:0.4rem;">Status</label>
				<select name="status" id="status" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="active" <?php echo e(old('status', $facility->status)=='active' ? 'selected' : ''); ?>>Active</option>
					<option value="inactive" <?php echo e(old('status', $facility->status)=='inactive' ? 'selected' : ''); ?>>Inactive</option>
					<option value="maintenance" <?php echo e(old('status', $facility->status)=='maintenance' ? 'selected' : ''); ?>>Maintenance</option>
				</select>
			</div>
			<div style="display:flex;justify-content:flex-end;gap:12px;">
				<a href="<?php echo e(route('facilities.index')); ?>" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Cancel</a>
				<button type="submit" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);cursor:pointer;">Update Facility</button>
			</div>
		</form>
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/edit.blade.php ENDPATH**/ ?>