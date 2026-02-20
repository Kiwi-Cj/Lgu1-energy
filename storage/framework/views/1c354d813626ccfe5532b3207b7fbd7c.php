<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Energy Monitoring Report</title>
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
    <h1>Annual Energy Monitoring Report</h1>
    <div class="meta">
        Year: <strong><?php echo e($selectedYear); ?></strong><br>
        Facility: <strong><?php echo e($selectedFacilityName); ?></strong><br>
        Generated: <strong><?php echo e($generatedAt); ?></strong>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Annual Actual kWh</td>
            <td class="num"><?php echo e(number_format($totalActualKwh ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Annual Baseline kWh</td>
            <td class="num"><?php echo e(number_format($annualBaseline ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Difference</td>
            <td class="num"><?php echo e(number_format($annualDifference ?? 0, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">Annual Status</td>
            <td><?php echo e($annualStatus ?? '-'); ?></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Month</th>
                <th class="num">Actual kWh</th>
                <th class="num">Baseline kWh</th>
                <th class="num">Difference</th>
                <th class="status">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $monthlyBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($row['label'] ?? '-'); ?></td>
                    <td class="num"><?php echo e(number_format($row['actual'] ?? 0, 2)); ?></td>
                    <td class="num"><?php echo e(number_format($row['baseline'] ?? 0, 2)); ?></td>
                    <td class="num"><?php echo e(number_format($row['diff'] ?? 0, 2)); ?></td>
                    <td class="status"><?php echo e($row['status'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5">No annual data available for selected filters.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        LGU Energy Monitoring System
    </div>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/energy-monitoring/annual-pdf.blade.php ENDPATH**/ ?>