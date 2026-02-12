
<?php $__env->startSection('title', 'Efficiency Summary Report'); ?>

<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php $__env->startSection('content'); ?>
<style>
    /* Report Card Container */
    .report-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        padding: 30px;
        border: 1px solid #eef2f6;
        margin-bottom: 2rem;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 30px;
    }
    .page-header h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .page-header h2 span { color: #2563eb; }
    .page-header p { color: #64748b; margin-top: 5px; font-size: 1rem; }

    /* Filter Section */
    .filter-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 25px;
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-group label { font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; }
    .filter-group select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        min-width: 180px;
        background: #fff;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }
    .filter-group select:focus { border-color: #2563eb; outline: none; }

    .btn-filter {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }
    .btn-filter:hover { transform: translateY(-1px); opacity: 0.9; }

    /* Table Styling */
    .table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .custom-table { width: 100%; border-collapse: collapse; background: #fff; text-align: center; }
    .custom-table thead { background: #f1f5f9; }
    .custom-table th { 
        padding: 15px; 
        font-size: 0.85rem; 
        font-weight: 700; 
        color: #475569; 
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
    }
    .custom-table td { 
        padding: 15px; 
        border-bottom: 1px solid #f1f5f9; 
        color: #334155; 
        font-size: 0.95rem; 
        vertical-align: middle;
    }
    .custom-table tr:hover { background-color: #f8fafc; }

    /* Rating Badges */
    .rating-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .rating-high { background: #dcfce7; color: #166534; }
    .rating-medium { background: #fef9c3; color: #854d0e; }
    .rating-low { background: #fee2e2; color: #991b1b; }
</style>

<div class="report-card">
    <div class="page-header">
        <h2>⚡ Efficiency <span>Summary Report</span></h2>
        <p>Real-time overview of energy efficiency and EUI metrics across all active facilities.</p>
    </div>

    <form method="GET" action="" class="filter-section">
        <div class="filter-group">
            <label for="facility_id">Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="">All Facilities</option>
                <?php $__currentLoopData = $facilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php echo e((isset($selectedFacility) && $selectedFacility == $facility->id) ? 'selected' : ''); ?>>
                        <?php echo e($facility->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="rating">Efficiency Rating</label>
            <select name="rating" id="rating">
                <option value="all" <?php echo e((isset($selectedRating) && ($selectedRating == 'all' || $selectedRating == '')) ? 'selected' : ''); ?>>All Ratings</option>
                <option value="High" <?php echo e((isset($selectedRating) && $selectedRating == 'High') ? 'selected' : ''); ?>>High</option>
                <option value="Medium" <?php echo e((isset($selectedRating) && $selectedRating == 'Medium') ? 'selected' : ''); ?>>Medium</option>
                <option value="Low" <?php echo e((isset($selectedRating) && $selectedRating == 'Low') ? 'selected' : ''); ?>>Low</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">
            <i class="fa fa-filter" style="margin-right: 5px;"></i> Apply Filters
        </button>
    </form>

    <div class="table-wrapper">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Facility Name</th>
                    <th>EUI (kWh/sqm)</th>
                    <th>Rating</th>
                    <th>Last Audit</th>
                    <th>Maintenance Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $efficiencyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight: 700; text-align: left; padding-left: 25px;"><?php echo e($row['facility']); ?></td>
                    <td><?php echo e($row['eui']); ?></td>
                    <td>
                        <?php
                            $ratingClass = 'rating-medium';
                            if($row['rating'] == 'High') $ratingClass = 'rating-high';
                            if($row['rating'] == 'Low') $ratingClass = 'rating-low';
                        ?>
                        <span class="rating-badge <?php echo e($ratingClass); ?>"><?php echo e($row['rating']); ?></span>
                    </td>
                    <td><?php echo e($row['last_audit']); ?></td>
                    <td>
                        <?php if($row['flag']): ?>
                            <span style="color:#e11d48; font-weight:700; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <i class="fa fa-flag"></i> For Maintenance
                            </span>
                        <?php else: ?>
                            <span style="color:#22c55e; font-weight:700; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <i class="fa fa-check-circle"></i> Operational (OK)
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" style="padding: 40px; color: #94a3b8; font-style: italic;">
                        No efficiency data found for the selected filters.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/reports/efficiency-summary.blade.php ENDPATH**/ ?>