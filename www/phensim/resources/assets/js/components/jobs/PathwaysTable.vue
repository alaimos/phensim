<template>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                   id="pathways-list-table">
                <thead>
                <tr>
                    <th rowspan="2" style="vertical-align: middle">Id</th>
                    <th rowspan="2" style="vertical-align: middle">Name</th>
                    <th rowspan="2" style="vertical-align: middle"># Targets</th>
                    <th colspan="2" class="text-center"># Nodes</th>
                    <th rowspan="2" style="vertical-align: middle">Action</th>
                </tr>
                <tr>
                    <th>Activated</th>
                    <th>Inhibited</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        props: ['list_url'],
        data() {
            return {};
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
            prepareTable() {
                let $ = window.$, tbl = $('#pathways-list-table'), self = this;
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
                        {data: 'name', name: 'name'},
                        {data: 'directTargets', name: 'directTargets'},
                        {data: 'activatedNodes', name: 'activatedNodes'},
                        {data: 'inhibitedNodes', name: 'inhibitedNodes'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                    order:      [[0, 'asc']],
                    columnDefs: [
                        {targets: 0},
                        {targets: 1},
                        {targets: 2, className: 'text-center'},
                        {targets: 3, className: 'text-center'},
                        {targets: 4, className: 'text-center'},
                        {targets: 5, className: 'text-center'}
                    ],
                    language:   {
                        processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Loading...</span>'

                    }
                });
            },
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.prepareTable();
            }
        }
    }
</script>
