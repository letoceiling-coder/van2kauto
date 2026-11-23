<?php

namespace App\Console\Commands;

use App\Services\Che168\EdgeOneChallenge;
use App\Services\Che168\SessionWarmupService;
use Illuminate\Console\Command;

class Che168WarmSessionCommand extends Command
{
    protected $signature = 'che168:warm-session
                            {--token= : EO-Bot-Captcha-Token (overrides CHE168_EDGEONE_CAPTCHA_TOKEN)}
                            {--apply-env : Write CHE168_DEVICE_ID and CHE168_COOKIES into .env}';

    protected $description = 'Pass EdgeOne JS check and harvest Che168 cookies (needs captcha token on datacenter IPs)';

    public function handle(SessionWarmupService $warmup): int
    {
        $bot = EdgeOneChallenge::botCookies();
        $this->line('Stage 1 (JS): __tst_status='.$bot['__tst_status'].', EO_Bot_Ssid='.$bot['EO_Bot_Ssid']);

        $token = $this->option('token') ?: config('che168.edgeone_captcha_token');
        if (! filled($token)) {
            $this->warn('No CHE168_EDGEONE_CAPTCHA_TOKEN — expect Tencent EO captcha page on server IP.');
        }

        try {
            $session = $warmup->warm($token);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Hint: SSH dynamic forward, open global.che168.com through server IP, solve checkbox, copy cookie EO-Bot-Captcha-Token.');

            return self::FAILURE;
        }

        $this->info('Session stored: '.SessionWarmupService::SESSION_PATH);
        $this->line('device_id: '.$session['device_id']);
        $this->line('cookies: '.strlen($session['cookies']).' chars');

        if ($this->option('apply-env')) {
            $this->applyEnv($session['device_id'], $session['cookies']);
            $this->call('config:clear');
        }

        return self::SUCCESS;
    }

    private function applyEnv(string $deviceId, string $cookies): void
    {
        $path = base_path('.env');
        if (! is_file($path)) {
            $this->warn('.env missing, skip apply-env');

            return;
        }

        $escaped = '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $cookies).'"';
        $text = file_get_contents($path);
        $text = $this->replaceEnvLine($text, 'CHE168_DEVICE_ID', $deviceId);
        $text = $this->replaceEnvLine($text, 'CHE168_COOKIES', $escaped);
        file_put_contents($path, $text);
        $this->info('.env updated (CHE168_DEVICE_ID, CHE168_COOKIES)');
    }

    private function replaceEnvLine(string $text, string $key, string $value): string
    {
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $line = $key.'='.$value;

        return preg_match($pattern, $text)
            ? (string) preg_replace($pattern, $line, $text)
            : $text.PHP_EOL.$line.PHP_EOL;
    }
}