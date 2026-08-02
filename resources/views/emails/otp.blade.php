<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your OTP Code</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-shell { padding: 16px 8px !important; }
            .email-content { padding: 28px 20px !important; }
            .otp-code { font-size: 30px !important; letter-spacing: 6px !important; padding: 14px 18px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f7fb; color:#273142; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f4f7fb;">
    <tr>
        <td class="email-shell" align="center" style="padding:40px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:560px; background-color:#ffffff; border:1px solid #e5eaf1; border-radius:12px;">
                <tr>
                    <td class="email-content" style="padding:36px 40px;">
                        <div style="text-align:center; margin-bottom:24px;">
                            @if($logoSrc)
                                <img src="{{ $logoSrc }}" width="72" alt="{{ $systemName }}" style="display:inline-block; width:72px; max-width:72px; height:auto; border:0; border-radius:16px;">
                            @else
                                <div style="display:inline-block; min-width:72px; padding:15px 18px; border:1px solid #bfdbfe; border-radius:16px; background-color:#eff6ff; color:#1d4ed8; font-size:16px; font-weight:700; line-height:1.2; letter-spacing:0.04em;">
                                    {{ $systemShortName }}
                                </div>
                            @endif
                            <div style="margin-top:10px; color:#64748b; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">
                                Secure account verification
                            </div>
                        </div>

                        <h1 style="margin:0 0 28px; color:#1f2937; font-size:24px; line-height:1.3; text-align:center;">Your OTP Code</h1>

                        <p style="margin:0 0 12px; color:#374151; font-size:16px; line-height:1.6;">
                            @if($recipientName !== '')
                                Hello {{ $recipientName }},
                            @else
                                Hello,
                            @endif
                        </p>
                        <p style="margin:0; color:#374151; font-size:16px; line-height:1.6;">Use this one-time password to securely access your {{ $systemName }} account:</p>

                        <div style="margin:26px 0; text-align:center;">
                            <span class="otp-code" style="display:inline-block; padding:16px 26px; border:1px solid #c7d7fe; border-radius:10px; background-color:#eef4ff; color:#2457c5; font-family:'Courier New', Courier, monospace; font-size:36px; font-weight:700; line-height:1; letter-spacing:8px;">{{ $otp }}</span>
                        </div>

                        <p style="margin:0 0 18px; color:#374151; font-size:16px; line-height:1.6; text-align:center;">
                            This code expires in <strong>{{ $expirationMinutes }} {{ $expirationMinutes === 1 ? 'minute' : 'minutes' }}</strong>.
                        </p>

                        <div style="margin:0 0 22px; padding:14px 16px; border-left:4px solid #f59e0b; background-color:#fffbeb; color:#78350f; font-size:14px; line-height:1.6;">
                            For your security, never share this code with anyone. {{ $systemName }} support will never ask for your OTP.
                        </div>

                        <p style="margin:0 0 18px; color:#4b5563; font-size:15px; line-height:1.6;">If you did not request this code, you can safely ignore this email.</p>
                        <p style="margin:0; color:#374151; font-size:15px; line-height:1.6;">Thank you,<br><strong>{{ $systemName }} Team</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px; border-top:1px solid #e5eaf1; color:#8a94a3; font-size:13px; line-height:1.5; text-align:center;">
                        &copy; {{ date('Y') }} {{ $systemName }}. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
