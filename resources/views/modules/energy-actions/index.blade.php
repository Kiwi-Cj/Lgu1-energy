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
// File removed
@endsection
