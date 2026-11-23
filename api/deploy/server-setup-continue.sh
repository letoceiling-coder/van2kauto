#!/bin/bash
set -euo pipefail

APP_DIR=/var/www/vanauto-api.neeklo.ru
cd "$APP_DIR"

source /root/vanauto-api-db-credentials.txt

export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env
grep -q '^DB_HOST=' .env && sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env || echo 'DB_HOST=127.0.0.1' >> .env
grep -q '^DB_PORT=' .env && sed -i 's/^DB_PORT=.*/DB_PORT=5432/' .env || echo 'DB_PORT=5432' >> .env
grep -q '^DB_DATABASE=' .env && sed -i 's/^DB_DATABASE=.*/DB_DATABASE=vanauto_api/' .env || echo 'DB_DATABASE=vanauto_api' >> .env
grep -q '^DB_USERNAME=' .env && sed -i 's/^DB_USERNAME=.*/DB_USERNAME=vanauto_api/' .env || echo 'DB_USERNAME=vanauto_api' >> .env
grep -q '^DB_PASSWORD=' .env && sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env || echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i 's|^APP_URL=.*|APP_URL=https://vanauto-api.neeklo.ru|' .env

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

php artisan migrate --force

if [ ! -d /etc/letsencrypt/live/vanauto-api.neeklo.ru ]; then
  certbot certonly --webroot -w /var/www/certbot -d vanauto-api.neeklo.ru \
    --non-interactive --agree-tos --register-unsafely-without-email \
    --cert-name vanauto-api.neeklo.ru
fi

cp /tmp/vanauto-api.nginx.conf /etc/nginx/sites-available/vanauto-api.neeklo.ru.conf
nginx -t
systemctl reload nginx

php artisan config:cache
php artisan route:cache

php artisan test
curl -sf -o /dev/null -w "%{http_code}" https://vanauto-api.neeklo.ru/up
echo ""
echo "SETUP_OK"