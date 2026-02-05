@extends('layouts.qc-admin')

@section('content')
<div style="max-width: 600px; margin: 40px auto;">
    <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(55,98,200,0.10); padding: 36px 32px 28px 32px;">
        <h2 style="font-size: 2rem; font-weight: 600; color: #222; text-align:center; margin-bottom: 24px;">My Profile</h2>
        <div style="display: flex; gap: 0; border-bottom: 1px solid #e5e7eb; margin-bottom: 24px;">
            <button class="tab-btn" onclick="showTab('info')" style="flex:1;padding:12px 0;border:none;background:#f1f5f9;font-size:1.08rem;font-weight:500;cursor:pointer;">Profile Info</button>
            <button class="tab-btn" onclick="showTab('security')" style="flex:1;padding:12px 0;border:none;background:#f1f5f9;font-size:1.08rem;font-weight:500;cursor:pointer;">Security</button>
            <button class="tab-btn" onclick="showTab('permissions')" style="flex:1;padding:12px 0;border:none;background:#f1f5f9;font-size:1.08rem;font-weight:500;cursor:pointer;">Permissions</button>
            <button class="tab-btn" onclick="showTab('notifications')" style="flex:1;padding:12px 0;border:none;background:#f1f5f9;font-size:1.08rem;font-weight:500;cursor:pointer;">Notifications</button>
            <button class="tab-btn" onclick="showTab('audit')" style="flex:1;padding:12px 0;border:none;background:#f1f5f9;font-size:1.08rem;font-weight:500;cursor:pointer;">Audit Log</button>
        </div>
        <div id="tab-info" class="profile-tab">
            @include('profile.partials.info')
        </div>
        <div id="tab-security" class="profile-tab" style="display:none;">
            @include('profile.partials.security')
        </div>
        <div id="tab-permissions" class="profile-tab" style="display:none;">
            @include('profile.partials.permissions')
        </div>
        <div id="tab-notifications" class="profile-tab" style="display:none;">
            @include('profile.partials.notifications')
        </div>
        <div id="tab-audit" class="profile-tab" style="display:none;">
            @include('profile.partials.audit')
        </div>
    </div>
</div>
<script>
function showTab(tab) {
    document.querySelectorAll('.profile-tab').forEach(function(el){el.style.display='none';});
    document.getElementById('tab-' + tab).style.display = 'block';
    document.querySelectorAll('.tab-btn').forEach(function(btn){btn.style.background='#f1f5f9';});
    event.target.style.background = '#e0e7ff';
}
</script>
@endsection
