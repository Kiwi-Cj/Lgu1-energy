
<?php $__env->startSection('title', 'Energy Efficiency Analysis'); ?>
<?php $__env->startSection('content'); ?>
<div style="max-width:1100px;margin:0 auto;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">Energy Efficiency Analysis</h2>
    <p style="color:#555;margin-bottom:24px;">Analyze energy usage trends, compare actual vs baseline kWh, and identify opportunities for efficiency improvements across all facilities.</p>
    <!-- TOP SUMMARY CARDS -->
    <div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 High Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($highCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fef9c3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#eab308;">🟡 Medium Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($mediumCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🔴 Low Efficiency</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($lowCount ?? 0); ?></div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#f59e42;">⚠️ Auto-Flagged for Maintenance</div>
            <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo e($flaggedCount ?? 0); ?></div>
        </div>
    </div>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Facilities</option>
                <?php $__currentLoopData = $facilities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($facility->id); ?>" <?php if(request('facility_id') == $facility->id): ?> selected <?php endif; ?>><?php echo e($facility->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;min-width:120px;">
            <label for="month" style="font-weight:700;margin-bottom:4px;">Month</label>
            <select name="month" id="month" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="all" <?php if(request('month') == 'all' || !request('month')): ?> selected <?php endif; ?>>All Months</option>
                <?php
                    $availableMonths = isset($availableMonths) && is_array($availableMonths) && count($availableMonths)
                        ? $availableMonths
                        : range(1,12);
                ?>
                <?php $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(str_pad($m,2,'0',STR_PAD_LEFT)); ?>" <?php if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)): ?> selected <?php endif; ?>><?php echo e(date('F', mktime(0,0,0,$m,1))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;min-width:100px;">
            <label for="year" style="font-weight:700;margin-bottom:4px;">Year</label>
            <select name="year" id="year" class="form-control" required style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Year</option>
                <?php $currentYear = date('Y'); ?>
                <?php $__currentLoopData = range($currentYear, $currentYear-10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($y); ?>" <?php if(request('year') == $y): ?> selected <?php endif; ?>><?php echo e($y); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="rating" style="font-weight:700;margin-bottom:4px;">Rating</label>
            <select name="rating" id="rating" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="" disabled selected hidden>Select Rating</option>
                <option value="all" <?php if(request('rating') == 'all' || request('rating') == ''): ?> selected <?php endif; ?>>All Ratings</option>
                <option value="High" <?php if(request('rating') == 'High'): ?> selected <?php endif; ?>>High</option>
                <option value="Medium" <?php if(request('rating') == 'Medium'): ?> selected <?php endif; ?>>Medium</option>
                <option value="Low" <?php if(request('rating') == 'Low'): ?> selected <?php endif; ?>>Low</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
        </div>
    </form>
    <?php
        $hasFilter = request('facility_id') || request('month') || request('rating');
    ?>
    <?php if($hasFilter): ?>
    <!-- MAIN TABLE -->
    <table class="table" style="width:100%;margin-top:12px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
        <thead style="background:#e9effc;">
            <tr>
                <th style="text-align:center;">Facility</th>
                <th style="text-align:center;">Month</th>
                <th style="text-align:center;">Actual kWh</th>
                <th style="text-align:center;">Avg kWh</th>
                <th style="text-align:center;">Variance</th>
                <th style="text-align:center;">EUI</th>
                <th style="text-align:center;">Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $efficiencyRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <!-- Modal link: always use $row['facility_id'] for AJAX -->
                    <a href="#" style="color:#2563eb;font-weight:600;text-decoration:underline;cursor:pointer;" onclick="openFacilityModalAjaxById('<?php echo e($row['facility_id'] ?? ''); ?>', '<?php echo e($row['month']); ?>'); return false;"><?php echo e($row['facility']); ?></a>
                </td>
                <td><?php echo e($row['month']); ?></td>
                <td><?php echo e($row['actual_kwh']); ?></td>
                <td><?php echo e($row['avg_kwh']); ?></td>
                <td><?php echo e($row['variance']); ?></td>
                <td><?php echo e($row['eui']); ?></td>
                <td>
                    <?php if($row['rating'] === 'High'): ?>
                        <span style="color:#22c55e;font-weight:700;">High</span>
                    <?php elseif($row['rating'] === 'Medium'): ?>
                        <span style="color:#eab308;font-weight:700;">Medium</span>
                    <?php else: ?>
                        <span style="color:#e11d48;font-weight:700;">Low</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="text-center">No efficiency data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Enhanced Facility Modal (read-only, no UI change) -->
<div id="facilityModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;">
    <div style="background:#fff;max-width:700px;width:95vw;max-height:90vh;overflow:auto;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.18);padding:0 0 24px 0;position:relative;">
        <!-- HEADER -->
        <div style="padding:24px 32px 12px 32px;border-bottom:1px solid #e5e7eb;">
            <div style="font-size:1.5rem;font-weight:700;" id="modalFacilityName"><?php echo e($modalFacilityName ?? 'Facility Name'); ?></div>
            <div style="color:#555;font-size:1.1rem;">Type: <span id="modalFacilityType"><?php echo e($modalFacilityType ?? '-'); ?></span> | Barangay: <span id="modalFacilityLocation"><?php echo e($modalFacilityLocation ?? '-'); ?></span></div>
        </div>
        <div style="padding:18px 32px;">
            <!-- ENERGY PROFILE SUMMARY -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Energy Profile</div>
                <div style="display:flex;gap:18px;flex-wrap:wrap;">
                    <div>Avg Monthly kWh: <span id="modalAvgKwh"><?php echo e($modalAvgKwh ?? '-'); ?></span></div>
                    <div>Main Source: <span id="modalMainSource"><?php echo e($modalMainSource ?? '-'); ?></span></div>
                    <div>Backup Power: <span id="modalBackupPower"><?php echo e($modalBackupPower ?? '-'); ?></span></div>
                    <div>No. of Meters: <span id="modalNumMeters"><?php echo e($modalNumMeters ?? '-'); ?></span></div>
                </div>
            </div>
            <!-- MONTHLY USAGE TABLE -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Monthly Usage</div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;min-width:520px;text-align:center;background:#f9fafb;border-radius:8px;">
                        <thead style="background:#e9effc;">
                            <tr>
                                <th>Month</th><th>Actual kWh</th><th>Avg kWh</th><th>Variance</th><th>Rating</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="modalUsageTable">
                            <?php if(isset($modalUsageTable) && is_array($modalUsageTable) && count($modalUsageTable)): ?>
                                <?php $__currentLoopData = $modalUsageTable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usageRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($usageRow['month'] ?? '-'); ?></td>
                                        <td><?php echo e($usageRow['actual_kwh'] ?? '-'); ?></td>
                                        <td><?php echo e($usageRow['avg_kwh'] ?? '-'); ?></td>
                                        <td><?php echo e($usageRow['variance'] ?? '-'); ?></td>
                                        <td><?php echo e($usageRow['rating'] ?? '-'); ?></td>
                                        <td><?php echo e($usageRow['status'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr><td colspan="6">Loading...</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- EUI -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Energy Use Intensity (EUI)</div>
                <div>Floor Area: <span id="modalFloorArea"><?php echo e($modalFloorArea ?? '-'); ?></span> sqm | EUI: <span id="modalEui"><?php echo e($modalEui ?? '-'); ?></span></div>
            </div>
            <!-- TREND CHART (optional) -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Trend Chart</div>
                <canvas id="modalTrendChart" height="120"></canvas>
            </div>
            <!-- AUTO-GENERATED RECOMMENDATIONS (NOT EDITABLE) -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Recommendations</div>
                <ul id="modalRecommendations" style="margin-left:18px;color:#444;font-size:1rem;">
                    <?php if(isset($modalRecommendations) && is_array($modalRecommendations) && count($modalRecommendations)): ?>
                        <?php $__currentLoopData = $modalRecommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($rec); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <li>Loading...</li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- MAINTENANCE STATUS -->
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;font-size:1.1rem;margin-bottom:6px;">Maintenance Status</div>
                <div>Last: <span id="modalLastMaint"><?php echo e($modalLastMaint ?? '-'); ?></span> | Next: <span id="modalNextMaint"><?php echo e($modalNextMaint ?? '-'); ?></span> | <span id="modalMaintRemarks"><?php echo e($modalMaintRemarks ?? '-'); ?></span></div>
                <a id="modalMaintLink" href="<?php echo e($modalMaintLink ?? '#'); ?>" style="color:#2563eb;text-decoration:underline;font-weight:500;">View Maintenance</a>
            </div>
            <!-- ACTION BUTTONS -->
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:18px;">
                <button onclick="closeFacilityModal()" style="padding:7px 22px;border-radius:7px;background:#e5e7eb;color:#222;font-weight:600;border:none;font-size:1rem;">Close</button>
                <button id="modalExportBtn" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;">Export Report</button>
            </div>
        </div>
        <button onclick="closeFacilityModal()" style="position:absolute;top:12px;right:18px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openFacilityModalAjaxById(facilityId, monthLabel) {
    if (!facilityId) return;
    // Parse monthLabel (e.g. "Mar 2026")
    let month = null, year = null;
    if (monthLabel) {
        const parts = monthLabel.split(' ');
        if (parts.length === 2) {
            const monthStr = parts[0];
            const yearStr = parts[1];
            month = (new Date(Date.parse(monthStr + ' 1, 2000'))).getMonth() + 1;
            year = parseInt(yearStr);
        }
    }
    document.getElementById('facilityModal').style.display = 'flex';
    // Loading states
    document.getElementById('modalFacilityName').textContent = 'Loading...';
    document.getElementById('modalFacilityType').textContent = '-';
    document.getElementById('modalFacilityLocation').textContent = '-';
    document.getElementById('modalAvgKwh').textContent = '-';
    document.getElementById('modalMainSource').textContent = '-';
    document.getElementById('modalBackupPower').textContent = '-';
    document.getElementById('modalNumMeters').textContent = '-';
    document.getElementById('modalFloorArea').textContent = '-';
    document.getElementById('modalEui').textContent = '-';
    document.getElementById('modalUsageTable').innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
    document.getElementById('modalLastMaint').textContent = '-';
    document.getElementById('modalNextMaint').textContent = '-';
    document.getElementById('modalMaintRemarks').textContent = '-';
    document.getElementById('modalMaintLink').href = '#';
    // Fetch data
    let url = '/modules/facilities/' + facilityId + '/modal-detail';
    if (month && year) {
        url += `?month=${month}&year=${year}`;
    }
    fetch(url)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalFacilityName').textContent = data.name || '-';
            document.getElementById('modalFacilityType').textContent = data.type || '-';
            document.getElementById('modalFacilityLocation').textContent = data.barangay || '-';
            document.getElementById('modalAvgKwh').textContent = data.avg_kwh || '-';
            document.getElementById('modalMainSource').textContent = data.main_source || '-';
            document.getElementById('modalBackupPower').textContent = data.backup_power || '-';
            document.getElementById('modalNumMeters').textContent = data.num_meters || '-';
            document.getElementById('modalFloorArea').textContent = data.floor_area || '-';
            document.getElementById('modalEui').textContent = data.eui || '-';
            // Usage table
            let usageHtml = '';
            if (data.usage && data.usage.length) {
                data.usage.forEach(row => {
                    usageHtml += `<tr><td>${row.month}</td><td>${row.actual_kwh}</td><td>${row.avg_kwh}</td><td>${row.variance}</td><td>${ratingIcon(row.rating)} ${row.rating}</td><td>${row.status||'-'}</td></tr>`;
                });
            } else {
                usageHtml = '<tr><td colspan="6">No data</td></tr>';
            }
            document.getElementById('modalUsageTable').innerHTML = usageHtml;
            // Maintenance
            document.getElementById('modalLastMaint').textContent = data.last_maintenance || '-';
            document.getElementById('modalNextMaint').textContent = data.next_maintenance || '-';
            document.getElementById('modalMaintRemarks').textContent = data.maint_remarks || '-';
            document.getElementById('modalMaintLink').href = data.maint_link || '#';
            // Trend chart
            if (window.modalTrendChartObj) window.modalTrendChartObj.destroy();
            const ctx = document.getElementById('modalTrendChart').getContext('2d');
            window.modalTrendChartObj = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.trend_labels,
                    datasets: [{
                        label: 'Actual kWh',
                        data: data.trend,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#e11d48',
                        pointBorderColor: '#e11d48',
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            // Show trend analysis below chart
            let trendDiv = document.getElementById('modalTrendAnalysis');
            if (!trendDiv) {
                trendDiv = document.createElement('div');
                trendDiv.id = 'modalTrendAnalysis';
                trendDiv.style = 'margin-top:8px;font-weight:600;color:#2563eb;';
                ctx.canvas.parentNode.appendChild(trendDiv);
            }
            trendDiv.textContent = '3-Month Trend: ' + (data.trend_analysis || '-');

            // Show trend-based recommendation directly below the trend label
            let trendRecDiv = document.getElementById('modalTrendRecommendation');
            if (!trendRecDiv) {
                trendRecDiv = document.createElement('div');
                trendRecDiv.id = 'modalTrendRecommendation';
                trendRecDiv.style = 'margin-top:4px;font-size:1rem;color:#444;';
                trendDiv.parentNode.insertBefore(trendRecDiv, trendDiv.nextSibling);
            }
            // Find the first trend-based recommendation from backend (if any)
            let trendRec = '';
            if (Array.isArray(data.recommendations)) {
                for (let r of data.recommendations) {
                    if (r.startsWith('Energy usage is')) { trendRec = r; break; }
                }
            }
            trendRecDiv.textContent = trendRec;

            // Use recommendations from backend if present, else generate based on rating
            const recList = document.getElementById('modalRecommendations');
            let recs = Array.isArray(data.recommendations) ? data.recommendations.filter(r => !r.startsWith('Energy usage is')) : [];
            if (recs.length === 0 && data.usage && data.usage.length) {
                // Find the row matching the clicked month (should be the last in usage, but safer to match by label)
                let selectedMonth = null;
                if (month && year) {
                    // Compose label as in backend: e.g. 'Mar 2026'
                    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const mIdx = parseInt(month,10)-1;
                    if (mIdx >= 0 && mIdx < 12) {
                        selectedMonth = monthNames[mIdx] + ' ' + year;
                    }
                }
                let usageRow = null;
                if (selectedMonth) {
                    usageRow = data.usage.find(row => row.month === selectedMonth);
                }
                if (!usageRow) usageRow = data.usage[data.usage.length - 1];
                if (usageRow && usageRow.rating) {
                    if (usageRow.rating === 'High') {
                        recs = ['Excellent efficiency! Maintain current practices.', 'Continue monitoring for any unusual spikes.', 'Consider sharing best practices with other facilities.'];
                    } else if (usageRow.rating === 'Medium') {
                        recs = ['Review energy usage patterns.', 'Check for possible equipment inefficiencies.', 'Consider minor improvements.'];
                    } else if (usageRow.rating === 'Low') {
                        recs = ['Conduct a full energy audit.', 'Schedule maintenance for major equipment.', 'Implement corrective actions immediately.'];
                    } else {
                        recs = ['No special recommendations.'];
                    }
                } else {
                    recs = ['No special recommendations.'];
                }
            } else if (recs.length === 0) {
                recs = ['No special recommendations.'];
            }
            // Remove duplicate recommendations
            const uniqueRecs = Array.from(new Set(recs));
            recList.innerHTML = uniqueRecs.map(r => `<li>${r}</li>`).join('');
        });
}
function closeFacilityModal() {
    document.getElementById('facilityModal').style.display = 'none';
}
function ratingIcon(r) {
    if (r==='High') return '🟢';
    if (r==='Medium') return '🟡';
    if (r==='Low') return '🔴';
    return '';
}
function ratingColor(r) {
    if (r==='High') return '#22c55e';
    if (r==='Medium') return '#eab308';
    if (r==='Low') return '#e11d48';
    return '#888';
}
// Make modal open and update dynamically when facility filter changes

</script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.qc-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\energy-system\resources\views/modules/energy-efficiency-analysis/index.blade.php ENDPATH**/ ?>