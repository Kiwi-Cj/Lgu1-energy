@extends('layouts.qc-admin')
@section('title', 'Edit Profile')

@section('content')
@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@if(session('success'))
<div id="successAlert" class="profile-alert profile-alert-success">
    <i class="fa fa-check-circle"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div id="errorAlert" class="profile-alert profile-alert-error">
    <i class="fa fa-times-circle"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

<div class="profile-edit-page">
    <a href="{{ route('profile.show') }}" class="profile-back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Profile
    </a>

    <div class="profile-edit-header">
        <img src="{{ $user?->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="Profile Photo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div class="profile-header-avatar-fallback" style="display:none;">
            {{ strtoupper(substr(trim((string) ($user->full_name ?? $user->name ?? 'U')), 0, 1)) }}
        </div>
        <div>
            <h1>Account Settings</h1>
            <p>Update your profile photo and password.</p>
        </div>
    </div>

    <div class="profile-edit-grid">
        <section class="profile-edit-card">
            <div class="profile-edit-card-head">
                <h3>Profile Information</h3>
                <p>Photo only. Name and email are read-only on this page.</p>
            </div>
            @include('profile.partials.update-profile-information-form', ['user' => $user])
        </section>

        <section class="profile-edit-card">
            <div class="profile-edit-card-head">
                <h3>Password and Security</h3>
                <p>Use a strong password and change it regularly.</p>
            </div>
            @include('profile.partials.update-password-form')
        </section>

    </div>
</div>

<style>
.profile-edit-page {
    max-width: 980px;
    margin: 26px auto 42px;
}

.profile-alert {
    position: fixed;
    top: 22px;
    right: 22px;
    z-index: 99999;
    min-width: 280px;
    max-width: 420px;
    border-radius: 12px;
    padding: 14px 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
}

.profile-alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.profile-alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.profile-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #475569;
    font-weight: 600;
    margin-bottom: 14px;
}

.profile-back-link:hover {
    color: #1d4ed8;
}

.profile-edit-header {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #ffffff;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
}

.profile-edit-header img {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #dbeafe;
}

.profile-header-avatar-fallback {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.5rem;
    color: #1e3a8a;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border: 3px solid #dbeafe;
}

.profile-edit-header h1 {
    margin: 0;
    font-size: 1.5rem;
    color: #0f172a;
}

.profile-edit-header p {
    margin: 4px 0 0;
    color: #64748b;
}

.profile-edit-grid {
    display: grid;
    gap: 14px;
}

.profile-edit-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #ffffff;
    overflow: hidden;
}

.profile-edit-card-head {
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.profile-edit-card-head h3 {
    margin: 0;
    color: #0f172a;
    font-size: 1.04rem;
}

.profile-edit-card-head p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.9rem;
}

.profile-edit-card > form,
.profile-edit-card > section {
    padding: 18px;
}

.profile-edit-card .profile-form-wrap {
    padding: 18px;
}

.profile-edit-card label {
    display: block;
    font-size: 0.86rem;
    color: #334155;
    margin-bottom: 6px;
}

.profile-edit-card input[type="text"],
.profile-edit-card input[type="email"],
.profile-edit-card input[type="password"],
.profile-edit-card input[type="file"] {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    color: #0f172a;
    padding: 10px 12px;
}

.profile-edit-card input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.profile-edit-card button {
    border: 0;
    border-radius: 10px;
    padding: 10px 14px;
    font-weight: 700;
    cursor: pointer;
}

.profile-edit-card button[type="submit"] {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
}

body.dark-mode .profile-alert-success {
    background: #14532d;
    color: #dcfce7;
    border-color: #166534;
}

body.dark-mode .profile-alert-error {
    background: #7f1d1d;
    color: #fee2e2;
    border-color: #991b1b;
}

body.dark-mode .profile-back-link {
    color: #93c5fd;
}

body.dark-mode .profile-back-link:hover {
    color: #bfdbfe;
}

body.dark-mode .profile-edit-header,
body.dark-mode .profile-edit-card {
    background: #0f172a;
    border-color: #334155;
}

body.dark-mode .profile-edit-header img {
    border-color: #1e3a8a;
}

body.dark-mode .profile-header-avatar-fallback {
    background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
    border-color: #1e3a8a;
    color: #dbeafe;
}

body.dark-mode .profile-edit-header h1,
body.dark-mode .profile-edit-card-head h3,
body.dark-mode .profile-edit-card label {
    color: #e2e8f0;
}

body.dark-mode .profile-edit-header p,
body.dark-mode .profile-edit-card-head p {
    color: #94a3b8;
}

body.dark-mode .profile-edit-card-head {
    background: #111827;
    border-bottom-color: #334155;
}

body.dark-mode .profile-edit-card input[type="text"],
body.dark-mode .profile-edit-card input[type="email"],
body.dark-mode .profile-edit-card input[type="password"],
body.dark-mode .profile-edit-card input[type="file"] {
    background: #0b1220;
    color: #e2e8f0;
    border-color: #334155;
}

@media (max-width: 720px) {
    .profile-edit-header {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<script>
window.addEventListener('DOMContentLoaded', function () {
    var success = document.getElementById('successAlert');
    var error = document.getElementById('errorAlert');
    if (success) setTimeout(function () { success.remove(); }, 3000);
    if (error) setTimeout(function () { error.remove(); }, 3000);
});
</script>
@endsection
