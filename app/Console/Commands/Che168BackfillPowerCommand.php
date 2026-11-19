<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Spec;
use App\Support\EngineParser;
use Illuminate\Console\Command;

class Che168BackfillPowerCommand extends Command
{
    protected $signature = 'che168:backfill-power {--all : Пересчитать и уже заполненные}';

    protected $description = 'Заполнить power_hp и displacement_l у объявлений из строки двигателя и характеристик комплектации';

    public function handle(): int
    {
        $specPower = [];
        $updated = 0;
        Listing::query()
            ->when(! $this->option('all'), fn ($q) => $q->whereNull('power_hp'))
            ->select(['infoid', 'engine', 'spec_id', 'power_hp', 'displacement_l'])
            ->orderBy('infoid')
            ->chunkById(500, function ($chunk) use (&$specPower, &$updated) {
                foreach ($chunk as $listing) {
                    $hp = EngineParser::powerHp($listing->engine);
                    if ($hp === null && $listing->spec_id) {
                        $specPower[$listing->spec_id] ??= EngineParser::powerFromSpecParam(Spec::query()->find($listing->spec_id)?->raw_param) ?? 0;
                        $hp = $specPower[$listing->spec_id] ?: null;
                    }
                    $disp = EngineParser::displacementL($listing->engine);
                    if ($hp !== $listing->power_hp || $disp != $listing->displacement_l) {
                        Listing::query()->whereKey($listing->infoid)->update(['power_hp' => $hp, 'displacement_l' => $disp]);
                        $updated++;
                    }
                }
            }, 'infoid');

        $this->info("Обновлено: {$updated}");

        return self::SUCCESS;
    }
}
