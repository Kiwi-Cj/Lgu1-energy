<?php

namespace App\Support;

use App\Models\User;

class RoleAccess
{
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

        $allowedRoles = static::abilityRoles($ability);
        if (! is_array($allowedRoles)) {
            return false;
        }

        return static::in($user, $allowedRoles);
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
