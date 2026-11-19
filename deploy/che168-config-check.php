<?php

$base = '/var/www/vanauto-api.neeklo.ru';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = config('che168.cookies');
echo 'cookie_len='.strlen((string) $c).PHP_EOL;
echo 'device='.config('che168.device_id').PHP_EOL;