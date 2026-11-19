<?php

namespace App\Console\Commands;

use App\Models\AuctionRun;
use App\Services\Automarket\ParseRunner;
use Illuminate\Console\Command;

class AutomarketParseRunCommand extends Command
{
    protected $signature = 'automarket:parse-run {id : Auction run id}';

    protected $description = 'Execute an A-Automarket scrape run';

    public function handle(ParseRunner $runner): int
    {
        $run = AuctionRun::query()->findOrFail((int) $this->argument('id'));
        $runner->run($run);

        return self::SUCCESS;
    }
}
