@extends('layouts.qc-admin')
@section('title', 'Energy')

@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@section('content')

        <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:18px;">
            <h3 style="font-size:1.35rem;font-weight:700;color:#2563eb;margin:0;letter-spacing:0.5px;">Incident Records</h3>
            <div style="display:flex;gap:14px;">
                <a href="{{ route('energy-incidents.create') }}" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:10px 28px;font-weight:600;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.1);font-size:1.05rem;transition:0.2s;text-decoration:none;">+ Log Incident</a>
                <a href="{{ route('energy-incidents.history') }}" style="background:linear-gradient(90deg,#6366f1,#2563eb);color:#fff;padding:10px 28px;font-weight:600;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.13);font-size:1.05rem;transition:0.2s;text-decoration:none;display:flex;align-items:center;gap:8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    View History
                </a>
            </div>
        </div>
        <div class="incident-list-container">
        @forelse($incidents as $incident)
            @php
                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $monthNum = isset($incident->month) && $incident->month ? (int) $incident->month : (isset($incident->energyRecord) && $incident->energyRecord->month ? (int) $incident->energyRecord->month : null);
                $yearNum = isset($incident->year) && $incident->year ? $incident->year : (isset($incident->energyRecord) && $incident->energyRecord->year ? $incident->energyRecord->year : null);
                $monthLabel = $monthNum && $monthNum >= 1 && $monthNum <= 12 ? $months[$monthNum-1] : '-';
                $dpn = isset($incident->deviation_percent) ? $incident->deviation_percent : (isset($incident->energyRecord) && isset($incident->energyRecord->deviation_percent) ? $incident->energyRecord->deviation_percent : null);
                $dateDetected = $incident->date_detected ? \Carbon\Carbon::parse($incident->date_detected)->format('M d, Y') : ($incident->created_at ? $incident->created_at->format('M d, Y') : '-');
            @endphp
            <div class="incident-list-row" tabindex="0" onclick="showIncidentModal({{ $incident->id }})">
                <div class="incident-list-main">
                    <div class="incident-facility">{{ $incident->facility->name ?? '-' }}</div>
                    <div class="incident-detected" style="text-align:right;min-width:120px;">{{ $dateDetected }}</div>
                </div>
            </div>
            <div id="incident-modal-{{ $incident->id }}" class="incident-modal" style="display:none;">
                <div class="incident-modal-content">
                    <h3 style="margin-top:0;margin-bottom:24px;font-size:1.5rem;font-weight:700;">Incident Details</h3>
                    <div style="margin-bottom:20px;"><b>Facility:</b> {{ $incident->facility->name ?? '-' }}</div>
                    <div style="margin-bottom:20px;"><b>Month/Year:</b> {{ $monthLabel }}/{{ $yearNum ?? '-' }}</div>
                    <div style="margin-bottom:20px;"><b>Deviation:</b> {{ $dpn !== null ? number_format($dpn, 2) . '%' : '-' }}</div>
                    <div style="margin-bottom:20px;"><b>Alert Level:</b> {{ $incident->alert_level ?? 'High' }}</div>
                    <div style="margin-bottom:20px;"><b>Status:</b> {{ $incident->status ?? 'High Alert' }}</div>
                    <div style="margin-bottom:20px;"><b>Date Detected:</b> {{ $dateDetected }}</div>
                    <div style="margin-bottom:20px;"><b>Description:</b> {{ $incident->description ?? '-' }}</div>
                    <div style="margin-bottom:20px;"><b>Probable Cause:</b> 
                        @php
                            $defaultProbable = 'System-detected: Abnormal consumption pattern';
                        @endphp
                        {{ (is_array($incident->probable_cause) && count($incident->probable_cause)) ? implode(', ', $incident->probable_cause) : ($incident->probable_cause ?: $defaultProbable) }}
                    </div>
                    <div style="margin-bottom:20px;"><b>Immediate Action:</b> 
                        @php $defaultAction = 'System flagged for review'; @endphp
                        {{ $incident->immediate_action ?: $defaultAction }}
                    </div>
                    <div style="margin-bottom:20px;"><b>Resolution:</b> 
                        @php $defaultResolution = 'Pending manual review and validation.'; @endphp
                        {{ $incident->resolution_summary ?: $defaultResolution }}
                    </div>
                    <div style="margin-bottom:28px;"><b>Preventive Recommendation:</b> 
                        @php $defaultPrev = 'Monitor facility usage and investigate anomalies.'; @endphp
                        {{ $incident->preventive_recommendation ?: $defaultPrev }}
                    </div>
                    @if(!empty($incident->attachments))
                        <div style="margin-bottom:24px;"><b>Attachments:</b> <a href="{{ asset('storage/'.$incident->attachments) }}" target="_blank">View</a></div>
                    @endif
                    <div style="margin-top:36px; text-align:right;">
                        <a href="{{ route('modules.maintenance.index') }}?facility_id={{ $incident->facility->id ?? '' }}" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;padding:10px 28px;font-weight:600;border:none;border-radius:10px;box-shadow:0 2px 8px rgba(31,38,135,0.13);font-size:1.05rem;transition:0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                            View Maintenance
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center; color:#64748b; padding:18px 0;">No incidents found.</div>
        @endforelse
    </div>
</div>
@foreach($incidents as $incident)
    @if($incident->facility)
        <div id="facility-modal-{{ $incident->facility->id }}" class="incident-modal" style="display:none;">
            <div class="incident-modal-content">
                <h3 style="margin-top:0;margin-bottom:24px;font-size:1.3rem;font-weight:700;color:#2563eb;">Facility Details</h3>
                <div style="margin-bottom:18px;"><b>Name:</b> {{ $incident->facility->name ?? '-' }}</div>
                <div style="margin-bottom:18px;"><b>Type:</b> {{ $incident->facility->type ?? '-' }}</div>
                <div style="margin-bottom:18px;"><b>Size:</b> {{ $incident->facility->size ?? '-' }}</div>
                <div style="margin-bottom:18px;"><b>Status:</b> {{ $incident->facility->status ?? '-' }}</div>
                <div style="margin-bottom:18px;"><b>Address:</b> {{ $incident->facility->address ?? '-' }}</div>
            </div>
        </div>
    @endif
@endforeach
<script>
function showIncidentModal(id) {
    document.getElementById('incident-modal-' + id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeIncidentModal(id) {
    document.getElementById('incident-modal-' + id).style.display = 'none';
    document.body.style.overflow = '';
}
function showFacilityModal(id) {
    document.getElementById('facility-modal-' + id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeFacilityModal(id) {
    document.getElementById('facility-modal-' + id).style.display = 'none';
    document.body.style.overflow = '';
}
</script>
<style>
.incident-list-container {
    background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08); padding:0; margin-bottom:32px;
}
.incident-list-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 24px;
    border-bottom:1px solid #e5e7eb;
    cursor:pointer;
    background:#fff;
    border-radius:14px;
    margin:10px 18px;
    box-shadow:0 1px 4px rgba(31,38,135,0.06);
    transition:box-shadow 0.18s, background 0.18s, transform 0.16s;
    position:relative;
}
.incident-list-row:last-child { border-bottom:none; }
.incident-list-row:hover, .incident-list-row:focus {
    background:#f5f8ff;
    box-shadow:0 6px 24px rgba(55,98,200,0.13);
    transform:translateY(-2px) scale(1.012);
    z-index:2;
}
.incident-list-main { display:grid; grid-template-columns:2fr 1.5fr; gap:48px; width:100%; align-items:center; }
.incident-facility { font-weight:600; color:#222; }
.incident-date, .incident-deviation, .incident-status, .incident-detected { color:#334155; font-size:1.01rem; }
.incident-alert.high { color:#e11d48; font-weight:700; }
.incident-alert.medium { color:#f59e42; font-weight:700; }
.incident-alert.low { color:#2563eb; font-weight:700; }
.incident-modal {
    position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(30,41,59,0.18); display:flex; align-items:center; justify-content:center; z-index:1000;
}
.incident-modal-content {
    background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(31,38,135,0.18); padding:36px 24px 28px 24px; min-width:340px; max-width:95vw; max-height:90vh; overflow-y:auto; position:relative;
}
.incident-modal-close {
    position:absolute; top:18px; right:18px; background:none; border:none; font-size:2rem; color:#64748b; cursor:pointer; transition:color 0.15s;
}
.incident-modal-close:hover { color:#e11d48; }
@media (max-width: 900px) {
    .incident-list-main { grid-template-columns:1.5fr 1fr 1fr 1fr 1fr 1.2fr; font-size:0.98rem; }
    .incident-modal-content { padding:18px 8px; }
}
</style>
@endsection
