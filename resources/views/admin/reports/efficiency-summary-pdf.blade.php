<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Efficiency Summary Report</title>
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
        .footer {
            margin-top: 16px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <h1>Efficiency Summary Report</h1>
    <div class="meta">
        Facility: <strong>{{ $selectedFacilityName ?? 'All Facilities' }}</strong><br>
        Rating Filter: <strong>{{ $selectedRatingLabel ?? 'All Ratings' }}</strong><br>
        Generated: <strong>{{ $generatedAt ?? now()->format('M d, Y h:i A') }}</strong>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Facilities Included</td>
            <td>{{ count($efficiencyRows ?? []) }}</td>
        </tr>
        <tr>
            <td class="label">Energy Efficient</td>
            <td>{{ $highCount ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Moderate Efficiency</td>
            <td>{{ $mediumCount ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Requires Action</td>
            <td>{{ $flaggedCount ?? 0 }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Facility Name</th>
                <th>Avg. Monthly kWh</th>
                <th>EUI (kWh/sqm)</th>
                <th>Efficiency Band</th>
                <th>Data Coverage</th>
                <th>Action Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($efficiencyRows ?? [] as $row)
                <tr>
                    <td>{{ $row['facility'] ?? '-' }}</td>
                    <td>{{ $row['avg_monthly_kwh'] ?? '-' }}</td>
                    <td>{{ $row['eui'] ?? '-' }}</td>
                    <td>{{ $row['rating'] ?? '-' }}</td>
                    <td>{{ $row['months_count'] ?? 0 }} month(s), latest {{ $row['latest_period'] ?? 'No readings' }}</td>
                    <td>{{ $row['maintenance_status'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No efficiency data available for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        LGU Energy Monitoring System
    </div>
</body>
</html>
