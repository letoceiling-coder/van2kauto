#!/bin/bash
set -euo pipefail
APP_DIR=/var/www/vanauto-api.neeklo.ru
cp -a "$APP_DIR/.env" /tmp/vanauto-api.env.bak
tar -xzf /tmp/vanauto-api-deploy.tgz -C "$APP_DIR"
cp -a /tmp/vanauto-api.env.bak "$APP_DIR/.env"
cd "$APP_DIR"
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan config:cache
echo OK