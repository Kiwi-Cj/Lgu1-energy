
<?php $__env->startSection('title', 'Facilities'); ?>

<?php $__env->startSection('content'); ?>

<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
?>

<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #16a34a22;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e;font-size:1.3rem;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div id="errorAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;max-width:420px;">
    <div style="background:#fee2e2;color:#b91c1c;padding:16px 24px;border-radius:12px;font-weight:700;font-size:1.08rem;box-shadow:0 2px 8px #e11d4822;display:flex;align-items:center;gap:10px;">
        <i class="fa fa-times-circle" style="color:#e11d48;font-size:1.3rem;"></i>
        <span><?php echo e(session('error')); ?></span>
    </div>
</div>
<?php endif; ?>



<!-- ...existing content... -->
<script>
window.addEventListener('DOMContentLoaded', function() {
        var success = document.getElementById('successAlert');
        var error = document.getElementById('errorAlert');
        if (success) setTimeout(() => success.style.display = 'none', 3000);
        if (error) setTimeout(() => error.style.display = 'none', 3000);
});
</script>
<style>
    /* --- Energy Report Inspired Aesthetic --- */
    .report-card-container {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 12px rgba(31,38,135,0.06); 
        padding: 30px;
        margin-bottom: 2rem;
        font-family: 'Inter', sans-serif;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
    }

    /* Modern KPI Cards */
    .stat-card {
        flex: 1;
        min-width: 200px;
        padding: 20px;
        border-radius: 15px;
        background: #ffffff;
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .card-icon-box {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px; font-size: 1rem;
    }

    /* Facility Grid & Cards */
    .facility-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .facility-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .facility-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.1);
        border-color: #dbeafe;
    }

    .image-wrapper {
        width: 100%;
        height: 170px;
        overflow: hidden;
        background: #f8fafc;
        position: relative;
    }

    .image-wrapper img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .facility-card:hover .image-wrapper img { transform: scale(1.1); }

    .content-padding { padding: 20px; flex-grow: 1; }

    .type-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6366f1;
        background: #eef2ff;
        padding: 4px 12px;
        border-radius: 100px;
        margin-bottom: 10px;
        display: inline-block;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #fff !important;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-gradient:hover { opacity: 0.9; transform: translateY(-1px); }

    .card-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    .action-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        text-decoration: none;
    }
    .action-icon.energy { background: #fff7ed; color: #f59e0b; }
    .action-icon.records { background: #eff6ff; color: #3b82f6; }
    .action-icon:hover { transform: scale(1.1); }

    @media (max-width: 768px) {
        .dashboard-header { flex-direction: column; align-items: stretch; text-align: center; }
        .btn-gradient { justify-content: center; }
    }
</style>

<div style="width:100%; margin:0 auto;">
    <div class="report-card-container">
        
        <div class="dashboard-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:800; color:#3762c8; margin:0; letter-spacing:-0.5px;">📘 Facilities Management</h2>
                <p style="color:#64748b; margin-top:4px; font-weight:500;">Manage and monitor LGU energy sectors.</p>
            </div>
            <div>
                <?php if(strtolower(Auth::user()->role ?? '') !== 'energy_officer'): ?>
                    <button type="button" id="btnAddFacilityTop" class="btn-gradient">
                        <i class="fa fa-plus-circle"></i> Add New Facility
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:2rem;">
            <div class="stat-card">
                <div class="card-icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fa fa-building"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Total</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;"><?php echo e($totalFacilities ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <div class="card-icon-box" style="background:#f0fdf4; color:#22c55e;"><i class="fa fa-check-circle"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Active</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;"><?php echo e($activeFacilities ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <div class="card-icon-box" style="background:#fffbeb; color:#f59e0b;"><i class="fa fa-wrench"></i></div>
                <div style="color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">Maintenance</div>
                <div style="font-size:1.5rem; font-weight:800; color:#1e293b;"><?php echo e($maintenanceFacilities ?? 0); ?></div>
            </div>
        </div>

        <div class="facility-grid">
            <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="facility-card">
                    <div class="image-wrapper">
                        <?php
                            $imageUrl = $facility->image_path ? asset('storage/' . $facility->image_path) : 
                                       ($facility->image ? (str_starts_with($facility->image, 'img/') ? asset($facility->image) : asset('storage/'.$facility->image)) : null);
                        ?>
                        <?php if($imageUrl): ?>
                            <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($facility->name); ?>">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#cbd5e1;">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo e(route('modules.facilities.show', $facility->id)); ?>" style="position:absolute; inset:0; z-index:1;"></a>
                    </div>

                    <div class="content-padding">
                        <span class="type-badge"><?php echo e($facility->type ?? 'General'); ?></span>
                        <h3 style="font-size:1.2rem; font-weight:800; color:#1e293b; margin:0 0 8px 0; line-height:1.2;"><?php echo e($facility->name); ?></h3>
                        <p style="font-size:0.88rem; color:#64748b; display:flex; align-items:flex-start; gap:6px; margin-bottom:12px;">
                            <i class="fas fa-location-dot" style="color:#94a3b8; margin-top:3px;"></i> <?php echo e(Str::limit($facility->address ?? 'No address provided', 50)); ?>

                        </p>

                        <div class="card-actions" style="position:relative; z-index:2;">
                            <a href="<?php echo e(url('/modules/facilities/' . $facility->id . '/energy-profile')); ?>" class="action-icon energy" title="Energy Profile">
                                <i class="fas fa-bolt"></i>
                            </a>
                            <a href="<?php echo e(route('facilities.monthly-records', $facility->id)); ?>" class="action-icon records" title="Monthly Records" style="background:#fef2f2; color:#e11d48;">
                                <i class="fas fa-file-lines"></i>
                            </a>
                            <div style="margin-left:auto; display:flex; align-items:center;">
                                <a href="<?php echo e(route('modules.facilities.show', $facility->id)); ?>" style="font-size:0.95rem; font-weight:700; color:#2563eb; text-transform:none; text-decoration:none; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:4px; transition:color 0.18s;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#2563eb'">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div style="grid-column: 1/-1; text-align:center; padding:60px; background:#fff; border-radius:20px; border:2px dashed #cbd5e1;">
                    <i class="fas fa-building fa-3x" style="color:#cbd5e1; margin-bottom:15px;"></i>
                    <h3 style="color:#64748b;">No facilities found</h3>
                    <p style="color:#94a3b8;">Start by adding a new facility to the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if(strtolower(Auth::user()->role ?? '') !== 'energy_officer'): ?>
    <button type="button" id="fabAddFacility" class="btn-gradient" style="position:fixed; bottom:40px; right:40px; width:60px; height:60px; border-radius:30px; padding:0; display:flex; align-items:center; justify-content:center; font-size:1.5rem; z-index:99; display:none;">
        <i class="fas fa-plus"></i>
    </button>
<?php endif; ?>

<?php echo $__env->make('modules.facilities.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
    window.addEventListener('scroll', function() {
        const fab = document.getElementById('fabAddFacility');
        if (fab) {
            if (window.scrollY > 200) fab.style.display = 'flex';
            else fab.style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addFacilityModal');
        const openModal = () => { if(modal) modal.style.display = 'flex'; };
        
        document.getElementById('btnAddFacilityTop')?.addEventListener('click', openModal);
        document.getElementById('fabAddFacility')?.addEventListener('click', openModal);

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('.modal').style.display = 'none');
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Lgu1-energy\resources\views/modules/facilities/index.blade.php ENDPATH**/ ?>