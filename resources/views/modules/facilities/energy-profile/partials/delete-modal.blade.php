<style>
body.dark-mode #deleteEnergyProfileModal .modal-content {
    background: #111827 !important;
    color: #e2e8f0 !important;
    border: 1px solid #334155;
}

body.dark-mode #deleteEnergyProfileModal .modal-content h2,
body.dark-mode #deleteEnergyProfileModal .modal-content div {
    color: #e2e8f0 !important;
}

body.dark-mode #deleteEnergyProfileModal .delete-modal-btn.cancel {
    background: #1f2937 !important;
    color: #e2e8f0 !important;
}
</style>

<!-- Delete Energy Profile Modal -->
<div id="deleteEnergyProfileModal" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="closeDeleteEnergyProfileModal()" style="top:12px;right:12px;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Energy Profile</h2>
        <div style="font-size:1.02rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this energy profile? This action cannot be undone.</div>
        <form id="deleteEnergyProfileForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_energy_profile_facility_id" name="facility_id">
            <input type="hidden" id="delete_energy_profile_id" name="profile_id">
            <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;">Yes, Delete</button>
            <button type="button" class="delete-modal-btn cancel" onclick="closeDeleteEnergyProfileModal()" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;">Cancel</button>
        </form>
    </div>
</div>
<script>
function openDeleteEnergyProfileModal(facilityId, profileId) {
    document.getElementById('delete_energy_profile_facility_id').value = facilityId;
    document.getElementById('delete_energy_profile_id').value = profileId;
    document.getElementById('deleteEnergyProfileModal').style.display = 'flex';
}
function closeDeleteEnergyProfileModal() {
    document.getElementById('deleteEnergyProfileModal').style.display = 'none';
}
document.getElementById('deleteEnergyProfileForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    var facilityId = document.getElementById('delete_energy_profile_facility_id').value;
    var profileId = document.getElementById('delete_energy_profile_id').value;
    fetch(`/modules/facilities/${facilityId}/energy-profile/${profileId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.redirected ? {success:true} : res.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            // alert removed for production
        }
    });
});
</script>
