<style>
.show-modal { display: flex !important; }
.energy-modal-btn { transition: background 0.2s, color 0.2s; }
.energy-modal-btn.delete { background: #e11d48; color: #fff; }
.energy-modal-btn.delete:hover { background: #b91c1c; }
.energy-modal-btn.cancel { background: #f3f4f6; color: #222; }
.energy-modal-btn.cancel:hover { background: #e5e7eb; color: #b91c1c; }
.energy-modal-btn.save { background: #2563eb; color: #fff; }
.energy-modal-btn.save:hover { background: #1d4ed8; }
</style>

<!-- ADD ENERGY PROFILE MODAL -->
<div id="addEnergyProfileModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
	<div class="modal-content" style="max-width:520px;background:#fff;border-radius:18px;padding:32px 28px;position:relative;">
		<button class="modal-close" type="button" onclick="closeAddEnergyProfileModal()" style="position:absolute;top:18px;right:18px;font-size:1.7rem;border:none;background:none;">&times;</button>

		<h2 style="margin-bottom:18px;font-size:1.7rem;font-weight:700;color:#2563eb;">Add Energy Profile</h2>

		<form method="POST" style="display:flex;flex-direction:column;gap:18px;">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="facility_id" id="add_energy_facility_id">

			<div style="display:flex;gap:14px;">
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Electric Meter No.</label>
					<input type="text" name="electric_meter_no" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
				</div>
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Utility Provider</label>
					<select name="utility_provider" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
						<option value="">Select Provider</option>
						<option value="Meralco">Meralco</option>
						<option value="Batelec">Batelec</option>
						<option value="FLECO">FLECO</option>
						<option value="Other">Other</option>
					</select>
				</div>
			</div>
			<div style="display:flex;gap:14px;">
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Contract Account No.</label>
					<input type="text" name="contract_account_no" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
				</div>
				<div style="flex:1;">
                    <label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Baseline kWh</label>
                    <input type="number" name="baseline_kwh" step="0.01" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
                </div>
			</div>
			<div style="display:flex;gap:14px;">
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Main Energy Source</label>
					<select name="main_energy_source" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
						<option value="">Select Source</option>
						<option value="Electricity">Electricity</option>
						<option value="Solar">Solar</option>
						<option value="Diesel">Diesel</option>
						<option value="Other">Other</option>
					</select>
				</div>
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Backup Power</label>
					<select name="backup_power" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
						<option value="">Select Backup</option>
						<option value="Generator">Generator</option>
						<option value="Battery">Battery</option>
						<option value="None">None</option>
						<option value="Other">Other</option>
					</select>
				</div>
			</div>
			<div style="display:flex;gap:14px;">
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Transformer Capacity</label>
					<select name="transformer_capacity" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
						<option value="">Select Capacity</option>
						<option value="25 kVA">25 kVA</option>
						<option value="50 kVA">50 kVA</option>
						<option value="100 kVA">100 kVA</option>
						<option value="250 kVA">250 kVA</option>
						<option value="500 kVA">500 kVA</option>
						<option value="Other">Other</option>
					</select>
				</div>
				<div style="flex:1;">
					<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Number of Meters</label>
					<input type="number" name="number_of_meters" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
				</div>
			</div>
			<div>
				<label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Baseline Source</label>
				<select name="baseline_source" required style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
					<option value="">Select Source</option>
					<option value="historical_data">Historical Data</option>
					<option value="manual_entry">Manual Entry</option>
					<option value="utility_bill">Utility Bill</option>
					<option value="system_estimate">System Estimate</option>
					<option value="initial_survey">Initial Survey</option>
					<option value="other">Other</option>
				</select>
			</div>
			<div style="display:flex;gap:12px;margin-top:8px;">
				<button type="button" class="energy-modal-btn cancel" onclick="closeAddEnergyProfileModal()" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
				<button type="submit" class="energy-modal-btn save" style="width:100%;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1.1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);">Add Energy Profile</button>
			</div>
		</form>
	</div>
</div>



<!-- DELETE ENERGY PROFILE MODAL -->
<div id="deleteEnergyProfileModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:10000;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="modal-content" style="max-width:400px;background:#fff;border-radius:16px;padding:32px 28px;position:relative;box-shadow:0 4px 24px rgba(0,0,0,0.10);display:flex;flex-direction:column;align-items:center;">
        <button class="modal-close" type="button" onclick="closeDeleteEnergyProfileModal()" style="position:absolute;top:18px;right:18px;font-size:1.7rem;border:none;background:none;">&times;</button>
        <h2 style="margin-bottom:18px;font-size:1.3rem;font-weight:700;color:#e11d48;text-align:center;">Delete Energy Profile?</h2>
        <p style="color:#64748b;font-size:1.05rem;text-align:center;margin-bottom:24px;">Are you sure you want to delete this energy profile? This action cannot be undone.</p>
        <form method="POST" id="deleteEnergyProfileForm" style="width:100%;display:flex;flex-direction:column;gap:14px;align-items:center;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <input type="hidden" name="energy_profile_id" id="delete_energy_profile_id">
            <div style="display:flex;gap:12px;justify-content:center;width:100%;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeDeleteEnergyProfileModal()" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn delete" style="padding:10px 22px;border-radius:8px;font-weight:600;font-size:1.05rem;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddEnergyProfileModal(facilityId) {
	document.getElementById('addEnergyProfileModal').style.display = 'flex';
	document.getElementById('add_energy_facility_id').value = facilityId;
}
function closeAddEnergyProfileModal() {
	document.getElementById('addEnergyProfileModal').style.display = 'none';
}

// Robust fallback: always close modal on X or Cancel click
document.addEventListener('DOMContentLoaded', function() {
	var modal = document.getElementById('addEnergyProfileModal');
	if (modal) {
		// X button
		var xBtn = modal.querySelector('.modal-close');
		if (xBtn) {
			xBtn.addEventListener('click', closeAddEnergyProfileModal);
		}
		// Cancel button
		var cancelBtn = modal.querySelector('.energy-modal-btn.cancel');
		if (cancelBtn) {
			cancelBtn.addEventListener('click', closeAddEnergyProfileModal);
		}
	}
});

function openAddMaintenanceModal(facilityId) {
    document.getElementById('addMaintenanceModal').style.display = 'flex';
    document.getElementById('add_maintenance_facility_id').value = facilityId;
}
function closeAddMaintenanceModal() {
    document.getElementById('addMaintenanceModal').style.display = 'none';
}

function openDeleteEnergyProfileModal(profileId) {
    document.getElementById('deleteEnergyProfileModal').style.display = 'flex';
    document.getElementById('delete_energy_profile_id').value = profileId;
}
function closeDeleteEnergyProfileModal() {
    document.getElementById('deleteEnergyProfileModal').style.display = 'none';
}
</script>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/energy-profile/partials/modals.blade.php ENDPATH**/ ?>