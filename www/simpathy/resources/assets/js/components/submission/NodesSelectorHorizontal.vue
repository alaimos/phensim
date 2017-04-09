<template>
    <div class="form-group" :class="{'has-error':hasError}">
        <label :for="id" class="control-label col-md-3">{{ label }}</label>
        <div class="col-md-9">
            <select class="form-control" :id="id" :name="name" multiple></select>
            <div class="help-block" v-if="hasError">{{ error }}</div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        props: ['id', 'name', 'label', 'data_url', 'org_field', 'error'],

        computed: {
            hasError: function () {
                return this.error != "";
            }
        },

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
                this.prepareSelect();
            }
        }
    }
</script>
