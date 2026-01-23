@extends('layouts.qc-admin')
@section('title', 'Billing and Cost Reports')
@section('content')
<div style="max-width:1100px;margin:0 auto;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">💰 2️⃣ Billing & Cost Report</h2>
    <p style="color:#555;margin-bottom:24px;">Billing and cost analysis for all facilities.</p>
    <!-- SUMMARY CARDS -->
    <div class="row" style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:2rem;">
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f5f8ff;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(55,98,200,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#3762c8;">Highest Energy Cost</div>
            <div style="font-size:1.5rem;font-weight:700;margin:8px 0;">{{ $highestCostFacility ?? '-' }}</div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#f0fdf4;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#22c55e;">Lowest Energy Cost</div>
            <div style="font-size:1.5rem;font-weight:700;margin:8px 0;">{{ $lowestCostFacility ?? '-' }}</div>
        </div>
        <div class="card" style="flex:1 1 220px;min-width:220px;background:#fff0f3;padding:24px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(225,29,72,0.08);">
            <div style="font-size:1.1rem;font-weight:500;color:#e11d48;">Unpaid Bills</div>
            <div style="font-size:1.5rem;font-weight:700;margin:8px 0;">{{ $unpaidBillsCount ?? '-' }}</div>
        </div>
    </div>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Facilities</option>
                @foreach($facilities ?? [] as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility_id') == $facility->id) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="month" style="font-weight:700;margin-bottom:4px;">Month / Year</label>
            <input type="month" name="month" id="month" class="form-control" style="min-width:140px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;" value="{{ request('month') }}">
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="status" style="font-weight:700;margin-bottom:4px;">Status</label>
            <select name="status" id="status" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Status</option>
                <option value="Paid" @if(request('status') == 'Paid') selected @endif>Paid</option>
                <option value="Unpaid" @if(request('status') == 'Unpaid') selected @endif>Unpaid</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
        </div>
    </form>
    <table class="table" style="width:100%;margin-top:12px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;">
        <thead style="background:#e9effc;">
            <tr>
                <th>Facility</th>
                <th>Month</th>
                <th>kWh</th>
                <th>Total Bill</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($billingRows ?? [] as $row)
            <tr>
                <td>{{ $row['facility'] }}</td>
                <td>{{ $row['month'] }}</td>
                <td>{{ $row['kwh'] }}</td>
                <td>{{ $row['total_bill'] }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No billing data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
