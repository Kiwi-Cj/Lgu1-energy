<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Energy System</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #0f172a;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(rgba(15, 23, 42, 0.58), rgba(15, 23, 42, 0.62)),
                        url('{{ asset("img/cityhall.jpeg") }}') center center / cover no-repeat;
            filter: blur(6px);
            transform: scale(1.08);
            z-index: -1;
        }
        .card {
            width: 100%;
            max-width: 460px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 18px 36px rgba(2, 6, 23, 0.45);
            padding: 28px 24px;
            text-align: center;
        }
        .icon {
            width: 62px;
            height: 62px;
            border-radius: 16px;
            margin: 0 auto 14px;
            background: #dbeafe;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: 700;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 1.45rem;
            color: #0f172a;
            font-weight: 800;
        }
        p {
            margin: 0 0 16px;
            color: #475569;
            line-height: 1.55;
        }
        .support-box {
            background: #f8fafc;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 18px;
            text-align: left;
        }
        .support-item {
            margin: 8px 0;
            font-size: 0.95rem;
            color: #1e293b;
        }
        .support-item strong {
            min-width: 66px;
            display: inline-block;
            color: #334155;
        }
        .support-item a {
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
        }
        .support-item a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.92rem;
            border: 1px solid #2563eb;
            background: #2563eb;
            color: #fff;
        }
        .btn:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }
        .status {
            margin-bottom: 12px;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #166534;
            background: #dcfce7;
            border: 1px solid #86efac;
        }
    </style>
</head>
<body>
    @php
        $supportEmail = env('ADMIN_SUPPORT_EMAIL', 'support@energysystem.com');
        $supportPhone = env('ADMIN_SUPPORT_PHONE', '+1 (555) 123-4567');
    @endphp

    <div class="card">
        <div class="icon">?</div>
        <h1>Forgot Password</h1>

        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <p>
            Password reset by self-service is currently disabled.<br>
            Please contact your system administrator to reset your account password.
        </p>

        <div class="support-box">
            <div class="support-item">
                <strong>Email:</strong>
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
            </div>
            <div class="support-item">
                <strong>Phone:</strong>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}">{{ $supportPhone }}</a>
            </div>
        </div>

        <a class="btn" href="{{ route('login') }}">Back to Login</a>
    </div>
</body>
</html>
