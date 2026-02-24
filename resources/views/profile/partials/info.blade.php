<div style="padding:18px 0;">
    <h4 style="font-size:1.15rem;font-weight:700;margin-bottom:18px;">Basic Information</h4>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px 24px;">
        <div><label style="color:#64748b;font-size:0.98rem;">Full Name</label><div style="font-size:1.08rem;font-weight:600;">{{ auth()->user()->full_name ?? auth()->user()->name }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Employee/User ID</label><div style="font-size:1.08rem;">{{ auth()->user()->id }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Position / Role</label><div style="font-size:1.08rem;">{{ ucwords(str_replace('_', ' ', (string) (auth()->user()?->role_key ?? auth()->user()?->role ?? 'User'))) }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Department / Office</label><div style="font-size:1.08rem;">{{ auth()->user()->department ?? '-' }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Assigned Facility</label><div style="font-size:1.08rem;">{{ auth()->user()->facility?->name ?? 'None' }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Email Address</label><div style="font-size:1.08rem;">{{ auth()->user()->email }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Contact Number</label><div style="font-size:1.08rem;">{{ auth()->user()->contact_number ?? '-' }}</div></div>
        <div><label style="color:#64748b;font-size:0.98rem;">Username</label><div style="font-size:1.08rem;">{{ auth()->user()->username }}</div></div>
    </div>
</div>
