<?php

namespace Tests\Feature;

use App\Models\ShowcaseCar;
use App\Models\SiteBuild;
use App\Models\User;
use App\Services\Site\SiteRebuilder;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class SiteRebuildTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::setMany(['site_rebuild_command' => '/opt/van2kauto/deploy/rebuild.sh', 'site_rebuild_debounce_minutes' => '5']);
    }

    private function change(): void
    {
        ShowcaseCar::query()->create(['brand' => 'Kia', 'model' => 'K5', 'year' => 2023]);
    }

    public function test_on_change_waits_for_debounce_then_builds_once(): void
    {
        Process::fake(['*' => Process::result('built 12 pages')]);
        $this->travelTo(now()->startOfMinute());
        $this->change();

        $this->artisan('site:rebuild --if-due')->assertSuccessful();
        $this->assertSame(0, SiteBuild::query()->count(), 'пауза ещё не прошла');

        $this->travel(6)->minutes();
        $this->artisan('site:rebuild --if-due')->assertSuccessful();
        $build = SiteBuild::query()->sole();
        $this->assertSame('auto', $build->trigger);
        $this->assertSame('success', $build->status);
        $this->assertStringContainsString('built 12 pages', $build->output);
        Process::assertRan(fn ($p) => $p->command === '/opt/van2kauto/deploy/rebuild.sh' && str_ends_with($p->environment['SHOWCASE_API_URL'], '/api/v1/showcase'));

        $this->travel(1)->minutes();
        $this->artisan('site:rebuild --if-due');
        $this->assertSame(1, SiteBuild::query()->count(), 'без новых изменений повторно не собираем');
        $this->assertFalse(app(SiteRebuilder::class)->hasPendingChanges());
    }

    public function test_manual_mode_builds_only_by_button(): void
    {
        Process::fake();
        Settings::set('site_rebuild_mode', 'manual');
        $this->change();
        $this->travel(30)->minutes();
        $this->artisan('site:rebuild --if-due');
        $this->assertSame(0, SiteBuild::query()->count());

        $this->actingAs(User::factory()->create())->post('/che168/settings/rebuild')->assertRedirect();
        $this->artisan('site:rebuild --if-due');
        $this->assertSame('manual', SiteBuild::query()->sole()->trigger);
        $this->assertSame('', (string) Settings::get('site_rebuild_requested_at'));
    }

    public function test_daily_mode_and_failed_build_is_recorded(): void
    {
        Process::fake(['*' => Process::result(output: '', errorOutput: 'next build failed', exitCode: 1)]);
        Settings::setMany(['site_rebuild_mode' => 'daily', 'site_rebuild_daily_hour' => '4']);
        $this->travelTo(now()->setTime(2, 0));
        $this->change();
        $this->artisan('site:rebuild --if-due');
        $this->assertSame(0, SiteBuild::query()->count());

        $this->travelTo(now()->setTime(4, 1));
        $this->artisan('site:rebuild --if-due')->assertFailed();
        $build = SiteBuild::query()->sole();
        $this->assertSame('failed', $build->status);
        $this->assertStringContainsString('next build failed', $build->output);
        $this->assertTrue(app(SiteRebuilder::class)->hasPendingChanges(), 'после неудачной сборки изменения всё ещё ждут');
    }

    public function test_missing_command_fails_cleanly(): void
    {
        Settings::set('site_rebuild_command', '');
        $build = app(SiteRebuilder::class)->run('manual');
        $this->assertSame('failed', $build->status);
        $this->assertStringContainsString('не задана', $build->output);
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs(User::factory()->create())->get('/che168/settings')->assertOk()->assertSee('Правило пересборки');
    }
}
