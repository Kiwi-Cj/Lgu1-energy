

<?php $__env->startSection('title','Facility Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
	// Ensure notifications and unreadNotifCount are available for the notification bell
	$user = auth()->user();
	$notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
	$unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
</div>
<?php endif; ?>
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<div style="width:100%;margin:40px 0;">
<div style="background:linear-gradient(135deg,#f8fafc,#eef2ff);border-radius:26px;padding:40px;box-shadow:0 12px 40px rgba(37,99,235,.18);position:relative;width:100%;">

<!-- BACK BUTTON -->
<a href="<?php echo e(route('modules.facilities.index')); ?>" style="
position:absolute;left:28px;top:-22px;
background:#fff;padding:10px 22px;
border-radius:14px;font-weight:800;
color:#2563eb;text-decoration:none;
box-shadow:0 4px 16px #2563eb33;">
← Back
</a>


<?php
$imageUrl = null;
if($facility->image_path){
	$imageUrl = asset('storage/' . $facility->image_path);
} elseif($facility->image){
	$imageUrl = str_starts_with($facility->image,'img/')
		? asset($facility->image)
		: asset('storage/'.$facility->image);
}
?>

<!-- HEADER -->
<div style="display:flex;gap:28px;align-items:center;margin-bottom:30px;">
<?php if($imageUrl): ?>
<img src="<?php echo e($imageUrl); ?>" style="width:160px;height:120px;border-radius:18px;
object-fit:cover;box-shadow:0 6px 20px rgba(0,0,0,.2);">
<?php else: ?>
<div style="width:160px;height:120px;border-radius:18px;
background:#e5e7eb;display:flex;
align-items:center;justify-content:center;
font-size:2.5rem;color:#9ca3af;">
<i class="fa fa-image"></i>
</div>
<?php endif; ?>

<div style="flex:1;">
<h1 style="margin:0;font-size:2.2rem;font-weight:900;color:#1e293b;">
	<?php echo e($facility->name); ?>

</h1>
<div style="color:#6366f1;font-weight:700;margin-top:6px;">
	<?php echo e($facility->type); ?> • <?php echo e($facility->department); ?>

</div>
<button type="button" onclick="openEditFacilityModal()" style="margin-top:18px;background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:8px;font-weight:600;font-size:1.05rem;cursor:pointer;">
	<i class="fa fa-edit" style="margin-right:6px;"></i> Edit Facility
</button>

<div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;">
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:
<?php echo e($facility->status=='active'?'#dcfce7':($facility->status=='maintenance'?'#fef3c7':'#fee2e2')); ?>;
color:
<?php echo e($facility->status=='active'?'#166534':($facility->status=='maintenance'?'#92400e':'#991b1b')); ?>;">
<?php echo e(ucfirst($facility->status)); ?>

</span>

<?php if($facility->engineer_approved ?? false): ?>
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:#e0f2fe;color:#0369a1;">
✔ Engineer Approved
</span>
<?php else: ?>
<span style="padding:6px 18px;border-radius:999px;
font-weight:800;font-size:.9rem;
background:#f1f5f9;color:#64748b;">
✖ Not Approved
</span>
<?php endif; ?>
</div>
</div>
</div>


<!-- DETAILS GRID -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-top:24px;">

<?php
// Get latest monthly record for this facility
$latestRecord = $facility->energyRecords()->orderByDesc('year')->orderByDesc('month')->first();
$sizeLabel = '-';
if ($latestRecord && $latestRecord->actual_kwh !== null) {
	$kwh = $latestRecord->actual_kwh;
	if ($kwh < 1500) {
		$sizeLabel = 'Small';
	} elseif ($kwh < 3000) {
		$sizeLabel = 'Medium';
	} elseif ($kwh < 6000) {
		$sizeLabel = 'Large';
	} else {
		$sizeLabel = 'Extra Large';
	}
}
?>

<?php $__currentLoopData = [
	['📍','Address',$facility->address],
	['🏘','Barangay',$facility->barangay],
	['📐','Floor Area',$facility->floor_area.' sqm'],
	['🏢','Floors',$facility->floors],
	['📅','Year Built',$facility->year_built],
	['⏱','Operating Hours',$facility->operating_hours],
	['📊','Facility Size',$sizeLabel]
]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	<div style="background:#fff;padding:18px;border-radius:16px;display:flex;gap:14px;box-shadow:0 6px 18px rgba(0,0,0,.08);">
		<div style="width:44px;height:44px;border-radius:14px;background:#2563eb1a;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#2563eb;">
			<?php echo e($info[0]); ?>

		</div>
		<div>
			<div style="font-size:.85rem;color:#64748b;font-weight:700;"><?php echo e($info[1]); ?></div>
			<div style="font-size:1.05rem;font-weight:800;color:#1e293b;"><?php echo e($info[2] ?: '-'); ?></div>
		</div>
	</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<!-- ENERGY PROFILE DETAILS -->
<?php
$profile = $facility->energyProfiles()->latest()->first();
?>
<div style="margin-top:32px;padding:26px 32px;border-radius:22px;background:linear-gradient(135deg,#f8fafc,#e0f2fe);box-shadow:0 8px 28px rgba(37,99,235,.10);">
	<h3 style="margin:0 0 18px;font-weight:900;color:#0ea5e9;font-size:1.18rem;display:flex;align-items:center;gap:8px;">
		<i class="fa fa-id-card"></i> Energy Profile Details
	</h3>
	<?php if($profile): ?>
		<?php if(!$profile->engineer_approved): ?>
			<div style="color:#64748b;font-weight:700;font-size:1.1rem;padding:18px 0;text-align:center;">
				<i class="fa fa-clock" style="color:#2563eb;font-size:1.5rem;margin-bottom:8px;"></i><br>
				Energy profile is pending approval.<br>
				Please wait for engineer approval before details are shown.
			</div>
		<?php else: ?>
			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
				<div><span style="color:#64748b;font-weight:700;">Electric Meter No:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->electric_meter_no ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Utility Provider:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->utility_provider ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Contract Account No:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->contract_account_no ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Baseline kWh:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->baseline_kwh ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Main Energy Source:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->main_energy_source ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Backup Power:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->backup_power ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Transformer Capacity:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->transformer_capacity ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Number of Meters:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->number_of_meters ?? '-'); ?></span></div>
				<div><span style="color:#64748b;font-weight:700;">Engineer Approved:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->engineer_approved ? 'Yes' : 'No'); ?></span></div>
				<div>
					<span style="color:#64748b;font-weight:700;">Baseline Locked:</span><br>
					<?php
						$role = strtolower(auth()->user()->role ?? '');
					?>
					<?php if($role === 'staff'): ?>
						<span style="display:inline-flex;align-items-center;gap:6px;font-weight:800;color:#0ea5e9;background:#e0f2fe;padding:6px 14px;border-radius:999px;" title="Baseline is locked for staff and cannot be changed.">
							<i class="fa fa-lock"></i> Locked
						</span>
					<?php else: ?>
						<span style="display:inline-flex;align-items-center;gap:6px;font-weight:800;color:#64748b;background:#f1f5f9;padding:6px 14px;border-radius:999px;" title="Baseline is editable for admin and super admin.">
							<i class="fa fa-unlock"></i> Editable
						</span>
					<?php endif; ?>
				</div>
				<div><span style="color:#64748b;font-weight:700;">Baseline Source:</span><br><span style="font-weight:800;color:#1e293b;"><?php echo e($profile->baseline_source ?? '-'); ?></span></div>
				<?php if($profile->bill_image): ?>
					<div><span style="color:#64748b;font-weight:700;">Bill Image:</span><br><img src="<?php echo e(asset('storage/'.$profile->bill_image)); ?>" alt="Bill Image" style="max-width:120px;border-radius:8px;box-shadow:0 2px 8px #2563eb22;"></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<div style="color:#64748b;font-weight:700;">No energy profile data available for this facility.</div>
	<?php endif; ?>
</div>

<!-- ENERGY SUMMARY -->
<?php
	$energyProfile = $facility->energyProfiles()->latest()->first();
	$baselineKwh = $energyProfile && is_numeric($energyProfile->baseline_kwh) ? floatval($energyProfile->baseline_kwh) : null;
	$baselineSource = $energyProfile && $energyProfile->baseline_source ? $energyProfile->baseline_source : 'Energy Profile';
	$hasBaseline = $baselineKwh > 0;
?>

<?php if($energyProfile && !$energyProfile->engineer_approved): ?>
<div style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);text-align:center;">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		⚡ Energy Performance
	</h3>
	<div style="color:#64748b;font-weight:700;font-size:1.1rem;padding:18px 0;">
		<i class="fa fa-clock" style="color:#2563eb;font-size:1.5rem;margin-bottom:8px;"></i><br>
		Energy profile is pending approval.<br>
		Please wait for engineer approval before performance details are shown.
	</div>
</div>
<?php elseif($hasBaseline): ?>
<div style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		⚡ Energy Performance
	</h3>
	<div style="font-size:1.7rem;font-weight:900;color:#2563eb;">
		<?php echo e(number_format($baselineKwh,2)); ?> kWh
	</div>
	<div style="font-size:.9rem;color:#475569;">
		Baseline consumption (<?php echo e($baselineSource); ?>)
	</div>
</div>
<?php else: ?>
<div style="margin-top:32px;padding:26px;border-radius:22px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 30px rgba(37,99,235,.15);">
	<h3 style="margin:0 0 14px;font-weight:900;color:#2563eb;">
		⚡ Energy Performance
	</h3>
	<div style="color:#b91c1c;font-weight:700;">
		⚠ Insufficient data (no baseline set in energy profile)
	</div>
</div>
<?php endif; ?>

<!-- ACTIONS -->
<div style="display:flex;gap:14px;justify-content:flex-end;margin-top:30px;">
	<!-- Edit Facility button removed -->
	<button type="button" onclick="openDeleteFacilityModal(<?php echo e($facility->id); ?>, '<?php echo e(route('facilities.destroy', $facility->id)); ?>')" style="background:#e11d48;color:#fff;padding:12px 26px;border:none;border-radius:999px;font-weight:800;cursor:pointer;">🗑 Delete</button>
</div>

</div>
</div>




<?php $__env->stopSection(); ?>
<?php echo $__env->make('modules.facilities.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
function openEditFacilityModal() {
	var facility = <?php echo json_encode($facility, 15, 512) ?>;
	document.getElementById('edit_facility_id').value = facility.id || '';
	document.getElementById('edit_name').value = facility.name || '';
	document.getElementById('edit_type').value = facility.type || '';
	document.getElementById('edit_department').value = facility.department || '';
	document.getElementById('edit_address').value = facility.address || '';
	document.getElementById('edit_barangay').value = facility.barangay || '';
	document.getElementById('edit_floor_area').value = facility.floor_area || '';
	document.getElementById('edit_floors').value = facility.floors || '';
	document.getElementById('edit_year_built').value = facility.year_built || '';
	document.getElementById('edit_operating_hours').value = facility.operating_hours || '';
	document.getElementById('edit_status').value = facility.status || '';
	// Set form action dynamically
	document.getElementById('editFacilityForm').action = '/facilities/' + facility.id;
	// Image preview
	var preview = document.getElementById('edit_image_preview');
	if (facility.image) {
		let imageUrl = '';
		if (facility.image.startsWith('img/')) {
			imageUrl = '/' + facility.image;
		} else {
			imageUrl = '/storage/' + facility.image;
		}
		preview.innerHTML = '<img src="' + imageUrl + '" style="max-width:100%;max-height:120px;border-radius:10px;">';
	} else {
		preview.innerHTML = '<div style="width:100%;height:80px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="fa fa-image"></i></div>';
	}
	document.getElementById('editFacilityModal').style.display = 'flex';
}
</script>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/facilities/show.blade.php ENDPATH**/ ?>