@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Page Header -->
        <div class="content bg-primary-dark">
            <section class="content content-full content-boxed">
                <div class="push-50-t push-15 text-center">
                    <h1 class="h2 text-white push-5-t animated zoomIn">Simulation Results</h1>
                    <h2 class="h5 text-white-op animated zoomIn">List of involved pathways</h2>
                </div>
            </section>
        </div>
        <!-- END Page Header -->

        <!-- Page Content -->
        <div class="content content-boxed">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <!-- Pathway List -->
                    <div class="block">
                        <div class="block-header bg-gray-lighter">
                            <ul class="block-options">
                                <li>
                                    <button type="button" data-toggle="block-option"
                                            data-action="fullscreen_toggle"></button>
                                </li>
                            </ul>
                            <h3 class="block-title"><i class="fa fa-fw fa-code-fork"></i> Pathways List</h3>
                        </div>
                        <div class="block-content">
                            <pathways-table
                                    list_url="{{ route('view-pathway-list-data', ['job' => $job]) }}"></pathways-table>
                        </div>
                    </div>
                    <!-- END Pathway List -->
                </div>
            </div>
        </div>
        <!-- END Page Content -->

        <!-- Download Content -->
        <div class="content content-boxed">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="block">
                        <div class="block-header bg-gray-lighter">
                            <ul class="block-options">
                                <li>
                                    <button type="button" data-toggle="block-option"
                                            data-action="fullscreen_toggle"></button>
                                </li>
                            </ul>
                            <h3 class="block-title"><i class="fa fa-fw fa-download"></i> Downloads</h3>
                        </div>
                        <div class="block-content">
                            <div class="row push-30 push-10-t">
                                <div class="col-sm-12 text-center">
                                    <a href="{{ route('download-simulation-data', ['job' => $job]) }}"
                                       class="btn btn-success">
                                        <i class="fa fa-download"></i> Download Simulation Data
                                    </a>
                                </div>
                                <div class="col-sm-12 text-center">
                                    <a href="{{ route('download-simulation-pathway-data', ['job' => $job]) }}"
                                       class="btn btn-success">
                                        <i class="fa fa-download"></i> Download Pathway Matrix
                                    </a>
                                </div>
                                <div class="col-sm-12 text-center">
                                    <a href="{{ route('download-simulation-nodes-data', ['job' => $job]) }}"
                                       class="btn btn-success">
                                        <i class="fa fa-download"></i> Download Nodes Matrix
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Download Content -->
    </main>
    <!-- END Main Container -->
@endsection