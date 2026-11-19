@extends('che168.layouts.app')
@section('title', $listing->title)
@section('content')
@php
    $photos = $listing->photos;
    $hero = $photos->first();
    $facts = [
        'Марка' => $listing->brand?->name,
        'Серия' => $listing->series?->name,
        'Комплектация' => $listing->spec?->name,
        'Год' => $listing->reg_date,
        'Выпуск' => $listing->produce_date?->format('Y-m-d'),
        'Пробег, км' => $listing->mileage_km,
        'Топливо' => $listing->fuel_name,
        'Двигатель' => $listing->engine,
        'КПП' => $listing->gearbox,
        'Привод' => $listing->driving_mode,
        'Руль' => $listing->steering,
        'Кузов' => $listing->body_type,
        'Двери' => $listing->doors,
        'Мест' => $listing->seats,
        'Класс' => $listing->class_level,
        'Цвет' => $listing->color,
        'VIN' => $listing->vin_masked,
        'Город' => $listing->city_slug,
        'Габариты' => $listing->dimension,
        'Масса, кг' => $listing->curb_weight_kg,
        'Экспорт, $' => $listing->export_fee_usd,
        'Порт, $' => $listing->port_fee_usd,
    ];
    $paramGroups = data_get($listing->spec, 'raw_param.paramtypeitems', []);
    $configGroups = data_get($listing->spec, 'raw_config.configtypeitems', []);
@endphp
<p><a href="{{ route('che168.listings.index') }}">← К списку</a></p>
<div class="detail-layout">
    <div class="card">
        <h1 style="margin:0;font-size:1.35rem">{{ $listing->title }}</h1>
        <p style="color:var(--muted)">#{{ $listing->infoid }} · {{ $listing->scraped_at?->format('d.m.Y H:i') }} · фото: {{ $photos->count() }}</p>
        <p style="font-size:1.6rem;font-weight:700;margin:.25rem 0">${{ $listing->price_usd ? number_format($listing->price_usd, 0, '.', ' ') : '—' }}</p>
        @if($hero)
            <img class="hero-photo" id="hero" src="{{ $hero->large_url ?: $hero->preview_url }}" alt="">
        @endif
        @if($photos->isNotEmpty())
            <div class="gallery">
                @foreach($photos as $ph)
                    <button type="button" style="padding:0;border:0;background:transparent;cursor:pointer" onclick="document.getElementById('hero').src=this.querySelector('img').dataset.large">
                        <img src="{{ $ph->preview_url }}" alt="" data-large="{{ $ph->large_url ?: $ph->original_url ?: $ph->preview_url }}">
                    </button>
                @endforeach
            </div>
            <p><a href="{{ $hero->original_url ?: $hero->large_url }}" target="_blank">Открыть текущее фото</a></p>
        @else
            <p style="color:var(--muted)">Фото появятся после полного парсера карточки.</p>
        @endif

        <div class="facts">
            @foreach($facts as $label => $value)
                <div><span>{{ $label }}</span><strong>{{ ($value === null || $value === '') ? '—' : $value }}</strong></div>
            @endforeach
        </div>

        @if($listing->report)
            <div class="spec-block">
                <h3>Отчёт</h3>
                <p>Страховых случаев: {{ $listing->report->claims_count ?? '—' }}, пробег в отчёте: {{ $listing->report->latest_mileage_km ?? '—' }} км</p>
                @if($listing->report->insurance_url)<p><a href="{{ $listing->report->insurance_url }}" target="_blank">Страховой отчёт</a></p>@endif
                @if($listing->report->maintenance_url)<p><a href="{{ $listing->report->maintenance_url }}" target="_blank">История обслуживания</a></p>@endif
            </div>
        @endif

        @foreach($paramGroups as $group)
            <div class="spec-block">
                <h3>{{ $group['name'] ?? $group['title'] ?? 'Характеристики' }}</h3>
                <table>
                    @foreach($group['paramitems'] ?? [] as $item)
                        <tr>
                            <td style="color:var(--muted)">{{ $item['name'] ?? '' }}</td>
                            <td>{{ is_scalar($item['value'] ?? null) ? $item['value'] : json_encode($item['value'] ?? null, JSON_UNESCAPED_UNICODE) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endforeach

        @foreach($configGroups as $group)
            <div class="spec-block">
                <h3>{{ $group['name'] ?? 'Комплектация' }}</h3>
                <table>
                    @foreach($group['configitems'] ?? [] as $item)
                        @php
                            $vals = collect($item['valueitems'] ?? [])->map(function ($v) {
                                if (is_string($v) || is_numeric($v)) return (string) $v;
                                return $v['value'] ?? $v['name'] ?? '';
                            })->filter()->implode(', ');
                        @endphp
                        <tr>
                            <td style="color:var(--muted)">{{ $item['name'] ?? '' }}</td>
                            <td>{{ $vals !== '' ? $vals : '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endforeach

        @if($listing->detail_url)<p style="margin-top:1rem"><a href="{{ $listing->detail_url }}" target="_blank">Открыть на Che168</a></p>@endif
    </div>
    <form method="post" action="{{ route('che168.listings.update', $listing) }}" class="card">
        @csrf
        @method('PUT')
        <h2 style="margin:0 0 1rem;font-size:1rem">Редактирование</h2>
        <label>Статус</label>
        <select name="status">
            @foreach(['active','inactive','absent'] as $s)
                <option value="{{ $s }}" @selected($listing->status === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <label style="margin-top:.75rem">Название</label>
        <input name="title" value="{{ $listing->title }}">
        <label style="margin-top:.75rem">Цена USD</label>
        <input name="price_usd" type="number" step="0.01" value="{{ $listing->price_usd }}">
        <button class="btn btn-accent" style="margin-top:1rem;width:100%" type="submit">Сохранить</button>
    </form>
</div>
@endsection