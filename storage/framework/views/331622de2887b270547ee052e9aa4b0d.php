<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('content'); ?>
<?php
	$user = auth()->user();
	$role = strtolower($user?->role ?? '');
?>


<?php if($role !== 'super admin'): ?>
	<div style="max-width:600px;margin:60px auto 0 auto;padding:32px 24px;background:#fff0f3;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);text-align:center;">
		<h2 style="color:#e11d48;font-size:2rem;font-weight:800;margin-bottom:12px;">Restricted Access</h2>
		<div style="font-size:1.2rem;color:#b91c1c;margin-bottom:18px;">This page is for <b>Super Admin</b> only.</div>
		<a href="/modules/dashboard/index" style="display:inline-block;margin-top:18px;padding:10px 24px;background:#3762c8;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Go to Dashboard</a>
	</div>
<?php else: ?>
	<style>
		.action-btn-view:hover .action-label-view,
		.action-btn-edit:hover .action-label-edit,
		.action-btn-delete:hover .action-label-delete {
			visibility: visible !important;
			opacity: 1 !important;
		}

		/* Simple modal (no bootstrap needed) */
		.modal-backdrop {
			position: fixed;
			inset: 0;
			background: rgba(0,0,0,0.45);
			display: none;
			z-index: 9998;
		}
		.modal-sheet {
			position: fixed;
			inset: 0;
			display: none;
			z-index: 9999;
			padding: 18px;
			overflow: auto;
		}
		.modal-card {
			max-width: 860px;
			margin: 40px auto;
			background: #fff;
			border-radius: 14px;
		}
	</style>
	<h1 style="font-size:2.1rem;font-weight:800;color:#312e81;margin-bottom:10px;letter-spacing:-1px;">Users and Roles</h1>
	<div style="display:flex;flex-wrap:wrap;gap:24px 2%;margin-bottom:32px;justify-content:space-between;">
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#fff;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(55,98,200,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#4f46e5;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>👥</span> Total Users</div>
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;"><?php echo e($totalUsers ?? '-'); ?></div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#ecfdf5;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(34,197,94,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#16a34a;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🟢</span> Active Users</div>
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;"><?php echo e($activeUsers ?? '-'); ?></div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#f5f3ff;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(139,92,246,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#8b5cf6;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🔐</span> Roles</div>
			<div style="display:flex;flex-wrap:wrap;gap:8px 10px;margin-top:14px;">
				<?php
					$roleCounts = collect($users ?? [])->groupBy('role')->map->count();
					$rolesCollection = $roleCounts->mapWithKeys(function ($count, $role) {
						$label = str_replace('_', ' ', trim($role));
						$label = ucwords($label);
						return [$label => $count];
					})->sortKeys();
					if ($rolesCollection->isEmpty()) {
						$rolesCollection = collect([
							'Admin'          => 0,
							'Energy Officer' => 0,
							'Staff'          => 0,
						]);
					}
				?>
				<?php $__currentLoopData = $rolesCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleLabel => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<span style="background:#ede9fe;padding:4px 14px;border-radius:999px;font-size:1rem;font-weight:600;color:#7c3aed;box-shadow:0 0 0 1px rgba(124,58,237,0.18);display:inline-flex;align-items:center;gap:6px;">
						<span><?php echo e($roleLabel); ?></span>
						<span style="min-width:22px;height:22px;border-radius:999px;background:#7c3aed;color:#fdfbff;font-size:0.85rem;display:inline-flex;align-items:center;justify-content:center;"><?php echo e($count); ?></span>
					</span>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#fef2f2;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(225,29,72,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#e11d48;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🚫</span> Inactive Users</div>
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;"><?php echo e($inactiveUsers ?? '-'); ?></div>
		</div>
	</div>
	<!-- 4️⃣ ACTION BUTTONS -->
	<div style="margin-bottom:24px;display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
		<button type="button" onclick="openUserModalCreate()" style="display:flex;align-items:center;gap:8px;padding:10px 22px;font-size:1.08rem;font-weight:600;background:#4f46e5;color:#fff;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(79,70,229,0.10);transition:background 0.18s,box-shadow 0.18s;cursor:pointer;outline:none;">
			<span style="font-size:1.25rem;display:flex;align-items:center;">➕</span> Add New User
		</button>
		<a href="<?php echo e(route('users.roles')); ?>"
		   style="display:flex;align-items:center;gap:8px;padding:10px 22px;font-size:1.08rem;font-weight:600;background:#ede9fe;color:#7c3aed;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(124,58,237,0.10);text-decoration:none;transition:background 0.18s,box-shadow 0.18s,color 0.18s;cursor:pointer;outline:none;"
		   onmouseover="this.style.background='#c7d2fe';this.style.color='#4f46e5'"
		   onmouseout="this.style.background='#ede9fe';this.style.color='#7c3aed'">
			<span style="font-size:1.25rem;display:flex;align-items:center;">🧩</span> Manage Roles
		</a>
	</div>
	<!-- 3️⃣ USERS TABLE -->
	<div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);padding:18px;">
		<table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
			<thead style="background:#e9effc;">
				<tr>
					<th>User ID</th>
					<th>Full Name</th>
					<th>Email / Username</th>
					<th>Role</th>
					<th>Assigned Facility</th>
					<th>Status</th>
					<th style="text-align:center;vertical-align:middle;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
				<tr>
					<td><?php echo e($user->id); ?></td>
					<td><?php echo e($user->full_name ?? $user->name ?? '-'); ?></td>
					<td><?php echo e($user->email); ?></td>
					<td><?php echo e($user->role); ?></td>
					<td><?php echo e($user->facility->name ?? ($user->facility_id ? ('Facility #' . $user->facility_id) : '-')); ?></td>
					<td>
						<?php if(strtolower($user->status ?? '') == 'active'): ?>
							<span style="display:inline-block;padding:4px 12px;border-radius:999px;background:#dcfce7;color:#16a34a;font-weight:600;font-size:0.85rem;">
								<?php echo e(ucfirst($user->status)); ?>

							</span>
						<?php else: ?>
							<span style="display:inline-block;padding:4px 12px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:600;font-size:0.85rem;">
								<?php echo e($user->status ?? 'Inactive'); ?>

							</span>
						<?php endif; ?>
					</td>
					<td style="display:flex;gap:10px;align-items:center;justify-content:center;vertical-align:middle;">
						<a href="javascript:void(0)"
						   onclick="openUserModalView(this)"
						   data-user="<?php echo e(json_encode([
								'id' => $user->id,
								'full_name' => $user->full_name ?? $user->name ?? '',
								'email' => $user->email ?? '',
								'username' => $user->username ?? '',
								'role' => $user->role ?? '',
								'status' => $user->status ?? '',
								'facility' => $user->facility->name ?? null,
								'department' => $user->department ?? '',
								'contact_number' => $user->contact_number ?? '',
						   ], JSON_HEX_APOS | JSON_HEX_QUOT)); ?>"
						   class="action-btn-view"
						   style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-eye"></i>
							<span class="action-label-view" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">View</span>
						</a>
						<a href="javascript:void(0)"
						   onclick="openUserModalEdit(this)"
						   data-user-id="<?php echo e($user->id); ?>"
						   data-user="<?php echo e(json_encode([
								'id' => $user->id,
								'full_name' => $user->full_name ?? $user->name ?? '',
								'email' => $user->email ?? '',
								'username' => $user->username ?? '',
								'role' => strtolower($user->role ?? ''),
								'status' => strtolower($user->status ?? 'active'),
								'facility_id' => $user->facility_id ? (int)$user->facility_id : null,
								'department' => $user->department ?? '',
								'contact_number' => $user->contact_number ?? '',
						   ], JSON_HEX_APOS | JSON_HEX_QUOT)); ?>"
						   class="action-btn-edit"
						   style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none; cursor:pointer;">
							<i class="fa fa-pen"></i>
							<span class="action-label-edit" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Edit</span>
						</a>
						<a href="<?php echo e(url('modules/users/disable/'.$user->id)); ?>" class="action-btn-delete" style="position:relative; color:#e11d48; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-trash"></i>
							<span class="action-label-delete" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Disable</span>
						</a>
					</td>
				</tr>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
				<tr><td colspan="7">No users found.</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<!-- 5️⃣ ROLES OVERVIEW (Optional) -->
	<div style="margin-top:2.5rem;">
		<h3 style="font-size:1.2rem;font-weight:700;color:#3762c8;margin-bottom:10px;">Roles Overview</h3>
		<ul style="list-style:none;padding:0;">
			<li><b>Admin:</b> Full system access</li>
			<li><b>Energy Officer:</b> Reports & analytics</li>
			<li><b>Staff:</b> Data entry only</li>
		</ul>
	</div>
	<!-- USER MODAL -->
	<div class="modal-backdrop" id="userModalBackdrop" onclick="closeUserModal()"></div>
	<div class="modal-sheet" id="userModalSheet" aria-hidden="true">
		<div class="modal-card" role="dialog" aria-modal="true" style="background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(49,46,129,0.13);padding:0 0 18px 0;max-width:480px;width:100%;margin:40px auto;">
			<div class="modal-header" style="padding:28px 32px 0 32px;display:flex;align-items:center;justify-content:space-between;">
				<div>
					<h2 class="modal-title" id="userModalTitle" style="font-size:1.6rem;font-weight:800;color:#312e81;margin-bottom:2px;">Create User</h2>
					<div class="modal-subtitle" id="userModalSubtitle" style="font-size:1.08rem;color:#6366f1;font-weight:500;">Add a new system user and optionally assign a facility (Staff only).</div>
				</div>
				<button type="button" class="modal-close" onclick="closeUserModal()" style="background:none;border:none;font-size:1.3rem;color:#a1a1aa;cursor:pointer;padding:0 0 0 16px;">✕</button>
			</div>
			<form id="userModalForm" method="POST" action="<?php echo e(route('users.store')); ?>" style="padding:18px 32px 0 32px;">
				<?php echo csrf_field(); ?>
				<input type="hidden" id="userModalMethod" name="_method" value="">
				<div class="form-grid" style="display:grid;grid-template-columns:1fr;gap:16px;">
					<div class="form-field">
						<label for="um_full_name" style="font-weight:600;color:#312e81;">Full Name *</label>
						<input id="um_full_name" name="full_name" type="text" required style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
					</div>
					<div class="form-field">
						<label for="um_email" style="font-weight:600;color:#312e81;">Email *</label>
						<input id="um_email" name="email" type="email" required style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
					</div>
					<div class="form-field">
						<label for="um_username" style="font-weight:600;color:#312e81;">Username</label>
						<input id="um_username" name="username" type="text" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
					</div>
					<div class="form-field">
						<label for="um_role" style="font-weight:600;color:#312e81;">Role *</label>
						<select id="um_role" name="role" required onchange="toggleUserModalFacility()" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
							<option value="">Select Role</option>
							<option value="admin">Admin</option>
							<option value="staff">Staff</option>
							<option value="energy_officer">Energy Officer</option>
						</select>
					</div>
					<div class="form-field" id="um_facility_wrap" style="display:none;">
						<label for="um_facility_id" style="font-weight:600;color:#312e81;">Assigned Facility <span style='font-weight:400;color:#888;'>(Staff only, optional)</span></label>
						<select id="um_facility_id" name="facility_id" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
							<option value="">-- No Facility Assigned --</option>
							<?php $__currentLoopData = ($facilities ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</select>
					</div>
					<div class="form-field">
						<label for="um_status" style="font-weight:600;color:#312e81;">Status *</label>
						<select id="um_status" name="status" required style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
					</div>
					<div class="form-field">
						<label for="um_department" style="font-weight:600;color:#312e81;">Department</label>
						<input id="um_department" name="department" type="text" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
					</div>
					<div class="form-field">
						<label for="um_contact_number" style="font-weight:600;color:#312e81;">Contact Number</label>
						<input id="um_contact_number" name="contact_number" type="text" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
					</div>
				</div>
				<div id="um_password_block" style="margin-top:14px;">
					<div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
						<div class="form-field">
							<label for="um_password" style="font-weight:600;color:#312e81;">Password *</label>
							<input id="um_password" name="password" type="password" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
						</div>
						<div class="form-field">
							<label for="um_password_confirmation" style="font-weight:600;color:#312e81;">Confirm Password *</label>
							<input id="um_password_confirmation" name="password_confirmation" type="password" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e0e7ef;font-size:1rem;">
						</div>
					</div>
					<div style="margin-top:6px;color:#666;font-size:0.95rem;">Password is required only when creating a new user.</div>
				</div>
				<div class="form-actions" style="display:flex;justify-content:flex-end;gap:12px;margin-top:22px;">
					<button type="button" onclick="closeUserModal()" style="padding:10px 22px;font-size:1.08rem;font-weight:600;background:#ede9fe;color:#7c3aed;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(124,58,237,0.10);transition:background 0.18s,box-shadow 0.18s;color 0.18s;cursor:pointer;outline:none;">Cancel</button>
					<button type="submit" id="userModalSubmitBtn" style="padding:10px 22px;font-size:1.08rem;font-weight:600;background:#4f46e5;color:#fff;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(79,70,229,0.10);transition:background 0.18s,box-shadow 0.18s;cursor:pointer;outline:none;">Create User</button>
				</div>
			</form>
		</div>
	</div>

	<!-- USER VIEW MODAL (read-only) -->
	<div class="modal-backdrop" id="userViewModalBackdrop" onclick="closeUserViewModal()" style="display:none;"></div>
	<div class="modal-sheet" id="userViewModalSheet" aria-hidden="true" style="display:none;">
		<div class="modal-card" role="dialog" aria-modal="true">
			<div class="modal-header">
				<div>
					<h2 class="modal-title" id="userViewModalTitle">User Details</h2>
					<div class="modal-subtitle" id="userViewModalSubtitle">View user information and assigned facility.</div>
				</div>
				<button type="button" class="modal-close" onclick="closeUserViewModal()">Close</button>
			</div>

			<div class="form-grid">
				<div class="form-field">
					<label>Full Name</label>
					<div id="uv_full_name" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Email</label>
					<div id="uv_email" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Username</label>
					<div id="uv_username" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Role</label>
					<div id="uv_role" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Status</label>
					<div id="uv_status" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Assigned Facility</label>
					<div id="uv_facility" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Department</label>
					<div id="uv_department" style="font-weight:700;color:#111827;">-</div>
				</div>
				<div class="form-field">
					<label>Contact Number</label>
					<div id="uv_contact_number" style="font-weight:700;color:#111827;">-</div>
				</div>
			</div>
			<div class="form-actions" style="justify-content:flex-end;margin-top:16px;">
				<button type="button" class="btn-soft btn-cancel" onclick="closeUserViewModal()">Close</button>
			</div>
		</div>
	</div>

	<script>
	function openUserModal() {
		document.getElementById('userModalBackdrop').style.display = 'block';
		document.getElementById('userModalSheet').style.display = 'block';
		document.getElementById('userModalSheet').setAttribute('aria-hidden', 'false');
	}

	function closeUserModal() {
		document.getElementById('userModalBackdrop').style.display = 'none';
		document.getElementById('userModalSheet').style.display = 'none';
		document.getElementById('userModalSheet').setAttribute('aria-hidden', 'true');
	}

	function openUserViewModal() {
		document.getElementById('userViewModalBackdrop').style.display = 'block';
		document.getElementById('userViewModalSheet').style.display = 'block';
		document.getElementById('userViewModalSheet').setAttribute('aria-hidden', 'false');
	}

	function closeUserViewModal() {
		document.getElementById('userViewModalBackdrop').style.display = 'none';
		document.getElementById('userViewModalSheet').style.display = 'none';
		document.getElementById('userViewModalSheet').setAttribute('aria-hidden', 'true');
	}

	function toggleUserModalFacility() {
		const role = (document.getElementById('um_role').value || '').toLowerCase();
		const facilityWrap = document.getElementById('um_facility_wrap');
		if (role === 'staff') {
			facilityWrap.style.display = 'block';
		} else {
			facilityWrap.style.display = 'none';
			document.getElementById('um_facility_id').value = '';
		}
	}

	function resetUserModalFields() {
		document.getElementById('um_full_name').value = '';
		document.getElementById('um_email').value = '';
		document.getElementById('um_username').value = '';
		document.getElementById('um_role').value = '';
		document.getElementById('um_status').value = 'active';
		document.getElementById('um_facility_id').value = '';
		document.getElementById('um_department').value = '';
		document.getElementById('um_contact_number').value = '';
		document.getElementById('um_password').value = '';
		document.getElementById('um_password_confirmation').value = '';
		toggleUserModalFacility();
	}

	function openUserModalCreate() {
		resetUserModalFields();
		document.getElementById('userModalTitle').textContent = 'Create User';
		document.getElementById('userModalSubtitle').textContent = 'Add a new system user and optionally assign a facility (Staff only).';
		document.getElementById('userModalSubmitBtn').textContent = 'Create User';

		const form = document.getElementById('userModalForm');
		form.action = "<?php echo e(route('users.store')); ?>";
		document.getElementById('userModalMethod').value = '';

		// Password required on create
		document.getElementById('um_password_block').style.display = 'block';
		document.getElementById('um_password').required = true;
		document.getElementById('um_password_confirmation').required = true;

		openUserModal();
	}

	function openUserModalEdit(el) {
		resetUserModalFields();
		document.getElementById('userModalTitle').textContent = 'Edit User';
		document.getElementById('userModalSubtitle').textContent = 'Update user information and facility assignment.';
		document.getElementById('userModalSubmitBtn').textContent = 'Update User';

		const payloadRaw = el.getAttribute('data-user') || '{}';
		let payload = {};
		try { payload = JSON.parse(payloadRaw); } catch (e) { 
			console.error('Error parsing user data:', e);
			payload = {}; 
		}

		const userId = el.getAttribute('data-user-id') || payload.id || '';
		if (!userId) {
			console.error('Missing user id for edit modal.');
			return;
		}
		const fullName = payload.full_name || '';
		const email = payload.email || '';
		const username = payload.username || '';
		const role = (payload.role || '').toLowerCase();
		const status = (payload.status || 'active').toLowerCase();
		const facilityId = payload.facility_id ? String(payload.facility_id) : '';
		const department = payload.department || '';
		const contactNumber = payload.contact_number || '';

		// Populate all fields
		document.getElementById('um_full_name').value = fullName;
		document.getElementById('um_email').value = email;
		document.getElementById('um_username').value = username;
		document.getElementById('um_role').value = role;
		document.getElementById('um_status').value = status;
		document.getElementById('um_department').value = department;
		document.getElementById('um_contact_number').value = contactNumber;
        
		// Set facility_id (convert to string to match option values)
		if (facilityId) {
			document.getElementById('um_facility_id').value = facilityId;
		} else {
			document.getElementById('um_facility_id').value = '';
		}

		// Toggle facility field visibility based on role
		toggleUserModalFacility();

		const form = document.getElementById('userModalForm');
		form.action = "<?php echo e(url('modules/users')); ?>/" + userId;
		document.getElementById('userModalMethod').value = 'PUT';

		// Password not required on edit
		document.getElementById('um_password_block').style.display = 'none';
		document.getElementById('um_password').required = false;
		document.getElementById('um_password_confirmation').required = false;

		openUserModal();
	}

	function openUserModalView(el) {
		const payloadRaw = el.getAttribute('data-user') || '{}';
		let payload = {};
		try { payload = JSON.parse(payloadRaw); } catch (e) {
			console.error('Error parsing user data:', e);
			payload = {};
		}

		const clean = (val) => (val !== null && val !== undefined && val !== '') ? val : '-';

		document.getElementById('uv_full_name').textContent = clean(payload.full_name);
		document.getElementById('uv_email').textContent = clean(payload.email);
		document.getElementById('uv_username').textContent = clean(payload.username);
		document.getElementById('uv_role').textContent = clean(payload.role);
		document.getElementById('uv_status').textContent = clean(payload.status);
		document.getElementById('uv_facility').textContent = clean(payload.facility);
		document.getElementById('uv_department').textContent = clean(payload.department);
		document.getElementById('uv_contact_number').textContent = clean(payload.contact_number);

		openUserViewModal();
	}

	// ESC to close
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape') closeUserModal();
	});

	</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/users/index.blade.php ENDPATH**/ ?>