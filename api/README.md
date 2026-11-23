# Che168 catalog (Laravel)

Парсинг и каталог объявлений [global.che168.com](https://global.che168.com/en/used-cars).

- **Сейчас:** SQLite (`database/database.sqlite`) — разработка и локальный парсинг.
- **Продакшен:** PostgreSQL — те же миграции, смена `.env`.

## Быстрый старт

```bash
cp .env.example .env   # если ещё нет
touch database/database.sqlite   # Windows: New-Item database/database.sqlite
php artisan key:generate
php artisan migrate
php artisan serve
```

## База данных

### Локально (SQLite)

```env
DB_CONNECTION=sqlite
# DB_DATABASE=  # по умолчанию database/database.sqlite
```

### Продакшен (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=che168
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Деплой:

```bash
php artisan migrate --force
```

> Миграции без SQLite-специфики: `json`, `decimal`, внешние ключи — совместимы с PostgreSQL.

Экспорт SQLite → PostgreSQL (когда понадобится): [Laravel Backup](https://laravel.com/docs/migrations) или `pgloader`, либо повторный импорт через artisan-команды парсера.

## Artisan: парсинг

```bash
php artisan che168:test
php artisan che168:sync-brands
php artisan che168:sync-brands --series
php artisan che168:sync-listings --resume --max-pages=5
php artisan che168:sync-detail 60515670
php artisan che168:sync-detail --pending=50
php artisan che168:download-photos --pending=10
php artisan che168:download-photos 60515670
```

Файлы фото: `storage/app/che168/{infoid}/`.

При ответе HTML/captcha задайте в `.env` стабильный `CHE168_DEVICE_ID` и при необходимости `CHE168_COOKIES` из браузера.

### Парсинг с VPS (EdgeOne)

С IP дата-центра Che168 отдаёт **два шага**:

1. **JS challenge** — cookies `__tst_status` и `EO_Bot_Ssid` (считает `che168:warm-session` / `EdgeOneChallenge`).
2. **Tencent EO Captcha** — checkbox на странице «Security Verification»; после прохождения cookie `EO-Bot-Captcha-Token` (≈30 суток).

```bash
# На сервере: получить токен через SSH-проброс (браузер ходит с IP VPS)
ssh -D 1080 root@31.192.111.33   # в браузере SOCKS 127.0.0.1:1080, открыть global.che168.com, пройти checkbox
# DevTools → cookie EO-Bot-Captcha-Token → .env CHE168_EDGEONE_CAPTCHA_TOKEN

php artisan che168:warm-session --token=... --apply-env
php artisan che168:test
```

Headless Chromium на сервере **сам checkbox не проходит** (Playwright: `cookie_count=0`, captcha page). После токена warm-session забирает `che_sessionvid` и полный cookie jar.

## Che168 API

```env
CHE168_API_BASE=https://globalapi.che168.com
CHE168_APP_ID=global.m
CHE168_DEVICE_ID=<uuid>
CHE168_LANGUAGE=en
```

Документация по API и полям: [`docs/CHE168_GLOBAL_AUDIT.md`](docs/CHE168_GLOBAL_AUDIT.md).

## Модели

`Brand` → `Series` → `Spec` → `Listing` (+ `ListingPhoto`, `ListingReport`, `ScrapeCheckpoint`).