<meta name="csrf-token" content="{{ csrf_token() }}">
@extends('layouts.qc-admin')
@section('title', 'Facilities Needing Maintenance')

@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
    $userRole = strtolower($user->role ?? '');
@endphp

@section('content')
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
        box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow: hidden;
    }
</style>

{{-- Alerts --}}
@if(session('success'))
<div id="successAlert" style="position:fixed;top:32px;right:32px;z-index:99999;min-width:280px;">
    <div style="background:#dcfce7;color:#166534;padding:16px 24px;border-radius:12px;font-weight:700;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:flex;align-items:center;gap:10px;">
        <i class="fa fa-check-circle" style="color:#22c55e; font-size: 1.2rem;"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

<div class="report-card">
    <div class="page-header">
        <h2>Facilities Needing <span>Maintenance</span></h2>
        <div style="display:flex; gap:10px;">
             <button id="addMaintenanceBtn" class="btn btn-primary" style="background:#10b981; color:#fff; padding:10px 20px; border-radius:10px; font-weight:700; border:none; cursor:pointer;">
                <i class="fa fa-plus"></i> Add Manual
            </button>
            <a href="{{ route('maintenance.history') }}" style="background:#2563eb; color:#fff; padding:10px 20px; border-radius:10px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-history"></i> History
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-box" style="background: #fff1f2;">
            <div class="stat-label" style="color: #e11d48;">🔴 Needing Maint.</div>
            <div class="stat-value">{{ $needingCount ?? 0 }}</div>
        </div>
        <div class="stat-box" style="background: #fefce8;">
            <div class="stat-label" style="color: #a16207;">🟡 Pending</div>
            <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
        </div>
        <div class="stat-box" style="background: #f0fdf4;">
            <div class="stat-label" style="color: #15803d;">🔧 Ongoing</div>
            <div class="stat-value">{{ $ongoingCount ?? 0 }}</div>
        </div>
        <div class="stat-box" style="background: #ecfeff;">
            <div class="stat-label" style="color: #0e7490;">✅ Completed</div>
            <div class="stat-value">{{ $completedCount ?? 0 }}</div>
        </div>
    </div>

    <form method="GET" action="" class="filter-section">
        <div class="filter-group">
            <label>Facility</label>
            <select name="facility_id" id="facility_id">
                <option value="" disabled selected>Select Facility</option>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility_id') == $facility->id) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Month</label>
            <select name="month" id="month">
                <option value="" disabled selected>Select Month</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}" @if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)) selected @endif>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select name="maintenance_type" id="maintenance_type">
                <option value="">All Types</option>
                <option value="Preventive" @if(request('maintenance_type') == 'Preventive') selected @endif>Preventive</option>
                <option value="Corrective" @if(request('maintenance_type') == 'Corrective') selected @endif>Corrective</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" id="status">
                <option value="">All Status</option>
                <option value="Pending" @if(request('status') == 'Pending') selected @endif>Pending</option>
                <option value="Ongoing" @if(request('status') == 'Ongoing') selected @endif>Ongoing</option>
                <option value="Completed" @if(request('status') == 'Completed') selected @endif>Completed</option>
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
                    <th>Efficiency</th>
                    <th>Status</th>
                    <th>Scheduled</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceRows ?? [] as $i => $row)
                <tr data-id="{{ $row['id'] ?? $i }}" 
                    data-maintenance_type="{{ $row['maintenance_type'] ?? '' }}" 
                    data-scheduled_date="{{ $row['scheduled_date'] ?? '' }}" 
                    data-assigned_to="{{ $row['assigned_to'] ?? '' }}" 
                    data-completed_date="{{ $row['completed_date'] ?? '' }}">
                    <td style="font-weight:700;">{{ $row['facility'] }}</td>
                    <td>{{ $row['issue_type'] }}</td>
                    <td>{{ $row['trigger_month'] }}</td>
                    <td>{{ $row['efficiency_rating'] }}</td>
                    <td><span style="padding:4px 10px; background:#f1f5f9; border-radius:20px; font-size:0.8rem; font-weight:700;">{{ $row['maintenance_status'] }}</span></td>
                    <td>{{ $row['scheduled_date'] }}</td>
                    <td style="color:#64748b;">{{ $row['remarks'] ?? '-' }}</td>
                    <td>{!! str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']) !!}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:40px; color:#94a3b8;">No facilities needing maintenance found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="modal-content">
        <div style="padding:20px 30px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div id="modalTitle" style="font-size:1.2rem; font-weight:800; color:#1e293b;">Schedule Maintenance</div>
            <button onclick="closeScheduleModal()" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer;">&times;</button>
        </div>
        <div style="padding:30px;">
            <form id="scheduleForm">
                <input type="hidden" name="maintenance_id" id="modalMaintenanceId">
                
                <div style="margin-bottom:15px;">
                    <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">FACILITY</label>
                    <select id="modalFacility" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                        <option value="" disabled selected>Select Facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">TRIGGER MONTH</label>
                        <select id="modalTriggerMonth" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                            @foreach(range(1,12) as $m)
                                <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">ISSUE TYPE</label>
                        <input type="text" id="modalIssueType" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                    </div>
                </div>

                <div id="efficiencyRatingGroup" style="margin-bottom:15px;">
                    <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">EFFICIENCY RATING</label>
                    <select id="modalEfficiencyRating" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">TYPE & DATE</label>
                    <div style="display:flex; gap:10px;">
                        <select id="modalMaintType" style="flex:1; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                        </select>
                        <input type="date" id="modalScheduleDate" style="flex:1; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">ASSIGNED TO</label>
                    <input type="text" id="modalAssignedTo" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">REMARKS</label>
                    <textarea id="modalRemarks" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; min-height:60px;"></textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                    <div>
                        <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">STATUS</label>
                        <select id="modalStatus" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.75rem; color:#475569; display:block; margin-bottom:5px;">COMPLETED DATE</label>
                        <input type="date" id="modalCompletedDate" disabled style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
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
    document.getElementById('modalStatus').addEventListener('change', function() {
        const compDate = document.getElementById('modalCompletedDate');
        compDate.disabled = (this.value !== 'Completed');
        if (compDate.disabled) compDate.value = '';
    });

    // Schedule/Edit button click
    document.querySelectorAll('.schedule-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            const idx = Array.from(row.parentNode.children).indexOf(row);
            const cells = row.querySelectorAll('td');

            document.getElementById('modalTitle').innerText = 'Update Maintenance';
            document.getElementById('modalMaintenanceId').value = idx;
            
            // Prefill logic
            const facilityName = cells[0].innerText.trim();
            const facSelect = document.getElementById('modalFacility');
            for(let i=0; i<facSelect.options.length; i++) {
                if(facSelect.options[i].text === facilityName) facSelect.selectedIndex = i;
            }
            facSelect.disabled = true;

            document.getElementById('modalIssueType').value = cells[1].innerText;
            document.getElementById('modalIssueType').readOnly = true;
            
            document.getElementById('modalTriggerMonth').disabled = true;
            document.getElementById('efficiencyRatingGroup').style.display = 'none';

            document.getElementById('modalMaintType').value = row.getAttribute('data-maintenance_type') || 'Preventive';
            document.getElementById('modalScheduleDate').value = row.getAttribute('data-scheduled_date') || '';
            document.getElementById('modalAssignedTo').value = row.getAttribute('data-assigned_to') || '';
            document.getElementById('modalRemarks').value = cells[6].innerText !== '-' ? cells[6].innerText : '';
            document.getElementById('modalStatus').value = cells[4].innerText.trim();
            document.getElementById('modalCompletedDate').value = row.getAttribute('data-completed_date') || '';
            document.getElementById('modalCompletedDate').disabled = (document.getElementById('modalStatus').value !== 'Completed');

            document.getElementById('scheduleModal').style.display = 'flex';
        });
    });

    // Add Manual Button
    document.getElementById('addMaintenanceBtn').addEventListener('click', function() {
        document.getElementById('scheduleForm').reset();
        document.getElementById('modalTitle').innerText = 'Schedule Maintenance';
        document.getElementById('modalMaintenanceId').value = '';
        document.getElementById('modalFacility').disabled = false;
        document.getElementById('modalTriggerMonth').disabled = false;
        document.getElementById('modalIssueType').readOnly = false;
        document.getElementById('efficiencyRatingGroup').style.display = 'block';
        document.getElementById('scheduleModal').style.display = 'flex';
    });

    // Form Submission
    document.getElementById('scheduleForm').onsubmit = function(e) {
        e.preventDefault();
        const status = document.getElementById('modalStatus').value;
        const compDate = document.getElementById('modalCompletedDate').value;

        if (status === 'Completed' && !compDate) {
            alert('Completed Date is required!');
            return false;
        }

        const idx = document.getElementById('modalMaintenanceId').value;
        const tableBody = document.querySelector('.maint-table tbody');
        const row = idx !== '' ? tableBody.children[idx] : null;

        const data = {
            maintenance_id: row ? row.getAttribute('data-id') : '',
            facility_id: document.getElementById('modalFacility').value,
            trigger_month: document.getElementById('modalTriggerMonth').value,
            issue_type: document.getElementById('modalIssueType').value,
            efficiency_rating: document.getElementById('modalEfficiencyRating').value,
            maintenance_type: document.getElementById('modalMaintType').value,
            scheduled_date: document.getElementById('modalScheduleDate').value,
            assigned_to: document.getElementById('modalAssignedTo').value,
            remarks: document.getElementById('modalRemarks').value,
            maintenance_status: status,
            completed_date: compDate,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        fetch("{{ route('modules.maintenance.schedule') }}", {
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
});

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}
</script>
@endsection