

<?php $__env->startSection('title', 'First 3 Months Data'); ?>
<?php $__env->startSection('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">First 3 Months kWh Data</h2>
    <div style="display:flex;gap:12px;align-items:center;">
        <button type="button" id="btnAddFirst3Mo" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s;">+ Add Month Data</button>
    </div>
</div>

<?php if(isset($facilityModel) && $facilityModel): ?>
    <div style="margin-bottom:1.2rem;">
        <strong>Facility:</strong> <?php echo e($facilityModel->name); ?>

    </div>
<?php else: ?>
    <div style="margin-bottom:1.2rem; color:#b91c1c; font-weight:600;">No facility selected.</div>
<?php endif; ?>

<?php if(session('success')): ?>
    <div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:8px;margin-bottom:18px;text-align:center;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border-radius:8px;margin-bottom:18px;">
        <ul style="margin:0;padding-left:18px;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div id="mainContentWrapper" style="position:relative;">
<div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08); min-width:400px; max-width:600px; margin:0 auto 32px auto;">
    <table style="width:100%;border-collapse:collapse;min-width:400px;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:left;">
                <th style="padding:10px 14px;">Month</th>
                <th style="padding:10px 14px;">kWh</th>
                <th style="padding:10px 14px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $first3mo = null;
                if(isset($facilityModel)) {
                    $first3mo = \DB::table('first3months_data')->where('facility_id', $facilityModel->id)->first();
                }
                $months = [1 => 'Month 1', 2 => 'Month 2', 3 => 'Month 3'];
            ?>
            <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 14px;font-weight:600;color:#2563eb;"><?php echo e($label); ?></td>
                <td style="padding:10px 14px;"><?php echo e($first3mo?->{'month'.$num} ?? '-'); ?></td>
                <td style="padding:10px 14px;">
                    <?php if($first3mo && $first3mo->{'month'.$num} !== null): ?>
                    <form method="POST" action="<?php echo e(route('facilities.first3months.delete', ['facility_id' => $facilityModel->id ?? request('facility_id'), 'month_no' => $num])); ?>" style="display:inline;" class="delete-month-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="button" class="btn-delete-month" data-month="<?php echo e($num); ?>" style="background:#ef4444;color:#fff;border:none;padding:6px 16px;border-radius:7px;font-weight:600;cursor:pointer;">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<!-- Modal for Adding Month Data (Centered Overlay) -->
<div id="modalAddFirst3Mo" class="modal" style="display:none;position:fixed;z-index:10050;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div style="background:#fff;padding:32px 28px;border-radius:16px;min-width:320px;box-shadow:0 8px 32px rgba(31,38,135,0.13);position:relative;max-width:90vw;">
            <button class="modal-close" onclick="document.getElementById('modalAddFirst3Mo').style.display='none'" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#6366f1;cursor:pointer;">&times;</button>
            <h3 style="font-size:1.3rem;font-weight:700;color:#2563eb;margin-bottom:18px;">Add Month kWh Data</h3>
            <form method="POST" action="<?php echo e(route('facilities.first3months.store')); ?>" autocomplete="off">
                <?php echo csrf_field(); ?>
                <div class="form-group" style="margin-bottom:18px;">
                    <label for="month_no" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">Month</label>
                    <select name="month_no" id="month_no" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                        <option value="">Select Month</option>
                        <?php
                            $monthLabels = [1 => 'Month 1', 2 => 'Month 2', 3 => 'Month 3'];
                        ?>
                        <?php $__currentLoopData = $monthLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $currentVal = $first3mo?->{'month'.$num} ?? null;
                            ?>
                            <?php if(!$currentVal): ?>
                                <option value="<?php echo e($num); ?>"><?php echo e($label); ?></option>
                            <?php else: ?>
                                <option value="<?php echo e($num); ?>"><?php echo e($label); ?> (current: <?php echo e($currentVal); ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label for="kwh" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">kWh</label>
                    <input type="number" step="0.01" name="kwh" id="kwh" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                </div>
                <input type="hidden" name="facility_id" value="<?php echo e($facilityModel->id ?? request('facility_id') ?? ''); ?>">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:12px 0;width:100%;border-radius:8px;font-weight:700;font-size:1.13rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);border:none;transition:background 0.2s;">Save</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('btnAddFirst3Mo').onclick = function() {
    document.getElementById('modalAddFirst3Mo').style.display = 'flex';
};
document.querySelectorAll('.modal-close').forEach(btn=>btn.addEventListener('click',()=>btn.closest('.modal').style.display='none'));
window.addEventListener('click', e=>{ document.querySelectorAll('.modal').forEach(m=>{if(e.target===m)m.style.display='none';}); });

// Delete modal logic (event delegation)
document.addEventListener('DOMContentLoaded', function() {
    let deleteTargetForm = null;
    document.getElementById('mainContentWrapper').addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete-month')) {
            e.preventDefault();
            deleteTargetForm = e.target.closest('form');
            const monthNum = e.target.getAttribute('data-month');
            document.getElementById('deleteMonthLabel').textContent = 'Month ' + monthNum;
            document.getElementById('modalDeleteMonth').style.display = 'flex';
        }
    });
    document.getElementById('confirmDeleteMonth').onclick = function() {
        if(deleteTargetForm) deleteTargetForm.submit();
    };
    document.getElementById('cancelDeleteMonth').onclick = function() {
        document.getElementById('modalDeleteMonth').style.display = 'none';
        deleteTargetForm = null;
    };
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalDeleteMonth');
        if(e.target === modal) {
            modal.style.display = 'none';
            deleteTargetForm = null;
        }
    });
});
</script>

<!-- Delete Month Modal (centered in main content) -->
<div id="modalDeleteMonth" class="modal" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;align-items:center;justify-content:center;z-index:20;background:rgba(0,0,0,0.08);">
    <div style="background:#fff;padding:32px 28px;border-radius:16px;min-width:320px;box-shadow:0 8px 32px rgba(31,38,135,0.13);position:relative;max-width:90vw;">
        <h3 style="font-size:1.2rem;font-weight:700;color:#ef4444;margin-bottom:18px;">Delete <span id="deleteMonthLabel"></span>?</h3>
        <div style="margin-bottom:24px;">Are you sure you want to delete this month's kWh data? This action cannot be undone.</div>
        <div style="display:flex;gap:16px;justify-content:flex-end;">
            <button id="cancelDeleteMonth" type="button" style="background:#e5e7eb;color:#222;border:none;padding:8px 22px;border-radius:7px;font-weight:600;cursor:pointer;">Cancel</button>
            <button id="confirmDeleteMonth" type="button" style="background:#ef4444;color:#fff;border:none;padding:8px 22px;border-radius:7px;font-weight:600;cursor:pointer;">Delete</button>
        </div>
    </div>
</div>
</div> <!-- end #mainContentWrapper -->

<?php $__env->stopSection(); ?>


<?php
	// Compute average kWh for this facility from first3months_data
	$first3mo = \DB::table('first3months_data')->where('facility_id', $facilityModel->id ?? null)->first();
	$avgKwh = null;
	if ($first3mo) {
		$avgKwh = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
	}
?>
<?php echo $__env->make('modules.facilities.energy-profile.partials.modals', ['avgKwh' => $avgKwh], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modules.facilities.energy-profile.partials.delete-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>



<script>
function editEnergyProfile(btn) {
	// Populate modal fields from data attributes
	document.getElementById('edit_energy_profile_id').value = btn.getAttribute('data-id');
	document.getElementById('edit_electric_meter_no').value = btn.getAttribute('data-electric_meter_no');
	document.getElementById('edit_utility_provider').value = btn.getAttribute('data-utility_provider');
	document.getElementById('edit_contract_account_no').value = btn.getAttribute('data-contract_account_no');
    document.getElementById('edit_baseline_kwh').value = btn.getAttribute('data-baseline_kwh');
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

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/first3months.blade.php ENDPATH**/ ?>