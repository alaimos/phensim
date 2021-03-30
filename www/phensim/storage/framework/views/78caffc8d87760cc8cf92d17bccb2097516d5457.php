<!-- Top navbar -->
<nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
    <div class="container-fluid">
        <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block"
           href="<?php echo e(route('home')); ?>"><?php echo e(__('Dashboard')); ?></a>
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('layout.user-menu')->html();
} elseif ($_instance->childHasBeenRendered('ph5pJho')) {
    $componentId = $_instance->getRenderedChildComponentId('ph5pJho');
    $componentTag = $_instance->getRenderedChildComponentTagName('ph5pJho');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('ph5pJho');
} else {
    $response = \Livewire\Livewire::mount('layout.user-menu');
    $html = $response->html();
    $_instance->logRenderedChild('ph5pJho', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
    </div>
</nav>
<?php /**PATH /var/www/html/resources/views/layouts/navbars/navs/auth.blade.php ENDPATH**/ ?>