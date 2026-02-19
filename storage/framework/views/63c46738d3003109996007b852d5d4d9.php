<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<?php $__env->startSection('title', 'Facilities Needing Maintenance'); ?>

<?php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .page-header h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .page-header h2 span { color: #2563eb; }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-box {
        padding: 24px 20px;
        border-radius: 14px;
        transition: transform 0.2s;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .stat-box:hover { transform: translateY(-3px); }
    .stat-label { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
    .stat-value { font-size: 2.2rem; font-weight: 800; margin-top: 10px; color: #1e293b; }

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
    .filter-group select, .filter-group input {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        min-width: 160px;
        background: #fff;
        font-size: 0.95rem;
    }
    .btn-filter {
        background: linear-gradient(90deg,#2563eb,#6366f1);
        color: #fff;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-filter:hover { opacity: 0.9; transform: translateY(-1px); }

    /* Table Styling */
    .maint-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .maint-table { width: 100%; border-collapse: collapse; background: #fff; text-align: center; }
    .maint-table thead { background: #f1f5f9; }
    .maint-table th { padding: 15px; font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; }
    .maint-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.95rem; }
    .maint-table tr:hover { background-color: #f8fafc; }

    /* Modal Backdrop */
    .modal-overlay {
        display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh;
        background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; backdrop-filter: blur(4px);
    }
    .modal-content {
        background: #fff; max-width: 500px; width: 95vw; border-radius: 20px; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow-y: auto;
        max-height: 420px; /* reduced height */
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
</style>


<?php if(session('success')): ?>
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e; font-size: 1.2rem;"></i>
        <span><?php echo e(session('success')); ?></span>
    </div>
</div>
<?php endif; ?>

<div class="report-card">
    <div class="page-header">
        <h2>Facilities Needing <span>Maintenance</span></h2>
        <div style="display:flex; gap:10px;">
             <button id="addMaintenanceBtn" class="btn btn-primary" style="background:#10b981; color:#fff; padding:10px 20px; border-radius:10px; font-weight:700; border:none; cursor:pointer;">
                <i class="fa fa-plus"></i> Add Manual
            </button>
            <a href="<?php echo e(route('maintenance.history')); ?>" style="background:#2563eb; color:#fff; padding:10px 20px; border-radius:10px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-history"></i> History
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box" style="background: #fff1f2;">
            <div class="stat-label" style="color: #e11d48;">🔴 Needing Maint.</div>
            <div class="stat-value"><?php echo e($needingCount ?? 0); ?></div>
        </div>
        <div class="stat-box" style="background: #fefce8;">
            <div class="stat-label" style="color: #a16207;">🟡 Pending</div>
            <div class="stat-value"><?php echo e($pendingCount ?? 0); ?></div>
        </div>
        <div class="stat-box" style="background: #f0fdf4;">
            <div class="stat-label" style="color: #15803d;">🔧 Ongoing</div>
            <div class="stat-value"><?php echo e($ongoingCount ?? 0); ?></div>
        </div>
        <div class="stat-box" style="background: #ecfeff;">
            <div class="stat-label" style="color: #0e7490;">✅ Completed</div>
            <div class="stat-value"><?php echo e($completedCount ?? 0); ?></div>
        </div>
    </div>

    <form method="GET" action="" class="filter-section">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" disabled selected>Select Facility</option>
                <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php if(request('facility_id') == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" disabled selected>Select Month</option>
                <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select name="maintenance_type" id="maintenance_type">
                <option value="">All Types</option>
                <option value="Preventive" <?php if(request('maintenance_type') == 'Preventive'): ?> selected <?php endif; ?>>Preventive</option>
                <option value="Corrective" <?php if(request('maintenance_type') == 'Corrective'): ?> selected <?php endif; ?>>Corrective</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" id="status">
                <option value="">All Status</option>
                <option value="Pending" <?php if(request('status') == 'Pending'): ?> selected <?php endif; ?>>Pending</option>
                <option value="Ongoing" <?php if(request('status') == 'Ongoing'): ?> selected <?php endif; ?>>Ongoing</option>
                <option value="Completed" <?php if(request('status') == 'Completed'): ?> selected <?php endif; ?>>Completed</option>
            </select>
        </div>
        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <div class="maint-table-wrapper">
        <table class="maint-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Issue Type</th>
                    <th>Trigger Month</th>
                    <!-- Efficiency column removed -->
                    <th>Status</th>
                    <th>Scheduled</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $maintenanceRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr data-id="<?php echo e($row['id'] ?? $i); ?>" 
                    data-maintenance_type="<?php echo e($row['maintenance_type'] ?? ''); ?>" 
                    data-scheduled_date="<?php echo e($row['scheduled_date'] ?? ''); ?>" 
                    data-assigned_to="<?php echo e($row['assigned_to'] ?? ''); ?>" 
                    data-completed_date="<?php echo e($row['completed_date'] ?? ''); ?>">
                    <td style="font-weight:700;"><?php echo e($row['facility']); ?></td>
                    <td><?php echo e($row['issue_type']); ?></td>
                    <td><?php echo e($row['trigger_month']); ?></td>
                    <!-- Efficiency value removed -->
                    <td><span style="padding:4px 10px; background:#f1f5f9; border-radius:20px; font-size:0.8rem; font-weight:700;"><?php echo e($row['maintenance_status']); ?></span></td>
                    <td><?php echo e($row['scheduled_date']); ?></td>
                    <td style="color:#64748b;"><?php echo e($row['remarks'] ?? '-'); ?></td>
                    <td><?php echo str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="padding:40px; color:#94a3b8;">No facilities needing maintenance found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="modal-content" style="max-width:520px;width:95vw;background:#fff;border-radius:22px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:0;max-height:92vh;overflow-y:auto;position:relative;">
        <div style="padding:24px 32px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; border-radius:22px 22px 0 0;">
            <div id="modalTitle" style="font-size:1.35rem; font-weight:900; color:#1e293b; letter-spacing:-1px;">Schedule Maintenance</div>
            <button onclick="closeScheduleModal()" style="background:none; border:none; font-size:1.7rem; color:#94a3b8; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:32px;">
            <form id="scheduleForm">
                <input type="hidden" name="maintenance_id" id="modalMaintenanceId">
                <div style="font-size:1.08rem;color:#64748b;margin-bottom:0;font-weight:700;">FACILITY</div>
                <div style="margin-bottom:18px;">
                    <select id="modalFacility" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                        <option value="" disabled selected>Select Facility</option>
                        <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($facility->id); ?>"><?php echo e($facility->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <div style="font-size:.98rem;color:#64748b;font-weight:700;">TRIGGER MONTH & YEAR</div>
                        <div style="display:flex;gap:8px;">
                            <select id="modalTriggerMonth" style="flex:2; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                                <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>"><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <select id="modalTriggerYear" style="flex:1; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                                <?php $currentYear = date('Y'); ?>
                                <?php for($y = $currentYear-2; $y <= $currentYear+2; $y++): ?>
                                    <option value="<?php echo e($y); ?>" <?php if($y==$currentYear): ?> selected <?php endif; ?>><?php echo e($y); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <div style="font-size:.98rem;color:#64748b;font-weight:700;">ISSUE TYPE</div>
                        <select id="modalIssueType" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                            <option value="" disabled selected>Select Issue</option>
                            <option value="High Consumption / Inefficient">High Consumption / Inefficient</option>
                            <option value="Trend Increasing">Trend Increasing</option>
                            <option value="Electrical - Power Outage">Electrical - Power Outage</option>
                            <option value="Electrical - Circuit Overload">Electrical - Circuit Overload</option>
                            <option value="Lighting - Bulb Replacement">Lighting - Bulb Replacement</option>
                            <option value="Lighting - Fixture Repair">Lighting - Fixture Repair</option>
                            <option value="Aircon - Not Cooling">Aircon - Not Cooling</option>
                            <option value="Aircon - Cleaning Needed">Aircon - Cleaning Needed</option>
                            <option value="Plumbing - Leak">Plumbing - Leak</option>
                            <option value="Plumbing - Clogged Drain">Plumbing - Clogged Drain</option>
                            <option value="Roof - Leak">Roof - Leak</option>
                            <option value="Roof - Gutter Cleaning">Roof - Gutter Cleaning</option>
                            <option value="Pest Control">Pest Control</option>
                            <option value="General - Preventive Check">General - Preventive Check</option>
                            <option value="General - Other">General - Other</option>
                        </select>
                    </div>
                </div>
                <!-- Efficiency Rating removed -->
                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <div style="font-size:.98rem;color:#64748b;font-weight:700;">TYPE & DATE</div>
                        <select id="modalMaintType" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                        </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <input type="date" id="modalScheduleDate" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                    </div>
                </div>
                <div style="font-size:1.08rem;color:#64748b;margin-bottom:0;font-weight:700;">ASSIGNED TO</div>
                <div style="margin-bottom:18px;">
                    <input type="text" id="modalAssignedTo" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                </div>
                <div style="font-size:1.08rem;color:#64748b;margin-bottom:0;font-weight:700;">REMARKS</div>
                <div style="margin-bottom:18px;">
                    <textarea id="modalRemarks" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; min-height:60px; font-size:1.08rem;"></textarea>
                </div>
                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;margin-bottom:18px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <div style="font-size:.98rem;color:#64748b;font-weight:700;">STATUS</div>
                        <select id="modalStatus" style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <div style="font-size:.98rem;color:#64748b;font-weight:700;">COMPLETED DATE</div>
                        <input type="date" id="modalCompletedDate" disabled style="width:100%; padding:12px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:1.08rem;">
                    </div>
                </div>
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button type="button" onclick="closeScheduleModal()" style="padding:10px 20px; border-radius:8px; background:#f1f5f9; color:#475569; font-weight:700; border:none; cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:10px 25px; border-radius:8px; background:#2563eb; color:#fff; font-weight:700; border:none; cursor:pointer;">Save Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Alert handling
    const alert = document.getElementById('successAlert');
    if (alert) setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 500); }, 3000);

    // Toggle completed date based on status
    const modalStatus = document.getElementById('modalStatus');
    if (modalStatus) {
        modalStatus.addEventListener('change', function() {
            const compDate = document.getElementById('modalCompletedDate');
            if (compDate) {
                compDate.disabled = (this.value !== 'Completed');
                if (compDate.disabled) compDate.value = '';
            }
        });
    }

    // Schedule/Edit button click
    document.querySelectorAll('.schedule-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            if (!row) return;
            const idx = Array.from(row.parentNode.children).indexOf(row);
            const cells = row.querySelectorAll('td');

            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.innerText = 'Update Maintenance';
            const modalMaintenanceId = document.getElementById('modalMaintenanceId');
            if (modalMaintenanceId) modalMaintenanceId.value = idx;

            // Prefill logic
            const facilityName = cells[0]?.innerText.trim();
            const facSelect = document.getElementById('modalFacility');
            if (facSelect && facilityName) {
                for(let i=0; i<facSelect.options.length; i++) {
                    if(facSelect.options[i].text === facilityName) facSelect.selectedIndex = i;
                }
                facSelect.disabled = true;
            }

            const modalIssueType = document.getElementById('modalIssueType');
            if (modalIssueType) {
                modalIssueType.value = cells[1]?.innerText || '';
                modalIssueType.readOnly = true;
            }

            const modalTriggerMonth = document.getElementById('modalTriggerMonth');
            if (modalTriggerMonth) modalTriggerMonth.disabled = true;
            const efficiencyRatingGroup = document.getElementById('efficiencyRatingGroup');
            if (efficiencyRatingGroup) efficiencyRatingGroup.style.display = 'none';

            const modalMaintType = document.getElementById('modalMaintType');
            if (modalMaintType) modalMaintType.value = row.getAttribute('data-maintenance_type') || 'Preventive';
            const modalScheduleDate = document.getElementById('modalScheduleDate');
            if (modalScheduleDate) modalScheduleDate.value = row.getAttribute('data-scheduled_date') || '';
            const modalAssignedTo = document.getElementById('modalAssignedTo');
            if (modalAssignedTo) modalAssignedTo.value = row.getAttribute('data-assigned_to') || '';
            const modalRemarks = document.getElementById('modalRemarks');
            if (modalRemarks) modalRemarks.value = cells[6]?.innerText !== '-' ? cells[6]?.innerText : '';
            if (document.getElementById('modalStatus')) document.getElementById('modalStatus').value = cells[4]?.innerText.trim();
            if (document.getElementById('modalCompletedDate')) {
                document.getElementById('modalCompletedDate').value = row.getAttribute('data-completed_date') || '';
                document.getElementById('modalCompletedDate').disabled = (document.getElementById('modalStatus').value !== 'Completed');
            }

            const scheduleModal = document.getElementById('scheduleModal');
            if (scheduleModal) scheduleModal.style.display = 'flex';
        });
    });

    // Add Manual Button
    const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
    if (addMaintenanceBtn) {
        addMaintenanceBtn.addEventListener('click', function() {
            const scheduleForm = document.getElementById('scheduleForm');
            if (scheduleForm) scheduleForm.reset();
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.innerText = 'Schedule Maintenance';
            const modalMaintenanceId = document.getElementById('modalMaintenanceId');
            if (modalMaintenanceId) modalMaintenanceId.value = '';
            const modalFacility = document.getElementById('modalFacility');
            if (modalFacility) modalFacility.disabled = false;
            const modalTriggerMonth = document.getElementById('modalTriggerMonth');
            if (modalTriggerMonth) modalTriggerMonth.disabled = false;
            const modalIssueType = document.getElementById('modalIssueType');
            if (modalIssueType) modalIssueType.readOnly = false;
            const efficiencyRatingGroup = document.getElementById('efficiencyRatingGroup');
            if (efficiencyRatingGroup) efficiencyRatingGroup.style.display = 'block';
            const scheduleModal = document.getElementById('scheduleModal');
            if (scheduleModal) scheduleModal.style.display = 'flex';
        });
    }

    // Form Submission
    const scheduleForm = document.getElementById('scheduleForm');
    if (scheduleForm) {
        scheduleForm.onsubmit = function(e) {
            e.preventDefault();
            const status = document.getElementById('modalStatus')?.value;
            const compDate = document.getElementById('modalCompletedDate')?.value;

            if (status === 'Completed' && !compDate) {
                alert('Completed Date is required!');
                return false;
            }

            const idx = document.getElementById('modalMaintenanceId')?.value;
            const tableBody = document.querySelector('.maint-table tbody');
            const row = idx !== '' && tableBody ? tableBody.children[idx] : null;

            // Compose trigger_month as 'Month Year' (e.g., 'February 2026')
            const monthNum = document.getElementById('modalTriggerMonth')?.value;
            const yearVal = document.getElementById('modalTriggerYear')?.value;
            const monthName = monthNum ? new Date(2000, parseInt(monthNum, 10) - 1, 1).toLocaleString('default', { month: 'long' }) : '';
            const triggerMonth = monthName && yearVal ? `${monthName} ${yearVal}` : '';
            const data = {
                maintenance_id: row ? row.getAttribute('data-id') : '',
                facility_id: document.getElementById('modalFacility')?.value,
                trigger_month: triggerMonth,
                issue_type: document.getElementById('modalIssueType')?.value,
                maintenance_type: document.getElementById('modalMaintType')?.value,
                scheduled_date: document.getElementById('modalScheduleDate')?.value,
                assigned_to: document.getElementById('modalAssignedTo')?.value,
                remarks: document.getElementById('modalRemarks')?.value,
                maintenance_status: status,
                completed_date: compDate,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            };

            fetch("<?php echo e(route('modules.maintenance.schedule')); ?>", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data._token },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    location.reload();
                }
            });
        };
    }
});

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/maintenance/index.blade.php ENDPATH**/ ?>