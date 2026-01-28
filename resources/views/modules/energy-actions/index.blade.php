@extends('layouts.qc-admin')
@section('title', 'Energy Actions')
@section('content')
@php
    $facilityId = request('facility');
    $filteredActions = $facilityId ? $actions->where('facility_id', $facilityId) : $actions;
    $facilityName = null;
    if ($facilityId && $filteredActions->count() > 0) {
        $facilityName = $filteredActions->first()->facility->name ?? null;
    }
@endphp
<div style="max-width:900px;margin:32px auto 0 auto;background:#fff;padding:32px 28px 24px 28px;border-radius:16px;box-shadow:0 2px 12px rgba(55,98,200,0.10);">
    <h2 style="font-weight:700;font-size:2rem;color:#3762c8;margin-bottom:1.5rem;">Energy Actions
        @if($facilityName)
            <span style="font-size:1.1rem;font-weight:500;color:#2563eb;">for {{ $facilityName }}</span>
        @endif
    </h2>
    <table class="table" style="width:100%;border-collapse:collapse;font-size:0.97rem;">
        <thead style="background:#f1f5f9;">
            <tr style="text-align:center;">
                <th style="padding:8px 10px;">Facility</th>
                <th style="padding:8px 10px;">Action Type</th>
                <th style="padding:8px 10px;">Description</th>
                <th style="padding:8px 10px;">Priority</th>
                <th style="padding:8px 10px;">Target Date</th>
                <th style="padding:8px 10px;">Status</th>
                <th style="padding:8px 10px;">Created</th>
            </tr>
        </thead>
        <tbody>
        @forelse($filteredActions as $action)
            <tr style="border-bottom:1px solid #e5e7eb;text-align:center;">
                <td style="padding:8px 10px;">{{ $action->facility->name ?? '-' }}</td>
                <td style="padding:8px 10px;">{{ $action->action_type }}</td>
                <td style="padding:8px 10px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $action->description }}</td>
                <td style="padding:8px 10px;">{{ $action->priority }}</td>
                <td style="padding:8px 10px;">{{ $action->target_date }}</td>
                <td style="padding:8px 10px;">{{ $action->status }}</td>
                <td style="padding:8px 10px;">{{ $action->created_at->format('Y-m-d') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="padding:18px;text-align:center;">No energy actions found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
