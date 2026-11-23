<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    protected $primaryKey = 'series_id';

    public $incrementing = false;

    protected $table = 'series';

    protected $fillable = [
        'series_id',
        'brand_id',
        'name',
        'icon_url',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(Spec::class, 'series_id', 'series_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'series_id', 'series_id');
    }
}