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
                            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                                   id="jobs-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
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

        <div id="log-viewer-dialog" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-file-text" aria-hidden="true"></i> Log Viewer</h4>
                    </div>
                    <div class="modal-body">
                    <pre>

                    </pre>
                    </div>
                    <div class="modal-footer">
                    <span class="pull-left">
                        <button type="button" class="btn btn-primary live-log-button" data-toggle="button"
                                aria-pressed="false" autocomplete="off">
                            <i class="fa fa-play fa-fw"></i> Live logs
                        </button>
                        <span class="updating"></span>
                    </span>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                            <i class="fa fa-times-circle fa-fw"></i> Close
                        </button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </main>
    <!-- END Main Container -->
@endsection
@push('inline-scripts')
<script>
    $(function () {
        var dialog = $('#log-viewer-dialog'),
            updatingIcon = dialog.find('.updating'),
            liveButton = dialog.find('.live-log-button'), currentId, timer;
        dialog.on('hidden.bs.modal', function () {
            if (liveButton.hasClass('active')) {
                liveButton.button('toggle');
            }
            updatingIcon.html('');
            currentId = null;
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        });
        liveButton.on('click', function () {
            if (!liveButton.hasClass('active')) {
                updatingIcon.html('<i class="fa fa-cog fa-spin  fa-fw"></i><span class="sr-only">Updating...</span>');
                timer = setInterval(function () {
                    if (!currentId) return;
                    $.ajax({
                        dataType: 'json',
                        method: 'GET',
                        url: '{{ url('/jobs') }}/' + currentId + '/log',
                        success: function (data) {
                            dialog.find('.modal-body').find('pre').html(data.log);
                        }
                    });
                }, 10000);
            } else {
                updatingIcon.html('');
                clearInterval(timer);
                timer = null;
            }
        });
        var tbl = $('#jobs-table'),
            viewLog = function (id) {
                currentId = id;
                $.ajax({
                    dataType: 'json',
                    method: 'GET',
                    url: '{{ url('/jobs') }}/' + id + '/log',
                    success: function (data) {
                        dialog.find('.modal-body').find('pre').html(data.log);
                        dialog.modal('show');
                        $('i.loading-job').remove();
                    }
                });
            };
        tbl.dataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('jobs-list') }}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'job_type', name: 'job_type'},
                {data: 'job_status', name: 'job_status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[3, 'desc']],
            columnDefs: [
                {targets: 0},
                {targets: 1},
                {targets: 2},
                {targets: 3},
                {targets: 4}
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>'

            }
        });
        tbl.on('click', 'a.btn-view-job', function () {
            var t = $(this), id = t.data('id');
            t.parent().append('&nbsp;&nbsp;<i class="fa fa-spinner fa-pulse fa-fw loading-job"></i>');
            viewLog(id);
        })


    });
</script>
@endpush
