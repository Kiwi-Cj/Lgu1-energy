@php
    $energyTab = $energyTab ?? '';
@endphp

<style>
    .em-page {
        padding: 14px;
        display: grid;
        gap: 12px;
    }

    .em-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .em-header h2 {
        margin: 0;
        color: #1e3a8a;
        font-size: 1.45rem;
        font-weight: 800;
    }

    .em-header-subtitle {
        margin-top: 4px;
        color: #64748b;
    }

    .em-header-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .em-action-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 700;
        color: #334155;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .em-action-btn.primary {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .em-action-btn.soft {
        background: #f8fafc;
        color: #334155;
    }

    .em-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }

    .em-filter {
        padding: 12px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .em-table-wrap {
        overflow-x: auto;
    }

    .em-panel-footer {
        padding: 12px 14px;
        border-top: 1px solid #e2e8f0;
        background: #fcfdff;
    }

    .ems-switcher {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 10px;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
    }

    .ems-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        font-size: .84rem;
        font-weight: 800;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 8px 12px;
        background: #fff;
        transition: all .15s ease;
    }

    .ems-tab:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, .08);
    }

    .ems-tab.active {
        color: #fff;
        border-color: #1d4ed8;
        background: linear-gradient(90deg, #2563eb, #1d4ed8);
        box-shadow: 0 8px 18px rgba(37, 99, 235, .24);
    }

    body.dark-mode .ems-switcher {
        background: #0f172a;
        border-color: #334155;
    }

    body.dark-mode .em-action-btn.soft {
        background: #111827;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .em-panel {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .em-filter,
    body.dark-mode .em-panel-footer {
        background: #0f172a;
        border-color: #334155;
    }

    body.dark-mode .em-header h2,
    body.dark-mode .em-header-subtitle {
        color: #e2e8f0;
    }

    body.dark-mode .ems-tab {
        background: #111827;
        border-color: #334155;
        color: #cbd5e1;
    }

    body.dark-mode .ems-tab.active {
        color: #fff;
        border-color: #2563eb;
        background: linear-gradient(90deg, #1d4ed8, #2563eb);
    }
</style>

<div class="ems-switcher">
    <a href="{{ route('modules.energy-monitoring.index') }}" class="ems-tab{{ $energyTab === 'facility' ? ' active' : '' }}">
        <i class="fa-solid fa-building"></i> Facility Monitoring
    </a>
    <a href="{{ route('modules.main-meter.monitoring') }}" class="ems-tab{{ $energyTab === 'main' ? ' active' : '' }}">
        <i class="fa-solid fa-bolt"></i> Main Meter
    </a>
    <a href="{{ route('modules.submeters.monitoring') }}" class="ems-tab{{ $energyTab === 'sub' ? ' active' : '' }}">
        <i class="fa-solid fa-network-wired"></i> Submeter
    </a>
    <a href="{{ route('modules.load-tracking.index') }}" class="ems-tab{{ $energyTab === 'load' ? ' active' : '' }}">
        <i class="fa-solid fa-plug-circle-bolt"></i> Load Tracking
    </a>
    <a href="{{ route('modules.ai-alerts.index') }}" class="ems-tab{{ $energyTab === 'ai' ? ' active' : '' }}">
        <i class="fa-solid fa-robot"></i> AI Alerts
    </a>
</div>
