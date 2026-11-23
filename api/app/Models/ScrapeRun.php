<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapeRun extends Model
{
    protected $fillable = [
        'mode',
        'status',
        'pages_done',
        'items_saved',
        'items_skipped',
        'items_marked_absent',
        'log',
        'error_message',
        'needs_captcha',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'log' => 'array',
            'needs_captcha' => 'boolean',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function appendLog(string $line): void
    {
        $log = $this->log ?? [];
        $log[] = ['at' => now()->toIso8601String(), 'msg' => $line];
        if (count($log) > 200) {
            $log = array_slice($log, -200);
        }
        $this->log = $log;
        $this->save();
    }
}