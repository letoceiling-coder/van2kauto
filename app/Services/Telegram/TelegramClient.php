<?php

namespace App\Services\Telegram;

use App\Support\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramClient
{
    public function __construct(private ?string $token = null) {}

    public function token(): string
    {
        return (string) ($this->token ?? Settings::get('tg_bot_token', ''));
    }

    public function configured(): bool
    {
        return $this->token() !== '';
    }

    /** @return array<string, mixed> */
    public function call(string $method, array $params = []): array
    {
        $token = $this->token();
        if ($token === '') {
            throw new RuntimeException('Токен бота не задан в настройках');
        }

        $response = Http::timeout(15)->asJson()->post("https://api.telegram.org/bot{$token}/{$method}", $params);
        $json = $response->json() ?? [];

        if (! ($json['ok'] ?? false)) {
            $exception = new TelegramException((string) ($json['description'] ?? 'HTTP '.$response->status()), (int) ($json['error_code'] ?? $response->status()));
            throw $exception;
        }

        return is_array($json['result'] ?? null) ? $json['result'] : ['value' => $json['result'] ?? null];
    }

    public function sendMessage(int|string $chatId, string $text, array $extra = []): array
    {
        return $this->call('sendMessage', ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true] + $extra);
    }

    public function sendPhoto(int|string $chatId, string $photo, string $caption, array $extra = []): array
    {
        return $this->call('sendPhoto', ['chat_id' => $chatId, 'photo' => $photo, 'caption' => $caption, 'parse_mode' => 'HTML'] + $extra);
    }

    public function editMessageText(int|string $chatId, int $messageId, string $text, array $extra = []): array
    {
        return $this->call('editMessageText', ['chat_id' => $chatId, 'message_id' => $messageId, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true] + $extra);
    }

    public function answerCallback(string $callbackId, string $text = ''): void
    {
        $this->call('answerCallbackQuery', ['callback_query_id' => $callbackId, 'text' => $text]);
    }

    public function getMe(): array
    {
        return $this->call('getMe');
    }

    public function setWebhook(string $url, string $secret): array
    {
        return $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
            'drop_pending_updates' => false,
        ]);
    }

    public function deleteWebhook(): array
    {
        return $this->call('deleteWebhook');
    }

    public function getWebhookInfo(): array
    {
        return $this->call('getWebhookInfo');
    }
}
