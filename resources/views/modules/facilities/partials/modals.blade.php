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

#addFacilityModal,
#editFacilityModal {
    padding: 12px;
    box-sizing: border-box;
}

#addFacilityModal .facility-form-modal,
#editFacilityModal .facility-form-modal {
    width: min(760px, calc(100vw - 24px)) !important;
    max-width: 760px !important;
    max-height: calc(100vh - 24px) !important;
    padding: 0 !important;
    display: flex;
    flex-direction: column;
    overflow: hidden !important;
    border: 1px solid #dbe5f2;
    border-radius: 22px !important;
    box-shadow: 0 28px 80px rgba(15,23,42,.30) !important;
}

.facility-form-header {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 22px 68px 20px 24px;
    background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
    border-bottom: 1px solid #dbe5f2;
}

.facility-form-header-icon {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: linear-gradient(135deg,#2563eb,#6366f1);
    color: #fff;
    box-shadow: 0 9px 20px rgba(79,70,229,.20);
}

.facility-form-header h2 {
    margin: 0;
    color: #0f172a !important;
    font-size: 1.25rem;
    font-weight: 900;
    letter-spacing: -.02em;
}

.facility-form-header p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: .84rem;
    font-weight: 600;
    line-height: 1.4;
}

.facility-form-close {
    position: absolute;
    z-index: 5;
    top: 18px;
    right: 18px;
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dbe5f2 !important;
    border-radius: 11px;
    background: rgba(255,255,255,.9) !important;
    color: #64748b;
    font-size: 1rem !important;
    cursor: pointer;
}

.facility-form-close:hover { background:#fff1f2 !important; border-color:#fecdd3 !important; color:#e11d48; }

#addFacilityForm,
#editFacilityForm {
    flex: 1 1 auto;
    min-height: 0;
    gap: 14px !important;
    padding: 20px 26px 0;
    overflow-y: auto;
}

.facility-form-section-title {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 5px 0 -2px;
    color: #475569;
    font-size: .73rem;
    font-weight: 850;
    letter-spacing: .07em;
    text-transform: uppercase;
}

.facility-form-section-title i { color:#2563eb; }

#addFacilityForm > div[style*="display:flex"][style*="flex-wrap:wrap"]:not(.facility-form-actions),
#editFacilityForm > div[style*="display:flex"][style*="flex-wrap:wrap"]:not(.facility-form-actions) {
    display: grid !important;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 14px 16px !important;
}

#addFacilityForm > div > div,
#editFacilityForm > div > div {
    min-width: 0 !important;
}

#addFacilityForm label,
#editFacilityForm label {
    color: #334155;
    font-size: .78rem;
    font-weight: 800 !important;
}

#addFacilityForm input,
#addFacilityForm select,
#editFacilityForm input,
#editFacilityForm select {
    min-height: 45px;
    box-sizing: border-box;
    padding: 10px 12px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 11px !important;
    background: #fff;
    color: #0f172a;
    font-size: .88rem !important;
}

#addFacilityForm input:focus,
#addFacilityForm select:focus,
#editFacilityForm input:focus,
#editFacilityForm select:focus {
    outline: none;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

#addFacilityForm input[type="file"],
#editFacilityForm input[type="file"] {
    min-height: auto;
    background: #f8fafc;
}

#addFacilityForm > div > div:has(#add_status),
#addFacilityForm > div > div:has(#add_operating_hours) {
    grid-column: 1 / -1;
}

.facility-form-actions {
    position: sticky;
    z-index: 4;
    bottom: 0;
    margin: 20px -26px 0 !important;
    padding: 14px 26px;
    flex-wrap: nowrap !important;
    border-top: 1px solid #e2e8f0;
    background: rgba(255,255,255,.97);
    backdrop-filter: blur(8px);
}

.facility-form-actions .energy-modal-btn {
    min-height: 44px;
    padding: 10px 19px !important;
    border-radius: 11px !important;
    font-size: .86rem !important;
    font-weight: 850 !important;
    cursor: pointer;
}

.facility-form-actions .cancel {
    flex: 0 0 auto;
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #dbe5f2 !important;
}

.facility-form-actions .save {
    flex: 1;
    background: #2563eb !important;
    box-shadow: 0 7px 16px rgba(37,99,235,.20) !important;
}

.facility-form-actions .save:hover { background:#1d4ed8 !important; }

body.dark-mode .facility-form-header { background:linear-gradient(135deg,#111827,#172033); border-color:#2a3850; }
body.dark-mode .facility-form-header h2 { color:#f8fafc !important; }
body.dark-mode .facility-form-header p { color:#94a3b8; }
body.dark-mode .facility-form-close { background:#111827 !important; border-color:#334155 !important; color:#cbd5e1; }
body.dark-mode .facility-form-section-title { color:#cbd5e1; }
body.dark-mode .facility-form-actions { background:rgba(15,23,42,.97); border-color:#2a3850; }

@media (max-width: 600px) {
    #addFacilityModal,
    #editFacilityModal { padding: 6px; }
    #addFacilityModal .facility-form-modal,
    #editFacilityModal .facility-form-modal { width:calc(100vw - 12px) !important; max-height:calc(100vh - 12px) !important; border-radius:17px !important; }
    .facility-form-header { padding:17px 55px 16px 16px; }
    .facility-form-header-icon { width:42px; height:42px; flex-basis:42px; border-radius:12px; }
    #addFacilityForm,
    #editFacilityForm { padding:16px 16px 0; }
    #addFacilityForm > div[style*="display:flex"][style*="flex-wrap:wrap"]:not(.facility-form-actions),
    #editFacilityForm > div[style*="display:flex"][style*="flex-wrap:wrap"]:not(.facility-form-actions) { grid-template-columns:1fr; }
    #addFacilityForm > div > div:has(#add_status),
    #addFacilityForm > div > div:has(#add_operating_hours) { grid-column:auto; }
    .facility-form-actions { margin:18px -16px 0 !important; padding:12px 16px; }
    .facility-form-actions .energy-modal-btn { flex:1; }
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
    <div class="modal-content facility-form-modal" role="dialog" aria-modal="true" aria-labelledby="addFacilityTitle" aria-describedby="addFacilitySubtitle" style="max-width:520px;width:95vw;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:36px 32px;max-height:92vh;overflow-y:auto;position:relative;">
        <button class="modal-close facility-form-close" type="button" onclick="closeAddFacilityModal()" aria-label="Close add facility form"><i class="fa-solid fa-xmark"></i></button>
        <header class="facility-form-header">
            <div class="facility-form-header-icon"><i class="fa-solid fa-building-circle-check"></i></div>
            <div><h2 id="addFacilityTitle">Add Facility</h2><p id="addFacilitySubtitle">Create a facility profile and configure its operating details.</p></div>
        </header>
        <form id="addFacilityForm" action="{{ route('facilities.store') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            @if ($errors->any())
                <div role="alert" style="background:#fee2e2;color:#b91c1c;padding:10px 16px;border-radius:8px;font-size:.88rem;margin-bottom:8px;border:1px solid #fecaca;">
                    <strong>There were some problems with your input:</strong>
                    <ul style="margin:8px 0 0 18px;padding:0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf
            <div class="facility-form-section-title"><i class="fa-solid fa-building"></i> Basic Information</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_name" style="font-weight:600;">Name <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_name" name="name" value="{{ old('name') }}" required autocomplete="organization" placeholder="e.g. LGU Health Office" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_type" style="font-weight:600;">Type <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_type" name="type" value="{{ old('type') }}" required placeholder="e.g. Government Office" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_department" style="font-weight:600;">Department</label>
                    <input type="text" id="add_department" name="department" value="{{ old('department') }}" placeholder="e.g. City Health Department" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_address" style="font-weight:600;">Address <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_address" name="address" value="{{ old('address') }}" required autocomplete="street-address" placeholder="Street, municipality, province" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_status" style="font-weight:600;">Status <span style='color:#e11d48;'>*</span></label>
                    <select id="add_status" name="status" required style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                        <option value="active" @selected(old('status', $defaultFacilityStatus ?? 'active') === 'active')>Active</option>
                        <option value="maintenance" @selected(old('status', $defaultFacilityStatus ?? 'active') === 'maintenance')>Maintenance</option>
                        <option value="inactive" @selected(old('status', $defaultFacilityStatus ?? 'active') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="facility-form-section-title"><i class="fa-solid fa-location-dot"></i> Location &amp; Building</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_barangay" style="font-weight:600;">Barangay <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="add_barangay" name="barangay" value="{{ old('barangay') }}" required autocomplete="address-level3" placeholder="Barangay" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_floor_area" style="font-weight:600;">Floor Area (sqm)</label>
                    <input type="number" id="add_floor_area" name="floor_area" value="{{ old('floor_area') }}" min="0" step="0.01" inputmode="decimal" placeholder="e.g. 1250" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_operating_hours" style="font-weight:600;">Operating Hours</label>
                    <input type="text" id="add_operating_hours" name="operating_hours" value="{{ old('operating_hours') }}" placeholder="e.g. 8:00 AM – 5:00 PM" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_floors" style="font-weight:600;">Floors</label>
                    <input type="number" id="add_floors" name="floors" value="{{ old('floors') }}" min="0" step="1" inputmode="numeric" placeholder="e.g. 3" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_year_built" style="font-weight:600;">Year Built</label>
                    <input type="number" id="add_year_built" name="year_built" value="{{ old('year_built') }}" min="1900" max="{{ now()->year }}" step="1" inputmode="numeric" placeholder="e.g. {{ now()->year - 10 }}" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div class="facility-form-section-title"><i class="fa-solid fa-image"></i> Facility Image</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="add_image" style="font-weight:600;">Image</label>
                    <input type="file" id="add_image" name="image" accept="{{ collect($facilityAllowedImageTypes)->map(fn ($type) => '.'.$type)->implode(',') }}" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                    <small style="color:#64748b;">Accepted: {{ strtoupper(implode(', ', $facilityAllowedImageTypes)) }}. Maximum: {{ $facilityImageMaxMb }} MB.</small>
                </div>
            </div>
            <div class="facility-form-actions" style="display:flex;gap:14px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeAddFacilityModal()" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn save" style="flex:1;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:700;border:none;font-size:1.13rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);"><i class="fa-solid fa-plus"></i> Add Facility</button>
            </div>
        </form>
    </div>
</div>
<script>
function openAddFacilityModal() {
    const modal = document.getElementById('addFacilityModal');
    const form = document.getElementById('addFacilityForm');
    if (!modal) return;
    modal.style.display = 'flex';
    if (form) form.scrollTop = 0;
    window.requestAnimationFrame(() => document.getElementById('add_name')?.focus());
}
function closeAddFacilityModal() {
    const modal = document.getElementById('addFacilityModal');
    if (modal) modal.style.display = 'none';
}
function closeEditFacilityModal() {
    var modal = document.getElementById('editFacilityModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;
    const editModal = document.getElementById('editFacilityModal');
    const addModal = document.getElementById('addFacilityModal');
    if (editModal?.style.display === 'flex') closeEditFacilityModal();
    else if (addModal?.style.display === 'flex') closeAddFacilityModal();
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#addFacilityModal, #editFacilityModal').forEach((modal) => {
        modal.addEventListener('click', function (event) {
            if (event.target !== modal) return;
            modal.id === 'addFacilityModal' ? closeAddFacilityModal() : closeEditFacilityModal();
        });
    });

@if ($errors->any())
    openAddFacilityModal();
@endif
});
</script>

<!-- Edit Facility Modal -->
<div id="editFacilityModal" class="modal-overlay" style="display:none;align-items:center;justify-content:center;z-index:10050;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="modal-content facility-form-modal" role="dialog" aria-modal="true" aria-labelledby="editFacilityTitle" aria-describedby="editFacilitySubtitle" style="max-width:520px;width:95vw;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:36px 32px;max-height:92vh;overflow-y:auto;position:relative;">
        <button class="modal-close facility-form-close" type="button" onclick="closeEditFacilityModal()" aria-label="Close edit facility form"><i class="fa-solid fa-xmark"></i></button>
        <header class="facility-form-header">
            <div class="facility-form-header-icon"><i class="fa-solid fa-building-pen"></i></div>
            <div><h2 id="editFacilityTitle">Edit Facility</h2><p id="editFacilitySubtitle">Update the facility profile and operating details.</p></div>
        </header>
        <form id="editFacilityForm" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:18px;">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_facility_id" name="facility_id">
            <div class="facility-form-section-title"><i class="fa-solid fa-building"></i> Basic Information</div>
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
            <div class="facility-form-section-title"><i class="fa-solid fa-location-dot"></i> Location &amp; Building</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_barangay" style="font-weight:600;">Barangay <span style='color:#e11d48;'>*</span></label>
                    <input type="text" id="edit_barangay" name="barangay" required placeholder="Barangay" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_floor_area" style="font-weight:600;">Floor Area (sqm)</label>
                    <input type="number" id="edit_floor_area" name="floor_area" min="0" step="0.01" inputmode="decimal" placeholder="e.g. 1250" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_floors" style="font-weight:600;">Floors</label>
                    <input type="number" id="edit_floors" name="floors" min="0" step="1" inputmode="numeric" placeholder="e.g. 3" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_year_built" style="font-weight:600;">Year Built</label>
                    <input type="number" id="edit_year_built" name="year_built" min="1900" max="{{ now()->year }}" step="1" inputmode="numeric" placeholder="e.g. {{ now()->year - 10 }}" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                </div>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div style="flex:1;display:flex;flex-direction:column;gap:6px;min-width:180px;">
                    <label for="edit_operating_hours" style="font-weight:600;">Operating Hours</label>
                    <input type="text" id="edit_operating_hours" name="operating_hours" placeholder="e.g. 8:00 AM – 5:00 PM" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
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
            <div class="facility-form-section-title"><i class="fa-solid fa-image"></i> Facility Image</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="edit_image" style="font-weight:600;">Image</label>
                <input type="file" id="edit_image" name="image" accept="{{ collect($facilityAllowedImageTypes)->map(fn ($type) => '.'.$type)->implode(',') }}" style="width:100%;border-radius:8px;border:1px solid #c3cbe5;padding:9px 12px;font-size:1.08rem;">
                <small style="color:#64748b;">Accepted: {{ strtoupper(implode(', ', $facilityAllowedImageTypes)) }}. Maximum: {{ $facilityImageMaxMb }} MB.</small>
                <div id="edit_image_error" style="{{ $errors->has('image') ? '' : 'display:none;' }}color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 10px;font-size:.88rem;font-weight:700;">
                    {{ $errors->first('image') }}
                </div>
                <div id="edit_image_preview" style="margin-top:8px;"></div>
            </div>
            <div class="facility-form-actions" style="display:flex;gap:14px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="energy-modal-btn cancel" onclick="closeEditFacilityModal()" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:8px;padding:10px 22px;">Cancel</button>
                <button type="submit" class="energy-modal-btn save" style="flex:1;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:700;border:none;font-size:1.13rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
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
                <div style="font-size:1.02rem;color:#b91c1c;margin-bottom:18px;">Delete this facility? It will be moved to Archive. Related records are preserved and can be restored later.</div>
                <form id="deleteFacilityForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
                        @csrf
                        @method('DELETE')
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label for="archive_reason" style="font-weight:700;color:#334155;">Reason for Delete <span style="color:#e11d48;">*</span></label>
                            <textarea
                                id="archive_reason"
                                name="archive_reason"
                                rows="3"
                                maxlength="500"
                                required
                                placeholder="State why this facility is being deleted to archive (e.g. duplicate, decommissioned, transferred, closed)."
                                style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                            >{{ old('archive_reason') }}</textarea>
                            <div style="font-size:0.86rem;color:#64748b;">Required. This will appear in the Facility Archive record.</div>
                        </div>
                        <button type="button" class="delete-modal-btn cancel" onclick="document.getElementById('deleteFacilityModal').style.display='none'" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
                        <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Delete</button>
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
    var reasonInput = document.getElementById('archive_reason');
    if (form) {
        if (route) {
            form.action = route;
        } else {
            form.action = '/modules/facilities/' + facilityId;
        }
    }
    if (reasonInput) {
        reasonInput.value = '';
    }
    document.getElementById('deleteFacilityModal').style.display = 'flex';
}
</script>
