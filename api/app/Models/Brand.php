<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $primaryKey = 'brand_id';

    public $incrementing = false;

    protected $fillable = [
        'brand_id',
        'name',
        'py',
        'icon_url',
    ];

    public function series(): HasMany
    {
        return $this->hasMany(Series::class, 'brand_id', 'brand_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'brand_id', 'brand_id');
    }
}