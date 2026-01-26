<?php
    if (!isset($userRole)) {
        $userRole = strtolower(auth()->user()->role ?? '');
    }
    if (!isset($months)) {
        $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    }
?>

<?php $__env->startSection('title', 'Monthly Records'); ?>
<?php $__env->startSection('content'); ?>




<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Monthly Records</h2>
    <div style="display:flex;gap:12px;align-items:center;">
        <?php if($userRole !== 'staff'): ?>
            <?php $hasDuplicate = session('errors') && session('errors')->has('duplicate'); ?>
            <button id="btnAddMonthlyRecord"
                class="btn-add-monthly-record"
                style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:10px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10); <?php if($hasDuplicate): ?> opacity:0.5; pointer-events:none; <?php endif; ?>"
                <?php if($hasDuplicate): ?> disabled <?php endif; ?>>
                + Add Monthly Record
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if($hasDuplicate): ?>
<div id="duplicateErrorPopup"
    style="position:fixed;top:32px;left:50%;transform:translateX(-50%);background:#fee2e2;color:#b91c1c;padding:14px 28px;border-radius:10px;font-weight:700;z-index:99999;box-shadow:0 2px 12px rgba(225,29,72,0.13);font-size:1.08rem;">
    <?php echo e(session('errors')->first('duplicate')); ?>

</div>
<script>
    setTimeout(() => {
        const popup = document.getElementById('duplicateErrorPopup');
        if(popup) popup.style.display = 'none';
        const btn = document.getElementById('btnAddMonthlyRecord');
        if(btn) { btn.disabled = false; btn.style.opacity = 1; btn.style.pointerEvents = 'auto'; }
    }, 2000);
</script>
<?php endif; ?>



<?php
    $selectedFacility = null;
    if (isset($facilities) && count($facilities) > 0) {
        $selectedId = request('facility_id');
        if ($selectedId) {
            foreach ($facilities as $f) {
                if ($f->id == $selectedId) {
                    $selectedFacility = $f;
                    break;
                }
            }
        }
        if (!$selectedFacility) {
            $selectedFacility = $facilities[0];
        }
    }
?>

<?php if($selectedFacility): ?>
    <div style="margin-bottom:1.2rem;">
        <strong>Facility:</strong> <?php echo e($selectedFacility->name); ?>

    </div>
<?php endif; ?>

<div style="display:flex; gap:14px; align-items:center; margin-bottom:1.5rem;"></div>

<div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
    <table style="width:100%;border-collapse:collapse;min-width:900px;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:left;">
                <th style="padding:12px 10px;">Month</th>
                <th style="padding:12px 10px;">Year</th>
                <th style="padding:12px 10px;">Facility</th>
                <th style="padding:12px 10px;">kWh Consumed</th>
                <th style="padding:12px 10px;">Average kWh</th>
                <th style="padding:12px 10px;">Variance</th>
                <th style="padding:12px 10px;">Deviation %</th>
                <th style="padding:12px 10px;">Alert Level</th>
                <th style="padding:12px 10px;">Alert Message</th>
                <?php if($userRole !== 'staff'): ?> <th style="padding:12px 10px;">Actions</th> <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $monthlyRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 14px;"><?php echo e($months[$record->month] ?? $record->month); ?></td>
                <td style="padding:10px 14px;"><?php echo e($record->year); ?></td>
                <td style="padding:10px 14px;"><?php echo e($record->facility->name ?? '-'); ?></td>
                <td style="padding:10px 14px;"><?php echo e(number_format($record->actual_kwh,2)); ?> kWh</td>
                <td style="padding:10px 14px;"><?php echo e(number_format($record->average_monthly_kwh,2)); ?> kWh</td>
                <td style="padding:10px 14px;"><?php echo e(number_format($record->actual_kwh - $record->average_monthly_kwh,2)); ?></td>
                <td style="padding:10px 14px;">
                    <?php if($record->average_monthly_kwh && $record->average_monthly_kwh != 0): ?>
                        <?php echo e(round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2)); ?>%
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px; color:
                    <?php if($record->average_monthly_kwh && $record->average_monthly_kwh != 0): ?>
                        <?php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        ?>
                        <?php echo e($deviation <= 10 && $deviation >= -10 ? '#16a34a' : ($deviation > 20 ? '#e11d48' : '#f59e42')); ?>

                    <?php else: ?>
                        #222
                    <?php endif; ?>
                    ; font-weight:600;">
                    <?php if($record->average_monthly_kwh && $record->average_monthly_kwh != 0): ?>
                        <?php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        ?>
                        <?php echo e($deviation <= 10 && $deviation >= -10 ? 'Normal' : ($deviation > 20 ? 'Critical' : 'Warning')); ?>

                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td style="padding:10px 14px;">
                    <?php if($record->average_monthly_kwh && $record->average_monthly_kwh != 0): ?>
                        <?php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        ?>
                        <?php if($deviation <= 10 && $deviation >= -10): ?>
                            Energy usage is within baseline limits.
                        <?php elseif($deviation < 0): ?>
                            Energy usage below baseline by <?php echo e(abs(number_format($deviation, 2))); ?>%
                        <?php else: ?>
                            Energy usage exceeded baseline by <?php echo e(number_format($deviation, 2)); ?>%
                        <?php endif; ?>
                    <?php else: ?>
                        No baseline data yet.
                    <?php endif; ?>
                </td>
                <?php if($userRole !== 'staff'): ?>
                <td style="padding:10px 14px;display:flex;gap:6px;">
                    <button onclick="openResetModal(<?php echo e($record->id); ?>)" style="background:#f1c40f;color:#222;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Reset Baseline"><i class="fa fa-repeat"></i></button>
                    <button onclick="toggleApproval(<?php echo e($record->id); ?>)" style="background:#22c55e;color:#fff;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Toggle Approval"><i class="fa fa-check-circle"></i></button>
                    <button onclick="openDeleteMonthlyRecordModal(<?php echo e($record->id); ?>)" style="background:#e11d48;color:#fff;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Delete"><i class="fa fa-trash"></i></button>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="<?php echo e($userRole !== 'staff' ? 11 : 10); ?>" style="padding:18px;text-align:center;color:#888;">No monthly records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>


<?php echo $__env->make('modules.energy-monitoring.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
function openResetModal(recordId){
    document.getElementById('reset_record_id').value = recordId;
    document.getElementById('resetBaselineModal').classList.add('show-modal');
}

function toggleApproval(recordId){
    fetch(`/modules/energy-monitoring/monthly-records/${recordId}/toggle-approval`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}
    }).then(res=>res.json()).then(data=>{
        alert(data.message || 'Approval toggled!');
        location.reload();
    });
}
</script>

<?php $__env->stopSection(); ?>
<script>
// Ensure average_monthly_kwh is always submitted
window.addEventListener('DOMContentLoaded', function() {
    var addForm = document.getElementById('addMonthlyRecordForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            var avgField = addForm.querySelector('[name="average_monthly_kwh"]');
            if (!avgField) {
                avgField = document.createElement('input');
                avgField.type = 'hidden';
                avgField.name = 'average_monthly_kwh';
                avgField.value = '0';
                addForm.appendChild(avgField);
            } else if (avgField.value === '' || avgField.value == null) {
                avgField.value = '0';
            }
        });
    }
});
</script>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/records.blade.php ENDPATH**/ ?>