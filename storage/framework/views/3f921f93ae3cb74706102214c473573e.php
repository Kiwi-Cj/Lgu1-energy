
<?php $__env->startSection('title', 'Add New Bill'); ?>
<?php $__env->startSection('content'); ?>
<div class="billing-create-card" style="max-width:520px;margin:40px auto;background:#f5f8ff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;color:#3762c8;margin-bottom:18px;">Add New Bill</h2>
    <form method="POST" action="<?php echo e(route('modules.billing.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="facility_id" style="font-weight:600;margin-bottom:6px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="month" style="font-weight:600;margin-bottom:6px;">Month</label>
            <input type="month" name="month" id="month" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="kwh_consumed" style="font-weight:600;margin-bottom:6px;">kWh Consumed</label>
            <input type="number" step="0.01" name="kwh_consumed" id="kwh_consumed" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="unit_cost" style="font-weight:600;margin-bottom:6px;">Unit Cost (PHP)</label>
            <input type="number" step="0.01" name="unit_cost" id="unit_cost" class="form-control" value="12.50" readonly style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;background:#e5e7eb;" required>
        </div>
        <div class="mb-3" style="margin-bottom:24px;">
            <label for="status" style="font-weight:600;margin-bottom:6px;">Status</label>
            <select name="status" id="status" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="meralco_bill_picture" style="font-weight:600;margin-bottom:6px;">Meralco Bill Picture (optional)</label>
            <input type="file" name="meralco_bill_picture" id="meralco_bill_picture" class="form-control" accept="image/*">
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:8px 28px;">Save</button>
            <a href="<?php echo e(route('modules.billing.index')); ?>" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const facilitySelect = document.getElementById('facility_id');
    const monthInput = document.getElementById('month');
    const kwhInput = document.getElementById('kwh_consumed');

    function fetchKwhConsumed() {
        const facilityId = facilitySelect.value;
        const monthVal = monthInput.value;
        if (!facilityId || !monthVal) {
            kwhInput.value = '';
            return;
        }
        fetch(`/modules/energy/get-kwh-consumed?facility_id=${facilityId}&month=${monthVal}`)
            .then(response => response.json())
            .then(data => {
                if (data.kwh_consumed !== null && data.kwh_consumed !== undefined) {
                    kwhInput.value = data.kwh_consumed;
                } else {
                    kwhInput.value = '';
                }
            })
            .catch(() => {
                kwhInput.value = '';
            });
    }

    facilitySelect.addEventListener('change', fetchKwhConsumed);
    monthInput.addEventListener('change', fetchKwhConsumed);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/billing/create.blade.php ENDPATH**/ ?>