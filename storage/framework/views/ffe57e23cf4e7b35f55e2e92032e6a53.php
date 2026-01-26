
<?php $__env->startSection('title', 'Reports Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1200px;margin:0 auto;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">Reports Dashboard</h2>
    <!-- TOP SUMMARY CARDS -->
    <div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Total Facilities</div>
            <div id="card-total-facilities" style="font-size:2rem;font-weight:700;margin:8px 0;">...</div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">Total kWh (Selected Period)</div>
            <div id="card-total-kwh" style="font-size:2rem;font-weight:700;margin:8px 0;">...</div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#f59e42;">Total Energy Cost (₱)</div>
            <div id="card-total-cost" style="font-size:2rem;font-weight:700;margin:8px 0;">...</div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">Low Efficiency Facilities</div>
            <div id="card-low-efficiency" style="font-size:2rem;font-weight:700;margin:8px 0;">...</div>
        </div>
    </div>
    <!-- FILTERS (optional, for future use) -->
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCards(data) {
        document.getElementById('card-total-facilities').textContent = data.totalFacilities ?? '-';
        document.getElementById('card-total-kwh').textContent = data.totalKwh ?? '-';
        document.getElementById('card-total-cost').textContent = data.totalCost ?? '-';
        document.getElementById('card-low-efficiency').textContent = data.lowEfficiencyCount ?? '-';
    }
    function fetchSummary() {
        fetch('/modules/reports/dashboard-summary')
            .then(res => res.json())
            .then(updateCards)
            .catch(() => {
                document.getElementById('card-total-facilities').textContent = '-';
                document.getElementById('card-total-kwh').textContent = '-';
                document.getElementById('card-total-cost').textContent = '-';
                document.getElementById('card-low-efficiency').textContent = '-';
            });
    }
    fetchSummary();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/reports/index.blade.php ENDPATH**/ ?>