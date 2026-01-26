
<?php $__env->startSection('title', 'Annual Energy Summary'); ?>
<?php $__env->startSection('content'); ?>
<div class="energy-annual-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
    <div style="display:flex;align-items:center;gap:18px;">
        <a href="<?php echo e(route('modules.energy.index')); ?>" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:5px;padding:2px 10px;font-size:0.85rem;min-width:0;height:32px;display:flex;align-items:center;gap:4px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M15 8a.5.5 0 0 1-.5.5H3.707l3.147 3.146a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L3.707 7.5H14.5A.5.5 0 0 1 15 8z"/></svg>
            <span style="display:none;display:inline-block;">Back</span>
        </a>
        <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin:0;">Annual Energy Summary</h2>
    </div>
    <form method="GET" action="" style="display:flex;align-items:center;gap:12px;">
        <label for="facility_id" style="font-weight:600;margin-right:6px;">Facility</label>
        <select name="facility_id" id="facility_id" class="form-control" style="min-width:140px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
            <option value="" disabled selected hidden>Select Facility</option>
            <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($facility->id); ?>" <?php if($selectedFacility == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <label for="year" style="font-weight:600;margin-right:6px;margin-left:10px;">Year</label>
        <select name="year" id="year" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
            <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($y); ?>" <?php if($selectedYear == $y): ?> selected <?php endif; ?>><?php echo e($y); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;">Go</button>
        <a
            href="<?php echo e(route('modules.energy.annual.export-excel', array_filter([
                'facility_id' => request('facility_id'),
                'year' => request('year'),
            ]))); ?>"
            class="btn btn-success"
            style="background: linear-gradient(90deg,#22c55e,#16a34a); color:#fff; font-weight:600; border:none; border-radius:5px; padding:2px 10px; font-size:0.85rem; min-width:0; height:32px; box-shadow:0 2px 8px rgba(31,38,135,0.10); text-decoration:none; transition:background 0.2s; display:flex; align-items:center; gap:4px;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5V13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2.6a.5.5 0 0 1 1 0V13a3 3 0 0 1-3 3H3a3 3 0 0 1-3-3v-2.6a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
            <span style="display:none;display:inline-block;">Excel</span>
        </a>
    </form>
</div>
<?php
    $filterActive = request()->has('facility_id') && request('facility_id');
?>
<?php if($filterActive): ?>
<div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Total Actual kWh</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e(($totalActualKwh ?? 0) != 0 ? $totalActualKwh : '-'); ?> kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Baseline</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e(($annualBaseline ?? 0) != 0 ? $annualBaseline : '-'); ?> kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Difference</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e(($annualDifference ?? 0) != 0 ? $annualDifference : '-'); ?> kWh</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Annual Status</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">
            <?php if(isset($annualStatus)): ?>
                <span style="color:<?php echo e($annualStatus == 'High' ? '#e11d48' : '#22c55e'); ?>;"><?php echo e($annualStatus); ?></span>
            <?php else: ?>
                -
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="mb-5">
    <h4 style="font-weight:600;color:#3762c8;margin-bottom:12px;">Monthly Breakdown</h4>
    <table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
        <thead style="background:#e9effc;">
            <tr>
                <th style="text-align:center;">Month</th>
                <th style="text-align:center;">Actual kWh</th>
                <th style="text-align:center;">Baseline kWh</th>
                <th style="text-align:center;">Difference</th>
                <th style="text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($monthlyBreakdown) > 0): ?>
                <?php $__currentLoopData = $monthlyBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($month['label']); ?></td>
                    <td><?php echo e($month['actual'] != 0 ? $month['actual'] : '-'); ?></td>
                    <td><?php echo e($month['baseline'] != 0 ? $month['baseline'] : '-'); ?></td>
                    <td><?php echo e($month['diff'] != 0 ? $month['diff'] : '-'); ?></td>
                    <td>
                        <span style="font-weight:600;color:<?php echo e($month['status'] == 'High' ? '#e11d48' : '#22c55e'); ?>;">
                            <?php echo e($month['status']); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No data for this filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mb-5">
    <h4 style="font-weight:600;color:#3762c8;margin-bottom:12px;">Monthly kWh Graph</h4>
    <canvas id="annualChart" height="120"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('annualChart').getContext('2d');
const annualChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyBreakdown, 'label'), 512) ?>,
        datasets: [
            {
                label: 'Actual kWh',
                data: <?php echo json_encode(array_column($monthlyBreakdown, 'actual'), 512) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Baseline kWh',
                data: <?php echo json_encode(array_column($monthlyBreakdown, 'baseline'), 512) ?>,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.08)',
                fill: true,
                tension: 0.3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy/annual/annual.blade.php ENDPATH**/ ?>