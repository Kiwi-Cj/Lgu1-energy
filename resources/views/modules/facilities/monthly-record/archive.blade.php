@extends('layouts.qc-admin')
@section('title', 'Monthly Records Archive')

@section('content')
<div style="padding: 12px;">
    @if(session('success'))
        <div style="margin-bottom:14px;background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:14px;background:#fee2e2;color:#b91c1c;padding:12px 16px;border-radius:10px;font-weight:600;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <div style="font-size:1.9rem;font-weight:700;color:#0f172a;">Monthly Records Archive</div>
            <div style="font-size:1rem;color:#64748b;">Facility: <span style="font-weight:600;color:#1e293b;">{{ $facility->name }}</span></div>
        </div>
        <a href="{{ route('facilities.monthly-records', $facility->id) }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:600;">
            <i class="fa fa-arrow-left"></i>
            Back to Records
        </a>
    </div>

    <div style="background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(15,23,42,0.06);overflow:hidden;">
        <div style="padding:14px 16px;border-bottom:1px solid #e5e7eb;font-weight:700;color:#334155;">
            Deleted Records ({{ $archivedRecords->count() }})
        </div>

        @if($archivedRecords->isEmpty())
            <div style="padding:22px 16px;color:#64748b;">No archived monthly records yet.</div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:920px;">
                    <thead>
                        <tr style="background:#f8fafc;color:#334155;">
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Billing Period</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Scope</th>
                            <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Actual kWh</th>
                            <th style="text-align:right;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Energy Cost</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Alert</th>
                            <th style="text-align:left;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Archived At</th>
                            <th style="text-align:center;padding:12px 14px;border-bottom:1px solid #e5e7eb;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedRecords as $record)
                            @php
                                $monthLabel = ($record->month && is_numeric($record->month))
                                    ? date('F', mktime(0,0,0,(int)$record->month,1))
                                    : (string) $record->month;
                            @endphp
                            <tr>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;font-weight:600;">
                                    {{ trim($monthLabel . ' ' . $record->year) }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    @if($record->meter)
                                        <span style="font-weight:700;">{{ strtoupper((string) $record->meter->meter_type) }}</span> - {{ $record->meter->meter_name }}
                                    @else
                                        <span style="display:inline-flex;padding:4px 10px;border-radius:999px;font-size:.76rem;font-weight:800;background:#f1f5f9;color:#334155;">FACILITY AGGREGATE</span>
                                    @endif
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#1e293b;">
                                    {{ is_numeric($record->actual_kwh) ? number_format((float) $record->actual_kwh, 2) : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:right;color:#1e293b;">
                                    {{ is_numeric($record->energy_cost) ? number_format((float) $record->energy_cost, 2) : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                                    {{ $record->alert ?: '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;color:#475569;">
                                    {{ $record->deleted_at ? $record->deleted_at->format('M d, Y h:i A') : '-' }}
                                </td>
                                <td style="padding:12px 14px;border-bottom:1px solid #f1f5f9;text-align:center;">
                                    <form method="POST" action="{{ route('energy-records.restore', ['facility' => $facility->id, 'record' => $record->id]) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit"
                                            style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-weight:600;"
                                            onclick="return confirm('Restore monthly record for {{ $monthLabel }} {{ $record->year }}?');">
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
