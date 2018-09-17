<template>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                   id="pathways-list-table">
                <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Activity Score</th>
                    <th>p-Value</th>
                    <th>Action</th>
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
                    autoWidth:  false,
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
                        {data: 'activityScore', name: 'activityScore'},
                        {data: 'pValue', name: 'pValue'},
                        {data: 'action', name: 'action', orderable: false, searchable: false}
                    ],
                    order:      [[3, 'asc'], [2, 'desc']],
                    columnDefs: [
                        {targets: 0},
                        {targets: 1},
                        {targets: 2},
                        {targets: 3},
                        {targets: 4, className: 'text-center'}
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
