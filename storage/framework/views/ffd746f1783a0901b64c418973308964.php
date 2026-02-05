<?php $__env->startSection('title', 'Energy Actions'); ?>
<?php $__env->startSection('content'); ?>
<?php
    $facilityId = request('facility');
    $filteredActions = $facilityId ? $actions->where('facility_id', $facilityId) : $actions;
    $facilityName = null;
    if ($facilityId && $filteredActions->count() > 0) {
        $facilityName = $filteredActions->first()->facility->name ?? null;
    }
?>
<div style="max-width:900px;margin:32px auto 0 auto;background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;font-size:2rem;color:#3762c8;margin-bottom:1.5rem;">Energy Actions
        <?php if($facilityName): ?>
            <span style="font-size:1.1rem;font-weight:500;color:#2563eb;">for <?php echo e($facilityName); ?></span>
        <?php endif; ?>
    </h2>
    <table class="table" style="width:100%;border-collapse:collapse;font-size:0.97rem;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:center;">
                <th style="padding:8px 10px;">Facility</th>
                <th style="padding:8px 10px;">Action Type</th>
                <th style="padding:8px 10px;">Description</th>
                <th style="padding:8px 10px;">Priority</th>
                <th style="padding:8px 10px;">Target Date</th>
                <th style="padding:8px 10px;">Status</th>
                <th style="padding:8px 10px;">Created</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $filteredActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr style="border-bottom:1px solid #e5e7eb;text-align:center;">
                <td style="padding:8px 10px;"><?php echo e($action->facility->name ?? '-'); ?></td>
                <td style="padding:8px 10px;"><?php echo e($action->action_type); ?></td>
                <td style="padding:8px 10px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo e($action->description); ?></td>
                <td style="padding:8px 10px;"><?php echo e($action->priority); ?></td>
                <td style="padding:8px 10px;"><?php echo e($action->target_date); ?></td>
                <td style="padding:8px 10px;"><?php echo e($action->status); ?></td>
                <td style="padding:8px 10px;"><?php echo e($action->created_at->format('Y-m-d')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" style="padding:18px;text-align:center;">No energy actions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-actions/index.blade.php ENDPATH**/ ?>