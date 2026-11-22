<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Сервер: * * * * * cd /var/www/vanauto-api.neeklo.ru && php artisan schedule:run
Schedule::command('site:rebuild --if-due')->everyMinute()->withoutOverlapping(30)->runInBackground();
Schedule::command('tg:broadcasts')->everyMinute()->withoutOverlapping(5);
Schedule::command('leads:renotify')->everyFiveMinutes()->withoutOverlapping(5);

// Парсер che168: новые объявления каждый день, полный обход (отметка проданных) по воскресеньям. Выключается в «Настройках».
// Настройки читаем безопасно: при пустой базе (первый деплой, тесты) artisan не должен падать.
$setting = function (string $key, string $default): string {
    try {
        return (string) \App\Support\Settings::get($key, $default);
    } catch (\Throwable) {
        return $default;
    }
};
$parserOn = fn () => $setting('parser_auto', '1') === '1';
$hour = max(0, min(23, (int) $setting('parser_daily_hour', '5')));
Schedule::command('che168:auto new')->dailyAt(sprintf('%02d:00', $hour))->when($parserOn)->withoutOverlapping(360)->runInBackground();
Schedule::command('che168:auto full')->weeklyOn(0, '02:00')->when($parserOn)->withoutOverlapping(720)->runInBackground();
// Курс $ по ЦБ для цены «от»
Schedule::command('rates:update')->dailyAt('09:30');
