# Production deployment

Laravel package discovery caches must not be copied between development and
production. Development caches can reference packages such as Laravel Pail that
are intentionally absent after a production `composer install --no-dev`.

The Composer `pre-autoload-dump` hook removes stale `packages.php` and
`services.php` files before Laravel regenerates them from the packages actually
installed on the server.

Run these commands from the application root during deployment:

```sh
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not upload a local `bootstrap/cache/packages.php`,
`bootstrap/cache/services.php`, or development `vendor` directory to
production.
