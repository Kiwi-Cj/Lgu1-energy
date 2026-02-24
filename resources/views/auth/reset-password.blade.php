<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Energy System</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logocityhall.jpg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #d7e0ea;
            --line-strong: #c1cedc;
            --card: rgba(255, 255, 255, 0.97);
            --primary: #2563eb;
            --primary-2: #1d4ed8;
            --primary-soft: #eff6ff;
            --success-bg: #ecfdf3;
            --success-line: #86efac;
            --success-text: #166534;
            --danger-bg: #fef2f2;
            --danger-line: #fca5a5;
            --danger-text: #991b1b;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 20px 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
            background: #0f172a;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                linear-gradient(rgba(15, 23, 42, 0.62), rgba(15, 23, 42, 0.62)),
                url('{{ asset("img/cityhall.jpeg") }}') center center / cover no-repeat;
            filter: blur(6px);
            transform: scale(1.08);
            z-index: -1;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: var(--card);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 20px;
            box-shadow: 0 22px 44px rgba(2, 6, 23, 0.34);
            padding: 18px;
        }
        .header {
            margin-bottom: 12px;
        }
        .title {
            margin: 0 0 4px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ink);
        }
        .subtitle {
            margin: 0;
            color: #52627a;
            font-size: 0.9rem;
            line-height: 1.45;
        }
        .alert {
            margin-bottom: 10px;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.84rem;
            line-height: 1.35;
            border: 1px solid var(--danger-line);
            background: var(--danger-bg);
            color: var(--danger-text);
        }
        .alert-success {
            border-color: var(--success-line);
            background: var(--success-bg);
            color: var(--success-text);
            font-weight: 600;
        }
        .alert ul {
            margin: 0;
            padding-left: 18px;
        }
        .form-card {
            margin-top: 12px;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 14px;
            padding: 14px;
        }
        .field {
            margin-bottom: 12px;
        }
        .field:last-of-type {
            margin-bottom: 10px;
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
            padding: 11px 12px;
            font-size: 0.92rem;
            background: #fff;
            color: var(--ink);
        }
        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .field input.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
        }
        .field-help {
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.77rem;
            line-height: 1.3;
        }
        .field-error {
            margin-top: 6px;
            color: #dc2626;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .requirements {
            margin: 0 0 12px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #bfdbfe;
            background: var(--primary-soft);
        }
        .requirements-title {
            margin: 0 0 6px;
            color: #1e3a8a;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .requirements ul {
            margin: 0;
            padding-left: 16px;
            color: #475569;
            font-size: 0.78rem;
            line-height: 1.35;
        }
        .actions {
            display: grid;
            gap: 8px;
        }
        .btn {
            border: 1px solid transparent;
            border-radius: 12px;
            min-height: 42px;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
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
            color: #475569;
            background: #fff;
            border-color: var(--line);
        }
        .btn-soft:hover {
            background: #f8fafc;
            color: #334155;
        }
        .footer-note {
            margin-top: 10px;
            text-align: center;
            color: var(--muted);
            font-size: 0.76rem;
            line-height: 1.3;
        }
        .success-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(3px);
        }
        .success-modal-card {
            width: 100%;
            max-width: 380px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.28);
            padding: 16px;
        }
        .success-modal-badge {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            font-weight: 800;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .success-modal-title {
            margin: 0 0 6px;
            font-size: 1rem;
            font-weight: 800;
            color: var(--ink);
        }
        .success-modal-text {
            margin: 0 0 12px;
            color: #475569;
            font-size: 0.84rem;
            line-height: 1.4;
        }
        .success-modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        @media (min-width: 641px) {
            body {
                padding-top: 24px;
                padding-bottom: 24px;
            }
        }
        @media (max-width: 640px) {
            .card {
                border-radius: 16px;
                padding: 14px;
            }
            .title {
                font-size: 1.2rem;
            }
            .form-card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1 class="title">Reset Password</h1>
            <p class="subtitle">Create a new secure password for your account, then continue to login.</p>
        </div>

        @if(session('password_reset_success'))
            <div class="alert alert-success">
                {{ session('status') ?: 'Password reset successful.' }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(! session('password_reset_success'))
            <form method="POST" action="{{ route('password.store') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-card">
                    <div class="requirements">
                        <p class="requirements-title">Password Guidelines</p>
                        <ul>
                            <li>Use at least 12 characters</li>
                            <li>Include uppercase and lowercase letters</li>
                            <li>Include at least one number and one symbol</li>
                        </ul>
                    </div>

                    <div class="field">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            class="@error('email') is-invalid @enderror"
                            required
                            autocomplete="email"
                            placeholder="name@example.com"
                        >
                        @error('email')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">New Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="@error('password') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                            placeholder="Enter new password"
                        >
                        <div class="field-help">Make it strong and unique to protect your account.</div>
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="@error('password_confirmation') is-invalid @enderror"
                            required
                            autocomplete="new-password"
                            placeholder="Re-enter new password"
                        >
                        @error('password_confirmation')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                        <a href="{{ route('login') }}" class="btn btn-soft">Back to Login</a>
                    </div>
                </div>
            </form>
        @endif

        <div class="footer-note">
            If the reset link is expired, request a new password reset link from the forgot password page.
        </div>
    </div>

    @if(session('password_reset_success'))
        <div class="success-modal" role="dialog" aria-modal="true" aria-labelledby="resetSuccessTitle">
            <div class="success-modal-card">
                <div class="success-modal-badge">✓</div>
                <h2 id="resetSuccessTitle" class="success-modal-title">Password Reset Successful</h2>
                <p class="success-modal-text">
                    Your password has been updated successfully. Choose where you want to go next.
                </p>
                <div class="success-modal-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    <a href="{{ url('/') }}" class="btn btn-soft">Home Page</a>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
