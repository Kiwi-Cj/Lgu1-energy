@extends('layouts.qc-admin')
@section('title', 'Energy Record Details')
@section('content')
<div class="energy-show-card" style="max-width:700px;margin:40px auto;background:#f5f8ff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h2 style="font-weight:700;font-size:2rem;color:#3762c8;margin:0;">Energy Record Details</h2>
        <a href="{{ route('modules.energy.index', array_filter(['facility_id' => $facility_id ?? null, 'month' => $month ?? null, 'year' => $year ?? null])) }}" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;text-decoration:none;">&larr; Back to List</a>
    </div>
    <table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;">
        <tbody>
            <tr><th style="width:220px;">ID</th><td>{{ $usage->id }}</td></tr>
            <tr><th>Facility</th><td>{{ $usage->facility->name ?? '-' }}</td></tr>
            <tr><th>Month</th><td>
                @php
                    $monthNum = (int) $usage->month;
                    $dateObj = DateTime::createFromFormat('!m', $monthNum);
                    $monthName = $dateObj ? $dateObj->format('M') : $usage->month;
                @endphp
                {{ $monthName }}
            </td></tr>
            <tr><th>Year</th><td>{{ $usage->year }}</td></tr>
            <tr><th>kWh Consumed</th><td>{{ $usage->kwh_consumed }}</td></tr>
            <tr><th>Electric Meter No.</th><td>{{ $usage->electric_meter_no ?? '-' }}</td></tr>
            <tr><th>Utility Provider</th><td>{{ $usage->utility_provider ?? '-' }}</td></tr>
            <tr><th>Contract Account No.</th><td>{{ $usage->contract_account_no ?? '-' }}</td></tr>
            <tr><th>Average Monthly kWh</th><td>{{ $usage->average_monthly_kwh ?? '-' }}</td></tr>
            <tr><th>Main Energy Source</th><td>{{ $usage->main_energy_source ?? '-' }}</td></tr>
            <tr><th>Backup Power</th><td>{{ $usage->backup_power ?? '-' }}</td></tr>
            <tr><th>Transformer Capacity</th><td>{{ $usage->transformer_capacity ?? '-' }}</td></tr>
            <tr><th>Number of Meters</th><td>{{ $usage->number_of_meters ?? '-' }}</td></tr>
            <tr><th>Created By</th><td>{{ $usage->created_by ?? '-' }}</td></tr>
            <tr><th>Created At</th><td>{{ $usage->created_at }}</td></tr>
            <tr><th>Updated At</th><td>{{ $usage->updated_at }}</td></tr>
        </tbody>
    </table>
</div>
@endsection
