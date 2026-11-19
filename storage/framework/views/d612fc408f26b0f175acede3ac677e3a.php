
<?php $__env->startSection('title', 'Лоты A-Automarket'); ?>
<?php $__env->startSection('cabinet-logo'); ?>
    <a href="<?php echo e(route('automarket.dashboard')); ?>" class="logo">VAN<span>2</span>KAUTO · Auctions</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('cabinet-nav'); ?>
    <a href="<?php echo e(route('automarket.dashboard')); ?>">Парсер</a>
    <a href="<?php echo e(route('automarket.lots.index')); ?>" class="active">Лоты</a>
    <a href="<?php echo e(route('automarket.session')); ?>">Вход</a>
    <a href="<?php echo e(route('automarket.proxies.index')); ?>">Прокси</a>
    <a href="<?php echo e(route('che168.dashboard')); ?>">Che168</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0">Лоты</h1>
<form method="get" class="card filters" style="margin:1rem 0">
    <div><label>Поиск</label><input name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Марка, модель, лот"></div>
    <div><label>Марка</label>
        <select name="maker_id">
            <option value="">Все</option>
            <?php $__currentLoopData = $makers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($maker->maker_id); ?>" <?php if((string) ($filters['maker_id'] ?? '') === (string) $maker->maker_id): echo 'selected'; endif; ?>><?php echo e($maker->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Аукцион</label><input name="auction" value="<?php echo e($filters['auction'] ?? ''); ?>"></div>
    <div><label>Год от</label><input name="year_min" value="<?php echo e($filters['year_min'] ?? ''); ?>"></div>
    <div><label>Год до</label><input name="year_max" value="<?php echo e($filters['year_max'] ?? ''); ?>"></div>
    <div><label>Старт от, ¥</label><input name="price_min" value="<?php echo e($filters['price_min'] ?? ''); ?>"></div>
    <div><label>Старт до, ¥</label><input name="price_max" value="<?php echo e($filters['price_max'] ?? ''); ?>"></div>
    <div><label>Пробег от</label><input name="mileage_min" value="<?php echo e($filters['mileage_min'] ?? ''); ?>"></div>
    <div><label>Пробег до</label><input name="mileage_max" value="<?php echo e($filters['mileage_max'] ?? ''); ?>"></div>
    <div><label>Статус</label>
        <select name="status">
            <option value="">Все</option>
            <?php $__currentLoopData = ['active','inactive','absent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? '') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <button class="btn btn-accent" type="submit">Фильтр</button>
</form>
<div class="card" style="overflow-x:auto">
<table>
    <thead><tr><th></th><th>Лот</th><th>Аукцион</th><th>Авто</th><th>Год</th><th>Пробег</th><th>Старт ¥</th><th>Оценка</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $lots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php if($item->photos->first()): ?><img src="<?php echo e($item->photos->first()->preview_url); ?>" width="72" height="54" style="width:72px;height:54px;object-fit:cover;border-radius:6px"><?php endif; ?></td>
            <td><?php echo e($item->bid); ?><div style="color:var(--muted)">#<?php echo e($item->lot_id); ?></div></td>
            <td><?php echo e($item->auction_name); ?><div style="color:var(--muted)"><?php echo e($item->auction_at?->format('d.m.Y H:i')); ?></div></td>
            <td><?php echo e($item->maker_name); ?> <?php echo e($item->model_name); ?><div style="color:var(--muted)"><?php echo e($item->grade); ?></div></td>
            <td><?php echo e($item->year ?: '—'); ?></td>
            <td><?php echo e($item->mileage_km ? number_format($item->mileage_km, 0, '.', ' ') : '—'); ?></td>
            <td><?php echo e($item->start_price_jpy ? number_format($item->start_price_jpy, 0, '.', ' ') : '—'); ?></td>
            <td><span class="badge badge-<?php echo e($item->status); ?>"><?php echo e($item->score ?: $item->status); ?></span></td>
            <td><a href="<?php echo e(route('automarket.lots.show', $item)); ?>">Открыть</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="9">Лотов пока нет. Запустите парсер.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($lots->links('che168.pagination')); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/automarket/lots/index.blade.php ENDPATH**/ ?>