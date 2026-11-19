@extends('che168.layouts.app')
@section('title', 'Настройки')
@section('content')
<h1 style="margin-top:0">Настройки</h1>
@php($labels = ['tg_text_welcome' => 'Приветствие (/start)', 'tg_text_welcome_car' => 'Приветствие при переходе с карточки ({car} — название)', 'tg_text_contact_button' => 'Кнопка «отправить номер»', 'tg_text_thanks' => 'Ответ после номера', 'tg_text_message_received' => 'Ответ на сообщение'])
<form method="post" action="{{ route('admin.settings.update') }}">
@csrf @method('PUT')
<div class="card">
    <h3 style="margin-top:0">Telegram-бот</h3>
    <div class="form-grid">
        <div class="wide">
            <label>Токен бота от @BotFather {{ $hasToken ? '(задан — оставьте пустым, чтобы не менять)' : '' }}</label>
            <input name="tg_bot_token" type="password" autocomplete="off" placeholder="{{ $hasToken ? '••••••••••' : '123456789:AA...' }}">
        </div>
        <div class="wide">
            <label>Чаты для заявок (chat_id через запятую). Добавьте бота в группу менеджеров и напишите в ней /id — бот ответит номером.</label>
            <input name="tg_manager_chat_ids" value="{{ $s('tg_manager_chat_ids') }}" placeholder="-1001234567890, 123456789">
        </div>
        @foreach($textKeys as $key)
            <div class="wide"><label>{{ $labels[$key] ?? $key }}</label><textarea name="{{ $key }}" rows="{{ $key === 'tg_text_contact_button' ? 1 : 3 }}">{{ $s($key) }}</textarea></div>
        @endforeach
    </div>
</div>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Сайт и пересборка</h3>
    <div class="form-grid">
        <div><label>Адрес сайта</label><input name="site_url" value="{{ $s('site_url') }}"></div>
        <div><label>Откуда принимать заявки (Origin через запятую, * — откуда угодно)</label><input name="leads_allowed_origins" value="{{ $s('leads_allowed_origins') }}"></div>
        <div><label>Правило пересборки</label><select name="site_rebuild_mode">@foreach($modes as $k=>$l)<option value="{{ $k }}" @selected($s('site_rebuild_mode')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Пауза после последнего изменения, мин</label><input name="site_rebuild_debounce_minutes" type="number" value="{{ $s('site_rebuild_debounce_minutes') }}"></div>
        <div><label>Час ежедневной сборки (0–23)</label><input name="site_rebuild_daily_hour" type="number" value="{{ $s('site_rebuild_daily_hour') }}"></div>
        <div class="wide"><label>Команда сборки на сервере</label><input name="site_rebuild_command" value="{{ $s('site_rebuild_command') }}" placeholder="/opt/van2kauto/deploy/rebuild.sh"></div>
    </div>
    <p class="muted">Сборку запускает планировщик раз в минуту. «Автоматически» — через N минут тишины после последней правки, чтобы пачка изменений дала одну сборку. Данные витрины API отдаёт с ETag и кэшем, поэтому сборка их тянет быстро.</p>
</div>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Правило «проходной по утильсбору»</h3>
    <div class="form-grid">
        <div><label>Мощность до, л.с.</label><input name="utilization_max_hp" type="number" value="{{ $s('utilization_max_hp') }}"></div>
        <div><label>Возраст от, лет</label><input name="utilization_min_age" type="number" value="{{ $s('utilization_min_age') }}"></div>
        <div><label>Возраст до, лет</label><input name="utilization_max_age" type="number" value="{{ $s('utilization_max_age') }}"></div>
    </div>
</div>
<button class="btn btn-accent" style="margin-top:1rem">Сохранить настройки</button>
</form>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Подключение бота</h3>
    <p class="muted">Бот: {{ $s('tg_bot_username') ? '@'.$s('tg_bot_username') : 'не проверен' }} · Вебхук: <code>{{ $webhookUrl }}</code></p>
    @if($webhook)
        @if(isset($webhook['error']))<p class="muted">Ошибка: {{ $webhook['error'] }}</p>
        @else<p class="muted">Telegram: {{ ($webhook['url'] ?? '') ?: 'вебхук не установлен' }} · в очереди: {{ $webhook['pending_update_count'] ?? 0 }} @if(!empty($webhook['last_error_message'])) · последняя ошибка: {{ $webhook['last_error_message'] }} @endif</p>@endif
    @endif
    <div class="row">
        <form method="post" action="{{ route('admin.settings.bot-test') }}">@csrf<button class="btn btn-ghost" @disabled(!$hasToken)>Проверить бота и чаты</button></form>
        <form method="post" action="{{ route('admin.settings.webhook') }}">@csrf<button class="btn btn-ghost" @disabled(!$hasToken)>Установить вебхук</button></form>
        <form method="post" action="{{ route('admin.settings.webhook.delete') }}">@csrf @method('DELETE')<button class="btn btn-ghost" @disabled(!$hasToken)>Снять вебхук</button></form>
    </div>
</div>

<div class="card" style="margin-top:1rem">
    <div class="row" style="justify-content:space-between">
        <h3 style="margin:0">Сборки сайта</h3>
        <form method="post" action="{{ route('admin.settings.rebuild') }}">@csrf<button class="btn btn-ghost btn-sm">Пересобрать сейчас</button></form>
    </div>
    <p class="muted">{{ $pending ? 'Есть изменения витрины, которых нет на сайте.' : 'Сайт соответствует витрине.' }}</p>
    <table>
        <thead><tr><th>#</th><th>Запуск</th><th>Причина</th><th>Статус</th><th>Длительность</th></tr></thead>
        <tbody>
        @forelse($builds as $b)
            <tr>
                <td>{{ $b->id }}</td><td>{{ $b->started_at?->format('d.m H:i') }}</td><td>{{ $b->trigger }}</td><td>{{ $b->status }}</td>
                <td>{{ $b->finished_at ? $b->started_at->diffInSeconds($b->finished_at, true).' с' : '…' }}</td>
            </tr>
            @if($b->output && $loop->first)<tr><td colspan="5"><pre class="out">{{ $b->output }}</pre></td></tr>@endif
        @empty
            <tr><td colspan="5">Сборок ещё не было.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
