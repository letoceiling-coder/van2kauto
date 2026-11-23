<?php

return [

    'base' => rtrim(env('AUTOMARKET_BASE', 'https://auctions.a-automarket.com'), '/'),

    'username' => env('AUTOMARKET_USERNAME'),

    'password' => env('AUTOMARKET_PASSWORD'),

    'page_size' => (int) env('AUTOMARKET_PAGE_SIZE', 100),

    'request_delay_ms' => (int) env('AUTOMARKET_REQUEST_DELAY_MS', 400),

    'http_timeout' => (int) env('AUTOMARKET_HTTP_TIMEOUT', 60),

];
