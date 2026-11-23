<?php

namespace App\Services\Site;

use App\Models\SiteBuild;
use App\Support\Settings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;

/**
 * Правила пересборки статического сайта (настройки → «Сайт»):
 *  manual    — только по кнопке в админке;
 *  on_change — автоматически через N минут после последнего изменения витрины (дебаунс, чтобы пачка правок = одна сборка);
 *  daily     — раз в сутки в заданный час, если были изменения.
 * Кнопка «Пересобрать» работает в любом режиме. Сборку запускает планировщик (cron, раз в минуту), а не веб-запрос.
 */
class SiteRebuilder
{
    public const MODES = ['manual' => 'Только вручную', 'on_change' => 'Автоматически после изменений', 'daily' => 'Раз в сутки, если были изменения'];

    public static function command(): string
    {
        return trim((string) config('services.site.rebuild_command', ''));
    }

    public function requestManual(): void
    {
        Settings::set('site_rebuild_requested_at', now()->toIso8601String());
    }

    public function dirtyAt(): ?Carbon
    {
        $v = Settings::get('site_dirty_at');

        return $v ? Carbon::parse($v) : null;
    }

    public function lastSuccess(): ?SiteBuild
    {
        return SiteBuild::query()->where('status', 'success')->latest('id')->first();
    }

    public function hasPendingChanges(): bool
    {
        $dirty = $this->dirtyAt();
        $last = $this->lastSuccess();

        return $dirty !== null && ($last === null || $dirty->gt($last->started_at));
    }

    public function dueTrigger(?Carbon $now = null): ?string
    {
        $now ??= now();
        if (SiteBuild::query()->where('status', 'running')->where('started_at', '>', $now->copy()->subMinutes(20))->exists()) {
            return null;
        }
        if (Settings::get('site_rebuild_requested_at')) {
            return 'manual';
        }
        if (! $this->hasPendingChanges()) {
            return null;
        }
        // Упавшая сборка не перезапускается каждую минуту: ждём 15 минут или новых изменений.
        $lastFailed = SiteBuild::query()->where('status', 'failed')->latest('id')->first();
        if ($lastFailed && $lastFailed->started_at->gt($now->copy()->subMinutes(15)) && $this->dirtyAt()->lte($lastFailed->started_at)) {
            return null;
        }

        return match (Settings::get('site_rebuild_mode', 'on_change')) {
            'on_change' => $this->dirtyAt()->lte($now->copy()->subMinutes(max(0, Settings::int('site_rebuild_debounce_minutes', 5)))) ? 'auto' : null,
            'daily' => (int) $now->format('G') === Settings::int('site_rebuild_daily_hour', 4) ? 'daily' : null,
            default => null,
        };
    }

    public function run(string $trigger): SiteBuild
    {
        Settings::set('site_rebuild_requested_at', '');
        $build = SiteBuild::query()->create(['trigger' => $trigger, 'status' => 'running', 'started_at' => now()]);

        $command = self::command();
        if ($command === '') {
            $build->update(['status' => 'failed', 'output' => 'Команда сборки не задана (SITE_REBUILD_COMMAND в .env)', 'finished_at' => now()]);

            return $build;
        }

        try {
            $result = Process::timeout(1200)->env(['SHOWCASE_API_URL' => rtrim((string) config('app.url'), '/').'/api/v1/showcase'])->run($command);
            $output = trim($result->output()."\n".$result->errorOutput());
            $build->update([
                'status' => $result->successful() ? 'success' : 'failed',
                'output' => mb_substr($output, -8000),
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $build->update(['status' => 'failed', 'output' => $e->getMessage(), 'finished_at' => now()]);
        }

        return $build;
    }
}
