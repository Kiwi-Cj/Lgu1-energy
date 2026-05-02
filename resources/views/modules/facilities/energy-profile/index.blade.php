@extends('layouts.qc-admin')
@section('title', 'Energy Profile')

@php
    $avgKwh = isset($facilityModel) ? $facilityModel->baseline_kwh : null;
    $user = auth()->user();
    $userRole = strtolower($user->role ?? '');
    $mainMeterOptions = $mainMeterOptions ?? collect();
    $mainMeters = $mainMeters ?? collect();
    $subMeterOptions = $subMeterOptions ?? collect();
    $subMetersByParentMainId = $subMetersByParentMainId ?? collect();
    $subMeterEntityIdMap = $subMeterEntityIdMap ?? collect();
    $subMeterLoadTrackingMap = $subMeterLoadTrackingMap ?? collect();
    $mainMeterLoadTrackingMap = $mainMeterLoadTrackingMap ?? collect();
    $equipmentByMeterKey = $equipmentByMeterKey ?? collect();
    $parentMeterOptions = $parentMeterOptions ?? collect();
    $parentMeterMap = collect($parentMeterOptions)->keyBy('id');
    $activeMeterCount = $activeMeterCount ?? 0;
    $activeMainMeterCount = $activeMainMeterCount ?? 0;
    $subMeterCount = $subMeterCount ?? 0;
    $unapprovedMeterCount = $unapprovedMeterCount ?? 0;
    $archivedMeterCount = $archivedMeterCount ?? 0;
    $canManageMeters = $canManageMeters ?? false;
    $canApproveMeters = $canApproveMeters ?? false;
    $canManageLoadTracking = $canManageLoadTracking ?? false;
    $hasApprovedMainForSub = $mainMeterOptions->isNotEmpty();
@endphp

<style>
    /* --- Shared UI Variables (Same as Energy Report) --- */
    .energy-profile-page {
        --report-bg: #ffffff;
        --report-text: #333333;
        --report-subtext: #555555;
        --card-shadow: rgba(31, 38, 135, 0.08);
        --table-header-bg: #e9effc;
        --table-row-even: #f8fafc;
        --table-border: #e5e7eb;
    }

    body.dark-mode .energy-profile-page {
        --report-bg: #1e293b;
        --report-text: #f1f5f9;
        --report-subtext: #94a3b8;
        --card-shadow: rgba(0, 0, 0, 0.4);
        --table-header-bg: #334155;
        --table-row-even: #1e293b;
        --table-border: #475569;
    }

    .profile-card {
        background: var(--report-bg);
        border-radius: 18px;
        box-shadow: 0 4px 12px var(--card-shadow);
        margin-bottom: 1.2rem;
        padding: 24px;
        color: var(--report-text);
        transition: background 0.3s ease;
    }

    .profile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }

    .btn-action-main {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        color: #fff;
        background: linear-gradient(90deg,#2563eb,#6366f1);
        cursor: pointer;
        transition: 0.2s;
        text-wrap: nowrap;
    }

    .btn-action-main:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .meter-directory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .meter-directory-card {
        border: 1px solid var(--table-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--report-bg);
    }

    .meter-directory-head {
        padding: 10px 12px;
        background: var(--table-header-bg);
        color: #1e293b;
        font-weight: 800;
        font-size: .9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .meter-directory-toolbar {
        padding: 10px 12px;
        border-top: 1px solid var(--table-border);
        border-bottom: 1px solid var(--table-border);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .meter-toolbar-top {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
    }

    .meter-toolbar-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .meter-search-input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: .85rem;
        background: #ffffff;
        color: #1e293b;
    }

    .meter-toolbar-note {
        font-size: .76rem;
        color: var(--report-subtext);
    }

    .meter-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .meter-inline-btn {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: .75rem;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .meter-inline-btn.secondary {
        border-color: #d1d5db;
        background: #f8fafc;
        color: #334155;
    }

    .meter-toggle-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .74rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .meter-directory-list {
        max-height: 260px;
        overflow: auto;
    }

    .meter-directory-list.is-collapsed {
        display: none;
    }

    .meter-row {
        padding: 13px 14px;
        border-top: 1px solid var(--table-border);
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .meter-row-clickable {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .meter-row-clickable:hover {
        background: #f8fafc;
    }

    .meter-row-clickable:focus-visible {
        outline: 2px solid #3b82f6;
        outline-offset: -2px;
    }

    .meter-row-name {
        font-weight: 800;
        color: var(--report-text);
        font-size: 1.05rem;
        line-height: 1.2;
    }

    .meter-row-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .meter-row-badges {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .meter-row-meta {
        font-size: .83rem;
        color: var(--report-subtext);
        display: flex;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 14px;
    }

    .meter-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .meter-meta-item i {
        color: #64748b;
    }

    .meter-row-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .meter-row-link-count {
        font-size: .8rem;
        color: #334155;
        font-weight: 700;
    }

    .meter-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 6px;
    }

    .meter-row-action-btn {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: .75rem;
        font-weight: 700;
        cursor: pointer;
    }

    .meter-row-action-btn.icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
    }

    .meter-row-action-btn.danger {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .meter-status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: .72rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .meter-approval-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: .72rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .meter-empty-state {
        display: none;
        padding: 12px;
        color: var(--report-subtext);
        font-size: .85rem;
        border-top: 1px solid var(--table-border);
    }

    @media (max-width: 820px) {
        .meter-toolbar-top {
            grid-template-columns: 1fr;
        }

        .meter-row {
            padding: 12px;
        }

        .meter-row-meta {
            gap: 10px;
        }
    }

    .meter-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .meter-detail-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 9px 10px;
    }

    .meter-detail-item-label {
        font-size: .74rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .meter-detail-item-value {
        margin-top: 3px;
        font-size: .9rem;
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }

    .meter-equip-card {
        margin-top: 12px;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #f8fbff;
        padding: 12px;
    }

    .meter-equip-title {
        font-weight: 800;
        color: #1d4ed8;
        margin-bottom: 4px;
        font-size: 1.02rem;
    }

    .meter-equip-subtitle {
        margin-bottom: 8px;
        color: #64748b;
        font-size: .82rem;
        font-weight: 600;
    }

    .meter-equip-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .meter-equip-context {
        font-size: .82rem;
        color: #475569;
    }

    .meter-equip-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        align-items: end;
    }

    .meter-equip-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .meter-equip-field label {
        font-size: .8rem;
        color: #334155;
        font-weight: 700;
    }

    .meter-equip-input {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
    }

    .meter-equip-actions {
        display: flex;
        justify-content: flex-end;
    }

    .meter-equip-save-btn {
        min-width: 210px;
        max-width: 100%;
        background: #1d4ed8;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 800;
        cursor: pointer;
    }

    .meter-equip-warning {
        display: none;
        margin-top: 8px;
        color: #9a3412;
        background: #fff7ed;
        border: 1px solid #fdba74;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: .78rem;
        font-weight: 700;
    }

    .meter-equip-list-card {
        margin-top: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .meter-equip-list-head {
        padding: 10px 12px;
        background: #f8fafc;
        color: #1e293b;
        font-weight: 800;
    }

    .meter-equip-list-wrap {
        max-height: 220px;
        overflow: auto;
    }

    .meter-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10040;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .meter-modal-card {
        width: min(520px, 95vw);
        max-height: calc(100vh - 32px);
        overflow: auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 24px 50px rgba(15, 23, 42, .28);
        padding: 22px 22px 16px;
        position: relative;
    }

    .meter-modal-card.compact {
        width: min(520px, 95vw);
    }

    .meter-modal-close {
        position: absolute;
        top: 10px;
        right: 12px;
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        font-size: 1.35rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .meter-modal-close:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .meter-modal-title {
        margin: 0;
        color: #2563eb;
        font-weight: 900;
        font-size: 1.75rem;
        line-height: 1.1;
    }

    .meter-modal-title.danger {
        color: #e11d48;
        font-size: 1.45rem;
    }

    .meter-modal-subtitle {
        margin: 6px 0 14px;
        color: #64748b;
        font-size: 1rem;
        font-weight: 600;
    }

    .meter-manage-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 16px;
    }

    .meter-form-field {
        min-width: 0;
    }

    .meter-form-field.full {
        grid-column: 1 / -1;
    }

    .meter-form-label {
        display: block;
        font-weight: 800;
        font-size: 0.96rem;
        color: #334155;
        margin-bottom: 6px;
    }

    .meter-required {
        color: #e11d48;
    }

    .meter-form-control {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 14px;
        color: #1e293b;
        background: #fff;
        font-size: 1rem;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .meter-form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.16);
    }

    .meter-form-textarea {
        min-height: 94px;
        resize: vertical;
    }

    .meter-form-hint {
        margin-top: 6px;
        font-size: .82rem;
        color: #64748b;
        font-weight: 600;
        line-height: 1.35;
    }

    .meter-form-hint-warning {
        color: #9a3412;
    }

    .meter-form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 6px;
    }

    .meter-form-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 18px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
    }

    .meter-form-btn.cancel {
        background: #e2e8f0;
        color: #334155;
    }

    .meter-form-btn.cancel:hover {
        background: #cbd5e1;
    }

    .meter-form-btn.save {
        background: #2563eb;
        color: #fff;
        min-width: 148px;
    }

    .meter-form-btn.save:hover {
        background: #1d4ed8;
    }

    .meter-form-btn.danger {
        background: #e11d48;
        color: #fff;
        min-width: 168px;
    }

    .meter-form-btn.danger:hover {
        background: #be123c;
    }

    .meter-archive-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .meter-archive-label {
        color: #334155;
        font-weight: 700;
        line-height: 1.4;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    @media (max-width: 900px) {
        .meter-manage-form {
            grid-template-columns: 1fr;
            gap: 11px;
        }

        .meter-form-field.full {
            grid-column: auto;
        }

        .meter-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .meter-form-btn {
            width: 100%;
        }

        .meter-equip-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .meter-equip-actions {
            justify-content: stretch;
        }

        .meter-equip-save-btn {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 520px) {
        .meter-equip-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        .profile-card { padding: 15px; }
        .profile-header { flex-direction: column; text-align: center; }
        .btn-action-main { width: 100%; }
        .profile-header h2 { font-size: 1.5rem !important; }
    }

    /* Alerts */
    .alert-box {
        position: fixed; top: 32px; right: 32px; z-index: 99999; 
        min-width: 280px; max-width: 420px;
        padding: 16px 24px; border-radius: 12px; font-weight: 700;
        display: flex; align-items: center; gap: 10px;
    }

    body.dark-mode .energy-profile-page .profile-card {
        background: #0f172a !important;
        border: 1px solid #334155;
        box-shadow: 0 12px 28px rgba(2, 6, 23, 0.55);
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-directory-card {
        border-color: #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-directory-head {
        background: #111827 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-row {
        border-color: #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-row-clickable:hover {
        background: #111827 !important;
    }

    body.dark-mode .energy-profile-page .meter-directory-toolbar {
        border-color: #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-search-input,
    body.dark-mode .energy-profile-page .meter-toggle-btn {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-inline-btn {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #93c5fd !important;
    }

    body.dark-mode .energy-profile-page .meter-inline-btn.secondary {
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .meter-toolbar-note,
    body.dark-mode .energy-profile-page .meter-row-link-count {
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .meter-meta-item i {
        color: #94a3b8 !important;
    }

    body.dark-mode .energy-profile-page .meter-row-action-btn {
        background: #0b1220 !important;
        border-color: #334155 !important;
        color: #93c5fd !important;
    }

    body.dark-mode .energy-profile-page .meter-row-action-btn.danger {
        color: #fda4af !important;
    }

    body.dark-mode .energy-profile-page .meter-detail-item {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-detail-item-label {
        color: #93c5fd !important;
    }

    body.dark-mode .energy-profile-page .meter-detail-item-value {
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-equip-card,
    body.dark-mode .energy-profile-page .meter-equip-list-card {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-equip-list-head {
        background: #111827 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-equip-subtitle,
    body.dark-mode .energy-profile-page .meter-equip-context {
        color: #93c5fd !important;
    }

    body.dark-mode .energy-profile-page .meter-equip-field label {
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .meter-equip-input {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-modal-card {
        background: #0f172a !important;
        border: 1px solid #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-modal-close {
        background: #111827 !important;
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .meter-form-label {
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .meter-form-control {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page .meter-form-hint {
        color: #93c5fd !important;
    }

    body.dark-mode .energy-profile-page .meter-form-btn.cancel {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border: 1px solid #334155 !important;
    }

    body.dark-mode .energy-profile-page .meter-archive-label {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .energy-profile-page [style*="color:#3762c8"],
    body.dark-mode .energy-profile-page [style*="color: #3762c8"],
    body.dark-mode .energy-profile-page [style*="color:#64748b"],
    body.dark-mode .energy-profile-page [style*="color: #64748b"] {
        color: #93c5fd !important;
    }
</style>

@section('content')
<div class="energy-profile-page" style="width:100%; margin:0 auto;">

    @if(session('success'))
    <div id="successAlert" class="alert-box" style="background:#dcfce7; color:#166534; box-shadow:0 2px 8px #16a34a22;">
        <i class="fa fa-check-circle" style="font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div id="errorAlert" class="alert-box" style="background:#fee2e2; color:#b91c1c; box-shadow:0 2px 8px #e11d4822;">
        <i class="fa fa-times-circle" style="font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 14px;border-radius:12px;font-weight:700;">
        Please check the submitted form fields and try again.
    </div>
    @endif

    <div class="profile-card">
        <div class="profile-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:700; color:#3762c8; margin:0;">
                    <i class="fa fa-clipboard-list" style="margin-right:8px;"></i>Energy Profile
                </h2>
                <p style="color:var(--report-subtext); margin-top:4px;">{{ $facilityModel->name ?? 'Facility Details' }}</p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Main Meters: {{ $mainMeterOptions->count() }}</span>
                    <span style="background:#ecfeff;color:#0f766e;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Active Main Meters: {{ $activeMainMeterCount }}</span>
                    <span style="background:#fff7ed;color:#9a3412;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:800;">Unapproved: {{ $unapprovedMeterCount }}</span>
                </div>
                @if($mainMeterOptions->isEmpty())
                    <div style="margin-top:8px;color:#9a3412;font-weight:700;font-size:.88rem;">No Main Meter found yet. Add one below, then link it to the profile.</div>
                @endif
            </div>
        </div>

        <div class="meter-directory-grid">
            <div class="meter-directory-card">
                <div class="meter-directory-head">
                    <span><i class="fa fa-bolt" style="margin-right:6px;"></i>Main Meter List</span>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:.78rem;">{{ $mainMeters->count() }} total</span>
                        <button type="button" class="meter-toggle-btn" data-meter-toggle-target="mainMeterDirectoryList" aria-expanded="true">
                            <span class="meter-toggle-label">Collapse</span>
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="meter-directory-toolbar">
                    <div class="meter-toolbar-top">
                        <input type="text"
                               class="meter-search-input"
                               data-meter-search-target="mainMeterDirectoryList"
                               placeholder="Search main meters (name, no, location, status)">
                        <div class="meter-toolbar-actions">
                            @if($canManageMeters)
                                <button type="button" class="meter-inline-btn" onclick="openAddMeterProfileModal('main')">
                                    <i class="fa fa-plus"></i> Add Main Meter
                                </button>
                            @endif
                            <a href="{{ route('modules.facilities.meters.archive', $facilityModel->id) }}" class="meter-inline-btn secondary">
                                <i class="fa fa-box-archive"></i> Archive
                                @if($archivedMeterCount > 0)
                                    <span style="background:#e11d48;color:#fff;border-radius:999px;padding:1px 7px;font-size:.72rem;">{{ $archivedMeterCount }}</span>
                                @endif
                            </a>
                            @if($canApproveMeters || $canManageMeters)
                                <a href="{{ route('modules.facilities.meters.unapproved', $facilityModel->id) }}" class="meter-inline-btn secondary">
                                    <i class="fa fa-circle-exclamation"></i> Unapproved
                                    @if($unapprovedMeterCount > 0)
                                        <span style="background:#f97316;color:#fff;border-radius:999px;padding:1px 7px;font-size:.72rem;">{{ $unapprovedMeterCount }}</span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="meter-toolbar-bottom">
                        <span class="meter-toolbar-note">Type to filter list items.</span>
                        <span style="font-size:.78rem;color:var(--report-subtext);font-weight:700;">
                            Click a row to view main meter details
                        </span>
                    </div>
                </div>
                <div class="meter-directory-list" id="mainMeterDirectoryList">
                    @forelse($mainMeters as $meter)
                        @php
                            $isActiveMeter = strtolower((string) ($meter->status ?? 'active')) === 'active';
                            $approvalState = $meter->approved_at ? 'approved' : 'not_approved';
                            $approvalText = $approvalState === 'approved' ? 'APPROVED' : 'NOT APPROVED';
                            $mainEquipmentUrl = (string) ($mainMeterLoadTrackingMap[$meter->id] ?? '');
                            $linkedSubMeters = collect($subMetersByParentMainId->get((int) $meter->id, collect()));
                            $linkedSubNames = $linkedSubMeters->pluck('meter_name')
                                ->filter(fn ($name) => trim((string) $name) !== '')
                                ->values();
                            $linkedSubCount = $linkedSubNames->count();
                            $linkedSubSummary = $linkedSubCount > 0 ? $linkedSubNames->implode(', ') : 'None';
                            $mainMeterSearchText = strtolower(trim(implode(' ', [
                                (string) ($meter->meter_name ?? ''),
                                (string) ($meter->meter_number ?? ''),
                                (string) ($meter->location ?? ''),
                                (string) ($meter->status ?? ''),
                                $approvalText,
                                (string) $linkedSubSummary,
                                is_numeric($meter->baseline_kwh) ? number_format((float) $meter->baseline_kwh, 2, '.', '') : '',
                            ])));
                        @endphp
                        <div class="meter-row meter-row-clickable"
                             data-meter-row
                             data-meter-search="{{ $mainMeterSearchText }}"
                             data-meter-detail="1"
                             data-meter-kind="Main Meter"
                             data-meter-name="{{ $meter->meter_name ?? 'N/A' }}"
                             data-meter-number="{{ $meter->meter_number ?? 'N/A' }}"
                             data-meter-type="{{ strtoupper((string) ($meter->meter_type ?? 'main')) }}"
                             data-meter-parent="None"
                             data-meter-location="{{ $meter->location ?? 'N/A' }}"
                             data-meter-status="{{ strtoupper((string) ($meter->status ?? 'active')) }}"
                             data-meter-approval="{{ $approvalText }}"
                             data-meter-baseline="{{ is_numeric($meter->baseline_kwh) ? number_format((float) $meter->baseline_kwh, 2) . ' kWh' : 'N/A' }}"
                             data-meter-multiplier="{{ is_numeric($meter->multiplier) ? number_format((float) $meter->multiplier, 4) : 'N/A' }}"
                             data-meter-notes="{{ $meter->notes ?? 'N/A' }}"
                             data-meter-linked-submeters="{{ $linkedSubSummary }}"
                             data-meter-linked-submeter-count="{{ $linkedSubCount }}"
                             data-meter-approved-at="{{ $meter->approved_at ? $meter->approved_at->format('Y-m-d H:i') : 'N/A' }}"
                             data-meter-equipment-url="{{ $mainEquipmentUrl }}"
                             data-meter-submeters-page-url="{{ route('modules.facilities.meters.main-submeters', [$facilityModel->id, $meter->id]) }}"
                             data-meter-scope="main"
                             data-meter-main-id="{{ (int) $meter->id }}"
                             data-meter-submeter-id=""
                             data-meter-equipment-key="main:{{ (int) $meter->id }}"
                             tabindex="0"
                             role="button"
                             aria-label="View details for {{ $meter->meter_name ?? 'meter' }}">
                            <div class="meter-row-top">
                                <div class="meter-row-name">{{ $meter->meter_name }}</div>
                                <div class="meter-row-badges">
                                    <span class="meter-status-pill" style="background:{{ $isActiveMeter ? '#dcfce7' : '#fee2e2' }};color:{{ $isActiveMeter ? '#166534' : '#991b1b' }};border-color:{{ $isActiveMeter ? '#86efac' : '#fecaca' }};">
                                        {{ strtoupper((string) ($meter->status ?? 'active')) }}
                                    </span>
                                    <span class="meter-approval-pill" style="background:{{ $approvalState === 'approved' ? '#dbeafe' : '#fff7ed' }};color:{{ $approvalState === 'approved' ? '#1d4ed8' : '#9a3412' }};border-color:{{ $approvalState === 'approved' ? '#93c5fd' : '#fdba74' }};">
                                        {{ $approvalText }}
                                    </span>
                                </div>
                            </div>
                            <div class="meter-row-meta">
                                <span class="meter-meta-item"><i class="fa fa-hashtag"></i> {{ $meter->meter_number ?: 'N/A' }}</span>
                                <span class="meter-meta-item"><i class="fa fa-map-marker-alt"></i> {{ $meter->location ?: 'N/A' }}</span>
                                <span class="meter-meta-item"><i class="fa fa-chart-line"></i> {{ is_numeric($meter->baseline_kwh) ? number_format((float) $meter->baseline_kwh, 2) . ' kWh' : 'N/A' }}</span>
                            </div>
                            <div class="meter-row-footer">
                                <span class="meter-row-link-count">
                                    Linked Sub-meters: {{ $linkedSubCount > 0 ? $linkedSubCount : 'None' }}
                                </span>
                                @if($canApproveMeters || $canManageMeters)
                                    @php
                                        $editMeterPayload = [
                                            'id' => $meter->id,
                                            'meter_name' => $meter->meter_name,
                                            'meter_number' => $meter->meter_number,
                                            'meter_type' => $meter->meter_type,
                                            'parent_meter_id' => $meter->parent_meter_id,
                                            'location' => $meter->location,
                                            'status' => $meter->status,
                                            'multiplier' => $meter->multiplier,
                                            'baseline_kwh' => $meter->baseline_kwh,
                                            'notes' => $meter->notes,
                                        ];
                                    @endphp
                                    <div class="meter-row-actions">
                                        <button type="button"
                                                class="meter-row-action-btn icon"
                                                onclick="openMeterDetailModalFromButton(this)"
                                                title="View main meter details"
                                                aria-label="View main meter details">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        @if($canApproveMeters)
                                            <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facilityModel->id, $meter->id]) }}" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="_redirect_to" value="energy_profile">
                                                <button type="submit"
                                                        class="meter-row-action-btn icon"
                                                        title="{{ $approvalState === 'approved' ? 'Unapprove main meter' : 'Approve main meter' }}"
                                                        aria-label="{{ $approvalState === 'approved' ? 'Unapprove main meter' : 'Approve main meter' }}"
                                                        style="border-color:{{ $approvalState === 'approved' ? '#86efac' : '#fdba74' }};background:{{ $approvalState === 'approved' ? '#dcfce7' : '#fff7ed' }};color:{{ $approvalState === 'approved' ? '#166534' : '#9a3412' }};">
                                                    <i class="fa {{ $approvalState === 'approved' ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canManageMeters)
                                            <button type="button"
                                                    class="meter-row-action-btn icon"
                                                    onclick='openEditMeterProfileModal(@js($editMeterPayload))'
                                                    title="Edit main meter"
                                                    aria-label="Edit main meter">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                    class="meter-row-action-btn danger icon"
                                                    onclick="openArchiveMeterProfileModal({{ $meter->id }}, @js($meter->meter_name))"
                                                    title="Delete main meter"
                                                    aria-label="Delete main meter">
                                                <i class="fa fa-archive"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="meter-row" data-meter-empty-static>
                            <div class="meter-row-meta">No main meter found for this facility.</div>
                        </div>
                    @endforelse
                </div>
                <div class="meter-empty-state" id="mainMeterDirectoryListEmpty">No matching main meter found.</div>
            </div>

            
        </div>

    </div>
</div>

@include('modules.facilities.energy-profile.partials.modals')
@include('modules.facilities.energy-profile.partials.delete-modal')

<div id="meterDetailModal" style="display:none;position:fixed;inset:0;z-index:10039;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(760px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(0,0,0,.2);padding:20px;position:relative;">
        <button type="button" onclick="closeMeterDetailModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.35rem;color:#64748b;cursor:pointer;">&times;</button>
        <h3 style="margin:0;color:#2563eb;font-weight:800;">Meter Details</h3>
        <p id="meterDetailSubtitle" style="margin:4px 0 0;color:#64748b;font-size:.9rem;">View selected meter information</p>
        <div class="meter-detail-grid">
            <div class="meter-detail-item"><div class="meter-detail-item-label">Type</div><div id="meterDetailType" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Meter Name</div><div id="meterDetailName" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Meter No.</div><div id="meterDetailNo" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Parent</div><div id="meterDetailParent" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Sub-meters</div><div id="meterDetailSubmeterCount" class="meter-detail-item-value">0</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Location</div><div id="meterDetailLocation" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Status</div><div id="meterDetailStatus" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Approval</div><div id="meterDetailApproval" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Approved At</div><div id="meterDetailApprovedAt" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Baseline</div><div id="meterDetailBaseline" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item"><div class="meter-detail-item-label">Multiplier</div><div id="meterDetailMultiplier" class="meter-detail-item-value">-</div></div>
            <div class="meter-detail-item" style="grid-column:1/-1;"><div class="meter-detail-item-label">Notes</div><div id="meterDetailNotes" class="meter-detail-item-value">-</div></div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:14px;flex-wrap:wrap;">
            <a id="meterDetailSubmetersBtn"
               href="#"
               style="display:none;text-decoration:none;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:10px;padding:10px 14px;font-weight:700;">
                View Linked Sub-meters
            </a>
            <button type="button" onclick="closeMeterDetailModal()" style="background:#f1f5f9;color:#334155;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Close</button>
        </div>
    </div>
</div>

@if($canManageMeters)
<div id="addMeterProfileModal" class="meter-modal-overlay">
    <div class="meter-modal-card">
        <button type="button" onclick="closeAddMeterProfileModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title">Add Meter</h3>
        <p class="meter-modal-subtitle">Create a main meter or link a sub-meter to an approved main meter.</p>
        <form method="POST" action="{{ route('modules.facilities.meters.store', $facilityModel->id) }}" class="meter-manage-form">
            @csrf
            <input type="hidden" name="_redirect_to" value="energy_profile">
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'add', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null, 'hasApprovedMainForSub' => $hasApprovedMainForSub])
            <div class="meter-form-actions">
                <button type="button" onclick="closeAddMeterProfileModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save">Save Meter</button>
            </div>
        </form>
    </div>
</div>

<div id="editMeterProfileModal" class="meter-modal-overlay" style="z-index:10041;">
    <div class="meter-modal-card">
        <button type="button" onclick="closeEditMeterProfileModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title">Edit Meter</h3>
        <p class="meter-modal-subtitle">Update meter information and linkage details.</p>
        <form id="editMeterProfileForm" method="POST" action="#" class="meter-manage-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="_redirect_to" value="energy_profile">
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'edit', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null, 'hasApprovedMainForSub' => $hasApprovedMainForSub])
            <div class="meter-form-actions">
                <button type="button" onclick="closeEditMeterProfileModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save">Update Meter</button>
            </div>
        </form>
    </div>
</div>

<div id="archiveMeterProfileModal" class="meter-modal-overlay" style="z-index:10042;">
    <div class="meter-modal-card compact">
        <button type="button" onclick="closeArchiveMeterProfileModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title danger">Delete Meter</h3>
        <p class="meter-modal-subtitle">This meter will be moved to archive and can be restored later.</p>
        <form id="archiveMeterProfileForm" method="POST" action="#" class="meter-archive-body">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_redirect_to" value="energy_profile">
            <div id="archiveMeterProfileLabel" class="meter-archive-label"></div>
            <div>
                <label class="meter-form-label" for="archive_meter_profile_reason">Reason for Delete <span class="meter-required">*</span></label>
                <textarea class="meter-form-control meter-form-textarea" id="archive_meter_profile_reason" name="archive_reason" required maxlength="500" rows="4" placeholder="Example: duplicate meter entry, removed panel, decommissioned"></textarea>
            </div>
            <div class="meter-form-actions">
                <button type="button" onclick="closeArchiveMeterProfileModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    const meterProfileConfig = {
        hasApprovedMainForSub: @json($hasApprovedMainForSub),
        noApprovedMainMessage: 'Add and approve at least one Main Meter first before creating Sub-meter.',
    };

    // Auto-hide alerts
    window.addEventListener('DOMContentLoaded', function() {
        const s = document.getElementById('successAlert');
        const e = document.getElementById('errorAlert');
        if (s) setTimeout(() => s.style.opacity = '0', 3000);
        if (e) setTimeout(() => e.style.opacity = '0', 3000);

        document.querySelectorAll('[data-meter-toggle-target]').forEach(function(button) {
            button.addEventListener('click', function() {
                const listId = String(button.getAttribute('data-meter-toggle-target') || '');
                const list = listId ? document.getElementById(listId) : null;
                if (!list) return;

                const collapsed = list.classList.toggle('is-collapsed');
                button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

                const label = button.querySelector('.meter-toggle-label');
                const icon = button.querySelector('i');
                if (label) label.textContent = collapsed ? 'Expand' : 'Collapse';
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'fa-chevron-down');
                    icon.classList.add(collapsed ? 'fa-chevron-down' : 'fa-chevron-up');
                }
            });
        });

        document.querySelectorAll('[data-meter-search-target]').forEach(function(input) {
            input.addEventListener('input', function() {
                const listId = String(input.getAttribute('data-meter-search-target') || '');
                const list = listId ? document.getElementById(listId) : null;
                if (!list) return;

                const query = String(input.value || '').trim().toLowerCase();
                const rows = Array.from(list.querySelectorAll('[data-meter-row]'));
                let visible = 0;

                rows.forEach(function(row) {
                    const haystack = String(row.getAttribute('data-meter-search') || '').toLowerCase();
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

        document.querySelectorAll('[data-meter-detail="1"]').forEach(function(row) {
            row.addEventListener('click', function(event) {
                if (shouldIgnoreMeterRowClick(event.target)) return;
                openMeterDetailModalFromRow(row);
            });

            row.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openMeterDetailModalFromRow(row);
                }
            });
        });

        const detailModal = document.getElementById('meterDetailModal');
        if (detailModal) {
            detailModal.addEventListener('click', function(event) {
                if (event.target === detailModal) {
                    closeMeterDetailModal();
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMeterDetailModal();
            }
        });
    });

    function shouldIgnoreMeterRowClick(target) {
        if (!target || !target.closest) return false;
        return !!target.closest('button, a, form, input, select, textarea');
    }

    function openMeterDetailModalFromRow(row) {
        if (!row) return;

        const modal = document.getElementById('meterDetailModal');
        if (!modal) return;

        const detailMap = {
            meterDetailSubtitle: row.getAttribute('data-meter-kind') || 'Meter',
            meterDetailType: row.getAttribute('data-meter-type') || 'N/A',
            meterDetailName: row.getAttribute('data-meter-name') || 'N/A',
            meterDetailNo: row.getAttribute('data-meter-number') || 'N/A',
            meterDetailParent: row.getAttribute('data-meter-parent') || 'N/A',
            meterDetailSubmeterCount: row.getAttribute('data-meter-linked-submeter-count') || '0',
            meterDetailLocation: row.getAttribute('data-meter-location') || 'N/A',
            meterDetailStatus: row.getAttribute('data-meter-status') || 'N/A',
            meterDetailApproval: row.getAttribute('data-meter-approval') || 'N/A',
            meterDetailApprovedAt: row.getAttribute('data-meter-approved-at') || 'N/A',
            meterDetailBaseline: row.getAttribute('data-meter-baseline') || 'N/A',
            meterDetailMultiplier: row.getAttribute('data-meter-multiplier') || 'N/A',
            meterDetailNotes: row.getAttribute('data-meter-notes') || 'N/A',
        };

        Object.entries(detailMap).forEach(function(entry) {
            const el = document.getElementById(entry[0]);
            if (!el) return;
            if (entry[0] === 'meterDetailSubtitle') {
                el.textContent = (entry[1] || 'Meter') + ' details';
            } else {
                el.textContent = entry[1] || 'N/A';
            }
        });

        const submeterPageBtn = document.getElementById('meterDetailSubmetersBtn');
        if (submeterPageBtn) {
            const url = String(row.getAttribute('data-meter-submeters-page-url') || '').trim();
            submeterPageBtn.textContent = 'View Linked Sub-meters';
            if (url !== '') {
                submeterPageBtn.href = url;
                submeterPageBtn.style.display = 'inline-flex';
            } else {
                submeterPageBtn.removeAttribute('href');
                submeterPageBtn.style.display = 'none';
            }
        }

        modal.style.display = 'flex';
    }

    function openMeterDetailModalFromButton(button) {
        if (!button || !button.closest) return;
        const row = button.closest('[data-meter-row]');
        if (!row) return;
        openMeterDetailModalFromRow(row);
    }

    function closeMeterDetailModal() {
        const modal = document.getElementById('meterDetailModal');
        if (modal) modal.style.display = 'none';
    }

    function openAddMeterProfileModal(defaultType) {
        const modal = document.getElementById('addMeterProfileModal');
        if (!modal) return;

        if (defaultType === 'sub' && !meterProfileConfig.hasApprovedMainForSub) {
            defaultType = 'main';
            alert(meterProfileConfig.noApprovedMainMessage);
        }

        const meterTypeInput = document.getElementById('add_meter_type');
        if (meterTypeInput && (defaultType === 'main' || defaultType === 'sub')) {
            meterTypeInput.value = defaultType;
        }
        toggleMeterProfileParentSelect('add');
        modal.style.display = 'flex';
    }

    function closeAddMeterProfileModal() {
        const modal = document.getElementById('addMeterProfileModal');
        if (modal) modal.style.display = 'none';
    }

    function closeEditMeterProfileModal() {
        const modal = document.getElementById('editMeterProfileModal');
        if (modal) modal.style.display = 'none';
    }

    function closeArchiveMeterProfileModal() {
        const modal = document.getElementById('archiveMeterProfileModal');
        if (modal) modal.style.display = 'none';
    }

    function toggleMeterProfileParentSelect(prefix) {
        const typeEl = document.getElementById(prefix + '_meter_type');
        const parentEl = document.getElementById(prefix + '_parent_meter_id');
        if (!typeEl || !parentEl) return;

        if (prefix === 'add' && typeEl.value === 'sub' && !meterProfileConfig.hasApprovedMainForSub) {
            typeEl.value = 'main';
            alert(meterProfileConfig.noApprovedMainMessage);
        }

        if (typeEl.value === 'main') {
            parentEl.value = '';
            parentEl.disabled = true;
            parentEl.required = false;
        } else {
            parentEl.disabled = false;
            parentEl.required = true;
        }
    }

    function openEditMeterProfileModal(meter) {
        const modal = document.getElementById('editMeterProfileModal');
        const form = document.getElementById('editMeterProfileForm');
        if (!modal || !form || !meter) return;

        form.action = "{{ url('/modules/facilities/' . $facilityModel->id . '/meters') }}/" + meter.id;
        document.getElementById('edit_meter_name').value = meter.meter_name ?? '';
        document.getElementById('edit_meter_number').value = meter.meter_number ?? '';
        document.getElementById('edit_meter_type').value = meter.meter_type ?? 'sub';
        document.getElementById('edit_parent_meter_id').value = meter.parent_meter_id ?? '';
        document.getElementById('edit_location').value = meter.location ?? '';
        document.getElementById('edit_status').value = meter.status ?? 'active';
        document.getElementById('edit_multiplier').value = meter.multiplier ?? '1';
        document.getElementById('edit_baseline_kwh').value = meter.baseline_kwh ?? '';
        document.getElementById('edit_notes').value = meter.notes ?? '';

        const parentSelect = document.getElementById('edit_parent_meter_id');
        if (parentSelect) {
            Array.from(parentSelect.options).forEach(function(opt) {
                opt.disabled = (opt.value !== '' && String(opt.value) === String(meter.id));
            });
        }

        toggleMeterProfileParentSelect('edit');
        modal.style.display = 'flex';
    }

    function openArchiveMeterProfileModal(meterId, meterName) {
        const modal = document.getElementById('archiveMeterProfileModal');
        const form = document.getElementById('archiveMeterProfileForm');
        const label = document.getElementById('archiveMeterProfileLabel');
        const reason = document.getElementById('archive_meter_profile_reason');
        if (!modal || !form) return;

        form.action = "{{ url('/modules/facilities/' . $facilityModel->id . '/meters') }}/" + meterId;
        if (label) label.textContent = "Meter: " + (meterName || '');
        if (reason) reason.value = '';
        modal.style.display = 'flex';
    }

    document.getElementById('add_meter_type')?.addEventListener('change', function() { toggleMeterProfileParentSelect('add'); });
    document.getElementById('edit_meter_type')?.addEventListener('change', function() { toggleMeterProfileParentSelect('edit'); });
    toggleMeterProfileParentSelect('add');
    toggleMeterProfileParentSelect('edit');

    @if($errors->any() && old('_redirect_to') === 'energy_profile' && old('meter_name'))
        openAddMeterProfileModal("{{ old('meter_type', $hasApprovedMainForSub ? 'sub' : 'main') }}");
    @endif

    function closeModal(modalId){ document.getElementById(modalId).classList.remove('show-modal'); }
</script>
@endsection


