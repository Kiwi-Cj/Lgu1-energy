@extends('layouts.qc-admin')
@section('title', 'Linked Sub-meters')

@php
    $mainMeter = $mainMeter ?? null;
    $subMeters = $subMeters ?? collect();
    $linkedSubCount = $linkedSubCount ?? 0;
    $activeLinkedSubCount = $activeLinkedSubCount ?? 0;
    $approvedLinkedSubCount = $approvedLinkedSubCount ?? 0;
    $archivedSubCount = $archivedSubCount ?? 0;
    $canManageMeters = $canManageMeters ?? false;
    $canApproveMeters = $canApproveMeters ?? false;
@endphp

<style>
    .submeters-page {
        --panel-bg: #ffffff;
        --panel-border: #e2e8f0;
        --panel-head: #eef2ff;
        --text-main: #0f172a;
        --text-sub: #475569;
    }

    .submeter-list-card {
        background: var(--panel-bg);
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(15,23,42,.08);
        padding: 16px 18px;
        margin-top: 14px;
    }

    .submeter-list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .submeter-list-title {
        font-weight: 800;
        color: #1e293b;
    }

    .submeter-list-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .submeter-list-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 10px;
        padding: 8px 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .submeter-list-btn.primary {
        border: none;
        background: #2563eb;
        color: #fff;
        cursor: pointer;
        padding: 9px 12px;
    }

    .submeter-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .submeter-search-input {
        width: min(420px, 100%);
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: .84rem;
        color: #0f172a;
        background: #fff;
    }

    .submeter-table-note {
        font-size: .76rem;
        color: #64748b;
        font-weight: 700;
    }

    .submeter-table-wrap {
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        overflow: auto;
        background: #fff;
    }

    .submeter-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .submeter-table thead th {
        padding: 11px 12px;
        text-align: left;
        color: #1e293b;
        border-bottom: 1px solid #dbeafe;
        background: var(--panel-head);
        font-size: .92rem;
        font-weight: 800;
    }

    .submeter-table thead th.right,
    .submeter-table tbody td.right {
        text-align: right;
    }

    .submeter-table thead th.center,
    .submeter-table tbody td.center {
        text-align: center;
    }

    .submeter-table tbody tr {
        transition: background-color .15s ease;
    }

    .submeter-table tbody tr:hover {
        background: #f8fafc;
    }

    .submeter-table tbody td {
        padding: 12px;
        border-bottom: 1px solid var(--panel-border);
        color: #334155;
        font-size: .93rem;
        vertical-align: middle;
    }

    .submeter-table tbody tr:last-child td {
        border-bottom: none;
    }

    .submeter-name-cell {
        font-weight: 800;
        font-size: 1.04rem;
        color: var(--text-main);
    }

    .submeter-baseline-cell {
        color: var(--text-main);
        font-weight: 800;
        font-size: 1.02rem;
    }

    .submeter-status-pill {
        display: inline-flex;
        border-radius: 999px;
        padding: 2px 9px;
        font-size: .72rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .submeter-action-wrap {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
    }

    .submeter-action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
    }

    .submeter-action-btn.danger {
        border-color: #fecaca;
        background: #fee2e2;
        color: #b91c1c;
    }

    .submeter-filter-empty {
        display: none;
        margin-top: 10px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 14px;
        color: #64748b;
        font-weight: 700;
    }

    .submeter-row-clickable {
        cursor: pointer;
    }

    .submeter-row-clickable:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: -2px;
    }

    .submeter-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .submeter-detail-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 9px 10px;
    }

    .submeter-detail-item-label {
        font-size: .74rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .submeter-detail-item-value {
        margin-top: 3px;
        font-size: .9rem;
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }

    @media (max-width: 840px) {
        .submeter-table-toolbar {
            align-items: stretch;
        }

        .submeter-search-input {
            width: 100%;
        }
    }

    body.dark-mode .submeters-page .submeter-list-card,
    body.dark-mode .submeters-page .submeter-table-wrap {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-list-title,
    body.dark-mode .submeters-page .submeter-table thead th,
    body.dark-mode .submeters-page .submeter-name-cell,
    body.dark-mode .submeters-page .submeter-baseline-cell {
        color: #e2e8f0 !important;
    }

    body.dark-mode .submeters-page .submeter-table-note,
    body.dark-mode .submeters-page .submeter-table tbody td {
        color: #cbd5e1 !important;
    }

    body.dark-mode .submeters-page .submeter-table thead th {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-table tbody td {
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-table tbody tr:hover {
        background: #111827 !important;
    }

    body.dark-mode .submeters-page .submeter-search-input {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .submeters-page .submeter-list-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .submeters-page .submeter-list-btn.primary {
        background: #1d4ed8 !important;
        color: #fff !important;
    }

    body.dark-mode .submeters-page .submeter-action-btn {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #93c5fd !important;
    }

    body.dark-mode .submeters-page .submeter-action-btn.danger {
        color: #fda4af !important;
    }

    body.dark-mode .submeters-page .submeter-detail-item {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .submeters-page .submeter-detail-item-label {
        color: #93c5fd !important;
    }

    body.dark-mode .submeters-page .submeter-detail-item-value {
        color: #e2e8f0 !important;
    }
</style>

@section('content')
<div class="submeters-page" style="width:100%;margin:0 auto;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 14px;border-radius:12px;font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
            Please check the sub-meter form fields and try again.
        </div>
    @endif

    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.08);padding:18px 20px;margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;color:#1d4ed8;font-size:1.55rem;font-weight:800;">
                    <i class="fa fa-network-wired" style="margin-right:8px;"></i>Linked Sub-meters
                </h2>
                <div style="margin-top:5px;color:#475569;font-weight:600;">
                    {{ $facility->name }} | Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Linked: {{ $linkedSubCount }}</span>
                    <span style="background:#ecfeff;color:#0f766e;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Active: {{ $activeLinkedSubCount }}</span>
                    <span style="background:#eef2ff;color:#4338ca;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Approved: {{ $approvedLinkedSubCount }}</span>
                </div>
            </div>
            <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}"
               style="text-decoration:none;background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;border-radius:10px;padding:9px 12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa fa-arrow-left"></i> Back to Energy Profile
            </a>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(15,23,42,.08);padding:16px 18px;">
        <div style="font-weight:800;color:#1e293b;margin-bottom:10px;">Main Meter Details</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Meter No.</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ $mainMeter->meter_number ?: 'N/A' }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Location</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ $mainMeter->location ?: 'N/A' }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Status</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">{{ strtoupper((string) ($mainMeter->status ?? 'N/A')) }}</div>
            </div>
            <div style="border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">
                <div style="font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;">Baseline</div>
                <div style="margin-top:3px;font-size:.95rem;color:#0f172a;font-weight:800;">
                    {{ is_numeric($mainMeter->baseline_kwh) ? number_format((float) $mainMeter->baseline_kwh, 2) . ' kWh' : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="submeter-list-card">
        <div class="submeter-list-head">
            <div class="submeter-list-title">Sub-meter List for This Main Meter</div>
            <div class="submeter-list-actions">
                <a href="{{ route('modules.facilities.meters.archive', ['facility' => $facility->id, 'meter_type' => 'sub', 'sub_only' => '1', 'main_meter_id' => (int) ($mainMeter->id ?? 0)]) }}"
                   title="View archived sub-meters"
                   class="submeter-list-btn">
                    <i class="fa fa-trash"></i> Trash
                    @if($archivedSubCount > 0)
                        <span style="background:#e11d48;color:#fff;border-radius:999px;padding:1px 7px;font-size:.72rem;">{{ $archivedSubCount }}</span>
                    @endif
                </a>
                @if($canManageMeters)
                    <button type="button"
                            onclick="openAddLinkedSubmeterModal()"
                            class="submeter-list-btn primary">
                        <i class="fa fa-plus"></i> Add Sub-meter
                    </button>
                @endif
            </div>
        </div>
        @if($subMeters->isEmpty())
            <div style="border:1px dashed #cbd5e1;border-radius:12px;padding:14px;color:#64748b;font-weight:700;">
                No linked sub-meter found for this main meter.
            </div>
        @else
            <div class="submeter-table-toolbar">
                <input type="text"
                       class="submeter-search-input"
                       data-submeter-search-target="linkedSubmeterTableBody"
                       placeholder="Search sub-meters (name, no, location, status)">
                <span class="submeter-table-note">Type to filter sub-meter rows.</span>
            </div>
            <div class="submeter-table-wrap">
                <table class="submeter-table">
                    <thead>
                        <tr>
                            <th>Sub-meter</th>
                            <th>Meter No.</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th class="right">Baseline</th>
                            @if($canManageMeters || $canApproveMeters)
                                <th class="center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="linkedSubmeterTableBody">
                        @foreach($subMeters as $sub)
                            @php
                                $isActive = strtolower((string) ($sub->status ?? '')) === 'active';
                                $isApproved = !empty($sub->approved_at);
                                $subApprovalText = $isApproved ? 'approved' : 'not approved';
                                $subMeterSearchText = strtolower(trim(implode(' ', [
                                    (string) ($sub->meter_name ?? ''),
                                    (string) ($sub->meter_number ?? ''),
                                    (string) ($sub->location ?? ''),
                                    (string) ($sub->status ?? ''),
                                    $subApprovalText,
                                    is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2, '.', '') : '',
                                ])));
                            @endphp
                            <tr data-submeter-row
                                data-submeter-search="{{ $subMeterSearchText }}"
                                data-submeter-name="{{ (string) ($sub->meter_name ?? 'N/A') }}"
                                data-submeter-number="{{ (string) ($sub->meter_number ?? 'N/A') }}"
                                data-submeter-parent="{{ (string) ($mainMeter->meter_name ?? 'N/A') }}"
                                data-submeter-location="{{ (string) ($sub->location ?? 'N/A') }}"
                                data-submeter-status="{{ strtoupper((string) ($sub->status ?? 'N/A')) }}"
                                data-submeter-approval="{{ $isApproved ? 'APPROVED' : 'NOT APPROVED' }}"
                                data-submeter-approved-at="{{ $sub->approved_at ? $sub->approved_at->format('Y-m-d H:i') : 'N/A' }}"
                                data-submeter-baseline="{{ is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2) . ' kWh' : 'N/A' }}"
                                data-submeter-multiplier="{{ is_numeric($sub->multiplier) ? number_format((float) $sub->multiplier, 4) : 'N/A' }}"
                                data-submeter-notes="{{ (string) ($sub->notes ?? 'N/A') }}"
                                data-submeter-equipment-url="{{ route('modules.facilities.meters.submeter-equipment', [$facility->id, $sub->id]) }}"
                                class="submeter-row-clickable"
                                tabindex="0"
                                role="button"
                                aria-label="View details for {{ (string) ($sub->meter_name ?? 'sub-meter') }}">
                                <td class="submeter-name-cell">{{ $sub->meter_name }}</td>
                                <td>{{ $sub->meter_number ?: 'N/A' }}</td>
                                <td>{{ $sub->location ?: 'N/A' }}</td>
                                <td>
                                    <span class="submeter-status-pill" style="border-color:{{ $isActive ? '#86efac' : '#fecaca' }};background:{{ $isActive ? '#dcfce7' : '#fee2e2' }};color:{{ $isActive ? '#166534' : '#991b1b' }};">
                                        {{ strtoupper((string) ($sub->status ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="submeter-status-pill" style="border-color:{{ $isApproved ? '#93c5fd' : '#fdba74' }};background:{{ $isApproved ? '#dbeafe' : '#fff7ed' }};color:{{ $isApproved ? '#1d4ed8' : '#9a3412' }};">
                                        {{ $isApproved ? 'APPROVED' : 'NOT APPROVED' }}
                                    </span>
                                </td>
                                <td class="right submeter-baseline-cell">
                                    {{ is_numeric($sub->baseline_kwh) ? number_format((float) $sub->baseline_kwh, 2) . ' kWh' : 'N/A' }}
                                </td>
                                @if($canManageMeters || $canApproveMeters)
                                    <td class="center">
                                        <div class="submeter-action-wrap">
                                            <button type="button"
                                                    onclick="openSubmeterDetailModalFromButton(this)"
                                                    title="View sub-meter details"
                                                    aria-label="View sub-meter details"
                                                    class="submeter-action-btn">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            @if($canApproveMeters)
                                                <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $sub->id]) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="_redirect_to" value="main_submeters">
                                                    <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
                                                    <button type="submit"
                                                            title="{{ $isApproved ? 'Unapprove sub-meter' : 'Approve sub-meter' }}"
                                                            aria-label="{{ $isApproved ? 'Unapprove sub-meter' : 'Approve sub-meter' }}"
                                                            class="submeter-action-btn"
                                                            style="border-color:{{ $isApproved ? '#86efac' : '#fdba74' }};background:{{ $isApproved ? '#dcfce7' : '#fff7ed' }};color:{{ $isApproved ? '#166534' : '#9a3412' }};">
                                                        <i class="fa {{ $isApproved ? 'fa-ban' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($canManageMeters)
                                                <button type="button"
                                                        onclick="openEditLinkedSubmeterModalFromButton(this)"
                                                        data-sub-id="{{ (int) $sub->id }}"
                                                        data-sub-name="{{ (string) ($sub->meter_name ?? '') }}"
                                                        data-sub-number="{{ (string) ($sub->meter_number ?? '') }}"
                                                        data-sub-location="{{ (string) ($sub->location ?? '') }}"
                                                        data-sub-status="{{ (string) ($sub->status ?? 'active') }}"
                                                        data-sub-multiplier="{{ (string) ($sub->multiplier ?? '1') }}"
                                                        data-sub-baseline="{{ (string) ($sub->baseline_kwh ?? '') }}"
                                                        data-sub-notes="{{ (string) ($sub->notes ?? '') }}"
                                                        title="Edit sub-meter"
                                                        aria-label="Edit sub-meter"
                                                        class="submeter-action-btn">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                        onclick="openArchiveLinkedSubmeterModal({{ (int) $sub->id }}, @js($sub->meter_name))"
                                                        title="Delete sub-meter"
                                                        aria-label="Delete sub-meter"
                                                        class="submeter-action-btn danger">
                                                    <i class="fa fa-archive"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="submeter-filter-empty" id="linkedSubmeterTableBodyEmpty">No matching sub-meter found.</div>
        @endif
    </div>
</div>

<div id="submeterDetailModal" style="display:none;position:fixed;inset:0;z-index:10059;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeSubmeterDetailModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0;color:#2563eb;font-weight:800;">Sub-meter Details</h3>
        <p style="margin:4px 0 0;color:#64748b;font-size:.9rem;">View selected sub-meter information</p>
        <div class="submeter-detail-grid">
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Meter Name</div><div id="submeterDetailName" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Meter No.</div><div id="submeterDetailNo" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Parent Main Meter</div><div id="submeterDetailParent" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Location</div><div id="submeterDetailLocation" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Status</div><div id="submeterDetailStatus" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Approval</div><div id="submeterDetailApproval" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Approved At</div><div id="submeterDetailApprovedAt" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Baseline</div><div id="submeterDetailBaseline" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item"><div class="submeter-detail-item-label">Multiplier</div><div id="submeterDetailMultiplier" class="submeter-detail-item-value">-</div></div>
            <div class="submeter-detail-item" style="grid-column:1/-1;"><div class="submeter-detail-item-label">Notes</div><div id="submeterDetailNotes" class="submeter-detail-item-value">-</div></div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:14px;">
            <a id="submeterDetailEquipmentBtn"
               href="#"
               style="display:none;text-decoration:none;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:10px;padding:10px 14px;font-weight:700;">
                View Equipment
            </a>
            <button type="button" onclick="closeSubmeterDetailModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Close</button>
        </div>
    </div>
</div>

<script>
function shouldIgnoreSubmeterRowClick(target) {
    if (!target || !target.closest) return false;
    return !!target.closest('button, a, form, input, select, textarea');
}

function openSubmeterDetailModalFromRow(row) {
    if (!row) return;

    const modal = document.getElementById('submeterDetailModal');
    if (!modal) return;

    const detailMap = {
        submeterDetailName: row.getAttribute('data-submeter-name') || 'N/A',
        submeterDetailNo: row.getAttribute('data-submeter-number') || 'N/A',
        submeterDetailParent: row.getAttribute('data-submeter-parent') || 'N/A',
        submeterDetailLocation: row.getAttribute('data-submeter-location') || 'N/A',
        submeterDetailStatus: row.getAttribute('data-submeter-status') || 'N/A',
        submeterDetailApproval: row.getAttribute('data-submeter-approval') || 'N/A',
        submeterDetailApprovedAt: row.getAttribute('data-submeter-approved-at') || 'N/A',
        submeterDetailBaseline: row.getAttribute('data-submeter-baseline') || 'N/A',
        submeterDetailMultiplier: row.getAttribute('data-submeter-multiplier') || 'N/A',
        submeterDetailNotes: row.getAttribute('data-submeter-notes') || 'N/A',
    };

    Object.entries(detailMap).forEach(function(entry) {
        const el = document.getElementById(entry[0]);
        if (!el) return;
        el.textContent = entry[1] || 'N/A';
    });

    const equipmentBtn = document.getElementById('submeterDetailEquipmentBtn');
    if (equipmentBtn) {
        const equipmentUrl = String(row.getAttribute('data-submeter-equipment-url') || '').trim();
        if (equipmentUrl !== '') {
            equipmentBtn.href = equipmentUrl;
            equipmentBtn.style.display = 'inline-flex';
        } else {
            equipmentBtn.removeAttribute('href');
            equipmentBtn.style.display = 'none';
        }
    }

    modal.style.display = 'flex';
}

function openSubmeterDetailModalFromButton(button) {
    if (!button || !button.closest) return;
    const row = button.closest('[data-submeter-row]');
    if (!row) return;
    openSubmeterDetailModalFromRow(row);
}

function closeSubmeterDetailModal() {
    const modal = document.getElementById('submeterDetailModal');
    if (!modal) return;
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-submeter-search-target]').forEach(function(input) {
        input.addEventListener('input', function() {
            const listId = String(input.getAttribute('data-submeter-search-target') || '');
            const list = listId ? document.getElementById(listId) : null;
            if (!list) return;

            const query = String(input.value || '').trim().toLowerCase();
            const rows = Array.from(list.querySelectorAll('[data-submeter-row]'));
            let visible = 0;

            rows.forEach(function(row) {
                const haystack = String(row.getAttribute('data-submeter-search') || '').toLowerCase();
                const show = query === '' || haystack.includes(query);
                row.style.display = show ? '' : 'none';
                if (show) visible += 1;
            });

            const dynamicEmpty = document.getElementById(listId + 'Empty');
            if (dynamicEmpty) {
                dynamicEmpty.style.display = rows.length > 0 && visible === 0 ? 'block' : 'none';
            }
        });
    });

    document.querySelectorAll('[data-submeter-row]').forEach(function(row) {
        row.addEventListener('click', function(event) {
            if (shouldIgnoreSubmeterRowClick(event.target)) return;
            openSubmeterDetailModalFromRow(row);
        });

        row.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openSubmeterDetailModalFromRow(row);
            }
        });
    });

    const detailModal = document.getElementById('submeterDetailModal');
    if (detailModal) {
        detailModal.addEventListener('click', function(event) {
            if (event.target === detailModal) {
                closeSubmeterDetailModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSubmeterDetailModal();
        }
    });
});
</script>

@if($canManageMeters)
<div id="addLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10060;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeAddLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Add Sub-meter</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
        </div>

        <form method="POST"
              action="{{ route('modules.facilities.meters.store', $facility->id) }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="add">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="meter_type" value="sub">
            <input type="hidden" name="parent_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">

            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
                <input type="text" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="Utility / serial no.">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier</label>
                <input type="number" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="1.0000">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Baseline kWh</label>
                <input type="number" name="baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 1200.00">
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeAddLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Save Sub-meter</button>
            </div>
        </form>
    </div>
</div>

<div id="editLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10061;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeEditLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 6px;color:#2563eb;font-weight:800;">Edit Sub-meter</h3>
        <div style="margin-bottom:12px;color:#475569;font-weight:600;">
            Main Meter: <span style="color:#0f172a;font-weight:800;">{{ $mainMeter->meter_name ?? 'N/A' }}</span>
        </div>

        @php
            $oldEditSubmeterId = (int) old('_submeter_edit_id');
            $oldEditAction = $oldEditSubmeterId > 0
                ? route('modules.facilities.meters.update', [$facility->id, $oldEditSubmeterId])
                : '#';
        @endphp
        <form id="editLinkedSubmeterForm"
              method="POST"
              action="{{ $oldEditAction }}"
              style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="edit">
            <input type="hidden" name="_submeter_edit_id" id="edit_submeter_id" value="{{ $oldEditSubmeterId > 0 ? $oldEditSubmeterId : '' }}">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="meter_type" value="sub">
            <input type="hidden" name="parent_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">

            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Name <span style="color:#e11d48;">*</span></label>
                <input type="text" id="edit_meter_name" name="meter_name" required maxlength="255" value="{{ old('meter_name') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 2F Lighting">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Meter Number</label>
                <input type="text" id="edit_meter_number" name="meter_number" maxlength="255" value="{{ old('meter_number') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="Utility / serial no.">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Location</label>
                <input type="text" id="edit_location" name="location" maxlength="255" value="{{ old('location') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. Panel 3">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Status <span style="color:#e11d48;">*</span></label>
                <select id="edit_status" name="status" required style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;">
                    <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Multiplier</label>
                <input type="number" id="edit_multiplier" name="multiplier" min="0.0001" max="999999" step="0.0001" value="{{ old('multiplier', '1') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="1.0000">
            </div>
            <div>
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Baseline kWh</label>
                <input type="number" id="edit_baseline_kwh" name="baseline_kwh" min="0" step="0.01" value="{{ old('baseline_kwh') }}"
                       style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;"
                       placeholder="e.g. 1200.00">
            </div>
            <div style="grid-column:1/-1;">
                <label style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Notes</label>
                <textarea id="edit_notes" name="notes" rows="3" maxlength="2000"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeEditLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Update Sub-meter</button>
            </div>
        </form>
    </div>
</div>

<div id="archiveLinkedSubmeterModal"
     style="display:none;position:fixed;inset:0;z-index:10062;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(520px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeArchiveLinkedSubmeterModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0 0 10px;color:#e11d48;font-weight:800;">Delete Sub-meter</h3>
        <div id="archiveLinkedSubmeterLabel" style="color:#334155;margin-bottom:12px;"></div>

        <form id="archiveLinkedSubmeterForm"
              method="POST"
              action="#"
              style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_redirect_to" value="main_submeters">
            <input type="hidden" name="_submeter_modal" value="archive">
            <input type="hidden" name="main_meter_id" value="{{ (int) ($mainMeter->id ?? 0) }}">
            <input type="hidden" name="_submeter_archive_id" id="archive_submeter_id" value="">
            <div>
                <label for="archive_submeter_reason" style="display:block;font-weight:700;color:#334155;margin-bottom:6px;">Reason for Delete <span style="color:#e11d48;">*</span></label>
                <textarea id="archive_submeter_reason"
                          name="archive_reason"
                          required
                          maxlength="500"
                          rows="4"
                          style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;resize:vertical;"
                          placeholder="Example: removed panel, duplicate entry, no longer in use">{{ old('archive_reason') }}</textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" onclick="closeArchiveLinkedSubmeterModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Cancel</button>
                <button type="submit" style="background:#e11d48;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddLinkedSubmeterModal() {
    const modal = document.getElementById('addLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'flex';
}

function closeAddLinkedSubmeterModal() {
    const modal = document.getElementById('addLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function openEditLinkedSubmeterModal(submeter) {
    const modal = document.getElementById('editLinkedSubmeterModal');
    const form = document.getElementById('editLinkedSubmeterForm');
    if (!modal || !form || !submeter) return;

    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + Number(submeter.id || 0);
    document.getElementById('edit_submeter_id').value = String(submeter.id || '');
    document.getElementById('edit_meter_name').value = submeter.meter_name ?? '';
    document.getElementById('edit_meter_number').value = submeter.meter_number ?? '';
    document.getElementById('edit_location').value = submeter.location ?? '';
    document.getElementById('edit_status').value = submeter.status ?? 'active';
    document.getElementById('edit_multiplier').value = submeter.multiplier ?? '1';
    document.getElementById('edit_baseline_kwh').value = submeter.baseline_kwh ?? '';
    document.getElementById('edit_notes').value = submeter.notes ?? '';

    modal.style.display = 'flex';
}

function openEditLinkedSubmeterModalFromButton(button) {
    if (!button) return;
    openEditLinkedSubmeterModal({
        id: Number(button.getAttribute('data-sub-id') || 0),
        meter_name: String(button.getAttribute('data-sub-name') || ''),
        meter_number: String(button.getAttribute('data-sub-number') || ''),
        location: String(button.getAttribute('data-sub-location') || ''),
        status: String(button.getAttribute('data-sub-status') || 'active'),
        multiplier: String(button.getAttribute('data-sub-multiplier') || '1'),
        baseline_kwh: String(button.getAttribute('data-sub-baseline') || ''),
        notes: String(button.getAttribute('data-sub-notes') || ''),
    });
}

function closeEditLinkedSubmeterModal() {
    const modal = document.getElementById('editLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function openArchiveLinkedSubmeterModal(subMeterId, meterName) {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    const form = document.getElementById('archiveLinkedSubmeterForm');
    const label = document.getElementById('archiveLinkedSubmeterLabel');
    const idInput = document.getElementById('archive_submeter_id');
    const reason = document.getElementById('archive_submeter_reason');
    if (!modal || !form || !idInput) return;

    const id = Number(subMeterId || 0);
    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + id;
    idInput.value = String(id);
    if (label) {
        label.textContent = 'Sub-meter: ' + String(meterName || '');
    }
    if (reason) {
        reason.value = '';
    }
    modal.style.display = 'flex';
}

function closeArchiveLinkedSubmeterModal() {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    if (!modal) return;
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const addModal = document.getElementById('addLinkedSubmeterModal');
    const editModal = document.getElementById('editLinkedSubmeterModal');
    const archiveModal = document.getElementById('archiveLinkedSubmeterModal');

    if (addModal) {
        addModal.addEventListener('click', function(event) {
            if (event.target === addModal) {
                closeAddLinkedSubmeterModal();
            }
        });
    }

    if (editModal) {
        editModal.addEventListener('click', function(event) {
            if (event.target === editModal) {
                closeEditLinkedSubmeterModal();
            }
        });
    }

    if (archiveModal) {
        archiveModal.addEventListener('click', function(event) {
            if (event.target === archiveModal) {
                closeArchiveLinkedSubmeterModal();
            }
        });
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeSubmeterDetailModal();
            closeAddLinkedSubmeterModal();
            closeEditLinkedSubmeterModal();
            closeArchiveLinkedSubmeterModal();
        }
    });
});
</script>

@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'add')
<script>
document.addEventListener('DOMContentLoaded', function () {
    openAddLinkedSubmeterModal();
});
</script>
@endif
@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'edit')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editLinkedSubmeterModal');
    if (modal) modal.style.display = 'flex';
});
</script>
@endif
@if($errors->any() && old('_redirect_to') === 'main_submeters' && old('_submeter_modal') === 'archive')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('archiveLinkedSubmeterModal');
    const form = document.getElementById('archiveLinkedSubmeterForm');
    const idInput = document.getElementById('archive_submeter_id');
    const reason = document.getElementById('archive_submeter_reason');
    const archiveId = Number(@json((int) old('_submeter_archive_id')));
    if (modal) modal.style.display = 'flex';
    if (form && archiveId > 0) {
        form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + archiveId;
    }
    if (idInput && archiveId > 0) {
        idInput.value = String(archiveId);
    }
    if (reason) {
        reason.value = @json((string) old('archive_reason'));
    }
});
</script>
@endif
@endif
@endsection
