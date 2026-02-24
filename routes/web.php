<?php

use App\Http\Controllers\Modules\EnergyMonitoringController;
use App\Http\Controllers\Modules\MaintenanceController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\NotificationController;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Route;

Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('auth')->name('notifications.markAllRead');
Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->middleware('auth')->name('notifications.markRead');

// Backward compatibility: allow GET /modules/settings/index to show settings page
Route::get('/modules/settings/index', function () {
    if (! auth()->check() || ! RoleAccess::can(auth()->user(), 'access_settings')) {
        return redirect()->route('modules.energy.index')
            ->with('error', 'You do not have permission to access System Settings.');
    }
    return app(\App\Http\Controllers\Modules\SettingsController::class)->index();
})->name('modules.settings.index');

// Restored for sidebar compatibility: energy.dashboard now points to energy-monitoring index (controller, so $facilities is set)
Route::get('/modules/energy-monitoring/index', [EnergyMonitoringController::class, 'index'])->name('energy.dashboard');

// OTP routes (should NOT be inside auth middleware)
Route::get('/otp/request', [\App\Http\Controllers\OtpController::class, 'showRequestForm'])->name('otp.request');
Route::post('/otp/send', [\App\Http\Controllers\OtpController::class, 'sendOtp'])->name('otp.send');
Route::get('/otp/verify', [\App\Http\Controllers\OtpController::class, 'showVerifyForm'])->name('otp.verify');
Route::post('/otp/verify', [\App\Http\Controllers\OtpController::class, 'verifyOtp'])->name('otp.verify.submit');
Route::post('/otp/resend', [\App\Http\Controllers\OtpController::class, 'resendOtp'])->name('otp.resend');

// Users & Roles Management - Admin/Energy Officer only
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'index'])->name('users.index');
    Route::get('modules/users/index', [\App\Http\Controllers\Modules\UsersController::class, 'index']);
    Route::post('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'store'])->name('users.store');
    Route::get('modules/users/{id}/edit', [\App\Http\Controllers\Modules\UsersController::class, 'edit'])->name('users.edit');
    Route::put('modules/users/{id}', [\App\Http\Controllers\Modules\UsersController::class, 'update'])->name('users.update');
    Route::get('modules/users/disable/{id}', [\App\Http\Controllers\Modules\UsersController::class, 'disable'])->name('users.disable');
    Route::get('/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'roles'])->name('users.roles');
});

Route::get('/', function () {
    return view('welcome');
});

Route::view('/features', 'landing.features')->name('landing.features');
Route::view('/testimonials', 'landing.testimonials')->name('landing.testimonials');
Route::view('/contact', 'landing.contact')->name('landing.contact');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('landing.contact.store');

// Maintenance History Route
Route::get('/modules/maintenance/history', [MaintenanceController::class, 'history'])->name('maintenance.history');
Route::delete('/modules/maintenance/history/{id}', [MaintenanceController::class, 'destroyHistory'])->name('modules.maintenance.history.destroy');

// Include authentication routes (login, register, etc.)
require __DIR__ . '/auth.php';
// Include reports routes
require __DIR__ . '/reports.php';
// Include energy incidents routes
require __DIR__ . '/energy-incidents.php';
// Modular route groups
require __DIR__ . '/profile.php';
require __DIR__ . '/facilities.php';
require __DIR__ . '/energy.php';

// Public welcome page route
Route::get('/modules/dashboard/index', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Energy Monitoring Dashboard (Controller-based, for dynamic cards)
Route::get('/modules/energy-monitoring', [EnergyMonitoringController::class, 'index'])->name('modules.energy-monitoring.index');

Route::middleware(['auth', 'verified'])->group(function () {
    // System Settings route for dashboard shortcut - Super Admin only
    Route::get('/modules/settings', [\App\Http\Controllers\Modules\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/modules/settings', [\App\Http\Controllers\Modules\SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__ . '/modules.php';

