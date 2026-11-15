#!/usr/bin/env bash
# Сборка статики и выкладка на сервер: ./deploy/deploy.sh [ssh-host]
set -euo pipefail
HOST="${1:-root@31.207.5.233}"
DIR=/var/www/vanauto.neeklo.ru
cd "$(dirname "$0")/.."
rm -rf out
STATIC_EXPORT=1 npx next build
rsync -az --delete out/ "$HOST:$DIR/"
echo "Deployed to https://vanauto.neeklo.ru"
