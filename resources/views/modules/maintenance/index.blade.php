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
    .table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .table-search {
        width: min(420px, 100%);
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 8px 12px;
        font-size: 0.92rem;
        color: #1e293b;
        background: #fff;
    }
    .table-search:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.25);
    }
    .result-count {
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 700;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .status-pill.pending { background: #fffbeb; color: #a16207; border-color: #fde68a; }
    .status-pill.ongoing { background: #ecfeff; color: #0e7490; border-color: #bae6fd; }
    .status-pill.completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .remarks-cell {
        color: #64748b;
        max-width: 300px;
        margin: 0 auto;
        text-align: left;
    }
    .hidden-row {
        display: none;
    }

    /* Maintenance Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.6);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        padding: 16px;
    }
    .maintenance-modal {
        width: min(560px, 95vw);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
        max-height: 92vh;
        overflow-y: auto;
    }
    .maintenance-modal-header {
        padding: 20px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 18px 18px 0 0;
    }
    .maintenance-modal-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
    }
    .maintenance-modal-close {
        background: none;
        border: none;
        font-size: 1.6rem;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
    }
    .maintenance-modal-close:hover { color: #334155; }
    .maintenance-modal-body { padding: 20px 22px; }
    .maintenance-form { display: flex; flex-direction: column; gap: 14px; }
    .maintenance-form .field-group { display: flex; flex-direction: column; gap: 6px; }
    .maintenance-form .field-label {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .maintenance-form .field-control {
        width: 100%;
        padding: 11px 12px;
        border-radius: 9px;
        border: 1px solid #cbd5e1;
        font-size: 0.95rem;
        background: #fff;
        color: #1e293b;
    }
    .maintenance-form .field-control:focus {
        outline: none;
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.24);
    }
    .maintenance-form .field-control[disabled] {
        background: #f8fafc;
        color: #64748b;
    }
    .maintenance-form textarea.field-control {
        min-height: 84px;
        resize: vertical;
    }
    .maintenance-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .trigger-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 8px;
    }
    .maintenance-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 4px;
    }
    .maintenance-btn-cancel,
    .maintenance-btn-save {
        border: none;
        border-radius: 9px;
        padding: 10px 16px;
        font-weight: 700;
        cursor: pointer;
    }
    .maintenance-btn-cancel {
        background: #f1f5f9;
        color: #475569;
    }
    .maintenance-btn-save {
        background: #2563eb;
        color: #fff;
    }
    @media (max-width: 760px) {
        .maintenance-form-grid {
            grid-template-columns: 1fr;
        }
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
        <a href="{{ route('modules.maintenance.index') }}" class="btn-filter" style="background:#fff;color:#334155;border:1px solid #cbd5e1;text-decoration:none;">Reset</a>
    </form>

    <div class="table-toolbar">
        <input
            type="text"
            id="maintenanceSearch"
            class="table-search"
            placeholder="Quick search: facility, issue type, status, remarks..."
        >
        <div class="result-count">Visible rows: <span id="maintenanceVisibleCount">{{ count($maintenanceRows ?? []) }}</span></div>
    </div>

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
                @forelse($maintenanceRows ?? [] as $i => $row)
                @php
                    $statusKey = strtolower((string) ($row['maintenance_status'] ?? ''));
                    $statusClass = str_contains($statusKey, 'complete') ? 'completed' : (str_contains($statusKey, 'ongoing') ? 'ongoing' : 'pending');
                    $searchText = strtolower(implode(' ', [
                        $row['facility'] ?? '',
                        $row['issue_type'] ?? '',
                        $row['trigger_month'] ?? '',
                        $row['maintenance_status'] ?? '',
                        $row['scheduled_date'] ?? '',
                        $row['remarks'] ?? '',
                    ]));
                @endphp
                <tr data-id="{{ $row['id'] ?? $i }}"
                    data-trigger_month="{{ $row['trigger_month'] ?? '' }}"
                    data-status="{{ $statusClass }}"
                    data-search="{{ $searchText }}"
                    data-maintenance_type="{{ $row['maintenance_type'] ?? '' }}" 
                    data-scheduled_date="{{ $row['scheduled_date'] ?? '' }}" 
                    data-assigned_to="{{ $row['assigned_to'] ?? '' }}" 
                    data-completed_date="{{ $row['completed_date'] ?? '' }}">
                    <td style="font-weight:700;">{{ $row['facility'] }}</td>
                    <td>{{ $row['issue_type'] }}</td>
                    <td>{{ $row['trigger_month'] }}</td>
                    <!-- Efficiency value removed -->
                    <td><span class="status-pill {{ $statusClass }}">{{ $row['maintenance_status'] }}</span></td>
                    <td>{{ $row['scheduled_date'] }}</td>
                    <td style="color:#64748b;">
                        <div class="remarks-cell" title="{{ $row['remarks'] ?? '-' }}">{{ \Illuminate\Support\Str::limit((string) ($row['remarks'] ?? '-'), 95) }}</div>
                    </td>
                    <td>{!! str_replace('btn btn-sm', 'btn btn-sm schedule-btn', $row['action']) !!}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:40px; color:#94a3b8;">No facilities needing maintenance found.</td></tr>
                @endforelse
                <tr id="maintenanceNoMatchRow" class="hidden-row">
                    <td colspan="7" style="padding:28px; color:#94a3b8;">No matching maintenance records found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="scheduleModal" class="modal-overlay">
    <div class="maintenance-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="maintenance-modal-header">
            <h3 id="modalTitle" class="maintenance-modal-title">Schedule Maintenance</h3>
            <button type="button" onclick="closeScheduleModal()" class="maintenance-modal-close" aria-label="Close modal">&times;</button>
        </div>
        <div class="maintenance-modal-body">
            <form id="scheduleForm" class="maintenance-form">
                <input type="hidden" name="maintenance_id" id="modalMaintenanceId">

                <div class="field-group">
                    <label for="modalFacility" class="field-label">Facility</label>
                    <select id="modalFacility" class="field-control">
                        <option value="" disabled selected>Select Facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalTriggerMonth" class="field-label">Trigger Month and Year</label>
                        <div class="trigger-grid">
                            <select id="modalTriggerMonth" class="field-control">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endforeach
                            </select>
                            <select id="modalTriggerYear" class="field-control">
                                @php $currentYear = date('Y'); @endphp
                                @for($y = $currentYear-2; $y <= $currentYear+2; $y++)
                                    <option value="{{ $y }}" @if($y==$currentYear) selected @endif>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="modalIssueType" class="field-label">Issue Type</label>
                        <select id="modalIssueType" class="field-control">
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

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalMaintType" class="field-label">Maintenance Type</label>
                        <select id="modalMaintType" class="field-control">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="modalScheduleDate" class="field-label">Scheduled Date</label>
                        <input type="date" id="modalScheduleDate" class="field-control">
                    </div>
                </div>

                <div class="field-group">
                    <label for="modalAssignedTo" class="field-label">Assigned To</label>
                    <input type="text" id="modalAssignedTo" class="field-control" placeholder="e.g. Engr. Cruz">
                </div>

                <div class="field-group">
                    <label for="modalRemarks" class="field-label">Remarks</label>
                    <textarea id="modalRemarks" class="field-control" placeholder="Add notes or maintenance details..."></textarea>
                </div>

                <div class="maintenance-form-grid">
                    <div class="field-group">
                        <label for="modalStatus" class="field-label">Status</label>
                        <select id="modalStatus" class="field-control">
                            <option value="Pending">Pending</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="modalCompletedDate" class="field-label">Completed Date</label>
                        <input type="date" id="modalCompletedDate" class="field-control" disabled>
                    </div>
                </div>

                <div class="maintenance-modal-actions">
                    <button type="button" onclick="closeScheduleModal()" class="maintenance-btn-cancel">Cancel</button>
                    <button type="submit" class="maintenance-btn-save">Save Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('successAlert');
    if (successAlert) setTimeout(() => { successAlert.style.opacity = '0'; setTimeout(() => successAlert.remove(), 500); }, 3000);

    const scheduleModal = document.getElementById('scheduleModal');
    const scheduleForm = document.getElementById('scheduleForm');
    const modalTitle = document.getElementById('modalTitle');
    const modalMaintenanceId = document.getElementById('modalMaintenanceId');
    const modalFacility = document.getElementById('modalFacility');
    const modalTriggerMonth = document.getElementById('modalTriggerMonth');
    const modalTriggerYear = document.getElementById('modalTriggerYear');
    const modalIssueType = document.getElementById('modalIssueType');
    const modalMaintType = document.getElementById('modalMaintType');
    const modalScheduleDate = document.getElementById('modalScheduleDate');
    const modalAssignedTo = document.getElementById('modalAssignedTo');
    const modalRemarks = document.getElementById('modalRemarks');
    const modalStatus = document.getElementById('modalStatus');
    const modalCompletedDate = document.getElementById('modalCompletedDate');
    const maintenanceSearch = document.getElementById('maintenanceSearch');
    const visibleCountEl = document.getElementById('maintenanceVisibleCount');
    const noMatchRow = document.getElementById('maintenanceNoMatchRow');
    const tableRows = Array.from(document.querySelectorAll('.maint-table tbody tr[data-search]'));

    const updateCompletedDateState = () => {
        if (!modalStatus || !modalCompletedDate) return;
        const completed = modalStatus.value === 'Completed';
        modalCompletedDate.disabled = !completed;
        if (!completed) {
            modalCompletedDate.value = '';
            return;
        }
        if (!modalCompletedDate.value) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            modalCompletedDate.value = `${yyyy}-${mm}-${dd}`;
        }
    };

    const parseTriggerMonth = (triggerText) => {
        const text = String(triggerText || '').trim();
        const match = text.match(/^([A-Za-z]+)\s+(\d{4})$/);
        if (!match) return { month: '', year: '' };
        const monthMap = {
            january: '01', february: '02', march: '03', april: '04', may: '05', june: '06',
            july: '07', august: '08', september: '09', october: '10', november: '11', december: '12',
            jan: '01', feb: '02', mar: '03', apr: '04', jun: '06', jul: '07', aug: '08', sep: '09', oct: '10', nov: '11', dec: '12'
        };
        const month = monthMap[match[1].toLowerCase()] || '';
        return { month, year: match[2] || '' };
    };

    const openScheduleModal = () => {
        if (scheduleModal) scheduleModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    const applyLocalSearch = () => {
        if (!tableRows.length) return;
        const query = String(maintenanceSearch?.value || '').trim().toLowerCase();
        let visible = 0;
        tableRows.forEach((row) => {
            const haystack = String(row.getAttribute('data-search') || '');
            const matched = query === '' || haystack.includes(query);
            row.classList.toggle('hidden-row', !matched);
            if (matched) visible++;
        });
        if (visibleCountEl) visibleCountEl.textContent = String(visible);
        if (noMatchRow) noMatchRow.classList.toggle('hidden-row', visible !== 0);
    };

    document.querySelectorAll('.schedule-btn').forEach((btn) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            if (!row) return;
            const cells = row.querySelectorAll('td');

            if (modalTitle) modalTitle.innerText = 'Update Maintenance';
            if (modalMaintenanceId) modalMaintenanceId.value = row.getAttribute('data-id') || '';

            const facilityName = cells[0]?.innerText.trim();
            if (modalFacility && facilityName) {
                for (let i = 0; i < modalFacility.options.length; i++) {
                    if (modalFacility.options[i].text === facilityName) {
                        modalFacility.selectedIndex = i;
                        break;
                    }
                }
                modalFacility.disabled = true;
            }

            if (modalIssueType) {
                modalIssueType.value = cells[1]?.innerText.trim() || '';
                modalIssueType.disabled = true;
            }

            const triggerMonthText = row.getAttribute('data-trigger_month') || cells[2]?.innerText || '';
            const parsed = parseTriggerMonth(triggerMonthText);
            if (modalTriggerMonth) {
                if (parsed.month) modalTriggerMonth.value = parsed.month;
                modalTriggerMonth.disabled = true;
            }
            if (modalTriggerYear) {
                if (parsed.year) modalTriggerYear.value = parsed.year;
                modalTriggerYear.disabled = true;
            }

            if (modalMaintType) modalMaintType.value = row.getAttribute('data-maintenance_type') || 'Preventive';
            if (modalScheduleDate) modalScheduleDate.value = row.getAttribute('data-scheduled_date') || '';
            if (modalAssignedTo) modalAssignedTo.value = row.getAttribute('data-assigned_to') || '';
            if (modalRemarks) {
                const remarksText = row.querySelector('.remarks-cell')?.getAttribute('title') || '';
                modalRemarks.value = remarksText === '-' ? '' : remarksText;
            }
            if (modalStatus) {
                const statusText = row.querySelector('.status-pill')?.innerText?.trim() || cells[3]?.innerText.trim() || 'Pending';
                modalStatus.value = statusText;
            }
            if (modalCompletedDate) modalCompletedDate.value = row.getAttribute('data-completed_date') || '';
            updateCompletedDateState();
            openScheduleModal();
        });
    });

    const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
    if (addMaintenanceBtn) {
        addMaintenanceBtn.addEventListener('click', function() {
            if (scheduleForm) scheduleForm.reset();
            if (modalTitle) modalTitle.innerText = 'Schedule Maintenance';
            if (modalMaintenanceId) modalMaintenanceId.value = '';
            if (modalFacility) modalFacility.disabled = false;
            if (modalIssueType) modalIssueType.disabled = false;
            if (modalTriggerMonth) modalTriggerMonth.disabled = false;
            if (modalTriggerYear) modalTriggerYear.disabled = false;
            if (modalStatus) modalStatus.value = 'Pending';
            updateCompletedDateState();
            openScheduleModal();
        });
    }

    if (modalStatus) modalStatus.addEventListener('change', updateCompletedDateState);

    if (scheduleForm) {
        scheduleForm.onsubmit = async function(e) {
            e.preventDefault();
            const status = modalStatus?.value;
            const completedDate = modalCompletedDate?.value;
            if (status === 'Completed' && !completedDate) {
                window.alert('Completed Date is required!');
                return false;
            }

            const monthNum = modalTriggerMonth?.value;
            const yearVal = modalTriggerYear?.value;
            const monthName = monthNum
                ? new Date(2000, parseInt(monthNum, 10) - 1, 1).toLocaleString('default', { month: 'long' })
                : '';
            const triggerMonth = monthName && yearVal ? `${monthName} ${yearVal}` : '';
            const payload = {
                maintenance_id: modalMaintenanceId?.value || '',
                facility_id: modalFacility?.value,
                trigger_month: triggerMonth,
                issue_type: modalIssueType?.value,
                maintenance_type: modalMaintType?.value,
                scheduled_date: modalScheduleDate?.value,
                assigned_to: modalAssignedTo?.value,
                remarks: modalRemarks?.value,
                maintenance_status: status,
                completed_date: completedDate,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            };

            try {
                const response = await fetch("{{ route('modules.maintenance.schedule') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': payload._token },
                    body: JSON.stringify(payload),
                });
                const body = await response.json().catch(() => ({}));
                if (!response.ok || !body.success) {
                    const errors = body?.errors ? Object.values(body.errors).flat().join('\n') : '';
                    window.alert(body?.message || errors || 'Failed to save maintenance.');
                    return;
                }
                location.reload();
            } catch (err) {
                window.alert('Network error while saving maintenance.');
            }
        };
    }

    if (maintenanceSearch) maintenanceSearch.addEventListener('input', applyLocalSearch);
    applyLocalSearch();

    if (scheduleModal) {
        scheduleModal.addEventListener('click', function(e) {
            if (e.target === scheduleModal) closeScheduleModal();
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeScheduleModal();
    });
});

function closeScheduleModal() {
    const scheduleModal = document.getElementById('scheduleModal');
    if (scheduleModal) scheduleModal.style.display = 'none';
    document.body.style.overflow = '';
}
</script>
@endsection
