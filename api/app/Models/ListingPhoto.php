<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingPhoto extends Model
{
    protected $fillable = [
        'infoid',
        'sort_order',
        'preview_url',
        'large_url',
        'original_url',
        'file_path',
        'hash',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'infoid', 'infoid');
    }
}