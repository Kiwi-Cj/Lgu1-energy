<div style="padding:18px 0;">
    <h4 style="font-size:1.15rem;font-weight:700;margin-bottom:18px;">Audit & System Info</h4>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px 14px;align-items:center;">
        <div><span style="color:#64748b;font-size:0.98rem;">Account Created</span><div style="font-size:1.08rem;">{{ auth()->user()->created_at }}</div></div>
        <div><span style="color:#64748b;font-size:0.98rem;">Last Updated</span><div style="font-size:1.08rem;">{{ auth()->user()->updated_at }}</div></div>
        <div><span style="color:#64748b;font-size:0.98rem;">Created By</span><div style="font-size:1.08rem;">{{ auth()->user()->created_by ?? 'System Admin' }}</div></div>
    </div>
</div>