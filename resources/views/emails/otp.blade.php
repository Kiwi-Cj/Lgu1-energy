
<div style="max-width: 480px; margin: 40px auto; border: 1px solid #eee; border-radius: 8px; background: #fff; font-family: Arial, sans-serif; box-shadow: 0 2px 8px #0001;">
	<div style="text-align:center; padding: 32px 24px 0 24px;">
		<img src="{{ asset('img/logocityhall.png') }}" alt="Energy System Logo" style="max-width: 90px; margin-bottom: 12px;">
	</div>
	<div style="padding: 0 32px 32px 32px;">
		<h2 style="text-align:center; margin-top: 16px; color: #222;">Your OTP Code</h2>
		<p style="margin: 24px 0 0 0; font-size: 15px; color: #333;">Hello,</p>
		<p style="margin: 8px 0 0 0; font-size: 15px; color: #333;">Your One-Time Password (OTP) for secure access is:</p>
		<div style="text-align:center; margin: 24px 0;">
			<span style="display:inline-block; font-size: 2.2em; font-weight: bold; letter-spacing: 2px; color: #2d7d46; background: #f6f6f6; padding: 12px 32px; border-radius: 8px;">{{ $otp }}</span>
		</div>
		<p style="font-size: 15px; color: #333; margin: 0 0 16px 0;">
			⏳ This code will expire in <b>{{ config('otp.expire_minutes', 3) }} minutes</b> for your security.
		</p>
		<p style="font-size: 15px; color: #333; margin: 0 0 16px 0;">
			If you did not request this OTP, please ignore this email. If you need further assistance, feel free to contact our support team.
		</p>
		<p style="font-size: 15px; color: #333; margin: 0 0 8px 0;">Thank you for using Energy System!</p>
		<div style="margin-top: 24px; text-align:center; color: #888; font-size: 13px;">&copy; {{ date('Y') }} Energy System</div>
	</div>
</div>
