<?php

return [

    'api_base' => env('CHE168_API_BASE', 'https://globalapi.che168.com'),

    'app_id' => env('CHE168_APP_ID', 'global.m'),

    'device_id' => env('CHE168_DEVICE_ID'),

    'language' => env('CHE168_LANGUAGE', 'en'),

    'fromsource' => env('CHE168_FROMSOURCE', '0'),

    'cookies' => env('CHE168_COOKIES'),

    'edgeone_captcha_token' => env('CHE168_EDGEONE_CAPTCHA_TOKEN'),

    'gate_secret' => env('CHE168_GATE_SECRET'),

    'http_proxy' => env('CHE168_HTTP_PROXY'),

    'http_timeout' => env('CHE168_HTTP_TIMEOUT', 45),

    'request_delay_ms' => env('CHE168_REQUEST_DELAY_MS', 400),

    'search' => [
        'page_size' => (int) env('CHE168_SEARCH_PAGE_SIZE', 100),
        'vehicle_list' => env('CHE168_VEHICLE_LIST', '1'),
        'report_status' => env('CHE168_REPORT_STATUS', '1,3'),
    ],

];