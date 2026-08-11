@extends('layouts.qc-admin')
@section('title', $feature['title'] ?? 'Energy Conservation Feature')

@section('content')
@php
    $feature = $feature ?? [];
    $overview = $overview ?? [];
    $facilities = $overview['facilities'] ?? collect();
    $totals = $overview['totals'] ?? [];
    $energyTips = $energyTips ?? collect();
    $canReviewTips = (bool) ($canReviewTips ?? false);
    $selectedFacility = $selectedFacility ?? null;
    $selectedFacilityId = (int) ($selectedFacilityId ?? 0);
    $selectedRecordContext = $selectedRecordContext ?? null;
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
    .checklist-panel .panel-head .btn-main { min-height: 40px; padding: 9px 13px; }
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
    .summary-period {
        display: flex;
        align-items: center;
        gap: 9px;
        width: fit-content;
        padding: 8px 12px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #475569;
        font-size: .8rem;
        font-weight: 700;
    }
    .summary-period i,
    .summary-period strong {
        color: #1d4ed8;
    }
    .metric-info {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 4px;
        color: #2563eb;
        cursor: help;
    }
    .form-grid {
        display: grid;
        gap: 12px;
    }
    .tip-filter-card {
        display: grid;
        grid-template-columns: minmax(260px, 1fr) minmax(180px, 220px) auto;
        align-items: start;
        gap: 12px;
        padding: 16px 18px;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #f8fbff;
    }
    .tip-filter-card .field input,
    .tip-filter-card .field select {
        height: 52px;
    }
    .tip-filter-card .action-row {
        align-self: start;
        justify-content: flex-end;
        flex-wrap: nowrap;
        margin-top: 24px;
    }
    .tip-filter-card .action-row .btn-main {
        min-height: 52px;
        justify-content: center;
        white-space: nowrap;
    }
    .tip-filter-card .help-text { margin-bottom: 0; }
    .record-context {
        display: grid;
        grid-template-columns: minmax(220px, 1.2fr) repeat(3, minmax(150px, 1fr));
        gap: 10px;
        padding: 14px;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff6ff, #f8fbff);
    }
    .record-context-item {
        min-width: 0;
        padding: 10px 12px;
        border: 1px solid #dbeafe;
        border-radius: 11px;
        background: rgba(255, 255, 255, .8);
    }
    .record-context-label {
        color: #64748b;
        font-size: .66rem;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .record-context-value {
        margin-top: 4px;
        color: #0f172a;
        font-size: .88rem;
        font-weight: 900;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .record-context-item.is-primary .record-context-value {
        color: #1d4ed8;
        font-size: .96rem;
    }
    .monthly-assessment {
        padding: 16px;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, .06);
    }
    .monthly-assessment-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .monthly-assessment-title { color: #0f172a; font-size: .9rem; font-weight: 900; }
    .monthly-assessment-copy { margin-top: 3px; color: #64748b; font-size: .72rem; line-height: 1.45; }
    .assessment-status {
        flex: 0 0 auto;
        padding: 6px 10px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .assessment-status.critical { background: #fee2e2; color: #b91c1c; }
    .assessment-status.warning { background: #ffedd5; color: #c2410c; }
    .assessment-status.watch { background: #fef9c3; color: #854d0e; }
    .assessment-status.success { background: #dcfce7; color: #166534; }
    .monthly-assessment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
        gap: 8px;
    }
    .monthly-assessment-metric {
        min-width: 0;
        padding: 10px 11px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }
    .monthly-assessment-metric span {
        display: block;
        color: #64748b;
        font-size: .62rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .monthly-assessment-metric strong {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: .8rem;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .monthly-assessment-note {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        color: #475569;
        font-size: .72rem;
        line-height: 1.5;
    }
    .monthly-assessment-note i { margin-top: 3px; color: #2563eb; }
    .recommendation-source-flow {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #dbeafe;
    }
    .recommendation-flow-step {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 9px;
        background: #eff6ff;
        color: #1e40af;
        font-size: .7rem;
        font-weight: 850;
    }
    .recommendation-flow-arrow { color: #94a3b8; font-size: .68rem; }
    .recommendation-owner-note {
        flex-basis: 100%;
        color: #64748b;
        font-size: .69rem;
        line-height: 1.45;
    }
    .ai-source-status {
        display: none;
        align-items: center;
        gap: 6px;
        margin-top: 7px;
        color: #1d4ed8;
        font-size: .7rem;
        font-weight: 800;
    }
    .ai-source-status.is-visible { display: flex; }
    .ai-source-status.is-error { color: #b45309; }
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
    .energy-tip-card .tip-review-status { display: none; }
    .tip-approved-text { margin-top: 10px; padding: 11px 12px; border-radius: 10px; background: #ecfdf5; color: #166534; line-height: 1.45; }
    .tip-review-form { display: grid; gap: 10px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #dbe4f0; }
    .tip-review-form textarea,
    .tip-review-form input,
    .tip-review-form select {
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
    .manual-recommendation {
        margin: 0;
        padding: 14px 16px;
        border: 1px solid #bfdbfe;
        border-radius: 14px;
        background: #eff6ff;
    }
    .manual-recommendation summary {
        display: flex;
        align-items: center;
        width: 100%;
        margin: 0;
        color: #1d4ed8;
        font-size: .86rem;
    }
    .manual-recommendation summary::after {
        content: 'Publishes advice with the monthly record assessment';
        margin-left: auto;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
    }
    .manual-recommendation .tip-review-form {
        margin-top: 14px;
        border-top-color: #bfdbfe;
    }
    .manual-saved-card {
        margin-top: 12px;
        padding: 14px 16px;
        border: 1px solid #bbf7d0;
        border-left: 4px solid #16a34a;
        border-radius: 14px;
        background: #f0fdf4;
    }
    .manual-saved-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .manual-saved-title { color: #166534; font-size: .84rem; font-weight: 900; }
    .manual-saved-status {
        padding: 4px 8px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: .65rem;
        font-weight: 900;
    }
    .manual-saved-text { margin-top: 8px; color: #14532d; line-height: 1.45; }
    .recommendation-table-wrap {
        margin-top: 12px;
        overflow-x: auto;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        background: #fff;
    }
    .recommendation-table { width: 100%; min-width: 820px; table-layout: fixed; border-collapse: collapse; }
    .recommendation-table th,
    .recommendation-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
    .recommendation-table th { padding: 10px 14px; background: #f8fafc; color: #64748b; font-size: .66rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .recommendation-table th:nth-child(1) { width: 49%; }
    .recommendation-table th:nth-child(2) { width: 17%; }
    .recommendation-table th:nth-child(3) { width: 12%; }
    .recommendation-table th:nth-child(4) { width: 11%; }
    .recommendation-table th:nth-child(5) { width: 9%; }
    .recommendation-table th:nth-child(6) { width: 2%; }
    .recommendation-table tbody tr { background: #fff; cursor: pointer; transition: background .15s ease, box-shadow .15s ease; }
    .recommendation-table tbody tr:hover { background: #f8fbff; box-shadow: inset 3px 0 0 #2563eb; }
    .recommendation-table tbody tr:last-child td { border-bottom: 0; }
    .recommendation-main-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .recommendation-row-icon {
        display: inline-flex;
        flex: 0 0 34px;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eaf2ff;
        color: #2563eb;
    }
    .recommendation-row-content { min-width: 0; }
    .recommendation-row-title {
        color: #0f172a;
        font-size: .76rem;
        font-weight: 850;
        line-height: 1.35;
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .recommendation-row-meta { margin-top: 3px; color: #64748b; font-size: .66rem; font-weight: 700; }
    .recommendation-staff { display: inline-flex; align-items: center; gap: 6px; color: #334155; font-size: .73rem; font-weight: 800; }
    .recommendation-staff i { color: #2563eb; }
    .recommendation-staff-avatar {
        width: 24px;
        height: 24px;
        border: 2px solid #fff;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 0 0 1px #bfdbfe;
    }
    .recommendation-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: max-content;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: .64rem;
        font-weight: 900;
        line-height: 1;
        text-transform: uppercase;
    }
    .recommendation-pill.is-pending,
    .recommendation-pill.is-review { background: #fef3c7; color: #92400e; }
    .recommendation-pill.is-progress { background: #dbeafe; color: #1d4ed8; }
    .recommendation-pill.is-implemented,
    .recommendation-pill.is-approved,
    .recommendation-pill.is-verified { background: #dcfce7; color: #166534; }
    .recommendation-pill.is-dismissed { background: #fee2e2; color: #991b1b; }
    .recommendation-target { color: #475569; font-size: .7rem; font-weight: 800; white-space: nowrap; }
    .recommendation-open-icon { color: #94a3b8; text-align: right; transition: color .15s ease, transform .15s ease; }
    .recommendation-row:hover .recommendation-open-icon { color: #2563eb; transform: translateX(2px); }
    .recommendation-empty { padding: 16px; color: #64748b; text-align: center; }
    .added-recommendation-list { display: grid; gap: 10px; }
    .added-recommendation-card {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #dbe4f0;
        border-left: 4px solid #2563eb;
        border-radius: 14px;
        background: #f8fbff;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease, transform .15s ease;
    }
    .added-recommendation-card:hover {
        border-color: #93c5fd;
        background: #eff6ff;
        transform: translateY(-1px);
    }
    .added-recommendation-card .recommendation-row-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
    }
    .added-recommendation-content { min-width: 0; }
    .added-recommendation-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .added-recommendation-title { color: #0f172a; font-size: .88rem; font-weight: 900; }
    .added-recommendation-text { margin-top: 5px; color: #475569; font-size: .8rem; line-height: 1.45; }
    .added-recommendation-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px 12px;
        margin-top: 9px;
        color: #64748b;
        font-size: .7rem;
        font-weight: 800;
    }
    .added-recommendation-meta > span { display: inline-flex; align-items: center; gap: 5px; }
    .recommendation-details-btn { margin-top: 10px; }
    .recommendation-modal {
        position: fixed;
        inset: 0;
        width: min(760px, calc(100vw - 28px));
        max-height: calc(100vh - 40px);
        margin: auto;
        padding: 0;
        border: 0;
        border-radius: 16px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
        overflow: hidden;
    }
    .recommendation-modal[open] { display: flex; flex-direction: column; }
    .recommendation-modal::backdrop { background: rgba(15, 23, 42, .6); }
    .recommendation-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
    }
    .recommendation-modal-title { color: #0f172a; font-weight: 900; }
    .recommendation-modal-close { border: 0; background: transparent; color: #64748b; font-size: 1.3rem; cursor: pointer; }
    .recommendation-modal-body { display: grid; gap: 12px; padding: 16px 18px; overflow-y: auto; }
    .recommendation-modal > form:first-of-type {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        flex-direction: column;
    }
    .recommendation-modal > form:first-of-type .recommendation-modal-body {
        flex: 1 1 auto;
        min-height: 0;
    }
    .recommendation-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid #e2e8f0;
    }
    .recommendation-modal input,
    .recommendation-modal select,
    .recommendation-modal textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 9px 10px;
        background: #fff;
        color: #0f172a;
    }
    .recommendation-modal textarea { min-height: 90px; resize: vertical; }
    .recommendation-readonly-value {
        min-height: 38px;
        padding: 9px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
        color: #334155;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }
    .recommendation-readonly-value.is-long { min-height: 72px; }
    .recommendation-assessment-snapshot {
        margin: 14px 18px 0;
        padding: 12px 13px;
        border: 1px solid #bfdbfe;
        border-radius: 11px;
        background: #eff6ff;
    }
    .recommendation-assessment-snapshot .record-context-label { color: #1d4ed8; }
    .recommendation-assessment-snapshot p { margin: 5px 0 0; color: #334155; font-size: .76rem; line-height: 1.55; }
    .recommendation-delete-form { padding: 0 18px 16px; }
    .recommendation-section-heading {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 14px 0 8px;
        color: #475569;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .tip-form-section {
        display: grid;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dbeafe;
        border-radius: 11px;
        background: rgba(255, 255, 255, .7);
    }
    .tip-form-section-title {
        color: #1e3a8a;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }
    .tip-review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .tip-review-field label { display: block; margin-bottom: 5px; color: #64748b; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
    .tip-field-help { margin-top: 5px; color: #64748b; font-size: .68rem; line-height: 1.35; }
    .tip-field-error { margin-top: 5px; color: #b91c1c; font-size: .7rem; font-weight: 800; }
    .stat-value.is-text { font-size: 1.05rem; color: #b45309; }
    .cprf-handoff {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        background: #eff6ff;
    }
    .cprf-handoff-icon {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
    }
    .cprf-handoff-title { color: #1e3a8a; font-size: .78rem; font-weight: 900; }
    .cprf-handoff-copy { margin-top: 3px; color: #475569; font-size: .7rem; line-height: 1.5; }
    .tip-review-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .tip-action { border: 0; border-radius: 9px; padding: 8px 11px; font-size: .76rem; font-weight: 800; cursor: pointer; }
    .tip-action.save { background: #e2e8f0; color: #334155; }
    .tip-action.approve { background: #16a34a; color: #fff; }
    .tip-action.dismiss { background: #fee2e2; color: #b91c1c; }

    @media (max-width: 640px) {
        .cprf-handoff { grid-template-columns: auto minmax(0, 1fr); }
        .cprf-handoff > .recommendation-pill { grid-column: 2; justify-self: start; }
        .monthly-assessment-grid { grid-template-columns: 1fr 1fr; }
        .monthly-assessment-head { flex-direction: column; }
    }

    /* Recommendation modal follows the shared Poppins/blue system theme. */
    .recommendation-modal {
        width: min(720px, calc(100vw - 32px));
        max-height: min(760px, calc(100dvh - 40px));
        border-radius: 20px;
        background: #ffffff;
        color: #1e293b;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .32);
    }
    .recommendation-modal,
    .recommendation-modal button,
    .recommendation-modal input,
    .recommendation-modal select,
    .recommendation-modal textarea {
        font-family: 'Poppins', sans-serif;
    }
    .recommendation-modal::backdrop {
        background: rgba(15, 23, 42, .64);
        backdrop-filter: blur(3px);
    }
    .recommendation-modal-head {
        padding: 18px 22px;
        background: #ffffff;
    }
    .recommendation-modal-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .recommendation-modal-heading-icon {
        display: inline-flex;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: #eff6ff;
        color: #2563eb;
        font-size: .92rem;
    }
    .recommendation-modal-title {
        color: #0f172a;
        font-size: 1.02rem;
        font-weight: 600;
        line-height: 1.3;
    }
    .recommendation-modal-head .recommendation-row-meta {
        margin-top: 3px;
        color: #64748b;
        font-size: .7rem;
        font-weight: 500;
    }
    .recommendation-modal-head .recommendation-modal-close {
        display: inline-flex;
        flex: 0 0 34px;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
        font-size: .82rem;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    .recommendation-modal-head .recommendation-modal-close:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .recommendation-modal-body {
        gap: 16px;
        padding: 20px 22px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .recommendation-modal .tip-review-grid {
        gap: 13px;
    }
    .recommendation-modal .tip-review-field label {
        margin-bottom: 7px;
        color: #64748b;
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: .035em;
    }
    .recommendation-modal input,
    .recommendation-modal select,
    .recommendation-modal textarea,
    .recommendation-modal .recommendation-readonly-value {
        border: 1px solid #dbe3ee;
        border-radius: 11px;
        font-size: .8rem;
        line-height: 1.45;
    }
    .recommendation-modal input,
    .recommendation-modal select {
        min-height: 44px;
        padding: 9px 12px;
        font-weight: 500;
    }
    .recommendation-modal textarea {
        min-height: 104px;
        padding: 11px 12px;
        font-weight: 400;
    }
    .recommendation-modal input:focus,
    .recommendation-modal select:focus,
    .recommendation-modal textarea:focus {
        border-color: #60a5fa;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }
    .recommendation-modal .recommendation-readonly-value {
        min-height: 44px;
        padding: 10px 12px;
        background: #f8fafc;
        color: #334155;
        font-weight: 500;
    }
    .recommendation-modal .recommendation-readonly-value.is-long {
        min-height: 76px;
        border-left: 3px solid #60a5fa;
        background: #f8fbff;
    }
    .recommendation-progress-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 12px;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #eff6ff;
        color: #475569;
        font-size: .69rem;
        font-weight: 500;
        line-height: 1.45;
    }
    .recommendation-progress-note i {
        margin-top: 2px;
        color: #2563eb;
    }
    .recommendation-modal-footer {
        padding: 14px 22px;
        background: #f8fafc;
    }
    .recommendation-modal .tip-action {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: .75rem;
        font-weight: 600;
        transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    .recommendation-modal .tip-action.save {
        border: 1px solid #dbe3ee;
        background: #ffffff;
        color: #475569;
    }
    .recommendation-modal .tip-action.approve {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 7px 16px rgba(37, 99, 235, .2);
    }
    .recommendation-modal .tip-action:hover {
        transform: translateY(-1px);
    }
    .recommendation-modal .tip-action.save:hover {
        background: #f1f5f9;
    }
    .recommendation-modal .tip-action.approve:hover {
        background: #1d4ed8;
        box-shadow: 0 9px 20px rgba(37, 99, 235, .26);
    }
    .tip-implementation-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 12px;
        margin-top: 9px;
        color: #475569;
        font-size: .73rem;
        font-weight: 800;
    }
    .tip-implementation-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .system-adopt-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        margin-top: 10px;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        padding: 7px 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .72rem;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }
    .system-adopt-btn:hover { background: #dbeafe; }
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
    .checklist-toolbar { display: grid; grid-template-columns: minmax(240px, 1fr) 200px auto; gap: 12px; align-items: end; }
    .checklist-task-form .checklist-toolbar { grid-template-columns: minmax(0, 1fr) minmax(220px, 240px); align-items: start; }
    .checklist-task-form .btn-main { min-height: 50px; white-space: nowrap; }
    .checklist-task-help { margin-top: 6px; color: #64748b; font-size: .75rem; line-height: 1.4; }
    .checklist-filter { grid-template-columns: minmax(240px, 1fr) 220px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; }
    .checklist-filter .field select,
    .checklist-filter .field input { background: #fff; }
    .checklist-overview { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .checklist-overview-card { padding: 14px 15px; border: 1px solid #e2e8f0; border-radius: 13px; background: #fff; }
    .checklist-overview-label { color: #64748b; font-size: .68rem; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; }
    .checklist-overview-value { margin-top: 4px; color: #0f172a; font-size: 1.15rem; font-weight: 900; }
    .checklist-overview-copy { margin-top: 3px; color: #64748b; font-size: .7rem; }
    .checklist-completion-track { height: 6px; margin-top: 9px; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
    .checklist-completion-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2563eb, #16a34a); }
    .checklist-progress { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-weight: 800; }
    .checklist-task-form { padding: 20px; border: 1px solid #bfdbfe; border-radius: 16px; background: linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%); box-shadow: 0 8px 20px rgba(37, 99, 235, .06); }
    .checklist-task-heading { display: flex; align-items: center; gap: 12px; }
    .checklist-task-heading-icon { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 12px; background: #dbeafe; color: #2563eb; font-size: 1rem; }
    .checklist-task-heading-title { color: #1e3a8a; font-size: .82rem; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
    .checklist-task-heading-copy { margin-top: 2px; color: #64748b; font-size: .76rem; }
    .checklist-section { display: grid; gap: 9px; }
    .checklist-section-title { color: #334155; font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
    .checklist-item { display: flex; align-items: flex-start; gap: 11px; padding: 14px; border: 1px solid #e2e8f0; border-radius: 13px; background: #fff; cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
    .checklist-item:hover { border-color: #93c5fd; box-shadow: 0 6px 16px rgba(37, 99, 235, .08); transform: translateY(-1px); }
    .checklist-item:has(input:checked) { border-color: #86efac; background: #f0fdf4; }
    .checklist-item input { width: 20px; height: 20px; margin-top: 1px; accent-color: #16a34a; }
    .checklist-task-delete { min-width: 40px; min-height: 40px; margin-left: auto; padding: 8px 11px; color: #dc2626; }
    .checklist-item-text { color: #1e293b; line-height: 1.4; }
    .checklist-item-meta { margin-top: 3px; color: #64748b; font-size: .73rem; }
    .checklist-empty { display: grid; justify-items: center; gap: 8px; padding: 34px 20px; border: 1px dashed #bfdbfe; border-radius: 16px; background: #f8fbff; text-align: center; }
    .checklist-empty-icon { display: grid; width: 48px; height: 48px; place-items: center; border-radius: 14px; background: #dbeafe; color: #2563eb; font-size: 1.1rem; }
    .checklist-empty-title { color: #1e293b; font-size: .95rem; font-weight: 900; }
    .checklist-empty-copy { max-width: 520px; color: #64748b; font-size: .82rem; line-height: 1.5; }
    .checklist-task-modal { position: fixed; inset: 0; width: min(760px, calc(100vw - 32px)); max-width: none; max-height: calc(100dvh - 32px); margin: auto; padding: 0; border: 0; border-radius: 18px; background: #fff; box-sizing: border-box; box-shadow: 0 24px 70px rgba(15, 23, 42, .3); overflow: auto; }
    .checklist-task-modal::backdrop { background: rgba(15, 23, 42, .6); backdrop-filter: blur(3px); }
    .checklist-task-modal .checklist-task-form { margin: 0; border: 0; border-radius: 0; box-shadow: none; }
    .checklist-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 15px; border-bottom: 1px solid #dbeafe; }
    .checklist-modal-close { display: grid; width: 36px; height: 36px; place-items: center; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #64748b; cursor: pointer; }
    .checklist-modal-actions { display: flex; justify-content: flex-end; gap: 9px; padding-top: 4px; }
    .checklist-routine-preview { min-height: 50px; display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 12px; background: #f8fafc; color: #334155; font-size: .86rem; font-weight: 800; white-space: nowrap; }
    body.dark-mode .feature-shell,
    body.dark-mode .panel,
    body.dark-mode .stat-card,
    body.dark-mode .field input,
    body.dark-mode .field select,
    body.dark-mode .field textarea,
    body.dark-mode .feature-point {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .panel.tips-panel { background: transparent; border-color: transparent; box-shadow: none; }
    body.dark-mode .panel.tips-panel > .panel-head { border-color: #334155; }
    body.dark-mode .feature-shell.checklist-page,
    body.dark-mode .feature-shell.goal-page,
    body.dark-mode .checklist-task-form { background: #0f172a; border-color: #334155; }
    body.dark-mode .checklist-filter,
    body.dark-mode .checklist-overview-card,
    body.dark-mode .checklist-empty,
    body.dark-mode .checklist-item { background: #111827; border-color: #334155; }
    body.dark-mode .checklist-task-heading-icon,
    body.dark-mode .checklist-empty-icon { background: #172554; color: #93c5fd; }
    body.dark-mode .checklist-task-heading-title,
    body.dark-mode .checklist-overview-value,
    body.dark-mode .checklist-empty-title,
    body.dark-mode .checklist-item-text { color: #f8fafc; }
    body.dark-mode .checklist-task-heading-copy,
    body.dark-mode .checklist-task-help,
    body.dark-mode .checklist-empty-copy { color: #94a3b8; }
    body.dark-mode .checklist-task-modal,
    body.dark-mode .checklist-modal-close,
    body.dark-mode .checklist-routine-preview { background: #111827; border-color: #334155; color: #e2e8f0; }
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
    body.dark-mode .record-context {
        border-color: #1e40af;
        background: linear-gradient(135deg, #172554, #0f172a);
    }
    body.dark-mode .record-context-item {
        border-color: #334155;
        background: rgba(15, 23, 42, .86);
    }
    body.dark-mode .record-context-label { color: #94a3b8; }
    body.dark-mode .record-context-value { color: #e2e8f0; }
    body.dark-mode .record-context-item.is-primary .record-context-value { color: #93c5fd; }
    body.dark-mode .feature-title,
    body.dark-mode .panel-title,
    body.dark-mode .stat-value {
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
    body.dark-mode .manual-recommendation { background: #172554; border-color: #1e40af; }
    body.dark-mode .manual-recommendation summary::after { color: #94a3b8; }
    body.dark-mode .manual-saved-card { background: #052e1a; border-color: #166534; }
    body.dark-mode .manual-saved-title,
    body.dark-mode .manual-saved-text { color: #bbf7d0; }
    body.dark-mode .recommendation-section-heading { color: #94a3b8; }
    body.dark-mode .recommendation-table-wrap,
    body.dark-mode .recommendation-table tbody tr { background: #0f172a; border-color: #334155; }
    body.dark-mode .recommendation-table th { background: #111827; color: #94a3b8; }
    body.dark-mode .recommendation-table td { border-color: #334155; }
    body.dark-mode .recommendation-table tbody tr:hover { background: #172554; }
    body.dark-mode .recommendation-row-title { color: #f8fafc; }
    body.dark-mode .recommendation-staff,
    body.dark-mode .recommendation-target { color: #cbd5e1; }
    body.dark-mode .added-recommendation-card { background: #111827; border-color: #334155; border-left-color: #60a5fa; }
    body.dark-mode .added-recommendation-card:hover { background: #172554; border-color: #1e40af; }
    body.dark-mode .added-recommendation-title { color: #f8fafc; }
    body.dark-mode .added-recommendation-text,
    body.dark-mode .added-recommendation-meta { color: #cbd5e1; }
    body.dark-mode .recommendation-readonly-value {
        border-color: #334155;
        background: #111827;
        color: #e2e8f0;
    }
    body.dark-mode .recommendation-modal,
    body.dark-mode .recommendation-modal-head {
        border-color: #334155;
        background: #0f172a;
        color: #e2e8f0;
    }
    body.dark-mode .recommendation-modal-title { color: #f8fafc; }
    body.dark-mode .recommendation-modal-heading-icon {
        background: #172554;
        color: #93c5fd;
    }
    body.dark-mode .recommendation-modal-head .recommendation-modal-close {
        border-color: #334155;
        background: #111827;
        color: #94a3b8;
    }
    body.dark-mode .recommendation-modal input,
    body.dark-mode .recommendation-modal select,
    body.dark-mode .recommendation-modal textarea {
        border-color: #334155;
        background: #0b1220;
        color: #e2e8f0;
    }
    body.dark-mode .recommendation-modal .recommendation-readonly-value {
        border-color: #334155;
        background: #111827;
        color: #e2e8f0;
    }
    body.dark-mode .recommendation-modal .recommendation-readonly-value.is-long {
        border-left-color: #60a5fa;
        background: #101b31;
    }
    body.dark-mode .recommendation-progress-note {
        border-color: #1e3a8a;
        background: #172554;
        color: #cbd5e1;
    }
    body.dark-mode .recommendation-modal-footer {
        border-color: #334155;
        background: #111827;
    }
    body.dark-mode .recommendation-modal .tip-action.save {
        border-color: #334155;
        background: #0f172a;
        color: #cbd5e1;
    }
    body.dark-mode .tip-form-section { background: rgba(15, 23, 42, .75); border-color: #334155; }
    body.dark-mode .tip-form-section-title { color: #93c5fd; }
    body.dark-mode .tip-implementation-meta { color: #cbd5e1; }
    body.dark-mode .system-adopt-btn { background: #172554; border-color: #1e40af; color: #bfdbfe; }
    body.dark-mode .system-adopt-btn:hover { background: #1e3a8a; }
    body.dark-mode .tip-review-form textarea,
    body.dark-mode .tip-review-form input { background: #0b1220; color: #e2e8f0; border-color: #334155; }
    body.dark-mode .feature-desc,
    body.dark-mode .panel-note,
    body.dark-mode .stat-label,
    body.dark-mode .stat-sub,
    body.dark-mode .help-text,
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
        .checklist-task-form .checklist-toolbar { grid-template-columns: minmax(0, 1fr) 220px; }
        .checklist-task-form .btn-main { grid-column: 1 / -1; justify-content: center; }
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
        .manual-recommendation summary::after { display: none; }
        .tip-filter-card { grid-template-columns: 1fr; padding: 12px; }
        .tip-filter-card .action-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 0;
        }
        .tip-filter-card .btn-main { justify-content: center; }
        .record-context { grid-template-columns: 1fr 1fr; }
        .panel.tips-panel > .panel-head { padding-inline: 0; }
        .panel.tips-panel > .panel-body { padding-inline: 0; }
        .checklist-toolbar { grid-template-columns: 1fr; }
        .checklist-task-form .checklist-toolbar { grid-template-columns: 1fr; }
        .checklist-task-form .btn-main { grid-column: auto; }
        .checklist-filter { grid-template-columns: 1fr; }
        .checklist-overview { grid-template-columns: 1fr; }
        .goal-metrics { grid-template-columns: 1fr; }
        .goal-create-grid { grid-template-columns: 1fr; }
        .recommendation-modal {
            width: calc(100vw - 16px);
            max-height: calc(100dvh - 16px);
            border-radius: 16px;
        }
        .recommendation-modal-head { padding: 15px 16px; }
        .recommendation-modal-heading-icon { display: none; }
        .recommendation-modal-body { padding: 16px; }
        .recommendation-modal-footer { padding: 12px 16px; }
        .recommendation-modal .tip-action { min-height: 38px; }
    }

    @media (max-width: 560px) {
        .record-context { grid-template-columns: 1fr; }
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
                    <h2 class="panel-title">{{ $featureSlug === 'energy-saving-tips' ? 'Facility Recommendations' : ($featureSlug === 'daily-checklist' ? 'Daily Task Board' : ($featureSlug === 'conservation-goals' ? 'Goal Planning' : 'Main Content')) }}</h2>
                    @if(!in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
                        <div class="panel-note">
                            @if($featureSlug === 'energy-saving-tips')
                                {{ $isCprfIntegrationPeriod
                                    ? 'Approved recommendations are published to the CPRF recommendation list.'
                                    : 'Review and publish practical energy guidance for the selected facility.' }}
                            @else
                                Actual content and forms tied to current app data.
                            @endif
                        </div>
                    @endif
                </div>
                @if($featureSlug === 'daily-checklist' && $canManageChecklistTasks && $selectedFacility)
                    <button class="btn-main" type="button" onclick="document.getElementById('checklistTaskModal')?.showModal()">
                        <i class="fa-solid fa-plus"></i> Add Task
                    </button>
                @endif
                @if(!in_array($featureSlug, ['daily-checklist', 'conservation-goals'], true))
                    <a class="back-link" href="{{ $selectedRecordContext['monthly_records_url'] ?? route('modules.energy-conservation.index') }}">
                        <i class="fa-solid fa-arrow-left"></i>
                        {{ $selectedRecordContext ? 'Back to Monthly Records' : 'Back' }}
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
                        $openingChecklistCount = $dailyChecklist->where('period', 'opening')->count();
                        $closingChecklistCount = $dailyChecklist->where('period', 'closing')->count();
                        $checklistCompletionPercent = $checklistTotal > 0 ? ($completedChecklistCount / $checklistTotal) * 100 : 0;
                    @endphp
                    <div class="checklist-overview" aria-label="Daily checklist summary">
                        <div class="checklist-overview-card">
                            <div class="checklist-overview-label">Completion</div>
                            <div class="checklist-overview-value">{{ $completedChecklistCount }} / {{ $checklistTotal }}</div>
                            <div class="checklist-overview-copy">Tasks completed for this date</div>
                            <div class="checklist-completion-track"><span style="width: {{ min(100, max(0, $checklistCompletionPercent)) }}%"></span></div>
                        </div>
                        <div class="checklist-overview-card">
                            <div class="checklist-overview-label">Opening Routine</div>
                            <div class="checklist-overview-value">{{ $openingChecklistCount }}</div>
                            <div class="checklist-overview-copy">Assigned opening tasks</div>
                        </div>
                        <div class="checklist-overview-card">
                            <div class="checklist-overview-label">Closing Routine</div>
                            <div class="checklist-overview-value">{{ $closingChecklistCount }}</div>
                            <div class="checklist-overview-copy">Assigned closing tasks</div>
                        </div>
                    </div>

                    @if($canManageChecklistTasks && $selectedFacility)
                        <dialog id="checklistTaskModal" class="checklist-task-modal" @if($errors->has('task_label') || $errors->has('period')) data-has-errors="true" @endif>
                        <form method="POST" action="{{ route('modules.energy-conservation.daily-checklist.tasks.store') }}" class="form-grid checklist-task-form">
                            @csrf
                            <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                            <input type="hidden" name="return_date" value="{{ $checklistDate }}">
                            <div class="checklist-modal-head">
                                <div class="checklist-task-heading">
                                    <div class="checklist-task-heading-icon"><i class="fa-solid fa-list-check"></i></div>
                                    <div>
                                        <div class="checklist-task-heading-title">Add a task for {{ $selectedFacility->name }}</div>
                                        <div class="checklist-task-heading-copy">Choose an instruction; its routine is assigned automatically.</div>
                                    </div>
                                </div>
                                <button class="checklist-modal-close" type="button" aria-label="Close add task dialog" onclick="this.closest('dialog').close()"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            @php
                                $checklistTaskOptions = [
                                    'Opening routine' => [
                                        'Record the opening main-meter reading.',
                                        'Inspect the main meter for visible damage or unusual readings.',
                                        'Use natural lighting before switching on indoor lights.',
                                        'Check that air-conditioning is set between 24 and 26 degrees Celsius.',
                                        'Confirm doors and windows are closed while air-conditioning is running.',
                                        'Inspect equipment for unusual noise, heat, smell, or vibration.',
                                        'Check for leaks or continuously running pumps.',
                                    ],
                                    'Closing routine' => [
                                        'Record the closing main-meter reading.',
                                        'Verify unused lights are switched off.',
                                        'Shut down computers, printers, and office equipment not in use.',
                                        'Turn off air-conditioning and ventilation after operating hours.',
                                        'Unplug chargers and non-essential pantry appliances.',
                                        'Confirm non-essential equipment is switched off before closing.',
                                        'Check timers and schedules for outdoor lighting.',
                                        'Confirm emergency and safety equipment remains powered.',
                                    ],
                                ];
                            @endphp
                            <div class="checklist-toolbar">
                                <div class="field">
                                    <label for="checklist_task_label">Task instruction</label>
                                    <select id="checklist_task_label" name="task_label" required>
                                        <option value="" disabled @selected(!old('task_label'))>Select a task instruction</option>
                                        @foreach($checklistTaskOptions as $taskGroup => $taskInstructions)
                                            @php $taskPeriod = str_starts_with($taskGroup, 'Opening') ? 'opening' : 'closing'; @endphp
                                            <optgroup label="{{ $taskGroup }}">
                                            @foreach($taskInstructions as $taskInstruction)
                                                <option value="{{ $taskInstruction }}" data-period="{{ $taskPeriod }}" @selected(old('task_label') === $taskInstruction)>{{ $taskInstruction }}</option>
                                            @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <div class="checklist-task-help">Tasks are grouped by routine. Selecting one automatically sets the correct routine.</div>
                                    @error('task_label')<div class="tip-field-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="field">
                                    <label>Assigned Routine</label>
                                    <input id="checklist_task_period" type="hidden" name="period" value="{{ old('period') }}">
                                    <div id="checklistRoutinePreview" class="checklist-routine-preview">
                                        <i class="fa-solid fa-clock"></i>
                                        {{ old('period') ? ucfirst(old('period')).' routine' : 'Select an instruction' }}
                                    </div>
                                    @error('period')<div class="tip-field-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="checklist-modal-actions">
                                <button type="button" class="btn-main btn-secondary" onclick="this.closest('dialog').close()">Cancel</button>
                                <button type="submit" class="btn-main"><i class="fa-solid fa-plus"></i> Add Task</button>
                            </div>
                        </form>
                        </dialog>
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
                                                <button
                                                    type="button"
                                                    class="btn-main btn-secondary checklist-task-delete"
                                                    title="Remove task"
                                                    aria-label="Remove {{ $item['label'] }}"
                                                    data-delete-checklist-task-url="{{ route('modules.energy-conservation.daily-checklist.tasks.destroy', $item['id']) }}"
                                                ><i class="fa-solid fa-trash"></i></button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            @if($canCompleteChecklist)
                                <div class="action-row">
                                    <span class="help-text">Changes are saved automatically when a task is checked or unchecked.</span>
                                </div>
                                </form>
                            @else
                                <div class="help-text">Task management only. Daily completion is performed by assigned staff.</div>
                                </div>
                            @endif
                    @else
                        <div class="checklist-empty">
                            <div class="checklist-empty-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                            <div class="checklist-empty-title">
                            @if(!$selectedFacility)
                                No facility available
                            @elseif($canManageChecklistTasks)
                                No checklist tasks yet
                            @else
                                No assigned checklist tasks
                            @endif
                            </div>
                            <div class="checklist-empty-copy">
                                @if(!$selectedFacility)
                                    Add or synchronize a facility before creating its daily energy checklist.
                                @elseif($canManageChecklistTasks)
                                    Add the first opening or closing task for {{ $selectedFacility->name }}.
                                @else
                                    An administrator or Energy Officer must assign checklist tasks to this facility first.
                                @endif
                            </div>
                            @if($selectedFacility && $canManageChecklistTasks)
                                <button class="btn-main" type="button" onclick="document.getElementById('checklistTaskModal')?.showModal()">
                                    <i class="fa-solid fa-plus"></i> Add First Task
                                </button>
                            @endif
                        </div>
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
                @elseif($featureSlug === 'energy-saving-tips')
                    <div class="summary-period" aria-label="Reporting period: {{ $overview['periodLabel'] ?? 'Selected month' }}">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <span>Reporting Period:</span>
                        <strong>{{ $overview['periodLabel'] ?? 'Selected month' }}</strong>
                    </div>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-label">{{ $selectedRecordContext ? 'Selected Facility' : 'Monitored Facilities' }}</div>
                            <div class="stat-value">{{ number_format((int) ($recommendationTotals['monitored_facilities'] ?? 0)) }}</div>
                            <div class="stat-sub">{{ $selectedRecordContext ? 'Facility covered by this recommendation.' : 'Facilities with records for '.($overview['periodLabel'] ?? 'the selected month').'.' }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Actual kWh</div>
                            <div class="stat-value">{{ number_format((float) ($recommendationTotals['actual_kwh'] ?? 0), 2) }}</div>
                            <div class="stat-sub">{{ $selectedRecordContext ? 'Main-meter consumption for the selected facility and month.' : 'Total main-meter consumption for '.($overview['periodLabel'] ?? 'the selected month').'.' }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">
                                Potential Avoidable Cost
                                <span class="metric-info"
                                      tabindex="0"
                                      title="Estimated as max(0, actual kWh minus baseline kWh) multiplied by the applicable PHP/kWh rate for the selected month."
                                      aria-label="Avoidable Cost formula: excess consumption above baseline multiplied by the applicable electricity rate.">
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                </span>
                            </div>
                            @if(($recommendationTotals['avoidable_cost'] ?? null) === null)
                                <div class="stat-value is-text">Baseline required</div>
                                <div class="stat-sub">Set an approved baseline before estimating excess cost.</div>
                            @else
                                <div class="stat-value">PHP {{ number_format((float) $recommendationTotals['avoidable_cost'], 2) }}</div>
                                <div class="stat-sub">Estimated excess cost above the approved baseline for {{ $overview['periodLabel'] ?? 'the selected month' }}.</div>
                            @endif
                        </div>
                    </div>

                    @if($featureSlug === 'energy-saving-tips' && $selectedRecordContext)
                        <div class="record-context" aria-label="Selected monthly record context">
                            <div class="record-context-item is-primary">
                                <div class="record-context-label">Facility</div>
                                <div class="record-context-value">
                                    {{ $selectedRecordContext['facility_name'] }}
                                    @if($selectedRecordContext['facility_type'])
                                        · {{ $selectedRecordContext['facility_type'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="record-context-item">
                                <div class="record-context-label">Month</div>
                                <div class="record-context-value">{{ $selectedRecordContext['period_label'] }}</div>
                            </div>
                            <div class="record-context-item">
                                <div class="record-context-label">Record Date</div>
                                <div class="record-context-value">{{ $selectedRecordContext['record_date_label'] }}</div>
                            </div>
                            <div class="record-context-item">
                                <div class="record-context-label">Main Meter</div>
                                <div class="record-context-value">{{ $selectedRecordContext['meter_name'] }}</div>
                            </div>
                            <div class="record-context-item">
                                <div class="record-context-label">Data Source</div>
                                <div class="record-context-value">{{ $selectedRecordContext['source_label'] }}</div>
                            </div>
                            <div class="record-context-item">
                                <div class="record-context-label">Record Approval</div>
                                <div class="record-context-value">{{ $selectedRecordContext['review_status'] }}</div>
                            </div>
                        </div>
                    @endif

                    @if(! ($featureSlug === 'energy-saving-tips' && $selectedRecordContext))
                    <form class="form-grid{{ in_array($featureSlug, ['energy-saving-tips', 'conservation-goals'], true) ? ' tip-filter-card' : '' }}" method="GET" action="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}">
                        <div class="field">
                            <label>Select Facility</label>
                            <select name="facility_id" @if(in_array($featureSlug, ['energy-saving-tips', 'conservation-goals'], true)) onchange="this.form.submit()" @endif>
                                @if($featureSlug !== 'energy-saving-tips')
                                    <option value="0">All facilities</option>
                                @endif
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
                        @if($featureSlug === 'energy-saving-tips')
                            <div class="field">
                                <label for="recommendation_month">Month</label>
                                <input id="recommendation_month" type="month" name="month" value="{{ $selectedMonth }}" required onchange="this.form.submit()">
                                <div class="help-text">Recommendations use records from this month.</div>
                            </div>
                        @endif
                        <div class="action-row" @if($featureSlug === 'conservation-goals') hidden @endif>
                            @if($featureSlug !== 'energy-saving-tips')
                                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            @endif
                            @if($featureSlug !== 'energy-saving-tips')
                                <button class="btn-main" type="submit"><i class="fa-solid fa-filter"></i> Apply Filter</button>
                            @endif
                            <a class="btn-main btn-secondary" href="{{ route('modules.energy-conservation.feature', ['feature' => $featureSlug]) }}"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                        </div>
                    </form>
                    @endif

                    @if($featureSlug === 'energy-saving-tips')
                        @php
                            $manualTip = $energyTips->first(
                                fn (array $tip) => (int) ($tip['facility_id'] ?? 0) === (int) $selectedFacilityId
                            );
                            $assessmentDetails = $manualTip['details'] ?? null;
                            $assessmentDifference = $assessmentDetails['difference_kwh'] ?? null;
                        @endphp

                        @if($manualTip && $assessmentDetails)
                            <section class="monthly-assessment" aria-labelledby="monthlyAssessmentTitle">
                                <div class="monthly-assessment-head">
                                    <div>
                                        <div id="monthlyAssessmentTitle" class="monthly-assessment-title">
                                            <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
                                            Monthly Record Assessment
                                        </div>
                                        <div class="monthly-assessment-copy">
                                            Review these measured details before writing and publishing the recommendation.
                                        </div>
                                    </div>
                                    <span class="assessment-status {{ $manualTip['tone'] ?? 'info' }}">
                                        {{ $assessmentDetails['status'] }}
                                    </span>
                                </div>
                                <div class="monthly-assessment-grid">
                                    <div class="monthly-assessment-metric">
                                        <span>Actual Usage</span>
                                        <strong>{{ number_format((float) $assessmentDetails['actual_kwh'], 2) }} kWh</strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Monthly Energy Cost</span>
                                        <strong>PHP {{ number_format((float) $assessmentDetails['energy_cost'], 2) }}</strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Rate</span>
                                        <strong>PHP {{ number_format((float) $assessmentDetails['rate_per_kwh'], 2) }}/kWh</strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Approved Baseline</span>
                                        <strong>{{ $assessmentDetails['baseline_kwh'] !== null ? number_format((float) $assessmentDetails['baseline_kwh'], 2).' kWh' : 'Not available' }}</strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Difference</span>
                                        <strong>
                                            @if($assessmentDifference === null)
                                                Not available
                                            @else
                                                {{ $assessmentDifference > 0 ? '+' : '' }}{{ number_format((float) $assessmentDifference, 2) }} kWh
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Deviation</span>
                                        <strong>
                                            @if($assessmentDetails['deviation_percent'] === null)
                                                Not available
                                            @else
                                                {{ (float) $assessmentDetails['deviation_percent'] > 0 ? '+' : '' }}{{ number_format((float) $assessmentDetails['deviation_percent'], 2) }}%
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="monthly-assessment-metric">
                                        <span>Potential Avoidable Cost</span>
                                        <strong>{{ $assessmentDetails['avoidable_cost'] !== null ? 'PHP '.number_format((float) $assessmentDetails['avoidable_cost'], 2) : 'Not available' }}</strong>
                                    </div>
                                </div>
                                <div class="monthly-assessment-note">
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                    <span>{{ $manualTip['assessment_message'] }} This assessment is automatically attached to the saved and published recommendation.</span>
                                </div>
                                <div class="recommendation-source-flow" aria-label="Recommendation review flow">
                                    <span class="recommendation-flow-step"><i class="fa-solid fa-brain"></i> AI Alerts suggestion</span>
                                    <i class="fa-solid fa-arrow-right recommendation-flow-arrow" aria-hidden="true"></i>
                                    <span class="recommendation-flow-step"><i class="fa-solid fa-user-check"></i> Reviewer approval</span>
                                    <i class="fa-solid fa-arrow-right recommendation-flow-arrow" aria-hidden="true"></i>
                                    <span class="recommendation-flow-step"><i class="fa-solid fa-paper-plane"></i> {{ $isCprfIntegrationPeriod ? 'CPRF recommendation' : 'Facility recommendation' }}</span>
                                    <div class="recommendation-owner-note">
                                        @if(($assessmentDetails['action_owner'] ?? 'monitor') === \App\Support\EnergyAlertRouting::INCIDENT)
                                            Very high or critical corrective work remains in the Incident and Maintenance workflow; only the reviewed advisory is published here.
                                        @elseif(($assessmentDetails['action_owner'] ?? 'monitor') === \App\Support\EnergyAlertRouting::CONSERVATION)
                                            This high-consumption case is owned by the Recommendation workflow and may be published after review.
                                        @else
                                            This is monitoring guidance. Validate the reading and publish only useful facility advice.
                                        @endif
                                    </div>
                                </div>
                            </section>
                        @endif

                        @if($canReviewTips && $manualTip)
                            <details id="recommendationActionPanel" class="tip-review-disclosure manual-recommendation" @if($errors->any()) open @endif>
                                <summary>Add Recommendation</summary>
                                <form class="tip-review-form" method="POST" action="{{ route('modules.energy-conservation.tips.review') }}">
                                    @csrf
                                    <input type="hidden" name="facility_id" value="{{ $selectedFacilityId }}">
                                    <input type="hidden" name="period" value="{{ $selectedMonth }}">
                                    @if($selectedRecordContext)
                                        <input type="hidden" name="record_id" value="{{ $selectedRecordContext['record_id'] }}">
                                    @endif
                                    <div class="tip-form-section">
                                        <div class="tip-form-section-title">1. Recommendation</div>
                                        <div class="tip-review-field">
                                            <label>Recommendation</label>
                                            <textarea id="manualRecommendationText" name="engineer_recommendation" required placeholder="Enter your recommendation for this facility...">{{ old('engineer_recommendation') }}</textarea>
                                            <div class="tip-field-help">
                                                {{ $isCprfIntegrationPeriod
                                                    ? 'This publishes advice to the CPRF recommendation list. It does not create or assign an implementation task.'
                                                    : 'This publishes advice to the facility recommendation list. It does not create or assign an implementation task.' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tip-review-actions">
                                        <button class="tip-action save" type="submit" name="status" value="for_review">Save Draft</button>
                                        <button class="tip-action approve" type="submit" name="status" value="approved">{{ $isCprfIntegrationPeriod ? 'Publish to CPRF' : 'Publish Recommendation' }}</button>
                                    </div>
                                </form>
                            </details>
                        @endif

                        @if($canReviewTips || $manualRecommendations->isNotEmpty())
                            <div class="recommendation-section-heading">
                                <i class="fa-solid fa-list-check"></i> Added Recommendations
                            </div>
                            <div class="added-recommendation-list">
                            @forelse($manualRecommendations as $recommendation)
                                @php
                                    $approvalClass = match ($recommendation->status) {
                                        'approved' => 'is-approved',
                                        'dismissed' => 'is-dismissed',
                                        default => 'is-review',
                                    };
                                @endphp
                                <article class="added-recommendation-card recommendation-row"
                                         data-recommendation-dialog="recommendationModal{{ $recommendation->id }}"
                                         tabindex="0">
                                    <div class="recommendation-row-icon"><i class="fa-solid fa-user-pen"></i></div>
                                    <div class="added-recommendation-content">
                                        <div class="added-recommendation-top">
                                            <div class="added-recommendation-title">Energy Recommendation</div>
                                            <span class="recommendation-pill {{ $approvalClass }}">
                                                {{ ucwords(str_replace('_', ' ', $recommendation->status)) }}
                                            </span>
                                        </div>
                                        <div class="added-recommendation-text">{{ $recommendation->engineer_recommendation }}</div>
                                        <div class="added-recommendation-meta">
                                            <span class="recommendation-staff">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                {{ $isCprfIntegrationPeriod ? 'Published to CPRF' : 'Facility recommendation' }}
                                            </span>
                                        </div>
                                        <button type="button" class="system-adopt-btn recommendation-details-btn">
                                            <i class="fa-solid fa-eye"></i> View Full Details
                                        </button>
                                    </div>
                                </article>
                            @empty
                                <div class="recommendation-empty">No added recommendations for this facility and month yet.</div>
                            @endforelse
                            </div>
                        @endif

                        @foreach($manualRecommendations as $recommendation)
                            <dialog id="recommendationModal{{ $recommendation->id }}" class="recommendation-modal">
                                <div class="recommendation-modal-head">
                                    <div class="recommendation-modal-heading">
                                        <span class="recommendation-modal-heading-icon" aria-hidden="true">
                                            <i class="fa-solid fa-lightbulb"></i>
                                        </span>
                                        <div>
                                            <div class="recommendation-modal-title">Recommendation Details</div>
                                            <div class="recommendation-row-meta">{{ $selectedFacility?->name }} &middot; {{ $selectedMonth }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="recommendation-modal-close" aria-label="Close">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="recommendation-assessment-snapshot">
                                    <div class="record-context-label">Monthly Record Assessment Sent With This Recommendation</div>
                                    <p>{{ $recommendation->generated_message }}</p>
                                </div>
                                @if($canReviewTips)
                                <form method="POST" action="{{ route('modules.energy-conservation.tips.update', $recommendation) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="recommendation-modal-body">
                                        <div class="tip-review-field">
                                            <label>Recommendation</label>
                                            <textarea name="engineer_recommendation" required>{{ $recommendation->engineer_recommendation }}</textarea>
                                        </div>
                                        <div class="tip-review-grid">
                                            <div class="tip-review-field">
                                                <label>Published To</label>
                                                <div class="recommendation-readonly-value">
                                                    {{ $isCprfIntegrationPeriod ? 'CPRF Recommendation List' : 'Facility Recommendation List' }}
                                                </div>
                                                <div class="tip-field-help">
                                                    {{ $isCprfIntegrationPeriod ? 'This recommendation is shared with CPRF.' : 'This recommendation is published for the facility.' }}
                                                </div>
                                            </div>
                                            <div class="tip-review-field">
                                                <label>Approval Status</label>
                                                <select name="status">
                                                    @foreach(['for_review' => 'For Review', 'approved' => 'Approved', 'dismissed' => 'Dismissed'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($recommendation->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="recommendation-row-meta">
                                            Reviewed by {{ $recommendation->reviewer?->username ?? '—' }}
                                            @if($recommendation->reviewed_at) · {{ $recommendation->reviewed_at->format('M d, Y h:i A') }} @endif
                                        </div>
                                    </div>
                                    <div class="recommendation-modal-footer">
                                        <button type="submit"
                                                form="deleteRecommendation{{ $recommendation->id }}"
                                                class="tip-action dismiss"
                                                onclick="return confirm('Delete this recommendation?')">
                                            Delete Recommendation
                                        </button>
                                        <button type="button" class="tip-action save recommendation-modal-close">Cancel</button>
                                        <button type="submit" class="tip-action approve">Save Changes</button>
                                    </div>
                                </form>
                                @else
                                    <div class="recommendation-modal-body">
                                        <div class="tip-review-field">
                                            <label>Recommendation</label>
                                            <div class="recommendation-readonly-value is-long">{{ $recommendation->engineer_recommendation }}</div>
                                        </div>
                                        <div class="tip-review-grid">
                                            <div class="tip-review-field">
                                                <label>Published To</label>
                                                <div class="recommendation-readonly-value">
                                                    {{ $isCprfIntegrationPeriod ? 'CPRF Recommendation List' : 'Facility Recommendation List' }}
                                                </div>
                                            </div>
                                            <div class="tip-review-field">
                                                <label>Approval Status</label>
                                                <div class="recommendation-readonly-value">
                                                    {{ ucwords(str_replace('_', ' ', $recommendation->status)) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="recommendation-modal-footer">
                                        <span class="recommendation-row-meta">Approved recommendation</span>
                                        <button type="button" class="tip-action save recommendation-modal-close">Close</button>
                                    </div>
                                @endif
                                @if($canReviewTips)
                                    <form id="deleteRecommendation{{ $recommendation->id }}"
                                          method="POST"
                                          action="{{ route('modules.energy-conservation.tips.destroy', $recommendation) }}"
                                          hidden>
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </dialog>
                        @endforeach

                        <div class="recommendation-section-heading">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> System-Generated Recommendation
                        </div>
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
                                            <span class="energy-tip-priority">System / {{ $tip['priority'] }}</span>
                                        </div>
                                        @if(!empty($tip['assessment_message']))
                                            <div class="energy-tip-message"><strong>Assessment:</strong> {{ $tip['assessment_message'] }}</div>
                                            <div class="energy-tip-message"><strong>Recommended action:</strong> {{ $tip['message'] }}</div>
                                        @else
                                            <div class="energy-tip-message">{{ $tip['message'] }}</div>
                                        @endif
                                        @if(!empty($tip['metric']))
                                            <div class="energy-tip-metric"><i class="fa-solid fa-chart-simple"></i> {{ $tip['metric'] }}</div>
                                        @endif
                                        @if($canReviewTips && !empty($tip['facility_id']))
                                            <button type="button"
                                                    class="system-adopt-btn"
                                                    data-system-recommendation="{{ $tip['message'] }}"
                                                    data-ai-recommendation-url="{{ route('modules.energy-monitoring.ai-recommendation', ['facility' => $tip['facility_id'], 'month' => $selectedMonth]) }}">
                                                <i class="fa-solid fa-brain"></i> Use AI Alerts Suggestion
                                            </button>
                                            <a class="system-adopt-btn"
                                               href="{{ route('modules.ai-alerts.index', ['month' => $selectedMonth]) }}">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open AI Alerts
                                            </a>
                                            <div class="ai-source-status" data-ai-source-status aria-live="polite"></div>
                                            <div class="tip-field-help">
                                                {{ $isCprfIntegrationPeriod
                                                    ? 'The AI Alerts suggestion is only a draft. Review it before publishing to CPRF.'
                                                    : 'The AI Alerts suggestion is only a draft. Review it before publishing to the facility recommendation list.' }}
                                            </div>
                                        @endif
                                        @if($review)
                                            <div class="tip-review-status">
                                                Recommendation: {{ strtoupper(str_replace('_', ' ', $reviewStatus)) }}
                                                @if($review->reviewer) &middot; Reviewed by {{ $review->reviewer->username }} @endif
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="feature-point">
                                    <i class="fa-solid fa-clock"></i>
                                    <span>No monthly energy data is available for a system-generated recommendation.</span>
                                </div>
                            @endforelse
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </div>
</div>
@if($featureSlug === 'energy-saving-tips')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const panel = document.getElementById('recommendationActionPanel');
        const recommendationInput = document.getElementById('manualRecommendationText');

        document.querySelectorAll('[data-system-recommendation]').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!panel || !recommendationInput) return;

                panel.open = true;
                recommendationInput.value = button.dataset.systemRecommendation || '';
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const status = button.parentElement?.querySelector('[data-ai-source-status]');
                const originalHtml = button.innerHTML;
                const insightUrl = button.dataset.aiRecommendationUrl || '';

                if (!insightUrl) {
                    window.setTimeout(() => recommendationInput.focus(), 350);
                    return;
                }

                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin"></i> Loading AI Alerts suggestion...';
                status?.classList.remove('is-error');
                status?.classList.add('is-visible');
                if (status) status.innerHTML = '<i class="fa-solid fa-brain"></i> Analyzing the matching facility and month...';

                try {
                    const url = new URL(insightUrl, window.location.origin);
                    url.searchParams.set('_refresh', Date.now().toString());
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Cache-Control': 'no-cache',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });
                    if (!response.ok) throw new Error('AI Alerts suggestion request failed');

                    const data = await response.json();
                    if (String(data.recommendation || '').trim() !== '') {
                        recommendationInput.value = String(data.recommendation).trim();
                    }
                    const isAi = String(data.recommendation_source || 'rules').toLowerCase() === 'ai';
                    if (status) {
                        status.innerHTML = isAi
                            ? '<i class="fa-solid fa-wand-magic-sparkles"></i> AI-generated draft loaded from AI Alerts. Review before publishing.'
                            : '<i class="fa-solid fa-chart-line"></i> Live rule fallback loaded from AI Alerts. Review before publishing.';
                    }
                } catch (error) {
                    status?.classList.add('is-error');
                    if (status) status.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> AI Alerts could not refresh. The computed fallback is loaded for review.';
                } finally {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                    window.setTimeout(() => recommendationInput.focus(), 150);
                }
            });
        });

        const openRecommendationModal = (row) => {
            const modal = document.getElementById(row.dataset.recommendationDialog || '');
            if (modal && typeof modal.showModal === 'function') modal.showModal();
        };

        document.querySelectorAll('.recommendation-row').forEach((row) => {
            row.addEventListener('click', () => openRecommendationModal(row));
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openRecommendationModal(row);
                }
            });
        });

        document.querySelectorAll('.recommendation-modal-close').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog')?.close());
        });

        document.querySelectorAll('.recommendation-modal').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) modal.close();
            });
        });

        document.querySelectorAll('.recommendation-delete-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm('Delete this recommendation? This action cannot be undone.')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endif
@if($featureSlug === 'daily-checklist')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const taskModal = document.getElementById('checklistTaskModal');
        const taskSelect = document.getElementById('checklist_task_label');
        const periodInput = document.getElementById('checklist_task_period');
        const routinePreview = document.getElementById('checklistRoutinePreview');

        const syncRoutine = () => {
            const period = taskSelect?.selectedOptions?.[0]?.dataset?.period;
            if (!periodInput || !routinePreview) return;

            if (!period) {
                periodInput.value = '';
                routinePreview.innerHTML = '<i class="fa-solid fa-clock"></i> Select an instruction';
                return;
            }

            periodInput.value = period;
            routinePreview.innerHTML = `<i class="fa-solid fa-clock"></i> ${period.charAt(0).toUpperCase()}${period.slice(1)} routine`;
        };

        taskSelect?.addEventListener('change', syncRoutine);
        syncRoutine();

        if (taskModal?.dataset.hasErrors === 'true') taskModal.showModal();
        taskModal?.addEventListener('click', (event) => {
            const bounds = taskModal.getBoundingClientRect();
            const inside = event.clientX >= bounds.left && event.clientX <= bounds.right
                && event.clientY >= bounds.top && event.clientY <= bounds.bottom;
            if (!inside) taskModal.close();
        });

        document.querySelectorAll('[data-delete-checklist-task-url]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!window.confirm('Remove this checklist task?')) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = button.dataset.deleteChecklistTaskUrl;
                form.hidden = true;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = @json(csrf_token());

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.append(csrf, method);
                document.body.appendChild(form);
                form.submit();
            });
        });
    });
</script>
@endif
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
