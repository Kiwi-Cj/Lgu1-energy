@extends('layouts.qc-admin')

@section('content')
<div style="max-width: 700px; margin: 40px auto 60px;">
    {{-- Header with Avatar and User Info --}}
    <div style="display:flex;align-items:center;gap:24px;margin-bottom:32px;background:#f8fafc;border-radius:16px;padding:28px 32px 22px 32px;box-shadow:0 2px 16px #3762c81a;">
        <img src="{{ auth()->user()->profile_photo_url ?? '/img/default-avatar.png' }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #e0e7ff;box-shadow:0 2px 12px #3762c822;">
        <div>
            <h2 style="margin:0;font-size:1.7rem;font-weight:700;letter-spacing:1px;color:#222;">Edit Profile</h2>
            <div style="margin-top:6px;color:#6b7280;">
                {{ auth()->user()->full_name ?? auth()->user()->name }}
                <span style="background:#e0e7ff;color:#3762c8;padding:4px 12px;border-radius:12px;font-size:0.98rem;font-weight:600;margin-left:10px;">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
        <div style="flex:1;text-align:right;">
            <a href="/profile" style="color:#2563eb;font-weight:600;font-size:1.05rem;text-decoration:underline;">&larr; Back to Profile</a>
        </div>
    </div>

    {{-- Card Container for Forms --}}
    <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(55,98,200,0.10);padding:36px 32px 28px 32px;">
        {{-- Update Profile Info --}}
        <div style="margin-bottom:36px;">
            @include('profile.partials.update-profile-information-form', ['user' => auth()->user()])
        </div>
        <hr style="border:none;border-top:1px solid #e5e7eb;margin:32px 0;">
        {{-- Update Password --}}
        <div style="margin-bottom:36px;">
            @include('profile.partials.update-password-form')
        </div>
        <hr style="border:none;border-top:1px solid #e5e7eb;margin:32px 0;">
        {{-- Delete Account --}}
        <div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
