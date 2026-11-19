#!/usr/bin/env bash
# Пересборка сайта на сервере по команде из админки (Настройки → «Команда сборки»).
# Исходники лежат в SRC (выкладываются deploy/deploy-source.sh), результат — в DIST.
set -euo pipefail
SRC="${SRC:-/opt/van2kauto}"
DIST="${DIST:-/var/www/vanauto.neeklo.ru}"
export SHOWCASE_API_URL="${SHOWCASE_API_URL:-https://vanauto-api.neeklo.ru/api/v1/showcase}"
export STATIC_EXPORT=1 NEXT_TELEMETRY_DISABLED=1

cd "$SRC"
echo "== $(date -Is) сборка из $SRC"
node scripts/fetch-showcase.mjs
[ -d node_modules ] || { command -v pnpm >/dev/null && pnpm install --frozen-lockfile || npx --yes pnpm@10 install --frozen-lockfile; }
rm -rf out
npx next build 2>&1 | tail -25
# Не выкладываем пустую/сломанную сборку.
[ -f out/index.html ] && [ -d out/catalog ] || { echo "out/ неполный — выкладка отменена"; exit 1; }
rsync -a --delete out/ "$DIST/"
echo "== выложено в $DIST: $(find out -name index.html | wc -l) страниц"
