@extends('layouts.qc-admin')
@section('title', 'Energy Conservation')

@section('content')
@php
    $featureCatalog = $featureCatalog ?? [];
    $featured = collect($featureCatalog)->take(4)->values();
@endphp

<style>
    .conservation-shell {
        width: 100%;
        margin: 0;
        padding: 28px 34px 36px;
        border-radius: 24px;
        background: radial-gradient(circle at top left, #eff6ff 0, #f8fafc 40%, #eef2ff 100%);
        box-shadow: 0 12px 40px rgba(37, 99, 235, .16);
        display: grid;
        gap: 18px;
    }
    .conservation-hero {
        display: grid;
        gap: 12px;
    }
    .conservation-kicker {
        color: #2563eb;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .conservation-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.5rem, 2.2vw, 2.3rem);
        font-weight: 900;
    }
    .conservation-subtitle {
        color: #475569;
        font-size: .98rem;
        line-height: 1.5;
        max-width: 920px;
    }
    .conservation-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .conservation-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.82);
        color: #1e3a8a;
        border: 1px solid #c7d2fe;
        font-size: .78rem;
        font-weight: 800;
    }
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .feature-card {
        display: grid;
        gap: 12px;
        padding: 16px;
        border-radius: 18px;
        border: 1px solid #dbe4f0;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        text-decoration: none;
        color: inherit;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .12);
        border-color: #c7d2fe;
    }
    .feature-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .feature-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ecfeff;
        color: #0f766e;
        box-shadow: inset 0 0 0 1px #cffafe;
        flex: 0 0 46px;
    }
    .feature-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .feature-status.enabled {
        background: #dcfce7;
        color: #166534;
    }
    .feature-status.coming-soon {
        background: #fef3c7;
        color: #92400e;
    }
    .feature-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.02rem;
        font-weight: 900;
    }
    .feature-desc {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.45;
    }
    .feature-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #2563eb;
        font-size: .82rem;
        font-weight: 800;
    }
    .hero-panel {
        border-radius: 18px;
        border: 1px solid #dbe4f0;
        background: linear-gradient(135deg, #ffffff, #eff6ff);
        padding: 18px;
    }
    .hero-panel-title {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 1.08rem;
        font-weight: 900;
    }
    .hero-panel-text {
        color: #475569;
        line-height: 1.5;
        font-size: .95rem;
    }
    .hero-list {
        margin-top: 12px;
        display: grid;
        gap: 8px;
    }
    .hero-item {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        color: #334155;
        font-size: .92rem;
        line-height: 1.4;
    }
    .hero-dot {
        color: #2563eb;
        margin-top: 2px;
    }
    body.dark-mode .conservation-shell,
    body.dark-mode .feature-card,
    body.dark-mode .hero-panel {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .conservation-title,
    body.dark-mode .feature-title,
    body.dark-mode .hero-panel-title {
        color: #f8fafc;
    }
    body.dark-mode .conservation-subtitle,
    body.dark-mode .feature-desc,
    body.dark-mode .hero-panel-text,
    body.dark-mode .hero-item {
        color: #cbd5e1;
    }
    @media (max-width: 900px) {
        .feature-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 560px) {
        .conservation-shell {
            padding: 18px;
        }
    }
</style>

<div class="conservation-shell">
    <div class="conservation-hero">
        <div class="conservation-kicker">Energy Conservation</div>
        <h1 class="conservation-title">Energy Conservation Program</h1>
        <div class="conservation-subtitle">
            Nakaayos na ito bilang hub para sa energy-saving tips, goals, ranking, rewards, AI suggestions, campaigns, checklist, savings, suggestions, at reports.
        </div>
        <div class="conservation-badges">
            <span class="conservation-badge">Enabled: Tips</span>
            <span class="conservation-badge">Enabled: Goals</span>
            <span class="conservation-badge">Enabled: AI</span>
            <span class="conservation-badge">Enabled: Checklist</span>
            <span class="conservation-badge">Coming Soon: Ranking</span>
            <span class="conservation-badge">Coming Soon: Rewards</span>
        </div>
    </div>

    <div class="hero-panel">
        <h2 class="hero-panel-title">What this module contains</h2>
        <div class="hero-panel-text">
            Pili ka ng feature card sa ibaba para buksan ang dedicated page niya. Yung ibang items ay naka-label kung ready na or upcoming pa lang.
        </div>
        <div class="hero-list">
            <div class="hero-item"><span class="hero-dot">•</span><span>Enabled features are ready for content and future wiring.</span></div>
            <div class="hero-item"><span class="hero-dot">•</span><span>Coming Soon features can be built into pages and workflows next.</span></div>
        </div>
    </div>

    <div class="feature-grid">
        @foreach($featureCatalog as $slug => $feature)
            <a class="feature-card" href="{{ route('modules.energy-conservation.feature', ['feature' => $slug, 'month' => $selectedMonth]) }}">
                <div class="feature-card-head">
                    <div class="feature-icon"><i class="{{ $feature['icon'] }}"></i></div>
                    <span class="feature-status {{ $feature['status'] }}">{{ $feature['badge'] }}</span>
                </div>
                <div>
                    <h2 class="feature-title">{{ $feature['title'] }}</h2>
                    <div class="feature-desc">{{ $feature['description'] }}</div>
                </div>
                <div class="feature-meta">
                    <span>Open feature</span>
                    <span><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
