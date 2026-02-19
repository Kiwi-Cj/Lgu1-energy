

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Energy Usage Forecast</h2>
    <form method="POST" action="/forecast">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="data" class="form-label">Enter kWh values (comma-separated):</label>
            <input type="text" class="form-control" id="data" name="data" value="<?php echo e(old('data', '100,120,130,150,170')); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Forecast</button>
    </form>
    <?php if(session('forecast')): ?>
        <div class="alert alert-success mt-3">
            <strong>Forecasted Next Value:</strong> <?php echo e(session('forecast')); ?>

        </div>
    <?php elseif(session('error')): ?>
        <div class="alert alert-danger mt-3">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/forecast.blade.php ENDPATH**/ ?>