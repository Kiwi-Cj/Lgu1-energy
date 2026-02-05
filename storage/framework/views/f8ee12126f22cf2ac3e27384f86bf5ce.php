
<?php $__env->startSection('title', 'Previous Monthly Records'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:900px;margin:0 auto;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">Previous Monthly Records</h2>
    <a href="<?php echo e(route('monthly-records.index', ['facility' => $facility->id])); ?>" style="color:#2563eb;font-weight:600;font-size:1.05rem;text-decoration:underline;">&larr; Back to Current Records</a>
    <div style="margin-top:24px;overflow-x:auto; background:#f8fafc; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.06);">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead style="background:#f1f5f9;">
                <tr>
                    <th style="padding:14px 22px; text-align:center;">Year</th>
                    <th style="padding:14px 22px; text-align:center;">Month</th>
                    <th style="padding:14px 22px; text-align:center;">Actual kWh</th>
                    <th style="padding:14px 22px; text-align:center;">Average kWh</th>
                    <th style="padding:14px 22px; text-align:center;">Deviation (%)</th>
                    <th style="padding:14px 22px; text-align:center;">Alert</th>
                    <th style="padding:14px 22px; text-align:center;">Energy Cost</th>
                    <th style="padding:14px 22px; text-align:center;">Bill Image</th>
                    <th style="padding:14px 22px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $currentYear = date('Y');
                $previousRecords = $records->where('year', '<', $currentYear)->sortByDesc(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
            ?>
            <?php $__empty_1 = true; $__currentLoopData = $previousRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $actual = $record->actual_kwh;
                    $baseline = $facility->average_monthly_kwh;
                    $deviation = $baseline > 0 ? round((($actual - $baseline) / $baseline) * 100, 2) : null;
                    if ($deviation === null) {
                        $alert = '';
                    } elseif ($sizeLabel === 'Small') {
                        if ($deviation > 30) {
                            $alert = 'High';
                        } elseif ($deviation > 15) {
                            $alert = 'Medium';
                        } else {
                            $alert = 'Low';
                        }
                    } elseif ($sizeLabel === 'Medium') {
                        if ($deviation > 20) {
                            $alert = 'High';
                        } elseif ($deviation > 10) {
                            $alert = 'Medium';
                        } else {
                            $alert = 'Low';
                        }
                    } elseif ($sizeLabel === 'Large' || $sizeLabel === 'Extra Large') {
                        if ($deviation > 15) {
                            $alert = 'High';
                        } elseif ($deviation > 5) {
                            $alert = 'Medium';
                        } else {
                            $alert = 'Low';
                        }
                    } else {
                        if ($deviation > 20) {
                            $alert = 'High';
                        } elseif ($deviation > 10) {
                            $alert = 'Medium';
                        } else {
                            $alert = 'Low';
                        }
                    }
                    $rate = isset($record->rate_per_kwh) && $record->rate_per_kwh ? $record->rate_per_kwh : 12.00;
                    $computedCost = $record->actual_kwh * $rate;
                ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:14px 22px; text-align:center;"><?php echo e($record->year); ?></td>
                    <td style="padding:14px 22px; text-align:center;"><?php echo e($months[$record->month - 1] ?? $record->month); ?></td>
                    <td style="padding:14px 22px; text-align:center;"><?php echo e(number_format($record->actual_kwh, 2)); ?></td>
                    <td style="padding:14px 22px; text-align:center;"><?php echo e(number_format($facility->average_monthly_kwh, 2)); ?></td>
                    <td style="padding:14px 22px; text-align:center;"><?php echo e($deviation !== null ? $deviation . '%' : ''); ?></td>
                    <td style="padding:14px 22px; text-align:center;">
                        <?php if($alert === 'High'): ?>
                            <span style="color:#e11d48;font-weight:600;">High</span>
                        <?php elseif($alert === 'Medium'): ?>
                            <span style="color:#f59e42;font-weight:600;">Medium</span>
                        <?php elseif($alert === 'Low'): ?>
                            <span style="color:#22c55e;font-weight:600;">Low</span>
                        <?php else: ?>
                            <span style="color:#64748b;">&nbsp;</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:14px 22px; text-align:center;">₱<?php echo e(number_format($computedCost, 2)); ?></td>
                    <td style="padding:14px 22px; text-align:center;">
                        <?php if($record->bill_image): ?>
                            <a href="<?php echo e(asset('storage/' . $record->bill_image)); ?>" target="_blank" style="color:#2563eb;text-decoration:underline;">View</a>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                    <td style="padding:14px 22px; text-align:center; display: flex; gap: 12px; align-items: center; justify-content: center;">
                        <!-- Action buttons can be copied from your main table -->
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" style="padding:18px 0;text-align:center;color:#b91c1c;">No previous records found for this facility.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/monthly-record/previous-records.blade.php ENDPATH**/ ?>