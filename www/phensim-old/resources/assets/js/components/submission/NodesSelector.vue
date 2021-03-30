<template>
    <div class="col-sm-8 col-sm-offset-2">
        <label :for="id" class="control-label">{{ label }}</label>
        <select class="form-control" :id="id" :name="name" multiple></select>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        props: ['id', 'name', 'label', 'data_url', 'org_field'],

        /**
         * Prepare the component (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            getOrganism() {
                return window.$('#' + this.org_field).val();
            },
            prepareSelect() {
                let $selectNoi = window.$('#' + this.id), $self = this;
                if ($selectNoi.hasClass('ok')) {
                    $selectNoi.select2('destroy').removeClass('ok');
                }
                $selectNoi.select2({
                    ajax:               {
                        url:            $self.data_url,
                        dataType:       'json',
                        delay:          250,
                        data:           function (params) {
                            return {
                                organism: $self.getOrganism(),
                                q:        params.term, // search term
                                page:     params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            let results = [];
                            window.$.each(data.data, function (i, v) {
                                results.push({
                                    id:        v.accession,
                                    text:      v.accession,
                                    name:      v.name,
                                    accession: v.accession,
                                });
                            });
                            return {
                                results:    results,
                                pagination: {
                                    more: (params.page * 30) < data.total
                                }
                            };
                        },
                        cache:          true
                    },
                    escapeMarkup:       function (markup) {
                        return markup;
                    }, // let our custom formatter work
                    minimumInputLength: 1,
                    templateResult:     function (result) {
                        if (result.loading) return result.text;
                        return $('<div class="row">' +
                            '<div class="col-xs-2">' + result.accession + '</div>' +
                            '<div class="col-xs-9">' + result.name + '</div>' +
                            '</div>');
                    },
                    templateSelection:  function (selection) {
                        return selection.accession || selection.text;
                    }
                }).addClass('ok');
            },
            /**
             * Prepare the component.
             */
            prepareComponent() {
                let $self = this;
                VueBus.$on('prepareSelect', function (id) {
                    if (id === $self.id) {
                        $self.prepareSelect();
                    }
                });
            }
        }
    }
</script>
