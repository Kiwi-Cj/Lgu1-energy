<div style="padding:18px 0;">
    <h4 style="font-size:1.15rem;font-weight:700;margin-bottom:18px;">System Role & Permissions</h4>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px 14px;align-items:center;">
        <div><i class="fa fa-eye" style="color:#3762c8;margin-right:8px;"></i>View Energy Records</div>
        <div><i class="fa fa-plus-circle" style="color:#3762c8;margin-right:8px;"></i>{{ auth()->user()->can_create_actions ? '✔' : '✖' }} Create Energy Actions</div>
        <div><i class="fa fa-check-circle" style="color:#3762c8;margin-right:8px;"></i>{{ auth()->user()->can_approve_actions ? '✔' : '✖' }} Approve Actions</div>
        <!-- Billing feature removed -->
        <div><i class="fa fa-cogs" style="color:#3762c8;margin-right:8px;"></i>{{ auth()->user()->is_admin ? '✔' : '✖' }} Admin Settings</div>
    </div>
    <small style="color:#6b7280;">Permissions are read-only</small>
</div>