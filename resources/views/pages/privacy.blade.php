@extends('layouts.qc-admin')

@section('title', 'Privacy Notice')

@section('content')
<div class="privacy-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h1>Privacy Notice</h1>
                <p class="header-subtitle">How we collect, use, and protect your personal information</p>
            </div>
        </div>
    </div>

    <div class="privacy-content">
        <div class="privacy-card">
            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-database"></i></div>
                <div>
                    <h2>Information We Collect</h2>
                    <p>We collect information you provide directly to us when you:</p>
                    <ul>
                        <li>Create an account or register for the system</li>
                        <li>Update your profile information</li>
                        <li>Submit forms or requests through the system</li>
                        <li>Communicate with us via email or contact forms</li>
                    </ul>
                    <p class="mt-2">This information may include:</p>
                    <ul>
                        <li><strong>Personal Identifiers:</strong> Name, email address, contact number</li>
                        <li><strong>Professional Information:</strong> Role, department, facility assignments</li>
                        <li><strong>System Activity:</strong> Login history, interactions with the platform</li>
                    </ul>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-gears"></i></div>
                <div>
                    <h2>How We Use Your Information</h2>
                    <p>Your information is used to provide, maintain, and improve the LGU Energy Efficiency System:</p>
                    <ul>
                        <li><strong>System Access:</strong> To authenticate your identity and grant access</li>
                        <li><strong>Energy Monitoring:</strong> To track and analyze energy consumption across facilities</li>
                        <li><strong>Reporting:</strong> To generate reports and analytics for decision-making</li>
                        <li><strong>Notifications:</strong> To send updates, alerts, and system announcements</li>
                        <li><strong>Compliance:</strong> To meet legal and regulatory requirements</li>
                    </ul>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-lock"></i></div>
                <div>
                    <h2>Data Security</h2>
                    <p>We implement robust security measures to protect your personal information:</p>
                    <ul>
                        <li><strong>Encryption:</strong> Data is encrypted in transit and at rest</li>
                        <li><strong>Access Control:</strong> Role-based access limits who can view or modify data</li>
                        <li><strong>Secure Authentication:</strong> Multi-factor authentication and OTP verification</li>
                        <li><strong>Regular Audits:</strong> Periodic security reviews and vulnerability assessments</li>
                        <li><strong>Data Backup:</strong> Regular backups to prevent data loss</li>
                    </ul>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <h2>Data Retention</h2>
                    <p>We retain your personal information only as long as necessary to fulfill the purposes outlined in this policy:</p>
                    <ul>
                        <li><strong>Active Accounts:</strong> Data is retained while your account remains active</li>
                        <li><strong>Inactive Accounts:</strong> Data may be retained for up to 5 years after account closure</li>
                        <li><strong>Legal Compliance:</strong> Some data may be retained longer to comply with legal obligations</li>
                        <li><strong>Aggregated Data:</strong> Anonymized data may be retained for analytical purposes</li>
                    </ul>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-user-check"></i></div>
                <div>
                    <h2>Your Rights</h2>
                    <p>You have the following rights regarding your personal information:</p>
                    <ul>
                        <li><strong>Right to Access:</strong> Request a copy of your personal data</li>
                        <li><strong>Right to Rectification:</strong> Request correction of inaccurate data</li>
                        <li><strong>Right to Erasure:</strong> Request deletion of your personal data (subject to legal obligations)</li>
                        <li><strong>Right to Restrict Processing:</strong> Request limitation of data processing</li>
                        <li><strong>Right to Data Portability:</strong> Request transfer of your data to another service</li>
                        <li><strong>Right to Object:</strong> Object to certain data processing activities</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent at any time</li>
                    </ul>
                    <p class="mt-2">To exercise any of these rights, please <a href="{{ route('landing.contact') }}">contact us</a>.</p>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-cookie-bite"></i></div>
                <div>
                    <h2>Cookies and Tracking</h2>
                    <p>We use cookies and similar technologies to enhance your experience:</p>
                    <ul>
                        <li><strong>Session Cookies:</strong> Essential for system functionality and authentication</li>
                        <li><strong>Preference Cookies:</strong> Remember your preferences (e.g., dark mode)</li>
                        <li><strong>Analytics Cookies:</strong> Help us understand how you interact with the system</li>
                    </ul>
                    <p class="mt-2">You can manage your cookie preferences through your browser settings.</p>
                </div>
            </div>

            <div class="privacy-divider"></div>

            <div class="privacy-section">
                <div class="section-icon"><i class="fa-solid fa-envelope"></i></div>
                <div>
                    <h2>Contact Us</h2>
                    <p>If you have questions, concerns, or requests regarding this Privacy Notice, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:privacy@lgucity.gov.ph">privacy@lgucity.gov.ph</a></li>
                        <li><strong>Contact Form:</strong> <a href="{{ route('landing.contact') }}">Click here</a></li>
                        <li><strong>Address:</strong> LGU Energy Office, City Hall Complex</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="privacy-footer">
            <p><i class="fa-regular fa-calendar"></i> Last Updated: <strong>{{ date('F d, Y') }}</strong></p>
            <p><i class="fa-regular fa-file-lines"></i> Version 1.0</p>
        </div>

        <div class="back-section">
            <a href="{{ url()->previous() }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<style>
.privacy-page {
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

.privacy-content {
    background: #fff;
    border-radius: 14px;
    padding: 32px 36px;
    border: 1px solid #e9edf4;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.privacy-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.section-icon {
    width: 48px;
    height: 48px;
    background: #dbeafe;
    color: #2563eb;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 4px;
}

.privacy-section h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 8px 0;
}

.privacy-section p {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
    margin: 0 0 8px 0;
}

.privacy-section ul {
    padding-left: 20px;
    margin: 6px 0 0 0;
}

.privacy-section ul li {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
    margin-bottom: 4px;
}

.privacy-section ul li strong {
    color: #1e293b;
}

.privacy-section a {
    color: #2563eb;
    text-decoration: none;
}

.privacy-section a:hover {
    text-decoration: underline;
}

.mt-2 {
    margin-top: 8px;
}

.privacy-divider {
    height: 1px;
    background: #e9edf4;
    margin: 24px 0;
}

.privacy-footer {
    display: flex;
    justify-content: center;
    gap: 32px;
    margin: 24px 0 20px 0;
    padding: 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e9edf4;
}

.privacy-footer p {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.privacy-footer p i {
    margin-right: 6px;
    color: #2563eb;
}

.privacy-footer p strong {
    color: #1e293b;
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
body.dark-mode .privacy-content {
    background: #1e293b;
    border-color: #334155;
}

body.dark-mode .privacy-section h2 {
    color: #e2e8f0;
}

body.dark-mode .privacy-section p {
    color: #cbd5e1;
}

body.dark-mode .privacy-section ul li {
    color: #cbd5e1;
}

body.dark-mode .privacy-section ul li strong {
    color: #f1f5f9;
}

body.dark-mode .privacy-section a {
    color: #93c5fd;
}

body.dark-mode .section-icon {
    background: #132a4a;
    color: #93c5fd;
}

body.dark-mode .privacy-divider {
    background: #334155;
}

body.dark-mode .privacy-footer {
    background: #0f172a;
    border-color: #334155;
}

body.dark-mode .privacy-footer p {
    color: #94a3b8;
}

body.dark-mode .privacy-footer p strong {
    color: #e2e8f0;
}

body.dark-mode .btn-back {
    background: #334155;
    color: #cbd5e1;
}

body.dark-mode .btn-back:hover {
    background: #475569;
    color: #f1f5f9;
}

@media (max-width: 640px) {
    .page-header {
        padding: 24px 20px;
    }
    .page-header h1 {
        font-size: 22px;
    }
    .header-icon {
        width: 48px;
        height: 48px;
        font-size: 22px;
    }
    .privacy-content {
        padding: 20px 16px;
    }
    .privacy-section {
        flex-direction: column;
        gap: 12px;
    }
    .section-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    .privacy-footer {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}
</style>
@endsection