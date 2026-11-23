@extends('che168.layouts.app')
@section('title', $car->exists ? 'Карточка: '.$car->title() : 'Новая карточка')
@section('content')
@php
    use App\Models\ShowcaseCar as SC;
    $v = fn ($k, $d = null) => old($k, $car->{$k} ?? $d);
    $util = old('utilization', $car->utilization_override === null ? 'auto' : ($car->utilization_override ? 'yes' : 'no'));
    $siteUrl = rtrim((string) \App\Support\Settings::get('site_url'), '/');
    $bot = \App\Support\Settings::get('tg_bot_username');
@endphp
<p><a href="{{ route('admin.showcase.index') }}">← Витрина</a></p>
<div class="row" style="justify-content:space-between">
    <h1 style="margin:0">{{ $car->exists ? $car->title().' '.$car->year : 'Новая карточка' }}</h1>
    @if($car->exists)
    <div class="row">
        @if($car->is_published)<a class="btn btn-ghost btn-sm" target="_blank" href="{{ $siteUrl }}/catalog/{{ $car->slug }}/">На сайте ↗</a>@endif
        <form method="post" action="{{ route('admin.showcase.duplicate', $car) }}">@csrf<button class="btn btn-ghost btn-sm">Создать копию</button></form>
        <form method="post" action="{{ route('admin.showcase.destroy', $car) }}" onsubmit="return confirm('Удалить карточку?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Удалить</button></form>
    </div>
    @endif
</div>

<div class="detail-layout" style="margin-top:1rem">
<div>
<form method="post" action="{{ $car->exists ? route('admin.showcase.update', $car) : route('admin.showcase.store') }}" class="card">
    @csrf
    @if($car->exists) @method('PUT') @endif
    <h3 style="margin-top:0">Модель</h3>
    <div class="form-grid">
        <div><label>Марка *</label><input name="brand" required value="{{ $v('brand') }}"></div>
        <div><label>Модель *</label><input name="model" required value="{{ $v('model') }}"></div>
        <div><label>Поколение</label><input name="generation" value="{{ $v('generation') }}"></div>
        <div><label>Год *</label><input name="year" type="number" required value="{{ $v('year') }}"></div>
        <div class="wide"><label>Комплектация (точное название с che168 — важно для «проходных»)</label><input name="trim" value="{{ $v('trim') }}"></div>
        <div><label>Страна</label><select name="country">@foreach(SC::COUNTRIES as $k=>$l)<option value="{{ $k }}" @selected($v('country','china')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Состояние</label><select name="condition">@foreach(SC::CONDITIONS as $k=>$l)<option value="{{ $k }}" @selected($v('condition','used')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Кузов</label><select name="body">@foreach(SC::BODIES as $k=>$l)<option value="{{ $k }}" @selected($v('body','crossover')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Статус</label><select name="status">@foreach(SC::STATUSES as $k=>$l)<option value="{{ $k }}" @selected($v('status','order')===$k)>{{ $l }}</option>@endforeach</select></div>
    </div>

    <h3>Характеристики</h3>
    <div class="form-grid">
        <div><label>Двигатель</label><select name="engine">@foreach(SC::ENGINES as $k=>$l)<option value="{{ $k }}" @selected($v('engine','petrol')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Объём, л</label><input name="displacement_l" type="number" step="0.1" value="{{ $v('displacement_l') }}"></div>
        <div><label>Мощность, л.с.</label><input name="power_hp" type="number" value="{{ $v('power_hp') }}"></div>
        <div><label>Подпись двигателя (если пусто — авто)</label><input name="engine_label" value="{{ $v('engine_label') }}" placeholder="Бензин, 1.5 л, 150 л.с."></div>
        <div><label>Привод</label><select name="drive">@foreach(SC::DRIVES as $k=>$l)<option value="{{ $k }}" @selected($v('drive','FWD')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>КПП</label><input name="gearbox" value="{{ $v('gearbox') }}" placeholder="Автомат"></div>
        <div><label>Руль</label><select name="steering">@foreach(SC::STEERING as $k=>$l)<option value="{{ $k }}" @selected($v('steering','left')===$k)>{{ $l }}</option>@endforeach</select></div>
        <div><label>Пробег, км</label><input name="mileage_km" type="number" value="{{ $v('mileage_km', 0) }}"></div>
        <div><label>Проходной по утильсбору</label>
            <select name="utilization">
                <option value="auto" @selected($util==='auto')>Авто (≤{{ \App\Support\Settings::int('utilization_max_hp',160) }} л.с., {{ \App\Support\Settings::int('utilization_min_age',3) }}–{{ \App\Support\Settings::int('utilization_max_age',5) }} лет)@if($car->exists) — сейчас: {{ $car->utilizationOk() === null ? '?' : ($car->utilizationOk() ? 'да' : 'нет') }}@endif</option>
                <option value="yes" @selected($util==='yes')>Да</option>
                <option value="no" @selected($util==='no')>Нет</option>
            </select>
        </div>
    </div>

    <h3>Продажа</h3>
    <div class="form-grid">
        <div><label>Цена «от», ₽ (обязательно для публикации)</label><input name="price_from_rub" type="number" step="1000" value="{{ $v('price_from_rub') }}">@if($car->source_infoid)<button class="btn btn-ghost btn-sm" style="margin-top:.35rem" formaction="{{ route('admin.showcase.reprice', $car) }}" formmethod="post" onclick="this.form.querySelector('[name=_method]')?.remove()">По формуле</button>@endif</div>
        <div><label>Срок доставки, дней от</label><input name="eta_from" type="number" value="{{ $v('eta_from', 30) }}"></div>
        <div><label>до</label><input name="eta_to" type="number" value="{{ $v('eta_to', 45) }}"></div>
        <div><label>Бейдж</label><input name="badge" value="{{ $v('badge') }}" placeholder="Хит продаж"></div>
        <div class="wide"><label>Короткое описание</label><textarea name="summary" rows="3">{{ $v('summary') }}</textarea></div>
        <div class="wide"><label>Преимущества — по одному в строке</label><textarea name="highlights_text" rows="4">{{ old('highlights_text', implode("\n", (array) $car->highlights)) }}</textarea></div>
    </div>

    <h3>Публикация</h3>
    <div class="form-grid">
        <div><label>Порядок (меньше — выше)</label><input name="sort_order" type="number" value="{{ $v('sort_order', 0) }}"></div>
        <div><label>Адрес (slug)</label><input name="slug" value="{{ $v('slug') }}" placeholder="авто из названия"></div>
        <div><label>Серия che168 (для совпадений)</label><input name="series_id" type="number" value="{{ $v('series_id') }}"></div>
        <div><label>Комплектация che168, spec_id</label><input name="spec_id" type="number" value="{{ $v('spec_id') }}"></div>
        <label class="row"><input type="checkbox" name="is_published" value="1" @checked($v('is_published'))> Опубликовать на сайте</label>
        <label class="row"><input type="checkbox" name="is_hit" value="1" @checked($v('is_hit'))> Показывать в хитах на главной</label>
    </div>
    <div class="row" style="margin-top:1rem"><button class="btn btn-accent">{{ $car->exists ? 'Сохранить' : 'Создать' }}</button></div>
</form>

@if($car->exists)
<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Фото ({{ $car->photos->count() }})</h3>
    <form method="post" action="{{ route('admin.showcase.photos.upload', $car) }}" enctype="multipart/form-data" class="row" style="margin-bottom:1rem">
        @csrf
        <input type="file" name="photos[]" multiple accept="image/*" style="max-width:360px">
        <button class="btn btn-ghost">Загрузить свои</button>
    </form>
    @if($car->photos->isNotEmpty())
    <form method="post" action="{{ route('admin.showcase.photos.update', $car) }}">
        @csrf @method('PUT')
        <div class="thumbs">
            @foreach($car->photos as $i => $photo)
            <figure>
                <img src="{{ $photo->url() }}" alt="">
                <label><span class="muted">#</span><input type="number" name="sort[{{ $photo->id }}]" value="{{ $photo->sort_order }}">@if($i===0)<span class="tag tag-ok">обложка</span>@endif</label>
                <label><input type="radio" name="cover" value="{{ $photo->id }}"> сделать обложкой</label>
                <label><input type="checkbox" name="delete[{{ $photo->id }}]" value="1"> удалить</label>
            </figure>
            @endforeach
        </div>
        <button class="btn btn-ghost" style="margin-top:.75rem">Сохранить фото</button>
    </form>
    @endif
</div>
@endif
</div>

<aside>
@if($car->exists && $match)
<div class="card">
    <h3 style="margin-top:0">Совпадения в фиде</h3>
    <p class="muted">{{ $match['query'] }}</p>
    <div class="facts" style="grid-template-columns:1fr 1fr">
        <div><span>Объявлений</span>{{ $match['total'] }}</div>
        <div><span>Та же комплектация</span>{{ $match['exact_spec'] }}</div>
        <div><span>Мин. цена</span>{{ $match['price_min'] ? '$'.number_format($match['price_min'], 0, '.', ' ') : '—' }}</div>
        <div><span>Медиана</span>{{ $match['price_median'] ? '$'.number_format($match['price_median'], 0, '.', ' ') : '—' }}</div>
    </div>
    @foreach($match['items'] as $item)
        <div style="border-top:1px solid var(--border);padding:.5rem 0">
            <a href="{{ route('che168.listings.show', $item) }}" target="_blank">{{ $item->title }}</a>
            <div class="muted">${{ number_format((float) $item->price_usd, 0, '.', ' ') }} · {{ number_format((int) $item->mileage_km, 0, '.', ' ') }} км · {{ $item->power_hp ? $item->power_hp.' л.с.' : '' }} @if($car->spec_id && $item->spec_id == $car->spec_id)<span class="tag tag-ok">комплектация</span>@endif</div>
            <form method="post" action="{{ route('admin.showcase.fill', $car) }}" class="row" style="margin-top:.3rem">
                @csrf <input type="hidden" name="infoid" value="{{ $item->infoid }}">
                <button class="btn btn-ghost btn-sm">Подставить характеристики</button>
                <button class="btn btn-ghost btn-sm" name="with_photos" value="1">+ фото</button>
            </form>
        </div>
    @endforeach
    @if($match['total'] === 0)<p class="muted">Не найдено. Укажите «Серия che168» или проверьте марку/модель как в фиде.</p>@endif
</div>
@endif
@if($car->exists)
<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Ссылки</h3>
    <p class="muted">Страница: {{ $siteUrl }}/catalog/{{ $car->slug }}/</p>
    @if($bot)<p class="muted">Бот с этой машиной: https://t.me/{{ $bot }}?start=car_{{ $car->slug }}</p>@endif
    @if($car->source_infoid)<p class="muted">Создано из объявления <a href="{{ route('che168.listings.show', $car->source_infoid) }}">#{{ $car->source_infoid }}</a></p>@endif
</div>
@endif
</aside>
</div>
@endsection
