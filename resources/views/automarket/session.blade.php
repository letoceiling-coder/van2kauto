@extends('che168.layouts.app')
@section('title', 'Вход в A-Automarket')
@section('cabinet-logo')
    <a href="{{ route('automarket.dashboard') }}" class="logo">VAN<span>2</span>KAUTO · Auctions</a>
@endsection
@section('cabinet-nav')
    <a href="{{ route('automarket.dashboard') }}">Парсер</a>
    <a href="{{ route('automarket.lots.index') }}">Лоты</a>
    <a href="{{ route('automarket.session') }}" class="active">Вход</a>
    <a href="{{ route('automarket.proxies.index') }}">Прокси</a>
    <a href="{{ route('che168.dashboard') }}">Che168</a>
@endsection
@section('content')
<h1 style="margin-top:0">Вход в A-Automarket</h1>
<p style="color:var(--muted)">Логин и пароль сохраняются только на сервере. Эта попытка идёт через {{ $proxy ? $proxy->label() : 'прямое соединение' }}.</p>
@if($errors->any())
    <div class="alert alert-warn">{{ $errors->first() }}</div>
@endif
@if($state === 'authenticated')
    <div class="alert alert-ok">Сессия активна. Парсер может читать каталог.</div>
@elseif($state === 'blocked')
    <div class="alert alert-warn">Аукцион ограничил доступ этому аккаунту с IP сервера. Сайт пишет: Access is limited, для доступа нужно связаться с ichirkov@gmail.com. Можно ввести другой логин.</div>
@elseif($state === 'captcha')
    <div class="alert alert-warn">Сайт запросил captcha. Введите логин ещё раз после проверки.</div>
@elseif($state === 'error')
    <div class="alert alert-warn">Сервер сейчас не открывает auctions.a-automarket.com.</div>
@else
    <div class="alert alert-warn">Сессия не активна. Войдите, чтобы парсер продолжил работу.</div>
@endif
<form method="post" action="{{ route('automarket.session.store') }}" class="card" style="max-width:420px">
    @csrf
    @if($proxy)<input type="hidden" name="proxy_id" value="{{ $proxy->id }}">@endif
    <label>Логин</label>
    <input name="username" value="{{ old('username', $username) }}" autocomplete="username" required>
    <label style="margin-top:.75rem">Пароль</label>
    <input name="password" type="password" autocomplete="current-password" required>
    <button class="btn btn-accent" style="margin-top:1rem" type="submit">Войти</button>
    <a class="btn btn-ghost" href="{{ route('automarket.dashboard') }}">К парсеру</a>
</form>
@endsection