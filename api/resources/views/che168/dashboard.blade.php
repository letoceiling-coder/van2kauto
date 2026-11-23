@extends('che168.layouts.app')

@section('title', 'Парсер Che168')

@section('content')
<h1 style="margin-top:0;font-size:1.5rem">Управление парсером</h1>
@if($errors->any())
    <div class="alert alert-warn">{{ $errors->first() }}</div>
@endif

<div class="grid-3" style="margin:1.25rem 0">
    <div class="card stat"><h3>{{ $stats['listings_active'] }}</h3><p>Активных</p></div>
    <div class="card stat"><h3>{{ $stats['listings_absent'] }}</h3><p>Отсутствует на Che168</p></div>
    <div class="card stat"><h3>{{ $stats['listings_total'] }}</h3><p>Всего в БД</p></div>
</div>

<div id="captcha-alert" class="alert alert-warn" style="display:none">
    Нужна проверка EdgeOne.
    <a href="{{ route('che168.gate.public') }}?force=captcha" target="_blank"><strong>Открыть captcha</strong></a>
    — после прохождения парсер продолжит с той же страницы.
    <form method="post" action="{{ route('che168.parse.start') }}" style="margin-top:.75rem">
        @csrf
        <input type="hidden" name="mode" value="full">
        <input type="hidden" name="resume" value="1">
        <button class="btn btn-accent" type="submit">Продолжить</button>
    </form>
</div>

<div class="card" style="margin-bottom:1rem">
    <h2 style="margin:0 0 1rem;font-size:1.1rem">Действия</h2>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        <form method="post" action="{{ route('che168.parse.start') }}">@csrf<input type="hidden" name="mode" value="full">
            <button class="btn btn-accent" type="submit">Запуск парсера</button>
        </form>
        <form method="post" action="{{ route('che168.parse.start') }}">@csrf<input type="hidden" name="mode" value="new">
            <button class="btn btn-ghost" type="submit">Искать новые</button>
        </form>
        <a class="btn btn-ghost" href="{{ route('che168.listings.index') }}">Каталог</a>
    </div>
    <p style="color:var(--muted);font-size:.8rem;margin:1rem 0 0">
        <strong>Запуск парсера</strong> — весь каталог донора: все страницы, каждое объявление с характеристиками, комплектацией и всеми фото. Снятые с сайта → <code>absent</code>.<br>
        <strong>Искать новые</strong> — только первые страницы, пока не встретятся уже сохранённые.
    </p>
</div>

<div class="card">
    <h2 style="margin:0 0 .75rem;font-size:1.1rem">Онлайн-лог</h2>
    <p id="run-status" style="color:var(--muted);font-size:.875rem">—</p>
    <div id="run-log" class="log"></div>
</div>

@if($recentRuns->isNotEmpty())
<div class="card" style="margin-top:1rem">
    <h2 style="margin:0 0 .75rem;font-size:1.1rem">История</h2>
    <table>
        <thead><tr><th>#</th><th>Режим</th><th>Статус</th><th>Стр.</th><th>Сохранено</th><th>Время</th></tr></thead>
        <tbody>
        @foreach($recentRuns as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->mode }}</td>
                <td>{{ $r->status }}</td>
                <td>{{ $r->pages_done }}</td>
                <td>{{ $r->items_saved }}</td>
                <td>{{ $r->created_at?->format('d.m H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const statusEl = document.getElementById('run-status');
    const logEl = document.getElementById('run-log');
    const captchaEl = document.getElementById('captcha-alert');
  let runId = @json(session('run_id') ?? $activeRun?->id);

    function asRun(payload) {
        if (!payload || typeof payload !== 'object') return null;
        if (Object.prototype.hasOwnProperty.call(payload, 'run')) return payload.run;
        return payload.id != null ? payload : null;
    }

    function render(payload) {
        const run = asRun(payload);
        if (!run) {
            statusEl.textContent = 'Парсер не запущен';
            return;
        }
        statusEl.textContent = `Задача #${run.id} · ${run.mode} · ${run.status} · страниц ${run.pages_done ?? 0} · карточек ${run.items_saved ?? 0}`;
        captchaEl.style.display = run.status === 'needs_captcha' ? 'block' : 'none';
        if (Array.isArray(run.log) && run.log.length) {
            logEl.innerHTML = run.log.map(l => `<div>[${l.at}] ${l.msg}</div>`).join('');
            logEl.scrollTop = logEl.scrollHeight;
        }
        if (run.status === 'completed' || run.status === 'failed') runId = null;
    }

    async function poll() {
        const url = runId
            ? @json(url('/che168/parse/runs')) + '/' + runId
            : @json(route('che168.parse.active'));
        try {
            const r = await fetch(url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!r.ok) return;
            render(await r.json());
        } catch (e) {}
    }
    poll();
    setInterval(poll, 2000);
})();
</script>
@endpush