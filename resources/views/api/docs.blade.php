@extends('che168.layouts.app')
@section('title', 'API Che168')
@section('content')
<div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between">
    <h1 style="margin:0">API каталога</h1>
    <button type="button" class="btn btn-accent" id="copy-docs">Скопировать документацию</button>
</div>
<p id="copy-state" style="min-height:1.25rem;color:var(--ok);margin:.5rem 0 0"></p>
<p style="color:var(--muted);max-width:720px">Базовый адрес <code>{{ $base }}</code>. Чтение без авторизации. Ответ всегда JSON. Лимит 300 запросов в минуту с одного IP. Коды: 200, 404 если нет товара или модели, 429 если лимит.</p>
<p>
    <a href="{{ $base }}">Оглавление JSON</a>
    · <a href="{{ $base }}/openapi.json">OpenAPI</a>
</p>

<div class="card" style="margin:1rem 0">
    <h2 style="margin:0 0 .75rem;font-size:1rem">Пагинация</h2>
    <p style="margin:0;color:var(--muted)">Страница: <code>page=2&amp;limit=24</code>. Смещение: <code>offset=24&amp;limit=24</code>. Если передан <code>offset</code>, он важнее <code>page</code>. В <code>meta</code> есть <code>total</code>, <code>has_more</code> и <code>next_offset</code>.</p>
</div>

@foreach($endpoints as $endpoint)
    <article class="card" id="{{ trim(str_replace(['/', '{', '}'], ['-', '', ''], $endpoint['path']), '-') }}" style="margin-bottom:1rem">
        <h2 style="margin:0 0 .35rem;font-size:1.05rem">{{ $endpoint['title'] }}</h2>
        <p style="margin:.25rem 0 1rem"><code>{{ $endpoint['method'] }} {{ $endpoint['path'] }}</code></p>
        <p style="color:var(--muted)">{{ $endpoint['summary'] }}</p>
        @if($endpoint['params'])
            <table>
                <thead><tr><th>Параметр</th><th>Описание</th></tr></thead>
                <tbody>
                @foreach($endpoint['params'] as $param)
                    <tr><td><code>{{ $param['name'] }}</code></td><td>{{ $param['desc'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif
        <p style="margin:1rem 0 .35rem">Пример</p>
        <pre class="copy-block" style="white-space:pre-wrap;background:#0d1014;padding:.75rem;border-radius:.6rem;overflow:auto;cursor:pointer" title="Нажмите, чтобы скопировать">curl "{{ $base }}{{ \Illuminate\Support\Str::after($endpoint['example'], '/api/v1') }}"</pre>
        <p style="margin:1rem 0 .35rem">Ответ</p>
        <pre style="white-space:pre-wrap;background:#0d1014;padding:.75rem;border-radius:.6rem;overflow:auto">{{ $endpoint['response'] }}</pre>
    </article>
@endforeach
@endsection
@push('scripts')
<script>
(function () {
    const state = document.getElementById('copy-state');
    const full = @json($plain);

    async function copy(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (e) {
            const area = document.createElement('textarea');
            area.value = text;
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            area.remove();
        }
        state.textContent = 'Скопировано';
        setTimeout(() => { state.textContent = ''; }, 1600);
    }

    document.getElementById('copy-docs').addEventListener('click', () => copy(full));
    document.querySelectorAll('.copy-block').forEach((node) => {
        node.addEventListener('click', () => copy(node.textContent.trim()));
    });
})();
</script>
@endpush