#!/usr/bin/env python3
"""Merge CHE168_DEVICE_ID and CHE168_COOKIES into Laravel .env (run on server)."""
import pathlib
import re
import sys

SECRETS = pathlib.Path("/tmp/che168-secrets.env")
ENV = pathlib.Path("/var/www/vanauto-api.neeklo.ru/.env")

if not SECRETS.is_file():
    print("missing", SECRETS, file=sys.stderr)
    sys.exit(1)

vals: dict[str, str] = {}
for line in SECRETS.read_text(encoding="utf-8-sig").splitlines():
    line = line.strip()
    if not line or line.startswith("#") or "=" not in line:
        continue
    k, v = line.split("=", 1)
    vals[k.strip()] = v.strip()

for key in ("CHE168_DEVICE_ID", "CHE168_COOKIES"):
    if key not in vals:
        print(f"missing {key}", file=sys.stderr)
        sys.exit(1)

text = ENV.read_text(encoding="utf-8")

for key, value in vals.items():
    if not key.startswith("CHE168_"):
        continue
    # Quote cookies (semicolons, spaces)
    if key == "CHE168_COOKIES" and not (value.startswith('"') and value.endswith('"')):
        value = '"' + value.replace("\\", "\\\\").replace('"', '\\"') + '"'
    pattern = re.compile(rf"^{re.escape(key)}=.*$", re.MULTILINE)
    replacement = f"{key}={value}"
    if pattern.search(text):
        text = pattern.sub(replacement, text)
    else:
        if not text.endswith("\n"):
            text += "\n"
        text += replacement + "\n"

ENV.write_text(text, encoding="utf-8")
print("ENV_UPDATED")