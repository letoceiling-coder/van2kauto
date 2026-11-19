<?php $__env->startSection('title', 'Объявления'); ?>
<?php $__env->startSection('content'); ?>
<h1 style="margin-top:0">Объявления</h1>
<form method="get" class="card filters" style="margin:1rem 0">
    <div><label>Поиск</label><input name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Название или ID"></div>
    <div><label>Марка</label>
        <select name="brand_id" onchange="this.form.submit()">
            <option value="">Все</option>
            <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($brand->brand_id); ?>" <?php if((string) ($filters['brand_id'] ?? '') === (string) $brand->brand_id): echo 'selected'; endif; ?>><?php echo e($brand->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Серия</label>
        <select name="series_id">
            <option value=""><?php echo e($series->isEmpty() ? 'Сначала марка' : 'Все'); ?></option>
            <?php $__currentLoopData = $series; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($item->series_id); ?>" <?php if((string) ($filters['series_id'] ?? '') === (string) $item->series_id): echo 'selected'; endif; ?>><?php echo e($item->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Топливо</label>
        <select name="fuel">
            <option value="">Все</option>
            <?php $__currentLoopData = $fuels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fuel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($fuel); ?>" <?php if(($filters['fuel'] ?? '') === $fuel): echo 'selected'; endif; ?>><?php echo e($fuel); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>КПП</label>
        <select name="gearbox">
            <option value="">Все</option>
            <?php $__currentLoopData = $gearboxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($gear); ?>" <?php if(($filters['gearbox'] ?? '') === $gear): echo 'selected'; endif; ?>><?php echo e($gear); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Кузов</label>
        <select name="body">
            <option value="">Все</option>
            <?php $__currentLoopData = $bodies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $body): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($body); ?>" <?php if(($filters['body'] ?? '') === $body): echo 'selected'; endif; ?>><?php echo e($body); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Цвет</label>
        <select name="color">
            <option value="">Все</option>
            <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($color); ?>" <?php if(($filters['color'] ?? '') === $color): echo 'selected'; endif; ?>><?php echo e($color); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Год от</label><input name="year_min" inputmode="numeric" value="<?php echo e($filters['year_min'] ?? ''); ?>"></div>
    <div><label>Год до</label><input name="year_max" inputmode="numeric" value="<?php echo e($filters['year_max'] ?? ''); ?>"></div>
    <div><label>Цена от, $</label><input name="price_min" type="number" step="100" value="<?php echo e($filters['price_min'] ?? ''); ?>"></div>
    <div><label>Цена до, $</label><input name="price_max" type="number" step="100" value="<?php echo e($filters['price_max'] ?? ''); ?>"></div>
    <div><label>Пробег от, км</label><input name="mileage_min" type="number" step="1000" value="<?php echo e($filters['mileage_min'] ?? ''); ?>"></div>
    <div><label>Пробег до, км</label><input name="mileage_max" type="number" step="1000" value="<?php echo e($filters['mileage_max'] ?? ''); ?>"></div>
    <div><label>Мощность от, л.с.</label><input name="power_min" type="number" value="<?php echo e($filters['power_min'] ?? ''); ?>"></div>
    <div><label>Мощность до, л.с.</label><input name="power_max" type="number" value="<?php echo e($filters['power_max'] ?? ''); ?>"></div>
    <div><label class="row"><input type="checkbox" name="not_in_showcase" value="1" <?php if(!empty($filters['not_in_showcase'])): echo 'checked'; endif; ?>> Ещё нет в витрине</label></div>
    <div><label>Статус</label>
        <select name="status">
            <option value="">Все</option>
            <?php $__currentLoopData = ['active','inactive','absent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php if(($filters['status'] ?? '') === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <button class="btn btn-accent" type="submit">Фильтр</button>
    <a class="btn btn-ghost" href="<?php echo e(route('che168.listings.index', $passPreset)); ?>" title="≤<?php echo e($passPreset['power_max']); ?> л.с., <?php echo e($passPreset['year_min']); ?>–<?php echo e($passPreset['year_max']); ?> г.">Проходные</a>
</form>
<form method="post" action="<?php echo e(route('admin.showcase.from-listings')); ?>" id="to-showcase"><?php echo csrf_field(); ?></form>
<div class="card" style="overflow-x:auto">
<div class="row" style="margin-bottom:.75rem">
    <button class="btn btn-accent btn-sm" form="to-showcase">В витрину выбранные</button>
    <label class="row muted" style="margin:0">фото на карточку <input form="to-showcase" type="number" name="photos" value="6" min="0" max="20" style="width:70px"></label>
    <span class="muted">До 20 за раз. Создаются черновики — цену и тексты правите в «Витрине».</span>
</div>
<table>
    <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c=>c.checked=this.checked)"></th><th></th><th>ID</th><th>Название</th><th>Марка</th><th>Год</th><th>Пробег</th><th>Л.с.</th><th>Цена $</th><th>Статус</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php if(isset($inShowcase[$item->infoid])): ?><a class="tag tag-ok" href="<?php echo e(route('admin.showcase.edit', $inShowcase[$item->infoid])); ?>">в витрине</a><?php else: ?><input class="pick" form="to-showcase" type="checkbox" name="infoids[]" value="<?php echo e($item->infoid); ?>"><?php endif; ?></td>
            <td><?php if($item->photos->first()): ?><img src="<?php echo e($item->photos->first()->preview_url); ?>" width="72" height="54" style="object-fit:cover;border-radius:6px;width:72px;height:54px"><?php endif; ?></td>
            <td><?php echo e($item->infoid); ?></td>
            <td><?php echo e($item->title); ?></td>
            <td><?php echo e($item->brand?->name ?? '—'); ?></td>
            <td><?php echo e($item->reg_date ? substr((string) $item->reg_date, 0, 4) : '—'); ?></td>
            <td><?php echo e($item->mileage_km ? number_format($item->mileage_km, 0, '.', ' ') : '—'); ?></td>
            <td><?php echo e($item->power_hp ?? '—'); ?></td>
            <td><?php echo e($item->price_usd ? number_format($item->price_usd, 0, '.', ' ') : '—'); ?></td>
            <td><span class="badge badge-<?php echo e($item->status); ?>"><?php echo e($item->status); ?></span></td>
            <td><a href="<?php echo e(route('che168.listings.show', $item)); ?>">Открыть</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="11">Нет объявлений по этим фильтрам. Запустите полный парсер — марка и характеристики появятся после карточек.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php echo e($listings->links('che168.pagination')); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('che168.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/che168/listings/index.blade.php ENDPATH**/ ?>