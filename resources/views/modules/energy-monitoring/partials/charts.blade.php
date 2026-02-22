
<!-- Chart templates for trend analysis -->
<style>
    .energy-trend-page .charts-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    .energy-trend-page .chart-empty {
        text-align: center;
        padding: 2rem;
        color: #6b7280;
    }
    .energy-trend-page .chart-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
    .energy-trend-page .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .energy-trend-page .chart-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #3762c8;
    }
    .energy-trend-page .chart-period {
        color: #6b7280;
        font-size: .95rem;
    }
    .energy-trend-page .energy-trend-canvas {
        width: 100%;
        max-width: 900px;
        height: 350px;
    }

    body.dark-mode .energy-trend-page .chart-empty {
        color: #94a3b8;
    }
    body.dark-mode .energy-trend-page .chart-card {
        background: #111827;
        border: 1px solid #334155;
        box-shadow: none;
    }
    body.dark-mode .energy-trend-page .chart-title {
        color: #93c5fd;
    }
    body.dark-mode .energy-trend-page .chart-period {
        color: #94a3b8;
    }
</style>

<div id="chartsContainer" class="charts-container">
    <!-- Loading state -->
    @if(empty($chartData) || empty($chartData['labels']) || empty($chartData['values']))
        <div class="chart-empty">No data available for the selected month and year.</div>
    @else
        <div class="chart-card">
            <div class="chart-header">
                <h4 class="chart-title">Energy Consumption Trend</h4>
                <span class="chart-period">{{ $chartData['period'] ?? '' }}</span>
            </div>
            <canvas id="energyTrendChart" class="energy-trend-canvas"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var isDark = document.body.classList.contains('dark-mode');
            var lineColor = isDark ? '#93c5fd' : '#3762c8';
            var fillColor = isDark ? 'rgba(59,130,246,0.18)' : 'rgba(55,98,200,0.2)';
            var tickColor = isDark ? '#94a3b8' : '#6b7280';
            var gridColor = isDark ? 'rgba(148,163,184,0.2)' : 'rgba(148,163,184,0.28)';

            var ctx = document.getElementById('energyTrendChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels'] ?? []),
                    datasets: [{
                        label: 'kWh Consumed',
                        data: @json($chartData['values'] ?? []),
                        backgroundColor: fillColor,
                        borderColor: lineColor,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: lineColor,
                        pointBorderColor: lineColor,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: { color: tickColor }
                        },
                        title: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: tickColor },
                            grid: { color: gridColor },
                            title: { display: true, text: 'kWh', color: tickColor }
                        },
                        x: {
                            ticks: { color: tickColor },
                            grid: { color: gridColor },
                            title: { display: true, text: 'Month', color: tickColor }
                        }
                    }
                }
            });
        });
        </script>
    @endif
</div>
