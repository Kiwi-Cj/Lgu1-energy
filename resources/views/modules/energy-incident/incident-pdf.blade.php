<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energy Incident Report #{{ $incident->id }}</title>
    <style>
        @page { margin: 30px 34px; }
        body { margin: 0; color: #1e293b; font: 10.5px DejaVu Sans, sans-serif; }
        .header { border-bottom: 3px solid #dc2626; padding-bottom: 12px; margin-bottom: 15px; }
        h1 { margin: 0; color: #0f172a; font-size: 22px; }
        .subtitle { margin-top: 4px; color: #64748b; }
        .badges { margin-top: 9px; }
        .badge { display: inline-block; margin-right: 6px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 4px 8px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .critical { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
        .status { background: #fffbeb; border-color: #fde68a; color: #a16207; }
        .source { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 7px; margin: 0 -7px 12px; }
        .grid td { width: 50%; border: 1px solid #dbe2ea; background: #f8fafc; padding: 9px 10px; vertical-align: top; }
        .label { color: #64748b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .value { margin-top: 4px; color: #0f172a; font-size: 12px; font-weight: bold; }
        .section { margin-top: 13px; }
        .section h2 { margin: 0 0 5px; color: #334155; font-size: 9px; text-transform: uppercase; }
        .box { border: 1px solid #dbe2ea; border-radius: 6px; padding: 9px 10px; line-height: 1.5; }
        .cimm { border-color: #99f6e4; background: #f0fdfa; }
        .footer { position: fixed; right: 0; bottom: -12px; left: 0; color: #94a3b8; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Energy Incident Report</h1>
        <div class="subtitle">Incident #{{ $incident->id }} · Generated {{ $generatedAt->format('M d, Y h:i A') }}</div>
        <div class="badges">
            <span class="badge critical">{{ $severityLabel }}</span>
            <span class="badge status">{{ $statusLabel }}</span>
            <span class="badge source">{{ $sourceLabel }}</span>
        </div>
    </div>

    <table class="grid">
        <tr><td><div class="label">Facility</div><div class="value">{{ $facilityName }}</div></td><td><div class="label">Category</div><div class="value">{{ $categoryLabel }}</div></td></tr>
        <tr><td><div class="label">Detected</div><div class="value">{{ $detectedAt?->format('M d, Y h:i A') ?? 'N/A' }}</div></td><td><div class="label">Billing Period</div><div class="value">{{ $incident->month && $incident->year ? date('M/Y', mktime(0, 0, 0, (int) $incident->month, 1, (int) $incident->year)) : 'N/A' }}</div></td></tr>
        <tr><td><div class="label">Actual Reading</div><div class="value">{{ $actualKwh !== null ? number_format($actualKwh, 2).' kWh' : 'N/A' }}</div></td><td><div class="label">Baseline</div><div class="value">{{ $baselineKwh !== null ? number_format($baselineKwh, 2).' kWh' : 'N/A' }}</div></td></tr>
        <tr><td><div class="label">Deviation</div><div class="value">{{ $incident->deviation_percent !== null ? number_format((float) $incident->deviation_percent, 2).'%' : 'N/A' }}</div></td><td><div class="label">Affected Asset</div><div class="value">{{ $incident->affected_asset ?: 'Not specified' }}</div></td></tr>
    </table>

    <div class="section"><h2>Description</h2><div class="box">{{ $incident->description ?: 'No description provided.' }}</div></div>
    <div class="section"><h2>Probable Cause</h2><div class="box">{{ $incident->probable_cause ?: 'Automated analysis or CIMM inspection required.' }}</div></div>
    <div class="section"><h2>Immediate Action</h2><div class="box">{{ $incident->immediate_action ?: 'Forwarded to CIMM for maintenance assessment and action.' }}</div></div>
    <div class="section"><h2>Resolution</h2><div class="box">{{ $incident->resolution_summary ?: 'Awaiting maintenance status and resolution updates from CIMM.' }}</div></div>
    <div class="section"><h2>Preventive Recommendation</h2><div class="box">{{ $incident->preventive_recommendation ?: 'Follow CIMM inspection findings and continue monitoring the affected facility.' }}</div></div>

    <div class="section"><h2>CIMM Maintenance</h2><div class="box cimm">
        <strong>Action owner:</strong> CIMM Maintenance Integration<br>
        <strong>Maintenance status:</strong> {{ $incident->maintenance?->maintenance_status ?? ($statusLabel === 'Resolved' ? 'Completed' : 'Pending sync/action') }}<br>
        <strong>Assigned to:</strong> {{ $incident->maintenance?->assigned_to ?: 'Awaiting CIMM assignment' }}<br>
        <strong>Scheduled date:</strong> {{ $incident->maintenance?->scheduled_date ? \Carbon\Carbon::parse($incident->maintenance->scheduled_date)->format('M d, Y') : 'Not scheduled' }}
    </div></div>

    <div class="section"><h2>Evidence</h2><div class="box">{{ $incident->evidence_path ? 'Photo evidence attached in the Energy system: '.basename($incident->evidence_path) : 'No reporter evidence attached.' }}</div></div>
    <div class="section"><h2>Report Preparation</h2><div class="box">Prepared by {{ $preparedBy }} on {{ $generatedAt->format('M d, Y h:i A') }}.</div></div>

    <div class="footer">{{ $systemName }} &middot; CIMM-managed incident workflow</div>
</body>
</html>
