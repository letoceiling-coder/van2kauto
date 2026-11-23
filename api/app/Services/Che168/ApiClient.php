<?php

namespace App\Services\Che168;

use App\Exceptions\Che168CaptchaException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ApiClient
{
    public function deviceId(): string
    {
        $id = config('che168.device_id');

        if (! filled($id)) {
            $id = (string) Str::uuid();
            config(['che168.device_id' => $id]);
        }

        return $id;
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $url = rtrim(config('che168.api_base'), '/').'/'.ltrim($path, '/');

        $query = array_merge($this->defaultQuery(), $query);

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Origin' => 'https://global.che168.com',
            'Referer' => 'https://global.che168.com/en/used-cars',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-site',
        ];

        $cookie = $this->cookieHeader();
        if (filled($cookie)) {
            $headers['Cookie'] = $cookie;
        }

        try {
            $pending = Http::withHeaders($headers)
                ->timeout((int) config('che168.http_timeout', 45));

            if (filled($proxy = config('che168.http_proxy'))) {
                $pending = $pending->withOptions(['proxy' => (string) $proxy]);
            }

            $response = $pending
                ->retry(3, 500, throw: false)
                ->get($url, $query);
        } catch (ConnectionException $e) {
            throw new RuntimeException("Che168 connection failed: {$url}", 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException("Che168 HTTP {$response->status()} for {$path}");
        }

        $body = $response->body();

        if (str_starts_with(trim($body), '<')) {
            throw new Che168CaptchaException(
                'Che168 returned HTML (EdgeOne captcha). Open /che168/captcha?force=captcha and pass verification.'
            );
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];

        if (($json['returncode'] ?? -1) !== 0) {
            throw new RuntimeException(
                'Che168 API error: '.($json['message'] ?? 'unknown').' ['.$path.']'
            );
        }

        return $json;
    }

    /**
     * @param  array<string, scalar|null>  $filters
     * @return array<string, mixed>
     */
    public function search(array $filters = []): array
    {
        $defaults = [
            'pageindex' => 1,
            'pagesize' => config('che168.search.page_size'),
            'sort' => 0,
            'vehicle_list' => config('che168.search.vehicle_list'),
            'report_status' => config('che168.search.report_status'),
        ];

        return $this->get('/api/v1/search', array_merge($defaults, $filters));
    }

  /** @return array<string, mixed> */
    public function carInfo(int $infoid): array
    {
        return $this->get("/api/v1/carinfo/{$infoid}");
    }

    /** @return array<string, mixed> */
    public function brands(): array
    {
        return $this->get('/api/v1/brand');
    }

    /** @return array<string, mixed> */
    public function series(int $brandId): array
    {
        return $this->get('/api/v1/series', ['brandid' => $brandId]);
    }

    /** @return array<string, mixed> */
    public function specParam(int $specId): array
    {
        return $this->get('/api/v1/specparam', ['specid' => $specId]);
    }

    /** @return array<string, mixed> */
    public function specConfig(int $specId): array
    {
        return $this->get('/api/v1/specconfig', ['specid' => $specId]);
    }

    /** @return array<string, mixed> */
    public function reportSummary(string $paramkey): array
    {
        return $this->get('/api/v1/report/summary', ['paramkey' => $paramkey]);
    }

    private function cookieHeader(): string
    {
        $parts = [];

        $configured = (string) config('che168.cookies');
        $configured = trim($configured, '"');
        if (filled($configured)) {
            $parts[] = $configured;
        }

        foreach (EdgeOneChallenge::botCookies() as $name => $value) {
            $parts[] = $name.'='.$value;
        }

        if (filled($token = config('che168.edgeone_captcha_token'))) {
            $parts[] = 'EO-Bot-Captcha-Token='.trim((string) $token, '*');
        }

        $device = $this->deviceId();
        if (filled($device) && ! str_contains(strtolower($configured), 'che_sessionvid')) {
            $parts[] = 'che_sessionvid='.$device;
        }

        return implode('; ', array_unique($parts));
    }

    /** @return array<string, scalar|null> */
    private function defaultQuery(): array
    {
        $query = [
            '_appid' => config('che168.app_id'),
            'deviceid' => $this->deviceId(),
            'language' => config('che168.language'),
            'fromsource' => config('che168.fromsource'),
        ];

        $cookies = config('che168.cookies');
        if (filled($cookies)) {
            // Http client uses Cookie header via withHeaders in constructor if needed — kept in config for proxy middleware later.
        }

        return $query;
    }
}