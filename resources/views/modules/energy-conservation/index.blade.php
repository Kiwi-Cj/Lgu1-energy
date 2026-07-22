@extends('layouts.qc-admin')
@section('title', 'Energy Conservation')

@section('content')
@php
    $featureCatalog = $featureCatalog ?? [];
    $activeFeatures = collect($featureCatalog)->filter(
        fn (array $feature, string $slug) => ($feature['status'] ?? null) === 'enabled' && $slug !== 'ai-recommendations'
    );
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
    body.dark-mode .conservation-shell,
    body.dark-mode .feature-card {
        background: #0f172a;
        border-color: #334155;
    }
    body.dark-mode .conservation-title,
    body.dark-mode .feature-title {
        color: #f8fafc;
    }
    body.dark-mode .conservation-subtitle,
    body.dark-mode .feature-desc {
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
            Manage energy-saving activities, daily checklists, recommendations, savings, suggestions, and reports.
        </div>
    </div>

    <div class="feature-grid">
        @foreach($activeFeatures as $slug => $feature)
            <a class="feature-card" href="{{ route('modules.energy-conservation.feature', ['feature' => $slug, 'month' => $selectedMonth]) }}">
                <div class="feature-card-head">
                    <div class="feature-icon"><i class="{{ $feature['icon'] }}"></i></div>
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
