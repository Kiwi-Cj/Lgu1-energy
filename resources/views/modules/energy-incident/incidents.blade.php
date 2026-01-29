@extends('layouts.qc-admin')

@section('content')
<div style="max-width:900px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin:0 0 18px 0;">
        <h2 style="font-size:2rem; font-weight:700; color:#222; margin:0;">Energy Incident History</h2>
    </div>
    <div style="overflow-x:auto; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(31,38,135,0.08);">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead style="background:#f1f5f9;">
                <tr style="text-align:center;">
                    <th style="padding:10px 14px; text-align:center;">Facility</th>
                    <th style="padding:10px 14px; text-align:center;">Month / Year</th>
                    <th style="padding:10px 14px; text-align:center;">Deviation (%)</th>
                    <th style="padding:10px 14px; text-align:center;">Alert Level</th>
                    <th style="padding:10px 14px; text-align:center;">Status</th>
                    <th style="padding:10px 14px; text-align:center;">Date Detected</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $incident)
                    <tr style="border-bottom:1px solid #e5e7eb; text-align:center;">
                        <td style="padding:10px 14px; text-align:center;">{{ $incident->facility->name ?? '-' }}</td>
                        <td style="padding:10px 14px; text-align:center;">
                            @php
                                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                $monthNum = isset($incident->month) ? (int) $incident->month : null;
                                $monthLabel = $monthNum && $monthNum >= 1 && $monthNum <= 12 ? $months[$monthNum-1] : ($incident->month ?? '-');
                            @endphp
                            {{ $monthLabel }}/{{ $incident->year ?? '-' }}
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            {{ isset($incident->deviation_percent) ? number_format($incident->deviation_percent, 2) . '%' : '-' }}
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            <span style="color:#e11d48;font-weight:600;">High</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            <span style="color:#2563eb;font-weight:600;">High Alert</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            {{ $incident->date_detected ? \Carbon\Carbon::parse($incident->date_detected)->format('M d, Y') : ($incident->created_at ? $incident->created_at->format('M d, Y') : '-') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color:#64748b; padding:18px 0;">No incidents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
