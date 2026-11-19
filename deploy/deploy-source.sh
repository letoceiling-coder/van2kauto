#!/usr/bin/env bash
# Выложить исходники сайта на сервер для пересборки из админки: ./deploy/deploy-source.sh [ssh-host]
set -euo pipefail
HOST="${1:-root@31.192.111.33}"
SRC=/opt/van2kauto
cd "$(dirname "$0")/.."
rsync -az --delete --exclude node_modules --exclude .next --exclude out --exclude .git --exclude .home --exclude content/showcase.json ./ "$HOST:$SRC/"
ssh "$HOST" "cd $SRC && [ -f content/showcase.json ] || echo '{\"cars\":[]}' > content/showcase.json; chown -R www-data:www-data $SRC /var/www/vanauto.neeklo.ru && runuser -u www-data -- env HOME=$SRC/.home pnpm install --frozen-lockfile --reporter=silent"
echo "Исходники в $HOST:$SRC. Команда сборки для админки: $SRC/deploy/rebuild.sh"
