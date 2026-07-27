<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your LGU Energy Efficiency System Account</title>
</head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#243b5a;">
    @php
        $roleLabel = ucwords(str_replace(['_', '-'], ' ', $role));
        $displayName = trim($recipientName) !== '' ? $recipientName : $recipientEmail;
    @endphp

    <div style="max-width:750px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(30,64,175,0.12);">
        <div style="padding:46px 32px 38px;text-align:center;background:linear-gradient(135deg,#6384d5 0%,#2658cc 100%);color:#ffffff;">
            <h1 style="margin:0;font-size:30px;line-height:1.25;font-weight:700;">LGU Energy Efficiency System</h1>
            <p style="margin:14px 0 0;font-size:17px;line-height:1.5;color:#eef4ff;">Barangay Culiat, Quezon City</p>
        </div>

        <div style="padding:50px;">
            <h2 style="margin:0 0 30px;font-size:29px;line-height:1.3;color:#243f65;">Welcome to the LGU Energy Efficiency System</h2>

            <p style="margin:0 0 24px;font-size:18px;line-height:1.65;color:#526077;">
                Hi <strong>{{ $displayName }}</strong>,
            </p>

            <p style="margin:0 0 26px;font-size:18px;line-height:1.65;color:#526077;">
                An administrator has created a <strong>{{ $roleLabel }}</strong> account for you.
                Use the credentials below to sign in.
            </p>

            <div style="margin:0 0 26px;padding:22px 26px;background:#eef6ff;border-left:5px solid #2296f3;border-radius:11px;color:#334155;">
                <p style="margin:0 0 14px;font-size:16px;line-height:1.5;">
                    <strong>Email:</strong>
                    <a href="mailto:{{ $recipientEmail }}" style="color:#1d5fd1;text-decoration:underline;">{{ $recipientEmail }}</a>
                </p>
                <p style="margin:0;font-size:16px;line-height:1.5;">
                    <strong>Temporary password:</strong>
                    <span style="display:inline-block;margin-left:6px;padding:4px 9px;background:#ffffff;border-radius:5px;font-family:Consolas,Monaco,monospace;color:#334155;">{{ $temporaryPassword }}</span>
                </p>
            </div>

            <a href="{{ $loginUrl }}"
               style="display:inline-block;padding:14px 38px;border-radius:9px;background:#6384d5;color:#ffffff;text-decoration:none;font-size:18px;font-weight:700;">
                Sign In
            </a>

            <p style="margin:28px 0 0;font-size:14px;line-height:1.6;color:#64748b;">
                For your security, sign in promptly and change the temporary password. If you were not expecting this account, contact your system administrator.
            </p>
        </div>

        <div style="padding:18px 28px;text-align:center;background:#f8fafc;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px;">
            &copy; {{ date('Y') }} LGU Energy Efficiency System
        </div>
    </div>
</body>
</html>
