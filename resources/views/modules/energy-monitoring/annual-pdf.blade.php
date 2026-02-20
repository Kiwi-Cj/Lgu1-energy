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
        Year: <strong>{{ $selectedYear }}</strong><br>
        Facility: <strong>{{ $selectedFacilityName }}</strong><br>
        Generated: <strong>{{ $generatedAt }}</strong>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Annual Actual kWh</td>
            <td class="num">{{ number_format($totalActualKwh ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Annual Baseline kWh</td>
            <td class="num">{{ number_format($annualBaseline ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Difference</td>
            <td class="num">{{ number_format($annualDifference ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Annual Status</td>
            <td>{{ $annualStatus ?? '-' }}</td>
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
            @forelse($monthlyBreakdown as $row)
                <tr>
                    <td>{{ $row['label'] ?? '-' }}</td>
                    <td class="num">{{ number_format($row['actual'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($row['baseline'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($row['diff'] ?? 0, 2) }}</td>
                    <td class="status">{{ $row['status'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No annual data available for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        LGU Energy Monitoring System
    </div>
</body>
</html>

