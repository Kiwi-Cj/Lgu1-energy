@extends('layouts.qc-admin')

@section('title', 'User Guide & Manual - '.$systemName)

@section('content')
<div class="guide-page" id="guidePageApp" data-guide-lang="tl">
    {{-- Hero Section --}}
    <section class="guide-hero" aria-labelledby="guide-title">
        <div class="guide-hero__copy">
            <div class="guide-hero__top-bar">
                <div class="guide-eyebrow">
                    <span class="pulse-dot"></span>
                    <span class="lang-tl">Opisyal na Gabay sa Paggamit</span>
                    <span class="lang-en">Official System User Manual</span>
                </div>

                {{-- Language Toggle Switcher --}}
                <div class="guide-lang-switcher" aria-label="Language selection">
                    <span class="lang-switcher-label"><i class="fa-solid fa-language"></i> Wika / Language:</span>
                    <div class="lang-btn-group" role="group">
                        <button type="button" class="lang-toggle-btn active" data-lang-val="tl">
                            <span class="flag-icon">🇵🇭</span> Tagalog
                        </button>
                        <button type="button" class="lang-toggle-btn" data-lang-val="en">
                            <span class="flag-icon">🇺🇸</span> English
                        </button>
                    </div>
                </div>
            </div>

            <h1 id="guide-title">
                <span class="lang-tl">Hakbang-hakbang na <em>Gabay sa Paggamit</em> ng System</span>
                <span class="lang-en">Step-by-Step <em>User Guide &amp; Manual</em></span>
            </h1>
            
            <p class="lang-tl">
                Alamin kung paano gamitin ang bawat module ng <strong>{{ $systemName }}</strong> mula sa pag-setup ng pasilidad, pag-encode ng buwanang bill ng kuryente, pag-monitor ng konsumo gamit ang AI, hanggang sa pag-export ng mga ulat para sa inyong LGU.
            </p>
            <p class="lang-en">
                Learn how to effectively use every module of <strong>{{ $systemName }}</strong> from facility setup, monthly electricity bill encoding, AI-powered consumption analytics, to generating official energy reports for your LGU.
            </p>

            <div class="guide-hero__meta">
                <span><i class="fa-solid fa-layer-group"></i> <span class="lang-tl">10 Kumpletong Modyul</span><span class="lang-en">10 Complete Modules</span></span>
                <span><i class="fa-solid fa-users-gear"></i> <span class="lang-tl">Para sa Lahat ng Roles</span><span class="lang-en">For All User Roles</span></span>
                <span><i class="fa-solid fa-bolt"></i> <span class="lang-tl">Bilingual Reference</span><span class="lang-en">Tagalog &amp; English</span></span>
            </div>

            <div class="guide-hero__actions">
                <button type="button" class="btn-guide-print" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> <span class="lang-tl">I-print / I-save bilang PDF</span><span class="lang-en">Print / Save as PDF</span>
                </button>
                <a href="#quick-workflow" class="btn-guide-workflow">
                    <i class="fa-solid fa-diagram-project"></i> <span class="lang-tl">Mabilisang Daloy (Workflow)</span><span class="lang-en">Standard Workflow</span>
                </a>
            </div>
        </div>

        <div class="guide-hero__visual" aria-hidden="true">
            <div class="guide-orbit guide-orbit--outer"></div>
            <div class="guide-orbit guide-orbit--inner"></div>
            <div class="guide-visual-card">
                <div class="visual-icon-box">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <strong>
                    <span class="lang-tl">Interactive Manual</span>
                    <span class="lang-en">Interactive Manual</span>
                </strong>
                <span>
                    <span class="lang-tl">Piliin ang wika, paksa, o mag-search</span>
                    <span class="lang-en">Select language, topic, or search</span>
                </span>
                <div class="visual-badge-list">
                    <span class="role-badge badge-admin">Super Admin / Admin</span>
                    <span class="role-badge badge-officer">Energy Officer</span>
                    <span class="role-badge badge-staff">Staff</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Search and Filter Bar --}}
    <section class="guide-search-panel" aria-label="Maghanap sa Gabay / Search User Guide">
        <div class="guide-search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
            <label class="sr-only" for="guideSearch">Search guide</label>
            <input type="search" id="guideSearch" 
                   data-placeholder-tl="Maghanap ng keyword, module, o hakbang (hal. 'Monthly Records', 'Meters', 'Export', 'Baseline')..."
                   data-placeholder-en="Search by keyword, module, or step (e.g., 'Monthly Records', 'Meters', 'Export', 'Baseline')..."
                   placeholder="Maghanap ng keyword, module, o hakbang (hal. 'Monthly Records', 'Meters', 'Export', 'Baseline')..." 
                   autocomplete="off">
            <kbd class="search-kbd" aria-hidden="true">/</kbd>
            <button type="button" id="clearGuideSearch" class="guide-search__clear" aria-label="Clear search" style="display:none;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="guide-result-summary" aria-live="polite">
            <span class="result-dot"></span>
            <span id="guideResultCount">
                <span class="lang-tl">Ipinapakita ang lahat ng 10 na paksa</span>
                <span class="lang-en">Showing all 10 topics</span>
            </span>
        </div>
    </section>

    {{-- Main Guide Container --}}
    <div class="guide-layout">
        {{-- Left Sticky Topic Navigation --}}
        <aside class="guide-sidebar" aria-label="Talaan ng Nilalaman / Table of Contents">
            <div class="sidebar-sticky-inner">
                <span class="guide-sidebar__title">
                    <i class="fa-solid fa-list-ol"></i> 
                    <span class="lang-tl">Talaan ng Nilalaman</span>
                    <span class="lang-en">Table of Contents</span>
                </span>
                <div class="guide-nav-list" role="list">
                    <button class="guide-nav-btn active" type="button" data-target="all">
                        <span class="btn-icon"><i class="fa-solid fa-border-all"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">Lahat ng Paksa</span><span class="lang-en">All Topics</span></strong>
                            <small><span class="lang-tl">Buong Gabay</span><span class="lang-en">Full Manual</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-roles">
                        <span class="btn-icon"><i class="fa-solid fa-id-badge"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">1. Mga User Roles</span><span class="lang-en">1. User Roles &amp; Access</span></strong>
                            <small><span class="lang-tl">Access at Permissions</span><span class="lang-en">Permissions Matrix</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-login">
                        <span class="btn-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">2. Pag-login at Dashboard</span><span class="lang-en">2. Sign-in &amp; Dashboard</span></strong>
                            <small><span class="lang-tl">Pagsisimula sa System</span><span class="lang-en">Getting Started</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-facility">
                        <span class="btn-icon"><i class="fa-solid fa-building"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">3. Pasilidad at Meters</span><span class="lang-en">3. Facility Registry &amp; Meters</span></strong>
                            <small><span class="lang-tl">Setup ng Gusali at Kuntador</span><span class="lang-en">Buildings &amp; Meter Setup</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-energy-profile">
                        <span class="btn-icon"><i class="fa-solid fa-sliders"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">4. Energy Profile &amp; Baseline</span><span class="lang-en">4. Energy Profile &amp; Baseline</span></strong>
                            <small><span class="lang-tl">Target at Utility Account</span><span class="lang-en">Utility Account &amp; Target</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-monthly-records">
                        <span class="btn-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">5. Monthly Records &amp; Bill</span><span class="lang-en">5. Monthly Records &amp; Billing</span></strong>
                            <small><span class="lang-tl">Pag-encode at Attachment</span><span class="lang-en">Data Encoding &amp; Upload</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-monitoring">
                        <span class="btn-icon"><i class="fa-solid fa-chart-line"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">6. Monitoring &amp; AI Alerts</span><span class="lang-en">6. Monitoring &amp; AI Alerts</span></strong>
                            <small><span class="lang-tl">Analytics at Anomaly Detection</span><span class="lang-en">Analytics &amp; Anomaly Detection</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-load-budget">
                        <span class="btn-icon"><i class="fa-solid fa-plug-circle-bolt"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">7. Load Tracking &amp; Budget</span><span class="lang-en">7. Load Tracking &amp; Budget</span></strong>
                            <small><span class="lang-tl">Equipment at Cash Flow</span><span class="lang-en">Equipment &amp; Cash Flow</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-conservation">
                        <span class="btn-icon"><i class="fa-solid fa-leaf"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">8. Conservation &amp; Maintenance</span><span class="lang-en">8. Conservation &amp; Maintenance</span></strong>
                            <small><span class="lang-tl">Checklist at Schedule</span><span class="lang-en">Daily Routine &amp; Scheduling</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-reports">
                        <span class="btn-icon"><i class="fa-solid fa-file-pdf"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">9. Reports &amp; Incidents</span><span class="lang-en">9. Reports &amp; Incidents</span></strong>
                            <small><span class="lang-tl">PDF / Excel Export</span><span class="lang-en">PDF / Excel Export</span></small>
                        </span>
                    </button>
                    <button class="guide-nav-btn" type="button" data-target="topic-admin">
                        <span class="btn-icon"><i class="fa-solid fa-users-gear"></i></span>
                        <span class="btn-copy">
                            <strong><span class="lang-tl">10. Administration</span><span class="lang-en">10. System Administration</span></strong>
                            <small><span class="lang-tl">Users, Logs &amp; Settings</span><span class="lang-en">Users, Audit &amp; Settings</span></small>
                        </span>
                    </button>
                </div>

                <div class="guide-sidebar__callout">
                    <i class="fa-solid fa-circle-question"></i>
                    <div>
                        <strong>
                            <span class="lang-tl">Kailangan ng tulong?</span>
                            <span class="lang-en">Need assistance?</span>
                        </strong>
                        <p class="lang-tl">Basahin ang aming <a href="{{ route('faqs.index') }}">Frequently Asked Questions (FAQs)</a> o magpadala ng mensahe sa Admin.</p>
                        <p class="lang-en">Read our <a href="{{ route('faqs.index') }}">Frequently Asked Questions (FAQs)</a> or contact your system administrator.</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Guide Content Area --}}
        <main class="guide-content" id="guideContent">

            {{-- Quick Visual Workflow --}}
            <section class="guide-section" id="quick-workflow" data-topic="topic-roles">
                <div class="workflow-card">
                    <div class="workflow-header">
                        <span class="workflow-badge"><i class="fa-solid fa-route"></i> <span class="lang-tl">Pangkalahatang Daloy</span><span class="lang-en">Standard Operating Workflow</span></span>
                        <h2>
                            <span class="lang-tl">Standard Operating Procedure (Buwanang Daloy ng Gawain)</span>
                            <span class="lang-en">Standard Operating Procedure (Monthly Energy Cycle)</span>
                        </h2>
                        <p class="lang-tl">Sundin ang 5 pangunahing yugto ng operasyon sa bawat buwan ng pagsusuri ng kuryente:</p>
                        <p class="lang-en">Follow these 5 core operational stages for each monthly energy monitoring cycle:</p>
                    </div>
                    <div class="workflow-steps-grid">
                        <div class="flow-step">
                            <div class="flow-num">1</div>
                            <div class="flow-icon"><i class="fa-solid fa-file-invoice"></i></div>
                            <h4>
                                <span class="lang-tl">Pagdating ng Bill</span>
                                <span class="lang-en">Bill Arrival</span>
                            </h4>
                            <p class="lang-tl">Tanggapin ang Meralco/Electric bill para sa natapos na buwan.</p>
                            <p class="lang-en">Receive the utility billing invoice for the elapsed period.</p>
                        </div>
                        <div class="flow-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                        <div class="flow-step">
                            <div class="flow-num">2</div>
                            <div class="flow-icon"><i class="fa-solid fa-keyboard"></i></div>
                            <h4>
                                <span class="lang-tl">Pag-encode</span>
                                <span class="lang-en">Data Encoding</span>
                            </h4>
                            <p class="lang-tl">Ipasok ang kWh reading, rate, at i-upload ang kopya ng resibo.</p>
                            <p class="lang-en">Enter kWh dials, rate per kWh, and attach the scanned invoice.</p>
                        </div>
                        <div class="flow-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                        <div class="flow-step">
                            <div class="flow-num">3</div>
                            <div class="flow-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                            <h4>
                                <span class="lang-tl">AI &amp; Alert Review</span>
                                <span class="lang-en">AI &amp; Alerts Review</span>
                            </h4>
                            <p class="lang-tl">Suriin kung nagkaroon ng Spike, Overconsumption, o Anomaly.</p>
                            <p class="lang-en">Evaluate baseline deviations, spike alerts, and AI insights.</p>
                        </div>
                        <div class="flow-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                        <div class="flow-step">
                            <div class="flow-num">4</div>
                            <div class="flow-icon"><i class="fa-solid fa-wrench"></i></div>
                            <h4>
                                <span class="lang-tl">Aksyon &amp; Tip</span>
                                <span class="lang-en">Action &amp; Maintenance</span>
                            </h4>
                            <p class="lang-tl">Magpatupad ng Conservation Checklist o Maintenance schedule.</p>
                            <p class="lang-en">Execute conservation checklists and schedule preventive repairs.</p>
                        </div>
                        <div class="flow-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                        <div class="flow-step">
                            <div class="flow-num">5</div>
                            <div class="flow-icon"><i class="fa-solid fa-file-arrow-down"></i></div>
                            <h4>
                                <span class="lang-tl">Export Reports</span>
                                <span class="lang-en">Export Reports</span>
                            </h4>
                            <p class="lang-tl">I-download ang opisyal na PDF/Excel report para sa pamunuan.</p>
                            <p class="lang-en">Generate official PDF / Excel reports for management and audits.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 1: Roles and Permissions --}}
            <section class="guide-section" id="topic-roles" data-topic="topic-roles">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-id-badge"></i> <span class="lang-tl">Modyul 1</span><span class="lang-en">Module 1</span></span>
                        <h2>
                            <span class="lang-tl">Mga Antas ng Gumagamit (User Roles at Access)</span>
                            <span class="lang-en">User Roles &amp; Permissions Matrix</span>
                        </h2>
                        <p class="lang-tl">Ang bawat account ay may partikular na tungkulin at limitasyon sa system upang mapanatili ang seguridad at kaayusan ng datos:</p>
                        <p class="lang-en">Each user account possesses specific privileges and operational scopes to maintain data integrity and governance:</p>
                    </div>

                    <div class="roles-table-wrap">
                        <table class="guide-table">
                            <thead>
                                <tr>
                                    <th><span class="lang-tl">User Role</span><span class="lang-en">User Role</span></th>
                                    <th><span class="lang-tl">Sino ang Gumagamit?</span><span class="lang-en">Target Users</span></th>
                                    <th><span class="lang-tl">Pangunahing Kakayahan (Permissions)</span><span class="lang-en">Key Permissions</span></th>
                                    <th><span class="lang-tl">Limitasyon</span><span class="lang-en">Restrictions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="role-tag super-admin"><i class="fa-solid fa-shield"></i> Super Admin</span>
                                    </td>
                                    <td><span class="lang-tl">IT Administrator / System Head</span><span class="lang-en">IT Administrator / System Head</span></td>
                                    <td>
                                        <span class="lang-tl">Buong kontrol sa lahat ng pasilidad, Users, Role assignment, System Settings, Integration, at Database Audit logs.</span>
                                        <span class="lang-en">Full access across all facilities, user administration, system configuration, external integrations, and security audit logs.</span>
                                    </td>
                                    <td><span class="lang-tl">Walang limitasyon.</span><span class="lang-en">No restrictions.</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="role-tag admin"><i class="fa-solid fa-user-gear"></i> Admin / Engineer</span>
                                    </td>
                                    <td><span class="lang-tl">City Engineer / Building Official</span><span class="lang-en">City Engineer / Building Official</span></td>
                                    <td>
                                        <span class="lang-tl">Pag-apruba ng Main &amp; Submeters, pamamahala ng pasilidad, pagkumpleto ng maintenance, at pag-export ng Excel/PDF.</span>
                                        <span class="lang-en">Meter approval (Main &amp; Submeters), facility master data management, maintenance completion, and full Excel/PDF report exports.</span>
                                    </td>
                                    <td><span class="lang-tl">Limitadong access sa system-level settings.</span><span class="lang-en">Restricted from core system-level infrastructure settings.</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="role-tag officer"><i class="fa-solid fa-bolt"></i> Energy Officer</span>
                                    </td>
                                    <td><span class="lang-tl">Energy Management Officer / Specialist</span><span class="lang-en">Energy Management Specialist / Officer</span></td>
                                    <td>
                                        <span class="lang-tl">Pag-setup ng Energy Profiles, pag-review ng AI alerts, pag-schedule ng maintenance, at pagsusuri ng konsumo.</span>
                                        <span class="lang-en">Energy Profile creation, baseline tracking, AI alert assessments, maintenance scheduling, and trend analytics.</span>
                                    </td>
                                    <td>
                                        <span class="lang-tl">Hindi maaaring mag-delete ng pasilidad o mag-delete ng profile baseline nang walang approval.</span>
                                        <span class="lang-en">Cannot delete facility master records or permanently purge energy baselines without admin rights.</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="role-tag staff"><i class="fa-solid fa-user-pen"></i> Staff (Facility Staff)</span>
                                    </td>
                                    <td><span class="lang-tl">Assigned Building Caretaker / Encoder</span><span class="lang-en">Assigned Building Encoder / Caretaker</span></td>
                                    <td>
                                        <span class="lang-tl">Pag-encode ng buwanang bill readings para sa <em>nakatalagang pasilidad</em>, pag-view ng dashboard, at PDF reports.</span>
                                        <span class="lang-en">Encodes monthly readings and attaches bills for <em>assigned facilities only</em>, views dashboards, and downloads PDF reports.</span>
                                    </td>
                                    <td>
                                        <span class="lang-tl">Makikita lamang ang sariling pasilidad; bawal mag-export ng raw Excel data.</span>
                                        <span class="lang-en">Restricted to assigned facility scopes; raw Excel export is blocked (PDF only).</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Topic 2: Login and Navigation --}}
            <section class="guide-section" id="topic-login" data-topic="topic-login">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-right-to-bracket"></i> <span class="lang-tl">Modyul 2</span><span class="lang-en">Module 2</span></span>
                        <h2>
                            <span class="lang-tl">Pag-login at Pag-navigate sa Dashboard</span>
                            <span class="lang-en">Sign-in &amp; Dashboard Navigation</span>
                        </h2>
                        <p class="lang-tl">Paano maayos na pumasok sa system at gamitin ang navigation menu:</p>
                        <p class="lang-en">How to securely authenticate and navigate across core workspaces:</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pagpasok sa Sign-in Page</span>
                                    <span class="lang-en">Access the Sign-in Page</span>
                                </h3>
                                <p class="lang-tl">Buksan ang browser at pumunta sa homepage ng inyong system. I-click ang <strong>Sign In</strong> o <strong>Access the System</strong> button sa bandang itaas o gitna ng landing page.</p>
                                <p class="lang-en">Navigate to the homepage in your browser and click <strong>Sign In</strong> or <strong>Access the System</strong> on the top navigation bar or hero banner.</p>
                                <div class="step-callout tip">
                                    <i class="fa-solid fa-lightbulb"></i>
                                    <span class="lang-tl"><strong>Pro-Tip:</strong> I-bookmark ang URL sa iyong browser (Ctrl + D) para mabilis ma-access araw-araw.</span>
                                    <span class="lang-en"><strong>Pro-Tip:</strong> Bookmark the system URL in your browser (Ctrl + D) for quick daily access.</span>
                                </div>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Paglagay ng Kredensyal</span>
                                    <span class="lang-en">Enter Credentials &amp; OTP</span>
                                </h3>
                                <p class="lang-tl">Ilagay ang iyong opisyal na <strong>Email Address</strong> at <strong>Password</strong>. Kung may aktibong One-Time Pin (OTP) verification ang system, ipasok ang 6-digit code na ipinadala sa iyong email.</p>
                                <p class="lang-en">Provide your registered <strong>Email Address</strong> and <strong>Password</strong>. If Two-Factor / One-Time Pin (OTP) is prompted, enter the 6-digit verification code sent to your email.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pagsusuri sa Dashboard</span>
                                    <span class="lang-en">Review the Dashboard Overview</span>
                                </h3>
                                <p class="lang-tl">Pagkapasok, bubukas ang Dashboard kung saan makikita ang:</p>
                                <p class="lang-en">Upon sign-in, the executive dashboard displays the following high-level key metrics:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Total kWh Consumed:</strong> <span class="lang-tl">Kabuuang kuryente na nagamit ngayong kasalukuyang taon.</span><span class="lang-en">Cumulative year-to-date electricity usage across facilities.</span></li>
                                    <li><strong>Total Energy Cost:</strong> <span class="lang-tl">Kabuuang halaga sa piso (₱) ng kuryente.</span><span class="lang-en">Total monetary expenditure in Philippine Pesos (₱).</span></li>
                                    <li><strong>Active Facilities &amp; Meters:</strong> <span class="lang-tl">Bilang ng mga aktibong gusali at kuntador.</span><span class="lang-en">Count of active buildings and monitored meters.</span></li>
                                    <li><strong>Recent Alerts:</strong> <span class="lang-tl">Mga pinakabagong abiso tungkol sa overconsumption o pending submissions.</span><span class="lang-en">Real-time alerts regarding overconsumption anomalies and pending review tasks.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 3: Facilities and Meters --}}
            <section class="guide-section" id="topic-facility" data-topic="topic-facility">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-building"></i> <span class="lang-tl">Modyul 3</span><span class="lang-en">Module 3</span></span>
                        <h2>
                            <span class="lang-tl">Pamamahala ng Pasilidad at Meters (Facility Registry)</span>
                            <span class="lang-en">Facility Registry &amp; Meter Infrastructure</span>
                        </h2>
                        <p class="lang-tl">Ito ang talaan ng lahat ng gusali ng LGU (hal. City Hall, Health Centers, Legislative Building) at ang kanilang mga kuntador.</p>
                        <p class="lang-en">Master registry of all LGU municipal buildings, public health facilities, and their corresponding electric meters.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pumunta sa Facility Registry</span>
                                    <span class="lang-en">Open Facility Registry</span>
                                </h3>
                                <p class="lang-tl">Sa kaliwang Sidebar, i-click ang <strong>Operations</strong> ➡️ <strong>Facility Registry</strong>.</p>
                                <p class="lang-en">From the left sidebar navigation, click <strong>Operations</strong> ➡️ <strong>Facility Registry</strong>.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pagdagdag ng Bagong Pasilidad (Add Facility)</span>
                                    <span class="lang-en">Register a New Facility</span>
                                </h3>
                                <p class="lang-tl">I-click ang <strong><i class="fa-solid fa-plus"></i> Add Facility</strong> button sa kanang itaas at punan ang sumusunod:</p>
                                <p class="lang-en">Click the <strong><i class="fa-solid fa-plus"></i> Add Facility</strong> button on the top right and supply:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Facility Name:</strong> <span class="lang-tl">Opisyal na pangalan ng gusali (hal. <em>City Health Complex</em>).</span><span class="lang-en">Official building name (e.g., <em>City Hall Main Building</em>).</span></li>
                                    <li><strong>Location / Barangay:</strong> <span class="lang-tl">Lokasyon sa lungsod o munisipyo.</span><span class="lang-en">Specific location, street address, or barangay.</span></li>
                                    <li><strong>Floor Area:</strong> <span class="lang-tl">Sukat ng gusali sa square meters ($m^2$).</span><span class="lang-en">Total floor area in square meters ($m^2$).</span></li>
                                    <li><strong>Building Category:</strong> <span class="lang-tl">Tanggapan, Ospital, Paaralan, o Pampublikong Pasilidad.</span><span class="lang-en">Administrative, Healthcare, Educational, or Utility structure.</span></li>
                                </ul>
                                <p class="lang-tl">I-click ang <strong>Save Facility</strong>.</p>
                                <p class="lang-en">Click <strong>Save Facility</strong> to store the record.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pag-setup ng Main Meters at Submeters</span>
                                    <span class="lang-en">Configure Main Meters &amp; Submeters</span>
                                </h3>
                                <p class="lang-tl">Sa loob ng pasilidad, i-click ang <strong>Manage Meters</strong> tab:</p>
                                <p class="lang-en">Inside the facility details view, open the <strong>Manage Meters</strong> tab:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Main Meter:</strong> <span class="lang-tl">Ang pangunahing kuntador mula sa Meralco o Distribution Utility. Ilagay ang <em>Meter Number</em>, <em>Multiplier</em>, at <em>Baseline kWh limit</em>.</span><span class="lang-en">The primary billing meter connected to the distribution utility. Specify <em>Meter Number</em>, <em>Multiplier</em>, and <em>Monthly Baseline kWh</em>.</span></li>
                                    <li><strong>Submeter:</strong> <span class="lang-tl">Kuntador para sa partikular na floor, opisina, o mabigat na kagamitan (hal. <em>2nd Floor Aircon Submeter</em>). I-link ito sa tamang Parent Main Meter.</span><span class="lang-en">Branch meter allocated to specific floors, departments, or heavy equipment. Must be linked to its parent Main Meter.</span></li>
                                </ul>
                                <div class="step-callout important">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span class="lang-tl"><strong>Mahalagang Paalala:</strong> Bago magamit ang isang meter para sa encoding ng billing, kinakailangang i-click ng Admin/Engineer ang <strong>Approve Meter</strong> button upang maging aktibo ito.</span>
                                    <span class="lang-en"><strong>Important Note:</strong> Before any meter can accept monthly reading entries, an Admin / Engineer must click the <strong>Approve Meter</strong> button to activate it.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 4: Energy Profile & Baseline --}}
            <section class="guide-section" id="topic-energy-profile" data-topic="topic-energy-profile">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-sliders"></i> <span class="lang-tl">Modyul 4</span><span class="lang-en">Module 4</span></span>
                        <h2>
                            <span class="lang-tl">Energy Profile at Baseline Establishment</span>
                            <span class="lang-en">Energy Profile &amp; Baseline Establishment</span>
                        </h2>
                        <p class="lang-tl">Ang Energy Profile ang nagtatakda ng utility contract, baseline kWh, at target efficiency ng bawat pasilidad.</p>
                        <p class="lang-en">The Energy Profile establishes utility contracts, reference consumption benchmarks (baselines), and efficiency targets.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Buksan ang Energy Profile ng Pasilidad</span>
                                    <span class="lang-en">Open the Facility Energy Profile</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Facility Registry</strong> ➡️ i-click ang pangalan ng pasilidad ➡️ piliin ang <strong>Energy Profile</strong>.</p>
                                <p class="lang-en">Go to <strong>Facility Registry</strong> ➡️ click the facility name ➡️ select the <strong>Energy Profile</strong> action.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pag-setup ng Utility Provider at Account</span>
                                    <span class="lang-en">Configure Utility Provider &amp; Account Details</span>
                                </h3>
                                <p class="lang-tl">Ipasok ang mga impormasyon mula sa inyong kontrata:</p>
                                <p class="lang-en">Input contract information from your utility service agreement:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Utility Provider:</strong> <span class="lang-tl">(hal. <em>MERALCO</em>, Electric Cooperative, o Renewable IPP).</span><span class="lang-en">(e.g., <em>MERALCO</em>, Distribution Cooperative, or Solar IPP).</span></li>
                                    <li><strong>Account Number:</strong> <span class="lang-tl">10 o 12-digit utility account identifier.</span><span class="lang-en">Official utility service identification number.</span></li>
                                    <li><strong>Primary Meter:</strong> <span class="lang-tl">Piliin ang aprubadong Main Meter na sumusukat sa buong gusali.</span><span class="lang-en">Select the approved Main Meter associated with the primary utility billing.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Ano ang Baseline kWh at Bakit Ito Mahalaga?</span>
                                    <span class="lang-en">Understanding Baseline kWh Benchmarks</span>
                                </h3>
                                <p class="lang-tl">Ang <strong>Baseline kWh</strong> ay ang normal o inaasahang buwanang konsumo ng gusali batay sa kasaysayan o floor area nito. Ginagamit ito ng system para:</p>
                                <p class="lang-en">The <strong>Baseline kWh</strong> serves as the expected monthly consumption benchmark. The system uses it to:</p>
                                <ul class="guide-bullets">
                                    <li>
                                        <span class="lang-tl">Kalkulahin ang <strong>Deviation (%)</strong>:</span>
                                        <span class="lang-en">Compute variance percentage:</span>
                                        <br>
                                        <code>Deviation (%) = ((Actual kWh - Baseline kWh) / Baseline kWh) * 100%</code>
                                    </li>
                                    <li>
                                        <span class="lang-tl">Mag-trigger ng <strong>AI Alerts</strong> kapag lumagpas sa 10% (Warning), 20% (High), o 30%+ (Critical).</span>
                                        <span class="lang-en">Trigger automated <strong>AI Alerts</strong> when usage exceeds +10% (Warning), +20% (High), or +30%+ (Critical).</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 5: Monthly Records and Bill Upload --}}
            <section class="guide-section" id="topic-monthly-records" data-topic="topic-monthly-records">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-file-invoice-dollar"></i> <span class="lang-tl">Modyul 5</span><span class="lang-en">Module 5</span></span>
                        <h2>
                            <span class="lang-tl">Pag-encode ng Buwanang Records at Bill Attachment</span>
                            <span class="lang-en">Monthly Records Encoding &amp; Invoice Attachment</span>
                        </h2>
                        <p class="lang-tl">Ito ang pangunahing gawain tuwing dumarating ang electric bill o natatapos ang buwanang meter reading.</p>
                        <p class="lang-en">The routine operational workflow whenever a utility bill arrives or monthly meter reading occurs.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pumunta sa Monthly Records</span>
                                    <span class="lang-en">Open Monthly Records</span>
                                </h3>
                                <p class="lang-tl">Sa <strong>Facility Registry</strong>, i-click ang <strong>Monthly Records</strong> button sa tabi ng pasilidad na ie-encode.</p>
                                <p class="lang-en">In <strong>Facility Registry</strong>, click the <strong>Monthly Records</strong> button on the target facility row.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">I-click ang "Add Monthly Record"</span>
                                    <span class="lang-en">Submit Reading in "Add Monthly Record" Form</span>
                                </h3>
                                <p class="lang-tl">Punan nang tumpak ang form ayon sa nakasaad sa papel o PDF na electric bill:</p>
                                <p class="lang-en">Fill out the modal fields accurately from your utility invoice:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Billing Date / Period:</strong> <span class="lang-tl">Petsa ng billing (hal. <em>September 15, 2026</em>). Awtomatikong kukunin ang Month at Year.</span><span class="lang-en">Billing statement date. Month and Year are extracted automatically.</span></li>
                                    <li><strong>Target Meter:</strong> <span class="lang-tl">Piliin ang aprubadong Main Meter.</span><span class="lang-en">Select the approved Main Meter.</span></li>
                                    <li><strong>Previous &amp; Current Reading:</strong> <span class="lang-tl">Ilagay ang dials (kWh). Awtomatikong kukuwentahin ang <code>Actual kWh = Current - Previous</code>.</span><span class="lang-en">Enter meter dials. System computes <code>Actual kWh = Current - Previous</code>.</span></li>
                                    <li><strong>Rate per kWh:</strong> <span class="lang-tl">Ilagay ang singil kada kWh (hal. ₱12.00). Awtomatikong kukuwentahin ang <code>Total Cost = Actual kWh * Rate</code>.</span><span class="lang-en">Enter utility effective tariff rate (e.g., ₱12.00). System computes <code>Total Cost = Actual kWh * Rate</code>.</span></li>
                                    <li><strong>Attach Bill Image:</strong> <span class="lang-tl">Mag-upload ng malinaw na larawan (JPG/PNG) o scan ng electric bill para sa COA compliance at audit trail.</span><span class="lang-en">Upload a clear photo or scan of the official utility bill for auditing and transparency.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pag-review at Status Indicators</span>
                                    <span class="lang-en">Review &amp; Status Color Coding</span>
                                </h3>
                                <p class="lang-tl">Matapos i-save, makikita ang tala sa talahanayan na may kulay na indicator:</p>
                                <p class="lang-en">Saved records immediately reflect calculated variance with visual status badges:</p>
                                <div class="badge-showcase">
                                    <span class="status-indicator normal"><i class="fa-solid fa-circle-check"></i> <span class="lang-tl">Normal (Pasok sa Baseline)</span><span class="lang-en">Normal (Within Baseline)</span></span>
                                    <span class="status-indicator warning"><i class="fa-solid fa-triangle-exclamation"></i> <span class="lang-tl">Warning (+10%–15%)</span><span class="lang-en">Warning (+10%–15%)</span></span>
                                    <span class="status-indicator high"><i class="fa-solid fa-fire-flame-curved"></i> <span class="lang-tl">High Overconsumption (+20%–30%)</span><span class="lang-en">High Overconsumption (+20%–30%)</span></span>
                                    <span class="status-indicator critical"><i class="fa-solid fa-skull-crossbones"></i> <span class="lang-tl">Critical (+30%+)</span><span class="lang-en">Critical (+30%+)</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 04</span><span class="lang-en">Step 04</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Buwanan at Lingguhang (Weekly) Breakdown</span>
                                    <span class="lang-en">Monthly &amp; Weekly Main Meter Breakdown</span>
                                </h3>
                                <p class="lang-tl">Maaari mong ilipat ang view mode sa pagitan ng <strong>Monthly Records</strong> at <strong>Weekly Breakdown (Main Meter)</strong>:</p>
                                <p class="lang-en">You can toggle the record view mode between <strong>Monthly Records</strong> and <strong>Weekly Breakdown (Main Meter)</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Week 1:</strong> <span class="lang-tl">Araw 1 hanggang 7 ng buwan (7 araw).</span><span class="lang-en">Days 1 to 7 of the billing month (7 days).</span></li>
                                    <li><strong>Week 2:</strong> <span class="lang-tl">Araw 8 hanggang 14 ng buwan (7 araw).</span><span class="lang-en">Days 8 to 14 of the billing month (7 days).</span></li>
                                    <li><strong>Week 3:</strong> <span class="lang-tl">Araw 15 hanggang 21 ng buwan (7 araw).</span><span class="lang-en">Days 15 to 21 of the billing month (7 days).</span></li>
                                    <li><strong>Week 4:</strong> <span class="lang-tl">Araw 22 hanggang katapusan ng buwan (natitirang mga araw).</span><span class="lang-en">Days 22 to the end of month (remaining days).</span></li>
                                    <li><span class="lang-tl">Awtomatikong ipinapakita ang proportioned weekly consumption, proportioned baseline, at tinatayang weekly energy cost.</span><span class="lang-en">Automatically displays proportioned weekly consumption, weekly baseline, and estimated weekly energy cost.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 6: Monitoring & AI Alerts --}}
            <section class="guide-section" id="topic-monitoring" data-topic="topic-monitoring">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-chart-line"></i> <span class="lang-tl">Modyul 6</span><span class="lang-en">Module 6</span></span>
                        <h2>
                            <span class="lang-tl">Energy Monitoring, Analytics, at AI Alerts</span>
                            <span class="lang-en">Energy Monitoring, Analytics, &amp; AI Alerts</span>
                        </h2>
                        <p class="lang-tl">Paggamit ng visual dashboards at artificial intelligence para masubaybayan ang trend ng kuryente at maagapan ang labis na gastos.</p>
                        <p class="lang-en">Leveraging visual trend dashboards and intelligent anomaly detection to preempt overspending.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Main Meter Monitoring (Monthly vs. Weekly) &amp; AI Recommendations</span>
                                    <span class="lang-en">Main Meter Monitoring (Monthly vs. Weekly) &amp; AI Recommendations</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Energy Management</strong> ➡️ <strong>Main Meter Monitoring</strong>:</p>
                                <p class="lang-en">Navigate to <strong>Energy Management</strong> ➡️ <strong>Main Meter Monitoring</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><span class="lang-tl">Pumili ng <strong>Billing Month</strong>, <strong>Timeframe</strong> (<em>Monthly</em> o <em>Weekly</em>), at <strong>Week Period</strong> (<em>All Weeks</em> o <em>Week 1–4</em>).</span><span class="lang-en">Select <strong>Billing Month</strong>, <strong>Timeframe</strong> (<em>Monthly</em> or <em>Weekly</em>), and <strong>Week Period</strong> (<em>All Weeks</em> or <em>Week 1–4</em>).</span></li>
                                    <li><span class="lang-tl">Tingnan ang <strong>Main Meter Consumption</strong>, <strong>Estimated Energy Cost</strong>, at <strong>Baseline Variance</strong> para sa napiling linggo o buwan.</span><span class="lang-en">Inspect <strong>Main Meter Consumption</strong>, <strong>Estimated Energy Cost</strong>, and <strong>Baseline Variance</strong> for the selected week or month.</span></li>
                                    <li><span class="lang-tl">I-click ang <strong><i class="fa-solid fa-wand-magic-sparkles"></i> AI Recommendation / Open AI Alert</strong> button para makakuha ng agarang pagsusuri mula sa AI batay sa pattern ng konsumo.</span><span class="lang-en">Click the <strong><i class="fa-solid fa-wand-magic-sparkles"></i> AI Recommendation / Open AI Alert</strong> button to obtain tailored energy efficiency guidance.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">AI Alerts &amp; Anomaly Detection</span>
                                    <span class="lang-en">AI Alerts &amp; Anomaly Detection</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Energy Management</strong> ➡️ <strong>AI Alerts</strong>. Awtomatikong pinag-aaralan ng algorithm ang lahat ng records:</p>
                                <p class="lang-en">Go to <strong>Energy Management</strong> ➡️ <strong>AI Alerts</strong>. Automated algorithms continuously evaluate all active meters:</p>
                                <ul class="guide-bullets">
                                    <li><strong>Consumption Spikes:</strong> <span class="lang-tl">Biglaang pagtaas ng konsumo kumpara sa baseline o nakaraang buwan.</span><span class="lang-en">Sudden abnormal increases in energy draw.</span></li>
                                    <li><strong>Sudden Drops:</strong> <span class="lang-tl">Hindi pangkaraniwang pagbagsak ng kuryente na posibleng senyales ng sirang metro o brownout.</span><span class="lang-en">Unusual consumption drops indicating possible meter faults or outages.</span></li>
                                    <li><strong>Actionable Insights:</strong> <span class="lang-tl">Rekomendasyon kung aling kagamitan o schedule ang dapat siyasatin.</span><span class="lang-en">Prescriptive diagnostic steps for building engineers.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Submeter Monitoring (IoT Sensors / Manual)</span>
                                    <span class="lang-en">Submeter Branch Monitoring</span>
                                </h3>
                                <p class="lang-tl">Kung may mga nakakabit na submeters (hal. PZEM IoT sensors o sub-panels), buksan ang <strong>Submeter Monitoring</strong> upang makita ang hatak ng bawat departamento o palapag.</p>
                                <p class="lang-en">When submeters are deployed (e.g., PZEM IoT sensors or sub-panels), open <strong>Submeter Monitoring</strong> to evaluate departmental and floor-level loads.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 7: Load Tracking & Cash Flow Budget --}}
            <section class="guide-section" id="topic-load-budget" data-topic="topic-load-budget">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-plug-circle-bolt"></i> <span class="lang-tl">Modyul 7</span><span class="lang-en">Module 7</span></span>
                        <h2>
                            <span class="lang-tl">Equipment Load Tracking at Cash Flow &amp; Budget</span>
                            <span class="lang-en">Equipment Load Tracking &amp; Cash Flow Budget</span>
                        </h2>
                        <p class="lang-tl">Alamin kung aling appliances ang may pinakamalaking hatak sa kuryente at subaybayan ang paggastos laban sa nakalaang pondo.</p>
                        <p class="lang-en">Audit major building equipment power draw and align monthly expenditures with municipal utility budget caps.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Load Tracking (Equipment Inventory)</span>
                                    <span class="lang-en">Load Tracking &amp; Power Inventory</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Energy Management</strong> ➡️ <strong>Load Tracking</strong>:</p>
                                <p class="lang-en">Navigate to <strong>Energy Management</strong> ➡️ <strong>Load Tracking</strong>:</p>
                                <ol class="guide-numbers">
                                    <li><span class="lang-tl">Piliin ang Pasilidad at i-click ang <strong>Add Equipment</strong>.</span><span class="lang-en">Select facility and click <strong>Add Equipment</strong>.</span></li>
                                    <li><span class="lang-tl">Ilagay ang pangalan ng gamit (hal. <em>Split-type Inverter AC 2.5HP</em>, <em>Water Booster Pump</em>).</span><span class="lang-en">Specify equipment name (e.g., <em>Inverter Air Conditioner 2.5HP</em>, <em>Water Booster Pump</em>).</span></li>
                                    <li><span class="lang-tl">Ipasok ang <strong>Rated Watts</strong> (hal. 1,800 Watts), <strong>Quantity</strong>, <strong>Hours/Day</strong>, at <strong>Days/Month</strong>.</span><span class="lang-en">Input <strong>Rated Watts</strong>, <strong>Quantity</strong>, <strong>Operating Hours/Day</strong>, and <strong>Days/Month</strong>.</span></li>
                                    <li>
                                        <span class="lang-tl">Awtomatikong kukuwentahin ng system ang tinatayang buwanang kWh at piso:</span>
                                        <span class="lang-en">The system automatically calculates monthly kWh and estimated monetary cost:</span>
                                        <br>
                                        <code>kWh = (Rated Watts * Quantity * Hours/Day * Days/Month) / 1000</code>
                                    </li>
                                </ol>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Cash Flow &amp; Budget Tracking</span>
                                    <span class="lang-en">Cash Flow &amp; Budget Allocation</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Energy Management</strong> ➡️ <strong>Cash Flow &amp; Budget</strong>:</p>
                                <p class="lang-en">Open <strong>Energy Management</strong> ➡️ <strong>Cash Flow &amp; Budget</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><span class="lang-tl">I-set ang taunang <strong>Allocated Electricity Budget</strong> para sa bawat pasilidad.</span><span class="lang-en">Define annual municipal utility appropriations per facility.</span></li>
                                    <li><span class="lang-tl">Subaybayan ang <strong>Spent to Date vs. Remaining Budget</strong> upang maiwasan ang budget deficit bago matapos ang fiscal year.</span><span class="lang-en">Monitor actual disbursements against remaining fiscal balances to prevent budget deficits.</span></li>
                                    <li><span class="lang-tl">I-export ang financial breakdown para sa City Budget Office o COA reports.</span><span class="lang-en">Export financial records directly for the City Budget Office.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 8: Conservation Program & Maintenance --}}
            <section class="guide-section" id="topic-conservation" data-topic="topic-conservation">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-leaf"></i> <span class="lang-tl">Modyul 8</span><span class="lang-en">Module 8</span></span>
                        <h2>
                            <span class="lang-tl">Energy Conservation Program at Maintenance Management</span>
                            <span class="lang-en">Conservation Programs &amp; Preventive Maintenance</span>
                        </h2>
                        <p class="lang-tl">Pamahalaan ang pang-araw-araw na gawi sa pagtitipid ng kuryente at ang preventive maintenance ng mga pasilidad.</p>
                        <p class="lang-en">Manage institutional energy conservation routines and preventive equipment servicing schedules.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Daily Conservation Checklist</span>
                                    <span class="lang-en">Daily Conservation Checklist</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Energy Management</strong> ➡️ <strong>Conservation Program</strong> ➡️ <strong>Daily Checklist</strong>:</p>
                                <p class="lang-en">Go to <strong>Energy Management</strong> ➡️ <strong>Conservation Program</strong> ➡️ <strong>Daily Checklist</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><span class="lang-tl">I-check ang mga nakatalagang gawain bawat araw (hal. <em>I-off ang Aircon tuwing 4:30 PM</em>, <em>Patayin ang ilaw sa tanghalian</em>).</span><span class="lang-en">Verify routine tasks daily (e.g., <em>AC shutdown by 4:30 PM</em>, <em>Lighting power-off during lunch breaks</em>).</span></li>
                                    <li><span class="lang-tl">Magdagdag ng sariling checklist tasks na angkop sa inyong gusali.</span><span class="lang-en">Add custom facility-specific checklist tasks.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Energy Saving Goals at Tips</span>
                                    <span class="lang-en">Efficiency Goals &amp; Actionable Tips</span>
                                </h3>
                                <p class="lang-tl">Magtakda ng target na pagtitipid (hal. <em>5% Reduction ngayong Quarter</em>) at subaybayan ang progress ng mga ipinatutupad na energy recommendations.</p>
                                <p class="lang-en">Set institutional targets (e.g., <em>5% Quarter-over-Quarter reduction</em>) and track progress of recommended conservation actions.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Maintenance Scheduling</span>
                                    <span class="lang-en">Maintenance Scheduling &amp; History</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Operations</strong> ➡️ <strong>Maintenance</strong>:</p>
                                <p class="lang-en">Navigate to <strong>Operations</strong> ➡️ <strong>Maintenance</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><span class="lang-tl">I-click ang <strong>Schedule Maintenance</strong> para sa preventive checks (Aircon cleaning, transformer check, wiring inspection).</span><span class="lang-en">Click <strong>Schedule Maintenance</strong> to record scheduled servicing (HVAC filter cleaning, transformer tests, electrical safety audits).</span></li>
                                    <li><span class="lang-tl">I-update ang status bilang <em>In Progress</em> o <em>Completed</em> upang mai-record sa Maintenance History.</span><span class="lang-en">Update work orders to <em>In Progress</em> or <em>Completed</em> to archive in Maintenance History.</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 9: Reports and Incidents --}}
            <section class="guide-section" id="topic-reports" data-topic="topic-reports">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-file-pdf"></i> <span class="lang-tl">Modyul 9</span><span class="lang-en">Module 9</span></span>
                        <h2>
                            <span class="lang-tl">Pag-generate at Pag-export ng Reports &amp; Incidents</span>
                            <span class="lang-en">Reports Generation, Exports, &amp; Incident Records</span>
                        </h2>
                        <p class="lang-tl">Ihanda ang mga opisyal na dokumento at ulat para sa Mayor, City Council, General Services Office (GSO), o COA.</p>
                        <p class="lang-en">Prepare official documentation and analytics for the Mayor, City Council, GSO, or Commission on Audit (COA).</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Energy Report at Performance Summary</span>
                                    <span class="lang-en">Energy Report &amp; Performance Summary</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Analytics</strong> ➡️ <strong>Reports</strong> ➡️ <strong>Energy Report</strong>:</p>
                                <p class="lang-en">Open <strong>Analytics</strong> ➡️ <strong>Reports</strong> ➡️ <strong>Energy Report</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><span class="lang-tl">Piliin ang Pasilidad, Taon, at Buwan na nais gawan ng ulat.</span><span class="lang-en">Filter by target building, fiscal year, and month.</span></li>
                                    <li><span class="lang-tl">Makikita ang buod ng Actual kWh, Baseline kWh, Variance, at Konsumo.</span><span class="lang-en">Review comparative summary cards of Actual kWh, Baseline, and Variance.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pagpili ng Tamang Export Format</span>
                                    <span class="lang-en">Select Appropriate Export Format</span>
                                </h3>
                                <div class="export-options-grid">
                                    <div class="export-card pdf">
                                        <div class="export-icon"><i class="fa-solid fa-file-pdf"></i></div>
                                        <h4>
                                            <span class="lang-tl">Export to PDF</span>
                                            <span class="lang-en">Export to PDF</span>
                                        </h4>
                                        <p class="lang-tl">Para sa pormal na printed report na may opisyal na header at pirmahan. Magagamit ng lahat ng roles kasama ang Staff.</p>
                                        <p class="lang-en">Formatted for formal presentation and official signing. Accessible to all user roles including Staff.</p>
                                    </div>
                                    <div class="export-card excel">
                                        <div class="export-icon"><i class="fa-solid fa-file-excel"></i></div>
                                        <h4>
                                            <span class="lang-tl">Export to Excel / CSV</span>
                                            <span class="lang-en">Export to Excel / CSV</span>
                                        </h4>
                                        <p class="lang-tl">Para sa detalyadong data processing, pivot tables, at accounting reconciliation (Para sa Admin at Energy Officer lamang).</p>
                                        <p class="lang-en">Raw data structure suitable for pivot tables, audits, and spreadsheet processing (Admin &amp; Energy Officer only).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Energy Incidents Logging</span>
                                    <span class="lang-en">Energy Incident Logging &amp; Investigation</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Reports</strong> ➡️ <strong>Incidents</strong> upang magtala o mag-download ng ulat sa mga hindi pangkaraniwang kaganapan tulad ng power outages, equipment failure, o matinding spike sa kuryente.</p>
                                <p class="lang-en">Navigate to <strong>Reports</strong> ➡️ <strong>Incidents</strong> to log and download reports for exceptional operational events (blackouts, equipment breakdowns, critical load spikes).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Topic 10: Administration --}}
            <section class="guide-section" id="topic-admin" data-topic="topic-admin">
                <div class="section-card">
                    <div class="section-card__header">
                        <span class="topic-pill"><i class="fa-solid fa-users-gear"></i> <span class="lang-tl">Modyul 10</span><span class="lang-en">Module 10</span></span>
                        <h2>
                            <span class="lang-tl">Pangangasiwa ng System (Para sa Super Admin &amp; Admin)</span>
                            <span class="lang-en">System Administration (Super Admin &amp; Admin)</span>
                        </h2>
                        <p class="lang-tl">Mga hakbang para sa pamamahala ng mga gumagamit, seguridad, at mga kaukulang kumpigurasyon.</p>
                        <p class="lang-en">Administrative management workflows for user authorization, security audits, and system configuration.</p>
                    </div>

                    <div class="steps-container">
                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 01</span><span class="lang-en">Step 01</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Pamamahala ng Users at Roles (Users Module)</span>
                                    <span class="lang-en">User Management &amp; Scoped Assignments</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Administration</strong> ➡️ <strong>Users</strong>:</p>
                                <p class="lang-en">Open <strong>Administration</strong> ➡️ <strong>Users</strong>:</p>
                                <ul class="guide-bullets">
                                    <li><strong><span class="lang-tl">Magdagdag ng User:</span><span class="lang-en">Create User:</span></strong> <span class="lang-tl">I-click ang <em>Add User</em>, ilagay ang pangalan, email, at piliin ang angkop na Role.</span><span class="lang-en">Click <em>Add User</em>, input official credentials, and assign appropriate system role.</span></li>
                                    <li><strong><span class="lang-tl">Facility Assignment:</span><span class="lang-en">Facility Scoping:</span></strong> <span class="lang-tl">Kung ang user ay may role na <em>Staff</em>, piliin kung aling pasilidad lamang ang kanyang mae-encode at mabubuksan.</span><span class="lang-en">For <em>Staff</em> accounts, select the designated buildings they are authorized to manage.</span></li>
                                    <li><strong><span class="lang-tl">Status Toggle:</span><span class="lang-en">Account Status:</span></strong> <span class="lang-tl">Maaaring i-deactivate ang account ng mga tauhang lumipat na ng departamento.</span><span class="lang-en">Toggle status to inactive for departed personnel.</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 02</span><span class="lang-en">Step 02</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Audit Logs (Transparency &amp; Accountability)</span>
                                    <span class="lang-en">Audit Logs &amp; Traceability</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Administration</strong> ➡️ <strong>Audit Logs</strong> upang makita ang real-time record ng lahat ng ginagawang pagbabago sa system (sino ang nag-delete, nag-edit, nag-approve, at petsa/oras ng aksyon).</p>
                                <p class="lang-en">Navigate to <strong>Administration</strong> ➡️ <strong>Audit Logs</strong> to inspect timestamped records of all database mutations, approvals, and security events.</p>
                            </div>
                        </div>

                        <div class="guide-step-card">
                            <div class="step-badge"><span class="lang-tl">Hakbang 03</span><span class="lang-en">Step 03</span></div>
                            <div class="step-body">
                                <h3>
                                    <span class="lang-tl">Integrations at System Settings</span>
                                    <span class="lang-en">Integrations &amp; System Settings</span>
                                </h3>
                                <p class="lang-tl">Pumunta sa <strong>Settings / Integrations</strong> upang mai-link ang system sa panlabas na mga plataporma tulad ng CPRF, UMAN, o IoT sensor brokers.</p>
                                <p class="lang-en">Open <strong>Settings / Integrations</strong> to synchronize data pipelines with external platforms (CPRF, UMAN, IoT sensor brokers).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<style>
/* ===== GUIDE PAGE STYLES & DUAL LANGUAGE SWITCHING ===== */
:root {
    --guide-primary: #1d4ed8;
    --guide-primary-dark: #1e40af;
    --guide-primary-light: #eff6ff;
    --guide-ink: #0f172a;
    --guide-body: #334155;
    --guide-muted: #64748b;
    --guide-border: #e2e8f0;
    --guide-card-bg: #ffffff;
    --guide-radius: 16px;
}

/* Language Visibility Rules */
.guide-page[data-guide-lang="tl"] .lang-en {
    display: none !important;
}

.guide-page[data-guide-lang="en"] .lang-tl {
    display: none !important;
}

.guide-page {
    max-width: 1240px;
    margin: 0 auto;
    padding: 10px 0 60px;
    color: var(--guide-body);
}

/* Hero */
.guide-hero {
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    gap: 32px;
    align-items: center;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #1e3a8a 100%);
    border-radius: 20px;
    padding: 38px 42px;
    color: #ffffff;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.22);
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}

.guide-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%);
    pointer-events: none;
}

.guide-hero__top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 16px;
}

.guide-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.12);
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #93c5fd;
    backdrop-filter: blur(8px);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #60a5fa;
    border-radius: 50%;
    box-shadow: 0 0 10px #60a5fa;
    animation: guidePulse 2s infinite;
}

@keyframes guidePulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.3); }
}

/* Language Switcher Component */
.guide-lang-switcher {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    padding: 4px 6px 4px 14px;
    backdrop-filter: blur(10px);
}

.lang-switcher-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.lang-btn-group {
    display: inline-flex;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
    padding: 2px;
}

.lang-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    border: none;
    border-radius: 999px;
    background: transparent;
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lang-toggle-btn.active {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.5);
}

.lang-toggle-btn:hover:not(.active) {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.12);
}

.flag-icon {
    font-size: 0.95rem;
}

.guide-hero h1 {
    font-size: 2.1rem;
    font-weight: 700;
    line-height: 1.25;
    color: #ffffff;
    margin-bottom: 12px;
}

.guide-hero h1 em {
    font-style: normal;
    color: #60a5fa;
    background: linear-gradient(90deg, #93c5fd, #60a5fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.guide-hero p {
    font-size: 0.96rem;
    line-height: 1.6;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.guide-hero__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 0.84rem;
    color: #94a3b8;
    margin-bottom: 24px;
}

.guide-hero__meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.guide-hero__meta i {
    color: #60a5fa;
}

.guide-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.btn-guide-print, .btn-guide-workflow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-guide-print {
    background: #2563eb;
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
}

.btn-guide-print:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.5);
}

.btn-guide-workflow {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
}

.btn-guide-workflow:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}

/* Visual Card */
.guide-hero__visual {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.guide-visual-card {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(14px);
    padding: 28px 24px;
    border-radius: 18px;
    text-align: center;
    max-width: 320px;
    width: 100%;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    z-index: 2;
}

.visual-icon-box {
    width: 58px;
    height: 58px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #ffffff;
    margin: 0 auto 14px;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}

.guide-visual-card strong {
    display: block;
    font-size: 1.15rem;
    color: #ffffff;
    margin-bottom: 4px;
}

.guide-visual-card span {
    display: block;
    font-size: 0.82rem;
    color: #94a3b8;
    margin-bottom: 16px;
}

.visual-badge-list {
    display: flex;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}

.role-badge {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 600;
}

.badge-admin { background: #fee2e2; color: #991b1b; }
.badge-officer { background: #e0e7ff; color: #3730a3; }
.badge-staff { background: #dcfce7; color: #166534; }

/* Search Panel */
.guide-search-panel {
    background: var(--guide-card-bg);
    border: 1px solid var(--guide-border);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}

.guide-search-wrap {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
}

.guide-search-wrap .search-icon {
    position: absolute;
    left: 14px;
    color: var(--guide-muted);
    font-size: 1rem;
}

.guide-search-wrap input {
    width: 100%;
    padding: 12px 70px 12px 42px;
    border: 1px solid var(--guide-border);
    border-radius: 12px;
    font-size: 0.92rem;
    background: #f8fafc;
    color: var(--guide-ink);
    transition: all 0.2s ease;
}

.guide-search-wrap input:focus {
    outline: none;
    border-color: #2563eb;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.search-kbd {
    position: absolute;
    right: 14px;
    background: #e2e8f0;
    color: #475569;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.76rem;
    font-weight: 600;
    font-family: inherit;
}

.guide-search__clear {
    position: absolute;
    right: 44px;
    background: none;
    border: none;
    color: var(--guide-muted);
    cursor: pointer;
    font-size: 0.9rem;
    padding: 4px;
}

.guide-result-summary {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: var(--guide-muted);
    white-space: nowrap;
}

.result-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
}

/* Layout Grid */
.guide-layout {
    display: grid;
    grid-template-columns: 290px 1fr;
    gap: 28px;
    align-items: start;
}

/* Sticky Sidebar */
.guide-sidebar {
    position: sticky;
    top: 90px;
}

.sidebar-sticky-inner {
    background: var(--guide-card-bg);
    border: 1px solid var(--guide-border);
    border-radius: var(--guide-radius);
    padding: 20px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}

.guide-sidebar__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--guide-ink);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--guide-border);
}

.guide-nav-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 20px;
}

.guide-nav-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: var(--guide-body);
    cursor: pointer;
    text-align: left;
    width: 100%;
    transition: all 0.18s ease;
}

.guide-nav-btn:hover {
    background: #f1f5f9;
    color: var(--guide-ink);
}

.guide-nav-btn.active {
    background: var(--guide-primary-light);
    color: var(--guide-primary);
    font-weight: 600;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid var(--guide-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: var(--guide-muted);
    flex-shrink: 0;
    transition: all 0.18s ease;
}

.guide-nav-btn.active .btn-icon {
    background: var(--guide-primary);
    color: #ffffff;
    border-color: var(--guide-primary);
}

.btn-copy strong {
    display: block;
    font-size: 0.84rem;
    line-height: 1.3;
}

.btn-copy small {
    display: block;
    font-size: 0.72rem;
    color: var(--guide-muted);
}

.guide-sidebar__callout {
    background: #f8fafc;
    border: 1px solid var(--guide-border);
    border-radius: 12px;
    padding: 14px;
    display: flex;
    gap: 10px;
    font-size: 0.78rem;
    color: var(--guide-body);
}

.guide-sidebar__callout i {
    color: #2563eb;
    font-size: 1.1rem;
    margin-top: 2px;
}

.guide-sidebar__callout strong {
    display: block;
    color: var(--guide-ink);
    margin-bottom: 2px;
}

.guide-sidebar__callout a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: underline;
}

/* Content Sections */
.guide-content {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.guide-section {
    scroll-margin-top: 96px;
}

.section-card {
    background: var(--guide-card-bg);
    border: 1px solid var(--guide-border);
    border-radius: var(--guide-radius);
    padding: 32px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
}

.section-card__header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--guide-border);
}

.topic-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
    margin-bottom: 8px;
}

.section-card__header h2 {
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--guide-ink);
    margin-bottom: 6px;
}

.section-card__header p {
    font-size: 0.92rem;
    color: var(--guide-muted);
}

/* Workflow Card */
.workflow-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #bfdbfe;
    border-radius: var(--guide-radius);
    padding: 30px;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
}

.workflow-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 999px;
    margin-bottom: 8px;
}

.workflow-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--guide-ink);
    margin-bottom: 4px;
}

.workflow-header p {
    font-size: 0.9rem;
    color: var(--guide-muted);
    margin-bottom: 24px;
}

.workflow-steps-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 8px;
}

.flow-step {
    background: #ffffff;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    padding: 18px 14px;
    text-align: center;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.flow-step:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.12);
}

.flow-num {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 22px;
    height: 22px;
    background: #2563eb;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.flow-icon {
    font-size: 1.4rem;
    color: #2563eb;
    margin: 8px 0 6px;
}

.flow-step h4 {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--guide-ink);
    margin-bottom: 4px;
}

.flow-step p {
    font-size: 0.72rem;
    line-height: 1.35;
    color: var(--guide-muted);
}

.flow-arrow {
    color: #93c5fd;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Steps Container */
.steps-container {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.guide-step-card {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 20px;
    background: #f8fafc;
    border: 1px solid var(--guide-border);
    border-radius: 14px;
    padding: 20px;
    transition: border-color 0.2s ease;
}

.guide-step-card:hover {
    border-color: #cbd5e1;
}

.step-badge {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 700;
    padding: 8px 12px;
    border-radius: 10px;
    text-align: center;
    height: fit-content;
    letter-spacing: 0.4px;
}

.step-body h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--guide-ink);
    margin-bottom: 8px;
}

.step-body p {
    font-size: 0.9rem;
    line-height: 1.55;
    color: var(--guide-body);
    margin-bottom: 12px;
}

.guide-bullets, .guide-numbers {
    padding-left: 20px;
    margin-bottom: 12px;
    font-size: 0.88rem;
    line-height: 1.55;
}

.guide-bullets li, .guide-numbers li {
    margin-bottom: 6px;
}

/* Callouts */
.step-callout {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.84rem;
    line-height: 1.45;
    margin-top: 10px;
}

.step-callout.tip {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    color: #1e40af;
}

.step-callout.tip i { color: #3b82f6; font-size: 1.05rem; }

.step-callout.important {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    color: #92400e;
}

.step-callout.important i { color: #f59e0b; font-size: 1.05rem; }

/* Table */
.roles-table-wrap {
    overflow-x: auto;
}

.guide-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

.guide-table th, .guide-table td {
    padding: 12px 16px;
    border: 1px solid var(--guide-border);
    text-align: left;
    vertical-align: top;
}

.guide-table th {
    background: #f8fafc;
    color: var(--guide-ink);
    font-weight: 700;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.guide-table tbody tr:nth-child(even) {
    background: #fcfdfd;
}

.role-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
}

.role-tag.super-admin { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }
.role-tag.admin { background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; }
.role-tag.officer { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
.role-tag.staff { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }

/* Status Indicators */
.badge-showcase {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-indicator.normal { background: #dcfce7; color: #15803d; }
.status-indicator.warning { background: #fef3c7; color: #b45309; }
.status-indicator.high { background: #ffedd5; color: #c2410c; }
.status-indicator.critical { background: #fee2e2; color: #b91c1c; }

/* Export Cards */
.export-options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 8px;
}

.export-card {
    background: #ffffff;
    border: 1px solid var(--guide-border);
    border-radius: 12px;
    padding: 16px;
}

.export-card.pdf { border-color: #fca5a5; background: #fffaf9; }
.export-card.excel { border-color: #86efac; background: #f9fdfa; }

.export-icon {
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.export-card.pdf .export-icon { color: #dc2626; }
.export-card.excel .export-icon { color: #16a34a; }

.export-card h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--guide-ink);
    margin-bottom: 4px;
}

.export-card p {
    font-size: 0.82rem;
    color: var(--guide-muted);
    line-height: 1.4;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .guide-hero {
        grid-template-columns: 1fr;
        padding: 32px 24px;
    }
    .guide-hero__visual { display: none; }
    .guide-layout {
        grid-template-columns: 1fr;
    }
    .guide-sidebar {
        position: static;
    }
    .workflow-steps-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .flow-arrow {
        transform: rotate(90deg);
        padding: 4px 0;
    }
}

@media (max-width: 640px) {
    .guide-step-card {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .export-options-grid {
        grid-template-columns: 1fr;
    }
    .guide-search-panel {
        flex-direction: column;
        align-items: stretch;
    }
    .guide-hero__top-bar {
        flex-direction: column;
        align-items: flex-start;
    }
}

/* ===== DARK MODE OVERRIDES ===== */
:is(html.dark-mode, body.dark-mode) .guide-page {
    --guide-primary: #3b82f6;
    --guide-primary-dark: #60a5fa;
    --guide-primary-light: #172554;
    --guide-ink: #f8fafc;
    --guide-body: #cbd5e1;
    --guide-muted: #94a3b8;
    --guide-border: #1e293b;
    --guide-card-bg: #0f172a;
    color: #cbd5e1;
}

:is(html.dark-mode, body.dark-mode) .guide-hero {
    background: linear-gradient(135deg, #0b1329 0%, #0f172a 60%, #172554 100%) !important;
    border: 1px solid #1e293b;
    box-shadow: 0 16px 36px rgba(2, 6, 23, 0.55);
}

:is(html.dark-mode, body.dark-mode) .guide-visual-card {
    background: rgba(15, 23, 42, 0.75) !important;
    border-color: #334155 !important;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
}

:is(html.dark-mode, body.dark-mode) .role-badge.badge-admin {
    background: #4c0519 !important;
    color: #fda4af !important;
    border: 1px solid #9f1239 !important;
}
:is(html.dark-mode, body.dark-mode) .role-badge.badge-officer {
    background: #1e1b4b !important;
    color: #c7d2fe !important;
    border: 1px solid #4338ca !important;
}
:is(html.dark-mode, body.dark-mode) .role-badge.badge-staff {
    background: #052e16 !important;
    color: #86efac !important;
    border: 1px solid #166534 !important;
}

:is(html.dark-mode, body.dark-mode) .guide-search-panel {
    background: #0f172a !important;
    border: 1px solid #1e293b !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
}

:is(html.dark-mode, body.dark-mode) .guide-search-wrap input {
    background: #111827 !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .guide-search-wrap input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
}

:is(html.dark-mode, body.dark-mode) .search-kbd {
    background: #1e293b !important;
    color: #94a3b8 !important;
    border: 1px solid #334155;
}

:is(html.dark-mode, body.dark-mode) .sidebar-sticky-inner {
    background: #0f172a !important;
    border: 1px solid #1e293b !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
}

:is(html.dark-mode, body.dark-mode) .guide-sidebar__title {
    color: #f8fafc !important;
    border-bottom-color: #1e293b !important;
}

:is(html.dark-mode, body.dark-mode) .guide-nav-btn {
    color: #94a3b8 !important;
}
:is(html.dark-mode, body.dark-mode) .guide-nav-btn:hover {
    background: #111827 !important;
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .guide-nav-btn.active {
    background: #172554 !important;
    color: #93c5fd !important;
}
:is(html.dark-mode, body.dark-mode) .btn-icon {
    background: #111827 !important;
    border-color: #334155 !important;
    color: #94a3b8 !important;
}
:is(html.dark-mode, body.dark-mode) .guide-nav-btn.active .btn-icon {
    background: #2563eb !important;
    color: #ffffff !important;
    border-color: #3b82f6 !important;
}

:is(html.dark-mode, body.dark-mode) .guide-sidebar__callout {
    background: #111827 !important;
    border-color: #1e293b !important;
    color: #cbd5e1 !important;
}
:is(html.dark-mode, body.dark-mode) .guide-sidebar__callout strong {
    color: #f8fafc !important;
}

:is(html.dark-mode, body.dark-mode) .section-card {
    background: #0f172a !important;
    border: 1px solid #1e293b !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.35) !important;
}
:is(html.dark-mode, body.dark-mode) .section-card__header {
    border-bottom-color: #1e293b !important;
}
:is(html.dark-mode, body.dark-mode) .section-card__header h2 {
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .section-card__header p {
    color: #94a3b8 !important;
}
:is(html.dark-mode, body.dark-mode) .topic-pill {
    background: #172554 !important;
    color: #93c5fd !important;
    border: 1px solid #1e40af !important;
}

:is(html.dark-mode, body.dark-mode) .workflow-card {
    background: linear-gradient(135deg, #0f172a 0%, #111e38 100%) !important;
    border: 1px solid #1e3a8a !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45) !important;
}
:is(html.dark-mode, body.dark-mode) .workflow-badge {
    background: #172554 !important;
    color: #93c5fd !important;
    border: 1px solid #1e40af !important;
}
:is(html.dark-mode, body.dark-mode) .workflow-header h2 {
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .workflow-header p {
    color: #94a3b8 !important;
}
:is(html.dark-mode, body.dark-mode) .flow-step {
    background: #0b1220 !important;
    border: 1px solid #1e293b !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35) !important;
}
:is(html.dark-mode, body.dark-mode) .flow-step:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.55) !important;
}
:is(html.dark-mode, body.dark-mode) .flow-step h4 {
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .flow-step p {
    color: #94a3b8 !important;
}
:is(html.dark-mode, body.dark-mode) .flow-arrow {
    color: #60a5fa !important;
}

:is(html.dark-mode, body.dark-mode) .guide-step-card {
    background: #111827 !important;
    border: 1px solid #1e293b !important;
}
:is(html.dark-mode, body.dark-mode) .guide-step-card:hover {
    border-color: #334155 !important;
}
:is(html.dark-mode, body.dark-mode) .step-badge {
    background: linear-gradient(135deg, #1e3a8a, #0f172a) !important;
    border: 1px solid #2563eb !important;
    color: #bfdbfe !important;
}
:is(html.dark-mode, body.dark-mode) .step-body h3 {
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .step-body p,
:is(html.dark-mode, body.dark-mode) .guide-bullets li,
:is(html.dark-mode, body.dark-mode) .guide-numbers li {
    color: #cbd5e1 !important;
}

:is(html.dark-mode, body.dark-mode) .step-callout.tip {
    background: #172554 !important;
    border-left-color: #3b82f6 !important;
    color: #bfdbfe !important;
}
:is(html.dark-mode, body.dark-mode) .step-callout.tip i {
    color: #60a5fa !important;
}
:is(html.dark-mode, body.dark-mode) .step-callout.important {
    background: #451a03 !important;
    border-left-color: #f59e0b !important;
    color: #fde68a !important;
}
:is(html.dark-mode, body.dark-mode) .step-callout.important i {
    color: #fbbf24 !important;
}

:is(html.dark-mode, body.dark-mode) .guide-table th {
    background: #111827 !important;
    border-color: #1e293b !important;
    color: #93c5fd !important;
}
:is(html.dark-mode, body.dark-mode) .guide-table td {
    background: #0f172a !important;
    border-color: #1e293b !important;
    color: #cbd5e1 !important;
}
:is(html.dark-mode, body.dark-mode) .guide-table tbody tr:nth-child(even) td {
    background: #111827 !important;
}

:is(html.dark-mode, body.dark-mode) .role-tag.super-admin {
    background: #4c0519 !important;
    color: #fda4af !important;
    border-color: #9f1239 !important;
}
:is(html.dark-mode, body.dark-mode) .role-tag.admin {
    background: #451a03 !important;
    color: #fdba74 !important;
    border-color: #9a3412 !important;
}
:is(html.dark-mode, body.dark-mode) .role-tag.officer {
    background: #1e1b4b !important;
    color: #c7d2fe !important;
    border-color: #4338ca !important;
}
:is(html.dark-mode, body.dark-mode) .role-tag.staff {
    background: #052e16 !important;
    color: #86efac !important;
    border-color: #166534 !important;
}

:is(html.dark-mode, body.dark-mode) .status-indicator.normal {
    background: #052e16 !important;
    color: #86efac !important;
    border: 1px solid #166534 !important;
}
:is(html.dark-mode, body.dark-mode) .status-indicator.warning {
    background: #451a03 !important;
    color: #fde68a !important;
    border: 1px solid #9a3412 !important;
}
:is(html.dark-mode, body.dark-mode) .status-indicator.high {
    background: #431407 !important;
    color: #fdba74 !important;
    border: 1px solid #c2410c !important;
}
:is(html.dark-mode, body.dark-mode) .status-indicator.critical {
    background: #4c0519 !important;
    color: #fda4af !important;
    border: 1px solid #9f1239 !important;
}

:is(html.dark-mode, body.dark-mode) .export-card {
    background: #111827 !important;
    border-color: #1e293b !important;
}
:is(html.dark-mode, body.dark-mode) .export-card.pdf {
    background: #1c1117 !important;
    border-color: #881337 !important;
}
:is(html.dark-mode, body.dark-mode) .export-card.excel {
    background: #061d18 !important;
    border-color: #065f46 !important;
}
:is(html.dark-mode, body.dark-mode) .export-card h4 {
    color: #f8fafc !important;
}
:is(html.dark-mode, body.dark-mode) .export-card p {
    color: #94a3b8 !important;
}

/* Print Styles */
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    .top-header, .sidebar-nav, .guide-sidebar, .guide-search-panel, .btn-guide-print, .btn-guide-workflow, .guide-lang-switcher {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .guide-hero {
        background: none !important;
        color: #000000 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-bottom: 2px solid #000000;
        margin-bottom: 20px;
    }
    .guide-hero h1, .guide-hero p, .guide-hero__meta {
        color: #000000 !important;
    }
    .guide-layout {
        display: block !important;
    }
    .section-card {
        page-break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        margin-bottom: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const appEl = document.getElementById('guidePageApp');
    const searchInput = document.getElementById('guideSearch');
    const clearBtn = document.getElementById('clearGuideSearch');
    const resultCount = document.getElementById('guideResultCount');
    const sections = document.querySelectorAll('.guide-section');
    const navButtons = document.querySelectorAll('.guide-nav-btn');
    const langButtons = document.querySelectorAll('.lang-toggle-btn');

    // Language Switcher Logic
    function setGuideLanguage(lang) {
        if (!['tl', 'en'].includes(lang)) lang = 'tl';
        appEl.setAttribute('data-guide-lang', lang);
        localStorage.setItem('guide_selected_lang', lang);

        langButtons.forEach(btn => {
            if (btn.getAttribute('data-lang-val') === lang) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Update search placeholder
        if (searchInput) {
            const newPlaceholder = searchInput.getAttribute(`data-placeholder-${lang}`);
            if (newPlaceholder) {
                searchInput.setAttribute('placeholder', newPlaceholder);
            }
        }

        // Re-run search if active
        performSearch();
    }

    langButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang-val');
            setGuideLanguage(lang);
        });
    });

    // Load saved language preference or default to 'tl'
    const savedLang = localStorage.getItem('guide_selected_lang') || 'tl';
    setGuideLanguage(savedLang);

    // Sidebar Category Filter / Scroll
    navButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            navButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const targetId = this.getAttribute('data-target');
            const currentLang = appEl.getAttribute('data-guide-lang') || 'tl';

            if (targetId === 'all') {
                sections.forEach(sec => sec.style.display = 'block');
                resultCount.innerHTML = currentLang === 'tl'
                    ? `Ipinapakita ang lahat ng ${sections.length} na paksa`
                    : `Showing all ${sections.length} topics`;
                window.scrollTo({ top: document.querySelector('.guide-content').offsetTop - 100, behavior: 'smooth' });
            } else {
                sections.forEach(sec => {
                    if (sec.id === targetId || sec.getAttribute('data-topic') === targetId) {
                        sec.style.display = 'block';
                    } else {
                        sec.style.display = 'none';
                    }
                });
                const targetSec = document.getElementById(targetId);
                if (targetSec) {
                    targetSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                resultCount.innerHTML = currentLang === 'tl'
                    ? `Ipinapakita ang napiling paksa`
                    : `Showing selected topic`;
            }
        });
    });

    // Real-time Search
    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();
        const currentLang = appEl.getAttribute('data-guide-lang') || 'tl';

        if (query.length > 0) {
            clearBtn.style.display = 'block';
        } else {
            clearBtn.style.display = 'none';
        }

        let matchCount = 0;

        sections.forEach(sec => {
            // Search inside visible language tags and common text
            const text = sec.textContent.toLowerCase();
            if (query === '' || text.includes(query)) {
                sec.style.display = 'block';
                matchCount++;
            } else {
                sec.style.display = 'none';
            }
        });

        if (query === '') {
            resultCount.innerHTML = currentLang === 'tl'
                ? `Ipinapakita ang lahat ng ${sections.length} na paksa`
                : `Showing all ${sections.length} topics`;
        } else {
            resultCount.innerHTML = currentLang === 'tl'
                ? `May ${matchCount} na paksang tumugma sa "${query}"`
                : `${matchCount} topic(s) matching "${query}"`;
        }
    }

    searchInput.addEventListener('input', performSearch);

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    });

    // Keyboard shortcut "/"
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});
</script>
@endsection
