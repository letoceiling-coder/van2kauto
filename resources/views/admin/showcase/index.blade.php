@extends('che168.layouts.app')
@section('title', 'Витрина сайта')
@section('content')
<div class="row" style="justify-content:space-between">
    <h1 style="margin:0">Витрина сайта</h1>
    <div class="row">
        <form method="post" action="{{ route('admin.showcase.top') }}" class="row" onsubmit="return confirm('Собрать топ-10 популярных проходных моделей из фида? Уже добавленные модели не дублируются.')">
            @csrf
            <input type="hidden" name="limit" value="10">
            <label class="row muted" style="margin:0"><input type="checkbox" name="publish" value="1"> сразу на сайт</label>
            <button class="btn btn-ghost">Топ-10 из фида</button>
        </form>
        <a class="btn btn-ghost" href="{{ route('che168.listings.index', ['power_max' => \App\Support\Settings::int('utilization_max_hp', 160)]) }}">Подобрать из фида</a>
        <a class="btn btn-accent" href="{{ route('admin.showcase.create') }}">+ Новая карточка</a>
    </div>
</div>

<div class="card" style="margin:1rem 0">
    <div class="row" style="justify-content:space-between">
        <div>
            @if($pending)
                <span class="tag tag-warn">Есть изменения, которых ещё нет на сайте</span>
            @else
                <span class="tag tag-ok">Сайт актуален</span>
            @endif
            @if($lastBuild)
                <span class="muted">Последняя сборка #{{ $lastBuild->id }}: {{ $lastBuild->status }} · {{ $lastBuild->started_at?->format('d.m H:i') }}</span>
            @endif
        </div>
        <form method="post" action="{{ route('admin.settings.rebuild') }}">@csrf<button class="btn btn-ghost btn-sm">Пересобрать сайт сейчас</button></form>
    </div>
</div>

<form method="get" class="row" style="margin-bottom:1rem">
    <input name="q" value="{{ request('q') }}" placeholder="Марка или модель" style="max-width:240px">
    <select name="state" style="max-width:180px">
        <option value="">Все</option>
        <option value="published" @selected(request('state')==='published')>Опубликованные</option>
        <option value="draft" @selected(request('state')==='draft')>Черновики</option>
    </select>
    <button class="btn btn-ghost">Показать</button>
</form>

<form method="post" action="{{ route('admin.showcase.bulk') }}" class="card" style="overflow-x:auto">
    @csrf
    <table>
        <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)"></th><th>Порядок</th><th></th><th>Карточка</th><th>Цена от</th><th>Утиль</th><th>В фиде</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        @forelse($cars as $car)
            <tr>
                <td><input class="pick" type="checkbox" name="ids[]" value="{{ $car->id }}"></td>
                <td><input type="number" name="sort[{{ $car->id }}]" value="{{ $car->sort_order }}" style="width:80px"></td>
                <td>@if($car->photos->first())<img src="{{ $car->photos->first()->url() }}" style="width:84px;height:60px;object-fit:cover;border-radius:6px">@else<span class="tag tag-warn">нет фото</span>@endif</td>
                <td>
                    <a href="{{ route('admin.showcase.edit', $car) }}"><b>{{ $car->title() }} {{ $car->year }}</b></a>
                    @if($car->is_hit)<span class="tag tag-ok">хит</span>@endif
                    <div class="muted">{{ $car->trim }} · {{ \App\Models\ShowcaseCar::COUNTRIES[$car->country] ?? $car->country }} · {{ $car->power_hp ? $car->power_hp.' л.с.' : 'мощность ?' }}</div>
                </td>
                <td>{{ $car->price_from_rub ? 'от '.number_format($car->price_from_rub, 0, '.', ' ').' ₽' : '—' }}</td>
                <td>@php($u = $car->utilizationOk()) @if($u === true)<span class="tag tag-ok">проходной</span>@elseif($u === false)<span class="tag">нет</span>@else<span class="muted">?</span>@endif</td>
                <td>{{ $matches[$car->id] ?? 0 }}</td>
                <td>@if($car->is_published)<span class="badge badge-active">на сайте</span>@else<span class="badge badge-inactive">черновик</span>@endif</td>
                <td class="row">
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.showcase.edit', $car) }}">Изменить</a>
                    <button class="btn btn-ghost btn-sm" formaction="{{ route('admin.showcase.publish', $car) }}">{{ $car->is_published ? 'Снять' : 'Опубликовать' }}</button>
                    <button class="btn btn-ghost btn-sm" formaction="{{ route('admin.showcase.duplicate', $car) }}">Копия</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">Карточек пока нет. Создайте вручную или выберите объявления в разделе «Объявления» и нажмите «В витрину». Пока витрина пустая, на сайте показывается демо-каталог.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($cars->isNotEmpty())
    <div class="row" style="margin-top:1rem">
        <button class="btn btn-accent" name="action" value="save_order">Сохранить порядок</button>
        <span class="muted">С выбранными:</span>
        <button class="btn btn-ghost btn-sm" name="action" value="publish">Опубликовать</button>
        <button class="btn btn-ghost btn-sm" name="action" value="unpublish">Снять</button>
        <button class="btn btn-ghost btn-sm" name="action" value="hit">В хиты</button>
        <button class="btn btn-ghost btn-sm" name="action" value="unhit">Убрать из хитов</button>
        <button class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm('Удалить выбранные карточки вместе с фото?')">Удалить</button>
    </div>
    <p class="muted">Публикуются только карточки с ценой «от». Меньший номер порядка — выше на сайте.</p>
    @endif
</form>
@endsection
