@extends('layouts.qc-admin')

@section('title', 'Facilities List')

@section('content')



<div style="background: #f7f9fb; border-radius: 20px; padding: 24px 18px 18px 18px; margin: 0 auto; max-width: 1300px;">
    <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 0.5rem;">
        <a href="{{ url()->previous() }}" style="color: #6c2eb9; font-weight: 500; text-decoration: none; font-size: 1.1rem;"><i class="fa fa-arrow-left"></i> Back</a>
        <h2 style="font-size:2.2rem; font-weight:700; margin:0; color:#222;">Facilities</h2>
    </div>
    <div style="margin-bottom: 0.7rem;">
        <a href="{{ route('facilities.create') }}" style="color: #6c2eb9; font-weight: 600; text-decoration: underline; font-size: 1.1rem;"><i class="fa fa-plus"></i> Add Facility</a>
    </div>
    <div style="display: flex; gap: 6px; margin-bottom: 0.7rem; flex-wrap: wrap;">
        <input type="text" placeholder="Search by name, type, department..." style="border:1px solid #d1d5db; border-radius:5px; padding:6px 10px; font-size:1rem; min-width:220px;">
        <input type="text" placeholder="Barangay, Address..." style="border:1px solid #d1d5db; border-radius:5px; padding:6px 10px; font-size:1rem; min-width:180px;">
        <button style="border:1px solid #d1d5db; border-radius:5px; background:#fff; color:#6c2eb9; font-weight:500; padding:6px 18px; font-size:1rem; cursor:pointer;"><i class="fa fa-search"></i> Filter</button>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:separate; border-spacing:0; background:#fff; border-radius:12px; overflow:hidden;">
            <thead style="background: #f1f5fa;">
                <tr>
                    <th style="padding:10px 8px; font-weight:700;">Image</th>
                    <th style="padding:10px 8px; font-weight:700;">Name</th>
                    <th style="padding:10px 8px; font-weight:700;">Type</th>
                    <th style="padding:10px 8px; font-weight:700;">Status</th>
                    <th style="padding:10px 8px; font-weight:700;">Department</th>
                    <th style="padding:10px 8px; font-weight:700;">Barangay</th>
                    <th style="padding:10px 8px; font-weight:700;">Address</th>
                    <th style="padding:10px 8px; font-weight:700;">Floor Area</th>
                    <th style="padding:10px 8px; font-weight:700;">Floors</th>
                    <th style="padding:10px 8px; font-weight:700;">Year Built</th>
                    <th style="padding:10px 8px; font-weight:700;">Operating Hours</th>
                    <th style="padding:10px 8px; font-weight:700;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facilities as $facility)
                <tr style="background:{{ $loop->even ? '#f6f8fc' : '#fff' }};">
                    <td style="padding:8px 6px; width:60px;">
                        @if($facility->image)
                            <img src="{{ asset('storage/' . $facility->image) }}" alt="Facility Image" style="width:40px;height:40px;object-fit:cover;border-radius:7px;">
                        @else
                            <div style="width:40px;height:40px;background:#f1f5f9;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:1.3rem;">
                                <i class="fa fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td style="padding:8px 6px; font-weight:600;">{{ $facility->name ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->type ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->status ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->department ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->barangay ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->address ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->floor_area ?? '-' }} sqm</td>
                    <td style="padding:8px 6px;">{{ $facility->floors ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->year_built ?? '-' }}</td>
                    <td style="padding:8px 6px;">{{ $facility->operating_hours ?? '-' }}</td>
                    <td style="padding:8px 6px;">
                        <a href="{{ route('facilities.show', $facility->id) }}" title="View" style="color:#6c2eb9; font-size:1.2rem; margin-right:8px;"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('facilities.edit', $facility->id) }}" title="Edit" style="color:#6c2eb9; font-size:1.2rem; margin-right:8px;"><i class="fa fa-pen"></i></a>
                        <form action="{{ route('facilities.destroy', $facility->id) }}" method="POST" style="display:inline; vertical-align:middle;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete" onclick="return confirm('Are you sure you want to delete this facility?');" style="background:none; border:none; color:#e11d48; font-size:1.2rem; cursor:pointer; padding:0; margin:0; display:inline-flex; align-items:center; vertical-align:middle;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="text-align:center; color:#94a3b8; font-size:1.1rem; padding: 32px 0;">No facilities found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
