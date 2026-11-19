#!/usr/bin/env bash
# Сборка статики и выкладка на сервер: ./deploy/deploy.sh [ssh-host]
set -euo pipefail
HOST="${1:-root@31.192.111.33}"
DIR=/var/www/vanauto.neeklo.ru
cd "$(dirname "$0")/.."
node scripts/fetch-showcase.mjs
rm -rf out
STATIC_EXPORT=1 npx next build
rsync -az --delete out/ "$HOST:$DIR/"
echo "Deployed to https://vanauto.neeklo.ru"
