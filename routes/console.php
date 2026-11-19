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
