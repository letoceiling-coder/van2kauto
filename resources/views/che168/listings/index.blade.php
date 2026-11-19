@extends('che168.layouts.app')
@section('title', 'Объявления')
@section('content')
<h1 style="margin-top:0">Объявления</h1>
<form method="get" class="card filters" style="margin:1rem 0">
    <div><label>Поиск</label><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Название или ID"></div>
    <div><label>Марка</label>
        <select name="brand_id" onchange="this.form.submit()">
            <option value="">Все</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->brand_id }}" @selected((string) ($filters['brand_id'] ?? '') === (string) $brand->brand_id)>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Серия</label>
        <select name="series_id">
            <option value="">{{ $series->isEmpty() ? 'Сначала марка' : 'Все' }}</option>
            @foreach($series as $item)
                <option value="{{ $item->series_id }}" @selected((string) ($filters['series_id'] ?? '') === (string) $item->series_id)>{{ $item->name }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Топливо</label>
        <select name="fuel">
            <option value="">Все</option>
            @foreach($fuels as $fuel)
                <option value="{{ $fuel }}" @selected(($filters['fuel'] ?? '') === $fuel)>{{ $fuel }}</option>
            @endforeach
        </select>
    </div>
    <div><label>КПП</label>
        <select name="gearbox">
            <option value="">Все</option>
            @foreach($gearboxes as $gear)
                <option value="{{ $gear }}" @selected(($filters['gearbox'] ?? '') === $gear)>{{ $gear }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Кузов</label>
        <select name="body">
            <option value="">Все</option>
            @foreach($bodies as $body)
                <option value="{{ $body }}" @selected(($filters['body'] ?? '') === $body)>{{ $body }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Цвет</label>
        <select name="color">
            <option value="">Все</option>
            @foreach($colors as $color)
                <option value="{{ $color }}" @selected(($filters['color'] ?? '') === $color)>{{ $color }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Год от</label><input name="year_min" inputmode="numeric" value="{{ $filters['year_min'] ?? '' }}"></div>
    <div><label>Год до</label><input name="year_max" inputmode="numeric" value="{{ $filters['year_max'] ?? '' }}"></div>
    <div><label>Цена от, $</label><input name="price_min" type="number" step="100" value="{{ $filters['price_min'] ?? '' }}"></div>
    <div><label>Цена до, $</label><input name="price_max" type="number" step="100" value="{{ $filters['price_max'] ?? '' }}"></div>
    <div><label>Пробег от, км</label><input name="mileage_min" type="number" step="1000" value="{{ $filters['mileage_min'] ?? '' }}"></div>
    <div><label>Пробег до, км</label><input name="mileage_max" type="number" step="1000" value="{{ $filters['mileage_max'] ?? '' }}"></div>
    <div><label>Статус</label>
        <select name="status">
            <option value="">Все</option>
            @foreach(['active','inactive','absent'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-accent" type="submit">Фильтр</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th></th><th>ID</th><th>Название</th><th>Марка</th><th>Год</th><th>Пробег</th><th>Цена $</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    @forelse($listings as $item)
        <tr>
            <td>@if($item->photos->first())<img src="{{ $item->photos->first()->preview_url }}" width="72" height="54" style="object-fit:cover;border-radius:6px;width:72px;height:54px">@endif</td>
            <td>{{ $item->infoid }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->brand?->name ?? '—' }}</td>
            <td>{{ $item->reg_date ? substr((string) $item->reg_date, 0, 4) : '—' }}</td>
            <td>{{ $item->mileage_km ? number_format($item->mileage_km, 0, '.', ' ') : '—' }}</td>
            <td>{{ $item->price_usd ? number_format($item->price_usd, 0, '.', ' ') : '—' }}</td>
            <td><span class="badge badge-{{ $item->status }}">{{ $item->status }}</span></td>
            <td><a href="{{ route('che168.listings.show', $item) }}">Открыть</a></td>
        </tr>
    @empty
        <tr><td colspan="9">Нет объявлений по этим фильтрам. Запустите полный парсер — марка и характеристики появятся после карточек.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $listings->links('che168.pagination') }}
@endsection