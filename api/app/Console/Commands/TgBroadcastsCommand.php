<?php

namespace App\Console\Commands;

use App\Models\TgBroadcast;
use App\Services\Telegram\BroadcastSender;
use Illuminate\Console\Command;

class TgBroadcastsCommand extends Command
{
    protected $signature = 'tg:broadcasts {--seconds=50}';

    protected $description = 'Отправить порцию рассылок подписчикам бота';

    public function handle(BroadcastSender $sender): int
    {
        TgBroadcast::query()->whereIn('status', ['queued', 'sending'])->orderBy('id')->get()
            ->each(fn (TgBroadcast $b) => $sender->process($b, (int) $this->option('seconds')));

        return self::SUCCESS;
    }
}
