<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Services\Che168\CatalogSyncService;
use Illuminate\Console\Command;

class Che168SyncBrandsCommand extends Command
{
    protected $signature = 'che168:sync-brands {--series : Also sync series for each brand (slow)}';

    protected $description = 'Sync brand catalog from Che168 API';

    public function handle(CatalogSyncService $sync): int
    {
        try {
            $count = $sync->syncBrands();
            $this->info("Brands upserted: {$count}");
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('series')) {
            return self::SUCCESS;
        }

        $totalSeries = 0;
        Brand::query()->orderBy('brand_id')->chunk(50, function ($brands) use ($sync, &$totalSeries) {
            foreach ($brands as $brand) {
                try {
                    $n = $sync->syncSeriesForBrand((int) $brand->brand_id);
                    $totalSeries += $n;
                    $this->line("  brand {$brand->brand_id}: {$n} series");
                } catch (\Throwable $e) {
                    $this->warn("  brand {$brand->brand_id} skipped: ".$e->getMessage());
                }
            }
        });

        $this->info("Series upserted: {$totalSeries}");

        return self::SUCCESS;
    }
}