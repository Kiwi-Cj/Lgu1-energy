@extends('layouts.qc-admin')
@section('title', 'Edit Energy Record')
@section('content')
<div class="energy-edit-card" style="max-width:520px;margin:40px auto;background:#f5f8ff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;color:#3762c8;margin-bottom:18px;">Edit Energy Record</h2>
    <form method="POST" action="{{ route('modules.energy.update', $usage->id) }}">
        <input type="hidden" name="facility_id_filter" value="{{ request('facility_id') }}">
        <input type="hidden" name="month_filter" value="{{ request('month') }}">
        <input type="hidden" name="year_filter" value="{{ request('year') }}">
        @csrf
        @method('PUT')
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="facility_id" style="font-weight:600;margin-bottom:6px;">Facility</label>
            <select name="facility_id" id="facility_id" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if($usage->facility_id == $facility->id) selected @endif>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3" style="margin-bottom:18px;display:flex;gap:12px;">
            <div style="flex:1;">
                <label for="month" style="font-weight:600;margin-bottom:6px;">Month</label>
                <select name="month" id="month" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                    @foreach(range(1,12) as $m)
                        <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}" @if($usage->month == str_pad($m,2,'0',STR_PAD_LEFT)) selected @endif>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;">
                <label for="year" style="font-weight:600;margin-bottom:6px;">Year</label>
                <select name="year" id="year" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
                    @foreach(range(date('Y'), date('Y')-10) as $y)
                        <option value="{{ $y }}" @if($usage->year == $y) selected @endif>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mb-3" style="margin-bottom:18px;">
            <label for="kwh_consumed" style="font-weight:600;margin-bottom:6px;">kWh Consumed</label>
            <input type="number" step="0.01" name="kwh_consumed" id="kwh_consumed" class="form-control" value="{{ $usage->kwh_consumed }}" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;" required>
        </div>
        <div class="mb-3" style="margin-bottom:24px;">
            <label for="status" style="font-weight:600;margin-bottom:6px;">Status</label>
            <select name="status" id="status" class="form-control" style="padding:7px 12px;border-radius:7px;border:1px solid #c3cbe5;">
                <option value="High" @if($usage->status == 'High') selected @endif>High</option>
                <option value="Normal" @if($usage->status == 'Normal') selected @endif>Normal</option>
            </select>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(90deg,#2563eb,#6366f1);color:#fff;font-weight:600;border:none;border-radius:7px;padding:8px 28px;">Update</button>
            <a href="{{ route('modules.energy.index') }}" class="btn btn-secondary" style="background:#e5e7eb;color:#222;font-weight:600;border:none;border-radius:7px;padding:8px 22px;text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>
@endsection
