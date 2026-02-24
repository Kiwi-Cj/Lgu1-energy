<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Energy System</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --panel: rgba(255, 255, 255, 0.97);
            --panel-border: rgba(226, 232, 240, 0.95);
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbe2ea;
            --line-strong: #c8d3e1;
            --primary: #2563eb;
            --primary-2: #1d4ed8;
            --primary-soft: #eff6ff;
            --success-bg: #ecfdf3;
            --success-line: #86efac;
            --danger-bg: #fef2f2;
            --danger-line: #fca5a5;
            --info-bg: #eff6ff;
            --info-line: #93c5fd;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #0f172a;
            padding: 16px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
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
            max-width: 500px;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            box-shadow: 0 22px 44px rgba(2, 6, 23, 0.38);
            padding: 18px;
        }
        .hero {
            display: flex;
            gap: 0;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        h1 {
            margin: 2px 0 4px;
            font-size: 1.2rem;
            color: var(--ink);
            font-weight: 800;
        }
        .subtitle {
            margin: 0;
            color: #52627a;
            font-size: 0.92rem;
            line-height: 1.4;
        }
        .progress {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0 12px;
        }
        .progress-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 9px 10px;
        }
        .progress-item.active {
            border-color: #b9d3ff;
            background: #f8fbff;
        }
        .progress-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        .progress-num {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #3468e7;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .progress-title {
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--ink);
        }
        .progress-text {
            margin: 0;
            color: var(--muted);
            font-size: 0.79rem;
            line-height: 1.35;
        }
        .status {
            margin-bottom: 12px;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.86rem;
            font-weight: 600;
            color: #166534;
            background: var(--success-bg);
            border: 1px solid var(--success-line);
        }
        .status-info {
            color: #1e3a8a;
            background: var(--info-bg);
            border: 1px solid var(--info-line);
        }
        .error {
            margin-bottom: 12px;
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #991b1b;
            background: var(--danger-bg);
            border: 1px solid var(--danger-line);
            text-align: left;
        }
        .hint {
            margin: 0 0 10px;
            color: #56657d;
            font-size: 0.84rem;
            line-height: 1.4;
        }
        .step-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            padding: 12px;
            margin-bottom: 10px;
        }
        .step-card.locked {
            background: #fbfcfe;
        }
        .step-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .step-card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: var(--ink);
            font-size: 0.95rem;
            font-weight: 700;
        }
        .step-badge {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            border: 1px solid #bfdbfe;
        }
        .step-note {
            margin: 0;
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.35;
        }
        .pill {
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            border: 1px solid var(--line);
            color: #475569;
            background: #f8fafc;
        }
        .pill.ready {
            color: #1e3a8a;
            border-color: #bfdbfe;
            background: #eff6ff;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .field {
            text-align: left;
        }
        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.84rem;
            font-weight: 700;
            color: #1e293b;
        }
        .field input {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-radius: 12px;
            padding: 10px 11px;
            font-size: 0.9rem;
            box-sizing: border-box;
            background: #fff;
        }
        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .field input::placeholder {
            color: #94a3b8;
        }
        .field-error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 0.82rem;
            text-align: left;
        }
        .inline-help {
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.77rem;
            line-height: 1.35;
        }
        .otp-row {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 10px;
            align-items: end;
        }
        .otp-row .field {
            margin: 0;
        }
        .otp-input {
            letter-spacing: 0.12em;
            text-align: center;
            font-weight: 700;
        }
        .btn {
            border: 1px solid transparent;
            border-radius: 12px;
            min-height: 40px;
            padding: 9px 12px;
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--primary), #3b82f6);
            border-color: #2f63de;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-2), var(--primary));
        }
        .btn-soft {
            color: #334155;
            background: #fff;
            border-color: var(--line-strong);
        }
        .btn-soft:hover {
            background: #f8fafc;
        }
        .btn-block {
            width: 100%;
        }
        .btn:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 2px;
            color: #475569;
            text-decoration: none;
            font-weight: 700;
            padding: 9px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
            box-sizing: border-box;
        }
        .back-link:hover {
            background: #f8fafc;
            color: #334155;
        }
        .footer-note {
            margin-top: 8px;
            color: var(--muted);
            font-size: 0.75rem;
            line-height: 1.35;
            text-align: center;
        }
        @media (max-width: 640px) {
            .card {
                padding: 14px;
                border-radius: 18px;
            }
            .progress,
            .grid-2,
            .otp-row {
                grid-template-columns: 1fr;
            }
        }
        @media (min-width: 641px) {
            body {
                padding-top: 24px;
                padding-bottom: 24px;
            }
        }
    </style>
</head>
<body>
    @php
        $otpPending = session('password_reset_otp_pending');
        $otpReady = is_array($otpPending);
    @endphp

    <div class="card">
        <div class="hero">
            <div>
                <h1>Forgot Password</h1>
                <p class="subtitle">Reset your password in 2 simple steps using your username, email, and OTP code.</p>
            </div>
        </div>

        <div class="progress">
            <div class="progress-item {{ ! $otpReady ? 'active' : '' }}">
                <div class="progress-head">
                    <span class="progress-num">1</span>
                    <span class="progress-title">Send OTP</span>
                </div>
                <p class="progress-text">Enter username and email, then send a one-time code.</p>
            </div>
            <div class="progress-item {{ $otpReady ? 'active' : '' }}">
                <div class="progress-head">
                    <span class="progress-num">2</span>
                    <span class="progress-title">Verify OTP</span>
                </div>
                <p class="progress-text">Enter the code to send your password reset link.</p>
            </div>
        </div>

        @if(session('otp_status'))
            <div class="status">{{ session('otp_status') }}</div>
        @endif

        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                {{ $errors->first('otp') ?: ($errors->first('username') ?: ($errors->first('email') ?: 'Unable to process your request. Please try again.')) }}
            </div>
        @endif

        <p class="hint">
            Use the account username and email you registered. After OTP verification, the reset link will be sent to that email.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <section class="step-card">
                <div class="step-card-header">
                    <h2 class="step-card-title">
                        <span class="step-badge">1</span>
                        Account Check
                    </h2>
                    <span class="pill {{ $otpReady ? 'ready' : '' }}">{{ $otpReady ? 'OTP sent' : 'Required' }}</span>
                </div>
                <p class="step-note">Enter your username and email first, then click Send OTP.</p>

                <div class="grid-2" style="margin-top:10px;">
                    <div class="field">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="your.username"
                        >
                        @error('username')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="name@example.com"
                        >
                        @error('email')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="inline-help">If you already requested a code, you can still continue to Step 2 below.</div>
                <button class="btn btn-soft btn-block" type="submit" name="submit_action" value="send_otp" style="margin-top:10px;">Send OTP</button>
            </section>

            <section class="step-card {{ $otpReady ? '' : 'locked' }}">
                <div class="step-card-header">
                    <h2 class="step-card-title">
                        <span class="step-badge">2</span>
                        Verify OTP
                    </h2>
                    <span class="pill {{ $otpReady ? 'ready' : '' }}">{{ $otpReady ? 'Ready' : 'Send OTP first' }}</span>
                </div>
                <p class="step-note">
                    Enter the 6-digit code from your email. OTP expires in {{ max(1, (int) config('otp.expire_minutes', 5)) }} minute(s).
                </p>

                <div class="otp-row" style="margin-top:10px;">
                    <div class="field">
                        <label for="otp">OTP Code</label>
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
                            placeholder="123456"
                        >
                        @error('otp')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button
                        class="btn btn-primary"
                        type="submit"
                        name="submit_action"
                        value="verify_otp"
                        {{ $otpReady ? '' : 'disabled' }}
                    >
                        Verify OTP & Send Link
                    </button>
                </div>

                <div class="inline-help">Did not receive the code? Wait 30 seconds, then click Send OTP again.</div>
            </section>

            <a class="back-link" href="{{ route('login') }}">Back to Login</a>
        </form>

        <div class="footer-note">
            Tip: Check your spam folder if the OTP or reset link email does not appear in your inbox.
        </div>
    </div>
    <script>
        (function () {
            var otpInput = document.getElementById('otp');
            if (!otpInput) return;
            otpInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        })();
    </script>
</body>
</html>
