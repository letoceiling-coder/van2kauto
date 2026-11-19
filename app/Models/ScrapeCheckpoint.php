<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeCheckpoint extends Model
{
    protected $fillable = [
        'name',
        'last_page',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}