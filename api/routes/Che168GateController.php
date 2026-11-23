<?php

namespace App\Http\Controllers;

use App\Services\Che168\EdgeOneChallenge;
use App\Services\Che168\SessionWarmupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class Che168GateController extends Controller
{
    private const TARGET = 'https://global.che168.com/en/used-cars';

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    public function showPublic(): View|Response
    {
        return $this->gateView($this->gateSecret());
    }

    public function show(Request $request): View|Response
    {
        if (! $this->authorized($request)) {
            return response('Forbidden', 403);
        }

        return $this->gateView((string) $request->query('secret'));
    }

    private function gateView(string $secret): View
    {
        return view('che168.gate', [
            'proxyUrl' => route('che168.gate.proxy', ['secret' => $secret]),
            'storeUrl' => route('che168.gate.store', ['secret' => $secret]),
        ]);
    }

    private function gateSecret(): string
    {
        $secret = config('che168.gate_secret');
        if (! filled($secret)) {
            abort(503, 'CHE168_GATE_SECRET is not configured.');
        }

        return (string) $secret;
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

        $storeUrl = route('che168.gate.store', ['secret' => $request->query('secret')]);
        $csrf = csrf_token();

        $inject = <<<JS
<script>
window.__saveEoToken=function(t){
  fetch("{$storeUrl}",{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":"{$csrf}"},body:JSON.stringify({token:t})})
    .then(r=>r.json()).then(d=>{window.parent.postMessage({type:"eo-captcha-saved",ok:d.ok,message:d.message},"*");})
    .catch(e=>window.parent.postMessage({type:"eo-captcha-saved",ok:false,message:String(e)},"*"));
};
</script>
JS;

        $count = 0;
        $replaced = preg_replace(
            '/function cb\(r\)\{if\(r\.ticket\)\{document\.cookie/',
            'function cb(r){if(r.ticket){window.__saveEoToken(r.ticket);document.cookie',
            $html,
            1,
            $count
        );

        $html = is_string($replaced) ? $replaced : $html;

        if ($count === 0) {
            $html = str_replace('</body>', $inject.'</body>', $html);
        } else {
            $html = str_replace('<head>', '<head>'.$inject, $html);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function store(Request $request): Response
    {
        if (! $this->authorized($request)) {
            return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
        }

        $token = (string) $request->input('token', '');
        if (! filled($token)) {
            return response()->json(['ok' => false, 'message' => 'token missing'], 422);
        }

        $this->writeEnvLine('CHE168_EDGEONE_CAPTCHA_TOKEN', $token);

        try {
            /** @var SessionWarmupService $warmup */
            $warmup = app(SessionWarmupService::class);
            $session = $warmup->warm($token);
            $this->writeEnvLine('CHE168_DEVICE_ID', $session['device_id']);
            $cookies = '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $session['cookies']).'"';
            $this->writeEnvLine('CHE168_COOKIES', $cookies);
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Token saved, warm failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Token saved. Device: '.($session['device_id'] ?? '?').'. Run che168:test on server.',
        ]);
    }

    private function authorized(Request $request): bool
    {
        $secret = config('che168.gate_secret');
        if (! filled($secret)) {
            return false;
        }

        return hash_equals((string) $secret, (string) $request->query('secret', ''));
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

        $text = file_get_contents($path);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $line = $key.'='.$value;

        $text = preg_match($pattern, $text)
            ? (string) preg_replace($pattern, $line, $text)
            : $text.PHP_EOL.$line.PHP_EOL;

        file_put_contents($path, $text);
    }
}