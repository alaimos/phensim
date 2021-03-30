<nav class="navbar navbar-top navbar-horizontal navbar-expand-md navbar-dark">
    <div class="container px-4">
        <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(asset('assets/img/brand/phensim_white.png')); ?>" />
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse-main" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="<?php echo e(route('home')); ?>">
                            <img src="<?php echo e(asset('assets/img/brand/phensim_blue.png')); ?>">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Navbar items -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link nav-link-icon" href="<?php echo e(route('home')); ?>">
                        <i class="ni ni-planet"></i>
                        <span class="nav-link-inner--text"><?php echo e(__('Dashboard')); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-icon" href="<?php echo e(route('register')); ?>">
                        <i class="ni ni-circle-08"></i>
                        <span class="nav-link-inner--text"><?php echo e(__('Register')); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-icon" href="<?php echo e(route('login')); ?>">
                        <i class="ni ni-key-25"></i>
                        <span class="nav-link-inner--text"><?php echo e(__('Login')); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-icon" href="<?php echo e(route('profile.edit')); ?>">
                        <i class="ni ni-single-02"></i>
                        <span class="nav-link-inner--text"><?php echo e(__('Profile')); ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php /**PATH /var/www/html/resources/views/layouts/navbars/navs/guest.blade.php ENDPATH**/ ?>