

<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div style="margin-bottom:18px;">
        <a href="<?php echo e(route('modules.maintenance.index')); ?>" class="btn btn-secondary" style="background:#e5e7eb;color:#2563eb;padding:8px 20px;border-radius:8px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(37,99,235,0.08);display:inline-block;">
            <i class="fa fa-arrow-left" style="margin-right:7px;"></i> Back to Maintenance
        </a>
    </div>
    <h2 class="mb-4" style="font-size:2rem;font-weight:700;color:#3762c8;">Maintenance History</h2>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Facility</option>
                <?php $__currentLoopData = \App\Models\Facility::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php if(request('facility_id') == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="month" style="font-weight:700;margin-bottom:4px;">Month</label>
            <select name="month" id="month" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Month</option>
                <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="maintenance_type" style="font-weight:700;margin-bottom:4px;">Type</label>
            <select name="maintenance_type" id="maintenance_type" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Type</option>
                <option value="Preventive" <?php if(request('maintenance_type') == 'Preventive'): ?> selected <?php endif; ?>>Preventive</option>
                <option value="Corrective" <?php if(request('maintenance_type') == 'Corrective'): ?> selected <?php endif; ?>>Corrective</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="status" style="font-weight:700;margin-bottom:4px;">Status</label>
            <select name="status" id="status" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Status</option>
                <option value="Pending" <?php if(request('status') == 'Pending'): ?> selected <?php endif; ?>>Pending</option>
                <option value="Ongoing" <?php if(request('status') == 'Ongoing'): ?> selected <?php endif; ?>>Ongoing</option>
                <option value="Completed" <?php if(request('status') == 'Completed'): ?> selected <?php endif; ?>>Completed</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
        </div>
    </form>
    <div class="card" style="box-shadow:0 2px 8px rgba(37,99,235,0.08);border-radius:14px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" style="background:#fff;border-radius:12px;overflow:hidden;">
                    <thead style="background:#f3f4f6;">
                        <tr>
                            <th>ID</th>
                            <th>Facility</th>
                            <th>Issue Type</th>
                            <th>Trigger Month</th>
                            <th>Efficiency</th>
                            <th>Trend</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Assigned</th>
                            <th>Completed</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $historyRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td style="text-align:center;"><?php echo e($row['id']); ?></td>
                            <td style="text-align:center;"><span style="font-weight:600;color:#2563eb;"><?php echo e($row['facility']); ?></span></td>
                            <td style="text-align:center;"><span style="color:#e11d48;font-weight:500;"><?php echo e($row['issue_type']); ?></span></td>
                            <td style="text-align:center;"><?php echo e($row['trigger_month']); ?></td>
                            <td style="text-align:center;">
                                <?php if($row['efficiency_rating'] === 'High'): ?>
                                    <span style="background:#bbf7d0;color:#15803d;padding:3px 10px;border-radius:8px;font-weight:600;">High</span>
                                <?php elseif($row['efficiency_rating'] === 'Medium'): ?>
                                    <span style="background:#fef9c3;color:#a16207;padding:3px 10px;border-radius:8px;font-weight:600;">Medium</span>
                                <?php else: ?>
                                    <span style="background:#fee2e2;color:#b91c1c;padding:3px 10px;border-radius:8px;font-weight:600;">Low</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;"><?php echo e($row['trend']); ?></td>
                            <td style="text-align:center;"><?php echo e($row['maintenance_type']); ?></td>
                            <td style="text-align:center;">
                                <span style="background:#e0f7fa;color:#0097a7;padding:3px 10px;border-radius:8px;font-weight:600;"><?php echo e($row['maintenance_status']); ?></span>
                            </td>
                            <td style="text-align:center;"><?php echo e($row['scheduled_date']); ?></td>
                            <td style="text-align:center;"><?php echo e($row['assigned_to']); ?></td>
                            <td style="text-align:center;"><span style="color:#22c55e;font-weight:600;"><?php echo e($row['completed_date']); ?></span></td>
                            <td style="text-align:center;"><?php echo e($row['remarks']); ?></td>
                            <td style="text-align:center;">
                                <form action="<?php echo e(route('modules.maintenance.history.destroy', $row['id'])); ?>" method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" onclick="return confirm('Delete this history record?')" style="background:none;border:none;color:#e11d48;font-size:1.1rem;cursor:pointer;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="12" class="text-center">No maintenance history found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/maintenance/history.blade.php ENDPATH**/ ?>