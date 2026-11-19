<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\Telegram\LeadNotifier;
use Illuminate\Console\Command;

class LeadsRenotifyCommand extends Command
{
    protected $signature = 'leads:renotify';

    protected $description = 'Повторно отправить в Telegram заявки, которые не ушли (сбой сети или бот не был настроен)';

    public function handle(LeadNotifier $notifier): int
    {
        Lead::query()
            ->whereNull('notified_at')
            ->where('status', '!=', 'spam')
            ->where('notify_attempts', '<', 10)
            ->where('created_at', '>=', now()->subDays(2))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(fn (Lead $lead) => $notifier->notify($lead));

        return self::SUCCESS;
    }
}
