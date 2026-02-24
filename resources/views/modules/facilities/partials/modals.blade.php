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

body.dark-mode #addFacilityModal .modal-content,
body.dark-mode #editFacilityModal .modal-content,
body.dark-mode #resetBaselineModal .modal-content,
body.dark-mode #deleteFacilityModal .modal-content {
    background: #111827 !important;
    color: #e2e8f0 !important;
    border: 1px solid #334155;
}

body.dark-mode #addFacilityModal label,
body.dark-mode #editFacilityModal label,
body.dark-mode #resetBaselineModal label,
body.dark-mode #addFacilityModal h2,
body.dark-mode #editFacilityModal h2,
body.dark-mode #resetBaselineModal h2 {
    color: #e2e8f0 !important;
}

body.dark-mode #addFacilityModal input,
body.dark-mode #addFacilityModal select,
body.dark-mode #addFacilityModal textarea,
body.dark-mode #editFacilityModal input,
body.dark-mode #editFacilityModal select,
body.dark-mode #editFacilityModal textarea,
body.dark-mode #resetBaselineModal textarea {
    background: #0b1220 !important;
    color: #e2e8f0 !important;
    border-color: #334155 !important;
}

body.dark-mode #addFacilityModal .energy-modal-btn.cancel,
body.dark-mode #editFacilityModal .energy-modal-btn.cancel,
body.dark-mode #resetBaselineModal .reset-modal-btn.cancel,
body.dark-mode #deleteFacilityModal .delete-modal-btn.cancel {
    background: #1f2937 !important;
    color: #e2e8f0 !important;
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
<div id="addFacilityModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:10050;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="modal-content" style="max-width:520px;width:95vw;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:36px 32px;max-height:92vh;overflow-y:auto;position:relative;">
        <button class="modal-close" type="button" onclick="closeAddFacilityModal()" style="position:absolute;top:18px;right:18px;font-size:1.7rem;border:none;background:none;">&times;</button>
        <h2 style="margin-bottom:8px;font-size:1.7rem;font-weight:900;color:#2563eb;letter-spacing:-1px;">Add Facility</h2>
        <div style="font-size:1.08rem;color:#64748b;margin-bottom:18px;">Enter new facility details below. Fields marked with <span style='color:#e11d48;'>*</span> are required.</div>
        <form id="addFacilityForm" action="/facilities" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            @if ($errors->any())
                <div style="background:#fee2e2;color:#b91c1c;padding:10px 16px;border-radius:8px;font-size:1.05rem;margin-bottom:8px;">
                    <strong>There were some problems with your input:</strong>
                    <ul style="margin:8px 0 0 18px;padding:0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_name" style="font-weight:600;">Name <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_name" name="name" required placeholder="Facility Name" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_type" style="font-weight:600;">Type <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_type" name="type" required placeholder="Facility Type" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_department" style="font-weight:600;">Department</label>
                    <input type="text" id="add_department" name="department" placeholder="Department" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_address" style="font-weight:600;">Address <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_address" name="address" required placeholder="Address" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_status" style="font-weight:600;">Status <span style='color:#e11d48;'>*</span></label>
                    <select id="add_status" name="status" required style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_barangay" style="font-weight:600;">Barangay <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_barangay" name="barangay" required placeholder="Barangay" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_floor_area" style="font-weight:600;">Floor Area (sqm)</label>
                    <input type="number" id="add_floor_area" name="floor_area" placeholder="Floor Area" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_operating_hours" style="font-weight:600;">Operating Hours</label>
                    <input type="text" id="add_operating_hours" name="operating_hours" placeholder="e.g. 8:00 AM - 5:00 PM" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_floors" style="font-weight:600;">Floors</label>
                    <input type="number" id="add_floors" name="floors" placeholder="Floors" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_year_built" style="font-weight:600;">Year Built</label>
                    <input type="number" id="add_year_built" name="year_built" placeholder="Year Built" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_image" style="font-weight:600;">Image</label>
                    <input type="file" id="add_image" name="image" accept="image/*" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:14px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeAddFacilityModal()" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn save" style="flex:1;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:700;border:none;font-size:1.13rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);">Add Facility</button>
            </div>
        </form>
    </div>
</div>
<script>
function openAddFacilityModal() {
    document.getElementById('addFacilityModal').style.display = 'flex';
}
function closeAddFacilityModal() {
    document.getElementById('addFacilityModal').style.display = 'none';
}
function closeEditFacilityModal() {
    var modal = document.getElementById('editFacilityModal');
    if (modal) modal.style.display = 'none';
}
</script>

<!-- Edit Facility Modal -->
<div id="editFacilityModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:10050;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="modal-content" style="max-width:520px;width:95vw;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:36px 32px;max-height:92vh;overflow-y:auto;position:relative;">
        <button class="modal-close" type="button" onclick="closeEditFacilityModal()" style="position:absolute;top:18px;right:18px;font-size:1.7rem;border:none;background:none;">&times;</button>
        <h2 style="margin-bottom:8px;font-size:1.7rem;font-weight:900;color:#2563eb;letter-spacing:-1px;">Edit Facility</h2>
        <div style="font-size:1.08rem;color:#64748b;margin-bottom:18px;">Update facility details below. Fields marked with <span style='color:#e11d48;'>*</span> are required.</div>
        <form id="editFacilityForm" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_facility_id" name="facility_id">
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_name" style="font-weight:600;">Name <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="edit_name" name="name" required placeholder="Facility Name" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_type" style="font-weight:600;">Type <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="edit_type" name="type" required placeholder="Facility Type" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_department" style="font-weight:600;">Department</label>
                    <input type="text" id="edit_department" name="department" placeholder="Department" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_address" style="font-weight:600;">Address <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="edit_address" name="address" required placeholder="Address" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_barangay" style="font-weight:600;">Barangay <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="edit_barangay" name="barangay" required placeholder="Barangay" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_floor_area" style="font-weight:600;">Floor Area (sqm)</label>
                    <input type="number" id="edit_floor_area" name="floor_area" placeholder="Floor Area" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_floors" style="font-weight:600;">Floors</label>
                    <input type="number" id="edit_floors" name="floors" placeholder="Floors" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_year_built" style="font-weight:600;">Year Built</label>
                    <input type="number" id="edit_year_built" name="year_built" placeholder="Year Built" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_operating_hours" style="font-weight:600;">Operating Hours</label>
                    <input type="text" id="edit_operating_hours" name="operating_hours" placeholder="e.g. 8am-5pm" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_status" style="font-weight:600;">Status</label>
                    <select id="edit_status" name="status" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="edit_image" style="font-weight:600;">Image</label>
                <input type="file" id="edit_image" name="image" accept="image/*" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                <div id="edit_image_preview" style="margin-top:8px;"></div>
            </div>
            <div style="display:flex;gap:14px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeEditFacilityModal()" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn save" style="flex:1;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:700;border:none;font-size:1.13rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);">Save Changes</button>
            </div>
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

<!-- Delete Facility Modal (centered, fixed, non-interactive background) -->
<style>
#deleteFacilityModal {
    display: none;
    position: fixed;
    z-index: 99999;
    left: 0; top: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.18);
    justify-content: center;
    align-items: center;
    pointer-events: auto;
}
#deleteFacilityModal .modal-content {
    max-width: 350px;
    background: #fff7f7;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(225,29,72,0.13);
    padding: 32px 28px;
    position: relative;
    margin: 0;
}
#deleteFacilityModal .modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #e11d48;
    cursor: pointer;
}
#deleteFacilityModal.open {
    display: flex !important;
}
</style>
<div id="deleteFacilityModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
        <div class="modal-content">
                <button class="modal-close" type="button" onclick="document.getElementById('deleteFacilityModal').style.display='none'">&times;</button>
                <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Facility</h2>
                <div style="font-size:1.02rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this facility? This action cannot be undone.</div>
                <form id="deleteFacilityForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Yes, Delete</button>
                        <button type="button" class="delete-modal-btn cancel" onclick="document.getElementById('deleteFacilityModal').style.display='none'" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
                </form>
        </div>
</div>
<script>
// Call this function and pass the facility ID before showing the modal
/**
 * Show delete modal and set form action dynamically.
 * @param {number|string} facilityId
 * @param {string} [route] Optional. If provided, will use this as the form action. Otherwise, defaults to /modules/facilities/{id}
 */
function openDeleteFacilityModal(facilityId, route) {
    var form = document.getElementById('deleteFacilityForm');
    if (form) {
        if (route) {
            form.action = route;
        } else {
            form.action = '/modules/facilities/' + facilityId;
        }
    }
    document.getElementById('deleteFacilityModal').style.display = 'flex';
}
</script>
