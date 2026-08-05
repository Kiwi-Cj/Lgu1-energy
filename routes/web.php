<?php

use App\Http\Controllers\Modules\EnergyMonitoringController;
use App\Http\Controllers\Modules\IntegrationController;
use App\Http\Controllers\Modules\MaintenanceController;
use App\Http\Controllers\Modules\MonthlyRecordActivityController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DownloadAuthorizationController;
use App\Http\Controllers\NotificationController;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\Route;

Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('auth')->name('notifications.markAllRead');
Route::get('/notifications', [NotificationController::class, 'index'])->middleware(['auth', 'verified'])->name('notifications.index');
Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->middleware('auth')->name('notifications.markRead');
Route::post('/notifications/{notification}/acknowledge', [NotificationController::class, 'acknowledge'])->middleware('auth')->name('notifications.acknowledge');
Route::post('/downloads/authorize', [DownloadAuthorizationController::class, 'authorize'])->middleware(['auth', 'verified'])->name('downloads.authorize');

// Backward compatibility: allow GET /modules/settings/index to show settings page
Route::get('/modules/settings/index', function () {
    if (! auth()->check() || ! RoleAccess::can(auth()->user(), 'access_settings')) {
        return redirect()->route('modules.energy-monitoring.index')
            ->with('error', 'You do not have permission to access System Settings.');
    }
    return app(\App\Http\Controllers\Modules\SettingsController::class)->index();
})->name('modules.settings.index');

// Restored for sidebar compatibility: energy.dashboard now points to energy-monitoring index (controller, so $facilities is set)
Route::get('/modules/energy-monitoring/index', [EnergyMonitoringController::class, 'index'])->name('energy.dashboard');

// Users & Roles Management - Admin/Energy Officer only
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'index'])->name('users.index');
    Route::get('modules/users/index', [\App\Http\Controllers\Modules\UsersController::class, 'index']);
    Route::post('modules/users', [\App\Http\Controllers\Modules\UsersController::class, 'store'])->name('users.store');
    Route::get('modules/users/{id}/edit', [\App\Http\Controllers\Modules\UsersController::class, 'edit'])->name('users.edit');
    Route::put('modules/users/{id}', [\App\Http\Controllers\Modules\UsersController::class, 'update'])->name('users.update');
    Route::patch('modules/users/{id}/status', [\App\Http\Controllers\Modules\UsersController::class, 'updateStatus'])->name('users.status.update');
    Route::get('/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'roles'])->name('users.roles');
    Route::post('/users/roles', [\App\Http\Controllers\Modules\UsersController::class, 'storeRole'])->name('users.roles.store');
    Route::delete('/users/roles/{role}', [\App\Http\Controllers\Modules\UsersController::class, 'destroyRole'])->name('users.roles.destroy');
});

// ============================================================
// LANDING PAGES (Public)
// ============================================================
Route::get('/', function () {
    return view('welcome');
});

Route::view('/features', 'landing.features')->name('landing.features');
Route::view('/testimonials', 'landing.testimonials')->name('landing.testimonials');
Route::view('/contact', 'landing.contact')->name('landing.contact');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('landing.contact.store');

// ============================================================
// ABOUT, FAQS, PRIVACY NOTICE (Public Pages)
// ============================================================
Route::view('/about', 'pages.about')->name('about.index');
Route::view('/faqs', 'pages.faqs')->name('faqs.index');
Route::view('/privacy', 'pages.privacy')->name('privacy.index');

// Maintenance history contains operational details and must not be public.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/modules/maintenance/history', [MaintenanceController::class, 'history'])->name('maintenance.history');
    Route::delete('/modules/maintenance/history/{id}', [MaintenanceController::class, 'destroyHistory'])->name('modules.maintenance.history.destroy');
});

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

// SSO — receives signed tokens from Main LGU (infragovservices.com hub)
Route::get('/sso/consume', [\App\Http\Controllers\SsoConsumeController::class, 'consume'])->name('sso.consume');

// Read-only headline metric for the Main LGU SSO hub dashboard
Route::get('/api/stats', [\App\Http\Controllers\StatsController::class, 'index'])->name('api.stats');

// Public welcome page route
Route::get('/modules/dashboard/index', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Energy Monitoring Dashboard (Controller-based, for dynamic cards)
Route::get('/modules/energy-monitoring', [EnergyMonitoringController::class, 'index'])->name('modules.energy-monitoring.index');
Route::get('/modules/energy-monitoring/{facility}/ai-recommendation', [EnergyMonitoringController::class, 'aiRecommendation'])
    ->middleware(['auth', 'verified'])
    ->name('modules.energy-monitoring.ai-recommendation');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/modules/monthly-record-activity', [MonthlyRecordActivityController::class, 'index'])
        ->name('monthly-record-activity.index');
    Route::patch('/modules/monthly-record-activity/{record}/review', [MonthlyRecordActivityController::class, 'review'])
        ->name('monthly-record-activity.review');

    // System Settings route for dashboard shortcut - Super Admin only
    Route::get('/modules/settings', [\App\Http\Controllers\Modules\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/modules/settings', [\App\Http\Controllers\Modules\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/modules/settings/test-email', [\App\Http\Controllers\Modules\SettingsController::class, 'testEmail'])->name('settings.test-email');
    Route::get('/modules/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
});

require __DIR__ . '/modules.php';
