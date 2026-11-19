<?php

namespace App\Services\Catalog;

class EndpointCatalog
{
    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        $paging = [
            ['name' => 'limit', 'desc' => 'Размер страницы. Синоним per_page. По умолчанию 24 для товаров и 50 для справочников, максимум 100 и 200.'],
            ['name' => 'offset', 'desc' => 'Смещение от начала выборки, с 0. Если передан, page игнорируется.'],
            ['name' => 'page', 'desc' => 'Номер страницы с 1. Используется, когда offset не передан.'],
        ];

        return [
            [
                'method' => 'GET',
                'path' => '/api/v1/listings',
                'title' => 'Товары',
                'summary' => 'Список объявлений со всеми фильтрами каталога. По умолчанию только status=active. Умный поиск q разбивает строку на слова: каждое слово должно встретиться в названии, марке, модели, комплектации, топливе, КПП, кузове, цвете, городе или ID.',
                'params' => array_merge([
                    ['name' => 'q', 'desc' => 'Поиск. Пример: mazda 2011 gasoline'],
                    ['name' => 'category_id', 'desc' => 'Марка. Синоним brand_id. Несколько через запятую: 33,36'],
                    ['name' => 'model_id', 'desc' => 'Модель (серия). Синоним series_id. Несколько через запятую.'],
                    ['name' => 'spec_id', 'desc' => 'Комплектация.'],
                    ['name' => 'status', 'desc' => 'active (по умолчанию), inactive, absent. any или all — без фильтра статуса.'],
                    ['name' => 'fuel', 'desc' => 'Топливо, как в карточке. Синоним fuel_name. Несколько через запятую.'],
                    ['name' => 'gearbox', 'desc' => 'КПП.'],
                    ['name' => 'body_type', 'desc' => 'Кузов. Синоним body.'],
                    ['name' => 'color', 'desc' => 'Цвет.'],
                    ['name' => 'driving_mode', 'desc' => 'Привод. Синоним drive.'],
                    ['name' => 'steering', 'desc' => 'Руль.'],
                    ['name' => 'class_level', 'desc' => 'Класс. Синоним class.'],
                    ['name' => 'city', 'desc' => 'Город, slug. Синоним city_slug.'],
                    ['name' => 'seats', 'desc' => 'Число мест.'],
                    ['name' => 'doors', 'desc' => 'Число дверей.'],
                    ['name' => 'year_min', 'desc' => 'Год регистрации от, включительно.'],
                    ['name' => 'year_max', 'desc' => 'Год регистрации до, включительно.'],
                    ['name' => 'price_min', 'desc' => 'Цена USD от.'],
                    ['name' => 'price_max', 'desc' => 'Цена USD до.'],
                    ['name' => 'mileage_min', 'desc' => 'Пробег км от.'],
                    ['name' => 'mileage_max', 'desc' => 'Пробег км до.'],
                    ['name' => 'cert_status', 'desc' => 'Числовой флаг сертификации с донора.'],
                    ['name' => 'sort', 'desc' => 'scraped_at (по умолчанию), price, mileage, year, title.'],
                    ['name' => 'dir', 'desc' => 'asc или desc. По умолчанию desc.'],
                ], $paging),
                'example' => '/api/v1/listings?q=mazda&category_id=33&price_max=20000&year_min=2018&limit=24&offset=0&sort=price&dir=asc',
                'response' => <<<'JSON'
{
  "data": [{
    "infoid": 60471527,
    "title": "Mazda Mazda5 2011 2.0L Manual Comfort",
    "price_usd": 3190,
    "year": 2011,
    "mileage_km": 80000,
    "fuel": "Gasoline",
    "category": {"id": 33, "name": "Mazda"},
    "model": {"id": 4962, "name": "Mazda5", "category_id": 33},
    "photo": {"preview": "https://...", "large": "https://...", "original": "https://..."},
    "photos_count": 18
  }],
  "meta": {"total": 128, "limit": 24, "offset": 0, "page": 1, "pages": 6, "has_more": true, "next_offset": 24}
}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/listings/{infoid}',
                'title' => 'Карточка товара',
                'summary' => 'Полная карточка: все фото, свойства, отчёт, группы характеристик и комплектации. include_raw=1 добавляет сырой ответ донора.',
                'params' => [
                    ['name' => 'infoid', 'desc' => 'ID объявления в пути.'],
                    ['name' => 'include_raw', 'desc' => '1 — добавить поле raw с исходным carinfo.'],
                ],
                'example' => '/api/v1/listings/60471527',
                'response' => <<<'JSON'
{
  "data": {
    "infoid": 60471527,
    "photos": [{"sort": 0, "preview": "https://...", "large": "https://...", "original": "https://..."}],
    "properties": {"vin": "L****123", "dimension": "4585*1800*1490", "city_id": 110100},
    "spec_groups": [{"name": "Engine", "items": [{"name": "Displacement", "value": "2.0L"}]}],
    "equipment_groups": [{"name": "Safety", "items": [{"name": "Airbags", "value": "●"}]}],
    "report": {"claims_count": 0, "latest_mileage_km": 79000}
  }
}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/search/suggest',
                'title' => 'Подсказки поиска',
                'summary' => 'Для поля поиска. Возвращает три группы: категории (марки), модели и товары. Совпадение с начала строки идёт первым. Пустой q возвращает пустые списки.',
                'params' => [
                    ['name' => 'q', 'desc' => 'Текст в поле поиска.'],
                    ['name' => 'limit', 'desc' => 'Сколько элементов в каждой группе. По умолчанию 8, максимум 20.'],
                ],
                'example' => '/api/v1/search/suggest?q=maz&limit=8',
                'response' => <<<'JSON'
{
  "query": "maz",
  "categories": [{"type": "category", "id": 33, "name": "Mazda", "listings_count": 12}],
  "models": [{"type": "model", "id": 4962, "name": "Mazda5", "category_id": 33, "category": "Mazda", "listings_count": 4}],
  "products": [{"type": "product", "infoid": 60471527, "title": "Mazda Mazda5 2011", "price_usd": 3190, "photo": "https://..."}]
}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/categories',
                'title' => 'Категории (марки)',
                'summary' => 'Справочник марок. category_id из этого списка передаётся в товары и модели.',
                'params' => array_merge([
                    ['name' => 'q', 'desc' => 'Фильтр по названию марки.'],
                ], $paging),
                'example' => '/api/v1/categories?q=toy&limit=50&offset=0',
                'response' => <<<'JSON'
{"data": [{"id": 3, "name": "Toyota", "py": "toyota", "icon_url": "https://...", "listings_count": 40}], "meta": {"total": 1, "limit": 50, "offset": 0, "page": 1, "pages": 1, "has_more": false, "next_offset": null}}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/models',
                'title' => 'Модели',
                'summary' => 'Серии внутри марки. Без category_id — все модели.',
                'params' => array_merge([
                    ['name' => 'category_id', 'desc' => 'Марка. Синоним brand_id.'],
                    ['name' => 'q', 'desc' => 'Фильтр по названию модели.'],
                ], $paging),
                'example' => '/api/v1/models?category_id=33&limit=50',
                'response' => <<<'JSON'
{"data": [{"id": 4962, "name": "Mazda5", "category_id": 33, "category": "Mazda", "listings_count": 4}], "meta": {"total": 1, "limit": 50, "offset": 0, "page": 1, "pages": 1, "has_more": false, "next_offset": null}}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/models/{id}',
                'title' => 'Модель',
                'summary' => 'Одна модель и её комплектации.',
                'params' => [
                    ['name' => 'id', 'desc' => 'model_id в пути.'],
                ],
                'example' => '/api/v1/models/4962',
                'response' => <<<'JSON'
{"data": {"id": 4962, "name": "Mazda5", "category_id": 33, "category": "Mazda", "specs": [{"id": 55424, "name": "2023 2.5L", "year_label": "2023"}]}}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/specs',
                'title' => 'Комплектации',
                'summary' => 'Тримы. Фильтр по модели или марке.',
                'params' => array_merge([
                    ['name' => 'model_id', 'desc' => 'Модель. Синоним series_id.'],
                    ['name' => 'category_id', 'desc' => 'Марка.'],
                    ['name' => 'q', 'desc' => 'Фильтр по названию комплектации.'],
                ], $paging),
                'example' => '/api/v1/specs?model_id=4962',
                'response' => <<<'JSON'
{"data": [{"id": 55424, "name": "2023 2.5L", "model_id": 4962, "model": "Mazda5", "category_id": 33, "category": "Mazda"}], "meta": {"total": 1, "limit": 50, "offset": 0, "page": 1, "pages": 1, "has_more": false, "next_offset": null}}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/facets',
                'title' => 'Доступные фильтры',
                'summary' => 'Значения фильтров и диапазоны цен/пробега с количеством товаров. Принимает те же фильтры, что список товаров: выбранный фильтр не сужает сам себя, остальные сужают. Нужен, чтобы строить панель фильтров как у донора.',
                'params' => [
                    ['name' => 'те же, что у /listings', 'desc' => 'Кроме limit, offset, page, sort, dir.'],
                ],
                'example' => '/api/v1/facets?category_id=33',
                'response' => <<<'JSON'
{
  "data": {
    "categories": [{"id": 33, "name": "Mazda", "count": 12}],
    "models": [{"id": 4962, "name": "Mazda5", "category_id": 33, "count": 4}],
    "fuels": [{"value": "Gasoline", "count": 10}],
    "gearboxes": [{"value": "Automatic", "count": 8}],
    "bodies": [{"value": "MPV", "count": 4}],
    "colors": [{"value": "Black", "count": 3}],
    "years": [{"value": "2011", "count": 2}],
    "price": {"min": 3190, "max": 19850},
    "mileage": {"min": 12000, "max": 180000}
  }
}
JSON,
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/properties',
                'title' => 'Справочник свойств',
                'summary' => 'Все встретившиеся значения свойств активных товаров: топливо, КПП, кузов, цвет, привод, руль, класс, город.',
                'params' => [],
                'example' => '/api/v1/properties',
                'response' => <<<'JSON'
{"data": [{"key": "fuel", "title": "Топливо", "values": ["Diesel", "Gasoline"]}, {"key": "gearbox", "title": "КПП", "values": ["Automatic", "Manual"]}]}
JSON,
            ],
        ];
    }

    public static function plain(string $base): string
    {
        $lines = [
            'VAN2KAUTO Che168 API',
            'База: '.$base,
            'Чтение без авторизации. Ответ JSON.',
            'Пагинация: limit или per_page, page с 1, либо offset с 0. offset важнее page.',
            '',
        ];

        foreach (self::all() as $endpoint) {
            $lines[] = $endpoint['method'].' '.$endpoint['path'];
            $lines[] = $endpoint['title'];
            $lines[] = $endpoint['summary'];
            foreach ($endpoint['params'] as $param) {
                $lines[] = '- '.$param['name'].': '.$param['desc'];
            }
            $path = \Illuminate\Support\Str::after($endpoint['example'], '/api/v1');
            $lines[] = 'Пример: '.$base.$path;
            $lines[] = 'Ответ:';
            $lines[] = $endpoint['response'];
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /** @return array<string, mixed> */
    public static function openapi(): array
    {
        $paths = [];
        foreach (self::all() as $endpoint) {
            $path = preg_replace('/\{[^}]+\}/', '{id}', $endpoint['path']) ?? $endpoint['path'];
            $paths[$path][strtolower($endpoint['method'])] = [
                'summary' => $endpoint['title'],
                'description' => $endpoint['summary'],
                'parameters' => array_map(fn (array $param) => [
                    'name' => $param['name'],
                    'in' => str_contains($endpoint['path'], '{') && in_array($param['name'], ['infoid', 'id'], true) ? 'path' : 'query',
                    'required' => in_array($param['name'], ['infoid', 'id'], true),
                    'schema' => ['type' => 'string'],
                    'description' => $param['desc'],
                ], $endpoint['params']),
                'responses' => [
                    '200' => ['description' => $endpoint['response']],
                ],
            ];
        }

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'VAN2KAUTO Che168 API',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => url('/')],
            ],
            'paths' => $paths,
        ];
    }
}
