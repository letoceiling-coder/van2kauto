<?php

namespace App\Services\Telegram;

use App\Models\TgBroadcast;
use App\Models\TgSubscriber;

/** Рассылка подписчикам бота порциями (≈20 сообщений/с, в пределах лимитов Telegram). */
class BroadcastSender
{
    public function __construct(private readonly TelegramClient $telegram) {}

    public function process(TgBroadcast $broadcast, int $seconds = 50, int $pauseMs = 50): void
    {
        if (! in_array($broadcast->status, ['queued', 'sending'], true)) {
            return;
        }
        $broadcast->update(['status' => 'sending', 'started_at' => $broadcast->started_at ?? now()]);
        $deadline = microtime(true) + $seconds;

        while (microtime(true) < $deadline) {
            $broadcast->refresh();
            if ($broadcast->status !== 'sending') {
                return; // отменили из админки
            }
            $subscriber = TgSubscriber::query()
                ->where('is_blocked', false)
                ->where('id', '>', $broadcast->last_subscriber_id)
                ->orderBy('id')
                ->first();
            if (! $subscriber) {
                $broadcast->update(['status' => 'done', 'finished_at' => now()]);

                return;
            }
            $this->sendOne($broadcast, $subscriber);
            if ($pauseMs > 0) {
                usleep($pauseMs * 1000);
            }
        }
    }

    public function sendOne(TgBroadcast $broadcast, TgSubscriber $subscriber): bool
    {
        $extra = [];
        if ($broadcast->button_text && $broadcast->button_url) {
            $extra['reply_markup'] = ['inline_keyboard' => [[['text' => $broadcast->button_text, 'url' => $broadcast->button_url]]]];
        }
        $ok = false;
        try {
            $broadcast->photo_url
                ? $this->telegram->sendPhoto($subscriber->chat_id, $broadcast->photo_url, $broadcast->text, $extra)
                : $this->telegram->sendMessage($subscriber->chat_id, $broadcast->text, $extra);
            $ok = true;
        } catch (TelegramException $e) {
            if ($e->isBlocked()) {
                $subscriber->update(['is_blocked' => true]);
            }
        } catch (\Throwable) {
            // сеть — посчитаем как ошибку и пойдём дальше
        }
        $broadcast->update([
            'last_subscriber_id' => $subscriber->id,
            'sent_count' => $broadcast->sent_count + ($ok ? 1 : 0),
            'failed_count' => $broadcast->failed_count + ($ok ? 0 : 1),
        ]);

        return $ok;
    }
}
