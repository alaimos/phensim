<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.headers.cards', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-12 mb-xl-0">
                <div class="card bg-gradient-default shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="text-white mb-0">Latest updates...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true; $__currentLoopData = $latestUpdates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="d-flex flex-column mt-2">
                                <div class="pt-1 text-sm font-weight-bold text-white d-flex justify-content-between">
                                    <div>
                                        <?php echo e($update->title); ?>

                                    </div>
                                    <div>
                                        <small class="text-gray"><i class="fas fa-clock mr-1"></i>
                                            <?php echo e($update->created_at->diffForHumans()); ?>

                                        </small>
                                    </div>
                                </div>
                                <div class="text-sm mt-1 mb-0 text-white-50 pb-1"
                                     style="border-bottom: 1px dotted #8898aa">
                                    <?php echo e($update->message); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="d-flex flex-column mt-2">
                                <div class="pt-1 text-sm font-weight-bold text-white d-flex justify-content-between">
                                    <div>
                                        Nothing new here!
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('layouts.footers.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard.blade.php ENDPATH**/ ?>