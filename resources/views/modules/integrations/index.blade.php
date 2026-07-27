@extends('layouts.qc-admin')
@section('title', 'System Integrations')

@section('content')
<style>
.integrations-page {
    --int-primary: #3564d4;
    --int-text: #172033;
    --int-muted: #667085;
    --int-border: #e2e8f0;
    --int-surface: #ffffff;
    --int-soft: #f8fafc;
    width: 100%;
    color: var(--int-text);
}
.integrations-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 14px;
    padding: 20px 22px;
    border: 1px solid var(--int-border);
    border-radius: 14px;
    background: var(--int-surface);
    box-shadow: 0 5px 18px rgba(15, 23, 42, .045);
}
.header-main {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}
.header-icon {
    display: grid;
    place-items: center;
    flex: 0 0 48px;
    height: 48px;
    border-radius: 13px;
    background: linear-gradient(135deg, #315fc9, #6485df);
    color: #fff;
    box-shadow: 0 8px 18px rgba(53, 100, 212, .22);
    font-size: 1.1rem;
}
.header-eyebrow {
    margin: 0 0 3px;
    color: #5270b9;
    font-size: .69rem;
    font-weight: 850;
    letter-spacing: .09em;
    text-transform: uppercase;
}
.integrations-header h1 {
    margin: 0;
    color: var(--int-text);
    font-size: clamp(1.3rem, 2vw, 1.72rem);
    line-height: 1.2;
}
.integrations-header p {
    max-width: 730px;
    margin: 5px 0 0;
    color: var(--int-muted);
    font-size: .84rem;
    line-height: 1.45;
}
.header-summary {
    display: flex;
    flex: 0 0 auto;
    overflow: hidden;
    border: 1px solid var(--int-border);
    border-radius: 10px;
    background: var(--int-soft);
}
.summary-item {
    min-width: 92px;
    padding: 9px 13px;
    text-align: center;
}
.summary-item + .summary-item { border-left: 1px solid var(--int-border); }
.summary-item strong {
    display: block;
    color: var(--int-text);
    font-size: .82rem;
}
.summary-item span {
    display: block;
    margin-top: 1px;
    color: var(--int-muted);
    font-size: .64rem;
}
.security-note {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 14px;
    padding: 9px 13px;
    border: 1px solid #dce5f5;
    border-radius: 10px;
    background: #f6f8fd;
    color: #526077;
    font-size: .75rem;
    line-height: 1.4;
}
.security-note i { flex: 0 0 auto; color: var(--int-primary); }
.integration-directory {
    display: grid;
    gap: 13px;
}
.system-card {
    --accent: #3564d4;
    --accent-soft: #edf2ff;
    display: grid;
    grid-template-columns: minmax(230px, .78fr) minmax(330px, 1fr) minmax(420px, 1.35fr);
    overflow: hidden;
    border: 1px solid var(--int-border);
    border-left: 4px solid var(--accent);
    border-radius: 13px;
    background: var(--int-surface);
    box-shadow: 0 5px 18px rgba(15, 23, 42, .045);
}
.system-card.cprf {
    --accent: #15966d;
    --accent-soft: #eaf9f3;
}
.system-card.sso {
    --accent: #7a55c7;
    --accent-soft: #f3effd;
}
.system-overview,
.system-apis,
.system-process {
    min-width: 0;
    padding: 18px;
}
.system-apis,
.system-process { border-left: 1px solid var(--int-border); }
.system-topline {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 13px;
}
.system-icon {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: var(--accent-soft);
    color: var(--accent);
    font-size: .95rem;
}
.system-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 8px;
    border-radius: 999px;
    background: #ecfdf3;
    color: #067647;
    font-size: .65rem;
    font-weight: 850;
    white-space: nowrap;
}
.system-status::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #12b76a;
}
.system-status.off { background: #fff4ed; color: #b54708; }
.system-status.off::before { background: #f79009; }
.system-overview h2 {
    margin: 0 0 6px;
    color: var(--int-text);
    font-size: 1rem;
    line-height: 1.3;
}
.system-description {
    margin: 0;
    color: var(--int-muted);
    font-size: .77rem;
    line-height: 1.5;
}
.system-direction {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px dashed var(--int-border);
    color: #526077;
    font-size: .69rem;
    font-weight: 700;
}
.system-direction i { color: var(--accent); }
.section-label {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 0 0 11px;
    color: #475467;
    font-size: .68rem;
    font-weight: 850;
    letter-spacing: .07em;
    text-transform: uppercase;
}
.section-label i { color: var(--accent); }
.endpoint-list {
    display: grid;
    gap: 6px;
}
.endpoint {
    display: grid;
    grid-template-columns: 43px minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    min-height: 32px;
    padding: 6px 8px;
    border: 1px solid #e7ebf1;
    border-radius: 8px;
    background: var(--int-soft);
}
.method {
    padding: 4px 3px;
    border-radius: 5px;
    background: #e7efff;
    color: #2857bd;
    font-size: .58rem;
    font-weight: 900;
    text-align: center;
}
.method.post { background: #e5f7ee; color: #087443; }
.endpoint code {
    min-width: 0;
    overflow-wrap: anywhere;
    color: #344054;
    font-family: Consolas, Monaco, monospace;
    font-size: .68rem;
    line-height: 1.35;
}
.api-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 10px;
}
.api-tag {
    padding: 4px 7px;
    border-radius: 6px;
    background: var(--accent-soft);
    color: #475467;
    font-size: .61rem;
    font-weight: 750;
}
.process-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 13px;
    margin: 0;
    padding: 0;
    list-style: none;
    counter-reset: integration-step;
}
.process-list li {
    position: relative;
    min-width: 0;
    padding-left: 29px;
    color: #667085;
    font-size: .7rem;
    line-height: 1.45;
    counter-increment: integration-step;
}
.process-list li::before {
    content: counter(integration-step);
    position: absolute;
    left: 0;
    top: 0;
    display: grid;
    place-items: center;
    width: 21px;
    height: 21px;
    border-radius: 6px;
    background: var(--accent-soft);
    color: var(--accent);
    font-size: .61rem;
    font-weight: 900;
}
.process-list strong {
    display: block;
    margin-bottom: 1px;
    color: #344054;
    font-size: .71rem;
}
body.dark-mode .integrations-page {
    --int-text: #f4f4f5;
    --int-muted: #a1a1aa;
    --int-border: #3f3f46;
    --int-surface: #242429;
    --int-soft: #2d2d33;
}
body.dark-mode .header-summary { background: #2d2d33; }
body.dark-mode .security-note { background: #252b37; border-color: #3b4659; color: #c4ccda; }
body.dark-mode .section-label,
body.dark-mode .system-direction { color: #c2c2ca; }
body.dark-mode .endpoint { border-color: #3f3f46; }
body.dark-mode .endpoint code,
body.dark-mode .process-list strong { color: #e4e4e7; }
body.dark-mode .process-list li { color: #a1a1aa; }
@media (max-width: 1250px) {
    .system-card {
        grid-template-columns: 220px minmax(300px, .95fr) minmax(360px, 1.2fr);
    }
}
@media (max-width: 1050px) {
    .system-card { grid-template-columns: 230px 1fr; }
    .system-process {
        grid-column: 1 / -1;
        border-top: 1px solid var(--int-border);
        border-left: 0;
    }
}
@media (max-width: 760px) {
    .integrations-header { align-items: flex-start; padding: 17px; }
    .header-summary { display: none; }
    .system-card { grid-template-columns: 1fr; }
    .system-apis,
    .system-process {
        grid-column: auto;
        border-top: 1px solid var(--int-border);
        border-left: 0;
    }
}
@media (max-width: 520px) {
    .header-icon { flex-basis: 42px; height: 42px; }
    .system-overview,
    .system-apis,
    .system-process { padding: 15px; }
    .process-list { grid-template-columns: 1fr; }
}
</style>

<div class="integrations-page">
    <header class="integrations-header">
        <div class="header-main">
            <span class="header-icon"><i class="fa-solid fa-plug-circle-bolt"></i></span>
            <div>
                <div class="header-eyebrow">Admin / Integration Directory</div>
                <h1>System Integrations</h1>
                <p>APIs, security methods, and data flows connected to the LGU Energy Efficiency System.</p>
            </div>
        </div>
        <div class="header-summary" aria-label="Integration summary">
            <div class="summary-item"><strong>3</strong><span>Systems</span></div>
            <div class="summary-item"><strong>REST</strong><span>JSON APIs</span></div>
            <div class="summary-item"><strong>Secure</strong><span>Token / HMAC</span></div>
        </div>
    </header>

    <div class="security-note">
        <i class="fa-solid fa-shield-halved"></i>
        <span>Configuration status only—not a live uptime check. Tokens and API secrets are never displayed.</span>
    </div>

    <main class="integration-directory">
        <article class="system-card cimm">
            <section class="system-overview">
                <div class="system-topline">
                    <span class="system-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <span class="system-status{{ $statuses['cimm'] ? '' : ' off' }}">{{ $statuses['cimm'] ? 'Configured' : 'Not configured' }}</span>
                </div>
                <h2>CIMM Maintenance Sync</h2>
                <p class="system-description">Two-way synchronization of active maintenance schedules, status changes, and completed work.</p>
                <div class="system-direction"><i class="fa-solid fa-arrow-right-arrow-left"></i> Energy Maintenance ⇄ CIMM Schedule</div>
            </section>

            <section class="system-apis">
                <h3 class="section-label"><i class="fa-solid fa-code"></i> API endpoints</h3>
                <div class="endpoint-list">
                    <div class="endpoint"><span class="method">GET</span><code>/api/v1/cimm-maintenance-sync/maintenance</code></div>
                    <div class="endpoint"><span class="method">GET</span><code>/api/v1/cimm-maintenance-sync/maintenance-history</code></div>
                    <div class="endpoint"><span class="method post">POST</span><code>/api/v1/cimm-maintenance-sync/maintenance/{id}/sync</code></div>
                </div>
                <div class="api-tags">
                    <span class="api-tag">REST + JSON</span>
                    <span class="api-tag">Bearer Token</span>
                    <span class="api-tag">60 req/min</span>
                    <span class="api-tag">Two-way</span>
                </div>
            </section>

            <section class="system-process">
                <h3 class="section-label"><i class="fa-solid fa-route"></i> Integration process</h3>
                <ol class="process-list">
                    <li><strong>Energy publishes records</strong>CIMM pulls active maintenance and completed history.</li>
                    <li><strong>CIMM maps each record</strong>The Energy maintenance ID links to its local schedule.</li>
                    <li><strong>CIMM sends updates</strong>Pending, Ongoing, or Completed is posted to the sync API.</li>
                    <li><strong>Energy applies effects</strong>Completion archives work, resolves incidents, and notifies users.</li>
                </ol>
            </section>
        </article>

        <article class="system-card cprf">
            <section class="system-overview">
                @php
                    $cprfReady = $statuses['cprf'] && $statuses['cprf_feed'];
                    $cprfLabel = $cprfReady
                        ? 'Configured'
                        : (($statuses['cprf'] || $statuses['cprf_feed']) ? 'Partial setup' : 'Not configured');
                @endphp
                <div class="system-topline">
                    <span class="system-icon"><i class="fa-solid fa-building-circle-check"></i></span>
                    <span class="system-status{{ $cprfReady ? '' : ' off' }}">{{ $cprfLabel }}</span>
                </div>
                <h2>CPRF Facilities Reservation</h2>
                <p class="system-description">Public facility identities, energy profiles, meter readings, and approved recommendations.</p>
                <div class="system-direction"><i class="fa-solid fa-arrow-right-arrow-left"></i> CPRF Facilities ⇄ Energy Analytics</div>
            </section>

            <section class="system-apis">
                <h3 class="section-label"><i class="fa-solid fa-code"></i> API endpoints</h3>
                <div class="endpoint-list">
                    <div class="endpoint"><span class="method">GET</span><code>CPRF /public/api/energy-facilities-feed.php</code></div>
                    <div class="endpoint"><span class="method">GET</span><code>/api/v1/cprf/facilities</code></div>
                    <div class="endpoint"><span class="method">GET</span><code>/api/v1/cprf/facility-profiles</code></div>
                    <div class="endpoint"><span class="method">GET</span><code>/api/v1/cprf/recommendations</code></div>
                    <div class="endpoint"><span class="method post">POST</span><code>/api/v1/cprf/facility-readings</code></div>
                </div>
                <div class="api-tags">
                    <span class="api-tag">REST + JSON</span>
                    <span class="api-tag">Bearer Token</span>
                    <span class="api-tag">Hourly pull</span>
                    <span class="api-tag">Two-way</span>
                </div>
            </section>

            <section class="system-process">
                <h3 class="section-label"><i class="fa-solid fa-route"></i> Integration process</h3>
                <ol class="process-list">
                    <li><strong>Facilities are mirrored</strong>Energy pulls CPRF’s public feed hourly using its external ID.</li>
                    <li><strong>Profiles are shared</strong>CPRF reads mapped Energy profiles and approved recommendations.</li>
                    <li><strong>Readings are submitted</strong>CPRF posts facility-level meter values and encoder identity.</li>
                    <li><strong>Energy analyzes usage</strong>Consumption, baseline, cost, deviation, and alerts are calculated.</li>
                </ol>
            </section>
        </article>

        <article class="system-card sso">
            <section class="system-overview">
                <div class="system-topline">
                    <span class="system-icon"><i class="fa-solid fa-key"></i></span>
                    <span class="system-status{{ $statuses['sso'] ? '' : ' off' }}">{{ $statuses['sso'] ? 'Configured' : 'Not configured' }}</span>
                </div>
                <h2>Main LGU Single Sign-On</h2>
                <p class="system-description">Secure one-time login from the InfraGov services hub into the Energy system.</p>
                <div class="system-direction"><i class="fa-solid fa-arrow-right-to-bracket"></i> Main LGU Hub → Energy Session</div>
            </section>

            <section class="system-apis">
                <h3 class="section-label"><i class="fa-solid fa-code"></i> API endpoints</h3>
                <div class="endpoint-list">
                    <div class="endpoint"><span class="method">GET</span><code>/sso/consume?sso_token={signed_token}</code></div>
                    <div class="endpoint"><span class="method">GET</span><code>/api/stats</code></div>
                </div>
                <div class="api-tags">
                    <span class="api-tag">HMAC-SHA256</span>
                    <span class="api-tag">Short-lived</span>
                    <span class="api-tag">One-time nonce</span>
                    <span class="api-tag">Inbound SSO</span>
                </div>
            </section>

            <section class="system-process">
                <h3 class="section-label"><i class="fa-solid fa-route"></i> Integration process</h3>
                <ol class="process-list">
                    <li><strong>Main LGU signs a token</strong>It contains the user, target, expiry time, and unique nonce.</li>
                    <li><strong>Energy verifies it</strong>The HMAC signature, target system, and expiry are validated.</li>
                    <li><strong>Replay is prevented</strong>The one-time nonce is stored and reused tokens are rejected.</li>
                    <li><strong>A session is created</strong>The user is matched or provisioned, logged in, and redirected.</li>
                </ol>
            </section>
        </article>
    </main>
</div>
@endsection
