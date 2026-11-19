<?php $__env->startSection('title', $car->exists ? 'Карточка: '.$car->title() : 'Новая карточка'); ?>
<?php $__env->startSection('content'); ?>
<?php
    use App\Models\ShowcaseCar as SC;
    $v = fn ($k, $d = null) => old($k, $car->{$k} ?? $d);
    $util = old('utilization', $car->utilization_override === null ? 'auto' : ($car->utilization_override ? 'yes' : 'no'));
    $siteUrl = rtrim((string) \App\Support\Settings::get('site_url'), '/');
    $bot = \App\Support\Settings::get('tg_bot_username');
?>
<p><a href="<?php echo e(route('admin.showcase.index')); ?>">← Витрина</a></p>
<div class="row" style="justify-content:space-between">
    <h1 style="margin:0"><?php echo e($car->exists ? $car->title().' '.$car->year : 'Новая карточка'); ?></h1>
    <?php if($car->exists): ?>
    <div class="row">
        <?php if($car->is_published): ?><a class="btn btn-ghost btn-sm" target="_blank" href="<?php echo e($siteUrl); ?>/catalog/<?php echo e($car->slug); ?>/">На сайте ↗</a><?php endif; ?>
        <form method="post" action="<?php echo e(route('admin.showcase.duplicate', $car)); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost btn-sm">Создать копию</button></form>
        <form method="post" action="<?php echo e(route('admin.showcase.destroy', $car)); ?>" onsubmit="return confirm('Удалить карточку?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger btn-sm">Удалить</button></form>
    </div>
    <?php endif; ?>
</div>

<div class="detail-layout" style="margin-top:1rem">
<div>
<form method="post" action="<?php echo e($car->exists ? route('admin.showcase.update', $car) : route('admin.showcase.store')); ?>" class="card">
    <?php echo csrf_field(); ?>
    <?php if($car->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <h3 style="margin-top:0">Модель</h3>
    <div class="form-grid">
        <div><label>Марка *</label><input name="brand" required value="<?php echo e($v('brand')); ?>"></div>
        <div><label>Модель *</label><input name="model" required value="<?php echo e($v('model')); ?>"></div>
        <div><label>Поколение</label><input name="generation" value="<?php echo e($v('generation')); ?>"></div>
        <div><label>Год *</label><input name="year" type="number" required value="<?php echo e($v('year')); ?>"></div>
        <div class="wide"><label>Комплектация (точное название с che168 — важно для «проходных»)</label><input name="trim" value="<?php echo e($v('trim')); ?>"></div>
        <div><label>Страна</label><select name="country"><?php $__currentLoopData = SC::COUNTRIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('country','china')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Состояние</label><select name="condition"><?php $__currentLoopData = SC::CONDITIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('condition','used')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Кузов</label><select name="body"><?php $__currentLoopData = SC::BODIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('body','crossover')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Статус</label><select name="status"><?php $__currentLoopData = SC::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('status','order')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    </div>

    <h3>Характеристики</h3>
    <div class="form-grid">
        <div><label>Двигатель</label><select name="engine"><?php $__currentLoopData = SC::ENGINES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('engine','petrol')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Объём, л</label><input name="displacement_l" type="number" step="0.1" value="<?php echo e($v('displacement_l')); ?>"></div>
        <div><label>Мощность, л.с.</label><input name="power_hp" type="number" value="<?php echo e($v('power_hp')); ?>"></div>
        <div><label>Подпись двигателя (если пусто — авто)</label><input name="engine_label" value="<?php echo e($v('engine_label')); ?>" placeholder="Бензин, 1.5 л, 150 л.с."></div>
        <div><label>Привод</label><select name="drive"><?php $__currentLoopData = SC::DRIVES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('drive','FWD')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>КПП</label><input name="gearbox" value="<?php echo e($v('gearbox')); ?>" placeholder="Автомат"></div>
        <div><label>Руль</label><select name="steering"><?php $__currentLoopData = SC::STEERING; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('steering','left')===$k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div><label>Пробег, км</label><input name="mileage_km" type="number" value="<?php echo e($v('mileage_km', 0)); ?>"></div>
        <div><label>Проходной по утильсбору</label>
            <select name="utilization">
                <option value="auto" <?php if($util==='auto'): echo 'selected'; endif; ?>>Авто (≤<?php echo e(\App\Support\Settings::int('utilization_max_hp',160)); ?> л.с., <?php echo e(\App\Support\Settings::int('utilization_min_age',3)); ?>–<?php echo e(\App\Support\Settings::int('utilization_max_age',5)); ?> лет)<?php if($car->exists): ?> — сейчас: <?php echo e($car->utilizationOk() === null ? '?' : ($car->utilizationOk() ? 'да' : 'нет')); ?><?php endif; ?></option>
                <option value="yes" <?php if($util==='yes'): echo 'selected'; endif; ?>>Да</option>
                <option value="no" <?php if($util==='no'): echo 'selected'; endif; ?>>Нет</option>
            </select>
        </div>
    </div>

    <h3>Продажа</h3>
    <div class="form-grid">
        <div><label>Цена «от», ₽ (обязательно для публикации)</label><input name="price_from_rub" type="number" step="1000" value="<?php echo e($v('price_from_rub')); ?>"></div>
        <div><label>Срок доставки, дней от</label><input name="eta_from" type="number" value="<?php echo e($v('eta_from', 30)); ?>"></div>
        <div><label>до</label><input name="eta_to" type="number" value="<?php echo e($v('eta_to', 45)); ?>"></div>
        <div><label>Бейдж</label><input name="badge" value="<?php echo e($v('badge')); ?>" placeholder="Хит продаж"></div>
        <div class="wide"><label>Короткое описание</label><textarea name="summary" rows="3"><?php echo e($v('summary')); ?></textarea></div>
        <div class="wide"><label>Преимущества — по одному в строке</label><textarea name="highlights_text" rows="4"><?php echo e(old('highlights_text', implode("\n", (array) $car->highlights))); ?></textarea></div>
    </div>

    <h3>Публикация</h3>
    <div class="form-grid">
        <div><label>Порядок (меньше — выше)</label><input name="sort_order" type="number" value="<?php echo e($v('sort_order', 0)); ?>"></div>
        <div><label>Адрес (slug)</label><input name="slug" value="<?php echo e($v('slug')); ?>" placeholder="авто из названия"></div>
        <div><label>Серия che168 (для совпадений)</label><input name="series_id" type="number" value="<?php echo e($v('series_id')); ?>"></div>
        <div><label>Комплектация che168, spec_id</label><input name="spec_id" type="number" value="<?php echo e($v('spec_id')); ?>"></div>
        <label class="row"><input type="checkbox" name="is_published" value="1" <?php if($v('is_published')): echo 'checked'; endif; ?>> Опубликовать на сайте</label>
        <label class="row"><input type="checkbox" name="is_hit" value="1" <?php if($v('is_hit')): echo 'checked'; endif; ?>> Показывать в хитах на главной</label>
    </div>
    <div class="row" style="margin-top:1rem"><button class="btn btn-accent"><?php echo e($car->exists ? 'Сохранить' : 'Создать'); ?></button></div>
</form>

<?php if($car->exists): ?>
<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Фото (<?php echo e($car->photos->count()); ?>)</h3>
    <form method="post" action="<?php echo e(route('admin.showcase.photos.upload', $car)); ?>" enctype="multipart/form-data" class="row" style="margin-bottom:1rem">
        <?php echo csrf_field(); ?>
        <input type="file" name="photos[]" multiple accept="image/*" style="max-width:360px">
        <button class="btn btn-ghost">Загрузить свои</button>
    </form>
    <?php if($car->photos->isNotEmpty()): ?>
    <form method="post" action="<?php echo e(route('admin.showcase.photos.update', $car)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="thumbs">
            <?php $__currentLoopData = $car->photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <figure>
                <img src="<?php echo e($photo->url()); ?>" alt="">
                <label><span class="muted">#</span><input type="number" name="sort[<?php echo e($photo->id); ?>]" value="<?php echo e($photo->sort_order); ?>"><?php if($i===0): ?><span class="tag tag-ok">обложка</span><?php endif; ?></label>
                <label><input type="radio" name="cover" value="<?php echo e($photo->id); ?>"> сделать обложкой</label>
                <label><input type="checkbox" name="delete[<?php echo e($photo->id); ?>]" value="1"> удалить</label>
            </figure>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button class="btn btn-ghost" style="margin-top:.75rem">Сохранить фото</button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>
</div>

<aside>
<?php if($car->exists && $match): ?>
<div class="card">
    <h3 style="margin-top:0">Совпадения в фиде</h3>
    <p class="muted"><?php echo e($match['query']); ?></p>
    <div class="facts" style="grid-template-columns:1fr 1fr">
        <div><span>Объявлений</span><?php echo e($match['total']); ?></div>
        <div><span>Та же комплектация</span><?php echo e($match['exact_spec']); ?></div>
        <div><span>Мин. цена</span><?php echo e($match['price_min'] ? '$'.number_format($match['price_min'], 0, '.', ' ') : '—'); ?></div>
        <div><span>Медиана</span><?php echo e($match['price_median'] ? '$'.number_format($match['price_median'], 0, '.', ' ') : '—'); ?></div>
    </div>
    <?php $__currentLoopData = $match['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="border-top:1px solid var(--border);padding:.5rem 0">
            <a href="<?php echo e(route('che168.listings.show', $item)); ?>" target="_blank"><?php echo e($item->title); ?></a>
            <div class="muted">$<?php echo e(number_format((float) $item->price_usd, 0, '.', ' ')); ?> · <?php echo e(number_format((int) $item->mileage_km, 0, '.', ' ')); ?> км · <?php echo e($item->power_hp ? $item->power_hp.' л.с.' : ''); ?> <?php if($car->spec_id && $item->spec_id == $car->spec_id): ?><span class="tag tag-ok">комплектация</span><?php endif; ?></div>
            <form method="post" action="<?php echo e(route('admin.showcase.fill', $car)); ?>" class="row" style="margin-top:.3rem">
                <?php echo csrf_field(); ?> <input type="hidden" name="infoid" value="<?php echo e($item->infoid); ?>">
                <button class="btn btn-ghost btn-sm">Подставить характеристики</button>
                <button class="btn btn-ghost btn-sm" name="with_photos" value="1">+ фото</button>
            </form>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($match['total'] === 0): ?><p class="muted">Не найдено. Укажите «Серия che168» или проверьте марку/модель как в фиде.</p><?php endif; ?>
</div>
<?php endif; ?>
<?php if($car->exists): ?>
<div class="card" style="margin-top:1rem">
    <h3 style="margin-top:0">Ссылки</h3>
    <p class="muted">Страница: <?php echo e($siteUrl); ?>/catalog/<?php echo e($car->slug); ?>/</p>
    <?php if($bot): ?><p class="muted">Бот с этой машиной: https://t.me/<?php echo e($bot); ?>?start=car_<?php echo e($car->slug); ?></p><?php endif; ?>
    <?php if($car->source_infoid): ?><p class="muted">Создано из объявления <a href="<?php echo e(route('che168.listings.show', $car->source_infoid)); ?>">#<?php echo e($car->source_infoid); ?></a></p><?php endif; ?>
</div>
<?php endif; ?>
</aside>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/admin/showcase/edit.blade.php ENDPATH**/ ?>