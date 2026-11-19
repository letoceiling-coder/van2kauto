<?php

namespace Tests\Unit;

use App\Services\Che168\ImageUrlHelper;
use PHPUnit\Framework\TestCase;

class ImageUrlHelperTest extends TestCase
{
    private const SAMPLE = 'https://erscglobal2.autoimg.cn/escimg/auto/g34/M08/52/B4/640x480_c42_autohomecar__ChtpWGr4Q_2AdlATAAFh-M7ALiI011.jpg.webp?format=webp&w=540';

    public function test_original_strips_webp_and_uses_zero_size(): void
    {
        $url = ImageUrlHelper::original(self::SAMPLE);

        $this->assertStringContainsString('0x0_c42_autohomecar__', $url);
        $this->assertStringEndsWith('.jpg', $url);
        $this->assertStringNotContainsString('.webp', $url);
    }

    public function test_hash_extracted_from_cdn_url(): void
    {
        $hash = ImageUrlHelper::hashFromUrl(self::SAMPLE);

        $this->assertSame('ChtpWGr4Q_2AdlATAAFh-M7ALiI011', $hash);
    }
}