<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Message</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;padding:24px;">
        <h2 style="margin:0 0 16px;font-size:20px;">New Contact Form Message</h2>

        <p style="margin:0 0 8px;"><strong>Name:</strong> {{ $contactMessage->name }}</p>
        <p style="margin:0 0 8px;"><strong>Email:</strong> {{ $contactMessage->email }}</p>

        @if($contactMessage->subject)
            <p style="margin:0 0 8px;"><strong>Subject:</strong> {{ $contactMessage->subject }}</p>
        @endif

        <p style="margin:0 0 8px;"><strong>Submitted:</strong> {{ $contactMessage->created_at?->format('M d, Y h:i A') }}</p>

        @if($contactMessage->ip_address)
            <p style="margin:0 0 16px;"><strong>IP Address:</strong> {{ $contactMessage->ip_address }}</p>
        @endif

        <div style="margin-top:16px;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb;">
            <strong style="display:block;margin-bottom:8px;">Message</strong>
            <div style="white-space:pre-wrap;line-height:1.5;">{{ $contactMessage->message }}</div>
        </div>
    </div>
</body>
</html>
