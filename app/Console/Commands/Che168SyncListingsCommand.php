<?php

namespace App\Console\Commands;

use App\Services\Che168\CatalogSyncService;
use Illuminate\Console\Command;

class Che168SyncListingsCommand extends Command
{
    protected $signature = 'che168:sync-listings
        {--page= : Start page (default: checkpoint+1 or 1)}
        {--max-pages=1 : Pages to fetch in this run}
        {--checkpoint=search_default : Checkpoint name}
        {--resume : Continue from checkpoint last_page}';

    protected $description = 'Sync listing cards from /api/v1/search';

    public function handle(CatalogSyncService $sync): int
    {
        $checkpoint = $sync->checkpoint((string) $this->option('checkpoint'));
        $maxPages = max(1, (int) $this->option('max-pages'));

        $page = $this->option('page');
        if ($page === null) {
            $page = $this->option('resume') ? $checkpoint->last_page + 1 : 1;
        } else {
            $page = (int) $page;
        }

        $sleepMs = (int) config('che168.request_delay_ms', 400);

        try {
            for ($i = 0; $i < $maxPages; $i++) {
                $stats = $sync->syncSearchPage($page);
                $this->info("Page {$page}/{$stats['pagecount']}: saved {$stats['saved']}, catalog total {$stats['total']}");

                $checkpoint->update([
                    'last_page' => $page,
                    'meta' => $stats,
                ]);

                if ($page >= $stats['pagecount']) {
                    $this->comment('Reached last page.');
                    break;
                }

                $page++;
                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}