@extends('layouts.qc-admin')
@section('title', 'Facilities')

@section('content')

@php
    $user = auth()->user();
    $archivedFacilitiesCount = $archivedFacilitiesCount ?? 0;
@endphp

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



<!-- ...existing content... -->
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>
<style>
    /* --- Energy Report Inspired Aesthetic --- */
    .report-card-container {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 12px rgba(31,38,135,0.06); 
        padding: 30px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
    }

    /* Modern KPI Cards */
    .stat-card {
        flex: 1;
        min-width: 200px;
        padding: 20px;
        border-radius: 15px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .card-icon-box {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px; font-size: 1rem;
    }

    /* Facility Grid & Cards */
    .facility-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .facility-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .facility-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.1);
        border-color: #dbeafe;
    }

    .image-wrapper {
        width: 100%;
        height: 170px;
        overflow: hidden;
        background: #f8fafc;
        position: relative;
    }

    .image-wrapper img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .facility-card:hover .image-wrapper img { transform: scale(1.1); }

    .content-padding { padding: 20px; flex-grow: 1; }

    .type-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6366f1;
        background: #eef2ff;
        padding: 4px 12px;
        border-radius: 100px;
        margin-bottom: 10px;
        display: inline-block;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #fff !important;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-gradient:hover { opacity: 0.9; transform: translateY(-1px); }

    .card-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    .action-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        text-decoration: none;
    }
    .action-icon.energy { background: #fff7ed; color: #f59e0b; }
    .action-icon.records { background: #eff6ff; color: #3b82f6; }
    .action-icon.inventory { background: #ecfdf3; color: #16a34a; }
    .action-icon:hover { transform: scale(1.1); }

    .facility-search-wrap {
        margin-bottom: 1.25rem;
    }

    .facility-search-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #475569;
    }

    .facility-search-input-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        padding: 10px 12px;
    }

    .facility-search-input-wrap i {
        color: #64748b;
        font-size: 0.95rem;
    }

    .facility-search-input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.95rem;
        color: #1e293b;
        background: transparent;
    }

    .facility-search-clear {
        border: none;
        background: #e2e8f0;
        color: #334155;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        font-size: 0.82rem;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .facility-search-clear[hidden] {
        display: none;
    }

    .facility-search-meta {
        margin-top: 8px;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
    }

    .facility-search-empty {
        display: none;
        margin-top: 14px;
        text-align: center;
        padding: 26px 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
    }

    .facility-search-reset {
        margin-top: 10px;
        border: none;
        border-radius: 8px;
        background: #2563eb;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 8px 12px;
        cursor: pointer;
    }

    .facility-match {
        background: #fef08a;
        color: #1e293b;
        border-radius: 3px;
        padding: 0 2px;
    }

    body.dark-mode .facilities-page .report-card-container {
        background: #0f172a !important;
        border: 1px solid #1f2937;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.55);
    }

    body.dark-mode .facilities-page .stat-card,
    body.dark-mode .facilities-page .facility-card,
    body.dark-mode .facilities-page .image-wrapper {
        background: #111827 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode .facilities-page .facility-card:hover {
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.65);
        border-color: #60a5fa !important;
    }

    body.dark-mode .facilities-page .card-actions {
        border-top-color: #334155;
    }

    body.dark-mode .facilities-page .type-badge {
        background: #1e293b;
        color: #c4b5fd;
    }

    body.dark-mode .facilities-page .action-icon.energy {
        background: #3f2b1a;
        color: #fbbf24;
    }

    body.dark-mode .facilities-page .action-icon.records {
        background: #3f1d2e !important;
        color: #fda4af !important;
    }

    body.dark-mode .facilities-page .action-icon.inventory {
        background: #153827 !important;
        color: #86efac !important;
    }

    body.dark-mode .facilities-page .facility-search-input-wrap {
        background: #111827;
        border-color: #334155;
    }

    body.dark-mode .facilities-page .facility-search-input {
        color: #e2e8f0;
    }

    body.dark-mode .facilities-page .facility-search-clear {
        background: #334155;
        color: #e2e8f0;
    }

    body.dark-mode .facilities-page .facility-search-empty {
        background: #0b1220;
        border-color: #334155;
        color: #cbd5e1;
    }

    body.dark-mode .facilities-page .facility-search-reset {
        background: #3b82f6;
        color: #fff;
    }

    body.dark-mode .facilities-page .facility-match {
        background: #fde047;
        color: #0f172a;
    }

    body.dark-mode .facilities-page [style*="background:#fff"],
    body.dark-mode .facilities-page [style*="background: #fff"],
    body.dark-mode .facilities-page [style*="background:#ffffff"],
    body.dark-mode .facilities-page [style*="background: #ffffff"],
    body.dark-mode .facilities-page [style*="background:#f8fafc"],
    body.dark-mode .facilities-page [style*="background: #f8fafc"],
    body.dark-mode .facilities-page [style*="background:#f1f5f9"],
    body.dark-mode .facilities-page [style*="background: #f1f5f9"] {
        background: #111827 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .facilities-page [style*="color:#222"],
    body.dark-mode .facilities-page [style*="color: #222"],
    body.dark-mode .facilities-page [style*="color:#1e293b"],
    body.dark-mode .facilities-page [style*="color: #1e293b"],
    body.dark-mode .facilities-page [style*="color:#334155"],
    body.dark-mode .facilities-page [style*="color: #334155"],
    body.dark-mode .facilities-page [style*="color:#64748b"],
    body.dark-mode .facilities-page [style*="color: #64748b"],
    body.dark-mode .facilities-page [style*="color:#94a3b8"],
    body.dark-mode .facilities-page [style*="color: #94a3b8"] {
        color: #e2e8f0 !important;
    }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; align-items: stretch; text-align: center; }
        .btn-gradient { justify-content: center; }
        .facilities-page .dashboard-actions { justify-content: center !important; }
        .facilities-page .archive-link {
            padding: 10px 12px !important;
            gap: 6px !important;
            min-width: 42px;
            min-height: 42px;
            justify-content: center;
        }
        .facilities-page .archive-link .archive-label { display: none; }
    }
</style>

<div class="facilities-page" style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="dashboard-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:800; color:#3762c8; margin:0; letter-spacing:-0.5px;">📘 Facilities Management</h2>
                <p style="color:#64748b; margin-top:4px; font-weight:500;">Manage and monitor LGU energy sectors.</p>
            </div>
            <div class="dashboard-actions" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                <a href="{{ route('modules.facilities.archive') }}"
                   class="archive-link"
                   aria-label="Archive"
                   title="Archive"
                   style="display:inline-flex;align-items:center;gap:8px;background:#f8fafc;color:#1e293b;border:1px solid #cbd5e1;border-radius:10px;padding:10px 14px;font-weight:700;text-decoration:none;">
                    <i class="fa fa-archive" aria-hidden="true"></i>
                    <span class="archive-label">Archive</span>
                    @if($archivedFacilitiesCount > 0)
                        <span style="background:#e11d48;color:#fff;border-radius:999px;padding:2px 8px;font-size:0.78rem;">{{ $archivedFacilitiesCount }}</span>
                    @endif
                </a>
                @if(!in_array(strtolower(Auth::user()->role ?? ''), ['energy_officer', 'staff'], true))
                    <button type="button" id="btnAddFacilityTop" class="btn-gradient">
                        <i class="fa fa-plus-circle"></i> Add New Facility
                    </button>
                @endif
            </div>
        </div>

        <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:2rem;">
            <div class="stat-card">
                <div class="card-icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fa fa-building"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Total</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;">{{ $totalFacilities ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="card-icon-box" style="background:#f0fdf4; color:#22c55e;"><i class="fa fa-check-circle"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Active</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;">{{ $activeFacilities ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="card-icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fa fa-wrench"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Maintenance</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;">{{ $maintenanceFacilities ?? 0 }}</div>
            </div>
        </div>

        <div class="facility-search-wrap">
            <label for="facilityLiveSearch" class="facility-search-label">Live Search</label>
            <div class="facility-search-input-wrap">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input
                    id="facilityLiveSearch"
                    type="text"
                    class="facility-search-input"
                    placeholder="Search facility name, type, address, or barangay..."
                    autocomplete="off"
                >
                <button type="button" id="facilitySearchClear" class="facility-search-clear" aria-label="Clear search" title="Clear search" hidden>&times;</button>
            </div>
            <div class="facility-search-meta">
                Showing <span id="facilitySearchVisibleCount">{{ $facilities->count() }}</span> of <span id="facilitySearchTotalCount">{{ $facilities->count() }}</span> facilities
            </div>
            <div class="facility-search-meta">
                Matches facility, type, and address/barangay.
            </div>
        </div>

        <div class="facility-grid">
            @forelse($facilities as $facility)
                @php
                    $searchIndex = Str::lower(trim(implode(' ', [
                        (string) ($facility->name ?? ''),
                        (string) ($facility->type ?? ''),
                        (string) ($facility->address ?? ''),
                        (string) ($facility->barangay ?? ''),
                    ])));
                @endphp
                <div class="facility-card" data-facility-card data-search="{{ $searchIndex }}">
                    <div class="image-wrapper">
                        @php
                            $imageUrl = $facility->resolved_image_url;
                        @endphp
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $facility->name }}">
                        @else
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#cbd5e1;">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        @endif
                        <a href="{{ route('modules.facilities.show', $facility->id) }}" style="position:absolute; inset:0; z-index:1;"></a>
                    </div>

                    <div class="content-padding">
                        <span class="type-badge" data-search-text>{{ $facility->type ?? 'General' }}</span>
                        <h3 data-search-text style="font-size:1.2rem; font-weight:800; color:#1e293b; margin:0 0 8px 0; line-height:1.2;">{{ $facility->name }}</h3>
                        <p style="font-size:0.88rem; color:#64748b; display:flex; align-items:flex-start; gap:6px; margin-bottom:12px;">
                            <i class="fas fa-location-dot" style="color:#94a3b8; margin-top:3px;"></i>
                            <span data-search-text>{{ Str::limit($facility->address ?? 'No address provided', 50) }}</span>
                        </p>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                            <span style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:#475569; letter-spacing:.04em;">Facility Size</span>
                            <span style="display:inline-flex; align-items:center; gap:6px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:999px; padding:4px 10px; font-size:0.78rem; font-weight:800;">
                                <i class="fa fa-chart-bar"></i> {{ $facility->dynamicSize ?? ($facility->size ?? 'N/A') }}
                            </span>
                            @if(isset($facility->resolvedBaselineKwh) && $facility->resolvedBaselineKwh !== null)
                                <span style="font-size:0.78rem; color:#64748b; font-weight:600;">
                                    Baseline: {{ number_format((float) $facility->resolvedBaselineKwh, 2) }} kWh
                                    <i class="fa fa-info-circle"
                                       title="Source: {{ $facility->resolvedBaselineSourceLabel ?? 'Unknown' }}"
                                       style="margin-left:4px;color:#94a3b8;"></i>
                                </span>
                            @endif
                        </div>

                        <div class="card-actions" style="position:relative; z-index:2;">
                            @if((auth()->user()?->role_key ?? str_replace(' ', '_', strtolower((string) (auth()->user()?->role ?? '')))) !== 'staff')
                            <a href="{{ url('/modules/facilities/' . $facility->id . '/energy-profile') }}" class="action-icon energy" title="Energy Profile">
                                <i class="fas fa-bolt"></i>
                            </a>
                            @endif
                            <a href="{{ route('facilities.monthly-records', $facility->id) }}" class="action-icon records" title="Monthly Records" style="background:#fef2f2; color:#e11d48;">
                                <i class="fas fa-file-lines"></i>
                            </a>
                            <a href="{{ route('modules.facilities.equipment-inventory', $facility->id) }}" class="action-icon inventory" title="Equipment Inventory">
                                <i class="fas fa-cubes"></i>
                            </a>
                            <div style="margin-left:auto; display:flex; align-items:center;">
                                <a href="{{ route('modules.facilities.show', $facility->id) }}" style="font-size:0.95rem; font-weight:700; color:#2563eb; text-transform:none; text-decoration:none; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:4px; transition:color 0.18s;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#2563eb'">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align:center; padding:60px; background:#fff; border-radius:20px; border:2px dashed #cbd5e1;">
                    <i class="fas fa-building fa-3x" style="color:#cbd5e1; margin-bottom:15px;"></i>
                    <h3 style="color:#64748b;">No facilities found</h3>
                    <p style="color:#94a3b8;">Start by adding a new facility to the system.</p>
                </div>
            @endforelse
        </div>
        <div id="facilitySearchEmpty" class="facility-search-empty">
            <div>No matching facilities found for your search.</div>
            <button type="button" id="facilitySearchReset" class="facility-search-reset">Reset search</button>
        </div>
    </div>
</div>

@if(!in_array(strtolower(Auth::user()->role ?? ''), ['energy_officer', 'staff'], true))
    <button type="button" id="fabAddFacility" class="btn-gradient" style="position:fixed; bottom:40px; right:40px; width:60px; height:60px; border-radius:30px; padding:0; display:flex; align-items:center; justify-content:center; font-size:1.5rem; z-index:99; display:none;">
        <i class="fas fa-plus"></i>
    </button>
@endif

@include('modules.facilities.partials.modals')

<script>
    window.addEventListener('scroll', function() {
        const fab = document.getElementById('fabAddFacility');
        if (fab) {
            if (window.scrollY > 200) fab.style.display = 'flex';
            else fab.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addFacilityModal');
        const openModal = () => { if(modal) modal.style.display = 'flex'; };

        document.getElementById('btnAddFacilityTop')?.addEventListener('click', openModal);
        document.getElementById('fabAddFacility')?.addEventListener('click', openModal);

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalRoot = btn.closest('.modal, .modal-overlay');
                if (modalRoot) {
                    modalRoot.style.display = 'none';
                }
            });
        });

        const searchInput = document.getElementById('facilityLiveSearch');
        const clearButton = document.getElementById('facilitySearchClear');
        const resetButton = document.getElementById('facilitySearchReset');
        const cards = Array.from(document.querySelectorAll('[data-facility-card]'));
        const emptyState = document.getElementById('facilitySearchEmpty');
        const visibleCountEl = document.getElementById('facilitySearchVisibleCount');
        const totalCountEl = document.getElementById('facilitySearchTotalCount');
        const searchableTexts = Array.from(document.querySelectorAll('[data-search-text]'));

        if (totalCountEl) {
            totalCountEl.textContent = String(cards.length);
        }

        searchableTexts.forEach((element) => {
            if (!element.hasAttribute('data-original-text')) {
                element.setAttribute('data-original-text', element.textContent || '');
            }
        });

        const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const escapeHtml = (value) => value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const highlightElement = (element, tokens) => {
            const originalText = element.getAttribute('data-original-text') || '';
            const validTokens = Array.from(new Set(tokens.filter((token) => token.length >= 2)));

            if (validTokens.length === 0) {
                element.innerHTML = escapeHtml(originalText);
                return;
            }

            const regex = new RegExp(validTokens.map(escapeRegExp).join('|'), 'ig');
            const matches = Array.from(originalText.matchAll(regex));

            if (!matches.length) {
                element.innerHTML = escapeHtml(originalText);
                return;
            }

            let html = '';
            let lastIndex = 0;

            matches.forEach((match) => {
                const matchText = match[0] || '';
                const start = match.index ?? 0;
                const end = start + matchText.length;
                html += escapeHtml(originalText.slice(lastIndex, start));
                html += '<mark class="facility-match">' + escapeHtml(matchText) + '</mark>';
                lastIndex = end;
            });

            html += escapeHtml(originalText.slice(lastIndex));
            element.innerHTML = html;
        };

        const clearSearch = () => {
            if (!searchInput) return;
            searchInput.value = '';
            applyFacilitySearch();
            searchInput.focus();
        };

        const applyFacilitySearch = () => {
            if (!cards.length) {
                if (emptyState) emptyState.style.display = 'none';
                if (visibleCountEl) visibleCountEl.textContent = '0';
                if (clearButton) clearButton.hidden = true;
                return;
            }

            const query = ((searchInput?.value) || '').toLowerCase().trim();
            const tokens = query === '' ? [] : query.split(/\s+/).filter(Boolean);
            let visibleCount = 0;

            if (clearButton) {
                clearButton.hidden = query === '';
            }

            cards.forEach((card) => {
                const haystack = ((card.getAttribute('data-search')) || '').toLowerCase();
                const isMatch = tokens.every((token) => haystack.includes(token));
                card.style.display = isMatch ? '' : 'none';
                const cardSearchTexts = Array.from(card.querySelectorAll('[data-search-text]'));
                cardSearchTexts.forEach((element) => highlightElement(element, isMatch ? tokens : []));
                if (isMatch) visibleCount += 1;
            });

            if (visibleCountEl) {
                visibleCountEl.textContent = String(visibleCount);
            }

            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        };

        clearButton?.addEventListener('click', clearSearch);
        resetButton?.addEventListener('click', clearSearch);
        searchInput?.addEventListener('input', applyFacilitySearch);
        applyFacilityS    });
</script>
@endsection
