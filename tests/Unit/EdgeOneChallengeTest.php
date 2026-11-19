<?php

namespace Tests\Unit;

use App\Services\Che168\EdgeOneChallenge;
use PHPUnit\Framework\TestCase;

class EdgeOneChallengeTest extends TestCase
{
    public function test_bot_cookie_constants(): void
    {
        $cookies = EdgeOneChallenge::botCookies();
        $this->assertSame('379575867#', $cookies['__tst_status']);
        $this->assertSame('1291124736', $cookies['EO_Bot_Ssid']);
    }
}