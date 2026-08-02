@extends('layouts.qc-admin')

@section('title', 'About '.$systemName)

@section('content')
<div class="about-page">
    <section class="about-hero" aria-labelledby="about-title">
        <div class="about-hero__content">
            <div class="about-eyebrow">
                <span></span>
                About the platform
            </div>
            <h1 id="about-title">Built to turn energy data into <em>public-sector action.</em></h1>
            <p>
                {{ $systemName }} brings facility information, consumption records, monitoring workflows, and decision-ready reports together in one secure workspace.
            </p>
            <div class="about-hero__tags" aria-label="Platform focus areas">
                <span><i class="fa-solid fa-bolt"></i> Energy intelligence</span>
                <span><i class="fa-solid fa-building"></i> Facility oversight</span>
                <span><i class="fa-solid fa-shield-halved"></i> Accountable operations</span>
            </div>
        </div>

        <div class="about-hero__identity" aria-label="System identity">
            <div class="identity-orbit" aria-hidden="true"></div>
            <div class="identity-logo">
                <img src="{{ $systemLogoUrl }}" alt="{{ $systemName }} logo">
            </div>
            <span class="identity-label">Local government platform</span>
            <strong>{{ $systemName }}</strong>
            <small>{{ $systemOrganization }}</small>
        </div>
    </section>

    <section class="purpose-section" aria-labelledby="purpose-title">
        <div class="section-heading">
            <div>
                <span class="section-kicker">Purpose and direction</span>
                <h2 id="purpose-title">Why the platform exists</h2>
            </div>
            <p>Technology should make energy information easier to understand, manage, and use in service of communities.</p>
        </div>

        <div class="purpose-grid">
            <article class="purpose-card mission-card">
                <div class="purpose-card__top">
                    <span class="purpose-icon"><i class="fa-solid fa-bullseye"></i></span>
                    <span class="purpose-index">01</span>
                </div>
                <span class="purpose-label">Our mission</span>
                <h3>Make energy operations visible and manageable.</h3>
                <p>Provide local government teams with a structured, data-informed platform for monitoring, managing, and improving energy consumption across public facilities.</p>
            </article>

            <article class="purpose-card vision-card">
                <div class="purpose-card__top">
                    <span class="purpose-icon"><i class="fa-solid fa-eye"></i></span>
                    <span class="purpose-index">02</span>
                </div>
                <span class="purpose-label">Our vision</span>
                <h3>More efficient and responsible public facilities.</h3>
                <p>Enable local government units to use accurate information and practical insights to support efficiency, sustainability, and responsible resource use.</p>
            </article>

            <article class="purpose-card values-card">
                <div class="purpose-card__top">
                    <span class="purpose-icon"><i class="fa-solid fa-hand-holding-heart"></i></span>
                    <span class="purpose-index">03</span>
                </div>
                <span class="purpose-label">Our values</span>
                <h3>Principles behind every workflow.</h3>
                <ul class="values-list">
                    <li><i class="fa-solid fa-check"></i><span>Transparency and accountability</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Continuous improvement</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Environmental responsibility</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Collaboration and public service</span></li>
                </ul>
            </article>
        </div>
    </section>

    <section class="features-section" aria-labelledby="features-title">
        <div class="section-heading features-heading">
            <div>
                <span class="section-kicker">Platform capabilities</span>
                <h2 id="features-title">Designed for day-to-day energy operations</h2>
            </div>
            <span class="feature-count"><strong>06</strong> connected capabilities</span>
        </div>

        <div class="features-grid">
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-bolt"></i></span>
                <div>
                    <span class="feature-number">01</span>
                    <h3>Energy monitoring</h3>
                    <p>Review energy consumption records and understand facility performance through a centralized monitoring workspace.</p>
                </div>
            </article>
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-building"></i></span>
                <div>
                    <span class="feature-number">02</span>
                    <h3>Facility management</h3>
                    <p>Keep facility profiles, meters, and related operational information organized and accessible.</p>
                </div>
            </article>
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-wrench"></i></span>
                <div>
                    <span class="feature-number">03</span>
                    <h3>Maintenance tracking</h3>
                    <p>Record and follow maintenance activities associated with energy equipment and facilities.</p>
                </div>
            </article>
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-chart-column"></i></span>
                <div>
                    <span class="feature-number">04</span>
                    <h3>Reports and analytics</h3>
                    <p>Transform recorded data into structured summaries that support operational review and planning.</p>
                </div>
            </article>
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <span class="feature-number">05</span>
                    <h3>Incident management</h3>
                    <p>Document energy-related incidents and maintain the context needed for follow-up and prevention.</p>
                </div>
            </article>
            <article class="feature-item">
                <span class="feature-icon"><i class="fa-solid fa-leaf"></i></span>
                <div>
                    <span class="feature-number">06</span>
                    <h3>Energy conservation</h3>
                    <p>Support efficiency efforts with practical recommendations informed by available energy data.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="foundations-section" aria-labelledby="foundations-title">
        <div class="foundations-copy">
            <span class="section-kicker">Platform foundations</span>
            <h2 id="foundations-title">Structured for trustworthy operations.</h2>
            <p>The system is organized around four foundations that help teams work consistently with operational energy information.</p>
        </div>
        <div class="foundations-grid">
            <div class="foundation-item">
                <span><i class="fa-solid fa-layer-group"></i></span>
                <div><strong>Centralized</strong><small>One operational workspace</small></div>
            </div>
            <div class="foundation-item">
                <span><i class="fa-solid fa-user-shield"></i></span>
                <div><strong>Role-based</strong><small>Appropriate user access</small></div>
            </div>
            <div class="foundation-item">
                <span><i class="fa-solid fa-chart-line"></i></span>
                <div><strong>Data-informed</strong><small>Clearer review and planning</small></div>
            </div>
            <div class="foundation-item">
                <span><i class="fa-solid fa-arrows-rotate"></i></span>
                <div><strong>Continuous</strong><small>Built for ongoing improvement</small></div>
            </div>
        </div>
    </section>

    <div class="about-actions">
        <a href="{{ url()->previous() }}" class="about-button about-button--secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>
        @auth
            <a href="{{ route('dashboard') }}" class="about-button about-button--primary">
                Open dashboard
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        @endauth
    </div>
</div>

<style>
.about-page {
    --about-primary: #2563eb;
    --about-indigo: #4f46e5;
    --about-sky: #0ea5e9;
    --about-ink: #0f172a;
    --about-copy: #475569;
    --about-muted: #64748b;
    --about-line: #dce5f0;
    max-width: 1240px;
    margin: 0 auto;
    padding: 12px 0 30px;
}

.about-hero {
    position: relative;
    min-height: 330px;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr);
    align-items: center;
    gap: 52px;
    overflow: hidden;
    padding: 48px 52px;
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 24px;
    background:
        radial-gradient(circle at 90% 12%, rgba(125,211,252,.24), transparent 32%),
        radial-gradient(circle at 12% 88%, rgba(99,102,241,.2), transparent 32%),
        linear-gradient(125deg, #172554, #1e40af 52%, #2563eb);
    box-shadow: 0 24px 55px rgba(30,64,175,.22);
}

.about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 45px 45px;
    mask-image: linear-gradient(90deg, transparent, #000);
}

.about-hero__content,
.about-hero__identity {
    position: relative;
    z-index: 1;
}

.about-eyebrow {
    width: max-content;
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 17px;
    color: #bfdbfe;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.about-eyebrow span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #7dd3fc;
    box-shadow: 0 0 0 5px rgba(125,211,252,.12);
}

.about-hero h1 {
    max-width: 720px;
    margin: 0;
    color: #fff;
    font-size: clamp(2rem, 4vw, 3.35rem);
    line-height: 1.08;
    letter-spacing: -.05em;
}

.about-hero h1 em {
    color: #7dd3fc;
    font-style: normal;
}

.about-hero__content > p {
    max-width: 720px;
    margin: 19px 0 0;
    color: rgba(226,232,240,.8);
    font-size: .87rem;
    line-height: 1.75;
}

.about-hero__tags {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 25px;
}

.about-hero__tags span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 10px;
    color: rgba(255,255,255,.8);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 999px;
    background: rgba(255,255,255,.07);
    font-size: .61rem;
    font-weight: 700;
}

.about-hero__tags i {
    color: #7dd3fc;
}

.about-hero__identity {
    min-height: 225px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    overflow: hidden;
    padding: 26px;
    border: 1px solid rgba(255,255,255,.17);
    border-radius: 20px;
    background: rgba(255,255,255,.09);
    backdrop-filter: blur(12px);
    text-align: center;
}

.identity-orbit {
    position: absolute;
    width: 210px;
    height: 210px;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 50%;
    box-shadow: 0 0 0 30px rgba(255,255,255,.025), 0 0 0 62px rgba(255,255,255,.018);
}

.identity-logo {
    position: relative;
    z-index: 1;
    width: 68px;
    height: 68px;
    display: grid;
    place-items: center;
    margin-bottom: 14px;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,.32);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 14px 30px rgba(15,23,42,.22);
}

.identity-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.identity-label {
    position: relative;
    z-index: 1;
    margin-bottom: 5px;
    color: #bfdbfe;
    font-size: .56rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.about-hero__identity strong,
.about-hero__identity small {
    position: relative;
    z-index: 1;
    display: block;
}

.about-hero__identity strong {
    max-width: 250px;
    color: #fff;
    font-size: .92rem;
}

.about-hero__identity small {
    margin-top: 5px;
    color: rgba(226,232,240,.64);
    font-size: .61rem;
}

.purpose-section,
.features-section {
    padding-top: 76px;
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 40px;
    margin-bottom: 30px;
}

.section-kicker {
    display: block;
    margin-bottom: 9px;
    color: var(--about-primary);
    font-size: .66rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.section-heading h2 {
    margin: 0;
    color: var(--about-ink);
    font-size: clamp(1.55rem, 3vw, 2.2rem);
    line-height: 1.2;
    letter-spacing: -.04em;
}

.section-heading > p {
    max-width: 465px;
    margin: 0;
    padding-left: 20px;
    color: var(--about-muted);
    border-left: 2px solid #bfdbfe;
    font-size: .75rem;
    line-height: 1.65;
}

.purpose-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 17px;
}

.purpose-card {
    position: relative;
    overflow: hidden;
    min-height: 335px;
    padding: 27px;
    border: 1px solid var(--about-line);
    border-radius: 19px;
    background: #fff;
    box-shadow: 0 12px 32px rgba(15,23,42,.055);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}

.purpose-card::after {
    content: '';
    position: absolute;
    right: -75px;
    bottom: -85px;
    width: 190px;
    height: 190px;
    border-radius: 50%;
    background: #eff6ff;
}

.purpose-card:hover {
    transform: translateY(-5px);
    border-color: #bfdbfe;
    box-shadow: 0 20px 42px rgba(37,99,235,.1);
}

.vision-card::after {
    background: #ecfdf5;
}

.values-card::after {
    background: #fffbeb;
}

.purpose-card__top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}

.purpose-icon {
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 14px;
    background: #eff6ff;
    font-size: 1rem;
}

.vision-card .purpose-icon {
    color: #059669;
    border-color: #a7f3d0;
    background: #ecfdf5;
}

.values-card .purpose-icon {
    color: #d97706;
    border-color: #fde68a;
    background: #fffbeb;
}

.purpose-index {
    color: #cbd5e1;
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .1em;
}

.purpose-label {
    position: relative;
    z-index: 1;
    display: block;
    margin-bottom: 7px;
    color: var(--about-primary);
    font-size: .61rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.vision-card .purpose-label {
    color: #059669;
}

.values-card .purpose-label {
    color: #d97706;
}

.purpose-card h3 {
    position: relative;
    z-index: 1;
    margin: 0 0 13px;
    color: var(--about-ink);
    font-size: 1.08rem;
    line-height: 1.4;
    letter-spacing: -.025em;
}

.purpose-card p {
    position: relative;
    z-index: 1;
    margin: 0;
    color: var(--about-copy);
    font-size: .75rem;
    line-height: 1.72;
}

.values-list {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 10px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.values-list li {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    color: var(--about-copy);
    font-size: .7rem;
    line-height: 1.45;
}

.values-list i {
    width: 19px;
    height: 19px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    margin-top: 1px;
    color: #2563eb;
    border-radius: 50%;
    background: #dbeafe;
    font-size: .54rem;
}

.features-section {
    margin-top: 76px;
    padding: 34px;
    border: 1px solid var(--about-line);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 16px 42px rgba(15,23,42,.06);
}

.features-section .section-heading {
    margin-bottom: 25px;
}

.feature-count {
    min-width: 155px;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 12px;
    color: var(--about-muted);
    border: 1px solid var(--about-line);
    border-radius: 11px;
    background: #f8fafc;
    font-size: .62rem;
    font-weight: 700;
}

.feature-count strong {
    color: var(--about-primary);
    font-size: 1rem;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.feature-item {
    position: relative;
    min-height: 178px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px;
    border: 1px solid #e5edf7;
    border-radius: 15px;
    background: linear-gradient(145deg, #f8fbff, #fff);
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.feature-item:hover {
    transform: translateY(-4px);
    border-color: #bfdbfe;
    box-shadow: 0 13px 28px rgba(37,99,235,.09);
}

.feature-icon {
    width: 41px;
    height: 41px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    color: var(--about-primary);
    border: 1px solid #dbeafe;
    border-radius: 12px;
    background: #eff6ff;
    font-size: .85rem;
}

.feature-item:nth-child(2) .feature-icon,
.feature-item:nth-child(5) .feature-icon {
    color: #4f46e5;
    border-color: #ddd6fe;
    background: #f5f3ff;
}

.feature-item:nth-child(3) .feature-icon,
.feature-item:nth-child(6) .feature-icon {
    color: #059669;
    border-color: #a7f3d0;
    background: #ecfdf5;
}

.feature-number {
    display: block;
    margin-bottom: 8px;
    color: #b2bfd0;
    font-size: .55rem;
    font-weight: 800;
    letter-spacing: .1em;
}

.feature-item h3 {
    margin: 0 0 7px;
    color: var(--about-ink);
    font-size: .8rem;
    line-height: 1.4;
}

.feature-item p {
    margin: 0;
    color: var(--about-muted);
    font-size: .67rem;
    line-height: 1.62;
}

.foundations-section {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(260px, .72fr) minmax(500px, 1.28fr);
    align-items: center;
    gap: 54px;
    margin-top: 28px;
    padding: 34px;
    color: #fff;
    border-radius: 22px;
    background:
        radial-gradient(circle at 90% 10%, rgba(125,211,252,.2), transparent 28%),
        linear-gradient(120deg, #172554, #1e3a8a 52%, #1d4ed8);
    box-shadow: 0 18px 40px rgba(30,64,175,.17);
}

.foundations-copy {
    position: relative;
    z-index: 1;
}

.foundations-copy .section-kicker {
    color: #7dd3fc;
}

.foundations-copy h2 {
    margin: 0;
    color: #fff;
    font-size: 1.55rem;
    line-height: 1.28;
    letter-spacing: -.035em;
}

.foundations-copy p {
    margin: 12px 0 0;
    color: rgba(203,213,225,.72);
    font-size: .7rem;
    line-height: 1.65;
}

.foundations-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 9px;
}

.foundation-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px;
    border: 1px solid rgba(255,255,255,.13);
    border-radius: 13px;
    background: rgba(255,255,255,.075);
}

.foundation-item > span {
    width: 37px;
    height: 37px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    color: #7dd3fc;
    border-radius: 10px;
    background: rgba(125,211,252,.12);
    font-size: .78rem;
}

.foundation-item strong,
.foundation-item small {
    display: block;
}

.foundation-item strong {
    color: #fff;
    font-size: .7rem;
}

.foundation-item small {
    margin-top: 3px;
    color: rgba(203,213,225,.62);
    font-size: .56rem;
}

.about-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 24px;
}

.about-button {
    min-height: 43px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 15px;
    border: 1px solid transparent;
    border-radius: 11px;
    text-decoration: none;
    font-size: .7rem;
    font-weight: 800;
    transition: transform .17s ease, box-shadow .17s ease, background .17s ease;
}

.about-button:hover {
    transform: translateY(-2px);
}

.about-button--secondary {
    color: #475569;
    border-color: var(--about-line);
    background: #fff;
}

.about-button--primary {
    color: #fff;
    background: linear-gradient(110deg, var(--about-primary), var(--about-indigo), var(--about-sky));
    box-shadow: 0 10px 20px rgba(37,99,235,.2);
}

/* Readable information-page type scale */
.about-page .about-eyebrow { font-size: .78rem; }
.about-page .about-hero__content > p { font-size: .98rem; }
.about-page .about-hero__tags span { font-size: .73rem; }
.about-page .identity-label { font-size: .68rem; }
.about-page .about-hero__identity strong { font-size: 1.05rem; }
.about-page .about-hero__identity small { font-size: .76rem; }
.about-page .section-kicker { font-size: .76rem; }
.about-page .section-heading > p { font-size: .9rem; }
.about-page .purpose-index { font-size: .72rem; }
.about-page .purpose-label { font-size: .72rem; }
.about-page .purpose-card h3 { font-size: 1.2rem; }
.about-page .purpose-card p { font-size: .9rem; }
.about-page .values-list li { font-size: .84rem; }
.about-page .feature-count { font-size: .75rem; }
.about-page .feature-number { font-size: .66rem; }
.about-page .feature-item h3 { font-size: .96rem; }
.about-page .feature-item p { font-size: .8rem; }
.about-page .foundations-copy p { font-size: .83rem; }
.about-page .foundation-item strong { font-size: .82rem; }
.about-page .foundation-item small { font-size: .68rem; }
.about-page .about-button { font-size: .8rem; }

body.dark-mode .about-page {
    --about-ink: #f1f5f9;
    --about-copy: #cbd5e1;
    --about-muted: #94a3b8;
    --about-line: #334155;
}

body.dark-mode .purpose-card,
body.dark-mode .features-section,
body.dark-mode .about-button--secondary {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .purpose-card::after {
    background: rgba(37,99,235,.09);
}

body.dark-mode .vision-card::after {
    background: rgba(5,150,105,.08);
}

body.dark-mode .values-card::after {
    background: rgba(217,119,6,.08);
}

body.dark-mode .feature-item {
    border-color: #334155;
    background: linear-gradient(145deg, #172033, #1e293b);
}

body.dark-mode .feature-count {
    color: #94a3b8;
    border-color: #334155;
    background: #0f172a;
}

body.dark-mode .about-button--secondary {
    color: #cbd5e1;
}

@media (max-width: 980px) {
    .about-hero {
        grid-template-columns: 1fr;
    }

    .about-hero__identity {
        min-height: 190px;
    }

    .purpose-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .values-card {
        grid-column: 1 / -1;
        min-height: 0;
    }

    .values-list {
        grid-template-columns: repeat(2, 1fr);
    }

    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .foundations-section {
        grid-template-columns: 1fr;
        gap: 26px;
    }
}

@media (max-width: 680px) {
    .about-page {
        padding-top: 0;
    }

    .about-hero {
        gap: 30px;
        padding: 30px 22px;
        border-radius: 19px;
    }

    .about-hero h1 {
        font-size: 2rem;
    }

    .section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 14px;
    }

    .section-heading > p {
        max-width: none;
    }

    .purpose-grid,
    .features-grid,
    .values-list,
    .foundations-grid {
        grid-template-columns: 1fr;
    }

    .values-card {
        grid-column: auto;
    }

    .purpose-card {
        min-height: 0;
    }

    .features-section,
    .foundations-section {
        padding: 23px 18px;
        border-radius: 18px;
    }

    .feature-count {
        min-width: 0;
    }

    .about-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .about-button {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .purpose-card,
    .feature-item,
    .about-button {
        transition: none;
    }
}
</style>
@endsection
