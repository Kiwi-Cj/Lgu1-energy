@extends('layouts.qc-admin')
@section('title', 'Edit Facility')
@section('content')
<div class="facility-edit-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:70vh;width:100%;">
	<div style="width:100%;max-width:520px;background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
		<h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;text-align:center;">Edit Facility</h2>
		<form action="{{ route('facilities.update', $facility->id) }}" method="POST" enctype="multipart/form-data">
			@csrf
			@method('PUT')
			<div style="margin-bottom:1.2rem;">
				<label for="image" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Image</label>
				<input type="file" name="image" id="image" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				@if($facility->image)
					<div style="margin-top:8px;"><img src="{{ asset('storage/' . $facility->image) }}" alt="Current Image" style="width:100%;max-width:180px;border-radius:8px;"></div>
				@endif
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="name" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Name</label>
				<input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $facility->name) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="type" style="font-weight:500;display:block;margin-bottom:0.4rem;">Type</label>
				<select name="type" id="type" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Type</option>
					<option value="Market" {{ old('type', $facility->type)=='Market' ? 'selected' : '' }}>Market</option>
					<option value="Office" {{ old('type', $facility->type)=='Office' ? 'selected' : '' }}>Office</option>
					<option value="Warehouse" {{ old('type', $facility->type)=='Warehouse' ? 'selected' : '' }}>Warehouse</option>
					<option value="School" {{ old('type', $facility->type)=='School' ? 'selected' : '' }}>School</option>
					<option value="Hospital" {{ old('type', $facility->type)=='Hospital' ? 'selected' : '' }}>Hospital</option>
					<option value="Other" {{ old('type', $facility->type)=='Other' ? 'selected' : '' }}>Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="department" style="font-weight:500;display:block;margin-bottom:0.4rem;">Department</label>
				<select name="department" id="department" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Department</option>
					<option value="General Services" {{ old('department', $facility->department)=='General Services' ? 'selected' : '' }}>General Services</option>
					<option value="Engineering" {{ old('department', $facility->department)=='Engineering' ? 'selected' : '' }}>Engineering</option>
					<option value="Health" {{ old('department', $facility->department)=='Health' ? 'selected' : '' }}>Health</option>
					<option value="Education" {{ old('department', $facility->department)=='Education' ? 'selected' : '' }}>Education</option>
					<option value="Other" {{ old('department', $facility->department)=='Other' ? 'selected' : '' }}>Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="address" style="font-weight:500;display:block;margin-bottom:0.4rem;">Address</label>
				<input type="text" name="address" id="address" class="form-control" required value="{{ old('address', $facility->address) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="barangay" style="font-weight:500;display:block;margin-bottom:0.4rem;">Barangay</label>
				<input type="text" name="barangay" id="barangay" class="form-control" required value="{{ old('barangay', $facility->barangay) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="floor_area" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floor Area (sqm)</label>
					<input type="number" name="floor_area" id="floor_area" class="form-control" value="{{ old('floor_area', $facility->floor_area) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="floors" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floors</label>
					<input type="number" name="floors" id="floors" class="form-control" value="{{ old('floors', $facility->floors) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="year_built" style="font-weight:500;display:block;margin-bottom:0.4rem;">Year Built</label>
					<input type="number" name="year_built" id="year_built" class="form-control" value="{{ old('year_built', $facility->year_built) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="operating_hours" style="font-weight:500;display:block;margin-bottom:0.4rem;">Operating Hours</label>
					<input type="text" name="operating_hours" id="operating_hours" class="form-control" value="{{ old('operating_hours', $facility->operating_hours) }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="status" style="font-weight:500;display:block;margin-bottom:0.4rem;">Status</label>
				<select name="status" id="status" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="active" {{ old('status', $facility->status)=='active' ? 'selected' : '' }}>Active</option>
					<option value="inactive" {{ old('status', $facility->status)=='inactive' ? 'selected' : '' }}>Inactive</option>
					<option value="maintenance" {{ old('status', $facility->status)=='maintenance' ? 'selected' : '' }}>Maintenance</option>
				</select>
			</div>
			<div style="display:flex;justify-content:flex-end;gap:12px;">
				<a href="{{ route('facilities.index') }}" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Cancel</a>
				<button type="submit" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);cursor:pointer;">Update Facility</button>
			</div>
		</form>
	</div>
</div>
@endsection
