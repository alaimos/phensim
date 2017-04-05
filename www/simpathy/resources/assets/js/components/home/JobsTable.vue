<template>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap" id="jobs-table">
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
</template>

<script>
    export default {
        /*
         * The component's data.
         */
        data() {
            return {
                accessToken: null,

                tokens: [],
                scopes: [],

                form: {
                    name: '',
                    scopes: [],
                    errors: []
                }
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
            /**
             * Prepare the component.
             */
            prepareComponent() {
                this.getTokens();
                this.getScopes();

                window.$('#modal-create-token').on('shown.bs.modal', () => {
                    window.$('#create-token-name').focus();
                });
            },

            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                axios.get('/oauth/personal-access-tokens')
                    .then(response => {
                        this.tokens = response.data;
                    });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                axios.get('/oauth/scopes')
                    .then(response => {
                        this.scopes = response.data;
                    });
            },

            /**
             * Show the form for creating new tokens.
             */
            showCreateTokenForm() {
                window.$('#modal-create-token').modal('show');
            },

            /**
             * Create a new personal access token.
             */
            store() {
                this.accessToken = null;

                this.form.errors = [];

                axios.post('/oauth/personal-access-tokens', this.form)
                    .then(response => {
                        this.form.name = '';
                        this.form.scopes = [];
                        this.form.errors = [];

                        this.tokens.push(response.data.token);

                        this.showAccessToken(response.data.accessToken);
                    })
                    .catch(error => {
                        if (typeof error.response.data === 'object') {
                            this.form.errors = _.flatten(_.toArray(error.response.data));
                        } else {
                            this.form.errors = ['Something went wrong. Please try again.'];
                        }
                    });
            },

            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },

            /**
             * Show the given access token to the user.
             */
            showAccessToken(accessToken) {
                window.$('#modal-create-token').modal('hide');

                this.accessToken = accessToken;

                window.$('#modal-access-token').modal('show');
            },

            /**
             * Revoke the given token.
             */
            revoke(token) {
                axios.delete('/oauth/personal-access-tokens/' + token.id)
                    .then(response => {
                        this.getTokens();
                    });
            }
        }
    }
</script>
