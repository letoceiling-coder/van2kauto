<?php

namespace App\Services\Automarket;

use App\Exceptions\AutomarketAuthException;
use App\Exceptions\AutomarketCaptchaException;
use App\Models\AuctionLot;
use App\Models\AuctionMaker;
use App\Models\AuctionRun;
use Throwable;

class ParseRunner
{
    public function __construct(private readonly CatalogSync $sync) {}

    public function run(AuctionRun $run): void
    {
        $run->update([
            'status' => 'running',
            'started_at' => $run->started_at ?? now(),
            'finished_at' => null,
            'error_message' => null,
            'needs_captcha' => false,
            'needs_auth' => false,
        ]);
        $run->appendLog('Старт: '.$run->mode.' · '.$this->sync->proxyLabel());

        try {
            match ($run->mode) {
                'new' => $this->runNew($run),
                'full' => $this->runFull($run),
                default => throw new \InvalidArgumentException('Unknown mode'),
            };
            $run->update(['status' => 'completed', 'finished_at' => now()]);
            $run->appendLog('Завершено');
        } catch (AutomarketCaptchaException $e) {
            $run->update([
                'status' => 'needs_captcha',
                'needs_captcha' => true,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $run->appendLog($e->getMessage());
        } catch (AutomarketAuthException $e) {
            $run->update([
                'status' => 'needs_auth',
                'needs_auth' => true,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $run->appendLog($e->getMessage());
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $run->appendLog('Ошибка: '.$e->getMessage());
        }
    }

    private function runNew(AuctionRun $run, int $staleBreak = 5): void
    {
        $page = max(1, (int) $run->pages_done ?: 1);
        $consecutive = 0;
        $delay = $this->delay();

        $stride = 0;
        while ($page <= 80 && $consecutive < $staleBreak) {
            $result = $this->sync->searchPage($page);
            $run->pages_done = $page;
            $run->save();
            $received = count($result['lots']);
            $stride = max($stride, $received);
            $run->appendLog("Новые, стр. {$page}: лотов {$received} из ".$result['total']);

            foreach ($result['lots'] as $row) {
                if (AuctionLot::query()->whereKey($row['lot_id'])->exists()) {
                    $consecutive++;
                    $run->items_skipped++;
                    if ($consecutive >= $staleBreak) {
                        $run->appendLog("Остановка: {$staleBreak} подряд уже в БД");
                        break;
                    }

                    continue;
                }
                $consecutive = 0;
                $this->sync->saveListRow($row);
                $this->sync->saveDetail((int) $row['lot_id']);
                $run->items_saved++;
                usleep($delay);
            }

            if ($received === 0 || ($stride > 0 && $received < $stride) || ($result['total'] > 0 && ($page - 1) * $stride + $received >= $result['total'])) {
                break;
            }
            $page++;
            usleep($delay);
        }
        $run->save();
    }

    private function runFull(AuctionRun $run): void
    {
        $makersCount = $this->sync->syncMakers();
        $run->appendLog("Марок: {$makersCount}");
        $delay = $this->delay();
        $resumeMaker = (int) $run->maker_id;
        $resumePage = max(1, (int) $run->pages_done ?: 1);
        $started = false;

        $makers = AuctionMaker::query()->orderBy('name')->get();
        foreach ($makers as $maker) {
            if ($resumeMaker && ! $started) {
                if ((int) $maker->maker_id !== $resumeMaker) {
                    continue;
                }
                $started = true;
            }
            $models = $this->sync->syncModels((int) $maker->maker_id);
            $run->maker_id = (int) $maker->maker_id;
            $run->appendLog($maker->name.': моделей '.$models);
            $page = (int) $maker->maker_id === $resumeMaker && $resumePage > 1 ? $resumePage : 1;
            $started = true;
            $stride = 0;

            while ($page <= 5000) {
                $result = $this->sync->searchPage($page, ['mrk' => $maker->maker_id]);
                $run->pages_done = $page;
                $run->maker_id = (int) $maker->maker_id;
                $run->save();
                $received = count($result['lots']);
                $stride = max($stride, $received);
                $run->appendLog($maker->name." стр. {$page}: {$received} из ".$result['total']);

                if ($received === 0) {
                    break;
                }
                foreach ($result['lots'] as $row) {
                    $this->sync->saveListRow($row);
                    try {
                        $this->sync->saveDetail((int) $row['lot_id']);
                    } catch (AutomarketCaptchaException|AutomarketAuthException $e) {
                        throw $e;
                    } catch (Throwable $e) {
                        $run->items_skipped++;
                        $run->appendLog('lot '.$row['lot_id'].': '.$e->getMessage());
                    }
                    $run->items_saved++;
                    if ($run->items_saved % 20 === 0) {
                        $run->save();
                    }
                    usleep($delay);
                }
                $done = ($stride > 0 && $received < $stride)
                    || ($result['total'] > 0 && ($page - 1) * $stride + $received >= $result['total']);
                $page++;
                $run->pages_done = $page;
                $run->save();
                if ($done) {
                    break;
                }
                usleep($delay);
            }
        }

        $marked = AuctionLot::query()
            ->where('status', 'active')
            ->where(function ($query) use ($run) {
                $query->whereNull('scraped_at')->orWhere('scraped_at', '<', $run->started_at);
            })
            ->update(['status' => 'absent']);
        $run->items_marked_absent = $marked;
        $run->save();
        $run->appendLog('Нет на аукционе (absent): '.$marked);
    }

    private function delay(): int
    {
        return (int) config('automarket.request_delay_ms', 400) * 1000;
    }
}
