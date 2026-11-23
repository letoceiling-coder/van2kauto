<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ShowcaseCar;
use App\Models\ShowcasePhoto;
use App\Services\Showcase\FeedMatcher;
use App\Services\Showcase\ListingImporter;
use App\Services\Showcase\PhotoStore;
use App\Services\Site\SiteRebuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShowcaseAdminController extends Controller
{
    public function __construct(
        private readonly FeedMatcher $matcher,
        private readonly ListingImporter $importer,
        private readonly PhotoStore $photos,
    ) {}

    public function index(Request $request, SiteRebuilder $rebuilder): View
    {
        $cars = ShowcaseCar::query()
            ->with(['photos' => fn ($q) => $q->orderBy('sort_order')])
            ->when($request->input('state') === 'published', fn ($q) => $q->where('is_published', true))
            ->when($request->input('state') === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('brand', 'like', '%'.$request->input('q').'%')->orWhere('model', 'like', '%'.$request->input('q').'%')))
            ->orderBy('sort_order')->orderByDesc('id')
            ->get();

        return view('admin.showcase.index', [
            'cars' => $cars,
            'matches' => $cars->mapWithKeys(fn (ShowcaseCar $c) => [$c->id => $this->matcher->count($c)]),
            'pending' => $rebuilder->hasPendingChanges(),
            'lastBuild' => \App\Models\SiteBuild::query()->latest('id')->first(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.showcase.edit', ['car' => new ShowcaseCar(['year' => now()->year - 1, 'eta_from' => 30, 'eta_to' => 45, 'sort_order' => (int) ShowcaseCar::query()->max('sort_order') + 10]), 'match' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $car = ShowcaseCar::query()->create($this->validated($request));

        return redirect()->route('admin.showcase.edit', $car)->with('ok', 'Карточка создана. Добавьте фото и опубликуйте.');
    }

    public function edit(ShowcaseCar $car): View
    {
        $car->load('photos');

        return view('admin.showcase.edit', ['car' => $car, 'match' => $this->matcher->match($car)]);
    }

    public function update(Request $request, ShowcaseCar $car): RedirectResponse
    {
        $car->update($this->validated($request, $car));

        return back()->with('ok', 'Сохранено');
    }

    public function destroy(ShowcaseCar $car): RedirectResponse
    {
        $car->photos->each->delete();
        $car->delete();

        return redirect()->route('admin.showcase.index')->with('ok', 'Карточка удалена');
    }

    public function duplicate(ShowcaseCar $car): RedirectResponse
    {
        $copy = $car->duplicate();

        return redirect()->route('admin.showcase.edit', $copy)->with('ok', 'Копия создана (черновик). Поправьте комплектацию и цену.');
    }

    /** Массовые действия со списка: порядок, публикация, хиты. */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sort' => ['array'], 'sort.*' => ['integer'],
            'ids' => ['array'], 'ids.*' => ['integer'],
            'action' => ['nullable', 'in:publish,unpublish,hit,unhit,delete,save_order'],
        ]);

        foreach ($data['sort'] ?? [] as $id => $sort) {
            ShowcaseCar::query()->whereKey($id)->where('sort_order', '!=', $sort)->first()?->update(['sort_order' => $sort]);
        }
        $ids = $data['ids'] ?? [];
        $cars = ShowcaseCar::query()->whereIn('id', $ids)->get();
        match ($data['action'] ?? 'save_order') {
            'publish' => $cars->whereNotNull('price_from_rub')->each(fn ($c) => $c->update(['is_published' => true])),
            'unpublish' => $cars->each(fn ($c) => $c->update(['is_published' => false])),
            'hit' => $cars->each(fn ($c) => $c->update(['is_hit' => true])),
            'unhit' => $cars->each(fn ($c) => $c->update(['is_hit' => false])),
            'delete' => $cars->each(function ($c) {
                $c->photos->each->delete();
                $c->delete();
            }),
            default => null,
        };

        return back()->with('ok', 'Готово');
    }

    public function togglePublish(ShowcaseCar $car): RedirectResponse
    {
        if (! $car->is_published && $car->price_from_rub === null) {
            return back()->with('ok', 'Сначала укажите цену «от» — без неё карточку не публикуем.');
        }
        $car->update(['is_published' => ! $car->is_published]);

        return back()->with('ok', $car->is_published ? 'Опубликовано' : 'Снято с публикации');
    }

    /** Топ популярных «проходных» моделей из фида — в фоне (качает фото), результат появится в списке через 1–2 минуты. */
    public function top(Request $request): RedirectResponse
    {
        $limit = max(1, min(20, (int) $request->input('limit', 10)));
        $publish = $request->boolean('publish') ? ' --publish' : '';
        $log = storage_path('logs/showcase-top.log');
        exec('cd '.escapeshellarg(base_path()).' && php artisan showcase:top --limit='.$limit.$publish.' > '.escapeshellarg($log).' 2>&1 &');

        return back()->with('ok', "Собираю топ-{$limit} из фида: фото скачиваются, карточки появятся здесь через 1–2 минуты. Модели, которые уже есть в витрине, не дублируются.");
    }

    public function reprice(ShowcaseCar $car): RedirectResponse
    {
        $listing = $car->source_infoid ? Listing::query()->find($car->source_infoid) : null;
        $price = $listing ? \App\Services\Showcase\PriceEstimator::fromListing($listing) : null;
        if ($price === null) {
            return back()->with('ok', 'Нет исходного объявления с ценой — укажите цену вручную.');
        }
        $car->update(['price_from_rub' => $price]);

        return back()->with('ok', 'Цена пересчитана по формуле: от '.number_format($price, 0, '.', ' ').' ₽');
    }

    public function fromListing(Listing $listing): RedirectResponse
    {
        $car = $this->importer->createDraft($listing);

        return redirect()->route('admin.showcase.edit', $car)->with('ok', 'Черновик создан из объявления #'.$listing->infoid.'. Проверьте поля, цену и фото.');
    }

    public function bulkFromListings(Request $request): RedirectResponse
    {
        $ids = array_slice(array_map('intval', (array) $request->input('infoids', [])), 0, 20);
        $n = 0;
        foreach (Listing::query()->whereIn('infoid', $ids)->get() as $listing) {
            $this->importer->createDraft($listing, (int) $request->input('photos', 6));
            $n++;
        }

        return redirect()->route('admin.showcase.index', ['state' => 'draft'])->with('ok', "Создано черновиков: {$n}. Укажите цену и опубликуйте.");
    }

    public function fillFromListing(Request $request, ShowcaseCar $car): RedirectResponse
    {
        $listing = Listing::query()->findOrFail((int) $request->input('infoid'));
        $this->importer->fill($car, $listing);
        $n = $request->boolean('with_photos') ? $this->importer->importPhotos($car, $listing, 8) : 0;

        return back()->with('ok', 'Характеристики подставлены из #'.$listing->infoid.($n ? ", фото: {$n}" : ''));
    }

    public function uploadPhotos(Request $request, ShowcaseCar $car): RedirectResponse
    {
        $request->validate(['photos' => ['required', 'array', 'max:20'], 'photos.*' => ['image', 'max:12288']]);
        foreach ($request->file('photos') as $file) {
            $this->photos->upload($car, $file);
        }

        return back()->with('ok', 'Фото загружены');
    }

    public function updatePhotos(Request $request, ShowcaseCar $car): RedirectResponse
    {
        $data = $request->validate(['sort' => ['array'], 'sort.*' => ['integer'], 'delete' => ['array'], 'cover' => ['nullable', 'integer']]);
        foreach ($data['sort'] ?? [] as $id => $sort) {
            $car->photos()->whereKey($id)->first()?->update(['sort_order' => $sort]);
        }
        if (! empty($data['cover'])) {
            $min = (int) $car->photos()->min('sort_order');
            $car->photos()->whereKey($data['cover'])->first()?->update(['sort_order' => $min - 1]);
        }
        foreach ($data['delete'] ?? [] as $id => $on) {
            ShowcasePhoto::query()->where('showcase_car_id', $car->id)->whereKey($id)->first()?->delete();
        }

        return back()->with('ok', 'Фото обновлены');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?ShowcaseCar $car = null): array
    {
        $data = $request->validate([
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('showcase_cars', 'slug')->ignore($car?->id)],
            'country' => ['required', Rule::in(array_keys(ShowcaseCar::COUNTRIES))],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:120'],
            'generation' => ['nullable', 'string', 'max:80'],
            'trim' => ['nullable', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.(now()->year + 1)],
            'condition' => ['required', Rule::in(array_keys(ShowcaseCar::CONDITIONS))],
            'body' => ['required', Rule::in(array_keys(ShowcaseCar::BODIES))],
            'engine' => ['required', Rule::in(array_keys(ShowcaseCar::ENGINES))],
            'engine_label' => ['nullable', 'string', 'max:120'],
            'displacement_l' => ['nullable', 'numeric', 'min:0', 'max:9.9'],
            'power_hp' => ['nullable', 'integer', 'min:1', 'max:3000'],
            'drive' => ['required', Rule::in(array_keys(ShowcaseCar::DRIVES))],
            'gearbox' => ['nullable', 'string', 'max:60'],
            'steering' => ['required', Rule::in(array_keys(ShowcaseCar::STEERING))],
            'mileage_km' => ['nullable', 'integer', 'min:0'],
            'price_from_rub' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(ShowcaseCar::STATUSES))],
            'utilization' => ['nullable', 'in:auto,yes,no'],
            'badge' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'highlights_text' => ['nullable', 'string', 'max:3000'],
            'eta_from' => ['nullable', 'integer', 'min:1', 'max:365'],
            'eta_to' => ['nullable', 'integer', 'min:1', 'max:365'],
            'sort_order' => ['nullable', 'integer'],
            'is_published' => ['nullable', 'boolean'],
            'is_hit' => ['nullable', 'boolean'],
            'series_id' => ['nullable', 'integer'],
            'spec_id' => ['nullable', 'integer'],
        ]);

        $data['highlights'] = array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) ($data['highlights_text'] ?? '')) ?: [])));
        $data['utilization_override'] = match ($data['utilization'] ?? 'auto') {
            'yes' => true, 'no' => false, default => null,
        };
        unset($data['highlights_text'], $data['utilization']);
        $data['mileage_km'] = (int) ($data['mileage_km'] ?? 0);
        $data['eta_from'] = (int) ($data['eta_from'] ?? 30);
        $data['eta_to'] = max($data['eta_from'], (int) ($data['eta_to'] ?? 45));
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_published'] = $request->boolean('is_published') && ($data['price_from_rub'] ?? null) !== null;
        $data['is_hit'] = $request->boolean('is_hit');
        if (empty($data['slug'])) {
            unset($data['slug']);
        }

        return $data;
    }
}
