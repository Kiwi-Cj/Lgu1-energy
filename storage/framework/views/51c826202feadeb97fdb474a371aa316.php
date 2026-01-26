
<?php $__env->startSection('title', 'Add Energy Profile'); ?>
<?php $__env->startSection('content'); ?>
<div class="facility-create-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:70vh;width:100%;">
    <div style="width:100%;max-width:520px;background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
        <h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;text-align:center;">Add Energy Profile</h2>
        <form action="<?php echo e(route('modules.facilities.energy-profile.store', $facilityModel->id)); ?>" method="POST" enctype="multipart/form-data">
                        <div style="margin-bottom:1.2rem;">
                            <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Meralco Bill Image (optional)</label>
                            <input type="file" name="bill_image" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                        </div>
            <?php echo csrf_field(); ?>
            <?php if($errors->any()): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:10px 16px;border-radius:8px;margin-bottom:18px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Electric Meter No.</label>
                <input type="text" name="electric_meter_no" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Utility Provider</label>
                <select name="utility_provider" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select</option>
                    <option value="Meralco">Meralco</option>
                    <option value="Electric Coop">Electric Coop</option>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Contract Account No.</label>
                <input type="text" name="contract_account_no" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Average Monthly kWh</label>
                <input type="number" step="0.01" name="average_monthly_kwh" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Main Energy Source</label>
                <select name="main_energy_source" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select</option>
                    <option value="Grid">Grid</option>
                    <option value="Solar">Solar</option>
                    <option value="Generator">Generator</option>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Backup Power</label>
                <select name="backup_power" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select</option>
                    <option value="Generator">Generator</option>
                    <option value="UPS">UPS</option>
                    <option value="None">None</option>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Transformer Capacity (optional)</label>
                <input type="text" name="transformer_capacity" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.8rem;">
                <label style="font-weight:500;display:block;margin-bottom:0.4rem;">Number of Meters</label>
                <input type="number" name="number_of_meters" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;">
                <a href="<?php echo e(url('/modules/facilities/' . ($facilityModel->id ?? $facility->id ?? '') . '/energy-profile')); ?>" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Cancel</a>
                <button type="submit" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);cursor:pointer;">Save Energy Profile</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/energy-profile/create.blade.php ENDPATH**/ ?>