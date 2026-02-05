@extends('layouts.qc-admin')
@section('title', 'Facilities')
@section('content')

@php
    $userRole = strtolower(auth()->user()->role ?? '');
@endphp

<div class="facilities-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h1 style="font-size:2.2rem;font-weight:700;color:#3762c8;margin:0;">Facilities</h1>
    <div style="display:flex; gap:12px;">
        @if($userRole !== 'staff')
            <button type="button" id="btnAddFacilityTop" style="background: linear-gradient(90deg,#2563eb,#6366f1); color:#fff; font-weight:600; border:none; border-radius:10px; padding:10px 28px; font-size:1.1rem; box-shadow:0 2px 8px rgba(31,38,135,0.10); transition:background 0.18s;">+ Add Facility</button>
            <style>
            #btnAddFacilityTop:hover, #btnAddFacilityTop:focus {
                background:linear-gradient(90deg,#1d4ed8,#6366f1);
            }
            </style>
        @endif

    @if($userRole !== 'staff')
        <!-- Floating Add Facility Button (hidden if top button is visible) -->
        <button type="button" id="fabAddFacility" title="Add Facility" style="position:fixed;bottom:38px;right:38px;z-index:10001;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;border:none;border-radius:50%;width:62px;height:62px;box-shadow:0 4px 18px rgba(37,99,235,0.18);display:flex;align-items:center;justify-content:center;font-size:2.1rem;cursor:pointer;transition:background 0.18s;display:none;">
            <span style="font-size:2.2rem;line-height:1;">+</span>
        </button>
        <style>
        #fabAddFacility:hover, #fabAddFacility:focus {
            background:linear-gradient(90deg,#1d4ed8,#6366f1);
        }
        @media (max-width: 600px) {
            #fabAddFacility { right:16px; bottom:16px; width:52px; height:52px; font-size:1.6rem; }
        }
        </style>
    @endif
    </div>
</div>

<!-- Facility Summary Cards -->
<div class="facility-summary-cards" style="display:flex;gap:24px;margin-bottom:2.2rem;flex-wrap:wrap;">
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
        <div style="font-size:1.13rem;font-weight:700;color:#3762c8;display:flex;align-items:center;gap:6px;">
            <span style="font-size:1.25rem;">🏢</span> Total Facilities
        </div>
        <div style="font-size:2.3rem;font-weight:800;margin:8px 0;color:#222;">{{ $totalFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <div style="font-size:1.13rem;font-weight:700;color:#22c55e;display:flex;align-items:center;gap:6px;">
            <span style="font-size:1.25rem;">🟢</span> Active Facilities
        </div>
        <div style="font-size:2.3rem;font-weight:800;margin:8px 0;color:#222;">{{ $activeFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff7ed;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(234,179,8,0.08);">
        <div style="font-size:1.13rem;font-weight:700;color:#f59e0b;display:flex;align-items:center;gap:6px;">
            <span style="font-size:1.25rem;">🛠</span> Maintenance
        </div>
        <div style="font-size:2.3rem;font-weight:800;margin:8px 0;color:#222;">{{ $maintenanceFacilities ?? '-' }}</div>
    </div>
    <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
        <div style="font-size:1.13rem;font-weight:700;color:#e11d48;display:flex;align-items:center;gap:6px;">
            <span style="font-size:1.25rem;">🚫</span> Inactive Facilities
        </div>
        <div style="font-size:2.3rem;font-weight:800;margin:8px 0;color:#222;">{{ $inactiveFacilities ?? '-' }}</div>
    </div>
</div>

<!-- Facilities List -->
<div class="facilities-list" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:28px;">
    @forelse($facilities as $facility)
        <div class="facility-card" style="background:#fff;border-radius:16px;box-shadow:0 4px 18px rgba(0,0,0,0.08);padding:22px 18px;display:flex;flex-direction:column;align-items:center;cursor:pointer;position:relative;min-width:0;">
            <a href="{{ route('modules.facilities.show', $facility->id) }}" style="display:block;position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;"></a>
            @php
                $imageUrl = null;
                if ($facility->image) {
                    if (strpos($facility->image, 'img/') === 0) {
                        $imageUrl = asset($facility->image);
                    } else {
                        $imageUrl = asset('storage/'.$facility->image);
                    }
                }
            @endphp
            <div style="width:100%;aspect-ratio:4/1;border-radius:18px;margin-bottom:18px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.10);background:linear-gradient(135deg,#f1f5f9,#e2e8f0);display:flex;align-items:center;justify-content:center;">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $facility->name }}" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
                @else
                    <i class="fa fa-image" title="No Image" style="color:#94a3b8;font-size:2.2rem;"></i>
                @endif
            </div>
            <h3 style="font-size:1.25rem;font-weight:600;margin-bottom:8px;color:#222;">{{ $facility->name ?? '-' }}</h3>
            <div style="color:#6366f1;font-weight:500;margin-bottom:6px;">{{ $facility->type ?? '-' }}</div>
            <div style="font-size:0.98rem;color:#555;margin-bottom:10px;">{{ $facility->address ?? '-' }}</div>
            <div class="facility-icons" style="display:flex;gap:10px;align-items:center;z-index:2;">
                <a href="{{ url('/modules/facilities/' . $facility->id . '/energy-profile') }}" class="facility-icon-link" style="color:#f59e42;font-size:1.25rem;" title="View Energy Profile" onclick="event.stopPropagation();"><i class="fa fa-bolt"></i></a>
                <a href="{{ route('facilities.monthly-records', $facility->id) }}" class="facility-icon-link" style="color:#2563eb;font-size:1.25rem;" title="View Monthly Records" onclick="event.stopPropagation();"><i class="fa fa-calendar-alt"></i></a>
                <a href="{{ url('/facilities/first3months?facility_id=' . $facility->id) }}" class="facility-icon-link" style="color:#0ea5e9;font-size:1.25rem;" title="First 3 Months Data" onclick="event.stopPropagation();">
                    <i class="fa fa-calendar-plus"></i>
                </a>
                @if($userRole !== 'staff')
                    @if($userRole === 'engineer' || $userRole === 'super admin')
                        <button onclick="event.stopPropagation();toggleEngineerApproval({{ $facility->id }}, this)" class="facility-icon-link" style="color:{{ $facility->engineer_approved ? '#22c55e' : '#e11d48' }};font-size:1.2rem;background:none;border:none;position:relative;" title="Engineer Approval">
                            @if($facility->engineer_approved)
                                <i class="fa fa-check-circle"></i>
                            @else
                                <i class="fa fa-times-circle"></i>
                            @endif
                            <span class="approval-tooltip" style="display:none;position:absolute;left:50%;top:-32px;transform:translateX(-50%);background:#222;color:#fff;padding:4px 12px;border-radius:6px;font-size:0.95rem;font-weight:500;white-space:nowrap;z-index:10;">
                                {{ $facility->engineer_approved ? 'Approved' : 'Not Approved' }}
                            </span>
                        </button>
                    @endif
                @endif
            </div>
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
<!-- 3-Month Average Modal removed -->

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
document.addEventListener('DOMContentLoaded', function() {
    // Move modal to end of body for stacking
    const modal = document.getElementById('addFacilityModal');
    if (modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
    }
    // Auto-open Add Facility modal if there are validation errors
    @if ($errors->any())
        if (modal) {
            modal.style.display = 'flex';
        }
    @endif
    // Add Facility button logic (top)
    const btnAddFacilityTop = document.getElementById('btnAddFacilityTop');
    if (btnAddFacilityTop) {
        btnAddFacilityTop.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(()=>{
                document.getElementById('add_name')?.focus();
            }, 100);
        });
    }
    // Add Facility button logic (FAB)
    const fabAddFacility = document.getElementById('fabAddFacility');
    if (fabAddFacility) {
        fabAddFacility.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(()=>{
                document.getElementById('add_name')?.focus();
            }, 100);
        });
    }


    // 3-Month Avg Icon Button Logic removed

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
    }).then(res=>res.json()).then(data=>{ showToast(data.message||'Baseline reset!', 'success'); document.getElementById('resetBaselineModal').style.display='none'; setTimeout(()=>location.reload(), 1200); });
});


</script>

<script>
// Engineer approval (global scope)
function toggleEngineerApproval(facilityId, btn) {
    fetch('/modules/facilities/' + facilityId + '/toggle-engineer-approval', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.style.color = data.engineer_approved ? '#22c55e' : '#e11d48';
            btn.querySelector('i').className = data.engineer_approved ? 'fa fa-check-circle' : 'fa fa-times-circle';
            btn.querySelector('.approval-tooltip').textContent = data.engineer_approved ? 'Approved' : 'Not Approved';
            btn.blur();
        } else {
            alert(data.message || 'Action failed.');
        }
    })
    .catch(() => alert('Network error.'));
}
document.querySelectorAll('.facility-icon-link .approval-tooltip').forEach(function(tooltip){
    var parent = tooltip.parentElement;
    parent.addEventListener('mouseenter',function(){
        tooltip.style.display='block';
    });
    parent.addEventListener('mouseleave',function(){
        tooltip.style.display='none';
    });
});
</script>

<!-- Toast Notification -->
<div id="toastContainer" style="position:fixed;top:32px;right:32px;z-index:99999;display:flex;flex-direction:column;gap:12px;"></div>
<script>
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.padding = '14px 28px';
    toast.style.borderRadius = '8px';
    toast.style.fontWeight = '600';
    toast.style.fontSize = '1rem';
    toast.style.boxShadow = '0 2px 12px rgba(55,98,200,0.10)';
    toast.style.color = '#fff';
    toast.style.background = type === 'success' ? 'linear-gradient(90deg,#22c55e,#16a34a)' : (type === 'error' ? 'linear-gradient(90deg,#e11d48,#be123c)' : 'linear-gradient(90deg,#2563eb,#6366f1)');
    toast.style.opacity = '0.98';
    toast.style.transition = 'opacity 0.3s';
    container.appendChild(toast);
    setTimeout(()=>{ toast.style.opacity = '0'; }, 1800);
    setTimeout(()=>{ toast.remove(); }, 2200);
}
</script>

@endsection
