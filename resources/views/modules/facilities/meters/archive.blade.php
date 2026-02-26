@extends('layouts.qc-admin')
@section('title', 'Meter Archive')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'meter_type' => ''];
@endphp
<div style="padding:12px;">
    @if(session('success'))
        <div style="margin-bottom:12px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:12px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-weight:700;">{{ session('error') }}</div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
        <div>
            <h2 style="margin:0;color:#2563eb;font-weight:800;">Meter Archive</h2>
            <div style="color:#64748b;margin-top:4px;">Facility: <strong style="color:#1e293b;">{{ $facility->name }}</strong></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.facilities.meters.index', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#1e293b;padding:10px 14px;border-radius:10px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Back to Meters
            </a>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(15,23,42,0.06);overflow:hidden;">
        <form method="GET" action="{{ route('modules.facilities.meters.archive', $facility->id) }}" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;gap:10px;flex-wrap:wrap;align-items:end;background:#fcfdff;">
            <div style="display:flex;flex-direction:column;gap:5px;min-width:240px;flex:1;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Meter name/number/location/reason" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;min-width:160px;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Type</label>
                <select name="meter_type" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
                    <option value="">All</option>
                    <option value="main" @selected(($filters['meter_type'] ?? '') === 'main')>Main</option>
                    <option value="sub" @selected(($filters['meter_type'] ?? '') === 'sub')>Sub</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;font-weight:700;">Filter</button>
                <a href="{{ route('modules.facilities.meters.archive', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        @if($archivedMeters->count() === 0)
            <div style="padding:22px 16px;color:#64748b;">No archived meters yet.</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;min-width:1140px;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;color:#334155;">
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Meter</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Number</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Type</th>
                            <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Baseline kWh</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Reason</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Deleted By</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Archived At</th>
                            <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedMeters as $meter)
                            <tr>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $meter->meter_name }}</td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->meter_number ?: '-' }}</td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ strtoupper((string) $meter->meter_type) }}</td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;text-align:right;">
                                    {{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;max-width:260px;">
                                    @php $reason = (string) ($meter->archive_reason ?? ''); @endphp
                                    <span title="{{ $reason }}">{{ \Illuminate\Support\Str::limit($reason !== '' ? $reason : '-', 60) }}</span>
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $meter->deletedByUser?->full_name ?? $meter->deletedByUser?->name ?? $meter->deletedByUser?->username ?? 'Unknown' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $meter->deleted_at?->format('M d, Y h:i A') ?? '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                    <div style="display:inline-flex;gap:8px;flex-wrap:wrap;justify-content:center;">
                                        @if($canManageMeters)
                                            <form method="POST" action="{{ route('modules.facilities.meters.restore', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 12px;font-weight:700;"
                                                    onclick="return confirm('Restore meter ' + @js($meter->meter_name) + '?');">
                                                    Restore
                                                </button>
                                            </form>
                                        @endif
                                        @if($canForceDeleteMeters)
                                            <form method="POST" action="{{ route('modules.facilities.meters.force-delete', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background:#e11d48;color:#fff;border:none;border-radius:8px;padding:8px 12px;font-weight:700;"
                                                    onclick="return confirm('Permanently delete meter ' + @js($meter->meter_name) + '?');">
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
            @if($archivedMeters->hasPages())
                <div style="padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;border-top:1px solid #e5e7eb;background:#fcfdff;">
                    <div style="color:#64748b;">Showing {{ $archivedMeters->firstItem() }} to {{ $archivedMeters->lastItem() }} of {{ $archivedMeters->total() }} archived meters</div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        @if($archivedMeters->onFirstPage())
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Previous</span>
                        @else
                            <a href="{{ $archivedMeters->previousPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Previous</a>
                        @endif
                        <span style="padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;">Page {{ $archivedMeters->currentPage() }} / {{ $archivedMeters->lastPage() }}</span>
                        @if($archivedMeters->hasMorePages())
                            <a href="{{ $archivedMeters->nextPageUrl() }}" style="padding:8px 12px;border-radius:8px;background:#e2e8f0;color:#1e293b;text-decoration:none;font-weight:700;">Next</a>
                        @else
                            <span style="padding:8px 12px;border-radius:8px;background:#f1f5f9;color:#94a3b8;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
