<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TgBroadcast;
use App\Models\TgSubscriber;
use App\Services\Telegram\TelegramClient;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BotAdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.bot', [
            'subscribers' => TgSubscriber::query()
                ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('username', 'like', '%'.$request->input('q').'%')->orWhere('first_name', 'like', '%'.$request->input('q').'%')->orWhere('start_param', 'like', '%'.$request->input('q').'%')))
                ->latest('id')->paginate(50)->withQueryString(),
            'active' => TgSubscriber::query()->where('is_blocked', false)->count(),
            'blocked' => TgSubscriber::query()->where('is_blocked', true)->count(),
            'broadcasts' => TgBroadcast::query()->latest('id')->limit(20)->get(),
            'botUsername' => Settings::get('tg_bot_username'),
            'siteUrl' => Settings::get('site_url'),
        ]);
    }

    public function storeBroadcast(Request $request, TelegramClient $telegram): RedirectResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:3500'],
            'photo_url' => ['nullable', 'url', 'max:2048'],
            'button_text' => ['nullable', 'string', 'max:60', 'required_with:button_url'],
            'button_url' => ['nullable', 'url', 'max:2048', 'required_with:button_text'],
            'mode' => ['required', 'in:test,send'],
        ]);
        $data += ['photo_url' => null, 'button_text' => null, 'button_url' => null];
        if ($data['photo_url'] && mb_strlen($data['text']) > 1024) {
            return back()->withInput()->withErrors(['text' => 'С фото текст не длиннее 1024 символов (ограничение Telegram)']);
        }

        if ($data['mode'] === 'test') {
            $broadcast = new TgBroadcast($data);
            $errors = [];
            foreach (Settings::managerChatIds() as $chatId) {
                try {
                    $extra = $broadcast->button_text ? ['reply_markup' => ['inline_keyboard' => [[['text' => $broadcast->button_text, 'url' => $broadcast->button_url]]]]] : [];
                    $broadcast->photo_url
                        ? $telegram->sendPhoto($chatId, $broadcast->photo_url, $broadcast->text, $extra)
                        : $telegram->sendMessage($chatId, $broadcast->text, $extra);
                } catch (\Throwable $e) {
                    $errors[] = $e->getMessage();
                }
            }

            return back()->withInput()->with('ok', $errors ? 'Ошибка теста: '.implode('; ', $errors) : 'Тест отправлен в чат менеджеров. Если всё ок — нажмите «Отправить всем».');
        }

        unset($data['mode']);
        TgBroadcast::query()->create($data + ['status' => 'queued', 'created_by' => $request->user()?->email]);

        return back()->with('ok', 'Рассылка поставлена в очередь, отправка начнётся в течение минуты.');
    }

    public function cancelBroadcast(TgBroadcast $broadcast): RedirectResponse
    {
        if (in_array($broadcast->status, ['queued', 'sending'], true)) {
            $broadcast->update(['status' => 'cancelled', 'finished_at' => now()]);
        }

        return back()->with('ok', 'Рассылка остановлена');
    }
}
