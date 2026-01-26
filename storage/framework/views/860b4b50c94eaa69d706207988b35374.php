<?php $__env->startSection('title', 'User Roles'); ?>
<?php $__env->startSection('content'); ?>
<style>
    .action-btn-edit:hover .action-label-edit,
    .action-btn-delete:hover .action-label-delete {
        visibility: visible !important;
        opacity: 1 !important;
    }
</style>
<div style="max-width:1200px;margin:0 auto;">
	<!-- 1️⃣ Page Header -->
	<div style="margin-bottom:24px;">
		<h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">User Roles Management</h1>
		<div style="font-size:1.2rem;color:#555;">Define system roles and manage access permissions</div>
		<div style="margin-top:8px;font-size:1rem;color:#888;">
			<span>🔐 Total Roles: <?php echo e($totalRoles ?? '2'); ?></span> |
			<span>👥 Assigned Users: <?php echo e($assignedUsers ?? '-'); ?></span>
		</div>
	</div>
	<!-- 2️⃣ SUMMARY CARDS -->
	<div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f3e8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(139,92,246,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#8b5cf6;">🔐 Total Roles</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($totalRoles ?? '2'); ?></div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#3762c8;">👥 Assigned Users</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($assignedUsers ?? '-'); ?></div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#22c55e;">✅ Active Roles</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($activeRoles ?? '2'); ?></div>
		</div>
	</div>
	<!-- 3️⃣ ACTION BUTTONS -->
	<div style="margin-bottom:18px;display:flex;gap:18px;flex-wrap:wrap;">
		<a href="#" class="btn btn-success" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:1rem;display:flex;align-items:center;gap:8px;"><span>➕</span> Add New Role</a>
	</div>
	<!-- 4️⃣ ROLES TABLE -->
	<div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);padding:18px;">
		<table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
			<thead style="background:#e9effc;">
				<tr>
					<th style="width:5%;">#</th>
					<th style="width:20%;">Role Name</th>
					<th>Description</th>
					<th style="width:25%;">Permissions</th>
					<th style="width:18%;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$roles = [
						['id' => 1, 'name' => 'Admin', 'description' => 'Full system access including user and system management', 'permissions' => 'All Permissions', 'badge_color' => '#6366f1'],
						['id' => 2, 'name' => 'Staff', 'description' => 'Limited access for data entry and basic operations', 'permissions' => 'View / Encode', 'badge_color' => '#6b7280'],
					];
					$roles = $roles ?? [];
				?>
				<?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
				<tr>
					<td><?php echo e($role['id']); ?></td>
					<td>
						<span style="display:inline-block;padding:4px 12px;border-radius:999px;background:<?php echo e($role['badge_color'] ?? '#6366f1'); ?>;color:#fff;font-weight:600;font-size:0.85rem;">
							<?php echo e($role['name']); ?>

						</span>
					</td>
					<td><?php echo e($role['description']); ?></td>
					<td>
						<span style="display:inline-block;padding:4px 12px;border-radius:999px;background:#dcfce7;color:#16a34a;font-weight:600;font-size:0.85rem;">
							<?php echo e($role['permissions']); ?>

						</span>
					</td>
					<td style="display:flex;gap:10px;align-items:center;justify-content:center;">
						<a href="#" class="action-btn-edit" style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-pen"></i>
							<span class="action-label-edit" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Edit</span>
						</a>
						<a href="#" class="action-btn-delete" style="position:relative; color:#e11d48; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-trash"></i>
							<span class="action-label-delete" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Delete</span>
						</a>
					</td>
				</tr>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
				<tr><td colspan="5">No roles found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<!-- 5️⃣ INFO FOOTER -->
	<div style="margin-top:2.5rem;">
		<small style="color:#888;font-size:0.9rem;">
			Note: Roles determine what actions a user can perform within the system.
		</small>
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/users/roles.blade.php ENDPATH**/ ?>