
<?php $__env->startSection('title', 'First 3 Months Data'); ?>
<?php $__env->startSection('content'); ?>
<div style="background:linear-gradient(120deg,#f0f6ff 60%,#e0e7ff 100%);min-height:100vh;padding:0;margin:0;">
    <div class="container" style="max-width:480px;margin:0 auto;padding:48px 0 32px 0;">
        <div style="background:#fff;border-radius:18px;box-shadow:0 6px 32px rgba(37,99,235,0.10);padding:38px 32px 32px 32px;">
            <h2 style="font-size:2rem;font-weight:800;color:#2563eb;margin-bottom:0.5rem;text-alADign:center;letter-spacing:-1px;">First 3 Months kWh Data</h2>
            <div style="color:#64748b;font-size:1.08rem;text-align:center;margin-bottom:1.7rem;">Enter the initial 3 months of kWh readings for a new facility. This will be used as the baseline for energy analysis.</div>
            <?php if(session('success')): ?>
                <div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:8px;margin-bottom:18px;text-align:center;">
                        <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border-radius:8px;margin-bottom:18px;">
                        <ul style="margin:0;padding-left:18px;">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('facilities.first3months.store')); ?>" autocomplete="off">
                <?php echo csrf_field(); ?>
                <div class="form-group" style="margin-bottom:22px;">
                    <label for="facility_id" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">Facility</label>
                    <select name="facility_id" id="facility_id" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                        <option value="">Select Facility</option>
                        <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($facility->id); ?>" <?php echo e(request('facility_id') == $facility->id ? 'selected' : ''); ?>><?php echo e($facility->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:18px;">
                    <label for="month1" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">Month 1 kWh</label>
                    <input type="number" step="0.01" name="month1" id="month1" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                </div>
                <div class="form-group" style="margin-bottom:18px;">
                    <label for="month2" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">Month 2 kWh</label>
                    <input type="number" step="0.01" name="month2" id="month2" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label for="month3" style="font-weight:700;color:#2563eb;margin-bottom:6px;display:block;">Month 3 kWh</label>
                    <input type="number" step="0.01" name="month3" id="month3" class="form-control" required style="width:100%;padding:10px 12px;border-radius:7px;border:1.5px solid #c7d2fe;font-size:1.08rem;">
                </div>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:12px 0;width:100%;border-radius:8px;font-weight:700;font-size:1.13rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);border:none;transition:background 0.2s;">Save Data</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/first3months.blade.php ENDPATH**/ ?>