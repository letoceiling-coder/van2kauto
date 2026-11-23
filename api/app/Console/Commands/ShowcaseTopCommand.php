<?php

namespace App\Console\Commands;

use App\Services\Showcase\TopPicker;
use Illuminate\Console\Command;

class ShowcaseTopCommand extends Command
{
    protected $signature = 'showcase:top {--limit=10} {--photos=8} {--publish : Сразу опубликовать} {--dry : Только показать, что будет добавлено}';

    protected $description = 'Собрать в витрину топ популярных «проходных» моделей из фида che168 (реальные фото и характеристики, цена «от» по формуле)';

    public function handle(TopPicker $picker): int
    {
        if ($this->option('dry')) {
            foreach ($picker->pick((int) $this->option('limit')) as $i => $item) {
                $l = $item['listing'];
                $this->line(($i + 1).'. '.$item['series']->brand?->name.' '.$item['series']->name." — {$item['count']} предл., #{$l->infoid}, \${$l->price_usd}, {$l->power_hp} л.с.");
            }

            return self::SUCCESS;
        }
        $made = $picker->build((int) $this->option('limit'), (bool) $this->option('publish'), (int) $this->option('photos'));
        foreach ($made as $car) {
            $this->line("{$car->badge}: {$car->brand} {$car->model} {$car->year} — от ".number_format((int) $car->price_from_rub, 0, '.', ' ').' ₽, фото: '.$car->photos()->count().($car->is_published ? ', опубликовано' : ', черновик'));
        }
        $this->info('Готово: '.$made->count());

        return self::SUCCESS;
    }
}
