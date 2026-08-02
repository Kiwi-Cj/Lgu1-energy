@extends('layouts.qc-admin')
@section('title', 'Users')




@section('content')
<div class="report card users-shell" style="padding:32px 24px 32px 24px; background:#f8fafc; border-radius:18px; box-shadow:0 8px 32px rgba(37,99,235,0.09); margin-bottom:32px;">

@php
	$user = auth()->user();
	$role = \App\Support\RoleAccess::normalize($user?->role ?? '');
	$isSuperAdmin = $role === 'super_admin';
	$canAccessUsersPage = \App\Support\RoleAccess::can($user, 'access_users');
	$otpLoginEnabled = (bool) config('otp.enabled', true);
@endphp


@if(!$canAccessUsersPage)
	<div style="max-width:600px;margin:60px auto 0 auto;padding:32px 24px;background:#fff0f3;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);text-align:center;">
		<h2 style="color:#e11d48;font-size:2rem;font-weight:800;margin-bottom:12px;">Restricted Access</h2>
		<div style="font-size:1.2rem;color:#b91c1c;margin-bottom:18px;">Your role does not have permission to manage users.</div>
		<a href="/modules/dashboard/index" style="display:inline-block;margin-top:18px;padding:10px 24px;background:#3762c8;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">Go to Dashboard</a>
	</div>
@else
	<style>
		.users-shell {
			border: 1px solid #dbe6f5;
			background: linear-gradient(180deg, #f8fbff 0%, #f1f6fd 100%) !important;
		}
		.users-title {
			color: #1e1b4b !important;
			text-shadow: 0 1px 0 rgba(255, 255, 255, 0.85);
		}
		.users-stat-grid {
			display: grid !important;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 16px !important;
			margin-bottom: 28px !important;
		}
		.users-stat-card {
			--stat-accent: #4f46e5;
			--stat-soft: #eef2ff;
			position: relative;
			display: flex;
			flex-direction: column;
			align-items: stretch;
			min-width: 0 !important;
			max-width: none !important;
			min-height: 220px;
			padding: 22px !important;
			margin: 0 !important;
			background: #fff !important;
			border: 1px solid #dbe5f2 !important;
			border-radius: 18px !important;
			box-shadow: 0 8px 24px rgba(15, 23, 42, .06) !important;
			overflow: hidden;
			transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
		}
		.users-stat-card::before {
			content: '';
			position: absolute;
			inset: 0 0 auto;
			height: 4px;
			background: var(--stat-accent);
		}
		.users-stat-total { --stat-accent:#4f46e5; --stat-soft:#eef2ff; }
		.users-stat-active { --stat-accent:#16a34a; --stat-soft:#ecfdf5; }
		.users-stat-roles { --stat-accent:#7c3aed; --stat-soft:#f5f3ff; }
		.users-stat-inactive { --stat-accent:#e11d48; --stat-soft:#fff1f2; }
		.users-stat-heading {
			display: flex;
			align-items: center;
			gap: 11px;
			color: #334155;
			font-size: 1rem;
			font-weight: 800;
			line-height: 1.25;
		}
		.users-stat-icon {
			width: 40px;
			height: 40px;
			flex: 0 0 40px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 12px;
			background: var(--stat-soft);
			color: var(--stat-accent);
			font-size: 1.05rem;
		}
		.users-stat-value {
			margin-top: 22px;
			color: #0f172a;
			font-size: 2.65rem;
			font-weight: 850;
			line-height: 1;
			letter-spacing: -.04em;
		}
		.users-role-chips {
			display: flex;
			flex-wrap: wrap;
			align-content: flex-start;
			gap: 8px;
			margin-top: 18px;
		}
		.users-role-chip {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			min-height: 32px;
			padding: 5px 9px 5px 11px;
			border: 1px solid #ddd6fe;
			border-radius: 999px;
			background: #faf8ff;
			color: #6d28d9;
			font-size: .82rem;
			font-weight: 750;
		}
		.users-role-count {
			min-width: 21px;
			height: 21px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 999px;
			background: var(--stat-accent);
			color: #fff;
			font-size: .72rem;
			font-weight: 800;
		}
		.users-stat-card:hover {
			transform: translateY(-2px);
			border-color: color-mix(in srgb, var(--stat-accent) 30%, #dbe5f2) !important;
			box-shadow: 0 14px 30px rgba(15, 23, 42, .10) !important;
		}
		.users-actions {
			margin-bottom: 18px !important;
			padding: 4px 0;
		}
		.users-btn-primary,
		.users-btn-secondary {
			border: 1px solid transparent !important;
			transition: transform .15s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
		}
		.users-btn-primary:hover,
		.users-btn-secondary:hover {
			transform: translateY(-1px);
			box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18) !important;
		}
		.users-btn-secondary {
			background: #eef2ff !important;
			border-color: #c7d2fe !important;
		}
		.users-table-wrap {
			border: 1px solid #dbe6f5;
			border-radius: 12px;
			padding: 0 !important;
			background: #fff;
			height: 430px;
			overflow-y: auto;
			overflow-x: hidden;
			overscroll-behavior: contain;
			scrollbar-gutter: stable;
			-webkit-overflow-scrolling: touch;
		}
		.users-table {
			width: 100% !important;
			min-width: 100%;
			table-layout: fixed;
			border-collapse: separate;
			border-spacing: 0;
			border-radius: 0 !important;
			overflow: visible !important;
			margin: 0 !important;
		}
		.users-table thead tr {
			background: linear-gradient(135deg, #edf3ff 0%, #e5ecff 100%) !important;
		}
		.users-table th {
			position: sticky;
			top: 0;
			z-index: 10;
			height: 58px;
			background: linear-gradient(180deg, #eef4ff 0%, #e4edfc 100%);
			color: #263b62;
			font-size: .78rem;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .045em;
			line-height: 1.25;
			text-align: center;
			vertical-align: middle;
			white-space: normal;
			border-bottom: 1px solid #dbe6f5;
			box-shadow: 0 2px 5px rgba(37, 99, 235, .08);
		}
		.users-table th:first-child { border-top-left-radius: 11px; }
		.users-table th:last-child { border-top-right-radius: 11px; }
		.users-table td {
			color: #0f172a;
			border-top: 1px solid #edf2fb;
			vertical-align: middle;
		}
		.users-table th,
		.users-table td {
			padding: 12px 10px;
		}
		.users-table tbody tr { height: 70px; }
		.users-actions-cell {
			white-space: nowrap;
		}
		.users-table th:nth-child(1) { width: 12%; }
		.users-table th:nth-child(2) { width: 14%; }
		.users-table th:nth-child(3) { width: 20%; }
		.users-table th:nth-child(4) { width: 11%; }
		.users-table th:nth-child(5) { width: 11%; }
		.users-table th:nth-child(6) { width: 10%; }
		.users-table th:nth-child(7) { width: 22%; }
		.users-table tbody tr:hover {
			background: #f8fbff;
		}
		.users-filter-form {
			display: grid;
			grid-template-columns: minmax(220px, 1.5fr) repeat(3, minmax(145px, .7fr)) auto auto;
			gap: 10px;
			align-items: center;
			margin: 0 0 14px;
		}
		.users-filter-control {
			width: 100%;
			min-height: 42px;
			box-sizing: border-box;
			border: 1px solid #cbd5e1;
			border-radius: 10px;
			padding: 9px 11px;
			background: #fff;
			color: #0f172a;
			font: inherit;
		}
		.users-filter-btn,
		.users-filter-clear {
			min-height: 42px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 10px;
			padding: 9px 15px;
			font-weight: 800;
			text-decoration: none;
			white-space: nowrap;
		}
		.users-filter-btn { border: 1px solid #2563eb; background:#2563eb; color:#fff; cursor:pointer; }
		.users-filter-clear { border:1px solid #cbd5e1; background:#fff; color:#475569; }
		.user-identity-email { color:#0f172a; font-weight:700; word-break:break-word; }
		.user-identity-username,
		.user-login-meta { margin-top:3px; color:#64748b; font-size:.72rem; }
		.user-action-group { display:flex; justify-content:center; gap:6px; flex-wrap:wrap; }
		.user-action-btn {
			min-height: 32px;
			display:inline-flex;
			align-items:center;
			justify-content:center;
			gap:5px;
			border:1px solid #cbd5e1;
			border-radius:8px;
			padding:5px 8px;
			background:#fff;
			color:#475569;
			font-size:.7rem;
			font-weight:800;
			text-decoration:none;
			cursor:pointer;
		}
		.user-action-btn.primary { border-color:#bfdbfe; background:#eff6ff; color:#1d4ed8; }
		.user-action-btn.danger { border-color:#fecaca; background:#fff1f2; color:#be123c; }
		.user-action-btn.success { border-color:#bbf7d0; background:#f0fdf4; color:#15803d; }
		.user-action-btn:disabled { opacity:.55; cursor:not-allowed; }
		.user-status-form { margin:0; display:inline-flex; }
		.users-pagination { margin-top:14px; }
		.user-status-dialog {
			width:min(430px, calc(100% - 32px));
			border:0;
			border-radius:16px;
			padding:0;
			box-shadow:0 24px 70px rgba(15,23,42,.3);
		}
		.user-status-dialog::backdrop { background:rgba(15,23,42,.62); backdrop-filter:blur(4px); }
		.user-status-dialog-body { padding:22px; }
		.user-status-dialog-title { margin:0 0 8px; color:#0f172a; font-size:1.15rem; font-weight:900; }
		.user-status-dialog-message { margin:0; color:#64748b; line-height:1.55; }
		.user-status-dialog-actions { display:flex; justify-content:flex-end; gap:9px; padding:0 22px 22px; }
		.users-role-overview {
			padding: 16px 18px;
			background: #f8fbff;
			border: 1px solid #dde7f4;
			border-radius: 12px;
		}
		.users-role-overview ul li {
			margin-bottom: 6px;
			color: #334155;
		}
		.action-btn-view:hover .action-label-view,
		.action-btn-edit:hover .action-label-edit,
		.action-btn-delete:hover .action-label-delete {
			visibility: visible !important;
			opacity: 1 !important;
		}

		/* Simple modal (no bootstrap needed) */
		.modal-backdrop, .modal-overlay, .modal {
			position: fixed;
			inset: 0;
			background: rgba(15,23,42,0.6) !important;
			display: none;
			z-index: 9998;
			backdrop-filter: blur(4px) !important;
		}
		.modal-sheet, .modal-content {
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
		body.dark-mode .users-shell {
			background: linear-gradient(170deg, #0f172a 0%, #111827 100%) !important;
			border-color: #1f2c44 !important;
			box-shadow: 0 14px 30px rgba(2, 6, 23, 0.42) !important;
		}
		body.dark-mode .users-title {
			color: #e2e8f0 !important;
			text-shadow: none;
		}
		body.dark-mode .users-stat-card {
			background: #0f172a !important;
			color: #e2e8f0 !important;
			border-color: #253043 !important;
			box-shadow: 0 10px 24px rgba(2, 6, 23, 0.45) !important;
		}
		body.dark-mode .users-stat-card > div {
			color: #cbd5e1 !important;
		}
		body.dark-mode .users-stat-icon { background:#1e293b; color:var(--stat-accent) !important; }
		body.dark-mode .users-stat-value { color:#f8fafc !important; }
		body.dark-mode .users-role-chip {
			background:#17152c !important;
			border-color:#40366c !important;
			color:#ddd6fe !important;
		}
		body.dark-mode .users-role-count { color:#fff !important; }
		body.dark-mode .users-btn-secondary {
			background: #1e293b !important;
			color: #c4b5fd !important;
			border-color: #334155 !important;
		}
		body.dark-mode .users-btn-secondary:hover {
			background: #334155 !important;
			color: #e2e8f0 !important;
		}
		body.dark-mode .users-table-wrap,
		body.dark-mode .users-table {
			background: #0f172a !important;
			border-color: #223047 !important;
		}
		body.dark-mode .users-table thead tr {
			background: #172132 !important;
		}
		body.dark-mode .users-table th {
			background: linear-gradient(180deg, #1e293b 0%, #172033 100%) !important;
			color: #cbd5e1 !important;
			border-color: #253043 !important;
		}
		body.dark-mode .users-table td {
			color: #e2e8f0 !important;
			border-color: #1f2a3d !important;
		}
		body.dark-mode .users-table tbody tr:hover {
			background: #131f31 !important;
		}
		body.dark-mode .users-filter-control,
		body.dark-mode .users-filter-clear,
		body.dark-mode .user-action-btn { background:#111827; border-color:#334155; color:#cbd5e1; }
		body.dark-mode .user-identity-email { color:#e2e8f0; }
		body.dark-mode .user-identity-username,
		body.dark-mode .user-login-meta { color:#94a3b8; }
		body.dark-mode .user-status-dialog { background:#0f172a; color:#e2e8f0; }
		body.dark-mode .user-status-dialog-title { color:#f8fafc; }
		body.dark-mode .user-status-dialog-message { color:#94a3b8; }
		body.dark-mode .users-role-overview {
			background: #111a2a !important;
			border-color: #253043 !important;
		}
		body.dark-mode .users-role-overview ul li {
			color: #cbd5e1 !important;
		}
		body.dark-mode .user-edit-modal-pro,
		body.dark-mode .user-view-modal-pro,
		body.dark-mode .modal-card {
			background: #0f172a !important;
			border: 1px solid #253043 !important;
			box-shadow: 0 20px 40px rgba(2, 6, 23, 0.6) !important;
		}
		body.dark-mode .uv-modal-title,
		body.dark-mode .uv-value {
			color: #f1f5f9 !important;
		}
		body.dark-mode .uv-modal-subtitle,
		body.dark-mode .uv-label,
		body.dark-mode .uv-password-hint {
			color: #94a3b8 !important;
		}
		body.dark-mode .uv-modal-row {
			border-bottom-color: #1f2a3d !important;
		}
		body.dark-mode .uv-form-field label {
			color: #dbeafe !important;
		}
		body.dark-mode .uv-form-field input,
		body.dark-mode .uv-form-field select,
		body.dark-mode #facilitySearch {
			background: #111827 !important;
			border-color: #334155 !important;
			color: #e2e8f0 !important;
		}
		body.dark-mode .facility-checkbox-scroll,
		body.dark-mode .facility-checkbox-item {
			background: #111827 !important;
			border-color: #334155 !important;
			color: #e2e8f0 !important;
		}
		body.dark-mode .facility-checkbox-item:hover {
			background: #1f2937 !important;
		}
		body.dark-mode .facility-badge {
			background: #1e293b !important;
			color: #dbeafe !important;
			box-shadow: 0 0 0 1px #334155 !important;
		}
		@media (max-width: 1100px) {
			.users-stat-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
			.users-filter-form { grid-template-columns:repeat(2, minmax(0, 1fr)); }
		}
		@media (max-width: 680px) {
			.users-shell {
				padding: 20px 14px !important;
			}
			.users-title {
				font-size: 1.5rem !important;
				line-height: 1.15;
				margin-bottom: 14px !important;
			}
			.users-stat-grid {
				grid-template-columns: 1fr !important;
				gap: 12px !important;
				margin-bottom: 18px !important;
			}
			.users-stat-card {
				min-width: 0 !important;
				padding: 16px 14px !important;
				border-radius: 14px !important;
			}
			.users-stat-card > div:first-child {
				font-size: 0.95rem !important;
			}
			.users-stat-total > div:last-child,
			.users-stat-active > div:last-child,
			.users-stat-inactive > div:last-child {
				font-size: 2rem !important;
			}
			.users-actions {
				gap: 10px !important;
				flex-direction: column;
				align-items: stretch !important;
				margin-bottom: 16px !important;
			}
			.users-filter-form { grid-template-columns:1fr; }
			.users-btn-primary,
			.users-btn-secondary {
				width: 100%;
				justify-content: center;
				padding: 11px 14px !important;
				font-size: 0.96rem !important;
			}
			.users-table-wrap {
				padding: 10px !important;
				height: auto;
				max-height: none;
				overflow-x: auto !important;
				overflow-y: hidden !important;
				-webkit-overflow-scrolling: touch;
			}
			.users-table {
				min-width: 720px !important;
				width: max-content !important;
				table-layout: auto;
			}
			.users-table th,
			.users-table td {
				padding: 10px 8px !important;
				font-size: 0.82rem !important;
				line-height: 1.2;
				white-space: nowrap;
			}
			.users-table th {
				position: sticky;
				top: 0;
				z-index: 1;
			}
			.users-table td:nth-child(3) {
				max-width: 180px;
				white-space: normal;
				word-break: break-word;
			}
			.users-table td.users-actions-cell {
				gap: 8px !important;
			}
			.users-table td.users-actions-cell .action-label-view,
			.users-table td.users-actions-cell .action-label-edit,
			.users-table td.users-actions-cell .action-label-delete {
				display: none !important;
			}
			.users-table td.users-actions-cell a {
				font-size: 1rem !important;
			}
			.users-role-overview {
				margin-top: 1rem !important;
				padding: 14px !important;
			}
			.facility-checkbox-grid {
				grid-template-columns: 1fr !important;
				gap: 8px !important;
			}
			.uv-form-grid-2 {
				grid-template-columns: 1fr !important;
			}
			.modal-sheet,
			.modal-content {
				padding: 10px !important;
			}
			.modal-card,
			.user-edit-modal-pro,
			.user-view-modal-pro {
				width: 100% !important;
				margin: 8px auto !important;
				border-radius: 14px !important;
			}
			.modal-close-pro {
				top: 10px !important;
				right: 10px !important;
			}
			.uv-modal-header {
				padding: 22px 16px 0 16px !important;
			}
			.uv-modal-title {
				font-size: 1.2rem !important;
				padding-right: 28px;
			}
			.uv-modal-subtitle {
				font-size: 0.92rem !important;
				margin-bottom: 12px !important;
			}
			.uv-modal-form {
				padding: 14px 16px 0 16px !important;
			}
			.uv-modal-content {
				padding: 0 16px !important;
				gap: 8px !important;
			}
			.uv-modal-row {
				flex-direction: column;
				gap: 4px;
				padding: 10px 0;
			}
			.uv-label {
				min-width: 0;
			}
			.uv-value {
				text-align: left;
				width: 100%;
			}
			.uv-modal-actions {
				padding: 0 16px !important;
				flex-direction: column-reverse;
				align-items: stretch;
				gap: 10px !important;
			}
			.uv-btn-cancel,
			.uv-btn-submit,
			.uv-btn-close {
				width: 100%;
			}
		}
	</style>
	<h1 class="users-title" style="font-size:2.1rem;font-weight:800;color:#312e81;margin-bottom:10px;letter-spacing:-1px;">Users and Roles</h1>
	@if(session('success'))
		<div role="status" style="margin-bottom:16px;padding:12px 14px;border:1px solid #86efac;border-radius:10px;background:#f0fdf4;color:#166534;font-weight:700;">
			{{ session('success') }}
		</div>
	@endif
	@if(session('error'))
		<div role="alert" style="margin-bottom:16px;padding:12px 14px;border:1px solid #fecaca;border-radius:10px;background:#fff1f2;color:#9f1239;font-weight:700;">
			{{ session('error') }}
		</div>
	@endif
	<div class="users-stat-grid">
		<article class="users-stat-card users-stat-total">
			<div class="users-stat-heading"><span class="users-stat-icon"><i class="fa-solid fa-users"></i></span><span>Total Users</span></div>
			<div class="users-stat-value">{{ $totalUsers ?? '-' }}</div>
		</article>
		<article class="users-stat-card users-stat-active">
			<div class="users-stat-heading"><span class="users-stat-icon"><i class="fa-solid fa-user-check"></i></span><span>Active Users</span></div>
			<div class="users-stat-value">{{ $activeUsers ?? '-' }}</div>
		</article>
		<article class="users-stat-card users-stat-roles">
			<div class="users-stat-heading"><span class="users-stat-icon"><i class="fa-solid fa-shield-halved"></i></span><span>Roles</span></div>
			<div class="users-role-chips">
				@php
					$rolesCollection = collect($roleCounts ?? [])->mapWithKeys(function ($count, $role) use ($availableRoleOptions) {
						$label = $availableRoleOptions[$role] ?? str_replace('_', ' ', trim($role));
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
					<span class="users-role-chip">
						<span>{{ $roleLabel }}</span>
						<span class="users-role-count">{{ $count }}</span>
					</span>
				@endforeach
			</div>
		</article>
		<article class="users-stat-card users-stat-inactive">
			<div class="users-stat-heading"><span class="users-stat-icon"><i class="fa-solid fa-user-slash"></i></span><span>Inactive Users</span></div>
			<div class="users-stat-value">{{ $inactiveUsers ?? '-' }}</div>
		</article>
	</div>
	<!-- 4️⃣ ACTION BUTTONS -->
	<div class="users-actions" style="margin-bottom:24px;display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
		<button type="button" onclick="openUserModalCreate()" class="users-btn-primary" style="display:flex;align-items:center;gap:8px;padding:10px 22px;font-size:1.08rem;font-weight:600;background:#4f46e5;color:#fff;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(79,70,229,0.10);transition:background 0.18s,box-shadow 0.18s;cursor:pointer;outline:none;">
			<span style="font-size:1.25rem;display:flex;align-items:center;">➕</span> Add New User
		</button>
		<a href="{{ route('users.roles') }}" class="users-btn-secondary"
		   style="display:flex;align-items:center;gap:8px;padding:10px 22px;font-size:1.08rem;font-weight:600;background:#ede9fe;color:#7c3aed;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(124,58,237,0.10);text-decoration:none;transition:background 0.18s,box-shadow 0.18s,color 0.18s;cursor:pointer;outline:none;"
		   onmouseover="this.style.background='#c7d2fe';this.style.color='#4f46e5'"
		   onmouseout="this.style.background='#ede9fe';this.style.color='#7c3aed'">
			<span style="font-size:1.25rem;display:flex;align-items:center;">🧩</span> Manage Roles
		</a>
	</div>
	<form method="GET" action="{{ route('users.index') }}" class="users-filter-form" aria-label="Filter users">
		<input class="users-filter-control" type="search" name="q" value="{{ $search ?? '' }}" placeholder="Search name, email, username..." aria-label="Search users">
		<select class="users-filter-control" name="role" aria-label="Filter by role">
			<option value="">All roles</option>
			@foreach($availableRoleOptions as $roleKey => $roleLabel)
				<option value="{{ $roleKey }}" @selected(($selectedRole ?? '') === $roleKey)>{{ $roleLabel }}</option>
			@endforeach
		</select>
		<select class="users-filter-control" name="status" aria-label="Filter by status">
			<option value="">All statuses</option>
			<option value="active" @selected(($selectedStatus ?? '') === 'active')>Active</option>
			<option value="inactive" @selected(($selectedStatus ?? '') === 'inactive')>Inactive</option>
		</select>
		<select class="users-filter-control" name="sort" aria-label="Sort users">
			<option value="newest" @selected(($sort ?? '') === 'newest')>Newest accounts</option>
			<option value="name" @selected(($sort ?? '') === 'name')>Name A-Z</option>
			<option value="role" @selected(($sort ?? '') === 'role')>Role</option>
			<option value="last_login" @selected(($sort ?? '') === 'last_login')>Recent login</option>
		</select>
		<button class="users-filter-btn" type="submit"><i class="fa fa-filter"></i> Apply</button>
		<a class="users-filter-clear" href="{{ route('users.index') }}">Clear</a>
	</form>
	<!-- 3️⃣ USERS TABLE -->
	<div class="users-table-wrap" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(55,98,200,0.07);">
		<table class="table users-table" style="width:100%;background:#fff;text-align:center;">
			<thead style="background:#e9effc;">
				<tr>
					<th scope="col">Last Login</th>
					<th scope="col">Full Name</th>
					<th scope="col">Email / Username</th>
					<th scope="col">Role</th>
					<th scope="col">Assigned Facilities <span aria-label="count">(#)</span></th>
					<th scope="col">Status</th>
					<th scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
				@forelse($users as $user)
				<tr>
					<td data-label="Last Login">
						@if($user->last_login)
							<div>{{ \Illuminate\Support\Carbon::parse($user->last_login)->timezone(config('app.timezone'))->format('M d, Y') }}</div>
							<div class="user-login-meta">{{ \Illuminate\Support\Carbon::parse($user->last_login)->timezone(config('app.timezone'))->format('h:i A') }} &middot; {{ $otpLoginEnabled ? 'OTP enabled' : 'Password only' }}</div>
						@else
							<div>Never</div><div class="user-login-meta">No completed login</div>
						@endif
					</td>
					<td data-label="Full Name">{{ $user->full_name ?? $user->name ?? '-' }}</td>
					<td data-label="Email / Username">
						<div class="user-identity-email">{{ $user->email }}</div>
						<div class="user-identity-username">{{ $user->username ? '@'.$user->username : 'No username' }}</div>
					</td>
					<td data-label="Role">{{ $user->role }}</td>
					<td data-label="Assigned Facilities (#)">
						@php $facilityCount = (int) ($user->facilities_count ?? ($user->facilities?->count() ?? 0)); @endphp
						<span style="font-weight:700;color:#6366f1;font-size:1.1em;">{{ $facilityCount }}</span>
					</td>
					<td data-label="Status">
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
					@php
						$isCurrentAccount = (int) auth()->id() === (int) $user->id;
						$isActiveAccount = strtolower((string) $user->status) === 'active';
						$isProtectedLastSuperAdmin = $isActiveAccount
							&& \App\Support\RoleAccess::normalize($user->role) === 'super_admin'
							&& (int) ($activeSuperAdminCount ?? 0) <= 1;
						$viewPayload = [
							'id' => $user->id,
							'full_name' => $user->full_name ?? $user->name ?? '',
							'email' => $user->email ?? '',
							'username' => $user->username ?? '',
							'role' => $user->role ?? '',
							'status' => $user->status ?? '',
							'facilities' => $user->facilities?->pluck('name')->toArray() ?? [],
							'department' => $user->department ?? '',
							'contact_number' => $user->contact_number ?? '',
							'last_login' => $user->last_login ? \Illuminate\Support\Carbon::parse($user->last_login)->timezone(config('app.timezone'))->format('M d, Y h:i A') : 'Never',
							'security' => $otpLoginEnabled ? 'OTP enabled' : 'Password only',
						];
						$editPayload = array_merge($viewPayload, [
							'role' => \App\Support\RoleAccess::normalize($user->role),
							'status' => strtolower($user->status ?? 'active'),
							'facility_ids' => $user->facilities?->pluck('id')->toArray() ?? [],
						]);
					@endphp
					<td data-label="Actions" class="users-actions-cell">
						<div class="user-action-group">
							<button type="button" onclick="openUserModalView(this)" data-user="{{ json_encode($viewPayload, JSON_HEX_APOS | JSON_HEX_QUOT) }}" class="user-action-btn primary">
								<i class="fa fa-eye"></i> View
							</button>
							<button type="button" onclick="openUserModalEdit(this)" data-user-id="{{ $user->id }}" data-user="{{ json_encode($editPayload, JSON_HEX_APOS | JSON_HEX_QUOT) }}" class="user-action-btn">
								<i class="fa fa-pen"></i> Edit
							</button>
							@if($isActiveAccount)
								@if($isCurrentAccount || $isProtectedLastSuperAdmin)
									<button type="button" class="user-action-btn danger" disabled title="{{ $isCurrentAccount ? 'You cannot deactivate your own account.' : 'The last active Super Admin is protected.' }}">
										<i class="fa fa-shield-halved"></i> Protected
									</button>
								@else
									<form method="POST" action="{{ route('users.status.update', $user->id) }}" class="user-status-form" data-user-status-form data-user-name="{{ $user->full_name ?? $user->name ?? $user->email }}" data-next-status="inactive">
										@csrf @method('PATCH')
										<input type="hidden" name="status" value="inactive">
										<button type="submit" class="user-action-btn danger"><i class="fa fa-user-slash"></i> Deactivate</button>
									</form>
								@endif
							@else
								<form method="POST" action="{{ route('users.status.update', $user->id) }}" class="user-status-form" data-user-status-form data-user-name="{{ $user->full_name ?? $user->name ?? $user->email }}" data-next-status="active">
									@csrf @method('PATCH')
									<input type="hidden" name="status" value="active">
									<button type="submit" class="user-action-btn success"><i class="fa fa-user-check"></i> Reactivate</button>
								</form>
							@endif
						</div>
					</td>
				</tr>
				@empty
				<tr><td colspan="7">No users found.</td></tr>
				@endforelse
			</tbody>
		</table>
	</div>
	@if(method_exists($users, 'links'))
		<div class="users-pagination">{{ $users->links() }}</div>
	@endif

	<dialog id="userStatusDialog" class="user-status-dialog" aria-labelledby="userStatusDialogTitle">
		<div class="user-status-dialog-body">
			<h2 id="userStatusDialogTitle" class="user-status-dialog-title">Change user status?</h2>
			<p id="userStatusDialogMessage" class="user-status-dialog-message"></p>
		</div>
		<div class="user-status-dialog-actions">
			<button type="button" id="userStatusDialogCancel" class="user-action-btn">Cancel</button>
			<button type="button" id="userStatusDialogConfirm" class="user-action-btn danger">Confirm</button>
		</div>
	</dialog>
	<!-- 5️⃣ ROLES OVERVIEW (Optional) -->
	<div class="users-role-overview" style="margin-top:2.5rem;">
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
		   <div class="modal-card user-edit-modal-pro" role="dialog" aria-modal="true" aria-labelledby="userModalTitle" aria-describedby="userModalSubtitle">
			   <button type="button" class="modal-close-pro" onclick="closeUserModal()" aria-label="Close user form"><i class="fa-solid fa-xmark"></i></button>
			   <div class="uv-modal-header">
				   <div class="user-form-hero-icon"><i class="fa-solid fa-user-plus" id="userFormHeroIcon"></i></div>
				   <div class="user-form-hero-copy">
					   <h2 class="uv-modal-title" id="userModalTitle">Create User</h2>
					   <div class="uv-modal-subtitle" id="userModalSubtitle">Add a new account and configure its system access.</div>
				   </div>
			   </div>
			   <form id="userModalForm" method="POST" action="{{ route('users.store') }}" class="uv-modal-form">
				   @csrf
				   <input type="hidden" id="userModalMethod" name="_method" value="">
				   <input type="hidden" id="um_editing_user_id" name="editing_user_id" value="">
				   @if($errors->createUser->any())
					   <div id="createUserValidationErrors"
					        role="alert"
					        style="margin:0 38px 16px;padding:13px 15px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#991b1b;">
						   <div style="font-weight:800;margin-bottom:6px;">Please correct the following:</div>
						   <ul style="margin:0;padding-left:20px;">
							   @foreach($errors->createUser->all() as $message)
								   <li>{{ $message }}</li>
							   @endforeach
						   </ul>
					   </div>
				   @endif
				   @if($errors->editUser->any())
					   <div id="editUserValidationErrors"
					        role="alert"
					        style="margin:0 38px 16px;padding:13px 15px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#991b1b;">
						   <div style="font-weight:800;margin-bottom:6px;">Please correct the following:</div>
						   <ul style="margin:0;padding-left:20px;">
							   @foreach($errors->editUser->all() as $message)
								   <li>{{ $message }}</li>
							   @endforeach
						   </ul>
					   </div>
				   @endif
				   <div class="user-form-section-title"><i class="fa-solid fa-id-card"></i> Account &amp; Organization</div>
				   <div class="uv-form-grid">
					   <div class="uv-form-field">
						   <label for="um_full_name">Full Name *</label>
						   <input id="um_full_name" name="full_name" type="text" autocomplete="name" placeholder="Enter full name" required>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_email">Email *</label>
						   <input id="um_email" name="email" type="email" autocomplete="email" placeholder="name@example.com" required>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_username">Username</label>
						   <input id="um_username" name="username" type="text" autocomplete="username" placeholder="Optional username">
					   </div>
					   <div class="uv-form-field">
						   <label for="um_role">Role *</label>
						   <select id="um_role" name="role" required onchange="toggleUserModalFacility()">
							   <option value="">Select Role</option>
							   @foreach(($availableRoleOptions ?? collect()) as $roleSlug => $roleName)
								   @continue(!$isSuperAdmin && in_array($roleSlug, ['super_admin', 'admin'], true))
								   <option value="{{ $roleSlug }}">{{ $roleName }}</option>
							   @endforeach
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
					   <div class="uv-form-field" id="um_status_wrap">
						   <label for="um_status">Status *</label>
						   <select id="um_status" name="status" required>
							   <option value="active">Active</option>
							   <option value="inactive">Inactive</option>
						   </select>
					   </div>
					   <div class="uv-form-field">
						   <label for="um_department">Department</label>
						   <input id="um_department" name="department" type="text" autocomplete="organization" placeholder="Department or office">
					   </div>
					   <div class="uv-form-field">
						   <label for="um_contact_number">Contact Number</label>
						   <input id="um_contact_number" name="contact_number" type="tel" autocomplete="tel" inputmode="tel" placeholder="09XXXXXXXXX">
					   </div>
				   </div>
				   <div id="um_password_block" class="uv-password-block">
					   <div class="user-form-section-title"><i class="fa-solid fa-key"></i> Password &amp; Security</div>
					   <div class="uv-password-tools" id="um_password_tools">
						   <label class="uv-password-autogen">
							   <input type="checkbox" id="um_password_autogen_toggle">
							   <span>Auto-generate strong password</span>
						   </label>
						   <button type="button" class="uv-password-generate-btn" id="um_password_generate_btn" onclick="generateUserModalPassword()" style="display:none;">Generate</button>
					   </div>
					   <div class="uv-form-grid uv-form-grid-2">
						   <div class="uv-form-field">
							   <label for="um_password" id="um_password_label">Password *</label>
							   <div class="uv-password-input-wrap">
								   <input id="um_password" name="password" type="password" autocomplete="new-password">
								   <button type="button" class="uv-password-toggle-btn" id="um_password_toggle_btn" onclick="toggleUserModalPasswordVisibility('um_password', this)">Show</button>
							   </div>
						   </div>
						   <div class="uv-form-field">
							   <label for="um_password_confirmation" id="um_password_confirmation_label">Confirm Password *</label>
							   <div class="uv-password-input-wrap">
								   <input id="um_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
								   <button type="button" class="uv-password-toggle-btn" id="um_password_confirmation_toggle_btn" onclick="toggleUserModalPasswordVisibility('um_password_confirmation', this)">Show</button>
							   </div>
						   </div>
					   </div>
					   <div class="uv-password-hint" id="um_password_hint">Password is required when creating a new user.</div>
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
	   .uv-password-tools {
		   display: flex;
		   align-items: center;
		   justify-content: space-between;
		   gap: 10px;
		   margin-bottom: 10px;
		   flex-wrap: wrap;
	   }
	   .uv-password-autogen {
		   display: inline-flex;
		   align-items: center;
		   gap: 8px;
		   font-size: 0.95rem;
		   color: #334155;
		   font-weight: 600;
		   cursor: pointer;
	   }
	   .uv-password-autogen input[type="checkbox"] {
		   accent-color: #4f46e5;
		   width: 16px;
		   height: 16px;
	   }
	   .uv-password-generate-btn {
		   border: 1px solid #c7d2fe;
		   background: #eef2ff;
		   color: #4338ca;
		   border-radius: 8px;
		   padding: 7px 12px;
		   font-weight: 700;
		   cursor: pointer;
	   }
	   .uv-password-input-wrap {
		   position: relative;
		   display: flex;
		   align-items: center;
	   }
	   .uv-password-input-wrap input {
		   padding-right: 64px !important;
	   }
	   .uv-password-toggle-btn {
		   position: absolute;
		   right: 8px;
		   top: 50%;
		   transform: translateY(-50%);
		   border: 1px solid #dbeafe;
		   background: #eff6ff;
		   color: #1d4ed8;
		   border-radius: 7px;
		   padding: 4px 8px;
		   font-size: 0.78rem;
		   font-weight: 700;
		   cursor: pointer;
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
	   .user-edit-modal-pro {
		   width: min(780px, calc(100vw - 32px));
		   max-width: 780px;
		   max-height: calc(100vh - 40px);
		   margin: 20px auto;
		   padding: 0;
		   display: flex;
		   flex-direction: column;
		   overflow: hidden;
		   border: 1px solid #dbe5f2;
		   border-radius: 22px;
		   box-shadow: 0 28px 80px rgba(15,23,42,.30);
	   }
	   .user-edit-modal-pro .modal-close-pro {
		   z-index: 5;
		   top: 18px;
		   right: 18px;
		   width: 38px;
		   height: 38px;
		   border: 1px solid #dbe5f2;
		   border-radius: 11px;
		   background: rgba(255,255,255,.9);
		   color: #64748b;
		   font-size: 1rem;
	   }
	   .user-edit-modal-pro .modal-close-pro:hover { background:#fff1f2; border-color:#fecdd3; color:#e11d48; }
	   .user-edit-modal-pro .uv-modal-header {
		   flex: 0 0 auto;
		   display: flex;
		   align-items: center;
		   gap: 14px;
		   padding: 22px 68px 20px 24px;
		   text-align: left;
		   background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
		   border-bottom: 1px solid #dbe5f2;
	   }
	   .user-form-hero-icon {
		   width: 48px;
		   height: 48px;
		   flex: 0 0 48px;
		   display: inline-flex;
		   align-items: center;
		   justify-content: center;
		   border-radius: 14px;
		   background: linear-gradient(135deg,#2563eb,#6366f1);
		   color: #fff;
		   box-shadow: 0 9px 20px rgba(79,70,229,.20);
	   }
	   .user-form-hero-copy { min-width:0; }
	   .user-edit-modal-pro .uv-modal-title { margin:0; padding:0; color:#0f172a; font-size:1.25rem; font-weight:900; }
	   .user-edit-modal-pro .uv-modal-subtitle { margin:4px 0 0; color:#64748b; font-size:.84rem; font-weight:600; line-height:1.4; }
	   .user-edit-modal-pro .uv-modal-form {
		   flex: 1 1 auto;
		   min-height: 0;
		   padding: 20px 26px 0;
		   overflow-y: auto;
	   }
	   .user-edit-modal-pro #createUserValidationErrors,
	   .user-edit-modal-pro #editUserValidationErrors { margin:0 0 16px !important; font-size:.82rem; }
	   .user-form-section-title {
		   display: flex;
		   align-items: center;
		   gap: 7px;
		   margin: 0 0 12px;
		   color: #475569;
		   font-size: .73rem;
		   font-weight: 850;
		   letter-spacing: .07em;
		   text-transform: uppercase;
	   }
	   .user-form-section-title i { color:#2563eb; }
	   .user-edit-modal-pro .uv-form-grid { gap:14px 16px; }
	   .user-edit-modal-pro .uv-form-field { gap:6px; }
	   .user-edit-modal-pro .uv-form-field label { color:#334155; font-size:.78rem; font-weight:800; }
	   .user-edit-modal-pro .uv-form-field input,
	   .user-edit-modal-pro .uv-form-field select {
		   min-height: 45px;
		   padding: 10px 12px;
		   border: 1px solid #cbd5e1;
		   border-radius: 11px;
		   background: #fff;
		   color: #0f172a;
		   font-size: .88rem;
	   }
	   .user-edit-modal-pro .uv-form-field input:focus,
	   .user-edit-modal-pro .uv-form-field select:focus {
		   border-color: #6366f1;
		   background: #fff;
		   box-shadow: 0 0 0 3px rgba(99,102,241,.12);
	   }
	   .user-edit-modal-pro #um_facility_wrap { grid-column:1 / -1; }
	   .user-edit-modal-pro .uv-password-block { margin-top:22px; padding-top:18px; border-top:1px solid #e2e8f0; }
	   .user-edit-modal-pro .uv-password-tools {
		   min-height: 45px;
		   padding: 10px 12px;
		   border: 1px solid #dbeafe;
		   border-radius: 11px;
		   background: #f8fbff;
	   }
	   .user-edit-modal-pro .uv-password-autogen { color:#334155; font-size:.82rem; }
	   .user-edit-modal-pro .uv-password-hint { margin-top:8px; color:#64748b; font-size:.78rem; }
	   .user-edit-modal-pro .uv-modal-actions {
		   position: sticky;
		   z-index: 4;
		   bottom: 0;
		   margin: 20px -26px 0;
		   padding: 14px 26px;
		   border-top: 1px solid #e2e8f0;
		   background: rgba(255,255,255,.97);
		   backdrop-filter: blur(8px);
	   }
	   .user-edit-modal-pro .uv-btn-cancel,
	   .user-edit-modal-pro .uv-btn-submit { min-height:44px; padding:10px 19px; border-radius:11px; font-size:.86rem; font-weight:850; }
	   .user-edit-modal-pro .uv-btn-cancel { background:#f1f5f9; color:#475569; border:1px solid #dbe5f2; box-shadow:none; }
	   .user-edit-modal-pro .uv-btn-submit { background:#2563eb; box-shadow:0 7px 16px rgba(37,99,235,.20); }
	   .user-edit-modal-pro .uv-btn-submit:hover { background:#1d4ed8; }
	   body.dark-mode .user-edit-modal-pro .uv-modal-header { background:linear-gradient(135deg,#111827,#172033); border-color:#2a3850; }
	   body.dark-mode .user-edit-modal-pro .uv-modal-title { color:#f8fafc !important; }
	   body.dark-mode .user-edit-modal-pro .modal-close-pro { background:#111827; border-color:#334155; color:#cbd5e1; }
	   body.dark-mode .user-edit-modal-pro .user-form-section-title,
	   body.dark-mode .user-edit-modal-pro .uv-form-field label,
	   body.dark-mode .user-edit-modal-pro .uv-password-autogen { color:#cbd5e1 !important; }
	   body.dark-mode .user-edit-modal-pro .uv-password-block,
	   body.dark-mode .user-edit-modal-pro .uv-modal-actions { border-color:#2a3850; }
	   body.dark-mode .user-edit-modal-pro .uv-password-tools { background:#111827; border-color:#334155; }
	   body.dark-mode .user-edit-modal-pro .uv-modal-actions { background:rgba(15,23,42,.97); }
	   @media (max-width:600px) {
		   .user-edit-modal-pro { width:calc(100vw - 20px) !important; max-height:calc(100vh - 20px); margin:10px auto !important; border-radius:17px !important; }
		   .user-edit-modal-pro .uv-modal-header { padding:17px 55px 16px 16px !important; }
		   .user-form-hero-icon { width:42px; height:42px; flex-basis:42px; border-radius:12px; }
		   .user-edit-modal-pro .uv-modal-form { padding:16px 16px 0 !important; }
		   .user-edit-modal-pro .uv-modal-actions { margin:18px -16px 0; padding:12px 16px !important; flex-direction:row; }
		   .user-edit-modal-pro .uv-btn-cancel,
		   .user-edit-modal-pro .uv-btn-submit { width:auto; flex:1; }
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
		   <div class="modal-card user-view-modal-pro" role="dialog" aria-modal="true" aria-labelledby="uv_header_name" aria-describedby="uv_header_email">
			   <button type="button" class="user-view-close" onclick="closeUserViewModal()" aria-label="Close user details"><i class="fa-solid fa-xmark"></i></button>
			   <header class="user-view-hero">
				   <div class="user-view-eyebrow"><i class="fa-solid fa-address-card"></i> User Details</div>
				   <div class="user-view-identity">
					   <div class="user-view-avatar" id="uv_avatar_initial">U</div>
					   <div class="user-view-identity-copy">
						   <h2 id="uv_header_name">User</h2>
						   <p id="uv_header_email">-</p>
						   <div class="user-view-badges">
							   <span class="user-view-badge role" id="uv_header_role">-</span>
							   <span class="user-view-badge status" id="uv_header_status">-</span>
						   </div>
					   </div>
				   </div>
			   </header>
			   <div class="user-view-scroll">
				   <section class="user-view-section">
					   <h3>Account Information</h3>
					   <div class="user-view-grid">
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-user"></i></span><div><span class="uv-label">Full Name</span><span class="uv-value" id="uv_full_name">-</span></div></div>
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-at"></i></span><div><span class="uv-label">Username</span><span class="uv-value" id="uv_username">-</span></div></div>
						   <div class="user-view-field wide"><span class="user-view-field-icon"><i class="fa-solid fa-envelope"></i></span><div><span class="uv-label">Email Address</span><span class="uv-value" id="uv_email">-</span></div></div>
					   </div>
				   </section>
				   <section class="user-view-section">
					   <h3>Access &amp; Security</h3>
					   <div class="user-view-grid">
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-shield-halved"></i></span><div><span class="uv-label">Role</span><span class="uv-value" id="uv_role">-</span></div></div>
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-circle-check"></i></span><div><span class="uv-label">Status</span><span class="uv-value" id="uv_status">-</span></div></div>
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-clock"></i></span><div><span class="uv-label">Last Login</span><span class="uv-value" id="uv_last_login">-</span></div></div>
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-lock"></i></span><div><span class="uv-label">Login Security</span><span class="uv-value" id="uv_security">-</span></div></div>
					   </div>
				   </section>
				   <section class="user-view-section">
					   <h3>Organization</h3>
					   <div class="user-view-grid">
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-building"></i></span><div><span class="uv-label">Department</span><span class="uv-value" id="uv_department">-</span></div></div>
						   <div class="user-view-field"><span class="user-view-field-icon"><i class="fa-solid fa-phone"></i></span><div><span class="uv-label">Contact Number</span><span class="uv-value" id="uv_contact_number">-</span></div></div>
						   <div class="user-view-field wide facilities"><span class="user-view-field-icon"><i class="fa-solid fa-location-dot"></i></span><div><span class="uv-label">Assigned Facilities</span><span class="uv-value" id="uv_facilities">-</span></div></div>
					   </div>
				   </section>
			   </div>
			   <footer class="user-view-footer">
				   <button type="button" class="uv-btn-close" onclick="closeUserViewModal()"><i class="fa-solid fa-check"></i> Done</button>
			   </footer>
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
	   .user-view-modal-pro {
		   width: min(680px, calc(100vw - 32px));
		   max-width: 680px;
		   max-height: calc(100vh - 48px);
		   margin: 24px auto;
		   padding: 0;
		   display: flex;
		   flex-direction: column;
		   overflow: hidden;
		   border: 1px solid #dbe5f2;
		   border-radius: 22px;
		   box-shadow: 0 28px 80px rgba(15,23,42,.30);
	   }
	   .user-view-close {
		   position: absolute;
		   z-index: 5;
		   top: 18px;
		   right: 18px;
		   width: 38px;
		   height: 38px;
		   display: inline-flex;
		   align-items: center;
		   justify-content: center;
		   border: 1px solid #dbe5f2;
		   border-radius: 11px;
		   background: rgba(255,255,255,.9);
		   color: #64748b;
		   cursor: pointer;
	   }
	   .user-view-close:hover { background:#fff1f2; border-color:#fecdd3; color:#e11d48; }
	   .user-view-hero {
		   flex: 0 0 auto;
		   padding: 24px 70px 22px 26px;
		   background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
		   border-bottom: 1px solid #dbe5f2;
	   }
	   .user-view-eyebrow {
		   margin-bottom: 17px;
		   color: #4f46e5;
		   font-size: .72rem;
		   font-weight: 850;
		   letter-spacing: .08em;
		   text-transform: uppercase;
	   }
	   .user-view-identity { display:flex; align-items:center; gap:15px; min-width:0; }
	   .user-view-avatar {
		   width: 64px;
		   height: 64px;
		   flex: 0 0 64px;
		   display: inline-flex;
		   align-items: center;
		   justify-content: center;
		   border-radius: 18px;
		   background: linear-gradient(135deg,#2563eb,#6366f1);
		   color: #fff;
		   font-size: 1.55rem;
		   font-weight: 900;
		   box-shadow: 0 10px 22px rgba(79,70,229,.22);
	   }
	   .user-view-identity-copy { min-width:0; }
	   .user-view-identity-copy h2 { margin:0; color:#0f172a; font-size:1.35rem; font-weight:900; line-height:1.2; }
	   .user-view-identity-copy p { margin:4px 0 9px; color:#64748b; font-size:.86rem; word-break:break-word; }
	   .user-view-badges { display:flex; flex-wrap:wrap; gap:7px; }
	   .user-view-badge { display:inline-flex; align-items:center; min-height:26px; padding:4px 9px; border-radius:999px; font-size:.7rem; font-weight:850; }
	   .user-view-badge.role { background:#ede9fe; color:#6d28d9; }
	   .user-view-badge.status { background:#dcfce7; color:#166534; }
	   .user-view-badge.status.inactive { background:#fee2e2; color:#b91c1c; }
	   .user-view-scroll { flex:1 1 auto; min-height:0; overflow-y:auto; padding:22px 26px 8px; }
	   .user-view-section { margin-bottom:22px; }
	   .user-view-section h3 { margin:0 0 11px; color:#475569; font-size:.74rem; font-weight:850; letter-spacing:.07em; text-transform:uppercase; }
	   .user-view-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
	   .user-view-field {
		   min-width:0;
		   min-height:74px;
		   padding:13px;
		   display:flex;
		   align-items:flex-start;
		   gap:11px;
		   border:1px solid #e2e8f0;
		   border-radius:13px;
		   background:#f8fafc;
	   }
	   .user-view-field.wide { grid-column:1 / -1; }
	   .user-view-field > div { min-width:0; }
	   .user-view-field-icon {
		   width:34px;
		   height:34px;
		   flex:0 0 34px;
		   display:inline-flex;
		   align-items:center;
		   justify-content:center;
		   border-radius:10px;
		   background:#eaf1ff;
		   color:#2563eb;
		   font-size:.82rem;
	   }
	   .user-view-field .uv-label { display:block; min-width:0; margin-bottom:4px; color:#64748b; font-size:.7rem; font-weight:800; letter-spacing:.035em; text-transform:uppercase; }
	   .user-view-field .uv-value { display:block; width:auto; color:#0f172a; font-size:.86rem; font-weight:800; line-height:1.35; text-align:left; overflow-wrap:anywhere; word-break:normal; }
	   .user-view-field #uv_facilities { display:flex; max-width:none; max-height:110px; margin:0; gap:6px; overflow-y:auto; flex-wrap:wrap; }
	   .user-view-field .facility-badge { margin:0; padding:5px 9px; background:#eef2ff; color:#4338ca; font-size:.7rem; box-shadow:none; }
	   .user-view-footer {
		   flex:0 0 auto;
		   display:flex;
		   justify-content:flex-end;
		   padding:14px 26px;
		   border-top:1px solid #e2e8f0;
		   background:#fff;
	   }
	   .user-view-footer .uv-btn-close { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:#2563eb; color:#fff; font-size:.86rem; font-weight:800; box-shadow:0 6px 14px rgba(37,99,235,.18); }
	   .user-view-footer .uv-btn-close:hover { background:#1d4ed8; color:#fff; }
	   body.dark-mode .user-view-hero { background:linear-gradient(135deg,#111827,#172033); border-color:#2a3850; }
	   body.dark-mode .user-view-close { background:#111827; border-color:#334155; color:#cbd5e1; }
	   body.dark-mode .user-view-identity-copy h2,
	   body.dark-mode .user-view-field .uv-value { color:#f8fafc !important; }
	   body.dark-mode .user-view-field { background:#111827; border-color:#2a3850; }
	   body.dark-mode .user-view-field-icon { background:#1e293b; color:#93c5fd; }
	   body.dark-mode .user-view-footer { background:#0f172a; border-color:#2a3850; }
	   @media (max-width:600px) {
		   .user-view-modal-pro { width:calc(100vw - 20px) !important; max-height:calc(100vh - 20px); margin:10px auto !important; border-radius:17px !important; }
		   .user-view-hero { padding:20px 58px 18px 18px; }
		   .user-view-avatar { width:52px; height:52px; flex-basis:52px; border-radius:15px; font-size:1.2rem; }
		   .user-view-identity-copy h2 { font-size:1.1rem; }
		   .user-view-grid { grid-template-columns:1fr; }
		   .user-view-field.wide { grid-column:auto; }
		   .user-view-scroll { padding:18px 16px 4px; }
		   .user-view-footer { padding:12px 16px; }
		   .user-view-footer .uv-btn-close { width:100%; justify-content:center; }
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
		var closeButton = document.querySelector('#userViewModalSheet .user-view-close');
		if (closeButton) closeButton.focus();
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

	function generateUserModalStrongPassword(length) {
		var targetLength = Math.max(12, Number(length || 14));
		var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
		var lower = 'abcdefghijkmnopqrstuvwxyz';
		var number = '23456789';
		var symbol = '!@#$%^&*_-+=?';
		var all = upper + lower + number + symbol;

		function pick(str) {
			return str.charAt(Math.floor(Math.random() * str.length));
		}

		var chars = [
			pick(upper),
			pick(lower),
			pick(number),
			pick(symbol)
		];

		while (chars.length < targetLength) {
			chars.push(pick(all));
		}

		for (var i = chars.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * (i + 1));
			var tmp = chars[i];
			chars[i] = chars[j];
			chars[j] = tmp;
		}

		return chars.join('');
	}

	function resetUserModalPasswordUi() {
		var autoToggle = document.getElementById('um_password_autogen_toggle');
		var generateBtn = document.getElementById('um_password_generate_btn');
		var passwordInput = document.getElementById('um_password');
		var confirmInput = document.getElementById('um_password_confirmation');
		var toggleBtns = [
			document.getElementById('um_password_toggle_btn'),
			document.getElementById('um_password_confirmation_toggle_btn')
		];

		if (autoToggle) autoToggle.checked = false;
		if (generateBtn) generateBtn.style.display = 'none';
		if (passwordInput) passwordInput.type = 'password';
		if (confirmInput) confirmInput.type = 'password';
		toggleBtns.forEach(function(btn) {
			if (btn) btn.textContent = 'Show';
		});
	}

	function generateUserModalPassword() {
		var passwordInput = document.getElementById('um_password');
		var confirmInput = document.getElementById('um_password_confirmation');
		if (!passwordInput || !confirmInput) return;

		var generated = generateUserModalStrongPassword(14);
		passwordInput.value = generated;
		confirmInput.value = generated;
	}

	function handleUserModalAutoPasswordToggle() {
		var autoToggle = document.getElementById('um_password_autogen_toggle');
		var generateBtn = document.getElementById('um_password_generate_btn');
		var passwordInput = document.getElementById('um_password');
		var confirmInput = document.getElementById('um_password_confirmation');
		if (!autoToggle || !generateBtn || !passwordInput || !confirmInput) return;

		generateBtn.style.display = autoToggle.checked ? 'inline-flex' : 'none';

		if (autoToggle.checked) {
			generateUserModalPassword();
		} else {
			passwordInput.value = '';
			confirmInput.value = '';
		}
	}

	function toggleUserModalPasswordVisibility(inputId, btn) {
		var input = document.getElementById(inputId);
		if (!input || !btn) return;

		var show = input.type === 'password';
		input.type = show ? 'text' : 'password';
		btn.textContent = show ? 'Hide' : 'Show';
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
		document.getElementById('um_editing_user_id').value = '';
		document.getElementById('um_status_wrap').style.display = 'flex';
		resetUserModalPasswordUi();
		toggleUserModalFacility();
	}

	function openUserModalCreate() {
		resetUserModalFields();
		document.getElementById('userModalTitle').textContent = 'Create User';
		document.getElementById('userModalSubtitle').textContent = 'Add a new account and configure its system access.';
		document.getElementById('userModalSubmitBtn').textContent = 'Create User';
		document.getElementById('userFormHeroIcon').className = 'fa-solid fa-user-plus';

		const form = document.getElementById('userModalForm');
		form.action = "{{ route('users.store') }}";
		document.getElementById('userModalMethod').value = '';
		document.getElementById('um_editing_user_id').value = '';

		// Password required on create
		document.getElementById('um_password_block').style.display = 'block';
		document.getElementById('um_password_tools').style.display = 'flex';
		document.getElementById('um_password').required = true;
		document.getElementById('um_password_confirmation').required = true;
		document.getElementById('um_password_label').textContent = 'Password *';
		document.getElementById('um_password_confirmation_label').textContent = 'Confirm Password *';
		document.getElementById('um_password_hint').textContent = 'Password is required when creating a new user.';

		openUserModal();
	}

	function openUserModalEdit(el) {
		resetUserModalFields();
		document.getElementById('userModalTitle').textContent = 'Edit User';
		document.getElementById('userModalSubtitle').textContent = 'Update account information, role, and facility access.';
		document.getElementById('userModalSubmitBtn').textContent = 'Update User';
		document.getElementById('userFormHeroIcon').className = 'fa-solid fa-user-pen';

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
		document.getElementById('um_editing_user_id').value = userId;
		document.getElementById('um_status_wrap').style.display = 'none';

		// Password is optional on edit
		document.getElementById('um_password_block').style.display = 'block';
		document.getElementById('um_password_tools').style.display = 'none';
		document.getElementById('um_password').required = false;
		document.getElementById('um_password_confirmation').required = false;
		document.getElementById('um_password_label').textContent = 'Password (optional)';
		document.getElementById('um_password_confirmation_label').textContent = 'Confirm Password (optional)';
		document.getElementById('um_password_hint').textContent = 'Leave password fields blank if you do not want to change the current password.';

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
		const fullName = clean(payload.full_name);
		const email = clean(payload.email);
		const roleLabel = clean(payload.role) === '-' ? '-' : String(payload.role).replace(/[_-]+/g, ' ').replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
		const statusLabel = clean(payload.status) === '-' ? '-' : String(payload.status).replace(/\b\w/g, function (letter) { return letter.toUpperCase(); });
		const isInactive = String(payload.status || '').toLowerCase() !== 'active';

		   document.getElementById('uv_header_name').textContent = fullName;
		   document.getElementById('uv_header_email').textContent = email;
		   document.getElementById('uv_header_role').textContent = roleLabel;
		   document.getElementById('uv_header_status').textContent = statusLabel;
		   document.getElementById('uv_header_status').classList.toggle('inactive', isInactive);
		   document.getElementById('uv_avatar_initial').textContent = fullName === '-' ? 'U' : fullName.trim().charAt(0).toUpperCase();

		   document.getElementById('uv_full_name').textContent = fullName;
		   document.getElementById('uv_email').textContent = clean(payload.email);
		   document.getElementById('uv_username').textContent = clean(payload.username);
		   document.getElementById('uv_role').textContent = roleLabel;
		   document.getElementById('uv_status').textContent = statusLabel;
		   document.getElementById('uv_last_login').textContent = clean(payload.last_login);
		   document.getElementById('uv_security').textContent = clean(payload.security);
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
		if (e.key === 'Escape') {
			closeUserModal();
			closeUserViewModal();
		}
	});

	document.addEventListener('DOMContentLoaded', function () {
		var statusDialog = document.getElementById('userStatusDialog');
		var statusDialogTitle = document.getElementById('userStatusDialogTitle');
		var statusDialogMessage = document.getElementById('userStatusDialogMessage');
		var statusDialogConfirm = document.getElementById('userStatusDialogConfirm');
		var statusDialogCancel = document.getElementById('userStatusDialogCancel');
		var pendingStatusForm = null;

		document.querySelectorAll('[data-user-status-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (form.dataset.confirmed === 'true' || !statusDialog || typeof statusDialog.showModal !== 'function') return;

				event.preventDefault();
				pendingStatusForm = form;
				var isActivating = form.dataset.nextStatus === 'active';
				var userName = form.dataset.userName || 'this user';
				statusDialogTitle.textContent = isActivating ? 'Reactivate user?' : 'Deactivate user?';
				statusDialogMessage.textContent = isActivating
					? userName + ' will be able to sign in and access assigned facilities again.'
					: userName + ' will be blocked from signing in until the account is reactivated.';
				statusDialogConfirm.textContent = isActivating ? 'Reactivate' : 'Deactivate';
				statusDialogConfirm.classList.toggle('success', isActivating);
				statusDialogConfirm.classList.toggle('danger', !isActivating);
				statusDialog.showModal();
			});
		});

		if (statusDialogCancel) {
			statusDialogCancel.addEventListener('click', function () {
				pendingStatusForm = null;
				statusDialog.close();
			});
		}

		if (statusDialogConfirm) {
			statusDialogConfirm.addEventListener('click', function () {
				if (!pendingStatusForm) return;
				var form = pendingStatusForm;
				pendingStatusForm = null;
				form.dataset.confirmed = 'true';
				statusDialog.close();
				form.requestSubmit();
			});
		}

		if (statusDialog) {
			statusDialog.addEventListener('cancel', function () { pendingStatusForm = null; });
			statusDialog.addEventListener('click', function (event) {
				if (event.target === statusDialog) {
					pendingStatusForm = null;
					statusDialog.close();
				}
			});
		}

		var autoToggle = document.getElementById('um_password_autogen_toggle');
		if (autoToggle) {
			autoToggle.addEventListener('change', handleUserModalAutoPasswordToggle);
		}

		@if($errors->createUser->any())
			openUserModalCreate();

			document.getElementById('um_full_name').value = @json(old('full_name', ''));
			document.getElementById('um_email').value = @json(old('email', ''));
			document.getElementById('um_username').value = @json(old('username', ''));
			document.getElementById('um_role').value = @json(old('role', ''));
			document.getElementById('um_status').value = @json(old('status', 'active'));
			document.getElementById('um_department').value = @json(old('department', ''));
			document.getElementById('um_contact_number').value = @json(old('contact_number', ''));

			var selectedFacilityIds = @json(
				is_array(old('facility_id'))
					? array_map('strval', old('facility_id'))
					: []
			);
			document.querySelectorAll('input[name="facility_id[]"]').forEach(function (checkbox) {
				checkbox.checked = selectedFacilityIds.includes(checkbox.value);
			});
			toggleUserModalFacility();
		@endif

		@if($errors->editUser->any() && old('editing_user_id'))
			var failedEditTrigger = document.createElement('button');
			failedEditTrigger.setAttribute('data-user-id', @json((string) old('editing_user_id')));
			failedEditTrigger.setAttribute('data-user', JSON.stringify({
				id: @json((string) old('editing_user_id')),
				full_name: @json(old('full_name', '')),
				email: @json(old('email', '')),
				username: @json(old('username', '')),
				role: @json(old('role', '')),
				status: @json(old('status', 'active')),
				facility_ids: @json(
					is_array(old('facility_id'))
						? array_map('strval', old('facility_id'))
						: []
				),
				department: @json(old('department', '')),
				contact_number: @json(old('contact_number', ''))
			}));
			openUserModalEdit(failedEditTrigger);
		@endif
	});

	</script>
@endif
</div>
@endsection
