<style>
.delete-modal-btn {
    transition: background 0.2s, color 0.2s;
}
.delete-modal-btn.delete {
    background: #e11d48;
    color: #fff;
}
.delete-modal-btn.delete:hover, .delete-modal-btn.delete:focus {
    background: #b91c1c;
    color: #fff;
}
.delete-modal-btn.cancel {
    background: #f3f4f6;
    color: #222;
}
.delete-modal-btn.cancel:hover, .delete-modal-btn.cancel:focus {
    background: #e5e7eb;
    color: #b91c1c;
}
.reset-modal-btn.reset {
    background: #2563eb !important;
    color: #fff !important;
    transition: background 0.2s, color 0.2s;
}
.reset-modal-btn.reset:hover, .reset-modal-btn.reset:focus {
    background: #1d4ed8 !important;
    color: #fff !important;
}
.reset-modal-btn.cancel {
    background: #f3f4f6 !important;
    color: #222 !important;
    transition: background 0.2s, color 0.2s;
}
.reset-modal-btn.cancel:hover, .reset-modal-btn.cancel:focus {
    background: #e5e7eb !important;
    color: #2563eb !important;
}
</style>

<!-- Modal placeholder for facilities (customize as needed) -->
<div id="facilityModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Facility Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="document.getElementById('facilityModal').style.display='none'">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Facility details will be loaded here dynamically.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="document.getElementById('facilityModal').style.display='none'">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Facility Modal -->
<div id="addFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:10050 !important;pointer-events:auto !important;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
    <div class="modal-content" style="max-width:440px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Add Facility</h2>
        <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Enter new facility details below.</div>
        <form id="addFacilityForm" action="/facilities" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
            <?php echo csrf_field(); ?>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_name" style="font-weight:500;">Name</label>
                    <input type="text" id="add_name" name="name" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_type" style="font-weight:500;">Type</label>
                    <input type="text" id="add_type" name="type" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_department" style="font-weight:500;">Department</label>
                    <input type="text" id="add_department" name="department" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_address" style="font-weight:500;">Address</label>
                    <input type="text" id="add_address" name="address" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_barangay" style="font-weight:500;">Barangay</label>
                    <input type="text" id="add_barangay" name="barangay" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_floor_area" style="font-weight:500;">Floor Area</label>
                    <input type="number" id="add_floor_area" name="floor_area" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_floors" style="font-weight:500;">Floors</label>
                    <input type="number" id="add_floors" name="floors" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_year_built" style="font-weight:500;">Year Built</label>
                    <input type="number" id="add_year_built" name="year_built" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
            </div>
            <div style="display:flex;gap:12px;">
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_operating_hours" style="font-weight:500;">Operating Hours</label>
                    <input type="text" id="add_operating_hours" name="operating_hours" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                    <label for="add_status" style="font-weight:500;">Status</label>
                    <select id="add_status" name="status" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label for="add_image" style="font-weight:500;">Image</label>
                <input type="file" id="add_image" name="image" accept="image/*" style="border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
            </div>
            <button type="submit" style="background:#2563eb;color:#fff;padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Add Facility</button>
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
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
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
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <input type="hidden" id="delete_facility_id" name="facility_id">
            <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Yes, Delete</button>
            <button type="button" class="delete-modal-btn cancel" onclick="document.getElementById('deleteFacilityModal').style.display='none'" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
        </form>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/partials/modals.blade.php ENDPATH**/ ?>