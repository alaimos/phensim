@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Page Header -->
        <div class="content bg-primary-dark">
            <section class="content content-full content-boxed">
                <div class="push-50-t push-15 text-center">
                    <h1 class="h2 text-white push-5-t animated zoomIn">Simulation Results</h1>
                    <h2 class="h5 text-white-op animated zoomIn">List of nodes involved in
                        pathway &quot;{{$pathway->name}}&quot;</h2>
                </div>
            </section>
        </div>
        <!-- END Page Header -->
        <!-- Go Back Button -->
        <div class="bg-white">
            <section class="content-mini content-mini-full content-boxed">
                <div class="row">
                    <div class="col-sm-12 clearfix">
                        <div class="pull-right"><a
                                    href="{{ route('view-simulation-job', ['job' => $job]) }}"
                                    class="btn btn-primary"><i class="fa fa-arrow-left"></i> Go Back
                            </a></div>
                    </div>
                </div>
            </section>
        </div>
        <!-- END Go Back Button -->
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
                            <h3 class="block-title"><i class="fa fa-fw fa-code-fork"></i> Nodes List</h3>
                        </div>
                        <div class="block-content">
                            <nodes-table
                                    list_url="{{ route('sim-nodes-list-data', ['job' => $job, 'pid' => $pid]) }}"></nodes-table>
                        </div>
                    </div>
                    <!-- END Pathway List -->
                </div>
            </div>
        </div>
        <!-- END Page Content -->
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
                            <h3 class="block-title"><i class="fa fa-fw fa-code-fork"></i> Viewer</h3>
                        </div>
                        <div class="block-content">
                            <div class="push-15 text-center">
                                <form method="POST" action="http://www.kegg.jp/kegg-bin/show_pathway"
                                      target="_blank">
                                    <input type="hidden" name="map" value="{{ $coloring['mapId'] }}">
                                    <input type="hidden" name="multi_query" value="{{ $coloring['coloring'] }}">
                                    <button class="btn btn-success" type="submit">
                                        View simulation results on KEGG
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- END Pathway List -->
                </div>
            </div>
        </div>
        <!-- END Page Content -->
        <!-- Go Back Button -->
        <div class="bg-white">
            <section class="content-mini content-mini-full content-boxed">
                <div class="row">
                    <div class="col-sm-12 clearfix">
                        <div class="pull-right"><a
                                    href="{{ route('view-simulation-job', ['job' => $job]) }}"
                                    class="btn btn-primary"><i class="fa fa-arrow-left"></i> Go Back
                            </a></div>
                    </div>
                </div>
            </section>
        </div>
        <!-- END Go Back Button -->
    </main>
    <!-- END Main Container -->
@endsection