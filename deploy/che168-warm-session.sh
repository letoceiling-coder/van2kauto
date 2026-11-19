#!/bin/bash
set -euo pipefail
JAR=/tmp/che168-server-cookies.txt
UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
rm -f "$JAR"
curl -s -m 30 -c "$JAR" -b "$JAR" \
  -H "User-Agent: $UA" \
  -H 'Accept: text/html' \
  'https://global.che168.com/en/used-cars' -o /dev/null
# extract che_sessionvid from jar or cookie string
COOKIE=$(awk '!/^#/ && NF {printf "%s%s=%s", (NR>3?"; ":""), $6, $7}' "$JAR" 2>/dev/null || true)
if [ -z "$COOKIE" ]; then
  # netscape jar: domain, flag, path, secure, expiry, name, value
  COOKIE=$(awk '$0 !~ /^#/ {if (name) printf "%s", sep; printf "%s=%s", $6, $7; sep="; "; name=1}' "$JAR")
fi
DEVICE=$(grep -oP 'che_sessionvid\t\K[^\t]+' "$JAR" || awk '$6=="che_sessionvid" {print $7}' "$JAR")
if [ -z "$DEVICE" ]; then
  DEVICE=$(echo "$COOKIE" | sed -n 's/.*che_sessionvid=\([^;]*\).*/\1/p')
fi
echo "device=$DEVICE"
echo "cookie_len=${#COOKIE}"
printf '%s\n' "CHE168_DEVICE_ID=$DEVICE" > /tmp/che168-secrets.env
printf 'CHE168_COOKIES=%s\n' "$COOKIE" >> /tmp/che168-secrets.env
python3 /tmp/che168-env-update.py
cd /var/www/vanauto-api.neeklo.ru
php artisan config:clear -q
php artisan che168:test