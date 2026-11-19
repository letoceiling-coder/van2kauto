<?php $__env->startSection('title', 'Настройки'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0">Настройки</h1>
<?php ($labels = ['tg_text_welcome' => 'Приветствие (/start)', 'tg_text_welcome_car' => 'Приветствие при переходе с карточки ({car} — название)', 'tg_text_contact_button' => 'Кнопка «отправить номер»', 'tg_text_thanks' => 'Ответ после номера', 'tg_text_message_received' => 'Ответ на сообщение']); ?>
<form method="post" action="<?php echo e(route('admin.settings.update')); ?>">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<div class="card">
    <h3 style="margin-top:0">Telegram-бот</h3>
    <div class="form-grid">
        <div class="wide">
            <label>Токен бота от @BotFather <?php echo e($hasToken ? '(задан — оставьте пустым, чтобы не менять)' : ''); ?></label>
            <input name="tg_bot_token" type="password" autocomplete="off" placeholder="<?php echo e($hasToken ? '••••••••••' : '123456789:AA...'); ?>">
        </div>
        <div class="wide">
            <label>Чаты для заявок (chat_id через запятую). Добавьте бота в группу менеджеров и напишите в ней /id — бот ответит номером.</label>
            <input name="tg_manager_chat_ids" value="<?php echo e($s('tg_manager_chat_ids')); ?>" placeholder="-1001234567890, 123456789">
        </div>
        <?php $__currentLoopData = $textKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="wide"><label><?php echo e($labels[$key] ?? $key); ?></label><textarea name="<?php echo e($key); ?>" rows="<?php echo e($key === 'tg_text_contact_button' ? 1 : 3); ?>"><?php echo e($s($key)); ?></textarea></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Сайт и пересборка</h3>
    <div class="form-grid">
        <div><label>Адрес сайта</label><input name="site_url" value="<?php echo e($s('site_url')); ?>"></div>
        <div><label>Откуда принимать заявки (Origin через запятую, * — откуда угодно)</label><input name="leads_allowed_origins" value="<?php echo e($s('leads_allowed_origins')); ?>"></div>
        <div><label>Правило пересборки</label><select name="site_rebuild_mode"><?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($s('site_rebuild_mode')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Пауза после последнего изменения, мин</label><input name="site_rebuild_debounce_minutes" type="number" value="<?php echo e($s('site_rebuild_debounce_minutes')); ?>"></div>
        <div><label>Час ежедневной сборки (0–23)</label><input name="site_rebuild_daily_hour" type="number" value="<?php echo e($s('site_rebuild_daily_hour')); ?>"></div>
        <div class="wide"><label>Команда сборки на сервере</label><input name="site_rebuild_command" value="<?php echo e($s('site_rebuild_command')); ?>" placeholder="/opt/van2kauto/deploy/rebuild.sh"></div>
    </div>
    <p class="muted">Сборку запускает планировщик раз в минуту. «Автоматически» — через N минут тишины после последней правки, чтобы пачка изменений дала одну сборку. Данные витрины API отдаёт с ETag и кэшем, поэтому сборка их тянет быстро.</p>
</div>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Правило «проходной по утильсбору»</h3>
    <div class="form-grid">
        <div><label>Мощность до, л.с.</label><input name="utilization_max_hp" type="number" value="<?php echo e($s('utilization_max_hp')); ?>"></div>
        <div><label>Возраст от, лет</label><input name="utilization_min_age" type="number" value="<?php echo e($s('utilization_min_age')); ?>"></div>
        <div><label>Возраст до, лет</label><input name="utilization_max_age" type="number" value="<?php echo e($s('utilization_max_age')); ?>"></div>
    </div>
</div>
<button class="btn btn-accent" style="margin-top:1rem">Сохранить настройки</button>
</form>

<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Подключение бота</h3>
    <p class="muted">Бот: <?php echo e($s('tg_bot_username') ? '@'.$s('tg_bot_username') : 'не проверен'); ?> · Вебхук: <code><?php echo e($webhookUrl); ?></code></p>
    <?php if($webhook): ?>
        <?php if(isset($webhook['error'])): ?><p class="muted">Ошибка: <?php echo e($webhook['error']); ?></p>
        <?php else: ?><p class="muted">Telegram: <?php echo e(($webhook['url'] ?? '') ?: 'вебхук не установлен'); ?> · в очереди: <?php echo e($webhook['pending_update_count'] ?? 0); ?> <?php if(!empty($webhook['last_error_message'])): ?> · последняя ошибка: <?php echo e($webhook['last_error_message']); ?> <?php endif; ?></p><?php endif; ?>
    <?php endif; ?>
    <div class="row">
        <form method="post" action="<?php echo e(route('admin.settings.bot-test')); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost" <?php if(!$hasToken): echo 'disabled'; endif; ?>>Проверить бота и чаты</button></form>
        <form method="post" action="<?php echo e(route('admin.settings.webhook')); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost" <?php if(!$hasToken): echo 'disabled'; endif; ?>>Установить вебхук</button></form>
        <form method="post" action="<?php echo e(route('admin.settings.webhook.delete')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-ghost" <?php if(!$hasToken): echo 'disabled'; endif; ?>>Снять вебхук</button></form>
    </div>
</div>

<div class="card" style="margin-top:1rem">
    <div class="row" style="justify-content:space-between">
        <h3 style="margin:0">Сборки сайта</h3>
        <form method="post" action="<?php echo e(route('admin.settings.rebuild')); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost btn-sm">Пересобрать сейчас</button></form>
    </div>
    <p class="muted"><?php echo e($pending ? 'Есть изменения витрины, которых нет на сайте.' : 'Сайт соответствует витрине.'); ?></p>
    <table>
        <thead><tr><th>#</th><th>Запуск</th><th>Причина</th><th>Статус</th><th>Длительность</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $builds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($b->id); ?></td><td><?php echo e($b->started_at?->format('d.m H:i')); ?></td><td><?php echo e($b->trigger); ?></td><td><?php echo e($b->status); ?></td>
                <td><?php echo e($b->finished_at ? $b->started_at->diffInSeconds($b->finished_at, true).' с' : '…'); ?></td>
            </tr>
            <?php if($b->output && $loop->first): ?><tr><td colspan="5"><pre class="out"><?php echo e($b->output); ?></pre></td></tr><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5">Сборок ещё не было.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/settings.blade.php ENDPATH**/ ?>