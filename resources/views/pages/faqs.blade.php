@extends('layouts.qc-admin')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="faqs-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <div>
                <h1>Frequently Asked Questions</h1>
                <p class="header-subtitle">Find answers to common questions about the LGU Energy Efficiency System</p>
            </div>
        </div>
    </div>

    <div class="faq-search-section">
        <div class="search-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="faqSearch" placeholder="Search for answers..." onkeyup="filterFaqs()">
        </div>
    </div>

    <div class="faq-categories">
        <button class="category-btn active" data-category="all" onclick="filterCategory('all')">All</button>
        <button class="category-btn" data-category="getting-started" onclick="filterCategory('getting-started')">Getting Started</button>
        <button class="category-btn" data-category="features" onclick="filterCategory('features')">Features</button>
        <button class="category-btn" data-category="account" onclick="filterCategory('account')">Account & Security</button>
        <button class="category-btn" data-category="troubleshooting" onclick="filterCategory('troubleshooting')">Troubleshooting</button>
    </div>

    <div class="faq-list">
        <!-- Getting Started -->
        <div class="faq-group" data-category="getting-started">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I log in to the system?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Use your registered email address and password on the login page. If you haven't registered yet, contact your system administrator to create an account. Make sure to use the correct credentials and check that Caps Lock is off.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>I forgot my password. How do I reset it?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Click on the "Forgot Password" link on the login page. Enter your registered email address and follow the instructions sent to your email. You will receive a password reset link that will allow you to create a new password.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I navigate the dashboard?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>The dashboard provides a high-level overview of your energy consumption across all facilities. Use the sidebar menu on the left to navigate between different modules: Dashboard, Facilities, Energy Monitoring, Maintenance, Reports, and Admin settings.</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="faq-group" data-category="features">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I add a new facility?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Navigate to the Facilities section from the sidebar. Click on the "Add Facility" button. Fill in the required information including facility name, location, type, and contact details. Once saved, the facility will appear in your facilities list.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I track energy consumption for a facility?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Go to Energy Monitoring in the sidebar. You can view consumption data for each facility, including monthly usage, cost, and deviation from baseline. You can also filter by date range and export reports for analysis.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I generate reports?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Navigate to the Reports section from the sidebar. Choose the type of report you need: Energy Report, Efficiency Summary, or Incidents Report. Select the facility, date range, and other filters, then click "Generate Report". You can view, download, or print the report.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>What is the Energy Conservation feature?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>The Energy Conservation module provides AI-powered recommendations for reducing energy consumption and improving efficiency. It analyzes your energy usage patterns and suggests actionable measures to save energy and reduce costs.</p>
                </div>
            </div>
        </div>

        <!-- Account & Security -->
        <div class="faq-group" data-category="account">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I update my profile information?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Click on your profile icon or name in the top-right corner and select "My Profile". From there, you can update your name, email, contact number, and profile photo. Click "Save Changes" to apply the updates.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I change my password?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Go to your profile page (click your name in the top-right corner). Scroll to the password section. Enter your current password, then your new password twice to confirm. Click "Change Password" to save.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>How do I log out of the system?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Click on your profile icon or name in the top-right corner, then click "Logout" at the bottom of the dropdown menu. You will be redirected to the login page.</p>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="faq-group" data-category="troubleshooting">
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>I'm getting an error message. What should I do?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Take a screenshot of the error message and note what action you were performing. Clear your browser cache and try again. If the issue persists, contact your system administrator or submit a report through the Contact page.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>The system is running slow. What can I do?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Try the following: clear your browser cache, close unused tabs, check your internet connection, and ensure you're using a supported browser (Chrome, Firefox, Edge, or Safari). If the issue continues, notify your system administrator.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" onclick="toggleFaq(this)">
                    <span>Some data seems incorrect. How do I verify?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Check that the data source (meters, sensors, or manual input) is functioning correctly. Verify the data against source records. Contact your system administrator if data discrepancies are found.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-section">
        <p>Still have questions? <a href="{{ route('landing.contact') }}">Contact us</a> for further assistance.</p>
    </div>

    <div class="back-section">
        <a href="{{ url()->previous() }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>

<script>
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const isActive = item.classList.contains('active');
    // Close all open FAQs
    document.querySelectorAll('.faq-item.active').forEach(el => el.classList.remove('active'));
    if (!isActive) {
        item.classList.add('active');
    }
}

function filterCategory(category) {
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.category === category);
    });
    document.querySelectorAll('.faq-group').forEach(group => {
        const shouldShow = category === 'all' || group.dataset.category === category;
        group.style.display = shouldShow ? 'block' : 'none';
    });
}

function filterFaqs() {
    const query = document.getElementById('faqSearch').value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? 'block' : 'none';
    });
}
</script>

<style>
.faqs-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 10px 0;
}

.page-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    border-radius: 16px;
    padding: 32px 40px;
    margin-bottom: 32px;
    color: #fff;
    box-shadow: 0 4px 20px rgba(30, 58, 95, 0.25);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    flex-shrink: 0;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 600;
    margin: 0;
    color: #fff;
}

.header-subtitle {
    font-size: 15px;
    opacity: 0.85;
    margin: 4px 0 0 0;
}

.faq-search-section {
    margin-bottom: 24px;
}

.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-wrapper input {
    width: 100%;
    padding: 14px 16px 14px 46px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    background: #fff;
    color: #1e293b;
    transition: border-color 0.2s ease;
}

.search-wrapper input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
}

.faq-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 28px;
}

.category-btn {
    padding: 8px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 999px;
    background: #fff;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.category-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.category-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.faq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.faq-group {
    margin-bottom: 8px;
}

.faq-item {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e9edf4;
    overflow: hidden;
    transition: border-color 0.2s ease;
}

.faq-item:hover {
    border-color: #cbd5e1;
}

.faq-item.active {
    border-color: #2563eb;
}

.faq-question {
    width: 100%;
    padding: 16px 20px;
    background: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    cursor: pointer;
    text-align: left;
}

.faq-question:hover {
    background: #f8fafc;
}

.faq-question i {
    font-size: 14px;
    color: #94a3b8;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
    color: #2563eb;
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
    padding: 0 20px;
}

.faq-item.active .faq-answer {
    max-height: 400px;
    padding: 0 20px 16px 20px;
}

.faq-answer p {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
    margin: 0;
}

.contact-section {
    text-align: center;
    padding: 24px;
    background: #f8fafc;
    border-radius: 12px;
    margin: 24px 0 20px 0;
    border: 1px solid #e9edf4;
}

.contact-section p {
    font-size: 15px;
    color: #475569;
    margin: 0;
}

.contact-section a {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
}

.contact-section a:hover {
    text-decoration: underline;
}

.back-section {
    text-align: center;
    margin-top: 8px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: #e9edf4;
    color: #475569;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.2s ease;
}

.btn-back:hover {
    background: #d1d9e6;
    color: #1e293b;
}

/* Dark Mode */
body.dark-mode .faq-item {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .faq-item:hover {
    border-color: #475569;
}

body.dark-mode .faq-item.active {
    border-color: #3b82f6;
}

body.dark-mode .faq-question {
    color: #e2e8f0;
}

body.dark-mode .faq-question:hover {
    background: #0f172a;
}

body.dark-mode .faq-answer p {
    color: #cbd5e1;
}

body.dark-mode .search-wrapper input {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}

body.dark-mode .search-wrapper input:focus {
    border-color: #3b82f6;
}

body.dark-mode .category-btn {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}

body.dark-mode .category-btn:hover {
    border-color: #3b82f6;
    color: #93c5fd;
}

body.dark-mode .category-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

body.dark-mode .contact-section {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .contact-section p {
    color: #cbd5e1;
}

body.dark-mode .btn-back {
    background: #334155;
    color: #cbd5e1;
}

body.dark-mode .btn-back:hover {
    background: #475569;
    color: #f1f5f9;
}
</style>
@endsection