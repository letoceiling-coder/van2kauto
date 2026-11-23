<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TgBroadcast extends Model
{
    public const STATUSES = ['draft' => 'Черновик', 'queued' => 'В очереди', 'sending' => 'Отправляется', 'done' => 'Отправлена', 'cancelled' => 'Отменена'];

    protected $fillable = ['text', 'photo_url', 'button_text', 'button_url', 'status', 'last_subscriber_id', 'sent_count', 'failed_count', 'created_by', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
