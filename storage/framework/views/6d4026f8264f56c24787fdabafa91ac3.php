<table>
    <thead>
        <tr>
            <th>Facility</th>
            <th>Month</th>
            <th>Actual kWh</th>
            <th>Baseline kWh</th>
            <th>Variance</th>
            <th>Trend</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $energyRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($row['facility']); ?></td>
                <td><?php echo e($row['month']); ?></td>
                <td><?php echo e($row['actual_kwh']); ?></td>
                <td><?php echo e($row['baseline_kwh']); ?></td>
                <td><?php echo e($row['variance']); ?></td>
                <td><?php echo e($row['trend']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/exports/energy_report.blade.php ENDPATH**/ ?>