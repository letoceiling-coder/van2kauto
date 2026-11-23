@extends('che168.layouts.app')
@section('title', 'Заявки')
@section('content')
<h1 style="margin-top:0">Заявки</h1>
<div class="grid-3" style="margin-bottom:1rem">
    @foreach(\App\Models\Lead::STATUSES as $k => $l)
        <a class="card stat" href="{{ route('admin.leads.index', ['status' => $k]) }}"><h3>{{ $counts[$k] ?? 0 }}</h3><p>{{ $l }}</p></a>
    @endforeach
</div>
@if($unsent)
    <div class="alert alert-warn">{{ $unsent }} заявок не ушли в Telegram (бот не настроен или сбой). Они повторяются автоматически каждые 5 минут — проверьте <a href="{{ route('admin.settings.show') }}">настройки бота</a>.</div>
@endif
<form method="get" class="row" style="margin-bottom:1rem">
    <input name="q" value="{{ request('q') }}" placeholder="Телефон, имя, машина" style="max-width:260px">
    <select name="status" style="max-width:160px"><option value="">Все статусы</option>@foreach(\App\Models\Lead::STATUSES as $k=>$l)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $l }}</option>@endforeach</select>
    <select name="source" style="max-width:160px"><option value="">Все источники</option>@foreach(\App\Models\Lead::SOURCES as $k=>$l)<option value="{{ $k }}" @selected(request('source')===$k)>{{ $l }}</option>@endforeach</select>
    <button class="btn btn-ghost">Показать</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th>#</th><th>Когда</th><th>Источник</th><th>Контакт</th><th>Машина</th><th>UTM</th><th>Статус</th><th>TG</th></tr></thead>
    <tbody>
    @forelse($leads as $lead)
        <tr>
            <td><a href="{{ route('admin.leads.show', $lead) }}">{{ $lead->id }}</a></td>
            <td>{{ $lead->created_at->format('d.m H:i') }}</td>
            <td>{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</td>
            <td><b>{{ $lead->contact }}</b><div class="muted">{{ $lead->name }} {{ $lead->channel ? '· '.$lead->channel : '' }}</div></td>
            <td>{{ $lead->car_title ?? '—' }}</td>
            <td class="muted">{{ implode(' / ', array_filter((array) $lead->utm)) ?: '—' }}</td>
            <td><span class="badge badge-{{ $lead->status === 'new' ? 'active' : 'inactive' }}">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</span>@if($lead->manager)<div class="muted">{{ $lead->manager }}</div>@endif</td>
            <td>@if($lead->notified_at)✅@else<span title="{{ $lead->notify_error }}">⚠️</span>@endif</td>
        </tr>
    @empty
        <tr><td colspan="8">Заявок пока нет.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $leads->links('che168.pagination') }}
@endsection
