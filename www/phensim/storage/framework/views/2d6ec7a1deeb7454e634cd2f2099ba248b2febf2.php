<?php $attributes = $attributes->exceptProps(['gradient' => 'bg-gradient-primary']); ?>
<?php foreach (array_filter((['gradient' => 'bg-gradient-primary']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center">
    <span class="mask <?php echo e($gradient); ?> opacity-8"></span>
    <div class="container-fluid d-flex align-items-center">
        <div class="row">
            <div <?php echo e($attributes->merge(['class' => 'col-md-12'])); ?>>
                <h1 class="display-2 text-white"><?php echo e($slot); ?></h1>
                <?php if($description ?? null): ?>
                    <p class="text-white mt-0 mb-5"><?php echo e($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/page-header.blade.php ENDPATH**/ ?>