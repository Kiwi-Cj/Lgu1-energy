<!-- Edit Facility Modal -->
<div id="editFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:10050 !important;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
	<div class="modal-content" style="max-width:440px;background:#f8fafc;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);padding:32px 28px;position:relative;">
		<button class="modal-close" type="button" onclick="closeEditFacilityModal()" style="top:12px;right:12px;position:absolute;font-size:1.5rem;background:none;border:none;color:#2563eb;">&times;</button>
		<h2 style="margin-bottom:10px;font-size:1.5rem;font-weight:700;color:#2563eb;">Edit Facility</h2>
		<form id="editFacilityForm" action="{{ route('facilities.update', $facility->id) }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px;">
			@csrf
			@method('PUT')
			<div style="display:flex;gap:12px;">
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_name" style="font-weight:500;">Name</label>
					<input type="text" id="edit_name" name="name" value="{{ $facility->name }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_type" style="font-weight:500;">Type</label>
					<input type="text" id="edit_type" name="type" value="{{ $facility->type }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
			</div>
			<div style="display:flex;gap:12px;">
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_department" style="font-weight:500;">Department</label>
					<input type="text" id="edit_department" name="department" value="{{ $facility->department }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_address" style="font-weight:500;">Address</label>
					<input type="text" id="edit_address" name="address" value="{{ $facility->address }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
			</div>
			<div style="display:flex;gap:12px;">
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_barangay" style="font-weight:500;">Barangay</label>
					<input type="text" id="edit_barangay" name="barangay" value="{{ $facility->barangay }}" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_floor_area" style="font-weight:500;">Floor Area</label>
					<input type="number" id="edit_floor_area" name="floor_area" value="{{ $facility->floor_area }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
			</div>
			<div style="display:flex;gap:12px;">
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_floors" style="font-weight:500;">Floors</label>
					<input type="number" id="edit_floors" name="floors" value="{{ $facility->floors }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_year_built" style="font-weight:500;">Year Built</label>
					<input type="number" id="edit_year_built" name="year_built" value="{{ $facility->year_built }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
			</div>
			<div style="display:flex;gap:12px;">
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_operating_hours" style="font-weight:500;">Operating Hours</label>
					<input type="text" id="edit_operating_hours" name="operating_hours" value="{{ $facility->operating_hours }}" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
				</div>
				<div style="flex:1;display:flex;flex-direction:column;gap:4px;">
					<label for="edit_status" style="font-weight:500;">Status</label>
					<select id="edit_status" name="status" required style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
						<option value="active" @if($facility->status=='active') selected @endif>Active</option>
						<option value="inactive" @if($facility->status=='inactive') selected @endif>Inactive</option>
						<option value="maintenance" @if($facility->status=='maintenance') selected @endif>Maintenance</option>
					</select>
				</div>
			</div>
			<div style="display:flex;flex-direction:column;gap:4px;">
				<label for="edit_image" style="font-weight:500;">Image</label>
				<input type="file" id="edit_image" name="image" accept="image/*" style="width:100%;border-radius:7px;border:1px solid #c3cbe5;padding:7px 10px;">
			</div>
			<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
				<button type="submit" class="btn" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:7px 18px;font-size:0.98rem;min-width:90px;cursor:pointer;">Save</button>
				<button type="button" class="btn" onclick="closeEditFacilityModal()" style="background:#f3f4f6;color:#222;font-weight:600;border:none;border-radius:7px;padding:7px 18px;font-size:0.98rem;min-width:90px;cursor:pointer;">Cancel</button>
			</div>
		</form>
	</div>
</div>

<script>
function openEditFacilityModal() {
	document.getElementById('editFacilityModal').style.display = 'flex';
}
function closeEditFacilityModal() {
	document.getElementById('editFacilityModal').style.display = 'none';
}
</script>
@extends('layouts.qc-admin')
@section('title', 'Facility Details')
@section('content')

<div class="facility-details-wrapper" style="max-width:600px;margin:0 auto;">
   <div style="background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
	   <div style="display:flex;align-items:center;gap:22px;margin-bottom:1.5rem;">
		   @if($facility->image)
			   <img src="{{ asset('storage/' . $facility->image) }}" alt="Facility" style="width:140px;height:100px;object-fit:cover;border-radius:10px;">
		   @else
			   <div style="width:140px;height:100px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2.2rem;">
				   <i class="fa fa-image" title="No Image"></i>
			   </div>
		   @endif
		   <div style="flex:1;">
			   <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
				   <div>
					   <h2 style="font-size:1.7rem;font-weight:700;color:#222;margin:0 0 8px 0;">{{ $facility->name ?? '-' }}</h2>
					   <div style="color:#6366f1;font-weight:500;margin-bottom:4px;">{{ $facility->type ?? '-' }}</div>
					   <div style="font-size:1rem;color:#555;">{{ $facility->department ?? '-' }}</div>
				   </div>
				   <a href="{{ url('/energy-actions?facility='.$facility->id) }}" title="Show Energy Actions"
					  class="energy-action-icon-link" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;text-decoration:none;position:relative;top:-10px;">
					   <span class="energy-action-icon" style="display:flex;align-items:center;justify-content:center;width:38px;height:38px;background:#2563eb1a;color:#2563eb;border-radius:50%;font-size:1.25rem;transition:background 0.18s, color 0.18s;">
						   <i class="fa fa-bolt"></i>
					   </span>
					   <span class="energy-action-label" style="font-size:0.93rem;color:#2563eb;font-weight:600;margin-top:2px;position:absolute;top:36px;left:50%;transform:translateX(-50%);background:#fff;padding:2px 10px;border-radius:6px;box-shadow:0 2px 8px rgba(55,98,200,0.10);opacity:0;pointer-events:none;transition:opacity 0.18s;white-space:nowrap;">Show Energy Action</span>
				   </a>
				   <style>
				   .energy-action-icon-link:hover .energy-action-label {
					   opacity: 1 !important;
				   }
				   </style>
			   </div>
		   </div>
	   </div>
	   <div style="margin-bottom:1.1rem;"><strong>Address:</strong> {{ $facility->address ?? '-' }}</div>
	   <div style="margin-bottom:1.1rem;"><strong>Barangay:</strong> {{ $facility->barangay ?? '-' }}</div>
	   <div style="display:flex;gap:18px;margin-bottom:1.1rem;">
		   <div><strong>Floor Area:</strong> {{ $facility->floor_area ?? '-' }} sqm</div>
		   <div><strong>Floors:</strong> {{ $facility->floors ?? '-' }}</div>
	   </div>
	   <div style="display:flex;gap:18px;margin-bottom:1.1rem;">
		   <div><strong>Year Built:</strong> {{ $facility->year_built ?? '-' }}</div>
		   <div><strong>Operating Hours:</strong> {{ $facility->operating_hours ?? '-' }}</div>
	   </div>
	   <div style="margin-bottom:1.1rem;"><strong>Status:</strong> <span style="color:#2563eb;font-weight:600;">{{ ucfirst($facility->status) ?? '-' }}</span></div>

	   <div style="margin: 24px 0 0 0; font-size:1.15rem;">
		   <strong>Average kWh (First 3 Months):</strong>
		   @if($showAvg)
			   <span style="color:#2563eb;font-weight:600;">{{ number_format($avgKwh, 2) }} kWh</span>
		   @else
			   <span style="color:#e11d48;font-weight:500;">Insufficient data (at least 3 months required)</span>
		   @endif
	   </div>

	   <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:2.2rem;">
		   <button type="button" onclick="openEditFacilityModal()" class="btn" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 22px;cursor:pointer;">Edit</button>
		   <button type="button" onclick="openDeleteFacilityModal({{ $facility->id }})" style="background:#e11d48;color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 22px;cursor:pointer;">Delete</button>
	   </div>
   </div>
</div>

<!-- Delete Facility Modal -->
<div id="deleteFacilityModal" class="modal" style="display:none;align-items:center;justify-content:center;z-index:9999;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);">
    <div class="modal-content" style="max-width:350px;background:#fff7f7;border-radius:18px;box-shadow:0 8px 32px rgba(225,29,72,0.13);padding:32px 28px;">
        <button class="modal-close" type="button" onclick="closeDeleteFacilityModal()" style="top:12px;right:12px;position:absolute;font-size:1.5rem;background:none;border:none;color:#e11d48;">&times;</button>
        <h2 style="margin-bottom:10px;font-size:1.3rem;font-weight:700;color:#e11d48;">Delete Facility</h2>
        <div style="font-size:0.98rem;color:#b91c1c;margin-bottom:18px;">Are you sure you want to delete this facility? This action cannot be undone.</div>
        <form id="deleteFacilityForm" method="POST" action="{{ route('facilities.destroy', $facility->id) }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="energy-modal-btn delete" style="padding:12px 0;border:none;border-radius:8px;font-weight:700;font-size:1.08rem;background:#e11d48;color:#fff;">Yes, Delete</button>
            <button type="button" class="energy-modal-btn cancel" onclick="closeDeleteFacilityModal()" style="padding:10px 0;border:none;border-radius:8px;font-weight:600;background:#f3f4f6;color:#222;">Cancel</button>
        </form>
    </div>
</div>
<script>
function openDeleteFacilityModal(id) {
    document.getElementById('deleteFacilityModal').style.display = 'flex';
}
function closeDeleteFacilityModal() {
    document.getElementById('deleteFacilityModal').style.display = 'none';
}
</script>

	
@endsection
