@extends('layouts.app', ['title' => __('Users')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-primary">
        <x-slot name="description">
            From this page you can manage all users.
        </x-slot>
        Users
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                @livewire('users.index')
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection

@push('js')
    <script>
        window.addEventListener('swal:confirm', event => {
            swal({
                title: event.detail.title || '',
                text: event.detail.text || '',
                icon: event.detail.icon || '',
                buttons: event.detail.buttons || false,
                dangerMode: event.detail.danger || false,
                timer: event.detail.timer || null,
            }).then((hasConfirmed) => {
                if (hasConfirmed && event.detail.type === 'delete') {
                    window.livewire.emit('receivedConfirmation', event.detail.id);
                }
            });
        });
    </script>
@endpush
