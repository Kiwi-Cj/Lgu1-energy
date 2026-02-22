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
    max-width: 1240px;
    margin: 0 auto;
}
.settings-shell {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.07);
    padding: 24px;
}
.settings-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}
.settings-title-wrap h1 {
    margin: 0;
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
}
.settings-title-wrap p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.95rem;
}
.settings-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}
.settings-btn {
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.settings-btn.back {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #1e293b;
}
.settings-btn.save {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}
.settings-btn.save:hover { background: #1d4ed8; border-color: #1d4ed8; }

.settings-alert {
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 14px;
    font-size: 0.9rem;
    font-weight: 600;
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
    gap: 14px;
}
.settings-card {
    border: 1px solid #dbe6f3;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
}
.settings-head {
    width: 100%;
    border: none;
    background: linear-gradient(90deg, #f8fbff 0%, #f1f5ff 100%);
    color: #1e293b;
    text-align: left;
    padding: 14px 16px;
    font-size: 1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
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
    padding: 16px;
    background: #ffffff;
}
.settings-card.open .settings-body {
    display: block;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px 16px;
}
.settings-subblock {
    margin-bottom: 14px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fbfdff;
}
.settings-subblock h3 {
    margin: 0 0 10px;
    color: #1d4ed8;
    font-size: 0.95rem;
    font-weight: 800;
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
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 11px;
    font-size: 0.92rem;
    background: #ffffff;
    color: #0f172a;
}
.settings-field input:focus,
.settings-field select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.16);
    outline: none;
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
    background: #111827;
    color: #e2e8f0;
}
body.dark-mode .settings-head small,
body.dark-mode .settings-chevron {
    color: #94a3b8;
}
body.dark-mode .settings-body {
    background: #0f172a;
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
}
</style>

<div class="settings-page">
    <div class="settings-shell">
        <div class="settings-topbar">
            <div class="settings-title-wrap">
                <h1>System Settings</h1>
                <p>Configure app behavior, security, notification, and threshold rules.</p>
            </div>
            <div class="settings-actions">
                <a href="{{ route('dashboard.index') }}" class="settings-btn back"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="submit" form="settingsForm" class="settings-btn save"><i class="fa fa-floppy-disk"></i> Save Settings</button>
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
                    <span><i class="fa fa-sliders"></i> General / App Settings</span>
                    <span><small>Branding + timezone</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
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
                    <span><i class="fa fa-shield-halved"></i> User & Security</span>
                    <span><small>Session + login controls</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
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
                            <input type="number" id="session_timeout" name="session_timeout" min="5" max="720" value="{{ $getSetting('session_timeout', 120) }}">
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
                    <span><i class="fa fa-bolt"></i> Energy Monitoring</span>
                    <span><small>Alert thresholds + incident rules</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
                </button>
                <div class="settings-body">
                    @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'xlarge' => 'Extra Large'] as $sizeKey => $sizeLabel)
                        <div class="settings-subblock">
                            <h3>{{ $sizeLabel }} Facility Thresholds (%)</h3>
                            <div class="settings-grid">
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

                    <div class="settings-grid">
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
                    <span><i class="fa fa-building"></i> Facility Settings</span>
                    <span><small>Uploads + defaults</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
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
                    <span><i class="fa fa-envelope"></i> Email & Notifications</span>
                    <span><small>SMTP + notification toggle</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
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
                    <span><i class="fa fa-file-lines"></i> Reports & Audit Trail</span>
                    <span><small>Retention + export rules</small> <i class="fa fa-chevron-right settings-chevron"></i></span>
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
                            <input type="number" id="retention_period" name="retention_period" min="1" max="120" value="{{ $getSetting('retention_period', 12) }}">
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
