@extends('layouts.qc-admin')
@section('title', 'Add Facility')
@section('content')
<div class="facility-create-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:70vh;width:100%;">
	<div style="width:100%;max-width:520px;background:#fff;padding:38px 28px 32px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(31,38,135,0.13);">
		<h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;text-align:center;">Add Facility</h2>
		<form action="{{ route('facilities.store') }}" method="POST" enctype="multipart/form-data">
		@csrf
			<div style="margin-bottom:1.2rem;">
				<label for="image" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Image</label>
				<input type="file" name="image" id="image" accept="image/*" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="name" style="font-weight:500;display:block;margin-bottom:0.4rem;">Facility Name</label>
				<input type="text" name="name" id="name" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="type" style="font-weight:500;display:block;margin-bottom:0.4rem;">Type</label>
				<select name="type" id="type" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Type</option>
					<option value="Market">Market</option>
					<option value="Office">Office</option>
					<option value="Warehouse">Warehouse</option>
					<option value="School">School</option>
					<option value="Hospital">Hospital</option>
					<option value="Other">Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="department" style="font-weight:500;display:block;margin-bottom:0.4rem;">Department</label>
				<select name="department" id="department" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="">Select Department</option>
					<option value="General Services">General Services</option>
					<option value="Engineering">Engineering</option>
					<option value="Health">Health</option>
					<option value="Education">Education</option>
					<option value="Other">Other</option>
				</select>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="address" style="font-weight:500;display:block;margin-bottom:0.4rem;">Address</label>
				<input type="text" name="address" id="address" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="barangay" style="font-weight:500;display:block;margin-bottom:0.4rem;">Barangay</label>
				<input type="text" name="barangay" id="barangay" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="floor_area" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floor Area (sqm)</label>
					<input type="number" name="floor_area" id="floor_area" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="floors" style="font-weight:500;display:block;margin-bottom:0.4rem;">Floors</label>
					<input type="number" name="floors" id="floors" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;display:flex;gap:12px;">
				<div style="flex:1;">
					<label for="year_built" style="font-weight:500;display:block;margin-bottom:0.4rem;">Year Built</label>
					<input type="number" name="year_built" id="year_built" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
				<div style="flex:1;">
					<label for="operating_hours" style="font-weight:500;display:block;margin-bottom:0.4rem;">Operating Hours</label>
					<input type="text" name="operating_hours" id="operating_hours" class="form-control" style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
				</div>
			</div>
			<div style="margin-bottom:1.2rem;">
				<label for="status" style="font-weight:500;display:block;margin-bottom:0.4rem;">Status</label>
				<select name="status" id="status" class="form-control" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;">
					<option value="active">Active</option>
					<option value="inactive">Inactive</option>
					<option value="maintenance">Maintenance</option>
				</select>
			</div>
			<div style="display:flex;justify-content:flex-end;gap:12px;">
				<a href="{{ route('facilities.index') }}" style="background:#f3f4f6;color:#222;font-weight:500;border:none;border-radius:8px;padding:10px 22px;text-decoration:none;">Cancel</a>
				<button type="submit" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;box-shadow:0 2px 8px rgba(31,38,135,0.10);cursor:pointer;">Save Facility</button>
			</div>
		</form>
	</div>
</div>
@endsection
