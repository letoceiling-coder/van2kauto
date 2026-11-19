<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionLot extends Model
{
    protected $primaryKey = 'lot_id';

    public $incrementing = false;

    protected $fillable = [
        'lot_id', 'bid', 'auction_name', 'auction_code', 'auction_at',
        'maker_id', 'model_id', 'maker_name', 'model_name', 'grade',
        'year', 'mileage_km', 'displacement_cc', 'transmission', 'color',
        'model_type', 'equipment', 'start_price_jpy', 'end_price_jpy',
        'result', 'score', 'fuel', 'chassis', 'status', 'scraped_at', 'attributes',
    ];

    protected function casts(): array
    {
        return [
            'auction_at' => 'datetime',
            'scraped_at' => 'datetime',
            'attributes' => 'array',
        ];
    }

    public function maker(): BelongsTo
    {
        return $this->belongsTo(AuctionMaker::class, 'maker_id', 'maker_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AuctionModel::class, 'model_id', 'model_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AuctionPhoto::class, 'lot_id', 'lot_id')->orderBy('sort_order');
    }
}
