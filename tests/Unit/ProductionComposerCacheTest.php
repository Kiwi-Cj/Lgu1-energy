<?php

test('composer clears stale Laravel package discovery caches before rebuilding autoload files', function () {
    $composer = json_decode(
        file_get_contents(dirname(__DIR__, 2).'/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR
    );

    $commands = implode("\n", $composer['scripts']['pre-autoload-dump'] ?? []);

    expect($commands)
        ->toContain('bootstrap/cache/packages.php')
        ->toContain('bootstrap/cache/services.php');
});
