

<?php $__env->startSection('content'); ?>

<div style="max-width:1200px;margin:0 auto;">

    <!-- 🔹 PAGE HEADER -->
    <div style="margin-bottom:24px;">
        <h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">
            LGU Energy Efficiency & Conservation Management System
        </h1>

        <div style="font-size:1.2rem;color:#555;">Dashboard Overview</div>

        <div style="margin-top:6px;font-size:0.95rem;color:#777;">
            Reporting Period:
            <strong><?php echo e($reportStart ?? 'January 2025'); ?></strong> –
            <strong><?php echo e($reportEnd ?? 'March 2025'); ?></strong>
        </div>

        <div style="margin-top:4px;font-size:0.9rem;color:#888;">
            <?php echo e(date('F j, Y')); ?> | Role: <?php echo e(Auth::user()->role ?? 'User'); ?>

        </div>
    </div>

    <!-- 🔹 SUMMARY KPI CARDS -->
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:2rem;">

        <div class="card" style="flex:1;min-width:220px;background:#f5f8ff;padding:20px;border-radius:14px;">
            <div style="color:#3762c8;font-weight:600;">🏢 Total Facilities</div>
            <div style="font-size:2rem;font-weight:700;">
                <?php echo e($totalFacilities ?? 0); ?>

            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#f0fdf4;padding:20px;border-radius:14px;">
            <div style="color:#22c55e;font-weight:600;">⚡ Total Energy Consumption</div>
            <div style="font-size:2rem;font-weight:700;">
                <?php echo e(number_format($totalKwh ?? 0)); ?> kWh
            </div>
            <div style="font-size:0.9rem;color:#16a34a;">
                <?php echo e($kwhTrend ?? '+5.2%'); ?> vs last period
            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#fff7ed;padding:20px;border-radius:14px;">
            <div style="color:#f59e0b;font-weight:600;">💰 Total Energy Cost</div>
            <div style="font-size:2rem;font-weight:700;">
                ₱<?php echo e(number_format($totalCost ?? 0, 2)); ?>

            </div>
        </div>

        <div class="card" style="flex:1;min-width:220px;background:#fff0f3;padding:20px;border-radius:14px;">
            <div style="color:#e11d48;font-weight:600;">🚨 Active Alerts</div>
            <div style="font-size:2rem;font-weight:700;">
                <?php echo e($activeAlerts ?? 0); ?>

            </div>
        </div>
    </div>

    <!-- 🔹 ENERGY CHARTS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
            Energy Performance Overview
        </h3>

        <div style="display:flex;gap:28px;flex-wrap:wrap;">
            <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
                <strong>Monthly Energy Consumption (Actual vs Baseline)</strong>
                <canvas id="energyChart" height="180"></canvas>
            </div>

            <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
                <strong>Energy Cost Trend</strong>
                <canvas id="costChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- 🔹 TOP ENERGY CONSUMERS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
            🔥 Top Energy-Consuming Facilities
        </h3>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f7fb;">
                    <th style="padding:10px;text-align:left;">Facility</th>
                    <th style="padding:10px;">Monthly kWh</th>
                    <th style="padding:10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $topFacilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="padding:10px;"><?php echo e($facility->name); ?></td>
                        <td style="padding:10px;text-align:center;">
                            <?php echo e(number_format($facility->monthly_kwh)); ?>

                        </td>
                        <td style="padding:10px;text-align:center;">
                            <?php echo $facility->status_badge; ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" style="padding:12px;color:#888;">
                            No data available.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 🔹 RECENT SYSTEM ACTIVITY -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;">Recent System Activity</h3>
        <ul style="padding-left:18px;color:#444;">
            <?php $__empty_1 = true; $__currentLoopData = $recentLogs ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li><?php echo e($log); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li style="color:#888;">No recent activity recorded.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- 🔹 ALERTS -->
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.3rem;font-weight:700;color:#e11d48;">
            Alerts & Notifications
        </h3>

        <ul style="padding-left:18px;">
            <?php $__empty_1 = true; $__currentLoopData = $alerts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li style="color:#e11d48;"><?php echo e($alert); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li style="color:#888;">No alerts generated.</li>
            <?php endif; ?>
        </ul>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const energyChart = new Chart(document.getElementById('energyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($energyChartLabels ?? []); ?>,
        datasets: [
            {
                label: 'Actual kWh',
                data: <?php echo json_encode($energyChartData ?? []); ?>,
                backgroundColor: '#3762c8'
            },
            {
                label: 'Baseline kWh',
                data: <?php echo json_encode($baselineChartData ?? []); ?>,
                type: 'line',
                borderColor: '#22c55e',
                fill: false
            }
        ]
    }
});

const costChart = new Chart(document.getElementById('costChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($costChartLabels ?? []); ?>,
        datasets: [{
            label: 'Energy Cost (₱)',
            data: <?php echo json_encode($costChartData ?? []); ?>,
            borderColor: '#e11d48',
            fill: true
        }]
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/dashboard/index.blade.php ENDPATH**/ ?>