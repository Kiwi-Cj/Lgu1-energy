@extends('layouts.qc-admin')
@section('title', 'System Settings')

@section('content')
@php
    $userRole = strtolower(auth()->user()->role ?? '');
@endphp
@if($userRole === 'staff')
    @php
        header('Location: ' . route('modules.energy.index'));
        exit;
    @endphp
@endif
<div style="max-width:1100px;margin:0 auto;">

    <!-- 1️⃣ Page Header -->
    <div style="margin-bottom:24px;">
        <h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;">System Settings</h1>
        <div style="font-size:1.2rem;color:#555;">Configure and manage system preferences and application settings</div>
    </div>

    <!-- 2️⃣ SETTINGS CARDS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin-bottom:2rem;">

        <div style="background:#f5f8ff;padding:24px;border-radius:16px;box-shadow:0 4px 12px rgba(55,98,200,0.12);">
            <h3 style="font-size:1.1rem;font-weight:600;color:#3762c8;margin-bottom:10px;">User & Role Management</h3>
            <p style="font-size:.95rem;color:#555;">Manage system users, assign roles, and control access permissions.</p>
            <a href="{{ url('/modules/users/index') }}" style="display:inline-block;margin-top:10px;padding:8px 16px;background:#3762c8;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;">Go</a>
        </div>

        <div style="background:#f0fdf4;padding:24px;border-radius:16px;box-shadow:0 4px 12px rgba(34,197,94,0.12);">
            <h3 style="font-size:1.1rem;font-weight:600;color:#22c55e;margin-bottom:10px;">Facility Settings</h3>
            <p style="font-size:.95rem;color:#555;">Manage registered facilities and their attributes.</p>
            <a href="{{ url('/modules/facilities/index') }}" style="display:inline-block;margin-top:10px;padding:8px 16px;background:#22c55e;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;">Go</a>
        </div>

        <div style="background:#fff0f3;padding:24px;border-radius:16px;box-shadow:0 4px 12px rgba(225,29,72,0.12);">
            <h3 style="font-size:1.1rem;font-weight:600;color:#e11d48;margin-bottom:10px;">System Preferences</h3>
            <p style="font-size:.95rem;color:#555;">Configure general system settings such as notifications, language, and themes.</p>
            <a href="#" style="display:inline-block;margin-top:10px;padding:8px 16px;background:#e11d48;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;">Go</a>
        </div>

        <div style="background:#f3e8ff;padding:24px;border-radius:16px;box-shadow:0 4px 12px rgba(139,92,246,0.12);">
            <h3 style="font-size:1.1rem;font-weight:600;color:#8b5cf6;margin-bottom:10px;">Audit & Logs</h3>
            <p style="font-size:.95rem;color:#555;">View system logs, track user activity, and audit changes.</p>
            <a href="#" style="display:inline-block;margin-top:10px;padding:8px 16px;background:#8b5cf6;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;">Go</a>
        </div>

    </div>

    <!-- 3️⃣ OPTIONAL INFO -->
    <div style="margin-top:2rem;font-size:.95rem;color:#666;">
        <p>All changes made in the settings panel will affect system-wide behavior. Only users with administrative privileges can modify these settings.</p>
    </div>

</div>
@endsection
