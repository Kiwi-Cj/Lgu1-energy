@extends('layouts.qc-admin')
@section('title', 'Energy Report')
@section('content')
<div style="max-width:1100px;margin:0 auto;">
	<h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">📘 1️⃣ Energy Consumption Report</h2>
	<p style="color:#555;margin-bottom:24px;">Detailed energy consumption data and trends for all facilities.</p>
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
		<div style="display:flex;flex-direction:column;justify-content:flex-end;">
			<button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
		</div>
	</form>
		<table style="width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);">
    <thead style="background:#e9effc;">
        <tr>
            <th style="padding:12px;text-align:left;">Facility</th>
            <th style="padding:12px;text-align:left;">Month</th>
            <th style="padding:12px;text-align:right;">Actual kWh</th>
            <th style="padding:12px;text-align:right;">Avg kWh</th>
            <th style="padding:12px;text-align:right;">Variance</th>
            <th style="padding:12px;text-align:center;">Trend</th>
        </tr>
    </thead>

    <tbody>
        @forelse($energyRows ?? [] as $row)
        <tr style="border-top:1px solid #e5e7eb;">
            <td style="padding:10px;">{{ $row['facility'] }}</td>
            <td style="padding:10px;">{{ $row['month'] }}</td>
            <td style="padding:10px;text-align:right;">{{ $row['actual_kwh'] }}</td>
            <td style="padding:10px;text-align:right;">{{ $row['avg_kwh'] }}</td>
            <td style="padding:10px;text-align:right;">{{ $row['variance'] }}</td>
            <td style="padding:10px;text-align:center;">
    @if($row['trend'] === 'up')
        <span style="color:#dc2626;font-weight:700;">↑ Increasing</span>
    @elseif($row['trend'] === 'down')
        <span style="color:#16a34a;font-weight:700;">↓ Decreasing</span>
    @else
        <span style="color:#6b7280;font-weight:700;">→ Stable</span>
    @endif
</td>

        </tr>
        @empty
        <tr>
            <td colspan="6" style="padding:14px;text-align:center;color:#6b7280;">
                No energy data found.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
