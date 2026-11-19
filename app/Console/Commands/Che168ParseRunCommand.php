<?php

namespace App\Console\Commands;

use App\Models\ScrapeRun;
use App\Services\Che168\ParseRunnerService;
use Illuminate\Console\Command;

class Che168ParseRunCommand extends Command
{
    protected $signature = 'che168:parse-run {id : Scrape run id}';

    protected $description = 'Execute a scrape run in background';

    public function handle(ParseRunnerService $runner): int
    {
        $run = ScrapeRun::query()->findOrFail((int) $this->argument('id'));
        $runner->run($run);

        return self::SUCCESS;
    }
}