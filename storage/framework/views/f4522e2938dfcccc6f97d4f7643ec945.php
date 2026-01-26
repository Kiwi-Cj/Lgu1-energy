
<?php $__env->startSection('title', 'Energy Monitoring Dashboard'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $userRole = strtolower(auth()->user()->role ?? '');
?>

<h2 style="font-size:2rem; font-weight:700; margin-bottom:1.5rem;">Energy Monitoring Dashboard</h2>

<!-- Summary Cards -->
<div style="display:flex; gap:24px; flex-wrap:wrap; margin-bottom:2rem;">
    <div style="flex:1 1 180px; background:#f5f8ff; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-weight:500; color:#3762c8;">🏢 Total Facilities</div>
        <div style="font-weight:700; font-size:1.8rem;"><?php echo e($totalFacilities ?? '-'); ?></div>
    </div>
    <div style="flex:1 1 180px; background:#f0fdf4; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <div style="font-weight:500; color:#22c55e;">🟢 Active</div>
        <div style="font-weight:700; font-size:1.8rem;"><?php echo e($activeFacilities ?? '-'); ?></div>
    </div>
    <div style="flex:1 1 180px; background:#fff7ed; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(234,179,8,0.08);">
        <div style="font-weight:500; color:#f59e42;">🛠 Maintenance</div>
        <div style="font-weight:700; font-size:1.8rem;"><?php echo e($maintenanceFacilities ?? '-'); ?></div>
    </div>
    <div style="flex:1 1 180px; background:#fff0f3; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(225,29,72,0.08);">
        <div style="font-weight:500; color:#e11d48;">🚫 Inactive</div>
        <div style="font-weight:700; font-size:1.8rem;"><?php echo e($inactiveFacilities ?? '-'); ?></div>
    </div>
    <div style="flex:1 1 180px; background:#e0f2fe; padding:18px; border-radius:14px; box-shadow:0 2px 8px rgba(14,165,233,0.08);">
        <div style="font-weight:500; color:#0284c7;">⚡ Avg Monthly kWh</div>
        <div style="font-weight:700; font-size:1.8rem;"><?php echo e(round($avgMonthlyKwh ?? 0,2)); ?></div>
    </div>
</div>

<!-- Facility Table -->
<div style="overflow-x:auto; margin-bottom:2rem;">
<table style="width:100%; border-collapse:collapse;">
    <thead style="background:#f3f4f6; color:#111;">
        <tr>
            <th style="padding:10px 12px; text-align:left;">Facility</th>
            <th>Type</th>
            <th>Status</th>
            <th>Floor Area</th>
            <th>Baseline kWh</th>
            <th>Trend</th>
            <th>EUI (kWh/m²)</th>
            <th>Last Maint</th>
            <th>Next Maint</th>
            <th>Alerts</th>
            <?php if($userRole !== 'staff'): ?> <th>Actions</th> <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td><?php echo e($facility->name); ?></td>
                <td><?php echo e($facility->type); ?></td>
                <td><?php echo e($facility->status); ?></td>
                <td><?php echo e($facility->floor_area ?? '-'); ?></td>
                <td><?php echo e($facility->baseline_kwh ?? '-'); ?></td>
                <td><?php echo e($facility->trend_analysis ?? '-'); ?></td>
                <td><?php echo e($facility->monthly_eui ?? '-'); ?></td>
                <td><?php echo e($facility->last_maintenance ?? '-'); ?></td>
                <td><?php echo e($facility->next_maintenance ?? '-'); ?></td>
                <td>
                    <?php if($facility->alert_level): ?>
                        <span style="color:red; font-weight:600;"><?php echo e($facility->alert_level); ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <?php if($userRole !== 'staff'): ?>
                <td style="display:flex; gap:6px;">
                    <a href="<?php echo e(url('/modules/facilities/'.$facility->id.'/energy-profile')); ?>" title="View"><i class="fa fa-eye"></i></a>
                    <button onclick="openResetBaselineModal(<?php echo e($facility->id); ?>)" title="Reset Baseline"><i class="fa fa-repeat"></i></button>
                    <button onclick="toggleEngineerApproval(<?php echo e($facility->id); ?>)" title="Toggle Approval"><i class="fa fa-check-circle"></i></button>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="11" style="text-align:center; padding:20px;">No facilities found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Trend Chart -->
<div style="margin-bottom:2rem;">
    <canvas id="energyTrendChart" style="width:100%; max-width:800px; height:300px;"></canvas>
</div>

<!-- Modals -->
<?php echo $__env->make('modules.facilities.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const ctx = document.getElementById('energyTrendChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendLabels ?? [], 15, 512) ?>,
            datasets: [{
                label: 'kWh Consumption',
                data: <?php echo json_encode($trendData ?? [], 15, 512) ?>,
                borderColor:'#2563eb',
                backgroundColor:'rgba(37,99,235,0.2)',
                fill:true,
                tension:0.2,
                pointRadius:5
            }]
        },
        options:{
            responsive:true,
            plugins:{
                legend:{display:true},
                tooltip:{mode:'index', intersect:false}
            },
            scales:{
                y:{beginAtZero:true},
                x:{title:{display:true, text:'Month'}}
            }
        }
    });
});

// Reset Baseline
function openResetBaselineModal(facilityId){
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display='flex';
}

document.getElementById('resetBaselineForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    const id = document.getElementById('reset_facility_id').value;
    const reason = document.getElementById('reset_reason').value;

    fetch(`/modules/facilities/${id}/reset-baseline`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'},
        body:JSON.stringify({reason})
    }).then(res=>res.json()).then(data=>{
        alert(data.message||'Baseline reset!');
        document.getElementById('resetBaselineModal').style.display='none';
        location.reload();
    });
});

// Engineer Approval Toggle
function toggleEngineerApproval(facilityId){
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}
    }).then(res=>res.json()).then(data=>{
        alert(data.message||'Engineer approval toggled!');
        location.reload();
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/index.blade.php ENDPATH**/ ?>