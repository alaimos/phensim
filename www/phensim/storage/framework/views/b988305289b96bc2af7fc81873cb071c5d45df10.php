<ul class="navbar-nav align-items-center d-none d-md-flex">
    <li class="nav-item dropdown">
        <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
           aria-expanded="false">
            <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle">
                            <img alt="<?php echo e(auth()->user()->name); ?> avatar"
                                 src="<?php echo e(Gravatar::src(auth()->user()->email, 40)); ?>">
                        </span>
                <div class="media-body ml-2 d-none d-lg-block">
                    <span class="mb-0 text-sm  font-weight-bold"><?php echo e(auth()->user()->name); ?></span>
                </div>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
            <div class=" dropdown-header noti-title">
                <h6 class="text-overflow m-0"><?php echo e(__('Welcome!')); ?></h6>
            </div>
            <a href="<?php echo e(route('profile.edit')); ?>" class="dropdown-item">
                <i class="ni ni-single-02"></i>
                <span><?php echo e(__('My profile')); ?></span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo e(route('logout')); ?>"
               class="dropdown-item"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="ni ni-user-run"></i>
                <span><?php echo e(__('Logout')); ?></span>
            </a>
        </div>
    </li>
</ul>
<?php /**PATH /var/www/html/resources/views/livewire/layout/user-menu.blade.php ENDPATH**/ ?>