<?php

namespace Tests\Feature;

use App\Models\AuctionLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomarketCabinetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_the_cabinet_login(): void
    {
        $this->get('/a-automarket')->assertRedirect(route('che168.login'));
    }

    public function test_dashboard_lots_and_manual_edit_are_available_after_login(): void
    {
        $user = User::factory()->create();
        $lot = AuctionLot::query()->create([
            'lot_id' => 982030098,
            'bid' => '70018',
            'auction_name' => 'MIRIVE Aichi',
            'maker_name' => 'Toyota',
            'model_name' => 'Vanguard',
            'grade' => '240S',
            'year' => 2011,
            'start_price_jpy' => 120000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->get('/a-automarket')->assertOk()->assertSee('Запуск парсера');
        $this->actingAs($user)->get('/a-automarket/lots?q=Vanguard&year_min=2010')->assertOk()->assertSee('70018');
        $this->actingAs($user)->get('/a-automarket/lots/'.$lot->lot_id)->assertOk()->assertSee('Toyota');

        $this->actingAs($user)->put('/a-automarket/lots/'.$lot->lot_id, [
            'status' => 'inactive',
            'grade' => '240S S Package',
            'start_price_jpy' => 130000,
        ])->assertRedirect();

        $this->assertSame('inactive', $lot->fresh()->status);
        $this->assertSame(130000, $lot->fresh()->start_price_jpy);
    }
}
