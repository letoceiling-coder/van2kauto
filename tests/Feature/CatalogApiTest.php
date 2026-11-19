<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\ListingReport;
use App\Models\Series;
use App\Models\Spec;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_listings_support_filters_offset_and_limit(): void
    {
        $mazda = Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        $bmw = Brand::query()->create(['brand_id' => 15, 'name' => 'BMW']);
        $mazda5 = Series::query()->create(['series_id' => 10, 'brand_id' => 33, 'name' => 'Mazda5']);

        $this->listing(1, $mazda->brand_id, $mazda5->series_id, 'Mazda Mazda5 2011', 3000, 'active', 'Gasoline');
        $this->listing(2, $mazda->brand_id, $mazda5->series_id, 'Mazda Mazda5 2018', 9000, 'active', 'Gasoline');
        $this->listing(3, $bmw->brand_id, null, 'BMW X3', 19000, 'active', 'Diesel');
        $this->listing(4, $mazda->brand_id, $mazda5->series_id, 'Sold Mazda', 1000, 'absent', 'Gasoline');

        $response = $this->getJson('/api/v1/listings?category_id=33&price_max=5000&fuel=Gasoline&limit=1&offset=0&sort=price&dir=asc');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.limit', 1)
            ->assertJsonPath('meta.offset', 0)
            ->assertJsonPath('meta.has_more', false)
            ->assertJsonPath('data.0.infoid', 1)
            ->assertJsonPath('data.0.category.name', 'Mazda')
            ->assertJsonPath('data.0.model.name', 'Mazda5');

        $this->getJson('/api/v1/listings?q=bmw%20diesel&status=any')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.infoid', 3);
    }

    public function test_listing_detail_returns_every_photo(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        $this->listing(7, 33, null, 'Mazda detail', 4000, 'active', 'Gasoline');
        ListingPhoto::query()->create([
            'infoid' => 7,
            'sort_order' => 1,
            'preview_url' => 'https://cdn.test/2.jpg',
            'large_url' => 'https://cdn.test/2-large.jpg',
            'original_url' => 'https://cdn.test/2-orig.jpg',
        ]);

        $this->getJson('/api/v1/listings/7')
            ->assertOk()
            ->assertJsonCount(2, 'data.photos')
            ->assertJsonPath('data.fuel', 'Gasoline');
    }

    public function test_suggest_returns_category_model_and_product(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        Series::query()->create(['series_id' => 10, 'brand_id' => 33, 'name' => 'Mazda5']);
        $this->listing(7, 33, 10, 'Mazda Mazda5 2011', 3190, 'active', 'Gasoline');

        $this->getJson('/api/v1/search/suggest?q=maz')
            ->assertOk()
            ->assertJsonPath('categories.0.name', 'Mazda')
            ->assertJsonPath('models.0.name', 'Mazda5')
            ->assertJsonPath('products.0.infoid', 7);
    }

    public function test_category_and_model_lists(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        Series::query()->create(['series_id' => 10, 'brand_id' => 33, 'name' => 'Mazda5']);

        $this->getJson('/api/v1/categories?q=maz')
            ->assertOk()
            ->assertJsonPath('data.0.id', 33);

        $this->getJson('/api/v1/models?category_id=33')
            ->assertOk()
            ->assertJsonPath('data.0.id', 10)
            ->assertJsonPath('data.0.category', 'Mazda');

        $this->getJson('/api/v1/models/10')
            ->assertOk()
            ->assertJsonPath('data.name', 'Mazda5');
    }

    public function test_facets_and_properties(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        $this->listing(1, 33, null, 'Mazda one', 3000, 'active', 'Gasoline');
        $this->listing(2, 33, null, 'Mazda two', 8000, 'active', 'Diesel');

        $this->getJson('/api/v1/facets?category_id=33')
            ->assertOk()
            ->assertJsonFragment(['value' => 'Gasoline'])
            ->assertJsonPath('data.price.min', 3000);

        $this->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonFragment(['key' => 'fuel']);
    }

    public function test_docs_page_is_public(): void
    {
        $this->get('/api/docs')
            ->assertOk()
            ->assertSee('Документация')
            ->assertSee('Скопировать документацию');
        $this->getJson('/api/v1')->assertOk()->assertJsonPath('version', '1');
        $this->getJson('/api/v1/openapi.json')->assertOk()->assertJsonPath('openapi', '3.0.3');
    }

    public function test_every_catalog_filter(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda']);
        Brand::query()->create(['brand_id' => 15, 'name' => 'BMW']);
        Series::query()->create(['series_id' => 10, 'brand_id' => 33, 'name' => 'Mazda5']);
        Series::query()->create(['series_id' => 20, 'brand_id' => 15, 'name' => 'X3']);
        Spec::query()->create(['spec_id' => 100, 'series_id' => 10, 'name' => 'Comfort']);
        Spec::query()->create(['spec_id' => 200, 'series_id' => 20, 'name' => 'Luxury']);

        $this->listing(1, 33, 10, 'Alpha Mazda', 3000, 'active', 'Gasoline', [
            'spec_id' => 100,
            'gearbox' => 'Manual',
            'body_type' => 'Sedan',
            'color' => 'White',
            'driving_mode' => 'FWD',
            'steering' => 'Left',
            'class_level' => 'Compact',
            'city_slug' => 'hefei',
            'seats' => 5,
            'doors' => 4,
            'reg_date' => '2011.06',
            'mileage_km' => 5000,
        ]);
        $this->listing(2, 15, 20, 'Beta BMW', 20000, 'active', 'Diesel', [
            'spec_id' => 200,
            'gearbox' => 'Automatic',
            'body_type' => 'SUV',
            'color' => 'Black',
            'driving_mode' => 'AWD',
            'steering' => 'Right',
            'class_level' => 'Luxury',
            'city_slug' => 'shanghai',
            'seats' => 7,
            'doors' => 5,
            'reg_date' => '2022.03',
            'mileage_km' => 90000,
        ]);
        $this->listing(3, 33, 10, 'Gone Mazda', 1000, 'absent', 'Gasoline');

        $only = function (string $query, int $infoid): void {
            $this->getJson('/api/v1/listings?'.$query)
                ->assertOk()
                ->assertJsonPath('meta.total', 1)
                ->assertJsonPath('data.0.infoid', $infoid);
        };

        $only('category_id=33', 1);
        $only('brand_id=15', 2);
        $only('model_id=10', 1);
        $only('series_id=20', 2);
        $only('spec_id=100', 1);
        $only('fuel=Diesel', 2);
        $only('gearbox=Manual', 1);
        $only('body_type=SUV', 2);
        $only('body=Sedan', 1);
        $only('color=White', 1);
        $only('driving_mode=AWD', 2);
        $only('drive=FWD', 1);
        $only('steering=Right', 2);
        $only('class_level=Compact', 1);
        $only('class=Luxury', 2);
        $only('city=hefei', 1);
        $only('seats=7', 2);
        $only('doors=4', 1);
        $only('year_min=2020', 2);
        $only('year_max=2015', 1);
        $only('price_min=10000', 2);
        $only('price_max=5000', 1);
        $only('mileage_min=50000', 2);
        $only('mileage_max=10000', 1);
        $only('category_id=33,15&status=active&fuel=Gasoline', 1);
        $only('q=beta%20suv', 2);
        $only('status=absent', 3);

        $this->getJson('/api/v1/listings?status=any')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);

        $this->getJson('/api/v1/listings?sort=price&dir=asc')
            ->assertOk()
            ->assertJsonPath('data.0.infoid', 1);

        $this->getJson('/api/v1/listings?sort=title&dir=desc')
            ->assertOk()
            ->assertJsonPath('data.0.infoid', 2);
    }

    public function test_limit_offset_and_page(): void
    {
        Brand::query()->create(['brand_id' => 1, 'name' => 'A']);
        foreach ([1000, 2000, 3000, 4000, 5000] as $i => $price) {
            $this->listing($i + 1, 1, null, 'Car '.$i, $price, 'active', 'Gasoline');
        }

        $this->getJson('/api/v1/listings?limit=2&page=2&sort=price&dir=asc')
            ->assertOk()
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.limit', 2)
            ->assertJsonPath('meta.offset', 2)
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.pages', 3)
            ->assertJsonPath('meta.has_more', true)
            ->assertJsonPath('meta.next_offset', 4)
            ->assertJsonPath('data.0.infoid', 3)
            ->assertJsonPath('data.1.infoid', 4);

        $this->getJson('/api/v1/listings?per_page=2&offset=2&page=1&sort=price&dir=asc')
            ->assertOk()
            ->assertJsonPath('meta.offset', 2)
            ->assertJsonPath('data.0.infoid', 3);

        $this->getJson('/api/v1/listings?limit=1000')
            ->assertOk()
            ->assertJsonPath('meta.limit', 100);

        $this->getJson('/api/v1/categories?limit=1&offset=0')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.has_more', false);
    }

    public function test_reference_lists_and_detail(): void
    {
        Brand::query()->create(['brand_id' => 33, 'name' => 'Mazda', 'py' => 'mazda']);
        Series::query()->create(['series_id' => 10, 'brand_id' => 33, 'name' => 'Mazda5']);
        Spec::query()->create([
            'spec_id' => 100,
            'series_id' => 10,
            'name' => 'Comfort',
            'year_label' => '2011',
            'raw_param' => [
                'paramtypeitems' => [
                    ['name' => 'Engine', 'paramitems' => [['name' => 'Displacement', 'value' => '2.0L']]],
                ],
            ],
            'raw_config' => [
                'configtypeitems' => [
                    ['name' => 'Safety', 'configitems' => [['name' => 'Airbags', 'valueitems' => ['●']]]],
                ],
            ],
        ]);
        $this->listing(7, 33, 10, 'Mazda detail', 4000, 'active', 'Gasoline', [
            'spec_id' => 100,
            'gearbox' => 'Manual',
            'vin_masked' => 'L****123',
            'dimension' => '4585*1800*1490',
            'raw_carinfo' => ['infoid' => 7, 'source' => 'test'],
        ]);
        ListingPhoto::query()->create([
            'infoid' => 7,
            'sort_order' => 1,
            'preview_url' => 'https://cdn.test/7b.jpg',
            'large_url' => 'https://cdn.test/7b-large.jpg',
            'original_url' => 'https://cdn.test/7b-orig.jpg',
        ]);
        ListingReport::query()->create([
            'infoid' => 7,
            'claims_count' => 1,
            'latest_mileage_km' => 42000,
            'insurance_url' => 'https://report.test/insurance',
        ]);

        $this->getJson('/api/v1/models?brand_id=33')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Mazda5');

        $this->getJson('/api/v1/specs?model_id=10')
            ->assertOk()
            ->assertJsonPath('data.0.id', 100)
            ->assertJsonPath('data.0.category', 'Mazda');

        $this->getJson('/api/v1/facets?model_id=10')
            ->assertOk()
            ->assertJsonPath('data.categories.0.name', 'Mazda')
            ->assertJsonPath('data.gearboxes.0.value', 'Manual');

        $this->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonFragment(['key' => 'gearbox', 'title' => 'КПП']);

        $this->getJson('/api/v1/search/suggest?q=')
            ->assertOk()
            ->assertJsonPath('categories', [])
            ->assertJsonPath('products', []);

        $this->getJson('/api/v1/listings/7?include_raw=1')
            ->assertOk()
            ->assertJsonCount(2, 'data.photos')
            ->assertJsonPath('data.photos.1.original', 'https://cdn.test/7b-orig.jpg')
            ->assertJsonPath('data.properties.vin', 'L****123')
            ->assertJsonPath('data.properties.dimension', '4585*1800*1490')
            ->assertJsonPath('data.report.claims_count', 1)
            ->assertJsonPath('data.report.latest_mileage_km', 42000)
            ->assertJsonPath('data.spec_groups.0.name', 'Engine')
            ->assertJsonPath('data.spec_groups.0.items.0.value', '2.0L')
            ->assertJsonPath('data.equipment_groups.0.items.0.value', '●')
            ->assertJsonPath('data.raw.source', 'test')
            ->assertJsonPath('data.spec.name', 'Comfort');

        $this->getJson('/api/v1/listings/999999')->assertNotFound();
        $this->getJson('/api/v1/models/999999')->assertNotFound();
    }

    private function listing(int $id, int $brandId, ?int $seriesId, string $title, float $price, string $status, string $fuel, array $extra = []): void
    {
        Listing::query()->create(array_merge([
            'infoid' => $id,
            'brand_id' => $brandId,
            'series_id' => $seriesId,
            'title' => $title,
            'price_usd' => $price,
            'fuel_name' => $fuel,
            'status' => $status,
            'reg_date' => '2018.05',
            'mileage_km' => 10000,
        ], $extra));

        ListingPhoto::query()->create([
            'infoid' => $id,
            'sort_order' => 0,
            'preview_url' => 'https://cdn.test/'.$id.'.jpg',
            'large_url' => 'https://cdn.test/'.$id.'-large.jpg',
            'original_url' => 'https://cdn.test/'.$id.'-orig.jpg',
        ]);
    }
}
