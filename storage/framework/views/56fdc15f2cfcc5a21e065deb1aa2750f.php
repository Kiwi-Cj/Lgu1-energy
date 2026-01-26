
<?php $__env->startSection('title', 'Energy Profile'); ?>
<?php $__env->startSection('content'); ?>


<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
	<h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Energy Profile</h2>
	<div style="display:flex;gap:12px;align-items:center;">
		<button type="button" class="btn-add-energy-profile" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; <?php if($energyProfiles->count()): ?> opacity:0.5; pointer-events:none; <?php endif; ?>" <?php if($energyProfiles->count()): ?> disabled <?php endif; ?>>+ Add Energy Profile</button>

	</div>
</div>

<?php if(isset($facilityModel)): ?>
	<div style="margin-bottom:1.2rem;">
		<strong>Facility:</strong> <?php echo e($facilityModel->name); ?>

	</div>
<?php endif; ?>

<div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
	<table style="width:100%;border-collapse:collapse;min-width:900px;">
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
				<th style="padding:10px 14px;">Action</th>
			</tr>
		</thead>
		<tbody>
		<?php $latestProfileId = $energyProfiles->count() ? $energyProfiles->first()->id : null; ?>
		<?php $__empty_1 = true; $__currentLoopData = $energyProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
			<tr style="border-bottom:1px solid #e5e7eb;">
				<td style="padding:10px 14px;"><?php echo e($profile->electric_meter_no); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->utility_provider); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->contract_account_no); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->average_monthly_kwh); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->main_energy_source); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->backup_power); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->transformer_capacity ?? '-'); ?></td>
				<td style="padding:10px 14px;"><?php echo e($profile->number_of_meters); ?></td>
				<td style="padding:10px 14px;display:flex;gap:8px;align-items:center;">
					<button type="button" title="Edit"
						style="background:none;border:none;color:#2563eb;font-size:1.2rem;cursor:pointer;"
						onclick="editEnergyProfile(this)"
						data-id="<?php echo e($profile->id); ?>"
						data-electric_meter_no="<?php echo e($profile->electric_meter_no); ?>"
						data-utility_provider="<?php echo e($profile->utility_provider); ?>"
						data-contract_account_no="<?php echo e($profile->contract_account_no); ?>"
						data-average_monthly_kwh="<?php echo e($profile->average_monthly_kwh); ?>"
						data-main_energy_source="<?php echo e($profile->main_energy_source); ?>"
						data-backup_power="<?php echo e($profile->backup_power); ?>"
						data-transformer_capacity="<?php echo e($profile->transformer_capacity); ?>"
						data-number_of_meters="<?php echo e($profile->number_of_meters); ?>"
					>
						<i class="fa fa-edit"></i>
					</button>
					<button type="button" title="Delete"
						style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;"
						onclick="openDeleteEnergyProfileModal(<?php echo e(isset($facilityModel) ? $facilityModel->id : 'null'); ?>, <?php echo e($profile->id); ?>)"
						data-id="<?php echo e($profile->id); ?>"
						data-electric_meter_no="<?php echo e($profile->electric_meter_no); ?>"
						data-utility_provider="<?php echo e($profile->utility_provider); ?>"
					>
						<i class="fa fa-trash"></i>
					</button>
				</td>
			</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
			<tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:22px 0;">No energy profile data found.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>


<?php echo $__env->make('modules.facilities.energy-profile.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modules.facilities.energy-profile.partials.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>



<script>
function editEnergyProfile(btn) {
	// Populate modal fields from data attributes
	document.getElementById('edit_energy_profile_id').value = btn.getAttribute('data-id');
	document.getElementById('edit_electric_meter_no').value = btn.getAttribute('data-electric_meter_no');
	document.getElementById('edit_utility_provider').value = btn.getAttribute('data-utility_provider');
	document.getElementById('edit_contract_account_no').value = btn.getAttribute('data-contract_account_no');
	document.getElementById('edit_average_monthly_kwh').value = btn.getAttribute('data-average_monthly_kwh');
	document.getElementById('edit_main_energy_source').value = btn.getAttribute('data-main_energy_source');
	document.getElementById('edit_backup_power').value = btn.getAttribute('data-backup_power');
	document.getElementById('edit_transformer_capacity').value = btn.getAttribute('data-transformer_capacity');
	document.getElementById('edit_number_of_meters').value = btn.getAttribute('data-number_of_meters');
	// Bill image is not set here (file input cannot be set for security reasons)
	document.getElementById('editEnergyProfileModal').classList.add('show-modal');
}

function deleteEnergyProfile(btn) {
    var facilityId = <?php echo e(isset($facilityModel) ? $facilityModel->id : 'null'); ?>;
    var profileId = btn.getAttribute('data-id');
    if(!facilityId || !profileId) {
        alert('Missing facility or profile ID.');
        return;
    }
    if(confirm('Are you sure you want to delete this energy profile?')) {
        fetch(`/modules/facilities/${facilityId}/energy-profile/${profileId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('Energy profile deleted!');
                location.reload();
            } else {
                alert('Delete failed.');
            }
        });
    }
}

function closeEditEnergyProfileModal() {
	document.getElementById('editEnergyProfileModal').classList.remove('show-modal');
}
function closeDeleteEnergyProfileModal() {
	document.getElementById('deleteEnergyProfileModal').classList.remove('show-modal');
}
function closeAddEnergyProfileModal() {
	document.getElementById('addEnergyProfileModal').classList.remove('show-modal');
}
// Optionally, add openAddEnergyProfileModal() for the add button
document.querySelector('.btn-add-energy-profile')?.addEventListener('click', function() {
    document.getElementById('addEnergyProfileModal').classList.add('show-modal');
    // Set facility_id if available
    var facilityId = <?php echo e(isset($facilityModel) ? $facilityModel->id : 'null'); ?>;
    if(facilityId) {
        document.getElementById('add_energy_facility_id').value = facilityId;
    }
});

function updateEnergyProfile(profileId, facilityId) {
    const form = document.getElementById('editEnergyProfileForm');
    const formData = new FormData(form);
    fetch(`/modules/facilities/${facilityId}/energy-profile/${profileId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Energy profile updated!');
            location.reload();
        } else {
            alert('Update failed.');
        }
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/energy-profile/index.blade.php ENDPATH**/ ?>