<?php

namespace Tests\Unit;

use App\Services\Automarket\HtmlParser;
use PHPUnit\Framework\TestCase;

class AutomarketHtmlParserTest extends TestCase
{
    public function test_list_row_prices_are_thousands_of_yen_and_lot_id_comes_from_the_link(): void
    {
        $html = <<<'HTML'
        <html><body>
        Found total lots: 68 573
        <table><tr>
            <td id="date_0">2026-11-20 10:30</td>
            <td id="bid_number_0"><a href="/auctions/?p=project/lot&amp;id=982030098&amp;s">70018</a></td>
            <td id="auction_0">MIRIVE Aichi</td>
            <td id="photo_0"><img src="https://i.aleado.ru/image/auto/2026-11-20/246/70018/1.jpg?w=72"></td>
            <td id="company_0">Toyota</td>
            <td id="model_0">Vanguard</td>
            <td id="grade_0">240S</td>
            <td id="year_0">2011</td>
            <td id="mileage_0">85,000 km</td>
            <td id="displacement_0">2,400 cc</td>
            <td id="transmission_0">AT</td>
            <td id="color_0">White</td>
            <td id="model_type_0">DBA-ACA38W</td>
            <td id="equipment_0">AAC</td>
            <td id="start_price_0">120</td>
            <td id="end_price_0">—</td>
            <td id="result_0"></td>
            <td id="scores_0">4</td>
        </tr></table>
        </body></html>
        HTML;

        $parser = new HtmlParser;
        $lots = $parser->lots($html);

        $this->assertSame(68573, $parser->totalLots($html));
        $this->assertCount(1, $lots);
        $this->assertSame(982030098, $lots[0]['lot_id']);
        $this->assertSame('70018', $lots[0]['bid']);
        $this->assertSame('Toyota', $lots[0]['maker_name']);
        $this->assertSame(2011, $lots[0]['year']);
        $this->assertSame(85000, $lots[0]['mileage_km']);
        $this->assertSame(2400, $lots[0]['displacement_cc']);
        $this->assertSame(120000, $lots[0]['start_price_jpy']);
        $this->assertNull($lots[0]['end_price_jpy']);
        $this->assertSame(246, $lots[0]['auction_code']);
    }

    public function test_login_captcha_and_model_payloads(): void
    {
        $parser = new HtmlParser;

        $this->assertTrue($parser->isLoginPage('<form><input name="username">ATTENTION!</form>'));
        $this->assertFalse($parser->isAuthenticated('<form><input name="username">ATTENTION!</form>'));
        $this->assertTrue($parser->isAuthenticated('<a href="/auth/logout.php">out</a>'));
        $this->assertTrue($parser->isCaptcha('<div class="g-recaptcha" data-sitekey="x"></div>'));

        $models = $parser->models('+:'.json_encode('<select><option value="0">---</option><option value="55">Vanguard</option></select>'));
        $this->assertSame([['model_id' => 55, 'name' => 'Vanguard']], $models);

        $wrapped = "+:var res = ' <select name=\\\"mdl\\\" id=\\\"mdl\\\">\\n <option value=\\\"-1\\\">---All models---</option>\\n <option value=\\\"1689\\\">4RUNNER</option>\\n </select>';";
        $this->assertSame([['model_id' => 1689, 'name' => '4RUNNER']], $parser->models($wrapped));
        $this->assertTrue($parser->isResultsPage('Found total lots: 0'));

        $makers = $parser->makers('<select id="mrk"><optgroup label="Japan"><option value="1">Toyota</option></optgroup></select>');
        $this->assertSame([['maker_id' => 1, 'name' => 'Toyota', 'group_name' => 'Japan']], $makers);
    }

    public function test_detail_collects_large_photos_and_known_labels(): void
    {
        $html = <<<'HTML'
        <img load_src="https://i.aleado.ru/pic/?system=auto&amp;date=2026-11-20&amp;auct=246&amp;bid=70018&amp;number=1&amp;h=96">
        <img load_src="https://i.aleado.ru/pic/?system=auto&amp;date=2026-11-20&amp;auct=246&amp;bid=70018&amp;number=2&amp;h=96">
        <div>Fuel</div><div>Gasoline</div>
        <div>Chassis number</div><div>ACA38-123</div>
        HTML;

        $detail = (new HtmlParser)->detail($html);

        $this->assertCount(2, $detail['photos']);
        $this->assertStringNotContainsString('h=96', $detail['photos'][0]);
        $this->assertSame('Gasoline', $detail['attributes']['fuel']);
        $this->assertSame('ACA38-123', $detail['attributes']['chassis number']);
    }
}
