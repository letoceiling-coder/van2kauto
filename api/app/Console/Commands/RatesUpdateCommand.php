<?php

namespace App\Console\Commands;

use App\Support\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RatesUpdateCommand extends Command
{
    protected $signature = 'rates:update';

    protected $description = 'Обновить курс $ по ЦБ РФ для формулы цены «от» (если включено «курс автоматически»)';

    public function handle(): int
    {
        if (Settings::get('price_rate_auto', '1') !== '1') {
            $this->line('Курс задан вручную — пропускаю');

            return self::SUCCESS;
        }
        try {
            $value = Http::timeout(15)->get('https://www.cbr-xml-daily.ru/daily_json.js')->json('Valute.USD.Value');
        } catch (\Throwable $e) {
            $this->error('ЦБ недоступен: '.$e->getMessage());

            return self::FAILURE;
        }
        if (! is_numeric($value) || $value < 10 || $value > 1000) {
            $this->error('Непонятный ответ ЦБ');

            return self::FAILURE;
        }
        Settings::setMany(['price_usd_rate' => (string) round((float) $value, 2), 'price_rate_updated_at' => now()->toIso8601String()]);
        $this->info('Курс $: '.round((float) $value, 2));

        return self::SUCCESS;
    }
}
