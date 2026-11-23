<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const STATUSES = ['new' => 'Новая', 'in_work' => 'В работе', 'done' => 'Закрыта', 'spam' => 'Спам'];

    public const SOURCES = ['site' => 'Сайт', 'bot' => 'Telegram-бот', 'chat' => 'Чат на сайте'];

    protected $fillable = [
        'source', 'name', 'contact', 'channel', 'comment', 'showcase_car_id', 'car_slug', 'car_title',
        'page', 'context', 'utm', 'ym_client_id', 'ip', 'user_agent', 'tg_user_chat_id',
        'status', 'manager', 'note', 'tg_messages', 'notified_at', 'notify_error', 'notify_attempts', 'chat_token', 'last_client_message_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'utm' => 'array',
            'tg_messages' => 'array',
            'notified_at' => 'datetime',
            'last_client_message_at' => 'datetime',
        ];
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadMessage::class)->orderBy('id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(ShowcaseCar::class, 'showcase_car_id');
    }
}
