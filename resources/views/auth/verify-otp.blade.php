<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify OTP | {{ $systemName }}</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #6366f1;
            --text: #0f172a;
            --muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: "Plus Jakarta Sans", sans-serif;
            color: var(--text);
            background: #0f172a;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            background: linear-gradient(rgba(15,23,42,.65), rgba(15,23,42,.65)),
                url('{{ asset("img/cityhall.jpeg") }}') center/cover no-repeat;
            filter: blur(8px);
            transform: scale(1.08);
        }
        .nav {
            height: 80px;
            padding: 0 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,255,255,.05);
            border-bottom: 1px solid rgba(255,255,255,.1);
            backdrop-filter: blur(15px);
        }
        .brand, .home-link {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }
        .brand { display: flex; align-items: center; gap: 12px; font-size: 1.15rem; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; object-fit: cover; }
        .home-link { opacity: .82; font-size: .92rem; }
        .wrapper {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 40px 20px;
        }
        .card {
            width: 100%;
            max-width: 440px;
            padding: 38px 34px 32px;
            text-align: center;
            border-radius: 22px;
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(255,255,255,.45);
            box-shadow: 0 25px 55px rgba(0,0,0,.38);
            backdrop-filter: blur(20px);
        }
        .shield {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 22px;
            color: var(--primary);
            background: #eff6ff;
            font-size: 2rem;
            font-weight: 800;
            transition: color .25s, background .25s, transform .25s;
        }
        .shield svg { width: 34px; height: 34px; }
        .shield.is-success {
            color: #16a34a;
            background: #dcfce7;
            animation: success-pop .45s ease-out;
        }
        h1 { margin: 0 0 9px; font-size: 1.75rem; letter-spacing: -.7px; }
        .subtitle { margin: 0 auto 8px; color: var(--muted); line-height: 1.55; font-size: .94rem; }
        .email { margin-bottom: 22px; color: var(--text); font-weight: 700; }
        .otp-boxes {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 9px;
            width: 100%;
        }
        .otp-digit {
            min-width: 0;
            height: 64px;
            padding: 0;
            border: 2px solid #dbe4f0;
            border-radius: 13px;
            outline: 0;
            background: #f8fafc;
            color: var(--text);
            text-align: center;
            font: 800 1.55rem/1 "Plus Jakarta Sans", sans-serif;
            caret-color: var(--primary);
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .otp-digit:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,.1);
        }
        .otp-boxes.is-invalid .otp-digit {
            border-color: #fb7185;
            background: #fff1f2;
        }
        .otp-boxes.is-invalid {
            animation: otp-shake .32s ease-in-out;
        }
        .otp-boxes.is-success .otp-digit {
            border-color: #4ade80;
            color: #15803d;
            background: #f0fdf4;
        }
        .error, .message {
            margin: 14px 0 0;
            padding: 11px 13px;
            border-radius: 11px;
            font-size: .88rem;
            font-weight: 600;
        }
        .error { color: #be123c; background: #fff1f2; border: 1px solid #ffe4e6; }
        .error[hidden] { display: none; }
        .message { display: none; color: #166534; background: #f0fdf4; border: 1px solid #dcfce7; }
        .timer { margin: 18px 0 4px; color: var(--muted); font-size: .88rem; }
        .timer strong { color: var(--primary); }
        .timer.is-warning strong { color: #d97706; }
        .timer.is-expired strong { color: #dc2626; }
        .verify-btn {
            width: 100%;
            margin-top: 16px;
            padding: 15px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(100deg, var(--primary), var(--secondary));
            font: 700 1rem "Plus Jakarta Sans", sans-serif;
            cursor: pointer;
            box-shadow: 0 10px 18px rgba(37,99,235,.2);
        }
        .verify-btn:disabled { opacity: .55; cursor: not-allowed; box-shadow: none; }
        .verify-btn.is-success,
        .verify-btn.is-success:disabled {
            opacity: 1;
            color: #166534;
            background: #dcfce7;
        }
        .actions { margin-top: 18px; font-size: .9rem; color: var(--muted); }
        .link-btn {
            padding: 0;
            border: 0;
            color: var(--primary);
            background: transparent;
            font: 700 .9rem "Plus Jakarta Sans", sans-serif;
            cursor: pointer;
        }
        .link-btn:disabled {
            color: #94a3b8;
            cursor: not-allowed;
            opacity: .72;
            pointer-events: none;
        }
        .back { display: inline-block; margin-top: 18px; color: var(--muted); font-size: .86rem; text-decoration: none; }
        @keyframes otp-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }
        @keyframes success-pop {
            0% { transform: scale(.75); opacity: .35; }
            70% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }
        @media (max-width: 480px) {
            .nav { padding: 0 20px; }
            .brand span { display: none; }
            .card { padding: 32px 23px 27px; }
            .otp-boxes { gap: 6px; }
            .otp-digit { height: 56px; border-radius: 11px; font-size: 1.35rem; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ $systemLogoUrl }}" alt="LGU logo">
            <span>{{ $systemName }}</span>
        </a>
        <a href="{{ url('/') }}" class="home-link">Home</a>
    </header>

    <main class="wrapper">
        <section class="card" aria-labelledby="otpTitle">
            <div id="statusIcon" class="shield" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="5" y="10" width="14" height="11" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                </svg>
            </div>
            <h1 id="otpTitle">Verify your identity</h1>
            <p id="otpSubtitle" class="subtitle">Enter the 6-digit one-time password sent to</p>
            <div id="otpEmail" class="email">{{ $maskedEmail }}</div>

            <form id="otpForm" method="POST" action="{{ route('verify.otp.submit') }}">
                @csrf
                <label for="otp_digit_1" style="position:absolute;left:-9999px;">6-digit OTP code</label>
                <input id="otp_code" name="otp_code" type="hidden" value="{{ old('otp_code') }}">
                <div id="otpBoxes" class="otp-boxes{{ $errors->has('otp_code') ? ' is-invalid' : '' }}" role="group" aria-label="6-digit OTP code" aria-describedby="otpTimer otpError">
                    @foreach(range(1, 6) as $digit)
                        <input
                            class="otp-digit"
                            id="otp_digit_{{ $digit }}"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]"
                            maxlength="{{ $digit === 1 ? 6 : 1 }}"
                            autocomplete="{{ $digit === 1 ? 'one-time-code' : 'off' }}"
                            aria-label="OTP digit {{ $digit }} of 6"
                            required
                            @if($digit === 1) autofocus @endif
                        >
                    @endforeach
                </div>

                <div id="otpError" class="error" role="alert" @if(! $errors->has('otp_code')) hidden @endif>
                    {{ $errors->first('otp_code') }}
                </div>

                <div id="resendMessage" class="message" role="status"></div>
                <div id="otpTimer" class="timer">Code expires in <strong>--:--</strong></div>
                <button id="verifyButton" class="verify-btn" type="submit" disabled>Verify and continue</button>
            </form>

            <div id="otpActions" class="actions">
                Didn't receive the code?
                <button id="resendButton" class="link-btn" type="button" disabled>Resend OTP</button>
            </div>
            <a id="otpBack" class="back" href="{{ route('login') }}">← Back to login</a>
        </section>
    </main>

    <script>
        (() => {
            const form = document.getElementById('otpForm');
            const hiddenInput = document.getElementById('otp_code');
            const digitInputs = Array.from(document.querySelectorAll('.otp-digit'));
            const otpBoxes = document.getElementById('otpBoxes');
            const otpError = document.getElementById('otpError');
            const verifyButton = document.getElementById('verifyButton');
            const resendButton = document.getElementById('resendButton');
            const resendMessage = document.getElementById('resendMessage');
            const timer = document.getElementById('otpTimer');
            const timerText = timer.querySelector('strong');
            const statusIcon = document.getElementById('statusIcon');
            const otpTitle = document.getElementById('otpTitle');
            const otpSubtitle = document.getElementById('otpSubtitle');
            const otpEmail = document.getElementById('otpEmail');
            const otpActions = document.getElementById('otpActions');
            const otpBack = document.getElementById('otpBack');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let expiresAt = {{ $expiresAt }};
            let resendAvailableAt = {{ $resendAvailableAt }};
            let secondsRemaining = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
            let isSubmitting = false;
            let isResending = false;
            let hasResendPenaltyMessage = false;

            const updateVerifyState = () => {
                const complete = hiddenInput.value.length === 6;
                verifyButton.disabled = isSubmitting || !complete || secondsRemaining <= 0;
                if (!isSubmitting) {
                    verifyButton.textContent = 'Verify and continue';
                }
            };

            const clearOtpError = () => {
                otpBoxes.classList.remove('is-invalid');
                otpError.hidden = true;
                otpError.textContent = '';
            };

            const showOtpError = (message) => {
                otpError.textContent = message;
                otpError.hidden = false;
                otpBoxes.classList.remove('is-invalid');
                void otpBoxes.offsetWidth;
                otpBoxes.classList.add('is-invalid');
                digitInputs.find((input) => !input.value)?.focus() || digitInputs[0]?.focus();
            };

            const syncOtpValue = () => {
                hiddenInput.value = digitInputs.map((input) => input.value).join('');
                updateVerifyState();
            };

            const fillOtpBoxes = (value) => {
                const digits = String(value || '').replace(/\D/g, '').slice(0, 6).split('');
                digitInputs.forEach((input, index) => {
                    input.value = digits[index] || '';
                });
                syncOtpValue();
                return digits.length;
            };

            digitInputs.forEach((input, index) => {
                input.addEventListener('input', () => {
                    clearOtpError();
                    const digits = input.value.replace(/\D/g, '');

                    if (digits.length > 1) {
                        const count = fillOtpBoxes(digits);
                        digitInputs[Math.min(count, 6) - 1]?.focus();
                        return;
                    }

                    input.value = digits.slice(0, 1);
                    syncOtpValue();
                    if (input.value && index < digitInputs.length - 1) {
                        digitInputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        digitInputs[index - 1].value = '';
                        digitInputs[index - 1].focus();
                        syncOtpValue();
                    } else if (event.key === 'ArrowLeft' && index > 0) {
                        digitInputs[index - 1].focus();
                    } else if (event.key === 'ArrowRight' && index < digitInputs.length - 1) {
                        digitInputs[index + 1].focus();
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();
                    clearOtpError();
                    const count = fillOtpBoxes(event.clipboardData?.getData('text') || '');
                    digitInputs[Math.max(0, Math.min(count, 6) - 1)]?.focus();
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                syncOtpValue();
                if (hiddenInput.value.length !== 6 || secondsRemaining <= 0 || isSubmitting) {
                    return;
                }

                clearOtpError();
                isSubmitting = true;
                updateVerifyState();
                verifyButton.textContent = 'Verifying...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: new FormData(form)
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 401 && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }

                        const validationMessage = data.errors?.otp_code?.[0];
                        throw new Error(validationMessage || data.message || 'Invalid or expired OTP.');
                    }

                    otpBoxes.classList.add('is-success');
                    digitInputs.forEach((input) => { input.disabled = true; });
                    statusIcon.classList.add('is-success');
                    statusIcon.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 12 4 4L19 6"></path>
                        </svg>`;
                    otpTitle.textContent = 'Identity verified';
                    otpSubtitle.textContent = 'Secure sign-in complete. Redirecting to your dashboard...';
                    otpEmail.style.display = 'none';
                    timer.style.display = 'none';
                    resendMessage.style.display = 'none';
                    otpActions.style.display = 'none';
                    otpBack.style.display = 'none';
                    verifyButton.classList.add('is-success');
                    verifyButton.textContent = 'Verified';

                    window.setTimeout(() => {
                        window.location.href = data.redirect || @json(route('dashboard'));
                    }, 900);
                } catch (error) {
                    isSubmitting = false;
                    updateVerifyState();
                    showOtpError(error.message || 'Unable to verify the OTP. Please try again.');
                    digitInputs.forEach((input) => { input.value = ''; });
                    syncOtpValue();
                    digitInputs[0]?.focus();
                }
            });

            const initialDigitCount = fillOtpBoxes(hiddenInput.value);
            if (initialDigitCount > 0 && initialDigitCount < 6) {
                digitInputs[initialDigitCount]?.focus();
            }

            function updateTimer() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = Math.max(0, expiresAt - now);
                secondsRemaining = remaining;
                const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                const seconds = String(remaining % 60).padStart(2, '0');
                timerText.textContent = remaining > 0 ? `${minutes}:${seconds}` : 'Expired';
                timer.classList.toggle('is-warning', remaining > 0 && remaining <= 30);
                timer.classList.toggle('is-expired', remaining === 0);
                updateVerifyState();

                const resendWait = Math.max(0, resendAvailableAt - now);
                resendButton.disabled = isResending || resendWait > 0;
                resendButton.textContent = isResending
                    ? 'Sending...'
                    : (resendWait > 0 ? `Resend OTP (${resendWait}s)` : 'Resend OTP');
                if (resendWait === 0 && hasResendPenaltyMessage) {
                    resendMessage.style.display = 'none';
                    hasResendPenaltyMessage = false;
                }
            }

            resendButton.addEventListener('click', async () => {
                const now = Math.floor(Date.now() / 1000);
                if (isResending || now < resendAvailableAt) {
                    updateTimer();
                    return;
                }

                clearOtpError();
                isResending = true;
                updateTimer();
                resendMessage.style.display = 'none';

                try {
                    const response = await fetch(@json(route('verify.otp.resend')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 401) {
                            window.location.href = @json(route('login'));
                            return;
                        }

                        if (response.status === 429) {
                            const retryAfter = Math.max(1, Number(data.retry_after) || 1);
                            resendAvailableAt = Number(data.resend_available_at)
                                || (Math.floor(Date.now() / 1000) + retryAfter);
                            resendMessage.textContent = data.message || `Please wait ${retryAfter} seconds before requesting another OTP.`;
                            resendMessage.style.color = '#be123c';
                            resendMessage.style.background = '#fff1f2';
                            resendMessage.style.display = 'block';
                            hasResendPenaltyMessage = true;
                            return;
                        }
                        throw new Error(data.message || 'Unable to resend OTP.');
                    }

                    expiresAt = data.expires_at;
                    resendAvailableAt = Number(data.resend_available_at)
                        || (Math.floor(Date.now() / 1000) + Math.max(1, Number(data.retry_after) || 30));
                    resendMessage.textContent = data.message;
                    resendMessage.style.color = '#166534';
                    resendMessage.style.background = '#f0fdf4';
                    resendMessage.style.display = 'block';
                    hasResendPenaltyMessage = false;
                    fillOtpBoxes('');
                    digitInputs[0]?.focus();
                } catch (error) {
                    resendMessage.textContent = error.message;
                    resendMessage.style.color = '#be123c';
                    resendMessage.style.background = '#fff1f2';
                    resendMessage.style.display = 'block';
                    resendAvailableAt = Math.floor(Date.now() / 1000) + 5;
                } finally {
                    isResending = false;
                    updateTimer();
                }
            });

            updateTimer();
            setInterval(updateTimer, 1000);
        })();
    </script>
</body>
</html>
