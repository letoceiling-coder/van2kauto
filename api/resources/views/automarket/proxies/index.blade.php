@extends('che168.layouts.app')
@section('title', 'Прокси A-Automarket')
@section('cabinet-logo')
    <a href="{{ route('automarket.dashboard') }}" class="logo">ВАН<span>23</span>АВТО · Auctions</a>
@endsection
@section('cabinet-nav')
    <a href="{{ route('automarket.dashboard') }}">Парсер</a>
    <a href="{{ route('automarket.lots.index') }}">Лоты</a>
    <a href="{{ route('automarket.session') }}">Вход</a>
    <a href="{{ route('automarket.proxies.index') }}" class="active">Прокси</a>
    <a href="{{ route('che168.dashboard') }}">Che168</a>
@endsection
@section('content')
<h1 style="margin-top:0">Прокси</h1>
<p style="color:var(--muted)">Каждый запуск парсера и каждая попытка входа берут один случайный включённый прокси и держат его до конца сессии. Без включённых прокси запросы идут напрямую с сервера.</p>
@if($errors->any())
    <div class="alert alert-warn">{{ $errors->first() }}</div>
@endif
<form method="post" action="{{ route('automarket.proxies.store') }}" class="card filters" style="margin:1rem 0">
    @csrf
    <div>
        <label>Тип</label>
        <select name="scheme">
            @foreach(['http' => 'HTTP', 'https' => 'HTTPS', 'socks5' => 'SOCKS5'] as $value => $label)
                <option value="{{ $value }}" @selected(old('scheme', 'http') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Хост</label><input name="host" value="{{ old('host') }}" placeholder="1.2.3.4" required></div>
    <div><label>Порт</label><input name="port" value="{{ old('port') }}" inputmode="numeric" required></div>
    <div><label>Логин</label><input name="username" value="{{ old('username') }}"></div>
    <div><label>Пароль</label><input name="password" type="password"></div>
    <button class="btn btn-accent" type="submit">Добавить</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th>Прокси</th><th>Статус</th><th>Последний раз</th><th>Ошибка</th><th></th></tr></thead>
    <tbody>
    @forelse($proxies as $proxy)
        <tr>
            <td>{{ $proxy->label() }}@if($proxy->username)<div style="color:var(--muted)">{{ $proxy->username }}</div>@endif</td>
            <td>{{ $proxy->enabled ? 'включён' : 'выключен' }}</td>
            <td>{{ $proxy->last_used_at?->format('d.m H:i') ?: '—' }}</td>
            <td style="max-width:280px">{{ $proxy->last_error ?: '—' }}</td>
            <td style="white-space:nowrap">
                <form method="post" action="{{ route('automarket.proxies.toggle', $proxy) }}" style="display:inline">@csrf<button class="btn btn-ghost" type="submit">{{ $proxy->enabled ? 'Выключить' : 'Включить' }}</button></form>
                <form method="post" action="{{ route('automarket.proxies.destroy', $proxy) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Удалить</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">Прокси ещё нет. Парсер ходит напрямую с сервера.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection