{{-- Sidebar partial --}}
<div class="sidebar-nav">
    <div class="sidebar-top">
        <div class="site-logo">
            <img src="{{ asset('img/logocityhall.png') }}" alt="LGU Logo">
            <div class="sidebar-divider"></div>
        </div>
        <ul class="nav-list">
            <li><a href="{{ url('modules/dashboard') }}" class="nav-link" id="dashboard-link">Dashboard</a></li>
            <li><a href="{{ url('modules/facilities') }}" class="nav-link" id="facility-link">Facility</a></li>
            {{-- Users link removed from sidebar for all roles --}}
            <li><a href="{{ url('modules/energy') }}" class="nav-link" id="energy-link">Energy</a></li>
            <li><a href="{{ url('modules/billing') }}" class="nav-link" id="billing-link">Billing</a></li>
            <li><a href="#" class="nav-link" id="efficiency-link">Efficiency</a></li>
            <li><a href="{{ url('modules/maintenance') }}" class="nav-link" id="maintenance-link">Maintenance</a></li>
            <li><a href="{{ url('modules/reccomendation') }}" class="nav-link" id="reccomendation-link">Reccomendation</a></li>
            <li><a href="#" class="nav-link" id="reports-link">Reports</a></li>
            <li><a href="{{ url('modules/setting') }}" class="nav-link" id="setting-link">Setting</a></li>
        </ul>
    </div>
    <div class="sidebar-divider"></div>
    <div class="user-info">
        <div class="user-welcome">Welcome, Kent</div>
        <button class="logout-btn">Logout</button>
    </div>
</div>
