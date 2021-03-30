@extends('layouts.app', ['title' => __('Simulations')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-primary">
        <x-slot name="description">
            From this page you can manage all your simulations.
        </x-slot>
        Simulations
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                @livewire('simulations.index')
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection

@push('js')
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
@endpush
