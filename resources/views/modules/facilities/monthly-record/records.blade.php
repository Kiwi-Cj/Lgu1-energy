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
    }

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
        border-top: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .monthly-table {
        width: 100%;
        min-width: 1160px;
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
        padding: 12px 14px;
    }

    .monthly-table th {
        color: #475569;
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: normal;
        line-height: 1.35;
        box-shadow: inset 0 -1px 0 #e2e8f0;
    }

    .monthly-table td {
        color: #1e293b;
        font-size: .88rem;
        line-height: 1.35;
        vertical-align: middle;
    }

    .monthly-table th:nth-child(1),
    .monthly-table td:nth-child(1) {
        width: 72px;
    }

    .monthly-table th:nth-child(2),
    .monthly-table td:nth-child(2) {
        width: 78px;
    }

    .monthly-table th:nth-child(3),
    .monthly-table td:nth-child(3) {
        width: 210px;
    }

    .monthly-table th:nth-child(4),
    .monthly-table td:nth-child(4),
    .monthly-table th:nth-child(5),
    .monthly-table td:nth-child(5),
    .monthly-table th:nth-child(8),
    .monthly-table td:nth-child(8),
    .monthly-table th:nth-child(9),
    .monthly-table td:nth-child(9) {
        text-align: right;
    }

    .monthly-table th:nth-child(6),
    .monthly-table td:nth-child(6),
    .monthly-table th:nth-child(7),
    .monthly-table td:nth-child(7),
    .monthly-table th:nth-child(10),
    .monthly-table td:nth-child(10),
    .monthly-table th:nth-child(11),
    .monthly-table td:nth-child(11) {
        text-align: center;
    }

    .monthly-table th:nth-child(11),
    .monthly-table td:nth-child(11) {
        width: 82px;
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

    .scope-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
    }

    .monthly-scope-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .monthly-meter-name {
        min-width: 0;
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
        min-width: 72px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 900;
        line-height: 1.2;
    }

    .monthly-bill-thumb {
        display: inline-flex;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dbe4f0;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .08);
    }

    .monthly-bill-thumb img {
        width: 44px;
        height: 44px;
        object-fit: cover;
        display: block;
    }

    .monthly-empty-mark {
        color: #94a3b8;
        font-weight: 700;
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
        width: 34px;
        height: 34px;
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
    }

    body.dark-mode .monthly-card {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 14px 28px rgba(2, 6, 23, 0.55);
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
        color: #94a3b8;
        box-shadow: inset 0 -1px 0 #334155;
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

    body.dark-mode .monthly-bill-thumb {
        border-color: #334155;
        box-shadow: 0 6px 14px rgba(2, 6, 23, .45);
    }

    body.dark-mode .monthly-modal-card {
        background: #0f172a;
        color: #e2e8f0;
    }

    body.dark-mode .monthly-modal-card.compact {
        background: #111827;
    }

    body.dark-mode .monthly-modal-subtitle {
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

    $tableMeterOptions = $recordsForYear
        ->groupBy(fn ($record) => (int) ($record->meter_id ?? 0))
        ->map(function ($group, $meterId) {
            $first = $group->first();

            return [
                'id' => (int) $meterId,
                'meter_name' => (string) ($first->meter->meter_name ?? ('Main Meter #' . (int) $meterId)),
                'meter_number' => (string) ($first->meter->meter_number ?? ''),
            ];
        })
        ->filter(fn ($row) => (int) ($row['id'] ?? 0) > 0)
        ->sortBy('meter_name')
        ->values();

    if ($tableFilterMeterId > 0 && ! $tableMeterOptions->contains(fn ($row) => (int) ($row['id'] ?? 0) === $tableFilterMeterId)) {
        $tableFilterMeterId = 0;
    }

    $tableRecords = $recordsForYear
        ->filter(function ($record) use ($tableFilterMonth, $tableFilterMeterId) {
            if ($tableFilterMonth > 0 && (int) ($record->month ?? 0) !== $tableFilterMonth) {
                return false;
            }

            if ($tableFilterMeterId > 0 && (int) ($record->meter_id ?? 0) !== $tableFilterMeterId) {
                return false;
            }

            return true;
        })
        ->values();

    $tableRecordCount = $tableRecords->count();
    $tableActualKwhTotal = round((float) $tableRecords->sum(fn ($record) => (float) ($record->actual_kwh ?? 0)), 2);
    $tableCostTotal = round((float) $tableRecords->sum(fn ($record) => \App\Support\EnergyCost::cost($record)), 2);
    $tableFilterApplied = $tableFilterMonth > 0 || $tableFilterMeterId > 0;
    $baselineAlertThresholds = \App\Models\EnergyRecord::alertThresholdsBySize();

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

<div class="monthly-shell">
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
                <div>
                    <h1>Monthly Energy Records</h1>
                    <p>Facility: <span class="facility-name">{{ $facility->name }}</span></p>
                    <p style="margin-top:2px;">
                        Source: <span class="facility-name">{{ $billingSourceLabel }}</span>
                    </p>
                </div>
                <div class="monthly-actions">
                    <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" class="monthly-action-btn is-info">
                        <i class="fa fa-bolt"></i>Energy Profile
                    </a>
                    <button type="button" onclick="openAddModal()" class="monthly-action-btn is-primary">
                        <i class="fa fa-plus"></i> Add Monthly Record
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="monthly-card">
        <div class="monthly-card-body">
            <div class="monthly-filters-head">
                <span class="monthly-table-subtitle">
                    {{ $approvedMainMeterCount }} approved main meter(s)
                    @if($pendingMainMeterCount > 0)
                        | {{ $pendingMainMeterCount }} pending approval
                    @endif
                    | {{ $summaryContextLabel }}
                </span>
            </div>
            @if($hasApprovedMainMeter)
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                    <span class="monthly-chip">Main Total: {{ number_format((float) $overallMainKwh, 2) }} kWh</span>
                </div>
            @endif

            @if($mainMeterOrganization->isEmpty())
                <div class="monthly-org-empty">
                    <div class="monthly-org-empty-title">{{ $mainMeterNoticeTitle }}</div>
                    <div style="font-size:.86rem;line-height:1.4;">{{ $mainMeterNoticeText }}</div>
                </div>
            @else
                <div class="monthly-org-wrap">
                    @foreach($mainMeterOrganization as $mainItem)
                        <div class="monthly-org-block">
                            <div class="monthly-org-head">
                                <div class="monthly-org-main">
                                    <div class="monthly-org-main-name">{{ $mainItem['main_name'] }}</div>
                                    <div class="monthly-org-main-meta">
                                        @if($mainItem['main_number'] !== '')
                                            {{ $mainItem['main_number'] }}
                                        @else
                                            Main meter #{{ (int) $mainItem['main_id'] }}
                                        @endif
                                    </div>
                                </div>
                                <div class="monthly-org-head-right">
                                    @php
                                        $mainSourceLabel = (string) ($mainItem['source_label'] ?? 'No Data');
                                    @endphp
                                    <span class="monthly-chip">
                                        Main ({{ $mainSourceLabel }}): {{ number_format((float) ($mainItem['main_total_kwh'] ?? 0), 2) }} kWh
                                    </span>
                                    @if($mainSourceLabel === 'Sensor' && (float) ($mainItem['manual_total_kwh'] ?? 0) > 0)
                                        <span class="monthly-chip" style="background:#f8fafc;border-color:#cbd5e1;color:#475569;">
                                            Manual fallback available
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="monthly-card">
        <div class="monthly-card-body">
            <div class="monthly-filters-head">
                <span class="monthly-chip">Per Meter Summary</span>
                <span class="monthly-table-subtitle">{{ $meterSummaryCards->count() }} meter(s) in selected scope</span>
            </div>
            @if($meterSummaryCards->isEmpty())
                <div style="border:1px dashed #cbd5e1;border-radius:12px;padding:14px;color:#64748b;font-weight:700;">
                    No meter summary available for the selected filter.
                </div>
            @else
                <div class="monthly-summary">
                    @foreach($meterSummaryCards as $meterSummary)
                        <div class="item">
                            <div class="label">{{ $meterSummary['meter_name'] }}</div>
                            <div class="meta">
                                @if($meterSummary['meter_number'] !== '')
                                    {{ $meterSummary['meter_number'] }} |
                                @endif
                                {{ number_format((int) $meterSummary['record_count']) }} record(s)
                            </div>
                            <div class="value">{{ number_format((float) $meterSummary['total_kwh'], 2) }} kWh</div>
                            <div class="meta">PHP {{ number_format((float) $meterSummary['total_cost'], 2) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
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
                <span class="monthly-chip">Total kWh: {{ number_format($tableActualKwhTotal, 2) }}</span>
                <span class="monthly-chip is-success">Total Cost: PHP {{ number_format($tableCostTotal, 2) }}</span>
                <a href="{{ route('facilities.monthly-records.archive', $facility->id) }}" 
                   style="display:inline-flex;align-items:center;gap:6px;background:#dc2626;color:#fff;text-decoration:none;padding:8px 14px;border-radius:8px;font-size:0.875rem;font-weight:600;transition:background 0.2s;"
                   title="View archived records">
                    <i class="fa fa-archive"></i> Archive
                </a>
            </div>
        </div>

        <div class="monthly-record-table-filter">
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
                    <select id="table_meter_filter" name="table_meter_id">
                        <option value="0" @selected($tableFilterMeterId === 0)>All Main Meters</option>
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
                        <th>Year</th>
                        <th>Month</th>
                        <th>Record Scope</th>
                        <th>kWh Used</th>
                        <th>Baseline kWh (Main Meter)</th>
                        <th>Change vs Baseline</th>
                        <th>Alert (Baseline)</th>
                        <th>Rate (PHP/kWh)</th>
                        <th>Energy Cost (PHP)</th>
                        <th>Bill Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tableRecords as $record)
                        @php
                            $rate = \App\Support\EnergyCost::ratePerKwh($record);
                            $cost = \App\Support\EnergyCost::cost($record, $rate);

                            $scopeLabelRow = 'MAIN';
                            $scopeNameRow = (string) ($record->meter->meter_name ?? 'Main Meter');
                            $scopeBg = '#eff6ff';
                            $scopeColor = '#1d4ed8';

                            $actualRow = is_numeric($record->actual_kwh) ? (float) $record->actual_kwh : null;
                            $baselineRow = ($record->meter && is_numeric($record->meter->baseline_kwh))
                                ? (float) $record->meter->baseline_kwh
                                : null;

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
                                    $changeBg = '#dcfce7';
                                    $changeColor = '#166534';
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
                        @endphp
                        <tr>
                            <td>{{ $record->year }}</td>
                            <td>{{ $monthLabels[(int) ($record->month ?? 0)] ?? $record->month }}</td>
                            <td>
                                <div class="monthly-scope-cell">
                                    <span class="scope-pill" style="background:{{ $scopeBg }};color:{{ $scopeColor }};">{{ $scopeLabelRow }}</span>
                                    <span class="monthly-meter-name">{{ $scopeNameRow }}</span>
                                </div>
                            </td>
                            <td class="monthly-number">{{ $record->actual_kwh !== null ? number_format((float) $record->actual_kwh, 2) : '-' }}</td>
                            <td class="monthly-muted-number">{{ $baselineRow !== null ? number_format($baselineRow, 2) : '-' }}</td>
                            <td>
                                <span class="monthly-status-pill" style="background:{{ $changeBg }};color:{{ $changeColor }};">
                                    {{ $changeLabel }}
                                </span>
                            </td>
                            <td>
                                <span class="monthly-status-pill" style="background:{{ $baselineAlertBg }};color:{{ $baselineAlertColor }};">
                                    {{ $baselineAlertLabel }}
                                </span>
                            </td>
                            <td class="monthly-muted-number">{{ number_format($rate, 2) }}</td>
                            <td class="monthly-cost">{{ number_format($cost, 2) }}</td>
                            <td>
                                @if($billImageUrl)
                                    <a href="{{ $billImageUrl }}" target="_blank" rel="noopener" class="monthly-bill-thumb">
                                        <img src="{{ $billImageUrl }}" alt="Bill Image">
                                    </a>
                                @else
                                    <span class="monthly-empty-mark">-</span>
                                @endif
                            </td>
                            <td>
                                <form id="deleteMonthlyRecordForm-{{ $record->id }}"
                                      action="{{ route('energy-records.delete', ['facility' => $facility->id, 'record' => $record->id]) }}"
                                      method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            title="Delete Record"
                                            class="monthly-delete-btn"
                                            onclick="openDeleteMonthlyRecordModal({{ $record->id }}, @js($monthLabels[(int) ($record->month ?? 0)] ?? ''), {{ (int) $record->year }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding:16px;color:#64748b;font-weight:700;">
                                @if($tableFilterApplied)
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

<div id="addModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card">
        <button type="button" onclick="closeAddModal()" class="monthly-modal-close">&times;</button>
        <h2 class="monthly-modal-title">Add Monthly Record</h2>
        <div class="monthly-modal-subtitle">
            Enter monthly usage based on <strong>{{ $billingSourceLabel }}</strong> bill. Cost is auto-computed.
        </div>

        <form id="addMonthlyRecordForm" method="POST" action="{{ route('energy-records.store', ['facility' => $facility->id]) }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;">
            @csrf

            <div class="monthly-field">
                <label for="add_date">Billing Date</label>
                <input type="date" id="add_date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>

            <div class="monthly-field">
                <label for="add_meter_id">Record Type</label>
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
                    <div style="font-size:.82rem;color:#0f172a;font-weight:700;">
                        Suggested default: {{ $primaryBillingMeter->meter_name }}{{ $primaryBillingMeter->meter_number ? ' (' . $primaryBillingMeter->meter_number . ')' : '' }}
                    </div>
                @endif
                @if($meterOptions->isEmpty())
                    <div style="font-size:.82rem;color:#b91c1c;font-weight:700;">No approved Main Meter available. Approve a meter first in Energy Profile.</div>
                @endif
            </div>

            <div class="monthly-pair-grid">
                <div class="monthly-field">
                    <label for="add_actual_kwh">Current Consumption (kWh)</label>
                    <input type="number" step="0.01" id="add_actual_kwh" name="actual_kwh" value="{{ old('actual_kwh') }}" required>
                </div>
                <div class="monthly-field">
                    <label for="add_rate_per_kwh">Rate (PHP/kWh)</label>
                    <input type="number" step="0.01" id="add_rate_per_kwh" name="rate_per_kwh" value="{{ old('rate_per_kwh', '12.00') }}" required>
                </div>
            </div>

            <div class="monthly-field">
                <label for="add_energy_cost">Auto-computed Cost (PHP)</label>
                <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" readonly>
            </div>

            <div class="monthly-field">
                <label for="add_bill_image">Bill Image (Optional)</label>
                <input type="file" id="add_bill_image" name="bill_image" accept="image/*">
            </div>

            <div class="monthly-modal-actions">
                <button id="addMonthlyRecordSaveBtn" type="submit" class="monthly-modal-btn primary" @disabled($meterOptions->isEmpty())>Save</button>
                <button type="button" onclick="closeAddModal()" class="monthly-modal-btn neutral">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteMonthlyRecordModal" class="monthly-modal-overlay">
    <div class="monthly-modal-card compact">
        <button type="button" onclick="closeDeleteMonthlyRecordModal()" class="monthly-modal-close">&times;</button>
        <h3 class="monthly-modal-title danger">Delete Monthly Record</h3>
        <div id="deleteMonthlyRecordText" style="margin-bottom:16px;color:#334155;font-size:.95rem;"></div>
        <div class="monthly-modal-actions">
            <button type="button" onclick="closeDeleteMonthlyRecordModal()" class="monthly-modal-btn neutral">Cancel</button>
            <button id="confirmDeleteMonthlyRecordBtn" type="button" class="monthly-modal-btn danger">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteMonthlyRecordId = null;

function openAddModal() {
    const modal = document.getElementById('addModal');
    if (!modal) return;
    modal.style.display = 'flex';
    computeEnergyCost();
    syncAddSaveButtonState();
}

function closeAddModal() {
    const modal = document.getElementById('addModal');
    if (!modal) return;
    modal.style.display = 'none';
}

function computeEnergyCost() {
    const kwhInput = document.getElementById('add_actual_kwh');
    const rateInput = document.getElementById('add_rate_per_kwh');
    const costInput = document.getElementById('add_energy_cost');
    if (!kwhInput || !rateInput || !costInput) return;

    const kwh = parseFloat(kwhInput.value) || 0;
    const rate = parseFloat(rateInput.value) || 0;
    const cost = kwh * rate;
    costInput.value = cost > 0 ? cost.toFixed(2) : '';
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
    if (text) text.textContent = `Are you sure you want to delete the record for ${monthName} ${year}?`;
    if (modal) modal.style.display = 'flex';
}

function closeDeleteMonthlyRecordModal() {
    deleteMonthlyRecordId = null;
    const modal = document.getElementById('deleteMonthlyRecordModal');
    if (modal) modal.style.display = 'none';
}

document.getElementById('confirmDeleteMonthlyRecordBtn')?.addEventListener('click', function () {
    if (!deleteMonthlyRecordId) return;
    const form = document.getElementById(`deleteMonthlyRecordForm-${deleteMonthlyRecordId}`);
    if (form) form.submit();
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

    function syncSummaryMonthState() {
        if (!summaryModeSelect || !summaryMonthSelect) return;
        summaryMonthSelect.disabled = summaryModeSelect.value === 'year';
    }

    summaryModeSelect?.addEventListener('change', syncSummaryMonthState);
    syncSummaryMonthState();
    syncAddSaveButtonState();
});

@if($errors->has('duplicate'))
window.addEventListener('DOMContentLoaded', function () {
    openAddModal();
});
@endif
</script>
@endsection
