
<?php $__env->startSection('title', 'Energy Profile'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $userRole = strtolower(auth()->user()->role ?? '');
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="font-size:2rem;font-weight:700;color:#222;margin:0;">Energy Profile</h2>
    <?php if($userRole !== 'staff'): ?>
        <button type="button" class="btn-add-energy-profile" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:10px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);">
            + Add Energy Profile
        </button>
    <?php endif; ?>
</div>

<?php if(isset($facilityModel)): ?>
    <div style="margin-bottom:1.2rem;"><strong>Facility:</strong> <?php echo e($facilityModel->name); ?></div>
<?php endif; ?>

<div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(31,38,135,0.08);">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:left;">
                <th style="padding:10px 14px;">Electric Meter No.</th>
                <th style="padding:10px 14px;">Utility Provider</th>
                <th style="padding:10px 14px;">Contract Account No.</th>
                <th style="padding:10px 14px;">Average Monthly kWh</th>
                <th style="padding:10px 14px;">Main Energy Source</th>
                <th style="padding:10px 14px;">Backup Power</th>
                <th style="padding:10px 14px;">Transformer Capacity</th>
                <th style="padding:10px 14px;">Number of Meters</th>
                <th style="padding:10px 14px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $energyProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="padding:10px 14px;"><?php echo e($profile->electric_meter_no); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->utility_provider); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->contract_account_no); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->average_monthly_kwh); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->main_energy_source); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->backup_power); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->transformer_capacity ?? '-'); ?></td>
                    <td style="padding:10px 14px;"><?php echo e($profile->number_of_meters); ?></td>
                    <td style="padding:10px 14px;display:flex;gap:8px;align-items:center;">
                        <?php if($userRole !== 'staff'): ?>
                            <button onclick="editEnergyProfile(this)" 
                                data-id="<?php echo e($profile->id); ?>"
                                data-electric_meter_no="<?php echo e($profile->electric_meter_no); ?>"
                                data-utility_provider="<?php echo e($profile->utility_provider); ?>"
                                data-contract_account_no="<?php echo e($profile->contract_account_no); ?>"
                                data-average_monthly_kwh="<?php echo e($profile->average_monthly_kwh); ?>"
                                data-main_energy_source="<?php echo e($profile->main_energy_source); ?>"
                                data-backup_power="<?php echo e($profile->backup_power); ?>"
                                data-transformer_capacity="<?php echo e($profile->transformer_capacity); ?>"
                                data-number_of_meters="<?php echo e($profile->number_of_meters); ?>"
                                title="Edit"
                                style="color:#6366f1;font-size:1.2rem;"><i class="fa fa-pen"></i>
                            </button>
                            <button onclick="deleteEnergyProfile(this)" 
                                data-id="<?php echo e($profile->id); ?>"
                                data-electric_meter_no="<?php echo e($profile->electric_meter_no); ?>"
                                data-utility_provider="<?php echo e($profile->utility_provider); ?>"
                                title="Delete"
                                style="color:#e11d48;font-size:1.2rem;"><i class="fa fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" style="text-align:center;color:#94a3b8;padding:22px 0;">No energy profiles found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo $__env->make('modules.energy-monitoring.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <!-- Add/Edit/Delete modals -->

<script>
// Open Add Modal
document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function() {
    document.getElementById('addEnergyProfileModal').classList.add('show-modal');
});

// Edit Energy Profile
function editEnergyProfile(btn) {
    const modal = document.getElementById('editEnergyProfileModal');
    modal.style.display = 'flex';
    document.getElementById('edit_energy_profile_id').value = btn.dataset.id;
    document.getElementById('edit_electric_meter_no').value = btn.dataset.electric_meter_no;
    document.getElementById('edit_utility_provider').value = btn.dataset.utility_provider;
    document.getElementById('edit_contract_account_no').value = btn.dataset.contract_account_no;
    document.getElementById('edit_average_monthly_kwh').value = btn.dataset.average_monthly_kwh;
    document.getElementById('edit_main_energy_source').value = btn.dataset.main_energy_source;
    document.getElementById('edit_backup_power').value = btn.dataset.backup_power;
    document.getElementById('edit_transformer_capacity').value = btn.dataset.transformer_capacity;
    document.getElementById('edit_number_of_meters').value = btn.dataset.number_of_meters;
}

// Delete Energy Profile
function deleteEnergyProfile(btn) {
    const id = btn.dataset.id;
    if(confirm(`Delete Energy Profile for Meter ${btn.dataset.electric_meter_no}?`)) {
        fetch(`/modules/energy-monitoring/energy-profile/${id}`, {
            method:'DELETE',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}
        }).then(res=>res.json()).then(data=>{
            alert(data.message||'Deleted');
            location.reload();
        });
    }
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/energy-profile.blade.php ENDPATH**/ ?>