
<?php $__env->startSection('title', 'Dashboard Overview'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* --- Shared Dashboard UI Aesthetic --- */
    .report-card-container {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 12px rgba(31,38,135,0.06); 
        padding: 30px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
    }

    .stat-card {
        flex: 1;
        min-width: 200px;
        padding: 24px;
        border-radius: 16px;
        transition: transform 0.2s ease;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .chart-container {
        background: #ffffff;
        padding: 24px;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        height: 100%;
    }

    .table-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 0; /* Let the header/body handle padding */
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-table thead th {
        padding: 16px;
        color: #3762c8;
        font-weight: 700;
        text-align: left;
        background: #f8fafc;
        border-bottom: 2px solid #e9effc;
    }

    .custom-table tbody tr td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .custom-table tbody tr:hover {
        background: #fcfdfe;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .chart-grid { flex-direction: column; }
        .chart-item { width: 100% !important; }
    }
</style>

<div style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2.5rem;">
            <div>
                <h1 style="font-size:1.8rem; font-weight:800; color:#1e293b; margin:0; letter-spacing:-0.5px;">⚡ Energy Efficiency Overview</h1>
                <p style="font-size:1rem; color:#64748b; margin-top:4px;">Real-time monitoring and analytics for LGU facilities.</p>
                <div style="font-size:0.85rem; color:#94a3b8; margin-top:8px; display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-calendar"></i>
                    <span>Period: <strong><?php echo e(now()->subMonths(5)->format('F')); ?></strong> – <strong><?php echo e(now()->format('F Y')); ?></strong></span>
                </div>
            </div>
            <div style="text-align:right;">
                <span style="background:#eef2ff; color:#4f46e5; padding:10px 18px; border-radius:12px; font-weight:800; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; border: 1px solid #e0e7ff;">
                    <i class="fa fa-shield"></i> <?php echo e(Auth::user()->role ?? 'Administrator'); ?>

                </span>
            </div>
        </div>

        <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:2.5rem;">
            <div class="stat-card" style="background:#f0f7ff;">
                <div style="color:#3762c8; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Total Facilities</div>
                <div style="font-size:2rem; font-weight:800; color:#1e3a8a;"><?php echo e($totalFacilities ?? 0); ?></div>
            </div>

            <div class="stat-card" style="background:#f0fdf4;">
                <div style="color:#16a34a; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Net Consumption</div>
                <div style="font-size:2rem; font-weight:800; color:#14532d;"><?php echo e(number_format($totalKwh ?? 0)); ?> <small style="font-size:0.9rem;">kWh</small></div>
                <div style="font-size:0.8rem; font-weight:700; color:#166534; margin-top:5px;">
                    <i class="fa fa-caret-up"></i> <?php echo e($kwhTrend ?? '0%'); ?> <span style="font-weight:500; opacity:0.8;">vs last period</span>
                </div>
            </div>

            <div class="stat-card" style="background:#fffbeb;">
                <div style="color:#d97706; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Total Expenditure</div>
                <div style="font-size:2rem; font-weight:800; color:#78350f;">₱<?php echo e(number_format($totalCost ?? 0, 0)); ?></div>
            </div>

            <div class="stat-card" style="background:#fef2f2;">
                <div style="color:#dc2626; font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">System Alerts</div>
                <div style="font-size:2rem; font-weight:800; color:#7f1d1d;"><?php echo e($activeAlerts ?? 0); ?></div>
            </div>
        </div>

        <div class="chart-grid" style="display:flex; gap:24px; margin-bottom:2.5rem;">
            <div class="chart-item" style="flex:1;">
                <div class="chart-container">
                    <h3 style="font-size:1rem; font-weight:800; color:#334155; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span style="width:8px; height:8px; background:#3762c8; border-radius:50%;"></span>
                        Actual vs Baseline Consumption
                    </h3>
                    <div style="height:320px;"><canvas id="energyChart"></canvas></div>
                </div>
            </div>

            <div class="chart-item" style="flex:1;">
                <div class="chart-container">
                    <h3 style="font-size:1rem; font-weight:800; color:#334155; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                        <span style="width:8px; height:8px; background:#e11d48; border-radius:50%;"></span>
                        Monthly Cost Trend
                    </h3>
                    <div style="height:320px;"><canvas id="costChart"></canvas></div>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap:24px;">
            
            <div style="border: 1px solid #f1f5f9; border-radius: 18px; overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; background: #fff;">
                    <h3 style="font-size:1rem; font-weight:800; color:#1e293b; margin:0;">🔥 High Consumption Hubs</h3>
                </div>
                <div style="overflow-x:auto;">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Facility Name</th>
                                <th style="text-align:center;">Total kWh (6mo)</th>
                                <th style="text-align:center;">Total Baseline (6mo)</th>
                                <th style="text-align:center;">Deviation %</th>
                                <th style="text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = ($topFacilities ?? collect())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td style="font-weight:700; color:#334155;"><?php echo e($facility->name); ?></td>
                                <td style="text-align:center; font-weight:800; color:#1e293b;"><?php echo e(number_format($facility->total_kwh, 2)); ?></td>
                                <td style="text-align:center; font-weight:700; color:#64748b;"><?php echo e(number_format($facility->baseline_kwh, 2)); ?></td>
                                <td style="text-align:center; font-weight:700; color:#e11d48;"><?php echo e($facility->deviation); ?>%</td>
                                <td style="text-align:center;">
                                    <?php
                                        $status = $facility->status ?? 'Normal';
                                        $bg = $status == 'High' ? '#fef2f2' : ($status == 'Medium' ? '#fffbeb' : '#f0fdf4');
                                        $text = $status == 'High' ? '#dc2626' : ($status == 'Medium' ? '#d97706' : '#16a34a');
                                    ?>
                                    <span style="background:<?php echo e($bg); ?>; color:<?php echo e($text); ?>; padding:5px 12px; border-radius:100px; font-size:0.75rem; font-weight:800; text-transform:uppercase;">
                                        <?php echo e($status); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" style="text-align:center; padding:30px; color:#94a3b8;">No records found for this period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="border: 1px solid #f1f5f9; border-radius: 18px; padding: 24px; background: #fff;">
                <h3 style="font-size:1rem; font-weight:800; color:#e11d48; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                    <i class="fa fa-bell"></i> Critical Notifications
                </h3>
                
                <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
                    <?php $__empty_1 = true; $__currentLoopData = collect($alerts ?? [])->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div style="background:#fff1f2; border-left:4px solid #e11d48; padding:15px; border-radius:10px; color:#9f1239; font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:10px;">
                            <i class="fa fa-exclamation-triangle"></i> <?php echo e($alert); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div style="padding:20px; text-align:center; background:#f8fafc; border-radius:12px; color:#94a3b8; font-size:0.9rem;">
                            <i class="fa fa-check-circle" style="color:#22c55e;"></i> All systems operational.
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Shared chart options for cleaner look
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Inter', weight: 600 } } }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Inter' } } },
            x: { grid: { display: false }, ticks: { font: { family: 'Inter' } } }
        }
    };

    const energyCtx = document.getElementById('energyChart').getContext('2d');
    new Chart(energyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($energyChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']); ?>,
            datasets: [
                {
                    label: 'Actual Usage (kWh)',
                    data: <?php echo json_encode($energyChartData ?? [1200,1500,1100,1700,1600,1400]); ?>,
                    backgroundColor: '#3762c8',
                    borderRadius: 8,
                    barThickness: 20
                },
                {
                    label: 'Efficiency Baseline',
                    data: <?php echo json_encode($baselineChartData ?? [1000,1400,1050,1500,1450,1350]); ?>,
                    type: 'line',
                    borderColor: '#22c55e',
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: commonOptions
    });

    const costCtx = document.getElementById('costChart').getContext('2d');
    new Chart(costCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($costChartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun']); ?>,
            datasets: [{
                label: 'Monthly Cost (₱)',
                data: <?php echo json_encode($costChartData ?? [5000,6200,4800,7100,6600,5800]); ?>,
                borderColor: '#e11d48',
                backgroundColor: 'rgba(225,29,72,0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#e11d48'
            }]
        },
        options: commonOptions
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/dashboard/index.blade.php ENDPATH**/ ?>