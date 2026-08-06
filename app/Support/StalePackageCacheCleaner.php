<?php

namespace App\Support;

final class StalePackageCacheCleaner
{
    /**
     * Remove a development package manifest that cannot be loaded by the
     * production vendor tree. Laravel will rebuild it from installed packages.
     */
    public static function clear(string $cacheDirectory, ?callable $providerExists = null): bool
    {
        $cacheDirectory = rtrim($cacheDirectory, '/\\');
        $packagesFile = $cacheDirectory.DIRECTORY_SEPARATOR.'packages.php';

        if (! is_file($packagesFile)) {
            return false;
        }

        $manifest = file_get_contents($packagesFile);
        if ($manifest === false || ! str_contains($manifest, "'laravel/pail'")) {
            return false;
        }

        $providerExists ??= static fn (string $provider): bool => class_exists($provider);
        if ($providerExists('Laravel\\Pail\\PailServiceProvider')) {
            return false;
        }

        foreach (['packages.php', 'services.php'] as $file) {
            $path = $cacheDirectory.DIRECTORY_SEPARATOR.$file;
            if (is_file($path)) {
                @unlink($path);
            }
        }

        clearstatcache(true, $packagesFile);

        return ! is_file($packagesFile);
    }
}
