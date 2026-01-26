<style>
.show-modal {
    display: flex !important;
}
.energy-modal-btn {
    transition: background 0.2s, color 0.2s;
}
.energy-modal-btn.delete {
    background: #e11d48;
    color: #fff;
}
.energy-modal-btn.delete:hover, .energy-modal-btn.delete:focus {
    background: #b91c1c;
    color: #fff;
}
.energy-modal-btn.cancel {
    background: #f3f4f6;
    color: #222;
}
.energy-modal-btn.cancel:hover, .energy-modal-btn.cancel:focus {
    background: #e5e7eb;
    color: #b91c1c;
}
.energy-modal-btn.save {
    background: #2563eb;
    color: #fff;
}
.energy-modal-btn.save:hover, .energy-modal-btn.save:focus {
    background: #1d4ed8;
    color: #fff;
}
</style>

<!-- Enhanced Add Energy Profile Modal -->
<div id="addEnergyProfileModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:9999;">
    <div class="modal-content" style="max-width:520px;background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
        <button class="modal-close" type="button" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#6366f1;cursor:pointer;">&times;</button>
        <h2 style="margin-bottom:18px;font-size:1.7rem;font-weight:700;color:#2563eb;text-align:left;">Add Energy Profile</h2>
        <form id="addEnergyProfileForm" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            @csrf
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
                    <label style="font-weight:500;margin-bottom:0.4rem;display:block;color:#222;">Average Monthly kWh</label>
                    <input type="number" name="average_monthly_kwh" step="0.01" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #d1d5db;background:#f8fafc;">
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

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeAddEnergyProfileModal()" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn save" style="width:100%;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1.1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);">Add Energy Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Energy Profile Modal -->
<div id="editEnergyProfileModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:9999;">
    <div class="modal-content" style="max-width:520px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Edit Energy Profile</h2>
        <form id="editEnergyProfileForm" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_energy_profile_id">
            <label>Electric Meter No.<input type="text" name="electric_meter_no" id="edit_electric_meter_no" required class="form-control"></label>
            <label>Utility Provider
                <select name="utility_provider" id="edit_utility_provider" required class="form-control">
                    <option value="">Select Provider</option>
                    <option value="Meralco">Meralco</option>
                    <option value="Batelec">Batelec</option>
                    <option value="FLECO">FLECO</option>
                    <option value="Other">Other</option>
                </select>
            </label>
            <label>Contract Account No.<input type="text" name="contract_account_no" id="edit_contract_account_no" required class="form-control"></label>
            <label>Average Monthly kWh<input type="number" name="average_monthly_kwh" id="edit_average_monthly_kwh" step="0.01" required class="form-control"></label>
            <label>Main Energy Source
                <select name="main_energy_source" id="edit_main_energy_source" required class="form-control">
                    <option value="">Select Source</option>
                    <option value="Electricity">Electricity</option>
                    <option value="Solar">Solar</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Other">Other</option>
                </select>
            </label>
            <label>Backup Power
                <select name="backup_power" id="edit_backup_power" required class="form-control">
                    <option value="">Select Backup</option>
                    <option value="Generator">Generator</option>
                    <option value="Battery">Battery</option>
                    <option value="None">None</option>
                    <option value="Other">Other</option>
                </select>
            </label>
            <label>Transformer Capacity
                <select name="transformer_capacity" id="edit_transformer_capacity" class="form-control">
                    <option value="">Select Capacity</option>
                    <option value="25 kVA">25 kVA</option>
                    <option value="50 kVA">50 kVA</option>
                    <option value="100 kVA">100 kVA</option>
                    <option value="250 kVA">250 kVA</option>
                    <option value="500 kVA">500 kVA</option>
                    <option value="Other">Other</option>
                </select>
            </label>
            <label>Number of Meters<input type="number" name="number_of_meters" id="edit_number_of_meters" required class="form-control"></label>

            <div style="display:flex;gap:10px;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeEditEnergyProfileModal()">Cancel</button>
                <button type="submit" class="energy-modal-btn save">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Energy Profile Modal -->
<div id="deleteEnergyProfileModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:9999;">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="closeDeleteEnergyProfileModal()" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Energy Profile</h2>
        <div id="delete_energy_profile_info" style="font-size:1.02rem;color:#b91c1c;margin-bottom:8px;"></div>
        <div style="font-size:0.98rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this energy profile? This action cannot be undone.</div>
        <form id="deleteEnergyProfileForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_energy_profile_id" name="id">
            <button type="submit" class="energy-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Yes, Delete</button>
            <button type="button" class="energy-modal-btn cancel" onclick="closeDeleteEnergyProfileModal()" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
        </form>
    </div>
</div>

<!-- Edit Facility Modal -->
<div id="editFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="max-width:440px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Edit Facility</h2>
        <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Update facility details below.</div>
        <form id="editFacilityForm" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_facility_id" name="id">
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_name" style="font-weight:500;">Name</label>
                    <input type="text" id="edit_name" name="name" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_type" style="font-weight:500;">Type</label>
                    <input type="text" id="edit_type" name="type" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_department" style="font-weight:500;">Department</label>
                    <input type="text" id="edit_department" name="department" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_address" style="font-weight:500;">Address</label>
                    <input type="text" id="edit_address" name="address" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_barangay" style="font-weight:500;">Barangay</label>
                    <input type="text" id="edit_barangay" name="barangay" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_floor_area" style="font-weight:500;">Floor Area</label>
                    <input type="number" id="edit_floor_area" name="floor_area" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_floors" style="font-weight:500;">Floors</label>
                    <input type="number" id="edit_floors" name="floors" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_year_built" style="font-weight:500;">Year Built</label>
                    <input type="number" id="edit_year_built" name="year_built" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_operating_hours" style="font-weight:500;">Operating Hours</label>
                    <input type="text" id="edit_operating_hours" name="operating_hours" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="edit_status" style="font-weight:500;">Status</label>
                    <select id="edit_status" name="status" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="font-weight:500;">Image Preview</label>
                <div id="edit_image_preview" style="margin-bottom:8px;"></div>
                <input type="file" name="image" accept="image/*" style="border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
            </div>
            <button type="submit" style="background:#2563eb;color:#fff;padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Update Facility</button>
        </form>
    </div>
</div>

<!-- Reset Baseline Modal -->
<div id="resetBaselineModal" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="max-width:370px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;">
        <button class="modal-close" type="button">&times;</button>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <span style="font-size:1.5rem;color:#2563eb;"><i class="fa fa-info-circle" title="Information"></i></span>
            <h2 style="margin:0;font-size:1.2rem;font-weight:700;color:#2563eb;">Reset Baseline</h2>
        </div>
        <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Are you sure you want to reset the baseline for this facility? Please provide a reason for audit trail.</div>
        <form id="resetBaselineForm" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" id="reset_facility_id" name="facility_id">
            <label for="reset_reason" style="font-weight:500;">Reason for reset</label>
            <textarea id="reset_reason" name="reason" placeholder="Reason for reset" required style="min-height:70px;padding:8px;border-radius:6px;border:1px solid #c3cbe5;"></textarea>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="reset-modal-btn reset" style="background:#2563eb;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;flex:1;transition:background 0.2s;">Reset Baseline</button>
                <button type="button" class="reset-modal-btn cancel" onclick="document.getElementById('resetBaselineModal').style.display='none'" style="background:#f3f4f6;color:#222;padding:10px 0;border:none;border-radius:8px;font-weight:600;flex:1;transition:background 0.2s;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Facility Modal -->
<div id="deleteFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="document.getElementById('deleteFacilityModal').style.display='none'" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Facility</h2>
        <div style="font-size:1.02rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this facility? This action cannot be undone.</div>
        <form id="deleteFacilityForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_facility_id" name="facility_id">
            <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Yes, Delete</button>
            <button type="button" class="delete-modal-btn cancel" onclick="document.getElementById('deleteFacilityModal').style.display='none'" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
        </form>
    </div>
</div>
