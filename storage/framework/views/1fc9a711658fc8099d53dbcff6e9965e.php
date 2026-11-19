
<?php $__env->startSection('title', 'Парсер A-Automarket'); ?>
<?php $__env->startSection('cabinet-logo'); ?>
    <a href="<?php echo e(route('automarket.dashboard')); ?>" class="logo">VAN<span>2</span>KAUTO · Auctions</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('cabinet-nav'); ?>
    <a href="<?php echo e(route('automarket.dashboard')); ?>" class="<?php if(request()->routeIs('automarket.dashboard')): ?> active <?php endif; ?>">Парсер</a>
    <a href="<?php echo e(route('automarket.lots.index')); ?>" class="<?php if(request()->routeIs('automarket.lots.*')): ?> active <?php endif; ?>">Лоты</a>
    <a href="<?php echo e(route('automarket.session')); ?>" class="<?php if(request()->routeIs('automarket.session')): ?> active <?php endif; ?>">Вход</a>
    <a href="<?php echo e(route('automarket.proxies.index')); ?>" class="<?php if(request()->routeIs('automarket.proxies.*')): ?> active <?php endif; ?>">Прокси</a>
    <a href="<?php echo e(route('che168.dashboard')); ?>">Che168</a>
    <a href="<?php echo e(route('api.docs')); ?>">Документация</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0;font-size:1.5rem">Парсер A-Automarket</h1>
<?php if($errors->any()): ?>
    <div class="alert alert-warn"><?php echo e($errors->first()); ?></div>
<?php endif; ?>
<div class="grid-3" style="margin:1.25rem 0">
    <div class="card stat"><h3><?php echo e($stats['active']); ?></h3><p>Активных лотов</p></div>
    <div class="card stat"><h3><?php echo e($stats['absent']); ?></h3><p>Нет на аукционе</p></div>
    <div class="card stat"><h3><?php echo e($stats['total']); ?></h3><p>Всего в БД</p></div>
</div>
<div id="captcha-alert" class="alert alert-warn" style="display:none">
    <span id="captcha-text">Парсер остановлен.</span>
    <form method="post" action="<?php echo e(route('automarket.parse.start')); ?>" style="margin-top:.75rem">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="mode" value="full">
        <input type="hidden" name="resume" value="1">
        <button class="btn btn-accent" type="submit">Продолжить</button>
        <a class="btn btn-ghost" href="<?php echo e(route('automarket.session')); ?>">Войти в аукцион</a>
    </form>
</div>
<div class="card" style="margin-bottom:1rem">
    <h2 style="margin:0 0 1rem;font-size:1.1rem">Действия</h2>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem">
        <form method="post" action="<?php echo e(route('automarket.parse.start')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="mode" value="full">
            <button class="btn btn-accent" type="submit">Запуск парсера</button>
        </form>
        <form method="post" action="<?php echo e(route('automarket.parse.start')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="mode" value="new">
            <button class="btn btn-ghost" type="submit">Искать новые</button>
        </form>
        <a class="btn btn-ghost" href="<?php echo e(route('automarket.lots.index')); ?>">Лоты</a>
    </div>
    <p style="color:var(--muted);font-size:.8rem;margin:1rem 0 0">
        <strong>Запуск парсера</strong> входит в кабинет, обходит все марки и страницы, сохраняет карточку и фото. Снятые лоты получают статус <code>absent</code>.<br>
        <strong>Искать новые</strong> берёт свежие страницы и останавливается, когда лоты уже есть в базе.
    </p>
</div>
<div class="card">
    <h2 style="margin:0 0 .75rem;font-size:1.1rem">Онлайн-лог</h2>
    <p id="run-status" style="color:var(--muted);font-size:.875rem">—</p>
    <div id="run-log" class="log"></div>
</div>
<?php if($recentRuns->isNotEmpty()): ?>
<div class="card" style="margin-top:1rem">
    <h2 style="margin:0 0 .75rem;font-size:1.1rem">История</h2>
    <table>
        <thead><tr><th>#</th><th>Режим</th><th>Статус</th><th>Стр.</th><th>Сохранено</th><th>Время</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $recentRuns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($item->id); ?></td>
                <td><?php echo e($item->mode); ?></td>
                <td><?php echo e($item->status); ?></td>
                <td><?php echo e($item->pages_done); ?></td>
                <td><?php echo e($item->items_saved); ?></td>
                <td><?php echo e($item->created_at?->format('d.m H:i')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const statusEl = document.getElementById('run-status');
    const logEl = document.getElementById('run-log');
    const alertEl = document.getElementById('captcha-alert');
    const alertText = document.getElementById('captcha-text');
    let runId = <?php echo json_encode(session('run_id') ?? $activeRun?->id, 15, 512) ?>;

    function asRun(payload) {
        if (!payload || typeof payload !== 'object') return null;
        if (Object.prototype.hasOwnProperty.call(payload, 'run')) return payload.run;
        return payload.id != null ? payload : null;
    }
    function render(payload) {
        const run = asRun(payload);
        if (!run) { statusEl.textContent = 'Парсер не запущен'; return; }
        statusEl.textContent = `Задача #${run.id} · ${run.mode} · ${run.status} · страница ${run.pages_done ?? 0} · лотов ${run.items_saved ?? 0}`;
        alertEl.style.display = (run.status === 'needs_captcha' || run.status === 'needs_auth') ? 'block' : 'none';
        alertText.textContent = run.error_message || (run.status === 'needs_auth' ? 'Сессия истекла. Нажмите «Продолжить» — парсер войдёт снова.' : 'Нужна проверка на сайте.');
        if (Array.isArray(run.log) && run.log.length) {
            logEl.innerHTML = run.log.map(l => `<div>[${l.at}] ${l.msg}</div>`).join('');
            logEl.scrollTop = logEl.scrollHeight;
        }
        if (run.status === 'completed' || run.status === 'failed') runId = null;
    }
    async function poll() {
        const url = runId
            ? <?php echo json_encode(url('/a-automarket/parse/runs'), 15, 512) ?> + '/' + runId
            : <?php echo json_encode(route('automarket.parse.active'), 15, 512) ?>;
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (response.ok) render(await response.json());
        } catch (e) {}
    }
    poll();
    setInterval(poll, 2000);
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/automarket/dashboard.blade.php ENDPATH**/ ?>