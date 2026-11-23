<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionMaker extends Model
{
    protected $primaryKey = 'maker_id';

    public $incrementing = false;

    protected $fillable = ['maker_id', 'name', 'group_name'];

    public function models(): HasMany
    {
        return $this->hasMany(AuctionModel::class, 'maker_id', 'maker_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(AuctionLot::class, 'maker_id', 'maker_id');
    }
}
