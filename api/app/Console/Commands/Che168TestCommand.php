<?php

namespace App\Console\Commands;

use App\Services\Che168\ApiClient;
use Illuminate\Console\Command;

class Che168TestCommand extends Command
{
    protected $signature = 'che168:test';

    protected $description = 'Test Che168 API connectivity (search page 1)';

    public function handle(ApiClient $api): int
    {
        $this->info('Device ID: '.$api->deviceId());

        try {
            $payload = $api->search(['pagesize' => 1, 'pageindex' => 1]);
            $result = $payload['result'] ?? [];
            $this->info('OK — total: '.($result['totalcount'] ?? '?').', sample: '.($result['carlist'][0]['carname'] ?? 'n/a'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}