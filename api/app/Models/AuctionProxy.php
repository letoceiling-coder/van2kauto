<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionProxy extends Model
{
    protected $fillable = [
        'scheme', 'host', 'port', 'username', 'password', 'enabled', 'last_used_at', 'last_error',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'enabled' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function label(): string
    {
        return strtoupper($this->scheme).' '.$this->host.':'.$this->port;
    }

    public function dsn(): string
    {
        $auth = '';
        if (filled($this->username)) {
            $auth = rawurlencode((string) $this->username);
            if (filled($this->password)) {
                $auth .= ':'.rawurlencode((string) $this->password);
            }
            $auth .= '@';
        }

        return $this->scheme.'://'.$auth.$this->host.':'.$this->port;
    }
}
