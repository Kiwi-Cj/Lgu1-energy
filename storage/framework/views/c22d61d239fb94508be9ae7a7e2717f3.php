<!-- resources/views/modules/billing/partials/modals.blade.php -->
<div id="addBillModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:520px;width:95vw;max-height:95vh;overflow:auto;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);padding:32px 28px 24px 28px;position:relative;">
        <button type="button" onclick="closeAddBillModal()" style="position:absolute;top:18px;right:18px;font-size:1.7rem;border:none;background:none;">&times;</button>
        <h2 style="font-weight:700;color:#3762c8;margin-bottom:18px;">Add New Bill</h2>
        <form method="POST" action="<?php echo e(route('modules.billing.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="mb-3" style="margin-bottom:18px;">
                <label for="modal_facility_id" style="font-weight:600;margin-bottom:6px;">Facility</label>
                <select name="facility_id" id="modal_facility_id" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                    <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="mb-3" style="margin-bottom:18px;">
                <label for="modal_month" style="font-weight:600;margin-bottom:6px;">Month</label>
                <input type="month" name="month" id="modal_month" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
            </div>
            <div class="mb-3" style="margin-bottom:18px;">
                <label for="modal_kwh_consumed" style="font-weight:600;margin-bottom:6px;">kWh Consumed</label>
                <input type="number" step="0.01" name="kwh_consumed" id="modal_kwh_consumed" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
            </div>
            <div class="mb-3" style="margin-bottom:18px;">
                <label for="modal_unit_cost" style="font-weight:600;margin-bottom:6px;">Unit Cost (PHP)</label>
                <input type="number" step="0.01" name="unit_cost" id="modal_unit_cost" class="form-control" value="12.50" readonly style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;background:#e5e7eb;" required>
            </div>
            <div class="mb-3" style="margin-bottom:24px;">
                <label for="modal_status" style="font-weight:600;margin-bottom:6px;">Status</label>
                <select name="status" id="modal_status" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div class="mb-3" style="margin-bottom:18px;">
                <label for="modal_meralco_bill_picture" style="font-weight:600;margin-bottom:6px;">Meralco Bill Picture (optional)</label>
                <input type="file" name="meralco_bill_picture" id="modal_meralco_bill_picture" class="form-control" accept="image/*">
            </div>
            <div style="display:flex;gap:12px;align-items:center;">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:8px 28px;">Save</button>
                <button type="button" onclick="closeAddBillModal()" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
function openAddBillModal() {
    document.getElementById('addBillModal').style.display = 'flex';
}
function closeAddBillModal() {
    document.getElementById('addBillModal').style.display = 'none';
}
</script>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/billing/partials/modals.blade.php ENDPATH**/ ?>