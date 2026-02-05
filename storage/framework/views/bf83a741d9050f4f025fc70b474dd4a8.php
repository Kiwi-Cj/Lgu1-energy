

<?php $__env->startSection('content'); ?>

<!-- 🔹 PAGE HEADER (Facility-style) -->
<div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;margin:0;">LGU Energy Efficiency & Conservation Management System</h1>
        <div style="font-size:1.2rem;color:#555;">Dashboard Overview</div>
        <div style="margin-top:4px;font-size:0.95rem;color:#777;">
            Reporting Period: <strong><?php echo e($reportStart ?? 'January 2025'); ?></strong> – <strong><?php echo e($reportEnd ?? 'March 2025'); ?></strong>
        </div>
        <div style="font-size:0.9rem;color:#888;">
            <?php echo e(date('F j, Y')); ?> | Role: <?php echo e(Auth::user()->role ?? 'User'); ?>

        </div>
    </div>
</div>

<!-- 🔹 SUMMARY KPI CARDS -->
<div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:2rem;">

    <div class="card" style="flex:1;min-width:220px;background:#f5f8ff;box-shadow:0 4px 16px rgba(55,98,200,0.08);padding:24px 20px 20px 20px;border-radius:18px;">
        <div style="color:#3762c8;font-weight:700;letter-spacing:0.5px;">🏢 Total Facilities</div>
        <div style="font-size:2.2rem;font-weight:800;color:#3762c8;"><?php echo e($totalFacilities ?? 0); ?></div>
    </div>

    <div class="card" style="flex:1;min-width:220px;background:#f0fdf4;box-shadow:0 4px 16px rgba(34,197,94,0.08);padding:24px 20px 20px 20px;border-radius:18px;">
        <div style="color:#22c55e;font-weight:700;letter-spacing:0.5px;">⚡ Total Energy Consumption</div>
        <div style="font-size:2.2rem;font-weight:800;color:#22c55e;">
            <?php echo e(number_format($totalKwh ?? 0)); ?> kWh
        </div>
        <div style="font-size:1rem;color:#16a34a;opacity:0.85;">
            <?php if(!empty($kwhTrend) && $kwhTrend !== '+0.0%' && $kwhTrend !== '0.0%' && $kwhTrend !== '+0%' && $kwhTrend !== '0%'): ?>
                <?php echo e($kwhTrend); ?> vs last period
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="flex:1;min-width:220px;background:#fff7ed;box-shadow:0 4px 16px rgba(245,158,11,0.08);padding:24px 20px 20px 20px;border-radius:18px;">
        <div style="color:#f59e0b;font-weight:700;letter-spacing:0.5px;">💰 Total Energy Cost</div>
        <div style="font-size:2.2rem;font-weight:800;color:#f59e0b;">
            ₱<?php echo e(number_format($totalCost ?? 0, 2)); ?>

        </div>
    </div>

    <div class="card" style="flex:1;min-width:220px;background:#fff0f3;box-shadow:0 4px 16px rgba(225,29,72,0.08);padding:24px 20px 20px 20px;border-radius:18px;">
        <div style="color:#e11d48;font-weight:700;letter-spacing:0.5px;">🚨 Active Alerts</div>
        <div style="font-size:2.2rem;font-weight:800;color:#e11d48;">
            <?php echo e($activeAlerts ?? 0); ?>

        </div>
    </div>
</div>

<!-- 🔹 ENERGY PERFORMANCE OVERVIEW (ENHANCED CHARTS) -->
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
        Energy Performance Overview
    </h3>

    <div style="display:flex;gap:28px;flex-wrap:wrap;">
        <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
            <strong style="color:#222;text-shadow:0 1px 2px rgba(255,255,255,0.5),0 1px 2px rgba(0,0,0,0.15);">Monthly Energy Consumption (Actual vs Baseline)</strong>
            <div style="height:220px;">
                <canvas id="energyChart"></canvas>
            </div>
        </div>

        <div style="flex:1;min-width:420px;background:#fff;padding:18px;border-radius:12px;">
            <strong style="color:#222;text-shadow:0 1px 2px rgba(255,255,255,0.5),0 1px 2px rgba(0,0,0,0.15);">Energy Cost Trend</strong>
            <div style="height:220px;">
                <canvas id="costChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- 🔹 TOP ENERGY CONSUMERS -->
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;margin-bottom:12px;">
        🔥 Top Energy-Consuming Facilities
    </h3>

    <div style="background:#f5f8ff;border-radius:18px;box-shadow:0 4px 16px rgba(55,98,200,0.08);padding:18px 12px 12px 12px;overflow-x:auto;">
        <table style="width:100%;border-collapse:separate;border-spacing:0 6px;">
            <thead>
                <tr style="background:#e3eaff;">
                    <th style="padding:12px 10px;text-align:left;color:#3762c8;font-weight:700;font-size:1.05rem;letter-spacing:0.5px;border-top-left-radius:10px;">Facility</th>
                    <th style="padding:12px 10px;color:#3762c8;font-weight:700;font-size:1.05rem;letter-spacing:0.5px;">Monthly kWh</th>
                    <th style="padding:12px 10px;color:#3762c8;font-weight:700;font-size:1.05rem;letter-spacing:0.5px;border-top-right-radius:10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($topFacilities ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(55,98,200,0.04);transition:background 0.2s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='#fff'">
                        <td style="padding:12px 10px;border-radius:8px 0 0 8px;font-weight:600;color:#222;"><?php echo e($facility->name); ?></td>
                        <td style="padding:12px 10px;text-align:center;font-weight:700;color:#222;"><?php echo e(number_format($facility->monthly_kwh)); ?></td>
                        <td style="padding:12px 10px;text-align:center;border-radius:0 8px 8px 0;">
                            <?php if($facility->status === 'High'): ?>
                                <span style="background:#fff0f3;color:#e11d48;font-weight:700;padding:4px 14px;border-radius:16px;font-size:0.98rem;">High</span>
                            <?php elseif($facility->status === 'Medium'): ?>
                                <span style="background:#fff7ed;color:#f59e0b;font-weight:700;padding:4px 14px;border-radius:16px;font-size:0.98rem;">Medium</span>
                            <?php elseif($facility->status === 'Normal'): ?>
                                <span style="background:#f0fdf4;color:#22c55e;font-weight:700;padding:4px 14px;border-radius:16px;font-size:0.98rem;">Normal</span>
                            <?php else: ?>
                                <span style="color:#888;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" style="padding:14px;color:#888;text-align:center;background:#fff;border-radius:8px;">
                            No data available.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 🔹 RECENT SYSTEM ACTIVITY -->
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.3rem;font-weight:700;color:#3762c8;">Recent System Activity</h3>
    <ul style="padding-left:18px;color:#444;">
        <?php $__empty_1 = true; $__currentLoopData = collect($recentLogs ?? [])->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
        <?php $__empty_1 = true; $__currentLoopData = collect($alerts ?? [])->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li style="color:#e11d48;"><?php echo e($alert); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li style="color:#888;">No alerts generated.</li>
        <?php endif; ?>
    </ul>
</div>



<!-- 🔹 CHART.JS (DIRECT LOAD – ENHANCED CHARTS) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Monthly Energy Consumption Chart
    const energyCtx = document.getElementById('energyChart').getContext('2d');
    new Chart(energyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($energyChartLabels ?? ['Jan','Feb','Mar','Apr','May']); ?>,
            datasets: [
                {
                    label: 'Actual kWh',
                    data: <?php echo json_encode($energyChartData ?? [1200,1500,1100,1700,1600]); ?>,
                    backgroundColor: 'rgba(55,98,200,0.85)',
                    borderRadius: 8,
                    barThickness: 36
                },
                {
                    label: 'Baseline kWh',
                    data: <?php echo json_encode($baselineChartData ?? [1000,1400,1050,1500,1450]); ?>,
                    type: 'line',
                    borderColor: '#22c55e',
                    borderWidth: 3,
                    tension: 0.35,
                    pointRadius: 4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': ' + ctx.raw.toLocaleString() + ' kWh';
                        }
                    }
                }
            },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Energy Cost Trend Chart
    const costCtx = document.getElementById('costChart').getContext('2d');
    new Chart(costCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($costChartLabels ?? ['Jan','Feb','Mar','Apr','May']); ?>,
            datasets: [{
                label: 'Energy Cost (₱)',
                data: <?php echo json_encode($costChartData ?? [5000,6200,4800,7100,6600]); ?>,
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225,29,72,0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });

});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/dashboard/index.blade.php ENDPATH**/ ?>