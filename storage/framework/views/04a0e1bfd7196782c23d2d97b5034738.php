<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energy Report PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 24px;
        }
        h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
            color: #0f172a;
        }
        .meta {
            margin-bottom: 14px;
            color: #475569;
            line-height: 1.35;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
        }
        .summary .label {
            width: 38%;
            font-weight: 700;
            background: #f8fafc;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th, table.data td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
        }
        table.data th {
            background: #f1f5f9;
            text-align: left;
        }
        .num {
            text-align: right;
        }
        .status {
            text-align: center;
            font-weight: 700;
        }
        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <h1>Energy Report</h1>
    <div class="meta">
        Period: <strong><?php echo e($selectedPeriod ?? 'All Periods'); ?></strong><br>
        Facility: <strong><?php echo e($selectedFacilityName ?? 'All Facilities'); ?></strong><br>
        Generated: <strong><?php echo e($generatedAt ?? now()->format('M d, Y h:i A')); ?></strong>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Total Actual kWh</td>
            <td class="num"><?php echo e(number_format($totalActualKwh ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Total Baseline kWh</td>
            <td class="num"><?php echo e(number_format($totalBaselineKwh ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Total Variance</td>
            <td class="num"><?php echo e(number_format($totalVarianceKwh ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Records Included</td>
            <td class="num"><?php echo e(count($energyData ?? [])); ?></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Facility</th>
                <th>Month</th>
                <th class="num">Actual kWh</th>
                <th class="num">Baseline kWh</th>
                <th class="num">Variance</th>
                <th class="status">Trend</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $energyData ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($row['facility'] ?? '-'); ?></td>
                    <td><?php echo e($row['month'] ?? '-'); ?></td>
                    <td class="num"><?php echo e($row['actual_kwh'] ?? '0.00'); ?></td>
                    <td class="num"><?php echo e($row['baseline_kwh'] ?? '0.00'); ?></td>
                    <td class="num"><?php echo e($row['variance'] ?? '0.00'); ?></td>
                    <td class="status"><?php echo e($row['trend'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6">No energy report data available for selected filters.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        LGU Energy Monitoring System
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/admin/reports/energy-pdf.blade.php ENDPATH**/ ?>