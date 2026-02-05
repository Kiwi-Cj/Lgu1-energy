@extends('layouts.qc-admin')
@section('title', 'Energy Report')
@section('content')
<div style="width:100%;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <div>
            <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:4px;">📘 Energy Consumption Report</h2>
            <p style="color:#555;margin-bottom:0;">Detailed energy consumption data and trends for all facilities.</p>
        </div>
        <a href="{{ route('reports.energy-export', request()->all()) }}" class="btn btn-success" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#22c55e,#16a34a);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(34,197,94,0.07);text-decoration:none;">
            ⬇️ Export
        </a>
    </div>
	
	<!-- FILTERS -->
	<form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
		<div style="display:flex;flex-direction:column;">
			<label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Facilities</option>
                @foreach($facilities ?? [] as $facility)
                    <option value="{{ $facility->id }}" {{ (request('facility_id') == $facility->id) ? 'selected' : '' }}>{{ $facility->name }}</option>
                @endforeach
            </select>
		</div>
        <div style="display:flex;flex-direction:column;">
            <label for="year" style="font-weight:700;margin-bottom:4px;">Year</label>
                <select name="year" id="year" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                    @foreach($years ?? [] as $year)
                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="month" style="font-weight:700;margin-bottom:4px;">Month</label>
            <select name="month" id="month" class="form-control" style="min-width:100px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Months</option>
                @php
                    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                @endphp
                @foreach($months as $num=>$name)
                    <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
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
            <th style="padding:12px;text-align:right;">Baseline kWh</th>
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
            <td style="padding:10px;text-align:right;">{{ $row['baseline_kwh'] }}</td>
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
