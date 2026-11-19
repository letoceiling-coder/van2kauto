<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Настройки админки (ключ → значение) с кэшем. Секреты (токен бота) хранятся зашифрованными.
 */
class Settings
{
    public const SECRET_KEYS = ['tg_bot_token'];

    /** Значения по умолчанию: всё, что можно поменять в админке без кода. */
    public const DEFAULTS = [
        'tg_bot_token' => '',
        'tg_bot_username' => '',
        'tg_webhook_secret' => '',
        'tg_manager_chat_ids' => '',
        'tg_text_welcome' => "Здравствуйте! Это VAN2KAUTO — привозим авто из Китая, Кореи и Японии под ключ.\n\nОставьте номер телефона — менеджер подберёт варианты и посчитает цену под ключ.",
        'tg_text_welcome_car' => "Здравствуйте! Вас интересует {car}.\n\nОставьте номер телефона — менеджер пришлёт актуальные варианты и точную цену под ключ.",
        'tg_text_contact_button' => '📱 Отправить номер',
        'tg_text_thanks' => 'Спасибо! Заявка принята, менеджер свяжется с вами в ближайшее время.',
        'tg_text_message_received' => 'Сообщение получили, менеджер скоро ответит.',
        'tg_text_lead_site' => 'Заявка с сайта',
        'site_url' => 'https://vanauto.neeklo.ru',
        'site_rebuild_mode' => 'on_change',
        'site_rebuild_debounce_minutes' => '5',
        'site_rebuild_command' => '',
        'site_rebuild_daily_hour' => '4',
        'site_dirty_at' => '',
        'site_rebuild_requested_at' => '',
        'showcase_version' => '1',
        'leads_allowed_origins' => 'https://vanauto.neeklo.ru',
        'utilization_max_hp' => '160',
        'utilization_min_age' => '3',
        'utilization_max_age' => '5',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();
        $value = $all[$key] ?? null;

        if ($value === null || $value === '') {
            return $default ?? (self::DEFAULTS[$key] ?? null);
        }

        return $value;
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = self::get($key);

        return is_numeric($v) ? (int) $v : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        if (in_array($key, self::SECRET_KEYS, true) && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings:all');
    }

    /** @param  array<string, string|null>  $values */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    /** @return array<string, string|null> */
    public static function all(): array
    {
        return Cache::remember('settings:all', 300, function () {
            $rows = Setting::query()->pluck('value', 'key')->all();
            foreach (self::SECRET_KEYS as $key) {
                if (! empty($rows[$key])) {
                    try {
                        $rows[$key] = Crypt::decryptString($rows[$key]);
                    } catch (\Throwable) {
                        $rows[$key] = null;
                    }
                }
            }

            return $rows;
        });
    }

    /** @return list<int> */
    public static function managerChatIds(): array
    {
        $raw = (string) self::get('tg_manager_chat_ids', '');

        return array_values(array_filter(array_map(
            fn ($v) => (int) trim($v),
            preg_split('/[\s,;]+/', $raw) ?: []
        )));
    }

    public static function bumpShowcaseVersion(): void
    {
        self::set('showcase_version', (string) (self::int('showcase_version', 1) + 1));
        self::set('site_dirty_at', now()->toIso8601String());
    }
}
