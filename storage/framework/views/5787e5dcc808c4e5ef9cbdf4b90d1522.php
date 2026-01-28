
<?php $__env->startSection('title', 'Facilities'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $userRole = strtolower(auth()->user()->role ?? '');
?>

<div class="facilities-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="font-size:2rem;font-weight:700;color:#222;margin:0;">Facilities</h2>
    <div style="display:flex; gap:12px;">
        <?php if($userRole !== 'staff'): ?>
            <button type="button" id="btnAddFacilityTop" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.18s;">+ Add Facility</button>
            <style>
            #btnAddFacilityTop:hover, #btnAddFacilityTop:focus {
                background:linear-gradient(90deg,#1d4ed8,#6366f1);
            }
            </style>
        <?php endif; ?>

    <?php if($userRole !== 'staff'): ?>
        <!-- Floating Add Facility Button (hidden if top button is visible) -->
        <button type="button" id="fabAddFacility" title="Add Facility" style="position:fixed;bottom:38px;right:38px;z-index:10001;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:50%;width:62px;height:62px;box-shadow:0 4px 18px rgba(37,99,235,0.18);display:flex;align-items:center;justify-content:center;font-size:2.1rem;cursor:pointer;transition:background 0.18s;display:none;">
            <span style="font-size:2.2rem;line-height:1;">+</span>
        </button>
        <style>
        #fabAddFacility:hover, #fabAddFacility:focus {
            background:linear-gradient(90deg,#1d4ed8,#6366f1);
        }
        @media (max-width: 600px) {
            #fabAddFacility { right:16px; bottom:16px; width:52px; height:52px; font-size:1.6rem; }
        }
        </style>
    <?php endif; ?>
    </div>
</div>

<!-- Facility Summary Cards -->
<div class="facility-summary-cards" style="display:flex;gap:24px;margin-bottom:2.2rem;flex-wrap:wrap;">
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">🏢 Total Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($totalFacilities ?? '-'); ?></div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 Active Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($activeFacilities ?? '-'); ?></div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#f59e42;">🛠 Maintenance</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($maintenanceFacilities ?? '-'); ?></div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🚫 Inactive Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($inactiveFacilities ?? '-'); ?></div>
    </div>
</div>

<!-- Facilities List -->
<div class="facilities-list" style="display:flex;flex-wrap:wrap;gap:28px;">
    <?php $__empty_1 = true; $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="facility-card" style="background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,0.08);padding:22px 18px;width:320px;display:flex;flex-direction:column;align-items:center;cursor:pointer;position:relative;">
            <?php
                $imageUrl = $facility->image ? asset('storage/'.$facility->image) : null;
            ?>
            <?php if($imageUrl): ?>
                <img src="<?php echo e($imageUrl); ?>" alt="Facility" style="width:100%;height:140px;object-fit:cover;border-radius:10px;margin-bottom:18px;">
            <?php else: ?>
                <div style="width:100%;height:140px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2.2rem;margin-bottom:18px;">
                    <i class="fa fa-image" title="No Image"></i>
                </div>
            <?php endif; ?>
            <h3 style="font-size:1.25rem;font-weight:600;margin-bottom:8px;color:#222;"><?php echo e($facility->name ?? '-'); ?></h3>
            <div style="color:#6366f1;font-weight:500;margin-bottom:6px;"><?php echo e($facility->type ?? '-'); ?></div>
            <div style="font-size:0.98rem;color:#555;margin-bottom:10px;"><?php echo e($facility->address ?? '-'); ?></div>
            <div class="facility-icons" style="display:flex;gap:10px;align-items:center;z-index:2;">
                <a href="<?php echo e(url('/modules/facilities/' . $facility->id . '/energy-profile')); ?>" class="facility-icon-link" style="color:#f59e42;font-size:1.25rem;" title="View Energy Profile" onclick="event.stopPropagation();"><i class="fa fa-bolt"></i></a>
                <a href="<?php echo e(route('facilities.monthly-records', $facility->id)); ?>" class="facility-icon-link" style="color:#2563eb;font-size:1.25rem;" title="View Monthly Records" onclick="event.stopPropagation();"><i class="fa fa-calendar-alt"></i></a>
                <a href="<?php echo e(url('/facilities/first3months?facility_id=' . $facility->id)); ?>" class="facility-icon-link" style="color:#0ea5e9;font-size:1.25rem;" title="First 3 Months Data" onclick="event.stopPropagation();">
                    <i class="fa fa-calendar-plus"></i>
                </a>
                <?php if($userRole !== 'staff'): ?>
                    <?php if($userRole === 'engineer' || $userRole === 'super admin'): ?>
                        <button onclick="event.stopPropagation();toggleEngineerApproval(<?php echo e($facility->id); ?>)" class="facility-icon-link" style="color:#22c55e;font-size:1.2rem;background:none;border:none;" title="Engineer Approval"><i class="fa fa-check-circle"></i></button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('modules.facilities.show', $facility->id)); ?>" class="facility-card-link" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;"></a>
        <style>
        .facility-card {
            position: relative;
            overflow: visible;
        }
        .facility-card-link {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 1;
            text-decoration: none;
            color: inherit;
        }
        .facility-icons {
            z-index: 2;
        }
        .facility-icon-link {
            z-index: 3;
            position: relative;
        }
        .facility-icon-link:focus, .facility-icon-link:hover {
            color: #1d4ed8 !important;
        }
        </style>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="width:100%;text-align:center;color:#94a3b8;font-size:1.1rem;padding:32px 0;">No facilities found.</div>
    <?php endif; ?>
</div>


<!-- Modal for 3-Month Average kWh (rendered only once, outside the loop) -->
<!-- 3-Month Average Modal removed -->

<!-- Modals -->
<?php echo $__env->make('modules.facilities.partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

<style>
.modal { display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);z-index:9999;align-items:center;justify-content:center; }
.action-btn-delete {
    transition: background 0.2s, color 0.2s;
}
.action-btn-delete:hover, .action-btn-delete:focus {
    background: #fee2e2;
    color: #b91c1c !important;
    border-radius: 6px;
}
.facility-card {
    transition: box-shadow 0.18s, transform 0.18s, border 0.18s;
    cursor: pointer;
}
.facility-card:hover {
    box-shadow: 0 8px 32px rgba(37,99,235,0.18);
    transform: translateY(-3px) scale(1.03);
    background: #f0f6ff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Move modal to end of body for stacking
    const modal = document.getElementById('addFacilityModal');
    if (modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }
    // Add Facility button logic (top)
    const btnAddFacilityTop = document.getElementById('btnAddFacilityTop');
    if (btnAddFacilityTop) {
        btnAddFacilityTop.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(()=>{
                document.getElementById('add_name')?.focus();
            }, 100);
        });
    }
    // Add Facility button logic (FAB)
    const fabAddFacility = document.getElementById('fabAddFacility');
    if (fabAddFacility) {
        fabAddFacility.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(()=>{
                document.getElementById('add_name')?.focus();
            }, 100);
        });
    }
    document.querySelectorAll('.facility-card-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only trigger if not clicking on an icon
        });
    });

    // 3-Month Avg Icon Button Logic removed

    // Close modals
    document.querySelectorAll('.modal-close').forEach(btn=>btn.addEventListener('click',()=>btn.closest('.modal').style.display='none'));

    // Outside click
    window.addEventListener('click', e=>{ document.querySelectorAll('.modal').forEach(m=>{if(e.target===m)m.style.display='none';}); });

    // Edit
    document.querySelectorAll('.action-btn-edit').forEach(btn=>{
        btn.addEventListener('click', e=>{
            e.preventDefault();
            const facility=JSON.parse(btn.dataset.facility);
            openEditFacilityModal(facility);
        });
    });
});

// Reset baseline
function openResetBaselineModal(id){ document.getElementById('reset_facility_id').value=id; document.getElementById('resetBaselineModal').style.display='flex'; }
document.getElementById('resetBaselineForm')?.addEventListener('submit',function(e){
    e.preventDefault();
    const id=document.getElementById('reset_facility_id').value;
    const reason=document.getElementById('reset_reason').value;
    fetch(`/modules/facilities/${id}/reset-baseline`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'},
        body:JSON.stringify({reason})
    }).then(res=>res.json()).then(data=>{ alert(data.message||'Baseline reset!'); document.getElementById('resetBaselineModal').style.display='none'; location.reload(); });
});

// Engineer approval
function toggleEngineerApproval(id){
    fetch(`/modules/facilities/${id}/toggle-engineer-approval`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}
    }).then(res=>res.json()).then(data=>{ alert(data.message||'Engineer approval toggled!'); location.reload(); });
}

// Delete facility
function openDeleteFacilityModal(id) {
    document.getElementById('delete_facility_id').value = id;
    document.getElementById('deleteFacilityModal').style.display = 'flex';
}
document.getElementById('deleteFacilityForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('delete_facility_id').value;
    const form = this;
    fetch(`/facilities/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
        },
    }).then(res => {
        if (res.ok) {
            document.getElementById('deleteFacilityModal').style.display = 'none';
            location.reload();
        } else {
            alert('Failed to delete facility.');
        }
    });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/index.blade.php ENDPATH**/ ?>