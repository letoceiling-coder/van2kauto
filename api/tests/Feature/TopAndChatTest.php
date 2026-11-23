<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\ScrapeRun;
use App\Models\Series;
use App\Models\ShowcaseCar;
use App\Models\TgSubscriber;
use App\Models\User;
use App\Services\Che168\ParseRunnerService;
use App\Services\Showcase\PriceEstimator;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TopAndChatTest extends TestCase
{
    use RefreshDatabase;

    private int $mid = 500;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Settings::setMany(['tg_bot_token' => '123456:ABCDEFGHIJKLMNOPQRSTUVWXYZ_abc', 'tg_manager_chat_ids' => '-1001', 'tg_webhook_secret' => 'sekret', 'price_usd_rate' => '100', 'price_multiplier' => '1.3', 'price_fixed_rub' => '200000', 'price_round_rub' => '10000']);
        Http::fake([
            'api.telegram.org/*' => fn () => Http::response(['ok' => true, 'result' => ['message_id' => ++$this->mid]]),
            'cdn.test/*' => Http::response(str_repeat('x', 2000), 200, ['Content-Type' => 'image/jpeg']),
            'www.cbr-xml-daily.ru/*' => Http::response(['Valute' => ['USD' => ['Value' => 91.2345]]]),
        ]);
    }

    private function sent(string $method)
    {
        return collect(Http::recorded())->map(fn ($p) => $p[0])->filter(fn (Request $r) => str_ends_with($r->url(), '/'.$method))->values();
    }

    public function test_price_formula_and_rate_update(): void
    {
        // (10000 + 400 + 500) × 100 × 1.3 + 200 000 = 1 617 000 → вниз до 10 000
        $this->assertSame(1610000, PriceEstimator::fromUsd(10000, 400, 500));
        $this->assertNull(PriceEstimator::fromUsd(null));

        $this->artisan('rates:update')->assertSuccessful();
        $this->assertSame('91.23', Settings::get('price_usd_rate'));
        Settings::set('price_rate_auto', '0');
        Settings::set('price_usd_rate', '100');
        $this->artisan('rates:update')->assertSuccessful();
        $this->assertSame('100', Settings::get('price_usd_rate'), 'ручной курс не трогаем');
    }

    private function feed(): void
    {
        $y = now()->year;
        Brand::query()->create(['brand_id' => 1, 'name' => 'Audi']);
        Brand::query()->create(['brand_id' => 2, 'name' => 'Li Auto']);
        Series::query()->create(['series_id' => 10, 'brand_id' => 1, 'name' => 'Audi A3']);
        Series::query()->create(['series_id' => 11, 'brand_id' => 1, 'name' => 'Q2L']);
        Series::query()->create(['series_id' => 12, 'brand_id' => 2, 'name' => 'Li L9']);
        $n = 1;
        $add = function (int $series, array $a) use (&$n, $y) {
            $id = 7000 + $n++;
            Listing::query()->create($a + ['infoid' => $id, 'brand_id' => $series === 12 ? 2 : 1, 'series_id' => $series, 'title' => 't', 'status' => 'active', 'reg_date' => ($y - 4).'.03', 'mileage_km' => 50000, 'engine' => '1.4T 150HP L4', 'fuel_name' => 'Gasoline', 'price_usd' => 12000]);
            foreach (range(0, ($a['photos'] ?? 8) - 1) as $i) {
                ListingPhoto::query()->create(['infoid' => $id, 'sort_order' => $i, 'large_url' => "https://cdn.test/{$id}-{$i}.jpg"]);
            }

            return $id;
        };
        // A3: 3 предложения, самое дешёвое с плохими фото → берём следующее
        $add(10, ['price_usd' => 9000, 'photos' => 2]);
        $cheapA3 = $add(10, ['price_usd' => 10000]);
        $add(10, ['price_usd' => 15000]);
        // Q2L: 2 предложения, одно слишком мощное, одно слишком старое
        $add(11, ['price_usd' => 11000]);
        $add(11, ['price_usd' => 8000, 'engine' => '2.0T 220HP L4']);
        $add(11, ['price_usd' => 7000, 'reg_date' => ($y - 9).'.01']);
        // Li L9: много, но последовательный гибрид — не проходной
        foreach (range(1, 5) as $_) {
            $add(12, ['fuel_name' => 'Range Extender', 'engine' => 'Range Extender 154 HP']);
        }
        $this->assertSame(10000.0, (float) Listing::query()->find($cheapA3)->price_usd);
    }

    public function test_top_picker_builds_published_cards_from_real_feed(): void
    {
        $this->feed();
        $this->artisan('showcase:top', ['--limit' => 5, '--publish' => true, '--photos' => 3])->assertSuccessful();

        $cars = ShowcaseCar::query()->orderBy('sort_order')->get();
        $this->assertSame(['A3', 'Q2L'], $cars->pluck('model')->all(), 'Li L9 (EREV) исключён, марка из названия убрана');
        $a3 = $cars->first();
        $this->assertSame('Топ-1', $a3->badge);
        $this->assertTrue($a3->is_published);
        $this->assertTrue($a3->is_hit);
        $this->assertSame(3, $a3->photos()->count());
        $this->assertSame(PriceEstimator::fromUsd(10000), (int) $a3->price_from_rub, 'самое доступное объявление с нормальными фото');
        $this->assertStringContainsString('3 предложения', $a3->summary);

        // повторный запуск не дублирует модели
        $this->artisan('showcase:top', ['--limit' => 5])->assertSuccessful();
        $this->assertSame(2, ShowcaseCar::query()->count());

        $this->getJson('/api/v1/showcase')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.badge', 'Топ-1');
    }

    public function test_reprice_uses_source_listing(): void
    {
        $this->feed();
        $this->artisan('showcase:top', ['--limit' => 1, '--photos' => 0]);
        $car = ShowcaseCar::query()->first();
        $car->update(['price_from_rub' => 1]);
        Settings::set('price_multiplier', '1.5');
        $this->actingAs(User::factory()->create())->post('/che168/showcase/'.$car->id.'/reprice')->assertRedirect();
        $this->assertSame(PriceEstimator::fromUsd(10000), (int) $car->fresh()->price_from_rub);
    }

    public function test_site_chat_roundtrip_with_manager_reply_in_telegram(): void
    {
        $start = $this->postJson('/api/v1/chat', ['message' => 'Сколько будет стоить Audi A3 2022?', 'page' => '/'], ['Origin' => 'https://vanauto.neeklo.ru'])
            ->assertCreated()->assertJsonPath('messages.0.from', 'client');
        $token = $start->json('token');
        $this->assertSame(40, strlen($token));

        $lead = Lead::query()->sole();
        $this->assertSame('chat', $lead->source);
        $root = $lead->tg_messages['-1001'];
        $this->assertStringContainsString('реплаем', $this->sent('sendMessage')->first()['text']);

        // клиент дописал и оставил контакт → ушло реплаем на заявку
        $this->postJson("/api/v1/chat/{$token}", ['message' => 'Можно в кредит?'])->assertOk();
        $this->postJson("/api/v1/chat/{$token}", ['contact' => '123'])->assertUnprocessable();
        $this->postJson("/api/v1/chat/{$token}", ['contact' => '+7 900 111-22-33', 'name' => 'Олег'])->assertOk();
        $fwd = $this->sent('sendMessage')->filter(fn ($r) => str_contains($r['text'], 'кредит'))->first();
        $this->assertSame($root, $fwd['reply_to_message_id']);
        $this->assertSame('+7 900 111-22-33', $lead->fresh()->contact);

        // менеджер отвечает реплаем в группе
        $this->postJson('/api/telegram/webhook', ['update_id' => 1, 'message' => ['message_id' => 9001, 'text' => 'Да, от 2,1 млн. Пришлю варианты', 'chat' => ['id' => -1001, 'type' => 'supergroup'], 'from' => ['id' => 5, 'first_name' => 'Руслан'], 'reply_to_message' => ['message_id' => $root]]], ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk();

        $poll = $this->getJson("/api/v1/chat/{$token}?after=0")->assertOk();
        $last = collect($poll->json('messages'))->last();
        $this->assertSame('manager', $last['from']);
        $this->assertSame('Руслан', $last['author']);
        $this->assertSame('in_work', $lead->fresh()->status);
        $this->assertTrue($poll->json('has_contact'));
        $this->assertCount(0, $this->getJson("/api/v1/chat/{$token}?after={$last['id']}")->json('messages'));

        // реплай на обычное сообщение группы (не заявку) игнорируется
        $this->postJson('/api/telegram/webhook', ['update_id' => 2, 'message' => ['message_id' => 9002, 'text' => 'офтоп', 'chat' => ['id' => -1001, 'type' => 'supergroup'], 'from' => ['id' => 5], 'reply_to_message' => ['message_id' => 1]]], ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk();
        $this->assertSame(1, $lead->messages()->where('sender', 'manager')->count());

        $this->getJson('/api/v1/chat/'.str_repeat('x', 40))->assertNotFound();
        $this->postJson('/api/v1/chat', ['message' => 'x'], ['Origin' => 'https://evil.example'])->assertForbidden();
    }

    public function test_admin_reply_reaches_bot_user_and_site_chat(): void
    {
        TgSubscriber::query()->create(['chat_id' => 777]);
        $lead = Lead::query()->create(['source' => 'bot', 'contact' => '@x', 'tg_user_chat_id' => 777, 'status' => 'new']);
        $this->actingAs(User::factory()->create(['name' => 'Руслан']))->post("/che168/leads/{$lead->id}/reply", ['text' => 'Добрый день!'])->assertRedirect();

        $this->assertSame('Добрый день!', $lead->messages()->sole()->text);
        $this->assertSame(777, $this->sent('sendMessage')->last()['chat_id']);
        $this->actingAs(User::first())->get("/che168/leads/{$lead->id}")->assertOk()->assertSee('Переписка');
    }

    public function test_parser_autorun_alerts_managers_on_captcha(): void
    {
        $this->mock(ParseRunnerService::class, function ($m) {
            $m->shouldReceive('run')->once()->andReturnUsing(fn (ScrapeRun $run) => $run->update(['status' => 'needs_captcha']));
        });
        $this->artisan('che168:auto', ['mode' => 'new'])->assertFailed();
        $this->assertSame('needs_captcha', ScrapeRun::query()->sole()->status);
        $this->assertStringContainsString('капча', $this->sent('sendMessage')->last()['text']);
    }

    public function test_settings_page_shows_price_and_parser_blocks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/che168/settings', ['price_multiplier' => '1.4', 'parser_auto' => '0'])->assertRedirect();
        $this->assertSame('1.4', Settings::get('price_multiplier'));
        $this->actingAs($user)->get('/che168/settings')->assertOk()->assertSee('Цена «от» по формуле')->assertSee('Парсер che168');
        $this->actingAs($user)->get('/che168/showcase')->assertOk()->assertSee('Топ-10 из фида');
    }
}
