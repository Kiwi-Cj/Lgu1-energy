<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserRole;

class RoleAccess
{
    /** @var array<string, array<int, string>|null> */
    private static array $customPermissionsCache = [];

    public static function normalize(null|string|User $roleOrUser): string
    {
        if ($roleOrUser instanceof User) {
            $roleOrUser = $roleOrUser->role ?? '';
        }

        $role = strtolower(trim((string) $roleOrUser));
        if ($role === '') {
            return '';
        }

        $role = preg_replace('/[\s-]+/', '_', $role) ?? $role;
        return $role;
    }

    public static function is(null|string|User $roleOrUser, string $role): bool
    {
        return static::normalize($roleOrUser) === static::normalize($role);
    }

    /**
     * @param  array<int, string>  $roles
     */
    public static function in(null|string|User $roleOrUser, array $roles): bool
    {
        $normalized = static::normalize($roleOrUser);
        if ($normalized === '') {
            return false;
        }

        foreach ($roles as $role) {
            if ($normalized === static::normalize($role)) {
                return true;
            }
        }

        return false;
    }

    public static function can(?User $user, string $ability): bool
    {
        if (! $user) {
            return false;
        }

        $role = static::normalize($user);
        if ($role === '') {
            return false;
        }

        $customPermissions = static::customRolePermissions($role);
        if ($customPermissions !== null) {
            return in_array($ability, $customPermissions, true);
        }

        $allowedRoles = static::abilityRoles($ability);
        if (! is_array($allowedRoles)) {
            return false;
        }

        return static::in($user, $allowedRoles);
    }

    /**
     * Return null when the role is not a database-backed custom role.
     *
     * @return array<int, string>|null
     */
    private static function customRolePermissions(string $role): ?array
    {
        if (array_key_exists($role, static::$customPermissionsCache)) {
            return static::$customPermissionsCache[$role];
        }

        try {
            $customRole = UserRole::query()->where('slug', $role)->first(['permissions']);
        } catch (\Throwable $e) {
            // Keep config-backed roles usable while migrations/database are unavailable.
            return null;
        }

        if (! $customRole) {
            return static::$customPermissionsCache[$role] = null;
        }

        $permissions = $customRole->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        if (! is_array($permissions)) {
            return static::$customPermissionsCache[$role] = [];
        }

        return static::$customPermissionsCache[$role] = array_values(
            array_unique(array_filter(array_map('strval', $permissions)))
        );
    }

    /**
     * @return array<int, string>
     */
    private static function abilityRoles(string $ability): array
    {
        if (function_exists('app')) {
            try {
                if (app()->bound('config')) {
                    $value = config("role_permissions.abilities.{$ability}", []);
                    return is_array($value) ? $value : [];
                }
            } catch (\Throwable $e) {
                // Fall back to direct config file load when container is unavailable (e.g. plain unit tests).
            }
        }

        $configFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'role_permissions.php';
        if (! is_file($configFile)) {
            return [];
        }

        $config = require $configFile;
        $value = $config['abilities'][$ability] ?? [];

        return is_array($value) ? $value : [];
    }
}
