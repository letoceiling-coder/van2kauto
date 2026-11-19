<?php $__env->startSection('title', 'API Che168'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between">
    <h1 style="margin:0">API каталога</h1>
    <button type="button" class="btn btn-accent" id="copy-docs">Скопировать документацию</button>
</div>
<p id="copy-state" style="min-height:1.25rem;color:var(--ok);margin:.5rem 0 0"></p>
<p style="color:var(--muted);max-width:720px">Базовый адрес <code><?php echo e($base); ?></code>. Чтение без авторизации. Ответ всегда JSON. Лимит 300 запросов в минуту с одного IP. Коды: 200, 404 если нет товара или модели, 429 если лимит.</p>
<p>
    <a href="<?php echo e($base); ?>">Оглавление JSON</a>
    · <a href="<?php echo e($base); ?>/openapi.json">OpenAPI</a>
</p>

<div class="card" style="margin:1rem 0">
    <h2 style="margin:0 0 .75rem;font-size:1rem">Пагинация</h2>
    <p style="margin:0;color:var(--muted)">Страница: <code>page=2&amp;limit=24</code>. Смещение: <code>offset=24&amp;limit=24</code>. Если передан <code>offset</code>, он важнее <code>page</code>. В <code>meta</code> есть <code>total</code>, <code>has_more</code> и <code>next_offset</code>.</p>
</div>

<?php $__currentLoopData = $endpoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $endpoint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <article class="card" id="<?php echo e(trim(str_replace(['/', '{', '}'], ['-', '', ''], $endpoint['path']), '-')); ?>" style="margin-bottom:1rem">
        <h2 style="margin:0 0 .35rem;font-size:1.05rem"><?php echo e($endpoint['title']); ?></h2>
        <p style="margin:.25rem 0 1rem"><code><?php echo e($endpoint['method']); ?> <?php echo e($endpoint['path']); ?></code></p>
        <p style="color:var(--muted)"><?php echo e($endpoint['summary']); ?></p>
        <?php if($endpoint['params']): ?>
            <table>
                <thead><tr><th>Параметр</th><th>Описание</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $endpoint['params']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $param): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><code><?php echo e($param['name']); ?></code></td><td><?php echo e($param['desc']); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
        <p style="margin:1rem 0 .35rem">Пример</p>
        <pre class="copy-block" style="white-space:pre-wrap;background:#0d1014;padding:.75rem;border-radius:.6rem;overflow:auto;cursor:pointer" title="Нажмите, чтобы скопировать">curl "<?php echo e($base); ?><?php echo e(\Illuminate\Support\Str::after($endpoint['example'], '/api/v1')); ?>"</pre>
        <p style="margin:1rem 0 .35rem">Ответ</p>
        <pre style="white-space:pre-wrap;background:#0d1014;padding:.75rem;border-radius:.6rem;overflow:auto"><?php echo e($endpoint['response']); ?></pre>
    </article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const state = document.getElementById('copy-state');
    const full = <?php echo json_encode($plain, 15, 512) ?>;

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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/api/docs.blade.php ENDPATH**/ ?>