<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadMessage;
use App\Services\Telegram\LeadNotifier;
use App\Services\Telegram\TelegramClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Живой чат «спросить менеджера» на сайте.
 * Клиент пишет → заявка (source=chat) + сообщение менеджерам в Telegram.
 * Менеджер отвечает реплаем в Telegram или из админки → ответ появляется у клиента на сайте (и в боте, если клиент оттуда).
 */
class ChatService
{
    public function __construct(
        private readonly LeadService $leads,
        private readonly LeadNotifier $notifier,
        private readonly TelegramClient $telegram,
    ) {}

    /** @param  array<string, mixed>  $data */
    public function start(array $data, string $text): Lead
    {
        $lead = $this->leads->create($data + [
            'source' => 'chat',
            'contact' => $data['contact'] ?? 'чат на сайте',
            'comment' => $text,
            'chat_token' => Str::random(40),
            'last_client_message_at' => now(),
        ]);
        $lead->messages()->create(['sender' => 'client', 'text' => $text]);

        return $lead;
    }

    public function clientMessage(Lead $lead, string $text): LeadMessage
    {
        $msg = $lead->messages()->create(['sender' => 'client', 'text' => $text]);
        $lead->update(['last_client_message_at' => now(), 'status' => $lead->status === 'done' ? 'new' : $lead->status]);
        $this->notifier->forwardClientMessage($lead, $text);

        return $msg;
    }

    public function updateContact(Lead $lead, ?string $name, string $contact): void
    {
        $lead->update(array_filter(['contact' => $contact, 'name' => $name ?: null]));
        $this->notifier->forwardClientMessage($lead, "📞 Оставил контакт: {$contact}".($name ? " ({$name})" : ''));
        $this->notifier->refresh($lead);
    }

    public function managerReply(Lead $lead, string $text, ?string $author): LeadMessage
    {
        $msg = $lead->messages()->create(['sender' => 'manager', 'author' => $author, 'text' => $text]);
        if ($lead->status === 'new') {
            $this->leads->setStatus($lead, 'in_work', $author);
        }
        // Клиент пришёл из бота — дублируем ответ ему в личку.
        if ($lead->tg_user_chat_id && $this->telegram->configured()) {
            try {
                $this->telegram->sendMessage($lead->tg_user_chat_id, htmlspecialchars($text, ENT_NOQUOTES));
            } catch (\Throwable) {
            }
        }

        return $msg;
    }

    public function leadByTelegramReply(int $chatId, int $messageId): ?Lead
    {
        $leadId = DB::table('lead_tg_messages')->where('chat_id', $chatId)->where('message_id', $messageId)->value('lead_id');

        return $leadId ? Lead::query()->find($leadId) : null;
    }
}
