@extends('layouts.qc-admin')
@section('title', 'Energy Trend')
@section('content')

<h2 style="font-size:2rem;font-weight:700;color:#222;margin-bottom:1.5rem;">Energy Consumption Trend</h2>

@include('modules.energy-monitoring.partials.charts', ['chartData' => $trendData])

@endsection
