@extends('layouts.app', ['title' => __('View Simulation')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-primary">
        <x-slot name="description">
            From this page you can view all results for your simulation.
        </x-slot>
        Results of &quot;{{ $simulation->name }}&quot;
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow collapsible">
                    <div class="card-header border-0" id="headingInputParameters"
                         data-toggle="collapse" data-target="#inputParameters" aria-expanded="false"
                         aria-controls="inputParameters">
                        <h3 class="mb-0">Input Parameters</h3>
                    </div>
                    <div id="inputParameters"
                         class="collapse" aria-labelledby="headingInputParameters">
                        <div class="card-body">
                            @if (session()->has('download-status'))
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    {{ session('download-status') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <div class="d-flex align-items-center justify-content-center flex-wrap mb-4">
                                <a href="{{ route('simulations.download.input', $simulation) }}"
                                   class="btn btn-primary">
                                    Download Input Files
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @livewire('simulations.show', ['simulation' => $simulation])
                <div class="card shadow mt-4">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Download results</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            <a href="{{ route('simulations.download.output', $simulation) }}" class="btn btn-primary">
                                Download raw results
                            </a>
                            <a href="{{ route('simulations.download.pathway', $simulation) }}" class="btn btn-primary">
                                Download pathway scores matrix
                            </a>
                            <a href="{{ route('simulations.download.node', $simulation) }}" class="btn btn-primary">
                                Download node scores matrix
                            </a>
                            <a href="{{ route('simulations.download.sbml', $simulation) }}" class="btn btn-primary">
                                Download results as an SBML model
                            </a>
                            <a href="{{ route('simulations.download.sif', $simulation) }}" class="btn btn-primary">
                                Download results as an extended SIF file
                            </a>
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
