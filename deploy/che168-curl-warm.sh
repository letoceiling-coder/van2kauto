#!/bin/bash
# Two-stage EdgeOne on datacenter IP: JS cookies + optional EO-Bot-Captcha-Token
set -euo pipefail
UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
URL='https://global.che168.com/en/used-cars'
JAR=/tmp/che168-warm.jar
TOKEN="${CHE168_EDGEONE_CAPTCHA_TOKEN:-}"
TST=$(node -e "function a(a){function n(){for(var a={wQzOV:_0x649a('0x4'),iTyzs:function(a,n){return a+n}},n=a[_0x649a('0x5')][_0x649a('0x6')]('|'),e=0;;){switch(n[e++]){case'0':t+='EO_Bot_Ssid=';continue;case'1':return t;case'2':t+='';continue;case'3':t=a[_0x649a('0x7')](t,1291124736);continue;case'4':var t='';continue}break}}var e={WTKkN:355370132,bOYDu:19683478,dtzqS:function(a,n){return a+n},wyeCN:4522257,pCQRM:function(a){return a()}},t=0;return t+=e[_0x649a('0x0')],t+=e[_0x649a('0x1')],t=e[_0x649a('0x2')](t,e[_0x649a('0x3')]),[t,e[_0x649a('0x8')](n)][a]}var _0x49a6=['wyeCN','4|2|0|3|1','wQzOV','split','iTyzs','pCQRM','cookie','location.href=location.href.replace(/[?|&]tads/, \\'\\')','WTKkN','bOYDu','dtzqS'];(function(a,n){var e=function(n){for(;--n;)a.push(a.shift())};e(++n)})(_0x49a6,0x147);var _0x649a=function(a,n){a-=0;var e=_0x49a6[a];return e};console.log(a(0));")
COOKIE="__tst_status=${TST}#; EO_Bot_Ssid=1291124736"
if [ -n "$TOKEN" ]; then
  COOKIE="${COOKIE}; EO-Bot-Captcha-Token=${TOKEN}"
fi
rm -f "$JAR"
curl -s -m 25 -c "$JAR" -b "$JAR" -H "User-Agent: $UA" -H "Cookie: $COOKIE" "$URL" -o /tmp/che168-warm.html
echo "html_bytes=$(wc -c < /tmp/che168-warm.html)"
grep -q che_sessionvid /tmp/che168-warm.html && echo has_vid_inline=yes || echo has_vid_inline=no
grep -v '^#' "$JAR" | awk 'NF && $6!="" {print $6"="$7}' | head -25