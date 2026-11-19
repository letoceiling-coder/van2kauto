#!/bin/bash
set -euo pipefail
cd /var/www/vanauto-api.neeklo.ru
export CHROME_PATH=/snap/bin/chromium
node deploy/che168-playwright-session.mjs
cat /tmp/che168-playwright-result.json