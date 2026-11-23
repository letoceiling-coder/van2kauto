# Установка Open Server Panel на этот ПК

## Что уже сделано

1. Папка: `C:\OSPanel`
2. Запущена **фоновая загрузка** полного установщика:
   - `C:\OSPanel\open_server_panel_6_5_1_setup.exe` (~1.7 ГБ)
   - Источник: https://ospanel.io/download/ → «Нет, спасибо, хочу просто скачать»
3. Файл `C:\OSPanel\ospanel-installer.exe` (11 МБ) — **не** полный дистрибутив, только модуль с `files.ospanel.io/dist/`; для чистой установки используйте **setup.exe** выше.

## Проверить прогресс загрузки

```powershell
Get-Item C:\OSPanel\open_server_panel_6_5_1_setup.exe | Format-List Name,Length,LastWriteTime
# ожидаемый размер ~1.82 GB (1825361102 байт — уточнить по HASH на сайте)
Get-FileHash C:\OSPanel\open_server_panel_6_5_1_setup.exe -Algorithm SHA256
# официальный SHA256: AE4C68F5ECACE658D8D60E7EEDA455063F318D5E4215D5591F86D0DADB970B20
```

## Установка (вручную, GUI)

1. Дождаться окончания загрузки и совпадения SHA256.
2. Запустить **от обычного пользователя** (не обязательно админ):
   ```
   C:\OSPanel\open_server_panel_6_5_1_setup.exe
   ```
3. Путь установки: **`C:\OSPanel`** (корень диска, только латиница).
4. Режим: **стандартная** установка (не portable).
5. Компоненты (минимум под ваши задачи):
   - **Apache** или **Nginx** (один HTTP)
   - **PHP** (нужная версия)
   - **MySQL 8.0**
   - **PostgreSQL** (если есть в списке модулей)
   - **Redis**
6. После установки — **System Preparation Tool** (`osp sysprep`) **от администратора** (службы, hosts, сертификаты).
7. VPN WireGuard: добавить в исключения подсеть **`127.0.0.0/8`**.
8. Запуск: `C:\OSPanel\bin\ospanel.exe` или ярлык в меню Пуск.

## Hosts (локальные домены)

```
192.168.0.14  gb.batmorton.loc eu.bn.loc fastfood.loc firma-trendagent.loc free.loc
```

Для локальной разработки можно также: `127.0.0.1 example.loc` (OSPanel сам дописывает свои домены в hosts).

## После установки — проверка

```powershell
Test-NetConnection 127.0.0.1 -Port 80
Test-NetConnection 127.0.0.1 -Port 3306
Test-NetConnection 127.0.0.1 -Port 6379
Test-NetConnection 127.0.0.1 -Port 5432
```

Документация: https://github.com/OSPanel/OpenServerPanel/wiki