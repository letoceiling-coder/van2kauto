<?php

namespace App\Services\Telegram;

use RuntimeException;

class TelegramException extends RuntimeException
{
    /** Пользователь заблокировал бота или чат недоступен. */
    public function isBlocked(): bool
    {
        return $this->getCode() === 403 || str_contains(mb_strtolower($this->getMessage()), 'chat not found');
    }
}
