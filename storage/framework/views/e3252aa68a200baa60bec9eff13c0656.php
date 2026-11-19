
<?php $__env->startSection('title', trim(($lot->maker_name.' '.$lot->model_name)) ?: 'Лот'); ?>
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
<p><a href="<?php echo e(route('automarket.lots.index')); ?>">← К списку</a></p>
<div class="detail-layout">
    <div class="card">
        <h1 style="margin:0;font-size:1.35rem"><?php echo e($lot->maker_name); ?> <?php echo e($lot->model_name); ?></h1>
        <p style="color:var(--muted)">Лот <?php echo e($lot->bid); ?> · #<?php echo e($lot->lot_id); ?> · <?php echo e($lot->auction_name); ?> · <?php echo e($lot->auction_at?->format('d.m.Y H:i')); ?></p>
        <p style="font-size:1.5rem;font-weight:700;margin:.25rem 0"><?php echo e($lot->start_price_jpy ? number_format($lot->start_price_jpy, 0, '.', ' ').' ¥' : '—'); ?></p>
        <?php $hero = $lot->photos->first(); ?>
        <?php if($hero): ?><img class="hero-photo" id="hero" src="<?php echo e($hero->large_url ?: $hero->preview_url); ?>" alt=""><?php endif; ?>
        <?php if($lot->photos->isNotEmpty()): ?>
            <div class="gallery">
                <?php $__currentLoopData = $lot->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" style="padding:0;border:0;background:transparent;cursor:pointer" onclick="document.getElementById('hero').src=this.querySelector('img').dataset.large">
                        <img src="<?php echo e($photo->preview_url); ?>" alt="" data-large="<?php echo e($photo->large_url ?: $photo->preview_url); ?>">
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <div class="facts">
            <?php $__currentLoopData = [
                'Комплектация' => $lot->grade,
                'Год' => $lot->year,
                'Пробег, км' => $lot->mileage_km,
                'Объём, cc' => $lot->displacement_cc,
                'КПП' => $lot->transmission,
                'Цвет' => $lot->color,
                'Код модели' => $lot->model_type,
                'Оборудование' => $lot->equipment,
                'Оценка' => $lot->score,
                'Результат' => $lot->result,
                'Топливо' => $lot->fuel,
                'Кузов' => $lot->chassis,
                'Финиш, ¥' => $lot->end_price_jpy,
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><span><?php echo e($label); ?></span><strong><?php echo e(($value === null || $value === '') ? '—' : $value); ?></strong></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($lot->attributes): ?>
            <div class="spec-block">
                <h3>Дополнительно</h3>
                <table>
                    <?php $__currentLoopData = $lot->attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr><td style="color:var(--muted)"><?php echo e($name); ?></td><td><?php echo e($value); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </table>
            </div>
        <?php endif; ?>
        <p style="margin-top:1rem"><a href="https://auctions.a-automarket.com/auctions/?p=project/lot&id=<?php echo e($lot->lot_id); ?>&s" target="_blank">Открыть на A-Automarket</a></p>
    </div>
    <form method="post" action="<?php echo e(route('automarket.lots.update', $lot)); ?>" class="card">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <h2 style="margin:0 0 1rem;font-size:1rem">Редактирование</h2>
        <label>Статус</label>
        <select name="status">
            <?php $__currentLoopData = ['active','inactive','absent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>" <?php if($lot->status === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <label style="margin-top:.75rem">Комплектация</label>
        <input name="grade" value="<?php echo e($lot->grade); ?>">
        <label style="margin-top:.75rem">Стартовая цена, ¥</label>
        <input name="start_price_jpy" type="number" value="<?php echo e($lot->start_price_jpy); ?>">
        <button class="btn btn-accent" style="margin-top:1rem;width:100%" type="submit">Сохранить</button>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/automarket/lots/show.blade.php ENDPATH**/ ?>