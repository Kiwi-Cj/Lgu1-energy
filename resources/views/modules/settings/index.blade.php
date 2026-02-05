@extends('layouts.qc-admin')
@section('title', 'System Settings')

@section('content')
@php
$userRole = strtolower(auth()->user()->role ?? '');
if($userRole === 'staff'){
    header('Location: ' . route('modules.energy.index'));
    exit;
}
@endphp

<style>
:root{
    --primary:#2563eb;
    --bg:#f4f6fb;
    --card:#ffffff;
    --border:#e5e7eb;
    --text:#1f2937;
    --muted:#6b7280;
    --radius:14px;
    --shadow:0 6px 28px rgba(0,0,0,.08);
}
.settings-wrap{
    width:100%;
    margin:0;
    padding:32px 0 48px 0;
}
.settings-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    margin-bottom:22px;
    box-shadow:var(--shadow);
}
.settings-header{
    padding:24px 30px;
    font-weight:700;
    font-size:1.15rem;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
    background:#f8fafc;
}
.settings-arrow{transition:.3s;}
.settings-card.open .settings-arrow{transform:rotate(90deg);}
.settings-body{
    display:none;
    padding:36px 36px 24px 36px;
    border-top:1px solid var(--border);
    flex-direction:column;
    align-items:flex-start;
    gap:0;
}
.settings-card.open .settings-body{display:flex;}
.settings-grid{
    width:100%;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:28px 36px;
    align-items:center;
}
.settings-field{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:flex-start;
    min-width:0;
}
.settings-field label{
    font-weight:600;
    margin-bottom:6px;
    display:block;
    color:var(--text);
    font-size:1rem;
}
.settings-field input,
.settings-field select{
    width:100%;
    padding:12px 14px;
    border-radius:10px;
    border:1px solid var(--border);
    font-size:1rem;
    background:#f9fafb;
    color:var(--text);
    transition:border-color .2s, box-shadow .2s;
}
.settings-field input:focus,
.settings-field select:focus{
    border-color:var(--primary);
    outline:none;
    box-shadow:0 0 0 2px #2563eb33;
}
@media(max-width:1100px){
    .settings-grid{grid-template-columns:repeat(2,1fr);}
    .settings-body{padding:24px;}
}
@media(max-width:768px){
    .settings-grid{grid-template-columns:1fr;}
    .settings-body{padding:12px;}
}
</style>

<div class="settings-wrap">
<h1 style="font-size:2.3rem;font-weight:800;color:#2563eb;">System Settings</h1>
<p style="color:#6b7280;margin-bottom:26px;">System-wide configuration & behavior</p>

<form method="POST" action="{{ url('/modules/settings') }}" enctype="multipart/form-data">
@csrf

<!-- GENERAL -->
<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">General / App Settings <span class="settings-arrow">▶</span></div>
<div class="settings-body">
<div class="settings-grid">
    <div class="settings-field">
        <label for="system_name">System Name</label>
        <input type="text" id="system_name" name="system_name" value="{{ $settings['general'][0]->value ?? '' }}" placeholder="System Name">
    </div>
    <div class="settings-field">
        <label for="short_name">Short Name</label>
        <input type="text" id="short_name" name="short_name" value="{{ $settings['general'][1]->value ?? '' }}" placeholder="Short Name">
    </div>
    <div class="settings-field">
        <label for="org_name">Organization</label>
        <input type="text" id="org_name" name="org_name" value="{{ $settings['general'][2]->value ?? '' }}" placeholder="Organization">
    </div>
    <div class="settings-field">
        <label for="system_logo">System Logo</label>
        <input type="file" id="system_logo" name="system_logo">
    </div>
    <div class="settings-field">
        <label for="favicon">Favicon</label>
        <input type="file" id="favicon" name="favicon">
    </div>
    <div class="settings-field">
        <label for="timezone">Timezone</label>
        <select id="timezone" name="timezone"><option>Asia/Manila</option></select>
    </div>
</div>
</div>
</div>

<!-- USER -->
<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">User & Security <span class="settings-arrow">▶</span></div>
<div class="settings-body">
<div class="settings-grid">
    <div class="settings-field">
        <label for="otp_expiration">OTP Expiration</label>
        <input type="number" id="otp_expiration" name="otp_expiration" value="{{ $settings['user'][1]->value ?? 5 }}" placeholder="OTP Expiration">
    </div>
    <div class="settings-field">
        <label for="max_login_attempts">Max Attempts</label>
        <input type="number" id="max_login_attempts" name="max_login_attempts" value="{{ $settings['user'][2]->value ?? 5 }}" placeholder="Max Attempts">
    </div>
    <div class="settings-field">
        <label for="session_timeout">Session Timeout</label>
        <input type="number" id="session_timeout" name="session_timeout" value="{{ $settings['user'][3]->value ?? 120 }}" placeholder="Session Timeout">
    </div>
    <div class="settings-field">
        <label for="enable_otp_login">OTP Login</label>
        <select id="enable_otp_login" name="enable_otp_login"><option value="1">OTP ON</option><option value="0">OTP OFF</option></select>
    </div>
</div>
</div>
</div>

<!-- ENERGY -->

<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">Energy Monitoring <span class="settings-arrow">▶</span></div>
<div class="settings-body">
    <div style="width:100%;display:flex;flex-direction:column;gap:32px;">
        <div>
            <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Small Facility</h3>
            <div class="settings-grid">
                <div class="settings-field">
                    <label for="alert_low_small">Low %</label>
                    <input type="number" id="alert_low_small" name="alert_low_small" value="{{ $settings['energy'][0]->low_small ?? 5 }}" placeholder="Low %">
                </div>
                <div class="settings-field">
                    <label for="alert_medium_small">Medium %</label>
                    <input type="number" id="alert_medium_small" name="alert_medium_small" value="{{ $settings['energy'][1]->medium_small ?? 10 }}" placeholder="Medium %">
                </div>
                <div class="settings-field">
                    <label for="alert_high_small">High %</label>
                    <input type="number" id="alert_high_small" name="alert_high_small" value="{{ $settings['energy'][2]->high_small ?? 20 }}" placeholder="High %">
                </div>
            </div>
        </div>
        <div>
            <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Medium Facility</h3>
            <div class="settings-grid">
                <div class="settings-field">
                    <label for="alert_low_medium">Low %</label>
                    <input type="number" id="alert_low_medium" name="alert_low_medium" value="{{ $settings['energy'][0]->low_medium ?? 7 }}" placeholder="Low %">
                </div>
                <div class="settings-field">
                    <label for="alert_medium_medium">Medium %</label>
                    <input type="number" id="alert_medium_medium" name="alert_medium_medium" value="{{ $settings['energy'][1]->medium_medium ?? 13 }}" placeholder="Medium %">
                </div>
                <div class="settings-field">
                    <label for="alert_high_medium">High %</label>
                    <input type="number" id="alert_high_medium" name="alert_high_medium" value="{{ $settings['energy'][2]->high_medium ?? 23 }}" placeholder="High %">
                </div>
            </div>
        </div>
        <div>
            <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Large Facility</h3>
            <div class="settings-grid">
                <div class="settings-field">
                    <label for="alert_low_large">Low %</label>
                    <input type="number" id="alert_low_large" name="alert_low_large" value="{{ $settings['energy'][0]->low_large ?? 10 }}" placeholder="Low %">
                </div>
                <div class="settings-field">
                    <label for="alert_medium_large">Medium %</label>
                    <input type="number" id="alert_medium_large" name="alert_medium_large" value="{{ $settings['energy'][1]->medium_large ?? 16 }}" placeholder="Medium %">
                </div>
                <div class="settings-field">
                    <label for="alert_high_large">High %</label>
                    <input type="number" id="alert_high_large" name="alert_high_large" value="{{ $settings['energy'][2]->high_large ?? 26 }}" placeholder="High %">
                </div>
            </div>
        </div>
        <div>
            <h3 style="font-size:1.08rem;font-weight:700;color:#2563eb;margin-bottom:12px;">Extra Large Facility</h3>
            <div class="settings-grid">
                <div class="settings-field">
                    <label for="alert_low_xlarge">Low %</label>
                    <input type="number" id="alert_low_xlarge" name="alert_low_xlarge" value="{{ $settings['energy'][0]->low_xlarge ?? 12 }}" placeholder="Low %">
                </div>
                <div class="settings-field">
                    <label for="alert_medium_xlarge">Medium %</label>
                    <input type="number" id="alert_medium_xlarge" name="alert_medium_xlarge" value="{{ $settings['energy'][1]->medium_xlarge ?? 18 }}" placeholder="Medium %">
                </div>
                <div class="settings-field">
                    <label for="alert_high_xlarge">High %</label>
                    <input type="number" id="alert_high_xlarge" name="alert_high_xlarge" value="{{ $settings['energy'][2]->high_xlarge ?? 28 }}" placeholder="High %">
                </div>
            </div>
        </div>
        <div class="settings-grid">
            <div class="settings-field">
                <label for="auto_log_incident">Auto Log Incident</label>
                <select id="auto_log_incident" name="auto_log_incident"><option value="1">Auto Log YES</option><option value="0">NO</option></select>
            </div>
        </div>
    </div>
</div>
</div>

<!-- FACILITY -->
<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">Facility Settings <span class="settings-arrow">▶</span></div>
<div class="settings-body">
<div class="settings-grid">
    <div class="settings-field">
        <label for="facility_image_size">Image Size MB</label>
        <input type="number" id="facility_image_size" name="facility_image_size" value="{{ $settings['facility'][0]->value ?? 5 }}" placeholder="Image Size MB">
    </div>
    <div class="settings-field">
        <label for="allowed_image_types">Allowed Image Types</label>
        <input type="text" id="allowed_image_types" name="allowed_image_types" value="{{ $settings['facility'][1]->value ?? 'jpg,png' }}">
    </div>
    <div class="settings-field">
        <label for="default_facility_status">Default Facility Status</label>
        <select id="default_facility_status" name="default_facility_status"><option>active</option><option>inactive</option></select>
    </div>
</div>
</div>
</div>

<!-- EMAIL -->
<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">Email & Notifications <span class="settings-arrow">▶</span></div>
<div class="settings-body">
<div class="settings-grid">
    <div class="settings-field">
        <label for="mail_host">Mail Host</label>
        <input type="text" id="mail_host" name="mail_host" value="{{ $settings['email'][1]->value ?? '' }}">
    </div>
    <div class="settings-field">
        <label for="mail_port">Mail Port</label>
        <input type="number" id="mail_port" name="mail_port" value="{{ $settings['email'][2]->value ?? 587 }}">
    </div>
    <div class="settings-field">
        <label for="enable_email_notifications">Email Notifications</label>
        <select id="enable_email_notifications" name="enable_email_notifications"><option value="1">ON</option><option value="0">OFF</option></select>
    </div>
</div>
</div>
</div>

<!-- REPORTS -->
<div class="settings-card">
<div class="settings-header" onclick="toggleCard(this)">Reports & Audit Trail <span class="settings-arrow">▶</span></div>
<div class="settings-body">
<div class="settings-grid">
    <div class="settings-field">
        <label for="enable_audit_logs">Enable Audit Logs</label>
        <select id="enable_audit_logs" name="enable_audit_logs"><option value="1">YES</option><option value="0">NO</option></select>
    </div>
    <div class="settings-field">
        <label for="retention_period">Retention Period (months)</label>
        <input type="number" id="retention_period" name="retention_period" value="{{ $settings['reports'][2]->value ?? 12 }}">
    </div>
    <div class="settings-field">
        <label for="export_format">Export Format</label>
        <select id="export_format" name="export_format"><option>pdf</option><option>excel</option></select>
    </div>
</div>
</div>
</div>

<!-- SAVE -->
<div style="text-align:right;margin-top:32px;">
<button type="submit" style="padding:14px 46px;border-radius:14px;border:none;font-weight:700;background:#2563eb;color:#fff;">
Save Settings
</button>
</div>

</form>
</div>

<script>
function toggleCard(header){
    // Toggle only the clicked card, allow multiple open
    header.parentElement.classList.toggle('open');
}
</script>
@endsection
