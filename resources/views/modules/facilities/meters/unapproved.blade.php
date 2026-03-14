@extends('layouts.qc-admin')
@section('title', 'Unapproved Meters')

@section('content')
@php
    $filters = $filters ?? ['q' => '', 'status' => ''];
    $totalUnapproved = $unapprovedMainMeters->count() + $unapprovedSubMeters->count();
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
            <h2 style="margin:0;color:#2563eb;font-weight:800;">Unapproved Meters</h2>
            <div style="color:#64748b;margin-top:4px;">Facility: <strong style="color:#1e293b;">{{ $facility->name }}</strong></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('modules.facilities.energy-profile.index', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#1e293b;padding:10px 14px;border-radius:10px;font-weight:700;">
                <i class="fa fa-arrow-left"></i> Back to Energy Profile
            </a>
            <a href="{{ route('modules.facilities.meters.archive', $facility->id) }}" style="text-decoration:none;background:#fff;color:#1e293b;padding:10px 14px;border-radius:10px;font-weight:700;border:1px solid #cbd5e1;">
                <i class="fa fa-box-archive"></i> Meter Archive
            </a>
        </div>
    </div>

    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(15,23,42,0.06);overflow:hidden;">
        <form method="GET" action="{{ route('modules.facilities.meters.unapproved', $facility->id) }}" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;gap:10px;flex-wrap:wrap;align-items:end;background:#fcfdff;">
            <div style="display:flex;flex-direction:column;gap:5px;min-width:240px;flex:1;">
                <label style="font-size:.84rem;font-weight:700;color:#475569;">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Meter name/number/location/notes" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px;">
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
                <a href="{{ route('modules.facilities.meters.unapproved', $facility->id) }}" style="text-decoration:none;background:#f1f5f9;color:#334155;border-radius:10px;padding:10px 14px;font-weight:700;">Reset</a>
            </div>
        </form>

        @if($totalUnapproved === 0)
            <div style="padding:16px;color:#64748b;border-bottom:1px solid #e5e7eb;background:#f8fafc;">No unapproved meters found for current filters.</div>
        @endif

        <div style="padding:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <h3 style="margin:0;color:#1e293b;font-weight:800;">Unapproved Main Meters</h3>
                    <span style="display:inline-flex;padding:5px 10px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:800;font-size:.8rem;">{{ $unapprovedMainMeters->count() }}</span>
                </div>
                @if($unapprovedMainMeters->count() === 0)
                    <div style="padding:16px;color:#64748b;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;">No unapproved main meters found.</div>
                @else
                    <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:12px;">
                        <table style="width:100%;min-width:1040px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;color:#334155;">
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Meter</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Number</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Location</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Baseline kWh</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Notes</th>
                                    <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unapprovedMainMeters as $meter)
                                    <tr>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $meter->meter_name }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->meter_number ?: '-' }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->location ?: '-' }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ ucfirst((string) $meter->status) }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;text-align:right;">
                                            {{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}
                                        </td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;max-width:260px;">
                                            <span title="{{ $meter->notes ?: '' }}">{{ \Illuminate\Support\Str::limit($meter->notes ?: '-', 60) }}</span>
                                        </td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                            @if($canApproveMeters)
                                                <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="_redirect_to" value="meters_unapproved">
                                                    <button type="submit" style="background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:8px;padding:7px 12px;font-weight:700;">
                                                        Approve
                                                    </button>
                                                </form>
                                            @else
                                                <span style="color:#94a3b8;">View only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
        </div>

        <div style="padding:0 16px 16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <h3 style="margin:0;color:#1e293b;font-weight:800;">Unapproved Sub Meters</h3>
                    <span style="display:inline-flex;padding:5px 10px;border-radius:999px;background:#ede9fe;color:#6d28d9;font-weight:800;font-size:.8rem;">{{ $unapprovedSubMeters->count() }}</span>
                </div>
                @if(! $hasMainMeter)
                    <div style="padding:16px;color:#92400e;border:1px dashed #f59e0b;border-radius:12px;background:#fffbeb;">
                        No main meter found for this facility. Sub meter list is hidden.
                    </div>
                @elseif($unapprovedSubMeters->count() === 0)
                    <div style="padding:16px;color:#64748b;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;">No unapproved sub meters found.</div>
                @else
                    <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:12px;">
                        <table style="width:100%;min-width:1120px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f8fafc;color:#334155;">
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Meter</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Number</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Parent</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Location</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Status</th>
                                    <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Baseline kWh</th>
                                    <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Notes</th>
                                    <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unapprovedSubMeters as $meter)
                                    <tr>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-weight:700;">{{ $meter->meter_name }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->meter_number ?: '-' }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->parentMeter?->meter_name ?: '-' }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ $meter->location ?: '-' }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">{{ ucfirst((string) $meter->status) }}</td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;text-align:right;">
                                            {{ $meter->baseline_kwh !== null ? number_format((float) $meter->baseline_kwh, 2) : '-' }}
                                        </td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;max-width:260px;">
                                            <span title="{{ $meter->notes ?: '' }}">{{ \Illuminate\Support\Str::limit($meter->notes ?: '-', 60) }}</span>
                                        </td>
                                        <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                            @if($canApproveMeters)
                                                <form method="POST" action="{{ route('modules.facilities.meters.toggle-approval', [$facility->id, $meter->id]) }}" style="display:inline;">
                                                    @csrf
                                                    <input type="hidden" name="_redirect_to" value="meters_unapproved">
                                                    <button type="submit" style="background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:8px;padding:7px 12px;font-weight:700;">
                                                        Approve
                                                    </button>
                                                </form>
                                            @else
                                                <span style="color:#94a3b8;">View only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
        </div>
    </div>
</div>
@endsection
