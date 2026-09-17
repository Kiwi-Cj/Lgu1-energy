@extends('layouts.qc-admin')
@section('title', 'Conservation Program')

@section('content')
@php
    $featureCatalog = $featureCatalog ?? [];
    $workspaceSlugs = ['energy-saving-tips', 'daily-checklist', 'conservation-goals'];
    $workspaceFeatures = collect($workspaceSlugs)
        ->filter(fn ($slug) => isset($featureCatalog[$slug]))
        ->mapWithKeys(fn ($slug) => [$slug => $featureCatalog[$slug]]);
    $workspacePurpose = [
        'energy-saving-tips' => 'Assign & verify',
        'daily-checklist' => 'Daily routine',
        'conservation-goals' => 'Measure targets',
    ];
    $workspaceStats = [
        'energy-saving-tips' => ($stats['approved_recommendations'] ?? 0) . ' Active Actions',
        'daily-checklist' => ($stats['checklist_completed_today'] ?? 0) . ' Completed Today',
        'conservation-goals' => ($stats['active_goals_count'] ?? 0) . ' Active Targets',
    ];
    $canViewReports = \App\Support\RoleAccess::can(auth()->user(), 'access_reports');
    $workflow = [
        ['number' => 1, 'title' => 'Detect', 'description' => 'AI Alert identifies the issue.', 'icon' => 'fa-solid fa-triangle-exclamation', 'tone' => 'detect'],
        ['number' => 2, 'title' => 'Assign', 'description' => 'Approve one action and owner.', 'icon' => 'fa-solid fa-user-check', 'tone' => 'plan'],
        ['number' => 3, 'title' => 'Execute', 'description' => 'Staff completes the action.', 'icon' => 'fa-solid fa-clipboard-check', 'tone' => 'execute'],
        ['number' => 4, 'title' => 'Verify', 'description' => 'Compare the result with target.', 'icon' => 'fa-solid fa-bullseye', 'tone' => 'measure'],
        ['number' => 5, 'title' => 'Report', 'description' => 'Review the verified outcome.', 'icon' => 'fa-solid fa-chart-column', 'tone' => 'report'],
    ];
@endphp

<style>
    .conservation-shell {
        position: relative;
        overflow: hidden;
        width: 100%;
        padding: 30px 32px 36px;
        border: 1px solid #dbe5f3;
        border-radius: 25px;
        background: #f6f8fc;
        box-shadow: 0 16px 44px rgba(37,99,235,.12);
        display: grid;
        gap: 24px;
    }
    .conservation-shell:before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 220px;
        background: radial-gradient(circle at 10% 0, rgba(16,185,129,.14), transparent 46%), radial-gradient(circle at 88% 0, rgba(59,130,246,.12), transparent 38%);
        pointer-events: none;
    }
    .conservation-hero, .workflow-wrap, .workspace-section, .kpi-strip, .compliance-guide {
        position: relative;
    }
    .conservation-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #047857;
        font-size: .76rem;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
    }
    .conservation-title {
        margin: 8px 0 6px;
        color: #0f172a;
        font-size: clamp(1.7rem, 2.7vw, 2.55rem);
        font-weight: 950;
        letter-spacing: -.035em;
    }
    .conservation-subtitle {
        max-width: 820px;
        color: #64748b;
        font-size: .94rem;
        line-height: 1.55;
    }
    .flow-rule {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 12px;
        color: #0f766e;
        font-size: .74rem;
        font-weight: 900;
    }
    .flow-rule i {
        width: 24px;
        height: 24px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #d1fae5;
    }
    .conservation-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }
    .conservation-hero-copy {
        min-width: 0;
    }
    .conservation-entry-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .conservation-entry-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 9px 15px;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        background: #fff;
        color: #047857;
        text-decoration: none;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(15,23,42,.04);
        transition: .18s ease;
    }
    .conservation-entry-action.primary {
        border-color: #059669;
        background: #059669;
        color: #fff;
        box-shadow: 0 6px 16px rgba(5,150,105,.24);
    }
    .conservation-entry-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15,23,42,.09);
    }

    /* Program KPI Strip */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    .kpi-card {
        padding: 16px 18px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(15,23,42,.03);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: .17s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15,23,42,.06);
    }
    .kpi-icon-wrap {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
    }
    .kpi-icon-wrap.green { background: #dcfce7; color: #15803d; }
    .kpi-icon-wrap.blue { background: #dbeafe; color: #1d4ed8; }
    .kpi-icon-wrap.amber { background: #fef3c7; color: #b45309; }
    .kpi-icon-wrap.indigo { background: #e0e7ff; color: #4338ca; }
    .kpi-meta { min-width: 0; }
    .kpi-label { font-size: .72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .kpi-value { font-size: 1.25rem; font-weight: 950; color: #0f172a; line-height: 1.2; margin-top: 2px; }
    .kpi-note { font-size: .68rem; font-weight: 700; color: #059669; margin-top: 2px; }

    /* Section Labels */
    .section-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .section-label h2 {
        margin: 0;
        color: #1e293b;
        font-size: .84rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .07em;
    }
    .section-label span {
        color: #94a3b8;
        font-size: .73rem;
    }

    /* Workflow Strip */
    .workflow {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 9px;
    }
    .workflow-step {
        position: relative;
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: start;
        gap: 10px;
        min-height: 98px;
        padding: 14px;
        border: 1px solid #dbe4f0;
        border-radius: 16px;
        background: rgba(255,255,255,.85);
        color: inherit;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(15,23,42,.03);
        transition: .17s ease;
    }
    .workflow-step:hover {
        transform: translateY(-2px);
        border-color: #a7f3d0;
        box-shadow: 0 9px 20px rgba(15,23,42,.06);
    }
    .workflow-number {
        width: 31px;
        height: 31px;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: #ecfdf5;
        color: #047857;
        font-size: .75rem;
        font-weight: 950;
    }
    .workflow-copy strong {
        display: block;
        color: #0f172a;
        font-size: .82rem;
        font-weight: 950;
    }
    .workflow-copy small {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: .7rem;
        line-height: 1.4;
    }
    .workflow-icon {
        position: absolute;
        right: 12px;
        bottom: 10px;
        color: #a7f3d0;
        font-size: 1rem;
    }
    .workflow-step.plan .workflow-number { background: #eef2ff; color: #4f46e5; }
    .workflow-step.plan .workflow-icon { color: #c7d2fe; }
    .workflow-step.execute .workflow-number { background: #eff6ff; color: #2563eb; }
    .workflow-step.execute .workflow-icon { color: #bfdbfe; }
    .workflow-step.measure .workflow-number { background: #fff7ed; color: #c2410c; }
    .workflow-step.measure .workflow-icon { color: #fed7aa; }
    .workflow-step.report .workflow-number { background: #f5f3ff; color: #7c3aed; }
    .workflow-step.report .workflow-icon { color: #ddd6fe; }

    /* Workspaces Grid */
    .workspace-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .workspace-card {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-rows: auto 1fr auto;
        gap: 14px;
        min-height: 200px;
        padding: 22px;
        border: 1px solid #dbe4f0;
        border-radius: 20px;
        background: #ffffff;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(15,23,42,.05);
        transition: .18s ease;
    }
    .workspace-card:before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 5px;
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .workspace-card:nth-child(2):before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .workspace-card:nth-child(3):before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .workspace-card:hover {
        transform: translateY(-4px);
        border-color: #93c5fd;
        box-shadow: 0 16px 36px rgba(15,23,42,.1);
    }
    .workspace-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .workspace-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: #ecfdf5;
        color: #047857;
        font-size: 1.25rem;
    }
    .workspace-state {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 999px;
        background: #f0fdf4;
        color: #15803d;
        font-size: .64rem;
        font-weight: 950;
        text-transform: uppercase;
        border: 1px solid #dcfce7;
    }
    .workspace-card:nth-child(2) .workspace-icon { background: #eff6ff; color: #2563eb; }
    .workspace-card:nth-child(2) .workspace-state { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .workspace-card:nth-child(3) .workspace-icon { background: #fff7ed; color: #c2410c; }
    .workspace-card:nth-child(3) .workspace-state { background: #fff7ed; color: #b45309; border-color: #fef3c7; }
    .workspace-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.08rem;
        font-weight: 950;
    }
    .workspace-desc {
        margin-top: 6px;
        color: #64748b;
        font-size: .82rem;
        line-height: 1.5;
    }
    .workspace-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }
    .workspace-live-stat {
        font-size: .72rem;
        font-weight: 900;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .workspace-live-stat i { color: #059669; }
    .workspace-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #2563eb;
        font-size: .75rem;
        font-weight: 900;
    }

    /* Compliance & GEMP Best Practices Banner */
    .compliance-guide {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 22px 24px;
        box-shadow: 0 6px 20px rgba(15,23,42,.04);
    }
    .compliance-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .compliance-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: .92rem;
        font-weight: 950;
        margin: 0;
    }
    .compliance-title i { color: #059669; font-size: 1.1rem; }
    .compliance-badge {
        padding: 4px 10px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .compliance-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    .compliance-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .compliance-item strong {
        color: #0f172a;
        font-size: .82rem;
        font-weight: 900;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .compliance-item p {
        margin: 0;
        color: #64748b;
        font-size: .75rem;
        line-height: 1.45;
    }

    /* Dark Mode */
    body.dark-mode .conservation-shell { background: #0f172a; border-color: #334155; }
    body.dark-mode .conservation-title, body.dark-mode .section-label h2, body.dark-mode .workflow-copy strong, body.dark-mode .workspace-title, body.dark-mode .compliance-title, body.dark-mode .compliance-item strong, body.dark-mode .kpi-value { color: #f8fafc; }
    body.dark-mode .workflow-step, body.dark-mode .workspace-card, body.dark-mode .kpi-card, body.dark-mode .compliance-guide { background: #18181b; border-color: #334155; }
    body.dark-mode .compliance-item { background: #0f172a; border-color: #334155; }
    body.dark-mode .conservation-subtitle, body.dark-mode .workflow-copy small, body.dark-mode .workspace-desc, body.dark-mode .compliance-item p, body.dark-mode .kpi-label, body.dark-mode .section-label span { color: #cbd5e1; }
    body.dark-mode .conservation-entry-action { background: #18181b; border-color: #047857; color: #a7f3d0; }
    body.dark-mode .conservation-entry-action.primary { background: #047857; color: #fff; }
    body.dark-mode .workspace-footer { border-color: #334155; }
    body.dark-mode .workspace-live-stat { color: #94a3b8; }

    /* Dark Mode Icons & Workflow Badges */
    body.dark-mode .kpi-icon-wrap.green { background: rgba(16,185,129,.2); color: #6ee7b7; }
    body.dark-mode .kpi-icon-wrap.blue { background: rgba(59,130,246,.2); color: #93c5fd; }
    body.dark-mode .kpi-icon-wrap.amber { background: rgba(245,158,11,.2); color: #fbbf24; }
    body.dark-mode .kpi-icon-wrap.indigo { background: rgba(99,102,241,.2); color: #a5b4fc; }

    body.dark-mode .workflow-number { background: rgba(16,185,129,.2); color: #6ee7b7; }
    body.dark-mode .workflow-step.plan .workflow-number { background: rgba(99,102,241,.2); color: #a5b4fc; }
    body.dark-mode .workflow-step.execute .workflow-number { background: rgba(59,130,246,.2); color: #93c5fd; }
    body.dark-mode .workflow-step.measure .workflow-number { background: rgba(245,158,11,.2); color: #fbbf24; }
    body.dark-mode .workflow-step.report .workflow-number { background: rgba(168,85,247,.2); color: #d8b4fe; }

    body.dark-mode .workflow-icon { color: rgba(16,185,129,.35); }
    body.dark-mode .workflow-step.plan .workflow-icon { color: rgba(99,102,241,.35); }
    body.dark-mode .workflow-step.execute .workflow-icon { color: rgba(59,130,246,.35); }
    body.dark-mode .workflow-step.measure .workflow-icon { color: rgba(245,158,11,.35); }
    body.dark-mode .workflow-step.report .workflow-icon { color: rgba(168,85,247,.35); }

    body.dark-mode .workspace-icon { background: rgba(16,185,129,.2); color: #6ee7b7; }
    body.dark-mode .workspace-card:nth-child(2) .workspace-icon { background: rgba(59,130,246,.2); color: #93c5fd; }
    body.dark-mode .workspace-card:nth-child(3) .workspace-icon { background: rgba(245,158,11,.2); color: #fbbf24; }

    body.dark-mode .workspace-state { background: rgba(16,185,129,.18); border-color: rgba(16,185,129,.35); color: #6ee7b7; }
    body.dark-mode .workspace-card:nth-child(2) .workspace-state { background: rgba(59,130,246,.18); border-color: rgba(59,130,246,.35); color: #93c5fd; }
    body.dark-mode .workspace-card:nth-child(3) .workspace-state { background: rgba(245,158,11,.18); border-color: rgba(245,158,11,.35); color: #fbbf24; }

    body.dark-mode .flow-rule { color: #5eead4; }
    body.dark-mode .flow-rule i { background: rgba(16,185,129,.2); color: #6ee7b7; }
    body.dark-mode .compliance-badge { background: rgba(16,185,129,.2); color: #6ee7b7; }

    @media(max-width: 1050px) {
        .kpi-strip { grid-template-columns: repeat(2, 1fr); }
        .workflow { grid-template-columns: repeat(3, 1fr); }
        .workspace-grid { grid-template-columns: 1fr 1fr; }
        .compliance-grid { grid-template-columns: 1fr; }
        .conservation-hero { align-items: flex-start; flex-direction: column; }
        .conservation-entry-actions { justify-content: flex-start; width: 100%; }
    }
    @media(max-width: 720px) {
        .conservation-shell { padding: 22px 18px; }
        .kpi-strip { grid-template-columns: 1fr; }
        .workflow { display: flex; overflow-x: auto; padding-bottom: 5px; }
        .workflow-step { flex: 0 0 205px; }
        .workspace-grid { grid-template-columns: 1fr; }
    }
</style>

<section class="conservation-shell">
    <!-- Hero Header -->
    <header class="conservation-hero">
        <div class="conservation-hero-copy">
            <div class="conservation-kicker"><i class="fa-solid fa-leaf"></i> Conservation Program</div>
            <h1 class="conservation-title">From alert to verified savings</h1>
            <div class="conservation-subtitle">Use one action record per energy issue. AI Alerts detects the risk; this workspace owns assignment, execution, and verification.</div>
            <div class="flow-rule"><i class="fa-solid fa-code-merge"></i> One issue &rarr; one owner &rarr; one action record &rarr; one verified result</div>
        </div>
        <div class="conservation-entry-actions">
            <a class="conservation-entry-action primary" href="{{ route('modules.ai-alerts.index', ['month' => $selectedMonth]) }}">
                <i class="fa-solid fa-triangle-exclamation"></i> Start from AI Alerts
            </a>
            @if($canViewReports)
                <a class="conservation-entry-action" href="{{ route('reports.performance-summary') }}">
                    <i class="fa-solid fa-chart-column"></i> View performance summary
                </a>
            @endif
        </div>
    </header>

    <!-- Program Metrics Strip -->
    <section class="kpi-strip" aria-label="Conservation program metrics">
        <div class="kpi-card">
            <div class="kpi-icon-wrap green"><i class="fa-solid fa-lightbulb"></i></div>
            <div class="kpi-meta">
                <div class="kpi-label">Active Recommendations</div>
                <div class="kpi-value">{{ $stats['approved_recommendations'] ?? 0 }}</div>
                <div class="kpi-note">{{ $stats['total_recommendations'] ?? 0 }} total issued</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap blue"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="kpi-meta">
                <div class="kpi-label">Daily Checklist Today</div>
                <div class="kpi-value">{{ $stats['checklist_completed_today'] ?? 0 }} / {{ $stats['checklist_total'] ?? 0 }}</div>
                <div class="kpi-note">Opening &amp; closing tasks</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap amber"><i class="fa-solid fa-bullseye"></i></div>
            <div class="kpi-meta">
                <div class="kpi-label">Conservation Goals</div>
                <div class="kpi-value">{{ $stats['active_goals_count'] ?? 0 }} Active</div>
                <div class="kpi-note">Monthly &amp; Annual Targets</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrap indigo"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="kpi-meta">
                <div class="kpi-label">GEMP Compliance Target</div>
                <div class="kpi-value">&minus;10% kWh</div>
                <div class="kpi-note">RA 11285 Mandate</div>
            </div>
        </div>
    </section>

    <!-- Workflow Steps Strip -->
    <section class="workflow-wrap" aria-label="Conservation workflow">
        <div class="section-label">
            <h2>Standard workflow</h2>
            <span>Guide only; use the workspaces below to update work</span>
        </div>
        <div class="workflow">
            @foreach($workflow as $step)
                <div class="workflow-step {{ $step['tone'] }}">
                    <span class="workflow-number">{{ $step['number'] }}</span>
                    <span class="workflow-copy">
                        <strong>{{ $step['title'] }}</strong>
                        <small>{{ $step['description'] }}</small>
                    </span>
                    <i class="workflow-icon {{ $step['icon'] }}"></i>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 3 Conservation Workspaces -->
    <section class="workspace-section">
        <div class="section-label">
            <h2>Conservation workspaces</h2>
            <span>Only the three tools that create or update conservation work</span>
        </div>
        <div class="workspace-grid">
            @foreach($workspaceFeatures as $slug => $feature)
                <a class="workspace-card" href="{{ route('modules.energy-conservation.feature', ['feature' => $slug, 'month' => $selectedMonth]) }}">
                    <div class="workspace-head">
                        <span class="workspace-icon"><i class="{{ $feature['icon'] }}"></i></span>
                        <span class="workspace-state">{{ $workspacePurpose[$slug] ?? 'Workspace' }}</span>
                    </div>
                    <div>
                        <h2 class="workspace-title">{{ $feature['title'] }}</h2>
                        <div class="workspace-desc">{{ $feature['description'] }}</div>
                    </div>
                    <div class="workspace-footer">
                        <span class="workspace-live-stat">
                            <i class="fa-solid fa-circle-dot"></i> {{ $workspaceStats[$slug] ?? 'Active' }}
                        </span>
                        <span class="workspace-action">
                            <span>Open workspace</span> <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- GEMP & RA 11285 Quick Best Practices Guide -->
    <section class="compliance-guide" aria-label="LGU Energy Efficiency Best Practices">
        <div class="compliance-head">
            <h3 class="compliance-title">
                <i class="fa-solid fa-seedling"></i> LGU Energy Efficiency &amp; Conservation Quick Guide
            </h3>
            <span class="compliance-badge"><i class="fa-solid fa-award"></i> RA 11285 / GEMP Standards</span>
        </div>
        <div class="compliance-grid">
            <div class="compliance-item">
                <strong><i class="fa-solid fa-snowflake" style="color:#0284c7;"></i> Air-Conditioning (50–60% Load)</strong>
                <p>Maintain thermostat strictly at <strong>24°C–26°C</strong>. Keep doors/windows shut and schedule AC cutoff 30 minutes before 5:00 PM closing.</p>
            </div>
            <div class="compliance-item">
                <strong><i class="fa-solid fa-lightbulb" style="color:#d97706;"></i> Lighting Optimization</strong>
                <p>Maximize natural daylight during morning hours. Turn off office lights during the <strong>12:00 PM – 1:00 PM</strong> lunch break and after hours.</p>
            </div>
            <div class="compliance-item">
                <strong><i class="fa-solid fa-plug-circle-xmark" style="color:#dc2626;"></i> Phantom Load &amp; IT Devices</strong>
                <p>Shut down computers and monitors. Unplug water dispensers, microwave ovens, and power strips before leaving the building.</p>
            </div>
        </div>
    </section>
</section>
@endsection
