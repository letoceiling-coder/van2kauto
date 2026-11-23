<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShowcaseCar extends Model
{
    public const COUNTRIES = ['china' => 'Китай', 'korea' => 'Корея', 'japan' => 'Япония'];

    public const CONDITIONS = ['new' => 'Новый', 'used' => 'С пробегом'];

    public const BODIES = ['crossover' => 'Кроссовер', 'suv' => 'Внедорожник', 'sedan' => 'Седан', 'hatchback' => 'Хэтчбек', 'minivan' => 'Минивэн', 'wagon' => 'Универсал', 'coupe' => 'Купе', 'pickup' => 'Пикап', 'kei' => 'Кей-кар'];

    public const ENGINES = ['petrol' => 'Бензин', 'diesel' => 'Дизель', 'hybrid' => 'Гибрид', 'phev' => 'Подключаемый гибрид', 'erev' => 'Последовательный гибрид', 'ev' => 'Электро'];

    public const DRIVES = ['FWD' => 'Передний', 'RWD' => 'Задний', 'AWD' => 'Полный'];

    public const STEERING = ['left' => 'Левый', 'right' => 'Правый'];

    public const STATUSES = ['order' => 'Под заказ', 'auction' => 'Аукцион', 'stock' => 'В наличии в РФ'];

    protected $fillable = [
        'slug', 'source_infoid', 'brand_id', 'series_id', 'spec_id',
        'country', 'brand', 'model', 'generation', 'trim', 'year', 'condition', 'body',
        'engine', 'engine_label', 'displacement_l', 'power_hp', 'drive', 'gearbox', 'steering',
        'mileage_km', 'price_from_rub', 'status', 'utilization_override', 'badge', 'summary',
        'highlights', 'eta_from', 'eta_to', 'is_published', 'is_hit', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'is_published' => 'boolean',
            'is_hit' => 'boolean',
            'utilization_override' => 'boolean',
            'displacement_l' => 'decimal:1',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ShowcaseCar $car) {
            if (! $car->slug) {
                $car->slug = self::uniqueSlug($car->title().' '.$car->year, $car->id);
            }
            if ($car->is_published && ! $car->published_at) {
                $car->published_at = now();
            }
        });
        static::saved(fn () => Settings::bumpShowcaseVersion());
        static::deleted(fn () => Settings::bumpShowcaseVersion());
    }

    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'car';
        $slug = $base;
        $i = 2;
        while (self::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ShowcasePhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function sourceListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'source_infoid', 'infoid');
    }

    public function title(): string
    {
        return trim($this->brand.' '.$this->model);
    }

    /** «Проходной» по утильсбору: ручной оверрайд, иначе мощность и возраст из настроек. */
    public function utilizationOk(): ?bool
    {
        if ($this->utilization_override !== null) {
            return (bool) $this->utilization_override;
        }
        if (! $this->power_hp) {
            return null;
        }
        $maxHp = Settings::int('utilization_max_hp', 160);
        $age = (int) now()->year - (int) $this->year;
        $minAge = Settings::int('utilization_min_age', 3);
        $maxAge = Settings::int('utilization_max_age', 5);

        return $this->power_hp <= $maxHp && ($this->condition === 'new' || ($age >= $minAge && $age <= $maxAge));
    }

    /** Копия карточки: черновик без публикации, с копией ссылок на те же файлы фото. */
    public function duplicate(): self
    {
        $copy = $this->replicate(['slug', 'published_at']);
        $copy->is_published = false;
        $copy->is_hit = false;
        $copy->slug = self::uniqueSlug($this->slug.'-copy');
        $copy->save();

        foreach ($this->photos as $photo) {
            $copy->photos()->create($photo->only(['path', 'source_url', 'width', 'height', 'sort_order']));
        }

        return $copy;
    }
}
