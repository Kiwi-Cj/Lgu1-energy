@extends('layouts.qc-admin')
@section('title', 'Bill Details')
@section('content')
<div class="billing-show-card" style="max-width:520px;margin:40px auto;background:#f5f8ff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;color:#3762c8;margin-bottom:18px;">Bill Details</h2>
    <table class="table" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;">
        <tr><th>Facility</th><td>{{ $bill->facility->name ?? '-' }}</td></tr>
        <tr><th>Month</th><td>{{ $bill->month ? date('M Y', strtotime($bill->month.'-01')) : '-' }}</td></tr>
        <tr><th>kWh Consumed</th><td>{{ $bill->kwh_consumed }}</td></tr>
        <tr><th>Unit Cost (PHP)</th><td>{{ $bill->unit_cost }}</td></tr>
        <tr><th>Total Bill (PHP)</th><td>{{ $bill->total_bill }}</td></tr>
        <tr><th>Status</th><td>{{ $bill->status }}</td></tr>
        <tr>
            <th>Meralco Bill Picture</th>
            <td>
                @if($bill->meralco_bill_picture)
                    <a href="{{ asset('storage/' . $bill->meralco_bill_picture) }}" target="_blank">
                        <img src="{{ asset('storage/' . $bill->meralco_bill_picture) }}" alt="Meralco Bill" style="max-width:120px;max-height:120px;border-radius:6px;border:1px solid #ccc;">
                        <br>View/Download
                    </a>
                @else
                    <span style="color:#888;">No file uploaded</span>
                @endif
            </td>
        </tr>
    </table>
    <div style="margin-top:24px;">
        <a href="{{ route('modules.billing.edit', $bill->id) }}" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:8px 28px;">Edit</a>
        <a href="{{ route('modules.billing.index') }}" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;text-decoration:none;">Back</a>
    </div>
</div>
@endsection
