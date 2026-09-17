@extends('layouts.qc-admin')
@section('title', 'Monthly Records')

@section('content')
<style>
    .monthly-shell {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding-bottom: 6px;
    }

    .report-card-container.monthly-report-card-container {
        width: 100%;
        padding: 26px;
        border: 1px solid #dbe5f2;
        border-radius: 26px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 58%, #eef4ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
        box-sizing: border-box;
    }

    .report-card-container.monthly-report-card-container,
    .report-card-container.monthly-report-card-container * {
        box-sizing: border-box;
    }

    .monthly-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .monthly-card-body {
        padding: 16px 18px;
    }

    .monthly-alert {
        padding: 12px 14px;
        border-radius: 12px;
        font-weight: 700;
    }

    .monthly-alert.success {
        background: #dcfce7;
        color: #166534;
    }

    .monthly-alert.error {
        background: #fee2e2;
        color: #b91c1c;
    }

    .monthly-alert.warn {
        background: #fff7ed;
        color: #9a3412;
    }

    .monthly-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .monthly-header h1 {
        margin: 0;
        color: #2563eb;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .monthly-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .92rem;
    }

    .monthly-header .facility-name {
        color: #1e293b;
        font-weight: 800;
    }

    .monthly-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-action-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        min-height: 50px;
        padding: 0 16px;
        font-weight: 800;
        font-size: .92rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
        box-sizing: border-box;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .monthly-action-btn:hover {
        transform: translateY(-1px);
    }

    .monthly-action-btn.is-info {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .monthly-action-btn.is-submeter {
        background: #f5f3ff;
        color: #6d28d9;
        border-color: #ddd6fe;
    }

    .monthly-action-btn.is-primary {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        border: none;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.24);
    }

    @media (max-width: 760px) {
        .monthly-action-btn {
            min-height: 46px;
            padding: 0 13px;
        }
    }

    body.dark-mode .monthly-action-btn.is-info {
        background: #10213f;
        color: #93c5fd;
        border-color: #1e3a8a;
    }

    body.dark-mode .monthly-action-btn.is-submeter {
        background: #271447;
        color: #c4b5fd;
        border-color: #4c1d95;
    }

    body.dark-mode .monthly-action-btn.is-primary {
        background: linear-gradient(90deg,#1d4ed8,#4f46e5);
        color: #fff;
    }

    .monthly-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .monthly-summary .item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
    }

    .monthly-summary .label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .monthly-summary .value {
        margin-top: 4px;
        color: #1e293b;
        font-size: 1.06rem;
        font-weight: 800;
    }

    .monthly-overview-chart-wrap {
        overflow-x: auto;
        padding: 12px 4px 2px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .monthly-overview-chart {
        min-width: 840px;
        height: 300px;
        display: grid;
        grid-template-columns: repeat(12, minmax(58px, 1fr));
        align-items: end;
        gap: 12px;
        padding: 18px 10px 0;
        border-bottom: 1px solid #cbd5e1;
        background:
            repeating-linear-gradient(
                to top,
                transparent 0,
                transparent 59px,
                #eef2f7 60px
            );
    }

    .monthly-overview-bar-column {
        height: 100%;
        display: grid;
        grid-template-rows: minmax(0, 1fr) auto auto;
        align-items: end;
        gap: 7px;
        min-width: 0;
    }

    .monthly-overview-bar-area {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        gap: 5px;
        min-height: 0;
    }

    .monthly-overview-bar-value {
        color: #334155;
        font-size: .72rem;
        line-height: 1;
        font-weight: 800;
        white-space: nowrap;
    }

    .monthly-overview-bar {
        width: min(42px, 72%);
        min-height: 3px;
        border-radius: 9px 9px 3px 3px;
        background: linear-gradient(180deg, #3b82f6 0%, #2563eb 55%, #4f46e5 100%);
        box-shadow: 0 7px 14px rgba(37, 99, 235, .2);
        transition: filter .15s ease, transform .15s ease;
    }

    .monthly-overview-bar-column:hover .monthly-overview-bar,
    .monthly-overview-bar-column:focus .monthly-overview-bar {
        filter: brightness(1.08);
        transform: scaleX(1.06);
    }

    .monthly-overview-bar-column.is-empty .monthly-overview-bar {
        background: #cbd5e1;
        box-shadow: none;
    }

    .monthly-overview-bar-column.is-warning .monthly-overview-bar { background:linear-gradient(180deg,#fbbf24,#d97706); box-shadow:0 7px 14px rgba(217,119,6,.22); }
    .monthly-overview-bar-column.is-high .monthly-overview-bar,
    .monthly-overview-bar-column.is-very-high .monthly-overview-bar { background:linear-gradient(180deg,#fb923c,#ea580c); box-shadow:0 7px 14px rgba(234,88,12,.23); }
    .monthly-overview-bar-column.is-critical .monthly-overview-bar { background:linear-gradient(180deg,#fb7185,#e11d48); box-shadow:0 7px 16px rgba(225,29,72,.25); }
    .monthly-overview-bar-column.is-drop-warning .monthly-overview-bar,
    .monthly-overview-bar-column.is-drop-high .monthly-overview-bar,
    .monthly-overview-bar-column.is-drop-critical .monthly-overview-bar { background:linear-gradient(180deg,#818cf8,#4f46e5); box-shadow:0 7px 14px rgba(79,70,229,.22); }
    .monthly-overview-no-record { min-height:18px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:.65rem; font-weight:800; }
    .monthly-overview-alert { display:inline-flex; align-items:center; justify-content:center; min-height:18px; margin-top:2px; padding:2px 6px; border-radius:999px; color:#475569; background:#f1f5f9; font-size:.56rem; font-weight:900; white-space:nowrap; }
    .monthly-overview-alert.critical { color:#991b1b; background:#fee2e2; }
    .monthly-overview-alert.very-high,.monthly-overview-alert.high { color:#9a3412; background:#ffedd5; }
    .monthly-overview-alert.warning { color:#92400e; background:#fef3c7; }
    .monthly-overview-alert.drop-critical,.monthly-overview-alert.drop-high,.monthly-overview-alert.drop-warning { color:#4338ca; background:#e0e7ff; }
    .monthly-overview-insights { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; margin:12px 4px 4px; }
    .monthly-overview-insight { display:flex; align-items:center; gap:9px; min-width:0; padding:10px 11px; border:1px solid #e2e8f0; border-radius:11px; background:#f8fafc; }
    .monthly-overview-insight i { color:#2563eb; }
    .monthly-overview-insight-label { color:#64748b; font-size:.61rem; font-weight:800; text-transform:uppercase; }
    .monthly-overview-insight-value { margin-top:2px; color:#1e293b; font-size:.76rem; font-weight:900; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    .monthly-overview-month {
        color: #0f172a;
        font-size: .78rem;
        font-weight: 900;
        text-align: center;
    }

    .monthly-overview-cost {
        min-height: 28px;
        color: #15803d;
        font-size: .67rem;
        line-height: 1.15;
        font-weight: 800;
        text-align: center;
        white-space: nowrap;
    }

    .monthly-overview-cost span {
        display: block;
        color: #64748b;
        font-size: .61rem;
        font-weight: 700;
    }

    .monthly-overview-meter-list {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 14px;
    }

    .monthly-overview-meter {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 9px;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e40af;
        font-size: .72rem;
        font-weight: 800;
    }

    .monthly-overview-meter-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 8px;
        border-radius: 50%;
        background: #2563eb;
    }

    .monthly-overview-toggle {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: background-color .15s ease, color .15s ease, transform .15s ease;
    }

    .monthly-overview-toggle:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .monthly-overview-toggle i {
        transition: transform .2s ease;
    }

    .monthly-overview-toggle[aria-expanded="false"] i {
        transform: rotate(180deg);
    }

    .monthly-overview-content.is-collapsed {
        display: none;
    }

    @media (max-width: 760px) {
        .monthly-overview-chart {
            height: 260px;
        }
    }

    .monthly-filters-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .monthly-inline-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-inline-filter-label {
        color: #475569;
        font-size: .8rem;
        font-weight: 700;
    }

    .monthly-inline-filter-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .monthly-inline-filter select {
        min-width: 210px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 7px 10px;
        font-size: .88rem;
        color: #1e293b;
        background: #fff;
    }

    .monthly-inline-filter-btn {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 10px;
        padding: 7px 12px;
        font-size: .82rem;
        font-weight: 800;
        cursor: pointer;
    }

    .monthly-record-table-filter {
        padding: 10px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: flex-end;
        gap: 18px;
    }

    .monthly-filter-heading { min-width: 110px; padding-bottom: 7px; }
    .monthly-filter-heading strong { display:flex; align-items:center; gap:7px; color:#334155; font-size:.73rem; }
    .monthly-filter-heading span { display:block; margin-top:3px; color:#94a3b8; font-size:.6rem; }

    .monthly-record-table-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-record-table-filter .monthly-field {
        min-width: 150px;
    }

    .monthly-record-table-filter .monthly-field select {
        min-width: 0;
    }

    .monthly-filter-grid {
        display: grid;
        grid-template-columns: minmax(120px, 180px) minmax(220px, 320px) minmax(160px, 220px) minmax(140px, 180px) max-content;
        gap: 10px;
        align-items: end;
        justify-content: start;
    }

    .monthly-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .monthly-field label {
        color: #475569;
        font-size: .82rem;
        font-weight: 700;
    }

    .monthly-field input,
    .monthly-field select {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 11px;
        font-size: .92rem;
    }

    .monthly-pair-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .monthly-field-actions {
        display: flex;
        justify-content: flex-start;
    }

    .monthly-apply-btn {
        background: #1d4ed8;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        min-height: 42px;
        width: 220px !important;
        min-width: 220px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 900px) {
        .monthly-filter-grid {
            grid-template-columns: 1fr;
        }

        .monthly-field-actions .monthly-apply-btn {
            width: 100%;
        }
    }

    @media (max-width: 560px) {
        .monthly-pair-grid {
            grid-template-columns: 1fr;
        }

        .monthly-inline-filter {
            width: 100%;
        }

        .monthly-inline-filter-controls {
            width: 100%;
        }

        .monthly-inline-filter select {
            min-width: 0;
            width: 100%;
        }

        .monthly-record-table-filter-form {
            width: 100%;
        }

        .monthly-record-table-filter .monthly-field {
            width: 100%;
        }
    }

    .monthly-table-header {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        background: #fcfdff;
    }

    .monthly-table-title {
        color: #1e293b;
        font-weight: 800;
        font-size: 1rem;
    }

    .monthly-period-label { margin-bottom:6px; color:#172554; font-weight:950; }
    .monthly-archive-btn { min-height:42px; display:inline-flex; align-items:center; gap:8px; padding:9px 13px; color:#334155; border:1px solid #cbd5e1; border-radius:10px; background:#f8fafc; text-decoration:none; font-size:.75rem; font-weight:800; transition:transform .15s ease,border-color .15s ease,background .15s ease; }
    .monthly-archive-btn:hover { color:#1d4ed8; border-color:#93c5fd; background:#eff6ff; transform:translateY(-1px); }

    .monthly-table-subtitle {
        color: #64748b;
        font-size: .84rem;
        margin-top: 2px;
    }

    .monthly-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 800;
        padding: 4px 10px;
    }

    /* CPRF Meter Reading Dials Chip */
    .monthly-dial-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 4px 9px;
        border-radius: 7px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        font-size: 0.74rem;
        font-weight: 750;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .monthly-dial-chip i {
        color: #15803d;
        font-size: 0.74rem;
    }
    .monthly-dial-chip .dial-arrow {
        color: #22c55e;
        font-weight: 900;
        font-size: 0.76rem;
    }

    /* CPRF Integration Tag */
    .monthly-cprf-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 6px;
        padding: 2px 7px;
        border-radius: 6px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .monthly-cprf-tag i {
        font-size: 0.65rem;
    }

    .monthly-scope-cell {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .monthly-meter-name {
        font-weight: 750;
        color: #1e293b;
        font-size: 0.85rem;
        line-height: 1.35;
    }

    .monthly-table-wrap {
        overflow-x: auto;
        border-top: 1px solid #dbe4f0;
        border-radius: 0 0 14px 14px;
        background: #ffffff;
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 #eef2f7;
        overscroll-behavior-inline: contain;
    }

    .monthly-table-wrap::-webkit-scrollbar {
        height: 10px;
    }

    .monthly-table-wrap::-webkit-scrollbar-track {
        background: #eef2f7;
    }

    .monthly-table-wrap::-webkit-scrollbar-thumb {
        border: 2px solid #eef2f7;
        border-radius: 999px;
        background: #94a3b8;
    }

    .monthly-table {
        width: 100%;
        min-width: 1380px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    .monthly-table thead tr {
        background: #f8fafc;
    }

    .monthly-table th,
    .monthly-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 12px 14px;
        box-sizing: border-box;
    }

    .monthly-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .035em;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 4;
        white-space: normal;
        line-height: 1.35;
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    .monthly-table td {
        color: #1e293b;
        font-size: .82rem;
        line-height: 1.25;
        vertical-align: middle;
    }

    .monthly-table th:nth-child(1),
    .monthly-table td:nth-child(1) {
        width: 260px;
        min-width: 240px;
        padding-left: 20px;
        text-align: left;
    }

    .monthly-table th:nth-child(2),
    .monthly-table td:nth-child(2) {
        width: 220px;
        min-width: 210px;
        text-align: left;
    }

    .monthly-table th:nth-child(3),
    .monthly-table td:nth-child(3) {
        width: 210px;
        min-width: 200px;
        text-align: left;
    }

    .monthly-table th:nth-child(4),
    .monthly-table td:nth-child(4) {
        width: 180px;
        min-width: 170px;
        text-align: left;
    }

    .monthly-table th:nth-child(5),
    .monthly-table td:nth-child(5) {
        width: 170px;
        min-width: 160px;
        text-align: left;
    }

    .monthly-table th:nth-child(6),
    .monthly-table td:nth-child(6) {
        width: 180px;
        min-width: 170px;
        text-align: left;
    }

    .monthly-table th:nth-child(7),
    .monthly-table td:nth-child(7) {
        width: 140px;
        min-width: 130px;
        text-align: center;
    }

    .monthly-table th:nth-child(12),
    .monthly-table td:nth-child(12) {
        width: 78px;
    }

    .monthly-table tbody tr {
        background: #ffffff;
        transition: background-color .15s ease, box-shadow .15s ease;
    }

    .monthly-summary .meta {
        margin-top: 6px;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
    }

    .monthly-table tbody tr:nth-child(even) {
        background: #f9fbfd;
    }

    .monthly-table tbody tr:hover {
        background: #f1f7ff;
        box-shadow: inset 3px 0 0 #2563eb;
    }

    .monthly-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .scope-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 7px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
        flex: 0 0 auto;
    }

    .monthly-scope-cell {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .monthly-meter-name {
        min-width: 0;
        flex: 1 1 auto;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.35;
    }

    .monthly-number {
        color: #0f172a;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .monthly-muted-number {
        color: #334155;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .monthly-cost {
        color: #047857;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .monthly-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        min-width: 60px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .monthly-review-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .monthly-review-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 86px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 900;
        line-height: 1.15;
        white-space: nowrap;
    }

    .monthly-review-remark {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fff7f7;
        color: #b91c1c;
        font-size: .68rem;
        font-weight: 700;
        line-height: 1.3;
        text-align: left;
        overflow-wrap: anywhere;
        box-sizing: border-box;
    }

    .monthly-bill-thumb {
        display: inline-flex;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dbe4f0;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .08);
    }

    .monthly-bill-thumb img {
        width: 38px;
        height: 38px;
        object-fit: cover;
        display: block;
    }

    .monthly-empty-mark {
        color: #94a3b8;
        font-weight: 700;
    }

    .monthly-pending-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        color: #94a3b8;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .monthly-recommendation-cell {
        display: grid;
        gap: 6px;
        min-width: 0;
        text-align: left;
    }

    .monthly-recommendation-cell.is-action-only {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
    }

    .monthly-recommendation-action-wrap {
        position: relative;
        display: inline-flex;
    }

    .monthly-recommendation-unread {
        position: absolute;
        top: -7px;
        right: -8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #e11d48;
        color: #ffffff;
        font-size: .58rem;
        font-weight: 900;
        line-height: 1;
        box-shadow: 0 3px 8px rgba(225, 29, 72, .28);
        pointer-events: none;
    }

    .monthly-action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
        min-height: 34px;
    }

    .monthly-recommendation-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        flex: 0 0 auto;
        min-height: 28px;
        padding: 5px 9px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        text-decoration: none;
        font-size: .68rem;
        font-weight: 900;
        line-height: 1.15;
        text-align: center;
        white-space: nowrap;
        transition: transform .15s ease, background-color .15s ease, border-color .15s ease;
    }

    .monthly-recommendation-btn:hover {
        transform: translateY(-1px);
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1e40af;
    }

    .monthly-recommendation-btn i {
        font-size: .62rem;
    }

    .monthly-chip.is-success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #166534;
    }

    .monthly-overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }

    .monthly-overview-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        padding: 10px 12px;
    }

    .monthly-overview-item .label {
        color: #64748b;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .monthly-overview-item .value {
        margin-top: 4px;
        color: #0f172a;
        font-size: 1.08rem;
        font-weight: 800;
    }

    .monthly-overview-item .meta {
        margin-top: 5px;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
    }

    .monthly-delete-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fff1f2;
        color: #e11d48;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .15s ease, background-color .15s ease;
    }

    .monthly-delete-btn:hover {
        transform: translateY(-1px);
        background: #ffe4e6;
    }

    .monthly-breakdown-toggle {
        min-height: 30px;
        padding: 5px 9px;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        background: #eff6ff;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        font: inherit;
        font-size: .68rem;
        font-weight: 900;
        white-space: nowrap;
        transition: transform .15s ease, background-color .15s ease, border-color .15s ease;
    }

    .monthly-breakdown-toggle:hover,
    .monthly-breakdown-toggle[aria-expanded="true"] {
        transform: translateY(-1px);
        border-color: #93c5fd;
        background: #dbeafe;
        color: #1e40af;
    }

    .monthly-record-detail-row,
    .monthly-record-detail-row:hover {
        background: #f8fbff !important;
        box-shadow: none !important;
    }

    .monthly-record-detail-row[hidden] {
        display: none;
    }

    .monthly-record-detail-cell {
        padding: 0 16px 18px !important;
        border-bottom: 1px solid #dbe5f2;
    }

    .monthly-record-breakdown {
        padding: 16px;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(37, 99, 235, .08);
    }

    .monthly-record-breakdown-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 13px;
    }

    .monthly-record-breakdown-head strong {
        display: block;
        color: #0f172a;
        font-size: .92rem;
        font-weight: 900;
    }

    .monthly-record-breakdown-head span {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: .75rem;
        font-weight: 700;
    }

    .monthly-record-breakdown-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
    }

    .monthly-record-breakdown-item {
        min-width: 0;
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #f8fafc;
    }

    .monthly-record-breakdown-item span {
        display: block;
        color: #64748b;
        font-size: .65rem;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .monthly-record-breakdown-item strong {
        display: block;
        margin-top: 5px;
        color: #0f172a;
        font-size: .83rem;
        font-weight: 900;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .monthly-record-breakdown-item small {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .monthly-record-breakdown-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .monthly-record-breakdown-actions a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 30px;
        padding: 5px 10px;
        border: 1px solid #dbe5f2;
        border-radius: 9px;
        background: #ffffff;
        color: #334155;
        text-decoration: none;
        font-size: .7rem;
        font-weight: 850;
    }

    .monthly-record-breakdown-actions a:hover {
        border-color: #93c5fd;
        color: #1d4ed8;
    }

    .monthly-breakdown-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .monthly-breakdown-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .monthly-breakdown-head {
        padding: 10px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid #e2e8f0;
        background: #fcfdff;
    }

    .monthly-breakdown-title {
        color: #1e293b;
        font-weight: 800;
        font-size: .95rem;
    }

    .monthly-breakdown-content.is-collapsed {
        display: none;
    }

    .monthly-collapse-btn {
        width: 34px;
        height: 34px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .monthly-breakdown-subtotal {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .monthly-breakdown-subtotal td {
        font-weight: 800;
        color: #0f172a;
    }

    .monthly-breakdown-controls {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-breakdown-control-btn {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 10px;
        min-height: 34px;
        padding: 0 12px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .monthly-org-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .monthly-org-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
    }

    .monthly-org-head {
        width: 100%;
        border: none;
        background: #fcfdff;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
        text-align: left;
    }

    .monthly-org-main {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .monthly-org-main-name {
        color: #1e293b;
        font-size: .95rem;
        font-weight: 800;
    }

    .monthly-org-main-meta {
        color: #64748b;
        font-size: .8rem;
        font-weight: 700;
    }

    .monthly-org-head-right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .monthly-org-arrow {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .monthly-org-content {
        padding: 10px 12px;
        background: #ffffff;
    }

    .monthly-org-content.is-collapsed {
        display: none;
    }

    .monthly-org-sub-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 8px;
    }

    .monthly-org-sub-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        background: #f8fafc;
    }

    .monthly-org-sub-name {
        color: #1e293b;
        font-size: .88rem;
        font-weight: 700;
    }

    .monthly-org-sub-meta {
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
    }

    .monthly-org-sub-link {
        text-decoration: none;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .monthly-org-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 10px 12px;
        color: #64748b;
        font-size: .86rem;
        font-weight: 700;
    }

    .monthly-org-empty-title {
        color: #1e293b;
        font-weight: 900;
        margin-bottom: 4px;
    }

    .monthly-modal-overlay {
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15,23,42,0.6);
        backdrop-filter: blur(4px);
    }

    .monthly-modal-card {
        width: min(520px, 92vw);
        background: #f8fafc;
        border-radius: 16px;
        box-shadow: 0 10px 35px rgba(15,23,42,.25);
        padding: 22px;
        position: relative;
    }

    .monthly-modal-card.record-form {
        width: min(700px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        padding: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #dbe5f2;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15,23,42,.30);
    }

    .monthly-record-modal-header {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 68px 20px 24px;
        background: linear-gradient(135deg,#f8fbff 0%,#eef2ff 100%);
        border-bottom: 1px solid #dbe5f2;
    }

    .monthly-record-modal-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg,#2563eb,#6366f1);
        color: #fff;
        box-shadow: 0 9px 20px rgba(79,70,229,.20);
    }

    .monthly-record-modal-header .monthly-modal-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .monthly-record-modal-header .monthly-modal-subtitle {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .84rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .record-form .monthly-modal-close {
        z-index: 5;
        top: 18px;
        right: 18px;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbe5f2;
        border-radius: 11px;
        background: rgba(255,255,255,.9);
        font-size: 1rem;
    }

    .record-form .monthly-modal-close:hover {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #e11d48;
    }

    .record-form form,
    #addMonthlyRecordForm,
    #addWeeklyRecordForm {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        gap: 14px !important;
        padding: 20px 26px 0;
    }

    .monthly-form-section-title {
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

    .monthly-form-section-title i { color: #2563eb; }

    .record-form .monthly-field label,
    #addMonthlyRecordForm .monthly-field label,
    #addWeeklyRecordForm .monthly-field label {
        color: #334155;
        font-size: .78rem;
        font-weight: 800;
        margin-bottom: 4px;
        display: block;
    }

    .record-form .monthly-field input,
    .record-form .monthly-field select,
    .record-form .monthly-field textarea,
    #addMonthlyRecordForm .monthly-field input,
    #addMonthlyRecordForm .monthly-field select,
    #addWeeklyRecordForm .monthly-field input,
    #addWeeklyRecordForm .monthly-field select,
    #addWeeklyRecordForm .monthly-field textarea {
        min-height: 45px;
        padding: 10px 12px;
        border-radius: 11px;
        background: #fff;
        border: 1px solid #cbd5e1;
        font-family: inherit;
        font-size: .92rem;
        width: 100%;
        box-sizing: border-box;
    }

    .record-form .monthly-field textarea,
    #addWeeklyRecordForm .monthly-field textarea {
        min-height: 65px;
    }

    .record-form .monthly-field input:focus,
    .record-form .monthly-field select:focus,
    .record-form .monthly-field textarea:focus,
    #addMonthlyRecordForm .monthly-field input:focus,
    #addMonthlyRecordForm .monthly-field select:focus,
    #addWeeklyRecordForm .monthly-field input:focus,
    #addWeeklyRecordForm .monthly-field select:focus,
    #addWeeklyRecordForm .monthly-field textarea:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }

    .monthly-meter-suggestion {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 8px 10px;
        border-radius: 9px;
        background: #eff6ff;
        color: #1e40af;
        font-size: .78rem;
        font-weight: 700;
        margin-top: 5px;
    }

    .monthly-computed-field { 
        position: relative; 
    }
    .monthly-computed-field > i {
        position: absolute;
        left: 14px;
        bottom: 14px;
        color: #059669;
        font-size: 0.95rem;
        z-index: 2;
        pointer-events: none;
    }
    .record-form .monthly-field.monthly-computed-field input,
    #addMonthlyRecordForm .monthly-field.monthly-computed-field input,
    #addWeeklyRecordForm .monthly-field.monthly-computed-field input,
    .monthly-computed-field input {
        padding-left: 38px !important;
        background: #ecfdf5 !important;
        border-color: #a7f3d0 !important;
        color: #065f46 !important;
        font-weight: 850 !important;
    }

    #addMonthlyRecordForm input[type="file"] {
        min-height: auto;
        background: #f8fafc;
    }

    .monthly-upload-help {
        color: #64748b;
        font-size: .75rem;
        font-weight: 600;
    }

    .record-form .monthly-modal-actions {
        position: sticky;
        z-index: 4;
        bottom: 0;
        margin: 20px -26px 0;
        padding: 14px 26px;
        border-top: 1px solid #e2e8f0;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(8px);
    }

    .record-form .monthly-modal-btn {
        min-height: 44px;
        border-radius: 11px;
        font-size: .86rem;
    }

    .record-form .monthly-modal-btn.primary {
        order: 2;
        box-shadow: 0 7px 16px rgba(37,99,235,.20);
    }

    .record-form .monthly-modal-btn.neutral { order: 1; }

    .monthly-modal-card.compact {
        width: min(400px, 92vw);
        background: #ffffff;
    }

    .monthly-modal-close {
        position: absolute;
        top: 10px;
        right: 12px;
        font-size: 1.35rem;
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
    }

    .monthly-modal-title {
        margin: 0 0 8px;
        color: #2563eb;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .monthly-modal-title.danger {
        color: #e11d48;
        font-size: 1.2rem;
    }

    .monthly-modal-subtitle {
        font-size: .9rem;
        color: #64748b;
        margin-bottom: 14px;
    }

    .monthly-modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 4px;
    }

    .monthly-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .monthly-reset-btn {
        text-decoration: none;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        min-height: 42px;
        padding: 0 14px;
        font-weight: 700;
        color: #334155;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .monthly-modal-btn {
        flex: 1;
        border: none;
        border-radius: 10px;
        min-height: 42px;
        font-weight: 800;
        cursor: pointer;
    }

    .monthly-modal-btn.primary {
        background: #2563eb;
        color: #fff;
    }
    .monthly-modal-btn.primary:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: none;
        filter: grayscale(0.25);
    }

    .monthly-modal-btn.neutral {
        background: #e2e8f0;
        color: #1e293b;
        font-weight: 700;
    }

    .monthly-modal-btn.danger {
        background: #e11d48;
        color: #fff;
    }

    @media (max-width: 560px) {
        .monthly-modal-card {
            padding: 18px;
        }

        .monthly-modal-actions {
            flex-direction: column-reverse;
        }

        .monthly-modal-card.record-form {
            width: calc(100vw - 12px);
            max-height: calc(100vh - 12px);
            border-radius: 17px;
        }

        .monthly-record-modal-header { padding: 17px 55px 16px 16px; }
        .monthly-record-modal-icon { width: 42px; height: 42px; flex-basis: 42px; border-radius: 12px; }
        #addMonthlyRecordForm { padding: 16px 16px 0; }
        .record-form .monthly-modal-actions {
            margin: 18px -16px 0;
            padding: 12px 16px;
            flex-direction: row;
        }
    }

    @media (max-width: 900px) {
        .monthly-table th:nth-child(2),
        .monthly-table td:nth-child(2) {
            position: static;
            box-shadow: none;
        }
    }

    body.dark-mode .monthly-card {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 14px 28px rgba(2, 6, 23, 0.55);
    }

    body.dark-mode .monthly-report-card-container {
        background: linear-gradient(145deg, #0f172a 0%, #111827 62%, #172033 100%);
        border-color: #334155;
        box-shadow: 0 18px 45px rgba(2, 6, 23, .45);
    }

    body.dark-mode .monthly-table-header {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .monthly-breakdown-block,
    body.dark-mode .monthly-breakdown-head,
    body.dark-mode .monthly-breakdown-subtotal {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .monthly-breakdown-title {
        color: #e2e8f0;
    }

    body.dark-mode .monthly-overview-item {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .monthly-overview-item .label,
    body.dark-mode .monthly-overview-item .meta {
        color: #94a3b8;
    }

    body.dark-mode .monthly-overview-item .value {
        color: #e2e8f0;
    }

    body.dark-mode .monthly-collapse-btn,
    body.dark-mode .monthly-reset-btn,
    body.dark-mode .monthly-breakdown-control-btn {
        background: #111827;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .monthly-org-block {
        border-color: #334155;
        background: #111827;
    }

    body.dark-mode .monthly-org-head {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .monthly-org-main-name {
        color: #e2e8f0;
    }

    body.dark-mode .monthly-org-main-meta,
    body.dark-mode .monthly-org-sub-meta {
        color: #94a3b8;
    }

    body.dark-mode .monthly-org-arrow {
        border-color: #334155;
        background: #0f172a;
        color: #e2e8f0;
    }

    body.dark-mode .monthly-org-content {
        background: #0f172a;
    }

    body.dark-mode .monthly-org-sub-card {
        border-color: #334155;
        background: #111827;
    }

    body.dark-mode .monthly-org-sub-name {
        color: #e2e8f0;
    }

    body.dark-mode .monthly-org-sub-link {
        border-color: #1e3a8a;
        background: #10213f;
        color: #93c5fd;
    }

    body.dark-mode .monthly-org-empty {
        border-color: #334155;
        color: #94a3b8;
    }

    body.dark-mode .monthly-org-empty-title {
        color: #f8fafc;
    }

    body.dark-mode .monthly-table thead tr,
    body.dark-mode .monthly-table tbody tr:nth-child(even),
    body.dark-mode .monthly-table tbody tr:hover {
        background: #111827;
    }

    body.dark-mode .monthly-table th,
    body.dark-mode .monthly-table td {
        border-color: #334155;
        color: #cbd5e1;
    }

    body.dark-mode .monthly-table-wrap,
    body.dark-mode .monthly-table tbody tr {
        background: #0f172a;
    }

    body.dark-mode .monthly-table th {
        background: #111827;
        color: #94a3b8;
        box-shadow: inset 0 -1px 0 #334155;
    }

    body.dark-mode .monthly-table-wrap {
        scrollbar-color: #475569 #111827;
    }

    body.dark-mode .monthly-table-wrap::-webkit-scrollbar-track {
        background: #111827;
    }

    body.dark-mode .monthly-table-wrap::-webkit-scrollbar-thumb {
        border-color: #111827;
        background: #475569;
    }

    body.dark-mode .monthly-table tbody tr:hover {
        background: #10213f;
        box-shadow: inset 3px 0 0 #60a5fa;
    }

    body.dark-mode .monthly-meter-name,
    body.dark-mode .monthly-number {
        color: #f8fafc;
    }

    body.dark-mode .monthly-muted-number {
        color: #cbd5e1;
    }

    body.dark-mode .monthly-cost {
        color: #86efac;
    }

    body.dark-mode .monthly-review-remark {
        border-color: #7f1d1d;
        background: #2b1118;
        color: #fda4af;
    }

    body.dark-mode .monthly-breakdown-toggle {
        border-color: #334b70;
        background: rgba(37, 99, 235, .14);
        color: #bfdbfe;
    }

    body.dark-mode .monthly-record-detail-row,
    body.dark-mode .monthly-record-detail-row:hover {
        background: #0f192a !important;
    }

    body.dark-mode .monthly-record-detail-cell {
        border-color: #334155;
    }

    body.dark-mode .monthly-record-breakdown {
        border-color: #334b70;
        background: #111827;
        box-shadow: none;
    }

    body.dark-mode .monthly-record-breakdown-head strong,
    body.dark-mode .monthly-record-breakdown-item strong {
        color: #e5edf7;
    }

    body.dark-mode .monthly-record-breakdown-head span,
    body.dark-mode .monthly-record-breakdown-item span,
    body.dark-mode .monthly-record-breakdown-item small {
        color: #94a3b8;
    }

    body.dark-mode .monthly-record-breakdown-item {
        border-color: #334155;
        background: #0f172a;
    }

    body.dark-mode .monthly-record-breakdown-actions a {
        border-color: #334155;
        background: #172033;
        color: #cbd5e1;
    }

    body.dark-mode .monthly-bill-thumb {
        border-color: #334155;
        box-shadow: 0 6px 14px rgba(2, 6, 23, .45);
    }

    body.dark-mode .monthly-recommendation-btn {
        border-color: #1d4ed8;
        background: #172554;
        color: #bfdbfe;
    }

    body.dark-mode .monthly-recommendation-btn:hover {
        background: #1e3a8a;
        color: #eff6ff;
    }

    body.dark-mode .monthly-recommendation-unread {
        border-color: #0f172a;
        background: #fb7185;
        color: #4c0519;
    }

    body.dark-mode .monthly-modal-card {
        background: #0f172a;
        color: #e2e8f0;
    }

    body.dark-mode .monthly-modal-card.compact {
        background: #111827;
    }

    body.dark-mode .monthly-modal-card.record-form { background:#0f172a; border-color:#334155; }
    body.dark-mode .monthly-record-modal-header { background:linear-gradient(135deg,#111827,#172033); border-color:#2a3850; }
    body.dark-mode .monthly-record-modal-header .monthly-modal-title { color:#f8fafc; }
    body.dark-mode .record-form .monthly-modal-close { background:#111827; border-color:#334155; color:#cbd5e1; }
    body.dark-mode .monthly-form-section-title { color:#cbd5e1; }
    body.dark-mode .monthly-meter-suggestion { background:#172554; color:#bfdbfe; }
    body.dark-mode .record-form .monthly-field label { color:#cbd5e1; }
    body.dark-mode .record-form .monthly-field input,
    body.dark-mode .record-form .monthly-field select,
    body.dark-mode .record-form .monthly-field textarea { background:#1e293b; border-color:#334155; color:#f1f5f9; }
    body.dark-mode .monthly-computed-field input { background:#052e2b !important; border-color:#047857 !important; color:#a7f3d0 !important; }
    body.dark-mode .record-form .monthly-modal-actions { background:rgba(15,23,42,.97); border-color:#334155; }

    body.dark-mode .monthly-modal-subtitle {
        color: #94a3b8;
    }

    body.dark-mode .monthly-overview-chart {
        border-bottom-color: #475569;
        background:
            repeating-linear-gradient(
                to top,
                transparent 0,
                transparent 59px,
                #1e293b 60px
            );
    }

    body.dark-mode .monthly-overview-bar-value,
    body.dark-mode .monthly-overview-month {
        color: #e2e8f0;
    }

    body.dark-mode .monthly-overview-cost {
        color: #86efac;
    }

    body.dark-mode .monthly-overview-cost span {
        color: #94a3b8;
    }

    body.dark-mode .monthly-overview-bar-column.is-empty .monthly-overview-bar {
        background: #475569;
    }

    body.dark-mode .monthly-overview-insight { background:#111827; border-color:#334155; }
    body.dark-mode .monthly-overview-insight-value { color:#e2e8f0; }

    body.dark-mode .monthly-overview-meter {
        border-color: #1e3a8a;
        background: #172554;
        color: #bfdbfe;
    }

    body.dark-mode .monthly-overview-toggle {
        border-color: #475569;
        background: #111827;
        color: #cbd5e1;
    }

    body.dark-mode .monthly-overview-toggle:hover {
        background: #172554;
        color: #bfdbfe;
    }

    /* Enhanced records workflow and consolidated desktop table */
    .monthly-header-identity { display:flex; align-items:flex-start; gap:14px; }
    .monthly-header-icon { width:48px; height:48px; flex:0 0 48px; display:grid; place-items:center; border-radius:14px; color:#fff; background:linear-gradient(135deg,#2563eb,#6366f1); box-shadow:0 9px 20px rgba(37,99,235,.2); }
    .monthly-header-context { display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
    .monthly-context-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border:1px solid #dbe5f2; border-radius:999px; color:#475569; background:#fff; font-size:.68rem; font-weight:800; }
    .monthly-performance-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .monthly-performance-card { position:relative; overflow:hidden; min-height:108px; padding:16px 17px; border:1px solid #dbe5f2; border-radius:16px; background:#fff; box-shadow:0 8px 20px rgba(15,23,42,.05); }
    .monthly-performance-card::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:var(--monthly-accent,#2563eb); }
    .monthly-performance-top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .monthly-performance-label { color:#64748b; font-size:.69rem; font-weight:850; text-transform:uppercase; letter-spacing:.045em; }
    .monthly-performance-icon { width:33px; height:33px; display:grid; place-items:center; border-radius:10px; color:var(--monthly-accent,#2563eb); background:var(--monthly-soft,#eff6ff); }
    .monthly-performance-value { margin-top:10px; color:#0f172a; font-size:1.42rem; line-height:1; font-weight:950; }
    .monthly-performance-note { margin-top:6px; color:#64748b; font-size:.66rem; font-weight:650; }
    .monthly-performance-card.records { --monthly-accent:#2563eb; --monthly-soft:#eff6ff; }
    .monthly-performance-card.approved { --monthly-accent:#059669; --monthly-soft:#ecfdf5; }
    .monthly-performance-card.pending { --monthly-accent:#f59e0b; --monthly-soft:#fffbeb; }
    .monthly-performance-card.attention { --monthly-accent:#e11d48; --monthly-soft:#fff1f2; }
    .monthly-record-comparison { display:grid; gap:5px; }
    .monthly-record-metric { display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .monthly-record-metric span:first-child { color:#94a3b8; font-size:.61rem; font-weight:800; text-transform:uppercase; }
    .monthly-performance-cell { display:grid; justify-items:start; gap:6px; }
    .monthly-billing-cell { display:grid; gap:5px; }
    .monthly-document-actions { display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; }
    .monthly-bill-link { display:inline-flex; align-items:center; justify-content:center; gap:5px; min-height:32px; padding:6px 9px; border:1px solid #cbd5e1; border-radius:9px; color:#334155; background:#fff; text-decoration:none; font-size:.68rem; font-weight:850; }
    .monthly-bill-link.missing { color:#94a3b8; background:#f8fafc; cursor:default; }
    body.monthly-modal-open { overflow:hidden; }

    @media (min-width: 761px) {
        .monthly-table { width:100%; min-width:1050px; }
        .monthly-table th:nth-child(1), .monthly-table td:nth-child(1) { width:19%; text-align:left; padding-left:16px; }
        .monthly-table th:nth-child(2), .monthly-table td:nth-child(2) { width:15%; text-align:left; }
        .monthly-table th:nth-child(3), .monthly-table td:nth-child(3) { width:18%; text-align:left; }
        .monthly-table th:nth-child(4), .monthly-table td:nth-child(4) { width:14%; text-align:left; }
        .monthly-table th:nth-child(5), .monthly-table td:nth-child(5) { width:14%; text-align:center; }
        .monthly-table th:nth-child(6), .monthly-table td:nth-child(6) { width:13%; text-align:center; }
        .monthly-table th:nth-child(7), .monthly-table td:nth-child(7) { width:7%; text-align:center; }
    }
    body.dark-mode .monthly-context-chip, body.dark-mode .monthly-performance-card, body.dark-mode .monthly-bill-link { background:#111827; border-color:#334155; color:#cbd5e1; }
    body.dark-mode .monthly-performance-value { color:#f1f5f9; }
    body.dark-mode .monthly-table-title { color:#f1f5f9; }
    body.dark-mode .monthly-table-subtitle { color:#8fa0b5; }
    body.dark-mode .monthly-record-table-filter { border-color:#334155; background:#0f192a; }
    body.dark-mode .monthly-filter-heading strong { color:#cbd5e1; }
    body.dark-mode .monthly-filter-heading span { color:#7f91a8; }
    body.dark-mode .monthly-record-table-filter .monthly-field label { color:#aebed0; }
    body.dark-mode .monthly-record-table-filter .monthly-field select { color:#e5edf7; border-color:#334155; background:#111827; }
    body.dark-mode .monthly-record-table-filter .monthly-field select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.14); outline:0; }
    body.dark-mode .monthly-inline-filter-btn { color:#fff; border-color:#2563eb; background:linear-gradient(105deg,#2563eb,#4f46e5); }
    body.dark-mode .monthly-chip { color:#bfdbfe; border-color:#334b70; background:rgba(37,99,235,.13); }
    body.dark-mode .monthly-chip.is-success { color:#a7f3d0; border-color:rgba(52,211,153,.3); background:rgba(5,150,105,.12); }
    body.dark-mode .monthly-archive-btn { color:#cbd5e1; border-color:#475569; background:#172033; }
    body.dark-mode .monthly-archive-btn:hover { color:#bfdbfe; border-color:#3b82f6; background:#172554; }
    body.dark-mode .monthly-period-label { color:#93c5fd; }
    body.dark-mode .monthly-performance-label,
    body.dark-mode .monthly-performance-note { color:#94a3b8; }
    body.dark-mode .monthly-performance-icon { color:var(--monthly-accent,#60a5fa); background:rgba(37,99,235,.12); }
    body.dark-mode .monthly-context-chip { color:#cbd5e1; }
    body.dark-mode .monthly-reset-btn { color:#cbd5e1; }

    :is(html.dark-mode, body.dark-mode) .scope-pill.main {
        background: #1e3a8a !important;
        color: #bfdbfe !important;
        border: 1px solid #3b82f6 !important;
    }
    :is(html.dark-mode, body.dark-mode) .scope-pill.facility {
        background: #052e16 !important;
        color: #86efac !important;
        border: 1px solid #166534 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-cprf-tag {
        background: #042f2e !important;
        border-color: #0d9488 !important;
        color: #5eead4 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-cprf-tag i {
        color: #5eead4 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip {
        background: #052e16 !important;
        border-color: #166534 !important;
        color: #86efac !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip i {
        color: #4ade80 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-dial-chip .dial-arrow {
        color: #86efac !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.change-pill.increased {
        background: #4c0519 !important;
        color: #fda4af !important;
        border: 1px solid #be123c !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.change-pill.decreased {
        background: #1e1b4b !important;
        color: #c7d2fe !important;
        border: 1px solid #4338ca !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.change-pill.no-change {
        background: #1e3a8a !important;
        color: #bfdbfe !important;
        border: 1px solid #3b82f6 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.critical,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.very-high,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.drop-critical,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.drop-high,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.spike {
        background: #4c0519 !important;
        color: #fda4af !important;
        border: 1px solid #be123c !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.warning,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.high,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.drop-warning,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.drop-medium {
        background: #451a03 !important;
        color: #fdba74 !important;
        border: 1px solid #9a3412 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.normal,
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.good {
        background: #052e16 !important;
        color: #86efac !important;
        border: 1px solid #166534 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-status-pill.alert-pill.no-baseline {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border: 1px solid #475569 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.approved {
        background: #052e16 !important;
        color: #86efac !important;
        border: 1px solid #166534 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.for_review,
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.pending,
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.for-review {
        background: #451a03 !important;
        color: #fdba74 !important;
        border: 1px solid #9a3412 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.returned,
    :is(html.dark-mode, body.dark-mode) .monthly-review-pill.rejected {
        background: #4c0519 !important;
        color: #fda4af !important;
        border: 1px solid #be123c !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-recommendation-btn {
        border-color: #3b82f6 !important;
        background: #1e3a8a !important;
        color: #bfdbfe !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-recommendation-btn:hover {
        background: #2563eb !important;
        color: #ffffff !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-breakdown-toggle {
        border-color: #334b70 !important;
        background: #1e293b !important;
        color: #bfdbfe !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-breakdown-toggle:hover {
        background: #2563eb !important;
        color: #ffffff !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-bill-link {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #bfdbfe !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-bill-link.missing {
        color: #94a3b8 !important;
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-bill-link:hover:not(.missing) {
        background: #2563eb !important;
        color: #ffffff !important;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-delete-btn {
        background: #172033;
        border-color: #475569;
        color: #fda4af;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-delete-btn:hover {
        background: #4c0519;
        border-color: #be123c;
        color: #fecdd3;
    }
    :is(html.dark-mode, body.dark-mode) .monthly-pending-mark {
        color: #f59e0b;
        background: #451a03;
        border: 1px solid #9a3412;
    }
    @media (max-width:900px) { .monthly-performance-grid,.monthly-overview-insights { grid-template-columns:repeat(2,minmax(0,1fr)); } .monthly-record-table-filter { align-items:stretch; flex-direction:column; gap:8px; } .monthly-filter-heading { padding-bottom:0; } }
    @media (max-width:760px) {
        .monthly-table-wrap { overflow:visible; padding:10px; border-top:0; }
        .monthly-table, .monthly-table tbody { display:block; width:100%; min-width:0; }
        .monthly-table thead { display:none; }
        .monthly-table tbody { display:grid; gap:11px; }
        .monthly-table tbody tr { display:grid; grid-template-columns:1fr 1fr; overflow:hidden; border:1px solid #dbe5f2; border-radius:13px; background:#fff; }
        .monthly-table tbody td { display:block; width:auto !important; padding:11px 12px !important; border-bottom:1px solid #edf2f7; text-align:left !important; }
        .monthly-table tbody td::before { content:attr(data-label); display:block; margin-bottom:6px; color:#94a3b8; font-size:.59rem; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
        .monthly-table tbody td:first-child, .monthly-table tbody td:nth-child(3), .monthly-table tbody td:nth-child(6), .monthly-table tbody td:last-child { grid-column:1/-1; }
        .monthly-table tbody tr.monthly-record-detail-row { display:block; margin-top:-11px; border-top:0; border-radius:0 0 13px 13px; }
        .monthly-record-detail-row .monthly-record-detail-cell { padding:10px !important; border-bottom:0; }
        .monthly-record-detail-row .monthly-record-detail-cell::before { display:none; }
        .monthly-record-breakdown { padding:13px; }
        .monthly-record-breakdown-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        body.dark-mode .monthly-table tbody tr { background:#111827; border-color:#334155; }
    }
    @media (max-width:600px) { .report-card-container.monthly-report-card-container { padding:13px; border-radius:18px; } .monthly-performance-grid,.monthly-overview-insights,.monthly-record-breakdown-grid { grid-template-columns:1fr; } .monthly-header-identity { align-items:flex-start; } .monthly-header-icon { width:42px; height:42px; flex-basis:42px; } .monthly-record-breakdown-head { flex-direction:column; gap:8px; } .monthly-record-breakdown-actions { justify-content:stretch; } .monthly-record-breakdown-actions a { flex:1 1 auto; justify-content:center; } }

    /* Weekly Breakdown Redesigned Cards */
    .weekly-breakdown-wrap {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 0 0 16px 16px;
    }

    .weekly-explainer-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        color: #1e3a8a;
    }
    .weekly-explainer-icon {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
        font-size: 1.1rem;
    }
    .weekly-explainer-body h4 {
        margin: 0 0 4px;
        font-size: 0.95rem;
        font-weight: 800;
        color: #1e40af;
    }
    .weekly-explainer-body p {
        margin: 0;
        font-size: 0.84rem;
        line-height: 1.45;
        color: #334155;
    }

    .weekly-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 10px 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }
    .weekly-toolbar-info {
        font-size: 0.85rem;
        font-weight: 750;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .weekly-toolbar-info strong {
        color: #1e40af;
    }
    .weekly-toolbar-hint {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
    }
    .weekly-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .weekly-tool-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        font-size: 0.8rem;
        font-weight: 750;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .weekly-tool-btn:hover {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #93c5fd;
        transform: translateY(-1px);
    }

    .weekly-month-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .weekly-month-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .weekly-month-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px 18px;
        background: linear-gradient(135deg, #f8fbff 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
    }
    .weekly-month-header:hover {
        background: linear-gradient(135deg, #f0f7ff 0%, #e8f0fe 100%);
    }
    .weekly-month-card.is-collapsed .weekly-month-header {
        border-bottom: none;
    }
    .weekly-month-card.is-collapsed .weekly-cards-grid {
        display: none;
    }

    .weekly-month-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .weekly-month-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        color: #ffffff;
        border-radius: 9px;
        font-weight: 850;
        font-size: 0.92rem;
        letter-spacing: -0.01em;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }
    .weekly-month-meter {
        font-size: 0.88rem;
        font-weight: 750;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        padding: 5px 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }

    .weekly-month-stats {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .weekly-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 750;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .weekly-stat-chip.kwh {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .weekly-stat-chip.cost {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .weekly-stat-chip.status-approved {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .weekly-stat-chip.status-pending {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }
    .weekly-stat-chip.link {
        background: #ffffff;
        color: #2563eb;
        border-color: #bfdbfe;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .weekly-stat-chip.link:hover {
        background: #2563eb;
        color: #ffffff;
    }

    .weekly-toggle-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 800;
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .weekly-toggle-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .weekly-month-card.is-collapsed .weekly-toggle-btn {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    .weekly-month-card.is-collapsed .weekly-toggle-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    /* 4-Week Grid */
    .weekly-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 14px;
        padding: 16px 18px;
        background: #ffffff;
    }

    .week-tile {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 14px;
        border-radius: 13px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.15s ease;
    }
    .week-tile:hover {
        border-color: #93c5fd;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.09);
        transform: translateY(-2px);
    }

    .week-tile-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .week-tag {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .week-tag-number {
        font-size: 0.96rem;
        font-weight: 900;
        color: #1d4ed8;
    }
    .week-tag-days {
        font-size: 0.76rem;
        color: #64748b;
        font-weight: 700;
    }

    .week-alert-pill {
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .week-alert-pill.alert-normal { background: #dcfce7; color: #166534; border-color: #86efac; }
    .week-alert-pill.alert-warning { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .week-alert-pill.alert-high { background: #ffedd5; color: #9a3412; border-color: #fed7aa; }
    .week-alert-pill.alert-very-high { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
    .week-alert-pill.alert-critical { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .week-alert-pill.alert-drop-warning { background: #cffafe; color: #0e7490; border-color: #a5f3fc; }
    .week-alert-pill.alert-drop-high { background: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
    .week-alert-pill.alert-drop-critical { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .week-alert-pill.alert-no-data { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    .week-tile-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 12px;
        background: #ffffff;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #eef2f7;
    }
    .week-metric-box {
        display: flex;
        flex-direction: column;
    }
    .week-metric-box .box-label {
        font-size: 0.67rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .week-metric-box .box-val {
        font-size: 1.1rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.25;
        margin-top: 2px;
    }
    .week-metric-box .box-val small {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
    }
    .week-metric-box .box-sub {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    .week-tile-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding-top: 10px;
        border-top: 1px solid #edf2f7;
    }
    .week-variance-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.76rem;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .week-variance-pill.normal {
        background: #dcfce7;
        color: #166534;
    }
    .week-variance-pill.higher {
        background: #fee2e2;
        color: #991b1b;
    }
    .week-variance-pill.lower {
        background: #e0e7ff;
        color: #4338ca;
    }

    .week-action-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 7px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 800;
        transition: all 0.15s ease;
    }
    .week-action-link:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    .week-source-tag {
        font-size: 0.68rem;
        font-weight: 750;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 3px;
        width: fit-content;
    }
    .week-source-tag.manual {
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 2px 6px;
        border-radius: 5px;
    }
    .week-source-tag.pending {
        color: #9a3412;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        padding: 2px 6px;
        border-radius: 5px;
    }
    .week-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 8px;
        border-radius: 7px;
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #b91c1c;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.15s ease;
    }
    .week-delete-btn:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }
    .week-log-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 7px;
        background: #2563eb;
        border: 1px solid #2563eb;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .week-log-btn:hover {
        background: #1d4ed8;
    }

    /* Dark mode support */
    body.dark-mode .weekly-breakdown-wrap {
        background: #0b1220;
    }
    body.dark-mode .weekly-toolbar {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .weekly-toolbar-info {
        color: #cbd5e1;
    }
    body.dark-mode .weekly-toolbar-info strong {
        color: #93c5fd;
    }
    body.dark-mode .weekly-toolbar-hint {
        color: #94a3b8;
    }
    body.dark-mode .weekly-tool-btn {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    body.dark-mode .weekly-tool-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    body.dark-mode .weekly-explainer-card {
        background: linear-gradient(135deg, #10213f 0%, #1e1b4b 100%);
        border-color: #1e3a8a;
        color: #cbd5e1;
    }
    body.dark-mode .weekly-explainer-body h4 {
        color: #93c5fd;
    }
    body.dark-mode .weekly-explainer-body p {
        color: #94a3b8;
    }
    body.dark-mode .weekly-month-card {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .weekly-month-header {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .weekly-month-header:hover {
        background: #1e293b;
    }
    body.dark-mode .weekly-toggle-btn {
        background: #1e1b4b;
        color: #c7d2fe;
        border-color: #3730a3;
    }
    body.dark-mode .weekly-toggle-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    body.dark-mode .weekly-month-card.is-collapsed .weekly-toggle-btn {
        background: #1e293b;
        color: #94a3b8;
        border-color: #334155;
    }
    body.dark-mode .weekly-month-card.is-collapsed .weekly-toggle-btn:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    body.dark-mode .weekly-month-meter {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    body.dark-mode .weekly-stat-chip {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    body.dark-mode .weekly-stat-chip.kwh {
        background: #172554;
        border-color: #1e40af;
        color: #93c5fd;
    }
    body.dark-mode .weekly-stat-chip.cost {
        background: #052e16;
        border-color: #166534;
        color: #86efac;
    }
    body.dark-mode .weekly-stat-chip.status-approved {
        background: #064e3b;
        border-color: #059669;
        color: #a7f3d0;
    }
    body.dark-mode .weekly-stat-chip.status-pending {
        background: #431407;
        border-color: #9a3412;
        color: #fed7aa;
    }
    body.dark-mode .weekly-stat-chip.link {
        background: #1e293b;
        border-color: #334155;
        color: #93c5fd;
    }
    body.dark-mode .weekly-cards-grid {
        background: #0f172a;
    }
    body.dark-mode .week-tile {
        background: #111827;
        border-color: #334155;
    }
    body.dark-mode .week-tile:hover {
        background: #1e293b;
        border-color: #3b82f6;
    }
    body.dark-mode .week-tile-head {
        border-color: #334155;
    }
    body.dark-mode .week-tag-number {
        color: #93c5fd;
    }
    body.dark-mode .week-tag-days {
        color: #94a3b8;
    }
    body.dark-mode .week-tile-metrics {
        background: #0b1220;
        border-color: #334155;
    }
    body.dark-mode .week-metric-box .box-label {
        color: #94a3b8;
    }
    body.dark-mode .week-metric-box .box-val {
        color: #f8fafc;
    }
    body.dark-mode .week-metric-box .box-val small,
    body.dark-mode .week-metric-box .box-sub {
        color: #94a3b8;
    }
    body.dark-mode .week-tile-footer {
        border-color: #1e293b;
    }
    body.dark-mode .week-action-link {
        background: #1e293b;
        border-color: #334155;
        color: #93c5fd;
    }
    body.dark-mode .week-action-link:hover {
        background: #2563eb;
        color: #ffffff;
    }
    body.dark-mode .week-source-tag {
        color: #94a3b8;
    }
    body.dark-mode .week-source-tag.manual {
        background: #064e3b;
        color: #a7f3d0;
        border-color: #059669;
    }
    body.dark-mode .week-source-tag.pending {
        background: #431407;
        color: #fed7aa;
        border-color: #9a3412;
    }
    body.dark-mode .week-delete-btn {
        background: #450a0a;
        color: #fca5a5;
        border-color: #991b1b;
    }
    body.dark-mode .week-delete-btn:hover {
        background: #ef4444;
        color: #ffffff;
    }

    /* Record Type Selector Modal Styles */
    .record-type-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin: 18px 0 8px;
    }

    @media (max-width: 640px) {
        .record-type-grid {
            grid-template-columns: 1fr;
        }
    }

    .record-type-card {
        border: 2px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        background: #ffffff;
        text-align: left;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .record-type-card:hover {
        transform: translateY(-3px);
    }

    .record-type-card.is-monthly:hover {
        border-color: #2563eb;
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.15);
    }

    .record-type-card.is-weekly:hover {
        border-color: #4f46e5;
        box-shadow: 0 14px 28px rgba(79, 70, 229, 0.15);
    }

    .record-type-badge-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 14px;
        transition: transform 0.2s ease;
    }

    .record-type-card:hover .record-type-badge-icon {
        transform: scale(1.1);
    }

    .record-type-card.is-monthly .record-type-badge-icon {
        background: #eff6ff;
        color: #2563eb;
    }

    .record-type-card.is-weekly .record-type-badge-icon {
        background: #eef2ff;
        color: #4f46e5;
    }

    .record-type-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .record-type-card.is-monthly .record-type-title {
        color: #1e3a8a;
    }

    .record-type-card.is-weekly .record-type-title {
        color: #312e81;
    }

    .record-type-desc {
        font-size: 0.84rem;
        color: #64748b;
        line-height: 1.45;
        margin-bottom: 14px;
    }

    .record-type-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .record-type-tag {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .record-type-btn {
        margin-top: 14px;
        width: 100%;
        padding: 10px 14px;
        border-radius: 12px;
        border: none;
        font-weight: 800;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .record-type-card.is-monthly .record-type-btn {
        background: #2563eb;
        color: #ffffff;
    }

    .record-type-card.is-monthly:hover .record-type-btn {
        background: #1d4ed8;
    }

    .record-type-card.is-weekly .record-type-btn {
        background: #4f46e5;
        color: #ffffff;
    }

    .record-type-card.is-weekly:hover .record-type-btn {
        background: #4338ca;
    }

    /* Dark mode */
    body.dark-mode .record-type-card {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .record-type-card.is-monthly:hover {
        border-color: #60a5fa;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.4);
    }
    body.dark-mode .record-type-card.is-weekly:hover {
        border-color: #818cf8;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.4);
    }
    body.dark-mode .record-type-card.is-monthly .record-type-title {
        color: #93c5fd;
    }
    body.dark-mode .record-type-card.is-weekly .record-type-title {
        color: #a5b4fc;
    }
    body.dark-mode .record-type-desc {
        color: #94a3b8;
    }
    body.dark-mode .record-type-tags {
        border-top-color: #334155;
    }
    body.dark-mode .record-type-tag {
        background: #0f172a;
        color: #94a3b8;
    }
</style>

@php
    $monthLabels = $monthLabels ?? [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];
    $meterOptions = collect($meterOptions ?? []);
    $hasApprovedMainMeter = $meterOptions->isNotEmpty();
    $totalMainMeterCount = (int) ($totalMainMeterCount ?? $meterOptions->count());
    $approvedMainMeterCount = (int) ($approvedMainMeterCount ?? $meterOptions->count());
    $pendingMainMeterCount = (int) ($pendingMainMeterCount ?? 0);
    $selectedRecordScope = (string) ($selectedRecordScope ?? 'main');
    $scopeLabel = (string) ($scopeLabel ?? 'Main Meter Records');

    $billingSourceLabel = trim((string) ($billingSourceLabel ?? '')) ?: 'Main Meter';
    $primaryBillingMeter = $primaryBillingMeter ?? null;
    $oldMeterId = (string) ($oldMeterId ?? old('meter_id', ''));

    $latestMeterDials = collect($latestMeterDials ?? []);
    $meterDialTimeline = collect($meterDialTimeline ?? []);

    $years = collect($years ?? [date('Y')])->map(fn ($year) => (int) $year)->values();
    if ($years->isEmpty()) {
        $years = collect([(int) date('Y')]);
    }
    $selectedYear = (int) ($selectedYear ?? (int) $years->first());

    $summaryMode = strtolower(trim((string) ($summaryMode ?? 'year')));
    if (! in_array($summaryMode, ['year', 'current', 'month'], true)) {
        $summaryMode = 'year';
    }
    $summaryMonth = (int) ($summaryMonth ?? (int) date('n'));
    if ($summaryMonth < 1 || $summaryMonth > 12) {
        $summaryMonth = (int) date('n');
    }
    $summaryContextLabel = (string) ($summaryContextLabel ?? ('Year Total (' . $selectedYear . ')'));

    $recordsForYear = collect($recordsForYear ?? []);
    $mainRecordIndex = collect($mainRecordIndex ?? []);
    $meterSummaryCards = collect($meterSummaryCards ?? []);
    $monthMeterBreakdown = collect($monthMeterBreakdown ?? []);
    $mainMeterOrganization = collect($mainMeterOrganization ?? []);
    $mainSubMonthlyComparison = collect($mainSubMonthlyComparison ?? []);
    $monthlyOverviewChart = collect(range(1, 12))->map(function ($monthNumber) use ($recordsForYear, $monthLabels) {
        $monthRecords = $recordsForYear
            ->filter(fn ($record) => (int) ($record->month ?? 0) === (int) $monthNumber);
        $monthKwh = round((float) $monthRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
        $monthBaseline = round((float) $monthRecords->sum(function ($record) {
            if ($record->meter && is_numeric($record->meter->baseline_kwh)) {
                return (float) $record->meter->baseline_kwh;
            }
            return is_numeric($record->baseline_kwh) ? (float) $record->baseline_kwh : 0.0;
        }), 2);
        $allApproved = $monthRecords->isNotEmpty() && $monthRecords->every(
            fn ($record) => (string) ($record->review_status ?: 'for_review') === 'approved'
        );
        $deviation = $allApproved && $monthBaseline > 0
            ? round((($monthKwh - $monthBaseline) / $monthBaseline) * 100, 2)
            : null;
        $level = $deviation !== null
            ? \App\Models\EnergyRecord::resolveAlertLevel($deviation, $monthBaseline)
            : ($monthRecords->isNotEmpty() ? 'Pending Review' : 'No Data');

        return [
            'month' => (int) $monthNumber,
            'label' => $monthLabels[(int) $monthNumber] ?? ('Month ' . (int) $monthNumber),
            'kwh' => $monthKwh,
            'baseline_kwh' => $monthBaseline > 0 ? $monthBaseline : null,
            'deviation' => $deviation,
            'alert_level' => $level,
            'cost' => round((float) $monthRecords->sum(fn ($record) => \App\Support\EnergyCost::cost($record)), 2),
            'record_count' => (int) $monthRecords->count(),
        ];
    });
    $monthlyOverviewMaxKwh = max(1, (float) $monthlyOverviewChart->max('kwh'));
    $monthlyOverviewTotalCost = round((float) $monthlyOverviewChart->sum('cost'), 2);
    $monthlyOverviewRecordedMonths = $monthlyOverviewChart->where('record_count', '>', 0)->values();
    $monthlyOverviewCoverage = $monthlyOverviewRecordedMonths->count();
    $monthlyOverviewAverageKwh = $monthlyOverviewCoverage > 0
        ? round((float) $monthlyOverviewRecordedMonths->avg('kwh'), 2)
        : 0.0;
    $monthlyOverviewPeak = $monthlyOverviewRecordedMonths->sortByDesc('kwh')->first();
    $monthlyOverviewAttentionCount = $monthlyOverviewRecordedMonths
        ->filter(fn ($row) => ! in_array((string) ($row['alert_level'] ?? ''), ['Normal', 'No Data', 'Pending Review'], true))
        ->count();

    $mainMeterRecordCount = (int) ($mainMeterRecordCount ?? 0);
    $selectedRecordCount = (int) ($selectedRecordCount ?? $recordsForYear->count());
    $selectedActualKwhTotal = round((float) ($selectedActualKwhTotal ?? 0), 2);
    $selectedCostTotal = round((float) ($selectedCostTotal ?? 0), 2);
    $facilityActualKwhTotal = round((float) ($facilityActualKwhTotal ?? 0), 2);
    $facilityCostTotal = round((float) ($facilityCostTotal ?? 0), 2);
    $overallMainKwh = round((float) ($overallMainKwh ?? 0), 2);
    $overallLinkedSubKwh = round((float) ($overallLinkedSubKwh ?? 0), 2);
    $overallMainMinusSubKwh = round((float) ($overallMainMinusSubKwh ?? 0), 2);

    $tableFilterMonth = (int) request()->query('table_month', 0);
    if ($tableFilterMonth < 1 || $tableFilterMonth > 12) {
        $tableFilterMonth = 0;
    }

    $tableFilterMeterId = (int) request()->query('table_meter_id', 0);
    if ($tableFilterMeterId < 1) {
        $tableFilterMeterId = 0;
    }

    $tableMeterOptions = $meterOptions
        ->map(function ($meter) {
            return [
                'id' => (int) ($meter->id ?? 0),
                'meter_name' => (string) ($meter->meter_name ?? ('Main Meter #' . (int) ($meter->id ?? 0))),
                'meter_number' => (string) ($meter->meter_number ?? ''),
            ];
        })
        ->filter(fn ($row) => (int) ($row['id'] ?? 0) > 0)
        ->sortBy('meter_name')
        ->values();

    if ($tableFilterMeterId > 0 && ! $tableMeterOptions->contains(fn ($row) => (int) ($row['id'] ?? 0) === $tableFilterMeterId)) {
        $tableFilterMeterId = 0;
    }
    if ($tableMeterOptions->count() === 1) {
        $tableFilterMeterId = (int) ($tableMeterOptions->first()['id'] ?? 0);
    }
    $tableMainMeterSelectionRequired = $tableMeterOptions->count() > 1 && $tableFilterMeterId === 0;

    $tableRecords = $recordsForYear
        ->filter(function ($record) use ($tableFilterMonth, $tableFilterMeterId, $tableMainMeterSelectionRequired) {
            if ($tableMainMeterSelectionRequired) {
                return false;
            }
            if ($tableFilterMonth > 0 && (int) ($record->month ?? 0) !== $tableFilterMonth) {
                return false;
            }

            // CPRF facility-level rows (meter_id NULL) aren't tied to any
            // physical meter, so they always show regardless of which
            // specific meter is selected here — hiding them behind a
            // meter filter would mean CPRF-pushed readings are invisible
            // unless "All Main Meters" is explicitly chosen every time.
            $isCprfFacilityLevel = $record->meter_id === null && ($record->input_source ?? null) === 'cprf';
            if (!$isCprfFacilityLevel && $tableFilterMeterId > 0 && (int) ($record->meter_id ?? 0) !== $tableFilterMeterId) {
                return false;
            }

            return true;
        })
        ->values();

    $tableRecordCount = $tableRecords->count();
    $tableActualKwhTotal = round((float) $tableRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
    $tableCostTotal = round((float) $tableRecords->sum(fn ($record) => \App\Support\EnergyCost::cost($record)), 2);
    $tableIncludesCprfFacilityLevel = $tableRecords->contains(
        fn ($record) => $record->meter_id === null && strtolower((string) ($record->input_source ?? '')) === 'cprf'
    );
    $tableFilterApplied = $tableFilterMonth > 0 || $tableFilterMeterId > 0;
    $baselineAlertThresholds = \App\Models\EnergyRecord::alertThresholdsBySize();
    $tableApprovedCount = $tableRecords->filter(fn ($record) => (string) ($record->review_status ?: 'for_review') === 'approved')->count();
    $tablePendingCount = $tableRecords->filter(fn ($record) => (string) ($record->review_status ?: 'for_review') !== 'approved')->count();
    $tableAttentionCount = $tableRecords->filter(function ($record) use ($baselineAlertThresholds) {
        if ((string) ($record->review_status ?: 'for_review') !== 'approved') {
            return false;
        }
        $actual = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
        $baseline = ($record->meter && is_numeric($record->meter->baseline_kwh))
            ? (float) $record->meter->baseline_kwh
            : (is_numeric($record->baseline_kwh) ? (float) $record->baseline_kwh : null);
        $deviation = is_numeric($record->deviation)
            ? (float) $record->deviation
            : (($actual !== null && $baseline !== null && $baseline > 0) ? (($actual - $baseline) / $baseline) * 100 : null);
        if ($deviation === null || $baseline === null || $baseline <= 0) {
            return false;
        }
        $level = \App\Models\EnergyRecord::resolveAlertLevel($deviation, $baseline, $baselineAlertThresholds);
        return ! in_array($level, ['', 'Normal'], true) || ! empty($record->trend_spike_detected);
    })->count();
    $tableCoverageCount = $tableRecords->pluck('month')->filter()->unique()->count();
    $isCprfManaged = method_exists($facility, 'isCprfManaged') && $facility->isCprfManaged();
    $canManageLocalMonthlyRecords = \App\Support\RoleAccess::can(auth()->user(), 'encode_main_meter_readings');

    $tableFilterResetQuery = request()->except(['table_month', 'table_meter_id']);
    $tableFilterResetUrl = request()->url() . (empty($tableFilterResetQuery) ? '' : ('?' . http_build_query($tableFilterResetQuery)));
    if (! $hasApprovedMainMeter) {
        if ($totalMainMeterCount === 0) {
            $mainMeterNoticeTitle = 'No Main Meter configured yet.';
            $mainMeterNoticeText = 'Add a Main Meter in Energy Profile first, then approve it before encoding monthly records.';
        } elseif ($pendingMainMeterCount > 0) {
            $mainMeterNoticeTitle = $pendingMainMeterCount . ' Main Meter pending approval.';
            $mainMeterNoticeText = 'Approve at least one Main Meter in Energy Profile before encoding monthly records.';
        } else {
            $mainMeterNoticeTitle = 'No approved Main Meter found for this facility.';
            $mainMeterNoticeText = 'Check the Main Meter list in Energy Profile and approve an eligible meter first.';
        }
    }

    $filteredWeeklyRows = collect($weeklyMainMeterRows ?? [])
        ->filter(function ($wRow) use ($tableFilterMonth, $tableFilterMeterId, $tableMainMeterSelectionRequired) {
            if ($tableMainMeterSelectionRequired) {
                return false;
            }
            if ($tableFilterMonth > 0 && (int) ($wRow['month'] ?? 0) !== $tableFilterMonth) {
                return false;
            }
            if ($tableFilterMeterId > 0 && (int) ($wRow['meter_id'] ?? 0) !== $tableFilterMeterId) {
                $isCprfFacilityLevel = ($wRow['meter_id'] ?? null) === null;
                if (! $isCprfFacilityLevel) {
                    return false;
                }
            }
            return true;
        })
        ->values();

    $weeklyGroupedByMonth = $filteredWeeklyRows->groupBy(fn ($w) => ((int)($w['year'] ?? 0)) . '-' . str_pad((string)($w['month'] ?? 0), 2, '0', STR_PAD_LEFT));
@endphp

@php
    $requestedRecordDate = trim((string) request('record_date', ''));
    $recordDateDefault = date('Y-m-d');
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $requestedRecordDate, $recordDateParts)
        && checkdate((int) $recordDateParts[2], (int) $recordDateParts[3], (int) $recordDateParts[1])) {
        $recordDateDefault = $requestedRecordDate;
    }

@endphp

<div class="report-card-container monthly-report-card-container monthly-shell">
    @if(session('success'))
        <div class="monthly-alert success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="monthly-alert error">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->has('duplicate'))
        <div class="monthly-alert warn">
            {{ $errors->first('duplicate') }}
        </div>
    @endif
    @if($errors->has('duplicate_week'))
        <div class="monthly-alert warn">
            {{ $errors->first('duplicate_week') }}
        </div>
    @endif

    <div class="monthly-card">
        <div class="monthly-card-body">
            <div class="monthly-header">
                <div class="monthly-header-identity">
                    <span class="monthly-header-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    <div>
                        <h1>Monthly Energy Records</h1>
                        <p>Billing, baseline performance, review status, and supporting documents in one place.</p>
                        <div class="monthly-header-context">
                            <span class="monthly-context-chip"><i class="fa-solid fa-building"></i> {{ $facility->name }}</span>
                            <span class="monthly-context-chip"><i class="fa-solid fa-plug-circle-bolt"></i> {{ $billingSourceLabel }}</span>
                            <span class="monthly-context-chip"><i class="fa-solid fa-calendar"></i> {{ $selectedYear }}</span>
                        </div>
                    </div>
                </div>
                <div class="monthly-actions">
                    <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="monthly-action-btn is-info">
                        <i class="fa fa-bolt"></i> Energy Profile
                    </a>
                    @if(config('features.submeters_enabled', false))
                    <a href="{{ route('facilities.monthly-records.submeters', $facility->id) }}" class="monthly-action-btn" style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;font-weight:700;box-shadow:0 2px 6px rgba(79,70,229,0.3);text-decoration:none;">
                        <i class="fa-solid fa-diagram-project"></i> Sub-meter Records
                    </a>
                    @endif
                    @if($canManageLocalMonthlyRecords)
                    <button type="button" onclick="openRecordTypeModal()" class="monthly-action-btn is-primary" id="openAddRecordTypeBtn">
                        <i class="fa-solid fa-plus"></i> Add Record
                    </button>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <section class="monthly-performance-grid" aria-label="Monthly record performance summary">
        <article class="monthly-performance-card records"><div class="monthly-performance-top"><span class="monthly-performance-label">Filtered records</span><span class="monthly-performance-icon"><i class="fa-solid fa-file-lines"></i></span></div><div class="monthly-performance-value">{{ number_format($tableRecordCount) }}</div><div class="monthly-performance-note">{{ $tableCoverageCount }} of 12 month(s) covered</div></article>
        <article class="monthly-performance-card approved"><div class="monthly-performance-top"><span class="monthly-performance-label">Approved</span><span class="monthly-performance-icon"><i class="fa-solid fa-circle-check"></i></span></div><div class="monthly-performance-value">{{ number_format($tableApprovedCount) }}</div><div class="monthly-performance-note">Included in evaluated performance</div></article>
        <article class="monthly-performance-card pending"><div class="monthly-performance-top"><span class="monthly-performance-label">Pending review</span><span class="monthly-performance-icon"><i class="fa-solid fa-clock"></i></span></div><div class="monthly-performance-value">{{ number_format($tablePendingCount) }}</div><div class="monthly-performance-note">Needs validation before final status</div></article>
        <article class="monthly-performance-card attention"><div class="monthly-performance-top"><span class="monthly-performance-label">Requires attention</span><span class="monthly-performance-icon"><i class="fa-solid fa-triangle-exclamation"></i></span></div><div class="monthly-performance-value">{{ number_format($tableAttentionCount) }}</div><div class="monthly-performance-note">Non-normal variance or trend spike</div></article>
    </section>

    <div class="monthly-card">
        <div class="monthly-card-body">
            <div class="monthly-filters-head">
                <div>
                    <div class="monthly-table-title">Main Meter Overview</div>
                    <div class="monthly-table-subtitle">
                        {{ $approvedMainMeterCount }} approved main meter(s)
                        @if($pendingMainMeterCount > 0)
                            &middot; {{ $pendingMainMeterCount }} pending approval
                        @endif
                        &middot; {{ $summaryContextLabel }}
                    </div>
                </div>
                @if($hasApprovedMainMeter)
                    <div style="display:flex;gap:7px;flex-wrap:wrap;">
                        <span class="monthly-chip">Total Usage: {{ number_format((float) $overallMainKwh, 2) }} kWh</span>
                        <span class="monthly-chip is-success">Total Cost: PHP {{ number_format($monthlyOverviewTotalCost, 2) }}</span>
                        <button type="button"
                                id="monthlyOverviewToggle"
                                class="monthly-overview-toggle"
                                aria-expanded="false"
                                aria-controls="monthlyOverviewContent"
                                title="Expand Main Meter Overview">
                            <i class="fa fa-chevron-up" aria-hidden="true"></i>
                            <span class="sr-only">Expand Main Meter Overview</span>
                        </button>
                    </div>
                @endif
            </div>

            <div id="monthlyOverviewContent" class="monthly-overview-content is-collapsed">
            @if($mainMeterOrganization->isEmpty())
                <div class="monthly-org-empty">
                    <div class="monthly-org-empty-title">{{ $mainMeterNoticeTitle }}</div>
                    <div style="font-size:.86rem;line-height:1.4;">{{ $mainMeterNoticeText }}</div>
                </div>
            @else
                <div class="monthly-overview-insights">
                    <div class="monthly-overview-insight"><i class="fa-solid fa-calendar-check"></i><div><div class="monthly-overview-insight-label">Coverage</div><div class="monthly-overview-insight-value">{{ $monthlyOverviewCoverage }} of 12 months</div></div></div>
                    <div class="monthly-overview-insight"><i class="fa-solid fa-chart-simple"></i><div><div class="monthly-overview-insight-label">Recorded-month average</div><div class="monthly-overview-insight-value">{{ number_format($monthlyOverviewAverageKwh, 2) }} kWh</div></div></div>
                    <div class="monthly-overview-insight"><i class="fa-solid fa-arrow-up-right-dots"></i><div><div class="monthly-overview-insight-label">Highest month</div><div class="monthly-overview-insight-value">{{ $monthlyOverviewPeak['label'] ?? 'No data' }}{{ $monthlyOverviewPeak ? ' · '.number_format((float) $monthlyOverviewPeak['kwh'], 2).' kWh' : '' }}</div></div></div>
                    <div class="monthly-overview-insight"><i class="fa-solid fa-triangle-exclamation"></i><div><div class="monthly-overview-insight-label">Attention months</div><div class="monthly-overview-insight-value">{{ $monthlyOverviewAttentionCount }}</div></div></div>
                </div>
                <div class="monthly-overview-chart-wrap" aria-label="Monthly energy usage chart for {{ $selectedYear }}">
                    <div class="monthly-overview-chart">
                        @foreach($monthlyOverviewChart as $chartMonth)
                            @php
                                $chartKwh = (float) ($chartMonth['kwh'] ?? 0);
                                $chartCost = (float) ($chartMonth['cost'] ?? 0);
                                $chartRecordCount = (int) ($chartMonth['record_count'] ?? 0);
                                $chartHasRecord = $chartRecordCount > 0;
                                $chartLevel = (string) ($chartMonth['alert_level'] ?? 'No Data');
                                $chartLevelClass = \Illuminate\Support\Str::slug($chartLevel);
                                $chartHeight = $chartHasRecord && $chartKwh > 0
                                    ? max(4, round(($chartKwh / $monthlyOverviewMaxKwh) * 100, 2))
                                    : 1;
                                $chartDescription = ($chartMonth['label'] ?? '') . ' ' . $selectedYear
                                    . ': ' . number_format($chartKwh, 2) . ' kWh, PHP '
                                    . number_format($chartCost, 2) . ', '
                                    . number_format($chartRecordCount) . ' record(s), status ' . $chartLevel;
                            @endphp
                            <div class="monthly-overview-bar-column {{ !$chartHasRecord ? 'is-empty' : 'is-'.$chartLevelClass }}"
                                 tabindex="0"
                                 title="{{ $chartDescription }}"
                                 aria-label="{{ $chartDescription }}">
                                <div class="monthly-overview-bar-area">
                                    @if($chartHasRecord)
                                        <div class="monthly-overview-bar-value">{{ number_format($chartKwh, 2) }}</div>
                                        <span class="monthly-overview-alert {{ $chartLevelClass }}">{{ strtoupper($chartLevel) }}</span>
                                    @else
                                        <div class="monthly-overview-no-record">No record</div>
                                    @endif
                                    <div class="monthly-overview-bar" style="height: {{ $chartHeight }}%;"></div>
                                </div>
                                <div class="monthly-overview-month">{{ $chartMonth['label'] }}</div>
                                <div class="monthly-overview-cost">
                                    @if($chartHasRecord)
                                        PHP {{ number_format($chartCost, 2) }}
                                        <span>{{ number_format($chartRecordCount) }} record(s)</span>
                                    @else
                                        <span>No billing data</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="monthly-overview-meter-list">
                    @foreach($mainMeterOrganization as $mainItem)
                        @php
                            $mainSourceLabel = (string) ($mainItem['source_label'] ?? 'No Data');
                        @endphp
                        <div class="monthly-overview-meter">
                            <span class="monthly-overview-meter-dot"></span>
                            {{ $mainItem['main_name'] }}
                            @if($mainItem['main_number'] !== '')
                                ({{ $mainItem['main_number'] }})
                            @endif
                            &middot; {{ $mainSourceLabel }}
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>
    </div>

    <div class="monthly-card">
        <div class="monthly-table-header">
            <div>
                <div class="monthly-table-title">Records Table</div>
                <div class="monthly-table-subtitle">
                    {{ $tableRecordCount }} record(s) for {{ $selectedYear }} under {{ $scopeLabel }}
                    @if($tableFilterApplied)
                        (filtered from {{ $selectedRecordCount }})
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="monthly-chip">
                    Total kWh{{ $tableIncludesCprfFacilityLevel ? ' (including CPRF facility-level records)' : '' }}:
                    {{ number_format($tableActualKwhTotal, 2) }}
                </span>
                <span class="monthly-chip is-success">Total Cost: PHP {{ number_format($tableCostTotal, 2) }}</span>
                @if(config('features.submeters_enabled', false))
                <a href="{{ route('facilities.monthly-records.submeters', $facility->id) }}"
                   class="monthly-archive-btn"
                   style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;"
                   title="View sub-meter monthly records">
                    <i class="fa-solid fa-diagram-project"></i> Sub-meters
                </a>
                @endif
                <a href="{{ route('facilities.monthly-records.archive', $facility->id) }}"
                   class="monthly-archive-btn"
                   title="View archived records">
                    <i class="fa fa-archive"></i> Archive
                </a>
            </div>
        </div>

        <div style="padding: 10px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <span style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-right: 4px;"><i class="fa-solid fa-clock-rotate-left"></i> View Mode:</span>
            <a href="{{ route('facilities.monthly-records', array_merge(request()->except(['timeframe', 'week']), ['facility' => $facility->id, 'timeframe' => 'monthly'])) }}"
               class="monthly-action-btn {{ ($timeframe ?? 'monthly') !== 'weekly' ? 'is-info' : '' }}"
               style="min-height: 36px; padding: 0 14px; font-size: 0.82rem; border-radius: 9px;">
                <i class="fa-solid fa-calendar-days"></i> Monthly Records
            </a>
            <a href="{{ route('facilities.monthly-records', array_merge(request()->except(['timeframe', 'week']), ['facility' => $facility->id, 'timeframe' => 'weekly'])) }}"
               class="monthly-action-btn {{ ($timeframe ?? 'monthly') === 'weekly' ? 'is-info' : '' }}"
               style="min-height: 36px; padding: 0 14px; font-size: 0.82rem; border-radius: 9px;">
                <i class="fa-solid fa-bolt"></i> Weekly Breakdown (Main Meter)
            </a>
        </div>

        <div class="monthly-record-table-filter">
            <div class="monthly-filter-heading">
                <strong><i class="fa-solid fa-filter"></i> Filter records</strong>
                <span>Narrow the table view</span>
            </div>
            <form method="GET" action="{{ route('facilities.monthly-records', $facility->id) }}" class="monthly-record-table-filter-form">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <input type="hidden" name="record_scope" value="{{ $selectedRecordScope }}">
                <input type="hidden" name="summary_mode" value="{{ $summaryMode }}">
                <input type="hidden" name="summary_month" value="{{ $summaryMonth }}">
                <input type="hidden" name="main_sub_scope" value="{{ $mainSubScope }}">
                <input type="hidden" name="timeframe" value="{{ $timeframe ?? 'monthly' }}">

                <div class="monthly-field">
                    <label for="table_month_filter">Month</label>
                    <select id="table_month_filter" name="table_month">
                        <option value="0" @selected($tableFilterMonth === 0)>All Months</option>
                        @foreach($monthLabels as $monthNumber => $monthLabel)
                            <option value="{{ (int) $monthNumber }}" @selected($tableFilterMonth === (int) $monthNumber)>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                </div>

                @if(($timeframe ?? 'monthly') === 'weekly')
                <div class="monthly-field">
                    <label for="table_week_filter">Week</label>
                    <select id="table_week_filter" name="week">
                        <option value="0" @selected(($selectedWeek ?? 0) === 0)>All Weeks (1–4)</option>
                        <option value="1" @selected(($selectedWeek ?? 0) === 1)>Week 1 (Days 1–7)</option>
                        <option value="2" @selected(($selectedWeek ?? 0) === 2)>Week 2 (Days 8–14)</option>
                        <option value="3" @selected(($selectedWeek ?? 0) === 3)>Week 3 (Days 15–21)</option>
                        <option value="4" @selected(($selectedWeek ?? 0) === 4)>Week 4 (Days 22–End)</option>
                    </select>
                </div>
                @endif

                <div class="monthly-field">
                    <label for="table_meter_filter">Main Meter</label>
                    <select id="table_meter_filter" name="table_meter_id" required>
                        @if($tableMeterOptions->count() > 1)
                            <option value="" disabled @selected($tableFilterMeterId === 0)>Select Main Meter</option>
                        @endif
                        @foreach($tableMeterOptions as $meterOption)
                            <option value="{{ (int) ($meterOption['id'] ?? 0) }}" @selected($tableFilterMeterId === (int) ($meterOption['id'] ?? 0))>
                                {{ $meterOption['meter_name'] }}@if(($meterOption['meter_number'] ?? '') !== '') ({{ $meterOption['meter_number'] }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="monthly-filter-actions">
                    <button type="submit" class="monthly-inline-filter-btn">Apply</button>
                    <a href="{{ $tableFilterResetUrl }}" class="monthly-reset-btn">Reset</a>
                </div>
            </form>
        </div>

        @if(($timeframe ?? 'monthly') === 'weekly')
            <div class="weekly-breakdown-wrap">
                <div class="weekly-explainer-card">
                    <div class="weekly-explainer-icon"><i class="fa-solid fa-lightbulb"></i></div>
                    <div class="weekly-explainer-body">
                        <h4>Weekly Main Meter Readings</h4>
                        <p>Track weekly electricity consumption per meter: <strong>Week 1</strong> (Days 1–7), <strong>Week 2</strong> (Days 8–14), <strong>Week 3</strong> (Days 15–21), and <strong>Week 4</strong> (Days 22–End of month). Weekly readings can be logged individually and automatically synchronize to a complete Monthly Record once 4/4 weeks are logged.</p>
                    </div>
                </div>

                @if($weeklyGroupedByMonth->isNotEmpty())
                    <div class="weekly-toolbar">
                        <div class="weekly-toolbar-info">
                            <i class="fa-solid fa-layer-group" style="color: #2563eb;"></i>
                            <span>Grouped by <strong>{{ $weeklyGroupedByMonth->count() }} Month(s)</strong></span>
                            <span class="weekly-toolbar-hint">(Latest month expanded; previous months collapsed)</span>
                        </div>
                        <div class="weekly-toolbar-actions">
                            <button type="button" class="weekly-tool-btn" onclick="toggleAllWeeklyMonths(true)">
                                <i class="fa-solid fa-angles-down"></i> Expand All
                            </button>
                            <button type="button" class="weekly-tool-btn" onclick="toggleAllWeeklyMonths(false)">
                                <i class="fa-solid fa-angles-up"></i> Collapse All
                            </button>
                        </div>
                    </div>
                @endif

                @forelse($weeklyGroupedByMonth as $monthKey => $monthWeeks)
                    @php
                        $firstWeek = $monthWeeks->first();
                        $parentRec = $firstWeek['parent_record'] ?? null;
                        $manualCount = $monthWeeks->where('is_manual', true)->count();
                        $isWeeklyAggregate = $parentRec && $parentRec->input_source === 'weekly_aggregate';
                        $has4Weeks = $manualCount === 4 || $isWeeklyAggregate;

                        $monthTotalKwh = $parentRec ? (float)($parentRec->actual_kwh ?? 0) : $monthWeeks->where('is_manual', true)->sum('actual_kwh');
                        $monthTotalCost = $parentRec ? (float)\App\Support\EnergyCost::cost($parentRec) : $monthWeeks->where('is_manual', true)->sum('cost');
                        $monthStatus = (string)($parentRec?->review_status ?: 'for_review');
                        $isCollapsed = !$loop->first && ($tableFilterMonth === 0);
                    @endphp
                    <div class="weekly-month-card {{ $isCollapsed ? 'is-collapsed' : '' }}" id="weekly-month-{{ $monthKey }}">
                        <div class="weekly-month-header" onclick="toggleWeeklyMonth(this, event)">
                            <div class="weekly-month-heading">
                                <span class="weekly-month-badge">
                                    <i class="fa-solid fa-calendar-days"></i> {{ $firstWeek['month_name'] }} {{ $firstWeek['year'] }}
                                </span>
                                <span class="weekly-month-meter">
                                    <i class="fa-solid fa-gauge-high" style="color:#2563eb;"></i> {{ $firstWeek['meter_name'] }}
                                </span>
                            </div>
                            <div class="weekly-month-stats">
                                @if($has4Weeks)
                                    <span class="weekly-stat-chip status-approved" title="All 4 weeks logged and synchronized to Monthly Record"><i class="fa-solid fa-circle-check"></i> 4/4 Weeks Complete</span>
                                @elseif($manualCount > 0)
                                    <span class="weekly-stat-chip" style="background:#fff7ed;color:#c2410c;border-color:#fed7aa;" title="In Progress: {{ $manualCount }} of 4 weeks logged"><i class="fa-solid fa-hourglass-half"></i> In Progress ({{ $manualCount }}/4 Weeks)</span>
                                @endif

                                @if($parentRec)
                                    <span class="weekly-stat-chip kwh">
                                        Full Month: <strong>{{ number_format($monthTotalKwh, 2) }} kWh</strong>
                                    </span>
                                    <span class="weekly-stat-chip cost">
                                        Total Cost: <strong>PHP {{ number_format($monthTotalCost, 2) }}</strong>
                                    </span>
                                    @if($monthStatus === 'approved')
                                        <span class="weekly-stat-chip status-approved"><i class="fa-solid fa-circle-check"></i> Approved Month</span>
                                    @elseif($monthStatus === 'returned')
                                        <span class="weekly-stat-chip" style="background:#fee2e2;color:#b91c1c;border-color:#fca5a5;"><i class="fa-solid fa-rotate-left"></i> Returned</span>
                                    @else
                                        <span class="weekly-stat-chip status-pending"><i class="fa-solid fa-clock"></i> For Review</span>
                                    @endif
                                    <a href="{{ route('facilities.monthly-records', ['facility' => $facility->id, 'year' => $firstWeek['year'], 'summary_mode' => 'month', 'summary_month' => $firstWeek['month']]) }}"
                                       class="weekly-stat-chip link" title="View full monthly record" onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-file-invoice"></i> Month Record
                                    </a>
                                @else
                                    <span class="weekly-stat-chip kwh" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;">
                                        Logged So Far: <strong>{{ number_format($monthTotalKwh, 2) }} kWh</strong>
                                    </span>
                                    <span class="weekly-stat-chip cost" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;">
                                        Subtotal Cost: <strong>PHP {{ number_format($monthTotalCost, 2) }}</strong>
                                    </span>
                                @endif

                                <button type="button" class="weekly-toggle-btn" aria-label="Toggle weeks" onclick="event.stopPropagation(); toggleWeeklyMonth(this.closest('.weekly-month-card').querySelector('.weekly-month-header'), event)">
                                    <span class="weekly-toggle-text">{{ $isCollapsed ? 'Show 4 Weeks' : 'Collapse' }}</span>
                                    <i class="fa-solid {{ $isCollapsed ? 'fa-chevron-down' : 'fa-chevron-up' }} weekly-toggle-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="weekly-cards-grid">
                            @foreach($monthWeeks as $w)
                                @php
                                    $wAlert = $w['alert_level'] ?? 'Normal';
                                    $wAlertSlug = \Illuminate\Support\Str::slug($wAlert);
                                    $wDev = $w['deviation'];
                                @endphp
                                <div class="week-tile">
                                    <div class="week-tile-head">
                                        <div class="week-tag">
                                            <span class="week-tag-number">{{ $w['week_short'] }}</span>
                                            <span class="week-tag-days">{{ $w['week_label'] }}</span>
                                            @if($w['is_manual'])
                                                <span class="week-source-tag manual"><i class="fa-solid fa-pen-to-square"></i> Manual Reading</span>
                                                @if($w['manual_date'])
                                                    <span style="font-size:0.68rem;color:#64748b;font-weight:600;">{{ $w['manual_date'] }}</span>
                                                @endif
                                            @elseif($w['actual_kwh'] === null)
                                                <span class="week-source-tag pending"><i class="fa-solid fa-clock"></i> Not Logged Yet</span>
                                            @else
                                                <span class="week-source-tag"><i class="fa-solid fa-calculator"></i> Proportioned from Bill</span>
                                            @endif
                                        </div>
                                        <span class="week-alert-pill alert-{{ $wAlertSlug }}">{{ $wAlert }}</span>
                                    </div>

                                    @if($w['actual_kwh'] !== null)
                                    <div class="week-tile-metrics">
                                        <div class="week-metric-box">
                                            <span class="box-label">Consumption</span>
                                            <span class="box-val">{{ number_format((float)$w['actual_kwh'], 2) }} <small>kWh</small></span>
                                            <span class="box-sub">Base: {{ $w['baseline_kwh'] !== null ? number_format((float)$w['baseline_kwh'], 2) : '-' }} kWh</span>
                                        </div>
                                        <div class="week-metric-box">
                                            <span class="box-label">Estimated Cost</span>
                                            <span class="box-val" style="color:#15803d;">PHP {{ number_format((float)$w['cost'], 2) }}</span>
                                            <span class="box-sub">PHP {{ number_format((float)$w['rate'], 2) }}/kWh</span>
                                        </div>
                                    </div>

                                    <div class="week-tile-footer">
                                        <div class="week-variance-pill {{ ($wDev ?? 0) > 0 ? 'higher' : (($wDev ?? 0) < 0 ? 'lower' : 'normal') }}">
                                            @if($wDev !== null)
                                                <i class="fa-solid {{ $wDev > 0 ? 'fa-arrow-trend-up' : ($wDev < 0 ? 'fa-arrow-trend-down' : 'fa-check') }}"></i>
                                                <span>{{ $wDev > 0 ? '+' : '' }}{{ number_format($wDev, 2) }}% vs base</span>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <a href="{{ route('modules.energy-monitoring.index', ['month' => sprintf('%04d-%02d', $w['year'], $w['month']), 'timeframe' => 'weekly', 'week' => $w['week_number']]) }}"
                                                class="week-action-link" title="Open in Energy Monitoring Dashboard">
                                                <i class="fa-solid fa-chart-line"></i> Monitor
                                            </a>
                                            @if($w['is_manual'] && $canManageLocalMonthlyRecords)
                                            <form method="POST" action="{{ route('facility-meter-weekly-readings.destroy', ['facility' => $facility->id, 'reading' => $w['manual_reading_id']]) }}" onsubmit="return confirm('Delete this weekly reading for {{ $w['week_short'] }}?');" style="display:inline;margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="week-delete-btn" title="Delete weekly reading">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    @else
                                    <div class="week-tile-metrics" style="background:#f8fafc;border-style:dashed;">
                                        <div class="week-metric-box" style="grid-column: span 2; text-align: center; padding: 4px 0;">
                                            <span class="box-label">Status</span>
                                            <span class="box-val" style="font-size:0.92rem;color:#94a3b8;font-weight:700;">No reading logged yet</span>
                                            <span class="box-sub">Base: {{ $w['baseline_kwh'] !== null ? number_format((float)$w['baseline_kwh'], 2) : '-' }} kWh</span>
                                        </div>
                                    </div>

                                    <div class="week-tile-footer">
                                        <span style="font-size:0.75rem;color:#94a3b8;font-weight:600;">Week {{ $w['week_number'] }} pending</span>
                                        @if($canManageLocalMonthlyRecords)
                                        <button type="button" class="week-log-btn" onclick="openAddWeeklyModal({{ (int)$w['meter_id'] }}, {{ (int)$w['year'] }}, {{ (int)$w['month'] }}, {{ (int)$w['week_number'] }})">
                                            <i class="fa-solid fa-plus"></i> Log Week
                                        </button>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div style="padding: 36px; text-align: center; background: #ffffff; border-radius: 14px; border: 1px dashed #cbd5e1; color: #64748b; font-weight: 700;">
                        <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; color: #94a3b8; margin-bottom: 10px; display: block;"></i>
                        No weekly records found for the selected year or filters.
                    </div>
                @endforelse
            </div>
        @else
            <div class="monthly-table-wrap">
                <table class="monthly-table">
                    <thead>
                        <tr>
                            <th>Period / Main Meter</th>
                            <th>Consumption</th>
                            <th>Performance</th>
                            <th>Billing</th>
                            <th>Review Status</th>
                            <th>Documents / Insight</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($tableRecords as $record)
                        @php
                            $rate = \App\Support\EnergyCost::ratePerKwh($record);
                            $cost = \App\Support\EnergyCost::cost($record, $rate);

                            $isCprfFacilityLevel = $record->meter_id === null && ($record->input_source ?? null) === 'cprf';
                            $scopeLabelRow = $isCprfFacilityLevel ? 'FACILITY' : 'MAIN';
                            $scopeNameRow = $isCprfFacilityLevel
                                ? 'Facility-Level'
                                : (string) ($record->meter->meter_name ?? 'Main Meter');
                            $scopeBg = $isCprfFacilityLevel ? '#ecfdf5' : '#eff6ff';
                            $scopeColor = $isCprfFacilityLevel ? '#047857' : '#1d4ed8';

                            $actualRow = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
                            $baselineRow = ($record->meter && is_numeric($record->meter->baseline_kwh))
                                ? (float) $record->meter->baseline_kwh
                                : (is_numeric($record->baseline_kwh) ? (float) $record->baseline_kwh : null);

                            $deviationRow = null;
                            if (is_numeric($record->deviation)) {
                                $deviationRow = (float) $record->deviation;
                            } elseif ($actualRow !== null && $baselineRow !== null && $baselineRow > 0) {
                                $deviationRow = (($actualRow - $baselineRow) / $baselineRow) * 100;
                            }

                            $changeLabel = '-';
                            $changeBg = '#f1f5f9';
                            $changeColor = '#475569';
                            if ($deviationRow !== null) {
                                if ($deviationRow > 0.0001) {
                                    $changeLabel = 'Increased ' . number_format($deviationRow, 2) . '%';
                                    $changeBg = '#fee2e2';
                                    $changeColor = '#991b1b';
                                } elseif ($deviationRow < -0.0001) {
                                    $changeLabel = 'Decreased ' . number_format(abs($deviationRow), 2) . '%';
                                    $changeBg = '#e0e7ff';
                                    $changeColor = '#4338ca';
                                } else {
                                    $changeLabel = 'No Change';
                                    $changeBg = '#eff6ff';
                                    $changeColor = '#1d4ed8';
                                }
                            }

                            $baselineAlertLabel = 'No baseline';
                            $baselineAlertBg = '#f1f5f9';
                            $baselineAlertColor = '#475569';
                            if ($deviationRow !== null && $baselineRow !== null && $baselineRow > 0) {
                                $baselineAlertLabel = \App\Models\EnergyRecord::resolveAlertLevel($deviationRow, $baselineRow, $baselineAlertThresholds);
                                $alertThemes = [
                                    'Critical' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                                    'Very High' => ['bg' => '#fff1f2', 'color' => '#be123c'],
                                    'High' => ['bg' => '#ffedd5', 'color' => '#9a3412'],
                                    'Warning' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                                    'Drop Critical' => ['bg' => '#ede9fe', 'color' => '#6d28d9'],
                                    'Drop High' => ['bg' => '#e0e7ff', 'color' => '#4338ca'],
                                    'Drop Warning' => ['bg' => '#cffafe', 'color' => '#0e7490'],
                                    'Normal' => ['bg' => '#dcfce7', 'color' => '#166534'],
                                ];
                                $alertTheme = $alertThemes[$baselineAlertLabel] ?? $alertThemes['Normal'];
                                $baselineAlertBg = $alertTheme['bg'];
                                $baselineAlertColor = $alertTheme['color'];
                            }

                            $billPath = ltrim((string) ($record->bill_image ?? ''), '/');
                            if (str_starts_with($billPath, 'http://') || str_starts_with($billPath, 'https://')) {
                                $billImageUrl = $billPath;
                            } elseif (str_starts_with($billPath, 'uploads/')) {
                                $billImageUrl = asset($billPath);
                            } elseif (str_starts_with($billPath, 'storage/')) {
                                $billPath = substr($billPath, strlen('storage/'));
                                $billImageUrl = ($billPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($billPath))
                                    ? asset('storage/' . $billPath)
                                    : null;
                            } else {
                                $billImageUrl = ($billPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($billPath))
                                    ? asset('storage/' . $billPath)
                                    : null;
                            }

                            $recommendationNotification = $recommendationNotificationsByRecordId->get((int) $record->id);
                            $hasUnreadRecommendation = $recommendationNotification && $recommendationNotification->read_at === null;
                            $recommendationRouteParameters = [
                                'feature' => 'energy-saving-tips',
                                'facility_id' => $facility->id,
                                'record_id' => $record->id,
                                'month' => sprintf(
                                    '%04d-%02d',
                                    (int) ($record->year ?? $selectedYear),
                                    (int) ($record->month ?? 1)
                                ),
                            ];
                            if ($recommendationNotification) {
                                $recommendationRouteParameters['recommendation_notification_id'] = $recommendationNotification->id;
                            }
                            $recommendationUrl = route('modules.energy-conservation.feature', $recommendationRouteParameters);

                            $reviewStatus = (string) ($record->review_status ?: 'for_review');
                            $reviewThemes = [
                                'for_review' => ['label' => 'For Review', 'bg' => '#fff7ed', 'color' => '#c2410c'],
                                'approved' => ['label' => 'Approved', 'bg' => '#dcfce7', 'color' => '#166534'],
                                'returned' => ['label' => 'Returned', 'bg' => '#fee2e2', 'color' => '#b91c1c'],
                            ];
                            $reviewTheme = $reviewThemes[$reviewStatus] ?? $reviewThemes['for_review'];

                            $recordYear = (int) ($record->year ?? $selectedYear);
                            $recordMonth = (int) ($record->month ?? 1);
                            $previousMonth = $recordMonth === 1 ? 12 : $recordMonth - 1;
                            $previousYear = $recordMonth === 1 ? $recordYear - 1 : $recordYear;
                            $previousRecordKey = (int) ($record->meter_id ?? 0) . '-' . $previousYear . '-' . $previousMonth;
                            $previousRecord = $record->meter_id ? $mainRecordIndex->get($previousRecordKey) : null;
                            $previousActual = $previousRecord && is_numeric($previousRecord->actual_kwh)
                                ? (float) $previousRecord->actual_kwh
                                : null;
                            $previousChange = $previousActual !== null && $previousActual > 0 && $actualRow !== null
                                ? (($actualRow - $previousActual) / $previousActual) * 100
                                : null;
                            $varianceKwh = $actualRow !== null && $baselineRow !== null
                                ? $actualRow - $baselineRow
                                : null;
                            $sourceKey = strtolower(trim((string) ($record->input_source ?? 'manual')));
                            $sourceLabel = match ($sourceKey) {
                                'cprf' => 'CPRF synchronization',
                                'iot', 'sensor' => 'IoT sensor',
                                default => 'Manual bill entry',
                            };
                            $billingPeriodLabel = ($monthLabels[$recordMonth] ?? ('Month ' . $recordMonth))
                                . ($record->day ? ' ' . (int) $record->day : '')
                                . ', ' . $recordYear;
                            $recordedByLabel = trim((string) ($record->recorded_by_name ?? '')) ?: match ($sourceKey) {
                                'cprf' => 'CPRF integration',
                                default => 'System user',
                            };
                            $reviewedAtLabel = $record->reviewed_at
                                ? $record->reviewed_at->format('M j, Y g:i A')
                                : 'Not reviewed yet';
                        @endphp
                        <tr>
                            <td data-label="Period / Main Meter">
                                <div class="monthly-period-label">{{ $monthLabels[(int) ($record->month ?? 0)] ?? $record->month }} {{ (int) ($record->year ?? $selectedYear) }}</div>
                                <div class="monthly-scope-cell">
                                    <span class="scope-pill {{ $isCprfFacilityLevel ? 'facility' : 'main' }}" style="background:{{ $scopeBg }};color:{{ $scopeColor }};">{{ $scopeLabelRow }}</span>
                                    <span class="monthly-meter-name">{{ $scopeNameRow }}</span>
                                    @if($sourceKey === 'cprf' || str_contains(strtolower((string)($record->external_source ?? '')), 'cprf'))
                                        <span class="monthly-cprf-tag" title="Imported from CPRF meter reading integration"><i class="fa-solid fa-cloud-arrow-down"></i> CPRF</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Consumption">
                                <div class="monthly-record-comparison">
                                    <div class="monthly-record-metric"><span>Actual</span><strong class="monthly-number">{{ $record->actual_kwh !== null ? number_format((float) $record->actual_kwh, 2).' kWh' : '-' }}</strong></div>
                                    <div class="monthly-record-metric"><span>Baseline</span><strong class="monthly-muted-number">{{ $baselineRow !== null ? number_format($baselineRow, 2).' kWh' : '-' }}</strong></div>
                                </div>
                                @if($record->previous_reading_kwh !== null && $record->current_reading_kwh !== null)
                                    <div class="monthly-dial-chip" title="Meter Dial: Previous {{ number_format((float) $record->previous_reading_kwh, 2) }} kWh &rarr; Current {{ number_format((float) $record->current_reading_kwh, 2) }} kWh (Net Consumption: +{{ number_format((float) $record->actual_kwh, 2) }} kWh)">
                                        <i class="fa-solid fa-gauge-high"></i>
                                        <span>{{ number_format((float) $record->previous_reading_kwh, 0) }}</span>
                                        <span class="dial-arrow">&rarr;</span>
                                        <span>{{ number_format((float) $record->current_reading_kwh, 0) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td data-label="Performance"><div class="monthly-performance-cell">
                                @if($reviewStatus === 'approved')
                                    @php
                                        $changeClass = $deviationRow > 0.0001 ? 'increased' : ($deviationRow < -0.0001 ? 'decreased' : 'no-change');
                                        $alertLevelSlug = strtolower(str_replace(' ', '-', (string) $baselineAlertLabel));
                                    @endphp
                                    <span class="monthly-status-pill change-pill {{ $changeClass }}" style="background:{{ $changeBg }};color:{{ $changeColor }};">
                                        {{ $changeLabel }}
                                    </span>
                                    <span class="monthly-status-pill alert-pill {{ $alertLevelSlug }}" style="background:{{ $baselineAlertBg }};color:{{ $baselineAlertColor }};">
                                        {{ $baselineAlertLabel }}
                                    </span>
                                    @if(!empty($record->trend_spike_detected))
                                        <div style="margin-top:6px;">
                                            <span class="monthly-status-pill alert-pill spike" style="background:#fee2e2;color:#991b1b;">
                                                3-Month Spike
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <span class="monthly-pending-mark" title="Status will be shown after this record is approved.">
                                        <i class="fa-solid fa-clock"></i> Pending review
                                    </span>
                                @endif
                            </div></td>
                            <td data-label="Billing"><div class="monthly-billing-cell"><div class="monthly-record-metric"><span>Rate</span><strong class="monthly-muted-number">PHP {{ number_format($rate, 2) }}/kWh</strong></div><div class="monthly-record-metric"><span>Cost</span><strong class="monthly-cost">PHP {{ number_format($cost, 2) }}</strong></div></div></td>
                            <td data-label="Review Status">
                                <div class="monthly-review-cell">
                                    <span class="monthly-review-pill {{ $reviewStatus }}" style="background:{{ $reviewTheme['bg'] }};color:{{ $reviewTheme['color'] }};"><i class="fa-solid {{ $reviewStatus === 'approved' ? 'fa-circle-check' : ($reviewStatus === 'returned' ? 'fa-rotate-left' : 'fa-clock') }}"></i>{{ $reviewTheme['label'] }}</span>
                                    @if($record->review_remarks)<div class="monthly-review-remark" title="{{ $record->review_remarks }}">{{ \Illuminate\Support\Str::limit($record->review_remarks, 55) }}</div>@endif
                                </div>
                            </td>
                            <td data-label="Documents / Insight">
                                <div class="monthly-document-actions"><div class="monthly-recommendation-cell is-action-only">
                                    <span class="monthly-recommendation-action-wrap">
                                        <a href="{{ $recommendationUrl }}"
                                           class="monthly-recommendation-btn"
                                           title="View recommendation">
                                            <span>Insight</span>
                                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                        @if($hasUnreadRecommendation)
                                            <span class="monthly-recommendation-unread"
                                                  title="1 unread recommendation"
                                                  aria-label="1 unread recommendation">1</span>
                                        @endif
                                    </span>
                                </div>
                                @if($billImageUrl)
                                    <a href="{{ $billImageUrl }}" target="_blank" rel="noopener" class="monthly-bill-link"><i class="fa-solid fa-camera"></i> Reading Photo</a>
                                @else
                                    <span class="monthly-bill-link missing"><i class="fa-solid fa-camera"></i> No photo</span>
                                @endif
                                </div>
                            </td>
                            <td data-label="Actions">
                                <div class="monthly-action-group">
                                <button type="button"
                                        class="monthly-breakdown-toggle"
                                        data-record-breakdown="monthly-record-breakdown-{{ $record->id }}"
                                        aria-expanded="false"
                                        aria-controls="monthly-record-breakdown-{{ $record->id }}"
                                        title="View monthly record breakdown">
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                    <span>Details</span>
                                </button>
                                @if($canManageLocalMonthlyRecords && strtolower((string) ($record->input_source ?? 'manual')) !== 'cprf')
                                <form id="deleteMonthlyRecordForm-{{ $record->id }}"
                                      action="{{ route('energy-records.delete', ['facility' => $facility->id, 'record' => $record->id]) }}"
                                      method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="archive_reason" value="">
                                    <button type="button"
                                            title="Move to Archive"
                                            class="monthly-delete-btn"
                                            onclick="openDeleteMonthlyRecordModal({{ $record->id }}, @js($monthLabels[(int) ($record->month ?? 0)] ?? ''), {{ (int) $record->year }})">
                                        <i class="fa fa-box-archive"></i>
                                    </button>
                                </form>
                                @else
                                    <span class="monthly-empty-mark">-</span>
                                @endif
                                </div>
                            </td>
                        </tr>
                        <tr id="monthly-record-breakdown-{{ $record->id }}" class="monthly-record-detail-row" hidden>
                            <td colspan="7" class="monthly-record-detail-cell">
                                <section class="monthly-record-breakdown" aria-label="{{ $billingPeriodLabel }} monthly record breakdown">
                                    <div class="monthly-record-breakdown-head">
                                        <div>
                                            <strong>{{ $billingPeriodLabel }} breakdown</strong>
                                            <span>{{ $scopeNameRow }} &middot; {{ $sourceLabel }}</span>
                                        </div>
                                        <span class="monthly-review-pill {{ $reviewStatus }}" style="background:{{ $reviewTheme['bg'] }};color:{{ $reviewTheme['color'] }};">
                                            <i class="fa-solid {{ $reviewStatus === 'approved' ? 'fa-circle-check' : ($reviewStatus === 'returned' ? 'fa-rotate-left' : 'fa-clock') }}"></i>
                                            {{ $reviewTheme['label'] }}
                                        </span>
                                    </div>

                                    <div class="monthly-record-breakdown-grid">
                                        <div class="monthly-record-breakdown-item">
                                            <span>Total consumption</span>
                                            <strong>{{ $actualRow !== null ? number_format($actualRow, 2) . ' kWh' : 'No reading' }}</strong>
                                            <small>Recorded usage for this billing period</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Baseline / target</span>
                                            <strong>{{ $baselineRow !== null ? number_format($baselineRow, 2) . ' kWh' : 'Not configured' }}</strong>
                                            <small>Reference used by alerts and performance checks</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Variance from baseline</span>
                                            <strong>
                                                @if($varianceKwh !== null && $deviationRow !== null)
                                                    {{ $varianceKwh >= 0 ? '+' : '' }}{{ number_format($varianceKwh, 2) }} kWh
                                                @else
                                                    Not available
                                                @endif
                                            </strong>
                                            <small>{{ $deviationRow !== null ? (($deviationRow >= 0 ? '+' : '') . number_format($deviationRow, 2) . '% vs baseline') : 'Add a baseline to calculate variance' }}</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Previous month</span>
                                            <strong>{{ $previousActual !== null ? number_format($previousActual, 2) . ' kWh' : 'No prior record' }}</strong>
                                            <small>{{ $previousChange !== null ? (($previousChange >= 0 ? '+' : '') . number_format($previousChange, 2) . '% month over month') : 'Comparison unavailable' }}</small>
                                        </div>
                                        @if($record->previous_reading_kwh !== null && $record->current_reading_kwh !== null)
                                        <div class="monthly-record-breakdown-item" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px;">
                                            <span style="color:#166534; font-weight:750; font-size:0.75rem;"><i class="fa-solid fa-gauge-high"></i> Meter Dial Readings</span>
                                            <strong style="color:#15803d; font-size:0.96rem; margin-top:3px;">{{ number_format((float) $record->previous_reading_kwh, 2) }} &rarr; {{ number_format((float) $record->current_reading_kwh, 2) }} kWh</strong>
                                            <small style="color:#166534; margin-top:2px;">Dial computation: {{ number_format((float) $record->current_reading_kwh, 2) }} (Current) &minus; {{ number_format((float) $record->previous_reading_kwh, 2) }} (Previous) = {{ number_format((float) $record->actual_kwh, 2) }} kWh consumed</small>
                                        </div>
                                        @endif
                                        <div class="monthly-record-breakdown-item">
                                            <span>Recorded cost</span>
                                            <strong>PHP {{ number_format($cost, 2) }}</strong>
                                            <small>{{ number_format($actualRow ?? 0, 2) }} kWh &times; PHP {{ number_format($rate, 2) }}/kWh</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Data source</span>
                                            <strong>{{ $sourceLabel }}</strong>
                                            <small>Recorded by {{ $recordedByLabel }}</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Billing period</span>
                                            <strong>{{ $billingPeriodLabel }}</strong>
                                            <small>{{ $billImageUrl ? 'Meter reading photo attached' : 'No reading photo attached' }}</small>
                                        </div>
                                        <div class="monthly-record-breakdown-item">
                                            <span>Verification</span>
                                            <strong>{{ $reviewTheme['label'] }}</strong>
                                            <small>{{ $reviewedAtLabel }}</small>
                                        </div>
                                    </div>

                                    <div class="monthly-record-breakdown-actions">
                                        <a href="{{ $recommendationUrl }}"><i class="fa-solid fa-wand-magic-sparkles"></i> View energy insight</a>
                                        @if($billImageUrl)
                                            <a href="{{ $billImageUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-camera"></i> View reading photo</a>
                                        @endif
                                    </div>
                                </section>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:28px;color:#64748b;font-weight:700;text-align:center;">
                                @if($tableMainMeterSelectionRequired)
                                    Select a Main Meter first to view its monthly records.
                                @elseif($tableFilterApplied)
                                    No records found for the selected table filters.
                                @else
                                    No records found for the selected scope and year.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManageLocalMonthlyRecords)
<div id="recordTypeModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card" style="max-width: 660px;" role="dialog" aria-modal="true" aria-labelledby="recordTypeModalTitle">
        <button type="button" onclick="closeRecordTypeModal()" class="monthly-modal-close" aria-label="Close record type selector"><i class="fa-solid fa-xmark"></i></button>
        <header class="monthly-record-modal-header" style="margin-bottom: 6px;">
            <div class="monthly-record-modal-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-layer-group"></i></div>
            <div>
                <h2 id="recordTypeModalTitle" class="monthly-modal-title">Choose Record Type</h2>
                <div class="monthly-modal-subtitle">
                    Select how you would like to log energy usage for this facility.
                </div>
            </div>
        </header>

        <div class="record-type-grid">
            <!-- Option 1: Monthly Electricity Bill -->
            <div class="record-type-card is-monthly" onclick="selectRecordType('monthly')">
                <div>
                    <div class="record-type-badge-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div class="record-type-title">
                        <span>Monthly Record</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:0.85rem;opacity:0.7;"></i>
                    </div>
                    <div class="record-type-desc">
                        Encode official monthly kilowatt-hours and billing statement from your utility provider (e.g. Meralco).
                    </div>
                </div>
                <div>
                    <div class="record-type-tags">
                        <span class="record-type-tag"><i class="fa-solid fa-receipt"></i> Official Bill</span>
                        <span class="record-type-tag"><i class="fa-solid fa-paperclip"></i> Image Upload</span>
                        <span class="record-type-tag"><i class="fa-solid fa-check-double"></i> Full Month</span>
                    </div>
                    <button type="button" class="record-type-btn">
                        <i class="fa-solid fa-plus"></i> Encode Monthly Bill
                    </button>
                </div>
            </div>

            <!-- Option 2: Weekly Meter Reading -->
            <div class="record-type-card is-weekly" onclick="selectRecordType('weekly')">
                <div>
                    <div class="record-type-badge-icon"><i class="fa-solid fa-calendar-week"></i></div>
                    <div class="record-type-title">
                        <span>Weekly Reading</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:0.85rem;opacity:0.7;"></i>
                    </div>
                    <div class="record-type-desc">
                        Log weekly dial or meter readings (Week 1–4). Automatically creates the official monthly total once all 4 weeks are complete.
                    </div>
                </div>
                <div>
                    <div class="record-type-tags">
                        <span class="record-type-tag"><i class="fa-solid fa-gauge"></i> Week 1–4</span>
                        <span class="record-type-tag"><i class="fa-solid fa-calculator"></i> Auto-Sums at 4/4</span>
                        <span class="record-type-tag"><i class="fa-solid fa-clock-rotate-left"></i> Dial Tracking</span>
                    </div>
                    <button type="button" class="record-type-btn">
                        <i class="fa-solid fa-plus"></i> Encode Weekly Reading
                    </button>
                </div>
            </div>
        </div>

        <div class="monthly-modal-actions" style="margin-top: 10px; justify-content: flex-end;">
            <button type="button" onclick="closeRecordTypeModal()" class="monthly-modal-btn neutral">Cancel</button>
        </div>
    </div>
</div>

<div id="addModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card record-form" role="dialog" aria-modal="true" aria-labelledby="addMonthlyRecordTitle" aria-describedby="addMonthlyRecordSubtitle">
        <button type="button" onclick="closeAddModal()" class="monthly-modal-close" aria-label="Close add monthly record form"><i class="fa-solid fa-xmark"></i></button>
        <header class="monthly-record-modal-header">
            <div class="monthly-record-modal-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            <div>
                <h2 id="addMonthlyRecordTitle" class="monthly-modal-title">Add Monthly Record</h2>
                <div id="addMonthlyRecordSubtitle" class="monthly-modal-subtitle">
                    Encode usage from the <strong>{{ $billingSourceLabel }}</strong> bill. Energy cost is calculated automatically.
                </div>
            </div>
        </header>

        <form id="addMonthlyRecordForm" method="POST" action="{{ route('energy-records.store', ['facility' => $facility->id]) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;">
            @csrf

            <div class="monthly-form-section-title"><i class="fa-solid fa-receipt"></i> Billing Information</div>
            <div class="monthly-field">
                <label for="add_date">Billing Date <span style="color:#e11d48;">*</span></label>
                <input type="date" id="add_date" name="date" value="{{ old('date', $recordDateDefault) }}" required onchange="updateMonthlyPreviousReading(); syncAddSaveButtonState();">
            </div>

            <div class="monthly-field">
                <label for="add_meter_id">Main Meter <span style="color:#e11d48;">*</span></label>
                <select id="add_meter_id" name="meter_id" required onchange="updateMonthlyPreviousReading(); syncAddSaveButtonState();">
                    <option value="">Select Main Meter</option>
                    @forelse($meterOptions as $meterOption)
                        <option value="{{ $meterOption->id }}" @selected($oldMeterId === (string) $meterOption->id)>
                            {{ strtoupper((string) $meterOption->meter_type) }} - {{ $meterOption->meter_name }}
                            @if($meterOption->meter_number) ({{ $meterOption->meter_number }}) @endif
                        </option>
                    @empty
                        <option value="" disabled>No main meter available</option>
                    @endforelse
                </select>
                @if($primaryBillingMeter)
                    <div class="monthly-meter-suggestion">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>Suggested: {{ $primaryBillingMeter->meter_name }}{{ $primaryBillingMeter->meter_number ? ' (' . $primaryBillingMeter->meter_number . ')' : '' }}</span>
                    </div>
                @endif
                @if($meterOptions->isEmpty())
                    <div style="font-size:.82rem;color:#b91c1c;font-weight:700;">No approved Main Meter available. Approve a meter first in Energy Profile.</div>
                @endif
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-gauge-high"></i> Meter Dial Readings (Optional)</div>
            @php
                $defaultPreviousDial = null;
                if (!empty($oldMeterId) && isset($latestMeterDials[(int)$oldMeterId])) {
                    $defaultPreviousDial = $latestMeterDials[(int)$oldMeterId];
                } elseif (isset($latestMeterDials[0])) {
                    $defaultPreviousDial = $latestMeterDials[0];
                }
            @endphp
            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_previous_reading_kwh">Previous Meter Reading (kWh)</label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_previous_reading_kwh" name="previous_reading_kwh" value="{{ old('previous_reading_kwh', $defaultPreviousDial !== null ? number_format((float)$defaultPreviousDial, 2, '.', '') : '') }}" data-auto-filled="{{ $defaultPreviousDial !== null ? 'true' : 'false' }}" placeholder="e.g. 15000.00" oninput="this.dataset.autoFilled='false'; calculateConsumptionFromDials()">
                    <span id="prev_dial_hint" class="monthly-upload-help" style="{{ $defaultPreviousDial !== null ? 'display:block;' : 'display:none;' }} color:#2563eb; font-weight:600; margin-top:4px;">
                        @if($defaultPreviousDial !== null)
                            <i class="fa-solid fa-clock-rotate-left"></i> Auto-filled from previous record: <strong>{{ number_format((float)$defaultPreviousDial, 2) }} kWh</strong>
                        @endif
                    </span>
                </div>
                <div class="monthly-field">
                    <label for="add_current_reading_kwh">Current Meter Reading (kWh)</label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_current_reading_kwh" name="current_reading_kwh" value="{{ old('current_reading_kwh') }}" placeholder="e.g. 16250.50" oninput="calculateConsumptionFromDials()">
                </div>
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-bolt"></i> Consumption &amp; Cost</div>
            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_actual_kwh">Total Consumption (kWh) <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_actual_kwh" name="actual_kwh" value="{{ old('actual_kwh') }}" placeholder="e.g. 1250.50" required oninput="computeEnergyCost(); syncAddSaveButtonState();">
                    <span id="dial_calc_hint" class="monthly-upload-help" style="display:none; color:#16a34a; font-weight:700; margin-top:4px;">
                        <i class="fa-solid fa-circle-check"></i> Auto-computed from dials
                    </span>
                </div>
                <div class="monthly-field">
                    <label for="add_rate_per_kwh">Rate (PHP/kWh) <span style="font-size:0.75rem;font-weight:600;color:#64748b;">(QC Commercial Rate)</span> <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_rate_per_kwh" name="rate_per_kwh" value="{{ old('rate_per_kwh', '12.00') }}" required oninput="computeEnergyCost(); syncAddSaveButtonState();">
                </div>
            </div>

            <div class="monthly-field monthly-computed-field">
                <label for="add_energy_cost">Auto-computed Cost (PHP)</label>
                <i class="fa-solid fa-peso-sign" aria-hidden="true"></i>
                <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" readonly aria-live="polite" placeholder="Calculated from consumption × rate">
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-camera"></i> Supporting Evidence / Photo</div>
            <div class="monthly-field">
                <label for="add_bill_image">Meter Reading Photo (Optional)</label>
                <input type="file" id="add_bill_image" name="bill_image" accept="image/*">
                <span class="monthly-upload-help">Upload a clear photo of the meter dial or reading log sheet.</span>
            </div>

            <div class="monthly-modal-actions">
                <button type="button" onclick="closeAddModal()" class="monthly-modal-btn neutral">Cancel</button>
                <button id="addMonthlyRecordSaveBtn" type="submit" class="monthly-modal-btn primary" @disabled($meterOptions->isEmpty())><i class="fa-solid fa-floppy-disk"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>

<div id="addWeeklyModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card record-form" role="dialog" aria-modal="true" aria-labelledby="addWeeklyRecordTitle" aria-describedby="addWeeklyRecordSubtitle">
        <button type="button" onclick="closeAddWeeklyModal()" class="monthly-modal-close" aria-label="Close add weekly reading form"><i class="fa-solid fa-xmark"></i></button>
        <header class="monthly-record-modal-header">
            <div class="monthly-record-modal-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-calendar-week"></i></div>
            <div>
                <h2 id="addWeeklyRecordTitle" class="monthly-modal-title">Add Weekly Meter Reading</h2>
                <div id="addWeeklyRecordSubtitle" class="monthly-modal-subtitle">
                    Log weekly consumption for Main Meter. When <strong>4/4 weeks</strong> are logged, the full monthly record is automatically synchronized.
                </div>
            </div>
        </header>

        <form id="addWeeklyRecordForm" method="POST" action="{{ route('facility-meter-weekly-readings.store', ['facility' => $facility->id]) }}" style="display:flex;flex-direction:column;gap:12px;">
            @csrf

            <div class="monthly-form-section-title"><i class="fa-solid fa-gauge"></i> Meter &amp; Time Period</div>
            <div class="monthly-field">
                <label for="add_weekly_meter_id">Main Meter <span style="color:#e11d48;">*</span></label>
                <select id="add_weekly_meter_id" name="meter_id" required onchange="updateWeeklyPreviousReading(); syncAddWeeklySaveButtonState();">
                    <option value="">Select Main Meter</option>
                    @forelse($meterOptions as $meterOption)
                        <option value="{{ $meterOption->id }}" @selected($oldMeterId === (string) $meterOption->id)>
                            {{ strtoupper((string) $meterOption->meter_type) }} - {{ $meterOption->meter_name }}
                            @if($meterOption->meter_number) ({{ $meterOption->meter_number }}) @endif
                        </option>
                    @empty
                        <option value="" disabled>No main meter available</option>
                    @endforelse
                </select>
                @if($primaryBillingMeter)
                    <div class="monthly-meter-suggestion">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>Suggested: {{ $primaryBillingMeter->meter_name }}{{ $primaryBillingMeter->meter_number ? ' (' . $primaryBillingMeter->meter_number . ')' : '' }}</span>
                    </div>
                @endif
            </div>

            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_weekly_year">Year <span style="color:#e11d48;">*</span></label>
                    <input type="number" id="add_weekly_year" name="year" value="{{ old('year', $selectedYear) }}" min="2000" max="2100" required oninput="updateWeeklyPreviousReading(); syncAddWeeklySaveButtonState();">
                </div>
                <div class="monthly-field">
                    <label for="add_weekly_month">Month <span style="color:#e11d48;">*</span></label>
                    <select id="add_weekly_month" name="month" required onchange="updateWeeklyPreviousReading(); syncAddWeeklySaveButtonState();">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected(old('month', (int)date('n')) == $m)>
                                {{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_weekly_week_number">Week of Month <span style="color:#e11d48;">*</span></label>
                    <select id="add_weekly_week_number" name="week_number" required onchange="updateWeeklyPreviousReading(); syncAddWeeklySaveButtonState();">
                        <option value="1">Week 1 (Days 1–7)</option>
                        <option value="2">Week 2 (Days 8–14)</option>
                        <option value="3">Week 3 (Days 15–21)</option>
                        <option value="4">Week 4 (Days 22–End of Month)</option>
                    </select>
                </div>
                <div class="monthly-field">
                    <label for="add_weekly_reading_date">Reading Date (Optional)</label>
                    <input type="date" id="add_weekly_reading_date" name="reading_date" value="{{ old('reading_date', date('Y-m-d')) }}">
                </div>
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-gauge-high"></i> Meter Dial Readings (Optional)</div>
            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_weekly_previous_reading_kwh">Previous Meter Dial (kWh)</label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_weekly_previous_reading_kwh" name="previous_reading_kwh" value="{{ old('previous_reading_kwh') }}" placeholder="e.g. 15000.00" oninput="this.dataset.autoFilled='false'; calculateWeeklyConsumptionFromDials()">
                    <span id="weekly_prev_dial_hint" class="monthly-upload-help" style="display:none; color:#2563eb; font-weight:600; margin-top:4px;"></span>
                </div>
                <div class="monthly-field">
                    <label for="add_weekly_current_reading_kwh">Current Meter Dial (kWh)</label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_weekly_current_reading_kwh" name="current_reading_kwh" value="{{ old('current_reading_kwh') }}" placeholder="e.g. 15320.00" oninput="calculateWeeklyConsumptionFromDials()">
                </div>
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-bolt"></i> Weekly Consumption &amp; Rate</div>
            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_weekly_actual_kwh">Weekly Consumption (kWh) <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_weekly_actual_kwh" name="actual_kwh" value="{{ old('actual_kwh') }}" placeholder="e.g. 320.00" required oninput="computeWeeklyCost(); syncAddWeeklySaveButtonState();">
                    <span id="weekly_dial_calc_hint" class="monthly-upload-help" style="display:none; color:#16a34a; font-weight:700; margin-top:4px;">
                        <i class="fa-solid fa-circle-check"></i> Auto-computed from dials
                    </span>
                </div>
                <div class="monthly-field">
                    <label for="add_weekly_rate_per_kwh">Rate (PHP/kWh) <span style="font-size:0.75rem;font-weight:600;color:#64748b;">(QC Commercial Rate)</span> <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_weekly_rate_per_kwh" name="rate_per_kwh" value="{{ old('rate_per_kwh', '12.00') }}" required oninput="computeWeeklyCost(); syncAddWeeklySaveButtonState();">
                </div>
            </div>

            <div class="monthly-field monthly-computed-field">
                <label for="add_weekly_cost">Estimated Weekly Cost (PHP)</label>
                <i class="fa-solid fa-peso-sign" aria-hidden="true"></i>
                <input type="number" step="0.01" id="add_weekly_cost" readonly aria-live="polite" placeholder="Calculated from consumption × rate">
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-pen-to-square"></i> Notes / Observations (Optional)</div>
            <div class="monthly-field">
                <textarea id="add_weekly_notes" name="notes" rows="2" maxlength="500" placeholder="e.g. High aircon usage during event week, generator testing, or regular operational hours" style="width:100%;resize:vertical;border:1px solid #cbd5e1;border-radius:10px;padding:8px 12px;font:inherit;box-sizing:border-box;">{{ old('notes') }}</textarea>
            </div>

            <div style="padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;color:#166534;font-size:0.83rem;line-height:1.4;">
                <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i><strong>Auto-Sum Rule:</strong> When all <strong>4 weeks</strong> are logged for a month, their sum automatically becomes the official Monthly Record. If fewer than 4 weeks are logged, the month stays in progress.
            </div>

            <div class="monthly-modal-actions">
                <button type="button" onclick="closeAddWeeklyModal()" class="monthly-modal-btn neutral">Cancel</button>
                <button id="addWeeklyRecordSaveBtn" type="submit" class="monthly-modal-btn primary" @disabled($meterOptions->isEmpty())><i class="fa-solid fa-floppy-disk"></i> Save Weekly Reading</button>
            </div>
        </form>
    </div>
</div>
@endif

<div id="deleteMonthlyRecordModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card compact">
        <button type="button" onclick="closeDeleteMonthlyRecordModal()" class="monthly-modal-close">&times;</button>
        <h3 class="monthly-modal-title danger">Move Monthly Record to Archive</h3>
        <div id="deleteMonthlyRecordText" style="margin-bottom:16px;color:#334155;font-size:.95rem;"></div>
        <div class="monthly-field" style="margin-bottom:16px;text-align:left;">
            <label for="monthlyRecordArchiveReason">Reason for Archiving <span style="color:#e11d48;">*</span></label>
            <textarea id="monthlyRecordArchiveReason" maxlength="500" rows="3" required
                placeholder="Example: duplicate entry, incorrect reading, or billing correction"
                style="width:100%;resize:vertical;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;font:inherit;box-sizing:border-box;"></textarea>
            <div id="monthlyRecordArchiveReasonError" style="display:none;margin-top:5px;color:#e11d48;font-size:.82rem;font-weight:600;">Please enter a reason before archiving.</div>
        </div>
        <div class="monthly-modal-actions">
            <button type="button" onclick="closeDeleteMonthlyRecordModal()" class="monthly-modal-btn neutral">Cancel</button>
            <button id="confirmDeleteMonthlyRecordBtn" type="button" class="monthly-modal-btn danger">Move to Archive</button>
        </div>
    </div>
</div>

<script>
let deleteMonthlyRecordId = null;

function syncMonthlyModalScrollLock() {
    const hasOpenModal = ['recordTypeModal', 'addModal', 'addWeeklyModal', 'deleteMonthlyRecordModal'].some(function (id) {
        return document.getElementById(id)?.style.display === 'flex';
    });
    document.body.classList.toggle('monthly-modal-open', hasOpenModal);
}

function openRecordTypeModal() {
    const modal = document.getElementById('recordTypeModal');
    if (!modal) return;
    modal.style.display = 'flex';
    syncMonthlyModalScrollLock();
}

function closeRecordTypeModal() {
    const modal = document.getElementById('recordTypeModal');
    if (!modal) return;
    modal.style.display = 'none';
    syncMonthlyModalScrollLock();
}

function selectRecordType(type) {
    closeRecordTypeModal();
    if (type === 'monthly') {
        openAddModal();
    } else if (type === 'weekly') {
        openAddWeeklyModal();
    }
}

function openAddModal() {
    const modal = document.getElementById('addModal');
    const form = document.getElementById('addMonthlyRecordForm');
    if (!modal) return;
    modal.style.display = 'flex';
    syncMonthlyModalScrollLock();
    if (form) form.scrollTop = 0;
    updateMonthlyPreviousReading();
    computeEnergyCost();
    syncAddSaveButtonState();
    window.requestAnimationFrame(function () {
        const firstIncomplete = form?.querySelector(':required:invalid');
        (firstIncomplete || document.getElementById('add_actual_kwh'))?.focus();
    });
}

const meterDialTimeline = @json($meterDialTimeline ?? []);
const meterLatestDials = @json($latestMeterDials ?? []);

function getPreviousMeterReading(meterId, year, month, week) {
    meterId = parseInt(meterId, 10) || 0;
    year = parseInt(year, 10) || (new Date()).getFullYear();
    month = parseInt(month, 10) || ((new Date()).getMonth() + 1);
    week = (week !== undefined && week !== null) ? parseInt(week, 10) : 1;

    const targetKey = year * 10000 + month * 100 + week;

    // Filter checkpoints for this meter (or fallback to 0/all) with valid current dial
    let checkpoints = (meterDialTimeline || []).filter(function(cp) {
        return (cp.meter_id === meterId || (meterId === 0 && cp.meter_id === 0)) 
            && cp.current_reading_kwh !== null && cp.current_reading_kwh !== undefined && !isNaN(cp.current_reading_kwh);
    });

    if (checkpoints.length === 0 && meterId > 0) {
        checkpoints = (meterDialTimeline || []).filter(function(cp) {
            return cp.current_reading_kwh !== null && cp.current_reading_kwh !== undefined && !isNaN(cp.current_reading_kwh);
        });
    }

    // Sort ascending by order_key
    checkpoints.sort(function(a, b) { return a.order_key - b.order_key; });

    // Look for latest checkpoint before target period
    let priorCheckpoint = null;
    for (let i = checkpoints.length - 1; i >= 0; i--) {
        if (checkpoints[i].order_key < targetKey) {
            priorCheckpoint = checkpoints[i];
            break;
        }
    }

    if (priorCheckpoint) {
        return {
            dial: priorCheckpoint.current_reading_kwh,
            source: priorCheckpoint.period_label || 'prior reading',
            isStarting: false
        };
    }

    // If no checkpoint before targetKey, check if there's any overall latest dial
    const latestDial = (meterId && meterLatestDials[meterId] !== undefined)
        ? meterLatestDials[meterId]
        : (meterLatestDials[0] !== undefined ? meterLatestDials[0] : null);

    if (latestDial !== null && latestDial !== undefined && !isNaN(latestDial)) {
        return {
            dial: latestDial,
            source: 'latest recorded reading',
            isStarting: false
        };
    }

    // Default to 0.00 starting dial for fresh meter
    return {
        dial: 0.0,
        source: 'starting dial (0.00 kWh)',
        isStarting: true
    };
}

function updateMonthlyPreviousReading() {
    const meterSelect = document.getElementById('add_meter_id');
    const dateInput = document.getElementById('add_date');
    const prevInput = document.getElementById('add_previous_reading_kwh');
    const prevHint = document.getElementById('prev_dial_hint');
    if (!prevInput) return;

    const meterId = meterSelect && meterSelect.value ? parseInt(meterSelect.value, 10) : 0;
    let year = (new Date()).getFullYear();
    let month = (new Date()).getMonth() + 1;
    if (dateInput && dateInput.value) {
        const parts = dateInput.value.split('-');
        if (parts.length >= 2) {
            year = parseInt(parts[0], 10) || year;
            month = parseInt(parts[1], 10) || month;
        }
    }

    const res = getPreviousMeterReading(meterId, year, month, 0);

    if (res && res.dial !== null && res.dial !== undefined) {
        if (!prevInput.value || prevInput.dataset.autoFilled === 'true' || prevInput.dataset.autoFilled === undefined) {
            prevInput.value = parseFloat(res.dial).toFixed(2);
            prevInput.dataset.autoFilled = 'true';
            if (prevHint) {
                prevHint.style.display = 'block';
                if (res.isStarting) {
                    prevHint.innerHTML = '<i class="fa-solid fa-gauge"></i> Starting dial for meter: <strong>0.00 kWh</strong>';
                } else {
                    prevHint.innerHTML = '<i class="fa-solid fa-clock-rotate-left"></i> Auto-filled from ' + res.source + ': <strong>' + parseFloat(res.dial).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' kWh</strong>';
                }
            }
            calculateConsumptionFromDials();
        }
    } else {
        if (prevInput.dataset.autoFilled === 'true') {
            prevInput.value = '';
            prevInput.dataset.autoFilled = 'false';
            if (prevHint) prevHint.style.display = 'none';
        }
    }
}

function handleMeterSelectionChange() {
    updateMonthlyPreviousReading();
}

function calculateConsumptionFromDials() {
    const prevInput = document.getElementById('add_previous_reading_kwh');
    const currInput = document.getElementById('add_current_reading_kwh');
    const kwhInput = document.getElementById('add_actual_kwh');
    const hint = document.getElementById('dial_calc_hint');
    if (!prevInput || !currInput || !kwhInput) return;

    const prevStr = String(prevInput.value || '').trim();
    const currStr = String(currInput.value || '').trim();

    if (prevStr !== '' && currStr !== '') {
        const prev = parseFloat(prevStr);
        const curr = parseFloat(currStr);
        if (!isNaN(prev) && !isNaN(curr)) {
            if (curr >= prev) {
                const diff = curr - prev;
                kwhInput.value = diff.toFixed(2);
                if (hint) {
                    hint.style.display = 'block';
                    hint.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + curr.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' &minus; ' + prev.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' = <strong>' + diff.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' kWh</strong>';
                }
                computeEnergyCost();
                syncAddSaveButtonState();
            } else {
                if (hint) {
                    hint.style.display = 'block';
                    hint.innerHTML = '<span style="color:#e11d48;"><i class="fa-solid fa-triangle-exclamation"></i> Current reading must be &ge; previous reading.</span>';
                }
            }
        }
    } else {
        if (hint) hint.style.display = 'none';
    }
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    if (!modal) return;
    modal.style.display = 'none';
    syncMonthlyModalScrollLock();
}

function computeEnergyCost() {
    const kwhInput = document.getElementById('add_actual_kwh');
    const rateInput = document.getElementById('add_rate_per_kwh');
    const costInput = document.getElementById('add_energy_cost');
    if (!kwhInput || !rateInput || !costInput) return;

    const hasKwh = String(kwhInput.value || '').trim() !== '';
    const hasRate = String(rateInput.value || '').trim() !== '';
    const kwh = parseFloat(kwhInput.value) || 0;
    const rate = parseFloat(rateInput.value) || 0;
    const cost = kwh * rate;
    costInput.value = hasKwh && hasRate ? cost.toFixed(2) : '';
}

function syncAddSaveButtonState() {
    const saveBtn = document.getElementById('addMonthlyRecordSaveBtn');
    const meterSelect = document.getElementById('add_meter_id');
    const dateInput = document.getElementById('add_date');
    const kwhInput = document.getElementById('add_actual_kwh');
    const rateInput = document.getElementById('add_rate_per_kwh');
    if (!saveBtn || !meterSelect || !dateInput || !kwhInput || !rateInput) return;

    const hasMainMeterOption = Array.from(meterSelect.options).some(function (option) {
        return option.value !== '' && !option.disabled;
    });

    const hasSelectedMainMeter = String(meterSelect.value || '').trim() !== '';
    const hasDate = String(dateInput.value || '').trim() !== '';

    const kwhValue = Number(kwhInput.value);
    const rateValue = Number(rateInput.value);
    const hasValidKwh = String(kwhInput.value || '').trim() !== '' && Number.isFinite(kwhValue) && kwhValue >= 0;
    const hasValidRate = String(rateInput.value || '').trim() !== '' && Number.isFinite(rateValue) && rateValue >= 0;

    // Bill image is optional and should not block save.
    saveBtn.disabled = !(hasMainMeterOption && hasSelectedMainMeter && hasDate && hasValidKwh && hasValidRate);
}

/* Weekly Reading Modal Handlers */
function openAddWeeklyModal(meterId, year, month, week) {
    const modal = document.getElementById('addWeeklyModal');
    const form = document.getElementById('addWeeklyRecordForm');
    if (!modal) return;

    const meterSelect = document.getElementById('add_weekly_meter_id');
    if (meterId && meterSelect) {
        meterSelect.value = String(meterId);
    } else if (meterSelect && !meterSelect.value && meterSelect.options.length > 1) {
        for (let i = 0; i < meterSelect.options.length; i++) {
            if (meterSelect.options[i].value !== '') {
                meterSelect.value = meterSelect.options[i].value;
                break;
            }
        }
    }
    if (year) {
        const yearInput = document.getElementById('add_weekly_year');
        if (yearInput) yearInput.value = String(year);
    }
    if (month) {
        const monthSelect = document.getElementById('add_weekly_month');
        if (monthSelect) monthSelect.value = String(month);
    }
    if (week) {
        const weekSelect = document.getElementById('add_weekly_week_number');
        if (weekSelect) weekSelect.value = String(week);
    }

    modal.style.display = 'flex';
    syncMonthlyModalScrollLock();
    if (form) form.scrollTop = 0;
    updateWeeklyPreviousReading();
    computeWeeklyCost();
    syncAddWeeklySaveButtonState();

    window.requestAnimationFrame(function () {
        const firstIncomplete = form?.querySelector(':required:invalid');
        (firstIncomplete || document.getElementById('add_weekly_actual_kwh'))?.focus();
    });
}

function closeAddWeeklyModal() {
    const modal = document.getElementById('addWeeklyModal');
    if (!modal) return;
    modal.style.display = 'none';
    syncMonthlyModalScrollLock();
}

function updateWeeklyPreviousReading() {
    const meterSelect = document.getElementById('add_weekly_meter_id');
    const yearInput = document.getElementById('add_weekly_year');
    const monthSelect = document.getElementById('add_weekly_month');
    const weekSelect = document.getElementById('add_weekly_week_number');
    const prevInput = document.getElementById('add_weekly_previous_reading_kwh');
    const prevHint = document.getElementById('weekly_prev_dial_hint');

    if (!prevInput) return;

    const meterId = meterSelect && meterSelect.value ? parseInt(meterSelect.value, 10) : 0;
    const year = yearInput && yearInput.value ? parseInt(yearInput.value, 10) : (new Date()).getFullYear();
    const month = monthSelect && monthSelect.value ? parseInt(monthSelect.value, 10) : ((new Date()).getMonth() + 1);
    const week = weekSelect && weekSelect.value ? parseInt(weekSelect.value, 10) : 1;

    const res = getPreviousMeterReading(meterId, year, month, week);

    if (res && res.dial !== null && res.dial !== undefined) {
        if (!prevInput.value || prevInput.dataset.autoFilled === 'true' || prevInput.dataset.autoFilled === undefined) {
            prevInput.value = parseFloat(res.dial).toFixed(2);
            prevInput.dataset.autoFilled = 'true';
            if (prevHint) {
                prevHint.style.display = 'block';
                if (res.isStarting) {
                    prevHint.innerHTML = '<i class="fa-solid fa-gauge"></i> Starting dial: <strong>0.00 kWh</strong>';
                } else {
                    prevHint.innerHTML = '<i class="fa-solid fa-clock-rotate-left"></i> Auto-filled from ' + res.source + ': <strong>' + parseFloat(res.dial).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' kWh</strong>';
                }
            }
            calculateWeeklyConsumptionFromDials();
        }
    } else {
        if (prevInput.dataset.autoFilled === 'true') {
            prevInput.value = '';
            prevInput.dataset.autoFilled = 'false';
            if (prevHint) prevHint.style.display = 'none';
        }
    }
    syncAddWeeklySaveButtonState();
}

function handleWeeklyMeterSelectionChange() {
    updateWeeklyPreviousReading();
}

function calculateWeeklyConsumptionFromDials() {
    const prevInput = document.getElementById('add_weekly_previous_reading_kwh');
    const currInput = document.getElementById('add_weekly_current_reading_kwh');
    const kwhInput = document.getElementById('add_weekly_actual_kwh');
    const hint = document.getElementById('weekly_dial_calc_hint');
    if (!prevInput || !currInput || !kwhInput) return;

    const prevStr = String(prevInput.value || '').trim();
    const currStr = String(currInput.value || '').trim();

    if (prevStr !== '' && currStr !== '') {
        const prev = parseFloat(prevStr);
        const curr = parseFloat(currStr);
        if (!isNaN(prev) && !isNaN(curr)) {
            if (curr >= prev) {
                const diff = curr - prev;
                kwhInput.value = diff.toFixed(2);
                if (hint) {
                    hint.style.display = 'block';
                    hint.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + curr.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' &minus; ' + prev.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' = <strong>' + diff.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' kWh</strong>';
                }
                computeWeeklyCost();
                syncAddWeeklySaveButtonState();
            } else {
                if (hint) {
                    hint.style.display = 'block';
                    hint.innerHTML = '<span style="color:#e11d48;"><i class="fa-solid fa-triangle-exclamation"></i> Current reading must be &ge; previous reading.</span>';
                }
            }
        }
    } else {
        if (hint) hint.style.display = 'none';
    }
}

function computeWeeklyCost() {
    const kwhInput = document.getElementById('add_weekly_actual_kwh');
    const rateInput = document.getElementById('add_weekly_rate_per_kwh');
    const costInput = document.getElementById('add_weekly_cost');
    if (!kwhInput || !rateInput || !costInput) return;

    const hasKwh = String(kwhInput.value || '').trim() !== '';
    const hasRate = String(rateInput.value || '').trim() !== '';
    const kwh = parseFloat(kwhInput.value) || 0;
    const rate = parseFloat(rateInput.value) || 0;
    const cost = kwh * rate;
    costInput.value = hasKwh && hasRate ? cost.toFixed(2) : '';
}

function syncAddWeeklySaveButtonState() {
    const saveBtn = document.getElementById('addWeeklyRecordSaveBtn');
    const meterSelect = document.getElementById('add_weekly_meter_id');
    const yearInput = document.getElementById('add_weekly_year');
    const monthSelect = document.getElementById('add_weekly_month');
    const weekSelect = document.getElementById('add_weekly_week_number');
    const kwhInput = document.getElementById('add_weekly_actual_kwh');
    const rateInput = document.getElementById('add_weekly_rate_per_kwh');
    if (!saveBtn || !meterSelect || !yearInput || !monthSelect || !weekSelect || !kwhInput || !rateInput) return;

    const hasMeter = String(meterSelect.value || '').trim() !== '';
    const hasYear = String(yearInput.value || '').trim() !== '';
    const hasMonth = String(monthSelect.value || '').trim() !== '';
    const hasWeek = String(weekSelect.value || '').trim() !== '';

    const kwhValue = Number(kwhInput.value);
    const rateValue = Number(rateInput.value);
    const hasValidKwh = String(kwhInput.value || '').trim() !== '' && Number.isFinite(kwhValue) && kwhValue >= 0;
    const hasValidRate = String(rateInput.value || '').trim() !== '' && Number.isFinite(rateValue) && rateValue >= 0;

    saveBtn.disabled = !(hasMeter && hasYear && hasMonth && hasWeek && hasValidKwh && hasValidRate);
}

function openDeleteMonthlyRecordModal(recordId, monthName, year) {
    deleteMonthlyRecordId = recordId;
    const text = document.getElementById('deleteMonthlyRecordText');
    const modal = document.getElementById('deleteMonthlyRecordModal');
    const reason = document.getElementById('monthlyRecordArchiveReason');
    const error = document.getElementById('monthlyRecordArchiveReasonError');
    if (text) text.textContent = `Move the record for ${monthName} ${year} to the archive? You can restore it later from the Monthly Records Archive.`;
    if (reason) reason.value = '';
    if (error) error.style.display = 'none';
    if (modal) modal.style.display = 'flex';
    syncMonthlyModalScrollLock();
}

function closeDeleteMonthlyRecordModal() {
    deleteMonthlyRecordId = null;
    const modal = document.getElementById('deleteMonthlyRecordModal');
    if (modal) modal.style.display = 'none';
    syncMonthlyModalScrollLock();
}

document.getElementById('confirmDeleteMonthlyRecordBtn')?.addEventListener('click', function () {
    if (!deleteMonthlyRecordId) return;
    const form = document.getElementById(`deleteMonthlyRecordForm-${deleteMonthlyRecordId}`);
    const reason = document.getElementById('monthlyRecordArchiveReason');
    const error = document.getElementById('monthlyRecordArchiveReasonError');
    const value = String(reason?.value || '').trim();
    if (!value) {
        if (error) error.style.display = 'block';
        reason?.focus();
        return;
    }
    if (form) {
        const hiddenReason = form.querySelector('input[name="archive_reason"]');
        if (hiddenReason) hiddenReason.value = value;
        form.submit();
    }
});

function toggleWeeklyMonth(headerEl, event) {
    if (event && event.target && event.target.closest('a')) {
        return;
    }
    const card = headerEl ? headerEl.closest('.weekly-month-card') : null;
    if (!card) return;
    const isNowCollapsed = card.classList.toggle('is-collapsed');
    const toggleText = card.querySelector('.weekly-toggle-text');
    if (toggleText) {
        toggleText.textContent = isNowCollapsed ? 'Show 4 Weeks' : 'Collapse';
    }
    const toggleIcon = card.querySelector('.weekly-toggle-icon');
    if (toggleIcon) {
        toggleIcon.classList.remove('fa-chevron-up', 'fa-chevron-down');
        toggleIcon.classList.add(isNowCollapsed ? 'fa-chevron-down' : 'fa-chevron-up');
    }
}

function toggleAllWeeklyMonths(expand) {
    document.querySelectorAll('.weekly-month-card').forEach(function (card) {
        if (expand) {
            card.classList.remove('is-collapsed');
        } else {
            card.classList.add('is-collapsed');
        }
        const toggleText = card.querySelector('.weekly-toggle-text');
        if (toggleText) {
            toggleText.textContent = expand ? 'Collapse' : 'Show 4 Weeks';
        }
        const toggleIcon = card.querySelector('.weekly-toggle-icon');
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-up', 'fa-chevron-down');
            toggleIcon.classList.add(expand ? 'fa-chevron-up' : 'fa-chevron-down');
        }
    });
}

window.addEventListener('DOMContentLoaded', function () {
    const recordTypeModal = document.getElementById('recordTypeModal');
    const addModal = document.getElementById('addModal');
    const addWeeklyModal = document.getElementById('addWeeklyModal');
    const deleteModal = document.getElementById('deleteMonthlyRecordModal');
    const summaryModeSelect = document.getElementById('summary_mode');
    const summaryMonthSelect = document.getElementById('summary_month');
    const addMonthlyRecordForm = document.getElementById('addMonthlyRecordForm');
    const addWeeklyRecordForm = document.getElementById('addWeeklyRecordForm');
    const overviewToggle = document.getElementById('monthlyOverviewToggle');
    const overviewContent = document.getElementById('monthlyOverviewContent');

    function setOverviewCollapsed(collapsed) {
        if (!overviewToggle || !overviewContent) return;

        overviewContent.classList.toggle('is-collapsed', collapsed);
        overviewToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        overviewToggle.setAttribute('title', collapsed ? 'Expand Main Meter Overview' : 'Collapse Main Meter Overview');

        const assistiveText = overviewToggle.querySelector('.sr-only');
        if (assistiveText) {
            assistiveText.textContent = collapsed ? 'Expand Main Meter Overview' : 'Collapse Main Meter Overview';
        }
    }

    if (overviewToggle && overviewContent) {
        setOverviewCollapsed(true);

        overviewToggle.addEventListener('click', function () {
            const collapsed = !overviewContent.classList.contains('is-collapsed');
            setOverviewCollapsed(collapsed);
        });
    }

    if (recordTypeModal) {
        recordTypeModal.addEventListener('click', function (event) {
            if (event.target === recordTypeModal) {
                closeRecordTypeModal();
            }
        });
    }

    if (addModal) {
        addModal.addEventListener('click', function (event) {
            if (event.target === addModal) {
                closeAddModal();
            }
        });
    }

    if (addWeeklyModal) {
        addWeeklyModal.addEventListener('click', function (event) {
            if (event.target === addWeeklyModal) {
                closeAddWeeklyModal();
            }
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                closeDeleteMonthlyRecordModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeRecordTypeModal();
            closeAddModal();
            closeAddWeeklyModal();
            closeDeleteMonthlyRecordModal();
        }
    });

    if (addMonthlyRecordForm) {
        addMonthlyRecordForm.addEventListener('input', syncAddSaveButtonState);
        addMonthlyRecordForm.addEventListener('change', syncAddSaveButtonState);
        addMonthlyRecordForm.addEventListener('submit', function (event) {
            syncAddSaveButtonState();
            const saveBtn = document.getElementById('addMonthlyRecordSaveBtn');
            if (saveBtn && saveBtn.disabled) {
                event.preventDefault();
                return;
            }
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Record...';
            }
        });
    }

    if (addWeeklyRecordForm) {
        addWeeklyRecordForm.addEventListener('input', syncAddWeeklySaveButtonState);
        addWeeklyRecordForm.addEventListener('change', syncAddWeeklySaveButtonState);
        addWeeklyRecordForm.addEventListener('submit', function (event) {
            syncAddWeeklySaveButtonState();
            const saveBtn = document.getElementById('addWeeklyRecordSaveBtn');
            if (saveBtn && saveBtn.disabled) {
                event.preventDefault();
                return;
            }
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Weekly Reading...';
            }
        });
    }

    document.querySelectorAll('[data-main-sub-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = String(button.getAttribute('data-main-sub-toggle') || '');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;

            const collapsed = target.classList.toggle('is-collapsed');
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

            const icon = button.querySelector('.monthly-org-arrow i');
            if (icon) {
                icon.classList.remove('fa-chevron-up', 'fa-chevron-down');
                icon.classList.add(collapsed ? 'fa-chevron-down' : 'fa-chevron-up');
            }
        });
    });

    document.querySelectorAll('[data-record-breakdown]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = String(button.getAttribute('data-record-breakdown') || '');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;

            const shouldOpen = target.hidden;

            document.querySelectorAll('[data-record-breakdown]').forEach(function (otherButton) {
                const otherTargetId = String(otherButton.getAttribute('data-record-breakdown') || '');
                const otherTarget = otherTargetId ? document.getElementById(otherTargetId) : null;
                if (otherTarget) otherTarget.hidden = true;
                otherButton.setAttribute('aria-expanded', 'false');
                const otherIcon = otherButton.querySelector('i');
                if (otherIcon) {
                    otherIcon.classList.remove('fa-chevron-up');
                    otherIcon.classList.add('fa-chevron-down');
                }
            });

            target.hidden = !shouldOpen;
            button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-chevron-down', 'fa-chevron-up');
                icon.classList.add(shouldOpen ? 'fa-chevron-up' : 'fa-chevron-down');
            }
        });
    });

    function syncSummaryMonthState() {
        if (!summaryModeSelect || !summaryMonthSelect) return;
        summaryMonthSelect.disabled = summaryModeSelect.value === 'year';
    }

    summaryModeSelect?.addEventListener('change', syncSummaryMonthState);
    syncSummaryMonthState();
    syncAddSaveButtonState();
    syncAddWeeklySaveButtonState();
});

@if($canManageLocalMonthlyRecords && ($errors->has('duplicate') || request()->boolean('open_add')))
window.addEventListener('DOMContentLoaded', function () {
    openAddModal();
});
@endif

@if($canManageLocalMonthlyRecords && ($errors->has('duplicate_week') || request()->boolean('open_weekly_add')))
window.addEventListener('DOMContentLoaded', function () {
    openAddWeeklyModal();
});
@endif
</script>
@endsection
