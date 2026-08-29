<?php

use App\Support\StalePackageCacheCleaner;

test('stale development package caches are removed before Laravel boots', function () {
    $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'energy-package-cache-'.bin2hex(random_bytes(6));
    mkdir($directory, 0777, true);

    $packages = $directory.DIRECTORY_SEPARATOR.'packages.php';
    $services = $directory.DIRECTORY_SEPARATOR.'services.php';
    file_put_contents($packages, "<?php return ['laravel/pail' => ['providers' => ['Laravel\\\\Pail\\\\PailServiceProvider']]];");
    file_put_contents($services, '<?php return [];');

    try {
        $cleared = StalePackageCacheCleaner::clear(
            $directory,
            static fn (string $provider): bool => false
        );

        expect($cleared)->toBeTrue()
            ->and(file_exists($packages))->toBeFalse()
            ->and(file_exists($services))->toBeFalse();
    } finally {
        if (is_file($packages)) {
            unlink($packages);
        }
        if (is_file($services)) {
            unlink($services);
        }
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }
});
