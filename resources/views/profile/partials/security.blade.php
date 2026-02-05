<div style="padding:18px 0;">
    <h4 style="font-size:1.15rem;font-weight:700;margin-bottom:18px;">Account & Security</h4>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px 24px;">
        <div><label style="color:#64748b;font-size:0.98rem;">Status</label><div style="font-size:1.08rem;">{{ ucfirst(auth()->user()->status) }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Last Login</label><div style="font-size:1.08rem;">{{ auth()->user()->last_login_at ?? 'N/A' }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">2FA / OTP</label><div style="font-size:1.08rem;">{{ auth()->user()->otp_enabled ? 'Enabled' : 'Disabled' }}</div></div>
    </div>
    <div style="margin-top:18px;text-align:right;">
        <a href="/profile/change-password" style="color:#2563eb;font-weight:600;font-size:1.05rem;text-decoration:underline;">Change Password</a>
    </div>
</div>