<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteBuild;
use App\Services\Site\SiteRebuilder;
use App\Services\Telegram\TelegramClient;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsAdminController extends Controller
{
    public const TEXT_KEYS = ['tg_text_welcome', 'tg_text_welcome_car', 'tg_text_contact_button', 'tg_text_thanks', 'tg_text_message_received'];

    public function show(SiteRebuilder $rebuilder, TelegramClient $telegram): View
    {
        $webhook = null;
        if ($telegram->configured()) {
            try {
                $webhook = $telegram->getWebhookInfo();
            } catch (\Throwable $e) {
                $webhook = ['error' => $e->getMessage()];
            }
        }

        return view('admin.settings', [
            's' => fn (string $k) => Settings::get($k),
            'hasToken' => $telegram->configured(),
            'webhook' => $webhook,
            'webhookUrl' => $this->webhookUrl(),
            'modes' => SiteRebuilder::MODES,
            'builds' => SiteBuild::query()->latest('id')->limit(10)->get(),
            'pending' => $rebuilder->hasPendingChanges(),
            'textKeys' => self::TEXT_KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tg_bot_token' => ['nullable', 'string', 'max:100', 'regex:/^\d+:[\w-]{20,}$/'],
            'tg_manager_chat_ids' => ['nullable', 'string', 'max:500'],
            'tg_text_welcome' => ['nullable', 'string', 'max:3000'],
            'tg_text_welcome_car' => ['nullable', 'string', 'max:3000'],
            'tg_text_contact_button' => ['nullable', 'string', 'max:60'],
            'tg_text_thanks' => ['nullable', 'string', 'max:2000'],
            'tg_text_message_received' => ['nullable', 'string', 'max:2000'],
            'site_url' => ['nullable', 'url', 'max:255'],
            'leads_allowed_origins' => ['nullable', 'string', 'max:1000'],
            'site_rebuild_mode' => ['nullable', Rule::in(array_keys(SiteRebuilder::MODES))],
            'site_rebuild_debounce_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'site_rebuild_daily_hour' => ['nullable', 'integer', 'min:0', 'max:23'],
            'site_rebuild_command' => ['nullable', 'string', 'max:500'],
            'utilization_max_hp' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'utilization_min_age' => ['nullable', 'integer', 'min:0', 'max:30'],
            'utilization_max_age' => ['nullable', 'integer', 'min:0', 'max:30'],
        ], ['tg_bot_token.regex' => 'Токен выглядит как 123456789:AA... — скопируйте его из @BotFather']);

        // Пустое поле токена = «не менять» (токен в форму не выводим).
        if (empty($data['tg_bot_token'])) {
            unset($data['tg_bot_token']);
        } else {
            Settings::set('tg_bot_username', '');
        }
        Settings::setMany(array_map(fn ($v) => $v === null ? '' : (string) $v, $data));

        return back()->with('ok', 'Настройки сохранены');
    }

    public function testBot(TelegramClient $telegram): RedirectResponse
    {
        try {
            $me = $telegram->getMe();
            Settings::set('tg_bot_username', (string) ($me['username'] ?? ''));
            $sent = 0;
            foreach (Settings::managerChatIds() as $chatId) {
                $telegram->sendMessage($chatId, '✅ Тест: бот VAN2KAUTO подключён, заявки будут приходить сюда.');
                $sent++;
            }

            return back()->with('ok', 'Бот @'.($me['username'] ?? '?').' на связи. Тестовых сообщений менеджерам: '.$sent);
        } catch (\Throwable $e) {
            return back()->with('ok', 'Ошибка Telegram: '.$e->getMessage());
        }
    }

    public function setWebhook(TelegramClient $telegram): RedirectResponse
    {
        $secret = Str::random(48);
        try {
            $telegram->setWebhook($this->webhookUrl(), $secret);
            Settings::set('tg_webhook_secret', $secret);

            return back()->with('ok', 'Вебхук установлен: '.$this->webhookUrl());
        } catch (\Throwable $e) {
            return back()->with('ok', 'Не удалось установить вебхук: '.$e->getMessage());
        }
    }

    public function deleteWebhook(TelegramClient $telegram): RedirectResponse
    {
        try {
            $telegram->deleteWebhook();
            Settings::set('tg_webhook_secret', '');

            return back()->with('ok', 'Вебхук снят');
        } catch (\Throwable $e) {
            return back()->with('ok', 'Ошибка: '.$e->getMessage());
        }
    }

    public function rebuild(SiteRebuilder $rebuilder): RedirectResponse
    {
        $rebuilder->requestManual();

        return back()->with('ok', 'Пересборка поставлена в очередь — начнётся в течение минуты.');
    }

    private function webhookUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/api/telegram/webhook';
    }
}
