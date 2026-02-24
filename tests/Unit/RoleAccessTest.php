<?php

use App\Models\User;
use App\Support\RoleAccess;

it('normalizes role labels into stable keys', function () {
    expect(RoleAccess::normalize('Super Admin'))->toBe('super_admin')
        ->and(RoleAccess::normalize('energy officer'))->toBe('energy_officer')
        ->and(RoleAccess::normalize('energy-officer'))->toBe('energy_officer')
        ->and(RoleAccess::normalize('  staff  '))->toBe('staff');
});

it('matches roles using normalized comparisons', function () {
    $user = new User(['role' => 'Energy Officer']);

    expect(RoleAccess::is($user, 'energy_officer'))->toBeTrue()
        ->and(RoleAccess::in($user, ['staff', 'energy officer']))->toBeTrue()
        ->and(RoleAccess::in($user, ['staff', 'admin']))->toBeFalse();
});

it('checks ability access from centralized config', function () {
    $staff = new User(['role' => 'staff']);
    $officer = new User(['role' => 'energy officer']);
    $admin = new User(['role' => 'admin']);
    $superAdmin = new User(['role' => 'super admin']);

    expect(RoleAccess::can($staff, 'manage_energy_profile'))->toBeFalse()
        ->and(RoleAccess::can($officer, 'manage_energy_profile'))->toBeTrue()
        ->and(RoleAccess::can($officer, 'delete_energy_profile'))->toBeFalse()
        ->and(RoleAccess::can($admin, 'delete_energy_profile'))->toBeTrue()
        ->and(RoleAccess::can($admin, 'access_settings'))->toBeFalse()
        ->and(RoleAccess::can($superAdmin, 'access_settings'))->toBeTrue()
        ->and(RoleAccess::can($staff, 'access_reports'))->toBeTrue();
});

it('exposes normalized role_key accessor on user model', function () {
    $user = new User(['role' => 'Super Admin']);

    expect($user->role_key)->toBe('super_admin');
});
