<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'components.page-header','data' => ['class' => 'col-lg-12','gradient' => 'bg-gradient-primary']]); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'col-lg-12','gradient' => 'bg-gradient-primary']); ?>
         <?php $__env->slot('description'); ?> 
            From this page you can manage all your simulations.
         <?php $__env->endSlot(); ?>
        Simulations
     <?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('simulations.index')->html();
} elseif ($_instance->childHasBeenRendered('8I8CavO')) {
    $componentId = $_instance->getRenderedChildComponentId('8I8CavO');
    $componentTag = $_instance->getRenderedChildComponentTagName('8I8CavO');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('8I8CavO');
} else {
    $response = \Livewire\Livewire::mount('simulations.index');
    $html = $response->html();
    $_instance->logRenderedChild('8I8CavO', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
            </div>
        </div>

        <?php echo $__env->make('layouts.footers.auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        window.addEventListener('swal:confirm', event => {
            swal({
                title: event.detail.title || "",
                text: event.detail.text || "",
                icon: event.detail.icon || "",
                buttons: true,
                dangerMode: event.detail.danger || false,
            }).then((hasConfirmed) => {
                if (hasConfirmed) {
                    window.livewire.emit('receivedConfirmation', event.detail.id, event.detail.type);
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', ['title' => __('Simulations')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/resources/views/simulations/index.blade.php ENDPATH**/ ?>