
<?php $__env->startSection('title', 'Energy Trend'); ?>
<?php $__env->startSection('content'); ?>

<h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;">Energy Consumption Trend</h2>

<?php echo $__env->make('modules.energy-monitoring.partials.charts', ['chartData' => $trendData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/trend.blade.php ENDPATH**/ ?>