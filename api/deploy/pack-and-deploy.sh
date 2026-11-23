#!/bin/bash
# Run on dev machine with scp/ssh access to root@31.192.111.33
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
tar --exclude=vendor --exclude=node_modules --exclude=.git --exclude=storage/logs --exclude=storage/framework/cache --exclude=storage/framework/sessions --exclude=storage/framework/views --exclude=deploy.tgz -czf deploy.tgz .
scp deploy.tgz root@31.192.111.33:/tmp/vanauto-api-deploy.tgz
scp deploy/server-admin-update.sh root@31.192.111.33:/tmp/vanauto-api-admin-update.sh
ssh root@31.192.111.33 "sed -i 's/\r$//' /tmp/vanauto-api-admin-update.sh && bash /tmp/vanauto-api-admin-update.sh"