#!/bin/bash
set -euo pipefail
cd /var/www/vanauto-api.neeklo.ru
COOKIE=$(php /tmp/che168-config-check.php 2>/dev/null | head -1 || true)
# read cookie via php
COOKIE=$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo (string) config("che168.cookies");
')
DEVICE=$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo (string) config("che168.device_id");
')
URL="https://globalapi.che168.com/api/v1/search?_appid=global.m&deviceid=${DEVICE}&language=en&fromsource=0&pageindex=1&pagesize=1&sort=0&vehicle_list=1&report_status=1,3"
curl -s -m 25 "$URL" \
  -H 'Accept: application/json' \
  -H 'Referer: https://global.che168.com/en/used-cars' \
  -H 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0.0.0 Safari/537.36' \
  -H "Cookie: $COOKIE" | head -c 150
echo ""