<?php $__env->startSection('title', 'Energy Monitoring Dashboard'); ?>

<style>
    .skip-link {
        position: absolute;
        left: -999px;
        top: 10px;
        background: #3762c8;
        color: #fff;
        padding: 8px 16px;
        z-index: 10000;
        border-radius: 6px;
        font-weight: 600;
        transition: left 0.2s;
    }
    .skip-link:focus { left: 10px; }
    
    /* Report Card Styling */
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    @media (max-width: 600px) {
        h1 { font-size: 1.5rem !important; }
        .overview-cards { flex-direction: column !important; gap: 12px !important; }
        .report-card { padding: 15px; }
    }
</style>

<?php $__env->startSection('content'); ?>

<?php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
?>

<a href="#main-content" class="skip-link" tabindex="0">Skip to main content</a>


<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>

<div class="report-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:25px; gap: 20px; flex-wrap: wrap;">
        <div>
            <h1 style="margin:0; font-size:1.8rem; color:#1e293b; font-weight:800; letter-spacing:-0.5px;">
                Energy Trend Monitoring <span style="color:#2563eb;">Dashboard</span>
            </h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:1rem;">Overview of all facility energy performance</p>
        </div>
        
        <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
            <div style="position:relative;">
                <i class="fa fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search facility..." 
                    style="border-radius:10px; border:1px solid #e2e8f0; padding:10px 10px 10px 35px; font-size:0.9rem; width:220px; outline:none; transition:border 0.2s;">
            </div>
            <button type="submit" style="background:#2563eb; color:#fff; border:none; border-radius:10px; padding:10px 20px; font-weight:600; cursor:pointer; transition:0.2s;">Search</button>
            <?php if(request('search')): ?>
                <a href="<?php echo e(url()->current()); ?>" style="color:#e11d48; text-decoration:none; font-weight:600; font-size:0.9rem;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="overview-cards" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background:#f8fafc; padding:20px; border-radius:14px; border-left:4px solid #2563eb;">
            <div style="font-weight:700; font-size:0.75rem; color:#64748b; text-transform:uppercase; margin-bottom:8px;">Total Facilities</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;"><?php echo e($totalFacilities ?? '-'); ?></div>
        </div>

        <div style="background:#fff1f2; padding:20px; border-radius:14px; border-left:4px solid #e11d48;">
            <div style="font-weight:700; font-size:0.75rem; color:#e11d48; text-transform:uppercase; margin-bottom:8px;">High Alert Facilities</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;"><?php echo e($highAlertCount ?? 0); ?></div>
        </div>

        <div style="background:#f0fdf4; padding:20px; border-radius:14px; border-left:4px solid #16a34a;">
            <div style="font-weight:700; font-size:0.75rem; color:#166534; text-transform:uppercase; margin-bottom:8px;">Total Cost (Month)</div>
            <div style="font-weight:800; font-size:1.8rem; color:#1e293b;">₱<?php echo e(number_format($totalEnergyCost ?? 0, 2)); ?></div>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table id="main-content" style="width:100%; border-collapse:separate; border-spacing:0; min-width:1000px;">
            <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:15px; border-radius:10px 0 0 10px; color:#475569; font-weight:700; text-align:center;">Facility</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Type</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Month</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Floor Area</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Baseline kWh</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Trend</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">EUI</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Alerts</th>
                    <th style="padding:15px; color:#475569; font-weight:700; text-align:center;">Recommendation</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php 
                    $record = $facility->currentMonthRecord;
                    $trendAnalysis = '-';
                    $alertLevel = '-';
                    $eui = null;
                    $hasCurrentMonth = $record !== null;
                ?>
                <?php if($hasCurrentMonth): ?>
                    <?php
                        $actualKwh = $record->actual_kwh ?? 0;
                        $floorArea = $facility->floor_area;
                        $eui = ($floorArea > 0) ? number_format($actualKwh / $floorArea, 2) : null;

                        // --- 3-Month Trend Logic (LGU World style, per facility) ---
                        $monthsToCompare = 3;
                        $now = \Carbon\Carbon::create($record->year, $record->month, 1);
                        $currentMonths = [];
                        $previousMonths = [];
                        for ($i = $monthsToCompare - 1; $i >= 0; $i--) {
                            $date = $now->copy()->subMonths($i);
                            $currentMonths[] = ['year' => $date->year, 'month' => $date->month];
                        }
                        for ($i = $monthsToCompare * 2 - 1; $i >= $monthsToCompare; $i--) {
                            $date = $now->copy()->subMonths($i);
                            $previousMonths[] = ['year' => $date->year, 'month' => $date->month];
                        }
                        $currentKwh = 0;
                        foreach ($currentMonths as $m) {
                            $currentKwh += \App\Models\EnergyRecord::where('facility_id', $facility->id)
                                ->where('year', $m['year'])
                                ->where('month', $m['month'])
                                ->sum('actual_kwh');
                        }
                        $previousKwh = 0;
                        foreach ($previousMonths as $m) {
                            $previousKwh += \App\Models\EnergyRecord::where('facility_id', $facility->id)
                                ->where('year', $m['year'])
                                ->where('month', $m['month'])
                                ->sum('actual_kwh');
                        }
                        if ($previousKwh > 0) {
                            $trend = (($currentKwh - $previousKwh) / $previousKwh) * 100;
                            $trendAnalysis = ($trend >= 0 ? '+' : '') . number_format($trend, 2) . '%';
                        } else {
                            $trend = null;
                            $trendAnalysis = '-';
                        }
                        // Alert logic based on facility size
                        $size = $facility->size_label ?? 'Medium';
                        $alert = 'Normal';
                        if($trend !== null){
                            if($size === 'Small'){
                                if($trend > 40) $alert = 'Critical'; elseif($trend > 30) $alert = 'High';
                                elseif($trend > 20) $alert = 'Moderate'; elseif($trend > 10) $alert = 'Low';
                            } elseif($size === 'Medium'){
                                if($trend > 30) $alert = 'Critical'; elseif($trend > 20) $alert = 'High';
                                elseif($trend > 15) $alert = 'Moderate'; elseif($trend > 7) $alert = 'Low';
                            } elseif($size === 'Extra Large'){
                                if($trend > 15) $alert = 'Critical'; elseif($trend > 10) $alert = 'High';
                                elseif($trend > 6) $alert = 'Moderate'; elseif($trend > 2) $alert = 'Low';
                            } else {
                                if($trend > 20) $alert = 'Critical'; elseif($trend > 12) $alert = 'High';
                                elseif($trend > 8) $alert = 'Moderate'; elseif($trend > 4) $alert = 'Low';
                            }
                        }
                        $alertLevel = $alert;
                    ?>
                    <tr style="border-bottom:1px solid #f1f5f9; transition: background 0.2s;">
                        <td style="padding:15px; text-align:center; font-weight:700; color:#334155;"><?php echo e($facility->name); ?></td>
                        <td style="padding:15px; text-align:center; color:#64748b;"><?php echo e($facility->type); ?></td>
                        <td style="padding:15px; text-align:center; font-weight:600;">
                            <?php $monthsArr = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; ?>
                            <?php echo e($monthsArr[$record->month-1] ?? '-'); ?>

                        </td>
                        <td style="padding:15px; text-align:center;"><?php echo e($facility->floor_area ?? '-'); ?> <small>m²</small></td>
                        <td style="padding:15px; text-align:center; color:#2563eb; font-weight:600;">
                            <?php
                                // Get baseline_kwh from the current month's energy record
                                $baselineKwh = $record->baseline_kwh ?? null;
                            ?>
                            <?php echo e($baselineKwh !== null ? number_format($baselineKwh, 2) : '-'); ?>

                        </td>
                        <td style="padding:15px; text-align:center; font-weight:700; color:<?php echo e(str_contains($trendAnalysis, '+') ? '#e11d48' : '#16a34a'); ?>;">
                            <?php echo e($trendAnalysis); ?>

                        </td>
                        <td style="padding:15px; text-align:center;"><?php echo e($eui ?? '-'); ?></td>
                        <td style="padding:15px; text-align:center;">
                            <?php
                                $colors = ['Critical'=>'#7c1d1d','High'=>'#e11d48','Moderate'=>'#f59e42','Low'=>'#16a34a','Normal'=>'#2563eb'];
                                $bgs = ['Critical'=>'#fef2f2','High'=>'#fff1f2','Moderate'=>'#fff7ed','Low'=>'#f0fdf4','Normal'=>'#eff6ff'];
                                $c = $colors[$alertLevel] ?? '#64748b';
                                $b = $bgs[$alertLevel] ?? '#f8fafc';
                            ?>
                            <span style="background:<?php echo e($b); ?>; color:<?php echo e($c); ?>; padding:4px 10px; border-radius:20px; font-size:0.8rem; font-weight:700; border:1px solid <?php echo e($c); ?>33;">
                                <?php echo e($alertLevel); ?>

                            </span>
                        </td>
                        <td style="padding:15px; text-align:center;">
                            <?php
                                $alertIcons = [
                                    'Critical' => ['icon' => '⚠️', 'color' => '#e11d48'],
                                    'High' => ['icon' => '⚡', 'color' => '#f59e42'],
                                    'Moderate' => ['icon' => '🔆', 'color' => '#fbbf24'],
                                    'Low' => ['icon' => '💡', 'color' => '#16a34a'],
                                    'Normal' => ['icon' => '✅', 'color' => '#2563eb'],
                                    'Critical' => ['icon' => '🚨', 'color' => '#7c1d1d'],
                                    'Very High' => ['icon' => '🚩', 'color' => '#e11d48'],
                                    'High' => ['icon' => '⚡', 'color' => '#f59e42'],
                                    'Warning' => ['icon' => '🔔', 'color' => '#f59e42'],
                                    'Normal' => ['icon' => '💡', 'color' => '#16a34a'],
                                ];
                                $iconData = $alertIcons[$alertLevel] ?? ['icon' => 'ℹ️', 'color' => '#64748b'];
                                $trendRecommendations = [
                                    'Critical' => 'Immediate action required! Trend shows a significant increase in energy use. Investigate and resolve excessive consumption.',
                                    'High' => 'High upward trend detected. Review operations and address high energy consumption.',
                                    'Moderate' => 'Moderate increase in trend. Monitor closely and plan for efficiency improvements.',
                                    'Low' => 'Slight upward trend. Consider energy efficiency improvements.',
                                    'Normal' => 'Stable trend. No immediate action required.',
                                ];
                                $trendRecommendation = $trendRecommendations[$alertLevel] ?? 'No recommendation';
                            ?>
                            <button type="button" title="View Recommendation" style="background: none; border: none; color: <?php echo e($iconData['color']); ?>; font-size: 1.3rem; cursor: pointer;" onclick="openRecommendationModal('<?php echo e($facility->id); ?>', '<?php echo e(addslashes($facility->name)); ?>', '<?php echo e($alertLevel); ?>', '<?php echo e(addslashes($trendRecommendation)); ?>')">
                                <span style="font-size:1.3rem;"><?php echo e($iconData['icon']); ?></span>
                            </button>
                        </td>
                       
                    </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="9" style="padding:50px; text-align:center; color:#94a3b8;">No facilities found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(method_exists($facilities, 'links')): ?>
        <div style="margin-top:20px; display:flex; justify-content:center;">
            <?php echo e($facilities->appends(request()->query())->links()); ?>

        </div>
    <?php endif; ?>
<!-- Recommendation Modal -->
<div id="recommendationModal" style="display:none;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div id="recommendationModalBox" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
            <button type="button" onclick="closeRecommendationModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h2 id="recommendationModalTitle" style="margin-bottom:12px;font-size:1.3rem;font-weight:700;"></h2>
            <div id="recommendationText" style="margin:0 0 10px 0;padding:0;font-size:1.08rem;"></div>
            <div style="text-align:right;margin-top:18px;">
                <button type="button" onclick="closeRecommendationModal()" style="background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-weight:600;font-size:1rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('modules.facilities.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
// Logic scripts (Reset & Approval)
function openResetBaselineModal(facilityId) {
    document.getElementById('reset_facility_id').value = facilityId;
    document.getElementById('resetBaselineModal').style.display = 'flex';
}

function toggleEngineerApproval(facilityId) {
    if(!confirm('Toggle engineer approval for this facility?')) return;
    fetch(`/modules/facilities/${facilityId}/toggle-engineer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    }).then(() => location.reload());
}

function openRecommendationModal(facilityId, facilityName, alertLevel, trendRecommendation) {
    const modal = document.getElementById('recommendationModal');
    const title = document.getElementById('recommendationModalTitle');
    const text  = document.getElementById('recommendationText');
    const box   = document.getElementById('recommendationModalBox');
    const alertStyles = {
        'Critical':   { color: '#fff', bg: '#e11d48', icon: '⚠️' },
        'High':       { color: '#fff', bg: '#f59e42', icon: '⚡' },
        'Moderate':   { color: '#222', bg: '#fbbf24', icon: '🔆' },
        'Low':        { color: '#222', bg: '#bbf7d0', icon: '💡' },
        'Normal':     { color: '#fff', bg: '#2563eb', icon: '✅' },
        'Critical': { color: '#fff', bg: '#7c1d1d', icon: '🚨' },
        'Very High': { color: '#fff', bg: '#e11d48', icon: '🚩' },
        'High':    { color: '#fff', bg: '#f59e42', icon: '⚡' },
        'Warning': { color: '#222', bg: '#fde68a', icon: '🔔' },
        'Normal':      { color: '#222', bg: '#bbf7d0', icon: '💡' },
    };
    const style = alertStyles[alertLevel] || { color: '#222', bg: '#f1f5f9', icon: 'ℹ️' };
    title.innerHTML = `<span style='font-size:1.5rem;margin-right:8px;'>${style.icon}</span> Recommendation for ${facilityName}`;
    text.textContent = trendRecommendation || 'No recommendation';
    text.style.color = style.color;
    text.style.background = style.bg;
    text.style.padding = '12px 16px';
    text.style.borderRadius = '8px';
    box.style.background = '#fff';
    modal.style.display = 'flex';
}
function closeRecommendationModal() {
    document.getElementById('recommendationModal').style.display = 'none';
}

// Auto-hide alert
window.addEventListener('DOMContentLoaded', () => {
    const success = document.getElementById('successAlert');
    if(success) setTimeout(() => success.style.opacity = '0', 3000);
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/energy-monitoring/index.blade.php ENDPATH**/ ?>