<?php

namespace App\Http\Controllers;

use App\Services\Che168\EdgeOneChallenge;
use App\Services\Che168\SessionWarmupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Support\Facades\Http;

class Che168GateController extends Controller
{
    private const TARGET = 'https://global.che168.com/en/used-cars';

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    public function showPublic(Request $request): Response
    {
        if ($request->boolean('saved')) {
            return $this->successPage();
        }

        if ($request->query('force') !== 'captcha' && filled(config('che168.edgeone_captcha_token'))) {
            return $this->successPage();
        }

        // Пользователь админки уже авторизован — секрет в страницу не вшиваем.
        return $this->proxy($request);
    }

    public function show(Request $request): Response
    {
        if (! $this->authorized($request)) {
            return response('Forbidden', 403);
        }

        if ($request->boolean('saved')) {
            return $this->successPage();
        }

        return $this->proxy($request);
    }

    public function proxy(Request $request): Response
    {
        if (! $this->authorized($request)) {
            return response('Forbidden', 403);
        }

        $token = config('che168.edgeone_captcha_token');
        $cookie = EdgeOneChallenge::cookieHeader($token);

        $html = Http::withHeaders([
            'User-Agent' => self::UA,
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cookie' => $cookie,
        ])->timeout(45)->get(self::TARGET)->body();

        usleep(1_200_000);

        $html = Http::withHeaders([
            'User-Agent' => self::UA,
            'Accept' => 'text/html',
            'Cookie' => $cookie,
        ])->timeout(45)->get(self::TARGET)->body();

        if (strlen($html) < 500) {
            $html = $this->fetchWithJar();
        }

        $storeUrl = auth()->check()
            ? route('che168.gate.store')
            : route('che168.gate.store', ['secret' => $request->query('secret')]);
        $csrf = csrf_token();

        $banner = '<div style="background:#1e293b;color:#e2e8f0;padding:10px 16px;font:14px system-ui,sans-serif">'
            .'Che168: отметьте галочку ниже. Токен сохранится на сервер автоматически.</div>';

        $inject = <<<JS
<script>
window.__saveEoToken=function(t){
  if(window.__eoSaving){return;}
  window.__eoSaving=true;
  var el=document.createElement("div");
  el.id="eo-saving";
  el.style.cssText="position:fixed;inset:0;background:rgba(15,23,42,.85);color:#e2e8f0;display:flex;align-items:center;justify-content:center;font:18px system-ui,sans-serif;z-index:2147483647";
  el.textContent="Saving token to server...";
  document.body.appendChild(el);
  fetch("{$storeUrl}",{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":"{$csrf}"},body:JSON.stringify({token:t})})
    .then(function(r){ if(!r.ok){return r.text().then(function(t){throw new Error(t||("HTTP "+r.status));}); } return r.json(); })
    .then(function(d){ if(d.ok){ location.replace("/che168/captcha?saved=1"); } else { window.__eoSaving=false; el.textContent=d.message||"Save error"; } })
    .catch(function(e){ window.__eoSaving=false; el.textContent=String(e); });
};
</script>
JS;

        $count = 0;
        $replaced = preg_replace(
            '/function cb\(r\)\{if\(r\.ticket\)\{[^}]+\}\}/',
            'function cb(r){if(r.ticket){window.__saveEoToken(r.ticket);}}',
            $html,
            1,
            $count
        );

        $html = is_string($replaced) ? $replaced : $html;

        if (! str_contains($html, 'window.__saveEoToken=function')) {
            if (str_contains($html, '<script src="https://gcaptcha.eo.gtimg.com/TEOCaptchaWidget-global.js"></script>')) {
                $html = str_replace(
                    '<script src="https://gcaptcha.eo.gtimg.com/TEOCaptchaWidget-global.js"></script>',
                    $inject.'<script src="https://gcaptcha.eo.gtimg.com/TEOCaptchaWidget-global.js"></script>',
                    $html
                );
            } elseif (str_contains($html, '</html>')) {
                $html = str_replace('</html>', $inject.'</html>', $html);
            } else {
                $html = $inject.$html;
            }
        }

        if (! str_contains($html, '<meta name="referrer"')) {
            $html = preg_replace('/<html([^>]*)>/i', '<html$1><meta name="referrer" content="origin">', $html, 1) ?? $html;
        }

        $html = preg_replace('/^\s*<div style="background:#1e293b[^<]+<\/div>/', '', $html) ?? $html;
        if (str_contains($html, '<main>')) {
            $html = str_replace('<main>', $banner.'<main>', $html);
        } else {
            $html = $banner.$html;
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "default-src * 'unsafe-inline' 'unsafe-eval' data: blob:; frame-src *; script-src * 'unsafe-inline' 'unsafe-eval';",
            'Referrer-Policy' => 'origin',
        ]);
    }

    public function store(Request $request): BaseResponse
    {
        if (! $this->authorized($request)) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $token = rtrim(trim((string) $request->input('token', '')), '*');
        // Только символы токена: без переводов строк и спецсимволов — иначе можно дописать произвольные строки в .env.
        if (! preg_match('/^[A-Za-z0-9._~+\/=\-]{8,4096}$/', $token)) {
            return response()->json(['ok' => false, 'message' => 'token missing or invalid'], 422);
        }

        $this->writeEnvLine('CHE168_EDGEONE_CAPTCHA_TOKEN', $token);

        $warmMessage = '';
        try {
            /** @var SessionWarmupService $warmup */
            $warmup = app(SessionWarmupService::class);
            $session = $warmup->warm($token);
            $this->writeEnvLine('CHE168_DEVICE_ID', $session['device_id']);
            $cookies = '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $session['cookies']).'"';
            $this->writeEnvLine('CHE168_COOKIES', $cookies);
            $warmMessage = ' Device: '.($session['device_id'] ?? '?');
        } catch (\Throwable $e) {
            $warmMessage = ' Warm-up: '.$e->getMessage();
        }

        Artisan::call('config:clear');

        return response()->json([
            'ok' => true,
            'message' => 'Token saved.'.$warmMessage,
        ]);
    }

    private function successPage(): Response
    {
        $html = '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><title>Che168 OK</title></head><body '
            .'style="font-family:system-ui,sans-serif;padding:2rem;background:#14532d;color:#bbf7d0;max-width:40rem">'
            .'<h1>Готово</h1>'
            .'<p>Сессия EdgeOne на сервере активна. API Che168 доступен (<code>php artisan che168:test</code>).</p>'
            .'<p>Эта страница — только для captcha, не каталог. Синк: <code>che168:sync-listings</code>, <code>che168:sync-brands</code>.</p>'
            .'<p><a style="color:#fff" href="/che168/captcha?force=captcha">Пройти captcha снова</a> · '
            .'<a style="color:#fff" href="/up">Health</a></p></body></html>';

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function authorized(Request $request): bool
    {
        if (auth()->check()) {
            return true;
        }
        $secret = config('che168.gate_secret');
        if (! filled($secret)) {
            return false;
        }

        return hash_equals((string) $secret, (string) $request->query('secret', ''));
    }

    private function gateSecret(): string
    {
        $secret = config('che168.gate_secret');
        if (! filled($secret)) {
            abort(503, 'CHE168_GATE_SECRET is not configured.');
        }

        return (string) $secret;
    }

    private function fetchWithJar(): string
    {
        $cookie = EdgeOneChallenge::cookieHeader(config('che168.edgeone_captcha_token'));

        return Http::withHeaders([
            'User-Agent' => self::UA,
            'Cookie' => $cookie,
        ])->timeout(45)->get(self::TARGET)->body();
    }

    private function writeEnvLine(string $key, string $value): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            return;
        }

        if (preg_match('/[\r\n\0]/', $value)) {
            throw new \InvalidArgumentException("Недопустимое значение для {$key}");
        }
        $text = file_get_contents($path);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $line = $key.'='.$value;

        // preg_replace_callback: «$1» и «\1» в значении не превращаются в обратные ссылки.
        $text = preg_match($pattern, $text)
            ? (string) preg_replace_callback($pattern, fn () => $line, $text)
            : $text.PHP_EOL.$line.PHP_EOL;

        file_put_contents($path, $text);
    }
}