
<?php $__env->startSection('title', 'Create Energy Incident'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:700px;margin:32px auto;background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;font-size:2rem;color:#3762c8;margin-bottom:1.5rem;">Create Energy Incident</h2>
    <form method="POST" action="<?php echo e(route('energy-incidents.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group" style="margin-bottom:18px;">
            <label for="facility_id" style="font-weight:600;color:#2563eb;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" required>
                <option value="">Select Facility</option>
                <?php $__currentLoopData = $facilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label for="date_detected" style="font-weight:600;color:#2563eb;">Date Detected</label>
            <input type="date" name="date_detected" id="date_detected" class="form-control" required>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label for="description" style="font-weight:600;color:#2563eb;">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label for="severity" style="font-weight:600;color:#2563eb;">Severity</label>
            <select name="severity" id="severity" class="form-control" required>
                <option value="">Select Severity</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label for="attachments" style="font-weight:600;color:#2563eb;">Attachments</label>
            <input type="file" name="attachments" id="attachments" class="form-control" accept="image/*,application/pdf">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 28px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1.1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);">Submit Incident</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-incident/create.blade.php ENDPATH**/ ?>