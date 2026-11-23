<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\Che168\CatalogSyncService;
use Illuminate\Console\Command;

class Che168SyncDetailCommand extends Command
{
    protected $signature = 'che168:sync-detail
        {infoid? : Vehicle ID}
        {--pending=20 : How many listings without raw_carinfo to process}
        {--sleep=500 : Delay between requests in ms}';

    protected $description = 'Sync full carinfo, photos, spec and report for listings';

    public function handle(CatalogSyncService $sync): int
    {
        $sleep = max(0, (int) $this->option('sleep'));
        $infoid = $this->argument('infoid');

        $ids = $infoid !== null
            ? [(int) $infoid]
            : Listing::query()
                ->whereNull('raw_carinfo')
                ->orderBy('infoid')
                ->limit((int) $this->option('pending'))
                ->pluck('infoid')
                ->all();

        if ($ids === []) {
            $this->comment('Nothing to sync.');

            return self::SUCCESS;
        }

        $ok = 0;
        foreach ($ids as $id) {
            try {
                $listing = $sync->syncListingDetail($id);
                $this->info("{$id}: {$listing->title} ({$listing->photos()->count()} photos)");
                $ok++;
            } catch (\Throwable $e) {
                $this->warn("{$id}: ".$e->getMessage());
            }

            if ($sleep > 0) {
                usleep($sleep * 1000);
            }
        }

        $this->info("Done: {$ok}/".count($ids));

        return $ok > 0 ? self::SUCCESS : self::FAILURE;
    }
}