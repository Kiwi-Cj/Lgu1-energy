@extends('layouts.qc-admin')
@section('title', 'Edit Profile')

@section('content')
@php
    $user = auth()->user();
    $profileDisplayName = $user?->full_name ?? $user?->name ?? $user?->username ?? 'User';
    $profileInitials = collect(preg_split('/\s+/', trim((string) $profileDisplayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr((string) $part, 0, 1)))
        ->implode('');
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

    <div class="profile-edit-report-card">
    <div class="profile-edit-header">
        <img src="{{ $user?->profile_photo_url ?? asset('img/default-avatar.png') }}" alt="{{ $profileDisplayName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div class="profile-header-avatar-fallback" style="display:none;">
            {{ $profileInitials ?: 'U' }}
        </div>
        <div>
            <div class="profile-edit-eyebrow"><i class="fa-solid fa-user-gear"></i> Profile preferences</div>
            <h1>Account Settings</h1>
            <p>Update your profile photo and password.</p>
        </div>
    </div>

    <div class="profile-edit-grid">
        <section class="profile-edit-card">
            <div class="profile-edit-card-head">
                <span class="profile-card-head-icon"><i class="fa-solid fa-image"></i></span>
                <div><h3>Profile Information</h3>
                <p>Photo only. Name and email are read-only on this page.</p>
                </div>
            </div>
            @include('profile.partials.update-profile-information-form', ['user' => $user])
        </section>

        <section class="profile-edit-card">
            <div class="profile-edit-card-head">
                <span class="profile-card-head-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <div><h3>Password and Security</h3>
                <p>Use a strong password and change it regularly.</p>
                </div>
            </div>
            @include('profile.partials.update-password-form')
        </section>

    </div>
    </div>
</div>

<style>
.profile-edit-page {
    max-width: 1080px;
    margin: 8px auto 36px;
}

.profile-edit-report-card {
    padding: 22px;
    border: 1px solid #dce6f2;
    border-radius: 22px;
    background: linear-gradient(155deg, #fff 0%, #f8fbff 100%);
    box-shadow: 0 16px 38px rgba(15, 23, 42, .07);
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
    margin: 0 0 12px 4px;
    font-size: .86rem;
}

.profile-back-link:hover {
    color: #1d4ed8;
}

.profile-edit-header {
    position: relative;
    min-height: 132px;
    overflow: hidden;
    border: 1px solid #d7e3f2;
    border-radius: 17px;
    background:
        radial-gradient(circle at 92% 0%, rgba(37,99,235,.12), transparent 33%),
        linear-gradient(135deg, #fff, #f7faff);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 13px;
    box-shadow: 0 8px 22px rgba(15,23,42,.045);
}

.profile-edit-header::before {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(90deg, #1d4ed8, #60a5fa);
    content: '';
}

.profile-edit-header img {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    outline: 2px solid #bfdbfe;
    box-shadow: 0 7px 18px rgba(30,64,175,.14);
}

.profile-header-avatar-fallback {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.25rem;
    color: #1e3a8a;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border: 4px solid #fff;
    outline: 2px solid #bfdbfe;
    box-shadow: 0 7px 18px rgba(30,64,175,.14);
}

.profile-edit-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 5px;
    color: #2563eb;
    font-size: .64rem;
    font-weight: 900;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.profile-edit-header h1 {
    margin: 0;
    font-size: 1.42rem;
    letter-spacing: -.03em;
    color: #0f172a;
}

.profile-edit-header p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: .86rem;
}

.profile-edit-grid {
    display: grid;
    gap: 14px;
}

.profile-edit-card {
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    background: #ffffff;
    overflow: hidden;
}

.profile-edit-card-head {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.profile-edit-card-head h3 {
    margin: 0;
    color: #0f172a;
    font-size: .94rem;
}

.profile-edit-card-head p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 0.78rem;
}

.profile-card-head-icon {
    display: inline-flex;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eaf1ff;
    color: #2563eb;
    font-size: .78rem;
}

.profile-edit-card > form,
.profile-edit-card > section {
    padding: 18px;
}

.profile-edit-card .profile-form-wrap {
    padding: 16px;
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
    padding: 9px 11px;
    font-size: .82rem;
}

.profile-edit-card input[type="file"]::file-selector-button {
    margin: -5px 10px -5px -6px;
    padding: 7px 11px;
    border: 0;
    border-radius: 8px;
    background: #eaf1ff;
    color: #1d4ed8;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
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
    box-shadow: 0 7px 16px rgba(37,99,235,.2);
}

.profile-edit-card button[type="submit"]:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(37,99,235,.26);
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

body.dark-mode .profile-edit-report-card {
    border-color: #334155;
    background: linear-gradient(155deg, #0f172a, #111827);
    box-shadow: 0 18px 38px rgba(2,6,23,.38);
}

body.dark-mode .profile-edit-header,
body.dark-mode .profile-edit-card {
    background: linear-gradient(145deg, #0f172a, #111827);
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

body.dark-mode .profile-card-head-icon {
    background: #1e3a5f;
    color: #93c5fd;
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

body.dark-mode .profile-edit-card input[type="file"]::file-selector-button {
    background: #1e3a5f;
    color: #bfdbfe;
}

@media (max-width: 720px) {
    .profile-edit-report-card {
        padding: 14px;
        border-radius: 17px;
    }

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
    var photoInput = document.getElementById('profile_photo');
    var photoPreview = document.getElementById('profilePhotoPreview');
    var photoFallback = document.getElementById('profilePhotoFallback');
    var selectedFile = document.getElementById('profileSelectedFile');
    var previewUrl = null;
    if (success) setTimeout(function () { success.remove(); }, 3000);
    if (error) setTimeout(function () { error.remove(); }, 3000);

    if (photoInput && photoPreview && selectedFile) {
        photoInput.addEventListener('change', function () {
            var file = photoInput.files && photoInput.files[0];
            if (!file) {
                selectedFile.innerHTML = '<i class="fa-regular fa-image"></i> No new photo selected';
                return;
            }

            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            photoPreview.src = previewUrl;
            photoPreview.style.display = 'block';
            if (photoFallback) photoFallback.style.display = 'none';
            selectedFile.innerHTML = '<i class="fa-solid fa-circle-check"></i> ';
            selectedFile.appendChild(document.createTextNode(file.name));
        });
    }
});
</script>
@endsection
