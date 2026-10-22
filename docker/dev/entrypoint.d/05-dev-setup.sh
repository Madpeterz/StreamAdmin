#!/bin/sh
# Dev stack only: vendor/ lives in a named volume (Docker Desktop bind mounts return short
# directory listings to PHP's DirectoryIterator), so install it there on first start.
set -e
cd /var/www/html
if [ ! -f vendor/autoload.php ]; then
    echo "dev-setup: installing composer dependencies into the vendor volume"
    composer install --no-interaction --prefer-dist
fi
chmod -R a+rwX storage bootstrap/cache
# compiled views (VIEW_COMPILED_PATH) are written by php-fpm (www-data) and artisan (root); tests use their own folder
mkdir -p /tmp/views && chmod -R a+rwX /tmp/views
# Bring the schema up to date and restore the local test login (admin / localtest); the db
# container may still be starting, so a failure here must not stop the app container.
php artisan migrate --force --no-interaction || echo "dev-setup: migrate failed, run it by hand"
php artisan db:seed --class=DevLoginSeeder --force --no-interaction || echo "dev-setup: login seed failed"
