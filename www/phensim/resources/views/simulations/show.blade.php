@extends('layouts.app', ['title' => __('View Simulation')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-primary">
        <x-slot name="description">
            From this page you can manage all your simulations.
        </x-slot>
        Results of &quot;{{ $simulation->name }}&quot;
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Input Parameters</h3>
                    </div>
                    <div class="card-body">
                        TODO
                    </div>
                </div>

                <div class="card shadow mt-4">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Results by pathway</h3>
                    </div>
                    @livewire('simulations.show.pathways', ['simulation' => $simulation])
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end">

                        </nav>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection

@push('js')
@endpush
