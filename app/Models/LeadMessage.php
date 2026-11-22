<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadMessage extends Model
{
    protected $fillable = ['lead_id', 'sender', 'author', 'text'];

    /** @return array<string, mixed> */
    public function toChat(): array
    {
        return ['id' => $this->id, 'from' => $this->sender, 'author' => $this->sender === 'manager' ? ($this->author ?: 'Менеджер') : null, 'text' => $this->text, 'at' => $this->created_at?->toIso8601String()];
    }
}
