<div>
    <div class="card shadow">
        <div class="card-header border-0">
            <div class="row align-items-center">
                <div class="col-4 text-left">
                    <a href="<?php echo e(route('simulations.create.simple')); ?>" class="btn btn-sm btn-primary">
                        New simple simulation
                    </a>
                </div>
                <div class="col">
                    &nbsp;
                </div>
                <div class="col-4 d-flex flex-row-reverse">
                    <a href="<?php echo e(route('simulations.create.advanced')); ?>" class="btn btn-sm btn-primary">
                        New advanced simulation
                    </a>
                    <div class="mr-2" wire:loading.delay>
                        <i class="fas fa-spinner fa-pulse"></i> Loading...
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" wire:click="sortByColumn('id')">
                            #
                            <?php if($sortColumn === 'id'): ?>
                                <i class="fa fa-fw fa-sort-<?php echo e($sortDirection === 'asc' ?  'up' : 'down'); ?>"></i>
                            <?php else: ?>
                                <i class="fa fa-fw fa-sort"></i>
                            <?php endif; ?>
                        </th>
                        <th scope="col" wire:click="sortByColumn('name')">
                            Name
                            <?php if($sortColumn === 'name'): ?>
                                <i class="fa fa-fw fa-sort-<?php echo e($sortDirection === 'asc' ?  'up' : 'down'); ?>"></i>
                            <?php else: ?>
                                <i class="fa fa-fw fa-sort"></i>
                            <?php endif; ?>
                        </th>
                        <th scope="col" wire:click="sortByColumn('status')">
                            Status
                            <?php if($sortColumn === 'status'): ?>
                                <i class="fa fa-fw fa-sort-<?php echo e($sortDirection === 'asc' ?  'up' : 'down'); ?>"></i>
                            <?php else: ?>
                                <i class="fa fa-fw fa-sort"></i>
                            <?php endif; ?>
                        </th>
                        <?php if(auth()->user()->is_admin): ?>
                            <th scope="col" wire:click="sortByColumn('user_name')">
                                User
                                <?php if($sortColumn === 'user_name'): ?>
                                    <i class="fa fa-fw fa-sort-<?php echo e($sortDirection === 'asc' ?  'up' : 'down'); ?>"></i>
                                <?php else: ?>
                                    <i class="fa fa-fw fa-sort"></i>
                                <?php endif; ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" wire:click="sortByColumn('created_at')">
                            Creation Date
                            <?php if($sortColumn === 'created_at'): ?>
                                <i class="fa fa-fw fa-sort-<?php echo e($sortDirection === 'asc' ?  'up' : 'down'); ?>"></i>
                            <?php else: ?>
                                <i class="fa fa-fw fa-sort"></i>
                            <?php endif; ?>
                        </th>
                        <th scope="col">
                            Actions
                        </th>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-control-sm" wire:model="searchColumns.name"/>
                        </td>
                        <td>
                            <select class="form-control form-control-sm" wire:model="searchColumns.status">
                                <option value="-1">-- Choose Status --</option>
                                <?php $__currentLoopData = \App\Models\Simulation::STATE_NAMES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>
                        <?php if(auth()->user()->is_admin): ?>
                            <td></td>
                        <?php endif; ?>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $simulations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $simulation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($simulation->id); ?></td>
                            <td><?php echo e($simulation->name); ?></td>
                            <td><?php echo e(\App\Models\Simulation::STATE_NAMES[$simulation->status]); ?></td>
                            <?php if(auth()->user()->is_admin): ?>
                                <td><?php echo e($simulation->user_name); ?></td>
                            <?php endif; ?>
                            <td><?php echo e($simulation->created_at->diffForHumans()); ?></td>
                            <td>
                                <?php if($simulation->isReady()): ?>
                                    <a href="#" wire:click.prevent="confirmSimulationSubmission(<?php echo e($simulation->id); ?>)"
                                       data-tippy-content="Submit simulation"
                                       title="Submit simulation">
                                        <i class="fas fa-play fa-fw"></i>
                                    </a>
                                <?php elseif($simulation->isFailed() || ($simulation->isCompleted() && auth()->user()->is_admin)): ?>
                                    <a href="#"
                                       wire:click.prevent="confirmSimulationReSubmission(<?php echo e($simulation->id); ?>)"
                                       data-tippy-content="Resubmit simulation"
                                       title="Resubmit simulation">
                                        <i class="fas fa-redo fa-fw"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($simulation->hasLogs()): ?>
                                    <a href="#" wire:click.prevent="displayLogs(<?php echo e($simulation->id); ?>)"
                                       data-tippy-content="Show logs"
                                       title="Show logs">
                                        <i class="fas fa-file-alt fa-fw"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($simulation->isCompleted()): ?>
                                    <a href="<?php echo e(route('simulations.show', $simulation)); ?>"
                                       data-tippy-content="Show simulation"
                                       title="Show simulation">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($simulation->canBeDeleted()): ?>
                                    <a href="#" wire:click.prevent="confirmSimulationDeletion(<?php echo e($simulation->id); ?>)"
                                       class="text-danger"
                                       data-tippy-content="Delete"
                                       title="Delete">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">
                                There are no simulations here!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer py-4">
            <nav class="d-flex justify-content-end">
                <?php echo e($simulations->links()); ?>

            </nav>
        </div>
    </div>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.modal','data' => ['wire:model' => 'displayingLog','scrollable' => 'true','width' => 'xl']]); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['wire:model' => 'displayingLog','scrollable' => 'true','width' => 'xl']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Logs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if($displayingLog && $currentSimulation): ?>
                    <?php if($currentSimulation->isProcessing()): ?>
                        <pre wire:poll.30s><?php echo e($currentSimulation->logs); ?></pre>
                    <?php else: ?>
                        <pre><?php echo e($currentSimulation->logs); ?></pre>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="modal-footer justify-content-between align-items-center">
                <div class="text-left">
                    <?php if($currentSimulation && $currentSimulation->isProcessing()): ?>
                        <i class="fa fa-sync fa-spin fa-fw"></i> Working...
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <button type="button" wire:click="$set('displayingLog', false)" wire:loading.attr="disabled"
                            class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
     <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
</div>

<?php $__env->startPush('js'); ?>
    <script>
        document.addEventListener('livewire:load', () => {
            tippy('[data-tippy-content]');
            Livewire.hook('message.processed', (message, component) => {
                tippy('[data-tippy-content]');
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /var/www/html/resources/views/livewire/simulations/index.blade.php ENDPATH**/ ?>