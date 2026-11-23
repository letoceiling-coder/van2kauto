<?php

namespace App\Services\Che168;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SessionWarmupService
{
    public const SESSION_PATH = 'che168/session.json';

    private const WARM_URL = 'https://global.che168.com/en/used-cars';

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    /**
     * @return array{device_id: string, cookies: string, meta: array<string, mixed>}
     */
    public function warm(?string $captchaToken = null): array
    {
        $captchaToken ??= config('che168.edgeone_captcha_token');

        $jar = [];
        $response = Http::withHeaders([
            'User-Agent' => self::UA,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cookie' => EdgeOneChallenge::cookieHeader($captchaToken),
        ])
            ->timeout(45)
            ->withOptions(['allow_redirects' => true])
            ->get(self::WARM_URL);

        $this->collectSetCookies($response->headers(), $jar);

        if ($response->successful()) {
            usleep(1_200_000);
            $response = Http::withHeaders([
                'User-Agent' => self::UA,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Cookie' => $this->jarHeader($jar, EdgeOneChallenge::cookieHeader($captchaToken)),
            ])
                ->timeout(45)
                ->get(self::WARM_URL);
            $this->collectSetCookies($response->headers(), $jar);
        }

        $body = $response->body();
        $isCaptcha = str_contains($body, 'Security Verification')
            || str_contains($body, 'TencentEOCaptchaWidget');

        $cookieHeader = $this->jarHeader($jar, EdgeOneChallenge::cookieHeader($captchaToken));
        $deviceId = $this->extractCookie($jar, 'che_sessionvid')
            ?? $this->matchCookieValue($cookieHeader, 'che_sessionvid');

        if (! filled($deviceId) && preg_match('/che_sessionvid=([A-F0-9-]{36})/i', $body, $m)) {
            $deviceId = $m[1];
        }

        $meta = [
            'http_status' => $response->status(),
            'captcha_page' => $isCaptcha,
            'cookie_names' => array_keys($jar),
            'bytes' => strlen($body),
        ];

        if (! filled($deviceId) && ! $isCaptcha) {
            $deviceId = config('che168.device_id');
        }

        if (! filled($deviceId)) {
            throw new RuntimeException(
                $isCaptcha
                    ? 'EdgeOne captcha: set CHE168_EDGEONE_CAPTCHA_TOKEN (cookie EO-Bot-Captcha-Token after checkbox on server IP) and retry che168:warm-session.'
                    : 'Could not obtain che_sessionvid after warm-up.'
            );
        }

        if ($jar === []) {
            $cookieHeader = EdgeOneChallenge::cookieHeader($captchaToken).'; che_sessionvid='.$deviceId;
        }

        $payload = [
            'device_id' => $deviceId,
            'cookies' => $cookieHeader,
            'meta' => $meta,
            'warmed_at' => now()->toIso8601String(),
        ];

        Storage::disk('local')->put(self::SESSION_PATH, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $payload;
    }

    /** @return array{device_id?: string, cookies?: string}|null */
    public function loadedSession(): ?array
    {
        if (! Storage::disk('local')->exists(self::SESSION_PATH)) {
            return null;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode(Storage::disk('local')->get(self::SESSION_PATH), true);

        return is_array($data) ? $data : null;
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     * @param  array<string, string>  $jar
     */
    private function collectSetCookies(array $headers, array &$jar): void
    {
        $setCookies = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? [];
        if (! is_array($setCookies)) {
            $setCookies = [$setCookies];
        }

        foreach ($setCookies as $line) {
            if (! is_string($line) || $line === '') {
                continue;
            }
            $pair = explode(';', $line, 2)[0];
            if (! str_contains($pair, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $pair, 2);
            $name = trim($name);
            if ($name !== '' && $value !== '') {
                $jar[$name] = $value;
            }
        }
    }

    /**
     * @param  array<string, string>  $jar
     */
    private function jarHeader(array $jar, string $bootstrap): string
    {
        $parts = array_filter(array_merge(
            array_map(fn ($k, $v) => $k.'='.$v, array_keys($jar), $jar),
            explode('; ', $bootstrap)
        ));

        return implode('; ', array_unique($parts));
    }

    /**
     * @param  array<string, string>  $jar
     */
    private function extractCookie(array $jar, string $name): ?string
    {
        return $jar[$name] ?? null;
    }

    private function matchCookieValue(string $header, string $name): ?string
    {
        if (preg_match('/'.preg_quote($name, '/').'=([^;]+)/', $header, $m)) {
            return $m[1];
        }

        return null;
    }
}