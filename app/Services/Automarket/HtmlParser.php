<?php

namespace App\Services\Automarket;

use DOMDocument;
use DOMXPath;

class HtmlParser
{
    public function isLoginPage(string $html): bool
    {
        return str_contains($html, 'name="username"') && str_contains($html, 'ATTENTION!');
    }

    public function isAuthenticated(string $html): bool
    {
        return str_contains($html, '/auth/logout.php') && ! $this->isLoginPage($html);
    }

    public function isBlocked(string $html): bool
    {
        return str_contains($html, 'error=block') || str_contains($html, 'Access is limited');
    }

    public function isCaptcha(string $html): bool
    {
        return (bool) preg_match('/g-recaptcha|recaptcha|data-sitekey|captcha/i', $html);
    }

    /** @return list<array{maker_id: int, name: string, group_name: ?string}> */
    public function makers(string $html): array
    {
        $xp = $this->xpath($html);
        $select = $xp->query('//*[@id="mrk"]')->item(0);
        if (! $select) {
            return [];
        }

        $makers = [];
        $group = null;
        foreach ($select->childNodes as $node) {
            if ($node->nodeName === 'optgroup') {
                $group = $node->attributes?->getNamedItem('label')?->nodeValue;
                foreach ($node->childNodes as $option) {
                    $this->pushMaker($makers, $option, $group);
                }
            } else {
                $this->pushMaker($makers, $node, $group);
            }
        }

        return $makers;
    }

    /** @return list<array{model_id: int, name: string}> */
    public function models(string $sajaxBody): array
    {
        if (! str_starts_with(ltrim($sajaxBody), '+:')) {
            return [];
        }
        $html = $this->unwrapSajax($sajaxBody);
        $xp = $this->xpath($html);
        $models = [];
        foreach ($xp->query('//option') as $option) {
            $id = (int) $option->attributes?->getNamedItem('value')?->nodeValue;
            $name = trim($option->textContent);
            if ($id > 0 && $name !== '') {
                $models[] = ['model_id' => $id, 'name' => $name];
            }
        }

        return $models;
    }

    public function isResultsPage(string $html): bool
    {
        return str_contains($html, 'Found total lots:');
    }

    public function totalLots(string $html): int
    {
        if (preg_match('/Found total lots:\s*(?:<[^>]+>\s*)*([\d\s]+)/i', $html, $match)) {
            return (int) preg_replace('/\D/', '', $match[1]);
        }

        return 0;
    }

    /** @return list<array<string, mixed>> */
    public function lots(string $html): array
    {
        $xp = $this->xpath($html);
        $lots = [];
        $seen = [];
        foreach ($xp->query('//td[starts-with(@id,"date_")]') as $cell) {
            $id = $cell->attributes?->getNamedItem('id')?->nodeValue ?? '';
            if (! preg_match('/^date_(\d+)$/', $id, $match)) {
                continue;
            }
            $n = $match[1];
            $link = $xp->query('//*[@id="bid_number_'.$n.'"]//a')->item(0);
            $href = html_entity_decode($link?->attributes?->getNamedItem('href')?->nodeValue ?? '');
            $lotId = 0;
            if (preg_match('/[?&]id=(\d+)/', $href, $idMatch)) {
                $lotId = (int) $idMatch[1];
            }
            if ($lotId <= 0 || isset($seen[$lotId])) {
                continue;
            }
            $seen[$lotId] = true;

            $photo = $xp->query('//*[@id="photo_'.$n.'"]//img')->item(0);
            $src = null;
            if ($photo instanceof \DOMElement) {
                $src = $photo->getAttribute('load_src') ?: $photo->getAttribute('src');
                if (str_contains($src, 'loading')) {
                    $src = $photo->getAttribute('load_src') ?: null;
                }
            }
            $code = null;
            if (is_string($src) && preg_match('#/image/auto/\d{4}-\d{2}-\d{2}/(\d+)/#', $src, $codeMatch)) {
                $code = (int) $codeMatch[1];
            }
            $start = $this->text($xp, 'priceLotS'.$n);
            $end = $this->text($xp, 'priceLotE'.$n);

            $lots[] = [
                'lot_id' => $lotId,
                'bid' => $this->text($xp, 'bid_number_'.$n),
                'auction_at' => $this->text($xp, 'date_'.$n),
                'auction_name' => $this->text($xp, 'auction_'.$n),
                'auction_code' => $code,
                'maker_name' => $this->text($xp, 'company_'.$n),
                'model_name' => $this->text($xp, 'model_'.$n),
                'grade' => $this->text($xp, 'grade_'.$n),
                'year' => $this->digits($this->text($xp, 'year_'.$n), 4),
                'mileage_km' => $this->digits($this->text($xp, 'mileage_'.$n)),
                'displacement_cc' => $this->digits($this->text($xp, 'displacement_'.$n)),
                'transmission' => $this->text($xp, 'transmission_'.$n),
                'color' => $this->text($xp, 'color_'.$n),
                'model_type' => $this->text($xp, 'model_type_'.$n),
                'equipment' => $this->text($xp, 'equipment_'.$n),
                'start_price_jpy' => $this->thousandYen($start !== '' ? $start : $this->text($xp, 'start_price_'.$n)),
                'end_price_jpy' => $this->thousandYen($end !== '' ? $end : $this->text($xp, 'end_price_'.$n)),
                'result' => $this->text($xp, 'result_'.$n),
                'score' => $this->text($xp, 'scores_'.$n),
                'photo' => $src,
            ];
        }

        return $lots;
    }

    /** @return array{photos: list<string>, attributes: array<string, string>} */
    public function detail(string $html): array
    {
        $photos = [];
        if (preg_match_all('/load_src="([^"]+)"/', $html, $matches)) {
            foreach ($matches[1] as $url) {
                $url = html_entity_decode($url);
                if (! str_contains($url, 'number=')) {
                    continue;
                }
                $large = preg_replace('/&h=\d+/', '', $url) ?? $url;
                $photos[$large] = $large;
            }
        }

        $attributes = [];
        $xp = $this->xpath($html);
        foreach ($xp->query('//td[contains(@class,"ColorCell_1")]') as $labelNode) {
            if (! $labelNode instanceof \DOMElement) {
                continue;
            }
            $label = strtolower(trim(preg_replace('/\s+/u', ' ', $labelNode->textContent) ?? ''));
            if ($label === '' || isset($attributes[$label])) {
                continue;
            }
            $valueNode = $labelNode->nextSibling;
            while ($valueNode && ! ($valueNode instanceof \DOMElement && str_contains($valueNode->getAttribute('class'), 'ColorCell_2'))) {
                $valueNode = $valueNode->nextSibling;
            }
            if (! $valueNode instanceof \DOMElement) {
                continue;
            }
            $image = '';
            foreach ($valueNode->getElementsByTagName('img') as $img) {
                $candidate = html_entity_decode($img->getAttribute('src'));
                if ($candidate !== '' && ! str_contains($candidate, 'loading')) {
                    $image = $candidate;
                    break;
                }
            }
            $text = trim(preg_replace('/\s+/u', ' ', $valueNode->textContent) ?? '');
            $value = $image !== '' ? $image : $text;
            if ($value !== '') {
                $attributes[$label] = $value;
            }
        }

        return [
            'photos' => array_values($photos),
            'attributes' => $attributes,
        ];
    }

    private function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8">'.$html);

        return new DOMXPath($dom);
    }

    /** @param  list<array{maker_id: int, name: string, group_name: ?string}>  $makers */
    private function pushMaker(array &$makers, mixed $node, ?string $group): void
    {
        if (! is_object($node) || ($node->nodeName ?? '') !== 'option') {
            return;
        }
        $id = (int) ($node->attributes?->getNamedItem('value')?->nodeValue ?? 0);
        $name = trim($node->textContent ?? '');
        if ($id > 0 && $name !== '' && ! str_starts_with($name, '---')) {
            $makers[] = ['maker_id' => $id, 'name' => $name, 'group_name' => $group];
        }
    }

    private function text(DOMXPath $xp, string $id): string
    {
        $node = $xp->query('//*[@id="'.$id.'"]')->item(0);

        return $node ? trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '') : '';
    }

    private function digits(string $value, ?int $length = null): ?int
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';
        if ($digits === '') {
            return null;
        }
        if ($length) {
            $digits = substr($digits, 0, $length);
        }

        return (int) $digits;
    }

    private function thousandYen(string $value): ?int
    {
        if (! preg_match('/\d/', $value)) {
            return null;
        }
        $number = (int) preg_replace('/\D/', '', $value);

        return $number * 1000;
    }

    private function unwrapSajax(string $body): string
    {
        $data = substr(ltrim($body), 2);
        if (preg_match("/var\\s+res\\s*=\\s*'(.*)'\\s*;?/s", $data, $match)) {
            return stripcslashes($match[1]);
        }
        $decoded = json_decode($data);

        return is_string($decoded) ? $decoded : trim($data, " \t\n\r'\"");
    }
}
