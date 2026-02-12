
<?php $__env->startSection('title', 'System Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $userRole = strtolower($user?->role ?? '');
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    if($userRole === 'staff'){
        header('Location: ' . route('modules.energy.index'));
        exit;
    }
?>

<style>
/* REPORT CARD CONTAINER - Added per instruction */
.report-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    padding: 30px;
    border: 1px solid #eef2f6;
    margin-bottom: 2rem;
}

:root{
    --primary:#2563eb;
    --bg:#f4f6fb;
    --card:#ffffff;
    --border:#e5e7eb;
    --text:#1f2937;
    --muted:#6b7280;
    --radius:14px;
    --shadow:0 6px 28px rgba(0,0,0,.08);
}
.settings-wrap{
    width:100%;
    margin:0;
    padding:10px 0 20px 0; /* Adjusted padding since it's now inside a card */
}
.settings-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    margin-bottom:22px;
    box-shadow:var(--shadow);
}
.settings-header{
    padding:24px 30px;
    font-weight:700;
    font-size:1.15rem;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
    background:#f8fafc;
}
.settings-arrow{transition:.3s;}
.settings-card.open .settings-arrow{transform:rotate(90deg);}
.settings-body{
    display:none;
    padding:36px 36px 24px 36px;
    border-top:1px solid var(--border);
    flex-direction:column;
    align-items:flex-start;
    gap:0;
}
.settings-card.open .settings-body{display:flex;}
.settings-grid{
    width:100%;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:28px 36px;
    align-items:center;
}
.settings-field{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:flex-start;
    min-width:0;
}
.settings-field label{
    font-weight:600;
    margin-bottom:6px;
    display:block;
    color:var(--text);
    font-size:1rem;
}
.settings-field input,
.settings-field select{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid var(--border);
    font-size:1rem;
    background:#f9fafb;
    color:var(--text);
    transition:border-color .2s, box-shadow .2s;
}
.settings-field input:focus,
.settings-field select:focus{
    border-color:var(--primary);
    outline:none;
    box-shadow:0 0 0 2px #2563eb33;
}
@media(max-width:1100px){
    .settings-grid{grid-template-columns:repeat(2,1fr);}
    .settings-body{padding:24px;}
}
@media(max-width:768px){
    .settings-grid{grid-template-columns:1fr;}
    .settings-body{padding:12px;}
}
</style>

<div class="report-card">
    <div class="settings-wrap">
        <h1 style="font-size:2.3rem;font-weight:800;color:#2563eb;">System Settings</h1>
        <p style="color:#6b7280;margin-bottom:26px;">System-wide configuration & behavior</p>

        <form method="POST" action="<?php echo e(url('/modules/settings')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">General / App Settings <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="system_name">System Name</label>
                        <input type="text" id="system_name" name="system_name" value="<?php echo e($settings['general'][0]->value ?? ''); ?>" placeholder="System Name">
                    </div>
                    <div class="settings-field">
                        <label for="short_name">Short Name</label>
                        <input type="text" id="short_name" name="short_name" value="<?php echo e($settings['general'][1]->value ?? ''); ?>" placeholder="Short Name">
                    </div>
                    <div class="settings-field">
                        <label for="org_name">Organization</label>
                        <input type="text" id="org_name" name="org_name" value="<?php echo e($settings['general'][2]->value ?? ''); ?>" placeholder="Organization">
                    </div>
                    <div class="settings-field">
                        <label for="system_logo">System Logo</label>
                        <input type="file" id="system_logo" name="system_logo">
                    </div>
                    <div class="settings-field">
                        <label for="favicon">Favicon</label>
                        <input type="file" id="favicon" name="favicon">
                    </div>
                    <div class="settings-field">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone"><option>Asia/Manila</option></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">User & Security <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="otp_expiration">OTP Expiration</label>
                        <input type="number" id="otp_expiration" name="otp_expiration" value="<?php echo e($settings['user'][1]->value ?? 5); ?>" placeholder="OTP Expiration">
                    </div>
                    <div class="settings-field">
                        <label for="max_login_attempts">Max Attempts</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" value="<?php echo e($settings['user'][2]->value ?? 5); ?>" placeholder="Max Attempts">
                    </div>
                    <div class="settings-field">
                        <label for="session_timeout">Session Timeout</label>
                        <input type="number" id="session_timeout" name="session_timeout" value="<?php echo e($settings['user'][3]->value ?? 120); ?>" placeholder="Session Timeout">
                    </div>
                    <div class="settings-field">
                        <label for="enable_otp_login">OTP Login</label>
                        <select id="enable_otp_login" name="enable_otp_login"><option value="1">OTP ON</option><option value="0">OTP OFF</option></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">Energy Monitoring <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div style="width:100%;display:flex;flex-direction:column;gap:32px;">
                    <div>
                        <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Small Facility</h3>
                        <div class="settings-grid">
                            <div class="settings-field">
                                <label for="alert_level1_small">Level 1 (Very Low) %</label>
                                <input type="number" id="alert_level1_small" name="alert_level1_small" value="<?php echo e($settings['energy'][0]->level1_small ?? 3); ?>" placeholder="Level 1 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level2_small">Level 2 (Low) %</label>
                                <input type="number" id="alert_level2_small" name="alert_level2_small" value="<?php echo e($settings['energy'][0]->level2_small ?? 5); ?>" placeholder="Level 2 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level3_small">Level 3 (Medium) %</label>
                                <input type="number" id="alert_level3_small" name="alert_level3_small" value="<?php echo e($settings['energy'][0]->level3_small ?? 10); ?>" placeholder="Level 3 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level4_small">Level 4 (High) %</label>
                                <input type="number" id="alert_level4_small" name="alert_level4_small" value="<?php echo e($settings['energy'][0]->level4_small ?? 20); ?>" placeholder="Level 4 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level5_small">Level 5 (Extreme) %</label>
                                <input type="number" id="alert_level5_small" name="alert_level5_small" value="<?php echo e($settings['energy'][0]->level5_small ?? 30); ?>" placeholder="Level 5 %">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Medium Facility</h3>
                        <div class="settings-grid">
                            <div class="settings-field">
                                <label for="alert_level1_medium">Level 1 (Very Low) %</label>
                                <input type="number" id="alert_level1_medium" name="alert_level1_medium" value="<?php echo e($settings['energy'][0]->level1_medium ?? 5); ?>" placeholder="Level 1 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level2_medium">Level 2 (Low) %</label>
                                <input type="number" id="alert_level2_medium" name="alert_level2_medium" value="<?php echo e($settings['energy'][0]->level2_medium ?? 7); ?>" placeholder="Level 2 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level3_medium">Level 3 (Medium) %</label>
                                <input type="number" id="alert_level3_medium" name="alert_level3_medium" value="<?php echo e($settings['energy'][0]->level3_medium ?? 13); ?>" placeholder="Level 3 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level4_medium">Level 4 (High) %</label>
                                <input type="number" id="alert_level4_medium" name="alert_level4_medium" value="<?php echo e($settings['energy'][0]->level4_medium ?? 23); ?>" placeholder="Level 4 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level5_medium">Level 5 (Extreme) %</label>
                                <input type="number" id="alert_level5_medium" name="alert_level5_medium" value="<?php echo e($settings['energy'][0]->level5_medium ?? 35); ?>" placeholder="Level 5 %">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Large Facility</h3>
                        <div class="settings-grid">
                            <div class="settings-field">
                                <label for="alert_level1_large">Level 1 (Very Low) %</label>
                                <input type="number" id="alert_level1_large" name="alert_level1_large" value="<?php echo e($settings['energy'][0]->level1_large ?? 7); ?>" placeholder="Level 1 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level2_large">Level 2 (Low) %</label>
                                <input type="number" id="alert_level2_large" name="alert_level2_large" value="<?php echo e($settings['energy'][0]->level2_large ?? 10); ?>" placeholder="Level 2 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level3_large">Level 3 (Medium) %</label>
                                <input type="number" id="alert_level3_large" name="alert_level3_large" value="<?php echo e($settings['energy'][0]->level3_large ?? 16); ?>" placeholder="Level 3 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level4_large">Level 4 (High) %</label>
                                <input type="number" id="alert_level4_large" name="alert_level4_large" value="<?php echo e($settings['energy'][0]->level4_large ?? 26); ?>" placeholder="Level 4 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level5_large">Level 5 (Extreme) %</label>
                                <input type="number" id="alert_level5_large" name="alert_level5_large" value="<?php echo e($settings['energy'][0]->level5_large ?? 40); ?>" placeholder="Level 5 %">
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Extra Large Facility</h3>
                        <div class="settings-grid">
                            <div class="settings-field">
                                <label for="alert_level1_xlarge">Level 1 (Very Low) %</label>
                                <input type="number" id="alert_level1_xlarge" name="alert_level1_xlarge" value="<?php echo e($settings['energy'][0]->level1_xlarge ?? 10); ?>" placeholder="Level 1 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level2_xlarge">Level 2 (Low) %</label>
                                <input type="number" id="alert_level2_xlarge" name="alert_level2_xlarge" value="<?php echo e($settings['energy'][0]->level2_xlarge ?? 12); ?>" placeholder="Level 2 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level3_xlarge">Level 3 (Medium) %</label>
                                <input type="number" id="alert_level3_xlarge" name="alert_level3_xlarge" value="<?php echo e($settings['energy'][0]->level3_xlarge ?? 18); ?>" placeholder="Level 3 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level4_xlarge">Level 4 (High) %</label>
                                <input type="number" id="alert_level4_xlarge" name="alert_level4_xlarge" value="<?php echo e($settings['energy'][0]->level4_xlarge ?? 28); ?>" placeholder="Level 4 %">
                            </div>
                            <div class="settings-field">
                                <label for="alert_level5_xlarge">Level 5 (Extreme) %</label>
                                <input type="number" id="alert_level5_xlarge" name="alert_level5_xlarge" value="<?php echo e($settings['energy'][0]->level5_xlarge ?? 45); ?>" placeholder="Level 5 %">
                            </div>
                        </div>
                    </div>
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="auto_log_incident">Auto Log Incident</label>
                            <select id="auto_log_incident" name="auto_log_incident"><option value="1">Auto Log YES</option><option value="0">NO</option></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">Facility Settings <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="facility_image_size">Image Size MB</label>
                        <input type="number" id="facility_image_size" name="facility_image_size" value="<?php echo e($settings['facility'][0]->value ?? 5); ?>" placeholder="Image Size MB">
                    </div>
                    <div class="settings-field">
                        <label for="allowed_image_types">Allowed Image Types</label>
                        <input type="text" id="allowed_image_types" name="allowed_image_types" value="<?php echo e($settings['facility'][1]->value ?? 'jpg,png'); ?>">
                    </div>
                    <div class="settings-field">
                        <label for="default_facility_status">Default Facility Status</label>
                        <select id="default_facility_status" name="default_facility_status"><option>active</option><option>inactive</option></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">Email & Notifications <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="mail_host">Mail Host</label>
                        <input type="text" id="mail_host" name="mail_host" value="<?php echo e($settings['email'][1]->value ?? ''); ?>">
                    </div>
                    <div class="settings-field">
                        <label for="mail_port">Mail Port</label>
                        <input type="number" id="mail_port" name="mail_port" value="<?php echo e($settings['email'][2]->value ?? 587); ?>">
                    </div>
                    <div class="settings-field">
                        <label for="enable_email_notifications">Email Notifications</label>
                        <select id="enable_email_notifications" name="enable_email_notifications"><option value="1">ON</option><option value="0">OFF</option></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="settings-header" onclick="toggleCard(this)">Reports & Audit Trail <span class="settings-arrow">▶</span></div>
            <div class="settings-body">
                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="enable_audit_logs">Enable Audit Logs</label>
                        <select id="enable_audit_logs" name="enable_audit_logs"><option value="1">YES</option><option value="0">NO</option></select>
                    </div>
                    <div class="settings-field">
                        <label for="retention_period">Retention Period (months)</label>
                        <input type="number" id="retention_period" name="retention_period" value="<?php echo e($settings['reports'][2]->value ?? 12); ?>">
                    </div>
                    <div class="settings-field">
                        <label for="export_format">Export Format</label>
                        <select id="export_format" name="export_format"><option>pdf</option><option>excel</option></select>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:right;margin-top:32px;">
            <button type="submit" style="padding:14px 46px;border-radius:14px;border:none;font-weight:700;background:#2563eb;color:#fff; cursor:pointer;">
            Save Settings
            </button>
        </div>

        </form>
    </div>
</div>

<script>
function toggleCard(header){
    // Toggle only the clicked card, allow multiple open
    header.parentElement.classList.toggle('open');
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/settings/index.blade.php ENDPATH**/ ?>