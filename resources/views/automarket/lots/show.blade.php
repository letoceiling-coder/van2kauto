@extends('che168.layouts.app')
@section('title', trim(($lot->maker_name.' '.$lot->model_name)) ?: 'Лот')
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
<p><a href="{{ route('automarket.lots.index') }}">← К списку</a></p>
<div class="detail-layout">
    <div class="card">
        <h1 style="margin:0;font-size:1.35rem">{{ $lot->maker_name }} {{ $lot->model_name }}</h1>
        <p style="color:var(--muted)">Лот {{ $lot->bid }} · #{{ $lot->lot_id }} · {{ $lot->auction_name }} · {{ $lot->auction_at?->format('d.m.Y H:i') }}</p>
        <p style="font-size:1.5rem;font-weight:700;margin:.25rem 0">{{ $lot->start_price_jpy ? number_format($lot->start_price_jpy, 0, '.', ' ').' ¥' : '—' }}</p>
        @php $hero = $lot->photos->first(); @endphp
        @if($hero)<img class="hero-photo" id="hero" src="{{ $hero->large_url ?: $hero->preview_url }}" alt="">@endif
        @if($lot->photos->isNotEmpty())
            <div class="gallery">
                @foreach($lot->photos as $photo)
                    <button type="button" style="padding:0;border:0;background:transparent;cursor:pointer" onclick="document.getElementById('hero').src=this.querySelector('img').dataset.large">
                        <img src="{{ $photo->preview_url }}" alt="" data-large="{{ $photo->large_url ?: $photo->preview_url }}">
                    </button>
                @endforeach
            </div>
        @endif
        <div class="facts">
            @foreach([
                'Комплектация' => $lot->grade,
                'Год' => $lot->year,
                'Пробег, км' => $lot->mileage_km,
                'Объём, cc' => $lot->displacement_cc,
                'КПП' => $lot->transmission,
                'Цвет' => $lot->color,
                'Код модели' => $lot->model_type,
                'Оборудование' => $lot->equipment,
                'Оценка' => $lot->score,
                'Результат' => $lot->result,
                'Топливо' => $lot->fuel,
                'Кузов' => $lot->chassis,
                'Финиш, ¥' => $lot->end_price_jpy,
            ] as $label => $value)
                <div><span>{{ $label }}</span><strong>{{ ($value === null || $value === '') ? '—' : $value }}</strong></div>
            @endforeach
        </div>
        @if($lot->attributes)
            <div class="spec-block">
                <h3>Дополнительно</h3>
                <table>
                    @foreach($lot->attributes as $name => $value)
                        <tr><td style="color:var(--muted)">{{ $name }}</td><td>{{ $value }}</td></tr>
                    @endforeach
                </table>
            </div>
        @endif
        <p style="margin-top:1rem"><a href="https://auctions.a-automarket.com/auctions/?p=project/lot&id={{ $lot->lot_id }}&s" target="_blank">Открыть на A-Automarket</a></p>
    </div>
    <form method="post" action="{{ route('automarket.lots.update', $lot) }}" class="card">
        @csrf
        @method('PUT')
        <h2 style="margin:0 0 1rem;font-size:1rem">Редактирование</h2>
        <label>Статус</label>
        <select name="status">
            @foreach(['active','inactive','absent'] as $status)
                <option value="{{ $status }}" @selected($lot->status === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <label style="margin-top:.75rem">Комплектация</label>
        <input name="grade" value="{{ $lot->grade }}">
        <label style="margin-top:.75rem">Стартовая цена, ¥</label>
        <input name="start_price_jpy" type="number" value="{{ $lot->start_price_jpy }}">
        <button class="btn btn-accent" style="margin-top:1rem;width:100%" type="submit">Сохранить</button>
    </form>
</div>
@endsection