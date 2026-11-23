<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionPhoto extends Model
{
    protected $fillable = ['lot_id', 'sort_order', 'preview_url', 'large_url'];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(AuctionLot::class, 'lot_id', 'lot_id');
    }
}
