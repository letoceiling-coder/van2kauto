<?php

namespace App\Services\Che168;

use App\Exceptions\Che168CaptchaException;
use App\Models\Listing;
use App\Models\ScrapeRun;
use Throwable;

class ParseRunnerService
{
    public function __construct(
        private readonly CatalogSyncService $sync,
    ) {}

    public function run(ScrapeRun $run): void
    {
        $run->update([
            'status' => 'running',
            'started_at' => $run->started_at ?? now(),
            'error_message' => null,
            'needs_captcha' => false,
            'finished_at' => null,
        ]);
        $run->appendLog('Старт: '.$run->mode);

        try {
            match ($run->mode) {
                'new' => $this->runNew($run),
                'full' => $this->runFull($run),
                default => throw new \InvalidArgumentException('Unknown mode'),
            };
            $run->update(['status' => 'completed', 'finished_at' => now()]);
            $run->appendLog('Завершено');
        } catch (Che168CaptchaException $e) {
            $run->update([
                'status' => 'needs_captcha',
                'needs_captcha' => true,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $run->appendLog('Нужна captcha: /che168/captcha?force=captcha');
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $run->appendLog('Ошибка: '.$e->getMessage());
        }
    }

    private function runNew(ScrapeRun $run, int $maxPages = 80, int $staleBreak = 5): void
    {
        $consecutiveExisting = 0;
        $page = 1;

        while ($page <= $maxPages) {
            $stats = $this->sync->syncSearchPageNewOnly($page);
            $run->pages_done = $page;
            $run->items_saved += $stats['inserted'];
            $run->items_skipped += $stats['skipped'];
            $run->save();
            $run->appendLog("Стр. {$page}: новых {$stats['inserted']}, пропуск {$stats['skipped']}");

            $consecutiveExisting = $stats['consecutive_existing'];
            if ($consecutiveExisting >= $staleBreak) {
                $run->appendLog("Остановка: {$staleBreak} подряд уже в БД");
                break;
            }

            if ($page >= ($stats['pagecount'] ?? $page)) {
                break;
            }

            $page++;
            usleep((int) config('che168.request_delay_ms', 400) * 1000);
        }
    }

    private function runFull(ScrapeRun $run): void
    {
        $brandCount = $this->sync->syncBrands();
        $run->appendLog("Справочник марок: {$brandCount}");

        $page = max(1, (int) $run->pages_done);
        $pageCount = $page;
        $delay = (int) config('che168.request_delay_ms', 400) * 1000;

        while ($page <= $pageCount && $page <= 5000) {
            $stats = $this->sync->syncSearchPage($page);
            $pageCount = max($page, (int) ($stats['pagecount'] ?: $page));
            $run->pages_done = $page;
            $run->save();
            $run->appendLog("Каталог {$page}/{$pageCount}: объявлений на странице {$stats['saved']}");

            foreach ($stats['infoids'] ?? [] as $id) {
                if ($id <= 0) {
                    continue;
                }

                try {
                    $this->sync->syncListingDetail($id);
                } catch (Che168CaptchaException $e) {
                    throw $e;
                } catch (Throwable $e) {
                    $run->items_skipped++;
                    $run->appendLog('infoid '.$id.': '.$e->getMessage());

                    continue;
                }

                $run->items_saved++;
                if ($run->items_saved % 20 === 0) {
                    $run->save();
                    $run->appendLog("Карточек с фото и характеристиками: {$run->items_saved}");
                }
                usleep($delay);
            }

            if ($stats['saved'] === 0) {
                break;
            }

            $page++;
            $run->pages_done = $page;
            $run->save();
            usleep($delay);
        }

        $run->pages_done = max(1, $page - 1);

        if ($page > $pageCount && $pageCount > 0) {
            $started = $run->started_at ?? $run->fresh()->started_at;
            $marked = Listing::query()
                ->where('status', 'active')
                ->where(function ($q) use ($started) {
                    $q->whereNull('scraped_at');
                    if ($started) {
                        $q->orWhere('scraped_at', '<', $started);
                    }
                })
                ->update(['status' => 'absent']);

            $run->items_marked_absent = $marked;
            $run->appendLog("Готово. Нет на сайте (absent): {$marked}");
        } else {
            $run->appendLog('Обход каталога не закончен, статус absent не менялся');
        }

        $run->save();
    }
}