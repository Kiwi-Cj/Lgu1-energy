@extends('layouts.qc-admin')
@section('title', 'Energy Report')

@php
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

<style>
    /* Desktop & Base Styles */
    .report-card {
        background: #fff; 
        border-radius: 18px; 
        box-shadow: 0 2px 8px rgba(31,38,135,0.08); 
        margin-bottom: 1.2rem; 
        padding: 24px;
    }
    .energy-report-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }
    .energy-report-buttons {
        display: flex; 
        gap: 12px;
    }
    .btn-export {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        transition: 0.2s;
    }
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px; /* Base width for desktop */
    }
    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #c3cbe5;
        border-radius: 8px;
    }

    /* Mobile Responsive Fixes */
    @media (max-width: 768px) {
        .report-card { padding: 15px; }
        
        .energy-report-header {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }

        .energy-report-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-export {
            padding: 10px 5px;
            font-size: 0.9rem;
            justify-content: center;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            min-width: 100% !important; /* Force full width to prevent overlap */
            width: 100%;
        }

        .btn-filter {
            width: 100%;
            margin-top: 10px;
        }

        /* Table container ensures internal content doesn't break parent */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        table thead th {
            font-size: 0.8rem !important;
            padding: 10px 5px !important;
        }

        table tbody td {
            font-size: 0.85rem !important;
            padding: 10px 5px !important;
            white-space: nowrap;
        }
    }
</style>

@section('content')
<div style="width:100%; margin:0 auto;">
    <div class="report-card">
        <div class="energy-report-header">
            <div>
                <h2 style="font-size:1.8rem; font-weight:700; color:#3762c8; margin:0;">📘 Energy Report</h2>
                <p style="color:#555; margin-top:4px;">Facility consumption and trends.</p>
            </div>
            <div class="energy-report-buttons">
                <a href="{{ route('reports.energy-export', request()->all()) }}" class="btn-export" style="background:#22c55e;">
                    <i class="fa fa-download"></i> Export
                </a>
                <a href="{{ route('modules.energy.export-pdf', array_filter(request()->all())) }}" class="btn-export" style="background:#e11d48;">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </a>
            </div>
        </div>

        <form method="GET" action="" class="filter-form">
            <div class="filter-group">
                <label style="font-weight:700; margin-bottom:5px;">Facility</label>
                <select name="facility_id" class="form-control">
                    <option value="">All Facilities</option>
                    @foreach($facilities ?? [] as $facility)
                        <option value="{{ $facility->id }}" {{ (request('facility_id') == $facility->id) ? 'selected' : '' }}>{{ $facility->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" style="flex: 0.5; min-width: 100px;">
                <label style="font-weight:700; margin-bottom:5px;">Year</label>
                <select name="year" class="form-control">
                    @foreach($years ?? [] as $year)
                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" style="flex: 0.5; min-width: 100px;">
                <label style="font-weight:700; margin-bottom:5px;">Month</label>
                <select name="month" class="form-control">
                    <option value="">All Months</option>
                    @php $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec']; @endphp
                    @foreach($months as $num=>$name)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-filter" style="padding:10px 25px; border-radius:8px; background:linear-gradient(90deg,#2563eb,#6366f1); border:none; font-weight:600; color:#fff;">
                Filter
            </button>
        </form>

        <div class="table-responsive">
            <table style="width:100%; border-collapse:collapse; background:#fff;">
                <thead style="background:#e9effc;">
                    <tr>
                        <th style="padding:14px; text-align:left; color:#3762c8; border-bottom:2px solid #c3cbe5;">Facility</th>
                        <th style="padding:14px; text-align:left; color:#3762c8; border-bottom:2px solid #c3cbe5;">Month</th>
                        <th style="padding:14px; text-align:right; color:#3762c8; border-bottom:2px solid #c3cbe5;">Actual</th>
                        <th style="padding:14px; text-align:right; color:#3762c8; border-bottom:2px solid #c3cbe5;">Baseline</th>
                        <th style="padding:14px; text-align:right; color:#3762c8; border-bottom:2px solid #c3cbe5;">Var</th>
                        <th style="padding:14px; text-align:center; color:#3762c8; border-bottom:2px solid #c3cbe5;">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($energyRows ?? [] as $row)
                    <tr style="background:{{ $loop->even ? '#f8fafc' : '#fff' }}; border-bottom:1px solid #eee;">
                        <td style="padding:12px;">{{ $row['facility'] }}</td>
                        <td style="padding:12px;">{{ $row['month'] }}</td>
                        <td style="padding:12px; text-align:right;">{{ $row['actual_kwh'] }}</td>
                        <td style="padding:12px; text-align:right;">{{ $row['baseline_kwh'] }}</td>
                        <td style="padding:12px; text-align:right;">{{ $row['variance'] }}</td>
                        <td style="padding:12px; text-align:center;">
                            @if($row['trend'] === 'up')
                                <span style="color:#dc2626;">↑</span>
                            @elseif($row['trend'] === 'down')
                                <span style="color:#16a34a;">↓</span>
                            @else
                                <span style="color:#6b7280;">→</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:30px; text-align:center; color:#6b7280;">No records.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection