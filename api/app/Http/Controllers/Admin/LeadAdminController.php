<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use App\Services\Telegram\LeadNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadAdminController extends Controller
{
    public function index(Request $request): View
    {
        $leads = Lead::query()
            ->with('car')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $op = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $request->input('q')).'%';
                $q->where(fn ($w) => $w->where('contact', $op, $like)->orWhere('name', $op, $like)->orWhere('car_title', $op, $like));
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'counts' => Lead::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'unsent' => Lead::query()->whereNull('notified_at')->where('status', '!=', 'spam')->count(),
        ]);
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', ['lead' => $lead->load(['car', 'messages'])]);
    }

    public function update(Request $request, Lead $lead, LeadService $service): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'note' => ['nullable', 'string', 'max:5000'],
            'manager' => ['nullable', 'string', 'max:120'],
        ]);
        $lead->update(['note' => $data['note'] ?? null]);
        $service->setStatus($lead, $data['status'], $data['manager'] ?? $request->user()?->name);

        return back()->with('ok', 'Сохранено');
    }

    public function reply(Request $request, Lead $lead, \App\Services\ChatService $chat): RedirectResponse
    {
        $data = $request->validate(['text' => ['required', 'string', 'max:2000']]);
        $chat->managerReply($lead, trim($data['text']), $request->user()?->name ?: 'Менеджер');

        return back()->with('ok', $lead->chat_token ? 'Ответ отправлен клиенту в чат на сайте' : ($lead->tg_user_chat_id ? 'Ответ отправлен клиенту в Telegram' : 'Ответ сохранён (у заявки нет чата — свяжитесь по контакту)'));
    }

    public function renotify(Lead $lead, LeadNotifier $notifier): RedirectResponse
    {
        $ok = $notifier->notify($lead);

        return back()->with('ok', $ok ? 'Отправлено в Telegram' : 'Не отправилось: '.$lead->fresh()->notify_error);
    }
}
