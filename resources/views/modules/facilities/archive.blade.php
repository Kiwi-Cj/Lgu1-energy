@extends('layouts.qc-admin')
@section('title', 'Facilities Archive')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'type' => '', 'status' => '', 'archived_from' => '', 'archived_to' => ''];
    $typeOptions = $typeOptions ?? collect();
    $statusOptions = $statusOptions ?? collect();
    $exportColumnOptions = $exportColumnOptions ?? [
        'facility' => 'Facility',
        'type' => 'Type',
        'status' => 'Status',
        'barangay' => 'Barangay',
        'archive_reason' => 'Archive Reason',
        'deleted_by' => 'Deleted By',
        'archived_at' => 'Archived At',
    ];
    $selectedExportColumns = $selectedExportColumns ?? array_keys($exportColumnOptions);
    $canForceDelete = $canForceDelete ?? false;
@endphp
<div class="facilities-archive-page" style="padding:12px;">
    @if(session('success'))
        <div style="margin-bottom:14px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;font-weight:700;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:14px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <h2 style="margin:0;font-size:1.8rem;font-weight:800;color:#2563eb;">Facilities Archive</h2>
            <p style="margin:4px 0 0;color:#64748b;">Archived facilities keep their related records and can be restored anytime.</p>
        </div>
        <a href="{{ route('modules.facilities.index') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:700;">
            <i class="fa fa-arrow-left"></i> Back to Facilities
        </a>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(31,38,135,0.06);overflow:hidden;">
        <form method="GET" action="{{ route('modules.facilities.archive') }}" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;flex-wrap:wrap;gap:10px;align-items:end;background:#fcfdff;">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:240px;flex:1 1 260px;">
                <label for="archive_q" style="font-size:0.85rem;font-weight:700;color:#475569;">Search</label>
                <input id="archive_q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Facility, address, barangay, type"
                    style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                <label for="archive_type" style="font-size:0.85rem;font-weight:700;color:#475569;">Type</label>
                <select id="archive_type" name="type" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                <label for="archive_status" style="font-size:0.85rem;font-weight:700;color:#475569;">Status</label>
                <select id="archive_status" name="status" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All Status</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst((string) $status) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:170px;">
                <label for="archived_from" style="font-size:0.85rem;font-weight:700;color:#475569;">Archived From</label>
                <input id="archived_from" type="date" name="archived_from" value="{{ $filters['archived_from'] }}"
                    style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;min-width:170px;">
                <label for="archived_to" style="font-size:0.85rem;font-weight:700;color:#475569;">Archived To</label>
                <input id="archived_to" type="date" name="archived_to" value="{{ $filters['archived_to'] }}"
                    style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">
                    Filter
                </button>
                <a href="{{ route('modules.facilities.archive') }}" style="background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;text-decoration:none;">
                    Reset
                </a>
                <button type="submit" name="export" value="csv" style="background:#0f766e;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">
                    Export CSV
                </button>
                <button type="submit" name="export" value="xlsx" style="background:#166534;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">
                    Export Excel
                </button>
            </div>
            <div style="flex:1 1 100%;"></div>
            <details style="width:100%;border:1px solid #dbe3ee;border-radius:12px;background:#fff;padding:10px 12px;">
                <summary style="cursor:pointer;font-weight:700;color:#334155;outline:none;">
                    Export Columns ({{ count($selectedExportColumns) }} selected)
                </summary>
                <div style="margin-top:10px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px 12px;">
                    @foreach($exportColumnOptions as $columnKey => $columnLabel)
                        <label style="display:flex;align-items:center;gap:8px;font-weight:600;color:#475569;">
                            <input type="checkbox" name="export_columns[]" value="{{ $columnKey }}" @checked(in_array($columnKey, $selectedExportColumns, true))>
                            <span>{{ $columnLabel }}</span>
                        </label>
                    @endforeach
                </div>
                <div style="margin-top:8px;font-size:0.84rem;color:#64748b;">
                    Tip: Export buttons will use the selected columns and current filters.
                </div>
            </details>
        </form>

        <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;font-weight:700;color:#334155;">
            Archived Facilities ({{ method_exists($archivedFacilities, 'total') ? $archivedFacilities->total() : $archivedFacilities->count() }})
        </div>

        @if($archivedFacilities->count() === 0)
            <div style="padding:22px 16px;color:#64748b;">No archived facilities yet.</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;min-width:1280px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;color:#334155;">
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Facility</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Type</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Status</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Barangay</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Reason</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Deleted By</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Archived At</th>
                            <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedFacilities as $facility)
                            <tr>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-weight:700;">
                                    {{ $facility->name }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    {{ $facility->type ?? '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    {{ ucfirst((string) ($facility->status ?? '-')) }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    {{ $facility->barangay ?? '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;max-width:280px;">
                                    @php $fullReason = (string) ($facility->archive_reason ?? ''); @endphp
                                    <div style="display:flex;align-items:center;gap:8px;justify-content:flex-start;">
                                        <span title="{{ $fullReason !== '' ? $fullReason : '-' }}">
                                            {{ \Illuminate\Support\Str::limit($fullReason !== '' ? $fullReason : '-', 70) }}
                                        </span>
                                        @if($fullReason !== '')
                                            <button type="button"
                                                onclick="openArchiveReasonModal(@js($facility->name), @js($fullReason))"
                                                style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:999px;padding:3px 10px;font-size:0.78rem;font-weight:700;">
                                                View
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    {{ $facility->deletedByUser?->full_name ?? $facility->deletedByUser?->name ?? $facility->deletedByUser?->username ?? 'Unknown' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $facility->deleted_at ? $facility->deleted_at->format('M d, Y h:i A') : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                    <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:center;">
                                    <form method="POST" action="{{ route('modules.facilities.restore', $facility->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit"
                                            style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-weight:700;"
                                            onclick="return confirm('Restore facility ' + @js($facility->name) + '?');">
                                            Restore
                                        </button>
                                    </form>
                                    @if($canForceDelete)
                                        <form method="POST" action="{{ route('modules.facilities.force-delete', $facility->id) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                style="background:#e11d48;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-weight:700;"
                                                onclick="return confirm('Permanently delete ' + @js($facility->name) + '? This will remove related records and cannot be undone.');">
                                                Permanent Delete
                                            </button>
                                        </form>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($archivedFacilities, 'hasPages') && $archivedFacilities->hasPages())
                <div style="padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-top:1px solid #e5e7eb;background:#fcfdff;">
                    <div style="color:#64748b;font-size:0.92rem;">
                        Showing {{ $archivedFacilities->firstItem() }} to {{ $archivedFacilities->lastItem() }} of {{ $archivedFacilities->total() }} archived facilities
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        @if($archivedFacilities->onFirstPage())
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Previous</span>
                        @else
                            <a href="{{ $archivedFacilities->previousPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:600;">Previous</a>
                        @endif
                        <span style="padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;">
                            Page {{ $archivedFacilities->currentPage() }} / {{ $archivedFacilities->lastPage() }}
                        </span>
                        @if($archivedFacilities->hasMorePages())
                            <a href="{{ $archivedFacilities->nextPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:600;">Next</a>
                        @else
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

<div id="archiveReasonModal" style="display:none;position:fixed;inset:0;z-index:10050;background:rgba(15,23,42,0.55);backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:16px;">
    <div style="width:min(640px,100%);background:#fff;border-radius:16px;box-shadow:0 18px 40px rgba(15,23,42,0.2);padding:20px;position:relative;">
        <button type="button" onclick="closeArchiveReasonModal()" style="position:absolute;top:10px;right:12px;border:none;background:none;font-size:1.3rem;color:#64748b;cursor:pointer;">&times;</button>
        <div style="font-size:0.85rem;font-weight:800;color:#64748b;letter-spacing:.03em;text-transform:uppercase;">Archive Reason</div>
        <h3 id="archiveReasonModalFacility" style="margin:6px 0 12px;font-size:1.15rem;font-weight:800;color:#1e293b;"></h3>
        <div id="archiveReasonModalText" style="white-space:pre-wrap;line-height:1.55;color:#334155;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;"></div>
        <div style="display:flex;justify-content:flex-end;margin-top:14px;">
            <button type="button" onclick="closeArchiveReasonModal()" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 16px;font-weight:700;">Close</button>
        </div>
    </div>
</div>

<script>
function openArchiveReasonModal(facilityName, reasonText) {
    var modal = document.getElementById('archiveReasonModal');
    var facilityEl = document.getElementById('archiveReasonModalFacility');
    var textEl = document.getElementById('archiveReasonModalText');
    if (!modal || !facilityEl || !textEl) return;
    facilityEl.textContent = facilityName || 'Facility';
    textEl.textContent = reasonText || '-';
    modal.style.display = 'flex';
}
function closeArchiveReasonModal() {
    var modal = document.getElementById('archiveReasonModal');
    if (modal) modal.style.display = 'none';
}
</script>
@endsection
