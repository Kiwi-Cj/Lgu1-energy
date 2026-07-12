<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .session-modal-card {
            width: 100%;
            max-width: 430px;
            background: #fff;
            border-radius: 22px;
            padding: 40px 34px 32px;
            text-align: center;
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.16);
        }
        .session-modal-icon {
            width: 78px;
            height: 78px;
            margin: 0 auto 14px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            color: #e11d48;
            background: #fff1f2;
            font-size: 2rem;
        }
        .session-modal-title {
            font-size: 1.55rem;
            font-weight: 900;
            color: #e11d48;
            margin-bottom: 10px;
        }
        .session-modal-copy {
            color: #334155;
            line-height: 1.55;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .session-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .session-modal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 132px;
            padding: 12px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
        }
        .session-modal-btn.primary {
            background: linear-gradient(90deg,#2563eb,#6366f1);
            color: #fff;
        }
        .session-modal-btn.secondary {
            background: #f8fafc;
            color: #1d4ed8;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="session-modal-card" role="dialog" aria-modal="true" aria-labelledby="sessionExpiredTitle">
        <div class="session-modal-icon">🔒</div>
        <div id="sessionExpiredTitle" class="session-modal-title">Session Ended for Security</div>
        <div class="session-modal-copy">
            Your session has expired because there was no activity for a period of time.
            Please sign in again to continue using the system.
        </div>
        <div class="session-modal-actions">
            <a href="{{ route('login') }}" class="session-modal-btn primary">Continue to Login</a>
            <a href="{{ url('/') }}" class="session-modal-btn secondary">Go to Home</a>
        </div>
    </div>
</body>
</html>
