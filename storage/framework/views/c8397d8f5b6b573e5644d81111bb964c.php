<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php $__env->startSection('content'); ?>

<div style="max-width:900px;margin:40px auto 60px;">
    
    <div style="display:flex;align-items:center;gap:28px;margin-bottom:36px;background:#f8fafc;border-radius:18px;padding:32px 36px 28px 36px;box-shadow:0 2px 16px #3762c81a;">
        <img src="<?php echo e(auth()->user()->profile_photo_url ?? '/img/default-avatar.png'); ?>"
             style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #e0e7ff;box-shadow:0 2px 12px #3762c822;">
        <div style="flex:1;">
            <h1 style="margin:0;font-size:2.3rem;font-weight:800;letter-spacing:1px;color:#222;">
                <?php echo e(auth()->user()->full_name ?? auth()->user()->name); ?>

            </h1>
            <div style="margin-top:10px;display:flex;align-items:center;gap:14px;">
                <span style="background:#e0e7ff;color:#3762c8;padding:6px 18px;border-radius:14px;font-size:1.08rem;font-weight:600;display:inline-block;"><?php echo e(ucfirst(auth()->user()->role)); ?></span>
                <span style="display:inline-block;width:13px;height:13px;border-radius:50%;background:<?php echo e(auth()->user()->status === 'active' ? '#22c55e' : '#ef4444'); ?>;border:2px solid #e0e7ff;"></span>
                <span style="font-size:1.08rem;color:#222;"><?php echo e(ucfirst(auth()->user()->status)); ?></span>
            </div>
        </div>
    </div>

    
    <section style="margin-bottom:40px;">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">Basic Information</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;display:grid;grid-template-columns:repeat(3,1fr);gap:24px 18px;">
            <div><label style="color:#64748b;font-size:0.98rem;">Employee/User ID</label><div style="font-size:1.08rem;font-weight:600;"><?php echo e(auth()->user()->id); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Username</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->username); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Position / Role</label><div style="font-size:1.08rem;"><?php echo e(ucfirst(auth()->user()->role)); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Department / Office</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->department ?? '-'); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Assigned Facility</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->facility?->name ?? 'None'); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Email Address</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->email); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Contact Number</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->contact_number ?? '-'); ?></div></div>
        </div>
    </section>

    
    <section style="margin-bottom:40px;">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">Account & Security</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;display:grid;grid-template-columns:repeat(3,1fr);gap:24px 18px;align-items:center;">
            <div><label style="color:#64748b;font-size:0.98rem;">Status</label><div style="font-size:1.08rem;"><?php echo e(ucfirst(auth()->user()->status)); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">Last Login</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->last_login_at ?? 'N/A'); ?></div></div>
            <div><label style="color:#64748b;font-size:0.98rem;">2FA / OTP</label><div style="font-size:1.08rem;"><?php echo e(auth()->user()->otp_enabled ? 'Enabled' : 'Disabled'); ?></div></div>
            <div style="grid-column:1/-1;text-align:right;">

            </div>
        </div>
    </section>

    
    <section style="margin-bottom:40px;">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">System Role & Permissions</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;display:grid;grid-template-columns:repeat(2,1fr);gap:18px 14px;align-items:center;">
            <div><i class="fa fa-eye" style="color:#3762c8;margin-right:8px;"></i>View Energy Records</div>
            <div><i class="fa fa-plus-circle" style="color:#3762c8;margin-right:8px;"></i><?php echo e(auth()->user()->can_create_actions ? '✔' : '✖'); ?> Create Energy Actions</div>
            <div><i class="fa fa-check-circle" style="color:#3762c8;margin-right:8px;"></i><?php echo e(auth()->user()->can_approve_actions ? '✔' : '✖'); ?> Approve Actions</div>
                <!-- Billing feature removed -->
            <div><i class="fa fa-cogs" style="color:#3762c8;margin-right:8px;"></i><?php echo e(auth()->user()->is_admin ? '✔' : '✖'); ?> Admin Settings</div>
        </div>
        <small style="color:#6b7280;">Permissions are read-only</small>
    </section>

    
    <section style="margin-bottom:40px;">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">Notification Preferences</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;display:grid;grid-template-columns:repeat(3,1fr);gap:18px 12px;align-items:center;">
            <label style="font-size:1.05rem;"><input type="checkbox" checked disabled style="margin-right:7px;"> Energy Alerts</label>
            <label style="font-size:1.05rem;"><input type="checkbox" checked disabled style="margin-right:7px;"> Incident Updates</label>
                <!-- Billing Notifications removed -->
            <label style="font-size:1.05rem;"><input type="checkbox" checked disabled style="margin-right:7px;"> Due Date Reminders</label>
            <label style="font-size:1.05rem;"><input type="checkbox" checked disabled style="margin-right:7px;"> Email</label>
            <label style="font-size:1.05rem;"><input type="checkbox" disabled style="margin-right:7px;"> SMS</label>
        </div>
    </section>

    
    <section style="margin-bottom:40px;">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">Assigned Responsibilities</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;display:grid;grid-template-columns:repeat(4,1fr);gap:24px 18px;align-items:center;">
            <div style="text-align:center;">
                <div style="font-size:1.35rem;font-weight:700;color:#3762c8;"><?php echo e(auth()->user()->facility?->name ?? 'None'); ?></div>
                <div style="color:#64748b;font-size:0.98rem;">Facilities</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.35rem;font-weight:700;color:#3762c8;"><?php echo e(auth()->user()->assigned_equipment_count ?? 0); ?></div>
                <div style="color:#64748b;font-size:0.98rem;">Equipment</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.35rem;font-weight:700;color:#3762c8;"><?php echo e(auth()->user()->active_actions_count ?? 0); ?></div>
                <div style="color:#64748b;font-size:0.98rem;">Active Actions</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.35rem;font-weight:700;color:#3762c8;"><?php echo e(auth()->user()->open_incidents_count ?? 0); ?></div>
                <div style="color:#64748b;font-size:0.98rem;">Open Incidents</div>
            </div>
        </div>
    </section>

    
    <section>
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:18px;letter-spacing:0.5px;color:#1e293b;">Audit & System Info</h3>
        <div style="background:#fff;border-radius:14px;box-shadow:0 2px 8px #3762c80d;padding:28px 24px 18px 24px;color:#4b5563;display:grid;grid-template-columns:repeat(3,1fr);gap:18px 14px;align-items:center;">
            <div><span style="color:#64748b;font-size:0.98rem;">Account Created</span><div style="font-size:1.08rem;"><?php echo e(auth()->user()->created_at); ?></div></div>
            <div><span style="color:#64748b;font-size:0.98rem;">Last Updated</span><div style="font-size:1.08rem;"><?php echo e(auth()->user()->updated_at); ?></div></div>
            <div><span style="color:#64748b;font-size:0.98rem;">Created By</span><div style="font-size:1.08rem;"><?php echo e(auth()->user()->created_by ?? 'System Admin'); ?></div></div>
        </div>
    </section>

    <div style="text-align:right;margin-top:32px;">
        <a href="/profile/edit" style="display:inline-block;padding:12px 32px;background:#3762c8;color:#fff;border-radius:8px;text-decoration:none;font-size:1.08rem;font-weight:600;box-shadow:0 2px 8px rgba(55,98,200,0.10);transition:background 0.18s;">Edit Profile</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/profile/show.blade.php ENDPATH**/ ?>