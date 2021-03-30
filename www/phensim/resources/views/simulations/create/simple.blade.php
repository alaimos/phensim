@extends('layouts.app', ['title' => __('New simulation')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-gray-dark">
        <x-slot name="description">
            From this page you can create a new simulation using a guided procedure.
        </x-slot>
        New simulation
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                    </div>
                    <div class="card-body">
                        @livewire('simulations.create.simple')
                    </div>
                    <div class="card-footer py-4">
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection

@push('js')
    <script>
        window.addEventListener('swal:confirm:submit', event => {
            swal({
                title: event.detail.title,
                text: event.detail.text,
                icon: event.detail.type,
                buttons: true,
                dangerMode: false,
            }).then((willSubmit) => {
                window.livewire.emit('simulationSubmit', event.detail.id, !!willSubmit);
            });
        });
    </script>
@endpush
