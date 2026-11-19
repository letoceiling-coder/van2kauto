<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spec extends Model
{
    protected $primaryKey = 'spec_id';

    public $incrementing = false;

    protected $fillable = [
        'spec_id',
        'series_id',
        'name',
        'year_label',
        'raw_param',
        'raw_config',
    ];

    protected function casts(): array
    {
        return [
            'raw_param' => 'array',
            'raw_config' => 'array',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id', 'series_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'spec_id', 'spec_id');
    }
}