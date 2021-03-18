@props(['id', 'width' => '', 'scrollable' => false, 'modal' => false])

@php
    $id = $id ?? md5($attributes->wire('model'));
    $maxWidth=match($width ?? ''){'sm'=>' modal-sm','lg'=>' modal-lg','xl'=>' modal-xl',default=>'',};
@endphp

<!-- Modal -->
<div
    x-data="{
        show: @entangle($attributes->wire('model')).defer,
    }"
    x-init="() => {
        let modal = $('#{{ $id }}');
        $watch('show', value => {
            console.log(value);
            if (value) {
                modal.modal('show')
            } else {
                modal.modal('hide')
            }
        });
        modal.on('hide.bs.modal', function () {
            show = false
        })
    }"
    wire:ignore.self
    class="modal fade"
    tabindex="-1"
    id="{{ $id }}"
    aria-labelledby="{{ $id }}"
    aria-hidden="true"
    x-ref="{{ $id }}"
>
    <div class="modal-dialog{{ $maxWidth }} modal-dialog-centered{{ $scrollable ? ' modal-dialog-scrollable' : '' }}">
        {{ $slot }}
    </div>
</div>
