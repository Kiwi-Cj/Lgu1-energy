<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<?php $__env->startSection('title', 'Facilities Needing Maintenance'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1100px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin:0;">Facilities Needing Maintenance</h2>
        <a href="<?php echo e(route('maintenance.history')); ?>" class="btn btn-primary" style="background:#2563eb;color:#fff;padding:10px 22px;border-radius:8px;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(37,99,235,0.08);display:inline-block;">
            <i class="fa fa-history" style="margin-right:7px;"></i> Maintenance History
        </a>
    </div>
    <div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🔴 Facilities Needing Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($needingCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fef9c3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#eab308;">🟡 Pending Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($pendingCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🔧 Ongoing Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($ongoingCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#e0f7fa;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(0,188,212,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#0097a7;">✅ Completed Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($completedCount ?? 0); ?></div>
        </div>
    </div>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" required>
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
    <!-- SCHEDULE MODAL -->
    <div id="scheduleModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
        <div style="background:#fff;max-width:480px;width:95vw;max-height:90vh;overflow:auto;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:0 0 24px 0;position:relative;">
            <div style="padding:24px 32px 12px 32px;border-bottom:1px solid #e5e7eb;">
                <div style="font-size:1.3rem;font-weight:700;">Schedule / Update Maintenance</div>
            </div>
            <div style="padding:18px 32px;">
                <form id="scheduleForm">
                    <input type="hidden" name="maintenance_id" id="modalMaintenanceId">
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:600;">Facility</label>
                        <input type="text" id="modalFacility" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;background:#f3f4f6;" readonly>
                    </div>
                    <div style="margin-bottom:12px;display:flex;gap:12px;">
                        <div style="flex:1;">
                            <label style="font-weight:600;">Trigger Month</label>
                            <input type="text" id="modalTriggerMonth" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;background:#f3f4f6;" readonly>
                        </div>
                        <div style="flex:1;">
                            <label style="font-weight:600;">Issue Type</label>
                            <input type="text" id="modalIssueType" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;background:#f3f4f6;" readonly>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:600;">Maintenance Type</label>
                        <select id="modalMaintType" name="maintenance_type" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:600;">Scheduled Date</label>
                        <input type="date" name="scheduled_date" id="modalScheduleDate" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:600;">Assigned To</label>
                        <input type="text" name="assigned_to" id="modalAssignedTo" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-weight:600;">Remarks</label>
                        <textarea name="remarks" id="modalRemarks" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;min-height:60px;"></textarea>
                    </div>
                    <div style="margin-bottom:12px;display:flex;gap:12px;">
                        <div style="flex:1;">
                            <label style="font-weight:600;">Status</label>
                            <select id="modalStatus" name="maintenance_status" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                                <option value="Pending">Pending</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-weight:600;">Completed Date</label>
                            <input type="date" name="completed_date" id="modalCompletedDate" class="form-control" style="width:100%;padding:8px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" disabled>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <button type="button" onclick="closeScheduleModal()" style="padding:7px 22px;border-radius:7px;background:#e5e7eb;color:#222;font-weight:600;border:none;font-size:1rem;">Cancel</button>
                        <button type="submit" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;">Save</button>
                    </div>
                </form>
            </div>
            <button onclick="closeScheduleModal()" style="position:absolute;top:12px;right:18px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
        </div>
    </div>

    <div style="overflow-x:auto;">
    <table class="table" style="width:100%;margin-top:12px;background:#fff;border-radius:14px;overflow:hidden;text-align:center;box-shadow:0 2px 12px rgba(55,98,200,0.07);border-collapse:separate;border-spacing:0;">
        <thead style="background:#e9effc;">
            <tr style="font-size:1.05rem;">
                <th style="padding:14px 10px;">Facility Name</th>
                <th style="padding:14px 10px;">Issue Type</th>
                <th style="padding:14px 10px;">Trigger Month</th>
                <th style="padding:14px 10px;">Efficiency Rating</th>
                <th style="padding:14px 10px;">Maintenance Status</th>
                <th style="padding:14px 10px;">Scheduled Date</th>
                <th style="padding:14px 10px;">Remarks</th>
                <th style="padding:14px 10px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $maintenanceRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr data-id="<?php echo e($row['id'] ?? $i); ?>" data-maintenance_type="<?php echo e($row['maintenance_type'] ?? ''); ?>" data-scheduled_date="<?php echo e($row['scheduled_date'] ?? ''); ?>" data-assigned_to="<?php echo e($row['assigned_to'] ?? ''); ?>" data-completed_date="<?php echo e($row['completed_date'] ?? ''); ?>" style="background:<?php if($loop->index%2==0): ?>#f9fafb;<?php else: ?>#fff;<?php endif; ?>;transition:background 0.2s;" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='<?php echo e($loop->index%2==0 ? '#f9fafb' : '#fff'); ?>'">
                <td style="padding:12px 8px;font-weight:500;"><?php echo e($row['facility']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['issue_type']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['trigger_month']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['efficiency_rating']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['maintenance_status']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['scheduled_date']); ?></td>
                <td style="padding:12px 8px;"><?php echo e($row['remarks'] ?? '-'); ?></td>
                <td style="padding:12px 8px;"><?php echo str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" class="text-center" style="padding:18px 0;">No facilities needing maintenance.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<script>
// Open modal on schedule button click
document.addEventListener('DOMContentLoaded', function() {
        // Enable Completed Date only if status is Completed
        document.getElementById('modalStatus').addEventListener('change', function() {
            if (this.value === 'Completed') {
                document.getElementById('modalCompletedDate').disabled = false;
            } else {
                document.getElementById('modalCompletedDate').disabled = true;
                document.getElementById('modalCompletedDate').value = '';
            }
        });
    document.querySelectorAll('.schedule-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const idx = Array.from(row.parentNode.children).indexOf(row);
            document.getElementById('modalMaintenanceId').value = idx;
            const cells = row.querySelectorAll('td');
            // Table columns: 0=Facility, 1=Issue Type, 2=Trigger Month, 3=Efficiency, 4=Status, 5=Scheduled Date, 6=Remarks, 7=Action
            document.getElementById('modalFacility').value = cells[0]?.innerText || '';
            document.getElementById('modalIssueType').value = cells[1]?.innerText || '';
            document.getElementById('modalTriggerMonth').value = cells[2]?.innerText || '';
            document.getElementById('modalMaintType').value = row.getAttribute('data-maintenance_type') || 'Preventive';
            document.getElementById('modalScheduleDate').value = row.getAttribute('data-scheduled_date') || '';
            document.getElementById('modalAssignedTo').value = row.getAttribute('data-assigned_to') || '';
            document.getElementById('modalRemarks').value = cells[6]?.innerText || '';
            document.getElementById('modalStatus').value = cells[4]?.innerText || 'Pending';
            document.getElementById('modalCompletedDate').value = row.getAttribute('data-completed_date') || '';
            // Enable/disable completed date
            if (document.getElementById('modalStatus').value === 'Completed') {
                document.getElementById('modalCompletedDate').disabled = false;
            } else {
                document.getElementById('modalCompletedDate').disabled = true;
            }
            document.getElementById('scheduleModal').style.display = 'flex';
        });
    });
});
function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
    // Optionally reset modal fields
    document.getElementById('scheduleForm').reset();
    document.getElementById('modalCompletedDate').disabled = true;
}
// Handle form submit (AJAX or normal post)
document.getElementById('scheduleForm').onsubmit = function(e) {
    e.preventDefault();
    const idx = document.getElementById('modalMaintenanceId').value;
    const table = document.querySelector('table tbody');
    const row = table && table.children[idx];
    // Gather form data
    const status = document.getElementById('modalStatus').value;
    const completedDate = document.getElementById('modalCompletedDate').value;
    if (status === 'Completed' && !completedDate) {
        const notif = document.createElement('div');
        notif.innerText = 'Completed Date is required when status is Completed!';
        notif.style.position = 'fixed';
        notif.style.top = '30px';
        notif.style.right = '30px';
        notif.style.background = '#e11d48';
        notif.style.color = '#fff';
        notif.style.padding = '16px 32px';
        notif.style.borderRadius = '8px';
        notif.style.fontWeight = 'bold';
        notif.style.fontSize = '1.1rem';
        notif.style.zIndex = 99999;
        notif.style.boxShadow = '0 2px 12px rgba(225,29,72,0.15)';
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 2000);
        document.getElementById('modalCompletedDate').focus();
        return false;
    }
    const data = {
        maintenance_id: row ? row.getAttribute('data-id') : '',
        maintenance_type: document.getElementById('modalMaintType').value,
        scheduled_date: document.getElementById('modalScheduleDate').value,
        assigned_to: document.getElementById('modalAssignedTo').value,
        remarks: document.getElementById('modalRemarks').value,
        maintenance_status: status,
        completed_date: completedDate,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
    fetch("<?php echo e(route('modules.maintenance.schedule')); ?>", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': data._token
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success && row) {
            // Update row visually (column order: Facility, Issue Type, Trigger Month, Efficiency, Status, Scheduled, Remarks, Action)
            row.children[0].innerText = resp.maintenance.facility || row.children[0].innerText;
            row.children[1].innerText = resp.maintenance.issue_type || row.children[1].innerText;
            row.children[2].innerText = resp.maintenance.trigger_month || row.children[2].innerText;
            row.children[3].innerText = resp.maintenance.efficiency_rating || row.children[3].innerText;
            row.children[4].innerText = resp.maintenance.maintenance_status || row.children[4].innerText;
            row.children[5].innerText = resp.maintenance.scheduled_date || '-';
            row.children[6].innerText = resp.maintenance.remarks || '-';
            // Update row data attributes for modal prefill
            row.setAttribute('data-maintenance_type', resp.maintenance.maintenance_type || '');
            row.setAttribute('data-scheduled_date', resp.maintenance.scheduled_date || '');
            row.setAttribute('data-assigned_to', resp.maintenance.assigned_to || '');
            row.setAttribute('data-completed_date', resp.maintenance.completed_date || '');
            // Change action button to Edit after scheduling
            row.children[7].innerHTML = '<button class="btn btn-sm schedule-btn" style="background:#6366f1;color:#fff;border:none;padding:7px 18px;border-radius:7px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;" title="Edit Maintenance"><i class="fa fa-edit"></i> Edit</button>';
            // Re-bind the click event for the new Edit button
            row.querySelector('.schedule-btn').addEventListener('click', function(e) {
                e.preventDefault();
                const idx = Array.from(row.parentNode.children).indexOf(row);
                document.getElementById('modalMaintenanceId').value = idx;
                const cells = row.querySelectorAll('td');
                document.getElementById('modalFacility').value = cells[0]?.innerText || '';
                document.getElementById('modalIssueType').value = cells[1]?.innerText || '';
                document.getElementById('modalTriggerMonth').value = cells[2]?.innerText || '';
                document.getElementById('modalMaintType').value = row.getAttribute('data-maintenance_type') || 'Preventive';
                document.getElementById('modalScheduleDate').value = row.getAttribute('data-scheduled_date') || '';
                document.getElementById('modalAssignedTo').value = row.getAttribute('data-assigned_to') || '';
                document.getElementById('modalRemarks').value = cells[6]?.innerText || '';
                document.getElementById('modalStatus').value = cells[4]?.innerText || 'Pending';
                document.getElementById('modalCompletedDate').value = row.getAttribute('data-completed_date') || '';
                if (document.getElementById('modalStatus').value === 'Completed') {
                    document.getElementById('modalCompletedDate').disabled = false;
                } else {
                    document.getElementById('modalCompletedDate').disabled = true;
                }
                document.getElementById('scheduleModal').style.display = 'flex';
            });
        }
        closeScheduleModal();
        // Show a user-friendly notification
        const notif = document.createElement('div');
        notif.innerText = 'Maintenance scheduled!';
        notif.style.position = 'fixed';
        notif.style.top = '30px';
        notif.style.right = '30px';
        notif.style.background = '#22c55e';
        notif.style.color = '#fff';
        notif.style.padding = '16px 32px';
        notif.style.borderRadius = '8px';
        notif.style.fontWeight = 'bold';
        notif.style.fontSize = '1.1rem';
        notif.style.zIndex = 99999;
        notif.style.boxShadow = '0 2px 12px rgba(34,197,94,0.15)';
        document.body.appendChild(notif);
        setTimeout(() => {
            notif.remove();
            window.location.reload();
        }, 1200);
    })
    .catch(() => {
        const notif = document.createElement('div');
        notif.innerText = 'Error saving maintenance.';
        notif.style.position = 'fixed';
        notif.style.top = '30px';
        notif.style.right = '30px';
        notif.style.background = '#e11d48';
        notif.style.color = '#fff';
        notif.style.padding = '16px 32px';
        notif.style.borderRadius = '8px';
        notif.style.fontWeight = 'bold';
        notif.style.fontSize = '1.1rem';
        notif.style.zIndex = 99999;
        notif.style.boxShadow = '0 2px 12px rgba(225,29,72,0.15)';
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 2000);
    });
    return false;
};
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/modules/maintenance/index.blade.php ENDPATH**/ ?>