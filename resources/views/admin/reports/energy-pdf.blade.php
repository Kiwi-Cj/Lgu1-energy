<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energy Performance Report</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1e293b; margin: 0; }
        h1 { margin: 0; font-size: 22px; color: #0f172a; }
        h2 { margin: 0 0 7px; font-size: 13px; color: #0f172a; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 12px; }
        .subtitle { margin-top: 3px; color: #64748b; font-size: 10px; }
        .meta { width: 100%; margin-top: 9px; border-collapse: collapse; }
        .meta td { padding: 2px 12px 2px 0; color: #475569; }
        .meta strong { color: #1e293b; }
        .section { margin-top: 13px; }
        .summary-box, .recommendation-box { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 7px; padding: 10px 12px; line-height: 1.55; }
        .recommendation-box { background: #f8fafc; border-color: #cbd5e1; }
        .kpis { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-left: -6px; margin-right: -6px; }
        .kpis td { width: 25%; border: 1px solid #cbd5e1; background: #fff; padding: 9px; vertical-align: top; }
        .kpi-label { color: #64748b; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .kpi-value { margin-top: 4px; color: #0f172a; font-size: 15px; font-weight: 700; }
        .kpi-note { margin-top: 3px; color: #64748b; font-size: 8px; }
        .assessment { display: inline-block; margin-top: 7px; padding: 4px 8px; border-radius: 12px; background: #f1f5f9; border: 1px solid #cbd5e1; font-weight: 700; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 6px; }
        table.data th { background: #f1f5f9; color: #334155; text-align: left; font-size: 8.5px; text-transform: uppercase; }
        .num { text-align: right; }
        .center { text-align: center; }
        .meter-meta { color: #64748b; font-size: 8px; margin-top: 2px; }
        .detail-note { margin-top: 8px; color: #64748b; font-size: 8px; line-height: 1.4; }
        .signatures { width: 100%; margin-top: 28px; border-collapse: separate; border-spacing: 24px 0; }
        .signatures td { width: 50%; text-align: center; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #64748b; padding-top: 5px; font-weight: 700; }
        .signature-role { color: #64748b; font-size: 8px; margin-top: 2px; }
        .footer { position: fixed; bottom: -12px; left: 0; right: 0; text-align: center; color: #94a3b8; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Energy Performance Report</h1>
        <div class="subtitle">Facility consumption, baseline performance, and recommended action</div>
        <table class="meta">
            <tr>
                <td>Facility: <strong>{{ $selectedFacilityName ?? 'All Facilities' }}</strong></td>
                <td>Period: <strong>{{ $selectedPeriod ?? 'All Periods' }}</strong></td>
            </tr>
            <tr>
                <td>Prepared by: <strong>{{ $preparedBy ?? 'System User' }}</strong></td>
                <td>Generated: <strong>{{ $generatedAt ?? now()->format('M d, Y h:i A') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Executive Summary</h2>
        <div class="summary-box">
            {{ $executiveSummary ?? 'No energy performance data is available for the selected filters.' }}
            <div class="assessment">Assessment: {{ $overallAssessment ?? 'No Data' }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Performance Indicators</h2>
        <table class="kpis">
            <tr>
                <td>
                    <div class="kpi-label">Actual Consumption</div>
                    <div class="kpi-value">{{ number_format($totalActualKwh ?? 0, 2) }}</div>
                    <div class="kpi-note">kWh consumed</div>
                </td>
                <td>
                    <div class="kpi-label">Approved Baseline</div>
                    <div class="kpi-value">{{ number_format($totalBaselineKwh ?? 0, 2) }}</div>
                    <div class="kpi-note">kWh target</div>
                </td>
                <td>
                    <div class="kpi-label">Variance</div>
                    <div class="kpi-value">{{ ($totalVarianceKwh ?? 0) > 0 ? '+' : '' }}{{ number_format($totalVarianceKwh ?? 0, 2) }}</div>
                    <div class="kpi-note">{{ $overallVariancePercent !== null ? (($overallVariancePercent > 0 ? '+' : '') . number_format($overallVariancePercent, 2) . '%') : 'No valid baseline' }}</div>
                </td>
                <td>
                    <div class="kpi-label">Estimated Cost</div>
                    <div class="kpi-value">PHP {{ number_format($totalEnergyCost ?? 0, 2) }}</div>
                    <div class="kpi-note">Selected period</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Recommended Action</h2>
        <div class="recommendation-box">{{ $primaryRecommendation ?: 'Continue monthly monitoring and validate all meter readings against the approved facility baseline.' }}</div>
    </div>

    <div class="section">
        <h2>Record Details</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Facility / Main Meter</th>
                    <th>Period</th>
                    <th class="num">Actual</th>
                    <th class="num">Baseline</th>
                    <th class="num">Variance</th>
                    <th class="num">EUI</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @forelse($energyData ?? [] as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['facility'] ?? '-' }}</strong>
                            <div class="meter-meta">{{ $row['meter'] ?? 'Main Meter' }}{{ !empty($row['meter_number']) ? ' - ' . $row['meter_number'] : '' }}</div>
                        </td>
                        <td>{{ $row['month'] ?? '-' }}</td>
                        <td class="num">{{ $row['actual_kwh'] ?? '0.00' }}</td>
                        <td class="num">{{ $row['baseline_kwh'] ?? 'N/A' }}</td>
                        <td class="num">{{ $row['variance'] ?? 'N/A' }}<div class="meter-meta">{{ $row['variance_percent'] ?? 'N/A' }}</div></td>
                        <td class="num">{{ $row['eui'] ?? 'N/A' }}</td>
                        <td>{{ $row['trend'] ?? 'Insufficient Historical Data' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">No energy report data available for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="detail-note">EUI is expressed in kWh per square meter. Trend requires at least one valid earlier period for the same facility; otherwise it is reported as insufficient historical data.</div>
    </div>

    <table class="signatures">
        <tr>
            <td><div class="signature-line">{{ $preparedBy ?? 'Prepared By' }}</div><div class="signature-role">Prepared by</div></td>
            <td><div class="signature-line">&nbsp;</div><div class="signature-role">Reviewed / Approved by</div></td>
        </tr>
    </table>

    <div class="footer">LGU Energy Monitoring System | Generated report for official monitoring use</div>
</body>
</html>
