<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password | {{ $systemName }}</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --indigo: #6366f1;
            --sky: #0ea5e9;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbe4f0;
            --surface: rgba(255, 255, 255, 0.96);
            --success: #15803d;
            --success-soft: #f0fdf4;
            --danger: #be123c;
            --danger-soft: #fff1f2;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: '';
            position: fixed;
            inset: -18px;
            z-index: -2;
            background:
                linear-gradient(120deg, rgba(15, 23, 42, 0.88), rgba(30, 64, 175, 0.58)),
                url('{{ asset("img/cityhall.jpeg") }}') center / cover no-repeat;
            filter: blur(7px);
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 18%, rgba(59, 130, 246, 0.28), transparent 34%),
                radial-gradient(circle at 88% 82%, rgba(99, 102, 241, 0.22), transparent 30%);
        }

        button,
        input {
            font: inherit;
        }

        .site-header {
            height: 76px;
            padding: 0 clamp(20px, 5vw, 72px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            font-size: 1.02rem;
            font-weight: 700;
        }

        .brand img {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.32);
            border-radius: 13px;
            box-shadow: 0 8px 20px rgba(2, 6, 23, 0.22);
        }

        .brand span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            color: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.06);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: background 160ms ease, color 160ms ease;
        }

        .home-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.13);
        }

        .home-link svg,
        .back-link svg,
        .button svg,
        .notice svg,
        .security-note svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .page {
            width: 100%;
            min-height: calc(100vh - 76px);
            display: grid;
            place-items: center;
            padding: 36px 20px;
        }

        .recovery-shell {
            width: min(100%, 1040px);
            display: grid;
            grid-template-columns: minmax(260px, 0.74fr) minmax(520px, 1.35fr);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.48);
            border-radius: 28px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(2, 6, 23, 0.42);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .recovery-aside {
            position: relative;
            overflow: hidden;
            padding: 42px 34px;
            color: #fff;
            background:
                radial-gradient(circle at 100% 0, rgba(125, 211, 252, 0.28), transparent 42%),
                linear-gradient(155deg, #1e40af 0%, #2563eb 48%, #4f46e5 100%);
        }

        .recovery-aside::after {
            content: '';
            position: absolute;
            right: -80px;
            bottom: -90px;
            width: 250px;
            height: 250px;
            border: 42px solid rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .aside-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            margin-bottom: 28px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 14px 30px rgba(30, 64, 175, 0.28);
        }

        .aside-icon svg {
            width: 28px;
            height: 28px;
        }

        .eyebrow {
            margin: 0 0 10px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .recovery-aside h2 {
            max-width: 300px;
            margin: 0;
            font-size: clamp(1.65rem, 3vw, 2.2rem);
            line-height: 1.15;
            letter-spacing: -0.04em;
        }

        .aside-copy {
            position: relative;
            z-index: 1;
            margin: 16px 0 30px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .assurance-list {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 16px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .assurance-list li {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .assurance-list .check {
            width: 21px;
            height: 21px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            margin-top: 1px;
            border-radius: 50%;
            color: #dbeafe;
            background: rgba(255, 255, 255, 0.15);
            font-size: 0.72rem;
            font-weight: 800;
        }

        .recovery-card {
            padding: 36px clamp(24px, 4vw, 46px) 30px;
            background: rgba(255, 255, 255, 0.98);
        }

        .card-heading {
            margin-bottom: 22px;
        }

        .card-heading h1 {
            margin: 0 0 7px;
            color: var(--ink);
            font-size: clamp(1.45rem, 3vw, 1.85rem);
            line-height: 1.2;
            letter-spacing: -0.035em;
        }

        .card-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .stepper {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 34px;
            margin-bottom: 22px;
        }

        .stepper::before {
            content: '';
            position: absolute;
            top: 17px;
            left: 35px;
            right: 35px;
            height: 2px;
            background: #e2e8f0;
        }

        .stepper::after {
            content: '';
            position: absolute;
            top: 17px;
            left: 35px;
            width: calc(50% - 35px);
            height: 2px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 220ms ease;
        }

        .stepper.is-advanced::after,
        .stepper.is-complete::after {
            transform: scaleX(1);
        }

        .step {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .step:last-child {
            justify-content: flex-end;
        }

        .step-number {
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border: 2px solid #dbe4f0;
            border-radius: 50%;
            color: #94a3b8;
            background: #fff;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .step.active .step-number {
            color: #fff;
            border-color: var(--primary);
            background: var(--primary);
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1);
        }

        .step.complete .step-number {
            color: #fff;
            border-color: var(--success);
            background: var(--success);
        }

        .step-label {
            min-width: 0;
            display: grid;
            gap: 2px;
        }

        .step-label strong {
            color: #334155;
            font-size: 0.78rem;
        }

        .step-label span {
            color: #94a3b8;
            font-size: 0.68rem;
            white-space: nowrap;
        }

        .notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            color: #166534;
            background: var(--success-soft);
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .notice.error {
            color: #9f1239;
            border-color: #fecdd3;
            background: var(--danger-soft);
        }

        .form-section {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.035);
        }

        .form-section + .form-section {
            margin-top: 14px;
        }

        .form-section.locked {
            background: #f8fafc;
        }

        .section-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 15px;
        }

        .section-title {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .section-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 10px;
            color: var(--primary);
            background: #eff6ff;
        }

        .section-icon svg {
            width: 18px;
            height: 18px;
        }

        .section-title h2 {
            margin: 1px 0 3px;
            font-size: 0.93rem;
            line-height: 1.3;
        }

        .section-title p {
            margin: 0;
            color: var(--muted);
            font-size: 0.72rem;
            line-height: 1.45;
        }

        .state-pill {
            flex: 0 0 auto;
            padding: 5px 9px;
            border: 1px solid #dbe4f0;
            border-radius: 999px;
            color: #64748b;
            background: #f8fafc;
            font-size: 0.67rem;
            font-weight: 700;
        }

        .state-pill.ready {
            color: #1d4ed8;
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .field label {
            display: block;
            margin: 0 0 7px 2px;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > svg {
            position: absolute;
            top: 50%;
            left: 13px;
            width: 17px;
            height: 17px;
            color: #94a3b8;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .field input {
            width: 100%;
            min-height: 46px;
            padding: 11px 13px 11px 40px;
            color: var(--ink);
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            font-size: 0.84rem;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .field input::placeholder {
            color: #a3afc2;
        }

        .field input:hover:not(:disabled) {
            border-color: #94a3b8;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.11);
        }

        .field input:disabled {
            color: #94a3b8;
            border-color: #e2e8f0;
            background: #f1f5f9;
            cursor: not-allowed;
        }

        .field.has-error input {
            border-color: #fb7185;
        }

        .field-error {
            margin: 6px 0 0 2px;
            color: var(--danger);
            font-size: 0.7rem;
            line-height: 1.4;
        }

        .action-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            margin-top: 14px;
        }

        .help-text {
            margin: 0;
            color: var(--muted);
            font-size: 0.7rem;
            line-height: 1.5;
        }

        .button {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 17px;
            border: 1px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.77rem;
            font-weight: 800;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .button:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .button:focus-visible,
        .back-link:focus-visible,
        .home-link:focus-visible {
            outline: 3px solid rgba(14, 165, 233, 0.4);
            outline-offset: 2px;
        }

        .button-secondary {
            color: #1d4ed8;
            border-color: #bfdbfe;
            background: #eff6ff;
        }

        .button-secondary:hover:not(:disabled) {
            background: #dbeafe;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .button-primary {
            color: #fff;
            background: linear-gradient(110deg, var(--primary), var(--indigo), var(--sky));
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        .button-primary:hover:not(:disabled) {
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.27);
        }

        .button:disabled {
            color: #94a3b8;
            border-color: #e2e8f0;
            background: #e9eef5;
            box-shadow: none;
            cursor: not-allowed;
        }

        .button.is-loading {
            pointer-events: none;
            opacity: 0.78;
        }

        .spinner {
            width: 16px;
            height: 16px;
            display: none;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 700ms linear infinite;
        }

        .button.is-loading .spinner {
            display: inline-block;
        }

        .otp-layout {
            display: grid;
            grid-template-columns: minmax(170px, 0.8fr) minmax(210px, 1.2fr);
            align-items: end;
            gap: 12px;
        }

        .otp-layout .button {
            width: 100%;
            min-height: 46px;
        }

        .field input.otp-input {
            padding-left: 14px;
            text-align: center;
            letter-spacing: 0.34em;
            font-size: 1rem;
            font-weight: 800;
        }

        .otp-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.68rem;
            line-height: 1.45;
        }

        .otp-meta strong {
            color: #475569;
        }

        .security-note {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 18px 2px 0;
            color: var(--muted);
            font-size: 0.68rem;
            line-height: 1.45;
        }

        .security-note svg {
            color: var(--success);
        }

        .back-link {
            width: max-content;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 20px auto 0;
            padding: 8px 10px;
            color: #475569;
            border-radius: 9px;
            text-decoration: none;
            font-size: 0.76rem;
            font-weight: 700;
            transition: color 160ms ease, background 160ms ease;
        }

        .back-link:hover {
            color: var(--primary-dark);
            background: #eff6ff;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 860px) {
            .recovery-shell {
                width: min(100%, 640px);
                grid-template-columns: 1fr;
            }

            .recovery-aside {
                display: none;
            }
        }

        @media (max-width: 560px) {
            .site-header {
                height: 66px;
                padding: 0 16px;
            }

            .brand {
                max-width: calc(100% - 54px);
                font-size: 0.9rem;
            }

            .brand img {
                width: 38px;
                height: 38px;
            }

            .home-link {
                width: 40px;
                height: 40px;
                padding: 0;
                justify-content: center;
            }

            .home-link span {
                display: none;
            }

            .page {
                min-height: calc(100vh - 66px);
                padding: 18px 12px;
                place-items: start center;
            }

            .recovery-shell {
                border-radius: 21px;
            }

            .recovery-card {
                padding: 26px 17px 22px;
            }

            .stepper {
                gap: 18px;
            }

            .step-label span {
                display: none;
            }

            .fields-grid,
            .otp-layout,
            .action-row {
                grid-template-columns: 1fr;
            }

            .action-row .button {
                width: 100%;
            }

            .section-heading {
                gap: 8px;
            }

            .form-section {
                padding: 15px;
            }

            .otp-meta {
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>
<body>
    @include('layouts.partials.flash-toast')

    @php
        $otpPending = session('password_reset_otp_pending');
        $otpReady = is_array($otpPending);
        $resetLinkSent = session()->has('status');
        $otpExpiry = max(1, (int) config('otp.expire_minutes', 5));
    @endphp

    <header class="site-header">
        <a class="brand" href="{{ url('/') }}" aria-label="Go to {{ $systemName }} home page">
            <img src="{{ $systemLogoUrl }}" alt="">
            <span>{{ $systemName }}</span>
        </a>
        <a class="home-link" href="{{ url('/') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 11.5 12 4l9 7.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.5 10v9h13v-9M9 19v-5h6v5" stroke-linejoin="round"/>
            </svg>
            <span>Home</span>
        </a>
    </header>

    <main class="page">
        <div class="recovery-shell">
            <aside class="recovery-aside" aria-label="Account recovery information">
                <div class="aside-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="4" y="10" width="16" height="11" rx="3"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke-linecap="round"/>
                        <path d="M12 14.5v2" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="eyebrow">Secure recovery</p>
                <h2>Regain access to your account.</h2>
                <p class="aside-copy">
                    Verify your registered account details and the one-time code sent to your email.
                </p>
                <ul class="assurance-list">
                    <li><span class="check">✓</span><span>Your account details are checked before a code is sent.</span></li>
                    <li><span class="check">✓</span><span>The 6-digit OTP is time-limited for added protection.</span></li>
                    <li><span class="check">✓</span><span>A secure reset link is sent only after verification.</span></li>
                </ul>
            </aside>

            <section class="recovery-card" aria-labelledby="recovery-title">
                <div class="card-heading">
                    <h1 id="recovery-title">Forgot your password?</h1>
                    <p>No worries. Complete these two quick steps to receive a secure password reset link.</p>
                </div>

                <div class="stepper {{ $resetLinkSent ? 'is-complete' : ($otpReady ? 'is-advanced' : '') }}" aria-label="Password recovery progress">
                    <div class="step {{ $otpReady || $resetLinkSent ? 'complete' : 'active' }}">
                        <span class="step-number">{{ $otpReady || $resetLinkSent ? '✓' : '1' }}</span>
                        <span class="step-label">
                            <strong>Account check</strong>
                            <span>Confirm your identity</span>
                        </span>
                    </div>
                    <div class="step {{ $resetLinkSent ? 'complete' : ($otpReady ? 'active' : '') }}">
                        <span class="step-number">{{ $resetLinkSent ? '✓' : '2' }}</span>
                        <span class="step-label">
                            <strong>Verify OTP</strong>
                            <span>Send reset link</span>
                        </span>
                    </div>
                </div>

                @if(session('otp_status'))
                    <div class="notice" role="status" aria-live="polite">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ session('otp_status') }}</span>
                    </div>
                @endif

                @if(session('status'))
                    <div class="notice" role="status" aria-live="polite">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="notice error" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7.5v5M12 16.5h.01" stroke-linecap="round"/>
                        </svg>
                        <span>{{ $errors->first('otp') ?: ($errors->first('username') ?: ($errors->first('email') ?: 'Unable to process your request. Please try again.')) }}</span>
                    </div>
                @endif

                <form id="recoveryForm" method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <section class="form-section" aria-labelledby="account-check-title">
                        <div class="section-heading">
                            <div class="section-title">
                                <span class="section-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="8" r="3.5"/>
                                        <path d="M5.5 20a6.5 6.5 0 0 1 13 0" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <h2 id="account-check-title">Account details</h2>
                                    <p>Enter the username and email registered to your account.</p>
                                </span>
                            </div>
                            <span class="state-pill {{ $otpReady ? 'ready' : '' }}">{{ $otpReady ? 'OTP sent' : 'Step 1' }}</span>
                        </div>

                        <div class="fields-grid">
                            <div class="field {{ $errors->has('username') ? 'has-error' : '' }}">
                                <label for="username">Username</label>
                                <div class="input-wrap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <circle cx="12" cy="8" r="3.5"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/>
                                    </svg>
                                    <input
                                        id="username"
                                        type="text"
                                        name="username"
                                        value="{{ old('username', $otpPending['username'] ?? '') }}"
                                        placeholder="Enter your username"
                                        autocomplete="username"
                                        autocapitalize="none"
                                        spellcheck="false"
                                        required
                                        autofocus
                                        aria-describedby="username-error"
                                    >
                                </div>
                                @error('username')
                                    <p class="field-error" id="username-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                                <label for="email">Email address</label>
                                <div class="input-wrap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m5 7 7 5 7-5" stroke-linejoin="round"/>
                                    </svg>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $otpPending['email'] ?? '') }}"
                                        placeholder="name@example.com"
                                        autocomplete="email"
                                        autocapitalize="none"
                                        spellcheck="false"
                                        required
                                        aria-describedby="email-error"
                                    >
                                </div>
                                @error('email')
                                    <p class="field-error" id="email-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="action-row">
                            <p class="help-text">We will send a 6-digit verification code to your registered email.</p>
                            <button class="button button-secondary" type="submit" name="submit_action" value="send_otp" data-loading-text="Sending code...">
                                <span class="spinner" aria-hidden="true"></span>
                                <span class="button-label">{{ $otpReady ? 'Send new code' : 'Send OTP code' }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m5 12 14-7-4 14-3-6-7-1Z" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </section>

                    <section class="form-section {{ $otpReady ? '' : 'locked' }}" aria-labelledby="verify-otp-title">
                        <div class="section-heading">
                            <div class="section-title">
                                <span class="section-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M12 14.5v2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>
                                    <h2 id="verify-otp-title">Verify one-time code</h2>
                                    <p>{{ $otpReady ? 'Enter the code from your email to continue.' : 'Request an OTP code first to unlock this step.' }}</p>
                                </span>
                            </div>
                            <span class="state-pill {{ $otpReady ? 'ready' : '' }}">{{ $otpReady ? 'Ready' : 'Locked' }}</span>
                        </div>

                        <div class="otp-layout">
                            <div class="field {{ $errors->has('otp') ? 'has-error' : '' }}">
                                <label for="otp">6-digit OTP code</label>
                                <input
                                    id="otp"
                                    class="otp-input"
                                    type="text"
                                    name="otp"
                                    value="{{ old('otp') }}"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    placeholder="••••••"
                                    {{ $otpReady ? 'required' : 'disabled' }}
                                    aria-describedby="otp-error otp-help"
                                >
                                @error('otp')
                                    <p class="field-error" id="otp-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                class="button button-primary"
                                type="submit"
                                name="submit_action"
                                value="verify_otp"
                                data-loading-text="Verifying..."
                                {{ $otpReady ? '' : 'disabled' }}
                            >
                                <span class="spinner" aria-hidden="true"></span>
                                <span class="button-label">Verify &amp; send reset link</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M5 12.5 9.2 17 19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="otp-meta" id="otp-help">
                            <span>Code expires in <strong>{{ $otpExpiry }} {{ $otpExpiry === 1 ? 'minute' : 'minutes' }}</strong>.</span>
                            <span>Did not receive it? Use <strong>Send new code</strong> above after 30 seconds.</span>
                        </div>
                    </section>

                    <div class="security-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>For your security, never share your OTP or password reset link with anyone.</span>
                    </div>

                    <a class="back-link" href="{{ route('login') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to sign in
                    </a>
                </form>
            </section>
        </div>
    </main>

    <script>
        (function () {
            var form = document.getElementById('recoveryForm');
            var otpInput = document.getElementById('otp');

            if (otpInput) {
                otpInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                });
            }

            if (!form) return;

            form.addEventListener('submit', function (event) {
                var button = event.submitter;
                if (!button || button.disabled || !form.checkValidity()) return;

                var label = button.querySelector('.button-label');
                button.classList.add('is-loading');
                button.setAttribute('aria-busy', 'true');

                if (label && button.dataset.loadingText) {
                    label.textContent = button.dataset.loadingText;
                }

                form.querySelectorAll('button[type="submit"]').forEach(function (submitButton) {
                    if (submitButton !== button) submitButton.disabled = true;
                });
            });
        })();
    </script>
</body>
</html>
