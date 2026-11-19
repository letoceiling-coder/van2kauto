@extends('che168.layouts.app')
@section('title', 'Лоты A-Automarket')
@section('cabinet-logo')
    <a href="{{ route('automarket.dashboard') }}" class="logo">VAN<span>2</span>KAUTO · Auctions</a>
@endsection
@section('cabinet-nav')
    <a href="{{ route('automarket.dashboard') }}">Парсер</a>
    <a href="{{ route('automarket.lots.index') }}" class="active">Лоты</a>
    <a href="{{ route('automarket.session') }}">Вход</a>
    <a href="{{ route('automarket.proxies.index') }}">Прокси</a>
    <a href="{{ route('che168.dashboard') }}">Che168</a>
@endsection
@section('content')
<h1 style="margin-top:0">Лоты</h1>
<form method="get" class="card filters" style="margin:1rem 0">
    <div><label>Поиск</label><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Марка, модель, лот"></div>
    <div><label>Марка</label>
        <select name="maker_id">
            <option value="">Все</option>
            @foreach($makers as $maker)
                <option value="{{ $maker->maker_id }}" @selected((string) ($filters['maker_id'] ?? '') === (string) $maker->maker_id)>{{ $maker->name }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Аукцион</label><input name="auction" value="{{ $filters['auction'] ?? '' }}"></div>
    <div><label>Год от</label><input name="year_min" value="{{ $filters['year_min'] ?? '' }}"></div>
    <div><label>Год до</label><input name="year_max" value="{{ $filters['year_max'] ?? '' }}"></div>
    <div><label>Старт от, ¥</label><input name="price_min" value="{{ $filters['price_min'] ?? '' }}"></div>
    <div><label>Старт до, ¥</label><input name="price_max" value="{{ $filters['price_max'] ?? '' }}"></div>
    <div><label>Пробег от</label><input name="mileage_min" value="{{ $filters['mileage_min'] ?? '' }}"></div>
    <div><label>Пробег до</label><input name="mileage_max" value="{{ $filters['mileage_max'] ?? '' }}"></div>
    <div><label>Статус</label>
        <select name="status">
            <option value="">Все</option>
            @foreach(['active','inactive','absent'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-accent" type="submit">Фильтр</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th></th><th>Лот</th><th>Аукцион</th><th>Авто</th><th>Год</th><th>Пробег</th><th>Старт ¥</th><th>Оценка</th><th></th></tr></thead>
    <tbody>
    @forelse($lots as $item)
        <tr>
            <td>@if($item->photos->first())<img src="{{ $item->photos->first()->preview_url }}" width="72" height="54" style="width:72px;height:54px;object-fit:cover;border-radius:6px">@endif</td>
            <td>{{ $item->bid }}<div style="color:var(--muted)">#{{ $item->lot_id }}</div></td>
            <td>{{ $item->auction_name }}<div style="color:var(--muted)">{{ $item->auction_at?->format('d.m.Y H:i') }}</div></td>
            <td>{{ $item->maker_name }} {{ $item->model_name }}<div style="color:var(--muted)">{{ $item->grade }}</div></td>
            <td>{{ $item->year ?: '—' }}</td>
            <td>{{ $item->mileage_km ? number_format($item->mileage_km, 0, '.', ' ') : '—' }}</td>
            <td>{{ $item->start_price_jpy ? number_format($item->start_price_jpy, 0, '.', ' ') : '—' }}</td>
            <td><span class="badge badge-{{ $item->status }}">{{ $item->score ?: $item->status }}</span></td>
            <td><a href="{{ route('automarket.lots.show', $item) }}">Открыть</a></td>
        </tr>
    @empty
        <tr><td colspan="9">Лотов пока нет. Запустите парсер.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $lots->links('che168.pagination') }}
@endsection