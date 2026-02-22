@extends('layouts.qc-admin')

@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@section('content')
@php
    $historyCollection = collect($historyRows ?? []);
    $historyTotal = $historyCollection->count();
    $historyCompleted = $historyCollection->where('maintenance_status', 'Completed')->count();
    $historyOngoing = $historyCollection->where('maintenance_status', 'Ongoing')->count();
    $historyPending = $historyCollection->where('maintenance_status', 'Pending')->count();
@endphp
<div class="history-page">
    <div class="history-shell">
        <div class="history-header">
            <h2>Maintenance History</h2>
            <a href="{{ route('modules.maintenance.index') }}" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Maintenance
            </a>
        </div>

        <div class="metrics-grid">
            <div class="metric-card total">
                <span class="metric-label">Total</span>
                <strong>{{ $historyTotal }}</strong>
            </div>
            <div class="metric-card pending">
                <span class="metric-label">Pending</span>
                <strong>{{ $historyPending }}</strong>
            </div>
            <div class="metric-card ongoing">
                <span class="metric-label">Ongoing</span>
                <strong>{{ $historyOngoing }}</strong>
            </div>
            <div class="metric-card completed">
                <span class="metric-label">Completed</span>
                <strong>{{ $historyCompleted }}</strong>
            </div>
        </div>

        <form method="GET" action="" class="history-filters">
            <div class="filter-item">
                <label for="facility_id">Facility</label>
                <select name="facility_id" id="facility_id">
                    <option value="">All Facilities</option>
                    @foreach(\App\Models\Facility::all() as $facility)
                        <option value="{{ $facility->id }}" @if(request('facility_id') == $facility->id) selected @endif>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <label for="month">Month</label>
                <select name="month" id="month">
                    <option value="">All Months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}" @if(request('month') == str_pad($m,2,'0',STR_PAD_LEFT)) selected @endif>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <label for="maintenance_type">Type</label>
                <select name="maintenance_type" id="maintenance_type">
                    <option value="">All Types</option>
                    <option value="Preventive" @if(request('maintenance_type') == 'Preventive') selected @endif>Preventive</option>
                    <option value="Corrective" @if(request('maintenance_type') == 'Corrective') selected @endif>Corrective</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">All Status</option>
                    <option value="Pending" @if(request('status') == 'Pending') selected @endif>Pending</option>
                    <option value="Ongoing" @if(request('status') == 'Ongoing') selected @endif>Ongoing</option>
                    <option value="Completed" @if(request('status') == 'Completed') selected @endif>Completed</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">Filter</button>
            <a href="{{ route('maintenance.history') }}" class="reset-btn">Reset</a>
        </form>

        <div class="history-toolbar">
            <input type="text" id="historySearch" class="search-input" placeholder="Quick search in history...">
            <div class="result-count">Visible rows: <span id="historyVisibleCount">{{ $historyTotal }}</span></div>
        </div>

        <div class="table-shell">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Facility</th>
                        <th>Issue Type</th>
                        <th>Trigger</th>
                        <th>Trend</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyRows as $row)
                        @php
                            $statusText = strtolower((string) ($row['maintenance_status'] ?? ''));
                            $statusClass = str_contains($statusText, 'complete') ? 'completed' : (str_contains($statusText, 'ongoing') ? 'ongoing' : 'pending');
                            $historySearchText = strtolower(implode(' ', [
                                $row['id'] ?? '',
                                $row['facility'] ?? '',
                                $row['issue_type'] ?? '',
                                $row['trigger_month'] ?? '',
                                $row['trend'] ?? '',
                                $row['maintenance_type'] ?? '',
                                $row['maintenance_status'] ?? '',
                                $row['assigned_to'] ?? '',
                                $row['remarks'] ?? '',
                            ]));
                        @endphp
                        <tr data-search="{{ $historySearchText }}">
                            <td>{{ $row['id'] }}</td>
                            <td class="facility-cell">{{ $row['facility'] }}</td>
                            <td>{{ $row['issue_type'] }}</td>
                            <td>{{ $row['trigger_month'] }}</td>
                            <td>{{ $row['trend'] }}</td>
                            <td>{{ $row['maintenance_type'] }}</td>
                            <td><span class="status-pill {{ $statusClass }}">{{ $row['maintenance_status'] }}</span></td>
                            <td>{{ $row['scheduled_date'] }}</td>
                            <td>{{ $row['assigned_to'] }}</td>
                            <td>{{ $row['completed_date'] }}</td>
                            <td><div class="remarks-cell" title="{{ $row['remarks'] }}">{{ \Illuminate\Support\Str::limit((string) $row['remarks'], 90) }}</div></td>
                            <td>
                                <form action="{{ route('modules.maintenance.history.destroy', $row['id']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this history record?')" class="delete-btn" title="Delete record">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="empty-cell">No maintenance history found.</td>
                        </tr>
                    @endforelse
                    <tr id="historyNoMatchRow" style="display:none;">
                        <td colspan="12" class="empty-cell">No matching records found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('historySearch');
    const visibleCountEl = document.getElementById('historyVisibleCount');
    const noMatchRow = document.getElementById('historyNoMatchRow');
    const rows = Array.from(document.querySelectorAll('.history-table tbody tr[data-search]'));

    const applySearch = () => {
        const query = String(searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            const haystack = String(row.getAttribute('data-search') || '');
            const matched = query === '' || haystack.includes(query);
            row.style.display = matched ? '' : 'none';
            if (matched) visible++;
        });
        if (visibleCountEl) visibleCountEl.textContent = String(visible);
        if (noMatchRow) noMatchRow.style.display = visible === 0 ? '' : 'none';
    };

    if (searchInput) searchInput.addEventListener('input', applySearch);
    applySearch();
});
</script>
<style>
.history-page {
    width: 100%;
}
.history-shell {
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 28px rgba(30, 64, 175, 0.08);
    padding: 24px 18px;
}
.history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.history-header h2 {
    margin: 0;
    font-size: 1.6rem;
    color: #1e293b;
    font-weight: 800;
}
.back-btn {
    text-decoration: none;
    color: #1d4ed8;
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(130px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}
.metric-card {
    border-radius: 12px;
    border: 1px solid transparent;
    padding: 10px 12px;
}
.metric-label {
    display: block;
    text-transform: uppercase;
    font-size: 0.72rem;
    font-weight: 800;
}
.metric-card strong {
    display: block;
    font-size: 1.45rem;
    margin-top: 4px;
}
.metric-card.total { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.metric-card.pending { background: #fffbeb; border-color: #fde68a; color: #a16207; }
.metric-card.ongoing { background: #ecfeff; border-color: #bae6fd; color: #0e7490; }
.metric-card.completed { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.history-filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr)) auto auto;
    gap: 10px;
    align-items: end;
    margin-bottom: 14px;
}
.filter-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.filter-item label {
    font-size: 0.78rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #475569;
}
.filter-item select {
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
}
.filter-btn,
.reset-btn {
    border: none;
    border-radius: 9px;
    padding: 9px 12px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
}
.filter-btn {
    background: #2563eb;
    color: #fff;
}
.reset-btn {
    background: #fff;
    color: #334155;
    border: 1px solid #cbd5e1;
}
.history-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.search-input {
    width: min(430px, 100%);
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 0.92rem;
}
.search-input:focus {
    outline: none;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.25);
}
.result-count {
    color: #64748b;
    font-size: 0.86rem;
    font-weight: 700;
}
.table-shell {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    overflow-x: auto;
}
.history-table {
    width: 100%;
    border-collapse: collapse;
}
.history-table th {
    background: #f1f5f9;
    color: #475569;
    text-transform: uppercase;
    font-size: 0.75rem;
    padding: 12px 10px;
    font-weight: 800;
    border-bottom: 1px solid #e2e8f0;
}
.history-table td {
    padding: 11px 10px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.9rem;
}
.history-table tr:hover {
    background: #f8fafc;
}
.facility-cell {
    font-weight: 700;
    color: #1d4ed8;
}
.status-pill {
    display: inline-flex;
    border-radius: 999px;
    padding: 3px 10px;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1px solid transparent;
}
.status-pill.pending { background: #fffbeb; color: #a16207; border-color: #fde68a; }
.status-pill.ongoing { background: #ecfeff; color: #0e7490; border-color: #bae6fd; }
.status-pill.completed { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
.remarks-cell {
    text-align: left;
    max-width: 300px;
    margin: 0 auto;
    color: #64748b;
}
.delete-btn {
    border: none;
    background: none;
    color: #dc2626;
    cursor: pointer;
    font-size: 1rem;
}
.empty-cell {
    color: #94a3b8;
    padding: 22px 10px;
    text-align: center;
}

body.dark-mode .history-page .history-shell {
    background: #0f172a;
    border-color: #334155;
    box-shadow: 0 18px 34px rgba(2, 6, 23, 0.5);
}
body.dark-mode .history-page .history-header h2 {
    color: #e2e8f0;
}
body.dark-mode .history-page .back-btn {
    color: #dbeafe;
    background: #1e3a8a;
    border-color: #1d4ed8;
}
body.dark-mode .history-page .metric-card.total {
    background: rgba(37, 99, 235, 0.22);
    border-color: rgba(147, 197, 253, 0.35);
    color: #93c5fd;
}
body.dark-mode .history-page .metric-card.pending {
    background: rgba(146, 64, 14, 0.22);
    border-color: rgba(251, 191, 36, 0.35);
    color: #fde68a;
}
body.dark-mode .history-page .metric-card.ongoing {
    background: rgba(14, 116, 144, 0.22);
    border-color: rgba(125, 211, 252, 0.35);
    color: #67e8f9;
}
body.dark-mode .history-page .metric-card.completed {
    background: rgba(22, 101, 52, 0.22);
    border-color: rgba(74, 222, 128, 0.3);
    color: #86efac;
}
body.dark-mode .history-page .filter-item label,
body.dark-mode .history-page .result-count,
body.dark-mode .history-page .remarks-cell {
    color: #94a3b8;
}
body.dark-mode .history-page .filter-item select,
body.dark-mode .history-page .search-input {
    background: #0b1220;
    color: #e2e8f0;
    border-color: #334155;
}
body.dark-mode .history-page .search-input::placeholder {
    color: #64748b;
}
body.dark-mode .history-page .reset-btn {
    background: #111827;
    color: #e2e8f0;
    border-color: #475569;
}
body.dark-mode .history-page .table-shell {
    background: #0f172a;
    border-color: #334155;
}
body.dark-mode .history-page .history-table th {
    background: #111827;
    color: #94a3b8;
    border-bottom-color: #334155;
}
body.dark-mode .history-page .history-table td {
    color: #e2e8f0;
    border-bottom-color: #334155;
}
body.dark-mode .history-page .history-table tr:hover {
    background: #1f2937;
}
body.dark-mode .history-page .facility-cell {
    color: #93c5fd;
}
body.dark-mode .history-page .status-pill.pending {
    background: rgba(146, 64, 14, 0.3);
    color: #fde68a;
    border-color: rgba(251, 191, 36, 0.35);
}
body.dark-mode .history-page .status-pill.ongoing {
    background: rgba(14, 116, 144, 0.25);
    color: #67e8f9;
    border-color: rgba(125, 211, 252, 0.35);
}
body.dark-mode .history-page .status-pill.completed {
    background: rgba(22, 101, 52, 0.25);
    color: #86efac;
    border-color: rgba(74, 222, 128, 0.3);
}
body.dark-mode .history-page .empty-cell {
    color: #94a3b8;
}

@media (max-width: 980px) {
    .metrics-grid {
        grid-template-columns: repeat(2, minmax(130px, 1fr));
    }
    .history-filters {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 680px) {
    .history-filters {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
