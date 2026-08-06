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

    .monthly-integration-badge {
        min-height: 28px;
        padding: 5px 10px;
        border: 1px solid #86efac;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #166534;
        background: #f0fdf4;
        font-size: .68rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .monthly-integration-badge.is-partial,
    .monthly-integration-badge.is-error {
        color: #b91c1c;
        border-color: #fecaca;
        background: #fef2f2;
    }

    .monthly-integration-badge.is-waiting {
        color: #92400e;
        border-color: #fde68a;
        background: #fffbeb;
    }

    .monthly-integration-badge.is-off {
        color: #475569;
        border-color: #cbd5e1;
        background: #f8fafc;
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
        min-width: 1460px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .monthly-table thead tr {
        background: #f8fafc;
    }

    .monthly-table th,
    .monthly-table td {
        border-bottom: 1px solid #eef2f7;
        padding: 11px 10px;
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
        width: 76px;
        padding-left: 20px;
    }

    .monthly-table th:nth-child(2),
    .monthly-table td:nth-child(2) {
        width: 220px;
    }

    .monthly-table th:nth-child(3),
    .monthly-table td:nth-child(3) {
        width: 115px;
    }

    .monthly-table th:nth-child(3),
    .monthly-table td:nth-child(3),
    .monthly-table th:nth-child(4),
    .monthly-table td:nth-child(4),
    .monthly-table th:nth-child(7),
    .monthly-table td:nth-child(7),
    .monthly-table th:nth-child(8),
    .monthly-table td:nth-child(8) {
        text-align: right;
    }

    .monthly-table th:nth-child(5),
    .monthly-table td:nth-child(5),
    .monthly-table th:nth-child(6),
    .monthly-table td:nth-child(6),
    .monthly-table th:nth-child(10),
    .monthly-table td:nth-child(10),
    .monthly-table th:nth-child(11),
    .monthly-table td:nth-child(11),
    .monthly-table th:nth-child(12),
    .monthly-table td:nth-child(12) {
        text-align: center;
    }

    .monthly-table th:nth-child(3),
    .monthly-table td:nth-child(3),
    .monthly-table th:nth-child(4),
    .monthly-table td:nth-child(4),
    .monthly-table th:nth-child(7),
    .monthly-table td:nth-child(7) {
        width: 115px;
    }

    .monthly-table th:nth-child(5),
    .monthly-table td:nth-child(5) {
        width: 135px;
    }

    .monthly-table th:nth-child(6),
    .monthly-table td:nth-child(6) {
        width: 115px;
    }

    .monthly-table th:nth-child(8),
    .monthly-table td:nth-child(8) {
        width: 135px;
    }

    .monthly-table th:nth-child(9),
    .monthly-table td:nth-child(9) {
        width: 115px;
        text-align: center;
    }

    .monthly-table th:nth-child(10),
    .monthly-table td:nth-child(10) {
        width: 82px;
    }

    .monthly-table th:nth-child(11),
    .monthly-table td:nth-child(11) {
        width: 135px;
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

    #addMonthlyRecordForm {
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

    #addMonthlyRecordForm .monthly-field label {
        color: #334155;
        font-size: .78rem;
        font-weight: 800;
    }

    #addMonthlyRecordForm .monthly-field input,
    #addMonthlyRecordForm .monthly-field select {
        min-height: 45px;
        padding: 10px 12px;
        border-radius: 11px;
        background: #fff;
    }

    #addMonthlyRecordForm .monthly-field input:focus,
    #addMonthlyRecordForm .monthly-field select:focus {
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
    }

    .monthly-computed-field { position: relative; }
    .monthly-computed-field > i {
        position: absolute;
        left: 13px;
        bottom: 14px;
        color: #059669;
    }
    #addMonthlyRecordForm #add_energy_cost {
        padding-left: 34px;
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
        font-weight: 850;
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
    body.dark-mode #addMonthlyRecordForm #add_energy_cost { background:#052e2b; border-color:#047857; color:#a7f3d0; }
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
    .monthly-workflow { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .monthly-workflow-step { display:flex; align-items:center; gap:9px; min-height:62px; padding:11px 14px; border-right:1px solid #e2e8f0; }
    .monthly-workflow-step:last-child { border-right:0; }
    .monthly-workflow-number { width:28px; height:28px; flex:0 0 28px; display:grid; place-items:center; border-radius:9px; color:#1d4ed8; background:#dbeafe; font-size:.72rem; font-weight:950; }
    .monthly-workflow-title { color:#334155; font-size:.72rem; font-weight:900; }
    .monthly-workflow-text { margin-top:2px; color:#64748b; font-size:.61rem; line-height:1.3; font-weight:650; }
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
    body.dark-mode .monthly-integration-badge { color:#86efac; border-color:#166534; background:rgba(22,101,52,.18); }
    body.dark-mode .monthly-integration-badge.is-partial,
    body.dark-mode .monthly-integration-badge.is-error { color:#fca5a5; border-color:#7f1d1d; background:rgba(127,29,29,.18); }
    body.dark-mode .monthly-integration-badge.is-waiting { color:#fde68a; border-color:#92400e; background:rgba(146,64,14,.18); }
    body.dark-mode .monthly-integration-badge.is-off { color:#cbd5e1; border-color:#475569; background:#111827; }
    body.dark-mode .monthly-performance-value { color:#f1f5f9; }
    body.dark-mode .monthly-workflow { background:#0f172a; border-color:#334155; }
    body.dark-mode .monthly-workflow-step { border-color:#334155; }
    body.dark-mode .monthly-workflow-title { color:#e2e8f0; }
    body.dark-mode .monthly-workflow-text { color:#94a3b8; }
    body.dark-mode .monthly-workflow-number { color:#bfdbfe; border:1px solid #334b70; background:rgba(37,99,235,.16); }
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
    @media (max-width:900px) { .monthly-performance-grid,.monthly-overview-insights { grid-template-columns:repeat(2,minmax(0,1fr)); } .monthly-workflow { grid-template-columns:repeat(2,minmax(0,1fr)); } .monthly-workflow-step:nth-child(2) { border-right:0; } .monthly-record-table-filter { align-items:stretch; flex-direction:column; gap:8px; } .monthly-filter-heading { padding-bottom:0; } }
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
    @media (max-width:600px) { .report-card-container.monthly-report-card-container { padding:13px; border-radius:18px; } .monthly-performance-grid,.monthly-overview-insights,.monthly-workflow,.monthly-record-breakdown-grid { grid-template-columns:1fr; } .monthly-workflow-step { border-right:0; border-bottom:1px solid #e2e8f0; } .monthly-header-identity { align-items:flex-start; } .monthly-header-icon { width:42px; height:42px; flex-basis:42px; } .monthly-record-breakdown-head { flex-direction:column; gap:8px; } .monthly-record-breakdown-actions { justify-content:stretch; } .monthly-record-breakdown-actions a { flex:1 1 auto; justify-content:center; } }
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
            $mainMeterNoticeText = 'Add a Main Meter in Energy Profile first, then approve it before encoding monthly records or viewing sub-meter data.';
        } elseif ($pendingMainMeterCount > 0) {
            $mainMeterNoticeTitle = $pendingMainMeterCount . ' Main Meter pending approval.';
            $mainMeterNoticeText = 'Approve at least one Main Meter in Energy Profile before encoding monthly records or viewing sub-meter data.';
        } else {
            $mainMeterNoticeTitle = 'No approved Main Meter found for this facility.';
            $mainMeterNoticeText = 'Check the Main Meter list in Energy Profile and approve an eligible meter first.';
        }
    }
@endphp

@php
    $requestedRecordDate = trim((string) request('record_date', ''));
    $recordDateDefault = date('Y-m-d');
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $requestedRecordDate, $recordDateParts)
        && checkdate((int) $recordDateParts[2], (int) $recordDateParts[3], (int) $recordDateParts[1])) {
        $recordDateDefault = $requestedRecordDate;
    }

    $umanState = $umanSync['state'] ?? ($umanConfigured ? 'waiting' : 'not_configured');
    $umanBadgeClass = match ($umanState) {
        'partial' => 'is-partial',
        'error' => 'is-error',
        'waiting' => 'is-waiting',
        'connected' => '',
        default => 'is-off',
    };
    $umanBadgeLabel = match ($umanState) {
        'connected' => 'UMAN Connected',
        'partial' => 'UMAN Partial Sync',
        'error' => 'UMAN Sync Error',
        'waiting' => 'UMAN Waiting for Sync',
        default => 'UMAN Not Configured',
    };
    $umanBadgeTitle = trim((string) ($umanSync['message'] ?? ''));
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
                            <span class="monthly-integration-badge {{ $umanBadgeClass }}"
                                  title="{{ $umanBadgeTitle ?: 'CPRF readings are imported through the UMAN monthly energy feed.' }}"
                                  aria-label="UMAN integration status: {{ $umanBadgeLabel }}">
                                <i class="fa-solid {{ $umanState === 'connected' ? 'fa-circle-check' : ($umanState === 'error' ? 'fa-circle-exclamation' : 'fa-arrows-rotate') }}"></i>
                                {{ $umanBadgeLabel }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="monthly-actions">
                    <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="monthly-action-btn is-info">
                        <i class="fa fa-bolt"></i> Energy Profile
                    </a>
                    @if($canManageLocalMonthlyRecords)
                    <button type="button" onclick="openAddModal()" class="monthly-action-btn is-primary">
                        <i class="fa fa-plus"></i> Add Monthly Record
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
                <a href="{{ route('facilities.monthly-records.archive', $facility->id) }}"
                   class="monthly-archive-btn"
                   title="View archived records">
                    <i class="fa fa-archive"></i> Archive
                </a>
            </div>
        </div>

        <div class="monthly-workflow" aria-label="Monthly record workflow">
            <div class="monthly-workflow-step"><span class="monthly-workflow-number">1</span><div><div class="monthly-workflow-title">Encode bill</div><div class="monthly-workflow-text">Select main meter and enter monthly usage in the Energy system.</div></div></div>
            <div class="monthly-workflow-step"><span class="monthly-workflow-number">2</span><div><div class="monthly-workflow-title">Validate record</div><div class="monthly-workflow-text">Review the bill, rate, and meter assignment.</div></div></div>
            <div class="monthly-workflow-step"><span class="monthly-workflow-number">3</span><div><div class="monthly-workflow-title">Evaluate performance</div><div class="monthly-workflow-text">Compare against baseline and threshold settings.</div></div></div>
            <div class="monthly-workflow-step"><span class="monthly-workflow-number">4</span><div><div class="monthly-workflow-title">Review insight</div><div class="monthly-workflow-text">Open recommendations and act on exceptions.</div></div></div>
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

                <div class="monthly-field">
                    <label for="table_month_filter">Month</label>
                    <select id="table_month_filter" name="table_month">
                        <option value="0" @selected($tableFilterMonth === 0)>All Months</option>
                        @foreach($monthLabels as $monthNumber => $monthLabel)
                            <option value="{{ (int) $monthNumber }}" @selected($tableFilterMonth === (int) $monthNumber)>{{ $monthLabel }}</option>
                        @endforeach
                    </select>
                </div>

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
                            $scopeLabelRow = $isCprfFacilityLevel ? 'CPRF' : 'MAIN';
                            $scopeNameRow = $isCprfFacilityLevel
                                ? 'Facility-Level (CPRF)'
                                : (string) ($record->meter->meter_name ?? 'Main Meter');
                            $scopeBg = $isCprfFacilityLevel ? '#f3e8ff' : '#eff6ff';
                            $scopeColor = $isCprfFacilityLevel ? '#7c3aed' : '#1d4ed8';

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
                                <div class="monthly-scope-cell"><span class="scope-pill" style="background:{{ $scopeBg }};color:{{ $scopeColor }};">{{ $scopeLabelRow }}</span><span class="monthly-meter-name">{{ $scopeNameRow }}</span></div>
                            </td>
                            <td data-label="Consumption">
                                <div class="monthly-record-comparison">
                                    <div class="monthly-record-metric"><span>Actual</span><strong class="monthly-number">{{ $record->actual_kwh !== null ? number_format((float) $record->actual_kwh, 2).' kWh' : '-' }}</strong></div>
                                    <div class="monthly-record-metric"><span>Baseline</span><strong class="monthly-muted-number">{{ $baselineRow !== null ? number_format($baselineRow, 2).' kWh' : '-' }}</strong></div>
                                </div>
                            </td>
                            <td data-label="Performance"><div class="monthly-performance-cell">
                                @if($reviewStatus === 'approved')
                                    <span class="monthly-status-pill" style="background:{{ $changeBg }};color:{{ $changeColor }};">
                                        {{ $changeLabel }}
                                    </span>
                                    <span class="monthly-status-pill" style="background:{{ $baselineAlertBg }};color:{{ $baselineAlertColor }};">
                                        {{ $baselineAlertLabel }}
                                    </span>
                                    @if(!empty($record->trend_spike_detected))
                                        <div style="margin-top:6px;">
                                            <span class="monthly-status-pill" style="background:#fee2e2;color:#991b1b;">
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
                                    <span class="monthly-review-pill" style="background:{{ $reviewTheme['bg'] }};color:{{ $reviewTheme['color'] }};"><i class="fa-solid {{ $reviewStatus === 'approved' ? 'fa-circle-check' : ($reviewStatus === 'returned' ? 'fa-rotate-left' : 'fa-clock') }}"></i>{{ $reviewTheme['label'] }}</span>
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
                                    <a href="{{ $billImageUrl }}" target="_blank" rel="noopener" class="monthly-bill-link"><i class="fa-solid fa-receipt"></i> Bill</a>
                                @else
                                    <span class="monthly-bill-link missing"><i class="fa-solid fa-receipt"></i> No bill</span>
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
                                        <span class="monthly-review-pill" style="background:{{ $reviewTheme['bg'] }};color:{{ $reviewTheme['color'] }};">
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
                                            <small>{{ $billImageUrl ? 'Supporting bill attached' : 'No supporting bill attached' }}</small>
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
                                            <a href="{{ $billImageUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-receipt"></i> Open supporting bill</a>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManageLocalMonthlyRecords)
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
                <input type="date" id="add_date" name="date" value="{{ old('date', $recordDateDefault) }}" required>
            </div>

            <div class="monthly-field">
                <label for="add_meter_id">Main Meter <span style="color:#e11d48;">*</span></label>
                <select id="add_meter_id" name="meter_id" required>
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

            <div class="monthly-form-section-title"><i class="fa-solid fa-bolt"></i> Consumption &amp; Cost</div>
            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_actual_kwh">Current Consumption (kWh) <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_actual_kwh" name="actual_kwh" value="{{ old('actual_kwh') }}" placeholder="e.g. 1250.50" required>
                </div>
                <div class="monthly-field">
                    <label for="add_rate_per_kwh">Rate (PHP/kWh) <span style="color:#e11d48;">*</span></label>
                    <input type="number" min="0" step="0.01" inputmode="decimal" id="add_rate_per_kwh" name="rate_per_kwh" value="{{ old('rate_per_kwh', '12.00') }}" required>
                </div>
            </div>

            <div class="monthly-field monthly-computed-field">
                <label for="add_energy_cost">Auto-computed Cost (PHP)</label>
                <i class="fa-solid fa-peso-sign" aria-hidden="true"></i>
                <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" readonly aria-live="polite" placeholder="Calculated from consumption × rate">
            </div>

            <div class="monthly-form-section-title"><i class="fa-solid fa-image"></i> Supporting Document</div>
            <div class="monthly-field">
                <label for="add_bill_image">Bill Image (Optional)</label>
                <input type="file" id="add_bill_image" name="bill_image" accept="image/*">
                <span class="monthly-upload-help">Upload a clear photo or scan of the electric bill.</span>
            </div>

            <div class="monthly-modal-actions">
                <button type="button" onclick="closeAddModal()" class="monthly-modal-btn neutral">Cancel</button>
                <button id="addMonthlyRecordSaveBtn" type="submit" class="monthly-modal-btn primary" @disabled($meterOptions->isEmpty())><i class="fa-solid fa-floppy-disk"></i> Save Record</button>
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
    const hasOpenModal = ['addModal', 'deleteMonthlyRecordModal'].some(function (id) {
        return document.getElementById(id)?.style.display === 'flex';
    });
    document.body.classList.toggle('monthly-modal-open', hasOpenModal);
}

function openAddModal() {
    const modal = document.getElementById('addModal');
    const form = document.getElementById('addMonthlyRecordForm');
    if (!modal) return;
    modal.style.display = 'flex';
    syncMonthlyModalScrollLock();
    if (form) form.scrollTop = 0;
    computeEnergyCost();
    syncAddSaveButtonState();
    window.requestAnimationFrame(function () {
        const firstIncomplete = form?.querySelector(':required:invalid');
        (firstIncomplete || document.getElementById('add_actual_kwh'))?.focus();
    });
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

document.getElementById('add_actual_kwh')?.addEventListener('input', computeEnergyCost);
document.getElementById('add_rate_per_kwh')?.addEventListener('input', computeEnergyCost);
computeEnergyCost();
syncAddSaveButtonState();

window.addEventListener('DOMContentLoaded', function () {
    const addModal = document.getElementById('addModal');
    const deleteModal = document.getElementById('deleteMonthlyRecordModal');
    const summaryModeSelect = document.getElementById('summary_mode');
    const summaryMonthSelect = document.getElementById('summary_month');
    const addMonthlyRecordForm = document.getElementById('addMonthlyRecordForm');
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

    if (addModal) {
        addModal.addEventListener('click', function (event) {
            if (event.target === addModal) {
                closeAddModal();
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
            closeAddModal();
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
});

@if($canManageLocalMonthlyRecords && ($errors->has('duplicate') || request()->boolean('open_add')))
window.addEventListener('DOMContentLoaded', function () {
    openAddModal();
});
@endif
</script>
@endsection
