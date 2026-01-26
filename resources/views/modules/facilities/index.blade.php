@extends('layouts.qc-admin')
@section('title', 'Facilities')
@section('content')

@php
    $userRole = strtolower(auth()->user()->role ?? '');
@endphp

<div class="facilities-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="font-size:2rem;font-weight:700;color:#222;margin:0;">Facilities</h2>
    <div style="display:flex; gap:12px;">
        @if($userRole !== 'staff')
            <button type="button" id="btnAddFacility" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10);">+ Add Facility</button>
            {{-- <a href="#" class="btn-export-report" style="background:#22c55e;color:#fff;padding:10px 24px;border-radius:8px;font-weight:500;text-decoration:none;">Export COA Report</a> --}}
        @endif
    </div>
</div>

<!-- Facility Summary Cards -->
<div class="facility-summary-cards" style="display:flex;gap:24px;margin-bottom:2.2rem;flex-wrap:wrap;">
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">🏢 Total Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $totalFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">🟢 Active Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $activeFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#f59e42;">🛠 Maintenance</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $maintenanceFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
        <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">🚫 Inactive Facilities</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;">{{ $inactiveFacilities ?? '-' }}</div>
    </div>
</div>

<!-- Facilities List -->
<div class="facilities-list" style="display:flex;flex-wrap:wrap;gap:28px;">
    @forelse($facilities as $facility)
        <div class="facility-card" style="background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,0.08);padding:22px 18px;width:320px;display:flex;flex-direction:column;align-items:center;cursor:pointer;position:relative;">
            @php
                $imageUrl = $facility->image ? asset('storage/'.$facility->image) : null;
            @endphp
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="Facility" style="width:100%;height:140px;object-fit:cover;border-radius:10px;margin-bottom:18px;">
            @else
                <div style="width:100%;height:140px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2.2rem;margin-bottom:18px;">
                    <i class="fa fa-image" title="No Image"></i>
                </div>
            @endif
            <h3 style="font-size:1.25rem;font-weight:600;margin-bottom:8px;color:#222;">{{ $facility->name ?? '-' }}</h3>
            <div style="color:#6366f1;font-weight:500;margin-bottom:6px;">{{ $facility->type ?? '-' }}</div>
            <div style="font-size:0.97rem;color:#2563eb;font-weight:500;margin-bottom:6px;text-transform:capitalize;">Size: {{ $facility->dynamicSize ?? $facility->size ?? '-' }}</div>
            <div style="font-size:0.98rem;color:#555;margin-bottom:10px;">{{ $facility->address ?? '-' }}</div>
            <div class="facility-icons" style="display:flex;gap:10px;align-items:center;z-index:2;">
                <a href="{{ url('/modules/facilities/' . $facility->id . '/energy-profile') }}" class="facility-icon-link" style="color:#f59e42;font-size:1.25rem;" title="View Energy Profile" onclick="event.stopPropagation();"><i class="fa fa-bolt"></i></a>
                <a href="{{ route('energy.records') }}?facility_id={{ $facility->id }}" class="facility-icon-link" style="color:#2563eb;font-size:1.25rem;" title="View Monthly Records" onclick="event.stopPropagation();"><i class="fa fa-calendar-alt"></i></a>
                <button type="button" class="btn-show-3mo-avg facility-icon-link" data-facility-id="{{ $facility->id }}" style="background:none;color:#6366f1;font-size:1.25rem;padding:0 6px;border:none;cursor:pointer;" title="Show 3-Month Avg kWh" onclick="event.stopPropagation();">
                    <i class="fa fa-chart-line"></i>
                </button>
                @if($userRole !== 'staff')
                    <a href="#" class="action-btn-edit facility-icon-link" data-facility='@json($facility)' style="color:#6366f1;font-size:1.2rem;" title="Edit Facility" onclick="event.stopPropagation();"><i class="fa fa-pen"></i></a>
                    {{-- <button type="button" class="action-btn-delete facility-icon-link" title="Delete Facility" onclick="event.stopPropagation();openDeleteFacilityModal({{ $facility->id }})" style="background:none;border:none;color:#e11d48;font-size:1.2rem;"><i class="fa fa-trash"></i></button> --}}
                    @if($userRole === 'engineer' || $userRole === 'super admin')
                        <button onclick="event.stopPropagation();toggleEngineerApproval({{ $facility->id }})" class="facility-icon-link" style="color:#22c55e;font-size:1.2rem;background:none;border:none;" title="Engineer Approval"><i class="fa fa-check-circle"></i></button>
                    @endif
                @endif
            </div>
            <a href="{{ route('modules.facilities.show', $facility->id) }}" class="facility-card-link" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;"></a>
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
    @empty
        <div style="width:100%;text-align:center;color:#94a3b8;font-size:1.1rem;padding:32px 0;">No facilities found.</div>
    @endforelse
</div>


<!-- Modal for 3-Month Average kWh (rendered only once, outside the loop) -->
<div id="modal3MoAvg" class="modal" style="display:none;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 28px;border-radius:16px;min-width:320px;box-shadow:0 8px 32px rgba(31,38,135,0.13);position:relative;">
        <button class="modal-close" onclick="document.getElementById('modal3MoAvg').style.display='none'" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#6366f1;cursor:pointer;">&times;</button>
        <h3 style="font-size:1.3rem;font-weight:700;color:#2563eb;margin-bottom:18px;">3-Month Average kWh</h3>
        <div id="avg3MoContent" style="font-size:1.1rem;color:#222;"></div>
        <div id="add3MoBtnContainer" style="margin-top:22px;text-align:right;">
            <a href="#" class="btn btn-primary btn-add-3mo-data" style="background:#2563eb;color:#fff;padding:8px 22px;border-radius:7px;font-weight:600;font-size:1rem;text-decoration:none;">+ Add First 3 Months Data</a>
        </div>
        <div id="show3MoBtnContainer" style="margin-top:12px;text-align:right;display:none;">
            <button type="button" class="btn btn-secondary btn-show-3mo-data" style="background:#6366f1;color:#fff;padding:7px 18px;border-radius:7px;font-weight:600;font-size:0.98rem;border:none;">Show 3-Months Data</button>
        </div>
        <div id="threeMoDataTable" style="margin-top:18px;display:none;"></div>
    </div>
</div>

<!-- Modals -->
@include('modules.facilities.partials.modals') {{-- make sure this includes add/edit/reset modals --}}

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
// Make only the card or the icon clickable, not both
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.facility-card-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only trigger if not clicking on an icon
            // (Icons already have stopPropagation on click)
        });
    });
    const addModal = document.getElementById('addFacilityModal');
    const editModal = document.getElementById('editFacilityModal');
    const resetModal = document.getElementById('resetBaselineModal');

    // Add First 3 Months Data button in modal: pass facility_id in query
    document.querySelector('.btn-add-3mo-data')?.addEventListener('click', function(e) {
        e.preventDefault();
        // Find the last opened facility id (from last modal trigger)
        const lastFacilityId = window.last3MoFacilityId || null;
        let url = "{{ route('facilities.first3months.create') }}";
        if (lastFacilityId) url += `?facility_id=${lastFacilityId}`;
        window.location.href = url;
    });

    // Auto-open 3-Month Avg modal if show3mo param is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    const show3mo = urlParams.get('show3mo');
    if (show3mo) {
        // Find the button for this facility and trigger click
        const btn = document.querySelector(`.btn-show-3mo-avg[data-facility-id='${show3mo}']`);
        if (btn) {
            setTimeout(() => btn.click(), 400); // slight delay to ensure DOM is ready
        }
    }

    // 3-Month Avg Icon Button Logic (global, not per facility)
    document.querySelectorAll('.btn-show-3mo-avg').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const facilityId = this.dataset.facilityId;
            window.last3MoFacilityId = facilityId;
            fetch(`/api/facilities/${facilityId}/3mo-avg`)
                .then(res => res.json())
                .then(data => {
                    // Show average or not enough data
                    document.getElementById('avg3MoContent').textContent = data.avg_kwh !== null ? `First 3 months average: ${data.avg_kwh} kWh` : 'Not enough data (need 3 months).';
                    // Show/hide Add button if data exists
                    const addBtnContainer = document.getElementById('add3MoBtnContainer');
                    const showBtnContainer = document.getElementById('show3MoBtnContainer');
                    const dataTable = document.getElementById('threeMoDataTable');
                    if (data.avg_kwh !== null) {
                        addBtnContainer.style.display = 'none';
                        showBtnContainer.style.display = 'block';
                    } else {
                        addBtnContainer.style.display = 'block';
                        showBtnContainer.style.display = 'none';
                        dataTable.style.display = 'none';
                    }
                    dataTable.style.display = 'none';
                    document.getElementById('modal3MoAvg').style.display = 'flex';
                });
            // Show 3-Months Data button logic
            document.querySelector('.btn-show-3mo-data')?.addEventListener('click', function() {
                const facilityId = window.last3MoFacilityId;
                fetch(`/api/facilities/${facilityId}/first3months-data`)
                    .then(res => res.json())
                    .then(data => {
                        const dataTable = document.getElementById('threeMoDataTable');
                        if (data && data.month1 !== undefined) {
                            dataTable.innerHTML = `
                                <table style='width:100%;border-collapse:collapse;margin-bottom:10px;'>
                                    <thead>
                                        <tr style='background:#f1f5f9;'>
                                            <th style='padding:8px 0;'>Month</th>
                                            <th style='padding:8px 0;'>kWh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style='text-align:center;font-weight:600;'>Month 1</td><td style='text-align:center;'>${data.month1}</td></tr>
                                        <tr><td style='text-align:center;font-weight:600;'>Month 2</td><td style='text-align:center;'>${data.month2}</td></tr>
                                        <tr><td style='text-align:center;font-weight:600;'>Month 3</td><td style='text-align:center;'>${data.month3}</td></tr>
                                        <tr><td colspan='2' style='text-align:center;padding-top:10px;'>
                                            <button class='btn-delete-3mo-data' style='background:#e11d48;color:#fff;padding:4px 18px;border-radius:5px;border:none;font-size:1rem;'>Delete</button>
                                        </td></tr>
                                    <!-- Modal for Delete 3-Months Data Confirmation -->
                                    <div id="modalDelete3MoData" class="modal" style="display:none;align-items:center;justify-content:center;z-index:10000;">
                                        <div style="background:#fff;padding:32px 28px;border-radius:16px;min-width:320px;box-shadow:0 8px 32px rgba(225,29,72,0.13);position:relative;max-width:90vw;">
                                            <button class="modal-close" onclick="document.getElementById('modalDelete3MoData').style.display='none'" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#e11d48;cursor:pointer;">&times;</button>
                                            <h3 style="font-size:1.2rem;font-weight:700;color:#e11d48;margin-bottom:18px;">Delete 3-Months Data</h3>
                                            <div style="font-size:1.05rem;color:#222;margin-bottom:18px;">Are you sure you want to delete the 3-months data for this facility? This action cannot be undone.</div>
                                            <div style="display:flex;gap:16px;justify-content:flex-end;">
                                                <button id="btnCancelDelete3MoData" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:8px 22px;">Cancel</button>
                                                <button id="btnConfirmDelete3MoData" style="background:#e11d48;color:#fff;font-weight:600;border:none;border-radius:8px;padding:8px 22px;">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                    </tbody>
                                </table>
                            `;
                            dataTable.style.display = 'block';
                        } else {
                            dataTable.innerHTML = '<div style="color:#e11d48;text-align:center;">No 3-months data found.</div>';
                            dataTable.style.display = 'block';
                        }
                        // Attach delete handler with custom modal
                        const delBtn = dataTable.querySelector('.btn-delete-3mo-data');
                        if (delBtn) {
                            delBtn.onclick = function() {
                                window._delete3MoFacilityId = facilityId;
                                document.getElementById('modalDelete3MoData').style.display = 'flex';
                            }
                        }
                    // Delete 3-Months Data Modal logic
                    document.getElementById('btnCancelDelete3MoData')?.addEventListener('click', function() {
                        document.getElementById('modalDelete3MoData').style.display = 'none';
                    });
                    document.getElementById('btnConfirmDelete3MoData')?.addEventListener('click', function() {
                        const facilityId = window._delete3MoFacilityId;
                        if (!facilityId) return;
                        fetch(`/api/facilities/${facilityId}/first3months-data`, {
                            method: 'DELETE',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                        .then(res => res.json())
                        .then(resp => {
                            if (resp.success) {
                                document.getElementById('modalDelete3MoData').style.display = 'none';
                                document.getElementById('modal3MoAvg').style.display = 'none';
                                location.reload();
                            } else {
                                alert('Failed to delete 3-months data.');
                            }
                        });
                    });
                    });
            });
        });
    });

    // Open Add
    document.getElementById('btnAddFacility')?.addEventListener('click',()=>addModal.style.display='flex');

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

function openEditFacilityModal(f){
    const m=document.getElementById('editFacilityModal'); m.style.display='flex';
    document.getElementById('edit_facility_id').value=f.id||'';
    document.getElementById('edit_name').value=f.name||'';
    document.getElementById('edit_type').value=f.type||'';
    document.getElementById('edit_department').value=f.department||'';
    document.getElementById('edit_address').value=f.address||'';
    document.getElementById('edit_barangay').value=f.barangay||'';
    document.getElementById('edit_floor_area').value=f.floor_area||'';
    document.getElementById('edit_floors').value=f.floors||'';
    document.getElementById('edit_year_built').value=f.year_built||'';
    document.getElementById('edit_operating_hours').value=f.operating_hours||'';
    document.getElementById('edit_status').value=f.status||'';
    const preview=document.getElementById('edit_image_preview');
    preview.innerHTML=f.image ? `<img src='/storage/${f.image}' style='width:100%;max-width:180px;border-radius:8px;'>` : '';
    document.getElementById('editFacilityForm').action=`/modules/facilities/${f.id}`;
}

// Reset baseline
function openResetBaselineModal(id){ document.getElementById('reset_facility_id').value=id; document.getElementById('resetBaselineModal').style.display='flex'; }
document.getElementById('resetBaselineForm')?.addEventListener('submit',function(e){
    e.preventDefault();
    const id=document.getElementById('reset_facility_id').value;
    const reason=document.getElementById('reset_reason').value;
    fetch(`/modules/facilities/${id}/reset-baseline`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({reason})
    }).then(res=>res.json()).then(data=>{ alert(data.message||'Baseline reset!'); document.getElementById('resetBaselineModal').style.display='none'; location.reload(); });
});

// Engineer approval
function toggleEngineerApproval(id){
    fetch(`/modules/facilities/${id}/toggle-engineer-approval`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

@endsection
