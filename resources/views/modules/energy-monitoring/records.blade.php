@php
    if (!isset($userRole)) {
        $userRole = strtolower(auth()->user()->role ?? '');
    }
    if (!isset($months)) {
        $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    }
@endphp
@extends('layouts.qc-admin')
@section('title', 'Monthly Records')
@section('content')


{{-- Assume $userRole, $months, $facilities, $monthlyRecords are passed from controller --}}

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Monthly Records</h2>
    <div style="display:flex;gap:12px;align-items:center;">
        @if($userRole !== 'staff')
            @php $hasDuplicate = session('errors') && session('errors')->has('duplicate'); @endphp
            <button id="btnAddMonthlyRecord"
                class="btn-add-monthly-record"
                style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:10px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10); @if($hasDuplicate) opacity:0.5; pointer-events:none; @endif"
                @if($hasDuplicate) disabled @endif>
                + Add Monthly Record
            </button>
        @endif
    </div>
</div>

@if($hasDuplicate)
<div id="duplicateErrorPopup"
    style="position:fixed;top:32px;left:50%;transform:translateX(-50%);background:#fee2e2;color:#b91c1c;padding:14px 28px;border-radius:10px;font-weight:700;z-index:99999;box-shadow:0 2px 12px rgba(225,29,72,0.13);font-size:1.08rem;">
    {{ session('errors')->first('duplicate') }}
</div>
<script>
    setTimeout(() => {
        const popup = document.getElementById('duplicateErrorPopup');
        if(popup) popup.style.display = 'none';
        const btn = document.getElementById('btnAddMonthlyRecord');
        if(btn) { btn.disabled = false; btn.style.opacity = 1; btn.style.pointerEvents = 'auto'; }
    }, 2000);
</script>
@endif

{{-- Filters --}}

@php
    $selectedFacility = null;
    if (isset($facilities) && count($facilities) > 0) {
        $selectedId = request('facility_id');
        if ($selectedId) {
            foreach ($facilities as $f) {
                if ($f->id == $selectedId) {
                    $selectedFacility = $f;
                    break;
                }
            }
        }
        if (!$selectedFacility) {
            $selectedFacility = $facilities[0];
        }
    }
@endphp

@if($selectedFacility)
    <div style="margin-bottom:1.2rem;">
        <strong>Facility:</strong> {{ $selectedFacility->name }}
    </div>
@endif

<div style="display:flex; gap:14px; align-items:center; margin-bottom:1.5rem;"></div>

<div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
    <table style="width:100%;border-collapse:collapse;min-width:900px;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:left;">
                <th style="padding:12px 10px;">Month</th>
                <th style="padding:12px 10px;">Year</th>
                <th style="padding:12px 10px;">Facility</th>
                <th style="padding:12px 10px;">kWh Consumed</th>
                <th style="padding:12px 10px;">Average kWh</th>
                <th style="padding:12px 10px;">Variance</th>
                <th style="padding:12px 10px;">Deviation %</th>
                <th style="padding:12px 10px;">Alert Level</th>
                <th style="padding:12px 10px;">Alert Message</th>
                @if($userRole !== 'staff') <th style="padding:12px 10px;">Actions</th> @endif
            </tr>
        </thead>
        <tbody>
        @forelse($monthlyRecords as $record)
            <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px 14px;">{{ $months[$record->month] ?? $record->month }}</td>
                <td style="padding:10px 14px;">{{ $record->year }}</td>
                <td style="padding:10px 14px;">{{ $record->facility->name ?? '-' }}</td>
                <td style="padding:10px 14px;">{{ number_format($record->actual_kwh,2) }} kWh</td>
                <td style="padding:10px 14px;">{{ number_format($record->average_monthly_kwh,2) }} kWh</td>
                <td style="padding:10px 14px;">{{ number_format($record->actual_kwh - $record->average_monthly_kwh,2) }}</td>
                <td style="padding:10px 14px;">
                    @if($record->average_monthly_kwh && $record->average_monthly_kwh != 0)
                        {{ round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2) }}%
                    @else
                        -
                    @endif
                </td>
                <td style="padding:10px 14px; color:
                    @if($record->average_monthly_kwh && $record->average_monthly_kwh != 0)
                        @php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        @endphp
                        {{ $deviation <= 10 && $deviation >= -10 ? '#16a34a' : ($deviation > 20 ? '#e11d48' : '#f59e42') }}
                    @else
                        #222
                    @endif
                    ; font-weight:600;">
                    @if($record->average_monthly_kwh && $record->average_monthly_kwh != 0)
                        @php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        @endphp
                        {{ $deviation <= 10 && $deviation >= -10 ? 'Normal' : ($deviation > 20 ? 'Critical' : 'Warning') }}
                    @else
                        -
                    @endif
                </td>
                <td style="padding:10px 14px;">
                    @if($record->average_monthly_kwh && $record->average_monthly_kwh != 0)
                        @php
                            $deviation = round((($record->actual_kwh - $record->average_monthly_kwh) / $record->average_monthly_kwh) * 100, 2);
                        @endphp
                        @if($deviation <= 10 && $deviation >= -10)
                            Energy usage is within baseline limits.
                        @elseif($deviation < 0)
                            Energy usage below baseline by {{ abs(number_format($deviation, 2)) }}%
                        @else
                            Energy usage exceeded baseline by {{ number_format($deviation, 2) }}%
                        @endif
                    @else
                        No baseline data yet.
                    @endif
                </td>
                @if($userRole !== 'staff')
                <td style="padding:10px 14px;display:flex;gap:6px;">
                    <button onclick="openResetModal({{ $record->id }})" style="background:#f1c40f;color:#222;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Reset Baseline"><i class="fa fa-repeat"></i></button>
                    <button onclick="toggleApproval({{ $record->id }})" style="background:#22c55e;color:#fff;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Toggle Approval"><i class="fa fa-check-circle"></i></button>
                    <button onclick="openDeleteMonthlyRecordModal({{ $record->id }})" style="background:#e11d48;color:#fff;border:none;border-radius:6px;padding:6px 12px;cursor:pointer;" title="Delete"><i class="fa fa-trash"></i></button>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="{{ $userRole !== 'staff' ? 11 : 10 }}" style="padding:18px;text-align:center;color:#888;">No monthly records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>


@include('modules.energy-monitoring.partials.modals')

<script>
function openResetModal(recordId){
    document.getElementById('reset_record_id').value = recordId;
    document.getElementById('resetBaselineModal').classList.add('show-modal');
}

function toggleApproval(recordId){
    fetch(`/modules/energy-monitoring/monthly-records/${recordId}/toggle-approval`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
    }).then(res=>res.json()).then(data=>{
        alert(data.message || 'Approval toggled!');
        location.reload();
    });
}
</script>

@endsection
<script>
// Ensure average_monthly_kwh is always submitted
window.addEventListener('DOMContentLoaded', function() {
    var addForm = document.getElementById('addMonthlyRecordForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            var avgField = addForm.querySelector('[name="average_monthly_kwh"]');
            if (!avgField) {
                avgField = document.createElement('input');
                avgField.type = 'hidden';
                avgField.name = 'average_monthly_kwh';
                avgField.value = '0';
                addForm.appendChild(avgField);
            } else if (avgField.value === '' || avgField.value == null) {
                avgField.value = '0';
            }
        });
    }
});
</script>
