<template>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                   id="nodes-list-table">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th class="text-center">Is Endpoint?</th>
                    <th class="text-center">Is Direct Target?</th>
                    <th>Activity Score</th>
                    <th>p-Value</th>
                    <th>Targeted By</th>
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
                let $ = window.$, tbl = $('#nodes-list-table'), self = this;
                tbl.dataTable({
                    autoWidth: false,
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
                        {data: 'nodeId', name: 'nodeId'},
                        {data: 'nodeName', name: 'nodeName'},
                        {data: 'isEndpoint', name: 'isEndpoint'},
                        {data: 'isDirectTarget', name: 'isDirectTarget'},
                        {data: 'activityScore', name: 'activityScore'},
                        {data: 'pValue', name: 'pValue'},
                        {data: 'targetedBy', name: 'targetedBy'}
                    ],
                    order:      [[5, 'asc']],
                    columnDefs: [
                        {targets: 0},
                        {targets: 1},
                        {targets: 2, className: 'text-center'},
                        {targets: 3, className: 'text-center'},
                        {targets: 4},
                        {targets: 5},
                        {targets: 6}
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
