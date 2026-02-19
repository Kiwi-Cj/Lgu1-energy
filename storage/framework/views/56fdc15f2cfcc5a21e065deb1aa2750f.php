
<?php $__env->startSection('title', 'Energy Profile'); ?>

<?php
    // first3months_data table removed; fallback to baseline_kwh
    $avgKwh = isset($facilityModel) ? $facilityModel->baseline_kwh : null;
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
?>

<style>
    /* --- Shared UI Variables (Same as Energy Report) --- */
    :root {
        --report-bg: #ffffff;
        --report-text: #333333;
        --report-subtext: #555555;
        --card-shadow: rgba(31, 38, 135, 0.08);
        --table-header-bg: #e9effc;
        --table-row-even: #f8fafc;
        --table-border: #e5e7eb;
    }

    @media (prefers-color-scheme: dark) {
        :root {
            --report-bg: #1e293b;
            --report-text: #f1f5f9;
            --report-subtext: #94a3b8;
            --card-shadow: rgba(0, 0, 0, 0.4);
            --table-header-bg: #334155;
            --table-row-even: #1e293b;
            --table-border: #475569;
        }
    }

    .profile-card {
        background: var(--report-bg);
        border-radius: 18px;
        box-shadow: 0 4px 12px var(--card-shadow);
        margin-bottom: 1.2rem;
        padding: 24px;
        color: var(--report-text);
        transition: background 0.3s ease;
    }

    .profile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }

    .btn-action-main {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        color: #fff;
        background: linear-gradient(90deg,#2563eb,#6366f1);
        cursor: pointer;
        transition: 0.2s;
        text-wrap: nowrap;
    }

    .btn-action-main:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--table-border);
        border-radius: 12px;
    }

    .custom-table { width: 100%; border-collapse: collapse; min-width: 1000px; }
    .custom-table thead { background: var(--table-header-bg); }
    .custom-table th { 
        padding: 14px; 
        text-align: left; 
        color: #3762c8; 
        border-bottom: 2px solid var(--table-border);
        font-size: 0.9rem;
    }
    
    @media (prefers-color-scheme: dark) {
        .custom-table th { color: #60a5fa; }
    }

    .custom-table td { padding: 12px; border-bottom: 1px solid var(--table-border); font-size: 0.95rem; }
    .row-even { background: var(--table-row-even); }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .profile-card { padding: 15px; }
        .profile-header { flex-direction: column; text-align: center; }
        .btn-action-main { width: 100%; }
        .profile-header h2 { font-size: 1.5rem !important; }
    }

    /* Alerts */
    .alert-box {
        position: fixed; top: 32px; right: 32px; z-index: 99999; 
        min-width: 280px; max-width: 420px;
        padding: 16px 24px; border-radius: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
    }
</style>

<?php $__env->startSection('content'); ?>
<div style="width:100%; margin:0 auto;">

    <?php if(session('success')): ?>
    <div id="successAlert" class="alert-box" style="background:#dcfce7; color:#166534; box-shadow:0 2px 8px #16a34a22;">
        <i class="fa fa-check-circle" style="font-size:1.3rem;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div id="errorAlert" class="alert-box" style="background:#fee2e2; color:#b91c1c; box-shadow:0 2px 8px #e11d4822;">
        <i class="fa fa-times-circle" style="font-size:1.3rem;"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?>

    <div class="profile-card">
        <div class="profile-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:700; color:#3762c8; margin:0;">📋 Energy Profile</h2>
                <p style="color:var(--report-subtext); margin-top:4px;"><?php echo e($facilityModel->name ?? 'Facility Details'); ?></p>
            </div>
            <?php if($userRole !== 'energy_officer'): ?>
                <button type="button" class="btn-action-main btn-add-energy-profile" 
                    <?php if($energyProfiles->count()): ?> disabled <?php endif; ?>>
                    <i class="fa fa-plus"></i> Add Energy Profile
                </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Meter No.</th>
                        <th>Utility Provider</th>
                        <th>Contract Account</th>
                        <th>Avg kWh</th>
                        <th>Main Source</th>
                        <th>Backup</th>
                        <th>Capacity</th>
                        <th>Meters</th>
                        <th>Baseline</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $energyProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="<?php echo e($loop->even ? 'row-even' : ''); ?>">
                        <td><?php echo e($profile->electric_meter_no); ?></td>
                        <td><?php echo e($profile->utility_provider); ?></td>
                        <td><?php echo e($profile->contract_account_no); ?></td>
                        <td><?php echo e(number_format($profile->baseline_kwh, 2)); ?></td>
                        <td><?php echo e($profile->main_energy_source); ?></td>
                        <td><?php echo e($profile->backup_power); ?></td>
                        <td><?php echo e($profile->transformer_capacity ?? '-'); ?></td>
                        <td><?php echo e($profile->number_of_meters); ?></td>
                        <td><?php echo e($profile->baseline_source ?? '-'); ?></td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:12px; justify-content:center; align-items:center;">
                                <?php if($userRole === 'engineer' || $userRole === 'super admin'): ?>
                                    <form method="POST" action="<?php echo e(route('energy-profile.toggle-approval', ['facility' => $facilityModel->id, 'profile' => $profile->id])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" style="background:none; border:none; cursor:pointer;">
                                            <i class="fa <?php echo e($profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle'); ?>" 
                                               style="font-size:1.4rem; color:<?php echo e($profile->engineer_approved ? '#22c55e' : '#e11d48'); ?>;"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <i class="fa <?php echo e($profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle'); ?>" 
                                       style="font-size:1.4rem; color:<?php echo e($profile->engineer_approved ? '#22c55e' : '#e11d48'); ?>;"></i>
                                <?php endif; ?>

                                <button type="button" onclick="openDeleteEnergyProfileModal(<?php echo e($facilityModel->id); ?>, <?php echo e($profile->id); ?>)" 
                                    style="background:none; border:none; color:#e11d48; font-size:1.1rem; cursor:pointer;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" style="padding:40px; text-align:center; color:var(--report-subtext);">No energy profile data found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $__env->make('modules.facilities.energy-profile.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modules.facilities.energy-profile.partials.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
    // Auto-hide alerts
    window.addEventListener('DOMContentLoaded', function() {
        const s = document.getElementById('successAlert');
        const e = document.getElementById('errorAlert');
        if (s) setTimeout(() => s.style.opacity = '0', 3000);
        if (e) setTimeout(() => e.style.opacity = '0', 3000);
    });

    document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function(){
        const modal = document.getElementById('addEnergyProfileModal');
        modal.classList.add('show-modal');
        modal.querySelector('#add_energy_facility_id').value = "<?php echo e($facilityModel->id ?? 'null'); ?>";
    });

    function closeModal(modalId){ document.getElementById(modalId).classList.remove('show-modal'); }
</script>
<?php $__env->stopSection(); ?>@extends('layouts.qc-admin')
<?php $__env->startSection('title', 'Energy Profile'); ?>

<?php
    // first3months_data table removed; fallback to baseline_kwh
    $avgKwh = isset($facilityModel) ? $facilityModel->baseline_kwh : null;
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
?>

<style>
    /* --- Permanent Light UI Colors --- */
    .profile-card {
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 4px 12px rgba(31, 38, 135, 0.08);
        margin-bottom: 1.2rem;
        padding: 24px;
        color: #333333;
    }

    .profile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }

    .btn-action-main {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        color: #fff;
        background: linear-gradient(90deg, #2563eb, #6366f1);
        cursor: pointer;
        transition: 0.2s;
        text-wrap: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-action-main:hover {
        opacity: 0.9;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-action-main:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        box-shadow: none;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .custom-table { width: 100%; border-collapse: collapse; min-width: 1000px; }
    .custom-table thead { background: #e9effc; }
    .custom-table th { 
        padding: 14px; 
        text-align: left; 
        color: #3762c8; 
        border-bottom: 2px solid #c3cbe5;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .custom-table td { 
        padding: 12px; 
        border-bottom: 1px solid #f1f5f9; 
        font-size: 0.95rem; 
        color: #475569;
    }
    
    .custom-table tr:nth-child(even) { background: #f8fafc; }
    .custom-table tr:hover { background: #f1f5f9; }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .profile-card { padding: 15px; }
        .profile-header { flex-direction: column; text-align: center; align-items: stretch; }
        .btn-action-main { justify-content: center; }
        .profile-header h2 { font-size: 1.5rem !important; }
    }

    /* Alerts */
    .alert-box {
        position: fixed; top: 32px; right: 32px; z-index: 99999; 
        min-width: 280px; max-width: 420px;
        padding: 16px 24px; border-radius: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
        transition: opacity 0.5s ease;
    }
</style>

<?php $__env->startSection('content'); ?>
<div style="width:100%; margin:0 auto;">

    <?php if(session('success')): ?>
    <div id="successAlert" class="alert-box" style="background:#dcfce7; color:#166534; box-shadow:0 2px 8px rgba(22, 163, 74, 0.15);">
        <i class="fa fa-check-circle" style="font-size:1.3rem; color:#22c55e;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <div id="errorAlert" class="alert-box" style="background:#fee2e2; color:#b91c1c; box-shadow:0 2px 8px rgba(225, 29, 72, 0.15);">
        <i class="fa fa-times-circle" style="font-size:1.3rem; color:#e11d48;"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?>

    <div class="profile-card">
        <div class="profile-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:800; color:#1e293b; margin:0;">📋 Energy Profile</h2>
                <p style="color:#64748b; margin-top:4px; font-size:1rem;"><?php echo e($facilityModel->name ?? 'Facility Details'); ?></p>
            </div>
            <?php if($userRole !== 'energy_officer'): ?>
                <button type="button" class="btn-action-main btn-add-energy-profile" 
                    <?php if($energyProfiles->count()): ?> disabled <?php endif; ?>>
                    <i class="fa fa-plus"></i> Add Energy Profile
                </button>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Meter No.</th>
                        <th>Utility Provider</th>
                        <th>Contract Account</th>
                        <th>Avg kWh</th>
                        <th>Main Source</th>
                        <th>Backup</th>
                        <th>Capacity</th>
                        <th>Meters</th>
                        <th>Baseline</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $energyProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($profile->electric_meter_no); ?></td>
                        <td><?php echo e($profile->utility_provider); ?></td>
                        <td><?php echo e($profile->contract_account_no); ?></td>
                        <td style="font-weight:600;"><?php echo e(number_format($profile->baseline_kwh, 2)); ?></td>
                        <td><?php echo e($profile->main_energy_source); ?></td>
                        <td><?php echo e($profile->backup_power); ?></td>
                        <td><?php echo e($profile->transformer_capacity ?? '-'); ?></td>
                        <td><?php echo e($profile->number_of_meters); ?></td>
                        <td><span style="background:#f1f5f9; padding:2px 8px; border-radius:5px; font-size:0.85rem;"><?php echo e($profile->baseline_source ?? '-'); ?></span></td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:12px; justify-content:center; align-items:center;">
                                <?php if($userRole === 'engineer' || $userRole === 'super admin'): ?>
                                    <form method="POST" action="<?php echo e(route('energy-profile.toggle-approval', ['facility' => $facilityModel->id, 'profile' => $profile->id])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" title="Toggle Approval" style="background:none; border:none; cursor:pointer; padding:0;">
                                            <i class="fa <?php echo e($profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle'); ?>" 
                                               style="font-size:1.4rem; color:<?php echo e($profile->engineer_approved ? '#22c55e' : '#e11d48'); ?>;"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <i class="fa <?php echo e($profile->engineer_approved ? 'fa-check-circle' : 'fa-times-circle'); ?>" 
                                       style="font-size:1.4rem; color:<?php echo e($profile->engineer_approved ? '#22c55e' : '#e11d48'); ?>;"></i>
                                <?php endif; ?>

                                <button type="button" title="Delete" onclick="openDeleteEnergyProfileModal(<?php echo e($facilityModel->id); ?>, <?php echo e($profile->id); ?>)" 
                                    style="background:none; border:none; color:#ef4444; font-size:1.1rem; cursor:pointer; padding:0;">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="10" style="padding:40px; text-align:center; color:#94a3b8; font-style:italic;">No energy profile data found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $__env->make('modules.facilities.energy-profile.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modules.facilities.energy-profile.partials.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
    // Auto-hide alerts logic
    window.addEventListener('DOMContentLoaded', function() {
        const s = document.getElementById('successAlert');
        const e = document.getElementById('errorAlert');
        if (s) setTimeout(() => { s.style.opacity = '0'; setTimeout(() => s.remove(), 500); }, 3000);
        if (e) setTimeout(() => { e.style.opacity = '0'; setTimeout(() => e.remove(), 500); }, 3000);
    });

    document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function(){
        const modal = document.getElementById('addEnergyProfileModal');
        modal.classList.add('show-modal');
        modal.querySelector('#add_energy_facility_id').value = "<?php echo e($facilityModel->id ?? 'null'); ?>";
    });

    function closeModal(modalId){ 
        document.getElementById(modalId).classList.remove('show-modal'); 
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/energy-profile/index.blade.php ENDPATH**/ ?>