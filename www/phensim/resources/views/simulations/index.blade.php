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
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-4 text-left">
                                <a href="{{ route('simulations.create.simple') }}" class="btn btn-sm btn-primary">
                                    New simple simulation
                                </a>
                            </div>
                            <div class="col">
                                &nbsp;
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('simulations.create.advanced') }}" class="btn btn-sm btn-primary">
                                    New advanced simulation
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                    </div>
                    @livewire('simulations.data-table')
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">

                        </nav>
                    </div>
                </div>
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
