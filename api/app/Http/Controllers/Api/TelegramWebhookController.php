<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Telegram\BotUpdateHandler;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, BotUpdateHandler $handler): JsonResponse
    {
        $secret = (string) Settings::get('tg_webhook_secret', '');
        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return response()->json(['ok' => false], 403);
        }

        // Telegram повторяет апдейт, если ответ задержался, — обрабатываем каждый update_id один раз.
        $updateId = (int) $request->input('update_id', 0);
        if ($updateId > 0 && ! \Illuminate\Support\Facades\Cache::add("tg:update:{$updateId}", 1, 86400)) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        try {
            $handler->handle($request->all());
        } catch (\Throwable $e) {
            // Telegram повторяет апдейт при не-200 — логируем и отвечаем 200, чтобы не зациклить.
            Log::warning('telegram webhook: '.$e->getMessage(), ['update_id' => $request->input('update_id')]);
        }

        return response()->json(['ok' => true]);
    }
}
