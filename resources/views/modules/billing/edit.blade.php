@extends('layouts.qc-admin')
@section('title', 'Edit Bill')
@section('content')
<div class="billing-edit-card" style="max-width:520px;margin:40px auto;background:#f5f8ff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;color:#3762c8;margin-bottom:18px;">Edit Bill</h2>
    <form method="POST" action="{{ route('modules.billing.update', $id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="facility_id" style="font-weight:600;margin-bottom:6px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" {{ $bill->facility_id == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="month" style="font-weight:600;margin-bottom:6px;">Month</label>
            <input type="month" name="month" id="month" class="form-control" value="{{ $bill->month }}" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="kwh_consumed" style="font-weight:600;margin-bottom:6px;">kWh Consumed</label>
            <input type="number" step="0.01" name="kwh_consumed" id="kwh_consumed" class="form-control" value="{{ $bill->kwh_consumed }}" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="unit_cost" style="font-weight:600;margin-bottom:6px;">Unit Cost (PHP)</label>
            <input type="number" step="0.01" name="unit_cost" id="unit_cost" class="form-control" value="12.50" readonly style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;background:#e5e7eb;" required>
        </div>
        <div class="mb-3" style="margin-bottom:24px;">
            <label for="status" style="font-weight:600;margin-bottom:6px;">Status</label>
            <select name="status" id="status" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                <option value="Paid" {{ $bill->status == 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Unpaid" {{ $bill->status == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="Pending" {{ $bill->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Overdue" {{ $bill->status == 'Overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:8px 28px;">Update</button>
            <a href="{{ route('modules.billing.index') }}" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>
@endsection
