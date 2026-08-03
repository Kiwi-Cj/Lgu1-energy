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
    $canEncodeMainReadings = $canEncodeMainReadings ?? false;
    $latestEnergyRecord = $latestEnergyRecord ?? null;
    $hasApprovedMainForSub = $mainMeterOptions->isNotEmpty();
    $isCprfManaged = isset($facilityModel) && method_exists($facilityModel, 'isCprfManaged') && $facilityModel->isCprfManaged();
    $resolvedBaseline = isset($facilityModel) ? $facilityModel->resolveBaselineKwh() : null;
    $latestActualKwh = is_numeric($latestEnergyRecord?->actual_kwh) ? (float) $latestEnergyRecord->actual_kwh : null;
    $latestPeriod = $latestEnergyRecord
        ? \Carbon\Carbon::create((int) $latestEnergyRecord->year, (int) $latestEnergyRecord->month, 1)->format('M Y')
        : 'No reading yet';
    $readinessChecks = [
        'Approved main meter' => $mainMeterOptions->isNotEmpty(),
        'Active main meter' => $activeMainMeterCount > 0,
        'Baseline configured' => is_numeric($resolvedBaseline) && (float) $resolvedBaseline > 0,
        'Energy reading available' => $latestActualKwh !== null,
    ];
    $readinessComplete = collect($readinessChecks)->filter()->count();
    $readinessPercent = (int) round(($readinessComplete / max(1, count($readinessChecks))) * 100);
    $currentReadingMonth = now()->format('Y-m');
    $currentReadingYear = (int) now()->format('Y');
    $currentReadingMonthNumber = (int) now()->format('n');
    $monthlyRecordsUrl = isset($facilityModel) ? route('facilities.monthly-records', [
        'facility' => $facilityModel->id,
        'year' => $currentReadingYear,
        'summary_mode' => 'month',
        'summary_month' => $currentReadingMonthNumber,
    ]) : '#';
    $addReadingUrl = isset($facilityModel) ? route('facilities.monthly-records', [
        'facility' => $facilityModel->id,
        'year' => $currentReadingYear,
        'summary_mode' => 'month',
        'summary_month' => $currentReadingMonthNumber,
        'open_add' => 1,
        'record_date' => $currentReadingMonth . '-01',
    ]) : '#';
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

    .profile-shell { position: relative; overflow: hidden; padding: 0; border: 1px solid #e2e8f0; box-shadow: 0 18px 50px rgba(15,23,42,.08); }
    .profile-shell::before { content: ''; position: absolute; z-index: 2; top: 0; right: 0; left: 0; height: 3px; background: linear-gradient(90deg,#2563eb,#6366f1 55%,#0ea5e9); }
    .profile-overview { position: relative; overflow: hidden; padding: 28px; color: #fff; background: radial-gradient(circle at 88% 5%,rgba(125,211,252,.2),transparent 28%),linear-gradient(125deg,#172554,#1e40af 52%,#2563eb); }
    .profile-overview::after { content: ''; position: absolute; inset: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size: 42px 42px; mask-image: linear-gradient(90deg,transparent,#000); }
    .profile-overview__top, .profile-overview__grid { position: relative; z-index: 1; }
    .profile-overview__top { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 28px; }
    .profile-breadcrumb { display: flex; align-items: center; gap: 7px; color: rgba(219,234,254,.72); font-size: .7rem; font-weight: 700; }
    .profile-breadcrumb a { color: inherit; text-decoration: none; }
    .profile-breadcrumb a:hover { color: #fff; }
    .profile-quick-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
    .profile-quick-action { min-height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 11px; color: #fff; border: 1px solid rgba(255,255,255,.18); border-radius: 10px; background: rgba(255,255,255,.08); text-decoration: none; font-size: .68rem; font-weight: 800; transition: transform .16s ease,background .16s ease; }
    .profile-quick-action:hover { color: #fff; background: rgba(255,255,255,.15); transform: translateY(-1px); }
    .profile-quick-action.is-primary { color: #1d4ed8; border-color: rgba(255,255,255,.72); background: #fff; }
    .profile-quick-action.is-primary:hover { color: #1e40af; background: #eff6ff; }
    .profile-overview__grid { display: grid; grid-template-columns: minmax(0,1.35fr) minmax(280px,.65fr); align-items: center; gap: 40px; }
    .facility-identity { display: flex; align-items: flex-start; gap: 17px; }
    .facility-identity__icon { width: 62px; height: 62px; flex: 0 0 auto; display: grid; place-items: center; color: #2563eb; border: 3px solid rgba(255,255,255,.25); border-radius: 18px; background: #fff; box-shadow: 0 14px 28px rgba(15,23,42,.2); font-size: 1.3rem; }
    .facility-source { width: max-content; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px; padding: 5px 8px; color: #bfdbfe; border: 1px solid rgba(191,219,254,.22); border-radius: 999px; background: rgba(255,255,255,.07); font-size: .61rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .facility-identity h1 { margin: 0; color: #fff; font-size: clamp(1.65rem,3vw,2.35rem); line-height: 1.15; letter-spacing: -.045em; }
    .facility-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 16px; margin-top: 12px; color: rgba(226,232,240,.76); font-size: .75rem; }
    .facility-meta span { display: inline-flex; align-items: center; gap: 6px; }
    .readiness-card { padding: 19px; border: 1px solid rgba(255,255,255,.16); border-radius: 17px; background: rgba(255,255,255,.09); backdrop-filter: blur(12px); }
    .readiness-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .readiness-head span { color: #bfdbfe; font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .readiness-head strong { color: #fff; font-size: 1.05rem; }
    .readiness-track { height: 7px; overflow: hidden; margin: 11px 0 14px; border-radius: 999px; background: rgba(15,23,42,.24); }
    .readiness-track span { height: 100%; display: block; border-radius: inherit; background: linear-gradient(90deg,#7dd3fc,#86efac); }
    .readiness-list { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; }
    .readiness-item { display: flex; align-items: center; gap: 7px; color: rgba(226,232,240,.72); font-size: .62rem; line-height: 1.35; }
    .readiness-item i { width: 18px; height: 18px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 50%; font-size: .5rem; }
    .readiness-item.is-done i { color: #166534; background: #bbf7d0; }
    .readiness-item.is-pending i { color: #c2410c; background: #fed7aa; }
    .profile-kpis { display: grid; grid-template-columns: repeat(5,minmax(0,1fr)); gap: 12px; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fbff; }
    .profile-kpi { min-width: 0; padding: 14px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; }
    .profile-kpi__top { display: flex; align-items: center; gap: 8px; color: #64748b; font-size: .62rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .profile-kpi__icon { width: 30px; height: 30px; display: grid; place-items: center; color: #2563eb; border-radius: 9px; background: #eff6ff; }
    .profile-kpi strong { display: block; margin-top: 10px; color: #0f172a; font-size: 1.16rem; letter-spacing: -.03em; }
    .profile-kpi small { display: block; overflow: hidden; margin-top: 3px; color: #7b8b9d; font-size: .6rem; text-overflow: ellipsis; white-space: nowrap; }
    .directory-section-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; padding: 23px 24px 15px; }
    .directory-section-heading span { display: block; margin-bottom: 5px; color: #2563eb; font-size: .65rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .directory-section-heading h2 { margin: 0; color: var(--report-text); font-size: 1.3rem; letter-spacing: -.03em; }
    .directory-section-heading p { max-width: 460px; margin: 0; color: var(--report-subtext); font-size: .72rem; line-height: 1.55; }
    .profile-shell > .meter-directory-grid { margin: 0; padding: 0 24px 24px; }

    body.dark-mode .energy-profile-page .profile-shell { border-color: #29384d; background: #0f172a; }
    body.dark-mode .energy-profile-page .profile-kpis { border-bottom-color: #29384d; background: #0f192a; }
    body.dark-mode .energy-profile-page .profile-kpi { border-color: #334155; background: #111c30; }
    body.dark-mode .energy-profile-page .profile-kpi__top { color: #94a3b8; }
    body.dark-mode .energy-profile-page .profile-kpi__icon { color: #7dd3fc; background: rgba(37,99,235,.14); }
    body.dark-mode .energy-profile-page .profile-kpi strong { color: #f1f5f9; }
    body.dark-mode .energy-profile-page .profile-kpi small { color: #8494a8; }
    body.dark-mode .energy-profile-page .facility-identity__icon { color: #2563eb; background: #fff; }
    body.dark-mode .energy-profile-page .facility-source,
    body.dark-mode .energy-profile-page .readiness-head span { color: #bfdbfe; }
    body.dark-mode .energy-profile-page .facility-meta,
    body.dark-mode .energy-profile-page .readiness-item { color: rgba(226,232,240,.76); }
    body.dark-mode .energy-profile-page .profile-quick-action { color: #fff; }
    body.dark-mode .energy-profile-page .profile-quick-action.is-primary { color: #1d4ed8; background: #fff; }
    body.dark-mode .energy-profile-page .profile-breadcrumb,
    body.dark-mode .energy-profile-page .profile-breadcrumb a { color: rgba(219,234,254,.72); }

    @media (max-width: 1080px) {
        .profile-overview__grid { grid-template-columns: 1fr; gap: 24px; }
        .profile-kpis { grid-template-columns: repeat(3,minmax(0,1fr)); }
    }

    @media (max-width: 700px) {
        .profile-overview { padding: 22px 18px; }
        .profile-overview__top { align-items: flex-start; flex-direction: column; }
        .profile-quick-actions { width: 100%; }
        .profile-quick-action { flex: 1; }
        .facility-identity { align-items: center; flex-direction: column; text-align: center; }
        .facility-source { margin-right: auto; margin-left: auto; }
        .facility-meta { justify-content: center; }
        .readiness-list { grid-template-columns: 1fr; }
        .profile-kpis { grid-template-columns: repeat(2,minmax(0,1fr)); padding: 16px; }
        .directory-section-heading { align-items: flex-start; flex-direction: column; padding: 20px 16px 13px; }
        .profile-shell > .meter-directory-grid { padding: 0 16px 16px; }
    }

    @media (max-width: 430px) {
        .profile-quick-actions { align-items: stretch; flex-direction: column; }
        .profile-quick-action { width: 100%; }
        .profile-kpis { grid-template-columns: 1fr; }
    }

    .meter-directory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .meter-directory-card {
        border: 1px solid var(--table-border);
        border-radius: 18px;
        overflow: hidden;
        background: var(--report-bg);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .07);
    }

    .meter-directory-head {
        padding: 14px 16px;
        background: var(--table-header-bg);
        color: #1e293b;
        font-weight: 800;
        font-size: .9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .meter-directory-title { display:flex; align-items:center; gap:10px; min-width:0; }
    .meter-directory-title__icon {
        width:38px; height:38px; border-radius:11px; display:inline-flex; align-items:center; justify-content:center;
        color:#2563eb; background:#dbeafe; border:1px solid #bfdbfe; flex:0 0 auto;
    }
    .meter-directory-title strong, .meter-directory-title small { display:block; }
    .meter-directory-title strong { color:var(--report-text); font-size:.92rem; }
    .meter-directory-title small { color:var(--report-subtext); font-size:.72rem; font-weight:600; margin-top:2px; }
    .meter-head-actions { display:flex; align-items:center; gap:8px; }
    .meter-count-badge {
        display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:5px 9px;
        background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; font-size:.73rem; font-weight:800;
    }

    .meter-directory-toolbar {
        padding: 12px 16px;
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
        border-radius: 11px;
        padding: 10px 78px 10px 38px;
        font-size: .85rem;
        background: #ffffff;
        color: #1e293b;
    }

    .meter-search-wrap { position:relative; min-width:0; }
    .meter-search-wrap > i {
        position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#64748b; pointer-events:none;
    }
    .meter-search-count {
        position:absolute; right:10px; top:50%; transform:translateY(-50%); padding:3px 7px; border-radius:999px;
        background:#f1f5f9; color:#475569; font-size:.68rem; font-weight:800; pointer-events:none;
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
        max-height: 390px;
        overflow: auto;
        padding: 8px;
        background: color-mix(in srgb, var(--report-bg) 94%, #e2e8f0);
    }

    .meter-directory-list.is-collapsed {
        display: none;
    }

    .meter-row {
        position: relative;
        padding: 15px 16px 14px 18px;
        border: 1px solid var(--table-border);
        border-radius: 13px;
        background: var(--report-bg);
        display: flex;
        flex-direction: column;
        gap: 12px;
        overflow: hidden;
    }

    .meter-row + .meter-row { margin-top: 8px; }
    .meter-row::before {
        content:''; position:absolute; inset:0 auto 0 0; width:4px;
        background:linear-gradient(180deg, #2563eb, #38bdf8);
    }

    .meter-row-clickable {
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background-color .16s ease;
    }

    .meter-row-clickable:hover {
        background: #f8fafc;
        border-color: #93c5fd;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .09);
        transform: translateY(-1px);
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
        gap: 8px;
    }

    .meter-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        padding: 5px 8px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
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
        padding-top: 10px;
        border-top: 1px dashed var(--table-border);
    }

    .meter-row-link-count {
        font-size: .8rem;
        color: #334155;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 32px;
        transition: transform .15s ease, filter .15s ease;
    }

    .meter-row-action-btn:hover { transform:translateY(-1px); filter:brightness(.97); }
    .meter-row-action-btn.is-view { padding:6px 11px; border-color:#2563eb; background:#2563eb; color:#fff; }

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

    .meter-status-pill.is-active { background:#dcfce7; color:#166534; border-color:#86efac; }
    .meter-status-pill.is-inactive { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .meter-approval-pill.is-approved { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
    .meter-approval-pill.is-pending { background:#fff7ed; color:#9a3412; border-color:#fdba74; }

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

        .meter-directory-head { align-items:flex-start; }
        .meter-head-actions { flex-wrap:wrap; justify-content:flex-end; }
    }

    @media (max-width: 560px) {
        .meter-directory-head { flex-direction:column; }
        .meter-head-actions { width:100%; justify-content:space-between; }
        .meter-toolbar-actions { display:grid; grid-template-columns:1fr 1fr; width:100%; }
        .meter-inline-btn { justify-content:center; }
        .meter-row-actions { width:100%; }
        .meter-row-action-btn.is-view { flex:1; }
        .meter-row-meta { display:grid; grid-template-columns:1fr; }
        .meter-meta-item { white-space:normal; }
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

    .meter-detail-overlay {
        display:none; position:fixed; inset:0; z-index:10039; padding:20px;
        background:rgba(2,6,23,.68); backdrop-filter:blur(7px); align-items:center; justify-content:center;
    }

    .meter-detail-modal {
        width:min(900px,calc(100vw - 32px)); max-height:min(760px,calc(100vh - 32px)); overflow:auto; position:relative;
        border:1px solid #dbeafe; border-radius:22px; background:#fff;
        box-shadow:0 28px 80px rgba(2,6,23,.32);
    }

    .meter-detail-modal-header {
        padding:18px 20px 15px; display:flex; align-items:flex-start; justify-content:space-between; gap:16px;
        background:linear-gradient(135deg,#eff6ff 0%,#fff 72%); border-bottom:1px solid #dbeafe;
    }

    .meter-detail-identity { display:flex; align-items:center; gap:13px; min-width:0; }
    .meter-detail-identity__icon {
        width:42px; height:42px; border-radius:12px; display:grid; place-items:center; flex:0 0 auto;
        background:linear-gradient(135deg,#2563eb,#38bdf8); color:#fff; font-size:1.1rem;
        box-shadow:0 10px 22px rgba(37,99,235,.24);
    }
    .meter-detail-eyebrow { color:#2563eb; font-size:.69rem; font-weight:900; letter-spacing:.11em; text-transform:uppercase; }
    .meter-detail-modal-title { margin:3px 0 0; color:#0f172a; font-size:1.3rem; font-weight:900; }
    .meter-detail-modal-subtitle { margin:3px 0 0; color:#64748b; font-size:.82rem; }
    .meter-detail-header-badges { display:flex; align-items:center; gap:6px; padding-right:36px; flex-wrap:wrap; justify-content:flex-end; }
    .meter-detail-header-badge {
        display:inline-flex; align-items:center; gap:6px; border:1px solid #cbd5e1; border-radius:999px;
        padding:5px 9px; background:#f8fafc; color:#475569; font-size:.7rem; font-weight:900;
    }
    .meter-detail-header-badge.is-active { background:#dcfce7; border-color:#86efac; color:#166534; }
    .meter-detail-header-badge.is-approved { background:#dbeafe; border-color:#93c5fd; color:#1d4ed8; }
    .meter-detail-header-badge.is-warning { background:#fff7ed; border-color:#fdba74; color:#9a3412; }
    .meter-detail-close {
        position:absolute; top:16px; right:17px; width:34px; height:34px; display:grid; place-items:center;
        border:1px solid #dbeafe; border-radius:10px; background:#fff; color:#64748b; cursor:pointer; font-size:.95rem;
    }
    .meter-detail-close:hover { color:#1d4ed8; border-color:#93c5fd; }
    .meter-detail-modal-body { padding:16px 18px 18px; }
    .meter-detail-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:14px; flex-wrap:wrap; }
    .meter-detail-section-title { display:flex; align-items:center; gap:7px; color:#334155; font-size:.76rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
    .meter-detail-section-title i { color:#2563eb; }
    .meter-detail-section-copy { margin:4px 0 0; color:#64748b; font-size:.76rem; }
    .meter-detail-data-count {
        display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px;
        background:#f1f5f9; border:1px solid #e2e8f0; color:#475569; font-size:.69rem; font-weight:800;
    }
    .meter-detail-group {
        margin-top:10px; padding:10px; border:1px solid #e2e8f0; border-radius:14px;
        background:#f8fafc;
    }
    .meter-detail-group:first-of-type { margin-top:10px; }
    .meter-detail-group-title {
        display:flex; align-items:center; gap:7px; margin:0 0 6px; color:#64748b;
        font-size:.69rem; font-weight:900; text-transform:uppercase; letter-spacing:.08em;
    }
    .meter-detail-group-title::after { content:''; height:1px; flex:1; background:#e2e8f0; }
    .meter-detail-group-title i { color:#2563eb; }
    .meter-detail-modal .meter-detail-grid,
    .meter-detail-modal .meter-detail-grid.is-four { grid-template-columns:repeat(4,minmax(0,1fr)); gap:8px; margin-top:7px; }
    .meter-detail-modal .meter-detail-item.span-2 { grid-column:span 2; }
    .meter-detail-modal .meter-detail-item {
        position:relative; min-height:76px; padding:11px; border-radius:11px; overflow:hidden;
        transition:border-color .16s ease,background-color .16s ease;
    }
    .meter-detail-modal .meter-detail-item::after {
        content:''; position:absolute; inset:0 0 auto; height:3px; background:#cbd5e1; opacity:.75;
    }
    .meter-detail-modal .meter-detail-item:hover { border-color:#cbd5e1; }
    .meter-detail-modal .meter-detail-item.is-featured { background:#eff6ff; border-color:#bfdbfe; }
    .meter-detail-modal .meter-detail-item.is-featured::after { background:linear-gradient(90deg,#2563eb,#38bdf8); opacity:1; }
    .meter-detail-modal .meter-detail-item.is-status-good { background:#f0fdf4; border-color:#bbf7d0; }
    .meter-detail-modal .meter-detail-item.is-status-good::after { background:#22c55e; opacity:1; }
    .meter-detail-modal .meter-detail-item.is-status-warning { background:#fff7ed; border-color:#fed7aa; }
    .meter-detail-modal .meter-detail-item.is-status-warning::after { background:#f97316; opacity:1; }
    .meter-detail-modal .meter-detail-item.is-approval-good { background:#eff6ff; border-color:#bfdbfe; }
    .meter-detail-modal .meter-detail-item.is-approval-good::after { background:#3b82f6; opacity:1; }
    .meter-detail-modal .meter-detail-item.is-wide { grid-column:1/-1; min-height:auto; }
    .meter-detail-modal .meter-detail-item.is-notes { padding:11px 12px; min-height:64px; background:#fff; }
    .meter-detail-modal .meter-detail-item-label { display:flex; align-items:center; gap:7px; }
    .meter-detail-modal .meter-detail-item-label i {
        width:22px; height:22px; display:inline-grid; place-items:center; flex:0 0 auto; border-radius:6px;
        color:#2563eb; background:#dbeafe; text-align:center; font-size:.68rem;
    }
    .meter-detail-modal .meter-detail-item-value { margin-top:6px; font-size:.88rem; line-height:1.3; }
    .meter-detail-modal .meter-detail-item.is-status-good .meter-detail-item-label i { background:#dcfce7; color:#15803d; }
    .meter-detail-modal .meter-detail-item.is-status-good .meter-detail-item-value { color:#166534; }
    .meter-detail-modal .meter-detail-item.is-status-warning .meter-detail-item-label i { background:#ffedd5; color:#c2410c; }
    .meter-detail-modal .meter-detail-item.is-status-warning .meter-detail-item-value { color:#9a3412; }
    .meter-detail-modal-footer {
        display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:12px; padding-top:12px;
        border-top:1px solid #e2e8f0; flex-wrap:wrap;
    }
    .meter-detail-submeters-btn {
        display:none; align-items:center; gap:8px; text-decoration:none; background:#2563eb; color:#fff;
        border:1px solid #2563eb; border-radius:11px; padding:10px 14px; font-weight:800; box-shadow:0 8px 18px rgba(37,99,235,.2);
    }
    .meter-detail-footer-note { color:#64748b; font-size:.75rem; display:inline-flex; align-items:center; gap:6px; }
    .meter-detail-dismiss {
        background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:10px; padding:9px 14px; font-weight:800; cursor:pointer;
    }

    @media (max-width:900px) {
        .meter-detail-modal .meter-detail-grid,
        .meter-detail-modal .meter-detail-grid.is-four { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }

    @media (max-width:700px) {
        .meter-detail-overlay { padding:10px; align-items:flex-end; }
        .meter-detail-modal { border-radius:20px 20px 12px 12px; max-height:92vh; }
        .meter-detail-modal-header { padding:18px; flex-direction:column; }
        .meter-detail-header-badges { padding-right:0; justify-content:flex-start; }
        .meter-detail-modal-body { padding:16px 18px 18px; }
        .meter-detail-group { padding:10px; }
    }

    @media (max-width:460px) {
        .meter-detail-modal .meter-detail-grid,
        .meter-detail-modal .meter-detail-grid.is-four { grid-template-columns:1fr; }
        .meter-detail-modal .meter-detail-item.span-2 { grid-column:auto; }
        .meter-detail-modal .meter-detail-item.is-wide { grid-column:auto; }
        .meter-detail-modal-footer { align-items:stretch; flex-direction:column; }
        .meter-detail-submeters-btn, .meter-detail-dismiss { justify-content:center; width:100%; }
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

    .meter-modal-card.form-modal {
        width: min(760px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        padding: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #dbe5f2;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15,23,42,.30);
    }

    .meter-form-modal-header {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 68px 20px 24px;
        background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
        border-bottom: 1px solid #dbe5f2;
    }

    .meter-form-modal-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: linear-gradient(135deg,#2563eb,#6366f1);
        color: #fff;
        box-shadow: 0 9px 20px rgba(79,70,229,.20);
    }

    .meter-form-modal-header .meter-modal-title {
        color: #0f172a;
        font-size: 1.25rem;
        letter-spacing: -.02em;
    }

    .meter-form-modal-header .meter-modal-subtitle {
        margin: 4px 0 0;
        font-size: .84rem;
        line-height: 1.4;
    }

    .form-modal .meter-modal-close {
        z-index: 5;
        top: 18px;
        right: 18px;
        width: 38px;
        height: 38px;
        border: 1px solid #dbe5f2;
        border-radius: 11px;
        background: rgba(255,255,255,.9);
        font-size: 1rem;
    }

    .form-modal .meter-modal-close:hover {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #e11d48;
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

    .form-modal .meter-manage-form {
        flex: 1 1 auto;
        min-height: 0;
        padding: 20px 26px 0;
        overflow-y: auto;
        gap: 14px 16px;
    }

    .meter-form-section-title {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 5px 0 -2px;
        color: #475569;
        font-size: .73rem;
        font-weight: 850;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .meter-form-section-title i { color: #2563eb; }

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

    .form-modal .meter-form-label {
        font-size: .78rem;
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

    .form-modal .meter-form-control {
        min-height: 45px;
        padding: 10px 12px;
        border-radius: 11px;
        font-size: .88rem;
        box-sizing: border-box;
    }

    .form-modal .meter-form-textarea {
        min-height: 84px;
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

    .form-modal .meter-form-actions {
        position: sticky;
        z-index: 4;
        bottom: 0;
        margin: 20px -26px 0;
        padding: 14px 26px;
        flex-wrap: nowrap;
        border-top: 1px solid #e2e8f0;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(8px);
    }

    .form-modal .meter-form-btn {
        min-height: 44px;
        padding: 10px 19px;
        border-radius: 11px;
        font-size: .86rem;
    }

    .form-modal .meter-form-btn.save {
        flex: 1;
        box-shadow: 0 7px 16px rgba(37,99,235,.20);
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

        .meter-modal-card.form-modal {
            width: calc(100vw - 12px);
            max-height: calc(100vh - 12px);
            border-radius: 17px;
        }

        .meter-form-modal-header { padding: 17px 55px 16px 16px; }
        .meter-form-modal-icon { width: 42px; height: 42px; flex-basis: 42px; border-radius: 12px; }
        .form-modal .meter-manage-form { padding: 16px 16px 0; }
        .form-modal .meter-form-actions {
            margin: 18px -16px 0;
            padding: 12px 16px;
            flex-direction: row;
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
        position: fixed; top: 22px; right: 22px; z-index: 99999;
        width: min(400px, calc(100vw - 32px));
        padding: 14px 16px; border-radius: 12px; font-weight: 800;
        display: flex; align-items: center; gap: 10px;
        border: 1px solid transparent;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
        transform: translateY(0);
        transition: opacity .22s ease, transform .22s ease;
    }

    .alert-box.is-hidden {
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
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

    body.dark-mode .energy-profile-page .meter-directory-card { box-shadow:0 18px 40px rgba(2,6,23,.38); }
    body.dark-mode .energy-profile-page .meter-directory-title__icon {
        background:#172554 !important; border-color:#1d4ed8 !important; color:#7dd3fc !important;
    }
    body.dark-mode .energy-profile-page .meter-directory-title strong { color:#f8fafc !important; }
    body.dark-mode .energy-profile-page .meter-directory-title small { color:#94a3b8 !important; }
    body.dark-mode .energy-profile-page .meter-count-badge {
        background:#172554 !important; border-color:#1e40af !important; color:#bfdbfe !important;
    }
    body.dark-mode .energy-profile-page .meter-directory-list { background:#0b1220 !important; }
    body.dark-mode .energy-profile-page .meter-row { background:#0f172a !important; }
    body.dark-mode .energy-profile-page .meter-search-wrap > i { color:#64748b !important; }
    body.dark-mode .energy-profile-page .meter-search-count { background:#1e293b !important; color:#cbd5e1 !important; }
    body.dark-mode .energy-profile-page .meter-meta-item {
        background:#111827 !important; border-color:#334155 !important; color:#cbd5e1 !important;
    }
    body.dark-mode .energy-profile-page .meter-row-action-btn.is-view {
        background:#2563eb !important; border-color:#3b82f6 !important; color:#fff !important;
    }
    body.dark-mode .energy-profile-page .meter-status-pill.is-active { background:#14532d !important; color:#bbf7d0 !important; border-color:#22c55e !important; }
    body.dark-mode .energy-profile-page .meter-status-pill.is-inactive { background:#7f1d1d !important; color:#fecaca !important; border-color:#ef4444 !important; }
    body.dark-mode .energy-profile-page .meter-approval-pill.is-approved { background:#172554 !important; color:#bfdbfe !important; border-color:#3b82f6 !important; }
    body.dark-mode .energy-profile-page .meter-approval-pill.is-pending { background:#431407 !important; color:#fed7aa !important; border-color:#f97316 !important; }

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

    body.dark-mode .energy-profile-page .meter-detail-modal {
        background:#0f172a !important; border-color:#334155 !important; box-shadow:0 30px 90px rgba(0,0,0,.58);
    }
    body.dark-mode .energy-profile-page .meter-detail-modal-header {
        background:linear-gradient(135deg,#172554 0%,#0f172a 72%) !important; border-color:#334155 !important;
    }
    body.dark-mode .energy-profile-page .meter-detail-modal-title { color:#f8fafc !important; }
    body.dark-mode .energy-profile-page .meter-detail-modal-subtitle { color:#94a3b8 !important; }
    body.dark-mode .energy-profile-page .meter-detail-close { background:#111827 !important; border-color:#334155 !important; color:#cbd5e1 !important; }
    body.dark-mode .energy-profile-page .meter-detail-section-title { color:#cbd5e1 !important; }
    body.dark-mode .energy-profile-page .meter-detail-modal .meter-detail-item.is-featured { background:#172554 !important; border-color:#1e40af !important; }
    body.dark-mode .energy-profile-page .meter-detail-modal-footer { border-color:#334155 !important; }
    body.dark-mode .energy-profile-page .meter-detail-footer-note { color:#94a3b8 !important; }
    body.dark-mode .energy-profile-page .meter-detail-submeters-btn { background:#2563eb !important; border-color:#3b82f6 !important; color:#fff !important; }
    body.dark-mode .energy-profile-page .meter-detail-dismiss { background:#111827 !important; border-color:#334155 !important; color:#e2e8f0 !important; }
    body.dark-mode .energy-profile-page .meter-detail-header-badge.is-active { background:#14532d !important; border-color:#22c55e !important; color:#bbf7d0 !important; }
    body.dark-mode .energy-profile-page .meter-detail-header-badge.is-approved { background:#172554 !important; border-color:#3b82f6 !important; color:#bfdbfe !important; }
    body.dark-mode .energy-profile-page .meter-detail-header-badge.is-warning { background:#431407 !important; border-color:#f97316 !important; color:#fed7aa !important; }

    body.dark-mode #meterDetailModal .meter-detail-modal { background:#0f172a !important; border-color:#334155 !important; box-shadow:0 30px 90px rgba(0,0,0,.58); }
    body.dark-mode #meterDetailModal .meter-detail-modal-header { background:linear-gradient(135deg,#172554 0%,#0f172a 72%) !important; border-color:#334155 !important; }
    body.dark-mode #meterDetailModal .meter-detail-modal-title { color:#f8fafc !important; }
    body.dark-mode #meterDetailModal .meter-detail-modal-subtitle { color:#94a3b8 !important; }
    body.dark-mode #meterDetailModal .meter-detail-close { background:#111827 !important; border-color:#334155 !important; color:#cbd5e1 !important; }
    body.dark-mode #meterDetailModal .meter-detail-section-title { color:#cbd5e1 !important; }
    body.dark-mode #meterDetailModal .meter-detail-section-copy { color:#94a3b8 !important; }
    body.dark-mode #meterDetailModal .meter-detail-group-title { color:#94a3b8 !important; }
    body.dark-mode #meterDetailModal .meter-detail-group-title::after { background:#334155 !important; }
    body.dark-mode #meterDetailModal .meter-detail-group { background:#0b1220 !important; border-color:#273449 !important; }
    body.dark-mode #meterDetailModal .meter-detail-data-count { background:#111827 !important; border-color:#334155 !important; color:#cbd5e1 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item { background:#111827 !important; border-color:#334155 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-featured { background:#172554 !important; border-color:#1e40af !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-good { background:#052e16 !important; border-color:#166534 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-warning { background:#431407 !important; border-color:#9a3412 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-approval-good { background:#172554 !important; border-color:#1e40af !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-notes { background:#111827 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item-label { color:#93c5fd !important; }
    body.dark-mode #meterDetailModal .meter-detail-item-label i { background:#172554 !important; color:#60a5fa !important; }
    body.dark-mode #meterDetailModal .meter-detail-item-value { color:#e2e8f0 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-good .meter-detail-item-label i { background:#14532d !important; color:#86efac !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-good .meter-detail-item-value { color:#bbf7d0 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-warning .meter-detail-item-label i { background:#7c2d12 !important; color:#fdba74 !important; }
    body.dark-mode #meterDetailModal .meter-detail-item.is-status-warning .meter-detail-item-value { color:#fed7aa !important; }
    body.dark-mode #meterDetailModal .meter-detail-modal-footer { border-color:#334155 !important; }
    body.dark-mode #meterDetailModal .meter-detail-footer-note { color:#94a3b8 !important; }
    body.dark-mode #meterDetailModal .meter-detail-submeters-btn { background:#2563eb !important; border-color:#3b82f6 !important; color:#fff !important; }
    body.dark-mode #meterDetailModal .meter-detail-dismiss { background:#111827 !important; border-color:#334155 !important; color:#e2e8f0 !important; }
    body.dark-mode #meterDetailModal .meter-detail-header-badge.is-active { background:#14532d !important; border-color:#22c55e !important; color:#bbf7d0 !important; }
    body.dark-mode #meterDetailModal .meter-detail-header-badge.is-approved { background:#172554 !important; border-color:#3b82f6 !important; color:#bfdbfe !important; }
    body.dark-mode #meterDetailModal .meter-detail-header-badge.is-warning { background:#431407 !important; border-color:#f97316 !important; color:#fed7aa !important; }

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

    body.dark-mode .energy-profile-page .meter-form-modal-header {
        background: linear-gradient(135deg,#111827,#172033) !important;
        border-color: #2a3850 !important;
    }

    body.dark-mode .energy-profile-page .meter-form-modal-header .meter-modal-title {
        color: #f8fafc !important;
    }

    body.dark-mode .energy-profile-page .meter-form-section-title {
        color: #cbd5e1 !important;
    }

    body.dark-mode .energy-profile-page .form-modal .meter-form-actions {
        background: rgba(15,23,42,.97) !important;
        border-color: #334155 !important;
    }

    /* Meter management modals render outside .energy-profile-page. */
    body.dark-mode .meter-modal-overlay { background:rgba(2,6,23,.76) !important; }
    body.dark-mode .meter-modal-overlay .meter-modal-card {
        color-scheme:dark;
        background:#0f172a !important;
        border-color:#334155 !important;
        color:#e2e8f0 !important;
        box-shadow:0 30px 90px rgba(0,0,0,.58) !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-modal-header {
        background:linear-gradient(135deg,#111827,#172554) !important;
        border-color:#334155 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-modal-header .meter-modal-title { color:#f8fafc !important; }
    body.dark-mode .meter-modal-overlay .meter-form-modal-header .meter-modal-subtitle { color:#94a3b8 !important; }
    body.dark-mode .meter-modal-overlay .meter-modal-close {
        background:#111827 !important;
        border-color:#334155 !important;
        color:#cbd5e1 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-modal-close:hover {
        background:#3f1723 !important;
        border-color:#9f1239 !important;
        color:#fda4af !important;
    }
    body.dark-mode .meter-modal-overlay .meter-manage-form { background:#0f172a !important; }
    body.dark-mode .meter-modal-overlay .meter-form-section-title { color:#cbd5e1 !important; }
    body.dark-mode .meter-modal-overlay .meter-form-section-title i { color:#60a5fa !important; }
    body.dark-mode .meter-modal-overlay .meter-form-label { color:#e2e8f0 !important; }
    body.dark-mode .meter-modal-overlay .meter-required { color:#fb7185 !important; }
    body.dark-mode .meter-modal-overlay .meter-form-control {
        background:#0b1220 !important;
        border-color:#334155 !important;
        color:#f8fafc !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-control::placeholder { color:#64748b !important; opacity:1; }
    body.dark-mode .meter-modal-overlay .meter-form-control:focus {
        border-color:#3b82f6 !important;
        box-shadow:0 0 0 3px rgba(59,130,246,.22) !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-control[readonly] {
        background:#111827 !important;
        color:#cbd5e1 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-hint { color:#94a3b8 !important; }
    body.dark-mode .meter-modal-overlay .meter-form-hint-warning { color:#fdba74 !important; }
    body.dark-mode .meter-modal-overlay .form-modal .meter-form-actions {
        background:rgba(15,23,42,.97) !important;
        border-color:#334155 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-form-btn.cancel {
        background:#1e293b !important;
        border:1px solid #334155 !important;
        color:#e2e8f0 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-archive-label {
        background:#111827 !important;
        border-color:#334155 !important;
        color:#e2e8f0 !important;
    }
    body.dark-mode .meter-modal-overlay .meter-modal-title:not(.danger) { color:#f8fafc !important; }
    body.dark-mode .meter-modal-overlay .meter-modal-subtitle { color:#94a3b8 !important; }
    body.dark-mode .meter-modal-overlay .meter-manage-form,
    body.dark-mode .meter-modal-overlay .meter-modal-card { scrollbar-color:#64748b #111827; }
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

    <div class="profile-card profile-shell">
        <section class="profile-overview" aria-labelledby="facilityProfileTitle">
            <div class="profile-overview__top">
                <div class="profile-breadcrumb">
                    <a href="{{ route('modules.reports.energy') }}">Energy Report</a>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>Facility Profile</span>
                </div>
                <div class="profile-quick-actions">
                    <a href="{{ route('modules.reports.energy', ['facility_id' => $facilityModel->id]) }}" class="profile-quick-action"><i class="fa-solid fa-arrow-left"></i> Back to report</a>
                    <a href="{{ $monthlyRecordsUrl }}" class="profile-quick-action"><i class="fa-solid fa-chart-line"></i> Monthly records</a>
                    @if(!$isCprfManaged && $canEncodeMainReadings && $mainMeterOptions->isNotEmpty())
                        <a href="{{ $addReadingUrl }}" class="profile-quick-action is-primary"><i class="fa-solid fa-plus"></i> Add reading</a>
                    @endif
                </div>
            </div>

            <div class="profile-overview__grid">
                <div class="facility-identity">
                    <span class="facility-identity__icon"><i class="fa-solid fa-building"></i></span>
                    <div>
                        <span class="facility-source"><i class="fa-solid {{ $isCprfManaged ? 'fa-arrows-rotate' : 'fa-location-dot' }}"></i> {{ $isCprfManaged ? 'CPRF Integrated' : 'Local Facility' }}</span>
                        <h1 id="facilityProfileTitle">{{ $facilityModel->name ?? 'Facility Details' }}</h1>
                        <div class="facility-meta">
                            <span><i class="fa-solid fa-layer-group"></i> {{ $facilityModel->type ?: 'Facility' }}</span>
                            <span><i class="fa-solid fa-map-marker-alt"></i> {{ $facilityModel->barangay ?: ($facilityModel->address ?: 'Location not specified') }}</span>
                            <span><i class="fa-solid fa-circle"></i> {{ ucfirst((string) ($facilityModel->status ?: 'Active')) }}</span>
                        </div>
                    </div>
                </div>

                <aside class="readiness-card" aria-label="Monitoring setup readiness">
                    <div class="readiness-head"><span>Setup readiness</span><strong>{{ $readinessPercent }}%</strong></div>
                    <div class="readiness-track" aria-hidden="true"><span style="width:{{ $readinessPercent }}%"></span></div>
                    <div class="readiness-list">
                        @foreach($readinessChecks as $label => $complete)
                            <div class="readiness-item {{ $complete ? 'is-done' : 'is-pending' }}">
                                <i class="fa-solid {{ $complete ? 'fa-check' : 'fa-exclamation' }}"></i><span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>
        </section>

        <div class="profile-kpis" aria-label="Facility energy profile summary">
            <div class="profile-kpi"><div class="profile-kpi__top"><span class="profile-kpi__icon"><i class="fa-solid fa-gauge-high"></i></span>Main meters</div><strong>{{ $mainMeterOptions->count() }}</strong><small>{{ $activeMainMeterCount }} currently active</small></div>
            <div class="profile-kpi"><div class="profile-kpi__top"><span class="profile-kpi__icon"><i class="fa-solid fa-bolt"></i></span>Active meters</div><strong>{{ $activeMeterCount }}</strong><small>Approved monitoring points</small></div>
            <div class="profile-kpi"><div class="profile-kpi__top"><span class="profile-kpi__icon"><i class="fa-solid fa-code-branch"></i></span>Sub-meters</div><strong>{{ $subMeterCount }}</strong><small>Linked downstream meters</small></div>
            <div class="profile-kpi"><div class="profile-kpi__top"><span class="profile-kpi__icon"><i class="fa-solid fa-bullseye"></i></span>Baseline</div><strong>{{ is_numeric($resolvedBaseline) ? number_format((float) $resolvedBaseline, 2) : '—' }}</strong><small>{{ is_numeric($resolvedBaseline) ? 'kWh target' : 'Not configured' }}</small></div>
            <div class="profile-kpi"><div class="profile-kpi__top"><span class="profile-kpi__icon"><i class="fa-solid fa-chart-column"></i></span>Latest usage</div><strong>{{ $latestActualKwh !== null ? number_format($latestActualKwh, 2) : '—' }}</strong><small>{{ $latestActualKwh !== null ? $latestPeriod.' · kWh' : $latestPeriod }}</small></div>
        </div>

        <div class="directory-section-heading">
            <div><span>Meter directory</span><h2>Main meter configuration</h2></div>
            <p>Review approved monitoring points, linked sub-meters, baselines, and equipment configuration.</p>
        </div>

        <div class="meter-directory-grid">
            <div class="meter-directory-card">
                <div class="meter-directory-head">
                    <div class="meter-directory-title">
                        <span class="meter-directory-title__icon"><i class="fa fa-bolt"></i></span>
                        <div>
                            <strong>Main Meter List</strong>
                            <small>Primary monitoring points and linked sub-meters</small>
                        </div>
                    </div>
                    <div class="meter-head-actions">
                        <span class="meter-count-badge"><i class="fa fa-gauge-high"></i> {{ $mainMeters->count() }} {{ Str::plural('meter', $mainMeters->count()) }}</span>
                        <button type="button" class="meter-toggle-btn" data-meter-toggle-target="mainMeterDirectoryList" aria-expanded="true">
                            <span class="meter-toggle-label">Collapse</span>
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="meter-directory-toolbar">
                    <div class="meter-toolbar-top">
                        <div class="meter-search-wrap">
                            <i class="fa fa-search"></i>
                            <input type="search"
                                   class="meter-search-input"
                                   data-meter-search-target="mainMeterDirectoryList"
                                   data-meter-search-count="mainMeterSearchCount"
                                   aria-label="Search main meters"
                                   placeholder="Search by meter name, number, location, or status">
                            <span class="meter-search-count" id="mainMeterSearchCount">{{ $mainMeters->count() }} found</span>
                        </div>
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
                        <span class="meter-toolbar-note"><i class="fa fa-circle-info"></i> Search results update as you type.</span>
                        <span class="meter-toolbar-note" style="font-weight:700;">
                            <i class="fa fa-arrow-pointer"></i> Select a row to open its full profile
                        </span>
                    </div>
                </div>
                <div class="meter-directory-list" id="mainMeterDirectoryList">
                    @forelse($mainMeters as $meter)
                        @php
                            $isActiveMeter = strtolower((string) ($meter->status ?? 'active')) === 'active';
                            $approvalState = $meter->approved_at ? 'approved' : 'not_approved';
                            $approvalText = $approvalState === 'approved' ? 'APPROVED' : 'NOT APPROVED';
                            $mainEquipmentUrl = '';
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
                             data-meter-created-at="{{ $meter->created_at ? $meter->created_at->format('M d, Y h:i A') : 'N/A' }}"
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
                                    <span class="meter-status-pill {{ $isActiveMeter ? 'is-active' : 'is-inactive' }}">
                                        {{ strtoupper((string) ($meter->status ?? 'active')) }}
                                    </span>
                                    <span class="meter-approval-pill {{ $approvalState === 'approved' ? 'is-approved' : 'is-pending' }}">
                                        {{ $approvalText }}
                                    </span>
                                </div>
                            </div>
                            <div class="meter-row-meta">
                                <span class="meter-meta-item"><i class="fa fa-hashtag"></i> {{ $meter->meter_number ?: 'N/A' }}</span>
                                <span class="meter-meta-item"><i class="fa fa-map-marker-alt"></i> {{ $meter->location ?: 'N/A' }}</span>
                                <span class="meter-meta-item"><i class="fa fa-calendar-plus"></i> {{ $meter->created_at ? $meter->created_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                <span class="meter-meta-item"><i class="fa fa-chart-line"></i> {{ is_numeric($meter->baseline_kwh) ? number_format((float) $meter->baseline_kwh, 2) . ' kWh' : 'N/A' }}</span>
                            </div>
                            <div class="meter-row-footer">
                                <span class="meter-row-link-count">
                                    <i class="fa fa-diagram-project"></i> Linked Sub-meters: {{ $linkedSubCount > 0 ? $linkedSubCount : 'None' }}
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
                                                class="meter-row-action-btn is-view"
                                                onclick="openMeterDetailModalFromButton(this)"
                                                title="View main meter details"
                                                aria-label="View main meter details">
                                            <i class="fa fa-eye"></i>
                                            <span>View details</span>
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
                                                     data-edit-meter='@json($editMeterPayload, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT)'
                                                     onclick="openEditMeterProfileModalFromButton(this, event)"
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
                        <div class="meter-row" data-meter-empty-static style="align-items:center;padding:32px 20px;text-align:center;">
                            <span style="width:48px;height:48px;display:grid;place-items:center;color:#2563eb;border-radius:14px;background:#eff6ff;font-size:1.1rem;"><i class="fa-solid fa-gauge-high"></i></span>
                            <div class="meter-row-name">No approved main meter yet</div>
                            <div class="meter-row-meta" style="justify-content:center;">Add and approve a main meter to start tracking facility consumption and baselines.</div>
                            @if($canManageMeters)
                                <button type="button" class="meter-inline-btn" onclick="openAddMeterProfileModal('main')"><i class="fa-solid fa-plus"></i> Add Main Meter</button>
                            @endif
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

<div id="meterDetailModal" class="meter-detail-overlay" role="dialog" aria-modal="true" aria-labelledby="meterDetailModalTitle">
    <div class="meter-detail-modal">
        <button type="button" onclick="closeMeterDetailModal()" class="meter-detail-close" aria-label="Close meter details"><i class="fa fa-xmark"></i></button>
        <header class="meter-detail-modal-header">
            <div class="meter-detail-identity">
                <span class="meter-detail-identity__icon"><i class="fa fa-gauge-high"></i></span>
                <div>
                    <span class="meter-detail-eyebrow">Meter directory</span>
                    <h3 id="meterDetailModalTitle" class="meter-detail-modal-title">Meter Details</h3>
                    <p id="meterDetailSubtitle" class="meter-detail-modal-subtitle">View selected meter information</p>
                </div>
            </div>
            <div class="meter-detail-header-badges">
                <span id="meterDetailHeaderStatus" class="meter-detail-header-badge"><i class="fa fa-circle"></i> Status</span>
                <span id="meterDetailHeaderApproval" class="meter-detail-header-badge"><i class="fa fa-shield-halved"></i> Approval</span>
            </div>
        </header>
        <div class="meter-detail-modal-body">
            <div class="meter-detail-section-head">
                <div>
                    <div class="meter-detail-section-title"><i class="fa fa-file-lines"></i> Meter information</div>
                    <p class="meter-detail-section-copy">Complete technical and administrative record for this monitoring point.</p>
                </div>
            </div>
            <section class="meter-detail-group">
                <h4 class="meter-detail-group-title"><i class="fa fa-fingerprint"></i> Identity &amp; assignment</h4>
                <div class="meter-detail-grid is-four">
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-layer-group"></i> Type</div><div id="meterDetailType" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item is-featured"><div class="meter-detail-item-label"><i class="fa fa-gauge"></i> Meter Name</div><div id="meterDetailName" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-hashtag"></i> Meter No.</div><div id="meterDetailNo" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item is-featured"><div class="meter-detail-item-label"><i class="fa fa-location-dot"></i> Location</div><div id="meterDetailLocation" class="meter-detail-item-value">-</div></div>
                </div>
            </section>
            <section class="meter-detail-group">
                <h4 class="meter-detail-group-title"><i class="fa fa-wave-square"></i> Monitoring configuration</h4>
                <div class="meter-detail-grid is-four">
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-code-branch"></i> Parent</div><div id="meterDetailParent" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-diagram-project"></i> Sub-meters</div><div id="meterDetailSubmeterCount" class="meter-detail-item-value">0</div></div>
                    <div class="meter-detail-item is-featured"><div class="meter-detail-item-label"><i class="fa fa-chart-line"></i> Baseline</div><div id="meterDetailBaseline" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-calculator"></i> Multiplier</div><div id="meterDetailMultiplier" class="meter-detail-item-value">-</div></div>
                </div>
            </section>
            <section class="meter-detail-group">
                <h4 class="meter-detail-group-title"><i class="fa fa-shield-halved"></i> Governance &amp; lifecycle</h4>
                <div class="meter-detail-grid is-four">
                    <div id="meterDetailStatusCard" class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-power-off"></i> Status</div><div id="meterDetailStatus" class="meter-detail-item-value">-</div></div>
                    <div id="meterDetailApprovalCard" class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-circle-check"></i> Approval</div><div id="meterDetailApproval" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-calendar-plus"></i> Date Added</div><div id="meterDetailCreatedAt" class="meter-detail-item-value">-</div></div>
                    <div class="meter-detail-item"><div class="meter-detail-item-label"><i class="fa fa-calendar-check"></i> Approved At</div><div id="meterDetailApprovedAt" class="meter-detail-item-value">-</div></div>
                </div>
            </section>
            <section class="meter-detail-group">
                <h4 class="meter-detail-group-title"><i class="fa fa-note-sticky"></i> Notes</h4>
                <div class="meter-detail-grid">
                    <div class="meter-detail-item is-wide is-notes"><div class="meter-detail-item-label"><i class="fa fa-comment-dots"></i> Additional remarks</div><div id="meterDetailNotes" class="meter-detail-item-value">-</div></div>
                </div>
            </section>
            <footer class="meter-detail-modal-footer">
                <div>
                    <a id="meterDetailSubmetersBtn" href="#" class="meter-detail-submeters-btn"><i class="fa fa-diagram-project"></i> View Linked Sub-meters <i class="fa fa-arrow-right"></i></a>
                    <span id="meterDetailNoSubmeterNote" class="meter-detail-footer-note"><i class="fa fa-circle-info"></i> No linked sub-meter directory available.</span>
                </div>
                <button type="button" onclick="closeMeterDetailModal()" class="meter-detail-dismiss">Close</button>
            </footer>
        </div>
    </div>
</div>

@if($canManageMeters)
<div id="addMeterProfileModal" class="meter-modal-overlay">
    <div class="meter-modal-card form-modal" role="dialog" aria-modal="true" aria-labelledby="addMeterProfileTitle" aria-describedby="addMeterProfileSubtitle">
        <button type="button" onclick="closeAddMeterProfileModal()" class="meter-modal-close" aria-label="Close add meter form"><i class="fa-solid fa-xmark"></i></button>
        <header class="meter-form-modal-header">
            <div class="meter-form-modal-icon"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <h3 id="addMeterProfileTitle" class="meter-modal-title">Add Main Meter</h3>
                <p id="addMeterProfileSubtitle" class="meter-modal-subtitle">Create the facility’s primary meter and monitoring baseline.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('modules.facilities.meters.store', $facilityModel->id) }}" class="meter-manage-form">
            @csrf
            <input type="hidden" name="_redirect_to" value="energy_profile">
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'add', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null, 'hasApprovedMainForSub' => $hasApprovedMainForSub, 'forceMeterType' => 'main', 'showFormSections' => true])
            <div class="meter-form-actions">
                <button type="button" onclick="closeAddMeterProfileModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save"><i class="fa-solid fa-floppy-disk"></i> Save Meter</button>
            </div>
        </form>
    </div>
</div>

<div id="editMeterProfileModal" class="meter-modal-overlay" style="z-index:10041;">
    <div class="meter-modal-card form-modal" role="dialog" aria-modal="true" aria-labelledby="editMeterProfileTitle" aria-describedby="editMeterProfileSubtitle">
        <button type="button" onclick="closeEditMeterProfileModal()" class="meter-modal-close" aria-label="Close edit meter form"><i class="fa-solid fa-xmark"></i></button>
        <header class="meter-form-modal-header">
            <div class="meter-form-modal-icon"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <h3 id="editMeterProfileTitle" class="meter-modal-title">Edit Meter</h3>
                <p id="editMeterProfileSubtitle" class="meter-modal-subtitle">Update meter identity, configuration, and baseline.</p>
            </div>
        </header>
        <form id="editMeterProfileForm" method="POST" action="#" class="meter-manage-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="_redirect_to" value="energy_profile">
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'edit', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null, 'hasApprovedMainForSub' => $hasApprovedMainForSub, 'showFormSections' => true])
            <div class="meter-form-actions">
                <button type="button" onclick="closeEditMeterProfileModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save"><i class="fa-solid fa-floppy-disk"></i> Update Meter</button>
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
        [s, e].forEach(function(alert) {
            if (!alert) return;

            setTimeout(function() {
                alert.classList.add('is-hidden');
            }, 2800);

            setTimeout(function() {
                alert.remove();
            }, 3300);
        });

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

                const countId = String(input.getAttribute('data-meter-search-count') || '');
                const count = countId ? document.getElementById(countId) : null;
                if (count) count.textContent = visible + ' found';
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

        document.querySelectorAll('#addMeterProfileModal, #editMeterProfileModal, #archiveMeterProfileModal').forEach(function(modal) {
            modal.addEventListener('click', function(event) {
                if (event.target !== modal) return;
                if (modal.id === 'addMeterProfileModal') closeAddMeterProfileModal();
                if (modal.id === 'editMeterProfileModal') closeEditMeterProfileModal();
                if (modal.id === 'archiveMeterProfileModal') closeArchiveMeterProfileModal();
            });
        });

        document.querySelectorAll('.form-modal .meter-manage-form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const submitButton = form.querySelector('.meter-form-btn.save');
                if (!submitButton) return;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMeterDetailModal();
                closeAddMeterProfileModal();
                closeEditMeterProfileModal();
                closeArchiveMeterProfileModal();
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
            meterDetailCreatedAt: row.getAttribute('data-meter-created-at') || 'N/A',
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

        const modalTitle = document.getElementById('meterDetailModalTitle');
        if (modalTitle) modalTitle.textContent = row.getAttribute('data-meter-name') || 'Meter Details';

        const statusText = String(row.getAttribute('data-meter-status') || 'N/A').toUpperCase();
        const approvalText = String(row.getAttribute('data-meter-approval') || 'N/A').toUpperCase();
        const headerStatus = document.getElementById('meterDetailHeaderStatus');
        const headerApproval = document.getElementById('meterDetailHeaderApproval');
        const statusCard = document.getElementById('meterDetailStatusCard');
        const approvalCard = document.getElementById('meterDetailApprovalCard');

        if (statusCard) {
            statusCard.classList.remove('is-status-good', 'is-status-warning');
            statusCard.classList.add(statusText === 'ACTIVE' ? 'is-status-good' : 'is-status-warning');
        }
        if (approvalCard) {
            approvalCard.classList.remove('is-approval-good', 'is-status-warning');
            approvalCard.classList.add(approvalText === 'APPROVED' ? 'is-approval-good' : 'is-status-warning');
        }

        if (headerStatus) {
            headerStatus.className = 'meter-detail-header-badge ' + (statusText === 'ACTIVE' ? 'is-active' : 'is-warning');
            headerStatus.innerHTML = '<i class="fa fa-circle"></i> ';
            headerStatus.append(document.createTextNode(statusText));
        }
        if (headerApproval) {
            headerApproval.className = 'meter-detail-header-badge ' + (approvalText === 'APPROVED' ? 'is-approved' : 'is-warning');
            headerApproval.innerHTML = '<i class="fa fa-shield-halved"></i> ';
            headerApproval.append(document.createTextNode(approvalText));
        }

        const submeterPageBtn = document.getElementById('meterDetailSubmetersBtn');
        const noSubmeterNote = document.getElementById('meterDetailNoSubmeterNote');
        if (submeterPageBtn) {
            const url = String(row.getAttribute('data-meter-submeters-page-url') || '').trim();
            const linkedCount = Math.max(0, Number.parseInt(row.getAttribute('data-meter-linked-submeter-count') || '0', 10) || 0);
            const submeterLabel = linkedCount > 0
                ? 'View ' + linkedCount + ' Linked Sub-meter' + (linkedCount === 1 ? '' : 's')
                : 'Manage Linked Sub-meters';
            submeterPageBtn.innerHTML = '<i class="fa fa-diagram-project"></i><span>' + submeterLabel + '</span><i class="fa fa-arrow-right"></i>';
            if (url !== '') {
                submeterPageBtn.href = url;
                submeterPageBtn.style.display = 'inline-flex';
                if (noSubmeterNote) noSubmeterNote.style.display = 'none';
            } else {
                submeterPageBtn.removeAttribute('href');
                submeterPageBtn.style.display = 'none';
                if (noSubmeterNote) noSubmeterNote.style.display = 'inline-flex';
            }
        }

        modal.style.display = 'flex';
        const closeButton = modal.querySelector('.meter-detail-close');
        if (closeButton) closeButton.focus();
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
        const form = modal?.querySelector('.meter-manage-form');
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
        if (form) form.scrollTop = 0;
        window.requestAnimationFrame(function() {
            document.getElementById('add_meter_name')?.focus();
        });
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
        const parentField = document.getElementById(prefix + '_parent_meter_field');
        if (!typeEl || !parentEl) return;

        if (prefix === 'add' && typeEl.value === 'sub' && !meterProfileConfig.hasApprovedMainForSub) {
            typeEl.value = 'main';
            alert(meterProfileConfig.noApprovedMainMessage);
        }

        if (typeEl.value === 'main') {
            parentEl.value = '';
            parentEl.disabled = true;
            parentEl.required = false;
            if (parentField) parentField.style.display = 'none';
        } else {
            parentEl.disabled = false;
            parentEl.required = true;
            if (parentField) parentField.style.display = '';
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
        form.scrollTop = 0;
        window.requestAnimationFrame(function() {
            document.getElementById('edit_meter_name')?.focus();
        });
    }

    function openEditMeterProfileModalFromButton(button, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!button) return;

        try {
            const meter = JSON.parse(button.getAttribute('data-edit-meter') || '{}');
            openEditMeterProfileModal(meter);
        } catch (error) {
            console.error('Unable to open meter editor.', error);
        }
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


