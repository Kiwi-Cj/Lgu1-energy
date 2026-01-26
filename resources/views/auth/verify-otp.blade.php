<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="{{ asset('css/style - Copy.css') }}">
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h2 class="title">OTP Verification</h2>
            <p class="subtitle">Enter the OTP sent to your email to complete registration.</p>
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form method="POST" action="{{ route('verify.otp') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user_id }}">
                <div class="input-box">
                    <label>OTP Code</label>
                    <input type="text" name="otp_code" maxlength="10" required autofocus>
                </div>
                <button class="btn-primary" type="submit">Verify OTP</button>
            </form>
        </div>
    </div>
</body>
</html>
