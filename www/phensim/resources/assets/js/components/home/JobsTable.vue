<template>
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                           id="jobs-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
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
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        props: ['log_url', 'list_url'],
        data() {
            return {
                dialog:    null,
                currentId: null,
            };
        },

        /**
         * Prepare the component (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            logUrl() {
                return this.log_url + '/' + this.currentId + '/log';
            },
            viewLog(id) {
                this.currentId = id;
                axios.get(this.logUrl()).then(data => {
                    this.dialog.find('.modal-body').find('pre').html(data.data.job_log);
                    this.dialog.modal('show');
                    if (data.data.job_status !== "processing") {
                        this.dialog.find('.live-log-button').hide();
                    } else {
                        this.dialog.find('.live-log-button').show();
                    }
                    window.$('i.loading-job').remove();
                }).catch(error => {
                    this.dialog.find('.modal-body').find('pre').html(error);
                    this.dialog.modal('show');
                    window.$('i.loading-job').remove();
                });
            },
            prepareDialog() {
                this.dialog = window.$('#log-viewer-dialog');
                let updatingIcon = this.dialog.find('.updating'),
                    liveButton = this.dialog.find('.live-log-button'), timer;
                this.dialog.on('hidden.bs.modal', () => {
                    if (liveButton.hasClass('active')) {
                        liveButton.button('toggle');
                    }
                    updatingIcon.html('');
                    this.currentId = null;
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                });
                liveButton.on('click', () => {
                    if (!liveButton.hasClass('active')) {
                        updatingIcon.html('<i class="fa fa-cog fa-spin  fa-fw"></i><span class="sr-only">Updating...</span>');
                        timer = setInterval(() => {
                            if (!this.currentId) return;
                            axios.get(this.logUrl()).then(data => {
                                this.dialog.find('.modal-body').find('pre').html(data.data.job_log);
                                if (data.data.job_status !== "processing") {
                                    this.dialog.find('.live-log-button').hide();
                                    updatingIcon.html('');
                                    clearInterval(timer);
                                } else {
                                    this.dialog.find('.live-log-button').show();
                                }
                            }).catch(error => {
                                this.dialog.find('.modal-body').find('pre').html(error);
                            });
                        }, 10000);
                    } else {
                        updatingIcon.html('');
                        clearInterval(timer);
                        timer = null;
                    }
                });
            },
            prepareTable() {
                let $ = window.$, tbl = $('#jobs-table'), self = this;
                tbl.dataTable({
                    processing: true,
                    serverSide: true,
                    ajax:       {
                        url:        self.list_url,
                        method:     'POST',
                        beforeSend: request => {
                            request.setRequestHeader('X-CSRF-TOKEN', window.Laravel.csrfToken);
                        }
                    },
                    columns:    [
                        {data: 'id', name: 'id'},
                        {data: 'job_name', name: 'job_name'},
                        {data: 'job_type', name: 'job_type'},
                        {data: 'job_status', name: 'job_status'},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                    order:      [[3, 'desc']],
                    columnDefs: [
                        {targets: 0},
                        {targets: 1},
                        {targets: 2},
                        {targets: 3},
                        {targets: 4}
                    ],
                    language:   {
                        processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>'

                    }
                });
                tbl.on('click', 'a.btn-view-job', function () {
                    let t = $(this), id = t.data('id');
                    t.parent().append('&nbsp;&nbsp;<i class="fa fa-spinner fa-pulse fa-fw loading-job"></i>');
                    self.viewLog(id);
                })
            },
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.prepareDialog();
                this.prepareTable();
            }
        }
    }
</script>
