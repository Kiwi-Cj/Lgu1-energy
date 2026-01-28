@extends('layouts.qc-admin')
@section('title', 'Request OTP')
@section('content')
<div style="max-width:400px;margin:40px auto;background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;font-size:1.3rem;color:#3762c8;margin-bottom:1.5rem;">Request OTP</h2>
    <form method="POST" action="{{ route('otp.send') }}">
        @csrf
        <div style="margin-bottom:18px;">
            <label for="email" style="font-weight:500;">Email</label>
            <input type="email" name="email" id="email" class="form-control" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
        </div>
        <button type="submit" class="btn btn-primary" style="background:#2563eb;color:#fff;padding:10px 32px;border-radius:8px;font-weight:600;font-size:1.1rem;width:100%;">Send OTP</button>
    </form>
    @if(session('success'))
        <div style="margin-top:18px;color:#22c55e;font-weight:600;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="margin-top:18px;color:#e11d48;font-weight:600;">
            {{ $errors->first() }}
        </div>
    @endif
</div>
@endsection
