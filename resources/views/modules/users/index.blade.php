@extends('layouts.qc-admin')
@section('title', 'Users')
@section('content')
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
		box-shadow: 0 12px 40px rgba(0,0,0,0.22);
		padding: 22px 22px 18px 22px;
	}
	.modal-header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 12px;
		margin-bottom: 16px;
	}
	.modal-title {
		margin: 0;
		font-size: 1.4rem;
		font-weight: 800;
		color: #3762c8;
	}
	.modal-subtitle {
		margin-top: 4px;
		color: #555;
		font-size: 0.95rem;
	}
	.modal-close {
		border: none;
		background: #f3f4f6;
		color: #111827;
		border-radius: 10px;
		padding: 8px 10px;
		cursor: pointer;
	}
	.form-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 14px;
	}
	@media(max-width: 820px){
		.form-grid{ grid-template-columns: 1fr; }
	}
	.form-field label {
		display: block;
		font-weight: 700;
		color: #222;
		margin-bottom: 6px;
	}
	.form-field input, .form-field select {
		width: 100%;
		padding: 11px 12px;
		border-radius: 10px;
		border: 1px solid #d1d5db;
		font-size: 1rem;
		background: #fff;
	}
	.form-actions {
		display: flex;
		gap: 12px;
		justify-content: flex-end;
		margin-top: 18px;
	}
	.btn-soft {
		padding: 10px 18px;
		border-radius: 10px;
		font-weight: 700;
		border: none;
		cursor: pointer;
	}
	.btn-cancel { background:#e5e7eb; color:#374151; }
	.btn-save { background:#22c55e; color:#fff; }

	/* Top action buttons (Add User / Manage Roles) */
	.top-action-btn{
		padding:10px 24px;
		border-radius:8px;
		font-weight:600;
		font-size:1rem;
		display:flex;
		align-items:center;
		gap:8px;
		line-height:1;
		cursor:pointer;
		text-decoration:none;
		border:none;
	}
	.top-action-btn--success{
		background:#22c55e;
		color:#fff;
	}
	.top-action-btn--success:hover{ filter: brightness(0.95); }
	.top-action-btn--primary{
		background:#3762c8;
		color:#fff;
	}
	.top-action-btn--primary:hover{ filter: brightness(0.95); }
</style>
<div style="max-width:1200px;margin:0 auto;">
	<!-- 1️⃣ Page Header -->
	<div style="margin-bottom:24px;">
		<h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">Users & Roles Management</h1>
		<div style="font-size:1.2rem;color:#555;">Manage system users, roles, and access permissions</div>
		<div style="margin-top:8px;font-size:1rem;color:#888;">
			<span>👥 Total Users: {{ $totalUsers ?? '-' }}</span> |
			<span>🟢 Active Users: {{ $activeUsers ?? '-' }}</span>
		</div>
	</div>
	<!-- 2️⃣ SUMMARY CARDS -->
	<div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#3762c8;">👥 Total Users</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $totalUsers ?? '-' }}</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 Active Users</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $activeUsers ?? '-' }}</div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#f3e8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(139,92,246,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#8b5cf6;">🔐 Roles</div>
            @php
                // Build role counts based on current users list
                $roleCounts = collect($users ?? [])
                    ->groupBy('role')
                    ->map
                    ->count();

                // Transform to "Label => count" and sort alphabetically
                $rolesCollection = $roleCounts->mapWithKeys(function ($count, $role) {
                    $label = str_replace('_', ' ', trim($role));
                    $label = ucwords($label);
                    return [$label => $count];
                })->sortKeys();

                // Fallback roles when there is no user data yet
                if ($rolesCollection->isEmpty()) {
                    $rolesCollection = collect([
                        'Admin'          => 0,
                        'Energy Officer' => 0,
                        'Staff'          => 0,
                    ]);
                }
            @endphp

            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">
                @foreach($rolesCollection as $roleLabel => $count)
                    <span style="background:#fdfbff;padding:4px 10px;border-radius:999px;font-size:0.85rem;font-weight:600;color:#7c3aed;box-shadow:0 0 0 1px rgba(124,58,237,0.25);display:inline-flex;align-items:center;gap:6px;">
                        <span>{{ $roleLabel }}</span>
                        @if($count > 0)
                            <span style="min-width:20px;height:20px;border-radius:999px;background:#7c3aed;color:#fdfbff;font-size:0.7rem;display:inline-flex;align-items:center;justify-content:center;">
                                {{ $count }}
                            </span>
                        @endif
                    </span>
                @endforeach
            </div>
		</div>
		<div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
			<div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🚫 Inactive Users</div>
			<div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $inactiveUsers ?? '-' }}</div>
		</div>
	</div>
	<!-- 4️⃣ ACTION BUTTONS -->
	<div style="margin-bottom:18px;display:flex;gap:18px;flex-wrap:wrap;">
		<button type="button" onclick="openUserModalCreate()" class="top-action-btn top-action-btn--success">
			<span>➕</span> Add New User
		</button>
		<a href="{{ route('users.roles') }}"
		   class="top-action-btn top-action-btn--primary">
			<span>🧩</span> Manage Roles
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
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				@forelse($users as $user)
				<tr>
					<td>{{ $user->id }}</td>
					<td>{{ $user->full_name ?? $user->name ?? '-' }}</td>
					<td>{{ $user->email }}</td>
					<td>{{ $user->role }}</td>
					<td>
						{{ $user->facility->name ?? ($user->facility_id ? ('Facility #' . $user->facility_id) : '-') }}
					</td>
					<td>
                        @if(strtolower($user->status ?? '') == 'active')
                            <span style="display:inline-block;padding:4px 12px;border-radius:999px;background:#dcfce7;color:#16a34a;font-weight:600;font-size:0.85rem;">
                                {{ ucfirst($user->status) }}
                            </span>
                        @else
                            <span style="display:inline-block;padding:4px 12px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:600;font-size:0.85rem;">
                                {{ $user->status ?? 'Inactive' }}
                            </span>
                        @endif
					</td>
					<td style="display:flex;gap:10px;align-items:center;justify-content:center;">
						<a href="javascript:void(0)"
						   onclick="openUserModalView(this)"
						   data-user="{{ json_encode([
								'id' => $user->id,
								'full_name' => $user->full_name ?? $user->name ?? '',
								'email' => $user->email ?? '',
								'username' => $user->username ?? '',
								'role' => $user->role ?? '',
								'status' => $user->status ?? '',
								'facility' => $user->facility->name ?? null,
								'department' => $user->department ?? '',
								'contact_number' => $user->contact_number ?? '',
						   ], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
						   class="action-btn-view"
						   style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-eye"></i>
							<span class="action-label-view" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">View</span>
						</a>
						<a href="javascript:void(0)"
						   onclick="openUserModalEdit(this)"
						   data-user-id="{{ $user->id }}"
						   data-user="{{ json_encode([
								'id' => $user->id,
								'full_name' => $user->full_name ?? $user->name ?? '',
								'email' => $user->email ?? '',
								'username' => $user->username ?? '',
								'role' => strtolower($user->role ?? ''),
								'status' => strtolower($user->status ?? 'active'),
								'facility_id' => $user->facility_id ? (int)$user->facility_id : null,
								'department' => $user->department ?? '',
								'contact_number' => $user->contact_number ?? '',
						   ], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
						   class="action-btn-edit"
						   style="position:relative; color:#6366f1; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none; cursor:pointer;">
							<i class="fa fa-pen"></i>
							<span class="action-label-edit" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Edit</span>
						</a>
						<a href="{{ url('modules/users/disable/'.$user->id) }}" class="action-btn-delete" style="position:relative; color:#e11d48; font-size:1.2rem; display:inline-flex; align-items:center; text-decoration:none;">
							<i class="fa fa-trash"></i>
							<span class="action-label-delete" style="visibility:hidden;opacity:0;position:absolute;right:36px;left:auto;top:50%;transform:translateY(-50%);background:#222;color:#fff;padding:4px 14px;min-width:54px;border-radius:6px;font-size:0.98rem;white-space:nowrap;transition:opacity 0.18s;pointer-events:none;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.12);">Disable</span>
						</a>
					</td>
				</tr>
				@empty
				<tr><td colspan="7">No users found.</td></tr>
				@endforelse
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
</div>

<!-- USER MODAL -->
<div class="modal-backdrop" id="userModalBackdrop" onclick="closeUserModal()"></div>
<div class="modal-sheet" id="userModalSheet" aria-hidden="true">
	<div class="modal-card" role="dialog" aria-modal="true">
		<div class="modal-header">
			<div>
				<h2 class="modal-title" id="userModalTitle">Create User</h2>
				<div class="modal-subtitle" id="userModalSubtitle">Add a new system user and optionally assign a facility (Staff only).</div>
			</div>
			<button type="button" class="modal-close" onclick="closeUserModal()">Close</button>
		</div>

		<form id="userModalForm" method="POST" action="{{ route('users.store') }}">
			@csrf
			<input type="hidden" id="userModalMethod" name="_method" value="">

			<div class="form-grid">
				<div class="form-field">
					<label for="um_full_name">Full Name *</label>
					<input id="um_full_name" name="full_name" type="text" required>
				</div>
				<div class="form-field">
					<label for="um_email">Email *</label>
					<input id="um_email" name="email" type="email" required>
				</div>

				<div class="form-field">
					<label for="um_username">Username</label>
					<input id="um_username" name="username" type="text">
				</div>
				<div class="form-field">
					<label for="um_role">Role *</label>
					<select id="um_role" name="role" required onchange="toggleUserModalFacility()">
						<option value="">Select Role</option>
						<option value="admin">Admin</option>
						<option value="staff">Staff</option>
						<option value="energy_officer">Energy Officer</option>
					</select>
				</div>

				<div class="form-field" id="um_facility_wrap" style="display:block;">
					<label for="um_facility_id">Assigned Facility (used for Staff users)</label>
					<select id="um_facility_id" name="facility_id">
						<option value="">-- No Facility Assigned --</option>
						@foreach(($facilities ?? []) as $facility)
							<option value="{{ $facility->id }}">{{ $facility->name }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-field">
					<label for="um_status">Status *</label>
					<select id="um_status" name="status" required>
						<option value="active">Active</option>
						<option value="inactive">Inactive</option>
					</select>
				</div>

				<div class="form-field">
					<label for="um_department">Department</label>
					<input id="um_department" name="department" type="text">
				</div>
				<div class="form-field">
					<label for="um_contact_number">Contact Number</label>
					<input id="um_contact_number" name="contact_number" type="text">
				</div>
			</div>

			<div id="um_password_block" style="margin-top:14px;">
				<div class="form-grid">
					<div class="form-field">
						<label for="um_password">Password *</label>
						<input id="um_password" name="password" type="password">
					</div>
					<div class="form-field">
						<label for="um_password_confirmation">Confirm Password *</label>
						<input id="um_password_confirmation" name="password_confirmation" type="password">
					</div>
				</div>
				<div style="margin-top:6px;color:#666;font-size:0.85rem;">
					Password is required only when creating a new user.
				</div>
			</div>

			<div class="form-actions">
				<button type="button" class="btn-soft btn-cancel" onclick="closeUserModal()">Cancel</button>
				<button type="submit" class="btn-soft btn-save" id="userModalSubmitBtn">Create User</button>
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
	// Keep the field visible; clear only if non-staff
	const role = (document.getElementById('um_role').value || '').toLowerCase();
	if (role !== 'staff') {
		// Optional: clear value when not staff
		// document.getElementById('um_facility_id').value = '';
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
	form.action = "{{ route('users.store') }}";
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
	form.action = "{{ url('modules/users') }}/" + userId;
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
@endsection
