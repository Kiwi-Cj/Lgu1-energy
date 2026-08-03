<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your {{ $systemShortName }} Account Is Ready</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 10px 6px !important; }
            .email-header { padding: 22px 20px !important; }
            .email-content { padding: 24px 20px !important; }
            .email-title { font-size: 23px !important; }
            .credential-value { font-size: 14px !important; word-break: break-all !important; }
            .signin-button { display: block !important; text-align: center !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eef3f9;color:#172033;font-family:Arial,Helvetica,sans-serif;">
@php
    $roleLabel = ucwords(str_replace(['_', '-'], ' ', $role));
    $displayName = trim($recipientName) !== '' ? $recipientName : $recipientEmail;
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#eef3f9;">
    <tr>
        <td class="email-shell" align="center" style="padding:24px 14px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:580px;border:1px solid #dbe5f1;border-radius:16px;background-color:#ffffff;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,.09);">
                <tr>
                    <td class="email-header" style="padding:26px 30px;background-color:#2457c5;background-image:linear-gradient(135deg,#173f96 0%,#2563d9 58%,#299ee7 100%);color:#ffffff;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div style="display:inline-block;padding:7px 10px;border:1px solid rgba(255,255,255,.32);border-radius:8px;background-color:rgba(255,255,255,.13);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">
                                        {{ $systemShortName }}
                                    </div>
                                    <h1 style="margin:13px 0 5px;font-size:25px;line-height:1.25;font-weight:800;letter-spacing:-.02em;">Your account is ready</h1>
                                    <p style="margin:0;color:#dbeafe;font-size:13px;line-height:1.5;">Secure access to {{ $systemName }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="email-content" style="padding:28px 30px;">
                        <div style="margin-bottom:6px;color:#2563eb;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Account created</div>
                        <h2 class="email-title" style="margin:0 0 13px;color:#172033;font-size:25px;line-height:1.25;font-weight:800;letter-spacing:-.025em;">Hello, {{ $displayName }}</h2>
                        <p style="margin:0 0 19px;color:#526177;font-size:14px;line-height:1.65;">
                            An administrator created your account with the
                            <strong style="display:inline-block;padding:2px 7px;border-radius:999px;background-color:#eef4ff;color:#1e4fa5;font-size:12px;">{{ $roleLabel }}</strong>
                            role. Use the temporary credentials below for your first sign-in.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0 0 18px;border:1px solid #cfe0f5;border-radius:12px;background-color:#f6f9fe;">
                            <tr>
                                <td style="padding:13px 16px;border-bottom:1px solid #dce8f6;">
                                    <div style="margin-bottom:4px;color:#718096;font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;">Email address</div>
                                    <a class="credential-value" href="mailto:{{ $recipientEmail }}" style="color:#1558c0;font-size:14px;font-weight:700;text-decoration:none;">{{ $recipientEmail }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px;">
                                    <div style="margin-bottom:4px;color:#718096;font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;">Temporary password</div>
                                    <span class="credential-value" style="display:inline-block;padding:6px 9px;border:1px solid #d6e2f1;border-radius:7px;background-color:#ffffff;color:#172033;font-family:Consolas,'Courier New',monospace;font-size:15px;font-weight:700;letter-spacing:.035em;">{{ $temporaryPassword }}</span>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-bottom:18px;">
                            <tr>
                                <td align="center" style="border-radius:11px;background-color:#2563eb;">
                                    <a class="signin-button" href="{{ $loginUrl }}" style="display:inline-block;padding:12px 24px;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;">Sign in to your account &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>

                        <div style="padding:12px 14px;border:1px solid #fde3a7;border-radius:10px;background-color:#fffaf0;color:#7c4a03;font-size:12px;line-height:1.55;">
                            <strong style="display:block;margin-bottom:3px;color:#6b3d00;">Protect your account</strong>
                            Change the temporary password immediately after signing in. Never share your password or verification codes with anyone.
                        </div>

                        <p style="margin:16px 0 0;color:#64748b;font-size:11px;line-height:1.55;">
                            If you were not expecting this account, do not sign in. Contact your system administrator for assistance.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:14px 22px;border-top:1px solid #e5ebf3;background-color:#f8fafc;text-align:center;color:#7b8798;font-size:10px;line-height:1.55;">
                        <strong style="color:#536174;">{{ $organizationName }}</strong><br>
                        &copy; {{ date('Y') }} {{ $systemName }}. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
