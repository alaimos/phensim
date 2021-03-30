<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo e(config('app.name', 'PHENSIM')); ?><?php if(isset($title)): ?> - <?php echo e($title); ?><?php endif; ?></title>
        <link href="<?php echo e(asset('assets/img/brand/favicon.png')); ?>" rel="icon" type="image/png">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
        <link href="<?php echo e(asset('argon/vendor/nucleo/css/nucleo.css')); ?>" rel="stylesheet">
        <link href="<?php echo e(asset('argon/vendor/@fortawesome/fontawesome-free/css/all.min.css')); ?>" rel="stylesheet">
        <link type="text/css" href="<?php echo e(asset('argon/css/argon.css?v=1.0.0')); ?>" rel="stylesheet">
        <?php echo \Livewire\Livewire::styles(); ?>

    </head>
    <body class="<?php echo e($class ?? ''); ?>">
        <?php if(auth()->guard()->check()): ?>
            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                <?php echo csrf_field(); ?>
            </form>
            <?php echo $__env->make('layouts.navbars.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        <div class="main-content">
            <?php echo $__env->make('layouts.navbars.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>

        <?php if(auth()->guard()->guest()): ?>
            <?php echo $__env->make('layouts.footers.guest', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        <script src="<?php echo e(asset('argon/vendor/jquery/dist/jquery.min.js')); ?>"></script>
        <script src="<?php echo e(asset('argon/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')); ?>"></script>
        <?php echo $__env->yieldPushContent('js'); ?>
        <script src="<?php echo e(asset('argon/js/argon.js?v=1.0.0')); ?>"></script>
        <?php echo \Livewire\Livewire::scripts(); ?>

        <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    </body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>