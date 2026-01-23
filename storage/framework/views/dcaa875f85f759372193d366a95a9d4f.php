
<?php $__env->startSection('title', 'Create User'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:800px;margin:0 auto;">
	<!-- 1️⃣ Page Header -->
	<div style="margin-bottom:24px;">
		<h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">Create User</h1>
		<div style="font-size:1.2rem;color:#555;">Add a new system user and optionally assign a facility (Staff only)</div>
	</div>

	<!-- 2️⃣ CREATE FORM -->
	<div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);padding:32px;">
		<form action="<?php echo e(route('users.store')); ?>" method="POST">
			<?php echo csrf_field(); ?>

			<!-- Full Name -->
			<div style="margin-bottom:20px;">
				<label for="full_name" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Full Name <span style="color:#e11d48;">*</span></label>
				<input type="text" name="full_name" id="full_name" value="<?php echo e(old('full_name')); ?>" required
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Email -->
			<div style="margin-bottom:20px;">
				<label for="email" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Email <span style="color:#e11d48;">*</span></label>
				<input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Username -->
			<div style="margin-bottom:20px;">
				<label for="username" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Username</label>
				<input type="text" name="username" id="username" value="<?php echo e(old('username')); ?>"
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Password -->
			<div style="margin-bottom:20px;">
				<label for="password" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Password <span style="color:#e11d48;">*</span></label>
				<input type="password" name="password" id="password" required
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Confirm Password -->
			<div style="margin-bottom:20px;">
				<label for="password_confirmation" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Confirm Password <span style="color:#e11d48;">*</span></label>
				<input type="password" name="password_confirmation" id="password_confirmation" required
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
			</div>

			<!-- Role -->
			<div style="margin-bottom:20px;">
				<label for="role" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Role <span style="color:#e11d48;">*</span></label>
				<select name="role" id="role" required onchange="toggleFacilityField()"
						style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;background:#fff;">
					<option value="">Select Role</option>
					<option value="admin" <?php echo e(old('role') === 'admin' ? 'selected' : ''); ?>>Admin</option>
					<option value="staff" <?php echo e(old('role') === 'staff' ? 'selected' : ''); ?>>Staff</option>
					<option value="energy_officer" <?php echo e(old('role') === 'energy_officer' ? 'selected' : ''); ?>>Energy Officer</option>
				</select>
				<?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Facility Assignment (only for Staff) -->
			<div id="facility-field" style="margin-bottom:20px;display:none;">
				<label for="facility_id" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Assigned Facility</label>
				<select name="facility_id" id="facility_id"
						style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;background:#fff;">
					<option value="">-- No Facility Assigned --</option>
					<?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($facility->id); ?>" <?php echo e(old('facility_id') == $facility->id ? 'selected' : ''); ?>>
							<?php echo e($facility->name); ?>

						</option>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</select>
				<small style="color:#666;font-size:0.85rem;display:block;margin-top:4px;">
					Only Staff users should have a facility assignment.
				</small>
				<?php $__errorArgs = ['facility_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Status -->
			<div style="margin-bottom:20px;">
				<label for="status" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Status <span style="color:#e11d48;">*</span></label>
				<select name="status" id="status" required
						style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;background:#fff;">
					<option value="active" <?php echo e(old('status','active') === 'active' ? 'selected' : ''); ?>>Active</option>
					<option value="inactive" <?php echo e(old('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
				</select>
				<?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Department -->
			<div style="margin-bottom:20px;">
				<label for="department" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Department</label>
				<input type="text" name="department" id="department" value="<?php echo e(old('department')); ?>"
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['department'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Contact Number -->
			<div style="margin-bottom:24px;">
				<label for="contact_number" style="display:block;font-weight:600;color:#222;margin-bottom:8px;">Contact Number</label>
				<input type="text" name="contact_number" id="contact_number" value="<?php echo e(old('contact_number')); ?>"
					   style="width:100%;padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:1rem;">
				<?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span style="color:#e11d48;font-size:0.9rem;"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
			</div>

			<!-- Action Buttons -->
			<div style="display:flex;gap:12px;justify-content:flex-end;">
				<a href="<?php echo e(route('users.index')); ?>" style="padding:12px 24px;border-radius:8px;font-weight:600;font-size:1rem;text-decoration:none;background:#e5e7eb;color:#374151;">Cancel</a>
				<button type="submit" style="padding:12px 24px;border-radius:8px;font-weight:600;font-size:1rem;background:#22c55e;color:#fff;border:none;cursor:pointer;">Create User</button>
			</div>
		</form>
	</div>
</div>

<script>
function toggleFacilityField() {
	const roleSelect = document.getElementById('role');
	const facilityField = document.getElementById('facility-field');

	if (roleSelect.value === 'staff') {
		facilityField.style.display = 'block';
	} else {
		facilityField.style.display = 'none';
		document.getElementById('facility_id').value = '';
	}
}

document.addEventListener('DOMContentLoaded', function() {
	toggleFacilityField();
});
</script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/users/create.blade.php ENDPATH**/ ?>