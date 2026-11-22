<?php

namespace App\Services\Telegram;

use App\Models\Lead;
use App\Support\Settings;

/** Отправка заявки менеджерам в Telegram с кнопками статуса. */
class LeadNotifier
{
    public function __construct(private readonly TelegramClient $telegram) {}

    public function notify(Lead $lead): bool
    {
        $chats = Settings::managerChatIds();
        if (! $this->telegram->configured() || $chats === []) {
            $lead->update(['notify_error' => 'Бот или чат менеджеров не настроены', 'notify_attempts' => $lead->notify_attempts + 1]);

            return false;
        }

        $messages = $lead->tg_messages ?? [];
        $errors = [];
        foreach ($chats as $chatId) {
            if (isset($messages[(string) $chatId])) {
                continue;
            }
            try {
                $result = $this->telegram->sendMessage($chatId, $this->text($lead), ['reply_markup' => $this->keyboard($lead)]);
                $messages[(string) $chatId] = (int) ($result['message_id'] ?? 0);
                $this->remember($lead, $chatId, (int) ($result['message_id'] ?? 0));
            } catch (\Throwable $e) {
                $errors[] = "{$chatId}: ".$e->getMessage();
            }
        }

        $ok = $errors === [];
        $lead->update([
            'tg_messages' => $messages,
            'notified_at' => $ok ? now() : $lead->notified_at,
            'notify_error' => $ok ? null : implode('; ', $errors),
            'notify_attempts' => $lead->notify_attempts + 1,
        ]);

        return $ok;
    }

    public function remember(Lead $lead, int $chatId, int $messageId): void
    {
        if ($messageId > 0) {
            \Illuminate\Support\Facades\DB::table('lead_tg_messages')->insertOrIgnore([
                'lead_id' => $lead->id, 'chat_id' => $chatId, 'message_id' => $messageId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** Новое сообщение клиента в чате — реплаем на исходную заявку в каждом чате менеджеров. true — ушло хотя бы в один чат. */
    public function forwardClientMessage(Lead $lead, string $text): bool
    {
        if (! $this->telegram->configured() || Settings::managerChatIds() === []) {
            return false;
        }
        $sent = false;
        $e = fn (?string $v) => htmlspecialchars((string) $v, ENT_QUOTES);
        foreach (Settings::managerChatIds() as $chatId) {
            $root = (int) (($lead->tg_messages ?? [])[(string) $chatId] ?? 0);
            try {
                $res = $this->telegram->sendMessage($chatId, "💬 <b>#{$lead->id}</b> {$e($lead->name ?: $lead->contact)}:\n{$e($text)}\n\n<i>Ответьте реплаем — ответ уйдёт клиенту</i>", $root ? ['reply_to_message_id' => $root, 'allow_sending_without_reply' => true] : []);
                $this->remember($lead, $chatId, (int) ($res['message_id'] ?? 0));
                $sent = true;
            } catch (\Throwable) {
                // не дошло — LeadMessage останется без forwarded_at, его дошлёт leads:renotify
            }
        }

        return $sent;
    }

    /** Обновить сообщения у всех менеджеров после смены статуса. */
    public function refresh(Lead $lead): void
    {
        foreach ($lead->tg_messages ?? [] as $chatId => $messageId) {
            if (! $messageId) {
                continue;
            }
            try {
                $this->telegram->editMessageText($chatId, (int) $messageId, $this->text($lead), ['reply_markup' => $this->keyboard($lead)]);
            } catch (\Throwable) {
                // сообщение могли удалить — не критично
            }
        }
    }

    public function text(Lead $lead): string
    {
        $e = fn (?string $v) => htmlspecialchars((string) $v, ENT_QUOTES);
        $icon = $lead->source === 'bot' ? '🤖' : '🔥';
        $lines = ["{$icon} <b>Заявка #{$lead->id}</b> · ".(Lead::SOURCES[$lead->source] ?? $lead->source)];

        if ($lead->car_title || $lead->car) {
            $title = $lead->car_title ?: $lead->car?->title().' '.$lead->car?->year;
            $url = $lead->car_slug ? rtrim((string) Settings::get('site_url'), '/').'/catalog/'.$lead->car_slug.'/' : null;
            $lines[] = '🚗 '.($url ? "<a href=\"{$e($url)}\">{$e($title)}</a>" : $e($title));
        }
        if ($lead->name) {
            $lines[] = '👤 '.$e($lead->name);
        }
        $lines[] = '📞 '.$e($lead->contact).($lead->channel ? ' · '.$e($lead->channel) : '');
        if ($lead->comment) {
            $lines[] = '💬 '.$e($lead->comment);
        }
        if ($lead->chat_token) {
            $lines[] = '<i>Чат на сайте: ответьте на это сообщение реплаем — клиент увидит ответ сразу.</i>';
        }
        foreach ((array) $lead->context as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $lines[] = '• '.$e((string) $key).': '.$e(is_array($value) ? implode(', ', $value) : (string) $value);
        }
        if ($lead->page) {
            $lines[] = '🔗 '.$e($lead->page);
        }
        $utm = array_filter((array) $lead->utm);
        if ($utm) {
            $lines[] = '📊 '.$e(implode(' / ', $utm));
        }
        $status = Lead::STATUSES[$lead->status] ?? $lead->status;
        $lines[] = '';
        $lines[] = "Статус: <b>{$e($status)}</b>".($lead->manager ? ' · '.$e($lead->manager) : '');

        return implode("\n", $lines);
    }

    /** @return array<string, mixed> */
    public function keyboard(Lead $lead): array
    {
        $buttons = match ($lead->status) {
            'new' => [['text' => '✅ Взял в работу', 'callback_data' => "lead:{$lead->id}:in_work"], ['text' => '🚫 Спам', 'callback_data' => "lead:{$lead->id}:spam"]],
            'in_work' => [['text' => '🏁 Закрыть', 'callback_data' => "lead:{$lead->id}:done"], ['text' => '↩️ Вернуть', 'callback_data' => "lead:{$lead->id}:new"]],
            default => [['text' => '↩️ Вернуть в новые', 'callback_data' => "lead:{$lead->id}:new"]],
        };

        return ['inline_keyboard' => [$buttons]];
    }
}
