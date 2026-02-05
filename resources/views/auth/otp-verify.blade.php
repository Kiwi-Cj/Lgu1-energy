@extends('layouts.qc-admin')
@section('title', 'Verify OTP')
@section('content')
<div style="max-width:400px;margin:40px auto;background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;font-size:1.3rem;color:#3762c8;margin-bottom:1.5rem;">Verify OTP</h2>
    <form method="POST" action="{{ route('otp.verify.submit') }}" id="verifyOtpForm" autocomplete="off">
        @csrf
        <div style="margin-bottom:18px;">
            <label for="email" style="font-weight:500;">Email</label>
            <input type="email" name="email" id="email" class="form-control" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
        </div>
        <div style="margin-bottom:18px;">
            <label for="otp" style="font-weight:500;">OTP Code</label>
            <input type="text" name="otp" id="otp" class="form-control" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
        </div>
        <button type="submit" class="btn btn-primary" id="btnVerifyOtp" style="background:#2563eb;color:#fff;padding:10px 32px;border-radius:8px;font-weight:600;font-size:1.1rem;width:100%;position:relative;">
            <span id="btnVerifyOtpText">Verify OTP</span>
            <span id="btnVerifyOtpLoading" style="display:none;position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);">
                <svg width="22" height="22" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="#fff"><g fill="none" fill-rule="evenodd" stroke-width="4"><circle cx="22" cy="22" r="18" stroke-opacity=".3"/><path d="M40 22c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 22 22" to="360 22 22" dur="1s" repeatCount="indefinite"/></path></g></svg>
            </span>
        </button>
    </form>
    @if(session('success'))
        <div style="margin-top:18px;color:#22c55e;font-weight:600;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="margin-top:18px;color:#e11d48;font-weight:600;">
            {{ $errors->first() }}
        </div>
    @endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verifyOtpForm');
    const btn = document.getElementById('btnVerifyOtp');
    const btnText = document.getElementById('btnVerifyOtpText');
    const btnLoading = document.getElementById('btnVerifyOtpLoading');
    if (form) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = '';
        });
    }
});
</script>
@endsection
