<?php

$base = '/var/www/vanauto-api.neeklo.ru';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'token_len='.strlen((string) config('che168.edgeone_captcha_token')).PHP_EOL;
echo 'device='.config('che168.device_id').PHP_EOL;
echo 'cookie_len='.strlen((string) config('che168.cookies')).PHP_EOL;

try {
    $api = app(App\Services\Che168\ApiClient::class);
    $j = $api->search(['pagesize' => 1, 'pageindex' => 1]);
    echo 'ok total='.($j['result']['totalcount'] ?? '?').PHP_EOL;
} catch (Throwable $e) {
    echo 'err='.$e->getMessage().PHP_EOL;
}