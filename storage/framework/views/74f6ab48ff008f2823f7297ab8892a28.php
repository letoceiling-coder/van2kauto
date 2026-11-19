<?php if($paginator->hasPages()): ?>
    <nav class="che168-pager" aria-label="Страницы">
        <p class="che168-pager-meta">
            <?php if($paginator->total()): ?>
                <?php echo e($paginator->firstItem()); ?>–<?php echo e($paginator->lastItem()); ?> из <?php echo e($paginator->total()); ?>

            <?php else: ?>
                Нет записей
            <?php endif; ?>
        </p>
        <ul class="che168-pager-pages">
            <?php if($paginator->onFirstPage()): ?>
                <li><span class="is-disabled" aria-hidden="true">‹</span></li>
            <?php else: ?>
                <li><a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Назад">‹</a></li>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li><span class="is-gap"><?php echo e($element); ?></span></li>
                <?php endif; ?>
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li><span class="is-current" aria-current="page"><?php echo e($page); ?></span></li>
                        <?php else: ?>
                            <li><a href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <li><a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Вперёд">›</a></li>
            <?php else: ?>
                <li><span class="is-disabled" aria-hidden="true">›</span></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?><?php /**PATH /Users/neeklo/NEEKLO/01_ACTIVE/САЙТЫ/VANCARANAPA/vanauto-api/resources/views/che168/pagination.blade.php ENDPATH**/ ?>