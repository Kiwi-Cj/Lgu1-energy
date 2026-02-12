
<?php $__env->startSection('title', 'Energy Trend'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<style>
    /* Report Card Container */
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    /* Header Styling */
    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }
    .page-header h1 span { color: #2563eb; }
    .page-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 1rem;
    }

    /* Filter Bar */
    .filter-bar {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        margin: 25px 0;
        flex-wrap: wrap;
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .filter-group label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }
    .filter-group select {
        padding: 10px 15px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #fff;
        min-width: 180px;
        outline: none;
    }
    .filter-btn {
        background: #2563eb;
        color: #fff;
        padding: 11px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
    }
    .filter-btn:hover { background: #1d4ed8; }

    /* Summary Grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .summary-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        transition: transform 0.2s;
    }
    .summary-card:hover { transform: translateY(-3px); }
    .summary-card span {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .summary-card h2 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        color: #1e293b;
    }
    .summary-card p {
        margin: 5px 0 0;
        font-size: 0.85rem;
        color: #94a3b8;
    }

    /* Analysis Box */
    .analysis-container {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }
    .analysis-header {
        background: #f1f5f9;
        padding: 15px 20px;
        font-weight: 700;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .analysis-body { padding: 25px; }
    .insight-box {
        margin-top: 25px;
        background: #eff6ff;
        padding: 15px 20px;
        border-radius: 12px;
        border-left: 4px solid #2563eb;
        font-size: 0.95rem;
        color: #1e3a8a;
    }

    @media (max-width: 600px) {
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-group select { min-width: 100%; }
    }
</style>


<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>

<div class="report-card">
    <div class="page-header">
        <h1>
            <i class="fa-solid fa-chart-line" style="color: #2563eb;"></i>
            Energy <span>Trend Analysis</span>
        </h1>
        <p>Monitor and analyze energy consumption patterns to improve facility efficiency.</p>
    </div>

    <form class="filter-bar" method="GET" action="<?php echo e(route('energy.trend')); ?>">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" disabled selected>Select Facility</option>
                <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php echo e(request('facility_id') == $facility->id ? 'selected' : ''); ?>><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Year</label>
            <select name="year" id="year">
                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($year); ?>" <?php echo e($selectedYear == $year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" disabled selected>Select Month</option>
                <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($month['value']); ?>" <?php echo e(request('month') == $month['value'] ? 'selected' : ''); ?>>
                        <?php echo e(\Carbon\Carbon::createFromFormat('Y-m', substr($month['value'],0,7))->format('F')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <button class="filter-btn" type="submit">Apply Filter</button>
    </form>

    <div class="summary-grid">
        <div class="summary-card" style="border-top: 4px solid #2563eb;">
            <span>Total Consumption</span>
            <h2><?php echo e(number_format($totalConsumption ?? 0, 2)); ?> <small style="font-size:0.9rem; color:#64748b;">kWh</small></h2>
            <p>Aggregate usage for period</p>
        </div>

        <div class="summary-card" style="border-top: 4px solid #e11d48;">
            <span>Peak Usage</span>
            <h2><?php echo e(number_format($peakUsage ?? 0, 2)); ?> <small style="font-size:0.9rem; color:#64748b;">kWh</small></h2>
            <p>Highest recorded point</p>
        </div>

        <div class="summary-card" style="border-top: 4px solid #16a34a;">
            <span>Lowest Usage</span>
            <h2><?php echo e(number_format($lowestUsage ?? 0, 2)); ?> <small style="font-size:0.9rem; color:#64748b;">kWh</small></h2>
            <p>Most efficient point</p>
        </div>
    </div>

    <div class="analysis-container">
        <div class="analysis-header">
            <i class="fa-solid fa-bolt" style="color:#eab308;"></i>
            Consumption Visual Trend
        </div>

        <div class="analysis-body">
            <p style="color:#64748b; margin-bottom:20px; line-height:1.6;">
                The visualization below represents your facility's energy demand. Significant spikes may indicate equipment malfunction or 
                increased operational hours that require review.
            </p>

            
            <div style="min-height: 300px; background: #fcfcfc; border-radius: 8px; padding: 15px;">
                <?php echo $__env->make('modules.energy-monitoring.partials.charts', [
                    'chartData' => $trendData
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="insight-box">
                <i class="fa-solid fa-lightbulb" style="margin-right:8px;"></i>
                <strong>Trend Insight:</strong>
                <span>
                    Energy consumption usually follows operational cycles. Consistently high peaks during off-hours may suggest 
                    opportunities for automated power-down protocols.
                </span>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded', function() {
    var success = document.getElementById('successAlert');
    if (success) {
        setTimeout(() => {
            success.style.transition = 'opacity 0.5s ease';
            success.style.opacity = '0';
            setTimeout(() => success.remove(), 500);
        }, 3000);
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/trend.blade.php ENDPATH**/ ?>