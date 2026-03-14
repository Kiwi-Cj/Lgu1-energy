@extends('layouts.qc-admin')
@section('title', 'Submeter Alerts')

@section('content')
<div class="em-page">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif

    @php
        $energyTab = 'sub';
    @endphp
    @include('layouts.partials.energy_monitoring_switcher')

    <div class="em-header">
        <div>
            <h2>Submeter Alerts Timeline</h2>
            <div class="em-header-subtitle">Warning and critical deviations against computed baselines.</div>
        </div>
        <a href="{{ route('modules.submeters.monitoring') }}" class="em-action-btn soft">
            Back to Monitoring
        </a>
    </div>

    <div class="em-panel">
        <form method="GET" action="{{ route('modules.submeters.alerts') }}" class="em-filter">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:220px;flex:1;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Facility</label>
                <select name="facility_id" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Alert Level</label>
                <select name="alert_level" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All</option>
                    <option value="warning" @selected($selectedLevel === 'warning')>Warning</option>
                    <option value="critical" @selected($selectedLevel === 'critical')>Critical</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Filter</button>
                <a href="{{ route('modules.submeters.alerts') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div class="em-table-wrap">
            <table style="width:100%;min-width:1100px;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Submeter</th>
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Facility</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Period</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Current kWh</th>
                        <th style="padding:12px 14px;text-align:right;border-bottom:1px solid #e2e8f0;">Baseline kWh</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Increase</th>
                        <th style="padding:12px 14px;text-align:center;border-bottom:1px solid #e2e8f0;">Level</th>
                        <th style="padding:12px 14px;text-align:left;border-bottom:1px solid #e2e8f0;">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        @php
                            $level = strtolower((string) $alert->alert_level);
                            $rowBg = $level === 'critical' ? '#fef2f2' : ($level === 'warning' ? '#fffbeb' : '#ffffff');
                        @endphp
                        <tr style="background:{{ $rowBg }};">
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;">
                                <a href="{{ route('modules.submeters.show', $alert->submeter_id) }}" style="text-decoration:none;color:#1e3a8a;font-weight:800;">
                                    {{ $alert->submeter?->submeter_name ?? '-' }}
                                </a>
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">
                                {{ $alert->submeter?->facility?->name ?? '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;color:#334155;">
                                {{ $alert->reading?->period_end_date?->format('Y-m-d') ?? '-' }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;color:#0f172a;">
                                {{ number_format((float) $alert->current_value_kwh, 2) }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#1d4ed8;font-weight:700;">
                                {{ number_format((float) $alert->baseline_value_kwh, 2) }}
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;font-weight:800;color:#be123c;">
                                {{ number_format((float) $alert->increase_percent, 2) }}%
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                @if($level === 'critical')
                                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">CRITICAL</span>
                                @else
                                    <span style="background:#fef3c7;color:#92400e;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">WARNING</span>
                                @endif
                            </td>
                            <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">
                                {{ $alert->reason ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:20px;text-align:center;color:#64748b;">No alerts found for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="em-panel-footer">
            {{ $alerts->links() }}
        </div>
    </div>
</div>
@endsection
