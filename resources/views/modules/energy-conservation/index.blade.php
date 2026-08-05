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
    .conservation-shell{position:relative;overflow:hidden;width:100%;padding:30px 32px 36px;border:1px solid #dbe5f3;border-radius:25px;background:#f6f8fc;box-shadow:0 16px 44px rgba(37,99,235,.12);display:grid;gap:23px}.conservation-shell:before{content:"";position:absolute;inset:0 0 auto;height:190px;background:radial-gradient(circle at 10% 0,rgba(16,185,129,.12),transparent 46%),radial-gradient(circle at 88% 0,rgba(59,130,246,.11),transparent 38%);pointer-events:none}.conservation-hero,.workflow-wrap,.workspace-section{position:relative}.conservation-kicker{display:inline-flex;align-items:center;gap:7px;color:#047857;font-size:.74rem;font-weight:950;letter-spacing:.1em;text-transform:uppercase}.conservation-title{margin:8px 0 6px;color:#0f172a;font-size:clamp(1.7rem,2.7vw,2.55rem);font-weight:950;letter-spacing:-.035em}.conservation-subtitle{max-width:780px;color:#64748b;font-size:.94rem;line-height:1.55}.flow-rule{display:flex;align-items:center;gap:7px;margin-top:12px;color:#0f766e;font-size:.72rem;font-weight:900}.flow-rule i{width:24px;height:24px;display:grid;place-items:center;border-radius:8px;background:#d1fae5}
    .section-label{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:11px}.section-label h2{margin:0;color:#1e293b;font-size:.82rem;font-weight:950;text-transform:uppercase;letter-spacing:.07em}.section-label span{color:#94a3b8;font-size:.72rem}.workflow{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}.workflow-step{position:relative;display:grid;grid-template-columns:auto 1fr;align-items:start;gap:10px;min-height:112px;padding:14px;border:1px solid #dbe4f0;border-radius:16px;background:#fff;color:inherit;text-decoration:none;box-shadow:0 6px 18px rgba(15,23,42,.045);transition:.17s ease}.workflow-step[href]:hover{transform:translateY(-2px);border-color:#a7f3d0;box-shadow:0 11px 24px rgba(15,23,42,.08)}.workflow-step.is-disabled{opacity:.58}.workflow-number{width:31px;height:31px;display:grid;place-items:center;border-radius:10px;background:#ecfdf5;color:#047857;font-size:.75rem;font-weight:950}.workflow-copy strong{display:block;color:#0f172a;font-size:.8rem;font-weight:950}.workflow-copy small{display:block;margin-top:5px;color:#64748b;font-size:.69rem;line-height:1.4}.workflow-icon{position:absolute;right:12px;bottom:10px;color:#a7f3d0;font-size:1rem}.workflow-step.plan .workflow-number{background:#eef2ff;color:#4f46e5}.workflow-step.plan .workflow-icon{color:#c7d2fe}.workflow-step.execute .workflow-number{background:#eff6ff;color:#2563eb}.workflow-step.execute .workflow-icon{color:#bfdbfe}.workflow-step.measure .workflow-number{background:#fff7ed;color:#c2410c}.workflow-step.measure .workflow-icon{color:#fed7aa}.workflow-step.report .workflow-number{background:#f5f3ff;color:#7c3aed}.workflow-step.report .workflow-icon{color:#ddd6fe}
    .workspace-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.workspace-card{position:relative;overflow:hidden;display:grid;grid-template-rows:auto 1fr auto;gap:13px;min-height:190px;padding:19px;border:1px solid #dbe4f0;border-radius:19px;background:#fff;color:inherit;text-decoration:none;box-shadow:0 8px 22px rgba(15,23,42,.05);transition:.17s ease}.workspace-card:before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,#10b981,#34d399)}.workspace-card:nth-child(2):before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}.workspace-card:nth-child(3):before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}.workspace-card:hover{transform:translateY(-3px);border-color:#a7f3d0;box-shadow:0 15px 32px rgba(15,23,42,.09)}.workspace-head{display:flex;align-items:center;justify-content:space-between;gap:10px}.workspace-icon{width:44px;height:44px;display:grid;place-items:center;border-radius:14px;background:#ecfdf5;color:#047857}.workspace-state{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;background:#f0fdf4;color:#15803d;font-size:.62rem;font-weight:950;text-transform:uppercase}.workspace-title{margin:0;color:#0f172a;font-size:1rem;font-weight:950}.workspace-desc{margin-top:6px;color:#64748b;font-size:.79rem;line-height:1.5}.workspace-action{display:flex;align-items:center;justify-content:space-between;color:#2563eb;font-size:.73rem;font-weight:900}.workspace-card:nth-child(2) .workspace-icon{background:#eff6ff;color:#2563eb}.workspace-card:nth-child(3) .workspace-icon{background:#fff7ed;color:#c2410c}.conservation-note{display:flex;gap:10px;padding:13px 15px;border:1px solid #bae6fd;border-radius:14px;background:#f0f9ff;color:#075985;font-size:.76rem;line-height:1.45}.conservation-note i{margin-top:2px}
    .conservation-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:24px}.conservation-hero-copy{min-width:0}.conservation-entry-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}.conservation-entry-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:8px 13px;border:1px solid #bbf7d0;border-radius:11px;background:#fff;color:#047857;text-decoration:none;font-size:.72rem;font-weight:900;white-space:nowrap;transition:.17s ease}.conservation-entry-action.primary{border-color:#059669;background:#059669;color:#fff}.conservation-entry-action:hover{transform:translateY(-1px);box-shadow:0 7px 16px rgba(15,23,42,.08)}.workflow-step{min-height:96px;background:rgba(255,255,255,.72);box-shadow:none;cursor:default}
    body.dark-mode .conservation-shell{background:#0f172a;border-color:#334155}body.dark-mode .conservation-title,body.dark-mode .section-label h2,body.dark-mode .workflow-copy strong,body.dark-mode .workspace-title{color:#f8fafc}body.dark-mode .workflow-step,body.dark-mode .workspace-card{background:#18181b;border-color:#334155}body.dark-mode .conservation-subtitle,body.dark-mode .workflow-copy small,body.dark-mode .workspace-desc{color:#cbd5e1}body.dark-mode .conservation-note{background:#10233c;border-color:#1e40af;color:#bfdbfe}
    body.dark-mode .conservation-entry-action{background:#18181b;border-color:#047857;color:#a7f3d0}body.dark-mode .conservation-entry-action.primary{background:#047857;color:#fff}
    @media(max-width:1050px){.workflow{grid-template-columns:repeat(3,1fr)}.workspace-grid{grid-template-columns:1fr 1fr}}@media(max-width:720px){.conservation-shell{padding:22px 17px}.workflow{display:flex;overflow-x:auto;padding-bottom:5px}.workflow-step{flex:0 0 205px}.workspace-grid{grid-template-columns:1fr}}@media(max-width:460px){.section-label{align-items:start;display:grid}}
    @media(max-width:1050px){.conservation-hero{align-items:flex-start;flex-direction:column}.conservation-entry-actions{justify-content:flex-start}}@media(max-width:460px){.conservation-entry-actions,.conservation-entry-action{width:100%}}
</style>

<section class="conservation-shell">
    <header class="conservation-hero">
        <div class="conservation-hero-copy">
        <div class="conservation-kicker"><i class="fa-solid fa-leaf"></i> Conservation Program</div>
        <h1 class="conservation-title">From alert to verified savings</h1>
        <div class="conservation-subtitle">Use one action record per energy issue. AI Alerts detects the risk; this workspace owns assignment, execution, and verification.</div>
        <div class="flow-rule"><i class="fa-solid fa-code-merge"></i> One issue &rarr; one owner &rarr; one action record &rarr; one verified result</div>
        </div>
        <div class="conservation-entry-actions">
            <a class="conservation-entry-action primary" href="{{ route('modules.ai-alerts.index', ['month' => $selectedMonth]) }}"><i class="fa-solid fa-triangle-exclamation"></i> Start from AI Alerts</a>
            @if($canViewReports)
                <a class="conservation-entry-action" href="{{ route('reports.efficiency-summary') }}"><i class="fa-solid fa-chart-column"></i> View verified results</a>
            @endif
        </div>
    </header>

    <section class="workflow-wrap" aria-label="Conservation workflow">
        <div class="section-label"><h2>Standard workflow</h2><span>Guide only; use the workspaces below to update work</span></div>
        <div class="workflow">
            @foreach($workflow as $step)
                <div class="workflow-step {{ $step['tone'] }}">
                    <span class="workflow-number">{{ $step['number'] }}</span>
                    <span class="workflow-copy"><strong>{{ $step['title'] }}</strong><small>{{ $step['description'] }}</small></span>
                    <i class="workflow-icon {{ $step['icon'] }}"></i>
                </div>
            @endforeach
        </div>
    </section>

    <section class="workspace-section">
        <div class="section-label"><h2>Conservation workspaces</h2><span>Only the three tools that create or update conservation work</span></div>
        <div class="workspace-grid">
            @foreach($workspaceFeatures as $slug => $feature)
                <a class="workspace-card" href="{{ route('modules.energy-conservation.feature', ['feature' => $slug, 'month' => $selectedMonth]) }}">
                    <div class="workspace-head"><span class="workspace-icon"><i class="{{ $feature['icon'] }}"></i></span><span class="workspace-state">{{ $workspacePurpose[$slug] ?? 'Workspace' }}</span></div>
                    <div><h2 class="workspace-title">{{ $feature['title'] }}</h2><div class="workspace-desc">{{ $feature['description'] }}</div></div>
                    <div class="workspace-action"><span>Open workspace</span><i class="fa-solid fa-arrow-right"></i></div>
                </a>
            @endforeach
        </div>
    </section>

</section>
@endsection
