@extends('layouts.qc-admin')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="faq-page">
    <section class="faq-hero" aria-labelledby="faq-title">
        <div class="faq-hero__copy">
            <div class="faq-eyebrow"><span></span>Help center</div>
            <h1 id="faq-title">Answers, without the <em>guesswork.</em></h1>
            <p>Find clear guidance for accessing your account, navigating the platform, using key features, and resolving common issues.</p>
            <div class="faq-hero__meta">
                <span><i class="fa-solid fa-book-open"></i> Practical system guidance</span>
                <span><i class="fa-solid fa-shield-halved"></i> Secure account help</span>
            </div>
        </div>
        <div class="faq-hero__visual" aria-hidden="true">
            <div class="faq-orbit faq-orbit--outer"></div>
            <div class="faq-orbit faq-orbit--inner"></div>
            <div class="faq-visual-icon"><i class="fa-solid fa-question"></i></div>
            <strong>Searchable guide</strong>
            <span>Four helpful topic groups</span>
        </div>
    </section>

    <section class="faq-search-panel" aria-label="Search frequently asked questions">
        <div class="faq-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <label class="sr-only" for="faqSearch">Search frequently asked questions</label>
            <input type="search" id="faqSearch" placeholder="Search a question or keyword..." autocomplete="off">
            <kbd aria-hidden="true">/</kbd>
            <button type="button" id="clearFaqSearch" class="faq-search__clear" aria-label="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="faq-result-summary" aria-live="polite">
            <span class="faq-result-dot"></span>
            <span id="faqResultCount">Showing all answers</span>
        </div>
    </section>

    <div class="faq-layout">
        <aside class="faq-sidebar" aria-label="FAQ categories">
            <span class="faq-sidebar__label">Browse by topic</span>
            <div class="faq-categories" role="list">
                <button class="category-btn active" type="button" data-category="all">
                    <span class="category-icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span class="category-copy"><strong>All questions</strong><small>Browse everything</small></span>
                    <span class="category-count" data-category-count="all">0</span>
                </button>
                <button class="category-btn" type="button" data-category="getting-started">
                    <span class="category-icon"><i class="fa-solid fa-rocket"></i></span>
                    <span class="category-copy"><strong>Getting started</strong><small>Access and navigation</small></span>
                    <span class="category-count" data-category-count="getting-started">0</span>
                </button>
                <button class="category-btn" type="button" data-category="features">
                    <span class="category-icon"><i class="fa-solid fa-sliders"></i></span>
                    <span class="category-copy"><strong>Features</strong><small>Facilities and reports</small></span>
                    <span class="category-count" data-category-count="features">0</span>
                </button>
                <button class="category-btn" type="button" data-category="account">
                    <span class="category-icon"><i class="fa-solid fa-user-shield"></i></span>
                    <span class="category-copy"><strong>Account and security</strong><small>Profile and password</small></span>
                    <span class="category-count" data-category-count="account">0</span>
                </button>
                <button class="category-btn" type="button" data-category="troubleshooting">
                    <span class="category-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <span class="category-copy"><strong>Troubleshooting</strong><small>Errors and data concerns</small></span>
                    <span class="category-count" data-category-count="troubleshooting">0</span>
                </button>
            </div>
            <div class="faq-sidebar__tip">
                <i class="fa-regular fa-lightbulb"></i>
                <p>Press <strong>/</strong> anywhere on this page to jump to search.</p>
            </div>
        </aside>

        <main class="faq-content" id="faqContent">
            <section class="faq-group" data-category="getting-started" aria-labelledby="group-getting-started">
                <div class="faq-group__heading">
                    <span class="faq-group__icon"><i class="fa-solid fa-rocket"></i></span>
                    <div><span>First steps</span><h2 id="group-getting-started">Getting started</h2></div>
                </div>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">01</span><span>How do I log in to the system?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Use your registered email address and password on the sign-in page. Accounts are created and managed by authorized system administrators. After your credentials are accepted, you may also be asked to verify a one-time code sent to your registered email.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">02</span><span>I forgot my password. How do I reset it?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Select “Forgot password?” on the sign-in page. Enter the username and email registered to your account, request the 6-digit OTP, then verify the code. A secure password-reset link will be sent to your registered email after successful verification.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">03</span><span>How do I navigate the dashboard?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>The dashboard provides a high-level operational overview. Use the sidebar to move between the modules available to your role, such as Facilities, Energy Monitoring, Maintenance, Reports, and administrative tools.</p></div></div>
                </article>
            </section>

            <section class="faq-group" data-category="features" aria-labelledby="group-features">
                <div class="faq-group__heading">
                    <span class="faq-group__icon"><i class="fa-solid fa-sliders"></i></span>
                    <div><span>Platform tools</span><h2 id="group-features">Features</h2></div>
                </div>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">04</span><span>How do I add a new facility?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Open Facilities from the sidebar and select the option to add a facility. Complete the required facility information and save the form. This capability is only available to roles with facility-management permission.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">05</span><span>How do I track energy consumption for a facility?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Open Energy Monitoring and select the appropriate facility. You can review available consumption records, costs, baselines, and period-based summaries depending on your role and the data recorded for that facility.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">06</span><span>How do I generate reports?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Open Reports and choose the report available for your task, such as an energy report or efficiency summary. Apply the relevant facility and reporting-period filters, then view or download the generated output if your account has permission.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">07</span><span>What is the Energy Conservation feature?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>The Energy Conservation module organizes conservation activities and provides recommendations based on available operational energy information. Recommendations should be reviewed together with facility context before implementation.</p></div></div>
                </article>
            </section>

            <section class="faq-group" data-category="account" aria-labelledby="group-account">
                <div class="faq-group__heading">
                    <span class="faq-group__icon"><i class="fa-solid fa-user-shield"></i></span>
                    <div><span>Identity and access</span><h2 id="group-account">Account and security</h2></div>
                </div>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">08</span><span>How do I update my profile information?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Open the account menu in the top-right corner and select My Profile. Update the fields available to your account and save the changes. Some identity or role information may only be changed by an administrator.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">09</span><span>How do I change my password?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Go to My Profile from the account menu and locate the password section. Enter your current password, provide and confirm a new secure password, then submit the update.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">10</span><span>How do I log out of the system?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Open your account menu in the top-right corner and select Logout. The current session will be closed and you will be returned to the sign-in page.</p></div></div>
                </article>
            </section>

            <section class="faq-group" data-category="troubleshooting" aria-labelledby="group-troubleshooting">
                <div class="faq-group__heading">
                    <span class="faq-group__icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div><span>Resolve common issues</span><h2 id="group-troubleshooting">Troubleshooting</h2></div>
                </div>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">11</span><span>I received an error message. What should I do?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Record the exact message and the action that caused it. Refresh the page and try again once. If it continues, take a screenshot without exposing passwords or OTP codes, then submit the details through the Contact page or notify your administrator.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">12</span><span>The system is running slowly. What can I do?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Check your connection, close unused browser tabs, and refresh the page. Use a current version of a supported browser. If only one module remains slow, include its name and the approximate time of the issue when contacting support.</p></div></div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" type="button">
                        <span class="faq-question__copy"><span class="faq-number">13</span><span>Some data seems incorrect. How do I verify it?</span></span>
                        <span class="faq-toggle"><i class="fa-solid fa-plus"></i></span>
                    </button>
                    <div class="faq-answer"><div><p>Compare the displayed information with the relevant meter, source record, or approved manual entry. Check the facility and reporting period selected. Report confirmed discrepancies with enough context for an authorized reviewer to investigate.</p></div></div>
                </article>
            </section>

            <div class="faq-empty" id="faqEmpty" hidden>
                <span><i class="fa-solid fa-magnifying-glass"></i></span>
                <h2>No matching answers</h2>
                <p>Try a shorter keyword, select another category, or contact the support team.</p>
                <button type="button" id="resetFaqFilters">Reset search</button>
            </div>
        </main>
    </div>

    <section class="faq-support" aria-labelledby="support-title">
        <div class="faq-support__icon"><i class="fa-regular fa-comments"></i></div>
        <div class="faq-support__copy">
            <span>Need more help?</span>
            <h2 id="support-title">Your question is not listed here?</h2>
            <p>Send the details to the support team and include the module or facility involved.</p>
        </div>
        <a href="{{ route('landing.contact') }}">Contact support <i class="fa-solid fa-arrow-right"></i></a>
    </section>

    <div class="faq-actions">
        <a href="{{ url()->previous() }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('faqSearch');
    const clearButton = document.getElementById('clearFaqSearch');
    const resetButton = document.getElementById('resetFaqFilters');
    const resultCount = document.getElementById('faqResultCount');
    const emptyState = document.getElementById('faqEmpty');
    const categoryButtons = Array.from(document.querySelectorAll('.category-btn'));
    const groups = Array.from(document.querySelectorAll('.faq-group'));
    const items = Array.from(document.querySelectorAll('.faq-item'));
    let activeCategory = 'all';

    function closeItem(item) {
        const button = item.querySelector('.faq-question');
        item.classList.remove('active');
        if (button) button.setAttribute('aria-expanded', 'false');
    }

    function openItem(item) {
        if (!item) return;
        items.forEach(closeItem);
        item.classList.add('active');
        item.querySelector('.faq-question')?.setAttribute('aria-expanded', 'true');
    }

    function applyFilters() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleItems = 0;

        groups.forEach(function (group) {
            const categoryMatches = activeCategory === 'all' || group.dataset.category === activeCategory;
            let visibleInGroup = 0;

            group.querySelectorAll('.faq-item').forEach(function (item) {
                const searchMatches = !query || item.textContent.toLowerCase().includes(query);
                const shouldShow = categoryMatches && searchMatches;
                item.hidden = !shouldShow;
                if (shouldShow) {
                    visibleItems += 1;
                    visibleInGroup += 1;
                } else {
                    closeItem(item);
                }
            });

            group.hidden = visibleInGroup === 0;
        });

        if (clearButton) clearButton.classList.toggle('is-visible', query.length > 0);
        if (emptyState) emptyState.hidden = visibleItems !== 0;
        if (resultCount) {
            resultCount.textContent = visibleItems === items.length
                ? 'Showing all ' + visibleItems + ' answers'
                : visibleItems + (visibleItems === 1 ? ' answer found' : ' answers found');
        }
    }

    categoryButtons.forEach(function (button) {
        const category = button.dataset.category;
        button.setAttribute('aria-pressed', category === activeCategory ? 'true' : 'false');
        const categoryTotal = category === 'all'
            ? items.length
            : document.querySelectorAll('.faq-group[data-category="' + category + '"] .faq-item').length;
        const countBadge = document.querySelector('[data-category-count="' + category + '"]');
        if (countBadge) countBadge.textContent = categoryTotal;

        button.addEventListener('click', function () {
            activeCategory = category;
            categoryButtons.forEach(function (candidate) {
                const active = candidate === button;
                candidate.classList.toggle('active', active);
                candidate.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            applyFilters();
            openItem(items.find(function (item) { return !item.hidden; }));
        });
    });

    items.forEach(function (item, index) {
        const button = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        if (!button || !answer) return;

        const buttonId = 'faq-question-' + (index + 1);
        const answerId = 'faq-answer-' + (index + 1);
        button.id = buttonId;
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', answerId);
        answer.id = answerId;
        answer.setAttribute('role', 'region');
        answer.setAttribute('aria-labelledby', buttonId);

        button.addEventListener('click', function () {
            const opening = !item.classList.contains('active');
            items.forEach(closeItem);
            if (opening) openItem(item);
        });
    });

    searchInput?.addEventListener('input', applyFilters);
    clearButton?.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.focus();
        applyFilters();
    });
    resetButton?.addEventListener('click', function () {
        activeCategory = 'all';
        searchInput.value = '';
        categoryButtons.forEach(function (button) {
            const active = button.dataset.category === 'all';
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        applyFilters();
        openItem(items.find(function (item) { return !item.hidden; }));
        searchInput.focus();
    });

    document.addEventListener('keydown', function (event) {
        const tagName = document.activeElement?.tagName?.toLowerCase();
        const isTyping = tagName === 'input' || tagName === 'textarea' || document.activeElement?.isContentEditable;
        if (event.key === '/' && !isTyping) {
            event.preventDefault();
            searchInput?.focus();
        }
        if (event.key === 'Escape' && document.activeElement === searchInput && searchInput.value) {
            searchInput.value = '';
            applyFilters();
        }
    });

    applyFilters();
    openItem(items.find(function (item) { return !item.hidden; }));
})();
</script>

<style>
.faq-page {
    --faq-primary: #2563eb;
    --faq-indigo: #4f46e5;
    --faq-sky: #0ea5e9;
    --faq-ink: #0f172a;
    --faq-copy: #475569;
    --faq-muted: #64748b;
    --faq-line: #dce5f0;
    max-width: 1240px;
    margin: 0 auto;
    padding: 12px 0 30px;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.faq-hero {
    position: relative;
    min-height: 315px;
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(260px, .7fr);
    align-items: center;
    gap: 52px;
    overflow: hidden;
    padding: 46px 52px;
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 24px;
    background:
        radial-gradient(circle at 88% 16%, rgba(125,211,252,.25), transparent 31%),
        radial-gradient(circle at 12% 88%, rgba(99,102,241,.2), transparent 30%),
        linear-gradient(125deg, #172554, #1e40af 53%, #2563eb);
    box-shadow: 0 24px 55px rgba(30,64,175,.22);
}

.faq-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px);
    background-size: 24px 24px;
    mask-image: linear-gradient(90deg, transparent 25%, #000);
}

.faq-hero__copy,
.faq-hero__visual {
    position: relative;
    z-index: 1;
}

.faq-eyebrow {
    width: max-content;
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 17px;
    color: #bfdbfe;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
}

.faq-eyebrow span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #7dd3fc;
    box-shadow: 0 0 0 5px rgba(125,211,252,.12);
}

.faq-hero h1 {
    max-width: 700px;
    margin: 0;
    color: #fff;
    font-size: clamp(2.25rem, 4.2vw, 3.55rem);
    line-height: 1.06;
    letter-spacing: -.052em;
}

.faq-hero h1 em {
    color: #7dd3fc;
    font-style: normal;
}

.faq-hero__copy > p {
    max-width: 690px;
    margin: 19px 0 0;
    color: rgba(226,232,240,.8);
    font-size: .84rem;
    line-height: 1.72;
}

.faq-hero__meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 24px;
}

.faq-hero__meta span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 10px;
    color: rgba(255,255,255,.78);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 999px;
    background: rgba(255,255,255,.07);
    font-size: .59rem;
    font-weight: 700;
}

.faq-hero__meta i {
    color: #7dd3fc;
}

.faq-hero__visual {
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 20px;
    background: rgba(255,255,255,.075);
    backdrop-filter: blur(12px);
    text-align: center;
}

.faq-orbit {
    position: absolute;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 50%;
}

.faq-orbit--outer { width: 230px; height: 230px; }
.faq-orbit--inner { width: 155px; height: 155px; }

.faq-visual-icon {
    position: relative;
    z-index: 1;
    width: 70px;
    height: 70px;
    display: grid;
    place-items: center;
    margin-bottom: 15px;
    color: #2563eb;
    border: 4px solid rgba(255,255,255,.25);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 15px 32px rgba(15,23,42,.2);
    font-size: 1.5rem;
}

.faq-hero__visual strong,
.faq-hero__visual > span {
    position: relative;
    z-index: 1;
    display: block;
}

.faq-hero__visual strong { color: #fff; font-size: .83rem; }
.faq-hero__visual > span { margin-top: 5px; color: rgba(226,232,240,.63); font-size: .59rem; }

.faq-search-panel {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 24px;
    margin: -26px 28px 0;
    padding: 14px;
    border: 1px solid rgba(226,232,240,.9);
    border-radius: 17px;
    background: rgba(255,255,255,.96);
    box-shadow: 0 18px 40px rgba(15,23,42,.11);
    backdrop-filter: blur(14px);
}

.faq-search {
    position: relative;
}

.faq-search > i {
    position: absolute;
    top: 50%;
    left: 15px;
    color: #94a3b8;
    transform: translateY(-50%);
}

.faq-search input {
    width: 100%;
    min-height: 50px;
    padding: 12px 92px 12px 44px;
    color: var(--faq-ink);
    border: 1.5px solid #d5dfeb;
    border-radius: 12px;
    background: #f8fafc;
    font-size: .77rem;
    transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
}

.faq-search input:focus {
    outline: none;
    border-color: var(--faq-primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,.1);
}

.faq-search kbd {
    position: absolute;
    top: 50%;
    right: 14px;
    min-width: 27px;
    padding: 4px 7px;
    color: #64748b;
    border: 1px solid #cbd5e1;
    border-bottom-width: 2px;
    border-radius: 7px;
    background: #fff;
    font-size: .58rem;
    text-align: center;
    transform: translateY(-50%);
}

.faq-search__clear {
    position: absolute;
    top: 50%;
    right: 48px;
    width: 29px;
    height: 29px;
    display: none;
    place-items: center;
    padding: 0;
    color: #64748b;
    border: 0;
    border-radius: 8px;
    background: #e2e8f0;
    cursor: pointer;
    transform: translateY(-50%);
}

.faq-search__clear.is-visible { display: grid; }

.faq-result-summary {
    min-width: 145px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: .64rem;
    font-weight: 700;
}

.faq-result-dot {
    width: 8px;
    height: 8px;
    flex: 0 0 auto;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 5px rgba(34,197,94,.1);
}

.faq-layout {
    display: grid;
    grid-template-columns: 1fr;
    align-items: start;
    gap: 28px;
    margin-top: 38px;
}

.faq-sidebar {
    position: static;
    padding: 13px;
    border: 1px solid var(--faq-line);
    border-radius: 18px;
    background: rgba(255,255,255,.94);
    box-shadow: 0 12px 32px rgba(15,23,42,.055);
}

.faq-sidebar__label {
    display: none;
}

.faq-categories {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
}

.category-btn {
    width: 100%;
    min-height: 72px;
    display: grid;
    grid-template-columns: auto minmax(0,1fr) auto;
    align-items: center;
    gap: 11px;
    padding: 10px 11px;
    color: #475569;
    border: 1px solid #e5edf7;
    border-radius: 13px;
    background: linear-gradient(145deg, #f8fbff, #fff);
    cursor: pointer;
    text-align: left;
    transition: color .16s ease, border-color .16s ease, background .16s ease, transform .16s ease, box-shadow .16s ease;
}

.category-btn:hover {
    color: #1d4ed8;
    transform: translateY(-2px);
    border-color: #bfdbfe;
    box-shadow: 0 9px 20px rgba(37,99,235,.08);
}

.category-btn.active {
    color: #fff;
    border-color: #2563eb;
    background: linear-gradient(120deg, #2563eb, #4f46e5);
    box-shadow: 0 11px 23px rgba(37,99,235,.2);
}

.category-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    color: #64748b;
    border-radius: 10px;
    background: #f1f5f9;
    font-size: .7rem;
}

.category-copy { min-width: 0; display: grid; gap: 2px; }
.category-copy strong { color: inherit; font-size: .65rem; }
.category-copy small { overflow: hidden; color: #94a3b8; font-size: .53rem; text-overflow: ellipsis; white-space: nowrap; }

.category-count {
    min-width: 23px;
    height: 23px;
    display: grid;
    place-items: center;
    color: #94a3b8;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
    font-size: .54rem;
    font-weight: 800;
}

.category-btn.active .category-icon {
    color: #fff;
    background: rgba(255,255,255,.16);
}

.category-btn.active .category-copy small {
    color: rgba(255,255,255,.68);
}

.category-btn.active .category-count {
    color: #fff;
    border-color: rgba(255,255,255,.25);
    background: rgba(255,255,255,.12);
}

.faq-sidebar__tip {
    display: none;
}

.faq-sidebar__tip i { margin-top: 2px; color: #d97706; }
.faq-sidebar__tip p { margin: 0; font-size: .55rem; line-height: 1.5; }

.faq-content {
    min-width: 0;
    width: min(100%, 1040px);
    margin: 0 auto;
}

.faq-group { margin-bottom: 32px; }

.faq-group__heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin: 0 0 12px 3px;
}

.faq-group__icon {
    width: 39px;
    height: 39px;
    display: grid;
    place-items: center;
    color: #2563eb;
    border: 1px solid #dbeafe;
    border-radius: 11px;
    background: #eff6ff;
    font-size: .75rem;
}

.faq-group__heading div > span {
    display: block;
    margin-bottom: 2px;
    color: #94a3b8;
    font-size: .52rem;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
}

.faq-group__heading h2 { margin: 0; color: var(--faq-ink); font-size: .92rem; }

.faq-item {
    overflow: hidden;
    margin-bottom: 8px;
    border: 1px solid var(--faq-line);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 7px 20px rgba(15,23,42,.035);
    transition: border-color .17s ease, box-shadow .17s ease, transform .17s ease;
}

.faq-item:hover { transform: translateY(-1px); border-color: #c5d4e7; box-shadow: 0 10px 24px rgba(15,23,42,.055); }
.faq-item.active { border-color: #93c5fd; box-shadow: 0 13px 28px rgba(37,99,235,.09); }

.faq-question {
    width: 100%;
    min-height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 16px;
    color: var(--faq-ink);
    border: 0;
    background: transparent;
    cursor: pointer;
    text-align: left;
}

.faq-question__copy { display: flex; align-items: center; gap: 13px; font-size: .75rem; font-weight: 700; line-height: 1.45; }

.faq-number {
    width: 31px;
    height: 31px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    color: #94a3b8;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #f8fafc;
    font-size: .54rem;
    font-weight: 800;
}

.faq-item.active .faq-number { color: #1d4ed8; border-color: #bfdbfe; background: #eff6ff; }

.faq-toggle {
    width: 31px;
    height: 31px;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    color: #64748b;
    border-radius: 9px;
    background: #f1f5f9;
    font-size: .65rem;
    transition: color .17s ease, background .17s ease, transform .2s ease;
}

.faq-item.active .faq-toggle { color: #fff; background: #2563eb; transform: rotate(45deg); }

.faq-answer {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows .25s ease;
}

.faq-answer > div { overflow: hidden; }
.faq-item.active .faq-answer { grid-template-rows: 1fr; }

.faq-answer p {
    margin: 0 61px 17px;
    padding: 15px 17px;
    color: var(--faq-copy);
    border-left: 3px solid #bfdbfe;
    border-radius: 0 10px 10px 0;
    background: #f8fafc;
    font-size: .68rem;
    line-height: 1.7;
}

.faq-empty {
    padding: 55px 25px;
    border: 1px dashed #cbd5e1;
    border-radius: 18px;
    background: rgba(255,255,255,.6);
    text-align: center;
}

.faq-empty > span {
    width: 54px;
    height: 54px;
    display: grid;
    place-items: center;
    margin: 0 auto 15px;
    color: #64748b;
    border-radius: 15px;
    background: #e2e8f0;
}

.faq-empty h2 { margin: 0; color: var(--faq-ink); font-size: 1rem; }
.faq-empty p { margin: 8px 0 17px; color: var(--faq-muted); font-size: .68rem; }
.faq-empty button { padding: 9px 13px; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 9px; background: #eff6ff; cursor: pointer; font-size: .65rem; font-weight: 800; }

.faq-support {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: auto minmax(0,1fr) auto;
    align-items: center;
    gap: 18px;
    margin-top: 42px;
    padding: 28px 30px;
    color: #fff;
    border-radius: 19px;
    background: radial-gradient(circle at 85% 15%, rgba(125,211,252,.22), transparent 30%), linear-gradient(110deg, #172554, #1e40af 55%, #2563eb);
    box-shadow: 0 18px 38px rgba(30,64,175,.18);
}

.faq-support__icon { width: 48px; height: 48px; display: grid; place-items: center; color: #dbeafe; border: 1px solid rgba(255,255,255,.18); border-radius: 14px; background: rgba(255,255,255,.1); font-size: 1rem; }
.faq-support__copy > span { color: #7dd3fc; font-size: .55rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.faq-support h2 { margin: 4px 0; color: #fff; font-size: 1rem; }
.faq-support p { margin: 0; color: rgba(203,213,225,.72); font-size: .64rem; }
.faq-support > a { min-height: 42px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; color: #1d4ed8; border-radius: 11px; background: #fff; text-decoration: none; font-size: .67rem; font-weight: 800; }

.faq-actions { margin-top: 18px; }
.faq-actions a { min-height: 40px; display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; color: #475569; border: 1px solid var(--faq-line); border-radius: 10px; background: #fff; text-decoration: none; font-size: .65rem; font-weight: 800; }

/* Readable information-page type scale */
.faq-page .faq-eyebrow { font-size: .78rem; }
.faq-page .faq-hero__copy > p { font-size: .98rem; }
.faq-page .faq-hero__meta span { font-size: .72rem; }
.faq-page .faq-hero__visual strong { font-size: 1rem; }
.faq-page .faq-hero__visual > span { font-size: .72rem; }
.faq-page .faq-search input { font-size: .9rem; }
.faq-page .faq-search kbd { font-size: .68rem; }
.faq-page .faq-result-summary { font-size: .76rem; }
.faq-page .category-icon { font-size: .8rem; }
.faq-page .category-copy strong { font-size: .78rem; }
.faq-page .category-copy small { font-size: .66rem; }
.faq-page .category-count { font-size: .65rem; }
.faq-page .faq-group__heading div > span { font-size: .64rem; }
.faq-page .faq-group__heading h2 { font-size: 1.08rem; }
.faq-page .faq-question__copy { font-size: .9rem; }
.faq-page .faq-number { font-size: .65rem; }
.faq-page .faq-answer p { font-size: .84rem; }
.faq-page .faq-empty p,
.faq-page .faq-empty button { font-size: .8rem; }
.faq-page .faq-support__copy > span { font-size: .66rem; }
.faq-page .faq-support h2 { font-size: 1.12rem; }
.faq-page .faq-support p,
.faq-page .faq-support > a,
.faq-page .faq-actions a { font-size: .8rem; }

body.dark-mode .faq-page { --faq-ink: #f1f5f9; --faq-copy: #cbd5e1; --faq-muted: #94a3b8; --faq-line: #334155; }
body.dark-mode .faq-search-panel,
body.dark-mode .faq-sidebar,
body.dark-mode .faq-item,
body.dark-mode .faq-actions a { background: #1e293b; border-color: #334155; }
body.dark-mode .faq-search input { color: #f1f5f9; border-color: #334155; background: #0f172a; }
body.dark-mode .faq-search input:focus { border-color: #3b82f6; background: #111c30; }
body.dark-mode .faq-search kbd,
body.dark-mode .category-count { color: #94a3b8; border-color: #475569; background: #0f172a; }
body.dark-mode .category-btn:hover { background: #172033; }
body.dark-mode .category-btn {
    border-color: #334155;
    background: linear-gradient(145deg, #172033, #1e293b);
}
body.dark-mode .category-btn.active {
    color: #fff;
    border-color: #2563eb;
    background: linear-gradient(120deg, #2563eb, #4f46e5);
}
body.dark-mode .category-icon,
body.dark-mode .faq-number,
body.dark-mode .faq-toggle,
body.dark-mode .faq-sidebar__tip { background: #0f172a; }
body.dark-mode .faq-answer p { color: #cbd5e1; border-left-color: #2563eb; background: #0f172a; }
body.dark-mode .faq-empty { border-color: #475569; background: rgba(15,23,42,.45); }

@media (max-width: 900px) {
    .faq-hero { grid-template-columns: 1fr; }
    .faq-hero__visual { min-height: 185px; }
    .faq-sidebar { position: static; overflow-x: auto; }
    .faq-sidebar__label,
    .faq-sidebar__tip { display: none; }
    .faq-categories { display: flex; min-width: max-content; }
    .category-btn { width: auto; min-height: 48px; grid-template-columns: auto auto; padding: 7px 10px; }
    .category-copy small,
    .category-count { display: none; }
}

@media (min-width: 901px) and (max-width: 1120px) {
    .faq-categories {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .faq-page { padding-top: 0; }
    .faq-hero { gap: 28px; padding: 30px 22px; border-radius: 19px; }
    .faq-hero h1 { font-size: 2.15rem; }
    .faq-search-panel { grid-template-columns: 1fr; gap: 8px; margin-right: 10px; margin-left: 10px; }
    .faq-result-summary { padding: 0 5px 3px; }
    .faq-layout { margin-top: 35px; }
    .faq-sidebar { margin-right: -4px; margin-left: -4px; padding: 10px; }
    .category-icon { width: 30px; height: 30px; }
    .faq-question { min-height: 62px; padding: 12px; }
    .faq-question__copy { gap: 9px; font-size: .82rem; }
    .faq-number { width: 28px; height: 28px; }
    .faq-answer p { margin: 0 12px 13px 49px; }
    .faq-support { grid-template-columns: auto 1fr; padding: 23px 20px; }
    .faq-support > a { grid-column: 1 / -1; width: 100%; justify-content: center; }
}

@media (prefers-reduced-motion: reduce) {
    .faq-item,
    .faq-toggle,
    .faq-answer { transition: none; }
}
</style>
@endsection
