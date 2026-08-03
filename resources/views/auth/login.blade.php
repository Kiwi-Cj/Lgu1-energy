<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        try {
            if (localStorage.getItem('darkMode') === 'on') {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.style.colorScheme = 'dark';
            }
        } catch (e) {}
    </script>
    <title>Sign In | {{ $systemName }}</title>
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
            --danger: #be123c;
            --danger-soft: #fff1f2;
            --surface: rgba(255, 255, 255, 0.97);
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
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
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
                linear-gradient(120deg, rgba(15, 23, 42, 0.88), rgba(30, 64, 175, 0.56)),
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
                radial-gradient(circle at 16% 18%, rgba(59, 130, 246, 0.3), transparent 34%),
                radial-gradient(circle at 88% 84%, rgba(99, 102, 241, 0.22), transparent 30%);
        }

        button,
        input {
            font: inherit;
        }

        .autofill-trap {
            position: fixed !important;
            top: -10000px !important;
            left: -10000px !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            border: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .site-header {
            height: 76px;
            flex: 0 0 auto;
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

        .header-actions {
            display: flex;
            align-items: center;
            gap: 9px;
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

        .theme-toggle {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #93c5fd;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.06);
            cursor: pointer;
            transition: color 160ms ease, background 160ms ease, transform 160ms ease;
        }

        .theme-toggle:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.13);
            transform: translateY(-1px);
        }

        .theme-toggle svg { width: 19px; height: 19px; }
        .theme-toggle .sun-icon { display: none; }
        html.dark-mode .theme-toggle .moon-icon { display: none; }
        html.dark-mode .theme-toggle .sun-icon { display: block; }

        .home-link svg,
        .input-icon,
        .password-toggle svg,
        .login-button svg,
        .security-note svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .page {
            width: 100%;
            flex: 1 0 auto;
            display: grid;
            place-items: center;
            padding: 36px 20px;
        }

        .login-shell {
            width: min(100%, 980px);
            min-height: 570px;
            display: grid;
            grid-template-columns: minmax(300px, 0.9fr) minmax(440px, 1.1fr);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.48);
            border-radius: 28px;
            background: var(--surface);
            box-shadow: 0 30px 80px rgba(2, 6, 23, 0.44);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .login-aside {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 42px 38px 38px;
            color: #fff;
            background:
                radial-gradient(circle at 100% 0, rgba(125, 211, 252, 0.3), transparent 42%),
                linear-gradient(155deg, #1e40af 0%, #2563eb 48%, #4f46e5 100%);
        }

        .login-aside::before,
        .login-aside::after {
            content: '';
            position: absolute;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .login-aside::before {
            top: -115px;
            right: -115px;
            width: 300px;
            height: 300px;
        }

        .login-aside::after {
            right: -90px;
            bottom: -110px;
            width: 280px;
            height: 280px;
            border-width: 48px;
            border-color: rgba(255, 255, 255, 0.06);
        }

        .aside-content,
        .aside-footer {
            position: relative;
            z-index: 1;
        }

        .aside-logo {
            width: 72px;
            height: 72px;
            display: block;
            margin-bottom: 34px;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.36);
            border-radius: 21px;
            background: #fff;
            box-shadow: 0 16px 32px rgba(30, 64, 175, 0.28);
        }

        .eyebrow {
            margin: 0 0 11px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .login-aside h2 {
            max-width: 330px;
            margin: 0;
            font-size: clamp(1.85rem, 3vw, 2.5rem);
            line-height: 1.13;
            letter-spacing: -0.045em;
        }

        .aside-copy {
            max-width: 330px;
            margin: 18px 0 0;
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.88rem;
            line-height: 1.7;
        }

        .aside-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 28px;
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.72rem;
            line-height: 1.45;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            flex: 0 0 auto;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-radius: 50%;
            background: #86efac;
            box-shadow: 0 0 0 5px rgba(134, 239, 172, 0.1);
        }

        .login-panel {
            display: flex;
            align-items: center;
            padding: 46px clamp(28px, 5vw, 58px);
            background: rgba(255, 255, 255, 0.98);
        }

        .login-content {
            width: 100%;
            max-width: 420px;
            margin: auto;
        }

        .mobile-logo {
            width: 64px;
            height: 64px;
            display: none;
            margin-bottom: 22px;
            object-fit: cover;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.13);
        }

        .login-heading {
            margin-bottom: 26px;
        }

        .login-heading h1 {
            margin: 0 0 8px;
            color: var(--ink);
            font-size: clamp(1.65rem, 3vw, 2rem);
            line-height: 1.2;
            letter-spacing: -0.04em;
        }

        .login-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.55;
        }

        .login-error {
            display: none;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 18px;
            padding: 12px 14px;
            color: #9f1239;
            border: 1px solid #fecdd3;
            border-radius: 12px;
            background: var(--danger-soft);
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .login-error.is-visible {
            display: flex;
        }

        .login-error svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            margin-top: 1px;
        }

        .input-box {
            margin-bottom: 18px;
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 0 2px 8px;
        }

        .label-row label {
            color: #334155;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            transform: translateY(-50%);
            transition: color 160ms ease;
        }

        .input-box input {
            width: 100%;
            min-height: 50px;
            padding: 12px 44px 12px 43px;
            color: var(--ink);
            border: 1.5px solid #cbd5e1;
            border-radius: 13px;
            background: #f8fafc;
            font-size: 0.86rem;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .input-box input::placeholder {
            color: #a3afc2;
        }

        .input-box input:hover {
            border-color: #94a3b8;
        }

        .input-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.11);
        }

        .input-wrap:focus-within .input-icon {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            padding: 0;
            color: #64748b;
            border: 0;
            border-radius: 9px;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
            transition: color 160ms ease, background 160ms ease;
        }

        .password-toggle:hover {
            color: var(--primary);
            background: #eff6ff;
        }

        .password-toggle .eye-off,
        .password-toggle.is-visible .eye-on {
            display: none;
        }

        .password-toggle.is-visible .eye-off {
            display: block;
        }

        .login-button {
            width: 100%;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 24px;
            padding: 12px 18px;
            overflow: hidden;
            color: #fff;
            border: 0;
            border-radius: 13px;
            background: linear-gradient(110deg, var(--primary), var(--indigo), var(--sky));
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 800;
            transition: transform 160ms ease, box-shadow 160ms ease, filter 160ms ease;
        }

        .login-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 17px 30px rgba(37, 99, 235, 0.28);
            filter: brightness(1.05);
        }

        .login-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.72;
            cursor: wait;
        }

        .button-loading {
            display: none;
            align-items: center;
            gap: 9px;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.38);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 700ms linear infinite;
        }

        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 0.67rem;
            line-height: 1.4;
            text-align: center;
        }

        .security-note svg {
            color: #15803d;
        }

        .footer {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px clamp(20px, 5vw, 72px);
            color: rgba(255, 255, 255, 0.58);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            font-size: 0.7rem;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.68);
            text-decoration: none;
            transition: color 160ms ease;
        }

        .footer-links a:hover {
            color: #fff;
        }

        .session-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: blur(7px);
        }

        .session-modal-card {
            width: min(100%, 430px);
            padding: 36px 32px 30px;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.35);
            text-align: center;
        }

        .session-modal-icon {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
            margin: 0 auto 17px;
            color: #e11d48;
            border-radius: 20px;
            background: #fff1f2;
        }

        .session-modal-icon svg {
            width: 30px;
            height: 30px;
            stroke: currentColor;
        }

        .session-modal-title {
            margin-bottom: 9px;
            color: var(--ink);
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .session-modal-copy {
            margin-bottom: 22px;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.6;
        }

        .session-modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .session-modal-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 11px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .session-modal-btn.primary {
            color: #fff;
            background: linear-gradient(100deg, var(--primary), var(--indigo));
        }

        .session-modal-btn.secondary {
            color: #334155;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        html.dark-mode {
            --ink: #f1f5f9;
            --muted: #94a3b8;
            --line: #334155;
            --surface: rgba(15, 23, 42, 0.97);
        }

        html.dark-mode .login-shell {
            border-color: rgba(96, 165, 250, 0.24);
            background: rgba(15, 23, 42, 0.96);
            box-shadow: 0 30px 80px rgba(2, 6, 23, 0.65);
        }

        html.dark-mode .login-panel {
            background: rgba(15, 23, 42, 0.98);
        }

        html.dark-mode .login-heading h1,
        html.dark-mode .label-row label {
            color: #f1f5f9;
        }

        html.dark-mode .input-box input {
            color: #e5edf7;
            border-color: #334155;
            background: #111c30;
        }

        html.dark-mode .input-box input:hover { border-color: #475569; }
        html.dark-mode .input-box input:focus { border-color: #3b82f6; background: #0b1220; }
        html.dark-mode .password-toggle:hover { background: rgba(37, 99, 235, 0.14); }
        html.dark-mode .login-error { color: #fecdd3; border-color: #7f3045; background: rgba(190, 18, 60, 0.12); }
        html.dark-mode .session-modal-card { color: #e2e8f0; border-color: #334155; background: #111827; }
        html.dark-mode .session-modal-title { color: #f8fafc; }
        html.dark-mode .session-modal-btn.secondary { color: #cbd5e1; border-color: #475569; background: #1e293b; }

        .home-link:focus-visible,
        .theme-toggle:focus-visible,
        .forgot-link:focus-visible,
        .password-toggle:focus-visible,
        .login-button:focus-visible,
        .session-modal-btn:focus-visible {
            outline: 3px solid rgba(14, 165, 233, 0.42);
            outline-offset: 2px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 820px) {
            .login-shell {
                width: min(100%, 560px);
                min-height: auto;
                grid-template-columns: 1fr;
            }

            .login-aside {
                display: none;
            }

            .mobile-logo {
                display: block;
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
                justify-content: center;
                padding: 0;
            }

            .home-link span {
                display: none;
            }

            .page {
                padding: 18px 12px;
                place-items: start center;
            }

            .login-shell {
                border-radius: 21px;
            }

            .login-panel {
                padding: 30px 20px 28px;
            }

            .footer {
                justify-content: center;
                padding: 16px;
                text-align: center;
            }

            .footer-links {
                display: none;
            }

            .session-modal-actions {
                grid-template-columns: 1fr;
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

    <header class="site-header">
        <a class="brand" href="{{ url('/') }}" aria-label="Go to {{ $systemName }} home page">
            <img src="{{ $systemLogoUrl }}" alt="">
            <span>{{ $systemName }}</span>
        </a>
        <div class="header-actions">
            <a class="home-link" href="{{ url('/') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 11.5 12 4l9 7.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.5 10v9h13v-9M9 19v-5h6v5" stroke-linejoin="round"/>
                </svg>
                <span>Home</span>
            </a>
            <button class="theme-toggle" id="loginThemeToggle" type="button" aria-label="Switch to dark mode" title="Toggle theme">
                <svg class="moon-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.7 15.2A8.7 8.7 0 0 1 8.8 3.3 9 9 0 1 0 20.7 15.2Z"/></svg>
                <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/></svg>
            </button>
        </div>
    </header>

    <main class="page">
        <div class="login-shell">
            <aside class="login-aside" aria-label="System information">
                <div class="aside-content">
                    <img class="aside-logo" src="{{ $systemLogoUrl }}" alt="{{ $systemName }} logo">
                    <p class="eyebrow">Energy intelligence platform</p>
                    <h2>Powering smarter public service.</h2>
                    <p class="aside-copy">
                        Monitor energy performance, uncover efficiency opportunities, and make informed decisions from one secure workspace.
                    </p>
                </div>
                <div class="aside-footer">
                    <span class="status-dot" aria-hidden="true"></span>
                    <span>Secure access for authorized personnel</span>
                </div>
            </aside>

            <section class="login-panel" aria-labelledby="login-title">
                <div class="login-content">
                    <img class="mobile-logo" src="{{ $systemLogoUrl }}" alt="{{ $systemName }} logo">

                    <div class="login-heading">
                        <h1 id="login-title">Sign in to your account</h1>
                        <p>Enter your credentials to access the {{ $systemName }} workspace.</p>
                    </div>

                    <div
                        id="loginError"
                        class="login-error {{ $errors->any() ? 'is-visible' : '' }}"
                        role="alert"
                        aria-live="assertive"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7.5v5M12 16.5h.01" stroke-linecap="round"/>
                        </svg>
                        <span id="loginErrorText">{{ $errors->first('email') ?: $errors->first('password') }}</span>
                    </div>

                    <form id="loginForm" method="POST" action="{{ url('/login') }}" autocomplete="off">
                        @csrf
                        <input class="autofill-trap" type="text" name="decoy_username" tabindex="-1" autocomplete="username" aria-hidden="true">
                        <input class="autofill-trap" type="password" name="decoy_password" tabindex="-1" autocomplete="current-password" aria-hidden="true">

                        <div class="input-box">
                            <div class="label-row">
                                <label for="loginEmail">Email address</label>
                            </div>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3.5" y="5.5" width="17" height="13" rx="2"/>
                                    <path d="m5 7 7 5 7-5" stroke-linejoin="round"/>
                                </svg>
                                <input
                                    type="email"
                                    name="email"
                                    id="loginEmail"
                                    value=""
                                    placeholder="name@lgu.infra.ph"
                                    required
                                    autocomplete="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                    readonly
                                >
                            </div>
                        </div>

                        <div class="input-box">
                            <div class="label-row">
                                <label for="loginPassword">Password</label>
                                <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
                            </div>
                            <div class="input-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="4" y="10" width="16" height="11" rx="3"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    <path d="M12 14.5v2" stroke-linecap="round"/>
                                </svg>
                                <input
                                    type="password"
                                    name="password"
                                    id="loginPassword"
                                    placeholder="Enter your password"
                                    required
                                    autocomplete="new-password"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                    readonly
                                >
                                <button
                                    class="password-toggle"
                                    id="passwordToggle"
                                    type="button"
                                    aria-label="Show password"
                                    aria-pressed="false"
                                >
                                    <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>
                                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="m3 3 18 18M10.6 6.1A9 9 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-2.1 2.8M6.3 6.7C3.9 8.4 2.5 12 2.5 12s3.5 6 9.5 6a9 9 0 0 0 3-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="login-button" id="loginBtn">
                            <span id="loginBtnText">Sign in securely</span>
                            <svg id="loginBtnIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M14 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="button-loading" id="loginBtnLoading">
                                <span class="spinner" aria-hidden="true"></span>
                                Authenticating...
                            </span>
                        </button>
                    </form>

                    <p class="security-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.2 7 10 4.2-1.8 7-5.3 7-10V6l-7-3Z"/>
                            <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Your credentials are protected and securely transmitted.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <div id="sessionEndedModal" class="session-modal-backdrop" aria-hidden="true">
        <div class="session-modal-card" role="dialog" aria-modal="true" aria-labelledby="sessionEndedTitle" aria-describedby="sessionEndedCopy">
            <div class="session-modal-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="4" y="10" width="16" height="11" rx="3" stroke-width="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 14.5v2" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div id="sessionEndedTitle" class="session-modal-title">Your session has ended</div>
            <div id="sessionEndedCopy" class="session-modal-copy">
                You were signed out after a period of inactivity. Please sign in again to keep your account and portal access protected.
            </div>
            <div class="session-modal-actions">
                <a href="{{ route('login') }}" class="session-modal-btn primary" id="sessionLoginLink">Continue to sign in</a>
                <a href="{{ url('/') }}" class="session-modal-btn secondary">Go to home</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <span>&copy; {{ date('Y') }} {{ $systemName }} &middot; {{ $systemOrganization }}</span>
        <nav class="footer-links" aria-label="Footer navigation">
            <a href="{{ url('/') }}">About system</a>
            <a href="{{ url('/contact') }}">Technical support</a>
        </nav>
    </footer>

    <script>
        (function () {
            const themeToggle = document.getElementById('loginThemeToggle');
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');
            const passwordToggle = document.getElementById('passwordToggle');
            const loginButton = document.getElementById('loginBtn');
            const buttonText = document.getElementById('loginBtnText');
            const buttonIcon = document.getElementById('loginBtnIcon');
            const buttonLoading = document.getElementById('loginBtnLoading');
            const errorBox = document.getElementById('loginError');
            const errorText = document.getElementById('loginErrorText');

            function applyTheme(isDark) {
                document.documentElement.classList.toggle('dark-mode', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
                if (themeToggle) themeToggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                try { localStorage.setItem('darkMode', isDark ? 'on' : 'off'); } catch (e) {}
            }

            applyTheme(document.documentElement.classList.contains('dark-mode'));

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    applyTheme(!document.documentElement.classList.contains('dark-mode'));
                });
            }

            function prepareCredentialField(input) {
                if (!input) return;

                input.value = '';

                const unlock = function () {
                    input.readOnly = false;
                };

                input.addEventListener('pointerdown', unlock);
                input.addEventListener('focus', unlock);
                input.addEventListener('keydown', unlock);
            }

            prepareCredentialField(emailInput);
            prepareCredentialField(passwordInput);

            window.addEventListener('pageshow', function () {
                [emailInput, passwordInput].forEach(function (input) {
                    if (!input) return;
                    input.value = '';
                    input.readOnly = true;
                });
            });

            function setError(message) {
                if (!errorBox || !errorText) return;
                errorText.textContent = message;
                errorBox.classList.add('is-visible');
            }

            function clearError() {
                if (!errorBox) return;
                errorBox.classList.remove('is-visible');
            }

            function setLoading(isLoading) {
                if (!loginButton || !buttonText || !buttonIcon || !buttonLoading) return;
                loginButton.disabled = isLoading;
                loginButton.setAttribute('aria-busy', isLoading ? 'true' : 'false');
                buttonText.style.display = isLoading ? 'none' : '';
                buttonIcon.style.display = isLoading ? 'none' : '';
                buttonLoading.style.display = isLoading ? 'flex' : 'none';
            }

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function () {
                    const willShow = passwordInput.type === 'password';
                    passwordInput.type = willShow ? 'text' : 'password';
                    this.classList.toggle('is-visible', willShow);
                    this.setAttribute('aria-pressed', willShow ? 'true' : 'false');
                    this.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
                    passwordInput.focus({ preventScroll: true });
                });
            }

            [emailInput, passwordInput].forEach(function (input) {
                if (input) input.addEventListener('input', clearError);
            });

            if (loginForm) {
                loginForm.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    if (!this.reportValidity() || !emailInput || !passwordInput) return;

                    const tokenInput = this.querySelector('input[name="_token"]');
                    const metaToken = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = tokenInput?.value || metaToken?.getAttribute('content');

                    clearError();
                    setLoading(true);

                    if (!csrfToken) {
                        setError('Your session token is missing. Please refresh the page and try again.');
                        setLoading(false);
                        return;
                    }

                    try {
                        const response = await fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                email: emailInput.value.trim(),
                                password: passwordInput.value
                            }),
                            credentials: 'same-origin'
                        });

                        const contentType = response.headers.get('content-type') || '';
                        const data = contentType.includes('application/json') ? await response.json() : {};

                        if (response.ok) {
                            window.location.href = data.redirect || '{{ route('dashboard') }}';
                            return;
                        }

                        const validationMessage = data.errors
                            ? Object.values(data.errors).flat()[0]
                            : null;

                        setError(validationMessage || data.message || 'Unable to sign in. Please check your credentials and try again.');
                    } catch (error) {
                        setError('We could not reach the server. Check your connection and try again.');
                    } finally {
                        setLoading(false);
                    }
                });
            }

            window.addEventListener('DOMContentLoaded', function () {
                const sessionEndedModal = document.getElementById('sessionEndedModal');
                const showSessionEndedModal = @json((bool) session('session_ended_modal'))
                    || new URLSearchParams(window.location.search).get('session') === 'expired';

                if (sessionEndedModal && showSessionEndedModal) {
                    sessionEndedModal.style.display = 'flex';
                    sessionEndedModal.setAttribute('aria-hidden', 'false');
                    document.getElementById('sessionLoginLink')?.focus();
                }
            });
        })();
    </script>
</body>
</html>
