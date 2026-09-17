<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meter Reading Reminder: {{ $facilityName }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { padding: 10px 6px !important; }
            .email-header { padding: 22px 20px !important; }
            .email-content { padding: 24px 20px !important; }
            .email-title { font-size: 22px !important; }
            .action-button { display: block !important; text-align: center !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eef3f9;color:#172033;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#eef3f9;">
    <tr>
        <td class="email-shell" align="center" style="padding:24px 14px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:580px;border:1px solid #dbe5f1;border-radius:16px;background-color:#ffffff;overflow:hidden;box-shadow:0 12px 34px rgba(15,23,42,.09);">
                <tr>
                    <td class="email-header" style="padding:26px 30px;background-color:#0284c7;background-image:linear-gradient(135deg,#0369a1 0%,#0284c7 60%,#38bdf8 100%);color:#ffffff;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="vertical-align:middle;">
                                    <div style="display:inline-block;padding:7px 10px;border:1px solid rgba(255,255,255,.32);border-radius:8px;background-color:rgba(255,255,255,.15);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">
                                        ⚡ {{ $systemShortName }} Scheduled Log
                                    </div>
                                    <h1 style="margin:13px 0 5px;font-size:24px;line-height:1.25;font-weight:800;letter-spacing:-.02em;">Meter Reading Reminder</h1>
                                    <p style="margin:0;color:#e0f2fe;font-size:13px;line-height:1.5;">Scheduled energy data collection</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="email-content" style="padding:28px 30px;">
                        <div style="margin-bottom:6px;color:#0284c7;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Scheduled reminder</div>
                        <h2 class="email-title" style="margin:0 0 13px;color:#172033;font-size:23px;line-height:1.25;font-weight:800;letter-spacing:-.025em;">Hello, {{ $recipientName }}</h2>
                        <p style="margin:0 0 19px;color:#526177;font-size:14px;line-height:1.65;">
                            {{ $messageText }}
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0 0 20px;border:1px solid #e0f2fe;border-radius:12px;background-color:#f0f9ff;">
                            <tr>
                                <td style="padding:13px 16px;border-bottom:1px solid #e0f2fe;">
                                    <div style="margin-bottom:4px;color:#0369a1;font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;">Facility</div>
                                    <strong style="color:#0c4a6e;font-size:15px;">{{ $facilityName }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px;border-bottom:1px solid #e0f2fe;">
                                    <div style="margin-bottom:4px;color:#0369a1;font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;">Target Period</div>
                                    <span style="color:#0c4a6e;font-size:14px;font-weight:700;">{{ $isWeekly ? "Week {$weekNumber} · {$periodLabel}" : $periodLabel }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px;">
                                    <div style="margin-bottom:4px;color:#0369a1;font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;">Action Required</div>
                                    <span style="color:#0369a1;font-size:13px;font-weight:600;">Inspect the main meter dial and log the reading into the portal.</span>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin-bottom:18px;">
                            <tr>
                                <td align="center" style="border-radius:11px;background-color:#0284c7;">
                                    <a class="action-button" href="{{ $actionUrl }}" style="display:inline-block;padding:12px 24px;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;">Record Meter Reading Now &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:16px 0 0;color:#64748b;font-size:11px;line-height:1.55;">
                            Keeping meter readings up to date helps the LGU monitor consumption trends, detect power leaks early, and ensure accurate energy accounting.
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
