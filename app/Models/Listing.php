<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Listing extends Model
{
    protected $primaryKey = 'infoid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'infoid',
        'spec_id',
        'brand_id',
        'series_id',
        'dealer_id',
        'title',
        'price_usd',
        'export_fee_usd',
        'port_fee_usd',
        'mileage_km',
        'reg_date',
        'produce_date',
        'city_slug',
        'city_id',
        'color',
        'vin_masked',
        'fuel_name',
        'engine',
        'gearbox',
        'driving_mode',
        'steering',
        'body_type',
        'doors',
        'seats',
        'class_level',
        'dimension',
        'curb_weight_kg',
        'cert_status',
        'claim_status',
        'mr_status',
        'certification',
        'paramkey',
        'report_url',
        'detail_url',
        'whatsapp_url',
        'wechat_url',
        'language',
        'status',
        'scraped_at',
        'raw_carinfo',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'decimal:2',
            'export_fee_usd' => 'decimal:2',
            'port_fee_usd' => 'decimal:2',
            'produce_date' => 'datetime',
            'scraped_at' => 'datetime',
            'raw_carinfo' => 'array',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id', 'series_id');
    }

    public function spec(): BelongsTo
    {
        return $this->belongsTo(Spec::class, 'spec_id', 'spec_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class, 'infoid', 'infoid')->orderBy('sort_order');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ListingReport::class, 'infoid', 'infoid');
    }
}