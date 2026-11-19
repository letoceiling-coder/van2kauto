<?php $__env->startSection('title', 'Заявки'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0">Заявки</h1>
<div class="grid-3" style="margin-bottom:1rem">
    <?php $__currentLoopData = \App\Models\Lead::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="card stat" href="<?php echo e(route('admin.leads.index', ['status' => $k])); ?>"><h3><?php echo e($counts[$k] ?? 0); ?></h3><p><?php echo e($l); ?></p></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php if($unsent): ?>
    <div class="alert alert-warn"><?php echo e($unsent); ?> заявок не ушли в Telegram (бот не настроен или сбой). Они повторяются автоматически каждые 5 минут — проверьте <a href="<?php echo e(route('admin.settings.show')); ?>">настройки бота</a>.</div>
<?php endif; ?>
<form method="get" class="row" style="margin-bottom:1rem">
    <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Телефон, имя, машина" style="max-width:260px">
    <select name="status" style="max-width:160px"><option value="">Все статусы</option><?php $__currentLoopData = \App\Models\Lead::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('status')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <select name="source" style="max-width:160px"><option value="">Все источники</option><?php $__currentLoopData = \App\Models\Lead::SOURCES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('source')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <button class="btn btn-ghost">Показать</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th>#</th><th>Когда</th><th>Источник</th><th>Контакт</th><th>Машина</th><th>UTM</th><th>Статус</th><th>TG</th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><a href="<?php echo e(route('admin.leads.show', $lead)); ?>"><?php echo e($lead->id); ?></a></td>
            <td><?php echo e($lead->created_at->format('d.m H:i')); ?></td>
            <td><?php echo e(\App\Models\Lead::SOURCES[$lead->source] ?? $lead->source); ?></td>
            <td><b><?php echo e($lead->contact); ?></b><div class="muted"><?php echo e($lead->name); ?> <?php echo e($lead->channel ? '· '.$lead->channel : ''); ?></div></td>
            <td><?php echo e($lead->car_title ?? '—'); ?></td>
            <td class="muted"><?php echo e(implode(' / ', array_filter((array) $lead->utm)) ?: '—'); ?></td>
            <td><span class="badge badge-<?php echo e($lead->status === 'new' ? 'active' : 'inactive'); ?>"><?php echo e(\App\Models\Lead::STATUSES[$lead->status] ?? $lead->status); ?></span><?php if($lead->manager): ?><div class="muted"><?php echo e($lead->manager); ?></div><?php endif; ?></td>
            <td><?php if($lead->notified_at): ?>✅<?php else: ?><span title="<?php echo e($lead->notify_error); ?>">⚠️</span><?php endif; ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8">Заявок пока нет.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($leads->links('che168.pagination')); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/leads/index.blade.php ENDPATH**/ ?>