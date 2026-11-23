<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\Series;
use App\Models\ShowcaseCar;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function car(array $attrs = []): ShowcaseCar
    {
        return ShowcaseCar::query()->create($attrs + [
            'brand' => 'Mazda', 'model' => 'CX-5', 'year' => now()->year - 3, 'trim' => '2.0L 2WD',
            'country' => 'china', 'condition' => 'used', 'body' => 'crossover', 'engine' => 'petrol',
            'power_hp' => 155, 'displacement_l' => 2.0, 'drive' => 'FWD', 'steering' => 'left',
            'mileage_km' => 30000, 'price_from_rub' => 2490000, 'status' => 'order', 'is_published' => true,
        ]);
    }

    private function feedListing(int $infoid, array $attrs = []): Listing
    {
        Brand::query()->firstOrCreate(['brand_id' => 33], ['name' => 'Mazda']);
        Series::query()->firstOrCreate(['series_id' => 500], ['brand_id' => 33, 'name' => 'CX-5']);

        return Listing::query()->create($attrs + [
            'infoid' => $infoid, 'brand_id' => 33, 'series_id' => 500, 'title' => 'Mazda CX-5 2023 2.0L',
            'price_usd' => 15000, 'mileage_km' => 30000, 'reg_date' => (now()->year - 3).'.05', 'status' => 'active',
            'engine' => '2.0L 155HP L4', 'fuel_name' => 'Gasoline', 'driving_mode' => 'Front-Engine, Front-Wheel Drive',
            'body_type' => 'SUV', 'gearbox' => 'Automatic', 'steering' => 'Left',
            'detail_url' => 'https://global.che168.com/detail/'.$infoid,
        ]);
    }

    public function test_public_api_shows_only_published_cards_without_donor_data(): void
    {
        $listing = $this->feedListing(9001);
        $this->car(['source_infoid' => $listing->infoid, 'sort_order' => 2, 'is_hit' => true]);
        $this->car(['model' => 'CX-30', 'sort_order' => 1]);
        $this->car(['model' => 'Hidden', 'is_published' => false]);

        $response = $this->getJson('/api/v1/showcase')->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.model', 'CX-30')
            ->assertJsonPath('data.1.hit', true)
            ->assertJsonPath('data.1.price_from_rub', 2490000)
            ->assertJsonPath('data.1.utilization_ok', true)
            ->assertJsonPath('data.1.engine_label', 'Бензин, 2.0 л, 155 л.с.');

        $body = $response->getContent();
        $this->assertStringNotContainsString('9001', $body);
        $this->assertStringNotContainsString('che168', $body);
        $this->assertStringNotContainsString('price_usd', $body);

        $this->getJson('/api/v1/showcase/hidden-'.now()->year)->assertNotFound();
    }

    public function test_api_uses_etag_and_changes_version_on_edit(): void
    {
        $car = $this->car();
        $first = $this->getJson('/api/v1/showcase')->assertOk()->assertHeader('ETag');
        $this->assertStringContainsString('stale-while-revalidate', $first->headers->get('Cache-Control'));
        $etag = $first->headers->get('ETag');

        $this->getJson('/api/v1/showcase', ['If-None-Match' => $etag])->assertStatus(304);

        $car->update(['price_from_rub' => 2390000]);
        $second = $this->getJson('/api/v1/showcase', ['If-None-Match' => $etag])->assertOk()->assertJsonPath('data.0.price_from_rub', 2390000);
        $this->assertNotSame($etag, $second->headers->get('ETag'));
        $this->assertNotEmpty(Settings::get('site_dirty_at'));
    }

    public function test_utilization_rule_follows_settings_and_override(): void
    {
        $car = $this->car(['power_hp' => 190]);
        $this->assertFalse($car->utilizationOk());
        Settings::set('utilization_max_hp', '200');
        $this->assertTrue($car->fresh()->utilizationOk());
        $car->update(['utilization_override' => false]);
        $this->assertFalse($car->fresh()->utilizationOk());
        $this->assertFalse($this->car(['power_hp' => 120, 'year' => now()->year - 8])->utilizationOk());
        $this->assertTrue($this->car(['power_hp' => 120, 'year' => now()->year, 'condition' => 'new'])->utilizationOk());
    }

    public function test_admin_creates_edits_and_requires_price_to_publish(): void
    {
        $user = User::factory()->create();
        $form = [
            'brand' => 'BYD', 'model' => 'Song Plus', 'year' => now()->year - 1, 'country' => 'china', 'condition' => 'used',
            'body' => 'crossover', 'engine' => 'phev', 'drive' => 'FWD', 'steering' => 'left', 'status' => 'order',
            'is_published' => '1', 'highlights_text' => "Расход 5 л\n\nПанорама",
        ];

        $this->actingAs($user)->post('/che168/showcase', $form)->assertRedirect();
        $car = ShowcaseCar::query()->firstOrFail();
        $this->assertFalse($car->is_published, 'без цены не публикуем');
        $this->assertSame(['Расход 5 л', 'Панорама'], $car->highlights);
        $this->assertSame('byd-song-plus-'.(now()->year - 1), $car->slug);

        $this->actingAs($user)->put('/che168/showcase/'.$car->id, $form + ['price_from_rub' => 2990000, 'utilization' => 'yes'])->assertRedirect();
        $car->refresh();
        $this->assertTrue($car->is_published);
        $this->assertTrue($car->utilization_override);

        $this->actingAs($user)->get('/che168/showcase')->assertOk()->assertSee('Song Plus');
        $this->actingAs($user)->get('/che168/showcase/'.$car->id.'/edit')->assertOk()->assertSee('Совпадения в фиде');
    }

    public function test_duplicate_makes_unpublished_copy_with_photos(): void
    {
        $user = User::factory()->create();
        $car = $this->car(['is_hit' => true]);
        $this->actingAs($user)->post('/che168/showcase/'.$car->id.'/photos', ['photos' => [UploadedFile::fake()->image('a.jpg', 800, 600)]])->assertRedirect();
        $this->assertSame(1, $car->photos()->count());

        $this->actingAs($user)->post('/che168/showcase/'.$car->id.'/duplicate')->assertRedirect();
        $copy = ShowcaseCar::query()->latest('id')->first();
        $this->assertNotSame($car->id, $copy->id);
        $this->assertFalse($copy->is_published);
        $this->assertFalse($copy->is_hit);
        $this->assertSame($car->photos()->first()->path, $copy->photos()->first()->path);

        // удаление копии не удаляет общий файл оригинала
        $copy->photos->each->delete();
        Storage::disk('public')->assertExists($car->photos()->first()->path);
    }

    public function test_bulk_order_publish_and_photo_cover(): void
    {
        $user = User::factory()->create();
        $a = $this->car(['is_published' => false]);
        $b = $this->car(['model' => 'CX-9', 'is_published' => false, 'price_from_rub' => null]);

        $this->actingAs($user)->post('/che168/showcase/bulk', ['ids' => [$a->id, $b->id], 'action' => 'publish', 'sort' => [$a->id => 5, $b->id => 1]]);
        $this->assertTrue($a->fresh()->is_published);
        $this->assertFalse($b->fresh()->is_published, 'без цены в массовой публикации пропускаем');
        $this->assertSame(1, $b->fresh()->sort_order);

        $this->actingAs($user)->post('/che168/showcase/'.$a->id.'/photos', ['photos' => [UploadedFile::fake()->image('1.jpg'), UploadedFile::fake()->image('2.jpg')]]);
        $second = $a->photos()->orderBy('sort_order')->get()->last();
        $this->actingAs($user)->put('/che168/showcase/'.$a->id.'/photos', ['cover' => $second->id]);
        $this->assertSame($second->id, $a->fresh()->photos->first()->id);
    }

    public function test_import_from_listing_maps_fields_and_downloads_photos(): void
    {
        Http::fake(['cdn.test/*' => Http::response(str_repeat('x', 2000), 200, ['Content-Type' => 'image/jpeg'])]);
        $user = User::factory()->create();
        $listing = $this->feedListing(9100, ['fuel_name' => 'Plug-in Hybrid', 'driving_mode' => 'Front-Engine, Four-Wheel Drive']);
        foreach ([0, 1, 2] as $i) {
            ListingPhoto::query()->create(['infoid' => 9100, 'sort_order' => $i, 'large_url' => "https://cdn.test/{$i}.jpg"]);
        }

        $this->actingAs($user)->post('/che168/showcase/from-listing/9100')->assertRedirect();
        $car = ShowcaseCar::query()->firstOrFail();

        $this->assertSame(9100, (int) $car->source_infoid);
        $this->assertSame('Mazda', $car->brand);
        $this->assertSame('CX-5', $car->model);
        $this->assertSame('phev', $car->engine);
        $this->assertSame('AWD', $car->drive);
        $this->assertSame('Автомат', $car->gearbox);
        $this->assertSame(155, $car->power_hp);
        $this->assertFalse($car->is_published);
        $this->assertSame(3, $car->photos()->count());
        Storage::disk('public')->assertExists($car->photos()->first()->path);

        // повторный импорт тех же фото не дублирует
        app(\App\Services\Showcase\ListingImporter::class)->importPhotos($car, $listing);
        $this->assertSame(3, $car->photos()->count());

        // список объявлений помечает уже добавленные
        $this->actingAs($user)->get('/che168/listings')->assertOk()->assertSee('в витрине');
    }

    public function test_bulk_import_and_feed_matches(): void
    {
        $user = User::factory()->create();
        $this->feedListing(9201, ['price_usd' => 14000]);
        $this->feedListing(9202, ['price_usd' => 16000]);
        $this->feedListing(9203, ['price_usd' => 30000, 'engine' => '2.5T 231HP L4']);
        $this->feedListing(9204, ['price_usd' => 9000, 'reg_date' => '2015.01']);

        $this->actingAs($user)->post('/che168/showcase/from-listings', ['infoids' => [9201, 9202], 'photos' => 0])->assertRedirect();
        $this->assertSame(2, ShowcaseCar::query()->count());

        // ручная карточка без серии находит совпадения по марке и модели
        $manual = $this->car(['brand' => 'mazda', 'model' => 'cx-5']);
        $match = app(\App\Services\Showcase\FeedMatcher::class)->match($manual);
        $this->assertSame(2, $match['total'], 'год ±1 и мощность ±10% отсекают 9203 и 9204');
        $this->assertSame(14000.0, $match['price_min']);

        // подставить характеристики из совпадения
        $this->actingAs($user)->post('/che168/showcase/'.$manual->id.'/fill', ['infoid' => 9201])->assertRedirect();
        $manual->refresh();
        $this->assertSame(500, (int) $manual->series_id);
        $this->assertSame(2490000, (int) $manual->price_from_rub, 'цену не трогаем');
    }

    public function test_listings_power_filter_in_api_and_admin(): void
    {
        $user = User::factory()->create();
        $this->feedListing(9301, ['engine' => '1.5T 150HP L4']);
        $this->feedListing(9302, ['engine' => '2.0T 245 horsepower L4']);

        $this->assertSame(150, Listing::query()->find(9301)->power_hp, 'мощность разбирается при сохранении');

        $this->getJson('/api/v1/listings?power_max=160')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.power_hp', 150);
        $this->getJson('/api/v1/listings?power_min=200')->assertOk()->assertJsonPath('data.0.infoid', 9302);
        $this->getJson('/api/v1/facets')->assertOk()->assertJsonPath('data.power.max', 245);

        $this->actingAs($user)->get('/che168/listings?power_max=160')->assertOk()->assertSee('9301')->assertDontSee('9302');
    }

    public function test_backfill_power_command(): void
    {
        $this->feedListing(9401, ['engine' => '1.5T 181HP L4']);
        Listing::query()->whereKey(9401)->update(['power_hp' => null]);

        $this->artisan('che168:backfill-power')->assertSuccessful();
        $this->assertSame(181, Listing::query()->find(9401)->power_hp);
    }
}
