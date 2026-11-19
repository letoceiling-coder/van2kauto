#!/bin/bash
set -euo pipefail
UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
JAR=/tmp/che168-jar.txt
TARGET="${1:-https://global.che168.com/en/used-cars}"
rm -f "$JAR"
STATUS=$(node -e "
function a(a){function n(){for(var a={wQzOV:_0x649a('0x4'),iTyzs:function(a,n){return a+n}},n=a[_0x649a('0x5')][_0x649a('0x6')]('|'),e=0;;){switch(n[e++]){case'0':t+='EO_Bot_Ssid=';continue;case'1':return t;case'2':t+='';continue;case'3':t=a[_0x649a('0x7')](t,1291124736);continue;case'4':var t='';continue}break}}var e={WTKkN:355370132,bOYDu:19683478,dtzqS:function(a,n){return a+n},wyeCN:4522257,pCQRM:function(a){return a()}},t=0;return t+=e[_0x649a('0x0')],t+=e[_0x649a('0x1')],t=e[_0x649a('0x2')](t,e[_0x649a('0x3')]),[t,e[_0x649a('0x8')](n)][a]}var _0x49a6=['wyeCN','4|2|0|3|1','wQzOV','split','iTyzs','pCQRM','cookie','location.href=location.href.replace(/[?|&]tads/, \\'\\')','WTKkN','bOYDu','dtzqS'];(function(a,n){var e=function(n){for(;--n;)a.push(a.shift())};e(++n)})(_0x49a6,0x147);var _0x649a=function(a,n){a-=0;var e=_0x49a6[a];return e};console.log(a(0));console.log('EO_Bot_Ssid=1291124736');
")
TST=$(echo "$STATUS" | head -1)
EO=$(echo "$STATUS" | tail -1)
curl -s -m 25 -c "$JAR" -b "$JAR" -H "User-Agent: $UA" "$TARGET" -o /tmp/che168-pass1.html
sleep 1.2
curl -s -m 25 -c "$JAR" -b "$JAR" \
  -H "User-Agent: $UA" \
  -H "Cookie: __tst_status=${TST}#; ${EO}" \
  "$TARGET" -o /tmp/che168-pass2.html
BYTES=$(wc -c < /tmp/che168-pass2.html)
echo "pass2_bytes=$BYTES"
head -c 120 /tmp/che168-pass2.html; echo
echo "--- cookies ---"
grep -v '^#' "$JAR" | awk '{print $6"="$7}' | head -20