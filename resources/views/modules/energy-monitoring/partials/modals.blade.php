<!-- Delete Monthly Record Modal -->
<div id="deleteMonthlyRecordModal" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="closeDeleteMonthlyRecordModal()" style="top:12px;right:12px;position:absolute;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Monthly Record</h2>
        <div style="font-size:1.02rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this monthly record? This action cannot be undone.</div>
        <form id="deleteMonthlyRecordForm" method="POST" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="delete_monthly_record_id" name="id">
            <button type="submit" class="delete-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;background:#e11d48;color:#fff;">Yes, Delete</button>
            <button type="button" class="delete-modal-btn cancel" onclick="closeDeleteMonthlyRecordModal()" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;background:#f3f4f6;color:#222;">Cancel</button>
        </form>
    <script>
    // Attach submit event to deleteMonthlyRecordForm to redirect to the correct route
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('deleteMonthlyRecordForm');
        if(form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                var id = document.getElementById('delete_monthly_record_id').value;
                var action = '/modules/energy/' + id;
                form.action = action;
                form.submit();
            };
        }
    });
    </script>
    </div>
</div>
<script>
function openDeleteMonthlyRecordModal(recordId) {
    document.getElementById('delete_monthly_record_id').value = recordId;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'flex';
}
function closeDeleteMonthlyRecordModal() {
    document.getElementById('deleteMonthlyRecordModal').style.display = 'none';
}
</script>
<!-- Add/Edit/Delete/Reset Modals -->
<style>
    .modal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.18); z-index:9999; align-items:center; justify-content:center; }
    .modal.show-modal { display:flex; }
    .modal-content { background:#fff; border-radius:16px; padding:2.2rem 2.5rem 2rem 2.5rem; box-shadow:0 8px 32px rgba(31,38,135,0.18); min-width:340px; max-width:96vw; position:relative; }
    .modal-close { position:absolute; top:16px; right:18px; background:none; border:none; font-size:1.7rem; color:#888; cursor:pointer; }
    .modal-content h2 { font-size:1.35rem; font-weight:700; margin-bottom:1.2rem; color:#222; }
    .modal-content form { display:flex; flex-wrap:wrap; gap:12px; align-items:center; }
    .modal-content input, .modal-content textarea, .modal-content select { padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; font-size:1rem; margin-bottom:0; flex:1; }
    .modal-content textarea { min-width:220px; min-height:48px; resize:vertical; }
    .modal-content button[type="submit"] { background:linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:8px; padding:10px 28px; font-size:1rem; cursor:pointer; transition:background 0.18s; }
    .modal-content button[type="submit"]:hover { background:linear-gradient(90deg,#1d4ed8,#6366f1); }
</style>
<div id="addEnergyProfileModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeAddEnergyProfileModal()">&times;</button>
        <h2>Add Energy Profile</h2>
        <form action="{{ route('energy-profiles.store') }}" method="POST">
            @csrf
            <input type="text" name="electric_meter_no" placeholder="Electric Meter No." required>
            <input type="text" name="utility_provider" placeholder="Utility Provider" required>
            <input type="text" name="contract_account_no" placeholder="Contract Account No." required>
            <input type="number" name="baseline_kwh" placeholder="Baseline kWh" required>
            <button type="submit">Add</button>
        </form>
    </div>
</div>

<div id="resetBaselineModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeResetBaselineModal()">&times;</button>
        <h2>Reset Baseline</h2>
        <form id="resetBaselineForm">
            <input type="hidden" id="reset_facility_id">
            <textarea id="reset_reason" placeholder="Reason for reset" required></textarea>
            <button type="submit">Reset</button>
        </form>
    </div>
</div>

<div id="addMonthlyRecordModal" class="modal">
    <div class="modal-content" style="max-width:420px;background:#fff;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
        <button class="modal-close" onclick="closeAddMonthlyRecordModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#6366f1;cursor:pointer;">&times;</button>
        <h2 style="margin-bottom:18px;font-size:1.5rem;font-weight:700;color:#2563eb;text-align:left;">Add Monthly Record</h2>
        <!-- Form removed as requested -->
    </div>
</div>

<script>
function closeAddEnergyProfileModal() {
    document.getElementById('addEnergyProfileModal').classList.remove('show-modal');
}
 // Script removed as requested
function closeAddMonthlyRecordModal() {
    document.getElementById('addMonthlyRecordModal').classList.remove('show-modal');
}
document.querySelector('.btn-add-monthly-record')?.addEventListener('click', function() {
    document.getElementById('addMonthlyRecordModal').classList.add('show-modal');
});

// Map of facility_id to baseline_kwh (from backend)
const facilityAverages = {
@foreach($facilities as $facility)
    {{ $facility->id }}: {{ $facility->energyProfiles->last()?->baseline_kwh ?? 0 }},
@endforeach
};
const facilitySelect = document.querySelector('select[name="facility_id"]');
const avgInput = document.getElementById('baseline_kwh_input');
if (facilitySelect && avgInput) {
    facilitySelect.addEventListener('change', function() {
        const avg = facilityAverages[this.value] || '';
        avgInput.value = avg;
    });
    // Set on load if a facility is preselected
    if (facilitySelect.value) {
        const avg = facilityAverages[facilitySelect.value] || '';
        avgInput.value = avg;
    }
}
</script>
