<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
                aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Brand -->
        <a class="navbar-brand pt-0" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(asset('assets/img/brand/phensim_blue.png')); ?>" class="navbar-brand-img" alt="...">
        </a>
        <!-- User -->
        <ul class="nav align-items-center d-md-none">
            <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('layout.user-menu-small')->html();
} elseif ($_instance->childHasBeenRendered('ifxDXKv')) {
    $componentId = $_instance->getRenderedChildComponentId('ifxDXKv');
    $componentTag = $_instance->getRenderedChildComponentTagName('ifxDXKv');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('ifxDXKv');
} else {
    $response = \Livewire\Livewire::mount('layout.user-menu-small');
    $html = $response->html();
    $_instance->logRenderedChild('ifxDXKv', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
        </ul>
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="<?php echo e(route('home')); ?>">
                            <img src="<?php echo e(asset('assets/img/brand/phensim_blue.png')); ?>" alt="">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse"
                                data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false"
                                aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Navigation -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('home')); ?>">
                        <i class="ni ni-tv-2 text-primary"></i> <?php echo e(__('Dashboard')); ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('simulations.index')); ?>">
                        <i class="ni ni-settings-gear-65 text-primary"></i> <?php echo e(__('Simulations')); ?>

                    </a>
                </li>
                <?php if(auth()->user()->is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('users.index')); ?>">
                            <i class="ni ni-single-02 text-primary"></i> <?php echo e(__('Users')); ?>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('messages.index')); ?>">
                            <i class="ni ni-bell-55 text-primary"></i> <?php echo e(__('Messages')); ?>

                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('docs.index')); ?>">
                        <i class="ni ni-books text-primary"></i> <?php echo e(__('User Manual')); ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('docs.api')); ?>">
                        <i class="ni ni-ui-04 text-primary"></i> <?php echo e(__('API')); ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('pages.references')); ?>">
                        <i class="ni ni-book-bookmark text-primary"></i> <?php echo e(__('References')); ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('pages.contacts')); ?>">
                        <i class="ni ni-badge text-primary"></i> <?php echo e(__('Contacts')); ?>

                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php /**PATH /var/www/html/resources/views/layouts/navbars/sidebar.blade.php ENDPATH**/ ?>