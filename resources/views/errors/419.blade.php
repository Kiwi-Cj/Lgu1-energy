
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</title>
    <style>
        .error-btn {
            display: inline-block;
            margin-bottom: 10px;
            padding: 10px 28px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            font-size: 1.08rem;
            text-decoration: none;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            cursor: pointer;
        }
        .error-btn.primary {
            background: linear-gradient(90deg,#6366f1,#2563eb);
            color: #fff;
        }
        .error-btn.primary:hover {
            background: linear-gradient(90deg,#2563eb,#6366f1);
            box-shadow: 0 2px 12px rgba(37,99,235,0.18);
        }
        .error-btn.secondary {
            background: #f3f4f6;
            color: #2563eb;
        }
        .error-btn.secondary:hover {
            background: #e0e7ff;
            color: #1e40af;
            box-shadow: 0 2px 12px rgba(99,102,241,0.10);
        }
    </style>
</head>
<body style="margin:0;padding:0;min-height:100vh;width:100vw;box-sizing:border-box;">
    <div style="min-height:100vh;width:100vw;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#f8fafc 0%,#e0e7ff 100%);font-family:'Inter',sans-serif;">
        <div style="width:100%;max-width:420px;padding:48px 36px 40px 36px;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(37,99,235,0.10);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
            <div style="font-size:4.5rem;font-weight:900;color:#6366f1;line-height:1;display:flex;align-items:center;justify-content:center;width:100%;">419</div>
            <div style="font-size:2rem;font-weight:700;color:#1e293b;margin-bottom:10px;">{{ __('Page Expired') }}</div>
            <!-- Removed default session expired message as requested -->
            <div style="font-size:1.15rem;color:#1e293b;margin-bottom:18px;font-weight:600;letter-spacing:0.01em;text-shadow:0 1px 8px #e0e7ff;">{{ __('You have been logged out because there was no activity or interaction for a period of time.') }}</div>
            <a href="{{ url('/') }}" class="error-btn primary">Go to Home</a>
            <a href="{{ route('login') }}" class="error-btn secondary" style="margin-left:8px;">Login</a>
            @if (request()->is('logout'))
                <div style="margin-top:18px;font-size:1rem;color:#64748b;">You have been logged out. Please log in again.</div>
            @endif
        </div>
    </div>
</body>
</html>
