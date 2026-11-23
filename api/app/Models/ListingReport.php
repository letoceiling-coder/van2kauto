<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingReport extends Model
{
    protected $primaryKey = 'infoid';

    public $incrementing = false;

    protected $fillable = [
        'infoid',
        'claims_count',
        'latest_mileage_km',
        'insurance_url',
        'maintenance_url',
        'raw_summary',
    ];

    protected function casts(): array
    {
        return [
            'raw_summary' => 'array',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'infoid', 'infoid');
    }
}