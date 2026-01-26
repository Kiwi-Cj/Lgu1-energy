<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<ul class="sidebar-menu">
    <li><a href="/admin-dashboard"><span class="menu-icon">📊</span><span>Dashboard</span></a></li>
    <li id="facilitiesDropdown" class="has-submenu">
        <a href="#" onclick="toggleSubmenu('facilitiesDropdown'); return false;">
            <i class="fas fa-building menu-icon"></i>
            Facilities Management
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu">
            <li><a href="/modules/facilities/facilities-list"><i class="fas fa-list menu-icon"></i> Facilities List</a></li>
            <li><a href="/modules/facilities/energy-usage"><i class="fas fa-bolt menu-icon"></i> Energy Usage</a></li>
            <li><a href="/modules/facilities/equipment-inventory"><i class="fas fa-tools menu-icon"></i> Equipment Inventory</a></li>
            <li><a href="/modules/facilities/consumption-history"><i class="fas fa-history menu-icon"></i> Consumption History</a></li>
        </ul>
    </li>
    <li><a href="/modules/consumption"><span class="menu-icon">⚡</span><span>Energy Consumption</span></a></li>
    <li><a href="/modules/billing"><span class="menu-icon">💰</span><span>Billing & Cost Analysis</span></a></li>
    <li><a href="/modules/projects"><span class="menu-icon">🏗️</span><span>Energy Efficiency Projects</span></a></li>
    <li><a href="/modules/alerts"><span class="menu-icon">🤖</span><span>AI Alerts & Recommendations</span></a></li>
    <li><a href="/modules/reports"><span class="menu-icon">📈</span><span>Reports & Analytics</span></a></li>
    <!-- Users & Roles link removed from sidebar -->
    <li><a href="/modules/settings"><span class="menu-icon">⚙️</span><span>System Settings</span></a></li>
</ul>
<script>
function toggleSubmenu(id) {
    var li = document.getElementById(id);
    if (li.classList.contains('open')) {
        li.classList.remove('open');
    } else {
        document.querySelectorAll('.sidebar-menu .has-submenu.open').forEach(function(openLi) {
            openLi.classList.remove('open');
        });
        li.classList.add('open');
    }
}
</script>
