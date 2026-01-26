
<?php $__env->startSection('title', 'Add Facility Energy Record'); ?>
<?php $__env->startSection('content'); ?>
<div class="energy-create-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:70vh;width:100%;">
    <div style="width:100%;max-width:520px;background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
        <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:1.5rem;text-align:center;">Add Energy Record</h2>
        <a href="<?php echo e(route('modules.energy.index')); ?>" style="display:inline-block;margin-bottom:1.2rem;color:#3762c8;text-decoration:none;font-weight:500;">&larr; Back to List</a>
        <?php if($errors->has('duplicate')): ?>
            <div style="color:#e11d48;font-weight:600;margin-bottom:10px;"><?php echo e($errors->first('duplicate')); ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('modules.energy.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div style="margin-bottom:1.2rem;">
                <label for="facility_id" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility</label>
                <select name="facility_id" id="facility_id" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select Facility</option>
                    <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label for="month" style="font-weight:500;display:block;margin-bottom:0.4rem;">Month</label>
                <select name="month" id="month" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select Month</option>
                    <?php $currentMonth = date('m'); ?>
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php echo e($currentMonth == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : ''); ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label for="year" style="font-weight:500;display:block;margin-bottom:0.4rem;">Year</label>
                <select name="year" id="year" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
                    <option value="">Select Year</option>
                    <?php $currentYear = date('Y'); ?>
                    <?php $__currentLoopData = range(date('Y'), date('Y')-10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($currentYear == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div style="margin-bottom:1.2rem;">
                <label for="kwh_consumed" style="font-weight:500;display:block;margin-bottom:0.4rem;">kWh Consumed</label>
                <input type="number" step="0.01" name="kwh_consumed" id="kwh_consumed" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <div style="margin-bottom:1.2rem;">
                <label for="meralco_bill" style="font-weight:500;display:block;margin-bottom:0.4rem;">Meralco Bill Image (optional)</label>
                <input type="file" name="meralco_bill" id="meralco_bill" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
            </div>
            <button type="submit" style="width:100%;background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 0; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.2s;">Add Record</button>
        </form>
        <script src="/js/energy-duplicate-check.js"></script>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy/create.blade.php ENDPATH**/ ?>