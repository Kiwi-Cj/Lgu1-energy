
<!-- Chart templates for trend analysis -->
<div id="chartsContainer" style="width:100%;display:flex;flex-direction:column;gap:2rem;">
    <!-- Loading state -->
    <?php if(empty($chartData) || empty($chartData['labels']) || empty($chartData['values'])): ?>
        <div style="text-align:center;padding:2rem;color:#6b7280;">No data available for the selected month and year.</div>
    <?php else: ?>
        <div class="chart-card" style="background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:2rem; margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <h4 style="font-size:1.3rem;font-weight:600;color:#3762c8;">Energy Consumption Trend</h4>
                <span style="color:#6b7280;font-size:.95rem;"><?php echo e($chartData['period'] ?? ''); ?></span>
            </div>
            <canvas id="energyTrendChart" style="width:100%;max-width:900px;height:350px;"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('energyTrendChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chartData['labels'] ?? [], 15, 512) ?>,
                    datasets: [{
                        label: 'kWh Consumed',
                        data: <?php echo json_encode($chartData['values'] ?? [], 15, 512) ?>,
                        backgroundColor: 'rgba(55,98,200,0.2)',
                        borderColor: '#3762c8',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        title: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'kWh' } },
                        x: { title: { display: true, text: 'Month' } }
                    }
                }
            });
        });
        </script>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-monitoring/partials/charts.blade.php ENDPATH**/ ?>