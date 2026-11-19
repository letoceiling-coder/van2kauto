<?php

namespace Tests\Unit;

use App\Support\EngineParser;
use PHPUnit\Framework\TestCase;

class EngineParserTest extends TestCase
{
    public function test_parses_all_che168_engine_formats(): void
    {
        $this->assertSame(181, EngineParser::powerHp('1.5T 181HP L4'));
        $this->assertSame(150, EngineParser::powerHp('1.5T 150hp L4'));
        $this->assertSame(160, EngineParser::powerHp('1.8T 160 horsepower L4'));
        $this->assertSame(190, EngineParser::powerHp('2.0T 190-horsepower L4'));
        $this->assertSame(156, EngineParser::powerHp('Range Extender 156 HP'));
        $this->assertSame(95, EngineParser::powerHp('Extended Range 95 Horsepower'));
        $this->assertNull(EngineParser::powerHp('--'));
        $this->assertNull(EngineParser::powerHp(null));
        $this->assertSame(2.0, EngineParser::displacementL('2.0L 171 horsepower L4'));
        $this->assertSame(1.5, EngineParser::displacementL('1.5T 181HP L4'));
        $this->assertNull(EngineParser::displacementL('Range Extender 156 HP'));
    }

    public function test_power_from_spec_params_prefers_engine_then_motor(): void
    {
        $engine = ['paramtypeitems' => [['name' => 'Engine', 'paramitems' => [['name' => 'Maximum horsepower (Ps)', 'value' => '156']]]]];
        $ev = ['paramtypeitems' => [['name' => 'Electric Motor', 'paramitems' => [['name' => 'Total Electric Motor Horsepower (Ps)', 'valueitems' => [['value' => '309']]]]]]];
        $kw = ['paramtypeitems' => [['name' => 'Electric Motor', 'paramitems' => [['name' => 'Total Motor Power (kW)', 'value' => '100']]]]];

        $this->assertSame(156, EngineParser::powerFromSpecParam($engine));
        $this->assertSame(309, EngineParser::powerFromSpecParam($ev));
        $this->assertSame(136, EngineParser::powerFromSpecParam($kw));
        $this->assertNull(EngineParser::powerFromSpecParam(null));
    }
}
