@extends('layouts.qc-admin')

@php
    // Ensure notifications and unreadNotifCount are available for the notification bell
    $user = auth()->user();
    $notifications = $notifications ?? ($user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect());
    $unreadNotifCount = $unreadNotifCount ?? ($user ? $user->notifications()->whereNull('read_at')->count() : 0);
@endphp

@section('content')
<div class="container" style="padding:2.5rem 0;max-width:900px;">
    <!-- Header -->
    <div style="margin-bottom:2.5rem;display:flex;align-items:center;gap:1.2rem;">
        <div style="background:#e0e7ff;padding:1.1rem 1.3rem;border-radius:14px;display:flex;align-items:center;">
            <i class="fa-solid fa-file-arrow-down" style="font-size:2.2rem;color:#3762c8;"></i>
        </div>
        <div>
            <h1 style="font-size:2.2rem;font-weight:800;color:#3762c8;margin-bottom:4px;letter-spacing:0.5px;">
                Export COA Energy Report
            </h1>
            <p style="color:#555;font-size:1.08rem;max-width:600px;">
                Generate and download official energy monitoring reports for <b>COA</b> submission and internal analysis. Use the filters below to customize your export.
            </p>
        </div>
    </div>

    <!-- Export Card -->
    <div style="background:linear-gradient(120deg,#f5f8ff 60%,#e0e7ff 100%);border-radius:20px;padding:2.5rem 2rem 2rem 2rem;box-shadow:0 8px 32px rgba(55,98,200,0.10);">
        <!-- Filters -->
        <form method="GET" action="{{ url('/modules/energy/export-excel') }}">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;align-items:end;">
                <div>
                    <label style="font-weight:700;color:#3762c8;letter-spacing:0.2px;margin-bottom:0.4rem;display:block;">From Date</label>
                    <input type="date" name="from_date" class="form-control" style="border-radius:8px;border:1.5px solid #c7d2fe;padding:10px 14px;font-size:1.05rem;" />
                </div>
                <div>
                    <label style="font-weight:700;color:#3762c8;letter-spacing:0.2px;margin-bottom:0.4rem;display:block;">To Date</label>
                    <input type="date" name="to_date" class="form-control" style="border-radius:8px;border:1.5px solid #c7d2fe;padding:10px 14px;font-size:1.05rem;" />
                </div>
                <div>
                    <label style="font-weight:700;color:#3762c8;letter-spacing:0.2px;margin-bottom:0.4rem;display:block;">Facility</label>
                    <select name="facility_id" class="form-control" style="border-radius:8px;border:1.5px solid #c7d2fe;padding:10px 14px;font-size:1.05rem;">
                        <option value="">All Facilities</option>
                        @if(isset($facilities) && count($facilities))
                            @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div style="margin-top:1.2rem;display:flex;gap:10px;align-items:center;">
                <a href="#" onclick="exportCOAPdf(this);return false;" class="btn btn-danger" style="padding:10px 22px;border-radius:8px;font-weight:600;font-size:1.05rem;box-shadow:0 2px 8px #e11d480a;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </a>
                <button type="submit" class="btn btn-success" style="padding:10px 22px;border-radius:8px;font-weight:600;font-size:1.05rem;box-shadow:0 2px 8px #22c55e0a;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-file-excel"></i> Download Excel
                </button>
            </div>
            <script>
            function exportCOAPdf(btn) {
                const form = btn.closest('form');
                const from = form.querySelector('[name=from_date]').value;
                const to = form.querySelector('[name=to_date]').value;
                const facility = form.querySelector('[name=facility_id]').value;
                let url = `{{ url('/modules/energy/export-pdf') }}?from_date=${from}&to_date=${to}`;
                if (facility) url += `&facility_id=${facility}`;
                window.location.href = url;
            }
            </script>
        </form>
    </div>

    <!-- Info Section -->
    <div style="margin-top:2.2rem;background:#f8fafc;border-left:5px solid #3762c8;padding:1.3rem 1.5rem;border-radius:12px;box-shadow:0 2px 8px #3762c81a;">
        <p style="margin:0;color:#444;font-size:1.01rem;">
            <i class="fa-solid fa-circle-info" style="color:#3762c8;margin-right:7px;"></i>
            <strong>Note:</strong> This report is system-generated based on recorded energy consumption data and is intended for 
            <strong>Commission on Audit (COA)</strong> review and official documentation.
        </p>
    </div>
</div>
@endsection
