@extends('che168.layouts.app')
@section('title', 'Бот и рассылки')
@section('content')
<h1 style="margin-top:0">Бот и рассылки</h1>
<div class="grid-3" style="margin-bottom:1rem">
    <div class="card stat"><h3>{{ $active }}</h3><p>Подписчиков (нажали «Старт»)</p></div>
    <div class="card stat"><h3>{{ $blocked }}</h3><p>Заблокировали бота</p></div>
    <div class="card stat"><h3>{{ $botUsername ? '@'.$botUsername : '—' }}</h3><p>Бот · ссылки: t.me/{{ $botUsername ?: 'bot' }}?start=site, ?start=car_&lt;slug&gt;</p></div>
</div>

<div class="detail-layout">
<div class="card">
    <h3 style="margin-top:0">Новая рассылка</h3>
    <p class="muted">Уходит всем подписчикам бота (не в канал). Сначала «Тест» — придёт в чат менеджеров. Поддерживается HTML: &lt;b&gt;, &lt;i&gt;, &lt;a href&gt;.</p>
    <form method="post" action="{{ route('admin.bot.broadcasts.store') }}">
        @csrf
        <label>Текст</label><textarea name="text" rows="7" required>{{ old('text') }}</textarea>
        <div class="form-grid" style="margin-top:.75rem">
            <div class="wide"><label>Фото (ссылка, необязательно) — например фото карточки витрины</label><input name="photo_url" value="{{ old('photo_url') }}"></div>
            <div><label>Текст кнопки</label><input name="button_text" value="{{ old('button_text') }}" placeholder="Смотреть на сайте"></div>
            <div><label>Ссылка кнопки</label><input name="button_url" value="{{ old('button_url') }}" placeholder="{{ $siteUrl }}/catalog/?utm_source=tg_bot"></div>
        </div>
        <div class="row" style="margin-top:.75rem">
            <button class="btn btn-ghost" name="mode" value="test">Тест менеджерам</button>
            <button class="btn btn-accent" name="mode" value="send" onclick="return confirm('Отправить всем {{ $active }} подписчикам?')">Отправить всем</button>
        </div>
    </form>
</div>
<div class="card">
    <h3 style="margin-top:0">История</h3>
    @forelse($broadcasts as $b)
        <div style="border-top:1px solid var(--border);padding:.5rem 0">
            <div class="muted">{{ $b->created_at->format('d.m H:i') }} · {{ \App\Models\TgBroadcast::STATUSES[$b->status] ?? $b->status }} · ✅ {{ $b->sent_count }} · ⚠️ {{ $b->failed_count }}</div>
            <div>{{ \Illuminate\Support\Str::limit(strip_tags($b->text), 120) }}</div>
            @if(in_array($b->status, ['queued','sending']))
                <form method="post" action="{{ route('admin.bot.broadcasts.cancel', $b) }}">@csrf<button class="btn btn-danger btn-sm">Остановить</button></form>
            @endif
        </div>
    @empty
        <p class="muted">Рассылок ещё не было.</p>
    @endforelse
</div>
</div>

<div class="card" style="margin-top:1rem;overflow-x:auto">
    <form method="get" class="row" style="margin-bottom:.75rem"><input name="q" value="{{ request('q') }}" placeholder="Имя, @username, метка" style="max-width:260px"><button class="btn btn-ghost">Найти</button></form>
    <table>
        <thead><tr><th>Пользователь</th><th>Телефон</th><th>Откуда (start)</th><th>Подписался</th><th>Был</th><th></th></tr></thead>
        <tbody>
        @forelse($subscribers as $sub)
            <tr>
                <td>{{ $sub->displayName() }}</td><td>{{ $sub->phone ?: '—' }}</td><td>{{ $sub->start_param ?: '—' }}</td>
                <td>{{ $sub->created_at->format('d.m.Y') }}</td><td>{{ $sub->last_seen_at?->format('d.m H:i') }}</td>
                <td>@if($sub->is_blocked)<span class="badge badge-absent">заблокировал</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="6">Подписчиков пока нет.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $subscribers->links('che168.pagination') }}
</div>
@endsection
