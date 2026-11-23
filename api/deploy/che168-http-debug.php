<?php

$base = '/var/www/vanauto-api.neeklo.ru';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$device = config('che168.device_id');
$cookies = (string) config('che168.cookies');
$query = [
    '_appid' => config('che168.app_id'),
    'deviceid' => $device,
    'language' => config('che168.language'),
    'fromsource' => config('che168.fromsource'),
    'pageindex' => 1,
    'pagesize' => 1,
    'sort' => 0,
    'vehicle_list' => config('che168.search.vehicle_list'),
    'report_status' => config('che168.search.report_status'),
];
$url = rtrim(config('che168.api_base'), '/').'/api/v1/search';

$headers = [
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept' => 'application/json',
    'Referer' => 'https://global.che168.com/en/used-cars',
    'Cookie' => $cookies,
];

$response = Http::withHeaders($headers)->timeout(45)->get($url, $query);
$body = $response->body();
echo 'status='.$response->status().PHP_EOL;
echo 'cookie_sent_len='.strlen($cookies).PHP_EOL;
echo 'device='.$device.PHP_EOL;
echo 'vid_in_cookie='.(str_contains($cookies, (string) $device) ? 'yes' : 'no').PHP_EOL;
echo 'body_prefix='.substr(trim($body), 0, 80).PHP_EOL;