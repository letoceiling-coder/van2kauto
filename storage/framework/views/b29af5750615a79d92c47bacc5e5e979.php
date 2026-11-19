<?php $__env->startSection('title', 'Заявка #'.$lead->id); ?>
<?php $__env->startSection('content'); ?>
<p><a href="<?php echo e(route('admin.leads.index')); ?>">← Заявки</a></p>
<h1 style="margin-top:0">Заявка #<?php echo e($lead->id); ?> <span class="muted"><?php echo e($lead->created_at->format('d.m.Y H:i')); ?></span></h1>
<div class="detail-layout">
    <div class="card">
        <div class="facts">
            <div><span>Источник</span><?php echo e(\App\Models\Lead::SOURCES[$lead->source] ?? $lead->source); ?></div>
            <div><span>Контакт</span><?php echo e($lead->contact); ?></div>
            <div><span>Имя</span><?php echo e($lead->name ?: '—'); ?></div>
            <div><span>Как связаться</span><?php echo e($lead->channel ?: '—'); ?></div>
            <div><span>Машина</span><?php if($lead->car): ?><a href="<?php echo e(route('admin.showcase.edit', $lead->car)); ?>"><?php echo e($lead->car_title); ?></a><?php else: ?><?php echo e($lead->car_title ?: '—'); ?><?php endif; ?></div>
            <div><span>Страница</span><?php echo e($lead->page ?: '—'); ?></div>
            <div><span>Metrika ClientID</span><?php echo e($lead->ym_client_id ?: '—'); ?></div>
            <div><span>IP</span><?php echo e($lead->ip ?: '—'); ?></div>
        </div>
        <?php if($lead->comment): ?><h3>Сообщение</h3><p style="white-space:pre-wrap"><?php echo e($lead->comment); ?></p><?php endif; ?>
        <?php if($lead->context): ?><h3>Ответы</h3><?php $__currentLoopData = $lead->context; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><span class="muted"><?php echo e($k); ?>:</span> <?php echo e($val); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
        <?php if($lead->utm): ?><h3>UTM</h3><?php $__currentLoopData = $lead->utm; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><span class="muted"><?php echo e($k); ?>:</span> <?php echo e($val); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
        <h3>Telegram</h3>
        <?php if($lead->notified_at): ?>
            <p class="muted">Отправлено <?php echo e($lead->notified_at->format('d.m H:i')); ?> в <?php echo e(count((array) $lead->tg_messages)); ?> чат(а)</p>
        <?php else: ?>
            <p class="muted">Не отправлено: <?php echo e($lead->notify_error ?: 'ещё не пробовали'); ?> (попыток: <?php echo e($lead->notify_attempts); ?>)</p>
        <?php endif; ?>
        <form method="post" action="<?php echo e(route('admin.leads.renotify', $lead)); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost btn-sm">Отправить в Telegram ещё раз</button></form>
    </div>
    <form method="post" action="<?php echo e(route('admin.leads.update', $lead)); ?>" class="card">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <label>Статус</label>
        <select name="status"><?php $__currentLoopData = \App\Models\Lead::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($lead->status===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        <label style="margin-top:.75rem">Менеджер</label>
        <input name="manager" value="<?php echo e($lead->manager); ?>">
        <label style="margin-top:.75rem">Заметка</label>
        <textarea name="note" rows="6"><?php echo e($lead->note); ?></textarea>
        <button class="btn btn-accent" style="margin-top:.75rem">Сохранить</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/leads/show.blade.php ENDPATH**/ ?>