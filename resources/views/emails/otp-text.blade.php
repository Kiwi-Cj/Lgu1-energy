Your OTP Code

@if($recipientName !== '')
Hello {{ $recipientName }},
@else
Hello,
@endif

Use this one-time password to securely access your {{ $systemName }} account:

{{ $otp }}

This code expires in {{ $expirationMinutes }} {{ $expirationMinutes === 1 ? 'minute' : 'minutes' }}.

For your security, never share this code with anyone. {{ $systemName }} support will never ask for your OTP.

If you did not request this code, you can safely ignore this email.

Thank you,
{{ $systemName }} Team

Copyright {{ date('Y') }} {{ $systemName }}. All rights reserved.
