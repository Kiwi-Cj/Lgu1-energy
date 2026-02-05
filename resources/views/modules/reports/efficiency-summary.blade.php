@extends('layouts.qc-admin')
@section('title', 'Efficiency Summary Report')
@section('content')
<div style="width:100%;margin:0;">
    <h2 style="font-size:2rem;font-weight:700;color:#3762c8;margin-bottom:18px;">⚡ 3️⃣ Efficiency Summary Report</h2>
    <p style="color:#555;margin-bottom:24px;">Summary of energy efficiency across all facilities.</p>
    <!-- FILTERS -->
    <form method="GET" action="" style="margin-bottom:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;flex-direction:column;">
            <label for="facility_id" style="font-weight:700;margin-bottom:4px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="min-width:170px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="">All Facilities</option>
                @foreach($facilities ?? [] as $facility)
                    <option value="{{ $facility->id }}" {{ (isset($selectedFacility) && $selectedFacility == $facility->id) ? 'selected' : '' }}>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;flex-direction:column;">
            <label for="rating" style="font-weight:700;margin-bottom:4px;">Rating</label>
            <select name="rating" id="rating" class="form-control" style="min-width:120px;padding:6px 10px;border-radius:7px;border:1px solid #c3cbe5;font-size:1rem;">
                <option value="all" {{ (isset($selectedRating) && ($selectedRating == 'all' || $selectedRating == '')) ? 'selected' : '' }}>All Ratings</option>
                <option value="High" {{ (isset($selectedRating) && $selectedRating == 'High') ? 'selected' : '' }}>High</option>
                <option value="Medium" {{ (isset($selectedRating) && $selectedRating == 'Medium') ? 'selected' : '' }}>Medium</option>
                <option value="Low" {{ (isset($selectedRating) && $selectedRating == 'Low') ? 'selected' : '' }}>Low</option>
            </select>
        </div>
        <div style="display:flex;flex-direction:column;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" style="padding:7px 22px;border-radius:7px;background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;font-size:1rem;box-shadow:0 2px 6px rgba(55,98,200,0.07);margin-top:24px;">Filter</button>
        </div>
    </form>
    <table class="table" style="width:100%;margin-top:18px;background:#fff;border-radius:10px;overflow:hidden;text-align:center;box-shadow:0 2px 8px rgba(55,98,200,0.07);">
        <thead style="background:#e9effc;">
            <tr>
                <th style="padding:12px;">Facility</th>
                <th style="padding:12px;">EUI</th>
                <th style="padding:12px;">Rating</th>
                <th style="padding:12px;">Last Audit</th>
                <th style="padding:12px;">Flag</th>
            </tr>
        </thead>
        <tbody>
            @forelse($efficiencyRows ?? [] as $row)
            <tr>
                <td style="padding:10px 8px;">{{ $row['facility'] }}</td>
                <td style="padding:10px 8px;">{{ $row['eui'] }}</td>
                <td style="padding:10px 8px;">{{ $row['rating'] }}</td>
                <td style="padding:10px 8px;">{{ $row['last_audit'] }}</td>
                <td style="padding:10px 8px;">
                    @if($row['flag'])
                        <span style="color:#e11d48;font-weight:700;">🚩 For Maintenance</span>
                    @else
                        <span style="color:#22c55e;font-weight:700;">OK</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="padding:16px;">No efficiency data found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
