<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteBuild extends Model
{
    protected $fillable = ['trigger', 'status', 'output', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
