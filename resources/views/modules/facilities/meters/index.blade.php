@extends('layouts.qc-admin')
@section('title', 'Facility Meters')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'meter_type' => '', 'status' => ''];
@endphp
<style>
    .meter-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10040;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .meter-modal-card {
        width: min(520px, 95vw);
        max-height: calc(100vh - 32px);
        overflow: auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 24px 50px rgba(15,23,42,.28);
        padding: 22px 22px 16px;
        position: relative;
    }

    .meter-modal-card.compact {
        width: min(520px, 95vw);
    }

    .meter-modal-close {
        position: absolute;
        top: 10px;
        right: 12px;
        width: 34px;
        height: 34px;
        border: none;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        font-size: 1.35rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .meter-modal-title {
        margin: 0;
        color: #2563eb;
        font-weight: 900;
        font-size: 1.75rem;
        line-height: 1.1;
    }

    .meter-modal-title.danger {
        color: #e11d48;
        font-size: 1.45rem;
    }

    .meter-modal-subtitle {
        margin: 6px 0 14px;
        color: #64748b;
        font-size: 1rem;
        font-weight: 600;
    }

    .meter-manage-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 16px;
    }

    .meter-form-field {
        min-width: 0;
    }

    .meter-form-field.full {
        grid-column: 1 / -1;
    }

    .meter-form-label {
        display: block;
        font-weight: 800;
        font-size: .96rem;
        color: #334155;
        margin-bottom: 6px;
    }

    .meter-required {
        color: #e11d48;
    }

    .meter-form-control {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 14px;
        color: #1e293b;
        background: #fff;
        font-size: 1rem;
        transition: border-color .16s ease, box-shadow .16s ease;
    }

    .meter-form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.16);
    }

    .meter-form-textarea {
        min-height: 94px;
        resize: vertical;
    }

    .meter-form-hint {
        margin-top: 6px;
        font-size: .82rem;
        color: #64748b;
        font-weight: 600;
        line-height: 1.35;
    }

    .meter-form-hint-warning {
        color: #9a3412;
    }

    .meter-form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 6px;
    }

    .meter-form-btn {
        border: none;
        border-radius: 12px;
        padding: 11px 18px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
    }

    .meter-form-btn.cancel {
        background: #e2e8f0;
        color: #334155;
    }

    .meter-form-btn.save {
        background: #2563eb;
        color: #fff;
        min-width: 148px;
    }

    .meter-form-btn.danger {
        background: #e11d48;
        color: #fff;
        min-width: 168px;
    }

    .meter-form-btn.danger:hover {
        background: #be123c;
    }

    .meter-archive-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .meter-archive-label {
        color: #334155;
        font-weight: 700;
        line-height: 1.4;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }

    @media (max-width: 900px) {
        .meter-manage-form {
            grid-template-columns: 1fr;
            gap: 11px;
        }

        .meter-form-field.full {
            grid-column: auto;
        }

        .meter-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .meter-form-btn {
            width: 100%;
        }
    }
</style>
<div style="padding:12px;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div style="margin-bottom:12px;background:#fff7ed;color:#9a3412;padding:12px 16px;border-radius:10px;font-weight:700;">
            Please check the meter form fields and try again.
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
        <div>
            <h2 style="margin:0;color:#2563eb;font-weight:800;">Meters Management</h2>
            <div style="color:#64748b;margin-top:4px;">Facility: <strong style="color:#1e293b;">{{ $facility->name }}</strong> (Main + Sub-meters)</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.facilities.show', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#1e293b;padding:10px 14px;border-radius:10px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Back to Facility
            </a>
            <a href="{{ route('modules.facilities.meters.archive', $facility->id) }}" style="text-decoration:none;background:#fff;color:#1e293b;padding:10px 14px;border-radius:10px;font-weight:700;border:1px solid #cbd5e1;">
                <i class="fa fa-box-archive"></i> Meter Archive
                @if(($archivedCount ?? 0) > 0)
                    <span style="margin-left:6px;background:#e11d48;color:#fff;border-radius:999px;padding:2px 7px;font-size:.8rem;">{{ $archivedCount }}</span>
                @endif
            </a>
            @if($canManageMeters)
                <button type="button" onclick="openAddMeterModal()" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">
                    <i class="fa fa-plus"></i> Add Meter
                </button>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:14px;">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;color:#64748b;font-weight:700;">ACTIVE METERS</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;">{{ $activeCount ?? 0 }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;color:#64748b;font-weight:700;">MAIN METERS</div>
            <div style="font-size:1.45rem;font-weight:800;color:#2563eb;">{{ $mainCount ?? 0 }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;color:#64748b;font-weight:700;">SUB-METERS</div>
            <div style="font-size:1.45rem;font-weight:800;color:#9333ea;">{{ $subCount ?? 0 }}</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px;">
            <div style="font-size:.8rem;color:#64748b;font-weight:700;">ARCHIVED</div>
            <div style="font-size:1.45rem;font-weight:800;color:#e11d48;">{{ $archivedCount ?? 0 }}</div>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(15,23,42,0.06);overflow:hidden;">
        <form method="GET" action="{{ route('modules.facilities.meters.index', $facility->id) }}" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;gap:10px;flex-wrap:wrap;align-items:end;background:#fcfdff;">
            <div style="display:flex;flex-direction:column;gap:5px;min-width:240px;flex:1;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Meter name/number/location/notes" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;min-width:150px;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Type</label>
                <select name="meter_type" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All</option>
                    <option value="main" @selected(($filters['meter_type'] ?? '') === 'main')>Main</option>
                    <option value="sub" @selected(($filters['meter_type'] ?? '') === 'sub')>Sub</option>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;min-width:150px;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Status</label>
                <select name="status" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Filter</button>
                <a href="{{ route('modules.facilities.meters.index', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        @if($meters->count() === 0)
            <div style="padding:22px 16px;color:#64748b;">No meters found for this facility yet. Add your main meter first, then add sub-meters.</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;min-width:1220px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;color:#334155;">
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Meter</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Number</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Type</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Parent</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Location</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Status</th>
                            <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Multiplier</th>
                            <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Baseline kWh</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Notes</th>
                            <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meters as $meter)
                            @php
                                $typeColor = $meter->meter_type === 'main' ? '#2563eb' : '#7c3aed';
                                $typeBg = $meter->meter_type === 'main' ? '#eff6ff' : '#f3e8ff';
                            @endphp
                            <tr>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">
                                    {{ $meter->meter_name }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $meter->meter_number ?: '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;">
                                    <span style="display:inline-flex;padding:4px 10px;border-radius:999px;font-size:.8rem;font-weight:800;background:{{ $typeBg }};color:{{ $typeColor }};">
                                        {{ strtoupper($meter->meter_type) }}
                                    </span>
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $meter->parentMeter?->meter_name ?: '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $meter->location ?: '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ ucfirst($meter->status) }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#475569;">
                                    {{ number_format((float) ($meter->multiplier ?? 1), 4) }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#475569;font-weight:600;">
                                    {{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;max-width:220px;">
                                    <span title="{{ $meter->notes ?: '' }}">{{ \Illuminate\Support\Str::limit($meter->notes ?: '-', 45) }}</span>
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                    @if($canManageMeters)
                                        @php
                                            $editMeterPayload = [
                                                'id' => $meter->id,
                                                'meter_name' => $meter->meter_name,
                                                'meter_number' => $meter->meter_number,
                                                'meter_type' => $meter->meter_type,
                                                'parent_meter_id' => $meter->parent_meter_id,
                                                'location' => $meter->location,
                                                'status' => $meter->status,
                                                'multiplier' => $meter->multiplier,
                                                'baseline_kwh' => $meter->baseline_kwh,
                                                'notes' => $meter->notes,
                                            ];
                                        @endphp
                                        <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:center;">
                                            <button type="button"
                                                onclick='openEditMeterModal(@js($editMeterPayload))'
                                                style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:8px;padding:7px 10px;font-weight:700;">
                                                Edit
                                            </button>
                                            <button type="button"
                                                onclick="openArchiveMeterModal({{ $meter->id }}, @js($meter->meter_name))"
                                                style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;border-radius:8px;padding:7px 10px;font-weight:700;">
                                                Archive
                                            </button>
                                        </div>
                                    @else
                                        <span style="color:#94a3b8;">View only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($meters->hasPages())
                <div style="padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-top:1px solid #e5e7eb;background:#fcfdff;">
                    <div style="color:#64748b;">Showing {{ $meters->firstItem() }} to {{ $meters->lastItem() }} of {{ $meters->total() }} meters</div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        @if($meters->onFirstPage())
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Previous</span>
                        @else
                            <a href="{{ $meters->previousPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Previous</a>
                        @endif
                        <span style="padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;">Page {{ $meters->currentPage() }} / {{ $meters->lastPage() }}</span>
                        @if($meters->hasMorePages())
                            <a href="{{ $meters->nextPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Next</a>
                        @else
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@if($canManageMeters)
<div id="addMeterModal" class="meter-modal-overlay">
    <div class="meter-modal-card">
        <button type="button" onclick="closeAddMeterModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title">Add Meter</h3>
        <p class="meter-modal-subtitle">Create a main meter or link a sub-meter to an approved main meter.</p>
        <form method="POST" action="{{ route('modules.facilities.meters.store', $facility->id) }}" class="meter-manage-form">
            @csrf
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'add', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null])
            <div class="meter-form-actions">
                <button type="button" onclick="closeAddMeterModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save">Save Meter</button>
            </div>
        </form>
    </div>
</div>

<div id="editMeterModal" class="meter-modal-overlay" style="z-index:10041;">
    <div class="meter-modal-card">
        <button type="button" onclick="closeEditMeterModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title">Edit Meter</h3>
        <p class="meter-modal-subtitle">Update meter information and linkage details.</p>
        <form id="editMeterForm" method="POST" action="#" class="meter-manage-form">
            @csrf
            @method('PUT')
            @include('modules.facilities.meters.partials.form-fields', ['mode' => 'edit', 'parentMeterOptions' => $parentMeterOptions, 'meter' => null])
            <div class="meter-form-actions">
                <button type="button" onclick="closeEditMeterModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn save">Update Meter</button>
            </div>
        </form>
    </div>
</div>

<div id="archiveMeterModal" class="meter-modal-overlay" style="z-index:10042;">
    <div class="meter-modal-card compact">
        <button type="button" onclick="closeArchiveMeterModal()" class="meter-modal-close">&times;</button>
        <h3 class="meter-modal-title danger">Delete Meter</h3>
        <p class="meter-modal-subtitle">This meter will be moved to archive and can be restored later.</p>
        <form id="archiveMeterForm" method="POST" action="#" class="meter-archive-body">
            @csrf
            @method('DELETE')
            <div id="archiveMeterLabel" class="meter-archive-label"></div>
            <div>
                <label class="meter-form-label" for="archive_meter_reason">Reason for Delete <span class="meter-required">*</span></label>
                <textarea class="meter-form-control meter-form-textarea" id="archive_meter_reason" name="archive_reason" required maxlength="500" rows="4" placeholder="Example: duplicate meter entry, removed panel, decommissioned"></textarea>
            </div>
            <div class="meter-form-actions">
                <button type="button" onclick="closeArchiveMeterModal()" class="meter-form-btn cancel">Cancel</button>
                <button type="submit" class="meter-form-btn danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
function openAddMeterModal(){ var m=document.getElementById('addMeterModal'); if(m) m.style.display='flex'; }
function closeAddMeterModal(){ var m=document.getElementById('addMeterModal'); if(m) m.style.display='none'; }
function closeEditMeterModal(){ var m=document.getElementById('editMeterModal'); if(m) m.style.display='none'; }
function closeArchiveMeterModal(){ var m=document.getElementById('archiveMeterModal'); if(m) m.style.display='none'; }

function toggleParentSelect(prefix) {
    var typeEl = document.getElementById(prefix + '_meter_type');
    var parentEl = document.getElementById(prefix + '_parent_meter_id');
    if (!typeEl || !parentEl) return;
    if (typeEl.value === 'main') {
        parentEl.value = '';
        parentEl.disabled = true;
    } else {
        parentEl.disabled = false;
    }
}

document.getElementById('add_meter_type')?.addEventListener('change', function(){ toggleParentSelect('add'); });
document.getElementById('edit_meter_type')?.addEventListener('change', function(){ toggleParentSelect('edit'); });
toggleParentSelect('add');
toggleParentSelect('edit');

function openEditMeterModal(meter) {
    var modal = document.getElementById('editMeterModal');
    var form = document.getElementById('editMeterForm');
    if (!modal || !form || !meter) return;
    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + meter.id;
    document.getElementById('edit_meter_name').value = meter.meter_name ?? '';
    document.getElementById('edit_meter_number').value = meter.meter_number ?? '';
    document.getElementById('edit_meter_type').value = meter.meter_type ?? 'sub';
    document.getElementById('edit_parent_meter_id').value = meter.parent_meter_id ?? '';
    document.getElementById('edit_location').value = meter.location ?? '';
    document.getElementById('edit_status').value = meter.status ?? 'active';
    document.getElementById('edit_multiplier').value = meter.multiplier ?? '1';
    document.getElementById('edit_baseline_kwh').value = meter.baseline_kwh ?? '';
    document.getElementById('edit_notes').value = meter.notes ?? '';

    var parentSel = document.getElementById('edit_parent_meter_id');
    if (parentSel) {
        Array.from(parentSel.options).forEach(function(opt) {
            opt.disabled = (opt.value !== '' && String(opt.value) === String(meter.id));
        });
    }
    toggleParentSelect('edit');
    modal.style.display = 'flex';
}

function openArchiveMeterModal(meterId, meterName) {
    var modal = document.getElementById('archiveMeterModal');
    var form = document.getElementById('archiveMeterForm');
    var label = document.getElementById('archiveMeterLabel');
    var reason = document.getElementById('archive_meter_reason');
    if (!modal || !form) return;
    form.action = "{{ url('/modules/facilities/' . $facility->id . '/meters') }}/" + meterId;
    if (label) label.textContent = "Meter: " + (meterName || '');
    if (reason) reason.value = '';
    modal.style.display = 'flex';
}
</script>
@endsection
