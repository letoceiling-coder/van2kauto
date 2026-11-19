<?php

namespace App\Console\Commands;

use App\Services\Site\SiteRebuilder;
use Illuminate\Console\Command;

class SiteRebuildCommand extends Command
{
    protected $signature = 'site:rebuild {--if-due : Собрать только если пора по правилам из настроек}';

    protected $description = 'Пересобрать статический сайт с актуальной витриной';

    public function handle(SiteRebuilder $rebuilder): int
    {
        $trigger = $this->option('if-due') ? $rebuilder->dueTrigger() : 'cli';
        if ($trigger === null) {
            return self::SUCCESS;
        }
        $build = $rebuilder->run($trigger);
        $this->line("Сборка #{$build->id}: {$build->status}");

        return $build->status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}
