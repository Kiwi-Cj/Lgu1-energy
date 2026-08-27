@extends('layouts.qc-admin')

@section('title', 'Privacy Notice')

@section('content')
<div class="privacy-page">
    <section class="privacy-hero" aria-labelledby="privacy-title">
        <div class="privacy-hero__copy">
            <div class="privacy-eyebrow"><span></span>Privacy and data handling</div>
            <h1 id="privacy-title">Your information deserves <em>clear protection.</em></h1>
            <p>This notice explains the types of information handled by {{ $systemName }}, why they are needed, and the choices available to system users.</p>
            <div class="privacy-hero__tags">
                <span><i class="fa-solid fa-user-shield"></i> Account safeguards</span>
                <span><i class="fa-solid fa-eye"></i> Transparent use</span>
                <span><i class="fa-solid fa-scale-balanced"></i> Responsible handling</span>
            </div>
        </div>
        <div class="privacy-hero__visual" aria-hidden="true">
            <div class="privacy-orbit"></div>
            <span class="privacy-shield"><i class="fa-solid fa-shield-halved"></i></span>
            <strong>Privacy notice</strong>
            <small>Information for platform users</small>
        </div>
    </section>

    <section class="privacy-document" aria-label="Privacy notice document">
        <header class="document-header">
            <div class="document-header__main">
                <span class="document-header__icon"><i class="fa-solid fa-file-shield"></i></span>
                <div>
                    <span class="document-kicker">Policy document</span>
                    <h2>How information is handled</h2>
                    <p>Read each section or use the contents menu to jump directly to a topic.</p>
                </div>
            </div>
            <div class="document-header__meta">
                <div class="document-topic-count">
                    <i class="fa-solid fa-list-check"></i>
                    <div><small>Contents</small><strong>8 topics</strong></div>
                </div>
                <div class="document-status">
                    <span class="status-dot"></span>
                    <div><small>Status</small><strong>Current notice</strong></div>
                </div>
            </div>
        </header>

        <div class="document-layout">
            <aside class="document-sidebar" aria-label="Privacy notice contents">
                <span class="document-sidebar__label">On this page</span>
                <nav>
                    <a href="#privacy-scope" class="is-active"><span>01</span> Scope</a>
                    <a href="#privacy-collection"><span>02</span> Information handled</a>
                    <a href="#privacy-use"><span>03</span> How information is used</a>
                    <a href="#privacy-security"><span>04</span> Security</a>
                    <a href="#privacy-retention"><span>05</span> Retention</a>
                    <a href="#privacy-rights"><span>06</span> Your choices</a>
                    <a href="#privacy-cookies"><span>07</span> Browser storage</a>
                    <a href="#privacy-contact"><span>08</span> Questions</a>
                </nav>
                <div class="document-sidebar__note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Operational policies and applicable legal requirements may affect specific requests.</p>
                </div>
            </aside>

            <div class="document-content">
                <article class="privacy-section" id="privacy-scope">
                    <div class="section-marker"><span>01</span><i class="fa-solid fa-file-shield"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Purpose</span>
                        <h2>Scope of this notice</h2>
                        <p>This notice applies to personal and operational information processed through {{ $systemName }}. It covers account access, profile information, activity within the platform, contact requests, and records associated with authorized system use.</p>
                        <div class="privacy-callout">
                            <i class="fa-solid fa-lightbulb"></i>
                            <p>The platform is intended for authorized local government personnel and other approved users.</p>
                        </div>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-collection">
                    <div class="section-marker"><span>02</span><i class="fa-solid fa-database"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Data categories</span>
                        <h2>Information the platform may handle</h2>
                        <p>The exact information depends on your role and the actions available to your account.</p>
                        <ul class="privacy-list">
                            <li><i class="fa-solid fa-check"></i><div><strong>Account information</strong><span>Name, username, registered email, and authentication-related information.</span></div></li>
                            <li><i class="fa-solid fa-check"></i><div><strong>Professional context</strong><span>Role, department, permissions, or assigned facilities.</span></div></li>
                            <li><i class="fa-solid fa-check"></i><div><strong>System activity</strong><span>Sign-in events, submissions, reviews, and other actions relevant to accountability.</span></div></li>
                            <li><i class="fa-solid fa-check"></i><div><strong>Messages and requests</strong><span>Information submitted through contact or support workflows.</span></div></li>
                        </ul>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-use">
                    <div class="section-marker"><span>03</span><i class="fa-solid fa-gears"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Processing purposes</span>
                        <h2>How information is used</h2>
                        <p>Information is used only where it supports system operation, authorized public-sector work, security, or administrative accountability.</p>
                        <div class="purpose-grid">
                            <div><i class="fa-solid fa-key"></i><strong>Provide access</strong><span>Verify accounts and apply role permissions.</span></div>
                            <div><i class="fa-solid fa-chart-line"></i><strong>Support operations</strong><span>Maintain facility and energy workflows.</span></div>
                            <div><i class="fa-solid fa-bell"></i><strong>Communicate</strong><span>Deliver relevant alerts and responses.</span></div>
                            <div><i class="fa-solid fa-clipboard-check"></i><strong>Maintain accountability</strong><span>Support review and authorized oversight.</span></div>
                        </div>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-security">
                    <div class="section-marker"><span>04</span><i class="fa-solid fa-lock"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Safeguards</span>
                        <h2>Security and access controls</h2>
                        <p>The platform uses administrative and technical safeguards intended to reduce unauthorized access and misuse. These include verified accounts, password protection, one-time-code workflows where configured, session controls, and role-based permissions.</p>
                        <p>No system can guarantee absolute security. Users must protect their credentials, avoid sharing OTP codes, and report suspected unauthorized access promptly.</p>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-retention">
                    <div class="section-marker"><span>05</span><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Lifecycle</span>
                        <h2>Retention and disposal</h2>
                        <p>Information should be kept only for as long as it remains necessary for authorized operations, accountability, security, or applicable legal and records-management requirements. Retention periods may vary by record type and organizational policy.</p>
                        <p>When information is no longer required, it should be deleted, anonymized, or otherwise handled through an approved disposal process.</p>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-rights">
                    <div class="section-marker"><span>06</span><i class="fa-solid fa-user-check"></i></div>
                    <div class="section-copy">
                        <span class="section-label">User requests</span>
                        <h2>Your privacy choices</h2>
                        <p>Subject to applicable law and organizational policy, you may ask about personal information associated with your account or request that inaccurate information be reviewed and corrected.</p>
                        <ul class="privacy-list compact">
                            <li><i class="fa-solid fa-check"></i><div><strong>Access</strong><span>Ask what personal information is associated with your account.</span></div></li>
                            <li><i class="fa-solid fa-check"></i><div><strong>Correction</strong><span>Request review of inaccurate or incomplete personal information.</span></div></li>
                            <li><i class="fa-solid fa-check"></i><div><strong>Other requests</strong><span>Raise a privacy concern or ask how a specific record is handled.</span></div></li>
                        </ul>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-cookies">
                    <div class="section-marker"><span>07</span><i class="fa-solid fa-cookie-bite"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Device information</span>
                        <h2>Cookies and browser storage</h2>
                        <p>The platform uses essential session mechanisms required for authentication, security, and normal operation. It may also store preferences such as the selected display theme in your browser.</p>
                        <p>Blocking essential browser storage may prevent sign-in or cause parts of the platform to work incorrectly.</p>
                    </div>
                </article>

                <article class="privacy-section" id="privacy-contact">
                    <div class="section-marker"><span>08</span><i class="fa-solid fa-envelope-open-text"></i></div>
                    <div class="section-copy">
                        <span class="section-label">Contact</span>
                        <h2>Questions or privacy concerns</h2>
                        <p>Use the official contact form to ask a question or submit a privacy-related concern. Do not include passwords, OTP codes, password-reset links, or other authentication secrets in your message.</p>
                        <a class="privacy-contact-link" href="{{ route('landing.contact') }}">Open contact form <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
        </div>

        <footer class="document-footer">
            <div><i class="fa-regular fa-file-lines"></i><span><small>Document</small><strong>Privacy Notice</strong></span></div>
            <div><i class="fa-solid fa-building-shield"></i><span><small>Responsible organization</small><strong>{{ $systemOrganization }}</strong></span></div>
            <div><i class="fa-solid fa-rotate"></i><span><small>Review</small><strong>Update when practices change</strong></span></div>
        </footer>
    </section>

    <div class="privacy-actions">
        <a href="{{ url()->previous() }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>

<script>
(function () {
    const links = Array.from(document.querySelectorAll('.document-sidebar a'));
    const sections = links.map(function (link) {
        return document.querySelector(link.getAttribute('href'));
    }).filter(Boolean);

    if (!('IntersectionObserver' in window) || !sections.length) return;

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            links.forEach(function (link) {
                const active = link.getAttribute('href') === '#' + entry.target.id;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        });
    }, { rootMargin: '-22% 0px -68% 0px', threshold: 0 });

    sections.forEach(function (section) { observer.observe(section); });
})();
</script>

<style>
.privacy-page {
    --privacy-primary: #2563eb;
    --privacy-indigo: #4f46e5;
    --privacy-ink: #0f172a;
    --privacy-copy: #475569;
    --privacy-muted: #64748b;
    --privacy-line: #dce5f0;
    max-width: 1240px;
    margin: 0 auto;
    padding: 12px 0 30px;
}

.privacy-hero {
    position: relative;
    min-height: 315px;
    display: grid;
    grid-template-columns: minmax(0,1.3fr) minmax(260px,.7fr);
    align-items: center;
    gap: 52px;
    overflow: hidden;
    padding: 46px 52px;
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 24px;
    background: radial-gradient(circle at 88% 14%, rgba(45,212,191,.23), transparent 30%), radial-gradient(circle at 12% 88%, rgba(99,102,241,.2), transparent 30%), linear-gradient(125deg, #172554, #1e3a8a 52%, #0f766e);
    box-shadow: 0 24px 55px rgba(15,118,110,.17);
}

.privacy-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
    background-size: 44px 44px;
    pointer-events: none;
}

.privacy-hero__copy,
.privacy-hero__visual { position: relative; z-index: 1; }

.privacy-eyebrow {
    width: max-content;
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 17px;
    color: #99f6e4;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.privacy-eyebrow span { width: 8px; height: 8px; border-radius: 50%; background: #5eead4; box-shadow: 0 0 0 5px rgba(94,234,212,.12); }
.privacy-hero h1 { max-width: 700px; margin: 0; color: #fff; font-size: clamp(2.25rem,4.2vw,3.55rem); line-height: 1.06; letter-spacing: -.052em; }
.privacy-hero h1 em { color: #5eead4; font-style: normal; }
.privacy-hero__copy > p { max-width: 690px; margin: 19px 0 0; color: rgba(226,232,240,.82); font-size: .98rem; line-height: 1.72; }
.privacy-hero__tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 24px; }
.privacy-hero__tags span { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; color: rgba(255,255,255,.8); border: 1px solid rgba(255,255,255,.14); border-radius: 999px; background: rgba(255,255,255,.07); font-size: .72rem; font-weight: 700; }
.privacy-hero__tags i { color: #5eead4; }

.privacy-hero__visual { min-height: 220px; display: flex; align-items: center; justify-content: center; flex-direction: column; overflow: hidden; border: 1px solid rgba(255,255,255,.16); border-radius: 20px; background: rgba(255,255,255,.075); backdrop-filter: blur(12px); text-align: center; }
.privacy-orbit { position: absolute; width: 210px; height: 210px; border: 1px solid rgba(255,255,255,.1); border-radius: 50%; box-shadow: 0 0 0 32px rgba(255,255,255,.025); }
.privacy-shield { position: relative; z-index: 1; width: 70px; height: 70px; display: grid; place-items: center; margin-bottom: 14px; color: #0f766e; border: 4px solid rgba(255,255,255,.25); border-radius: 22px; background: #fff; box-shadow: 0 15px 32px rgba(15,23,42,.2); font-size: 1.45rem; }
.privacy-hero__visual strong,
.privacy-hero__visual small { position: relative; z-index: 1; display: block; }
.privacy-hero__visual strong { color: #fff; font-size: 1rem; }
.privacy-hero__visual small { margin-top: 5px; color: rgba(226,232,240,.66); font-size: .72rem; }

.privacy-document {
    position: relative;
    margin-top: 28px;
    overflow: hidden;
    border: 1px solid var(--privacy-line);
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 20px 50px rgba(15,23,42,.075);
}

.privacy-document::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 2;
    height: 4px;
    background: linear-gradient(90deg,#2563eb,#4f46e5,#0d9488);
}

.document-header {
    min-height: 130px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    padding: 24px 30px;
    border-bottom: 1px solid var(--privacy-line);
    background: radial-gradient(circle at 92% 10%,rgba(37,99,235,.07),transparent 30%),linear-gradient(145deg,#f8fbff,#fff);
}

.document-header__main {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 17px;
}

.document-header__icon {
    width: 54px;
    height: 54px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 15px;
    background: linear-gradient(145deg,#eff6ff,#dbeafe);
    box-shadow: 0 9px 20px rgba(37,99,235,.11);
    font-size: 1.05rem;
}

.document-kicker { display: block; margin-bottom: 6px; color: var(--privacy-primary); font-size: .74rem; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; }
.document-header h2 { margin: 0; color: var(--privacy-ink); font-size: 1.65rem; letter-spacing: -.035em; }
.document-header p { margin: 7px 0 0; color: var(--privacy-muted); font-size: .86rem; }

.document-header__meta {
    flex: 0 0 auto;
    display: flex;
    align-items: stretch;
    gap: 8px;
}

.document-topic-count,
.document-status {
    min-width: 132px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    background: rgba(255,255,255,.86);
}

.document-topic-count > i {
    width: 29px;
    height: 29px;
    display: grid;
    place-items: center;
    color: #2563eb;
    border-radius: 9px;
    background: #eff6ff;
    font-size: .7rem;
}

.document-status { border-color: #bbf7d0; background: #f0fdf4; }
.status-dot { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 5px rgba(34,197,94,.1); }
.document-topic-count small,
.document-topic-count strong,
.document-status small,
.document-status strong { display: block; }
.document-topic-count small,
.document-status small { color: #64748b; font-size: .66rem; }
.document-topic-count strong { margin-top: 2px; color: #1e40af; font-size: .78rem; }
.document-status strong { margin-top: 2px; color: #166534; font-size: .78rem; }

.document-layout { display: grid; grid-template-columns: 255px minmax(0,1fr); align-items: start; }
.document-sidebar {
    position: sticky;
    top: 70px;
    min-height: 100%;
    padding: 27px 18px;
    border-right: 1px solid var(--privacy-line);
    background: linear-gradient(180deg,#f8fbff,#f8fafc);
}
.document-sidebar__label { display: block; margin: 0 9px 12px; color: #94a3b8; font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.document-sidebar nav { display: grid; gap: 3px; }
.document-sidebar nav a {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 11px;
    color: #64748b;
    border: 1px solid transparent;
    border-radius: 10px;
    text-decoration: none;
    font-size: .8rem;
    font-weight: 700;
    transition: color .16s ease,background .16s ease,border-color .16s ease,transform .16s ease;
}
.document-sidebar nav a span { color: #b2bfd0; font-size: .68rem; font-weight: 800; }
.document-sidebar nav a:hover,
.document-sidebar nav a.is-active {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
    transform: translateX(2px);
}
.document-sidebar nav a.is-active::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 10px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #2563eb;
    transform: translateY(-50%);
}
.document-sidebar nav a.is-active span { color: #2563eb; }
.document-sidebar__note { display: flex; align-items: flex-start; gap: 9px; margin-top: 20px; padding: 12px; color: #64748b; border-radius: 11px; background: #f8fafc; }
.document-sidebar__note i { margin-top: 2px; color: #2563eb; }
.document-sidebar__note p { margin: 0; font-size: .72rem; line-height: 1.55; }

.document-content {
    position: relative;
    min-width: 0;
    padding: 10px 40px 18px;
}
.privacy-section {
    display: grid;
    grid-template-columns: 58px minmax(0,1fr);
    gap: 22px;
    padding: 36px 0;
    border-bottom: 1px solid var(--privacy-line);
    scroll-margin-top: 90px;
}
.privacy-section:last-child { border-bottom: 0; }
.section-marker {
    position: relative;
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 8px;
}
.privacy-section:not(:last-child) .section-marker::after {
    content: '';
    position: absolute;
    top: 76px;
    bottom: -37px;
    left: 50%;
    width: 1px;
    background: linear-gradient(#bfdbfe,#e2e8f0);
    transform: translateX(-50%);
}
.section-marker > span { color: #94a3b8; font-size: .68rem; font-weight: 800; letter-spacing: .08em; }
.section-marker i {
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    color: #2563eb;
    border: 1px solid #bfdbfe;
    border-radius: 14px;
    background: linear-gradient(145deg,#eff6ff,#dbeafe);
    box-shadow: 0 7px 16px rgba(37,99,235,.09);
    font-size: .92rem;
}
.section-copy { min-width: 0; max-width: 880px; }
.section-label { display: block; margin-bottom: 6px; color: #2563eb; font-size: .7rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.section-copy h2 { margin: 0 0 13px; color: var(--privacy-ink); font-size: 1.35rem; letter-spacing: -.025em; }
.section-copy > p { margin: 0 0 12px; color: var(--privacy-copy); font-size: .96rem; line-height: 1.78; }
.privacy-callout { display: flex; align-items: flex-start; gap: 10px; margin-top: 17px; padding: 13px 15px; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 11px; background: #eff6ff; }
.privacy-callout i { margin-top: 3px; color: #d97706; }
.privacy-callout p { margin: 0; font-size: .82rem; line-height: 1.55; }

.privacy-list { display: grid; gap: 9px; margin: 17px 0 0; padding: 0; list-style: none; }
.privacy-list li { display: flex; align-items: flex-start; gap: 10px; padding: 12px; border: 1px solid #e5edf7; border-radius: 11px; background: #f8fafc; }
.privacy-list li > i { width: 21px; height: 21px; flex: 0 0 auto; display: grid; place-items: center; margin-top: 1px; color: #15803d; border-radius: 50%; background: #dcfce7; font-size: .58rem; }
.privacy-list strong,
.privacy-list span { display: block; }
.privacy-list strong { color: var(--privacy-ink); font-size: .84rem; }
.privacy-list span { margin-top: 3px; color: var(--privacy-muted); font-size: .78rem; line-height: 1.5; }
.privacy-list.compact { grid-template-columns: repeat(3,1fr); }

.purpose-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 9px; margin-top: 17px; }
.purpose-grid > div { display: grid; grid-template-columns: auto 1fr; gap: 4px 10px; padding: 14px; border: 1px solid #e5edf7; border-radius: 12px; background: #f8fafc; }
.purpose-grid i { grid-row: 1 / span 2; width: 35px; height: 35px; display: grid; place-items: center; color: #2563eb; border-radius: 10px; background: #dbeafe; }
.purpose-grid strong { color: var(--privacy-ink); font-size: .82rem; }
.purpose-grid span { color: var(--privacy-muted); font-size: .74rem; line-height: 1.45; }
.privacy-contact-link { min-height: 43px; display: inline-flex; align-items: center; gap: 8px; margin-top: 9px; padding: 10px 14px; color: #fff; border-radius: 10px; background: linear-gradient(105deg,#2563eb,#4f46e5); box-shadow: 0 9px 18px rgba(37,99,235,.18); text-decoration: none; font-size: .82rem; font-weight: 800; }

.document-footer { display: grid; grid-template-columns: repeat(3,1fr); gap: 1px; padding: 1px; border-top: 1px solid var(--privacy-line); background: var(--privacy-line); }
.document-footer > div { display: flex; align-items: center; gap: 10px; padding: 17px 20px; background: #f8fafc; }
.document-footer i { color: #2563eb; }
.document-footer span,
.document-footer small,
.document-footer strong { display: block; }
.document-footer small { color: #94a3b8; font-size: .66rem; }
.document-footer strong { margin-top: 2px; color: #334155; font-size: .75rem; }
.privacy-actions { margin-top: 18px; }
.privacy-actions a { min-height: 42px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 15px; color: #475569; border: 1px solid var(--privacy-line); border-radius: 10px; background: #fff; text-decoration: none; font-size: .8rem; font-weight: 800; }

body.dark-mode .privacy-page { --privacy-ink:#f1f5f9; --privacy-copy:#cbd5e1; --privacy-muted:#94a3b8; --privacy-line:#334155; }
body.dark-mode .privacy-document,
body.dark-mode .privacy-actions a { border-color:#334155; background:#1e293b; }
body.dark-mode .document-header { background:linear-gradient(145deg,#172033,#1e293b); }
body.dark-mode .document-header__icon,
body.dark-mode .section-marker i { color:#93c5fd; border-color:#334155; background:linear-gradient(145deg,#172554,#1e3a8a); }
body.dark-mode .document-topic-count { border-color:#334155; background:#0f172a; }
body.dark-mode .document-topic-count strong { color:#bfdbfe; }
body.dark-mode .document-status { border-color:#166534; background:#052e1a; }
body.dark-mode .document-sidebar { border-color:#334155; background:linear-gradient(180deg,#111c30,#0f172a); }
body.dark-mode .document-sidebar__note,
body.dark-mode .privacy-list li,
body.dark-mode .purpose-grid > div,
body.dark-mode .document-footer > div { border-color:#334155; background:#0f172a; }
body.dark-mode .document-sidebar nav a:hover,
body.dark-mode .document-sidebar nav a.is-active { color:#bfdbfe; background:rgba(37,99,235,.15); }
body.dark-mode .privacy-section:not(:last-child) .section-marker::after { background:linear-gradient(#1d4ed8,#334155); }
body.dark-mode .privacy-callout { color:#bfdbfe; border-color:#1d4ed8; background:rgba(37,99,235,.13); }
body.dark-mode .document-footer strong { color:#e2e8f0; }

@media (max-width: 940px) {
    .privacy-hero { grid-template-columns:1fr; }
    .privacy-hero__visual { min-height:185px; }
    .document-layout { grid-template-columns:1fr; }
    .document-sidebar { position:static; overflow-x:auto; padding:12px; border-right:0; border-bottom:1px solid var(--privacy-line); }
    .document-sidebar__label,
    .document-sidebar__note { display:none; }
    .document-sidebar nav { display:flex; min-width:max-content; }
    .document-sidebar nav a { padding:9px 11px; }
    .privacy-list.compact { grid-template-columns:1fr; }
}

@media (min-width: 941px) and (max-width: 1120px) {
    .document-header {
        align-items: flex-start;
    }

    .document-header__meta {
        flex-direction: column;
    }
}

@media (max-width: 650px) {
    .privacy-page { padding-top:0; }
    .privacy-hero { gap:28px; padding:30px 22px; border-radius:19px; }
    .privacy-hero h1 { font-size:2.15rem; }
    .document-header { align-items:flex-start; flex-direction:column; padding:22px 18px; }
    .document-header__main { align-items:flex-start; }
    .document-header__icon { width:46px; height:46px; }
    .document-header__meta { width:100%; }
    .document-topic-count,
    .document-status { min-width:0; flex:1; }
    .document-content { padding:4px 18px 10px; }
    .privacy-section { grid-template-columns:1fr; gap:12px; padding:28px 0; }
    .section-marker { align-items:center; flex-direction:row; }
    .privacy-section:not(:last-child) .section-marker::after { display:none; }
    .purpose-grid,
    .document-footer { grid-template-columns:1fr; }
    .document-footer { gap:1px; }
    .section-copy > p { font-size:.88rem; }
}
</style>
@endsection
