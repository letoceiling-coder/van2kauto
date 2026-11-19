<?php

$base = '/var/www/vanauto-api.neeklo.ru';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Che168\ApiClient;

try {
    $api = app(ApiClient::class);
    $payload = $api->search(['pagesize' => 1, 'pageindex' => 1]);
    echo 'OK total='.($payload['result']['totalcount'] ?? '?').PHP_EOL;
} catch (Throwable $e) {
    echo 'ERR: '.$e->getMessage().PHP_EOL;
}