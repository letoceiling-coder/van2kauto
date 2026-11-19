@extends('che168.layouts.app')
@section('title', 'Заявка #'.$lead->id)
@section('content')
<p><a href="{{ route('admin.leads.index') }}">← Заявки</a></p>
<h1 style="margin-top:0">Заявка #{{ $lead->id }} <span class="muted">{{ $lead->created_at->format('d.m.Y H:i') }}</span></h1>
<div class="detail-layout">
    <div class="card">
        <div class="facts">
            <div><span>Источник</span>{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</div>
            <div><span>Контакт</span>{{ $lead->contact }}</div>
            <div><span>Имя</span>{{ $lead->name ?: '—' }}</div>
            <div><span>Как связаться</span>{{ $lead->channel ?: '—' }}</div>
            <div><span>Машина</span>@if($lead->car)<a href="{{ route('admin.showcase.edit', $lead->car) }}">{{ $lead->car_title }}</a>@else{{ $lead->car_title ?: '—' }}@endif</div>
            <div><span>Страница</span>{{ $lead->page ?: '—' }}</div>
            <div><span>Metrika ClientID</span>{{ $lead->ym_client_id ?: '—' }}</div>
            <div><span>IP</span>{{ $lead->ip ?: '—' }}</div>
        </div>
        @if($lead->comment)<h3>Сообщение</h3><p style="white-space:pre-wrap">{{ $lead->comment }}</p>@endif
        @if($lead->context)<h3>Ответы</h3>@foreach($lead->context as $k=>$val)<div><span class="muted">{{ $k }}:</span> {{ $val }}</div>@endforeach @endif
        @if($lead->utm)<h3>UTM</h3>@foreach($lead->utm as $k=>$val)<div><span class="muted">{{ $k }}:</span> {{ $val }}</div>@endforeach @endif
        <h3>Telegram</h3>
        @if($lead->notified_at)
            <p class="muted">Отправлено {{ $lead->notified_at->format('d.m H:i') }} в {{ count((array) $lead->tg_messages) }} чат(а)</p>
        @else
            <p class="muted">Не отправлено: {{ $lead->notify_error ?: 'ещё не пробовали' }} (попыток: {{ $lead->notify_attempts }})</p>
        @endif
        <form method="post" action="{{ route('admin.leads.renotify', $lead) }}">@csrf<button class="btn btn-ghost btn-sm">Отправить в Telegram ещё раз</button></form>
    </div>
    <form method="post" action="{{ route('admin.leads.update', $lead) }}" class="card">
        @csrf @method('PUT')
        <label>Статус</label>
        <select name="status">@foreach(\App\Models\Lead::STATUSES as $k=>$l)<option value="{{ $k }}" @selected($lead->status===$k)>{{ $l }}</option>@endforeach</select>
        <label style="margin-top:.75rem">Менеджер</label>
        <input name="manager" value="{{ $lead->manager }}">
        <label style="margin-top:.75rem">Заметка</label>
        <textarea name="note" rows="6">{{ $lead->note }}</textarea>
        <button class="btn btn-accent" style="margin-top:.75rem">Сохранить</button>
    </form>
</div>
@endsection
