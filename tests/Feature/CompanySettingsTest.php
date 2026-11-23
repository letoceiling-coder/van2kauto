<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_details_saved_in_admin_reach_showcase_api_and_mark_site_dirty(): void
    {
        $user = User::factory()->create();
        $v0 = Settings::int('showcase_version', 1);

        $this->actingAs($user)->put('/che168/settings', [
            'company_legal' => 'ИП Петров Пётр Петрович',
            'company_inn' => '231234567890',
            'company_phone' => '+7 918 000-00-00',
            'company_telegram' => '@van23auto',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertGreaterThan($v0, Settings::int('showcase_version', 1));
        $this->assertNotEmpty(Settings::get('site_dirty_at'));

        $meta = $this->getJson('/api/v1/showcase')->assertOk()->json('meta.company');
        $this->assertSame('ИП Петров Пётр Петрович', $meta['legal']);
        $this->assertSame('231234567890', $meta['inn']);
        $this->assertSame('van23auto', $meta['telegram']);
        $this->assertSame('', $meta['email']);
    }

    public function test_invalid_requisites_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->from('/che168/settings')->put('/che168/settings', ['company_inn' => '12345', 'company_telegram' => 'https://t.me/x y'])
            ->assertSessionHasErrors(['company_inn', 'company_telegram']);
        $this->assertSame('', (string) Settings::get('company_inn', ''));
    }

    public function test_unchanged_settings_do_not_trigger_rebuild(): void
    {
        $user = User::factory()->create();
        $v0 = Settings::int('showcase_version', 1);
        $this->actingAs($user)->put('/che168/settings', ['parser_daily_hour' => 6])->assertRedirect();
        $this->assertSame($v0, Settings::int('showcase_version', 1));
    }
}
