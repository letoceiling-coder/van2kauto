<?php

namespace App\Services\Telegram;

use App\Models\Lead;
use App\Models\ShowcaseCar;
use App\Models\TgSubscriber;
use App\Services\LeadService;
use App\Support\Settings;

/**
 * Логика бота. Тексты — в настройках админки.
 *  /start [car_<slug>|<метка>] → подписчик + приветствие + кнопка «отправить номер»
 *  контакт → заявка менеджерам
 *  свободный текст → заявка (не чаще раза в 10 минут, иначе дописывается к последней)
 *  /id → показать chat_id (чтобы добавить чат менеджеров в настройки)
 *  кнопки в чате менеджеров → смена статуса заявки
 */
class BotUpdateHandler
{
    public function __construct(
        private readonly TelegramClient $telegram,
        private readonly LeadService $leads,
    ) {}

    public function handle(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->callback($update['callback_query']);

            return;
        }
        if (isset($update['my_chat_member'])) {
            $this->chatMember($update['my_chat_member']);

            return;
        }
        $message = $update['message'] ?? null;
        if (! is_array($message)) {
            return;
        }

        $chat = $message['chat'] ?? [];
        $chatId = (int) ($chat['id'] ?? 0);
        $text = trim((string) ($message['text'] ?? ''));

        if ($text === '/id' || str_starts_with($text, '/id@')) {
            $this->telegram->sendMessage($chatId, "chat_id: <code>{$chatId}</code>");

            return;
        }

        if (($chat['type'] ?? '') !== 'private') {
            return; // в группах бот только присылает заявки
        }

        $subscriber = $this->subscriber($message['from'] ?? [], $chatId);

        if (str_starts_with($text, '/start')) {
            $this->start($subscriber, trim(substr($text, 6)));

            return;
        }

        if (isset($message['contact'])) {
            $this->contact($subscriber, $message['contact']);

            return;
        }

        if ($text !== '') {
            $this->freeText($subscriber, $text);
        }
    }

    private function subscriber(array $from, int $chatId): TgSubscriber
    {
        $subscriber = TgSubscriber::query()->firstOrNew(['chat_id' => $chatId]);
        $subscriber->fill([
            'username' => $from['username'] ?? $subscriber->username,
            'first_name' => $from['first_name'] ?? $subscriber->first_name,
            'last_name' => $from['last_name'] ?? $subscriber->last_name,
            'is_blocked' => false,
            'last_seen_at' => now(),
        ])->save();

        return $subscriber;
    }

    private function start(TgSubscriber $subscriber, string $param): void
    {
        if ($param !== '') {
            $subscriber->update(['start_param' => substr($param, 0, 255)]);
        }
        $car = $this->carFromParam($subscriber->start_param);
        $text = $car
            ? str_replace('{car}', trim($car->title().' '.$car->year), (string) Settings::get('tg_text_welcome_car'))
            : (string) Settings::get('tg_text_welcome');

        $this->telegram->sendMessage($subscriber->chat_id, htmlspecialchars($text, ENT_NOQUOTES), ['reply_markup' => $this->contactKeyboard()]);
    }

    private function contact(TgSubscriber $subscriber, array $contact): void
    {
        $phone = (string) ($contact['phone_number'] ?? '');
        $subscriber->update(['phone' => substr($phone, 0, 32)]);
        $this->createLead($subscriber, $phone, null);
        $this->telegram->sendMessage($subscriber->chat_id, htmlspecialchars((string) Settings::get('tg_text_thanks'), ENT_NOQUOTES), ['reply_markup' => ['remove_keyboard' => true]]);
    }

    private function freeText(TgSubscriber $subscriber, string $text): void
    {
        $recent = Lead::query()
            ->where('tg_user_chat_id', $subscriber->chat_id)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest('id')
            ->first();

        if ($recent) {
            $recent->update(['comment' => trim($recent->comment."\n".$text)]);
            app(LeadNotifier::class)->refresh($recent);
        } else {
            $this->createLead($subscriber, $subscriber->phone ?: ($subscriber->username ? '@'.$subscriber->username : 'tg:'.$subscriber->chat_id), $text);
        }
        $this->telegram->sendMessage($subscriber->chat_id, htmlspecialchars((string) Settings::get('tg_text_message_received'), ENT_NOQUOTES));
    }

    private function createLead(TgSubscriber $subscriber, string $contact, ?string $comment): Lead
    {
        $car = $this->carFromParam($subscriber->start_param);
        $username = $subscriber->username ? '@'.$subscriber->username : null;

        return $this->leads->create([
            'source' => 'bot',
            'name' => trim($subscriber->first_name.' '.$subscriber->last_name) ?: null,
            'contact' => $contact,
            'channel' => $username ? 'Telegram '.$username : 'Telegram',
            'comment' => $comment,
            'car_slug' => $car?->slug,
            'tg_user_chat_id' => $subscriber->chat_id,
            'utm' => $subscriber->start_param && ! $car ? ['utm_source' => $subscriber->start_param] : null,
        ]);
    }

    private function carFromParam(?string $param): ?ShowcaseCar
    {
        if (! $param || ! str_starts_with($param, 'car_')) {
            return null;
        }

        return ShowcaseCar::query()->where('slug', substr($param, 4))->first();
    }

    private function callback(array $query): void
    {
        $data = (string) ($query['data'] ?? '');
        $chatId = (int) data_get($query, 'message.chat.id');

        if (! preg_match('/^lead:(\d+):(new|in_work|done|spam)$/', $data, $m) || ! in_array($chatId, Settings::managerChatIds(), true)) {
            $this->telegram->answerCallback((string) $query['id'], 'Недоступно');

            return;
        }
        $lead = Lead::query()->find((int) $m[1]);
        if (! $lead) {
            $this->telegram->answerCallback((string) $query['id'], 'Заявка не найдена');

            return;
        }
        $from = $query['from'] ?? [];
        $manager = isset($from['username']) ? '@'.$from['username'] : trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? ''));
        $this->leads->setStatus($lead, $m[2], $manager ?: null);
        $this->telegram->answerCallback((string) $query['id'], Lead::STATUSES[$m[2]]);
    }

    private function chatMember(array $member): void
    {
        $chatId = (int) data_get($member, 'chat.id');
        $status = (string) data_get($member, 'new_chat_member.status');
        if (data_get($member, 'chat.type') === 'private') {
            TgSubscriber::query()->where('chat_id', $chatId)->update(['is_blocked' => in_array($status, ['kicked', 'left'], true)]);
        }
    }

    /** @return array<string, mixed> */
    private function contactKeyboard(): array
    {
        return [
            'keyboard' => [[['text' => (string) Settings::get('tg_text_contact_button'), 'request_contact' => true]]],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }
}
