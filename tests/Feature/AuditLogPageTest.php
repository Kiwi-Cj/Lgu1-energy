<?php

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('authorized administrator can review enhanced audit log details', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    AuditLog::create([
        'user_id' => $admin->id,
        'role' => 'super_admin',
        'action' => 'facility.updated',
        'module' => 'Facilities',
        'description' => 'Updated Bernardo Court energy setup.',
        'method' => 'PATCH',
        'route_name' => 'facilities.update',
        'path' => '/facilities/40',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Audit Test Browser',
        'metadata' => ['facility_id' => 40],
    ]);

    $this->actingAs($admin)
        ->get(route('modules.audit.index', ['scope' => 'all']))
        ->assertOk()
        ->assertSee('Audit Trail')
        ->assertSee('Updated Facilities')
        ->assertSee('facility.updated')
        ->assertSee('Updated Bernardo Court energy setup.')
        ->assertSee('Audit Test Browser')
        ->assertSee('facility_id');
});

test('audit search supports route names and IP addresses', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    AuditLog::create([
        'user_id' => $admin->id,
        'role' => 'super_admin',
        'action' => 'settings.updated',
        'module' => 'Settings',
        'method' => 'PATCH',
        'route_name' => 'settings.update',
        'path' => '/modules/settings',
        'ip_address' => '10.20.30.40',
    ]);

    $this->actingAs($admin)
        ->get(route('modules.audit.index', ['scope' => 'all', 'q' => '10.20.30.40']))
        ->assertOk()
        ->assertSee('settings.updated');
});

test('successful changes are recorded with useful context and protected secrets', function () {
    Setting::setValue('enable_audit_logs', '1');
    $admin = User::factory()->create(['role' => 'super_admin']);

    Route::post('/_audit-flow-test/facilities/{facility}', fn () => response()->noContent())
        ->name('modules.facilities.update-flow-test');

    $this->actingAs($admin)
        ->post('/_audit-flow-test/facilities/40', [
            'facility_name' => 'Bernardo Court',
            'status' => 'active',
            'password' => 'must-never-be-stored',
            'otp_code' => '123456',
        ])
        ->assertNoContent();

    $log = AuditLog::where('route_name', 'modules.facilities.update-flow-test')->latest()->firstOrFail();

    expect($log->description)->toContain('Updated Facilities')
        ->and($log->metadata['route_parameters'])->toBe(['facility' => '40'])
        ->and($log->metadata['changed_fields'])->toMatchArray([
            'facility_name' => 'Bernardo Court',
            'status' => 'active',
        ])
        ->and($log->metadata['changed_fields'])->not->toHaveKeys(['password', 'otp_code'])
        ->and($log->metadata['response_status'])->toBe(204);
});
