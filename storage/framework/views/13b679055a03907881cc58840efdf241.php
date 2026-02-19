
<?php $__env->startSection('title', 'Energy'); ?>

<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php $__env->startSection('content'); ?>

        <div class="report card" style="padding:32px 24px 32px 24px; background:#f8fafc; border-radius:18px; box-shadow:0 8px 32px rgba(37,99,235,0.09); margin-bottom:32px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:18px;">
            <h3 style="font-size:1.35rem;font-weight:700;color:#2563eb;margin:0;letter-spacing:0.5px;">Incident Records</h3>
            <div style="display:flex;gap:14px;">
                <a href="<?php echo e(route('energy-incidents.history')); ?>" style="background:linear-gradient(90deg,#6366f1,#2563eb);color:#fff;padding:10px 28px;font-weight:600;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.13);font-size:1.05rem;transition:0.2s;text-decoration:none;display:flex;align-items:center;gap:8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    View History
                </a>
            <?php
                $alert = $incident->energyRecord->alert ?? $incident->alert_level ?? 'Normal / level 1';
                // Use the full label from backend
                if (strpos($alert, 'Extreme / level 5') !== false) {
                    $alertLabel = 'Extreme / Level 5';
                    $alertColor = '#7c1d1d'; // dark red
                } elseif (strpos($alert, 'Extreme / level 4') !== false) {
                    $alertLabel = 'Extreme / Level 4';
                    $alertColor = '#e11d48'; // red
                } elseif (strpos($alert, 'High / level 3') !== false) {
                    $alertLabel = 'High / Level 3';
                    $alertColor = '#f59e42'; // orange
                } elseif (strpos($alert, 'Warning / level 2') !== false) {
                    $alertLabel = 'Warning / Level 2';
                    $alertColor = '#f59e42'; // orange
                } else {
                    $alertLabel = 'Normal / Level 1';
                    $alertColor = '#16a34a'; // green
                }
            ?>
                    <button class="incident-modal-close" onclick="closeIncidentModal(<?php echo e($incident->id); ?>)" aria-label="Close modal">&times;</button>
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                        <div style="font-size:2.1rem;color:#e11d48;"><i class='fa fa-bolt'></i></div>
                        <h3 style="margin:0;font-size:1.45rem;font-weight:900;color:#1e293b;letter-spacing:-0.5px;">Energy Incident Report</h3>
                    </div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Facility:</span> <span style="font-weight:800;color:#2563eb;"><?php echo e($incident->facility->name ?? '-'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Month/Year:</span> <span style="font-weight:800;"><?php echo e($monthLabel); ?>/<?php echo e($yearNum ?? '-'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Deviation:</span> <span style="font-weight:800;color:#e11d48;"><?php echo e($dpn !== null ? number_format($dpn, 2) . '%' : '-'); ?></span></div>
                    <div style="margin-bottom:18px;">
                        <span style="font-weight:700;color:#64748b;">Alert Severity:</span> 
                        <?php
                            $alert = $incident->energyRecord->alert ?? $incident->alert_level ?? 'High';
                            $alertColor = $alert === 'High' ? '#e11d48' : ($alert === 'Medium' ? '#f59e42' : '#16a34a');
                        ?>
                        <span style="font-weight:900;color:<?php echo e($alertColor); ?>;text-transform:uppercase;"><?php echo e($alertLabel); ?></span>
                    </div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Status:</span> <span style="font-weight:800;"><?php echo e($incident->status ?? 'Open'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Date Detected:</span> <span style="font-weight:800;"><?php echo e($dateDetected); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Description:</span> <span style="font-weight:500;"><?php echo e($incident->description ?? 'System detected unusually high energy consumption for this period. Please review and validate.'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Probable Cause:</span> <span style="font-weight:500;"><?php echo e($incident->probable_cause ?: 'Automated system analysis: Abnormal usage pattern detected based on recent records.'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Immediate Action:</span> <span style="font-weight:500;"><?php echo e($incident->immediate_action ?: 'Incident flagged for LGU review and action.'); ?></span></div>
                    <div style="margin-bottom:18px;"><span style="font-weight:700;color:#64748b;">Resolution:</span> <span style="font-weight:500;"><?php echo e($incident->resolution_summary ?: 'Pending review by LGU energy officer or facility manager.'); ?></span></div>
                    <div style="margin-bottom:24px;"><span style="font-weight:700;color:#64748b;">Preventive Recommendation:</span> <span style="font-weight:500;"><?php echo e($incident->preventive_recommendation ?: 'Regularly monitor facility energy trends and investigate any unusual spikes immediately.'); ?></span></div>
                    <?php if(!empty($incident->attachments)): ?>
                        <div style="margin-bottom:24px;"><span style="font-weight:700;color:#64748b;">Attachments:</span> <a href="<?php echo e(asset('storage/'.$incident->attachments)); ?>" target="_blank">View</a></div>
                    <?php endif; ?>
                    <div style="margin-top:32px; text-align:right;">
                        <a href="<?php echo e(route('modules.maintenance.index')); ?>?facility_id=<?php echo e($incident->facility->id ?? ''); ?>" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:12px 30px;font-weight:800;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.13);font-size:1.08rem;transition:0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            View Maintenance
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?>
            <div style="text-align:center; color:#64748b; padding:18px 0;">No incidents found.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($incident->facility): ?>
        <div id="facility-modal-<?php echo e($incident->facility->id); ?>" class="incident-modal" style="display:none;">
            <div class="incident-modal-content" style="position:relative;">
                <button class="incident-modal-close" onclick="closeFacilityModal(<?php echo e($incident->facility->id); ?>)" aria-label="Close modal">&times;</button>
                <h3 style="margin-top:0;margin-bottom:24px;font-size:1.3rem;font-weight:700;color:#2563eb;">Facility Details</h3>
                <div style="margin-bottom:18px;"><b>Name:</b> <?php echo e($incident->facility->name ?? '-'); ?></div>
                <div style="margin-bottom:18px;"><b>Type:</b> <?php echo e($incident->facility->type ?? '-'); ?></div>
                <div style="margin-bottom:18px;"><b>Size:</b> <?php echo e($incident->facility->size ?? '-'); ?></div>
                <div style="margin-bottom:18px;"><b>Status:</b> <?php echo e($incident->facility->status ?? '-'); ?></div>
                <div style="margin-bottom:18px;"><b>Address:</b> <?php echo e($incident->facility->address ?? '-'); ?></div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<script>
function showIncidentModal(id) {
    document.getElementById('incident-modal-' + id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeIncidentModal(id) {
    document.getElementById('incident-modal-' + id).style.display = 'none';
    document.body.style.overflow = '';
}
function showFacilityModal(id) {
    document.getElementById('facility-modal-' + id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeFacilityModal(id) {
    document.getElementById('facility-modal-' + id).style.display = 'none';
    document.body.style.overflow = '';
}
</script>
<style>
.incident-list-container {
    background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08); padding:0; margin-bottom:32px;
}
.incident-list-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 24px;
    border-bottom:1px solid #e5e7eb;
    cursor:pointer;
    background:#fff;
    border-radius:14px;
    margin:10px 18px;
    box-shadow:0 1px 4px rgba(31,38,135,0.06);
    transition:box-shadow 0.18s, background 0.18s, transform 0.16s;
    position:relative;
}
.incident-list-row:last-child { border-bottom:none; }
.incident-list-row:hover, .incident-list-row:focus {
    background:#f5f8ff;
    box-shadow:0 6px 24px rgba(55,98,200,0.13);
    transform:translateY(-2px) scale(1.012);
    z-index:2;
}
.incident-list-main { display:grid; grid-template-columns:2fr 1.5fr; gap:48px; width:100%; align-items:center; }
.incident-facility { font-weight:600; color:#222; }
.incident-date, .incident-deviation, .incident-status, .incident-detected { color:#334155; font-size:1.01rem; }
.incident-alert.high { color:#e11d48; font-weight:700; }
.incident-alert.medium { color:#f59e42; font-weight:700; }
.incident-alert.low { color:#2563eb; font-weight:700; }
.incident-modal {
    position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(30,41,59,0.18); display:flex; align-items:center; justify-content:center; z-index:1000;
}
.incident-modal-content {
    background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(31,38,135,0.18); padding:36px 24px 28px 24px; min-width:340px; max-width:95vw; max-height:90vh; overflow-y:auto; position:relative;
}
.incident-modal-close {
    position:absolute; top:18px; right:18px; background:none; border:none; font-size:2rem; color:#64748b; cursor:pointer; transition:color 0.15s;
}
.incident-modal-close:hover { color:#e11d48; }
@media (max-width: 900px) {
    .incident-list-main { grid-template-columns:1.5fr 1fr 1fr 1fr 1fr 1.2fr; font-size:0.98rem; }
    .incident-modal-content { padding:18px 8px; }
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-incident/incidents.blade.php ENDPATH**/ ?>