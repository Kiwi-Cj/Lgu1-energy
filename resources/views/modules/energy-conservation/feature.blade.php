@extends('layouts.qc-admin')
@section('title', $feature['title'] ?? 'Energy Conservation Feature')

@section('content')
@php
    $feature = $feature ?? [];
    $overview = $overview ?? [];
    $facilities = $overview['facilities'] ?? collect();
    $rows = $overview['rows'] ?? collect();
    $totals = $overview['totals'] ?? [];
    $topFacility = $overview['topFacility'] ?? null;
    $averageDeviation = $overview['averageDeviation'] ?? null;
    $latestContactSuggestions = $overview['latestContactSuggestions'] ?? collect();
    $energyTips = $energyTips ?? collect();
    $canReviewTips = (bool) ($canReviewTips ?? false);
    $selectedFacility = $selectedFacility ?? null;
    $selectedFacilityId = (int) ($selectedFacilityId ?? 0);
    $featureStatus = $feature['status'] ?? 'enabled';
    $featureBadge = $feature['badge'] ?? 'Enabled';
@endphp

<style>
    .feature-shell {
        width: 100%;
        margin: 0;
        padding: 28px 34px 36px;
        border-radius: 24px;
        background: linear-gradient(135deg, #ffffff, #eff6ff);
        border: 1px solid #dbe4f0;
        box-shadow: 0 12px 40px rgba(37, 99, 235, .12);
        display: grid;
        gap: 18px;
    }
    .feature-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .feature-kicker {
        color: #2563eb;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .feature-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.5rem, 2.2vw, 2.25rem);
        font-weight: 900;
    }
    .feature-desc {
        margin-top: 8px;
        max-width: 900px;
        color: #475569;
        line-height: 1.5;
    }
    .feature-status {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .feature-status.enabled { background: #dcfce7; color: #166534; }
    .feature-status.coming-soon { background: #fef3c7; color: #92400e; }
    .feature-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(300px, .85fr);
        gap: 14px;
        align-items: start;
    }
    .feature-grid.single-panel { grid-template-columns: minmax(0, 1fr); }
    .feature-grid.checklist-layout { grid-template-columns: minmax(0, 1fr); }
    .feature-grid.goal-layout { grid-template-columns: minmax(0, 1fr); }
    .feature-shell.checklist-page {
        max-width: 1180px;
        margin-inline: auto;
        padding: 28px;
        background: #f8fafc;
        box-shadow: 0 10px 32px rgba(15, 23, 42, .08);
    }
    .checklist-page .feature-head { align-items: center; }
    .checklist-page .feature-desc { margin-top: 5px; }
    .checklist-panel .panel-head { padding: 18px 22px; }
    .checklist-panel .panel-body { padding: 22px; gap: 18px; }
    .feature-shell.goal-page {
        max-width: 1180px;
        margin-inline: auto;
        padding: 28px;
        background: #f8fafc;
        box-shadow: 0 10px 32px rgba(15, 23, 42, .08);
    }
    .goal-panel .panel-head { padding: 18px 22px; }
    .goal-panel .panel-body { padding: 22px; gap: 18px; }
    .goal-panel .tip-filter-card { grid-template-columns: minmax(260px, 1fr); }
    .goal-panel .stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .goal-period-form { display: flex; justify-content: flex-end; }
    .goal-period-form .field { width: min(100%, 220px); }
    .goal-toolbar { display: flex; align-items: end; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
    .goal-modal { width: min(820px, calc(100vw - 32px)); max-width: none; height: min(720px, calc(100dvh - 48px)); max-height: none; padding: 0; border: 0; border-radius: 18px; background: #fff; box-shadow: 0 24px 70px rgba(15, 23, 42, .3); overflow: hidden; }
    .goal-modal[open] { position: fixed; inset: 50% auto auto 50%; margin: 0; transform: translate(-50%, -50%); }
    .goal-modal::backdrop { background: rgba(15, 23, 42, .58); backdrop-filter: blur(3px); }
    .goal-modal-form { display: grid; grid-template-rows: auto minmax(0, 1fr) auto; height: 100%; }
    .goal-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #fff; }
    .goal-modal-body { padding: 18px 20px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #94a3b8 transparent; }
    .goal-modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #fff; }
    .goal-modal-close { width: 38px; height: 38px; padding: 0; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; color: #475569; cursor: pointer; }
    .goal-modal .goal-create-form { padding: 0; border: 0; background: transparent; }
    .goal-modal .goal-create-grid { gap: 11px 14px; }
    .goal-modal .field label { margin-bottom: 5px; }
    .goal-modal .field input,
    .goal-modal .field select { min-height: 44px; padding-block: 8px; }
    .goal-modal .field textarea { min-height: 72px; }
    .goal-modal .feature-point { padding: 10px 12px; font-size: .82rem; }
    .goal-create-form { padding: 20px; border: 1px solid #dbe4f0; border-radius: 16px; background: #f8fafc; }
    .goal-create-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .goal-list { display: grid; gap: 16px; }
    .goal-overview { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr); gap: 18px; }
    .goal-card { padding: 22px; border: 1px solid #dbe4f0; border-radius: 16px; background: #fff; }
    .goal-card-title { margin: 0; color: #0f172a; font-size: 1.2rem; font-weight: 900; }
    .goal-card-subtitle { margin-top: 5px; color: #64748b; font-size: .86rem; }
    .goal-status { display: inline-flex; align-items: center; gap: 7px; padding: 6px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: .72rem; font-weight: 900; text-transform: uppercase; }
    .goal-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #16a34a; }
    .goal-status.no-data { background: #e2e8f0; color: #475569; }
    .goal-status.no-data::before { background: #94a3b8; }
    .goal-status.failed { background: #fee2e2; color: #991b1b; }
    .goal-status.failed::before { background: #dc2626; }
    .goal-status.expired { background: #fef3c7; color: #92400e; }
    .goal-status.expired::before { background: #d97706; }
    .goal-status.upcoming { background: #dbeafe; color: #1e40af; }
    .goal-status.upcoming::before { background: #2563eb; }
    .goal-status.at-risk { background: #ffedd5; color: #9a3412; }
    .goal-status.at-risk::before { background: #ea580c; }
    .goal-heading-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .goal-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-top: 22px; }
    .goal-metric { padding: 14px; border-radius: 13px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .goal-metric-label { color: #64748b; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
    .goal-metric-value { margin-top: 5px; color: #0f172a; font-size: 1.15rem; font-weight: 900; }
    .goal-progress-head { display: flex; justify-content: space-between; gap: 12px; margin-top: 22px; color: #334155; font-weight: 800; }
    .goal-progress-track { height: 12px; margin-top: 9px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
    .goal-progress-bar { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2563eb, #14b8a6); }
    .goal-duration { margin-top: 12px; color: #64748b; font-size: .83rem; }
    .goal-recommendations { display: grid; gap: 11px; margin-top: 18px; }
    .goal-recommendation { display: flex; align-items: flex-start; gap: 10px; color: #334155; line-height: 1.4; }
    .goal-recommendation i { margin-top: 3px; color: #2563eb; }
    .goal-tips { margin-top: 18px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
    .goal-tips-title { color: #334155; font-size: .82rem; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
    .goal-tips-list { display: grid; gap: 8px; margin-top: 10px; }
    .goal-tip { display: flex; gap: 9px; align-items: flex-start; color: #475569; font-size: .86rem; line-height: 1.4; }
    .goal-tip i { margin-top: 3px; color: #f59e0b; }
    .goal-achievement { margin-top: 18px; padding: 16px; border: 1px solid #86efac; border-radius: 14px; background: #f0fdf4; }
    .goal-achievement-title { color: #166534; font-size: 1rem; font-weight: 900; }
    .goal-achievement-metrics { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 9px; color: #166534; font-size: .86rem; }
    .goal-accountability { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 10px; color: #64748b; font-size: .8rem; }
    .goal-action-plan { margin-top: 16px; padding: 13px 14px; border-radius: 12px; background: #f8fafc; color: #475569; font-size: .86rem; line-height: 1.5; white-space: pre-line; }
    .goal-data-source { margin-top: 12px; color: #64748b; font-size: .74rem; }
    .panel {
        background: #fff;
        border: 1px solid #dbe4f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
    }
    .panel.tips-panel {
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .panel.tips-panel > .panel-head {
        padding: 4px 4px 18px;
        border-bottom: 1px solid #dbe4f0;
    }
    .panel.tips-panel > .panel-body {
        padding: 20px 4px 4px;
        gap: 16px;
    }
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
    }
    .panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 900;
    }
    .panel-note {
        color: #64748b;
        font-size: .8rem;
        font-weight: 600;
    }
    .panel-body {
        padding: 18px;
        display: grid;
        gap: 14px;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1e40af;
        font-weight: 900;
        text-decoration: none;
    }
    .back-link:hover { text-decoration: underline; }
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 10px;
    }
    .stat-card {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid #dbe4f0;
        background: #f8fbff;
    }
    .stat-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .stat-value {
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
        margin-top: 5px;
    }
    .stat-sub {
        color: #64748b;
        font-size: .82rem;
        margin-top: 3px;
        line-height: 1.35;
    }
    .form-grid {
        display: grid;
        gap: 12px;
    }
    .tip-filter-card {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) auto;
        align-items: end;
        gap: 12px;
        padding: 16px 18px;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #f8fbff;
    }
    .tip-filter-card .action-row { align-self: end; justify-content: flex-end; }
    .tip-filter-card .action-row .btn-main { min-height: 44px; justify-content: center; }
    .tip-filter-card .help-text { margin-bottom: 0; }
    .field label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: .78rem;
        font-weight: 800;
    }
    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fff;
        color: #0f172a;
    }
    .field textarea {
        min-height: 120px;
        resize: vertical;
    }
    .help-text {
        margin-top: 6px;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.35;
    }
    .action-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-main {
        border: 0;
        border-radius: 12px;
        background: #2563eb;
        color: #fff;
        padding: 10px 14px;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .btn-secondary {
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #1e40af;
    }
    .feature-list {
        display: grid;
        gap: 10px;
    }
    .feature-point {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #f8fbff;
        color: #334155;
        line-height: 1.45;
    }
    .feature-point i {
        color: #2563eb;
        margin-top: 2px;
    }
    .energy-tip-list { display: grid; gap: 12px; }
    .energy-tip-card {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid #dbe4f0;
        border-left: 4px solid #3b82f6;
        border-radius: 14px;
        background: #f8fbff;
    }
    .energy-tip-card.critical { border-left-color: #dc2626; background: #fff7f7; }
    .energy-tip-card.warning { border-left-color: #f97316; background: #fffaf5; }
    .energy-tip-card.watch { border-left-color: #eab308; background: #fffdf2; }
    .energy-tip-card.success { border-left-color: #16a34a; background: #f6fff8; }
    .energy-tip-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eaf2ff;
        color: #2563eb;
    }
    .energy-tip-card.critical .energy-tip-icon { background: #fee2e2; color: #dc2626; }
    .energy-tip-card.warning .energy-tip-icon { background: #ffedd5; color: #ea580c; }
    .energy-tip-card.watch .energy-tip-icon { background: #fef9c3; color: #a16207; }
    .energy-tip-card.success .energy-tip-icon { background: #dcfce7; color: #15803d; }
    .energy-tip-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .energy-tip-title { color: #0f172a; font-weight: 900; line-height: 1.3; }
    .energy-tip-priority {
        flex: 0 0 auto;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: .66rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .energy-tip-message { margin-top: 4px; color: #475569; font-size: .84rem; line-height: 1.45; }
    .energy-tip-metric { margin-top: 6px; color: #1e40af; font-size: .75rem; font-weight: 800; }
    .tip-review-status { margin-top: 10px; font-size: .75rem; font-weight: 800; color: #64748b; }
    .tip-approved-text { margin-top: 10px; padding: 11px 12px; border-radius: 10px; background: #ecfdf5; color: #166534; line-height: 1.45; }
    .tip-review-form { display: grid; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #dbe4f0; }
    .tip-review-form textarea,
    .tip-review-form input {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 9px 10px;
        background: #fff;
        color: #0f172a;
    }
    .tip-review-form textarea { min-height: 68px; resize: vertical; }
    .tip-review-disclosure { margin-top: 9px; border-top: 1px solid #dbe4f0; }
    .tip-review-disclosure summary {
        width: fit-content;
        margin-top: 9px;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 800;
        cursor: pointer;
        list-style: none;
        user-select: none;
    }
    .tip-review-disclosure summary::-webkit-details-marker { display: none; }
    .tip-review-disclosure summary::before { content: '\f044'; margin-right: 7px; font-family: 'Font Awesome 6 Free'; font-weight: 900; }
    .tip-review-disclosure[open] summary::before { content: '\f077'; }
    .tip-review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .tip-review-field label { display: block; margin-bottom: 5px; color: #64748b; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
    .tip-review-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .tip-action { border: 0; border-radius: 9px; padding: 8px 11px; font-size: .76rem; font-weight: 800; cursor: pointer; }
    .tip-action.save { background: #e2e8f0; color: #334155; }
    .tip-action.approve { background: #16a34a; color: #fff; }
    .tip-action.dismiss { background: #fee2e2; color: #b91c1c; }
    .table-wrap {
        overflow-x: auto;
    }
    .simple-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 680px;
    }
    .simple-table th,
    .simple-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        text-align: left;
        vertical-align: top;
    }
    .simple-table th {
        color: #475569;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #f8fafc;
    }
    .pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eef2ff;
        color: #1e40af;
        font-size: .7rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .suggestion-list {
        display: grid;
        gap: 10px;
    }
    .suggestion-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px;
        background: #f8fbff;
    }
    .suggestion-name {
        color: #0f172a;
        font-weight: 900;
        margin-bottom: 3px;
    }
    .suggestion-meta {
        color: #64748b;
        font-size: .78rem;
        margin-bottom: 6px;
    }
    .suggestion-body {
        color: #334155;
        font-size: .9rem;
        line-height: 1.45;
    }
    .checklist-toolbar { display: grid; grid-template-columns: minmax(240px, 1fr) 200px auto; gap: 12px; align-items: end; }
    .checklist-filter { grid-template-columns: minmax(240px, 1fr) 220px; }
    .checklist-filter { padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; }
    .checklist-progress { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-weight: 800; }
    .checklist-task-form { padding: 16px; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; }
    .checklist-section { display: grid; gap: 9px; }
    .checklist-section-title { color: #334155; font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
    .checklist-item { display: flex; align-items: flex-start; gap: 11px; padding: 13px; border: 1px solid #e2e8f0; border-radius: 13px; background: #f8fafc; cursor: pointer; }
    .checklist-item:has(input:checked) { border-color: #86efac; background: #f0fdf4; }
    .checklist-item input { width: 20px; height: 20px; margin-top: 1px; accent-color: #16a34a; }
    .checklist-item-text { color: #1e293b; line-height: 1.4; }
    .checklist-item-meta { margin-top: 3px; color: #64748b; font-size: .73rem; }
    body.dark-mode .feature-shell,
    body.dark-mode .panel,
    body.dark-mode .stat-card,
    body.dark-mode .field input,
    body.dark-mode .field select,
    body.dark-mode .field textarea,
    body.dark-mode .feature-point,
    body.dark-mode .suggestion-item {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .panel.tips-panel { background: transparent; border-color: transparent; box-shadow: none; }
    body.dark-mode .panel.tips-panel > .panel-head { border-color: #334155; }
    body.dark-mode .feature-shell.checklist-page,
    body.dark-mode .feature-shell.goal-page,
    body.dark-mode .checklist-task-form { background: #0f172a; border-color: #334155; }
    body.dark-mode .goal-card,
    body.dark-mode .goal-metric,
    body.dark-mode .goal-create-form { background: #111827; border-color: #334155; }
    body.dark-mode .goal-card-title,
    body.dark-mode .goal-metric-value { color: #f8fafc; }
    body.dark-mode .goal-card-subtitle,
    body.dark-mode .goal-duration,
    body.dark-mode .goal-recommendation,
    body.dark-mode .goal-progress-head { color: #cbd5e1; }
    body.dark-mode .goal-tips { border-color: #334155; }
    body.dark-mode .goal-tips-title,
    body.dark-mode .goal-tip { color: #cbd5e1; }
    body.dark-mode .goal-achievement { background: #052e1a; border-color: #166534; }
    body.dark-mode .goal-achievement-title,
    body.dark-mode .goal-achievement-metrics { color: #bbf7d0; }
    body.dark-mode .goal-action-plan { background: #0f172a; color: #cbd5e1; }
    body.dark-mode .goal-modal,
    body.dark-mode .goal-modal-head,
    body.dark-mode .goal-modal-footer { background: #0f172a; color: #f8fafc; }
    body.dark-mode .goal-modal-head,
    body.dark-mode .goal-modal-footer,
    body.dark-mode .goal-modal-close { border-color: #334155; }
    body.dark-mode .goal-modal-close { background: #111827; color: #cbd5e1; }
    body.dark-mode .tip-filter-card { background: #111827; border-color: #334155; }
    body.dark-mode .feature-title,
    body.dark-mode .panel-title,
    body.dark-mode .stat-value,
    body.dark-mode .suggestion-name {
        color: #f8fafc;
    }
    body.dark-mode .energy-tip-card { background: #111827; border-color: #334155; }
    body.dark-mode .energy-tip-card.critical,
    body.dark-mode .energy-tip-card.warning,
    body.dark-mode .energy-tip-card.watch,
    body.dark-mode .energy-tip-card.success { background: #111827; }
    body.dark-mode .energy-tip-title { color: #f8fafc; }
    body.dark-mode .energy-tip-message { color: #cbd5e1; }
    body.dark-mode .energy-tip-priority { background: #1f2937; color: #cbd5e1; }
    body.dark-mode .energy-tip-metric { color: #93c5fd; }
    body.dark-mode .tip-approved-text { background: #052e1a; color: #bbf7d0; }
    body.dark-mode .tip-review-form { border-color: #334155; }
    body.dark-mode .tip-review-disclosure { border-color: #334155; }
    body.dark-mode .tip-review-disclosure summary { color: #93c5fd; }
    body.dark-mode .tip-review-form textarea,
    body.dark-mode .tip-review-form input { background: #0b1220; color: #e2e8f0; border-color: #334155; }
    body.dark-mode .feature-desc,
    body.dark-mode .panel-note,
    body.dark-mode .stat-label,
    body.dark-mode .stat-sub,
    body.dark-mode .help-text,
    body.dark-mode .suggestion-meta,
    body.dark-mode .suggestion-body,
    body.dark-mode .feature-point,
    body.dark-mode .field label {
        color: #cbd5e1;
    }
    body.dark-mode .simple-table th {
        background: #111827;
        color: #cbd5e1;
    }
    @media (max-width: 960px) {
        .feature-grid {
            grid-template-columns: 1fr;
        }
        .goal-panel .stat-grid { grid-template-columns: 1fr; }
        .goal-overview { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .feature-shell {
            padding: 18px;
        }
        .energy-tip-card { grid-template-columns: 32px minmax(0, 1fr); padding: 11px; gap: 9px; }
        .energy-tip-icon { width: 32px; height: 32px; }
        .energy-tip-top { display: grid; gap: 7px; }
        .energy-tip-priority { justify-self: start; }
        .tip-review-grid { grid-template-columns: 1fr; }
        .tip-review-actions .tip-action { flex: 1; }
        .tip-filter-card { grid-template-columns: 1fr; padding: 12px; }
        .tip-filter-card .action-row { display: grid; grid-template-columns: 1fr 1fr; }
        .tip-filter-card .btn-main { justify-content: center; }
        .panel.tips-panel > .panel-head { padding-inline: 0; }
        .panel.tips-panel > .panel-body { padding-inline: 0; }
        .checklist-toolbar { grid-template-columns: 1fr; }
        .checklist-filter { grid-template-columns: 1fr; }
        .goal-metrics { grid-template-columns: 1fr; }
        .goal-create-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="feature-shell{{ $featureSlug === 'daily-checklist' ? ' checklist-page' : '' }}{{ $featureSlug === 'conservation-goals' ? ' goal-page' : '' }}">
    <div class="feature-head">
        <div>
            @if(!in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
                <div class="feature-kicker">Energy Conservation Feature</div>
            @endif
            <h1 class="feature-title">{{ $feature['title'] ?? 'Feature' }}</h1>
            <div class="feature-desc">{{ $feature['description'] ?? '' }}</div>
        </div>
        @if(in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
            <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                <i class="fa-solid fa-arrow-left"></i> Back to Overview
            </a>
        @else
            <span class="feature-status {{ $featureStatus }}">{{ $featureBadge }}</span>
        @endif
    </div>

    <div class="feature-grid{{ $featureSlug === 'energy-saving-tips' ? ' single-panel' : '' }}{{ $featureSlug === 'daily-checklist' ? ' checklist-layout' : '' }}{{ $featureSlug === 'conservation-goals' ? ' goal-layout' : '' }}">
        <section class="panel{{ $featureSlug === 'energy-saving-tips' ? ' tips-panel' : '' }}{{ $featureSlug === 'daily-checklist' ? ' checklist-panel' : '' }}{{ $featureSlug === 'conservation-goals' ? ' goal-panel' : '' }}">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">{{ $featureSlug === 'energy-saving-tips' ? 'Facility Recommendations' : ($featureSlug === 'daily-checklist' ? 'Checklist' : ($featureSlug === 'conservation-goals' ? 'Goal Planning' : 'Main Content')) }}</h2>
                    @if(!in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
                        <div class="panel-note">{{ $featureSlug === 'energy-saving-tips' ? 'Generated from monthly consumption, baseline, and deviation data.' : 'Actual content and forms tied to current app data.' }}</div>
                    @endif
                </div>
                @if(!in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
                    <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                @endif
            </div>
            <div class="panel-body">
                @if($featureSlug === 'daily-checklist')
                    @if(session('success'))
                        <div class="checklist-progress"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
                    @endif
                    <form method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => 'daily-checklist']) }}" class="checklist-toolbar checklist-filter">
                        <div class="field">
                            <label>Facility</label>
                            <select name="facility_id" required onchange="this.form.submit()">
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" @selected((int) $selectedFacilityId === (int) $facility->id)>{{ $facility->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Checklist Date</label>
                            <input type="date" name="date" value="{{ $checklistDate }}" required onchange="this.form.submit()">
                        </div>
                    </form>

                    @php
                        $completedChecklistCount = $dailyChecklist->filter(fn ($item) => (bool) ($item['record']?->is_completed ?? false))->count();
                        $checklistTotal = $dailyChecklist->count();
                    @endphp
                    @if($checklistTotal > 0)
                        <div class="checklist-progress">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ $completedChecklistCount }} of {{ $checklistTotal }} tasks completed for {{ $selectedFacility?->name ?? 'selected facility' }}</span>
                        </div>
                    @endif

                    @if($canManageChecklistTasks && $selectedFacility)
                        <form method="POST" action="{{ route('modules.energy-conservation.daily-checklist.tasks.store') }}" class="form-grid checklist-task-form">
                            @csrf
                            <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                            <input type="hidden" name="return_date" value="{{ $checklistDate }}">
                            <div class="checklist-section-title">Add a task for {{ $selectedFacility->name }}</div>
                            <div class="checklist-toolbar">
                                <div class="field"><label>Task instruction</label><input type="text" name="task_label" maxlength="255" required placeholder="Example: Verify unused lights are switched off."></div>
                                <div class="field"><label>Routine</label><select name="period" required><option value="opening">Opening</option><option value="closing">Closing</option></select></div>
                                <button type="submit" class="btn-main"><i class="fa-solid fa-plus"></i> Add Task</button>
                            </div>
                        </form>
                    @endif

                    @if($selectedFacility && $dailyChecklist->isNotEmpty())
                        @if($canCompleteChecklist)
                            <form method="POST" action="{{ route('modules.energy-conservation.daily-checklist.update') }}" class="form-grid">
                                @csrf
                                <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                                <input type="hidden" name="checklist_date" value="{{ $checklistDate }}">
                        @else
                            <div class="form-grid">
                        @endif
                            @foreach(['opening' => 'Opening Routine', 'closing' => 'Closing Routine'] as $checklistPeriod => $checklistTitle)
                                <div class="checklist-section">
                                    <div class="checklist-section-title">{{ $checklistTitle }}</div>
                                    @foreach($dailyChecklist->where('period', $checklistPeriod) as $item)
                                        @php $record = $item['record']; @endphp
                                        <div class="checklist-item">
                                            <input type="checkbox" name="tasks[{{ $item['key'] }}]" value="1" @checked((bool) ($record?->is_completed ?? false)) @disabled(!$canCompleteChecklist) onchange="this.form.requestSubmit()">
                                            <span>
                                                <span class="checklist-item-text">{{ $item['label'] }}</span>
                                                @if($record?->completed_at)
                                                    <span class="checklist-item-meta">Completed {{ $record->completed_at->format('M d, Y h:i A') }} by {{ $record->completedBy?->full_name ?? $record->completedBy?->username ?? 'user' }}</span>
                                                @endif
                                            </span>
                                            @if($canManageChecklistTasks)
                                                <form method="POST" action="{{ route('modules.energy-conservation.daily-checklist.tasks.destroy', $item['id']) }}" style="margin-left:auto" onsubmit="return confirm('Remove this checklist task?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-main btn-secondary" title="Remove task"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            @if($canCompleteChecklist)
                                <div class="action-row">
                                    <span class="help-text">Changes are saved automatically when a task is checked or unchecked.</span>
                                    <button type="submit" class="btn-main"><i class="fa-solid fa-floppy-disk"></i> Save Checklist</button>
                                </div>
                                </form>
                            @else
                                <div class="help-text">Task management only. Daily completion is performed by assigned staff.</div>
                                </div>
                            @endif
                    @else
                        <div class="feature-point"><i class="fa-solid fa-circle-info"></i><span>
                            @if(!$selectedFacility)
                                No facility is available for this checklist.
                            @elseif($canManageChecklistTasks)
                                No checklist tasks yet. Use the form above to create the first task for this facility.
                            @else
                                No checklist tasks have been assigned to this facility yet.
                            @endif
                        </span></div>
                    @endif
                @elseif($featureSlug === 'conservation-goals')
                    <div class="goal-toolbar">
                    <form class="goal-period-form" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => 'conservation-goals']) }}">
                        <div class="field">
                            <label for="goal-month">Reporting Month</label>
                            <input id="goal-month" type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()">
                        </div>
                    </form>
                    @if($canManageGoals)
                        <button class="btn-main" type="button" onclick="document.getElementById('goalCreateModal').showModal()"><i class="fa-solid fa-plus"></i> New Goal</button>
                    @endif
                    </div>

                    @if(session('success'))
                        <div class="checklist-progress"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
                    @endif

                    @if($canManageGoals)
                        <dialog class="goal-modal" id="goalCreateModal" @if($errors->any()) data-has-errors="true" @endif>
                        <form class="goal-modal-form" method="POST" action="{{ route('modules.energy-conservation.goals.store') }}">
                            @csrf
                            <div class="goal-modal-head">
                                <div><h3 class="goal-card-title">Create Conservation Goal</h3><div class="goal-card-subtitle">Set a measurable energy reduction target.</div></div>
                                <button class="goal-modal-close" type="button" onclick="this.closest('dialog').close()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="goal-modal-body">
                        <div class="form-grid goal-create-form">
                            @if($errors->any())
                                <div class="feature-point"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
                            @endif
                            <div class="goal-create-grid">
                                <div class="field"><label>Goal Name</label><input name="name" value="{{ old('name') }}" required placeholder="Reduce Monthly Electricity Consumption"></div>
                                <div class="field"><label>Facility</label><select name="facility_id"><option value="">All facilities</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}">{{ $facility->name }}</option>@endforeach</select></div>
                                <div class="field"><label>Goal Type</label><select name="goal_type" required><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option><option value="yearly">Yearly</option></select></div>
                                <div class="field"><label>Target Measurement</label><select name="target_metric" required><option value="maximum_kwh">Maximum kWh</option><option value="reduction_percent">Reduction Percentage</option><option value="cost_savings">Cost Savings (PHP)</option></select></div>
                                <div class="field"><label>Target Value</label><input type="number" min="0.01" step="0.01" name="target_value" value="{{ old('target_value') }}" required></div>
                                <div class="field"><label>Responsible Department</label><input name="responsible_department" value="{{ old('responsible_department') }}" required placeholder="Engineering Department"></div>
                                <div class="field"><label>Baseline Start Date</label><input type="date" name="baseline_start_date" value="{{ old('baseline_start_date', now()->subYear()->startOfYear()->toDateString()) }}" required></div>
                                <div class="field"><label>Baseline End Date</label><input type="date" name="baseline_end_date" value="{{ old('baseline_end_date', now()->subYear()->endOfYear()->toDateString()) }}" required></div>
                                <div class="field"><label>Start Date</label><input type="date" name="start_date" value="{{ old('start_date', now()->startOfMonth()->toDateString()) }}" required></div>
                                <div class="field"><label>End Date / Deadline</label><input type="date" name="end_date" value="{{ old('end_date', now()->endOfMonth()->toDateString()) }}" required></div>
                            </div>
                            <div class="feature-point"><i class="fa-solid fa-database"></i><span>The baseline is calculated automatically from approved main-meter records within the selected baseline period.</span></div>
                            <div class="field"><label>Goal Description</label><textarea name="description" placeholder="Describe how this goal will reduce energy consumption.">{{ old('description') }}</textarea></div>
                            <div class="field"><label>Action Plan</label><textarea name="action_plan" required placeholder="List the actions that will be implemented to achieve this goal.">{{ old('action_plan') }}</textarea></div>
                        </div>
                            </div>
                            <div class="goal-modal-footer">
                                <button class="btn-main btn-secondary" type="button" onclick="this.closest('dialog').close()">Cancel</button>
                                <button class="btn-main" type="submit"><i class="fa-solid fa-plus"></i> Create Goal</button>
                            </div>
                        </form>
                        </dialog>
                    @endif

                    <div class="goal-list">
                    @forelse($conservationGoals as $goal)
                        <section class="goal-card">
                            <div class="goal-heading-row">
                                <div>
                                    <h3 class="goal-card-title">{{ $goal->name }}</h3>
                                    <div class="goal-card-subtitle">{{ $goal->description ?: 'No description provided.' }}</div>
                                    <div class="goal-accountability">
                                        <span><i class="fa-solid fa-building"></i> {{ $goal->facility?->name ?? 'All facilities' }}</span>
                                        <span><i class="fa-solid fa-user-tie"></i> {{ $goal->responsible_department }}</span>
                                    </div>
                                </div>
                                <span class="goal-status {{ Illuminate\Support\Str::slug($goal->effective_status) }}">{{ $goal->effective_status }}</span>
                            </div>

                            <div class="goal-metrics">
                                <div class="goal-metric">
                                    <div class="goal-metric-label">Baseline</div>
                                    <div class="goal-metric-value">{{ number_format((float) $goal->baseline_value, 2) }} kWh</div>
                                </div>
                                <div class="goal-metric">
                                    <div class="goal-metric-label">Target</div>
                                    <div class="goal-metric-value">{{ $goal->target_metric === 'cost_savings' ? 'PHP ' : '' }}{{ number_format((float) $goal->target_value, 2) }}{{ $goal->target_metric === 'maximum_kwh' ? ' kWh' : ($goal->target_metric === 'reduction_percent' ? '%' : '') }}</div>
                                </div>
                                <div class="goal-metric">
                                    <div class="goal-metric-label">Current Progress</div>
                                    <div class="goal-metric-value">{{ $goal->target_metric === 'cost_savings' ? 'PHP ' : '' }}{{ number_format((float) $goal->current_value, 2) }}{{ $goal->target_metric === 'maximum_kwh' ? ' kWh' : ($goal->target_metric === 'reduction_percent' ? '%' : '') }}</div>
                                </div>
                                <div class="goal-metric">
                                    <div class="goal-metric-label">Goal Type</div>
                                    <div class="goal-metric-value">{{ ucfirst($goal->goal_type) }}</div>
                                </div>
                            </div>

                            <div class="goal-progress-head">
                                <span>Goal Progress</span>
                                <span>{{ (int) $goal->progress_percent }}%</span>
                            </div>
                            <div class="goal-progress-track" role="progressbar" aria-label="Goal progress" aria-valuenow="{{ (int) $goal->progress_percent }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="goal-progress-bar" style="width: {{ max(0, min(100, (int) $goal->progress_percent)) }}%"></div>
                            </div>
                            <div class="goal-action-plan"><strong>Action Plan</strong><br>{{ $goal->action_plan }}</div>
                            <div class="goal-tips">
                                <div class="goal-tips-title"><i class="fa-solid fa-lightbulb"></i> Energy Saving Tips</div>
                                <div class="goal-tips-list">
                                    @foreach($goal->energy_tips as $tip)
                                        <div class="goal-tip"><i class="fa-solid fa-circle-check"></i><span>{{ $tip }}</span></div>
                                    @endforeach
                                </div>
                            </div>
                            @if($goal->effective_status === 'achieved')
                                <div class="goal-achievement">
                                    <div class="goal-achievement-title"><i class="fa-solid fa-trophy"></i> Goal Achieved</div>
                                    <div class="goal-achievement-metrics">
                                        <span><strong>Energy Saved:</strong> {{ number_format((float) $goal->energy_saved_kwh, 2) }} kWh</span>
                                        <span><strong>Estimated Cost Saved:</strong> PHP {{ number_format((float) $goal->estimated_cost_saved, 2) }}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="goal-data-source">
                                <i class="fa-solid fa-database"></i> {{ $goal->data_source }} · Updated {{ $goal->last_updated_label }}<br>
                                Baseline period: {{ $goal->baseline_start_date->format('M j, Y') }} - {{ $goal->baseline_end_date->format('M j, Y') }}
                            </div>
                            <div class="action-row" style="margin-top:12px;">
                                <div class="goal-duration"><i class="fa-regular fa-calendar"></i> {{ $goal->start_date->format('M j, Y') }} - {{ $goal->end_date->format('M j, Y') }}</div>
                                @if($canManageGoals)
                                    <form method="POST" action="{{ route('modules.energy-conservation.goals.destroy', $goal) }}" onsubmit="return confirm('Remove this conservation goal?')">@csrf @method('DELETE')<button class="btn-main btn-secondary" type="submit" title="Remove goal"><i class="fa-solid fa-trash"></i></button></form>
                                @endif
                            </div>
                        </section>
                    @empty
                        <div class="feature-point"><i class="fa-solid fa-bullseye"></i><span>No conservation goals yet. Create the first measurable goal above.</span></div>
                    @endforelse
                    </div>
                @elseif($featureSlug === 'suggestions-box')
                    <form class="form-grid" method="POST" action="{{ route('landing.contact.store') }}">
                        @csrf
                        <div class="stat-grid">
                            <div class="stat-card">
                                <div class="stat-label">Inbox Count</div>
                                <div class="stat-value">{{ number_format((int) ($overview['contactInboxCount'] ?? 0)) }}</div>
                                <div class="stat-sub">Messages already stored in `contact_messages`.</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Selected Month</div>
                                <div class="stat-value">{{ $selectedMonth }}</div>
                                <div class="stat-sub">Used for related energy summaries.</div>
                            </div>
                        </div>
                        <div class="field">
                            <label>Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()?->full_name ?? auth()->user()?->name ?? auth()->user()?->username ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label>Subject</label>
                            <input type="text" name="subject" value="{{ old('subject', 'Energy conservation suggestion') }}" placeholder="Short subject">
                        </div>
                        <div class="field">
                            <label>Suggestion</label>
                            <textarea name="message" required placeholder="Write your energy-saving suggestion here...">{{ old('message') }}</textarea>
                            <div class="help-text">This is saved in the existing contact inbox workflow and can be reviewed by admin.</div>
                        </div>
                        <div class="action-row">
                            <span class="help-text">Uses the current contact message database and inbox notifications.</span>
                            <button type="submit" class="btn-main"><i class="fa-solid fa-paper-plane"></i> Submit Suggestion</button>
                        </div>
                    </form>
                @elseif(in_array($featureSlug, ['energy-saving-tips', 'estimated-savings', 'ai-recommendations'], true))
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">Monitored Facilities</div>
                            <div class="stat-value">{{ number_format((int) ($totals['monitored_facilities'] ?? 0)) }}</div>
                            <div class="stat-sub">Facilities with monthly energy records.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Actual kWh</div>
                            <div class="stat-value">{{ number_format((float) ($totals['actual_kwh'] ?? 0), 2) }}</div>
                            <div class="stat-sub">Pulled from existing energy data.</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Avoidable Cost</div>
                            <div class="stat-value">PHP {{ number_format((float) ($totals['avoidable_cost'] ?? 0), 2) }}</div>
                            <div class="stat-sub">Baseline vs actual monthly comparison.</div>
                        </div>
                    </div>

                    <form class="form-grid{{ in_array($featureSlug, ['energy-saving-tips', 'conservation-goals'], true) ? ' tip-filter-card' : '' }}" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}">
                        <div class="field">
                            <label>Select Facility</label>
                            <select name="facility_id" @if($featureSlug === 'conservation-goals') onchange="this.form.submit()" @endif>
                                <option value="0">All facilities</option>
                                @foreach($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ (int) $selectedFacilityId === (int) $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}{{ $facility->type ? ' - ' . $facility->type : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if($featureSlug !== 'conservation-goals')
                                <div class="help-text">Use this to generate results from the selected facility's monthly energy record.</div>
                            @endif
                        </div>
                        <div class="action-row" @if($featureSlug === 'conservation-goals') hidden @endif>
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            <button class="btn-main" type="submit"><i class="fa-solid fa-filter"></i> Apply Filter</button>
                            <a class="btn-main btn-secondary" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                        </div>
                    </form>

                    @if($featureSlug === 'energy-saving-tips')
                        <div class="energy-tip-list">
                            @forelse($energyTips as $tip)
                                @php
                                    $review = $tip['review'] ?? null;
                                    $reviewStatus = $review?->status ?? 'for_review';
                                @endphp
                                <article class="energy-tip-card {{ $tip['tone'] ?? 'info' }}">
                                    <div class="energy-tip-icon"><i class="{{ $tip['icon'] ?? 'fa-solid fa-lightbulb' }}"></i></div>
                                    <div>
                                        <div class="energy-tip-top">
                                            <div class="energy-tip-title">{{ $tip['title'] }}</div>
                                            <span class="energy-tip-priority">{{ $tip['priority'] }}</span>
                                        </div>
                                        <div class="energy-tip-message">{{ $tip['message'] }}</div>
                                        @if(!empty($tip['metric']))
                                            <div class="energy-tip-metric"><i class="fa-solid fa-chart-simple"></i> {{ $tip['metric'] }}</div>
                                        @endif
                                        @if($reviewStatus === 'approved' && $review?->engineer_recommendation)
                                            <div class="tip-approved-text">
                                                <strong>Engineering-approved action:</strong><br>
                                                {{ $review->engineer_recommendation }}
                                            </div>
                                        @endif
                                        @if($review)
                                            <div class="tip-review-status">
                                                Status: {{ strtoupper(str_replace('_', ' ', $reviewStatus)) }}
                                                @if($review->reviewer) · Reviewed by {{ $review->reviewer->username }} @endif
                                            </div>
                                        @endif
                                        @if($canReviewTips && !empty($tip['facility_id']))
                                            <details class="tip-review-disclosure">
                                                <summary>Review recommendation</summary>
                                            <form class="tip-review-form" method="POST" action="{{ route('modules.energy-conservation.tips.review') }}">
                                                @csrf
                                                <input type="hidden" name="facility_id" value="{{ $tip['facility_id'] }}">
                                                <input type="hidden" name="period" value="{{ $selectedMonth }}">
                                                <div class="tip-review-field">
                                                    <label>Engineering Recommendation</label>
                                                    <textarea name="engineer_recommendation" placeholder="Review or replace the generated action...">{{ old('engineer_recommendation', $review?->engineer_recommendation ?? $tip['message']) }}</textarea>
                                                </div>
                                                <div class="tip-review-grid">
                                                    <div class="tip-review-field">
                                                        <label>Expected Savings (kWh)</label>
                                                        <input type="number" min="0" step="0.01" name="expected_savings_kwh" value="{{ old('expected_savings_kwh', $review?->expected_savings_kwh) }}">
                                                    </div>
                                                    <div class="tip-review-field">
                                                        <label>Target Date</label>
                                                        <input type="date" name="target_date" value="{{ old('target_date', $review?->target_date?->format('Y-m-d')) }}">
                                                    </div>
                                                </div>
                                                <div class="tip-review-actions">
                                                    <button class="tip-action save" type="submit" name="status" value="for_review">Save Draft</button>
                                                    <button class="tip-action approve" type="submit" name="status" value="approved">Approve</button>
                                                    <button class="tip-action dismiss" type="submit" name="status" value="dismissed">Dismiss</button>
                                                </div>
                                            </form>
                                            </details>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-clock"></i>
                                    <span>No Engineering-approved energy tip is available for the selected facility and month yet.</span>
                                </div>
                            @endforelse
                        </div>
                    @elseif($featureSlug === 'ai-recommendations')
                        <div class="feature-list">
                            @forelse($rows->take(5) as $row)
                                <div class="feature-point">
                                    <i class="fa-solid fa-robot"></i>
                                    <span>
                                        <strong>{{ $row['facility_name'] }}</strong>:
                                        {{ $row['recommendation'] }}
                                    </span>
                                </div>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>No monthly data yet for AI-style recommendations.</span>
                                </div>
                            @endforelse
                        </div>
                    @elseif($featureSlug === 'estimated-savings')
                        <div class="feature-list">
                            <div class="feature-point">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Estimated kWh savings are computed from baseline vs actual records already stored in `energy_records`.</span>
                            </div>
                            <div class="feature-point">
                                <i class="fa-solid fa-peso-sign"></i>
                                <span>Avoidable cost uses the same monthly data and current rate logic used by the app.</span>
                            </div>
                            <div class="feature-point">
                                <i class="fa-solid fa-leaf"></i>
                                <span>CO2 reduction can be added later as a computed field once you confirm the preferred emission factor.</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="feature-list">
                        @foreach(($feature['details'] ?? []) as $detail)
                            <div class="feature-point">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>{{ $detail }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @if(!in_array($featureSlug, ['energy-saving-tips', 'daily-checklist', 'conservation-goals'], true))
        <aside class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Live Data</h2>
                    <div class="panel-note">From current app tables and workflows.</div>
                </div>
            </div>
            <div class="panel-body">
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-label">Top Facility</div>
                        <div class="stat-value">{{ $topFacility['facility_name'] ?? 'No current data' }}</div>
                        <div class="stat-sub">{{ $topFacility ? number_format((float) ($topFacility['actual_kwh'] ?? 0), 2) . ' kWh actual' : 'Add monthly records first.' }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Average Deviation</div>
                        <div class="stat-value">{{ $averageDeviation !== null ? number_format((float) $averageDeviation, 2) . '%' : 'No data' }}</div>
                        <div class="stat-sub">Based on active facility records for {{ $selectedMonth }}.</div>
                    </div>
                </div>

                <div class="feature-list">
                    <div class="feature-point">
                        <i class="fa-solid fa-folder-open"></i>
                        <span>{{ number_format((int) ($overview['contactInboxCount'] ?? 0)) }} suggestions already stored in the system inbox.</span>
                    </div>
                    <div class="feature-point">
                        <i class="fa-solid fa-building"></i>
                        <span>{{ number_format((int) ($totals['facilities'] ?? 0)) }} facilities are available for filtering and goal previews.</span>
                    </div>
                    <div class="feature-point">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Reports can link directly to the existing energy report routes in the app.</span>
                    </div>
                </div>

                @if($featureSlug === 'suggestions-box' && $latestContactSuggestions->isNotEmpty())
                    <div>
                        <div class="panel-title" style="margin-bottom:10px;">Latest Suggestions</div>
                        <div class="suggestion-list">
                            @foreach($latestContactSuggestions as $suggestion)
                                <div class="suggestion-item">
                                    <div class="suggestion-name">{{ $suggestion->subject ?: 'Energy suggestion' }}</div>
                                    <div class="suggestion-meta">By {{ $suggestion->name }} | {{ $suggestion->created_at?->timezone('Asia/Manila')?->format('M d, Y') }}</div>
                                    <div class="suggestion-body">{{ \Illuminate\Support\Str::limit($suggestion->message, 120) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="action-row">
                    <a class="back-link" href="{{ route('modules.energy-conservation.index') }}">
                        <i class="fa-solid fa-grid-2"></i> Overview
                    </a>
                    <a class="btn-main btn-secondary" href="{{ route('modules.reports.energy') }}">
                        <i class="fa-solid fa-chart-column"></i> Open Reports
                    </a>
                </div>
            </div>
        </aside>
        @endif
    </div>
</div>
@if($featureSlug === 'conservation-goals' && $canManageGoals)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('goalCreateModal');
        if (!modal) return;

        if (modal.dataset.hasErrors === 'true') modal.showModal();
        modal.addEventListener('click', (event) => {
            const bounds = modal.getBoundingClientRect();
            const inside = event.clientX >= bounds.left && event.clientX <= bounds.right
                && event.clientY >= bounds.top && event.clientY <= bounds.bottom;
            if (!inside) modal.close();
        });
    });
</script>
@endif
@endsection
