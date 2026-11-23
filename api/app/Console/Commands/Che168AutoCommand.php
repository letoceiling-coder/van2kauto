<?php

namespace App\Console\Commands;

use App\Models\ScrapeRun;
use App\Services\Che168\ParseRunnerService;
use App\Services\Telegram\TelegramClient;
use App\Support\Settings;
use Illuminate\Console\Command;

class Che168AutoCommand extends Command
{
    protected $signature = 'che168:auto {mode=new : new — только новые, full — полный обход с отметкой проданных}';

    protected $description = 'Автозапуск парсера che168 по расписанию; если нужна капча — уведомление менеджерам в Telegram';

    public function handle(ParseRunnerService $runner, TelegramClient $telegram): int
    {
        if (ScrapeRun::query()->whereIn('status', ['pending', 'running'])->where('updated_at', '>', now()->subHours(6))->exists()) {
            $this->line('Парсер уже работает — пропускаю');

            return self::SUCCESS;
        }
        $mode = $this->argument('mode') === 'full' ? 'full' : 'new';
        $run = ScrapeRun::query()->create(['mode' => $mode, 'status' => 'pending']);
        $runner->run($run);
        $run->refresh();
        $this->line("Запуск #{$run->id} ({$mode}): {$run->status}, сохранено {$run->items_saved}");

        if ($run->status !== 'completed' && $telegram->configured()) {
            $text = $run->status === 'needs_captcha'
                ? "⚠️ Парсер che168 остановился: нужна капча.\nОткройте админку → Парсер → Captcha, пройдите проверку и нажмите «Продолжить»."
                : "⚠️ Парсер che168 завершился с ошибкой: ".mb_substr((string) $run->error_message, 0, 300);
            foreach (Settings::managerChatIds() as $chatId) {
                try {
                    $telegram->sendMessage($chatId, htmlspecialchars($text, ENT_NOQUOTES));
                } catch (\Throwable) {
                }
            }
        }

        return $run->status === 'completed' ? self::SUCCESS : self::FAILURE;
    }
}
