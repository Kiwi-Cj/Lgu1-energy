@extends('layouts.qc-admin')
@section('title', 'System Settings')

@section('content')
@php
    $getSetting = function (string $key, $fallback = '') use ($settings, $defaults) {
        return old($key, $settings[$key] ?? ($defaults[$key] ?? $fallback));
    };
@endphp

<style>
.settings-page {
    max-width: 1520px;
    margin: 0 auto;
}
.settings-shell {
    position: relative;
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 22%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #dbe6f3;
    border-radius: 26px;
    box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10);
    padding: 26px;
    overflow: hidden;
}
.settings-topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
}
.settings-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
    gap: 18px;
    margin-bottom: 20px;
}
.settings-title-wrap h1 {
    margin: 0;
    font-size: clamp(2rem, 3vw, 2.6rem);
    line-height: 1;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -0.04em;
}
.settings-title-wrap p {
    margin: 10px 0 0;
    color: #526277;
    font-size: 1rem;
    max-width: 760px;
}
.settings-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.08);
    color: #1d4ed8;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.settings-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    align-self: stretch;
}
.settings-summary-card {
    padding: 16px 16px 14px;
    border-radius: 18px;
    border: 1px solid #d9e5f5;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}
.settings-summary-card strong {
    display: block;
    margin-top: 8px;
    font-size: 1.25rem;
    font-weight: 900;
    color: #0f172a;
}
.settings-summary-card span {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.settings-summary-card i {
    color: #2563eb;
    font-size: 1rem;
}
.settings-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-shrink: 0;
}
.settings-btn {
    border: 1px solid transparent;
    border-radius: 14px;
    padding: 11px 16px;
    font-weight: 700;
    font-size: 0.92rem;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
}
.settings-btn.back {
    background: rgba(255, 255, 255, 0.9);
    border-color: #cbd5e1;
    color: #1e293b;
}
.settings-btn.save {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-color: #1d4ed8;
    color: #ffffff;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
}
.settings-btn:hover {
    transform: translateY(-1px);
}
.settings-btn.save:hover { background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); border-color: #1d4ed8; }

.settings-alert {
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 16px;
    font-size: 0.92rem;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
}
.settings-alert.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}
.settings-alert.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.settings-alert ul {
    margin: 8px 0 0 16px;
    padding: 0;
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.settings-card {
    border: 1px solid #d8e5f4;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
}
.settings-head {
    width: 100%;
    border: none;
    background: linear-gradient(90deg, #f8fbff 0%, #eef4ff 100%);
    color: #1e293b;
    text-align: left;
    padding: 17px 18px;
    font-size: 1.03rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}
.settings-head-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.settings-head-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #d8e5f4;
    color: #1d4ed8;
    box-shadow: 0 6px 12px rgba(37, 99, 235, 0.08);
}
.settings-head-meta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.settings-head-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.08);
    color: #315cba;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.02em;
}
.settings-head small {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 600;
}
.settings-chevron {
    transition: transform .2s ease;
    color: #475569;
}
.settings-card.open .settings-chevron {
    transform: rotate(90deg);
}
.settings-body {
    display: none;
    border-top: 1px solid #e2e8f0;
    padding: 18px;
    background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,251,255,0.98) 100%);
}
.settings-card.open .settings-body {
    display: block;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px 18px;
}
.settings-subblock {
    margin-bottom: 16px;
    padding: 16px;
    border: 1px solid #dbe6f3;
    border-radius: 18px;
    background: linear-gradient(180deg, #fbfdff 0%, #f6faff 100%);
}
.settings-subblock h3 {
    margin: 0 0 6px;
    color: #1d4ed8;
    font-size: 1rem;
    font-weight: 900;
}
.settings-subblock p {
    margin: 0 0 14px;
    color: #64748b;
    font-size: 0.82rem;
}
.settings-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.settings-field label {
    font-weight: 700;
    color: #334155;
    font-size: 0.85rem;
}
.settings-field input,
.settings-field select {
    width: 100%;
    min-height: 44px;
    border: 1px solid #c8d5e6;
    border-radius: 12px;
    padding: 11px 12px;
    font-size: 0.92rem;
    background: #ffffff;
    color: #0f172a;
    transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
}
.settings-field input:focus,
.settings-field select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.16);
    outline: none;
}
.settings-field input[type="file"] {
    padding: 8px 10px;
    background: #f8fbff;
}
.settings-help {
    font-size: 0.76rem;
    color: #64748b;
}
.settings-file-chip {
    margin-top: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 0.76rem;
}
.settings-error {
    margin-top: 4px;
    font-size: 0.75rem;
    color: #dc2626;
    font-weight: 700;
}
.settings-threshold-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
}
.settings-toggle-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px 18px;
}
.settings-section-note {
    margin-top: 6px;
    color: #64748b;
    font-size: 0.8rem;
}

body.dark-mode .settings-shell {
    background: #0f172a;
    border-color: #253043;
    box-shadow: 0 18px 36px rgba(2, 6, 23, 0.56);
}
body.dark-mode .settings-title-wrap h1 {
    color: #e2e8f0;
}
body.dark-mode .settings-title-wrap p {
    color: #94a3b8;
}
body.dark-mode .settings-btn.back {
    background: #111827;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .settings-summary-card {
    background: rgba(15, 23, 42, 0.75);
    border-color: #243143;
    box-shadow: none;
}
body.dark-mode .settings-summary-card strong {
    color: #e2e8f0;
}
body.dark-mode .settings-summary-card span {
    color: #8ea0b8;
}
body.dark-mode .settings-summary-card i {
    color: #7db6ff;
}
body.dark-mode .settings-alert.success {
    background: #14532d;
    color: #dcfce7;
    border-color: #166534;
}
body.dark-mode .settings-alert.error {
    background: #3f1517;
    color: #fecaca;
    border-color: #7f1d1d;
}
body.dark-mode .settings-card {
    background: #0f172a;
    border-color: #253043;
}
body.dark-mode .settings-head {
    background: linear-gradient(90deg, #111827 0%, #0f1a2b 100%);
    color: #e2e8f0;
}
body.dark-mode .settings-head-icon {
    background: #0f172a;
    border-color: #334155;
    color: #93c5fd;
    box-shadow: none;
}
body.dark-mode .settings-head-badge {
    background: rgba(59, 130, 246, 0.16);
    color: #bfdbfe;
}
body.dark-mode .settings-head small,
body.dark-mode .settings-chevron {
    color: #94a3b8;
}
body.dark-mode .settings-body {
    background: linear-gradient(180deg, #0f172a 0%, #0c1524 100%);
    border-top-color: #253043;
}
body.dark-mode .settings-subblock {
    background: #111827;
    border-color: #253043;
}
body.dark-mode .settings-subblock h3 {
    color: #93c5fd;
}
body.dark-mode .settings-field label {
    color: #cbd5e1;
}
body.dark-mode .settings-field input,
body.dark-mode .settings-field select {
    background: #111827;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .settings-field input[type="file"] {
    background: #0b1220;
}
body.dark-mode .settings-help {
    color: #94a3b8;
}
body.dark-mode .settings-file-chip {
    background: #1e293b;
    color: #bfdbfe;
    border-color: #334155;
}

@media (max-width: 720px) {
    .settings-shell {
        padding: 14px;
    }
    .settings-topbar {
        flex-direction: column;
        align-items: flex-start;
    }
    .settings-hero {
        grid-template-columns: 1fr;
    }
    .settings-summary {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="settings-page">
    <div class="settings-shell">
        <div class="settings-topbar">
            <div class="settings-title-wrap">
                <div class="settings-kicker"><i class="fa fa-gear"></i> Control Center</div>
                <h1>System Settings</h1>
                <p>Configure app behavior, security, notification, and threshold rules.</p>
            </div>
            <div class="settings-actions">
                <a href="{{ route('dashboard.index') }}" class="settings-btn back"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="submit" form="settingsForm" class="settings-btn save"><i class="fa fa-floppy-disk"></i> Save Settings</button>
            </div>
        </div>

        <div class="settings-hero">
            <div class="settings-subblock" style="margin-bottom:0;">
                <h3>Configuration Snapshot</h3>
                <p>Use this page to manage branding, login rules, energy thresholds, uploads, and reporting defaults from one place.</p>
                <div class="settings-section-note">
                    Current app identity: <strong>{{ $getSetting('system_name') }}</strong>
                    <span style="color:#94a3b8;">|</span>
                    Timezone: <strong>{{ $getSetting('timezone', 'Asia/Manila') }}</strong>
                </div>
            </div>
            <div class="settings-summary">
                <div class="settings-summary-card">
                    <i class="fa fa-bolt"></i>
                    <strong>20</strong>
                    <span>Threshold Inputs</span>
                </div>
                <div class="settings-summary-card">
                    <i class="fa fa-shield-halved"></i>
                    <strong>{{ (string) $getSetting('enable_otp_login', '1') === '1' ? 'OTP On' : 'OTP Off' }}</strong>
                    <span>Access Guard</span>
                </div>
                <div class="settings-summary-card">
                    <i class="fa fa-envelope"></i>
                    <strong>{{ (string) $getSetting('enable_email_notifications', '1') === '1' ? 'Email On' : 'Email Off' }}</strong>
                    <span>Notifications</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="settings-alert success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="settings-alert error">
                Please fix the highlighted fields.
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="settingsForm" class="settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf

            <section class="settings-card open">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-sliders"></i></span> General / App Settings</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Live identity</span><small>Branding + timezone</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="system_name">System Name</label>
                            <input type="text" id="system_name" name="system_name" value="{{ $getSetting('system_name') }}" required>
                            @error('system_name') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="short_name">Short Name</label>
                            <input type="text" id="short_name" name="short_name" value="{{ $getSetting('short_name') }}" required>
                            @error('short_name') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="org_name">Organization</label>
                            <input type="text" id="org_name" name="org_name" value="{{ $getSetting('org_name') }}" required>
                            @error('org_name') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="timezone">Timezone</label>
                            <select id="timezone" name="timezone" required>
                                @foreach(['Asia/Manila', 'UTC', 'Asia/Singapore', 'Asia/Tokyo'] as $tz)
                                    <option value="{{ $tz }}" {{ (string) $getSetting('timezone', 'Asia/Manila') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('timezone') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="system_logo">System Logo</label>
                            <input type="file" id="system_logo" name="system_logo" accept=".jpg,.jpeg,.png,.webp,.svg">
                            <span class="settings-help">Max 2MB. JPG/PNG/WEBP/SVG</span>
                            @if($getSetting('system_logo'))
                                <span class="settings-file-chip"><i class="fa fa-image"></i> {{ basename((string) $getSetting('system_logo')) }}</span>
                            @endif
                            @error('system_logo') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="favicon">Favicon</label>
                            <input type="file" id="favicon" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg">
                            <span class="settings-help">Max 1MB. ICO/PNG/JPG/SVG</span>
                            @if($getSetting('favicon'))
                                <span class="settings-file-chip"><i class="fa fa-star"></i> {{ basename((string) $getSetting('favicon')) }}</span>
                            @endif
                            @error('favicon') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-shield-halved"></i></span> User & Security</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Access rules</span><small>Session + login controls</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="otp_expiration">OTP Expiration (minutes)</label>
                            <input type="number" id="otp_expiration" name="otp_expiration" min="1" max="60" value="{{ $getSetting('otp_expiration', 5) }}">
                            @error('otp_expiration') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="max_login_attempts">Max Login Attempts</label>
                            <input type="number" id="max_login_attempts" name="max_login_attempts" min="1" max="15" value="{{ $getSetting('max_login_attempts', 5) }}">
                            @error('max_login_attempts') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="session_timeout">Session Timeout (minutes)</label>
                            <input type="number" id="session_timeout" name="session_timeout" min="1" max="60" value="{{ $getSetting('session_timeout', 60) }}">
                            @error('session_timeout') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="enable_otp_login">OTP Login</label>
                            <select id="enable_otp_login" name="enable_otp_login">
                                <option value="1" {{ (string) $getSetting('enable_otp_login', '1') === '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ (string) $getSetting('enable_otp_login', '1') === '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error('enable_otp_login') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-bolt"></i></span> Energy Monitoring</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Alert engine</span><small>Baseline Threshold</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra Large'] as $sizeKey => $sizeLabel)
                        <div class="settings-subblock">
                            <h3>{{ $sizeLabel }} Baseline Threshold (%)</h3>
                            <p>Each level must increase strictly from Level 1 to Level 5.</p>
                            <div class="settings-threshold-grid">
                                @for($lvl = 1; $lvl <= 5; $lvl++)
                                    @php $field = "alert_level{$lvl}_{$sizeKey}"; @endphp
                                    <div class="settings-field">
                                        <label for="{{ $field }}">Level {{ $lvl }}</label>
                                        <input type="number" step="0.01" min="0" max="500" id="{{ $field }}" name="{{ $field }}" value="{{ $getSetting($field) }}">
                                        @error($field) <div class="settings-error">{{ $message }}</div> @enderror
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div class="settings-toggle-grid">
                        <div class="settings-field">
                            <label for="auto_log_incident">Auto Log Incident</label>
                            <select id="auto_log_incident" name="auto_log_incident">
                                <option value="1" {{ (string) $getSetting('auto_log_incident', '1') === '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ (string) $getSetting('auto_log_incident', '1') === '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error('auto_log_incident') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-building"></i></span> Facility Settings</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Media rules</span><small>Uploads + defaults</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="facility_image_size">Max Facility Image Size (MB)</label>
                            <input type="number" id="facility_image_size" name="facility_image_size" min="1" max="20" value="{{ $getSetting('facility_image_size', 5) }}">
                            @error('facility_image_size') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="allowed_image_types">Allowed Image Types</label>
                            <input type="text" id="allowed_image_types" name="allowed_image_types" value="{{ $getSetting('allowed_image_types', 'jpg,png,jpeg') }}">
                            @error('allowed_image_types') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="default_facility_status">Default Facility Status</label>
                            <select id="default_facility_status" name="default_facility_status">
                                <option value="active" {{ (string) $getSetting('default_facility_status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ (string) $getSetting('default_facility_status', 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('default_facility_status') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-envelope"></i></span> Email & Notifications</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Delivery channel</span><small>SMTP + notification toggle</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="mail_host">Mail Host</label>
                            <input type="text" id="mail_host" name="mail_host" value="{{ $getSetting('mail_host', '') }}">
                            @error('mail_host') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="mail_port">Mail Port</label>
                            <input type="number" id="mail_port" name="mail_port" min="1" max="65535" value="{{ $getSetting('mail_port', 587) }}">
                            @error('mail_port') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="enable_email_notifications">Email Notifications</label>
                            <select id="enable_email_notifications" name="enable_email_notifications">
                                <option value="1" {{ (string) $getSetting('enable_email_notifications', '1') === '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ (string) $getSetting('enable_email_notifications', '1') === '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error('enable_email_notifications') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-card">
                <button class="settings-head" type="button" onclick="toggleSettingsCard(this)">
                    <span class="settings-head-label"><span class="settings-head-icon"><i class="fa fa-file-lines"></i></span> Reports & Audit Trail</span>
                    <span class="settings-head-meta"><span class="settings-head-badge">Governance</span><small>Retention + export rules</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="enable_audit_logs">Enable Audit Logs</label>
                            <select id="enable_audit_logs" name="enable_audit_logs">
                                <option value="1" {{ (string) $getSetting('enable_audit_logs', '1') === '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ (string) $getSetting('enable_audit_logs', '1') === '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error('enable_audit_logs') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="retention_period">Retention Period (months)</label>
                            <input type="number" id="retention_period" name="retention_period" min="1" max="120" value="{{ $getSetting('retention_period', 3) }}">
                            <div class="settings-help" style="margin-top:6px;color:#64748b;font-size:0.82rem;">Older audit log rows beyond this window are automatically pruned.</div>
                            @error('retention_period') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="settings-field">
                            <label for="export_format">Default Export Format</label>
                            <select id="export_format" name="export_format">
                                <option value="pdf" {{ (string) $getSetting('export_format', 'pdf') === 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="excel" {{ (string) $getSetting('export_format', 'pdf') === 'excel' ? 'selected' : '' }}>Excel</option>
                            </select>
                            @error('export_format') <div class="settings-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
</div>

<script>
function toggleSettingsCard(btn) {
    const card = btn.closest('.settings-card');
    if (!card) return;
    card.classList.toggle('open');
}

document.getElementById('settingsForm')?.addEventListener('submit', function (e) {
    const sizes = ['small', 'medium', 'large', 'xlarge'];
    for (const size of sizes) {
        const levels = [];
        for (let i = 1; i <= 5; i += 1) {
            const el = document.getElementById(`alert_level${i}_${size}`);
            levels.push(parseFloat(el?.value || '0'));
        }
        for (let i = 1; i < levels.length; i += 1) {
            if (!(levels[i] > levels[i - 1])) {
                e.preventDefault();
                alert(`${size.toUpperCase()} thresholds must be strictly increasing from Level 1 to Level 5.`);
                const target = document.getElementById(`alert_level${i + 1}_${size}`);
                if (target) target.focus();
                return;
            }
        }
    }
});
</script>
@endsection
