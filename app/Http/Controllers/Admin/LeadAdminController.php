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
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('contact', 'like', '%'.$request->input('q').'%')->orWhere('name', 'like', '%'.$request->input('q').'%')->orWhere('car_title', 'like', '%'.$request->input('q').'%')))
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
        return view('admin.leads.show', ['lead' => $lead->load('car')]);
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

    public function renotify(Lead $lead, LeadNotifier $notifier): RedirectResponse
    {
        $ok = $notifier->notify($lead);

        return back()->with('ok', $ok ? 'Отправлено в Telegram' : 'Не отправилось: '.$lead->fresh()->notify_error);
    }
}
