 <?php $__env->startSection('title', 'Monthly Records'); ?>
  <?php $__env->startSection('content'); ?>

<?php
    $sortedRecords = $records->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
    $first3mo = \DB::table('first3months_data')->where('facility_id', $facility->id)->first();
    if ($first3mo && is_numeric($first3mo->month1) && is_numeric($first3mo->month2) && is_numeric($first3mo->month3)
        && $first3mo->month1 > 0 && $first3mo->month2 > 0 && $first3mo->month3 > 0) {
        $baselineAvg = (floatval($first3mo->month1) + floatval($first3mo->month2) + floatval($first3mo->month3)) / 3;
    } else {
        $baselineAvg = $facility->baseline_kwh;
    }
    $hasBaseline = $baselineAvg > 0;
?>
<?php
    $currentYear = date('Y');
    $showAll = request('show_all') === '1';
    $selectedYear = request('year') ?? ($showAll ? $currentYear - 1 : $currentYear);
    $years = $records->pluck('year')->unique()->sortDesc()->values();
    $filteredRecords = $records->where('year', $selectedYear);
    $sortedRecords = $filteredRecords->sortBy(fn($r) => $r->year . str_pad($r->month, 2, '0', STR_PAD_LEFT));
?>

<div>


    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div>
            <span style="font-size:2rem; color:#2563eb; font-weight:700; letter-spacing:0.5px;">Facility: </span>
            <span style="font-size:2rem; color:#222; font-weight:600;"><?php echo e($facility->name); ?></span>
        </div>
        <button onclick="openAddModal()" class="btn btn-primary" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 22px; font-size:1.05rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.18s; <?php if(!$hasBaseline): ?> opacity:0.5; pointer-events:none; <?php endif; ?>" <?php if(!$hasBaseline): ?> disabled title="You need at least 3 months of data before adding monthly records." <?php endif; ?>>+ Monthly Energy Records</button>
    </div>

    <div style="margin-bottom:10px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <h2 style="font-size:1.15rem; font-weight:700; color:#222; margin:0;">Monthly Energy Records</h2>
        <?php if($showAll): ?>
        <form method="get" style="display: flex; align-items: center; gap: 8px;">
            <input type="hidden" name="show_all" value="1">
            <label for="year" style="font-weight:600; margin-right:4px;">Year:</label>
            <select name="year" id="year" onchange="this.form.submit()" style="padding:6px 12px; border-radius:7px; border:1px solid #c3cbe5; font-size:1rem;">
                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($year != $currentYear): ?>
                        <option value="<?php echo e($year); ?>" <?php if($year == $selectedYear): ?> selected <?php endif; ?>><?php echo e($year); ?></option>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        <?php endif; ?>
    </div>



    <!-- Average kWh Card -->

    <?php if(!$hasBaseline): ?>
        <div style="margin-bottom:1.2rem;color:#e11d48;font-weight:500;">You need to enter first 3 months data before you can add a monthly energy record.</div>
    <?php endif; ?>
    <?php
        $sizeLabel = '';
        if ($hasBaseline) {
            if ($baselineAvg <= 1000) {
                $sizeLabel = 'Small';
            } elseif ($baselineAvg <= 3000) {
                $sizeLabel = 'Medium';
            } elseif ($baselineAvg <= 10000) {
                $sizeLabel = 'Large';
            } else {
                $sizeLabel = 'Extra Large';
            }
        }
    ?>

    <?php if($hasBaseline): ?>
    <div style="margin-bottom:18px; display:flex; align-items:center; font-size:1.08rem;font-weight:600;color:#6366f1;">
        <span>
            Facility Size: <span style="font-weight:700;"><?php echo e($sizeLabel); ?></span>
        </span>
        <span style="margin-left:auto; font-size:1.08rem; color:#222; font-weight:600;">
            Baseline kWh (First 3 Months): <span style="font-weight:700; color:#2563eb;"><?php echo e(number_format($baselineAvg, 2)); ?> kWh</span>
        </span>
    </div>
    <?php endif; ?>

    <!-- Monthly Records Table -->
    <div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead style="background:#f1f5f9;">
                <tr style="text-align:center;">
                    <th style="padding:10px 14px; text-align:center;">Year</th>
                    <th style="padding:10px 14px; text-align:center;">Month</th>
                    <th style="padding:10px 14px; text-align:center;">Day</th>
                    <th style="padding:10px 14px; text-align:center;">Actual kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Average kWh</th>
                    <th style="padding:10px 14px; text-align:center;">Deviation (%)</th>
                    <th style="padding:10px 14px; text-align:center;">Alert</th>
                    <th style="padding:10px 14px; text-align:center;">Energy Cost</th>
                    <th style="padding:10px 14px; text-align:center;">Bill Image</th>
                    <th style="padding:10px 14px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $sortedRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr style="border-bottom:1px solid #e5e7eb; text-align:center;">
                    <td style="padding:10px 14px; text-align:center;"><?php echo e($record->year); ?></td>
                    <td style="padding:10px 14px; text-align:center;">
                        <?php
                            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        ?>
                        <?php echo e($months[$record->month - 1] ?? $record->month); ?>

                    </td>
                    <td style="padding:10px 14px; text-align:center;"><?php echo e($record->day ?? '-'); ?></td>
                    <td style="padding:10px 14px; text-align:center;"><?php echo e(number_format($record->actual_kwh, 2)); ?></td>
                    <td style="padding:10px 14px; text-align:center;"><?php echo e(number_format($baselineAvg, 2)); ?></td>
                    <td style="padding:10px 14px; text-align:center;">
                        <?php
                            $actual = $record->actual_kwh;
                            $baseline = $baselineAvg;
                            $deviation = $baseline > 0 ? round((($actual - $baseline) / $baseline) * 100, 2) : null;
                        ?>
                        <?php echo e($deviation !== null ? $deviation . '%' : ''); ?>

                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <?php
                            if ($deviation === null) {
                                $alert = '';
                            } elseif ($sizeLabel === 'Small') {
                                if ($deviation > 30) {
                                    $alert = 'High';
                                } elseif ($deviation > 15) {
                                    $alert = 'Medium';
                                } else {
                                    $alert = 'Low';
                                }
                            } elseif ($sizeLabel === 'Medium') {
                                if ($deviation > 20) {
                                    $alert = 'High';
                                } elseif ($deviation > 10) {
                                    $alert = 'Medium';
                                } else {
                                    $alert = 'Low';
                                }
                            } elseif ($sizeLabel === 'Large' || $sizeLabel === 'Extra Large') {
                                if ($deviation > 15) {
                                    $alert = 'High';
                                } elseif ($deviation > 5) {
                                    $alert = 'Medium';
                                } else {
                                    $alert = 'Low';
                                }
                            } else {
                                if ($deviation > 20) {
                                    $alert = 'High';
                                } elseif ($deviation > 10) {
                                    $alert = 'Medium';
                                } else {
                                    $alert = 'Low';
                                }
                            }
                        ?>
                        <?php if($alert === 'High'): ?>
                            <span style="color:#e11d48;font-weight:600;">High</span>
                        <?php elseif($alert === 'Medium'): ?>
                            <span style="color:#f59e42;font-weight:600;">Medium</span>
                        <?php elseif($alert === 'Low'): ?>
                            <span style="color:#22c55e;font-weight:600;">Low</span>
                        <?php else: ?>
                            <span style="color:#64748b;">&nbsp;</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <?php
                            // Use DB value if present, else compute using default rate
                            $rate = isset($record->rate_per_kwh) && $record->rate_per_kwh ? $record->rate_per_kwh : 12.00; // default 12.00
                            $computedCost = $record->actual_kwh * $rate;
                        ?>
                        ₱<?php echo e(number_format($computedCost, 2)); ?>

                    </td>
                    <td style="padding:10px 14px; text-align:center;">
                        <?php if($record->bill_image): ?>
                            <a href="<?php echo e(asset('storage/' . $record->bill_image)); ?>" target="_blank" style="color:#2563eb;text-decoration:underline;">View</a>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px; text-align:center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                        <?php if($alert === 'High'): ?>
                            <button type="button" title="Create Energy Action (High)" style="background: none; border: none; color: #e11d48; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal(<?php echo e($record->id); ?>, 'High')">
                                <span style="font-size:1.3rem;">⚠️</span>
                            </button>
                        <?php elseif($alert === 'Medium'): ?>
                            <button type="button" title="Create Energy Action (Medium)" style="background: none; border: none; color: #f59e42; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal(<?php echo e($record->id); ?>, 'Medium')">
                                <span style="font-size:1.3rem;">⚡</span>
                            </button>
                         <?php elseif($alert === 'Low'): ?>
                            <button type="button" title="View Recommendation (Low)" style="background: none; border: none; color: #22c55e; font-size: 1.3rem; cursor: pointer;" onclick="openEnergyActionModal(<?php echo e($record->id); ?>, 'Low')">
                                <span style="font-size:1.3rem;">💡</span>
                            </button>
                        <?php endif; ?>
                         <!-- Manual Add to Maintenance Button -->
                        <button type="button" title="Add to Maintenance" style="background: none; border: none; color: #2563eb; font-size: 1.3rem; cursor: pointer;" onclick="openAddMaintenanceModal(<?php echo e($record->id); ?>)">
                            <span style="font-size:1.3rem;">🛠️</span>
                        </button>
                        <!-- ADD TO MAINTENANCE MODAL -->
                        <div id="addMaintenanceModal" style="display:none;position:fixed;z-index:10070;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
                            <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
                                <div class="modal-content" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                                    <button type="button" onclick="closeAddMaintenanceModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                                    <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#2563eb;">Add to Maintenance</h3>
                                    <div id="addMaintenanceText" style="margin-bottom:18px;font-size:1.05rem;color:#222;">Add this monthly record to maintenance?</div>
                                    <form id="addMaintenanceForm" method="POST" action="" style="display:flex;gap:10px;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="record_id" id="maintenance_record_id" value="">
                                        <button type="submit" style="background:#2563eb;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">Add</button>
                                        <button type="button" onclick="closeAddMaintenanceModal()" style="background:#f3f4f6;color:#222;padding:10px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <script>
                        function openAddMaintenanceModal(recordId) {
                            document.getElementById('addMaintenanceModal').style.display = 'flex';
                            document.getElementById('maintenance_record_id').value = recordId;
                            // Set the form action dynamically if needed
                            document.getElementById('addMaintenanceForm').action = '/maintenance/add-from-record/' + recordId;
                        }
                        function closeAddMaintenanceModal() {
                            document.getElementById('addMaintenanceModal').style.display = 'none';
                        }
                        </script>
                        <form id="deleteMonthlyRecordForm-<?php echo e($record->id); ?>" action="<?php echo e(route('energy-records.delete', ['facility' => $facility->id, 'record' => $record->id])); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="button" title="Delete"
                                style="background:none;border:none;color:#e11d48;font-size:1.2rem;cursor:pointer;"
                                onclick="openDeleteMonthlyRecordModal(<?php echo e($record->id); ?>, '<?php echo e($months[$record->month-1]); ?>', <?php echo e($record->year); ?>)"
                                data-id="<?php echo e($record->id); ?>"
                            >
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                <!-- Energy Action Modal -->
                <div id="energyActionModal" style="display:none;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
                    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
                        <div style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                            <button type="button" onclick="closeEnergyActionModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                            <h2 id="energyModalTitle" style="margin-bottom:12px;font-size:1.3rem;font-weight:700;color:#2563eb;"></h2>
                            <ul id="energyRecommendations" style="margin:0 0 10px 18px;padding:0;font-size:1.08rem;color:#222;"></ul>
                            <div style="text-align:right;margin-top:18px;">
                                <button type="button" onclick="closeEnergyActionModal()" style="background:#2563eb;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-weight:600;font-size:1rem;">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                function openEnergyActionModal(recordId, level) {
                    const modal = document.getElementById('energyActionModal');
                    const title = document.getElementById('energyModalTitle');
                    const list  = document.getElementById('energyRecommendations');
                    list.innerHTML = '';
                    if (level === 'High') {
                        title.innerHTML = '⚠ High Energy Consumption';
                        list.innerHTML = `
                            <li>Immediately inspect major energy-consuming equipment</li>
                            <li>Limit non-essential electrical usage</li>
                            <li>Notify facility manager</li>
                            <li>Schedule urgent maintenance</li>
                        `;
                    } else if (level === 'Medium') {
                        title.innerHTML = '⚡ Medium Energy Alert';
                        list.innerHTML = `
                            <li>Review monthly energy usage trends</li>
                            <li>Check operating hours of equipment</li>
                            <li>Apply basic energy-saving measures</li>
                        `;
                    } else if (level === 'Low') {
                        title.innerHTML = '💡 Low Deviation - Good Practice';
                        list.innerHTML = `
                            <li>Maintain current energy-saving practices</li>
                            <li>Continue monitoring for unusual changes</li>
                            <li>Encourage staff to sustain efficiency</li>
                        `;
                    }
                    modal.style.display = 'flex';
                    setTimeout(() => { modal.classList.add('show'); }, 10);
                }

                function closeEnergyActionModal() {
                    const modal = document.getElementById('energyActionModal');
                    modal.classList.remove('show');
                    setTimeout(() => { modal.style.display = 'none'; }, 200);
                }
                </script>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" style="padding:18px 0;text-align:center;color:#b91c1c;">No records found for this facility.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:18px; display: flex; align-items: center; justify-content: flex-end;">
        <?php if(!$showAll): ?>
            <a href="?show_all=1" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:10px;padding:10px 18px;font-size:1.01rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Past Records</a>
        <?php else: ?>
            <a href="?" class="btn btn-secondary" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:10px;padding:10px 18px;font-size:1.01rem;box-shadow:0 2px 8px rgba(31,38,135,0.06);text-decoration:none;">Show Current Year Only</a>
        <?php endif; ?>
    </div>
</div>


<!-- ADD MONTHLY RECORD MODAL (Consistent UI) -->

<!-- ADD MONTHLY RECORD MODAL (Centered Overlay) -->
<div id="addModal" style="display:none;position:fixed;z-index:10050;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:420px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
            <button type="button" onclick="closeAddModal()" style="position:absolute;top:12px;right:12px;font-size:1.5rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Add Monthly Record</h2>
            <div style="font-size:1.02rem;color:#64748b;margin-bottom:18px;">Enter new monthly record details below.</div>
            <?php if($errors->has('duplicate')): ?>
                <div id="duplicateModal" style="display:flex;justify-content:center;align-items:center;position:fixed;z-index:10060;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
                    <div class="modal-content" style="max-width:400px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
                        <button type="button" onclick="closeDuplicateModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
                        <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Existing Data</h3>
                        <div style="margin-bottom:18px;font-size:1.05rem;color:#222;"><?php echo e($errors->first('duplicate')); ?></div>
                        <div style="display:flex;gap:10px;">
                            <button type="button" onclick="closeDuplicateModal()" style="background:#2563eb;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">OK</button>
                        </div>
                    </div>
                </div>
                <script>
                function closeDuplicateModal() {
                    document.getElementById('duplicateModal').style.display = 'none';
                }
                window.onload = function() {
                    if(document.getElementById('duplicateModal')) {
                        document.getElementById('addModal').style.display = 'block';
                    }
                };
                </script>
            <?php endif; ?>
            <form id="addMonthlyRecordForm" method="POST" action="<?php echo e(route('energy-records.store', ['facility' => $facility->id])); ?>" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
                <?php echo csrf_field(); ?>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_date" style="font-weight:500;">Date</label>
                    <input type="date" id="add_date" name="date" value="<?php echo e(date('Y-m-d')); ?>" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                <div style="display:flex;gap:12px;">
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_actual_kwh" style="font-weight:500;">Actual kWh</label>
                        <input type="number" step="0.01" id="add_actual_kwh" name="actual_kwh" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                        <label for="add_rate_per_kwh" style="font-weight:500;">Rate per kWh</label>
                        <input type="number" step="0.01" id="add_rate_per_kwh" name="rate_per_kwh" value="12.00" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_energy_cost" style="font-weight:500;">Energy Cost</label>
                    <input type="number" step="0.01" id="add_energy_cost" name="energy_cost" required readonly style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;background:#f3f4f6;">
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label for="add_bill_image" style="font-weight:500;">Bill Image</label>
                    <input type="file" id="add_bill_image" name="bill_image" accept="image/*" style="border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
                </div>
                
                
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="background:#2563eb;color:#fff;padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;flex:1;">Save</button>
                    <button type="button" onclick="closeAddModal()" style="background:#f3f4f6;color:#222;padding:12px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    setTimeout(computeEnergyCost, 100); // compute on open
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function computeEnergyCost() {
    const kwh = parseFloat(document.getElementById('add_actual_kwh').value) || 0;
    const rate = parseFloat(document.getElementById('add_rate_per_kwh').value) || 0;
    const cost = kwh * rate;
    document.getElementById('add_energy_cost').value = cost ? cost.toFixed(2) : '';
}
document.getElementById('add_actual_kwh').addEventListener('input', computeEnergyCost);
document.getElementById('add_rate_per_kwh').addEventListener('input', computeEnergyCost);
</script>

<!-- DELETE MONTHLY RECORD MODAL -->
<div id="deleteMonthlyRecordModal" style="display:none;position:fixed;z-index:10050;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);justify-content:center;align-items:center;">
    <div style="display:flex;justify-content:center;align-items:center;width:100vw;height:100vh;">
        <div class="modal-content" style="max-width:380px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:28px 24px;position:relative;">
            <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="position:absolute;top:10px;right:10px;font-size:1.3rem;background:none;border:none;color:#64748b;cursor:pointer;">&times;</button>
            <h3 style="margin-bottom:12px;font-size:1.2rem;font-weight:700;color:#e11d48;">Delete Monthly Record</h3>
            <div id="deleteMonthlyRecordText" style="margin-bottom:18px;font-size:1.05rem;color:#222;"></div>
            <div style="display:flex;gap:10px;">
                <button id="confirmDeleteMonthlyRecordBtn" type="button" style="background:#e11d48;color:#fff;padding:10px 0;border:none;border-radius:8px;font-weight:700;font-size:1.05rem;flex:1;">Delete</button>
                <button type="button" onclick="closeDeleteMonthlyRecordModal()" style="background:#f3f4f6;color:#222;padding:10px 0;border:none;border-radius:8px;font-weight:600;flex:1;">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteMonthlyRecordId = null;
function openDeleteMonthlyRecordModal(recordId, monthName, year) {
    deleteMonthlyRecordId = recordId;
    document.getElementById('deleteMonthlyRecordText').innerText = `Are you sure you want to delete the record for ${monthName} ${year}?`;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'flex';
}
function closeDeleteMonthlyRecordModal() {
    deleteMonthlyRecordId = null;
    document.getElementById('deleteMonthlyRecordModal').style.display = 'none';
}
document.getElementById('confirmDeleteMonthlyRecordBtn').onclick = function() {
    if (deleteMonthlyRecordId) {
        document.getElementById('deleteMonthlyRecordForm-' + deleteMonthlyRecordId).submit();
    }
};
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/facilities/monthly-record/records.blade.php ENDPATH**/ ?>