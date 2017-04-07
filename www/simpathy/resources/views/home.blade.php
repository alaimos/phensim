@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Page Header -->
        <div class="content bg-primary-dark">
            <div class="push-50-t push-15 clearfix">
                <h1 class="h2 text-white push-5-t animated zoomIn">{{ Auth::user()->name }}</h1>
                <h2 class="h5 text-white-op animated zoomIn">{{ Auth::user()->affiliation }}</h2>
            </div>
        </div>
        <!-- END Page Header -->

        <!-- Stats -->
        <div class="content bg-white border-b">
            <div class="row items-push text-uppercase">
                <div class="col-xs-6 col-sm-3">
                    <div class="font-w700 text-gray-darker animated fadeIn">Queued Jobs</div>
                    <a class="h2 font-w300 text-primary animated flipInX">{{ $queuedJobs }}</a>
                </div>
                <div class="col-xs-6 col-sm-3">
                    <div class="font-w700 text-gray-darker animated fadeIn">Processing Jobs</div>
                    <a class="h2 font-w300 text-primary animated flipInX">{{ $processingJobs }}</a>
                </div>
                <div class="col-xs-6 col-sm-3">
                    <div class="font-w700 text-gray-darker animated fadeIn">Completed Jobs</div>
                    <a class="h2 font-w300 text-primary animated flipInX">{{ $completedJobs }}</a>
                </div>
                <div class="col-xs-6 col-sm-3">
                    <div class="font-w700 text-gray-darker animated fadeIn">Failed Jobs</div>
                    <a class="h2 font-w300 text-primary animated flipInX">{{ $failedJobs }}</a>
                </div>
            </div>
        </div>
        <!-- END Stats -->

        <!-- Page Content -->
        <div class="content content-boxed">
            <div class="row">
                <div class="col-sm-7 col-lg-8">
                    <!-- History -->
                    <div class="block">
                        <div class="block-header bg-gray-lighter">
                            <ul class="block-options">
                                <li>
                                    <button type="button" data-toggle="block-option"
                                            data-action="fullscreen_toggle"></button>
                                </li>
                            </ul>
                            <h3 class="block-title"><i class="fa fa-fw fa-clock-o"></i> Analysis History</h3>
                        </div>
                        <div class="block-content">
                            <jobs-table log_url="{{ url('/jobs') }}" list_url="{{ route('jobs-list') }}"></jobs-table>
                        </div>
                    </div>
                    <!-- END History -->
                </div>
                <div class="col-sm-5 col-lg-4">
                    <!-- Analysis -->
                    <div class="block">
                        <div class="block-header bg-gray-lighter">
                            <h3 class="block-title"><i class="fa fa-fw fa-briefcase"></i> Analysis</h3>
                        </div>
                        <div class="block-content">
                            <ul class="list list-simple list-li-clearfix">
                                <li>
                                    <a class="item item-rounded pull-left push-10-r bg-info"
                                       href="{{ route('submit-simple') }}">
                                        <i class="fa fa-smile-o text-white-op"></i>
                                    </a>
                                    <h5 class="push-5-t">Simple Simulation</h5>
                                    <div class="font-s13">Run a simulation using existent pathway elements</div>
                                </li>
                                <li>
                                    <a class="item item-rounded pull-left push-10-r bg-amethyst"
                                       href="{{ route('submit-enriched') }}">
                                        <i class="fa fa-smile-o text-white-op"></i>
                                    </a>
                                    <h5 class="push-5-t">Enriched Simulation</h5>
                                    <div class="font-s13">Run a simulation using custom enriched pathways</div>
                                </li>
                                <li>
                                    <a class="item item-rounded pull-left push-10-r bg-danger"
                                       href="{{ url('/') }}">
                                        <i class="si si-layers text-white-op"></i>
                                    </a>
                                    <h5 class="push-10-t">SPECifIC</h5>
                                    <div class="font-s13">Run SPECifIC on the results of a simulation</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- END Analysis -->
                </div>
            </div>
        </div>
        <!-- END Page Content -->
    </main>
    <!-- END Main Container -->
@endsection