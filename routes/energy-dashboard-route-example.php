<?php
use Illuminate\Support\Facades\Route;
use App\Models\Facility;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/modules/energy/dashboard', function() {
        $facilities = \App\Models\Facility::all();
        $userRole = strtolower(auth()->user()?->role ?? '');
        return view('modules.energy-monitoring.index', compact('facilities', 'userRole'));
    })->name('energy.dashboard');

    // ...existing code...
});
