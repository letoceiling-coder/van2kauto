<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\ShowcaseCar;
use App\Models\TgBroadcast;
use App\Models\TgSubscriber;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LeadsAndBotTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abc';

    /** Ответ «Telegram» в текущем тесте; null — успех. */
    private ?\Closure $tg = null;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::setMany(['tg_bot_token' => self::TOKEN, 'tg_manager_chat_ids' => '-1001, 555', 'tg_webhook_secret' => 'sekret']);
        $mid = 100;
        Http::fake(['api.telegram.org/*' => function (Request $r) use (&$mid) {
            return $this->tg ? ($this->tg)($r) : Http::response(['ok' => true, 'result' => ['message_id' => ++$mid]]);
        }]);
    }

    private function car(): ShowcaseCar
    {
        return ShowcaseCar::query()->create([
            'brand' => 'Geely', 'model' => 'Monjaro', 'year' => 2024, 'trim' => '2.0T Flagship', 'power_hp' => 238,
            'price_from_rub' => 3990000, 'is_published' => true, 'slug' => 'geely-monjaro-2024',
        ]);
    }

    private function lead(array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/leads', $extra + [
            'name' => 'Иван', 'contact' => '+7 999 123-45-67', 'channel' => 'Telegram', 'consent' => true,
            'page' => '/catalog/geely-monjaro-2024/', 'utm' => ['utm_source' => 'yandex', 'utm_campaign' => 'monjaro', 'evil' => 'x'],
            'context' => ['Бюджет' => 'до 4 млн', 'Кузов' => ['Кроссовер', 'SUV']],
        ], ['Origin' => 'https://vanauto.neeklo.ru']);
    }

    private function telegramCalls(string $method): \Illuminate\Support\Collection
    {
        return collect(Http::recorded())->map(fn ($pair) => $pair[0])->filter(fn (Request $r) => str_ends_with($r->url(), '/'.$method))->values();
    }

    public function test_site_lead_is_saved_and_sent_to_every_manager_chat(): void
    {
        $car = $this->car();
        $this->lead(['car_slug' => $car->slug])->assertCreated()->assertJsonPath('ok', true);

        $lead = Lead::query()->firstOrFail();
        $this->assertSame($car->id, $lead->showcase_car_id);
        $this->assertSame('Geely Monjaro 2.0T Flagship 2024', $lead->car_title);
        $this->assertSame(['utm_source' => 'yandex', 'utm_campaign' => 'monjaro'], $lead->utm);
        $this->assertSame('Кроссовер, SUV', $lead->context['Кузов']);
        $this->assertNotNull($lead->notified_at);
        $this->assertCount(2, $lead->tg_messages);

        $sent = $this->telegramCalls('sendMessage');
        $this->assertCount(2, $sent);
        $this->assertSame([-1001, 555], $sent->map(fn ($r) => $r['chat_id'])->all());
        $this->assertStringContainsString('+7 999 123-45-67', $sent[0]['text']);
        $this->assertStringContainsString('geely-monjaro-2024', $sent[0]['text']);
        $this->assertStringContainsString('yandex / monjaro', $sent[0]['text']);
        $this->assertSame("lead:{$lead->id}:in_work", $sent[0]['reply_markup']['inline_keyboard'][0][0]['callback_data']);
    }

    public function test_lead_validation_consent_honeypot_and_origin(): void
    {
        $this->lead(['contact' => '12345'])->assertUnprocessable()->assertJsonValidationErrors('contact');
        $this->lead(['consent' => false])->assertUnprocessable()->assertJsonValidationErrors('consent');
        $this->lead(['contact' => '@ivan_auto'])->assertCreated();
        $this->lead(['website' => 'http://spam'])->assertCreated();
        $this->postJson('/api/v1/leads', ['contact' => '+79991234567', 'consent' => true], ['Origin' => 'https://evil.example'])->assertForbidden();

        $this->assertSame(1, Lead::query()->count(), 'honeypot и чужой Origin не создают заявку');
    }

    public function test_lead_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->lead()->assertCreated();
        }
        $this->lead()->assertStatus(429);
    }

    public function test_lead_is_kept_when_telegram_fails_and_renotified_later(): void
    {
        $this->tg = fn () => Http::response(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502);
        $this->lead()->assertCreated();
        $lead = Lead::query()->firstOrFail();
        $this->assertNull($lead->notified_at);
        $this->assertStringContainsString('Bad Gateway', $lead->notify_error);

        $this->tg = null;
        $this->artisan('leads:renotify')->assertSuccessful();
        $this->assertNotNull($lead->fresh()->notified_at);
        $this->assertNull($lead->fresh()->notify_error);
    }

    public function test_webhook_rejects_wrong_secret(): void
    {
        $this->postJson('/api/telegram/webhook', ['update_id' => 1])->assertForbidden();
        $this->postJson('/api/telegram/webhook', ['update_id' => 1], ['X-Telegram-Bot-Api-Secret-Token' => 'nope'])->assertForbidden();
    }

    private function webhook(array $update): void
    {
        $this->postJson('/api/telegram/webhook', $update + ['update_id' => random_int(1, 99999)], ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk();
    }

    private function message(array $message): array
    {
        return ['message' => $message + ['message_id' => 1, 'chat' => ['id' => 777, 'type' => 'private'], 'from' => ['id' => 777, 'username' => 'buyer', 'first_name' => 'Пётр']]];
    }

    public function test_bot_start_from_car_then_contact_creates_lead(): void
    {
        $car = $this->car();
        Settings::set('tg_text_welcome_car', 'Вам интересен {car}?');

        $this->webhook($this->message(['text' => '/start car_'.$car->slug]));
        $sub = TgSubscriber::query()->where('chat_id', 777)->firstOrFail();
        $this->assertSame('car_'.$car->slug, $sub->start_param);
        $welcome = $this->telegramCalls('sendMessage')->last();
        $this->assertSame('Вам интересен Geely Monjaro 2024?', $welcome['text']);
        $this->assertTrue($welcome['reply_markup']['keyboard'][0][0]['request_contact']);

        $this->webhook($this->message(['contact' => ['phone_number' => '+79990001122', 'user_id' => 777]]));
        $lead = Lead::query()->firstOrFail();
        $this->assertSame('bot', $lead->source);
        $this->assertSame('+79990001122', $lead->contact);
        $this->assertSame($car->id, $lead->showcase_car_id);
        $this->assertSame(777, (int) $lead->tg_user_chat_id);
        $this->assertSame('+79990001122', $sub->fresh()->phone);
        $this->assertCount(2, $lead->tg_messages, 'менеджерам ушло');
    }

    public function test_bot_free_text_merges_into_recent_lead(): void
    {
        $this->webhook($this->message(['text' => '/start insta']));
        $this->webhook($this->message(['text' => 'Хочу Камри до 3 млн']));
        $this->webhook($this->message(['text' => 'Можно гибрид']));

        $this->assertSame(1, Lead::query()->count());
        $lead = Lead::query()->first();
        $this->assertSame("Хочу Камри до 3 млн\nМожно гибрид", $lead->comment);
        $this->assertSame('@buyer', $lead->contact);
        $this->assertSame(['utm_source' => 'insta'], $lead->utm);
    }

    public function test_manager_buttons_change_status_only_from_manager_chat(): void
    {
        $this->lead()->assertCreated();
        $lead = Lead::query()->firstOrFail();

        $this->webhook(['callback_query' => ['id' => 'cb1', 'data' => "lead:{$lead->id}:in_work", 'from' => ['id' => 9, 'username' => 'ruslan'], 'message' => ['message_id' => 101, 'chat' => ['id' => 999]]]]);
        $this->assertSame('new', $lead->fresh()->status, 'чужой чат не может менять статус');

        $this->webhook(['callback_query' => ['id' => 'cb2', 'data' => "lead:{$lead->id}:in_work", 'from' => ['id' => 9, 'username' => 'ruslan'], 'message' => ['message_id' => 101, 'chat' => ['id' => -1001]]]]);
        $lead->refresh();
        $this->assertSame('in_work', $lead->status);
        $this->assertSame('@ruslan', $lead->manager);
        $edits = $this->telegramCalls('editMessageText');
        $this->assertCount(2, $edits, 'сообщение обновилось у обоих менеджеров');
        $this->assertStringContainsString('В работе', $edits[0]['text']);
        $this->assertStringContainsString('@ruslan', $edits[0]['text']);
    }

    public function test_id_command_and_block_tracking(): void
    {
        $this->webhook(['message' => ['message_id' => 1, 'text' => '/id', 'chat' => ['id' => -100555, 'type' => 'supergroup'], 'from' => ['id' => 1]]]);
        $this->assertStringContainsString('-100555', $this->telegramCalls('sendMessage')->last()['text']);

        $this->webhook($this->message(['text' => '/start']));
        $this->webhook(['my_chat_member' => ['chat' => ['id' => 777, 'type' => 'private'], 'new_chat_member' => ['status' => 'kicked']]]);
        $this->assertTrue(TgSubscriber::query()->where('chat_id', 777)->first()->is_blocked);
    }

    public function test_settings_store_token_encrypted_and_set_webhook(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/che168/settings', ['tg_bot_token' => '987654:ZYXWVUTSRQPONMLKJIHGFEDCBA_zz', 'tg_manager_chat_ids' => '-1002'])->assertRedirect();

        $raw = DB::table('settings')->where('key', 'tg_bot_token')->value('value');
        $this->assertStringNotContainsString('987654', $raw, 'токен в базе зашифрован');
        $this->assertSame('987654:ZYXWVUTSRQPONMLKJIHGFEDCBA_zz', Settings::get('tg_bot_token'));

        // пустое поле не стирает токен
        $this->actingAs($user)->put('/che168/settings', ['tg_bot_token' => '', 'tg_manager_chat_ids' => '-1003'])->assertRedirect();
        $this->assertSame('987654:ZYXWVUTSRQPONMLKJIHGFEDCBA_zz', Settings::get('tg_bot_token'));
        $this->assertSame([-1003], Settings::managerChatIds());

        $this->actingAs($user)->put('/che168/settings', ['tg_bot_token' => 'not-a-token'])->assertSessionHasErrors('tg_bot_token');

        $this->actingAs($user)->post('/che168/settings/webhook')->assertRedirect();
        $call = $this->telegramCalls('setWebhook')->last();
        $this->assertStringEndsWith('/api/telegram/webhook', $call['url']);
        $this->assertSame(Settings::get('tg_webhook_secret'), $call['secret_token']);
        $this->assertSame(48, strlen($call['secret_token']));

        $this->actingAs($user)->get('/che168/settings')->assertOk()->assertDontSee('987654:ZYX');
    }

    public function test_admin_lead_pages_and_status_update(): void
    {
        $user = User::factory()->create(['name' => 'Руслан']);
        $this->lead()->assertCreated();
        $lead = Lead::query()->firstOrFail();

        $this->actingAs($user)->get('/che168/leads')->assertOk()->assertSee('+7 999 123-45-67');
        $this->actingAs($user)->get('/che168/leads/'.$lead->id)->assertOk()->assertSee('до 4 млн');
        $this->actingAs($user)->put('/che168/leads/'.$lead->id, ['status' => 'done', 'note' => 'Купил Monjaro'])->assertRedirect();
        $this->assertSame('done', $lead->fresh()->status);
        $this->assertSame('Руслан', $lead->fresh()->manager);
    }

    public function test_broadcast_sends_to_subscribers_and_marks_blocked(): void
    {
        TgSubscriber::query()->create(['chat_id' => 1]);
        TgSubscriber::query()->create(['chat_id' => 2]);
        TgSubscriber::query()->create(['chat_id' => 3, 'is_blocked' => true]);
        $this->tg = fn (Request $r) => $r['chat_id'] === 2
            ? Http::response(['ok' => false, 'error_code' => 403, 'description' => 'Forbidden: bot was blocked by the user'], 403)
            : Http::response(['ok' => true, 'result' => ['message_id' => 1]]);

        $user = User::factory()->create();
        $this->actingAs($user)->post('/che168/bot/broadcasts', ['text' => 'Новые поступления', 'button_text' => 'Смотреть', 'button_url' => 'https://vanauto.neeklo.ru/catalog/', 'mode' => 'send'])->assertRedirect();
        $this->artisan('tg:broadcasts', ['--seconds' => 5])->assertSuccessful();

        $b = TgBroadcast::query()->firstOrFail();
        $this->assertSame('done', $b->status);
        $this->assertSame(1, $b->sent_count);
        $this->assertSame(1, $b->failed_count);
        $this->assertTrue(TgSubscriber::query()->where('chat_id', 2)->first()->is_blocked);
        $this->assertSame('Смотреть', $this->telegramCalls('sendMessage')->first()['reply_markup']['inline_keyboard'][0][0]['text']);
        $this->actingAs($user)->get('/che168/bot')->assertOk()->assertSee('Новые поступления');
    }

    public function test_broadcast_test_mode_goes_to_managers_only(): void
    {
        TgSubscriber::query()->create(['chat_id' => 42]);
        $user = User::factory()->create();
        $this->actingAs($user)->post('/che168/bot/broadcasts', ['text' => 'Тест', 'mode' => 'test'])->assertRedirect();

        $this->assertSame(0, TgBroadcast::query()->count());
        $this->assertSame([-1001, 555], $this->telegramCalls('sendMessage')->map(fn ($r) => $r['chat_id'])->all());
    }
}
