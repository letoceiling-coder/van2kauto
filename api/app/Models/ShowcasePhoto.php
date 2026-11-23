<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ShowcasePhoto extends Model
{
    protected $fillable = ['showcase_car_id', 'path', 'source_url', 'width', 'height', 'sort_order'];

    protected static function booted(): void
    {
        static::saved(fn () => Settings::bumpShowcaseVersion());
        static::deleted(function (ShowcasePhoto $photo) {
            // Файл удаляем, только если на него не ссылается копия карточки.
            if (! self::query()->where('path', $photo->path)->exists()) {
                Storage::disk('public')->delete($photo->path);
            }
            Settings::bumpShowcaseVersion();
        });
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(ShowcaseCar::class, 'showcase_car_id');
    }

    public function url(): string
    {
        return rtrim((string) config('app.url'), '/').'/storage/'.ltrim($this->path, '/');
    }
}
