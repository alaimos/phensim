@extends('layouts.app', ['title' => __('View Simulation')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-primary">
        <x-slot name="description">
            From this page you can view all results for a pathway in your simulation.
        </x-slot>
        Results of &quot;{{ $pathway }}&quot; for &quot;{{ $simulation->name }}&quot;
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                @livewire('simulations.pathways.show', ['simulation' => $simulation, 'pathway' => $pathway])
                <div class="card shadow mt-4">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Download results</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            @livewire('simulations.pathways.show.image', ['simulation' => $simulation, 'pathway' => $pathway])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection

@push('js')
@endpush
