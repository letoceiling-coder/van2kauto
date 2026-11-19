<?php $__env->startSection('title', 'Бот и рассылки'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0">Бот и рассылки</h1>
<div class="grid-3" style="margin-bottom:1rem">
    <div class="card stat"><h3><?php echo e($active); ?></h3><p>Подписчиков (нажали «Старт»)</p></div>
    <div class="card stat"><h3><?php echo e($blocked); ?></h3><p>Заблокировали бота</p></div>
    <div class="card stat"><h3><?php echo e($botUsername ? '@'.$botUsername : '—'); ?></h3><p>Бот · ссылки: t.me/<?php echo e($botUsername ?: 'bot'); ?>?start=site, ?start=car_&lt;slug&gt;</p></div>
</div>

<div class="detail-layout">
<div class="card">
    <h3 style="margin-top:0">Новая рассылка</h3>
    <p class="muted">Уходит всем подписчикам бота (не в канал). Сначала «Тест» — придёт в чат менеджеров. Поддерживается HTML: &lt;b&gt;, &lt;i&gt;, &lt;a href&gt;.</p>
    <form method="post" action="<?php echo e(route('admin.bot.broadcasts.store')); ?>">
        <?php echo csrf_field(); ?>
        <label>Текст</label><textarea name="text" rows="7" required><?php echo e(old('text')); ?></textarea>
        <div class="form-grid" style="margin-top:.75rem">
            <div class="wide"><label>Фото (ссылка, необязательно) — например фото карточки витрины</label><input name="photo_url" value="<?php echo e(old('photo_url')); ?>"></div>
            <div><label>Текст кнопки</label><input name="button_text" value="<?php echo e(old('button_text')); ?>" placeholder="Смотреть на сайте"></div>
            <div><label>Ссылка кнопки</label><input name="button_url" value="<?php echo e(old('button_url')); ?>" placeholder="<?php echo e($siteUrl); ?>/catalog/?utm_source=tg_bot"></div>
        </div>
        <div class="row" style="margin-top:.75rem">
            <button class="btn btn-ghost" name="mode" value="test">Тест менеджерам</button>
            <button class="btn btn-accent" name="mode" value="send" onclick="return confirm('Отправить всем <?php echo e($active); ?> подписчикам?')">Отправить всем</button>
        </div>
    </form>
</div>
<div class="card">
    <h3 style="margin-top:0">История</h3>
    <?php $__empty_1 = true; $__currentLoopData = $broadcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="border-top:1px solid var(--border);padding:.5rem 0">
            <div class="muted"><?php echo e($b->created_at->format('d.m H:i')); ?> · <?php echo e(\App\Models\TgBroadcast::STATUSES[$b->status] ?? $b->status); ?> · ✅ <?php echo e($b->sent_count); ?> · ⚠️ <?php echo e($b->failed_count); ?></div>
            <div><?php echo e(\Illuminate\Support\Str::limit(strip_tags($b->text), 120)); ?></div>
            <?php if(in_array($b->status, ['queued','sending'])): ?>
                <form method="post" action="<?php echo e(route('admin.bot.broadcasts.cancel', $b)); ?>"><?php echo csrf_field(); ?><button class="btn btn-danger btn-sm">Остановить</button></form>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="muted">Рассылок ещё не было.</p>
    <?php endif; ?>
</div>
</div>

<div class="card" style="margin-top:1rem;overflow-x:auto">
    <form method="get" class="row" style="margin-bottom:.75rem"><input name="q" value="<?php echo e(request('q')); ?>" placeholder="Имя, @username, метка" style="max-width:260px"><button class="btn btn-ghost">Найти</button></form>
    <table>
        <thead><tr><th>Пользователь</th><th>Телефон</th><th>Откуда (start)</th><th>Подписался</th><th>Был</th><th></th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $subscribers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($sub->displayName()); ?></td><td><?php echo e($sub->phone ?: '—'); ?></td><td><?php echo e($sub->start_param ?: '—'); ?></td>
                <td><?php echo e($sub->created_at->format('d.m.Y')); ?></td><td><?php echo e($sub->last_seen_at?->format('d.m H:i')); ?></td>
                <td><?php if($sub->is_blocked): ?><span class="badge badge-absent">заблокировал</span><?php endif; ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6">Подписчиков пока нет.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php echo e($subscribers->links('che168.pagination')); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/bot.blade.php ENDPATH**/ ?>