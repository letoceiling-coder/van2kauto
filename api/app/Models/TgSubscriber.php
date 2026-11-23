<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TgSubscriber extends Model
{
    protected $fillable = ['chat_id', 'username', 'first_name', 'last_name', 'start_param', 'phone', 'is_blocked', 'last_seen_at'];

    protected function casts(): array
    {
        return ['is_blocked' => 'boolean', 'last_seen_at' => 'datetime'];
    }

    public function displayName(): string
    {
        $name = trim($this->first_name.' '.$this->last_name);

        return $this->username ? '@'.$this->username.($name ? " ({$name})" : '') : ($name ?: (string) $this->chat_id);
    }
}
