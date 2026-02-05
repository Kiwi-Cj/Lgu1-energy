@extends('layouts.qc-admin')
@section('title', 'Users')
@section('content')
@php
	$user = auth()->user();
	$role = strtolower($user?->role ?? '');
@endphp


@if($role !== 'super admin')
	<div style="max-width:600px;margin:60px auto 0 auto;padding:32px 24px;background:#fff0f3;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);text-align:center;">
		<h2 style="color:#e11d48;font-size:2rem;font-weight:800;margin-bottom:12px;">Restricted Access</h2>
		<div style="font-size:1.2rem;color:#b91c1c;margin-bottom:18px;">This page is for <b>Super Admin</b> only.</div>
		<a href="/modules/dashboard/index" style="display:inline-block;margin-top:18px;padding:10px 24px;background:#3762c8;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Go to Dashboard</a>
	</div>
@else
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
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;">{{ $totalUsers ?? '-' }}</div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#ecfdf5;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(34,197,94,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#16a34a;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🟢</span> Active Users</div>
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;">{{ $activeUsers ?? '-' }}</div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#f5f3ff;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(139,92,246,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#8b5cf6;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🔐</span> Roles</div>
			<div style="display:flex;flex-wrap:wrap;gap:8px 10px;margin-top:14px;">
				@php
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
				@endphp
				@foreach($rolesCollection as $roleLabel => $count)
					<span style="background:#ede9fe;padding:4px 14px;border-radius:999px;font-size:1rem;font-weight:600;color:#7c3aed;box-shadow:0 0 0 1px rgba(124,58,237,0.18);display:inline-flex;align-items:center;gap:6px;">
						<span>{{ $roleLabel }}</span>
						<span style="min-width:22px;height:22px;border-radius:999px;background:#7c3aed;color:#fdfbff;font-size:0.85rem;display:inline-flex;align-items:center;justify-content:center;">{{ $count }}</span>
					</span>
				@endforeach
			</div>
		</div>
		<div style="flex:1 1 220px;min-width:220px;max-width:24%;background:#fef2f2;padding:24px 18px;border-radius:18px;box-shadow:0 4px 16px rgba(225,29,72,0.10);margin-bottom:0;display:flex;flex-direction:column;align-items:flex-start;">
			<div style="font-size:1.1rem;font-weight:600;color:#e11d48;display:flex;align-items:center;gap:8px;"><span style='font-size:1.3rem;'>🚫</span> Inactive Users</div>
			<div style="font-size:2.5rem;font-weight:800;margin:12px 0 0 0;line-height:1;">{{ $inactiveUsers ?? '-' }}</div>
		</div>
	</div>
	<!-- 4️⃣ ACTION BUTTONS -->
	<div style="margin-bottom:24px;display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
		<button type="button" onclick="openUserModalCreate()" style="display:flex;align-items:center;gap:8px;padding:10px 22px;font-size:1.08rem;font-weight:600;background:#4f46e5;color:#fff;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(79,70,229,0.10);transition:background 0.18s,box-shadow 0.18s;cursor:pointer;outline:none;">
			<span style="font-size:1.25rem;display:flex;align-items:center;">➕</span> Add New User
		</button>
		<a href="{{ route('users.roles') }}"
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
					<th>Assigned Facilities<br><span style="font-weight:400;font-size:0.95em;color:#888;">(#)</span></th>
					<th>Status</th>
					<th style="text-align:center;vertical-align:middle;">Actions</th>
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
						@php $facilityCount = $user->facilities ? $user->facilities->count() : 0; @endphp
						<span style="font-weight:700;color:#6366f1;font-size:1.1em;">{{ $facilityCount }}</span>
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
					<td style="display:flex;gap:10px;align-items:center;justify-content:center;vertical-align:middle;">
						<a href="javascript:void(0)"
						   onclick="openUserModalView(this)"
						   data-user="{{ json_encode([
								'id' => $user->id,
								'full_name' => $user->full_name ?? $user->name ?? '',
								'email' => $user->email ?? '',
								'username' => $user->username ?? '',
								'role' => $user->role ?? '',
								'status' => $user->status ?? '',
								   'facilities' => $user->facilities ? $user->facilities->pluck('name')->toArray() : [],
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
								   'facility_ids' => $user->facilities ? $user->facilities->pluck('id')->toArray() : [],
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
	<!-- USER MODAL -->
	   <div class="modal-backdrop" id="userModalBackdrop" onclick="closeUserModal()"></div>
	   <div class="modal-sheet" id="userModalSheet" aria-hidden="true">
		   <div class="modal-card user-edit-modal-pro" role="dialog" aria-modal="true">
			   <button type="button" class="modal-close-pro" onclick="closeUserModal()" aria-label="Close">&times;</button>
			   <div class="uv-modal-header">
				   <h2 class="uv-modal-title" id="userModalTitle">Create User</h2>
				   <div class="uv-modal-subtitle" id="userModalSubtitle">Add a new system user and optionally assign a facility (Staff only).</div>
			   </div>
			   <form id="userModalForm" method="POST" action="{{ route('users.store') }}" class="uv-modal-form">
				   @csrf
				   <input type="hidden" id="userModalMethod" name="_method" value="">
				   <div class="uv-form-grid">
					   <div class="uv-form-field">
						   <label for="um_full_name">Full Name *</label>
						   <input id="um_full_name" name="full_name" type="text" required>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_email">Email *</label>
						   <input id="um_email" name="email" type="email" required>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_username">Username</label>
						   <input id="um_username" name="username" type="text">
					   </div>
					   <div class="uv-form-field">
						   <label for="um_role">Role *</label>
						   <select id="um_role" name="role" required onchange="toggleUserModalFacility()">
							   <option value="">Select Role</option>
							   <option value="admin">Admin</option>
							   <option value="staff">Staff</option>
							   <option value="energy_officer">Energy Officer</option>
						   </select>
					   </div>
					   <div class="uv-form-field" id="um_facility_wrap" style="display:none;">
						   <label>Assigned Facility <span style='font-weight:400;color:#888;'>(Staff only, optional, multiple allowed)</span></label>
						   <input type="text" id="facilitySearch" placeholder="Search facility..." style="margin-bottom:8px;width:100%;padding:7px 10px;border-radius:6px;border:1px solid #e0e7ef;">
						   <div class="facility-checkbox-grid facility-checkbox-scroll" id="facilityCheckboxList">
							   @foreach(($facilities ?? []) as $facility)
								   <label class="facility-checkbox-item">
									   <input type="checkbox" name="facility_id[]" value="{{ $facility->id }}">
									   <span>{{ $facility->name }}</span>
								   </label>
							   @endforeach
						   </div>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_status">Status *</label>
						   <select id="um_status" name="status" required>
							   <option value="active">Active</option>
							   <option value="inactive">Inactive</option>
						   </select>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_department">Department</label>
						   <input id="um_department" name="department" type="text">
					   </div>
					   <div class="uv-form-field">
						   <label for="um_contact_number">Contact Number</label>
						   <input id="um_contact_number" name="contact_number" type="text">
					   </div>
				   </div>
				   <div id="um_password_block" class="uv-password-block">
					   <div class="uv-form-grid uv-form-grid-2">
						   <div class="uv-form-field">
							   <label for="um_password">Password *</label>
							   <input id="um_password" name="password" type="password" autocomplete="off">
						   </div>
						   <div class="uv-form-field">
							   <label for="um_password_confirmation">Confirm Password *</label>
							   <input id="um_password_confirmation" name="password_confirmation" type="password" autocomplete="off">
						   </div>
					   </div>
					   <div class="uv-password-hint">Password is required only when creating a new user.</div>
				   </div>
				   <div class="uv-modal-actions">
					   <button type="button" class="uv-btn-cancel" onclick="closeUserModal()">Cancel</button>
					   <button type="submit" id="userModalSubmitBtn" class="uv-btn-submit">Create User</button>
				   </div>
			   </form>
		   </div>
	   </div>

	   <style>
	   .facility-checkbox-grid {
		   display: grid;
		   grid-template-columns: 1fr 1fr;
		   gap: 8px 18px;
		   margin: 8px 0 0 0;
	   }
	   .facility-checkbox-item {
		   display: flex;
		   align-items: center;
		   gap: 8px;
		   font-size: 1rem;
		   color: #312e81;
		   font-weight: 500;
		   background: #f8fafc;
		   border-radius: 6px;
		   padding: 6px 10px;
		   transition: background 0.18s;
		   cursor: pointer;
	   }
	   .facility-checkbox-item input[type="checkbox"] {
		   accent-color: #6366f1;
		   width: 18px;
		   height: 18px;
		   margin-right: 4px;
	   }
	   .facility-checkbox-item:hover {
		   background: #e0e7ef;
	   }
	   .facility-checkbox-scroll {
		   max-height: 220px;
		   overflow-y: auto;
		   border: 1px solid #e0e7ef;
		   background: #f8fafc;
		   padding-right: 4px;
	   }
	   .user-edit-modal-pro {
		   max-width: 720px;
		   width: 96vw;
		   margin: 40px auto;
		   background: #fff;
		   border-radius: 18px;
		   box-shadow: 0 8px 32px rgba(49,46,129,0.13), 0 2px 8px rgba(30,41,59,0.07);
		   padding: 0 0 18px 0;
		   position: relative;
		   animation: pop 0.22s cubic-bezier(.4,2,.6,1);
	   }
	   .modal-close-pro {
		   position: absolute;
		   top: 18px;
		   right: 22px;
		   background: none;
		   border: none;
		   font-size: 2.1rem;
		   color: #64748b;
		   cursor: pointer;
		   border-radius: 50%;
		   width: 38px;
		   height: 38px;
		   display: flex;
		   align-items: center;
		   justify-content: center;
		   transition: background 0.18s, color 0.18s;
	   }
	   .modal-close-pro:hover {
		   background: #e0e7ef;
		   color: #e11d48;
	   }
	   .uv-modal-header {
		   padding: 38px 38px 0 38px;
		   text-align: center;
	   }
	   .uv-modal-title {
		   font-size: 1.45rem;
		   font-weight: 800;
		   color: #312e81;
		   margin-bottom: 2px;
	   }
	   .uv-modal-subtitle {
		   font-size: 1.08rem;
		   color: #6366f1;
		   font-weight: 500;
		   margin-bottom: 18px;
	   }
	   .uv-modal-form {
		   padding: 18px 38px 0 38px;
	   }
	   .uv-form-grid {
		   display: grid;
		   grid-template-columns: 1fr;
		   gap: 16px;
	   }
	   @media (min-width: 600px) {
		   .uv-form-grid {
			   grid-template-columns: 1fr 1fr;
			   gap: 18px 24px;
		   }
		   .uv-form-field {
			   min-width: 0;
		   }
	   }
	   .uv-form-grid-2 {
		   grid-template-columns: 1fr 1fr;
	   }
	   .uv-form-field {
		   display: flex;
		   flex-direction: column;
		   gap: 4px;
	   }
	   .uv-form-field label {
		   font-weight: 700;
		   color: #312e81;
		   font-size: 1rem;
	   }
	   .uv-form-field input,
	   .uv-form-field select {
		   width: 100%;
		   padding: 10px 12px;
		   border-radius: 8px;
		   border: 1px solid #e0e7ef;
		   font-size: 1rem;
		   background: #f8fafc;
		   transition: border 0.18s, background 0.18s;
	   }
	   .uv-form-field input:focus,
	   .uv-form-field select:focus {
		   border-color: #6366f1;
		   background: #f0f6ff;
		   outline: none;
	   }
	   .uv-password-block {
		   margin-top: 14px;
	   }
	   .uv-password-hint {
		   margin-top: 6px;
		   color: #666;
		   font-size: 0.95rem;
	   }
	   .uv-modal-actions {
		   display: flex;
		   justify-content: flex-end;
		   gap: 12px;
		   margin-top: 22px;
	   }
	   .uv-btn-cancel {
		   padding: 10px 22px;
		   font-size: 1.08rem;
		   font-weight: 600;
		   background: #ede9fe;
		   color: #7c3aed;
		   border: none;
		   border-radius: 10px;
		   box-shadow: 0 2px 8px rgba(124,58,237,0.10);
		   transition: background 0.18s, box-shadow 0.18s, color 0.18s;
		   cursor: pointer;
		   outline: none;
	   }
	   .uv-btn-cancel:hover {
		   background: #c7d2fe;
		   color: #4f46e5;
	   }
	   .uv-btn-submit {
		   padding: 10px 22px;
		   font-size: 1.08rem;
		   font-weight: 600;
		   background: #4f46e5;
		   color: #fff;
		   border: none;
		   border-radius: 10px;
		   box-shadow: 0 2px 8px rgba(79,70,229,0.10);
		   transition: background 0.18s, box-shadow 0.18s;
		   cursor: pointer;
		   outline: none;
	   }
	   .uv-btn-submit:hover {
		   background: #312e81;
	   }
	   @keyframes pop {
		   from { transform: scale(0.95); opacity: 0; }
		   to   { transform: scale(1); opacity: 1; }
	   }
	</style>
	<script>
	// Facility search filter
	document.addEventListener('DOMContentLoaded', function() {
		var search = document.getElementById('facilitySearch');
		if (search) {
			search.addEventListener('input', function() {
				var filter = search.value.toLowerCase();
				document.querySelectorAll('#facilityCheckboxList .facility-checkbox-item').forEach(function(item) {
					var label = item.textContent.toLowerCase();
					item.style.display = label.includes(filter) ? '' : 'none';
				});
			});
		}
	});
	</script>


	   <!-- USER VIEW MODAL (modern/professional) -->
	   <div class="modal-backdrop" id="userViewModalBackdrop" onclick="closeUserViewModal()" style="display:none;"></div>
	   <div class="modal-sheet" id="userViewModalSheet" aria-hidden="true" style="display:none;">
		   <div class="modal-card user-view-modal-pro" role="dialog" aria-modal="true">
			   <button type="button" class="modal-close-pro" onclick="closeUserViewModal()" aria-label="Close">&times;</button>
			   <div class="uv-modal-header">
				   <h2 class="uv-modal-title" id="userViewModalTitle">User Details</h2>
				   <div class="uv-modal-subtitle" id="userViewModalSubtitle">View user information and assigned facility.</div>
			   </div>
			   <div class="uv-modal-content">
				   <div class="uv-modal-row"><span class="uv-label">Full Name</span><span class="uv-value" id="uv_full_name">-</span></div>
				   <div class="uv-modal-row"><span class="uv-label">Email</span><span class="uv-value" id="uv_email">-</span></div>
				   <div class="uv-modal-row"><span class="uv-label">Username</span><span class="uv-value" id="uv_username">-</span></div>
				   <div class="uv-modal-row"><span class="uv-label">Role</span><span class="uv-value" id="uv_role">-</span></div>
				   <div class="uv-modal-row"><span class="uv-label">Status</span><span class="uv-value" id="uv_status">-</span></div>
				   <div class="uv-modal-row" style="align-items:flex-start;">
					   <span class="uv-label">Assigned Facilities</span>
					   <span class="uv-value" id="uv_facilities" style="display:block;max-width:260px;">
						   <!-- Facilities will be rendered here as badges -->
					   </span>
				   </div>
				   <div class="uv-modal-row"><span class="uv-label">Department</span><span class="uv-value" id="uv_department">-</span></div>
				   <div class="uv-modal-row"><span class="uv-label">Contact Number</span><span class="uv-value" id="uv_contact_number">-</span></div>
			   </div>
			   <div class="uv-modal-actions">
				   <button type="button" class="uv-btn-close" onclick="closeUserViewModal()">Close</button>
			   </div>
		   </div>
	   </div>

	   <style>
	   .user-view-modal-pro {
		   max-width: 420px;
		   width: 100%;
		   margin: 60px auto;
		   background: #fff;
		   border-radius: 18px;
		   box-shadow: 0 8px 32px rgba(49,46,129,0.13), 0 2px 8px rgba(30,41,59,0.07);
		   padding: 0 0 18px 0;
		   position: relative;
		   animation: pop 0.22s cubic-bezier(.4,2,.6,1);
	   }
	   .modal-close-pro {
		   position: absolute;
		   top: 18px;
		   right: 22px;
		   background: none;
		   border: none;
		   font-size: 2.1rem;
		   color: #64748b;
		   cursor: pointer;
		   border-radius: 50%;
		   width: 38px;
		   height: 38px;
		   display: flex;
		   align-items: center;
		   justify-content: center;
		   transition: background 0.18s, color 0.18s;
	   }
	   .modal-close-pro:hover {
		   background: #e0e7ef;
		   color: #e11d48;
	   }
	   .uv-modal-header {
		   padding: 38px 38px 0 38px;
		   text-align: center;
	   }
	   .uv-modal-title {
		   font-size: 1.45rem;
		   font-weight: 800;
		   color: #312e81;
		   margin-bottom: 2px;
	   }
	   .uv-modal-subtitle {
		   font-size: 1.08rem;
		   color: #6366f1;
		   font-weight: 500;
		   margin-bottom: 18px;
	   }
	   .uv-modal-content {
		   padding: 0 38px;
		   display: flex;
		   flex-direction: column;
		   gap: 12px;
	   }
	   .uv-modal-row {
		   display: flex;
		   justify-content: space-between;
		   align-items: flex-start;
		   padding: 8px 0;
		   border-bottom: 1px solid #f1f5f9;
		   font-size: 1.05rem;
	   }
	   .uv-modal-row:last-child {
		   border-bottom: none;
	   }
	   .uv-label {
		   color: #64748b;
		   font-weight: 600;
		   min-width: 120px;
	   }
	   .uv-value {
		   color: #111827;
		   font-weight: 700;
		   word-break: break-all;
		   text-align: right;
	   }
	   #uv_facilities {
		   display: flex;
		   flex-wrap: wrap;
		   gap: 8px 10px;
		   max-height: 120px;
		   overflow-y: auto;
		   align-items: flex-start;
		   justify-content: flex-start;
		   margin-top: 2px;
	   }
	   .facility-badge {
		   background: #f3f4f6;
		   padding: 4px 14px;
		   border-radius: 999px;
		   font-size: 1rem;
		   color: #312e81;
		   font-weight: 600;
		   margin-bottom: 6px;
		   margin-right: 0px;
		   white-space: nowrap;
		   box-shadow: 0 0 0 1px rgba(49,46,129,0.06);
	   }
	   .uv-modal-actions {
		   display: flex;
		   justify-content: flex-end;
		   padding: 0 38px;
		   margin-top: 18px;
	   }
	   .uv-btn-close {
		   padding: 10px 22px;
		   font-size: 1.08rem;
		   font-weight: 600;
		   background: #ede9fe;
		   color: #7c3aed;
		   border: none;
		   border-radius: 10px;
		   box-shadow: 0 2px 8px rgba(124,58,237,0.10);
		   transition: background 0.18s, box-shadow 0.18s, color 0.18s;
		   cursor: pointer;
		   outline: none;
	   }
	   .uv-btn-close:hover {
		   background: #c7d2fe;
		   color: #4f46e5;
	   }
	   @keyframes pop {
		   from { transform: scale(0.95); opacity: 0; }
		   to   { transform: scale(1); opacity: 1; }
	   }
	   </style>

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
			document.querySelectorAll('input[name="facility_id[]"]').forEach(cb => { cb.checked = false; });
		}
	}

	function resetUserModalFields() {
		document.getElementById('um_full_name').value = '';
		document.getElementById('um_email').value = '';
		document.getElementById('um_username').value = '';
		document.getElementById('um_role').value = '';
		document.getElementById('um_status').value = 'active';
		document.querySelectorAll('input[name="facility_id[]"]').forEach(cb => { cb.checked = false; });
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
		const facilityIds = Array.isArray(payload.facility_ids) ? payload.facility_ids.map(String) : [];
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
        
		   // Set facility checkboxes for multiple facilities
		   document.querySelectorAll('input[name="facility_id[]"]').forEach(cb => {
			   cb.checked = facilityIds.includes(cb.value);
		   });

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
		   const facilitiesElem = document.getElementById('uv_facilities');
		   if (Array.isArray(payload.facilities) && payload.facilities.length) {
			   facilitiesElem.innerHTML = '';
			   payload.facilities.forEach(function(name) {
				   const badge = document.createElement('span');
				   badge.className = 'facility-badge';
				   badge.textContent = name;
				   facilitiesElem.appendChild(badge);
			   });
		   } else {
			   facilitiesElem.textContent = '-';
		   }
		   document.getElementById('uv_department').textContent = clean(payload.department);
		   document.getElementById('uv_contact_number').textContent = clean(payload.contact_number);

		openUserViewModal();
	}

	// ESC to close
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape') closeUserModal();
	});

	</script>
@endif
@endsection
