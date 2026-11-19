<?php

namespace App\Services\Automarket;

use App\Exceptions\AutomarketAuthException;
use App\Exceptions\AutomarketCaptchaException;
use App\Models\AuctionProxy;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SessionClient
{
    private CookieJar $jar;

    private bool $ready = false;

    private bool $proxyChosen = false;

    private ?AuctionProxy $proxy = null;

    public function __construct(private readonly HtmlParser $parser)
    {
        $stored = [];
        if (Storage::disk('local')->exists($this->path())) {
            $stored = json_decode(Storage::disk('local')->get($this->path()), true) ?: [];
        }
        $this->jar = new CookieJar(false, $stored);
    }

    public function ensure(): void
    {
        if ($this->ready) {
            return;
        }
        $html = $this->request('GET', '/auctions/?p=project/searchform&searchtype=max&s&ld')->body();
        if ($this->parser->isAuthenticated($html)) {
            $this->ready = true;

            return;
        }
        if ($this->parser->isCaptcha($html)) {
            throw new AutomarketCaptchaException('A-Automarket запросил captcha. Откройте сайт с сервера и пройдите проверку.');
        }
        $this->login();
    }

    public function login(?string $username = null, ?string $password = null): void
    {
        $stored = $this->storedCredentials();
        $username = $username ?? (string) ($stored['username'] ?? config('automarket.username'));
        $password = $password ?? (string) ($stored['password'] ?? config('automarket.password'));
        if ($username === '' || $password === '') {
            throw new AutomarketAuthException('Не заданы логин и пароль A-Automarket.');
        }

        $html = $this->request('POST', '/auth/login.php', [
            'username' => $username,
            'password' => $password,
            'Submit' => 'Sign in',
        ])->body();

        if (! $this->parser->isAuthenticated($html)) {
            $check = $this->parser->isBlocked($html) ? $html : $this->request('GET', '/auctions/?p=project/searchform&searchtype=max&s&ld')->body();
            if ($this->parser->isBlocked($html) || $this->parser->isBlocked($check)) {
                throw new AutomarketAuthException('A-Automarket ограничил доступ этому аккаунту с IP сервера. Откройте страницу входа.');
            }
            if ($this->parser->isCaptcha($check)) {
                throw new AutomarketCaptchaException('A-Automarket запросил captcha при входе.');
            }
            if (! $this->parser->isAuthenticated($check)) {
                throw new AutomarketAuthException('Не удалось войти в A-Automarket. Проверьте логин и пароль.');
            }
        }

        $this->ready = true;
    }

    public function saveCredentials(string $username, string $password): void
    {
        Storage::disk('local')->put($this->credentialsPath(), json_encode([
            'username' => $username,
            'password' => $password,
        ]));
    }

    public function chooseProxy(?AuctionProxy $proxy = null): ?AuctionProxy
    {
        if ($this->proxyChosen) {
            return $this->proxy;
        }
        $this->proxyChosen = true;
        $this->proxy = $proxy ?? AuctionProxy::query()->where('enabled', true)->inRandomOrder()->first();
        if ($this->proxy) {
            $this->proxy->forceFill(['last_used_at' => now()])->save();
        }

        return $this->proxy;
    }

    public function proxyLabel(): string
    {
        $proxy = $this->chooseProxy();

        return $proxy ? $proxy->label() : 'без прокси';
    }

    public function username(): string
    {
        $stored = $this->storedCredentials();

        return (string) ($stored['username'] ?? config('automarket.username') ?? '');
    }

    /** @return 'authenticated'|'blocked'|'captcha'|'guest'|'error' */
    public function sessionState(): string
    {
        try {
            $html = $this->request('GET', '/auctions/?p=project/searchform&searchtype=max&s&ld')->body();
        } catch (ConnectionException $e) {
            return 'error';
        }
        if ($this->parser->isAuthenticated($html)) {
            $this->ready = true;

            return 'authenticated';
        }
        if ($this->parser->isBlocked($html)) {
            return 'blocked';
        }
        if ($this->parser->isCaptcha($html)) {
            return 'captcha';
        }

        return 'guest';
    }

    /** @param  array<string, scalar|null>  $query */
    public function get(string $path, array $query = []): string
    {
        $this->ensure();

        return $this->guard($this->request('GET', $path, query: $query)->body());
    }

    /** @param  array<string, scalar|array<int, scalar>|null>  $data */
    public function post(string $path, array $data = []): string
    {
        $this->ensure();

        return $this->guard($this->request('POST', $path, $data)->body());
    }

    /** @param  array<string, scalar|array<int, scalar>|null>  $data
     * @param  array<string, scalar|null>  $query
     */
    private function request(string $method, string $path, array $data = [], array $query = []): Response
    {
        $url = str_starts_with($path, 'http') ? $path : rtrim((string) config('automarket.base'), '/').'/'.ltrim($path, '/');
        $pending = $this->http();
        try {
            if ($method === 'POST') {
                $response = $pending->asForm()->post($url, $data);
            } elseif ($query === []) {
                $response = $pending->get($url);
            } else {
                $response = $pending->get(strtok($url, '?') ?: $url, $query);
            }
        } catch (ConnectionException $e) {
            $this->proxy?->forceFill([
                'last_error' => $this->redact(substr($e->getMessage(), 0, 500)),
            ])->save();
            throw $e;
        }

        $this->persist();

        return $response;
    }

    private function guard(string $html): string
    {
        if ($this->parser->isCaptcha($html) && ! $this->parser->isAuthenticated($html)) {
            throw new AutomarketCaptchaException('A-Automarket вернул captcha. Парсер остановлен.');
        }
        if ($this->parser->isBlocked($html)) {
            throw new AutomarketAuthException('A-Automarket ограничил доступ этому аккаунту с IP сервера. Откройте страницу входа.');
        }
        if ($this->parser->isLoginPage($html)) {
            $this->ready = false;
            $this->login();
            throw new AutomarketAuthException('Сессия истекла и была обновлена. Повторите запуск парсера.');
        }

        return $html;
    }

    private function http(): PendingRequest
    {
        $this->chooseProxy();
        $options = [
            'cookies' => $this->jar,
            'allow_redirects' => true,
            'force_ip_resolve' => 'v4',
        ];
        if ($this->proxy) {
            $options['proxy'] = $this->proxy->dsn();
        }

        return Http::withOptions($options)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en,ru;q=0.8',
        ])->connectTimeout(20)
            ->timeout((int) config('automarket.http_timeout', 60))
            ->retry(3, 1000, fn ($exception) => $exception instanceof ConnectionException);
    }

    private function persist(): void
    {
        $cookies = [];
        foreach ($this->jar as $cookie) {
            $cookies[] = $cookie->toArray();
        }
        Storage::disk('local')->put($this->path(), json_encode($cookies));
    }

    private function path(): string
    {
        return 'automarket/cookies.json';
    }

    /** @return array{username?: string, password?: string} */
    private function storedCredentials(): array
    {
        if (! Storage::disk('local')->exists($this->credentialsPath())) {
            return [];
        }
        $data = json_decode(Storage::disk('local')->get($this->credentialsPath()), true);

        return is_array($data) ? $data : [];
    }

    private function credentialsPath(): string
    {
        return 'automarket/credentials.json';
    }

    private function redact(string $message): string
    {
        return preg_replace('/([a-z][a-z0-9+.-]*:\/\/)[^@\s]+@/i', '$1', $message) ?? $message;
    }
}
