<?php $__env->startSection('title', 'Витрина сайта'); ?>
<?php $__env->startSection('content'); ?>
<div class="row" style="justify-content:space-between">
    <h1 style="margin:0">Витрина сайта</h1>
    <div class="row">
        <a class="btn btn-ghost" href="<?php echo e(route('che168.listings.index', ['power_max' => \App\Support\Settings::int('utilization_max_hp', 160)])); ?>">Подобрать из фида</a>
        <a class="btn btn-accent" href="<?php echo e(route('admin.showcase.create')); ?>">+ Новая карточка</a>
    </div>
</div>

<div class="card" style="margin:1rem 0">
    <div class="row" style="justify-content:space-between">
        <div>
            <?php if($pending): ?>
                <span class="tag tag-warn">Есть изменения, которых ещё нет на сайте</span>
            <?php else: ?>
                <span class="tag tag-ok">Сайт актуален</span>
            <?php endif; ?>
            <?php if($lastBuild): ?>
                <span class="muted">Последняя сборка #<?php echo e($lastBuild->id); ?>: <?php echo e($lastBuild->status); ?> · <?php echo e($lastBuild->started_at?->format('d.m H:i')); ?></span>
            <?php endif; ?>
        </div>
        <form method="post" action="<?php echo e(route('admin.settings.rebuild')); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost btn-sm">Пересобрать сайт сейчас</button></form>
    </div>
</div>

<form method="get" class="row" style="margin-bottom:1rem">
    <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Марка или модель" style="max-width:240px">
    <select name="state" style="max-width:180px">
        <option value="">Все</option>
        <option value="published" <?php if(request('state')==='published'): echo 'selected'; endif; ?>>Опубликованные</option>
        <option value="draft" <?php if(request('state')==='draft'): echo 'selected'; endif; ?>>Черновики</option>
    </select>
    <button class="btn btn-ghost">Показать</button>
</form>

<form method="post" action="<?php echo e(route('admin.showcase.bulk')); ?>" class="card" style="overflow-x:auto">
    <?php echo csrf_field(); ?>
    <table>
        <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)"></th><th>Порядок</th><th></th><th>Карточка</th><th>Цена от</th><th>Утиль</th><th>В фиде</th><th>Статус</th><th></th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $cars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><input class="pick" type="checkbox" name="ids[]" value="<?php echo e($car->id); ?>"></td>
                <td><input type="number" name="sort[<?php echo e($car->id); ?>]" value="<?php echo e($car->sort_order); ?>" style="width:80px"></td>
                <td><?php if($car->photos->first()): ?><img src="<?php echo e($car->photos->first()->url()); ?>" style="width:84px;height:60px;object-fit:cover;border-radius:6px"><?php else: ?><span class="tag tag-warn">нет фото</span><?php endif; ?></td>
                <td>
                    <a href="<?php echo e(route('admin.showcase.edit', $car)); ?>"><b><?php echo e($car->title()); ?> <?php echo e($car->year); ?></b></a>
                    <?php if($car->is_hit): ?><span class="tag tag-ok">хит</span><?php endif; ?>
                    <div class="muted"><?php echo e($car->trim); ?> · <?php echo e(\App\Models\ShowcaseCar::COUNTRIES[$car->country] ?? $car->country); ?> · <?php echo e($car->power_hp ? $car->power_hp.' л.с.' : 'мощность ?'); ?></div>
                </td>
                <td><?php echo e($car->price_from_rub ? 'от '.number_format($car->price_from_rub, 0, '.', ' ').' ₽' : '—'); ?></td>
                <td><?php ($u = $car->utilizationOk()); ?> <?php if($u === true): ?><span class="tag tag-ok">проходной</span><?php elseif($u === false): ?><span class="tag">нет</span><?php else: ?><span class="muted">?</span><?php endif; ?></td>
                <td><?php echo e($matches[$car->id] ?? 0); ?></td>
                <td><?php if($car->is_published): ?><span class="badge badge-active">на сайте</span><?php else: ?><span class="badge badge-inactive">черновик</span><?php endif; ?></td>
                <td class="row">
                    <a class="btn btn-ghost btn-sm" href="<?php echo e(route('admin.showcase.edit', $car)); ?>">Изменить</a>
                    <button class="btn btn-ghost btn-sm" formaction="<?php echo e(route('admin.showcase.publish', $car)); ?>"><?php echo e($car->is_published ? 'Снять' : 'Опубликовать'); ?></button>
                    <button class="btn btn-ghost btn-sm" formaction="<?php echo e(route('admin.showcase.duplicate', $car)); ?>">Копия</button>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="9">Карточек пока нет. Создайте вручную или выберите объявления в разделе «Объявления» и нажмите «В витрину». Пока витрина пустая, на сайте показывается демо-каталог.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if($cars->isNotEmpty()): ?>
    <div class="row" style="margin-top:1rem">
        <button class="btn btn-accent" name="action" value="save_order">Сохранить порядок</button>
        <span class="muted">С выбранными:</span>
        <button class="btn btn-ghost btn-sm" name="action" value="publish">Опубликовать</button>
        <button class="btn btn-ghost btn-sm" name="action" value="unpublish">Снять</button>
        <button class="btn btn-ghost btn-sm" name="action" value="hit">В хиты</button>
        <button class="btn btn-ghost btn-sm" name="action" value="unhit">Убрать из хитов</button>
        <button class="btn btn-danger btn-sm" name="action" value="delete" onclick="return confirm('Удалить выбранные карточки вместе с фото?')">Удалить</button>
    </div>
    <p class="muted">Публикуются только карточки с ценой «от». Меньший номер порядка — выше на сайте.</p>
    <?php endif; ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/showcase/index.blade.php ENDPATH**/ ?>