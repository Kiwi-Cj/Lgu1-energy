@extends('layouts.qc-admin')
@section('title', 'Facilities')

@section('content')

@php
    $user = auth()->user();
    $archivedFacilitiesCount = $archivedFacilitiesCount ?? 0;
@endphp

@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif



<!-- ...existing content... -->
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>
<style>
    /* --- Energy Report Inspired Aesthetic --- */
    .report-card-container {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 12px rgba(31,38,135,0.06); 
        padding: 28px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        gap: 20px;
    }

    /* Modern KPI Cards */
    .facility-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 2rem;
    }
    .stat-card {
        min-width: 0;
        padding: 18px 20px;
        border-radius: 15px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-top: 4px solid var(--stat-accent, #2563eb);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .045);
        transition: transform .22s ease, box-shadow .22s ease;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 28px rgba(15, 23, 42, .08); }

    .stat-card.is-total { --stat-accent:#2563eb; --stat-soft:#eff6ff; }
    .stat-card.is-active { --stat-accent:#16a34a; --stat-soft:#ecfdf3; }
    .stat-card.is-maintenance { --stat-accent:#ea8a00; --stat-soft:#fff8e8; }
    .stat-card.is-inactive { --stat-accent:#e11d48; --stat-soft:#fff1f2; }

    button.stat-card {
        appearance: none;
        color: inherit;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    .stat-card.is-selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        transform: translateY(-3px);
    }

    .stat-card:focus-visible {
        outline: 3px solid rgba(37, 99, 235, 0.3);
        outline-offset: 3px;
    }

    .card-icon-box {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px; font-size: 1rem;
        color: var(--stat-accent, #2563eb);
        background: var(--stat-soft, #eff6ff);
    }
    .facility-stat-topline { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .facility-stat-hint { color:#94a3b8; font-size:.72rem; font-weight:700; }
    .facility-stat-label {
        color: #64748b;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
    }
    .facility-stat-value {
        margin-top: 6px;
        color: #0f172a;
        font-size: 2.15rem;
        font-weight: 850;
        line-height: 1;
        letter-spacing: -.035em;
    }

    @media (max-width: 900px) {
        .facility-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 560px) {
        .facility-stat-grid { grid-template-columns: 1fr; }
        .facility-grid { grid-template-columns: minmax(0, 1fr); }
    }

    /* Facility Grid & Cards */
    .facility-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-top: 20px;
    }

    @media (max-width: 1350px) {
        .facility-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .facility-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 560px) {
        .facility-grid { grid-template-columns: minmax(0, 1fr); }
    }

    .facility-heading { display:flex; align-items:flex-start; gap:14px; }
    .facility-heading-icon {
        width:52px; height:52px; flex:0 0 52px; display:inline-flex; align-items:center; justify-content:center;
        border-radius:14px; color:#2563eb; background:linear-gradient(145deg,#eff6ff,#e0e7ff);
        border:1px solid #dbeafe; font-size:1.25rem;
    }
    .facility-page-title { margin:0; color:#0f2450; font-size:clamp(1.65rem,2.2vw,2.15rem); font-weight:850; line-height:1.1; letter-spacing:-.035em; }
    .facility-page-description { margin:6px 0 0; color:#64748b; font-weight:500; }

    .facility-toolbar { margin:0 0 22px; padding:18px; border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc; }
    .facility-toolbar-top { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:14px; }
    .facility-source-tabs {
        display:inline-flex;
        align-items:center;
        gap:5px;
        flex-wrap:wrap;
        padding:5px;
        border:1px solid #dbe3ef;
        border-radius:14px;
        background:#fff;
        box-shadow:0 3px 10px rgba(15,23,42,.04);
    }
    .facility-source-tab {
        min-height:38px; padding:7px 11px; border-radius:10px; border:1px solid transparent; background:transparent; color:#64748b;
        display:inline-flex; align-items:center; gap:7px; font-size:.8rem; font-weight:800; text-decoration:none; transition:.2s ease;
    }
    .facility-source-tab:hover { background:#f8fafc; color:#1d4ed8; }
    .facility-source-tab.is-active { border-color:#bfdbfe; background:#eff6ff; color:#1d4ed8; box-shadow:0 2px 7px rgba(37,99,235,.08); }
    .facility-source-tab-count {
        min-width:22px; height:22px; padding:0 6px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center;
        color:#64748b; background:#eef2f7; font-size:.68rem; font-weight:900;
    }
    .facility-source-tab.is-active .facility-source-tab-count { color:#fff; background:#2563eb; }
    .facility-sort-wrap { display:flex; align-items:center; gap:8px; color:#475569; font-size:.8rem; font-weight:800; }
    .facility-sort-select { min-height:40px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; color:#1e293b; padding:0 34px 0 11px; font:inherit; outline:none; }
    .facility-sort-select:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }

    .facility-card {
        background: #ffffff;
        border-radius: 17px;
        border: 1px solid #cddcf1;
        box-shadow: 0 5px 16px rgba(30, 64, 175, 0.055);
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .facility-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.1);
        border-color: #8fb4ee;
    }

    .image-wrapper {
        width: 100%;
        height: 120px;
        overflow: hidden;
        background: #f8fafc;
        position: relative;
        border-bottom: 1px solid #e0e8f4;
    }

    .facility-status-badge {
        position:absolute; top:9px; right:9px; z-index:2; display:inline-flex; align-items:center; gap:5px;
        border-radius:999px; padding:5px 8px; background:rgba(255,255,255,.94); box-shadow:0 4px 14px rgba(15,23,42,.12);
        color:#475569; font-size:.62rem; font-weight:850; text-transform:uppercase; letter-spacing:.035em;
    }
    .facility-status-badge.active { color:#15803d; }
    .facility-status-badge.maintenance { color:#b45309; }
    .facility-status-badge.inactive { color:#be123c; }
    .facility-status-badge i { font-size:.48rem; }

    .facility-card-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px; margin-top:9px; }
    .facility-card-meta-item { min-width:0; padding:6px 7px; border-radius:8px; background:#f8fafc; color:#64748b; font-size:.61rem; font-weight:700; }
    .facility-card-meta-item span { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .facility-card-meta-item strong { display:block; margin-top:1px; color:#1e293b; font-size:.76rem; }

    .image-wrapper img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .facility-card:hover .image-wrapper img { transform: scale(1.045); }

    .facility-image-placeholder {
        width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px;
        background:linear-gradient(145deg,#f8fafc,#eef4fb); color:#94a3b8;
    }
    .facility-image-placeholder-icon {
        width:58px; height:58px; display:inline-flex; align-items:center; justify-content:center;
        border-radius:16px; background:#fff; color:#94a3b8; border:1px solid #e2e8f0;
        box-shadow:0 8px 18px rgba(15,23,42,.06); font-size:1.35rem;
    }
    .facility-image-placeholder span { font-size:.72rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }

    .content-padding { padding:12px; flex-grow:1; display:flex; flex-direction:column; }
    .facility-card h3 { font-size:.92rem !important; margin-bottom:5px !important; line-height:1.18 !important; }
    .facility-card .content-padding > p { font-size:.7rem !important; margin-bottom:7px !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .facility-card .type-badge { margin-bottom:7px; padding:3px 8px; font-size:.58rem; }
    .facility-card .content-padding > div[style*="margin-bottom:10px"] { gap:5px !important; margin-bottom:5px !important; }
    .facility-card .content-padding > div[style*="margin-bottom:10px"] > span { font-size:.63rem !important; }

    .type-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6366f1;
        background: #eef2ff;
        padding: 4px 12px;
        border-radius: 100px;
        margin-bottom: 10px;
        display: inline-block;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #fff !important;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-gradient:hover { opacity: 0.9; transform: translateY(-1px); }

    .card-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
    }

    .facility-action-button {
        min-height:40px; display:inline-flex; align-items:center; justify-content:center; gap:5px;
        border:1px solid #dbe3ef; border-radius:10px; background:#fff; color:#475569;
        padding:7px 9px; font-size:.72rem; font-weight:800; text-decoration:none; transition:.18s ease;
    }
    .facility-action-button:hover { transform:translateY(-1px); border-color:#93c5fd; color:#1d4ed8; background:#f8fbff; }
    .facility-action-button.energy i { color:#f59e0b; }
    .facility-action-button.records i { color:#e11d48; }
    .facility-action-button.primary {
        grid-column:1 / -1; color:#fff; border-color:#2563eb; background:linear-gradient(135deg,#2563eb,#4f46e5);
        box-shadow:0 5px 12px rgba(37,99,235,.18);
    }
    .facility-action-button.primary:hover { color:#fff; background:linear-gradient(135deg,#1d4ed8,#4338ca); }
    .card-actions > .records:first-child { grid-column:1 / -1; }

    /* Compact, readable card layout for four-to-five column grids. */
    .facility-card-badges { display:flex; align-items:center; gap:5px; flex-wrap:wrap; min-height:24px; }
    .facility-card-title {
        display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden;
        min-height:2.2em; margin:7px 0 5px; color:#1e293b; font-size:.92rem; font-weight:850; line-height:1.1;
    }
    .facility-card-location { display:flex; align-items:center; gap:5px; min-width:0; margin:0 0 9px; color:#64748b; font-size:.72rem; }
    .facility-card-location i { flex:0 0 auto; color:#94a3b8; }
    .facility-card-location span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .facility-baseline-row {
        display:flex; align-items:center; gap:7px; min-width:0; padding:7px 8px; border:1px solid #e5edf7;
        border-radius:9px; background:#f8fbff; color:#64748b; font-size:.65rem; font-weight:700;
    }
    .facility-baseline-row i { color:#2563eb; }
    .facility-baseline-row strong { margin-left:auto; overflow:hidden; color:#1e293b; font-size:.7rem; text-overflow:ellipsis; white-space:nowrap; }
    .facility-card .card-actions { grid-template-columns:repeat(2,minmax(0,1fr)); align-items:center; }
    .facility-card .card-actions.is-staff { grid-template-columns:minmax(0,1fr); }
    .facility-card .facility-action-button { width:auto; padding:5px; }
    .facility-card .facility-action-button.primary { grid-column:1 / -1; grid-row:2; justify-content:center; padding:7px 9px; }
    .facility-card .facility-action-button.quick-action { width:auto; min-width:0; padding:7px 6px; font-size:.68rem; }
    .facility-card .facility-action-button.quick-action span { position:static; width:auto; height:auto; overflow:visible; clip:auto; white-space:nowrap; }
    .facility-card .card-actions > .records:first-child { grid-column:1 / -1; }

    body.dark-mode .facility-baseline-row { background:#0f172a; border-color:#334155; }
    body.dark-mode .facility-baseline-row strong,
    body.dark-mode .facility-card-title { color:#e2e8f0; }

    .action-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        text-decoration: none;
    }
    .action-icon.energy { background: #fff7ed; color: #f59e0b; }
    .action-icon.records { background: #eff6ff; color: #3b82f6; }
    .action-icon.inventory { background: #ecfdf3; color: #16a34a; }
    .action-icon:hover { transform: scale(1.1); }

    .facility-search-wrap {
        margin-bottom: 0;
    }

    .facility-search-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
    }

    .facility-search-input-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        padding: 10px 12px;
    }

    .facility-search-input-wrap i {
        color: #64748b;
        font-size: 0.95rem;
    }

    .facility-search-input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.95rem;
        color: #1e293b;
        background: transparent;
    }

    .facility-search-clear {
        border: none;
        background: #e2e8f0;
        color: #334155;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        font-size: 0.82rem;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .facility-search-clear[hidden] {
        display: none;
    }

    .facility-search-meta {
        margin-top: 8px;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
    }

    .facility-search-empty {
        display: none;
        margin-top: 14px;
        text-align: center;
        padding: 26px 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
    }

    .facility-search-reset {
        margin-top: 10px;
        border: none;
        border-radius: 8px;
        background: #2563eb;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 8px 12px;
        cursor: pointer;
    }

    .facility-match {
        background: #fef08a;
        color: #1e293b;
        border-radius: 3px;
        padding: 0 2px;
    }

    body.dark-mode .facilities-page .report-card-container {
        background: #0f172a !important;
        border: 1px solid #1f2937;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.55);
    }

    body.dark-mode .facilities-page .stat-card,
    body.dark-mode .facilities-page .facility-card,
    body.dark-mode .facilities-page .image-wrapper {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .facilities-page .facility-card:hover {
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.65);
        border-color: #60a5fa !important;
    }

    body.dark-mode .facilities-page .card-actions {
        border-top-color: #334155;
    }

    body.dark-mode .facilities-page .type-badge {
        background: #1e293b;
        color: #c4b5fd;
    }

    body.dark-mode .facilities-page .action-icon.energy {
        background: #3f2b1a;
        color: #fbbf24;
    }

    body.dark-mode .facilities-page .action-icon.records {
        background: #3f1d2e !important;
        color: #fda4af !important;
    }

    body.dark-mode .facilities-page .action-icon.inventory {
        background: #153827 !important;
        color: #86efac !important;
    }

    body.dark-mode .facilities-page .facility-search-input-wrap {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .facilities-page .facility-search-input {
        color: #e2e8f0;
    }

    body.dark-mode .facilities-page .facility-search-clear {
        background: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .facilities-page .facility-search-empty {
        background: #0b1220;
        border-color: #334155;
        color: #cbd5e1;
    }

    body.dark-mode .facilities-page .facility-search-reset {
        background: #3b82f6;
        color: #fff;
    }

    body.dark-mode .facilities-page .facility-toolbar,
    body.dark-mode .facilities-page .facility-card-meta-item {
        background:#111827;
        border-color:#334155;
    }
    body.dark-mode .facilities-page .facility-source-tabs,
    body.dark-mode .facilities-page .facility-source-tab,
    body.dark-mode .facilities-page .facility-sort-select { background:#0f172a; border-color:#334155; color:#e2e8f0; }
    body.dark-mode .facilities-page .facility-source-tab-count { color:#cbd5e1; background:#334155; }
    body.dark-mode .facilities-page .facility-source-tab.is-active { color:#bfdbfe; border-color:#1d4ed8; background:#172554; }
    body.dark-mode .facilities-page .facility-source-tab.is-active .facility-source-tab-count { color:#fff; background:#2563eb; }
    body.dark-mode .facilities-page .facility-card-meta-item strong { color:#e2e8f0; }
    body.dark-mode .facilities-page .facility-image-placeholder { background:linear-gradient(145deg,#111827,#172033); }
    body.dark-mode .facilities-page .facility-image-placeholder-icon,
    body.dark-mode .facilities-page .facility-action-button { background:#0f172a; border-color:#334155; color:#cbd5e1; }

    body.dark-mode .facilities-page .facility-match {
        background: #fde047;
        color: #0f172a;
    }

    .cprf-integration-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        margin-top: 8px;
        padding: 6px 10px;
        border: 1px solid;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        line-height: 1;
    }

    .cprf-integration-badge.is-active {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .cprf-integration-badge.is-inactive {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
    }

    .cprf-integration-badge .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
    }

    .cprf-integration-badge.is-inactive .status-dot {
        box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.12);
    }

    body.dark-mode .facilities-page [style*="background:#fff"],
    body.dark-mode .facilities-page [style*="background: #fff"],
    body.dark-mode .facilities-page [style*="background:#ffffff"],
    body.dark-mode .facilities-page [style*="background: #ffffff"],
    body.dark-mode .facilities-page [style*="background:#f8fafc"],
    body.dark-mode .facilities-page [style*="background: #f8fafc"],
    body.dark-mode .facilities-page [style*="background:#f1f5f9"],
    body.dark-mode .facilities-page [style*="background: #f1f5f9"] {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .facilities-page [style*="color:#222"],
    body.dark-mode .facilities-page [style*="color: #222"],
    body.dark-mode .facilities-page [style*="color:#1e293b"],
    body.dark-mode .facilities-page [style*="color: #1e293b"],
    body.dark-mode .facilities-page [style*="color:#334155"],
    body.dark-mode .facilities-page [style*="color: #334155"],
    body.dark-mode .facilities-page [style*="color:#64748b"],
    body.dark-mode .facilities-page [style*="color: #64748b"],
    body.dark-mode .facilities-page [style*="color:#94a3b8"],
    body.dark-mode .facilities-page [style*="color: #94a3b8"] {
        color: #e2e8f0 !important;
    }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; align-items: stretch; text-align: center; }
        .btn-gradient { justify-content: center; }
        .facilities-page .dashboard-actions { justify-content: center !important; }
        .facilities-page .archive-link {
            padding: 10px 12px !important;
            gap: 6px !important;
            min-width: 42px;
            min-height: 42px;
            justify-content: center;
        }
        .facilities-page .archive-link .archive-label { display: none; }
        .facility-heading { text-align:left; }
        .facility-toolbar-top { align-items:stretch; }
        .facility-source-tabs { display:grid; grid-template-columns:1fr; width:100%; }
        .facility-source-tab { justify-content:space-between; width:100%; }
        .facility-sort-wrap { width:100%; }
        .facility-sort-select { flex:1; }
        .report-card-container { padding:18px; border-radius:16px; }
    }
</style>

<div class="facilities-page" style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="dashboard-header">
            <div class="facility-heading">
                <span class="facility-heading-icon" aria-hidden="true"><i class="fas fa-building"></i></span>
                <div>
                <h1 class="facility-page-title">Facilities Management</h1>
                <p class="facility-page-description">Manage facility profiles, meters, records, and operational status.</p>
                <div class="cprf-integration-badge {{ ($cprfIntegrationActive ?? false) ? 'is-active' : 'is-inactive' }}"
                     title="{{ ($cprfIntegrationActive ?? false) ? 'CPRF feed is configured and public facilities have been mirrored.' : 'CPRF feed is not configured or has not completed its first mirror.' }}">
                    <span class="status-dot" aria-hidden="true"></span>
                    <span>CPRF Integration</span>
                    <span aria-label="Status: {{ ($cprfIntegrationActive ?? false) ? 'Active' : 'Inactive' }}">
                        {{ ($cprfIntegrationActive ?? false) ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                </div>
            </div>
            <div class="dashboard-actions" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                <a href="{{ route('modules.facilities.archive') }}"
                   class="archive-link"
                   aria-label="Archive"
                   title="Archive"
                   style="display:inline-flex;align-items:center;gap:8px;background:#f8fafc;color:#1e293b;border:1px solid #cbd5e1;border-radius:10px;padding:10px 14px;font-weight:700;text-decoration:none;">
                    <i class="fa fa-archive" aria-hidden="true"></i>
                    <span class="archive-label">Archive</span>
                    @if($archivedFacilitiesCount > 0)
                        <span style="background:#e11d48;color:#fff;border-radius:999px;padding:2px 8px;font-size:0.78rem;">{{ $archivedFacilitiesCount }}</span>
                    @endif
                </a>
                @if(\App\Support\RoleAccess::can(auth()->user(), 'manage_facility_master'))
                    <button type="button" id="btnAddFacilityTop" class="btn-gradient">
                        <i class="fa fa-plus-circle"></i> Add New Facility
                    </button>
                @endif
            </div>
        </div>

		<div class="facility-stat-grid" aria-label="Filter facilities by status">
            <button type="button" class="stat-card is-total is-selected" data-status-filter="all" aria-pressed="true">
				<div class="card-icon-box"><i class="fa fa-building"></i></div>
				<div class="facility-stat-topline"><div class="facility-stat-label">Total</div><span class="facility-stat-hint">View all</span></div>
				<div class="facility-stat-value">{{ $totalFacilities ?? 0 }}</div>
            </button>
            <button type="button" class="stat-card is-active" data-status-filter="active" aria-pressed="false">
				<div class="card-icon-box"><i class="fa fa-check-circle"></i></div>
				<div class="facility-stat-topline"><div class="facility-stat-label">Active</div><span class="facility-stat-hint">Filter</span></div>
				<div class="facility-stat-value">{{ $activeFacilities ?? 0 }}</div>
            </button>
            <button type="button" class="stat-card is-maintenance" data-status-filter="maintenance" aria-pressed="false">
				<div class="card-icon-box"><i class="fa fa-wrench"></i></div>
				<div class="facility-stat-topline"><div class="facility-stat-label">Maintenance</div><span class="facility-stat-hint">Filter</span></div>
				<div class="facility-stat-value">{{ $maintenanceFacilities ?? 0 }}</div>
            </button>
            <button type="button" class="stat-card is-inactive" data-status-filter="inactive" aria-pressed="false">
				<div class="card-icon-box"><i class="fa fa-ban"></i></div>
				<div class="facility-stat-topline"><div class="facility-stat-label">Inactive</div><span class="facility-stat-hint">Filter</span></div>
				<div class="facility-stat-value">{{ $inactiveFacilities ?? 0 }}</div>
            </button>
        </div>

        @php
            $sourceTab = $sourceTab ?? 'all';
            $sourceTabs = [
                'all' => [
                    'label' => 'All Facilities',
                    'count' => $totalFacilities ?? 0,
                    'icon' => 'fa-layer-group',
                    'title' => 'Show all available facilities',
                ],
            ];
            if ($canManageCprf ?? false) {
                $sourceTabs['cprf'] = [
                    'label' => 'CPRF Facilities',
                    'count' => $publicFacilitiesCount ?? 0,
                    'icon' => 'fa-building-shield',
                    'title' => 'Public facilities synchronized from Barangay Culiat CPRF',
                ];
            }
        @endphp
        <div class="facility-toolbar">
        <div class="facility-toolbar-top">
        <div class="facility-source-tabs" aria-label="Facility source filter">
            @foreach ($sourceTabs as $tabKey => $tab)
                <a href="{{ route('facilities.index', $tabKey === 'all' ? [] : ['source' => $tabKey]) }}"
                   class="facility-source-tab {{ $sourceTab === $tabKey ? 'is-active' : '' }}"
                   title="{{ $tab['title'] }}"
                   @if($sourceTab === $tabKey) aria-current="page" @endif>
                    <span><i class="fa-solid {{ $tab['icon'] }}" aria-hidden="true"></i> {{ $tab['label'] }}</span>
                    <span class="facility-source-tab-count">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>
            <label class="facility-sort-wrap" for="facilitySort">
                <span><i class="fas fa-sort-amount-down"></i> Sort by</span>
                <select id="facilitySort" class="facility-sort-select">
                    <option value="default">Default order</option>
                    <option value="name-asc">Name (A–Z)</option>
                    <option value="name-desc">Name (Z–A)</option>
                    <option value="status">Status</option>
                </select>
            </label>
            @if($sourceTab === 'cprf' && ($canSyncCprf ?? false))
                <form method="POST" action="{{ route('facilities.sync-cprf') }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit" class="btn-gradient" style="padding:8px 18px; border-radius:999px; font-size:0.85rem; font-weight:800;">
                        <i class="fas fa-rotate"></i> Sync from CPRF now
                    </button>
                </form>
            @endif
        </div>
        @if($sourceTab === 'cprf' && ($canManageCprf ?? false))
            <div style="background:#f5f3ff; border:1px solid #ddd6fe; color:#5b21b6; border-radius:12px; padding:10px 16px; font-size:0.85rem; font-weight:600; margin-bottom:1.25rem;">
                <i class="fas fa-circle-info"></i>
                These public facilities are synced automatically from the Barangay Culiat Facilities Reservation System (CPRF).
                Their names, addresses, and details are managed by CPRF and are read-only here — energy profiles, meters, and readings remain fully manageable.
            </div>
        @endif

        <div class="facility-search-wrap">
            <label for="facilityLiveSearch" class="facility-search-label">Live Search</label>
            <div class="facility-search-input-wrap">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input
                    id="facilityLiveSearch"
                    type="text"
                    class="facility-search-input"
                    placeholder="Search facility name, type, address, or barangay..."
                    autocomplete="off"
                >
                <button type="button" id="facilitySearchClear" class="facility-search-clear" aria-label="Clear search" title="Clear search" hidden>&times;</button>
            </div>
            <div class="facility-search-meta">
                Showing <span id="facilitySearchVisibleCount">{{ $facilities->count() }}</span> of <span id="facilitySearchTotalCount">{{ $facilities->count() }}</span> facilities
            </div>
            <div class="facility-search-meta">
                Search includes facility, type, address, barangay, and meter names.
            </div>
        </div>
        </div>

        <div class="facility-grid" id="facilityGrid">
            @forelse($facilities as $facility)
                @php
                    $searchIndex = Str::lower(trim(implode(' ', [
                        (string) ($facility->name ?? ''),
                        (string) ($facility->type ?? ''),
                        (string) ($facility->address ?? ''),
                        (string) ($facility->barangay ?? ''),
                        (string) ($facility->searchMeterNames ?? ''),
                        (string) ($facility->searchSubmeterNames ?? ''),
                    ])));
                    $normalizedStatus = Str::lower(trim((string) ($facility->status ?? 'inactive')));
                @endphp
                <div class="facility-card"
                     data-facility-card
                     data-search="{{ $searchIndex }}"
                     data-name="{{ Str::lower((string) $facility->name) }}"
                     data-status="{{ $normalizedStatus }}">
                    <div class="image-wrapper">
                        <span class="facility-status-badge {{ $normalizedStatus }}"><i class="fas fa-circle"></i> {{ Str::headline($normalizedStatus) }}</span>
                        @php
                            $imageUrl = $facility->resolved_image_url;
                        @endphp
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $facility->name }}">
                        @else
                            <div class="facility-image-placeholder" aria-label="No facility photo available">
                                <span class="facility-image-placeholder-icon"><i class="fas fa-building"></i></span>
                                <span>No facility photo</span>
                            </div>
                        @endif
                        <a href="{{ route('modules.facilities.show', $facility->id) }}" aria-label="View {{ $facility->name }} details" style="position:absolute; inset:0; z-index:1;"></a>
                    </div>

                    <div class="content-padding">
                        <div class="facility-card-badges">
                        <span class="type-badge" data-search-text>{{ $facility->type ?? 'General' }}</span>
                        @if(method_exists($facility, 'isCprfManaged') && $facility->isCprfManaged())
                            <span title="Synced from the CPRF Facilities Reservation System — identity details are read-only here"
                                  style="display:inline-flex; align-items:center; gap:5px; background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; border-radius:999px; padding:3px 10px; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.03em; margin-left:6px;">
                                <i class="fas fa-link"></i> CPRF
                            </span>
                        @endif
                        </div>
                        <h3 class="facility-card-title" data-search-text>{{ $facility->name }}</h3>
                        <p class="facility-card-location" title="{{ $facility->address ?? 'No address provided' }}">
                            <i class="fas fa-map-marker-alt"></i>
                            <span data-search-text>{{ Str::limit($facility->address ?? 'No address provided', 50) }}</span>
                        </p>
                        <div class="facility-baseline-row" title="Source: {{ $facility->resolvedBaselineSourceLabel ?? 'No baseline configured' }}">
                            <i class="fas fa-chart-line" aria-hidden="true"></i>
                            <span>Baseline</span>
                            @if(isset($facility->resolvedBaselineKwh) && $facility->resolvedBaselineKwh !== null)
                                <strong>{{ number_format((float) $facility->resolvedBaselineKwh, 0) }} kWh</strong>
                            @else
                                <strong>Not set</strong>
                            @endif
                        </div>

                        @php
                            $isStaffUser = (auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))) === 'staff';
                        @endphp
                        <div class="card-actions {{ $isStaffUser ? 'is-staff' : '' }}" style="position:relative; z-index:2;">
                            <a href="{{ route('modules.facilities.show', $facility->id) }}" class="facility-action-button primary">
                                View details <i class="fas fa-arrow-right"></i>
                            </a>
                            @if(!$isStaffUser)
                            <a href="{{ url('/modules/facilities/' . $facility->id . '/energy-profile') }}" class="facility-action-button energy quick-action" title="Open Energy Profile" aria-label="Open Energy Profile">
                                <i class="fas fa-bolt"></i><span>Energy Profile</span>
                            </a>
                            @endif
                            <a href="{{ route('facilities.monthly-records', $facility->id) }}" class="facility-action-button records quick-action" title="Open Monthly Records" aria-label="Open Monthly Records">
                                <i class="fas fa-file-alt"></i><span>Energy Records</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align:center; padding:60px; background:#fff; border-radius:20px; border:2px dashed #cbd5e1;">
                    <i class="fas fa-building fa-3x" style="color:#cbd5e1; margin-bottom:15px;"></i>
                    <h3 style="color:#64748b;">No facilities found</h3>
                    <p style="color:#94a3b8;">Start by adding a new facility to the system.</p>
                </div>
            @endforelse
        </div>
        <div id="facilitySearchEmpty" class="facility-search-empty">
            <div>No matching facilities found for your search.</div>
            <button type="button" id="facilitySearchReset" class="facility-search-reset">Reset search</button>
        </div>
    </div>
</div>

@include('modules.facilities.partials.modals')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addFacilityModal');
        const openModal = () => {
            if (typeof window.openAddFacilityModal === 'function') {
                window.openAddFacilityModal();
                return;
            }
            if (modal) modal.style.display = 'flex';
        };

        document.getElementById('btnAddFacilityTop')?.addEventListener('click', openModal);

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalRoot = btn.closest('.modal, .modal-overlay');
                if (modalRoot) {
                    modalRoot.style.display = 'none';
                }
            });
        });

        const searchInput = document.getElementById('facilityLiveSearch');
        const clearButton = document.getElementById('facilitySearchClear');
        const resetButton = document.getElementById('facilitySearchReset');
        const cards = Array.from(document.querySelectorAll('[data-facility-card]'));
        const emptyState = document.getElementById('facilitySearchEmpty');
        const visibleCountEl = document.getElementById('facilitySearchVisibleCount');
        const totalCountEl = document.getElementById('facilitySearchTotalCount');
        const searchableTexts = Array.from(document.querySelectorAll('[data-search-text]'));
        const statusFilterButtons = Array.from(document.querySelectorAll('[data-status-filter]'));
        const facilityGrid = document.getElementById('facilityGrid');
        const sortSelect = document.getElementById('facilitySort');
        let activeStatus = 'all';

        if (totalCountEl) {
            totalCountEl.textContent = String(cards.length);
        }

        searchableTexts.forEach((element) => {
            if (!element.hasAttribute('data-original-text')) {
                element.setAttribute('data-original-text', element.textContent || '');
            }
        });

        const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const escapeHtml = (value) => value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const highlightElement = (element, tokens) => {
            const originalText = element.getAttribute('data-original-text') || '';
            const validTokens = Array.from(new Set(tokens.filter((token) => token.length >= 2)));

            if (validTokens.length === 0) {
                element.innerHTML = escapeHtml(originalText);
                return;
            }

            const regex = new RegExp(validTokens.map(escapeRegExp).join('|'), 'ig');
            const matches = Array.from(originalText.matchAll(regex));

            if (!matches.length) {
                element.innerHTML = escapeHtml(originalText);
                return;
            }

            let html = '';
            let lastIndex = 0;

            matches.forEach((match) => {
                const matchText = match[0] || '';
                const start = match.index ?? 0;
                const end = start + matchText.length;
                html += escapeHtml(originalText.slice(lastIndex, start));
                html += '<mark class="facility-match">' + escapeHtml(matchText) + '</mark>';
                lastIndex = end;
            });

            html += escapeHtml(originalText.slice(lastIndex));
            element.innerHTML = html;
        };

        const clearSearch = () => {
            if (!searchInput) return;
            searchInput.value = '';
            applyFacilitySearch();
            searchInput.focus();
        };

        const resetFilters = () => {
            if (searchInput) {
                searchInput.value = '';
            }
            activeStatus = 'all';
            statusFilterButtons.forEach((button) => {
                const isSelected = button.getAttribute('data-status-filter') === activeStatus;
                button.classList.toggle('is-selected', isSelected);
                button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            });
            applyFacilitySearch();
            searchInput?.focus();
        };

        const applyFacilitySearch = () => {
            if (!cards.length) {
                if (emptyState) emptyState.style.display = 'none';
                if (visibleCountEl) visibleCountEl.textContent = '0';
                if (clearButton) clearButton.hidden = true;
                return;
            }

            const query = ((searchInput?.value) || '').toLowerCase().trim();
            const tokens = query === '' ? [] : query.split(/\s+/).filter(Boolean);
            let visibleCount = 0;

            if (clearButton) {
                clearButton.hidden = query === '';
            }

            cards.forEach((card) => {
                const haystack = ((card.getAttribute('data-search')) || '').toLowerCase();
                const cardStatus = ((card.getAttribute('data-status')) || '').toLowerCase();
                const matchesSearch = tokens.every((token) => haystack.includes(token));
                const matchesStatus = activeStatus === 'all' || cardStatus === activeStatus;
                const isMatch = matchesSearch && matchesStatus;
                card.style.display = isMatch ? '' : 'none';
                const cardSearchTexts = Array.from(card.querySelectorAll('[data-search-text]'));
                cardSearchTexts.forEach((element) => highlightElement(element, isMatch ? tokens : []));
                if (isMatch) visibleCount += 1;
            });

            if (visibleCountEl) {
                visibleCountEl.textContent = String(visibleCount);
            }

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        };

        const sortFacilityCards = () => {
            if (!facilityGrid || !sortSelect) return;
            const mode = sortSelect.value;
            const orderedCards = [...cards].sort((left, right) => {
                const originalLeft = cards.indexOf(left);
                const originalRight = cards.indexOf(right);
                if (mode === 'name-asc') return (left.dataset.name || '').localeCompare(right.dataset.name || '');
                if (mode === 'name-desc') return (right.dataset.name || '').localeCompare(left.dataset.name || '');
                if (mode === 'status') {
                    const rank = { maintenance: 0, inactive: 1, active: 2 };
                    return (rank[left.dataset.status] ?? 3) - (rank[right.dataset.status] ?? 3)
                        || (left.dataset.name || '').localeCompare(right.dataset.name || '');
                }
                return originalLeft - originalRight;
            });
            orderedCards.forEach((card) => facilityGrid.appendChild(card));
        };

        clearButton?.addEventListener('click', clearSearch);
        resetButton?.addEventListener('click', resetFilters);
        searchInput?.addEventListener('input', applyFacilitySearch);
        sortSelect?.addEventListener('change', sortFacilityCards);
        statusFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeStatus = button.getAttribute('data-status-filter') || 'all';
                statusFilterButtons.forEach((candidate) => {
                    const isSelected = candidate === button;
                    candidate.classList.toggle('is-selected', isSelected);
                    candidate.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                });
                applyFacilitySearch();
            });
        });
        applyFacilitySearch();
    });
</script>
@endsection
