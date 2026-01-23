<?php
    // $hasFilter and $efficiencyRows must be passed in
?>
<div id="efficiency-table-wrapper">
<?php if($hasFilter): ?>
<table class="table" style="width:100%;margin-top:12px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
    <thead style="background:#e9effc;">
        <tr>
            <th style="text-align:center;">Facility</th>
            <th style="text-align:center;">Month</th>
            <th style="text-align:center;">Actual kWh</th>
            <th style="text-align:center;">Avg kWh</th>
            <th style="text-align:center;">Variance</th>
            <th style="text-align:center;">EUI</th>
            <th style="text-align:center;">Rating</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $efficiencyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td>
                <a href="#" style="color:#2563eb;font-weight:600;text-decoration:underline;cursor:pointer;" onclick="openFacilityModalAjaxById('<?php echo e($row['facility_id'] ?? ''); ?>'); return false;"><?php echo e($row['facility']); ?></a>
            </td>
            <td><?php echo e($row['month']); ?></td>
            <td><?php echo e($row['actual_kwh']); ?></td>
            <td><?php echo e($row['avg_kwh']); ?></td>
            <td><?php echo e($row['variance']); ?></td>
            <td><?php echo e($row['eui']); ?></td>
            <td>
                <?php if($row['rating'] === 'High'): ?>
                    <span style="color:#22c55e;font-weight:700;">High</span>
                <?php elseif($row['rating'] === 'Medium'): ?>
                    <span style="color:#eab308;font-weight:700;">Medium</span>
                <?php else: ?>
                    <span style="color:#e11d48;font-weight:700;">Low</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="text-center">No efficiency data found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php endif; ?>
</div><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/energy-efficiency-analysis/_table.blade.php ENDPATH**/ ?>