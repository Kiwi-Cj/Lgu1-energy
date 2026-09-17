METER READING REMINDER: {{ $facilityName }}
{{ $systemName }}
--------------------------------------------------

Hello {{ $recipientName }},

{{ $messageText }}

Facility: {{ $facilityName }}
Period: {{ $isWeekly ? "Week {$weekNumber} ({$periodLabel})" : $periodLabel }}

Click the link below to record the meter reading:
{{ $actionUrl }}

Thank you,
{{ $organizationName }}
© {{ date('Y') }} {{ $systemName }}. All rights reserved.
