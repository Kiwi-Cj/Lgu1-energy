@extends('layouts.qc-admin')
@section('title', 'Inactive Facilities')

@section('content')

@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif
@if(session('error'))
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif

<script>
window.addEventListener('DOMContentLoaded', function() {
    var success = document.getElementById('successAlert');
    var error = document.getElementById('errorAlert');
    if (success) setTimeout(() => success.style.display = 'none', 3000);
    if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>

<style>
    .inactive-report-container {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        padding: 28px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
        border: 1px solid #e2e8f0;
    }

    .inactive-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .inactive-heading {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .inactive-heading-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #e11d48;
        background: linear-gradient(145deg, #fff1f2, #ffe4e6);
        border: 1px solid #fecdd3;
        font-size: 1.35rem;
        box-shadow: 0 8px 18px rgba(225, 29, 72, 0.12);
    }

    .inactive-page-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.6rem, 2.2vw, 2.1rem);
        font-weight: 850;
        line-height: 1.15;
        letter-spacing: -.03em;
    }

    .inactive-page-description {
        margin: 6px 0 0;
        color: #64748b;
        font-weight: 500;
        font-size: .95rem;
    }

    .inactive-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .inactive-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        background: #2563eb;
        color: #fff;
        font-weight: 750;
        font-size: .92rem;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.22);
        transition: all .2s ease;
    }

    .inactive-back-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        color: #fff;
    }

    .inactive-notice-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 14px;
        background: #fff8f8;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: .92rem;
        font-weight: 600;
        margin-bottom: 22px;
    }

    .inactive-notice-banner i {
        font-size: 1.25rem;
        color: #ea580c;
        flex: 0 0 auto;
    }

    .inactive-toolbar {
        margin: 0 0 22px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #f8fafc;
    }

    .inactive-search-wrap {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .inactive-search-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        min-height: 46px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .inactive-search-input-wrap:focus-within {
        border-color: #e11d48;
        box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.12);
    }

    .inactive-search-input-wrap i {
        color: #94a3b8;
        font-size: 1rem;
        margin-right: 10px;
    }

    .inactive-search-input {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        color: #0f172a;
        font-size: .94rem;
        font-family: inherit;
    }

    .inactive-search-meta {
        font-size: .8rem;
        color: #64748b;
        font-weight: 600;
    }

    .inactive-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-top: 20px;
    }

    @media (max-width: 1350px) {
        .inactive-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .inactive-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 560px) {
        .inactive-grid { grid-template-columns: minmax(0, 1fr); }
    }

    .inactive-facility-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        transition: all 0.25s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .inactive-facility-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.10);
        border-color: #cbd5e1;
    }

    .inactive-card-image-wrap {
        width: 100%;
        height: 120px;
        overflow: hidden;
        background: #f1f5f9;
        position: relative;
        border-bottom: 1px solid #e2e8f0;
    }

    .inactive-card-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: grayscale(80%);
        transition: filter .3s ease, transform .3s ease;
    }

    .inactive-facility-card:hover .inactive-card-image-wrap img {
        filter: grayscale(20%);
        transform: scale(1.04);
    }

    .inactive-card-status-badge {
        position: absolute;
        top: 9px;
        right: 9px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 5px 9px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        color: #be123c;
        font-size: .65rem;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .035em;
    }

    .inactive-card-body {
        padding: 14px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .inactive-type-badge {
        font-size: .65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        background: #f1f5f9;
        padding: 3px 10px;
        border-radius: 100px;
        display: inline-block;
        align-self: flex-start;
    }

    .inactive-card-title {
        margin: 4px 0 2px;
        color: #1e293b;
        font-size: .95rem;
        font-weight: 850;
        line-height: 1.2;
    }

    .inactive-card-location {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: .75rem;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inactive-card-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }

    .inactive-action-btn {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #dbe3ef;
        border-radius: 10px;
        background: #fff;
        color: #475569;
        padding: 6px 10px;
        font-size: .74rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: all .18s ease;
    }

    .inactive-action-btn:hover {
        transform: translateY(-1px);
        border-color: #93c5fd;
        color: #1d4ed8;
        background: #f8fbff;
    }

    .inactive-action-btn.reactivate {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .inactive-action-btn.reactivate:hover {
        background: #d1fae5;
        border-color: #6ee7b7;
        color: #065f46;
    }

    .inactive-action-btn.primary {
        grid-column: 1 / -1;
        color: #fff;
        border-color: #2563eb;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.16);
    }

    .inactive-action-btn.primary:hover {
        color: #fff;
        background: linear-gradient(135deg, #1d4ed8, #4338ca);
    }

    .inactive-empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 56px 24px;
        background: #fff;
        border-radius: 20px;
        border: 2px dashed #cbd5e1;
    }

    .inactive-empty-state i {
        color: #10b981;
        margin-bottom: 16px;
    }

    .inactive-empty-state h3 {
        margin: 0 0 6px;
        color: #0f172a;
        font-size: 1.25rem;
        font-weight: 800;
    }

    .inactive-empty-state p {
        margin: 0 0 18px;
        color: #64748b;
        font-size: .95rem;
    }

    /* Dark Mode */
    :is(html.dark-mode, body.dark-mode) .inactive-report-container {
        background: #0f172a !important;
        border-color: #1e293b !important;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.55) !important;
    }

    :is(html.dark-mode, body.dark-mode) .inactive-page-title { color: #f8fafc !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-page-description { color: #94a3b8 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-toolbar { background: #0b1220 !important; border-color: #1e293b !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-search-input-wrap { background: #111827 !important; border-color: #334155 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-search-input { color: #e2e8f0 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-notice-banner { background: #1c130c !important; border-color: #7c2d12 !important; color: #fed7aa !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-facility-card { background: #111827 !important; border-color: #1e293b !important; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4) !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-facility-card:hover { border-color: #e11d48 !important; box-shadow: 0 14px 32px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(225, 29, 72, 0.35) !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-card-image-wrap { background: #0b1220 !important; border-color: #1e293b !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-card-status-badge {
        background: rgba(76, 5, 25, 0.88) !important;
        color: #fb7185 !important;
        border: 1px solid #9f1239 !important;
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5) !important;
    }
    :is(html.dark-mode, body.dark-mode) .inactive-type-badge {
        background: #1e293b !important;
        color: #94a3b8 !important;
        border: 1px solid #334155 !important;
    }
    :is(html.dark-mode, body.dark-mode) .inactive-card-title { color: #f8fafc !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-card-location { color: #94a3b8 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-action-btn { background: #0b1220 !important; border-color: #334155 !important; color: #cbd5e1 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-action-btn:hover { background: #1e293b !important; border-color: #475569 !important; color: #ffffff !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-action-btn.reactivate { background: #064e3b !important; border-color: #047857 !important; color: #6ee7b7 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-action-btn.reactivate:hover { background: #065f46 !important; border-color: #059669 !important; color: #a7f3d0 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-action-btn.primary {
        background: linear-gradient(135deg, #1d4ed8, #4338ca) !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    :is(html.dark-mode, body.dark-mode) .inactive-empty-state { background: #111827 !important; border-color: #334155 !important; }
    :is(html.dark-mode, body.dark-mode) .inactive-empty-state h3 { color: #f8fafc !important; }
</style>

<div class="inactive-page-container" style="width:100%; margin:0 auto;">
    <div class="inactive-report-container">
        <header class="inactive-dashboard-header">
            <div class="inactive-heading">
                <span class="inactive-heading-icon" aria-hidden="true">
                    <i class="fa-solid fa-building-circle-xmark"></i>
                </span>
                <div>
                    <h1 class="inactive-page-title">Inactive Facilities</h1>
                    <p class="inactive-page-description">Dedicated place for all deactivated and non-operational facilities.</p>
                </div>
            </div>
            <div class="inactive-header-actions">
                <a href="{{ route('facilities.index') }}" class="inactive-back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Active Facilities ({{ $activeFacilitiesCount }})
                </a>
            </div>
        </header>

        <div class="inactive-notice-banner">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                Facilities in this section are currently non-operational or deactivated. You can click <strong>Reactivate</strong> on any facility at any time to return it to active operations and resume live energy monitoring.
            </div>
        </div>

        <div class="inactive-toolbar">
            <div class="inactive-search-wrap">
                <label for="inactiveSearchInput" style="font-weight:750;font-size:0.82rem;color:#475569;">Search Inactive Facilities</label>
                <div class="inactive-search-input-wrap">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        id="inactiveSearchInput"
                        type="text"
                        class="inactive-search-input"
                        placeholder="Search by facility name, type, barangay, address, or meter..."
                        autocomplete="off"
                    >
                </div>
                <div class="inactive-search-meta">
                    Showing <span id="inactiveVisibleCount">{{ $facilities->count() }}</span> of {{ $totalInactive }} inactive facilities
                </div>
            </div>
        </div>

        <div class="inactive-grid" id="inactiveGrid">
            @forelse($facilities as $facility)
                @php
                    $searchIndex = Str::lower(trim(implode(' ', [
                        (string) ($facility->name ?? ''),
                        (string) ($facility->type ?? ''),
                        (string) ($facility->address ?? ''),
                        (string) ($facility->barangay ?? ''),
                        (string) ($facility->searchMeterNames ?? ''),
                        (string) ($facility->searchSubmeterNames ?? ''),
                    ])));
                    $imageUrl = $facility->resolved_image_url;
                @endphp
                <div class="inactive-facility-card"
                     data-inactive-card
                     data-search="{{ $searchIndex }}"
                     data-name="{{ Str::lower((string) $facility->name) }}">
                    <div class="inactive-card-image-wrap">
                        <span class="inactive-card-status-badge"><i class="fa-solid fa-ban"></i> Inactive</span>
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $facility->name }}">
                        @else
                            <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#94a3b8;background:#f8fafc;">
                                <i class="fa-solid fa-building" style="font-size:1.8rem;"></i>
                                <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;">No photo</span>
                            </div>
                        @endif
                    </div>
                    <div class="inactive-card-body">
                        <span class="inactive-type-badge">{{ $facility->type ?? 'General' }}</span>
                        <h3 class="inactive-card-title">{{ $facility->name }}</h3>
                        <p class="inactive-card-location" title="{{ $facility->address ?? 'No address provided' }}">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>{{ Str::limit($facility->address ?: $facility->barangay ?: 'No address', 45) }}</span>
                        </p>

                        <div class="inactive-card-actions">
                            <a href="{{ route('modules.facilities.show', $facility->id) }}" class="inactive-action-btn primary">
                                View Details <i class="fas fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('facilities.monthly-records', $facility->id) }}" class="inactive-action-btn" title="View Records">
                                <i class="fas fa-file-alt"></i> Records
                            </a>
                            @if($canManageFacility)
                            <form method="POST" action="{{ route('facilities.reactivate', $facility->id) }}" onsubmit="return confirm('Reactivate {{ $facility->name }} back to active operations?');" style="margin:0;display:inline;">
                                @csrf
                                <button type="submit" class="inactive-action-btn reactivate" title="Reactivate this facility" style="width:100%;">
                                    <i class="fa-solid fa-rotate-left"></i> Reactivate
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="inactive-empty-state">
                    <i class="fa-solid fa-circle-check fa-3x"></i>
                    <h3>No Inactive Facilities</h3>
                    <p>All facilities in the registry are currently active and operational.</p>
                    <a href="{{ route('facilities.index') }}" class="inactive-back-btn">
                        <i class="fa-solid fa-arrow-left"></i> Go to Facility Registry
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inactiveSearchInput');
    const cards = Array.from(document.querySelectorAll('[data-inactive-card]'));
    const visibleCountEl = document.getElementById('inactiveVisibleCount');

    const applySearch = () => {
        if (!cards.length) return;
        const query = (searchInput?.value || '').toLowerCase().trim();
        const tokens = query === '' ? [] : query.split(/\s+/).filter(Boolean);
        let visibleCount = 0;

        cards.forEach((card) => {
            const haystack = (card.getAttribute('data-search') || '').toLowerCase();
            const isMatch = tokens.every((t) => haystack.includes(t));
            card.style.display = isMatch ? '' : 'none';
            if (isMatch) visibleCount++;
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = String(visibleCount);
        }
    };

    searchInput?.addEventListener('input', applySearch);
});
</script>

@endsection
