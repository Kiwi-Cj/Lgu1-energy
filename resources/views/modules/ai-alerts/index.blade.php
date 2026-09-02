@extends('layouts.qc-admin')
@section('title', 'AI Alerts')

@section('content')
@php
    $canAccessConservation = \App\Support\RoleAccess::can(auth()->user(), 'access_energy_conservation');
    $canAccessIncidents = \App\Support\RoleAccess::can(auth()->user(), 'access_reports');
    $canAccessLoadTracking = \App\Support\RoleAccess::can(auth()->user(), 'view_load_tracking');
    $canEncodeMonthly = \App\Support\RoleAccess::can(auth()->user(), 'encode_main_meter_readings')
        || \App\Support\RoleAccess::can(auth()->user(), 'manage_facility_master');
@endphp
<style>
    .ai-shell{position:relative;overflow:hidden;padding:32px;border:1px solid #dbe5f3;border-radius:26px;background:#f6f8fc;box-shadow:0 18px 50px rgba(30,64,175,.11);display:grid;gap:22px}
    .ai-shell:before{content:"";position:absolute;inset:0 0 auto;height:220px;background:radial-gradient(circle at 12% 0,rgba(99,102,241,.14),transparent 52%),radial-gradient(circle at 88% 5%,rgba(6,182,212,.12),transparent 42%);pointer-events:none}
    .ai-head,.ai-exec-banner,.ai-summary,.ai-toolbar,.ai-list{position:relative}
    .ai-head{display:flex;justify-content:space-between;align-items:end;gap:24px;flex-wrap:wrap}
    .ai-kicker{display:inline-flex;align-items:center;gap:8px;color:#4f46e5;font-size:.73rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}
    .ai-title{margin:8px 0 5px;color:#0f172a;font-size:clamp(1.8rem,3vw,2.75rem);line-height:1;font-weight:950;letter-spacing:-.04em}
    .ai-sub{color:#64748b;max-width:760px;line-height:1.55;font-size:.9rem}
    .ai-live{display:inline-flex;align-items:center;gap:6px;margin-top:12px;padding:6px 11px;border:1px solid #bbf7d0;border-radius:999px;background:#f0fdf4;color:#15803d;font-size:.68rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
    .ai-live:before{content:"";width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.16);animation:pulse-dot 2s infinite}
    @keyframes pulse-dot{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.25);opacity:.7}}
    .ai-period{display:flex;gap:9px;align-items:end;padding:12px;border:1px solid #dbe4f0;border-radius:16px;background:rgba(255,255,255,.86);box-shadow:0 8px 24px rgba(15,23,42,.06);backdrop-filter:blur(10px)}
    .ai-period label{display:grid;gap:6px;color:#475569;font-size:.72rem;font-weight:900}
    .ai-period input{min-width:176px;padding:11px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font:inherit;font-weight:700}
    .ai-period button{padding:12px 18px;border:0;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#4338ca);box-shadow:0 7px 15px rgba(79,70,229,.28);color:#fff;font-weight:900;cursor:pointer;display:inline-flex;align-items:center;gap:7px}
    .ai-period button:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(79,70,229,.35)}

    /* Executive Portfolio Health Banner */
    .ai-exec-banner{display:grid;grid-template-columns:1.8fr 1fr;gap:20px;padding:22px 26px;border-radius:20px;border:1px solid #c7d2fe;background:linear-gradient(135deg,rgba(255,255,255,.95) 0%,rgba(238,242,255,.85) 100%);box-shadow:0 10px 30px rgba(99,102,241,.08);backdrop-filter:blur(10px)}
    .ai-exec-banner.danger{border-color:#fecaca;background:linear-gradient(135deg,rgba(255,255,255,.95) 0%,rgba(254,242,242,.85) 100%)}
    .ai-exec-banner.warn{border-color:#fde68a;background:linear-gradient(135deg,rgba(255,255,255,.95) 0%,rgba(255,251,235,.85) 100%)}
    .ai-exec-content{display:flex;flex-direction:column;justify-content:center}
    .ai-exec-tag{display:inline-flex;align-items:center;gap:7px;font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:#4338ca;margin-bottom:8px}
    .ai-exec-tag.danger{color:#b91c1c}
    .ai-exec-tag.warn{color:#b45309}
    .ai-exec-tag.good{color:#047857}
    .ai-exec-title{margin:0 0 6px;color:#0f172a;font-size:1.25rem;font-weight:950;line-height:1.25}
    .ai-exec-desc{margin:0;color:#475569;font-size:.85rem;line-height:1.55}
    .ai-exec-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;background:rgba(255,255,255,.75);padding:14px;border-radius:14px;border:1px solid rgba(203,213,225,.6)}
    .ai-exec-stat-box{text-align:center}
    .ai-exec-stat-num{display:block;font-size:1.15rem;font-weight:950;color:#0f172a;line-height:1.2}
    .ai-exec-stat-num.danger{color:#dc2626}
    .ai-exec-stat-num.warn{color:#d97706}
    .ai-exec-stat-label{display:block;font-size:.65rem;font-weight:800;color:#64748b;text-transform:uppercase;margin-top:4px;letter-spacing:.03em}

    .ai-summary{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
    .ai-stat{position:relative;overflow:hidden;display:flex;align-items:center;gap:12px;padding:15px;border:1px solid #dbe4f0;border-radius:18px;background:#fff;box-shadow:0 7px 20px rgba(15,23,42,.045);cursor:pointer;text-align:left;font:inherit;width:100%;transition:all .18s ease}
    .ai-stat:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.085);border-color:#a5b4fc}
    .ai-stat:focus-visible{outline:2px solid #4f46e5;outline-offset:2px}
    .ai-stat.is-active{border-color:#4338ca;box-shadow:0 0 0 2px rgba(67,56,202,.25),0 10px 24px rgba(67,56,202,.12);background:#fafbff}
    .ai-stat.danger.is-active{border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.25),0 10px 24px rgba(220,38,38,.12);background:#fff8f8}
    .ai-stat.warn.is-active{border-color:#d97706;box-shadow:0 0 0 2px rgba(217,119,6,.25),0 10px 24px rgba(217,119,6,.12);background:#fffdf5}
    .ai-stat.good.is-active{border-color:#059669;box-shadow:0 0 0 2px rgba(5,150,105,.25),0 10px 24px rgba(5,150,105,.12);background:#f4fdf8}
    .ai-stat-icon{flex:0 0 42px;height:42px;display:grid;place-items:center;border-radius:13px;background:#e0e7ff;color:#4338ca;font-size:1rem;transition:transform .18s ease}
    .ai-stat:hover .ai-stat-icon{transform:scale(1.06)}
    .ai-stat-copy{min-width:0}
    .ai-stat-label{display:block;color:#64748b;font-size:.66rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
    .ai-stat strong{display:block;margin-top:3px;color:#0f172a;font-size:1.55rem;line-height:1;font-weight:950}
    .ai-stat small{display:block;margin-top:4px;color:#94a3b8;font-size:.66rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .ai-stat.danger .ai-stat-icon{background:#fee2e2;color:#dc2626}.ai-stat.danger strong{color:#dc2626}
    .ai-stat.warn .ai-stat-icon{background:#fef3c7;color:#d97706}.ai-stat.warn strong{color:#d97706}
    .ai-stat.good .ai-stat-icon{background:#d1fae5;color:#059669}.ai-stat.good strong{color:#059669}
    .ai-stat.urgent-action{border-color:#fecaca;background:#fffafb}
    .ai-stat.urgent-action .ai-stat-icon{background:#fee2e2;color:#b91c1c}
    .ai-exec-stat-box.is-clickable{cursor:pointer;border-radius:10px;padding:6px 4px;transition:all .15s ease}
    .ai-exec-stat-box.is-clickable:hover{background:rgba(255,255,255,.9);transform:translateY(-1px)}

    .ai-toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 18px;border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 4px 14px rgba(15,23,42,.03)}
    .ai-search{position:relative;flex:1;max-width:480px}
    .ai-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#94a3b8}
    .ai-search input{width:100%;padding:10px 12px 10px 37px;border:1px solid #d7deea;border-radius:10px;color:#0f172a;background:#f8fafc;font-size:.82rem;font-weight:600}
    .ai-visible-count{color:#64748b;font-size:.75rem;font-weight:850;white-space:nowrap;padding:4px 10px;background:#f1f5f9;border-radius:999px}

    .ai-list{display:grid;gap:16px}
    .ai-card{position:relative;overflow:hidden;padding:22px 24px;border:1px solid #dbe4f0;border-radius:22px;background:#fff;box-shadow:0 9px 26px rgba(15,23,42,.055);transition:transform .18s ease,box-shadow .18s ease}
    .ai-card:before{content:"";position:absolute;inset:0 auto 0 0;width:5px;background:#10b981}
    .ai-card.is-risk:before{background:#ef4444}
    .ai-card.is-drop:before,.ai-card.is-cost:before{background:#f59e0b}
    .ai-card.is-empty-data:before{background:#94a3b8}
    .ai-card:hover{transform:translateY(-2px);box-shadow:0 15px 34px rgba(15,23,42,.09)}
    .ai-card-top{display:flex;justify-content:space-between;gap:16px;align-items:start}
    .ai-facility{display:flex;gap:12px;align-items:center}
    .ai-facility-icon{flex:0 0 44px;height:44px;display:grid;place-items:center;border-radius:13px;background:#eef2ff;color:#4f46e5;font-size:1.1rem}
    .ai-card h2{margin:0;color:#0f172a;font-size:1.1rem;font-weight:950}
    .ai-facility-meta{display:flex;align-items:center;gap:7px;margin-top:5px;color:#64748b;font-size:.74rem;flex-wrap:wrap}
    .ai-facility-meta span+span:before{content:"•";margin-right:7px;color:#cbd5e1}
    .ai-badges{display:flex;gap:7px;flex-wrap:wrap;justify-content:end}
    .ai-badge{display:inline-flex;align-items:center;gap:5px;padding:6px 11px;border:1px solid #bbf7d0;border-radius:999px;font-size:.68rem;font-weight:900;background:#f0fdf4;color:#166534}
    .ai-badge.danger{border-color:#fecaca;background:#fef2f2;color:#b91c1c}
    .ai-badge.warn{border-color:#fde68a;background:#fffbeb;color:#a16207}
    .ai-badge.muted{border-color:#e2e8f0;background:#f8fafc;color:#64748b}
    .ai-badge.brand{border-color:#c7d2fe;background:#eef2ff;color:#4338ca}

    .ai-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:16px}
    .ai-metric{padding:14px;border:1px solid #edf1f6;border-radius:15px;background:#f8fafc}
    .ai-metric-head{display:flex;justify-content:space-between;gap:8px;align-items:center;color:#64748b;font-size:.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.035em}
    .ai-metric-icon{width:27px;height:27px;display:grid;place-items:center;border-radius:8px;background:#e0e7ff;color:#4f46e5}
    .ai-metric-value{display:block;margin-top:8px;color:#172554;font-size:1.05rem;font-weight:950}
    .ai-metric-note{display:flex;justify-content:space-between;gap:8px;margin-top:6px;color:#64748b;font-size:.7rem}
    .ai-delta{font-weight:900}.ai-delta.up{color:#dc2626}.ai-delta.down{color:#059669}
    .ai-progress{overflow:hidden;height:5px;margin-top:10px;border-radius:999px;background:#e2e8f0}
    .ai-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#6366f1,#818cf8)}
    .ai-progress.cost span{background:linear-gradient(90deg,#f59e0b,#ef4444)}

    /* Empty Data Notice State */
    .ai-missing-data-notice{display:flex;align-items:center;gap:14px;margin-top:16px;padding:14px 18px;border-radius:14px;border:1px dashed #cbd5e1;background:#f8fafc}
    .ai-missing-data-icon{flex:0 0 34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#f1f5f9;color:#64748b;font-size:1rem}
    .ai-missing-data-copy{flex:1;min-width:0}
    .ai-missing-data-copy strong{display:block;font-size:.82rem;font-weight:900;color:#334155}
    .ai-missing-data-copy span{display:block;font-size:.74rem;color:#64748b;margin-top:2px}

    .ai-tip{display:flex;gap:12px;margin-top:14px;padding:15px 18px;border:1px solid #c7d2fe;border-radius:16px;background:linear-gradient(135deg,#eef2ff,#f5f3ff);color:#3730a3;line-height:1.5;font-size:.83rem}
    .ai-tip-main-icon{flex:0 0 32px;height:32px;display:grid;place-items:center;border-radius:10px;background:#4f46e5;color:#fff}
    .ai-tip-copy{flex:1;min-width:0}
    .ai-tip-title{display:block;margin-bottom:2px;color:#312e81;font-size:.72rem;font-weight:950;text-transform:uppercase;letter-spacing:.05em}
    .ai-tip-meta{display:flex;align-items:center;gap:7px;margin-top:9px;color:#6366f1;font-size:.66rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
    .ai-tip-meta.is-loading>i{animation:ai-spin .8s linear infinite}
    .ai-refresh{margin-left:auto;padding:5px 10px;border:1px solid #c7d2fe;border-radius:8px;background:rgba(255,255,255,.65);color:#4f46e5;font:inherit;font-size:.72rem;font-weight:850;cursor:pointer;display:inline-flex;align-items:center;gap:5px}
    .ai-refresh:hover{background:#fff;border-color:#818cf8}
    .ai-refresh:disabled{opacity:.65;cursor:wait}

    .ai-empty{text-align:center;padding:46px;color:#64748b}
    .ai-empty i{display:block;margin-bottom:12px;font-size:2rem;color:#94a3b8}
    .ai-hidden{display:none!important}
    @keyframes ai-spin{to{transform:rotate(360deg)}}

    .ai-card-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9}
    .ai-card-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 13px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#475569;text-decoration:none;font-size:.72rem;font-weight:900;transition:all .15s ease}
    .ai-card-action:hover{border-color:#a5b4fc;background:#f8fafc;color:#4338ca}
    .ai-card-action.primary{border-color:#4f46e5;background:#4f46e5;color:#fff}
    .ai-card-action.primary:hover{background:#4338ca;color:#fff}
    .ai-card-action.load-track{border-color:#cbd5e1;background:#fff;color:#0369a1}
    .ai-card-action.load-track:hover{border-color:#7dd3fc;background:#f0f9ff;color:#0284c7}
    .ai-card-action.urgent{border-color:#dc2626;background:#dc2626;color:#fff}
    .ai-card-action.urgent:hover{background:#b91c1c;color:#fff}
    .ai-routing-note{display:inline-flex;align-items:center;gap:6px;color:#9a3412;font-size:.7rem;font-weight:850;margin-right:auto}

    body.dark-mode .ai-shell{border-color:#334155;background:#0f172a}
    body.dark-mode .ai-title,body.dark-mode .ai-card h2,body.dark-mode .ai-stat strong,body.dark-mode .ai-metric-value,body.dark-mode .ai-exec-title,body.dark-mode .ai-exec-stat-num{color:#f8fafc}
    body.dark-mode .ai-card,body.dark-mode .ai-stat,body.dark-mode .ai-toolbar,body.dark-mode .ai-period{background:#18181b;border-color:#334155}
    body.dark-mode .ai-period input,body.dark-mode .ai-search input{background:#111827;border-color:#475569;color:#fff}
    body.dark-mode .ai-exec-banner{background:linear-gradient(135deg,#18181b,#1f2937);border-color:#374151}
    body.dark-mode .ai-exec-stats{background:#111827;border-color:#374151}
    body.dark-mode .ai-exec-desc{color:#94a3b8}
    body.dark-mode .ai-metric{background:#202127;border-color:#30323a}
    body.dark-mode .ai-tip{background:#28264f;border-color:#4338ca;color:#c7d2fe}
    body.dark-mode .ai-tip-title{color:#e0e7ff}
    body.dark-mode .ai-missing-data-notice{background:#18181b;border-color:#374151}
    body.dark-mode .ai-stat.is-active{background:#27272a;border-color:#6366f1;box-shadow:0 0 0 2px rgba(99,102,241,.3)}
    body.dark-mode .ai-card-action{background:#111827;border-color:#475569;color:#cbd5e1}
    body.dark-mode .ai-card-action.primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
    body.dark-mode .ai-card-actions{border-top-color:#27272a}

    @media(max-width:1200px){.ai-summary{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:1050px){.ai-exec-banner{grid-template-columns:1fr}}
    @media(max-width:800px){
        .ai-shell{padding:21px 16px}
        .ai-head{align-items:stretch}
        .ai-period{width:100%}
        .ai-period label{flex:1}
        .ai-period input{width:100%;min-width:0}
        .ai-summary{grid-template-columns:repeat(2,1fr)}
        .ai-exec-stats{grid-template-columns:1fr 1fr}
        .ai-metrics{grid-template-columns:1fr}
        .ai-card-top,.ai-toolbar{display:grid}
        .ai-badges{justify-content:start}
        .ai-search{max-width:none}
        .ai-visible-count{display:none}
        .ai-card-actions{justify-content:stretch}
        .ai-card-action{flex:1}
    }
    @media(max-width:480px){
        .ai-summary{grid-template-columns:1fr}
        .ai-period{display:grid}
        .ai-exec-stats{grid-template-columns:1fr}
        .ai-card{padding:17px}
        .ai-tip-meta{align-items:flex-start;flex-wrap:wrap}
        .ai-refresh{margin-left:0}
    }
</style>

<section class="ai-shell">
    <header class="ai-head">
        <div>
            <div class="ai-kicker"><i class="fa-solid fa-wand-magic-sparkles"></i> Smart Energy Intelligence</div>
            <h1 class="ai-title">AI Alerts</h1>
            <div class="ai-sub">Review monthly usage exceptions, verified baseline comparisons, cost changes, and on-demand AI guidance before choosing the correct workflow.</div>
            <span class="ai-live">Monthly records connected</span>
        </div>
        <form class="ai-period" method="GET">
            <label>Billing month<input type="month" name="month" value="{{ $periodInput }}" required></label>
            <button type="submit"><i class="fa-solid fa-chart-line"></i> Analyze</button>
        </form>
    </header>

    <!-- AI Executive Summary Banner -->
    <div class="ai-exec-banner {{ $summary['health_tone'] }}">
        <div class="ai-exec-content">
            <span class="ai-exec-tag {{ $summary['health_tone'] }}">
                <i class="fa-solid {{ $summary['health_tone'] === 'danger' ? 'fa-triangle-exclamation' : ($summary['health_tone'] === 'warn' ? 'fa-circle-exclamation' : 'fa-circle-check') }}"></i>
                Portfolio Assessment: {{ $summary['health_status'] }}
            </span>
            <h2 class="ai-exec-title">{{ $period->format('F Y') }} Energy Intelligence Overview</h2>
            <p class="ai-exec-desc">{{ $summary['health_narrative'] }}</p>
        </div>
        <div class="ai-exec-stats">
            <div class="ai-exec-stat-box is-clickable" data-ai-stat-filter="urgent" title="Click to filter urgent facilities">
                <span class="ai-exec-stat-num {{ $summary['urgent'] > 0 ? 'danger' : '' }}">{{ $summary['urgent'] }}</span>
                <span class="ai-exec-stat-label"><i class="fa-solid fa-filter"></i> Urgent Facilities</span>
            </div>
            <div class="ai-exec-stat-box is-clickable" data-ai-stat-filter="usage" title="Click to view facilities with consumption anomalies">
                <span class="ai-exec-stat-num {{ $summary['total_excess_kwh'] > 0 ? 'warn' : '' }}">{{ number_format($summary['total_excess_kwh'], 0) }}</span>
                <span class="ai-exec-stat-label"><i class="fa-solid fa-bolt"></i> Est. Excess kWh</span>
            </div>
            <div class="ai-exec-stat-box is-clickable" data-ai-stat-filter="cost" title="Click to view facilities with cost changes">
                <span class="ai-exec-stat-num">₱{{ number_format($summary['total_cost'], 0) }}</span>
                <span class="ai-exec-stat-label"><i class="fa-solid fa-wallet"></i> Billed Amount</span>
            </div>
        </div>
    </div>

    <div class="ai-summary">
        <button type="button" class="ai-stat" data-ai-stat-filter="all" title="Filter: All Facilities Analyzed">
            <span class="ai-stat-icon"><i class="fa-solid fa-building-circle-check"></i></span>
            <span class="ai-stat-copy">
                <span class="ai-stat-label">Facilities analyzed</span>
                <strong>{{ $summary['facilities'] }}</strong>
                <small>With available meter data</small>
            </span>
        </button>
        <button type="button" class="ai-stat danger" data-ai-stat-filter="usage" title="Filter: Usage Exceptions">
            <span class="ai-stat-icon"><i class="fa-solid fa-bolt"></i></span>
            <span class="ai-stat-copy">
                <span class="ai-stat-label">Usage exceptions</span>
                <strong>{{ $summary['usage_exceptions'] }}</strong>
                <small>High or unusually low usage</small>
            </span>
        </button>
        <button type="button" class="ai-stat warn" data-ai-stat-filter="cost" title="Filter: Cost Increase Alerts">
            <span class="ai-stat-icon"><i class="fa-solid fa-peso-sign"></i></span>
            <span class="ai-stat-copy">
                <span class="ai-stat-label">Cost increase alerts</span>
                <strong>{{ $summary['cost'] }}</strong>
                <small>Above previous month</small>
            </span>
        </button>
        <button type="button" class="ai-stat good" data-ai-stat-filter="normal" title="Filter: Within Expected Range">
            <span class="ai-stat-icon"><i class="fa-solid fa-shield-halved"></i></span>
            <span class="ai-stat-copy">
                <span class="ai-stat-label">Within expected range</span>
                <strong>{{ $summary['normal'] }}</strong>
                <small>No active risk detected</small>
            </span>
        </button>
        <button type="button" class="ai-stat warn" data-ai-stat-filter="baseline-pending" title="Filter: Baseline Pending">
            <span class="ai-stat-icon"><i class="fa-solid fa-chart-line"></i></span>
            <span class="ai-stat-copy">
                <span class="ai-stat-label">Baseline pending</span>
                <strong>{{ $summary['baseline_pending'] }}</strong>
                <small>Need 3–6 approved readings</small>
            </span>
        </button>
        @if(($summary['no_data'] ?? 0) > 0)
            <button type="button" class="ai-stat" data-ai-stat-filter="no-data" title="Filter: Missing Monthly Data">
                <span class="ai-stat-icon" style="background:#f1f5f9;color:#64748b;"><i class="fa-solid fa-folder-minus"></i></span>
                <span class="ai-stat-copy">
                    <span class="ai-stat-label">Missing monthly data</span>
                    <strong>{{ $summary['no_data'] }}</strong>
                    <small>Need bill / meter encoding</small>
                </span>
            </button>
        @endif
    </div>

    <div class="ai-toolbar">
        <label class="ai-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Search facility, type, or department..." value="{{ request('facility', '') }}" data-ai-search></label>
        <span class="ai-visible-count" data-ai-count>{{ $alerts->count() }} facilities</span>
    </div>

    <div class="ai-list" data-ai-list>
        @forelse($alerts as $alert)
            @php
                $usagePercent = $alert['baseline_kwh'] && $alert['baseline_kwh'] > 0 ? ($alert['actual_kwh'] / $alert['baseline_kwh']) * 100 : 0;
                $costPercent = $alert['previous_cost'] > 0 ? ($alert['current_cost'] / $alert['previous_cost']) * 100 : 0;
                $isDrop = str_starts_with($alert['usage_level'], 'Drop');
                $isUsageWarning = $alert['usage_level'] === 'Warning';
                $isUrgent = in_array($alert['usage_level'], ['Critical', 'Drop Critical', 'Very High'], true);
                $isNoBaseline = $alert['has_data'] && $alert['usage_level'] === 'No Data';
                $status = !$alert['has_data'] ? 'no-data' : ($isNoBaseline ? 'baseline-pending' : ($isUrgent ? 'urgent' : ($alert['usage_alert'] ? 'usage' : ($alert['cost_alert'] ? 'cost' : 'normal'))));
                $cardClass = !$alert['has_data'] ? 'is-empty-data' : ($isUrgent ? 'is-risk' : (($isDrop || $isUsageWarning) ? 'is-drop' : ($alert['cost_alert'] ? 'is-cost' : '')));
                $usageBadgeClass = $isUrgent ? 'danger' : (($isDrop || $isUsageWarning) ? 'warn' : ($alert['usage_alert'] ? 'danger' : ($isNoBaseline ? 'muted' : '')));
            @endphp
            <article class="ai-card {{ $cardClass }}" data-ai-card data-status="{{ $status }}" data-urgent="{{ $isUrgent ? '1' : '0' }}" data-usage-alert="{{ $alert['usage_alert'] ? '1' : '0' }}" data-cost-alert="{{ $alert['cost_alert'] ? '1' : '0' }}" data-search="{{ strtolower($alert['facility']->name.' '.($alert['facility']->type ?? '').' '.($alert['facility']->department ?? '')) }}">
                <div class="ai-card-top">
                    <div class="ai-facility">
                        <span class="ai-facility-icon">
                            <i class="fa-solid {{ $isUrgent ? 'fa-triangle-exclamation' : 'fa-building' }}"></i>
                        </span>
                        <div>
                            <h2>{{ $alert['facility']->name }}</h2>
                            <div class="ai-facility-meta">
                                <span>{{ $period->format('F Y') }}</span>
                                @if($alert['facility']->type)<span>{{ $alert['facility']->type }}</span>@endif
                                <span>{{ $alert['source_label'] }}</span>
                                <span>Record: {{ $alert['review_status'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="ai-badges">
                        @if(!$alert['has_data'])
                            <span class="ai-badge muted"><i class="fa-solid fa-circle-minus"></i> No energy data</span>
                        @else
                            <span class="ai-badge {{ $usageBadgeClass }}">
                                <i class="fa-solid {{ $isUrgent ? 'fa-radiation' : ($alert['usage_alert'] ? 'fa-triangle-exclamation' : ($isNoBaseline ? 'fa-circle-info' : 'fa-circle-check')) }}"></i>
                                Usage: {{ $isNoBaseline ? 'Baseline pending' : $alert['usage_level'] }}
                            </span>
                            <span class="ai-badge {{ $alert['cost_alert'] ? 'warn' : '' }}">
                                <i class="fa-solid fa-wallet"></i> {{ $alert['cost_alert'] ? 'Cost increased' : 'No cost increase' }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($alert['has_data'])
                    <div class="ai-metrics">
                        <div class="ai-metric">
                            <div class="ai-metric-head"><span>Energy consumption</span><span class="ai-metric-icon"><i class="fa-solid fa-bolt"></i></span></div>
                            <strong class="ai-metric-value">{{ number_format($alert['actual_kwh'], 2) }} kWh</strong>
                            <div class="ai-metric-note">
                                <span>Baseline {{ $alert['baseline_kwh'] ? number_format($alert['baseline_kwh'], 2).' kWh' : 'unavailable' }}</span>
                                @if($alert['deviation'] !== null)
                                    <span class="ai-delta {{ $alert['deviation'] > 0 ? 'up' : 'down' }}">{{ $alert['deviation'] >= 0 ? '+' : '' }}{{ number_format($alert['deviation'], 1) }}%</span>
                                @endif
                            </div>
                            <div class="ai-progress"><span style="width:{{ min(100, max(0, $usagePercent)) }}%"></span></div>
                        </div>
                        <div class="ai-metric">
                            <div class="ai-metric-head"><span>Monthly energy cost</span><span class="ai-metric-icon"><i class="fa-solid fa-chart-line"></i></span></div>
                            <strong class="ai-metric-value">₱{{ number_format($alert['current_cost'], 2) }}</strong>
                            <div class="ai-metric-note">
                                <span>Recorded for {{ $period->format('M Y') }}</span>
                                @if($alert['cost_variance'] !== null)
                                    <span class="ai-delta {{ $alert['cost_variance'] > 0 ? 'up' : 'down' }}">{{ $alert['cost_variance'] >= 0 ? '+' : '' }}{{ number_format($alert['cost_variance'], 1) }}%</span>
                                @endif
                            </div>
                            <div class="ai-progress cost"><span style="width:{{ min(100, max(0, $costPercent)) }}%"></span></div>
                        </div>
                        <div class="ai-metric">
                            <div class="ai-metric-head"><span>Previous month reference</span><span class="ai-metric-icon"><i class="fa-solid fa-calendar-check"></i></span></div>
                            <strong class="ai-metric-value">{{ $alert['previous_cost'] > 0 ? '₱'.number_format($alert['previous_cost'], 2) : 'No comparison data' }}</strong>
                            <div class="ai-metric-note">
                                <span>{{ $period->copy()->subMonth()->format('F Y') }} actual bill</span>
                                <span>Reference</span>
                            </div>
                            <div class="ai-progress"><span style="width:{{ $alert['previous_cost'] > 0 ? 100 : 0 }}%"></span></div>
                        </div>
                    </div>
                @else
                    <div class="ai-missing-data-notice">
                        <span class="ai-missing-data-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                        <div class="ai-missing-data-copy">
                            <strong>No monthly energy bill or meter readings recorded for {{ $period->format('F Y') }}</strong>
                            <span>To unlock AI anomaly detection, variance alerts, and cost forecasting, please encode or import this facility's monthly reading.</span>
                        </div>
                    </div>
                @endif

                <div class="ai-tip" data-ai-insight data-insight-url="{{ route('modules.energy-monitoring.ai-recommendation', ['facility' => $alert['facility']->id, 'month' => $periodInput]) }}">
                    <span class="ai-tip-main-icon"><i class="fa-solid fa-brain"></i></span>
                    <div class="ai-tip-copy">
                        <span class="ai-tip-title">Recommended next action</span>
                        <span data-ai-tip>{{ $alert['tip'] }}</span>
                        <div class="ai-tip-meta" data-ai-meta>
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Computed assessment · generate AI insight when needed</span>
                            <button class="ai-refresh" type="button" data-ai-refresh><i class="fa-solid fa-wand-magic-sparkles"></i> Generate AI insight</button>
                        </div>
                    </div>
                </div>

                <div class="ai-card-actions">
                    @if($alert['has_data'])
                        <a class="ai-card-action" href="{{ route('facilities.monthly-records', ['facility' => $alert['facility']->id, 'year' => (int) $period->year, 'table_month' => (int) $period->month]) }}">
                            <i class="fa-solid fa-file-invoice-dollar"></i> View source record
                        </a>

                        @if($canAccessLoadTracking)
                            <a class="ai-card-action load-track" href="{{ route('modules.load-tracking.index', ['facility_id' => $alert['facility']->id]) }}" title="Inspect Equipment Load Inventory and Demand Schedules">
                                <i class="fa-solid fa-plug-circle-bolt"></i> Equipment Load Tracking
                            </a>
                        @endif

                        @if(($alert['action_owner'] ?? 'monitor') === \App\Support\EnergyAlertRouting::INCIDENT && $canAccessIncidents)
                            <a class="ai-card-action primary urgent" href="{{ route('energy-incidents.index', ['q' => $alert['facility']->name, 'year' => (int) $period->year, 'month' => (int) $period->month, 'source' => 'auto']) }}">
                                <i class="fa-solid fa-screwdriver-wrench"></i> Open incident &amp; maintenance
                            </a>
                        @elseif(($alert['action_owner'] ?? 'monitor') === \App\Support\EnergyAlertRouting::INCIDENT)
                            <span class="ai-routing-note"><i class="fa-solid fa-shield-halved"></i> Escalated to authorized incident reviewers</span>
                        @elseif($canAccessConservation)
                            <a class="ai-card-action primary" href="{{ route('modules.energy-conservation.feature', array_filter(['feature' => 'energy-saving-tips', 'facility_id' => $alert['facility']->id, 'record_id' => $alert['record_id'], 'month' => $periodInput])) }}">
                                <i class="fa-solid fa-user-check"></i> Review recommendation
                            </a>
                        @endif

                        @if(! $alert['review_ready'])
                            <span class="ai-routing-note"><i class="fa-solid fa-clock"></i> Monthly record is awaiting approval; CPRF publishing remains locked.</span>
                        @endif
                    @else
                        @if($canEncodeMonthly)
                            <a class="ai-card-action primary" href="{{ route('facilities.monthly-records', ['facility' => $alert['facility']->id, 'year' => (int) $period->year, 'table_month' => (int) $period->month]) }}">
                                <i class="fa-solid fa-plus-circle"></i> Encode {{ $period->format('M Y') }} Reading
                            </a>
                        @endif
                        @if($canAccessLoadTracking)
                            <a class="ai-card-action load-track" href="{{ route('modules.load-tracking.index', ['facility_id' => $alert['facility']->id]) }}">
                                <i class="fa-solid fa-plug-circle-bolt"></i> Equipment Load Tracking
                            </a>
                        @endif
                    @endif
                </div>
            </article>
        @empty
            <div class="ai-empty"><i class="fa-regular fa-folder-open"></i>No facilities are available for analysis.</div>
        @endforelse
        <div class="ai-empty ai-hidden" data-ai-filter-empty><i class="fa-solid fa-filter-circle-xmark"></i>No facilities match the selected filter.</div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const insightCards = Array.from(document.querySelectorAll('[data-ai-insight]'));
    const facilityCards = Array.from(document.querySelectorAll('[data-ai-card]'));
    const search = document.querySelector('[data-ai-search]');
    const count = document.querySelector('[data-ai-count]');
    const filterEmpty = document.querySelector('[data-ai-filter-empty]');
    let activeFilter = 'all';

    const applyFilters = () => {
        const query = String(search?.value || '').trim().toLowerCase();
        let visible = 0;
        facilityCards.forEach(card => {
            const statusMatch = activeFilter === 'all'
                || (activeFilter === 'urgent' && card.dataset.urgent === '1')
                || (activeFilter === 'usage' && card.dataset.usageAlert === '1')
                || (activeFilter === 'cost' && card.dataset.costAlert === '1')
                || card.dataset.status === activeFilter;
            const searchMatch = !query || String(card.dataset.search || '').includes(query);
            card.classList.toggle('ai-hidden', !(statusMatch && searchMatch));
            if (statusMatch && searchMatch) visible++;
        });
        if (count) count.textContent = `${visible} ${visible === 1 ? 'facility' : 'facilities'}`;
        filterEmpty?.classList.toggle('ai-hidden', visible !== 0);
    };
    const statCards = Array.from(document.querySelectorAll('[data-ai-stat-filter]'));

    const setFilter = (newFilter) => {
        activeFilter = newFilter;
        statCards.forEach(card => card.classList.toggle('is-active', (card.dataset.aiStatFilter || 'all') === activeFilter));
        applyFilters();
    };

    search?.addEventListener('input', applyFilters);
    statCards.forEach(card => card.addEventListener('click', () => {
        setFilter(card.dataset.aiStatFilter || 'all');
        // Smoothly scroll down to toolbar/list if on mobile or if needed
        const toolbar = document.querySelector('.ai-toolbar');
        if (toolbar && window.innerWidth < 800) {
            toolbar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }));
    setFilter(activeFilter);

    const loadInsight = async (card) => {
        const tip = card.querySelector('[data-ai-tip]');
        const meta = card.querySelector('[data-ai-meta]');
        const label = meta?.querySelector('span');
        const refresh = card.querySelector('[data-ai-refresh]');
        const icon = meta?.querySelector('i');
        meta?.classList.add('is-loading');
        if (label) label.textContent = 'Analyzing live facility data...';
        if (refresh) { refresh.hidden = false; refresh.disabled = true; refresh.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Refreshing...'; }
        if (icon) icon.className = 'fa-solid fa-arrows-rotate';
        try {
            const url = new URL(card.dataset.insightUrl, window.location.origin);
            const variant = Number(card.dataset.insightVariant || 0) + 1;
            card.dataset.insightVariant = String(variant);
            url.searchParams.set('_refresh', Date.now().toString());
            url.searchParams.set('_variant', String(variant));
            url.searchParams.set('_previous', String(tip?.textContent || '').trim().slice(0, 1200));
            const response = await fetch(url.toString(), {method:'GET',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','Cache-Control':'no-cache'},credentials:'same-origin',cache:'no-store'});
            if (!response.ok) throw new Error('Insight request failed');
            const data = await response.json();
            if (data.recommendation && tip) tip.textContent = data.recommendation;
            const isAi = String(data.recommendation_source || 'rules').toLowerCase() === 'ai';
            const analyzedAt = data.analyzed_at ? new Date(data.analyzed_at) : new Date();
            const time = analyzedAt.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
            if (label) label.textContent = `${isAi ? 'AI-generated' : 'Live rule fallback'} · refreshed ${time}`;
            if (icon) icon.className = isAi ? 'fa-solid fa-wand-magic-sparkles' : 'fa-solid fa-chart-line';
        } catch (error) {
            if (label) label.textContent = 'Could not refresh · showing computed fallback';
            if (icon) icon.className = 'fa-solid fa-triangle-exclamation';
        } finally {
            meta?.classList.remove('is-loading');
            if (refresh) { refresh.hidden = false; refresh.disabled = false; refresh.innerHTML = '<i class="fa-solid fa-rotate"></i> Refresh insight'; }
        }
    };
    insightCards.forEach(card => card.querySelector('[data-ai-refresh]')?.addEventListener('click', () => loadInsight(card)));
});
</script>
@endsection
