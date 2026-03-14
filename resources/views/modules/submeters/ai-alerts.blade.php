@extends('layouts.qc-admin')
@section('title', 'AI Alerts')

<style>
    .ai-alerts-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }
    .ai-alerts-table th,
    .ai-alerts-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        text-align: center !important;
    }
    .ai-alerts-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #1e293b;
        font-weight: 800;
    }
    .ai-alerts-text-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.35;
        word-break: break-word;
    }
    .ai-alerts-reco-meta {
        margin-top: 6px;
        font-size: .74rem;
        color: #64748b;
        font-weight: 700;
    }
    .ai-alerts-reco-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .92rem;
    }
    .ai-alerts-reco-icon-btn {
        border: none;
        cursor: pointer;
    }
    .ai-alerts-reco-icon-btn:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .ai-alerts-reco-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10090;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .ai-alerts-reco-modal.open { display: flex; }
    .ai-alerts-reco-card {
        width: min(640px, 100%);
        background: #fff;
        border: 1px solid #dbe3f1;
        border-radius: 16px;
        box-shadow: 0 20px 44px rgba(15, 23, 42, .22);
        padding: 16px;
        position: relative;
    }
    .ai-alerts-reco-close {
        position: absolute;
        top: 8px;
        right: 10px;
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 1.3rem;
        cursor: pointer;
    }
    .ai-alerts-reco-title {
        margin: 0;
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 900;
    }
    .ai-alerts-reco-source {
        margin-top: 6px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: .78rem;
        font-weight: 800;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
    }
    .ai-alerts-reco-body {
        margin-top: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 13px;
        color: #334155;
        line-height: 1.45;
        font-size: .96rem;
        background: #f8fafc;
    }
    .ai-alerts-reco-foot {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }
    .ai-alerts-open-btn {
        text-decoration: none;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

@section('content')
<div class="em-page">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:12px;font-weight:700;">{{ session('error') }}</div>
    @endif

    @php
        $energyTab = 'ai';
    @endphp
    @include('layouts.partials.energy_monitoring_switcher')

    <div class="em-header">
        <div>
            <h2>AI Alerts and Recommendations</h2>
            <div class="em-header-subtitle">Combined timeline for Main Meter and Submeter alerts.</div>
        </div>
        <div class="em-header-actions">
            <a href="{{ route('modules.main-meter.monitoring') }}" class="em-action-btn soft">Main Meter</a>
            <a href="{{ route('modules.submeters.monitoring') }}" class="em-action-btn soft">Submeter</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
        <div style="background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:12px;">
            <div style="font-size:.76rem;color:#1e40af;font-weight:800;letter-spacing:.03em;">TOTAL ALERTS</div>
            <div style="margin-top:4px;font-size:1.35rem;font-weight:900;color:#0f172a;">{{ number_format((int) ($summary['total'] ?? 0)) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:12px;">
            <div style="font-size:.76rem;color:#991b1b;font-weight:800;letter-spacing:.03em;">CRITICAL</div>
            <div style="margin-top:4px;font-size:1.35rem;font-weight:900;color:#991b1b;">{{ number_format((int) ($summary['critical'] ?? 0)) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #fde68a;border-radius:12px;padding:12px;">
            <div style="font-size:.76rem;color:#92400e;font-weight:800;letter-spacing:.03em;">WARNING</div>
            <div style="margin-top:4px;font-size:1.35rem;font-weight:900;color:#92400e;">{{ number_format((int) ($summary['warning'] ?? 0)) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #bfdbfe;border-radius:12px;padding:12px;">
            <div style="font-size:.76rem;color:#1d4ed8;font-weight:800;letter-spacing:.03em;">MAIN METER</div>
            <div style="margin-top:4px;font-size:1.35rem;font-weight:900;color:#1d4ed8;">{{ number_format((int) ($summary['main'] ?? 0)) }}</div>
        </div>
        <div style="background:#fff;border:1px solid #a5f3fc;border-radius:12px;padding:12px;">
            <div style="font-size:.76rem;color:#0f766e;font-weight:800;letter-spacing:.03em;">SUBMETER</div>
            <div style="margin-top:4px;font-size:1.35rem;font-weight:900;color:#0f766e;">{{ number_format((int) ($summary['sub'] ?? 0)) }}</div>
        </div>
    </div>

    <div class="em-panel">
        <form method="GET" action="{{ route('modules.ai-alerts.index') }}" class="em-filter">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:220px;flex:1;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Facility</label>
                <select name="facility_id" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) $selectedFacility === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Source</label>
                <select name="source" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="all" @selected($selectedSource === 'all')>All Sources</option>
                    <option value="main" @selected($selectedSource === 'main')>Main Meter</option>
                    <option value="sub" @selected($selectedSource === 'sub')>Submeter</option>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                <label style="font-size:.8rem;font-weight:700;color:#475569;">Alert Level</label>
                <select name="alert_level" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="" @selected($selectedLevel === '')>All</option>
                    <option value="warning" @selected($selectedLevel === 'warning')>Warning</option>
                    <option value="critical" @selected($selectedLevel === 'critical')>Critical</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#1d4ed8;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Filter</button>
                <a href="{{ route('modules.ai-alerts.index') }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        <div class="em-table-wrap">
            <table class="ai-alerts-table">
                <colgroup>
                    <col style="width:82px;">
                    <col style="width:190px;">
                    <col style="width:150px;">
                    <col style="width:98px;">
                    <col style="width:106px;">
                    <col style="width:106px;">
                    <col style="width:95px;">
                    <col style="width:96px;">
                    <col style="width:220px;">
                    <col style="width:250px;">
                    <col style="width:84px;">
                </colgroup>
                <thead>
                    <tr>
                        <th style="text-align:left;">Source</th>
                        <th style="text-align:left;">Facility</th>
                        <th style="text-align:left;">Meter</th>
                        <th style="text-align:center;">Period</th>
                        <th style="text-align:right;">Current kWh</th>
                        <th style="text-align:right;">Baseline kWh</th>
                        <th style="text-align:center;">Increase</th>
                        <th style="text-align:center;">Level</th>
                        <th style="text-align:left;">Reason</th>
                        <th style="text-align:left;">Recommendation</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        @php
                            $level = strtolower((string) ($alert['alert_level'] ?? 'warning'));
                            $isCritical = $level === 'critical';
                            $rowBg = $isCritical ? '#fef2f2' : '#fffbeb';
                            $source = (string) ($alert['source'] ?? 'sub');
                        @endphp
                        <tr style="background:{{ $rowBg }};">
                            <td>
                                @if($source === 'main')
                                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">MAIN</span>
                                @else
                                    <span style="background:#ecfeff;color:#0f766e;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">SUB</span>
                                @endif
                            </td>
                            <td style="color:#334155;font-weight:700;">
                                {{ $alert['facility_name'] ?? '-' }}
                            </td>
                            <td style="color:#334155;">
                                {{ $alert['meter_name'] ?? '-' }}
                            </td>
                            <td style="text-align:center;color:#334155;">
                                {{ $alert['period_label'] ?? '-' }}
                            </td>
                            <td style="text-align:right;font-weight:700;color:#0f172a;">
                                {{ is_numeric($alert['current_kwh'] ?? null) ? number_format((float) $alert['current_kwh'], 2) : '-' }}
                            </td>
                            <td style="text-align:right;color:#1d4ed8;font-weight:700;">
                                {{ is_numeric($alert['baseline_kwh'] ?? null) ? number_format((float) $alert['baseline_kwh'], 2) : '-' }}
                            </td>
                            <td style="text-align:center;font-weight:800;color:#be123c;">
                                {{ is_numeric($alert['increase_percent'] ?? null) ? number_format((float) $alert['increase_percent'], 2) . '%' : '-' }}
                            </td>
                            <td style="text-align:center;">
                                @if($isCritical)
                                    <span style="background:#fee2e2;color:#991b1b;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">CRITICAL</span>
                                @else
                                    <span style="background:#fef3c7;color:#92400e;border-radius:999px;padding:5px 10px;font-size:.78rem;font-weight:800;">WARNING</span>
                                @endif
                            </td>
                            <td style="color:#334155;">
                                <div class="ai-alerts-text-clamp"
                                     title="{{ trim((string) ($alert['reason'] ?? '')) !== '' ? $alert['reason'] : '-' }}">
                                    {{ trim((string) ($alert['reason'] ?? '')) !== '' ? $alert['reason'] : '-' }}
                                </div>
                            </td>
                            <td style="color:#334155;">
                                @php
                                    $recommendationText = trim((string) ($alert['recommendation'] ?? '')) !== '' ? (string) $alert['recommendation'] : 'No recommendation.';
                                    $recommendationSource = strtolower((string) ($alert['recommendation_source'] ?? 'rules')) === 'ai'
                                        ? 'AI recommendation'
                                        : 'Rule-based recommendation';
                                @endphp
                                <div style="text-align:center;">
                                    <button
                                        type="button"
                                        class="ai-alerts-reco-icon ai-alerts-reco-icon-btn"
                                        data-open-recommendation
                                        data-recommendation="{{ $recommendationText }}"
                                        data-recommendation-source="{{ $recommendationSource }}"
                                        title="View recommendation"
                                        aria-label="View recommendation"
                                    >
                                        <i class="fa-solid fa-lightbulb"></i>
                                    </button>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ $alert['detail_url'] ?? '#' }}"
                                   class="ai-alerts-open-btn">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding:20px;text-align:center;color:#64748b;">No AI alerts found for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="em-panel-footer">
            {{ $alerts->links() }}
        </div>
    </div>

    <div id="aiRecommendationModal" class="ai-alerts-reco-modal" aria-hidden="true">
        <div class="ai-alerts-reco-card" role="dialog" aria-modal="true" aria-labelledby="aiRecoTitle">
            <button type="button" class="ai-alerts-reco-close" onclick="closeAiRecommendationModal()">&times;</button>
            <h3 id="aiRecoTitle" class="ai-alerts-reco-title">Recommendation</h3>
            <div id="aiRecoSource" class="ai-alerts-reco-source">Rule-based recommendation</div>
            <div id="aiRecoBody" class="ai-alerts-reco-body">No recommendation.</div>
            <div class="ai-alerts-reco-foot">
                <button type="button" class="ai-alerts-open-btn" onclick="closeAiRecommendationModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openAiRecommendationModal(sourceLabel, recommendationText) {
    const modal = document.getElementById('aiRecommendationModal');
    const source = document.getElementById('aiRecoSource');
    const body = document.getElementById('aiRecoBody');
    if (!modal || !source || !body) return;
    source.textContent = sourceLabel || 'Rule-based recommendation';
    body.textContent = recommendationText || 'No recommendation.';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
}

function closeAiRecommendationModal() {
    const modal = document.getElementById('aiRecommendationModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-open-recommendation]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openAiRecommendationModal(
                btn.getAttribute('data-recommendation-source') || 'Rule-based recommendation',
                btn.getAttribute('data-recommendation') || 'No recommendation.'
            );
        });
    });
});

window.addEventListener('click', function (event) {
    const modal = document.getElementById('aiRecommendationModal');
    if (modal && event.target === modal) {
        closeAiRecommendationModal();
    }
});

window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAiRecommendationModal();
    }
});
</script>
@endsection
