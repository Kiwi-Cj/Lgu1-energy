<!-- Add Energy Profile Modal -->
<div id="addEnergyProfileModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;text-align:left;">Add Energy Profile</h2>
        <form id="addEnergyProfileForm" action="{{ url('/modules/facilities/' . ($facilityModel->id ?? $facility->id ?? '') . '/energy-profile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Electric Meter No.</label>
                <input type="text" name="electric_meter_no" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Utility Provider</label>
                <input type="text" name="utility_provider" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Contract Account No.</label>
                <input type="text" name="contract_account_no" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Average Monthly kWh</label>
                <input type="number" name="average_monthly_kwh" step="0.01" required id="average_monthly_kwh_profile" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;" readonly>
            </div>
            <div style="margin-bottom:1.2rem;display:flex;gap:12px;">
                <div style="flex:1;">
                    <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Main Energy Source</label>
                    <input type="text" name="main_energy_source" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                </div>
                <div style="flex:1;">
                    <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Backup Power</label>
                    <input type="text" name="backup_power" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                </div>
            </div>
            <div style="margin-bottom:1.2rem;display:flex;gap:12px;">
                <div style="flex:1;">
                    <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Transformer Capacity</label>
                    <input type="text" name="transformer_capacity" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                </div>
                <div style="flex:1;">
                    <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Number of Meters</label>
                    <input type="number" name="number_of_meters" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:12px;">
                <button type="button" class="modal-close" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Cancel</button>
                <button type="submit" style="width:100%;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1.1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:0;">Add Energy Profile</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); z-index:9999; align-items:center; justify-content:center; }
.modal-content { width:100%; max-width:520px; background:#fff; padding:28px; border-radius:18px; box-shadow:0 8px 32px rgba(31,38,135,0.13); max-height:90vh; overflow-y:auto; position:relative; }
.modal-close { position:absolute; top:18px; right:18px; background:none; border:none; font-size:1.5rem; color:#6366f1; cursor:pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addModal = document.getElementById('addEnergyProfileModal');
    document.querySelectorAll('.btn-add-energy-profile').forEach(btnAdd => {
        btnAdd.addEventListener('click', function(e) {
            e.preventDefault();
            addModal.style.display = 'flex';
            // Get facility id from data attribute or button context
            let facilityId = btnAdd.getAttribute('data-facility-id');
            if (!facilityId) {
                // fallback: try to extract from form action
                const form = addModal.querySelector('form');
                const actionUrl = form.getAttribute('action');
                const match = actionUrl.match(/facilities\/(\d+)\//);
                if (match && match[1]) facilityId = match[1];
            }
            const avgInput = document.getElementById('average_monthly_kwh_profile');
            if (facilityId) {
                fetch(`/api/facilities/${facilityId}/first3months-data`)
                    .then(res => res.json())
                    .then(data => {
                        let total = 0;
                        let count = 0;
                        ['month1','month2','month3'].forEach(m => {
                            if (!isNaN(parseFloat(data[m]))) {
                                total += parseFloat(data[m]);
                                count++;
                            }
                        });
                        if (count === 3) {
                            avgInput.value = (total / 3).toFixed(2);
                        } else {
                            avgInput.value = '';
                        }
                    })
                    .catch(() => { avgInput.value = ''; });
            }
        });
    });
    // Always set the value before submit
    const addForm = document.getElementById('addEnergyProfileForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const avgInput = document.getElementById('average_monthly_kwh_profile');
            if (avgInput) {
                avgInput.readOnly = true;
                // Optionally, you can re-fetch here if you want to guarantee latest value
            }
        });
    }
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal').style.display = 'none';
        });
    });
    window.addEventListener('click', function(e){
        document.querySelectorAll('.modal').forEach(modal => {
            if(e.target === modal) modal.style.display = 'none';
        });
    });
});
</script>
