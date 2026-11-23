#!/usr/bin/env bash
# Выкладка бэкенда (папка api/) на прод: бэкап кода и базы → rsync изменённых файлов → миграции → кэши.
# Запуск из корня репозитория: deploy/deploy-api.sh
# .env, vendor, storage на сервере не трогаются; файлы, которых нет в репозитории, не удаляются.
set -euo pipefail
HOST="${HOST:-root@31.192.111.33}"
DST="${DST:-/var/www/vanauto-api.neeklo.ru}"
cd "$(dirname "$0")/../api"

echo "→ тесты"
php artisan test --compact >/dev/null || { echo "Тесты не прошли — выкладка отменена (php artisan test)"; [ "${FORCE:-0}" = 1 ] || exit 1; }

STAMP=$(date +%Y%m%d-%H%M%S)
echo "→ бэкап на сервере: /root/backups/vanauto-api-$STAMP"
ssh "$HOST" "mkdir -p /root/backups && cd $DST && tar czf /root/backups/vanauto-api-$STAMP-code.tgz --exclude=vendor --exclude=storage --exclude=node_modules . && runuser -u postgres -- pg_dump vanauto_api | gzip > /root/backups/vanauto-api-$STAMP-db.sql.gz"

echo "→ файлы"
rsync -rlcz --itemize-changes \
  --exclude .git --exclude vendor/ --exclude node_modules/ --exclude storage/ --exclude .env --exclude '.env.*' \
  --exclude bootstrap/cache/ --exclude public/storage --exclude public/hot --exclude public/build/ \
  --exclude database/database.sqlite --exclude .phpunit.result.cache --exclude .claude/ \
  ./ "$HOST:$DST/" | grep -v '^\.' || true

echo "→ миграции и кэши"
ssh "$HOST" "cd $DST && chown -R www-data:www-data . && runuser -u www-data -- php artisan migrate --force && runuser -u www-data -- php artisan optimize:clear >/dev/null && runuser -u www-data -- php artisan config:cache && runuser -u www-data -- php artisan route:cache && runuser -u www-data -- php artisan view:cache && (systemctl reload php8.3-fpm 2>/dev/null || true)"
echo "✓ API выложен. Откат: /root/backups/vanauto-api-$STAMP-*"
