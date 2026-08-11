Your {{ $systemShortName }} Account Is Ready

Hello, {{ trim($recipientName) !== '' ? $recipientName : $recipientEmail }}.

An administrator created your account with the {{ ucwords(str_replace(['_', '-'], ' ', $role)) }} role.

Email address: {{ $recipientEmail }}
Temporary password: {{ $temporaryPassword }}

Sign in: {{ $loginUrl }}

Change the temporary password immediately after signing in. Never share your password or verification codes with anyone.

If you were not expecting this account, do not sign in. Contact your system administrator for assistance.

{{ $organizationName }}
Copyright {{ date('Y') }} {{ $systemName }}. All rights reserved.
