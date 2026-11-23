<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionModel extends Model
{
    protected $primaryKey = 'model_id';

    public $incrementing = false;

    protected $fillable = ['model_id', 'maker_id', 'name'];

    public function maker(): BelongsTo
    {
        return $this->belongsTo(AuctionMaker::class, 'maker_id', 'maker_id');
    }
}
